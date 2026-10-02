<?php

use App\Models\Category;
use App\Models\CompanyHighlight;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the company content, collections, featured products and highlights', function () {
    createCategoryTree(['Porcelain' => [], 'Wall Tiles' => []]);
    $featured = Product::factory()->featured()->create();
    Product::factory()->create();
    CompanyHighlight::factory()->feature()->create();
    CompanyHighlight::factory()->statistic('25+')->create();
    CompanyHighlight::factory()->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->has('company')
        ->has('featuredProducts', 1)
        ->where('featuredProducts.0.id', $featured->id)
        ->has('features', 1)
        ->where('statistics.0.value', '25+'));
});

it('leaves out featured products from hidden categories', function () {
    $hidden = Category::factory()->inactive()->create();
    Product::factory()->featured()->for($hidden)->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page->has('featuredProducts', 0));
});

it('shares the visible collections as the navigation', function () {
    $categories = createCategoryTree(['Tiles' => ['Porcelain' => ['Marble Effect' => ['Calacatta' => []]], 'Hidden' => []]]);
    $categories['Hidden']->update(['status' => false]);

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('site.navigation', 1)
        ->where('site.navigation.0.name', 'Porcelain')
        ->where('site.navigation.0.children.0.name', 'Marble Effect')
        ->has('site.navigation.0.children.0.children', 0));
});
