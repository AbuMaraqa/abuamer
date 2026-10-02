<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Texts stored per locale.
     */
    private const array LOCALIZED_SETTINGS = [
        'contact.address',
        'contact.working_hours',
        'seo.meta_title',
        'seo.meta_description',
        'seo.meta_keywords',
    ];

    /**
     * Add an empty Hebrew text, which falls back to English until it is written.
     */
    public function up(): void
    {
        foreach (self::LOCALIZED_SETTINGS as $property) {
            $this->migrator->update($property, fn (array|object $values): array => (array) $values + ['he' => '']);
        }
    }

    public function down(): void
    {
        foreach (self::LOCALIZED_SETTINGS as $property) {
            $this->migrator->update($property, fn (array|object $values): array => array_diff_key((array) $values, ['he' => true]));
        }
    }
};
