<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use App\Enums\SlideLinkType;
use App\Http\Requests\Concerns\ValidatesTranslations;
use App\Support\Locales;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSlideRequest extends FormRequest
{
    use ValidatesTranslations;

    private const array TRANSLATED_FIELDS = ['eyebrow', 'title', 'text', 'button_label', 'button_url'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permission::CompanyManage->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $linkType = $this->enum('link_type', SlideLinkType::class);

        $rules = [
            'status' => ['required', 'boolean'],
            'link_type' => ['required', Rule::enum(SlideLinkType::class)],
            'category_id' => [
                Rule::requiredIf($linkType === SlideLinkType::Category),
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'image' => [$this->imageRequired() ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:min_width=1600,min_height=800'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:min_width=750,min_height=1000'],
            'remove_mobile_image' => ['boolean'],
        ];

        foreach ($this->locales() as $locale) {
            // An optional language that is left empty shows the fallback language's slide texts.
            $isWritten = Locales::isRequired($locale) || filled($this->input("{$locale}.title"));

            $rules[$locale] = $this->languageRules($locale);
            $rules["{$locale}.eyebrow"] = ['nullable', 'string', 'max:80'];
            $rules["{$locale}.title"] = [...$this->requiredInLanguage($locale, $locale, ['eyebrow', 'text', 'button_label', 'button_url']), 'string', 'max:120'];
            $rules["{$locale}.text"] = ['nullable', 'string', 'max:300'];
            $rules["{$locale}.button_label"] = [Rule::requiredIf($isWritten && $linkType !== SlideLinkType::None), 'nullable', 'string', 'max:40'];
            // A full https link or a path on this website; never javascript: or other schemes.
            $rules["{$locale}.button_url"] = [
                Rule::requiredIf($isWritten && $linkType === SlideLinkType::Custom),
                'nullable',
                'string',
                'max:500',
                'regex:/^(https:\/\/|\/(?!\/))/',
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = $this->translationMessages();

        foreach ($this->locales() as $locale) {
            $messages["{$locale}.button_url.regex"] = __('Enter a link that starts with https:// or with / for a page on this website.');
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'image' => __('Slide image'),
            'mobile_image' => __('Mobile image'),
            'category_id' => __('Category'),
        ];

        foreach ($this->locales() as $locale) {
            $language = __(config("laravellocalization.supportedLocales.{$locale}.name"));

            foreach (['eyebrow' => 'Small heading', 'title' => 'Title', 'text' => 'Text', 'button_label' => 'Button text', 'button_url' => 'Button link'] as $field => $label) {
                $attributes["{$locale}.{$field}"] = __($label).' ('.$language.')';
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function slideAttributes(): array
    {
        $linkType = $this->enum('link_type', SlideLinkType::class);

        return [
            'status' => $this->boolean('status'),
            'link_type' => $linkType,
            'category_id' => $linkType === SlideLinkType::Category ? $this->integer('category_id') : null,
            ...$this->withoutEmptyLanguages(collect($this->locales())
                ->mapWithKeys(fn (string $locale): array => [
                    $locale => collect(self::TRANSLATED_FIELDS)
                        ->mapWithKeys(fn (string $field): array => [$field => $this->validated("{$locale}.{$field}")])
                        ->all(),
                ])
                ->all()),
        ];
    }

    protected function imageRequired(): bool
    {
        return true;
    }
}
