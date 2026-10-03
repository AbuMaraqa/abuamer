<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Support\Slug;
use Database\Seeders\Concerns\GeneratesDemoImages;
use Illuminate\Database\Seeder;

/**
 * Demo catalog for local development: the tiles and marble department from the project
 * brief, in Arabic and English, with a few products and specifications. The sanitary
 * ware department is seeded by SanitaryWareSeeder.
 */
class CatalogSeeder extends Seeder
{
    use GeneratesDemoImages;

    /**
     * Each node: [English name, Arabic name, children].
     *
     * @var list<array{0: string, 1: string, 2: array<int, mixed>}>
     */
    private const array TREE = [
        ['Tiles & Marble', 'البلاط والرخام', [
            ['Porcelain', 'بورسلان', [
                ['Marble Effect', 'تأثير الرخام', [
                    ['Calacatta', 'كالاكاتا', [
                        ['Gold', 'ذهبي', []],
                        ['White', 'أبيض', []],
                    ]],
                    ['Carrara', 'كرارا', []],
                ]],
                ['Stone Effect', 'تأثير الحجر', [
                    ['Natural Stone', 'حجر طبيعي', []],
                    ['Slate', 'أردواز', []],
                ]],
                ['Wood Effect', 'تأثير الخشب', []],
            ]],
            ['Wall Tiles', 'بلاط الجدران', [
                ['Kitchen', 'المطابخ', []],
                ['Bathroom', 'الحمامات', []],
                ['Decorative', 'ديكوري', []],
            ]],
            ['Floor Tiles', 'بلاط الأرضيات', [
                ['Indoor', 'داخلي', [
                    ['Living Room', 'غرف المعيشة', []],
                    ['Bedroom', 'غرف النوم', []],
                ]],
                ['Outdoor', 'خارجي', [
                    ['Garden', 'الحدائق', []],
                    ['Entrance', 'المداخل', []],
                ]],
            ]],
        ]],
    ];

