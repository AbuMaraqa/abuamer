<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function productPayload(Category $category, array $overrides = []): array
{
    // Lists replace the defaults entirely instead of being merged by index.
    return [...array_replace_recursive([
        'category_id' => $category->id,
        'sku' => 'nsq-cg-60120',
        'status' => true,
        'featured' => false,
        'sort_order' => 0,
        'ar' => ['name' => 'كالاكاتا ذهبي 60×120', 'slug' => '', 'short_description' => 'بلاط فاخر'],
        'en' => ['name' => 'Calacatta Gold 60x120', 'slug' => '', 'short_description' => 'Premium tile'],
        'specifications' => [
            ['id' => null, 'ar' => ['label' => 'المقاس', 'value' => '60 × 120 سم'], 'en' => ['label' => 'Size', 'value' => '60 × 120 cm']],
            ['id' => null, 'ar' => ['label' => 'السماكة', 'value' => '9 مم'], 'en' => ['label' => 'Thickness', 'value' => '9 mm']],
        ],
    ], $overrides), ...Arr::only($overrides, ['specifications', 'gallery', 'gallery_uploads'])];
}

describe('index', function () {
    it('lists products newest first with pagination', function () {
        $older = Product::factory()->create();
        $newer = Product::factory()->create();

        $response = $this->actingAs(admin())->get(route('admin.products.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Products/Index')
            ->where('products.data.0.id', $newer->id)
            ->where('products.data.1.id', $older->id)
            ->where('products.meta.total', 2));
    });

    it('filters by category including or excluding subcategories', function (bool $withDescendants, int $expectedCount) {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);
        Product::factory()->for($categories['Porcelain'])->create();
        Product::factory()->for($categories['Marble Effect'])->create();

        $response = $this->actingAs(admin())->get(route('admin.products.index', [
            'category' => $categories['Porcelain']->id,
            'descendants' => $withDescendants ? 1 : 0,
        ]));

        $response->assertInertia(fn (Assert $page) => $page->where('products.meta.total', $expectedCount));
    })->with([
        'with subcategories' => [true, 2],
        'direct only' => [false, 1],
    ]);

    it('filters by status and featured flag', function () {
        $hiddenFeatured = Product::factory()->inactive()->featured()->create();
        Product::factory()->inactive()->create();
        Product::factory()->featured()->create();

        $response = $this->actingAs(admin())->get(route('admin.products.index', ['status' => 'inactive', 'featured' => 'yes']));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.meta.total', 1)
            ->where('products.data.0.id', $hiddenFeatured->id));
    });

    it('searches by SKU', function () {
        $match = Product::factory()->create(['sku' => 'NSQ-SLATE-01']);
        Product::factory()->create(['sku' => 'NSQ-OAK-02']);

        $response = $this->actingAs(admin())->get(route('admin.products.index', ['q' => 'slate']));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('products.meta.total', 1)
            ->where('products.data.0.id', $match->id));
    });

    it('forbids users who cannot view products', function () {
        $response = $this->actingAs(userWithPermissions(Permission::CategoriesView))->get(route('admin.products.index'));

        $response->assertForbidden();
    });
});

