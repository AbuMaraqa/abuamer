<?php

namespace Database\Seeders;

use App\Enums\SlideLinkType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use App\Support\Slug;
use Database\Seeders\Concerns\GeneratesDemoImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo sanitary ware department for local development: brands, the category tree, products
 * with their specifications and a home page slide, in Arabic, English and Hebrew.
 *
 * The brands are fictional placeholders for the real manufacturers, which are entered from
 * the control panel. It also runs on an existing database and does nothing once the
 * department exists: php artisan db:seed --class=SanitaryWareSeeder
 */
class SanitaryWareSeeder extends Seeder
{
    use GeneratesDemoImages;

    private const string ROOT = 'Sanitary Ware';

    /**
     * Each node: [English, Arabic, Hebrew, children].
     *
     * @var list<array{0: string, 1: string, 2: string, 3: array<int, mixed>}>
     */
    private const array TREE = [
        [self::ROOT, 'الأدوات الصحية', 'כלים סניטריים', [
            ['Toilets', 'المراحيض', 'אסלות', [
                ['Wall-Hung Toilets', 'مراحيض معلّقة', 'אסלות תלויות', []],
                ['Floor-Standing Toilets', 'مراحيض أرضية', 'אסלות עומדות', []],
                ['Smart Toilets', 'مراحيض ذكية', 'אסלות חכמות', []],
                ['Concealed Cisterns', 'سيفونات مخفية', 'מיכלי הדחה סמויים', []],
            ]],
            ['Washbasins', 'المغاسل', 'כיורים', [
                ['Countertop Basins', 'مغاسل سطحية', 'כיורים מונחים', []],
                ['Wall-Hung Basins', 'مغاسل معلّقة', 'כיורים תלויים', []],
                ['Pedestal Basins', 'مغاسل بعمود', 'כיורים על עמוד', []],
            ]],
            ['Mixers & Taps', 'الخلاطات والحنفيات', 'ברזים', [
                ['Basin Mixers', 'خلاطات المغاسل', 'ברזי כיור', []],
                ['Kitchen Mixers', 'خلاطات المطبخ', 'ברזי מטבח', []],
                ['Bath & Shower Mixers', 'خلاطات البانيو والدش', 'ברזי אמבטיה ומקלחת', []],
                ['Concealed Mixers', 'خلاطات مدفونة', 'ברזים סמויים', []],
            ]],
            ['Showers', 'أنظمة الدش', 'מקלחות', [
                ['Shower Systems', 'أعمدة الدش', 'מערכות מקלחת', []],
                ['Rain Showers', 'رؤوس دش مطرية', 'ראשי מקלחת גשם', []],
                ['Hand Showers', 'دش يدوي', 'מקלחוני יד', []],
            ]],
            ['Bathtubs', 'أحواض الاستحمام', 'אמבטיות', [
                ['Freestanding Bathtubs', 'أحواض قائمة بذاتها', 'אמבטיות עומדות', []],
                ['Built-in Bathtubs', 'أحواض مدمجة', 'אמבטיות בנויות', []],
                ['Whirlpools', 'جاكوزي', 'ג׳קוזי', []],
            ]],
            ['Shower Enclosures', 'كبائن الاستحمام', 'מקלחונים', []],
            ['Bathroom Furniture', 'أثاث الحمام', 'ריהוט אמבטיה', [
                ['Vanity Units', 'وحدات المغاسل', 'ארונות כיור', []],
                ['Mirrors & Cabinets', 'المرايا والخزائن', 'מראות וארונות', []],
            ]],
            ['Kitchen Sinks', 'مجالي المطبخ', 'כיורי מטבח', []],
            ['Bathroom Accessories', 'إكسسوارات الحمام', 'אביזרי אמבטיה', []],
        ]],
    ];

