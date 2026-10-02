<?php

namespace App\Services\Catalog;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * An in-memory index of the whole category tree, built from one flat list of categories.
 *
 * Every question about the tree — children, ancestors, descendants, depth, slug paths,
 * visibility — is answered by walking arrays keyed by id, so no query runs per node
 * regardless of how deep the tree grows. Nothing here assumes a fixed number of levels.
 */
final class CategoryTree
{
    /**
     * The parent key under which root categories (parent_id = null) are indexed.
     */
    private const int ROOT = 0;

    /**
     * @var array<int, Category>
     */
    private array $categories = [];

    /**
     * Ordered child ids per parent id.
     *
     * @var array<int, list<int>>
     */
    private array $childIds = [];

    /**
     * @param  iterable<Category>  $categories  Every category of the tree, in any order.
     */
    public function __construct(iterable $categories)
    {
        foreach ($categories as $category) {
            $this->categories[$category->id] = $category;
        }

        $ordered = $this->categories;
        uasort($ordered, fn (Category $a, Category $b): int => [$a->sort_order, $a->id] <=> [$b->sort_order, $b->id]);

        foreach ($ordered as $category) {
            $this->childIds[$category->parent_id ?? self::ROOT][] = $category->id;
        }
    }

    public function has(int $id): bool
    {
        return isset($this->categories[$id]);
    }

    public function find(int $id): ?Category
    {
        return $this->categories[$id] ?? null;
    }

    public function count(): int
    {
        return count($this->categories);
    }

    /**
     * @return Collection<int, Category>
     */
    public function roots(): Collection
    {
        return $this->children(null);
    }

    /**
     * The direct children of a category, or the root categories when the parent is null.
     *
     * @return Collection<int, Category>
     */
    public function children(?int $parentId): Collection
    {
        return $this->collect($this->childIds($parentId));
    }

    /**
     * @return list<int>
     */
    public function childIds(?int $parentId): array
    {
        return $this->childIds[$parentId ?? self::ROOT] ?? [];
    }

    /**
     * Ancestors ordered from the root down to the direct parent.
     *
     * @return Collection<int, Category>
     */
    public function ancestors(int $id): Collection
    {
        return $this->collect($this->ancestorIds($id));
    }

    /**
     * Ancestor ids ordered from the root down to the direct parent.
     *
     * @return list<int>
     */
    public function ancestorIds(int $id): array
    {
        $ids = [];
        $parentId = $this->categories[$id]->parent_id ?? null;

        // The visited check keeps a corrupted (cyclic) dataset from looping forever.
        while ($parentId !== null && isset($this->categories[$parentId]) && ! in_array($parentId, $ids, true)) {
            $ids[] = $parentId;
            $parentId = $this->categories[$parentId]->parent_id;
        }

        return array_reverse($ids);
    }

    /**
     * The category preceded by its ancestors: the breadcrumb trail from the root.
     *
     * @return Collection<int, Category>
     */
    public function path(int $id): Collection
    {
        return $this->collect([...$this->ancestorIds($id), $id]);
    }

    /**
     * Ids of every category below the given one, depth first.
     *
     * When $activeOnly is true, disabled categories and everything beneath them are skipped.
     *
     * @return list<int>
     */
    public function descendantIds(int $id, bool $activeOnly = false): array
    {
        $ids = [];
        $stack = array_reverse($this->childIds($id));

        while ($stack !== []) {
            $childId = array_pop($stack);

            if ($activeOnly && ! $this->categories[$childId]->status) {
                continue;
            }

            $ids[] = $childId;
            array_push($stack, ...array_reverse($this->childIds($childId)));
        }

        return $ids;
    }

    /**
     * The category's own id followed by the ids of all its descendants.
     *
     * @return list<int>
     */
    public function subtreeIds(int $id, bool $activeOnly = false): array
    {
        return [$id, ...$this->descendantIds($id, $activeOnly)];
    }

    /**
     * Ids of every category shown on the website: enabled, with every ancestor enabled.
     *
     * @return list<int>
     */
    public function visibleIds(): array
    {
        return $this->roots()
            ->filter(fn (Category $root): bool => $root->status)
            ->flatMap(fn (Category $root): array => $this->subtreeIds($root->id, activeOnly: true))
            ->values()
            ->all();
    }

    /**
     * Zero for root categories, one for their children, and so on.
     */
    public function depth(int $id): int
    {
        return count($this->ancestorIds($id));
    }

    public function root(int $id): ?Category
    {
        $ancestorIds = $this->ancestorIds($id);

        return $this->find($ancestorIds[0] ?? $id);
    }

    public function isLeaf(int $id): bool
    {
        return $this->childIds($id) === [];
    }

    public function isDescendantOf(int $id, int $ancestorId): bool
    {
        return in_array($ancestorId, $this->ancestorIds($id), true);
    }

    /**
     * A category is visible on the website only when it and all of its ancestors are enabled.
     */
    public function isVisible(int $id): bool
    {
        if (! ($this->categories[$id]->status ?? false)) {
            return false;
        }

        foreach ($this->ancestorIds($id) as $ancestorId) {
            if (! $this->categories[$ancestorId]->status) {
                return false;
            }
        }

        return true;
    }

    /**
     * The slugs from the root to the category joined by slashes, e.g. "porcelain/marble-effect".
     */
    public function slugPath(int $id, string $locale): string
    {
        return $this->path($id)
            ->map(fn (Category $category): ?string => $category->translate($locale, true)?->slug)
            ->implode('/');
    }

    /**
     * Resolve a nested URL path such as ["porcelain", "marble-effect"] segment by segment:
     * each slug must belong to a child of the category matched by the previous segment.
     *
     * @param  list<string>  $slugs
     */
    public function findBySlugPath(array $slugs, string $locale): ?Category
    {
        $parentId = null;
        $match = null;

        foreach ($slugs as $slug) {
            $match = $this->children($parentId)
                ->first(fn (Category $category): bool => $category->translate($locale)?->slug === $slug);

            if ($match === null) {
                return null;
            }

            $parentId = $match->id;
        }

        return $match;
    }

    /**
     * Categories with their `children` relations filled recursively from memory,
     * starting below $parentId (or at the roots).
     *
     * The returned models are copies, so the shared tree instances are never mutated.
     *
     * @return Collection<int, Category>
     */
    public function nested(?int $parentId = null, bool $activeOnly = false, ?int $maxDepth = null): Collection
    {
        $nodes = [];

        foreach ($this->childIds($parentId) as $id) {
            $category = $this->categories[$id];

            if ($activeOnly && ! $category->status) {
                continue;
            }

            $node = clone $category;
            $node->setRelation('children', $maxDepth === 1
                ? new Collection
                : $this->nested($id, $activeOnly, $maxDepth === null ? null : $maxDepth - 1));
            $nodes[] = $node;
        }

        return new Collection($nodes);
    }

    /**
     * The category whose visible children are presented as the main collections: none (the
     * roots themselves) unless the website has a single visible root such as "Tiles".
     */
    public function collectionsParentId(): ?int
    {
        $visibleRoots = $this->roots()->filter(fn (Category $root): bool => $root->status);

        return $visibleRoots->count() === 1 ? $visibleRoots->first()->id : null;
    }

    /**
     * The visible main collections (see collectionsParentId()).
     *
     * @return Collection<int, Category>
     */
    public function collections(): Collection
    {
        return $this->children($this->collectionsParentId())
            ->filter(fn (Category $category): bool => $category->status)
            ->values();
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Category>
     */
    private function collect(array $ids): Collection
    {
        return new Collection(array_map(fn (int $id): Category => $this->categories[$id], $ids));
    }
}
