<?php

use App\Enums\Permission;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function brandPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'name' => 'Villeroy & Boch',
        'slug' => '',
        'country' => 'de',
        'website' => 'https://www.example.com',
        'status' => true,
        'ar' => ['description' => 'شركة ألمانية للأدوات الصحية.'],
        'en' => ['description' => 'German sanitary ware maker.'],
    ], $overrides);
}

beforeEach(fn () => Storage::fake('public'));

it('lists the brands in order with their product counts', function () {
    $second = Brand::factory()->create(['sort_order' => 2]);
    $first = Brand::factory()->create(['sort_order' => 1]);
    Product::factory()->for($first)->count(2)->create();

    $response = $this->actingAs(admin())->get(route('admin.brands.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Brands/Index')
        ->where('brands.0.id', $first->id)
        ->where('brands.0.products_count', 2)
        ->where('brands.1.id', $second->id));
});

it('adds a brand with a derived slug, its logo and descriptions after the existing brands', function () {
    Brand::factory()->create(['sort_order' => 3]);

    $response = $this->actingAs(admin())->post(route('admin.brands.store'), brandPayload([
        'logo' => UploadedFile::fake()->image('logo.png', 600, 200),
    ]));

    $response->assertRedirect(route('admin.brands.index'));
    $brand = Brand::query()->where('name', 'Villeroy & Boch')->sole();
    expect($brand->slug)->toBe('villeroy-boch')
        ->and($brand->country)->toBe('DE')
        ->and($brand->sort_order)->toBe(4)
        ->and($brand->translate('ar')->description)->toBe('شركة ألمانية للأدوات الصحية.')
        ->and($brand->getFirstMedia(Brand::LOGO_COLLECTION)->file_name)->toBe('logo.png');
});

it('rejects a duplicate name, an invalid country and a website that is not a link', function () {
    Brand::factory()->named('Villeroy & Boch')->create();

    $response = $this->actingAs(admin())->post(route('admin.brands.store'), brandPayload(['country' => 'Germany', 'website' => 'javascript:alert(1)']));

    $response->assertSessionHasErrors(['name', 'slug', 'country', 'website']);
});

it('updates a brand and removes its logo when requested', function () {
    $brand = Brand::factory()->named('Aquaro')->create();
    $brand->addMedia(UploadedFile::fake()->image('old.png', 600, 200))->toMediaCollection(Brand::LOGO_COLLECTION);

    $response = $this->actingAs(admin())->put(route('admin.brands.update', $brand), brandPayload(['name' => 'Aquaro', 'slug' => 'aquaro', 'remove_logo' => true]));

    $response->assertSessionHasNoErrors();
    expect($brand->fresh()->getFirstMedia(Brand::LOGO_COLLECTION))->toBeNull()
        ->and($brand->fresh()->website)->toBe('https://www.example.com');
});

it('saves a new order and hides a brand', function () {
    $brands = Brand::factory()->count(2)->sequence(fn ($sequence) => ['sort_order' => $sequence->index + 1])->create();

    $this->actingAs(admin())->patch(route('admin.brands.reorder'), ['ids' => [$brands[1]->id, $brands[0]->id]]);
    $this->actingAs(admin())->patch(route('admin.brands.status', $brands[0]), ['status' => false]);

    expect(Brand::query()->ordered()->pluck('id')->all())->toBe([$brands[1]->id, $brands[0]->id])
        ->and($brands[0]->fresh()->status)->toBeFalse();
});

it('deletes a brand and keeps its products without a brand', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->for($brand)->create();

    $this->actingAs(admin())->delete(route('admin.brands.destroy', $brand));

    $this->assertModelMissing($brand);
    expect($product->fresh()->brand_id)->toBeNull();
});

it('lets editors manage brands and forbids users without the permission', function () {
    $this->actingAs(userWithPermissions(Permission::BrandsManage))->get(route('admin.brands.index'))->assertOk();
    $this->actingAs(userWithPermissions(Permission::ProductsView))->get(route('admin.brands.index'))->assertForbidden();
    $this->actingAs(userWithPermissions(Permission::ProductsView))->post(route('admin.brands.store'), brandPayload())->assertForbidden();
});
