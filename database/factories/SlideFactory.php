<?php

namespace Database\Factories;

use App\Enums\SlideLinkType;
use App\Models\Category;
use App\Models\Slide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slide>
 */
class SlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_type' => SlideLinkType::None,
            'category_id' => null,
            'status' => true,
            'sort_order' => 0,
            'ar' => ['eyebrow' => 'مجموعة جديدة', 'title' => 'رخام كالاكاتا', 'text' => 'تفاصيل فاخرة لكل مساحة.'],
            'en' => ['eyebrow' => 'New collection', 'title' => 'Calacatta marble', 'text' => 'Refined details for every space.'],
        ];
    }

    public function linkedTo(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'link_type' => SlideLinkType::Category,
            'category_id' => $category->id,
            'ar' => [...$attributes['ar'], 'button_label' => 'اكتشف المجموعة'],
            'en' => [...$attributes['en'], 'button_label' => 'Discover the collection'],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }
}
