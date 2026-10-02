<?php

use App\Enums\HighlightType;
use App\Enums\Permission;
use App\Models\Company;
use App\Models\CompanyHighlight;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function companyPayload(array $overrides = []): array
{
    // Lists replace the defaults entirely instead of being merged by index.
    return [...array_replace_recursive([
        'founded_year' => 2008,
        'ar' => ['name' => 'نُسق للبلاط والسيراميك', 'tagline' => 'بلاط فاخر', 'story' => 'قصتنا'],
        'en' => ['name' => 'Nasaq Tiles & Ceramics', 'tagline' => 'Premium tiles', 'story' => 'Our story'],
        'values' => [],
        'features' => [],
        'statistics' => [],
    ], $overrides), ...Arr::only($overrides, ['values', 'features', 'statistics', 'gallery', 'gallery_uploads'])];
}

it('renders the company editor', function () {
    CompanyHighlight::factory()->feature('truck')->create();

    $response = $this->actingAs(admin())->get(route('admin.company.edit'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Company/Edit')
        ->where('company.features.0.icon', 'truck')
        ->where('featureIcons', CompanyHighlight::FEATURE_ICONS));
});

it('saves the company texts in both languages', function () {
    $response = $this->actingAs(admin())->put(route('admin.company.update'), companyPayload());

    $response->assertRedirect(route('admin.company.edit'));
    $company = Company::current();
    expect($company->founded_year)->toBe(2008)
        ->and($company->translate('ar')->name)->toBe('نُسق للبلاط والسيراميك')
        ->and($company->translate('en')->story)->toBe('Our story');
});

it('syncs each list of highlights in its submitted order', function () {
    $kept = CompanyHighlight::factory()->create(['sort_order' => 1]);
    $removed = CompanyHighlight::factory()->create(['sort_order' => 2]);
    $statistic = CompanyHighlight::factory()->statistic('10+')->create();

    $this->actingAs(admin())->put(route('admin.company.update'), companyPayload([
        'values' => [
            ['id' => null, 'ar' => ['title' => 'الابتكار'], 'en' => ['title' => 'Innovation']],
            ['id' => $kept->id, 'ar' => ['title' => 'الجودة'], 'en' => ['title' => 'Craftsmanship']],
        ],
        'statistics' => [
            ['id' => $statistic->id, 'value' => '25+', 'ar' => ['title' => 'عامًا'], 'en' => ['title' => 'Years']],
        ],
    ]));

    $values = CompanyHighlight::query()->where('type', HighlightType::Value)->with('translations')->ordered()->get();
    expect($values->map(fn (CompanyHighlight $value) => $value->translate('en')->title)->all())->toBe(['Innovation', 'Craftsmanship'])
        ->and($values->last()->id)->toBe($kept->id)
        ->and($statistic->fresh()->value)->toBe('25+');
    $this->assertModelMissing($removed);
});

it('does not turn a highlight of another list into a value', function () {
    $statistic = CompanyHighlight::factory()->statistic()->create();

    $this->actingAs(admin())->put(route('admin.company.update'), companyPayload([
        'values' => [['id' => $statistic->id, 'ar' => ['title' => 'الجودة'], 'en' => ['title' => 'Quality']]],
    ]));

    expect(CompanyHighlight::query()->where('type', HighlightType::Value)->count())->toBe(1);
    $this->assertModelMissing($statistic);
});

it('rejects an icon that is not offered', function () {
    $response = $this->actingAs(admin())->put(route('admin.company.update'), companyPayload([
        'features' => [['icon' => 'skull', 'ar' => ['title' => 'خدمة'], 'en' => ['title' => 'Service']]],
    ]));

    $response->assertSessionHasErrors('features.0.icon');
});

it('requires the statistic figure', function () {
    $response = $this->actingAs(admin())->put(route('admin.company.update'), companyPayload([
        'statistics' => [['ar' => ['title' => 'مشروع'], 'en' => ['title' => 'Projects']]],
    ]));

    $response->assertSessionHasErrors('statistics.0.value');
});

it('stores the hero image', function () {
    Storage::fake('public');

    $this->actingAs(admin())->put(route('admin.company.update'), companyPayload([
        'hero_image' => UploadedFile::fake()->image('hero.jpg', 1920, 1080),
    ]));

    expect(Company::current()->getFirstMedia(Company::HERO_COLLECTION)->file_name)->toBe('hero.jpg');
});

it('forbids users who cannot manage the company content', function () {
    $response = $this->actingAs(userWithPermissions(Permission::ProductsView))->put(route('admin.company.update'), companyPayload());

    $response->assertForbidden();
});
