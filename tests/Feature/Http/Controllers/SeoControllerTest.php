<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

it('lists every visible page in both languages with their alternates', function () {
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->create();
    Product::factory()->for($porcelain)->named('Calacatta Gold', 'كالاكاتا ذهبي')->create();

    $response = $this->get(route('sitemap'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<loc>'.url('ar/about').'</loc>', escape: false)
        ->assertSee('<loc>'.url('en/products/porcelain').'</loc>', escape: false)
        ->assertSee('<loc>'.url('en/product/calacatta-gold').'</loc>', escape: false)
        ->assertSee('hreflang="ar" href="'.url('ar/product/'.rawurlencode('كالاكاتا-ذهبي')).'"', escape: false);
});

it('lists the Hebrew pages with the slugs they are shown with', function () {
    $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->withHebrew('פורצלן')->create();
    Product::factory()->for($porcelain)->named('Calacatta Gold', 'كالاكاتا ذهبي')->create();

    $response = $this->get(route('sitemap'));

    $response->assertSee('<loc>'.url('he/products/'.rawurlencode('פורצלן')).'</loc>', escape: false)
        ->assertSee('<loc>'.url('he/product/calacatta-gold').'</loc>', escape: false)
        ->assertSee('hreflang="he" href="'.url('he/about').'"', escape: false);
});

it('leaves hidden categories and inactive products out of the sitemap', function () {
    $hidden = Category::factory()->named('Hidden Collection')->inactive()->create();
    Product::factory()->for($hidden)->named('Hidden Product')->create();
    Product::factory()->named('Inactive Product')->inactive()->create();

    $response = $this->get(route('sitemap'));

    $response->assertDontSee('hidden-collection')
        ->assertDontSee('hidden-product')
        ->assertDontSee('inactive-product');
});

it('disallows the control panel and points crawlers to the sitemap', function () {
    $response = $this->get(route('robots'));

    $response->assertOk()
        ->assertSee('Disallow: /ar/admin')
        ->assertSee('Disallow: /en/login')
        ->assertSee('Sitemap: '.route('sitemap'));
});

it('lists the brands page and the visible brand pages', function () {
    Brand::factory()->named('Aquaro')->create();
    Brand::factory()->named('Secret Brand')->inactive()->create();

    $response = $this->get(route('sitemap'));

    $response->assertSee('<loc>'.url('ar/brands').'</loc>', escape: false)
        ->assertSee('<loc>'.url('en/brands/aquaro').'</loc>', escape: false)
        ->assertDontSee('secret-brand');
});
