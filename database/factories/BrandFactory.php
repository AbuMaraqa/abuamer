<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()).' '.fake()->unique()->numberBetween(1, 999_999);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'country' => fake()->randomElement(['DE', 'IT', 'ES', 'TR']),
            'website' => null,
            'status' => true,
            'sort_order' => 0,
            'ar' => ['description' => 'علامة تجارية للأدوات الصحية.'],
            'en' => ['description' => fake()->sentence()],
        ];
    }

    /**
     * Use the given name and the slug derived from it, e.g. named('Aquaro').
     */
    public function named(string $name): static
    {
        return $this->state(fn () => ['name' => $name, 'slug' => Str::slug($name)]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }
}
