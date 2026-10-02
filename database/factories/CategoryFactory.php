<?php

namespace Database\Factories;

use App\Models\Category;
use App\Support\Slug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 999_999);
        $englishName = ucwords(fake()->words(2, true)).' '.$number;
        $arabicName = 'تصنيف '.$number;

        return [
            'parent_id' => null,
            'status' => true,
            'sort_order' => 0,
            'ar' => [
                'name' => $arabicName,
                'slug' => Slug::make($arabicName, 'ar'),
                'description' => 'وصف '.$arabicName,
            ],
            'en' => [
                'name' => $englishName,
                'slug' => Slug::make($englishName, 'en'),
                'description' => fake()->sentence(),
            ],
        ];
    }

    /**
     * Use the given names (and slugs derived from them), e.g. named('Porcelain', 'بورسلان').
     */
    public function named(string $englishName, ?string $arabicName = null): static
    {
        $arabicName ??= $englishName;

        return $this->state(fn (array $attributes) => [
            'ar' => [...$attributes['ar'], 'name' => $arabicName, 'slug' => Slug::make($arabicName, 'ar')],
            'en' => [...$attributes['en'], 'name' => $englishName, 'slug' => Slug::make($englishName, 'en')],
        ]);
    }

    /**
     * Add a Hebrew translation with the given name, e.g. withHebrew('פורצלן').
     */
    public function withHebrew(string $hebrewName): static
    {
        return $this->state(fn () => [
            'he' => ['name' => $hebrewName, 'slug' => Slug::make($hebrewName, 'he'), 'description' => 'תיאור '.$hebrewName],
        ]);
    }

    public function childOf(Category $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }
}