    /**
     * Fictional brands: [name, country, descriptions in English, Arabic and Hebrew].
     *
     * @var list<array{0: string, 1: string, 2: array{0: string, 1: string, 2: string}}>
     */
    private const array BRANDS = [
        ['Aquaro', 'IT', [
            'An Italian design house for basin mixers, shower systems and bathroom accessories, known for slim lines and warm metal finishes.',
            'دار تصميم إيطالية متخصصة في خلاطات المغاسل وأنظمة الدش وإكسسوارات الحمام، تتميز بخطوطها الرشيقة وتشطيباتها المعدنية الدافئة.',
            'בית עיצוב איטלקי לברזי כיור, מערכות מקלחת ואביזרי אמבטיה, הידוע בקווים דקים ובגימורי מתכת חמים.',
        ]],
        ['Nordbad', 'DE', [
            'German engineering for wall-hung toilets, concealed cisterns and thermostatic shower systems, built for decades of daily use.',
            'هندسة ألمانية للمراحيض المعلّقة والسيفونات المخفية وأنظمة الدش بالثرموستات، مصممة للاستخدام اليومي لعقود.',
            'הנדסה גרמנית לאסלות תלויות, מיכלי הדחה סמויים ומערכות מקלחת טרמוסטטיות, שנבנו לשימוש יומיומי לאורך עשורים.',
        ]],
        ['Velaria', 'ES', [
            'A Spanish specialist in vitreous china: basins, toilets and bathtubs with smooth, easy-to-clean glazes.',
            'شركة إسبانية متخصصة في البورسلان الصحي: مغاسل ومراحيض وأحواض استحمام بطبقة لامعة ناعمة سهلة التنظيف.',
            'מומחית ספרדית לחרסינה סניטרית: כיורים, אסלות ואמבטיות בזיגוג חלק וקל לניקוי.',
        ]],
        ['Bagnolino', 'IT', [
            'Bathroom furniture, shower screens and freestanding bathtubs with a contemporary Italian touch.',
            'أثاث حمامات وحواجز دش وأحواض استحمام قائمة بذاتها بلمسة إيطالية معاصرة.',
            'ריהוט אמבטיה, מחיצות מקלחת ואמבטיות עומדות בנגיעה איטלקית עכשווית.',
        ]],
        ['Arvento', 'TR', [
            'Durable kitchen mixers, granite sinks and shower sets, made for busy family kitchens and bathrooms.',
            'خلاطات مطابخ ومجالٍ من الجرانيت وأطقم دش متينة، مصممة لمطابخ وحمامات العائلات المزدحمة.',
            'ברזי מטבח, כיורי גרניט וסטים למקלחת עמידים, שנועדו למטבחים ולחדרי רחצה של משפחות עמוסות.',
        ]],
    ];

