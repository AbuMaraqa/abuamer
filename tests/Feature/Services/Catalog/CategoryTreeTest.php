<?php

use App\Models\Category;
use App\Services\Catalog\CategoryTree;
use App\Services\Catalog\CategoryTreeService;

function categoryTree(): CategoryTree
{
    return app(CategoryTreeService::class)->tree();
}

it('lists root categories and children in sort order', function () {
    $categories = createCategoryTree([
        'Porcelain' => ['Stone Effect' => [], 'Marble Effect' => []],
        'Wall Tiles' => [],
    ]);
    $categories['Marble Effect']->update(['sort_order' => 0]);

    expect(categoryTree()->roots()->pluck('id')->all())->toBe([$categories['Porcelain']->id, $categories['Wall Tiles']->id])
        ->and(categoryTree()->children($categories['Porcelain']->id)->pluck('id')->all())
        ->toBe([$categories['Marble Effect']->id, $categories['Stone Effect']->id]);
});

it('returns ancestors from the root down to the direct parent', function () {
    $categories = createCategoryTree(['Tiles' => ['Porcelain' => ['Marble Effect' => ['Calacatta' => ['Gold' => []]]]]]);

    $ancestors = categoryTree()->ancestors($categories['Gold']->id);

    expect($ancestors->pluck('id')->all())->toBe([
        $categories['Tiles']->id,
        $categories['Porcelain']->id,
        $categories['Marble Effect']->id,
        $categories['Calacatta']->id,
    ]);
});

it('returns every descendant at any depth', function () {
    $categories = createCategoryTree([
        'Porcelain' => [
            'Marble Effect' => ['Calacatta' => ['Gold' => [], 'White' => []], 'Carrara' => []],
            'Wood Effect' => [],
        ],
        'Wall Tiles' => ['Kitchen' => []],
    ]);

    $descendantIds = categoryTree()->descendantIds($categories['Porcelain']->id);

    expect($descendantIds)->toEqualCanonicalizing(collect($categories)
        ->only(['Marble Effect', 'Calacatta', 'Gold', 'White', 'Carrara', 'Wood Effect'])
        ->pluck('id')
        ->all());
});

it('skips disabled branches when only active descendants are requested', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []], 'Wood Effect' => []]]);
    $categories['Marble Effect']->update(['status' => false]);

    expect(categoryTree()->descendantIds($categories['Porcelain']->id, activeOnly: true))->toBe([$categories['Wood Effect']->id]);
});

it('computes depth without a stored depth column', function () {
    $categories = createCategoryTree(['Tiles' => ['Floor Tiles' => ['Indoor' => ['Marble' => ['White' => []]]]]]);

    expect(categoryTree()->depth($categories['Tiles']->id))->toBe(0)
        ->and(categoryTree()->depth($categories['Floor Tiles']->id))->toBe(1)
        ->and(categoryTree()->depth($categories['White']->id))->toBe(4);
});

it('supports trees deeper than any fixed number of levels', function () {
    $parent = null;

    foreach (range(1, 12) as $level) {
        $parent = Category::factory()->create(['parent_id' => $parent?->id]);
    }

    expect(categoryTree()->depth($parent->id))->toBe(11)
        ->and(categoryTree()->ancestors($parent->id))->toHaveCount(11);
});

it('identifies the root and leaf categories', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []]]]);

    expect(categoryTree()->root($categories['Calacatta']->id)->id)->toBe($categories['Porcelain']->id)
        ->and(categoryTree()->isLeaf($categories['Calacatta']->id))->toBeTrue()
        ->and(categoryTree()->isLeaf($categories['Marble Effect']->id))->toBeFalse();
});

it('builds the slug path of a nested category in each language', function () {
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->create();
    $marble = Category::factory()->named('Marble Effect', 'تأثير الرخام')->childOf($porcelain)->create();

    expect(categoryTree()->slugPath($marble->id, 'en'))->toBe('porcelain/marble-effect')
        ->and(categoryTree()->slugPath($marble->id, 'ar'))->toBe('بورسلان/تأثير-الرخام');
});

it('resolves a nested slug path segment by segment', function () {
    $categories = createCategoryTree([
        'Floor Tiles' => ['Indoor' => ['Marble' => ['White' => []]]],
        'Porcelain' => ['Marble' => []],
    ]);

    $resolved = categoryTree()->findBySlugPath(['floor-tiles', 'indoor', 'marble', 'white'], 'en');

    expect($resolved->id)->toBe($categories['White']->id)
        ->and(categoryTree()->findBySlugPath(['porcelain', 'indoor'], 'en'))->toBeNull()
        ->and(categoryTree()->findBySlugPath(['white'], 'en'))->toBeNull();
});

it('hides a category on the website when any ancestor is disabled', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []]]]);
    $categories['Porcelain']->update(['status' => false]);

    expect(categoryTree()->isVisible($categories['Calacatta']->id))->toBeFalse()
        ->and(categoryTree()->visibleIds())->toBe([]);
});

it('nests children recursively without changing the shared tree models', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []]]]);

    $nested = categoryTree()->nested();

    expect($nested->first()->children->first()->children->first()->id)->toBe($categories['Calacatta']->id)
        ->and(categoryTree()->find($categories['Porcelain']->id)->relationLoaded('children'))->toBeFalse();
});

it('rebuilds the tree from a persistent cache store', function () {
    config(['cache.default' => 'database']);
    $category = Category::factory()->named('Porcelain')->create();
    categoryTree();
    app()->forgetScopedInstances();

    $cachedTree = categoryTree();

    expect($cachedTree->find($category->id)->translate('en')->name)->toBe('Porcelain')
        ->and($cachedTree->slugPath($category->id, 'en'))->toBe('porcelain');
});

it('reflects category changes immediately after they are saved', function () {
    $category = Category::factory()->named('Porcelain')->create();
    expect(categoryTree()->find($category->id)->name)->toBe('Porcelain');

    $category->update(['en' => ['name' => 'Fine Porcelain']]);

    expect(categoryTree()->find($category->id)->translate('en')->name)->toBe('Fine Porcelain');
});
