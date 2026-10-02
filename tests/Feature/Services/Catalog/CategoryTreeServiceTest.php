<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use Illuminate\Validation\ValidationException;

function categoryTreeService(): CategoryTreeService
{
    return app(CategoryTreeService::class);
}

/**
 * @return list<int>
 */
function childIdsOf(?Category $parent): array
{
    return Category::query()->where('parent_id', $parent?->id)->ordered()->pluck('id')->all();
}

describe('move', function () {
    it('reorders categories within the same parent', function () {
        $categories = createCategoryTree(['A' => [], 'B' => [], 'C' => []]);

        categoryTreeService()->move($categories['C'], null, 1);

        expect(childIdsOf(null))->toBe([$categories['C']->id, $categories['A']->id, $categories['B']->id])
            ->and(Category::query()->ordered()->pluck('sort_order')->all())->toBe([1, 2, 3]);
    });

    it('moves a category into another category with its whole subtree', function () {
        $categories = createCategoryTree([
            'Floor Tiles' => ['Marble' => ['White' => []]],
            'Porcelain' => ['Stone Effect' => []],
        ]);

        categoryTreeService()->move($categories['Marble'], $categories['Porcelain']->id, 1);

        expect($categories['Marble']->parent_id)->toBe($categories['Porcelain']->id)
            ->and(childIdsOf($categories['Porcelain']))->toBe([$categories['Marble']->id, $categories['Stone Effect']->id])
            ->and($categories['White']->fresh()->parent_id)->toBe($categories['Marble']->id)
            ->and(categoryTreeService()->tree()->depth($categories['White']->id))->toBe(2);
    });

    it('closes the gap left in the previous parent', function () {
        $categories = createCategoryTree(['Floor Tiles' => ['Indoor' => [], 'Marble' => [], 'Outdoor' => []], 'Porcelain' => []]);

        categoryTreeService()->move($categories['Marble'], $categories['Porcelain']->id, 1);

        expect(Category::query()->whereKey([$categories['Indoor']->id, $categories['Outdoor']->id])->ordered()->pluck('sort_order')->all())
            ->toBe([1, 2]);
    });

    it('places a category after its last sibling when the position exceeds the count', function () {
        $categories = createCategoryTree(['A' => [], 'B' => [], 'Parent' => ['X' => []]]);

        categoryTreeService()->move($categories['A'], $categories['Parent']->id, 99);

        expect(childIdsOf($categories['Parent']))->toBe([$categories['X']->id, $categories['A']->id]);
    });

    it('moves a nested category back to the root level', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []], 'Wall Tiles' => []]);

        categoryTreeService()->move($categories['Marble Effect'], null, 2);

        expect(childIdsOf(null))->toBe([$categories['Porcelain']->id, $categories['Marble Effect']->id, $categories['Wall Tiles']->id]);
    });

    it('refuses to move a category into its own descendant', function () {
        $categories = createCategoryTree(['A' => ['B' => ['C' => []]]]);

        expect(fn () => categoryTreeService()->move($categories['A'], $categories['C']->id, 1))
            ->toThrow(ValidationException::class, 'لا يمكن نقل التصنيف إلى نفسه أو إلى أحد تصنيفاته الفرعية.');

        expect($categories['A']->fresh()->parent_id)->toBeNull();
    });

    it('refuses to move a category into itself', function () {
        $category = Category::factory()->create();

        expect(fn () => categoryTreeService()->move($category, $category->id, 1))->toThrow(ValidationException::class);
    });

    it('refuses a destination that already has a child with the same slug', function () {
        $floorTiles = Category::factory()->named('Floor Tiles')->create();
        $porcelain = Category::factory()->named('Porcelain')->create();
        $marble = Category::factory()->named('Marble')->childOf($floorTiles)->create();
        Category::factory()->named('Marble')->childOf($porcelain)->create();

        expect(fn () => categoryTreeService()->move($marble, $porcelain->id, 1))
            ->toThrow(ValidationException::class, 'يحتوي المكان المستهدف على تصنيف بالرابط المختصر "marble" مسبقًا.');

        expect($marble->fresh()->parent_id)->toBe($floorTiles->id);
    });
});

describe('reorder', function () {
    it('applies a complete order to the children of a parent', function () {
        $categories = createCategoryTree(['Parent' => ['A' => [], 'B' => [], 'C' => []]]);
        $newOrder = [$categories['B']->id, $categories['C']->id, $categories['A']->id];

        categoryTreeService()->reorder($categories['Parent']->id, $newOrder);

        expect(childIdsOf($categories['Parent']))->toBe($newOrder);
    });

    it('refuses an order that does not contain exactly the parent children', function () {
        $categories = createCategoryTree(['Parent' => ['A' => [], 'B' => []], 'Other' => []]);

        expect(fn () => categoryTreeService()->reorder($categories['Parent']->id, [$categories['A']->id, $categories['Other']->id]))
            ->toThrow(ValidationException::class);
    });
});

describe('delete', function () {
    it('deletes a leaf category and its translations', function () {
        $categories = createCategoryTree(['A' => [], 'B' => [], 'C' => []]);

        categoryTreeService()->delete($categories['B']);

        $this->assertModelMissing($categories['B']);
        $this->assertDatabaseMissing('category_translations', ['category_id' => $categories['B']->id]);
        expect(Category::query()->ordered()->pluck('sort_order')->all())->toBe([1, 2]);
    });

    it('refuses to delete a category with children unless descendants are included', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);

        expect(fn () => categoryTreeService()->delete($categories['Porcelain']))
            ->toThrow(ValidationException::class, 'لا يمكن حذف التصنيف لأنه يحتوي على تصنيفات فرعية.');

        $this->assertModelExists($categories['Porcelain']);
    });

    it('deletes a category together with its descendants when requested', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []]], 'Wall Tiles' => []]);

        categoryTreeService()->delete($categories['Porcelain'], withDescendants: true);

        expect(Category::query()->pluck('id')->all())->toBe([$categories['Wall Tiles']->id]);
    });

    it('never deletes a category whose subtree contains products', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []]]]);
        $product = Product::factory()->for($categories['Calacatta'])->create();

        expect(fn () => categoryTreeService()->delete($categories['Porcelain'], withDescendants: true))
            ->toThrow(ValidationException::class, 'لا يمكن حذف التصنيف لأنه يحتوي على منتجات.');

        $this->assertModelExists($categories['Calacatta']);
        $this->assertModelExists($product);
    });
});
