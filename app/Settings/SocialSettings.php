<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialSettings extends Settings
{
    public string $facebook;

    public string $instagram;

    public string $youtube;

    public string $tiktok;

    public string $linkedin;

    public string $x;

    public static function group(): string
    {
        return 'social';
    }

    /**
     * The configured profile links, keyed by network.
     *
     * @return array<string, string>
     */
    public function links(): array
    {
        return array_filter([
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'tiktok' => $this->tiktok,
            'linkedin' => $this->linkedin,
            'x' => $this->x,
        ]);
    }
}
