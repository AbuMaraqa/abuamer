<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reads and restructures the category tree.
 *
 * Reads use one cached query turned into a {@see CategoryTree}. Structural changes
 * (move, reorder, delete) run in a transaction against freshly locked rows, so the
 * tree can never be left half-updated or turned into a cycle by concurrent requests.
 */
#[Scoped]
class CategoryTreeService
{
    private const string CACHE_KEY = 'catalog.category-tree';

    private ?CategoryTree $tree = null;

    /**
     * The whole tree with names and slugs in every language, cached until a category changes.
     *
     * Tree models carry only structural columns and name/slug translations; load the
     * category itself for long-form content such as descriptions and SEO fields.
     */
    public function tree(): CategoryTree
    {
        return $this->tree ??= new CategoryTree($this->hydrate(
            Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->snapshot()),
        ));
    }

    public function flush(): void
    {
        $this->tree = null;

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The sort_order that appends a new category after its future siblings.
     */
    public function nextSortOrder(?int $parentId): int
    {
        return (int) Category::query()->where('parent_id', $parentId)->max('sort_order') + 1;
    }

    /**
     * Whether a sibling under $parentId already uses the slug in that locale.
     * Slugs only need to be unique among siblings because category URLs are nested paths.
     */
    public function siblingSlugExists(?int $parentId, string $locale, string $slug, ?int $ignoreCategoryId = null): bool
    {
        return CategoryTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('category', fn (Builder $category) => $category
                ->where('parent_id', $parentId)
                ->when($ignoreCategoryId, fn (Builder $category) => $category->whereKeyNot($ignoreCategoryId)))
            ->exists();
    }

    /**
     * Move a category (with its whole subtree) under a new parent at a 1-based position
     * among its new siblings. Moving within the same parent simply reorders.
     *
     * @throws ValidationException when the move would create a cycle or a slug clash.
     */
    public function move(Category $category, ?int $parentId, int $position): void
    {
        DB::transaction(function () use ($category, $parentId, $position) {
            $structure = $this->lockedStructure();
            $previousParentId = $structure->find($category->id)->parent_id;

            if ($previousParentId !== $parentId) {
                $this->ensureCanMoveUnder($structure, $category, $parentId);
            }

            $siblingIds = $this->withoutId($structure->childIds($parentId), $category->id);
            array_splice($siblingIds, max(0, min($position - 1, count($siblingIds))), 0, [$category->id]);

            Category::query()->whereKey($category->id)->update(['parent_id' => $parentId]);
            $this->resequence($siblingIds);

            if ($previousParentId !== $parentId) {
                $this->resequence($this->withoutId($structure->childIds($previousParentId), $category->id));
            }
        });

        $this->flush();
        $category->refresh();
    }

    /**
     * Apply a complete new order to the children of one parent.
     *
     * @param  list<int>  $orderedIds  Exactly the ids of the parent's current children.
     *
     * @throws ValidationException when the ids are not exactly the parent's children.
     */
    public function reorder(?int $parentId, array $orderedIds): void
    {
        DB::transaction(function () use ($parentId, $orderedIds) {
            $childIds = Category::query()->where('parent_id', $parentId)->lockForUpdate()->pluck('id')->all();

            sort($childIds);
            $requestedIds = $orderedIds;
            sort($requestedIds);

            if ($childIds !== $requestedIds) {
                throw ValidationException::withMessages([
                    'ids' => __('The list must contain exactly the categories under the selected parent.'),
                ]);
            }

            $this->resequence($orderedIds);
        });

        $this->flush();
    }

    /**
     * Delete a category. Child categories are only removed when explicitly requested,
     * and a category whose subtree still holds products is never deleted.
     *
     * @throws ValidationException when the category has children (without consent) or products.
     */
    public function delete(Category $category, bool $withDescendants = false): void
    {
        DB::transaction(function () use ($category, $withDescendants) {
            $structure = $this->lockedStructure();
            $subtreeIds = $structure->subtreeIds($category->id);

            if (count($subtreeIds) > 1 && ! $withDescendants) {
                throw ValidationException::withMessages([
                    'category' => __('Cannot delete category because it has child categories.'),
                ]);
            }

            if (Product::query()->inCategories($subtreeIds)->exists()) {
                throw ValidationException::withMessages([
                    'category' => __('Cannot delete category because it contains products. Move its products to another category first.'),
                ]);
            }

            // Deepest first so no parent is deleted before its children (the foreign key restricts it).
            // Deleting through the models also removes their translations and image files.
            Category::query()
                ->whereKey($subtreeIds)
                ->get()
                ->sortByDesc(fn (Category $node): int => $structure->depth($node->id))
                ->each->delete();

            $parentId = $structure->find($category->id)->parent_id;
            $this->resequence($this->withoutId($structure->childIds($parentId), $category->id));
        });

        $this->flush();
    }

    /**
     * @throws ValidationException
     */
    private function ensureCanMoveUnder(CategoryTree $structure, Category $category, ?int $parentId): void
    {
        if ($parentId === null) {
            $this->ensureNoSlugClash($category, null);

            return;
        }

        if (! $structure->has($parentId)) {
            throw ValidationException::withMessages(['parent_id' => __('The selected parent category no longer exists.')]);
        }

        if ($parentId === $category->id || $structure->isDescendantOf($parentId, $category->id)) {
            throw ValidationException::withMessages([
                'parent_id' => __('A category cannot be moved into itself or into one of its own subcategories.'),
            ]);
        }

        $this->ensureNoSlugClash($category, $parentId);
    }

    /**
     * @throws ValidationException
     */
    private function ensureNoSlugClash(Category $category, ?int $parentId): void
    {
        foreach ($category->translations as $translation) {
            if ($this->siblingSlugExists($parentId, $translation->locale, $translation->slug, $category->id)) {
                throw ValidationException::withMessages([
                    'parent_id' => __('The destination already contains a category with the slug ":slug".', ['slug' => $translation->slug]),
                ]);
            }
        }
    }

    /**
     * The tree as plain arrays. Laravel refuses to unserialize objects from the cache
     * (cache.serializable_classes), so models are rebuilt from these rows on read.
     *
     * @return list<array<string, mixed>>
     */
    private function snapshot(): array
    {
        return Category::query()
            ->select(['id', 'parent_id', 'status', 'sort_order'])
            ->with('translations:id,category_id,locale,name,slug')
            ->get()
            ->map(fn (Category $category): array => [
                'attributes' => $category->getAttributes(),
                'translations' => $category->translations->map->getAttributes()->all(),
            ])
            ->all();
    }

    /**
     * @param  list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}>  $rows
     * @return Collection<int, Category>
     */
    private function hydrate(array $rows): Collection
    {
        return Category::hydrate(array_column($rows, 'attributes'))
            ->each(fn (Category $category, int $index) => $category->setRelation(
                'translations',
                CategoryTranslation::hydrate($rows[$index]['translations']),
            ));
    }

    /**
     * The current tree structure, with every category row locked until the transaction ends.
     */
    private function lockedStructure(): CategoryTree
    {
        return new CategoryTree(
            Category::query()->lockForUpdate()->get(['id', 'parent_id', 'status', 'sort_order']),
        );
    }

    /**
     * Persist sort_order 1..n following the given order, in a single query.
     *
     * @param  list<int>  $orderedIds
     */
    private function resequence(array $orderedIds): void
    {
        if ($orderedIds === []) {
            return;
        }

        // Ids and positions are integers from the database or validated input, so they are safe to inline.
        $cases = collect($orderedIds)
            ->map(fn (int $id, int $index): string => 'when '.$id.' then '.($index + 1))
            ->implode(' ');

        Category::query()->whereKey($orderedIds)->update(['sort_order' => DB::raw("case id {$cases} end")]);
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function withoutId(array $ids, int $id): array
    {
        return array_values(array_filter($ids, fn (int $candidate): bool => $candidate !== $id));
    }
}
