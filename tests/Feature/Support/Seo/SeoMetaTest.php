<?php

use App\Models\Category;
use App\Models\Product;
use App\Settings\SeoSettings;
use Inertia\Testing\AssertableInertia as Assert;

it('uses the site-wide SEO title and description on the home page', function () {
    app(SeoSettings::class)->fill([
        'meta_title' => ['ar' => 'نُسق | بلاط فاخر', 'en' => 'Nasaq | Premium tiles'],
        'meta_description' => ['ar' => 'بلاط وبورسلان فاخر.', 'en' => 'Premium tiles.'],
    ])->save();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('seo.title', 'نُسق | بلاط فاخر')
        ->where('seo.description', 'بلاط وبورسلان فاخر.')
        ->where('seo.robots', 'index, follow')
        ->where('seo.structuredData.0.@type', 'Organization'));
});

it('describes a product with its own texts, translated URLs and structured data', function () {
    $category = Category::factory()->named('Porcelain', 'بورسلان')->create();
    $product = Product::factory()->for($category)->named('Calacatta Gold', 'كالاكاتا ذهبي')->create();
    $product->update(['ar' => ['seo_title' => 'كالاكاتا الذهبي الفاخر', 'seo_description' => 'وصف مخصص لمحركات البحث.']]);

    $response = $this->get(route('products.show', ['slug' => 'كالاكاتا-ذهبي']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('seo.title', fn (string $title) => str_starts_with($title, 'كالاكاتا الذهبي الفاخر | '))
        ->where('seo.description', 'وصف مخصص لمحركات البحث.')
        ->where('seo.type', 'product')
        ->where('seo.canonical', route('products.show', ['slug' => 'كالاكاتا-ذهبي']))
        ->where('seo.alternates.en', url('en/product/calacatta-gold'))
        ->where('seo.defaultUrl', route('products.show', ['slug' => 'كالاكاتا-ذهبي']))
        ->where('seo.structuredData.0.@type', 'Product')
        ->where('seo.structuredData.0.sku', $product->sku)
        ->where('seo.structuredData.1.@type', 'BreadcrumbList'));
});

it('points x-default to the default language from an English page', function () {
    $this->useRoutingLocale('en');
    Category::factory()->named('Porcelain', 'بورسلان')->create();

    $response = $this->get(route('products.category', ['path' => 'porcelain']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('seo.defaultUrl', url('ar/products/'.rawurlencode('بورسلان'))));
});

it('keeps searched and filtered listings out of search results', function () {
    $response = $this->get(route('products.index', ['q' => 'marble']));

    $response->assertInertia(fn (Assert $page) => $page->where('seo.robots', 'noindex, follow'));
});

it('includes the page number in the canonical URL of a paginated listing', function () {
    $response = $this->get(route('products.index', ['page' => 2, 'q' => 'x']));

    $response->assertInertia(fn (Assert $page) => $page->where('seo.canonical', route('products.index').'?page=2'));
});

it('keeps the control panel out of search results', function () {
    $response = $this->actingAs(admin())->get(route('admin.dashboard'));

    $response->assertInertia(fn (Assert $page) => $page->where('seo.robots', 'noindex, follow'));
});

it('renders the metadata in the document for crawlers', function () {
    $response = $this->get(route('about'));

    $response->assertSee('<link data-inertia="canonical" rel="canonical" href="'.route('about').'">', escape: false)
        ->assertSee('hreflang="x-default"', escape: false)
        ->assertSee('property="og:title"', escape: false);
});

it('escapes structured data so it cannot close the script tag', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['sku' => 'NSQ-1']);
    $product->update(['ar' => ['short_description' => '</script><script>alert(1)</script>']]);

    $response = $this->get(route('products.show', ['slug' => $product->translate('ar')->slug]));

    $response->assertDontSee('</script><script>alert(1)', escape: false);
});
