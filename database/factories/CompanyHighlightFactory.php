<?php

namespace Database\Factories;

use App\Enums\HighlightType;
use App\Models\CompanyHighlight;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyHighlight>
 */
class CompanyHighlightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => HighlightType::Value,
            'icon' => null,
            'value' => null,
            'sort_order' => 0,
            'ar' => ['title' => 'الجودة', 'description' => 'نختار أفضل الخامات.'],
            'en' => ['title' => 'Quality', 'description' => 'We select the finest materials.'],
        ];
    }

    public function feature(string $icon = 'gem'): static
    {
        return $this->state(fn () => ['type' => HighlightType::Feature, 'icon' => $icon]);
    }

    public function statistic(string $value = '25+'): static
    {
        return $this->state(fn () => ['type' => HighlightType::Statistic, 'value' => $value]);
    }
}