describe('store', function () {
    it('creates a product with translations and ordered specifications', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs(admin())->post(route('admin.products.store'), productPayload($category));

        $product = Product::sole();
        $response->assertRedirect(route('admin.products.edit', $product));
        expect($product->category_id)->toBe($category->id)
            ->and($product->sku)->toBe('NSQ-CG-60120')
            ->and($product->translate('ar')->slug)->toBe('كالاكاتا-ذهبي-60x120')
            ->and($product->translate('en')->slug)->toBe('calacatta-gold-60x120')
            ->and($product->specifications()->with('translations')->get()->map(fn ($specification) => $specification->translate('en')->label)->all())->toBe(['Size', 'Thickness'])
            ->and($product->specifications()->with('translations')->first()->translate('ar')->value)->toBe('60 × 120 سم');
    });

    it('stores the main image and gallery images in their collections', function () {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs(admin())->post(route('admin.products.store'), productPayload($category, [
            'main_image' => UploadedFile::fake()->image('main.jpg', 1200, 1200),
            'gallery_uploads' => [
                UploadedFile::fake()->image('room-1.jpg', 1200, 800),
                UploadedFile::fake()->image('room-2.jpg', 1200, 800),
            ],
        ]));

        $product = Product::sole();
        expect($product->getFirstMedia(Product::MAIN_IMAGE_COLLECTION)->file_name)->toBe('main.jpg')
            ->and($product->getMedia(Product::GALLERY_COLLECTION)->pluck('file_name')->all())->toBe(['room-1.jpg', 'room-2.jpg']);
    });

    it('rejects a slug already used by another product in the same language', function () {
        Product::factory()->named('Calacatta Gold 60x120')->create();

        $response = $this->actingAs(admin())->post(route('admin.products.store'), productPayload(Category::factory()->create()));

        $response->assertSessionHasErrors('en.slug');
        expect(Product::count())->toBe(1);
    });

    it('rejects a duplicate SKU', function () {
        Product::factory()->create(['sku' => 'NSQ-CG-60120']);

        $response = $this->actingAs(admin())->post(route('admin.products.store'), productPayload(Category::factory()->create()));

        $response->assertSessionHasErrors('sku');
    });

    it('requires a category and names in both languages', function () {
        $response = $this->actingAs(admin())->post(route('admin.products.store'), []);

        $response->assertSessionHasErrors(['category_id', 'ar.name', 'en.name', 'status', 'featured']);
    });

    it('requires both the label and the value of every specification', function () {
        $response = $this->actingAs(admin())->post(route('admin.products.store'), productPayload(Category::factory()->create(), [
            'specifications' => [['id' => null, 'ar' => ['label' => 'المقاس', 'value' => '60 × 120 سم'], 'en' => ['label' => 'Size', 'value' => '']]],
        ]));

        $response->assertSessionHasErrors(['specifications.0.en.value']);
    });

    it('rejects an image that is too small', function () {
        Storage::fake('public');

        $response = $this->actingAs(admin())->post(route('admin.products.store'), productPayload(Category::factory()->create(), [
            'main_image' => UploadedFile::fake()->image('tiny.jpg', 100, 100),
        ]));

        $response->assertSessionHasErrors('main_image');
    });

    it('forbids users who cannot create products', function () {
        $response = $this->actingAs(userWithPermissions(Permission::ProductsView))
            ->post(route('admin.products.store'), productPayload(Category::factory()->create()));

        $response->assertForbidden();
        expect(Product::count())->toBe(0);
    });
});

