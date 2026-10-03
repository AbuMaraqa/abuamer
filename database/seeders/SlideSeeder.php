<?php

namespace Database\Seeders;

use App\Enums\SlideLinkType;
use App\Models\Category;
use App\Models\Slide;
use Database\Seeders\Concerns\GeneratesDemoImages;
use Illuminate\Database\Seeder;

/**
 * Demo slides for local development. The images are generated tile textures standing in
 * for real photography, which is uploaded from the control panel.
 */
class SlideSeeder extends Seeder
{
    use GeneratesDemoImages;

    /**
     * @var list<array{category: string, colors: array{0: array{int, int, int}, 1: array{int, int, int}}, ar: array<string, string>, en: array<string, string>}>
     */
    private const array SLIDES = [
        [
            'category' => 'Calacatta',
            'colors' => [[58, 50, 41], [196, 178, 150]],
            'ar' => ['eyebrow' => 'مجموعة 2026', 'title' => 'رخام كالاكاتا بلمسة ذهبية', 'text' => 'عروق ذهبية دافئة على أرضية بيضاء ناصعة، لمساحات تنبض بالفخامة.', 'button_label' => 'اكتشف المجموعة'],
            'en' => ['eyebrow' => '2026 Collection', 'title' => 'Calacatta marble with a golden touch', 'text' => 'Warm golden veining on a bright white ground, for spaces that feel truly refined.', 'button_label' => 'Discover the collection'],
        ],
        [
            'category' => 'Porcelain',
            'colors' => [[34, 36, 39], [128, 132, 136]],
            'ar' => ['eyebrow' => 'مقاسات كبيرة', 'title' => 'بورسلان بلا حدود', 'text' => 'ألواح بمقاس 120 × 120 سم بفواصل شبه معدومة لمساحات واسعة ومتصلة.', 'button_label' => 'تصفح البورسلان'],
            'en' => ['eyebrow' => 'Large format', 'title' => 'Porcelain without limits', 'text' => '120 × 120 cm slabs with near-invisible joints for open, continuous spaces.', 'button_label' => 'Browse porcelain'],
        ],
        [
            'category' => 'Outdoor',
            'colors' => [[52, 34, 24], [168, 116, 78]],
            'ar' => ['eyebrow' => 'للمساحات الخارجية', 'title' => 'أرضيات تدوم تحت الشمس', 'text' => 'بلاط مانع للانزلاق ومقاوم للعوامل الجوية للحدائق والمداخل.', 'button_label' => 'المساحات الخارجية'],
            'en' => ['eyebrow' => 'Outdoor living', 'title' => 'Floors made to last in the sun', 'text' => 'Anti-slip, weather-resistant tiles for gardens and entrances.', 'button_label' => 'Outdoor tiles'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::SLIDES as $position => $data) {
            $category = Category::query()->whereTranslation('name', $data['category'], 'en')->first();

            $slide = Slide::create([
                'status' => true,
                'sort_order' => $position + 1,
                'link_type' => $category ? SlideLinkType::Category : SlideLinkType::None,
                'category_id' => $category?->id,
                'ar' => $data['ar'],
                'en' => $data['en'],
            ]);

            $slide->addMedia($this->tileTexture($data['colors'][0], $data['colors'][1], $position + 1))
                ->usingFileName("slide-{$slide->id}.jpg")
                ->toMediaCollection(Slide::IMAGE_COLLECTION);
        }
    }
}
