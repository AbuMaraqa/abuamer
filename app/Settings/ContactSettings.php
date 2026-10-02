<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * How visitors reach the company. Visitor-facing texts are stored per locale.
 */
class ContactSettings extends Settings
{
    public string $phone;

    public string $mobile;

    /**
     * International format without "+" or spaces, as wa.me links expect (e.g. 9665XXXXXXXX).
     */
    public string $whatsapp;

    public string $email;

    /** @var array<string, string> */
    public array $address;

    /** @var array<string, string> */
    public array $working_hours;

    /**
     * A link that opens the location in Google Maps.
     */
    public string $map_url;

    /**
     * The "embed a map" iframe source from Google Maps.
     */
    public string $map_embed_url;

    /**
     * Where contact form messages are emailed; messages are always stored either way.
     */
    public string $form_recipient;

    public static function group(): string
    {
        return 'contact';
    }
}
