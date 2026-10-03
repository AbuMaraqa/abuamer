<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('lists active products from visible categories only', function () {
        $categories = createCategoryTree(['Porcelain' => [], 'Wall Tiles' => [], 'Hidden' => []]);
        $categories['Hidden']->update(['status' => false]);
        $visible = Product::factory()->for($categories['Porcelain'])->create();
        Product::factory()->for($categories['Porcelain'])->inactive()->create();
        Product::factory()->for($categories['Hidden'])->create();

        $response = $this->get(route('products.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Index')
            ->has('products.data', 1)
            ->where('products.data.0.id', $visible->id)
            ->where('categories', fn ($categories) => collect($categories)->pluck('name')->all() === ['Porcelain', 'Wall Tiles']));
    });

    it('presents the children of a single root category as the collections', function () {
        createCategoryTree(['Tiles' => ['Porcelain' => [], 'Wall Tiles' => []]]);

        $response = $this->get(route('products.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('categories', fn ($categories) => collect($categories)->pluck('name')->all() === ['Porcelain', 'Wall Tiles']));
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

    it('shows a product without Hebrew texts in English on Hebrew pages', function () {
        $this->useRoutingLocale('he');
        $product = Product::factory()->named('Calacatta Gold', 'كالاكاتا ذهبي')->create();

        $response = $this->get(route('products.show', ['slug' => 'calacatta-gold']));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Product')
            ->where('product.id', $product->id)
            ->where('product.name', 'Calacatta Gold'));
    });

    it('redirects a Hebrew page addressed with another slug to the slug the product is shown with', function (bool $hasHebrew, string $expectedSlug) {
        $this->useRoutingLocale('he');
        $factory = Product::factory()->named('Calacatta Gold', 'كالاكاتا ذهبي');
        ($hasHebrew ? $factory->withHebrew('קלקטה זהב') : $factory)->create();

        $response = $this->get(route('products.show', ['slug' => 'كالاكاتا-ذهبي']));

        $response->assertRedirect(route('products.show', ['slug' => $expectedSlug]))
            ->assertStatus(301);
    })->with([
        'with a Hebrew translation' => [true, 'קלקטה-זהב'],
        'without a Hebrew translation' => [false, 'calacatta-gold'],
    ]);

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

describe('brands', function () {
    it('filters the catalog by brand and offers only the brands with products in it', function () {
        $categories = createCategoryTree(['Mixers' => [], 'Hidden' => []]);
        $categories['Hidden']->update(['status' => false]);
        $aquaro = Brand::factory()->named('Aquaro')->create();
        $nordbad = Brand::factory()->named('Nordbad')->create();
        Brand::factory()->named('Velaria')->create();
        $hiddenOnly = Brand::factory()->named('Arvento')->create();
        $match = Product::factory()->for($aquaro)->for($categories['Mixers'])->create();
        Product::factory()->for($nordbad)->for($categories['Mixers'])->create();
        Product::factory()->for($hiddenOnly)->for($categories['Hidden'])->create();

        $response = $this->get(route('products.index', ['brand' => 'aquaro']));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('brands', fn ($brands) => collect($brands)->pluck('slug')->all() === ['aquaro', 'nordbad'])
            ->where('filters.brand', 'aquaro')
            ->where('seo.robots', fn (string $robots): bool => str_contains($robots, 'noindex'))
            ->has('products.data', 1)
            ->where('products.data.0.id', $match->id)
            ->where('products.data.0.brand.name', 'Aquaro'));
    });

    it('ignores an unknown brand in the filter', function () {
        Product::factory()->count(2)->create();

        $response = $this->get(route('products.index', ['brand' => 'unknown']));

        $response->assertInertia(fn (Assert $page) => $page->where('filters.brand', null)->has('products.data', 2));
    });

    it('filters a category page by brand', function () {
        $categories = createCategoryTree(['Sanitary Ware' => ['Mixers' => [], 'Basins' => []]]);
        $brand = Brand::factory()->named('Aquaro')->create();
        $mixer = Product::factory()->for($brand)->for($categories['Mixers'])->create();
        Product::factory()->for($categories['Basins'])->create();

        $response = $this->get(route('products.category', ['path' => 'sanitary-ware', 'brand' => 'aquaro']));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Category')
            ->has('brands', 1)
            ->has('products.data', 1)
            ->where('products.data.0.id', $mixer->id));
    });

    it('shows the brand and the downloadable documents on the product page', function () {
        Storage::fake('public');
        $product = Product::factory()->for(Brand::factory()->named('Aquaro'))->named('Linea Basin Mixer')->create();
        $product->addMedia(UploadedFile::fake()->createWithContent('Data sheet.pdf', "%PDF-1.4\n%%EOF\n"))->toMediaCollection(Product::DOCUMENTS_COLLECTION);

        $response = $this->get(route('products.show', ['slug' => $product->translate('ar')->slug]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('product.brand.name', 'Aquaro')
            ->where('product.brand.url', route('brands.show', ['brand' => 'aquaro']))
            ->has('product.documents', 1)
            ->where('product.documents.0.name', 'Data sheet')
            ->where('product.documents.0.extension', 'PDF'));
    });

    it('does not name a hidden brand on its products', function () {
        $product = Product::factory()->for(Brand::factory()->inactive())->create();

        $response = $this->get(route('products.show', ['slug' => $product->translate('ar')->slug]));

        $response->assertInertia(fn (Assert $page) => $page->where('product.brand', null));
    });
});
