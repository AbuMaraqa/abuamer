<?php

namespace Database\Seeders;

use App\Enums\SlideLinkType;
use App\Models\Category;
use App\Models\Slide;
use Illuminate\Database\Seeder;

/**
 * Demo slides for local development. The images are generated tile textures standing in
 * for real photography, which is uploaded from the control panel.
 */
class SlideSeeder extends Seeder
{
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

    /**
     * A large-format tile surface with marble-like veins and soft light, saved as a temporary JPEG.
     *
     * @param  array{int, int, int}  $dark
     * @param  array{int, int, int}  $light
     */
    private function tileTexture(array $dark, array $light, int $seed): string
    {
        mt_srand($seed);
        [$width, $height, $tile] = [2400, 1350, 600];

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);

        // Grout between the tiles.
        imagefill($image, 0, 0, imagecolorallocate($image, ...array_map(fn (int $channel): int => (int) ($channel * 0.55), $dark)));

        for ($x = 0; $x < $width; $x += $tile) {
            for ($y = -$tile / 3; $y < $height; $y += $tile) {
                $mix = 0.35 + mt_rand(0, 12) / 100;
                $color = array_map(fn (int $from, int $to): int => (int) ($from + ($to - $from) * $mix), $dark, $light);
                imagefilledrectangle($image, $x + 2, (int) $y + 2, $x + $tile - 2, (int) $y + $tile - 2, imagecolorallocate($image, ...$color));
            }
        }

        // Veins: wandering lines in light, translucent strokes.
        for ($vein = 0; $vein < 18; $vein++) {
            [$x, $y] = [mt_rand(-300, $width), mt_rand(0, $height)];
            $angle = deg2rad(mt_rand(-35, 35));
            imagesetthickness($image, mt_rand(1, 3));
            $color = imagecolorallocatealpha($image, ...[...$light, mt_rand(70, 110)]);

            for ($step = 0; $step < 90; $step++) {
                $angle += deg2rad(mt_rand(-14, 14));
                [$nextX, $nextY] = [$x + cos($angle) * 22, $y + sin($angle) * 22];
                imageline($image, (int) $x, (int) $y, (int) $nextX, (int) $nextY, $color);
                [$x, $y] = [$nextX, $nextY];
            }
        }

        // Soft light falling from the top corner.
        for ($ring = 40; $ring > 0; $ring--) {
            $glow = imagecolorallocatealpha($image, 255, 246, 230, 127 - (int) (3 * (40 - $ring) / 40 * 3));
            imagefilledellipse($image, (int) ($width * 0.75), (int) ($height * 0.1), $ring * 90, $ring * 70, $glow);
        }

        $path = tempnam(sys_get_temp_dir(), 'slide').'.jpg';
        imagejpeg($image, $path, 88);
        imagedestroy($image);

        return $path;
    }
}
