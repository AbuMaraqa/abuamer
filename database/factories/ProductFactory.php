<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Support\Slug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
        $arabicName = 'منتج '.$number;

        return [
            'category_id' => Category::factory(),
            'sku' => 'NSQ-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT),
            'status' => true,
            'featured' => false,
            'sort_order' => 0,
            'ar' => [
                'name' => $arabicName,
                'slug' => Slug::make($arabicName, 'ar'),
                'short_description' => 'وصف مختصر لـ'.$arabicName,
                'description' => 'وصف تفصيلي لـ'.$arabicName,
            ],
            'en' => [
                'name' => $englishName,
                'slug' => Slug::make($englishName, 'en'),
                'short_description' => fake()->sentence(),
                'description' => fake()->paragraph(),
            ],
        ];
    }

    public function named(string $englishName, ?string $arabicName = null): static
    {
        $arabicName ??= $englishName;

        return $this->state(fn (array $attributes) => [
            'ar' => [...$attributes['ar'], 'name' => $arabicName, 'slug' => Slug::make($arabicName, 'ar')],
            'en' => [...$attributes['en'], 'name' => $englishName, 'slug' => Slug::make($englishName, 'en')],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
