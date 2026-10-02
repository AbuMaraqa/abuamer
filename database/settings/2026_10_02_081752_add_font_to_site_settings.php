<?php

use App\Enums\SiteFont;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.font', SiteFont::IbmPlexSansArabic->value);
    }
};
