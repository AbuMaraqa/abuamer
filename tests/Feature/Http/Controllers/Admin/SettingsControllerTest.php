<?php

use App\Enums\Role;
use App\Enums\SiteFont;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Company;
use App\Models\User;
use App\Settings\ContactSettings;
use App\Settings\SiteSettings;
use App\Settings\SocialSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function settingsPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'contact' => [
            'phone' => '+966 11 000 0000',
            'mobile' => '',
            'whatsapp' => '966500000000',
            'email' => 'info@nasaq.test',
            'address' => ['ar' => 'الرياض', 'en' => 'Riyadh'],
            'working_hours' => ['ar' => '', 'en' => ''],
            'map_url' => '',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=abc',
            'form_recipient' => 'sales@nasaq.test',
        ],
        'social' => ['facebook' => '', 'instagram' => 'https://instagram.com/nasaq', 'youtube' => '', 'tiktok' => '', 'linkedin' => '', 'x' => ''],
        'seo' => [
            'meta_title' => ['ar' => 'نُسق', 'en' => 'Nasaq'],
            'meta_description' => ['ar' => '', 'en' => ''],
            'meta_keywords' => ['ar' => '', 'en' => ''],
        ],
        'site' => ['default_locale' => 'en', 'maintenance_mode' => false, 'font' => 'tajawal', 'show_name_with_logo' => true],
    ], $overrides);
}

it('saves every settings group, keeping empty fields as empty text', function () {
    $response = $this->actingAs(admin())->put(route('admin.settings.update'), settingsPayload());

    $response->assertRedirect(route('admin.settings.edit'));
    $contact = app(ContactSettings::class);
    expect($contact->whatsapp)->toBe('966500000000')
        ->and($contact->mobile)->toBe('')
        ->and($contact->address)->toBe(['ar' => 'الرياض', 'en' => 'Riyadh'])
        ->and(app(SocialSettings::class)->links())->toBe(['instagram' => 'https://instagram.com/nasaq'])
        ->and(app(SiteSettings::class)->default_locale)->toBe('en')
        ->and(app(SiteSettings::class)->font())->toBe(SiteFont::Tajawal);
});

it('shows the company name next to the logo until it is turned off', function () {
    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->where('site.showNameWithLogo', true));

    $response = $this->actingAs(admin())->put(route('admin.settings.update'), settingsPayload([
        'site' => ['show_name_with_logo' => false],
    ]));

    $response->assertSessionHasNoErrors();
    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->where('site.showNameWithLogo', false));
});

it('uses the chosen font on the website', function () {
    app(SiteSettings::class)->fill(['font' => SiteFont::Tajawal->value])->save();

    $response = $this->get(route('home'));

    $response->assertSee('style="--font-site: var(--font-tajawal)"', escape: false);
});

it('rejects invalid contact values', function (array $override, string $field) {
    $response = $this->actingAs(admin())->put(route('admin.settings.update'), settingsPayload($override));

    $response->assertSessionHasErrors($field);
})->with([
    'WhatsApp with a plus sign' => [['contact' => ['whatsapp' => '+966 50 000 0000']], 'contact.whatsapp'],
    'map embed from another site' => [['contact' => ['map_embed_url' => 'https://evil.example/embed']], 'contact.map_embed_url'],
    'insecure social link' => [['social' => ['facebook' => 'http://facebook.com/nasaq']], 'social.facebook'],
    'unsupported language' => [['site' => ['default_locale' => 'fr']], 'site.default_locale'],
    'unknown font' => [['site' => ['font' => 'comic-sans']], 'site.font'],
]);

it('stores the logo, the light logo and the browser icon', function () {
    Storage::fake('public');

    // Sent the way the browser form sends it: multipart POST with a spoofed PUT method.
    $response = $this->actingAs(admin())->post(route('admin.settings.update'), settingsPayload([
        '_method' => 'PUT',
        'logo' => UploadedFile::fake()->image('logo.png', 400, 120),
        'logo_light' => UploadedFile::fake()->image('logo-white.png', 400, 120),
        'favicon' => UploadedFile::fake()->image('icon.png', 64, 64),
    ]));

    $response->assertSessionHasNoErrors();

    $company = Company::current();
    expect($company->getFirstMedia(Company::LOGO_COLLECTION)->file_name)->toBe('logo.png')
        ->and($company->getFirstMedia(Company::LOGO_LIGHT_COLLECTION)->file_name)->toBe('logo-white.png')
        ->and($company->getFirstMedia(Company::FAVICON_COLLECTION)->file_name)->toBe('icon.png');
});

it('refuses an SVG logo', function () {
    Storage::fake('public');
    $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

    $response = $this->actingAs(admin())->put(route('admin.settings.update'), settingsPayload(['logo' => $svg]));

    $response->assertSessionHasErrors('logo');
    expect(Company::current()->getFirstMedia(Company::LOGO_COLLECTION))->toBeNull();
});

it('refuses a browser icon that is not square', function () {
    Storage::fake('public');

    $response = $this->actingAs(admin())->put(route('admin.settings.update'), settingsPayload([
        'favicon' => UploadedFile::fake()->image('icon.png', 120, 60),
    ]));

    $response->assertSessionHasErrors('favicon');
});

it('removes the logo when requested', function () {
    Storage::fake('public');
    Company::current()->addMedia(UploadedFile::fake()->image('logo.png', 400, 120))->toMediaCollection(Company::LOGO_COLLECTION);

    $this->actingAs(admin())->put(route('admin.settings.update'), settingsPayload(['remove_logo' => true]));

    expect(Company::current()->getFirstMedia(Company::LOGO_COLLECTION))->toBeNull();
});

it('resends the site identity after saving, although the browser already has it', function () {
    Storage::fake('public');
    $admin = admin();

    $response = $this->actingAs($admin)
        ->followingRedirects()
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()),
            'X-Inertia-Except-Once-Props' => 'site',
        ])
        ->put(route('admin.settings.update'), settingsPayload([
            'logo' => UploadedFile::fake()->image('logo.png', 400, 120),
        ]));

    expect($response->json('props.site.logo'))->toEndWith('logo.png');
});

it('forbids editors from changing settings', function () {
    test()->seed(RolesAndPermissionsSeeder::class);
    $editor = User::factory()->create()->assignRole(Role::Editor);

    $response = $this->actingAs($editor)->put(route('admin.settings.update'), settingsPayload());

    $response->assertForbidden();
});
