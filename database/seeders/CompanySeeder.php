<?php

namespace Database\Seeders;

use App\Enums\HighlightType;
use App\Models\Company;
use App\Models\CompanyHighlight;
use App\Settings\ContactSettings;
use App\Settings\SeoSettings;
use App\Settings\SocialSettings;
use Illuminate\Database\Seeder;

/**
 * Placeholder company content and contact details for local development.
 * The real texts are entered from the control panel.
 */
class CompanySeeder extends Seeder
{
    /**
     * The company texts in Arabic and English.
     *
     * @var array<string, array<string, string>>
     */
    private const array TEXTS = [
        'ar' => [
            'name' => 'نُسق للبلاط والأدوات الصحية',
            'tagline' => 'بلاط ورخام وأدوات صحية لمساحات استثنائية',
            'hero_title' => 'تفاصيل معمارية تصنع الفرق',
            'hero_subtitle' => 'من البورسلان والرخام إلى المغاسل والخلاطات وأنظمة الدش، مجموعات مختارة من أفضل العلامات العالمية للمشاريع السكنية والتجارية.',
            'introduction' => 'منذ تأسيسها، تقدم نُسق حلولًا متكاملة للأرضيات والجدران والحمامات والمطابخ، تجمع بين البلاط والرخام الفاخر والأدوات الصحية من أفضل العلامات العالمية.',
            'story' => "بدأت نُسق بمعرض صغير للبلاط وشغف كبير بالتفاصيل.\n\nومع نمو مشاريع عملائنا أضفنا قسمًا متكاملًا للأدوات الصحية، ليجد المصمم والمقاول وصاحب المنزل كل ما يحتاجه تحت سقف واحد: من بلاط الأرضيات والجدران إلى المراحيض المعلّقة والمغاسل والخلاطات وأنظمة الدش، من الفلل الخاصة إلى الفنادق والمشاريع التجارية الكبرى.",
            'vision' => 'أن نكون الوجهة الأولى للبلاط والأدوات الصحية الفاخرة في المنطقة.',
            'mission' => 'تقديم منتجات عالية الجودة من علامات موثوقة، وخدمة استشارية تساعد عملاءنا على تنسيق كل تفاصيل مساحاتهم.',
            'cta_title' => 'لنصمم مساحتك القادمة معًا',
            'cta_text' => 'زر معرضنا أو تواصل معنا، وسيساعدك فريقنا في اختيار البلاط والأدوات الصحية المناسبة لمشروعك.',
        ],
        'en' => [
            'name' => 'Nasaq Tiles & Sanitary Ware',
            'tagline' => 'Tiles, marble and sanitary ware for exceptional spaces',
            'hero_title' => 'Architectural details that make the difference',
            'hero_subtitle' => 'From porcelain and marble to basins, mixers and shower systems: curated collections from leading international brands for residential and commercial projects.',
            'introduction' => 'Since its founding, Nasaq has delivered complete solutions for floors, walls, bathrooms and kitchens, bringing premium tiles and marble together with sanitary ware from the world’s leading brands.',
            'story' => "Nasaq started as a small tile showroom with a great passion for detail.\n\nAs our clients’ projects grew, we added a complete sanitary ware department, so designers, contractors and homeowners find everything under one roof: from floor and wall tiles to wall-hung toilets, basins, mixers and shower systems, for private villas, hotels and large commercial projects alike.",
            'vision' => 'To be the first destination for premium tiles and sanitary ware in the region.',
            'mission' => 'To offer high-quality products from trusted brands, and expert advice that helps our clients bring every detail of their spaces together.',
            'cta_title' => 'Let’s design your next space together',
            'cta_text' => 'Visit our showroom or get in touch, and our team will help you choose the right tiles and sanitary ware for your project.',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::current()->fill([
            'founded_year' => 2008,
            'ar' => self::TEXTS['ar'],
            'en' => self::TEXTS['en'],
        ])->save();

        $highlights = [
            [HighlightType::Value, null, null, ['الجودة', 'Quality'], ['نختار خاماتنا من أفضل المصانع العالمية.', 'We source from the finest manufacturers worldwide.']],
            [HighlightType::Value, null, null, ['الأصالة', 'Integrity'], ['نلتزم بالشفافية في كل تعامل.', 'We are transparent in every interaction.']],
            [HighlightType::Value, null, null, ['الابتكار', 'Innovation'], ['نواكب أحدث اتجاهات التصميم.', 'We follow the latest design trends.']],
            [HighlightType::Feature, 'gem', null, ['خامات فاخرة', 'Premium materials'], ['بورسلان ورخام وأدوات صحية بمواصفات عالمية.', 'Porcelain, marble and sanitary ware to international standards.']],
            [HighlightType::Feature, 'palette', null, ['تشكيلة واسعة', 'Wide selection'], ['مئات التصاميم من البلاط والمغاسل والخلاطات.', 'Hundreds of tiles, basins and mixers to choose from.']],
            [HighlightType::Feature, 'users', null, ['استشارات متخصصة', 'Expert advice'], ['فريق يساعدك على تنسيق البلاط مع الأدوات الصحية المناسبة.', 'A team that helps you match tiles with the right sanitary ware.']],
            [HighlightType::Feature, 'truck', null, ['توصيل موثوق', 'Reliable delivery'], ['توصيل منظم في الوقت المحدد.', 'Organised, on-time delivery.']],
            [HighlightType::Feature, 'shield', null, ['ضمان الجودة', 'Quality guarantee'], ['منتجات أصلية بضمان الشركة المصنّعة.', 'Genuine products with the manufacturer’s warranty.']],
            [HighlightType::Feature, 'ruler', null, ['حلول للمشاريع', 'Project solutions'], ['كميات وتوريدات للمشاريع الكبرى.', 'Volumes and supply for large projects.']],
            [HighlightType::Statistic, null, '15+', ['عامًا من الخبرة', 'Years of experience'], [null, null]],
            [HighlightType::Statistic, null, '1200+', ['منتج', 'Products'], [null, null]],
            [HighlightType::Statistic, null, '800+', ['مشروع منجز', 'Completed projects'], [null, null]],
            [HighlightType::Statistic, null, '5000+', ['عميل', 'Clients'], [null, null]],
        ];

        foreach ($highlights as $position => [$type, $icon, $value, $titles, $descriptions]) {
            CompanyHighlight::create([
                'type' => $type,
                'icon' => $icon,
                'value' => $value,
                'sort_order' => $position + 1,
                'ar' => ['title' => $titles[0], 'description' => $descriptions[0]],
                'en' => ['title' => $titles[1], 'description' => $descriptions[1]],
            ]);
        }

        app(ContactSettings::class)->fill([
            'phone' => '+966 11 000 0000',
            'mobile' => '+966 50 000 0000',
            'whatsapp' => '966500000000',
            'email' => 'info@nasaq.test',
            'address' => ['ar' => 'طريق الملك فهد، الرياض، المملكة العربية السعودية', 'en' => 'King Fahd Road, Riyadh, Saudi Arabia'],
            'working_hours' => ['ar' => 'السبت – الخميس، 9 صباحًا – 9 مساءً', 'en' => 'Saturday – Thursday, 9 am – 9 pm'],
            'form_recipient' => 'info@nasaq.test',
        ])->save();

        app(SocialSettings::class)->fill([
            'instagram' => 'https://instagram.com/nasaq',
            'facebook' => 'https://facebook.com/nasaq',
        ])->save();

        app(SeoSettings::class)->fill([
            'meta_title' => ['ar' => 'نُسق للبلاط والأدوات الصحية | بلاط ورخام وأدوات صحية فاخرة', 'en' => 'Nasaq Tiles & Sanitary Ware | Premium tiles, marble and sanitary ware'],
            'meta_description' => [
                'ar' => 'اكتشف مجموعات نُسق من البورسلان والرخام وبلاط الجدران والأرضيات، والأدوات الصحية والخلاطات وأنظمة الدش من أفضل العلامات العالمية.',
                'en' => 'Discover Nasaq’s collections of porcelain, marble, wall and floor tiles, plus sanitary ware, mixers and shower systems from leading international brands.',
            ],
        ])->save();
    }
}