    /**
     * Products keyed by the English name of their category.
     *
     * @var array<string, list<array{en: string, ar: string, sku: string, featured: bool, finish: array{0: string, 1: string}, size: string}>>
     */
    private const array PRODUCTS = [
        'Gold' => [
            ['en' => 'Calacatta Gold 60x120', 'ar' => 'كالاكاتا ذهبي 60×120', 'sku' => 'NSQ-CG-60120', 'featured' => true, 'finish' => ['Polished', 'لامع'], 'size' => '60 × 120'],
            ['en' => 'Calacatta Gold 120x120', 'ar' => 'كالاكاتا ذهبي 120×120', 'sku' => 'NSQ-CG-120120', 'featured' => false, 'finish' => ['Polished', 'لامع'], 'size' => '120 × 120'],
        ],
        'White' => [
            ['en' => 'Calacatta White 60x120', 'ar' => 'كالاكاتا أبيض 60×120', 'sku' => 'NSQ-CW-60120', 'featured' => true, 'finish' => ['Matte', 'مطفي'], 'size' => '60 × 120'],
        ],
        'Carrara' => [
            ['en' => 'Carrara Classic 60x60', 'ar' => 'كرارا كلاسيك 60×60', 'sku' => 'NSQ-CR-6060', 'featured' => false, 'finish' => ['Satin', 'ساتان'], 'size' => '60 × 60'],
        ],
        'Slate' => [
            ['en' => 'Slate Graphite 60x120', 'ar' => 'أردواز جرافيت 60×120', 'sku' => 'NSQ-SG-60120', 'featured' => true, 'finish' => ['Structured', 'محبب'], 'size' => '60 × 120'],
        ],
        'Wood Effect' => [
            ['en' => 'Oak Natural 20x120', 'ar' => 'بلوط طبيعي 20×120', 'sku' => 'NSQ-ON-20120', 'featured' => true, 'finish' => ['Matte', 'مطفي'], 'size' => '20 × 120'],
        ],
        'Kitchen' => [
            ['en' => 'Zellige Sage 10x10', 'ar' => 'زليج أخضر مريمي 10×10', 'sku' => 'NSQ-ZS-1010', 'featured' => false, 'finish' => ['Glossy', 'لامع'], 'size' => '10 × 10'],
        ],
        'Garden' => [
            ['en' => 'Travertine Outdoor 60x90', 'ar' => 'ترافرتين خارجي 60×90', 'sku' => 'NSQ-TO-6090', 'featured' => true, 'finish' => ['Anti-slip', 'مانع للانزلاق'], 'size' => '60 × 90'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesByName = [];
        $this->createNodes(self::TREE, null, $categoriesByName);

        $categoriesByName['Tiles & Marble']
            ->addMedia($this->tileTexture([58, 50, 41], [214, 202, 184], 11))
            ->usingFileName('tiles-and-marble.jpg')
            ->toMediaCollection(Category::IMAGE_COLLECTION);

        foreach (self::PRODUCTS as $categoryName => $products) {
            foreach ($products as $position => $product) {
                $this->createProduct($categoriesByName[$categoryName], $product, $position + 1);
            }
        }
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @param  array<string, Category>  $categoriesByName
     */
    private function createNodes(array $nodes, ?Category $parent, array &$categoriesByName): void
    {
        foreach ($nodes as $position => [$english, $arabic, $children]) {
            $category = Category::create([
                'parent_id' => $parent?->id,
                'status' => true,
                'sort_order' => $position + 1,
                'ar' => ['name' => $arabic, 'slug' => Slug::make($arabic, 'ar'), 'description' => "مجموعة {$arabic} المختارة من نُسق."],
                'en' => ['name' => $english, 'slug' => Slug::make($english, 'en'), 'description' => "The {$english} collection, selected by Nasaq."],
            ]);

            $categoriesByName[$english] = $category;
            $this->createNodes($children, $category, $categoriesByName);
        }
    }

    /**
     * @param  array{en: string, ar: string, sku: string, featured: bool, finish: array{0: string, 1: string}, size: string}  $data
     */
    private function createProduct(Category $category, array $data, int $position): void
    {
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => $data['sku'],
            'status' => true,
            'featured' => $data['featured'],
            'sort_order' => $position,
            'ar' => [
                'name' => $data['ar'],
                'slug' => Slug::make($data['ar'], 'ar'),
                'short_description' => 'بلاط بورسلان فاخر بتشطيب '.$data['finish'][1].' يمنح المساحات طابعًا معماريًا راقيًا.',
                'description' => "مصنوع من البورسلان عالي الكثافة لمقاومة التآكل والبقع.\nمناسب للمساحات السكنية والتجارية.",
            ],
            'en' => [
                'name' => $data['en'],
                'slug' => Slug::make($data['en'], 'en'),
                'short_description' => 'Premium porcelain tile with a '.mb_strtolower($data['finish'][0]).' finish that gives any space an architectural character.',
                'description' => "Made from high-density porcelain that resists wear and stains.\nSuitable for residential and commercial spaces.",
            ],
        ]);

        $specifications = [
            [['Size', $data['size'].' cm'], ['المقاس', $data['size'].' سم']],
            [['Thickness', '9 mm'], ['السماكة', '9 مم']],
            [['Finish', $data['finish'][0]], ['التشطيب', $data['finish'][1]]],
            [['Material', 'Porcelain'], ['المادة', 'بورسلان']],
        ];

        foreach ($specifications as $index => [[$labelEn, $valueEn], [$labelAr, $valueAr]]) {
            $product->specifications()->create([
                'sort_order' => $index + 1,
                'en' => ['label' => $labelEn, 'value' => $valueEn],
                'ar' => ['label' => $labelAr, 'value' => $valueAr],
            ]);
        }
    }
}