    /**
     * Country names for the "Country of origin" specification.
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private const array COUNTRIES = [
        'IT' => ['Italy', 'إيطاليا', 'איטליה'],
        'DE' => ['Germany', 'ألمانيا', 'גרמניה'],
        'ES' => ['Spain', 'إسبانيا', 'ספרד'],
        'TR' => ['Turkey', 'تركيا', 'טורקיה'],
    ];

    /**
     * Specification names in Arabic and Hebrew, keyed by the English name.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const array LABELS = [
        'Dimensions' => ['الأبعاد', 'מידות'],
        'Material' => ['المادة', 'חומר'],
        'Finish' => ['التشطيب', 'גימור'],
        'Color' => ['اللون', 'צבע'],
        'Installation' => ['طريقة التركيب', 'התקנה'],
        'Flush system' => ['نظام الدفق', 'מערכת הדחה'],
        'Outlet' => ['مخرج التصريف', 'יציאת ניקוז'],
        'Flow rate' => ['معدل التدفق', 'ספיקה'],
        'Cartridge' => ['الخرطوشة', 'מחסנית'],
        'Capacity' => ['السعة', 'קיבולת'],
        'Functions' => ['الوظائف', 'פונקציות'],
        'Includes' => ['يشمل', 'כולל'],
        'Warranty' => ['الضمان', 'אחריות'],
        'Country of origin' => ['بلد المنشأ', 'ארץ ייצור'],
    ];

    /**
     * @var list<array{category: string, brand: string, sku: string, featured: bool, name: array{0: string, 1: string, 2: string}, short: array{0: string, 1: string, 2: string}, specs: array<string, array{0: string, 1: string, 2: string}>}>
     */
    private const array PRODUCTS = [
        [
            'category' => 'Wall-Hung Toilets', 'brand' => 'Velaria', 'sku' => 'VEL-WH-5436', 'featured' => true,
            'name' => ['Lumo Rimless Wall-Hung Toilet', 'مرحاض معلّق لومو بدون حافة', 'אסלה תלויה לומו ללא שפה'],
            'short' => [
                'A compact wall-hung toilet with a rimless bowl that flushes clean and leaves nowhere for dirt to hide.',
                'مرحاض معلّق بتصميم مدمج ووعاء بدون حافة يضمن دفقًا نظيفًا ولا يترك مكانًا لتراكم الأوساخ.',
                'אסלה תלויה קומפקטית עם קערה ללא שפה, שמודחת נקי ולא משאירה מקום ללכלוך.',
            ],
            'specs' => [
                'Dimensions' => ['54 × 36 × 34 cm', '54 × 36 × 34 سم', '54 × 36 × 34 ס״מ'],
                'Material' => ['Vitreous china', 'بورسلان صحي', 'חרסינה סניטרית'],
                'Flush system' => ['Rimless, 3/6 L dual flush', 'بدون حافة، دفق مزدوج 3/6 لتر', 'ללא שפה, הדחה כפולה 3/6 ל׳'],
                'Installation' => ['Wall-hung, with concealed cistern', 'معلّق مع سيفون مخفي', 'תלוי, עם מיכל הדחה סמוי'],
                'Warranty' => ['10 years', '10 سنوات', '10 שנים'],
            ],
        ],
        [
            'category' => 'Floor-Standing Toilets', 'brand' => 'Velaria', 'sku' => 'VEL-FS-6537',
            'featured' => false,
            'name' => ['Classico Close-Coupled Toilet', 'مرحاض أرضي كلاسيكو مع خزان', 'אסלה עומדת קלאסיקו עם מיכל'],
            'short' => [
                'A close-coupled toilet with a soft-close seat and a quiet dual flush, for family bathrooms.',
                'مرحاض أرضي مع خزان ملتصق ومقعد بإغلاق هادئ ودفق مزدوج صامت، مثالي لحمامات العائلة.',
                'אסלה עומדת עם מיכל צמוד, מושב בטריקה שקטה והדחה כפולה שקטה, לחדרי רחצה משפחתיים.',
            ],
            'specs' => [
                'Dimensions' => ['65 × 37 × 79 cm', '65 × 37 × 79 سم', '65 × 37 × 79 ס״מ'],
                'Material' => ['Vitreous china', 'بورسلان صحي', 'חרסינה סניטרית'],
                'Outlet' => ['Floor outlet (S-trap)', 'تصريف أرضي (S)', 'ניקוז לרצפה (S)'],
                'Flush system' => ['3/6 L dual flush', 'دفق مزدوج 3/6 لتر', 'הדחה כפולה 3/6 ל׳'],
                'Warranty' => ['10 years', '10 سنوات', '10 שנים'],
            ],
        ],
        [
            'category' => 'Smart Toilets', 'brand' => 'Velaria', 'sku' => 'VEL-SM-5840', 'featured' => true,
            'name' => ['Aria Smart Toilet with Bidet Function', 'مرحاض ذكي آريا بوظيفة الشطاف', 'אסלה חכמה אריה עם פונקציית בידה'],
            'short' => [
                'A smart toilet with a heated seat, warm-water wash and dryer, controlled from a slim remote.',
                'مرحاض ذكي بمقعد مُدفأ وغسيل بالماء الدافئ ومجفف، يُتحكم به عبر جهاز تحكم أنيق.',
                'אסלה חכמה עם מושב מחומם, שטיפה במים חמים וייבוש, בשליטת שלט דק.',
            ],
            'specs' => [
                'Dimensions' => ['58 × 40 × 45 cm', '58 × 40 × 45 سم', '58 × 40 × 45 ס״מ'],
                'Functions' => ['Heated seat, warm-water wash, dryer', 'مقعد مُدفأ، غسيل بالماء الدافئ، تجفيف', 'מושב מחומם, שטיפה במים חמים, ייבוש'],
                'Installation' => ['Floor-standing', 'أرضي', 'עומד'],
                'Warranty' => ['5 years', '5 سنوات', '5 שנים'],
            ],
        ],
        [
            'category' => 'Concealed Cisterns', 'brand' => 'Nordbad', 'sku' => 'NRD-CC-112', 'featured' => false,
            'name' => ['Nordbad Concealed Cistern Frame 112 cm', 'سيفون مخفي نوردباد بإطار 112 سم', 'מיכל הדחה סמוי נורדבאד 112 ס״מ'],
            'short' => [
                'A self-supporting frame with a concealed cistern for wall-hung toilets, ready for dry-wall or masonry.',
                'إطار ذاتي التحميل مع سيفون مخفي للمراحيض المعلّقة، مناسب للجدران الجافة أو المبنية.',
                'מסגרת עצמאית עם מיכל הדחה סמוי לאסלות תלויות, מתאימה לקיר גבס או לבנייה.',
            ],
            'specs' => [
                'Dimensions' => ['112 × 50 × 12 cm', '112 × 50 × 12 سم', '112 × 50 × 12 ס״מ'],
                'Flush system' => ['3/6 L dual flush', 'دفق مزدوج 3/6 لتر', 'הדחה כפולה 3/6 ל׳'],
                'Installation' => ['Dry-wall or masonry', 'جدار جاف أو مبني', 'קיר גבס או בנייה'],
                'Warranty' => ['10 years', '10 سنوات', '10 שנים'],
            ],
        ],
        [
            'category' => 'Countertop Basins', 'brand' => 'Velaria', 'sku' => 'VEL-CB-5538', 'featured' => true,
            'name' => ['Ovo Oval Countertop Basin 55', 'مغسلة سطحية بيضاوية أوفو 55', 'כיור מונח אובאלי אובו 55'],
            'short' => [
                'A thin-rimmed oval basin that sits on the countertop like a sculpture.',
                'مغسلة بيضاوية بحافة رفيعة تستقر فوق السطح كقطعة فنية.',
                'כיור אובאלי עם שפה דקה, שמונח על המשטח כמו פסל.',
            ],
            'specs' => [
                'Dimensions' => ['55 × 38 × 14 cm', '55 × 38 × 14 سم', '55 × 38 × 14 ס״מ'],
                'Material' => ['Fine fireclay', 'فايركلاي ناعم', 'פיירקליי עדין'],
                'Finish' => ['Glossy white', 'أبيض لامع', 'לבן מבריק'],
                'Installation' => ['On the countertop', 'فوق السطح', 'מונח על משטח'],
            ],
        ],
        [
            'category' => 'Wall-Hung Basins', 'brand' => 'Bagnolino', 'sku' => 'BGN-WB-6046', 'featured' => false,
            'name' => ['Slim Wall-Hung Basin 60', 'مغسلة معلّقة سليم 60', 'כיור תלוי סלים 60'],
            'short' => [
                'A slim wall-hung basin with a generous bowl and a shelf for everyday essentials.',
                'مغسلة معلّقة رفيعة بحوض واسع ورف للأغراض اليومية.',
                'כיור תלוי דק עם קערה נדיבה ומדף לחפצים יומיומיים.',
            ],
            'specs' => [
                'Dimensions' => ['60 × 46 × 12 cm', '60 × 46 × 12 سم', '60 × 46 × 12 ס״מ'],
                'Material' => ['Vitreous china', 'بورسلان صحي', 'חרסינה סניטרית'],
                'Finish' => ['Matte white', 'أبيض مطفي', 'לבן מט'],
                'Installation' => ['Wall-hung', 'معلّق', 'תלוי'],
            ],
        ],
        [
            'category' => 'Basin Mixers', 'brand' => 'Aquaro', 'sku' => 'AQR-BM-LN01', 'featured' => true,
            'name' => ['Aquaro Linea Basin Mixer', 'خلاط مغسلة أكوارو لينيا', 'ברז כיור אקוורו ליניאה'],
            'short' => [
                'A single-lever basin mixer in solid brass with a soft, aerated flow that saves water.',
                'خلاط مغسلة بذراع واحدة من النحاس الصلب، بتدفق ناعم مهوّى يوفّر المياه.',
                'ברז כיור בידית אחת מפליז מלא, עם זרם רך ומאוורר שחוסך מים.',
            ],
            'specs' => [
                'Finish' => ['Brushed brass', 'نحاسي مصقول', 'פליז מוברש'],
                'Material' => ['Solid brass', 'نحاس صلب', 'פליז מלא'],
                'Cartridge' => ['35 mm ceramic', 'سيراميك 35 مم', 'קרמית 35 מ״מ'],
                'Flow rate' => ['5 L/min', '5 لتر/دقيقة', '5 ל׳/דקה'],
                'Warranty' => ['7 years', '7 سنوات', '7 שנים'],
            ],
        ],
        [
            'category' => 'Basin Mixers', 'brand' => 'Aquaro', 'sku' => 'AQR-BM-LN02', 'featured' => false,
            'name' => ['Aquaro Linea Tall Basin Mixer – Matte Black', 'خلاط مغسلة عالٍ أكوارو لينيا – أسود مطفي', 'ברז כיור גבוה אקוורו ליניאה – שחור מט'],
            'short' => [
                'The tall version of the Linea mixer, made for countertop basins, in a fingerprint-resistant matte black.',
                'النسخة العالية من خلاط لينيا للمغاسل السطحية، بلون أسود مطفي مقاوم لبصمات الأصابع.',
                'הגרסה הגבוהה של ברז ליניאה, לכיורים מונחים, בשחור מט עמיד לטביעות אצבע.',
            ],
            'specs' => [
                'Finish' => ['Matte black', 'أسود مطفي', 'שחור מט'],
                'Material' => ['Solid brass', 'نحاس صلب', 'פליז מלא'],
                'Cartridge' => ['35 mm ceramic', 'سيراميك 35 مم', 'קרמית 35 מ״מ'],
                'Flow rate' => ['5 L/min', '5 لتر/دقيقة', '5 ל׳/דקה'],
                'Warranty' => ['7 years', '7 سنوات', '7 שנים'],
            ],
        ],
        [
            'category' => 'Kitchen Mixers', 'brand' => 'Arvento', 'sku' => 'ARV-KM-PO40', 'featured' => true,
            'name' => ['Arvento Pull-Out Kitchen Mixer', 'خلاط مطبخ أرفينتو بدش قابل للسحب', 'ברז מטבח ארוונטו עם ראש נשלף'],
            'short' => [
                'A kitchen mixer with a two-function pull-out spray that reaches every corner of the sink.',
                'خلاط مطبخ برأس رش قابل للسحب بوظيفتين يصل إلى كل زوايا المجلى.',
                'ברז מטבח עם ראש נשלף דו־מצבי שמגיע לכל פינה בכיור.',
            ],
            'specs' => [
                'Finish' => ['Brushed stainless steel', 'ستانلس ستيل مصقول', 'נירוסטה מוברשת'],
                'Functions' => ['Pull-out spray: jet and shower', 'رأس قابل للسحب: تدفق مباشر ورذاذ', 'ראש נשלף: סילון ומקלחון'],
                'Cartridge' => ['40 mm ceramic', 'سيراميك 40 مم', 'קרמית 40 מ״מ'],
                'Warranty' => ['5 years', '5 سنوات', '5 שנים'],
            ],
        ],
        [
            'category' => 'Concealed Mixers', 'brand' => 'Aquaro', 'sku' => 'AQR-CM-TH02', 'featured' => false,
            'name' => ['Aquaro Concealed Thermostatic Shower Mixer', 'خلاط دش مدفون بثرموستات أكوارو', 'ברז מקלחת סמוי טרמוסטטי אקוורו'],
            'short' => [
                'A thermostatic mixer built into the wall that holds the water at your chosen temperature, with a two-way diverter.',
                'خلاط مدفون في الجدار بثرموستات يثبّت حرارة الماء على الدرجة المختارة، مع موزّع بمخرجين.',
                'ברז טרמוסטטי בתוך הקיר ששומר על המים בטמפרטורה שבחרתם, עם מפצל לשתי יציאות.',
            ],
            'specs' => [
                'Finish' => ['Chrome', 'كروم', 'כרום'],
                'Functions' => ['Thermostat, 2-way diverter', 'ثرموستات، موزّع بمخرجين', 'טרמוסטט, מפצל 2 יציאות'],
                'Installation' => ['Concealed, in the wall', 'مدفون في الجدار', 'סמוי בתוך הקיר'],
                'Warranty' => ['7 years', '7 سنوات', '7 שנים'],
            ],
        ],
        [
            'category' => 'Shower Systems', 'brand' => 'Nordbad', 'sku' => 'NRD-SS-300T', 'featured' => true,
            'name' => ['Nordbad Thermostatic Shower System 300', 'عمود دش نوردباد بثرموستات 300', 'מערכת מקלחת טרמוסטטית נורדבאד 300'],
            'short' => [
                'A complete shower column with a 30 cm rain shower, a hand shower and a safety thermostat set at 38 °C.',
                'عمود دش متكامل برأس مطري 30 سم ودش يدوي وثرموستات أمان مضبوط على 38 درجة.',
                'עמוד מקלחת שלם עם ראש גשם 30 ס״מ, מקלחון יד וטרמוסטט בטיחות המכוון ל־38 מעלות.',
            ],
            'specs' => [
                'Finish' => ['Chrome', 'كروم', 'כרום'],
                'Functions' => ['30 cm rain shower, hand shower, thermostat', 'رأس مطري 30 سم، دش يدوي، ثرموستات', 'ראש גשם 30 ס״מ, מקלחון יד, טרמוסטט'],
                'Installation' => ['Wall-mounted, exposed', 'على الجدار، ظاهر', 'על הקיר, גלוי'],
                'Warranty' => ['5 years', '5 سنوات', '5 שנים'],
            ],
        ],
        [
            'category' => 'Rain Showers', 'brand' => 'Aquaro', 'sku' => 'AQR-RS-4040', 'featured' => false,
            'name' => ['Aquaro Square Rain Shower 40 × 40', 'رأس دش مطري مربع أكوارو 40 × 40', 'ראש מקלחת גשם מרובע אקוורו 40 × 40'],
            'short' => [
                'An ultra-thin square rain shower with soft silicone nozzles that wipe clean of limescale.',
                'رأس دش مطري مربع فائق النحافة بفتحات سيليكون ناعمة يسهل تنظيفها من الترسبات الكلسية.',
                'ראש מקלחת גשם מרובע ודק במיוחד, עם פיות סיליקון רכות שקל לנקות מאבנית.',
            ],
            'specs' => [
                'Dimensions' => ['40 × 40 cm', '40 × 40 سم', '40 × 40 ס״מ'],
                'Finish' => ['Matte black', 'أسود مطفي', 'שחור מט'],
                'Installation' => ['Ceiling or wall', 'سقف أو جدار', 'תקרה או קיר'],
                'Flow rate' => ['12 L/min', '12 لتر/دقيقة', '12 ל׳/דקה'],
            ],
        ],
        [
            'category' => 'Freestanding Bathtubs', 'brand' => 'Bagnolino', 'sku' => 'BGN-FB-1780', 'featured' => true,
            'name' => ['Bagnolino Onda Freestanding Bathtub 170', 'حوض استحمام قائم بذاته بانيولينو أوندا 170', 'אמבטיה עומדת בניולינו אונדה 170'],
            'short' => [
                'A freestanding bathtub cast in one piece, with a silky matte surface that keeps the water warm for longer.',
                'حوض استحمام قائم بذاته مصبوب قطعة واحدة، بسطح مطفي حريري يحافظ على دفء الماء لفترة أطول.',
                'אמבטיה עומדת יצוקה מקשה אחת, עם משטח מט משיי ששומר על המים חמים לאורך זמן.',
            ],
            'specs' => [
                'Dimensions' => ['170 × 80 × 60 cm', '170 × 80 × 60 سم', '170 × 80 × 60 ס״מ'],
                'Material' => ['Mineral cast (solid surface)', 'حجر صناعي مصبوب', 'יציקה מינרלית (משטח מוצק)'],
                'Finish' => ['Matte white', 'أبيض مطفي', 'לבן מט'],
                'Capacity' => ['220 L', '220 لتر', '220 ל׳'],
                'Warranty' => ['10 years', '10 سنوات', '10 שנים'],
            ],
        ],
        [
            'category' => 'Whirlpools', 'brand' => 'Velaria', 'sku' => 'VEL-WP-1890', 'featured' => false,
            'name' => ['Velaria Whirlpool Bath 180 × 90', 'حوض جاكوزي فيلاريا 180 × 90', 'ג׳קוזי ולריה 180 × 90'],
            'short' => [
                'A spacious whirlpool bath with eight hydro-massage jets and soft LED lighting.',
                'حوض جاكوزي واسع بثماني فوهات تدليك مائي وإضاءة LED هادئة.',
                'ג׳קוזי מרווח עם שמונה סילוני עיסוי ותאורת LED רכה.',
            ],
            'specs' => [
                'Dimensions' => ['180 × 90 × 62 cm', '180 × 90 × 62 سم', '180 × 90 × 62 ס״מ'],
                'Material' => ['Sanitary acrylic', 'أكريليك صحي', 'אקריליק סניטרי'],
                'Functions' => ['8 hydro-massage jets, LED lighting', '8 فوهات تدليك مائي، إضاءة LED', '8 סילוני עיסוי, תאורת LED'],
                'Warranty' => ['5 years', '5 سنوات', '5 שנים'],
            ],
        ],
        [
            'category' => 'Shower Enclosures', 'brand' => 'Bagnolino', 'sku' => 'BGN-SE-1200', 'featured' => false,
            'name' => ['Walk-In Shower Screen with 8 mm Clear Glass', 'حاجز دش مفتوح بزجاج شفاف 8 مم', 'מחיצת מקלחת פתוחה מזכוכית שקופה 8 מ״מ'],
            'short' => [
                'A frameless walk-in screen in tempered safety glass, with a slim matte black profile.',
                'حاجز دش مفتوح بدون إطار من الزجاج المقسّى الآمن، مع بروفايل رفيع بلون أسود مطفي.',
                'מחיצה פתוחה ללא מסגרת מזכוכית בטיחות מחוסמת, עם פרופיל דק בשחור מט.',
            ],
            'specs' => [
                'Dimensions' => ['120 × 200 cm', '120 × 200 سم', '120 × 200 ס״מ'],
                'Material' => ['8 mm tempered safety glass', 'زجاج سيكوريت مقسّى 8 مم', 'זכוכית מחוסמת 8 מ״מ'],
                'Finish' => ['Clear glass, matte black profile', 'زجاج شفاف وبروفايل أسود مطفي', 'זכוכית שקופה, פרופיל שחור מט'],
                'Warranty' => ['2 years', 'سنتان', 'שנתיים'],
            ],
        ],
        [
            'category' => 'Vanity Units', 'brand' => 'Bagnolino', 'sku' => 'BGN-VU-80OK', 'featured' => true,
            'name' => ['Bagnolino Floating Vanity Unit 80 – Oak', 'وحدة مغسلة معلّقة بانيولينو 80 – بلوط', 'ארון כיור תלוי בניולינו 80 – אלון'],
            'short' => [
                'A floating vanity unit in warm oak, with a ceramic basin and two soft-close drawers.',
                'وحدة مغسلة معلّقة بلون البلوط الدافئ، مع مغسلة سيراميك ودرجين بإغلاق هادئ.',
                'ארון כיור תלוי באלון חם, עם כיור קרמי ושתי מגירות בטריקה שקטה.',
            ],
            'specs' => [
                'Dimensions' => ['80 × 46 × 50 cm', '80 × 46 × 50 سم', '80 × 46 × 50 ס״מ'],
                'Material' => ['Moisture-resistant MDF, oak veneer', 'MDF مقاوم للرطوبة بقشرة بلوط', 'MDF עמיד ללחות בציפוי אלון'],
                'Includes' => ['Ceramic basin, two soft-close drawers', 'مغسلة سيراميك ودرجان بإغلاق هادئ', 'כיור קרמי ושתי מגירות בטריקה שקטה'],
                'Warranty' => ['2 years', 'سنتان', 'שנתיים'],
            ],
        ],
        [
            'category' => 'Kitchen Sinks', 'brand' => 'Arvento', 'sku' => 'ARV-KS-8650', 'featured' => false,
            'name' => ['Arvento Granite Kitchen Sink 86 × 50', 'مجلى مطبخ جرانيت أرفينتو 86 × 50', 'כיור מטבח גרניט ארוונטו 86 × 50'],
            'short' => [
                'A granite-composite sink with a large bowl and a drainer, resistant to scratches, heat and stains.',
                'مجلى من الجرانيت المركّب بحوض كبير ومصفاة، مقاوم للخدوش والحرارة والبقع.',
                'כיור מגרניט מרוכב עם קערה גדולה ומשטח ייבוש, עמיד בפני שריטות, חום וכתמים.',
            ],
            'specs' => [
                'Dimensions' => ['86 × 50 cm', '86 × 50 سم', '86 × 50 ס״מ'],
                'Material' => ['Granite composite', 'جرانيت مركّب', 'גרניט מרוכב'],
                'Color' => ['Anthracite', 'أنثراسايت', 'אנתרציט'],
                'Installation' => ['Top-mount or undermount', 'تركيب علوي أو سفلي', 'התקנה עילית או תחתונה'],
                'Warranty' => ['10 years', '10 سنوات', '10 שנים'],
            ],
        ],
        [
            'category' => 'Bathroom Accessories', 'brand' => 'Aquaro', 'sku' => 'AQR-AS-LN04', 'featured' => false,
            'name' => ['Aquaro Linea Accessory Set (4 pieces)', 'طقم إكسسوارات أكوارو لينيا (4 قطع)', 'סט אביזרים אקוורו ליניאה (4 חלקים)'],
            'short' => [
                'Matching bathroom accessories in brushed brass to complete the Linea mixers.',
                'إكسسوارات حمام متناسقة بلون نحاسي مصقول تكمل خلاطات لينيا.',
                'אביזרי אמבטיה תואמים בפליז מוברש, להשלמת ברזי ליניאה.',
            ],
            'specs' => [
                'Includes' => ['Towel rail, towel ring, robe hook, paper holder', 'علاقة مناشف، حلقة مناشف، علاقة روب، حامل ورق', 'מתלה מגבות, טבעת מגבת, וו לחלוק, מחזיק נייר'],
                'Finish' => ['Brushed brass', 'نحاسي مصقول', 'פליז מוברש'],
                'Material' => ['Solid brass', 'نحاس صلب', 'פליז מלא'],
                'Installation' => ['Wall-mounted', 'على الجدار', 'על הקיר'],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Category::query()->whereTranslation('name', self::ROOT, 'en')->exists()) {
            return;
        }

        $brands = $this->createBrands();

        $categoriesByName = [];
        $this->createNodes(self::TREE, null, (int) Category::query()->whereNull('parent_id')->max('sort_order'), $categoriesByName);

        $root = $categoriesByName[self::ROOT];
        $root->addMedia($this->bathroomTexture([214, 208, 199], [92, 86, 79], 21))
            ->usingFileName('sanitary-ware.jpg')
            ->toMediaCollection(Category::IMAGE_COLLECTION);

        $positions = [];

        foreach (self::PRODUCTS as $data) {
            $positions[$data['category']] = ($positions[$data['category']] ?? 0) + 1;
            $this->createProduct($categoriesByName[$data['category']], $brands[$data['brand']], $data, $positions[$data['category']]);
        }

        $this->createSlide($root);
    }

    /**
     * @return array<string, Brand> The brands keyed by name.
     */
    private function createBrands(): array
    {
        $brands = [];

        foreach (self::BRANDS as $position => [$name, $country, $descriptions]) {
            $brands[$name] = Brand::query()->where('slug', Str::slug($name))->first() ?? Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'country' => $country,
                'status' => true,
                'sort_order' => $position + 1,
                'en' => ['description' => $descriptions[0]],
                'ar' => ['description' => $descriptions[1]],
                'he' => ['description' => $descriptions[2]],
            ]);
        }

