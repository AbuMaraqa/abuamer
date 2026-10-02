<?php

use App\Enums\Permission;
use App\Enums\SlideLinkType;
use App\Models\Category;
use App\Models\Slide;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function slidePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'status' => true,
        'link_type' => 'none',
        'category_id' => null,
        'ar' => ['eyebrow' => 'مجموعة 2026', 'title' => 'رخام كالاكاتا', 'text' => 'نص الشريحة', 'button_label' => '', 'button_url' => ''],
        'en' => ['eyebrow' => '2026 Collection', 'title' => 'Calacatta marble', 'text' => 'Slide text', 'button_label' => '', 'button_url' => ''],
        'image' => UploadedFile::fake()->image('slide.jpg', 1920, 1080),
    ], $overrides);
}

beforeEach(fn () => Storage::fake('public'));

it('lists the slides in their order', function () {
    $second = Slide::factory()->create(['sort_order' => 2]);
    $first = Slide::factory()->create(['sort_order' => 1]);

    $response = $this->actingAs(admin())->get(route('admin.slides.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Slides/Index')
        ->where('slides.0.id', $first->id)
        ->where('slides.1.id', $second->id));
});

it('adds a slide linked to a category after the existing slides', function () {
    Slide::factory()->create(['sort_order' => 4]);
    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->post(route('admin.slides.store'), slidePayload([
        'link_type' => 'category',
        'category_id' => $category->id,
        'ar' => ['button_label' => 'اكتشف'],
        'en' => ['button_label' => 'Discover'],
    ]));

    $response->assertRedirect(route('admin.slides.index'));
    $slide = Slide::query()->latest('id')->first();
    expect($slide->link_type)->toBe(SlideLinkType::Category)
        ->and($slide->category_id)->toBe($category->id)
        ->and($slide->sort_order)->toBe(5)
        ->and($slide->translate('en')->title)->toBe('Calacatta marble')
        ->and($slide->getFirstMedia(Slide::IMAGE_COLLECTION)->file_name)->toBe('slide.jpg');
});

it('requires an image for a new slide', function () {
    $response = $this->actingAs(admin())->post(route('admin.slides.store'), slidePayload(['image' => null]));

    $response->assertSessionHasErrors('image');
    expect(Slide::count())->toBe(0);
});

it('requires a category and a button text when linking to a category', function () {
    $response = $this->actingAs(admin())->post(route('admin.slides.store'), slidePayload(['link_type' => 'category']));

    $response->assertSessionHasErrors(['category_id', 'ar.button_label', 'en.button_label']);
});

it('requires the Hebrew button text only when the slide has a Hebrew title', function (string $hebrewTitle, bool $isRequired) {
    $response = $this->actingAs(admin())->post(route('admin.slides.store'), slidePayload([
        'link_type' => 'category',
        'category_id' => Category::factory()->create()->id,
        'ar' => ['button_label' => 'اكتشف'],
        'en' => ['button_label' => 'Discover'],
        'he' => ['title' => $hebrewTitle, 'button_label' => ''],
    ]));

    $isRequired ? $response->assertSessionHasErrors('he.button_label') : $response->assertSessionHasNoErrors();
})->with([
    'Hebrew title' => ['שיש קלקטה', true],
    'no Hebrew' => ['', false],
]);

it('refuses custom links that are not https or a path of this website', function (string $url) {
    $response = $this->actingAs(admin())->post(route('admin.slides.store'), slidePayload([
        'link_type' => 'custom',
        'ar' => ['button_label' => 'اقرأ', 'button_url' => $url],
        'en' => ['button_label' => 'Read', 'button_url' => '/en/about'],
    ]));

    $response->assertSessionHasErrors('ar.button_url');
})->with([
    'javascript scheme' => ['javascript:alert(1)'],
    'insecure link' => ['http://example.com'],
    'protocol-relative link' => ['//evil.example'],
]);

it('keeps the image when a slide is updated without a new one', function () {
    $slide = Slide::factory()->create();
    $slide->addMedia(UploadedFile::fake()->image('old.jpg', 1920, 1080))->toMediaCollection(Slide::IMAGE_COLLECTION);

    $response = $this->actingAs(admin())->put(route('admin.slides.update', $slide), slidePayload([
        'image' => null,
        'en' => ['title' => 'Porcelain without limits'],
    ]));

    $response->assertSessionHasNoErrors();
    expect($slide->fresh()->translate('en')->title)->toBe('Porcelain without limits')
        ->and($slide->fresh()->getFirstMedia(Slide::IMAGE_COLLECTION)->file_name)->toBe('old.jpg');
});

it('saves a new order', function () {
    $slides = Slide::factory()->count(3)->sequence(fn ($sequence) => ['sort_order' => $sequence->index + 1])->create();

    $this->actingAs(admin())->patch(route('admin.slides.reorder'), ['ids' => [$slides[2]->id, $slides[0]->id, $slides[1]->id]]);

    expect(Slide::query()->ordered()->pluck('id')->all())->toBe([$slides[2]->id, $slides[0]->id, $slides[1]->id]);
});

it('hides a slide', function () {
    $slide = Slide::factory()->create();

    $this->actingAs(admin())->patch(route('admin.slides.status', $slide), ['status' => false]);

    expect($slide->fresh()->status)->toBeFalse();
});

it('deletes a slide with its images', function () {
    $slide = Slide::factory()->create();
    $media = $slide->addMedia(UploadedFile::fake()->image('slide.jpg', 1920, 1080))->toMediaCollection(Slide::IMAGE_COLLECTION);

    $this->actingAs(admin())->delete(route('admin.slides.destroy', $slide));

    $this->assertModelMissing($slide);
    $this->assertModelMissing($media);
});

it('forbids users who cannot manage the website content', function () {
    $response = $this->actingAs(userWithPermissions(Permission::ProductsView))->post(route('admin.slides.store'), slidePayload());

    $response->assertForbidden();
    expect(Slide::count())->toBe(0);
});
