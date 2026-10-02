<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSpecification>
 */
class ProductSpecificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sort_order' => 0,
            'ar' => ['label' => 'المقاس', 'value' => '60 × 120 سم'],
            'en' => ['label' => 'Size', 'value' => '60 × 120 cm'],
        ];
    }
}
