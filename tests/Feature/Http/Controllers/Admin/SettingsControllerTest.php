<?php

use App\Enums\Role;
use App\Enums\SiteFont;
use App\Models\User;
use App\Settings\ContactSettings;
use App\Settings\SiteSettings;
use App\Settings\SocialSettings;
use Database\Seeders\RolesAndPermissionsSeeder;

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
        'site' => ['default_locale' => 'en', 'maintenance_mode' => false, 'font' => 'tajawal'],
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

it('forbids editors from changing settings', function () {
    test()->seed(RolesAndPermissionsSeeder::class);
    $editor = User::factory()->create()->assignRole(Role::Editor);

    $response = $this->actingAs($editor)->put(route('admin.settings.update'), settingsPayload());

    $response->assertForbidden();
});
