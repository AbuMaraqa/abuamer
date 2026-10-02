<?php

use App\Models\Category;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

it('renders a nested category resolved from its slug path', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => ['Gold' => []]]]]);

    $response = $this->get(route('products.category', ['path' => 'porcelain/marble-effect/calacatta']));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Catalog/Category')
        ->where('category.id', $categories['Calacatta']->id)
        ->has('children', 1)
        ->where('children.0.id', $categories['Gold']->id));
});

it('builds the breadcrumbs from the category ancestors', function () {
    createCategoryTree(['Tiles' => ['Porcelain' => ['Marble Effect' => []]]]);

    $response = $this->get(route('products.category', ['path' => 'tiles/porcelain/marble-effect']));

    $response->assertInertia(fn (Assert $page) => $page->where('breadcrumbs', [
        ['label' => 'الرئيسية', 'url' => route('home')],
        ['label' => 'المنتجات', 'url' => route('products.index')],
        ['label' => 'Tiles', 'url' => route('products.category', ['path' => 'tiles'])],
        ['label' => 'Porcelain', 'url' => route('products.category', ['path' => 'tiles/porcelain'])],
        ['label' => 'Marble Effect', 'url' => route('products.category', ['path' => 'tiles/porcelain/marble-effect'])],
    ]));
});

it('resolves Arabic slug paths', function () {
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->create();
    $marble = Category::factory()->named('Marble Effect', 'تأثير الرخام')->childOf($porcelain)->create();

    $response = $this->get(route('products.category', ['path' => 'بورسلان/تأثير-الرخام']));

    $response->assertInertia(fn (Assert $page) => $page->where('category.id', $marble->id));
});

it('links to the same category in the other language', function () {
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->create();
    Category::factory()->named('Marble Effect', 'تأثير الرخام')->childOf($porcelain)->create();

    $response = $this->get(route('products.category', ['path' => 'بورسلان/تأثير-الرخام']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('localeUrls.en', url('en/products/porcelain/marble-effect'))
        ->where('localeUrls.ar', route('products.category', ['path' => 'بورسلان/تأثير-الرخام'])));
});

it('resolves Hebrew slug paths with the English slug of a category that has no Hebrew name', function () {
    $this->useRoutingLocale('he');
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->withHebrew('פורצלן')->create();
    $marble = Category::factory()->named('Marble Effect', 'تأثير الرخام')->childOf($porcelain)->create();

    $response = $this->get(route('products.category', ['path' => 'פורצלן/marble-effect']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('category.id', $marble->id)
        ->where('category.name', 'Marble Effect'));
});

it('links to the Hebrew page with the slugs its categories are shown with', function () {
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->withHebrew('פורצלן')->create();
    Category::factory()->named('Marble Effect', 'تأثير الرخام')->childOf($porcelain)->create();

    $response = $this->get(route('products.category', ['path' => 'بورسلان/تأثير-الرخام']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('localeUrls.he', url('he/products/'.rawurlencode('פורצלן').'/marble-effect')));
});

it('returns 404 when the path segments are not nested', function () {
    createCategoryTree(['Porcelain' => [], 'Marble Effect' => []]);

    $response = $this->get(route('products.category', ['path' => 'porcelain/marble-effect']));

    $response->assertNotFound();
});

it('returns 404 when an ancestor is hidden', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);
    $categories['Porcelain']->update(['status' => false]);

    $response = $this->get(route('products.category', ['path' => 'porcelain/marble-effect']));

    $response->assertNotFound();
});

it('lists active products of the category and all its visible descendants', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []], 'Hidden Effect' => []], 'Wall Tiles' => []]);
    $categories['Hidden Effect']->update(['status' => false]);
    $direct = Product::factory()->for($categories['Porcelain'])->create();
    $nested = Product::factory()->for($categories['Calacatta'])->create();
    Product::factory()->for($categories['Calacatta'])->inactive()->create();
    Product::factory()->for($categories['Hidden Effect'])->create();
    Product::factory()->for($categories['Wall Tiles'])->create();

    $response = $this->get(route('products.category', ['path' => 'porcelain']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('products.data', fn ($products) => collect($products)->pluck('id')->sort()->values()->all() === collect([$direct->id, $nested->id])->sort()->values()->all()));
});

it('lists only directly assigned products when descendant products are disabled', function () {
    config(['catalog.include_descendant_products' => false]);
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);
    $direct = Product::factory()->for($categories['Porcelain'])->create();
    Product::factory()->for($categories['Marble Effect'])->create();

    $response = $this->get(route('products.category', ['path' => 'porcelain']));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('products.data', 1)
        ->where('products.data.0.id', $direct->id));
});