        return $brands;
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @param  array<string, Category>  $categoriesByName
     */
    private function createNodes(array $nodes, ?Category $parent, int $offset, array &$categoriesByName): void
    {
        foreach ($nodes as $position => [$english, $arabic, $hebrew, $children]) {
            $category = Category::create([
                'parent_id' => $parent?->id,
                'status' => true,
                'sort_order' => $offset + $position + 1,
                'en' => ['name' => $english, 'slug' => Slug::make($english, 'en'), 'description' => "The {$english} collection, selected by Nasaq from leading international brands."],
                'ar' => ['name' => $arabic, 'slug' => Slug::make($arabic, 'ar'), 'description' => "مجموعة {$arabic} المختارة من نُسق من أفضل العلامات العالمية."],
                'he' => ['name' => $hebrew, 'slug' => Slug::make($hebrew, 'he'), 'description' => "קולקציית {$hebrew} של נסק, מהמותגים הבינלאומיים המובילים."],
            ]);

            $categoriesByName[$english] = $category;
            $this->createNodes($children, $category, 0, $categoriesByName);
        }
    }

    /**
     * @param  array{category: string, brand: string, sku: string, featured: bool, name: array{0: string, 1: string, 2: string}, short: array{0: string, 1: string, 2: string}, specs: array<string, array{0: string, 1: string, 2: string}>}  $data
     */
    private function createProduct(Category $category, Brand $brand, array $data, int $position): void
    {
        [$englishName, $arabicName, $hebrewName] = $data['name'];

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'sku' => $data['sku'],
            'status' => true,
            'featured' => $data['featured'],
            'sort_order' => $position,
            'en' => [
                'name' => $englishName,
                'slug' => Slug::make($englishName, 'en'),
                'short_description' => $data['short'][0],
                'description' => "Supplied with the manufacturer’s warranty and installation guidance from our team.\nVisit our showroom to see the finish and quality up close.",
            ],
            'ar' => [
                'name' => $arabicName,
                'slug' => Slug::make($arabicName, 'ar'),
                'short_description' => $data['short'][1],
                'description' => "يُورَّد بضمان الشركة المصنّعة مع إرشادات التركيب من فريقنا.\nزر معرضنا لمشاهدة التشطيب والجودة عن قرب.",
            ],
            'he' => [
                'name' => $hebrewName,
                'slug' => Slug::make($hebrewName, 'he'),
                'short_description' => $data['short'][2],
                'description' => "מסופק עם אחריות היצרן והנחיות התקנה מהצוות שלנו.\nבקרו באולם התצוגה כדי לראות את הגימור והאיכות מקרוב.",
            ],
        ]);

        $specifications = [...$data['specs'], 'Country of origin' => self::COUNTRIES[$brand->country]];

        foreach (array_keys($specifications) as $index => $label) {
            [$valueEn, $valueAr, $valueHe] = $specifications[$label];
            [$labelAr, $labelHe] = self::LABELS[$label];

            $product->specifications()->create([
                'sort_order' => $index + 1,
                'en' => ['label' => $label, 'value' => $valueEn],
                'ar' => ['label' => $labelAr, 'value' => $valueAr],
                'he' => ['label' => $labelHe, 'value' => $valueHe],
            ]);
        }
    }

    private function createSlide(Category $root): void
    {
        $slide = Slide::create([
            'status' => true,
            'sort_order' => (int) Slide::query()->max('sort_order') + 1,
            'link_type' => SlideLinkType::Category,
            'category_id' => $root->id,
            'en' => ['eyebrow' => 'New department', 'title' => 'Sanitary ware with a global signature', 'text' => 'Wall-hung toilets, basins, mixers and shower systems from leading European brands, all in one showroom.', 'button_label' => 'Discover sanitary ware'],
            'ar' => ['eyebrow' => 'قسم جديد', 'title' => 'أدوات صحية بتوقيع عالمي', 'text' => 'مراحيض معلّقة ومغاسل وخلاطات وأنظمة دش من أفضل العلامات الأوروبية، في معرض واحد.', 'button_label' => 'اكتشف الأدوات الصحية'],
            'he' => ['eyebrow' => 'מחלקה חדשה', 'title' => 'כלים סניטריים בחתימה עולמית', 'text' => 'אסלות תלויות, כיורים, ברזים ומערכות מקלחת של המותגים האירופיים המובילים, באולם תצוגה אחד.', 'button_label' => 'לגלות את הכלים הסניטריים'],
        ]);

        $slide->addMedia($this->bathroomTexture([118, 124, 128], [46, 44, 42], 31))
            ->usingFileName("slide-{$slide->id}.jpg")
            ->toMediaCollection(Slide::IMAGE_COLLECTION);
    }
}
