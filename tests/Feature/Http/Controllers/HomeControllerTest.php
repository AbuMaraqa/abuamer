<?php

use App\Models\Category;
use App\Models\CompanyHighlight;
use App\Models\Product;
use App\Models\Slide;
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

it('shows visible slides in order with their links resolved for the language', function () {
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->create();
    $second = Slide::factory()->linkedTo($porcelain)->create(['sort_order' => 2]);
    $first = Slide::factory()->create(['sort_order' => 1]);
    Slide::factory()->inactive()->create(['sort_order' => 0]);

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('slides', 2)
        ->where('slides.0.id', $first->id)
        ->where('slides.0.button', null)
        ->where('slides.1.id', $second->id)
        ->where('slides.1.button.label', 'اكتشف المجموعة')
        ->where('slides.1.button.url', route('products.category', ['path' => 'بورسلان'])));
});

it('drops the button of a slide whose category is hidden', function () {
    $hidden = Category::factory()->inactive()->create();
    Slide::factory()->linkedTo($hidden)->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page->where('slides.0.button', null));
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
