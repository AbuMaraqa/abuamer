<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Texts shown to visitors are stored per locale, e.g. ['ar' => '…', 'en' => '…'].
     */
    public function up(): void
    {
        $empty = ['ar' => '', 'en' => ''];

        $this->migrator->add('contact.phone', '');
        $this->migrator->add('contact.mobile', '');
        $this->migrator->add('contact.whatsapp', '');
        $this->migrator->add('contact.email', '');
        $this->migrator->add('contact.address', $empty);
        $this->migrator->add('contact.working_hours', $empty);
        $this->migrator->add('contact.map_url', '');
        $this->migrator->add('contact.map_embed_url', '');
        $this->migrator->add('contact.form_recipient', '');

        $this->migrator->add('social.facebook', '');
        $this->migrator->add('social.instagram', '');
        $this->migrator->add('social.youtube', '');
        $this->migrator->add('social.tiktok', '');
        $this->migrator->add('social.linkedin', '');
        $this->migrator->add('social.x', '');

        $this->migrator->add('seo.meta_title', $empty);
        $this->migrator->add('seo.meta_description', $empty);
        $this->migrator->add('seo.meta_keywords', $empty);

        $this->migrator->add('site.default_locale', 'ar');
        $this->migrator->add('site.maintenance_mode', false);
    }
};
