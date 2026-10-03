<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use App\Services\Catalog\CategoryTreeService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\SanitaryWareSeeder;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    // Image conversions of the generated demo photos are slow and not under test here.
    config(['media-library.queue_conversions_by_default' => true]);
    Queue::fake();
});

it('seeds the tiles and the sanitary ware departments, with brands, in every language', function () {
    $this->seed([CatalogSeeder::class, SanitaryWareSeeder::class]);

    $departments = app(CategoryTreeService::class)->tree()->collections();
    $mixer = Product::query()->whereTranslation('name', 'Aquaro Linea Basin Mixer', 'en')->with(['translations', 'specifications.translations'])->sole();

    expect($departments->map->translate('en')->pluck('name')->all())->toBe(['Tiles & Marble', 'Sanitary Ware'])
        ->and($departments->every(fn (Category $department): bool => $department->getFirstMedia(Category::IMAGE_COLLECTION) !== null))->toBeTrue()
        ->and(Brand::count())->toBe(5)
        ->and(Product::query()->whereNotNull('brand_id')->count())->toBe(18)
        ->and(Slide::query()->sole()->category_id)->toBe($departments->last()->id)
        ->and($mixer->translate('ar')->name)->toBe('خلاط مغسلة أكوارو لينيا')
        ->and($mixer->translate('he')->name)->toBe('ברז כיור אקוורו ליניאה')
        ->and($mixer->specifications->last()->translate('ar')->only(['label', 'value']))->toBe(['label' => 'بلد المنشأ', 'value' => 'إيطاليا']);
});

it('adds the sanitary ware department to an existing catalog only once', function () {
    $this->seed(SanitaryWareSeeder::class);
    $this->seed(SanitaryWareSeeder::class);

    expect(Category::query()->whereTranslation('name', 'Sanitary Ware', 'en')->count())->toBe(1)
        ->and(Brand::count())->toBe(5)
        ->and(Slide::count())->toBe(1);
});
