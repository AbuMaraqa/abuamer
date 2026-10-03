<?php

use App\Models\Brand;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('lists the visible brands in order with their visible products counted', function () {
        $categories = createCategoryTree(['Mixers' => [], 'Hidden' => []]);
        $categories['Hidden']->update(['status' => false]);
        $second = Brand::factory()->named('Nordbad')->create(['sort_order' => 2]);
        $first = Brand::factory()->named('Aquaro')->create(['sort_order' => 1]);
        Brand::factory()->named('Secret')->inactive()->create();
        Product::factory()->for($first)->for($categories['Mixers'])->count(2)->create();
        Product::factory()->for($first)->for($categories['Mixers'])->inactive()->create();
        Product::factory()->for($first)->for($categories['Hidden'])->create();

        $response = $this->get(route('brands.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Brands/Index')
            ->has('brands', 2)
            ->where('brands.0.id', $first->id)
            ->where('brands.0.products_count', 2)
            ->where('brands.1.id', $second->id));
    });
});

describe('show', function () {
    it('shows a brand with its visible products', function () {
        $brand = Brand::factory()->named('Aquaro')->create(['ar' => ['description' => 'دار تصميم إيطالية.']]);
        $product = Product::factory()->for($brand)->create();
        Product::factory()->create();

        $response = $this->get(route('brands.show', ['brand' => 'aquaro']));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Brands/Show')
            ->where('brand.name', 'Aquaro')
            ->where('brand.description', 'دار تصميم إيطالية.')
            ->where('seo.title', fn (string $title): bool => str_starts_with($title, 'Aquaro |'))
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id));
    });

    it('filters the products by collection and offers only collections with products of the brand', function () {
        $categories = createCategoryTree(['Tiles' => ['Porcelain' => []], 'Sanitary Ware' => ['Mixers' => [], 'Basins' => []]]);
        $brand = Brand::factory()->create();
        $mixer = Product::factory()->for($brand)->for($categories['Mixers'])->create();
        Product::factory()->for($brand)->for($categories['Porcelain'])->create();

        $response = $this->get(route('brands.show', ['brand' => $brand->slug, 'category' => $categories['Sanitary Ware']->id]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('collections', [
                ['id' => $categories['Tiles']->id, 'name' => 'Tiles'],
                ['id' => $categories['Sanitary Ware']->id, 'name' => 'Sanitary Ware'],
            ])
            ->where('filters.category', $categories['Sanitary Ware']->id)
            ->has('products.data', 1)
            ->where('products.data.0.id', $mixer->id));
    });

    it('returns 404 for a hidden brand', function () {
        $brand = Brand::factory()->inactive()->create();

        $this->get(route('brands.show', ['brand' => $brand->slug]))->assertNotFound();
    });
});
