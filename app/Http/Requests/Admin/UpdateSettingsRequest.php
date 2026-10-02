<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use App\Enums\SiteFont;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permission::SettingsManage->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $phone = ['nullable', 'string', 'max:40', 'regex:/^\+?[0-9\s\-()]{6,}$/'];
        $httpsUrl = ['nullable', 'url:https', 'max:500'];

        $rules = [
            'contact.phone' => $phone,
            'contact.mobile' => $phone,
            'contact.whatsapp' => ['nullable', 'regex:/^[1-9][0-9]{7,14}$/'],
            'contact.email' => ['nullable', 'email', 'max:255'],
            'contact.map_url' => $httpsUrl,
            // Only Google Maps embeds may be shown in the contact page iframe.
            'contact.map_embed_url' => ['nullable', 'url:https', 'max:2000', 'starts_with:https://www.google.com/maps/embed'],
            'contact.form_recipient' => ['nullable', 'email', 'max:255'],

            'social.facebook' => $httpsUrl,
            'social.instagram' => $httpsUrl,
            'social.youtube' => $httpsUrl,
            'social.tiktok' => $httpsUrl,
            'social.linkedin' => $httpsUrl,
            'social.x' => $httpsUrl,

            'site.default_locale' => ['required', Rule::in(config('translatable.locales'))],
            'site.maintenance_mode' => ['required', 'boolean'],
            'site.font' => ['required', Rule::enum(SiteFont::class)],
            'site.show_name_with_logo' => ['required', 'boolean'],

            // SVG is not accepted: it can carry scripts and would be served from the site's origin.
            'logo' => ['nullable', 'image', 'mimes:png,webp', 'max:2048', 'dimensions:min_width=120,min_height=40'],
            'logo_light' => ['nullable', 'image', 'mimes:png,webp', 'max:2048', 'dimensions:min_width=120,min_height=40'],
            'favicon' => ['nullable', 'image', 'mimes:png', 'max:1024', 'dimensions:min_width=48,ratio=1'],
            'remove_logo' => ['boolean'],
            'remove_logo_light' => ['boolean'],
            'remove_favicon' => ['boolean'],
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules["contact.address.{$locale}"] = ['nullable', 'string', 'max:500'];
            $rules["contact.working_hours.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["seo.meta_title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["seo.meta_description.{$locale}"] = ['nullable', 'string', 'max:500'];
            $rules["seo.meta_keywords.{$locale}"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'favicon.dimensions' => __('The browser icon must be a square image of at least 48 × 48 pixels.'),
            'contact.whatsapp.regex' => __('Enter the WhatsApp number in international format without "+" or spaces, e.g. 9665XXXXXXXX.'),
            'contact.map_embed_url.starts_with' => __('Paste the "src" address of the Google Maps embed code (it starts with https://www.google.com/maps/embed).'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'logo' => __('Logo'),
            'logo_light' => __('Logo for dark backgrounds'),
            'favicon' => __('Browser icon (favicon)'),
        ];
    }

    /**
     * The validated values of one settings group. Empty fields are stored as empty
     * strings because the settings properties are non-nullable strings.
     *
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        return $this->withEmptyStrings($this->validated($group, []));
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function withEmptyStrings(array $values): array
    {
        return array_map(
            fn (mixed $value): mixed => is_array($value) ? $this->withEmptyStrings($value) : ($value ?? ''),
            $values,
        );
    }
}
