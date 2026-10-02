<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('lists active products from visible categories only', function () {
        $categories = createCategoryTree(['Porcelain' => [], 'Hidden' => []]);
        $categories['Hidden']->update(['status' => false]);
        $visible = Product::factory()->for($categories['Porcelain'])->create();
        Product::factory()->for($categories['Porcelain'])->inactive()->create();
        Product::factory()->for($categories['Hidden'])->create();

        $response = $this->get(route('products.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Index')
            ->has('products.data', 1)
            ->where('products.data.0.id', $visible->id)
            ->has('categories', 1));
    });

    it('paginates the products', function () {
        Product::factory()->count(config('catalog.per_page') + 1)->create();

        $response = $this->get(route('products.index', ['page' => 2]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.meta.total', config('catalog.per_page') + 1));
    });

    it('searches products by name in either language', function (string $term) {
        $match = Product::factory()->named('Calacatta Gold 60x120', 'كالاكاتا ذهبي')->create();
        Product::factory()->named('Carrara White', 'كرارا أبيض')->create();

        $response = $this->get(route('products.index', ['q' => $term]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $match->id));
    })->with([
        'English name' => ['calacatta'],
        'Arabic name' => ['كالاكاتا'],
    ]);

    it('searches products by SKU', function () {
        $match = Product::factory()->create(['sku' => 'NSQ-CAL-0001']);
        Product::factory()->create(['sku' => 'NSQ-CAR-0002']);

        $response = $this->get(route('products.index', ['q' => 'CAL-0001']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $match->id));
    });

    it('treats percent signs in the search term literally', function () {
        Product::factory()->named('Matte Finish')->create();

        $response = $this->get(route('products.index', ['q' => '%']));

        $response->assertInertia(fn (Assert $page) => $page->has('products.data', 0));
    });

    it('filters by a category including its descendants', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []], 'Wall Tiles' => []]);
        $nested = Product::factory()->for($categories['Marble Effect'])->create();
        Product::factory()->for($categories['Wall Tiles'])->create();

        $response = $this->get(route('products.index', ['category' => $categories['Porcelain']->id]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $nested->id));
    });
});

describe('show', function () {
    it('renders a product with its specifications and full category path', function () {
        $categories = createCategoryTree(['Tiles' => ['Porcelain' => ['Marble Effect' => ['Calacatta' => ['Gold' => []]]]]]);
        $product = Product::factory()->for($categories['Gold'])->named('Calacatta Gold 60x120', 'كالاكاتا ذهبي 60×120')->create();
        ProductSpecification::factory()->for($product)->create();

        $response = $this->get(route('products.show', ['slug' => $product->translate('ar')->slug]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Product')
            ->where('product.id', $product->id)
            ->where('product.specifications.0.label', 'المقاس')
            ->where('breadcrumbs', fn ($breadcrumbs) => collect($breadcrumbs)->pluck('label')->all() === [
                'الرئيسية', 'المنتجات', 'Tiles', 'Porcelain', 'Marble Effect', 'Calacatta', 'Gold', 'كالاكاتا ذهبي 60×120',
            ]));
    });

    it('redirects a slug from the other language to this language', function () {
        $product = Product::factory()->named('Calacatta Gold', 'كالاكاتا ذهبي')->create();

        $response = $this->get(route('products.show', ['slug' => 'calacatta-gold']));

        $response->assertRedirect(route('products.show', ['slug' => 'كالاكاتا-ذهبي']))
            ->assertStatus(301);
    });

    it('returns 404 for an inactive product', function () {
        $product = Product::factory()->inactive()->create();

        $response = $this->get(route('products.show', ['slug' => $product->translate('ar')->slug]));

        $response->assertNotFound();
    });

    it('returns 404 when the product category is hidden', function () {
        $category = Category::factory()->inactive()->create();
        $product = Product::factory()->for($category)->create();

        $response = $this->get(route('products.show', ['slug' => $product->translate('ar')->slug]));

        $response->assertNotFound();
    });
});