describe('update', function () {
    it('updates, adds, reorders and removes specifications', function () {
        $product = Product::factory()->create();
        $size = ProductSpecification::factory()->for($product)->create(['sort_order' => 1]);
        $removed = ProductSpecification::factory()->for($product)->create(['sort_order' => 2]);

        $this->actingAs(admin())->put(route('admin.products.update', $product), productPayload($product->category, [
            'specifications' => [
                ['id' => null, 'ar' => ['label' => 'التشطيب', 'value' => 'مطفي'], 'en' => ['label' => 'Finish', 'value' => 'Matte']],
                ['id' => $size->id, 'ar' => ['label' => 'المقاس', 'value' => '120 × 120 سم'], 'en' => ['label' => 'Size', 'value' => '120 × 120 cm']],
            ],
        ]));

        $specifications = $product->specifications()->with('translations')->get();
        expect($specifications->map(fn ($specification) => $specification->translate('en')->label)->all())->toBe(['Finish', 'Size'])
            ->and($specifications->last()->id)->toBe($size->id)
            ->and($specifications->last()->translate('en')->value)->toBe('120 × 120 cm');
        $this->assertModelMissing($removed);
    });

    it('does not take over a specification that belongs to another product', function () {
        $product = Product::factory()->create();
        $foreign = ProductSpecification::factory()->create();

        $this->actingAs(admin())->put(route('admin.products.update', $product), productPayload($product->category, [
            'specifications' => [['id' => $foreign->id, 'ar' => ['label' => 'اللون', 'value' => 'بيج'], 'en' => ['label' => 'Color', 'value' => 'Beige']]],
        ]));

        expect($foreign->fresh()->product_id)->toBe($foreign->product_id)
            ->and($foreign->fresh()->translate('en')->label)->toBe('Size')
            ->and($product->specifications()->with('translations')->sole()->translate('en')->label)->toBe('Color');
    });

    it('keeps, reorders, removes and appends gallery images', function () {
        Storage::fake('public');
        $product = Product::factory()->create();
        $first = $product->addMedia(UploadedFile::fake()->image('first.jpg', 800, 800))->toMediaCollection(Product::GALLERY_COLLECTION);
        $second = $product->addMedia(UploadedFile::fake()->image('second.jpg', 800, 800))->toMediaCollection(Product::GALLERY_COLLECTION);
        $removed = $product->addMedia(UploadedFile::fake()->image('removed.jpg', 800, 800))->toMediaCollection(Product::GALLERY_COLLECTION);

        $this->actingAs(admin())->put(route('admin.products.update', $product), productPayload($product->category, [
            'gallery' => [$second->id, $first->id],
            'gallery_uploads' => [UploadedFile::fake()->image('new.jpg', 800, 800)],
        ]));

        expect($product->fresh()->getMedia(Product::GALLERY_COLLECTION)->pluck('file_name')->all())->toBe(['second.jpg', 'first.jpg', 'new.jpg']);
        $this->assertModelMissing($removed);
    });

    it('ignores gallery images that belong to another product', function () {
        Storage::fake('public');
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        $foreign = $other->addMedia(UploadedFile::fake()->image('foreign.jpg', 800, 800))->toMediaCollection(Product::GALLERY_COLLECTION);

        $this->actingAs(admin())->put(route('admin.products.update', $product), productPayload($product->category, ['gallery' => [$foreign->id]]));

        $this->assertModelExists($foreign);
        expect($product->fresh()->getMedia(Product::GALLERY_COLLECTION))->toBeEmpty();
    });

    it('removes the main image when requested', function () {
        Storage::fake('public');
        $product = Product::factory()->create();
        $product->addMedia(UploadedFile::fake()->image('main.jpg', 800, 800))->toMediaCollection(Product::MAIN_IMAGE_COLLECTION);

        $this->actingAs(admin())->put(route('admin.products.update', $product), productPayload($product->category, ['remove_main_image' => true]));

        expect($product->fresh()->getFirstMedia(Product::MAIN_IMAGE_COLLECTION))->toBeNull();
    });

    it('keeps its own slugs and SKU without reporting a clash', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->named('Calacatta Gold 60x120', 'كالاكاتا ذهبي 60×120')->create(['sku' => 'NSQ-CG-60120']);

        $response = $this->actingAs(admin())->put(route('admin.products.update', $product), productPayload($category));

        $response->assertSessionHasNoErrors();
    });
});

describe('destroy', function () {
    it('deletes the product with its specifications and images', function () {
        Storage::fake('public');
        $product = Product::factory()->create();
        $specification = ProductSpecification::factory()->for($product)->create();
        $media = $product->addMedia(UploadedFile::fake()->image('main.jpg', 800, 800))->toMediaCollection(Product::MAIN_IMAGE_COLLECTION);

        $response = $this->actingAs(admin())->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertModelMissing($product);
        $this->assertModelMissing($specification);
        $this->assertModelMissing($media);
        Storage::disk('public')->assertMissing($media->getPathRelativeToRoot());
    });

    it('forbids users who cannot delete products', function () {
        $product = Product::factory()->create();

        $response = $this->actingAs(userWithPermissions(Permission::ProductsUpdate))->delete(route('admin.products.destroy', $product));

        $response->assertForbidden();
        $this->assertModelExists($product);
    });
});
