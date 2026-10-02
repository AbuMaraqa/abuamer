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
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::current()->fill([
            'founded_year' => 2008,
            'ar' => [
                'name' => 'نُسق للبلاط والسيراميك',
                'tagline' => 'بلاط فاخر لمساحات استثنائية',
                'hero_title' => 'تفاصيل معمارية تصنع الفرق',
                'hero_subtitle' => 'مجموعات مختارة من البورسلان والسيراميك والأحجار الطبيعية للمشاريع السكنية والتجارية.',
                'introduction' => 'منذ تأسيسها، تقدم نُسق للبلاط والسيراميك حلولًا متكاملة للأرضيات والجدران تجمع بين الجودة العالية والتصميم المعاصر.',
                'story' => "بدأت نُسق بمعرض صغير وشغف كبير بالتفاصيل.\n\nواليوم نعمل مع مصممين ومقاولين وأصحاب منازل لاختيار البلاط المناسب لكل مساحة، من الفلل الخاصة إلى الفنادق والمشاريع التجارية الكبرى.",
                'vision' => 'أن نكون الوجهة الأولى للبلاط الفاخر في المنطقة.',
                'mission' => 'تقديم منتجات عالية الجودة وخدمة استشارية تساعد عملاءنا على تحقيق رؤيتهم المعمارية.',
                'cta_title' => 'لنصمم مساحتك القادمة معًا',
                'cta_text' => 'زر معرضنا أو تواصل معنا، وسيساعدك فريقنا في اختيار البلاط المناسب لمشروعك.',
            ],
            'en' => [
                'name' => 'Nasaq Tiles & Ceramics',
                'tagline' => 'Premium tiles for exceptional spaces',
                'hero_title' => 'Architectural details that make the difference',
                'hero_subtitle' => 'Curated collections of porcelain, ceramic and natural stone for residential and commercial projects.',
                'introduction' => 'Since its founding, Nasaq Tiles & Ceramics has delivered complete flooring and wall solutions that combine high quality with contemporary design.',
                'story' => "Nasaq started as a small showroom with a great passion for detail.\n\nToday we work with designers, contractors and homeowners to choose the right tiles for every space, from private villas to hotels and large commercial projects.",
                'vision' => 'To be the first destination for premium tiles in the region.',
                'mission' => 'To offer high-quality products and expert advice that help our clients realise their architectural vision.',
                'cta_title' => 'Let’s design your next space together',
                'cta_text' => 'Visit our showroom or get in touch, and our team will help you choose the right tiles for your project.',
            ],
        ])->save();

        $highlights = [
            [HighlightType::Value, null, null, ['الجودة', 'Quality'], ['نختار خاماتنا من أفضل المصانع العالمية.', 'We source from the finest manufacturers worldwide.']],
            [HighlightType::Value, null, null, ['الأصالة', 'Integrity'], ['نلتزم بالشفافية في كل تعامل.', 'We are transparent in every interaction.']],
            [HighlightType::Value, null, null, ['الابتكار', 'Innovation'], ['نواكب أحدث اتجاهات التصميم.', 'We follow the latest design trends.']],
            [HighlightType::Feature, 'gem', null, ['خامات فاخرة', 'Premium materials'], ['بورسلان وسيراميك بمواصفات عالمية.', 'Porcelain and ceramics to international standards.']],
            [HighlightType::Feature, 'palette', null, ['تشكيلة واسعة', 'Wide selection'], ['مئات التصاميم والمقاسات والتشطيبات.', 'Hundreds of designs, sizes and finishes.']],
            [HighlightType::Feature, 'users', null, ['استشارات متخصصة', 'Expert advice'], ['فريق يساعدك على اختيار الأنسب لمشروعك.', 'A team that helps you choose the best fit.']],
            [HighlightType::Feature, 'truck', null, ['توصيل موثوق', 'Reliable delivery'], ['توصيل منظم في الوقت المحدد.', 'Organised, on-time delivery.']],
            [HighlightType::Feature, 'shield', null, ['ضمان الجودة', 'Quality guarantee'], ['منتجات مضمونة ومطابقة للمواصفات.', 'Guaranteed products that meet specifications.']],
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
            'meta_title' => ['ar' => 'نُسق للبلاط والسيراميك | بلاط وبورسلان فاخر', 'en' => 'Nasaq Tiles & Ceramics | Premium tiles and porcelain'],
            'meta_description' => [
                'ar' => 'اكتشف مجموعات نُسق من البورسلان والسيراميك وبلاط الجدران والأرضيات للمشاريع السكنية والتجارية.',
                'en' => 'Discover Nasaq’s collections of porcelain, ceramic, wall and floor tiles for residential and commercial projects.',
            ],
        ])->save();
    }
}
