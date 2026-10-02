<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyHighlight;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\Slide;
use App\Settings\ContactSettings;
use App\Settings\SeoSettings;
use App\Support\Slug;
use Astrotomic\Translatable\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Hebrew texts for the demo content of the other seeders, matched by their English
 * texts. Only missing Hebrew is added, so it can also run on an existing database:
 * php artisan db:seed --class=HebrewContentSeeder
 */
class HebrewContentSeeder extends Seeder
{
    private const string LOCALE = 'he';

    private const array COMPANY = [
        'name' => 'נסק אריחים וקרמיקה',
        'tagline' => 'אריחי יוקרה לחללים יוצאי דופן',
        'hero_title' => 'פרטים אדריכליים שעושים את ההבדל',
        'hero_subtitle' => 'קולקציות נבחרות של פורצלן, קרמיקה ואבן טבעית לפרויקטים למגורים ולמסחר.',
        'introduction' => 'מאז הקמתה, נסק אריחים וקרמיקה מציעה פתרונות שלמים לרצפות ולקירות, המשלבים איכות גבוהה ועיצוב עכשווי.',
        'story' => "נסק התחילה כאולם תצוגה קטן עם תשוקה גדולה לפרטים.\n\nהיום אנחנו עובדים עם מעצבים, קבלנים ובעלי בתים כדי לבחור את האריחים המתאימים לכל חלל, מווילות פרטיות ועד מלונות ופרויקטים מסחריים גדולים.",
        'vision' => 'להיות היעד הראשון לאריחי יוקרה באזור.',
        'mission' => 'להציע מוצרים איכותיים וייעוץ מקצועי שעוזרים ללקוחותינו להגשים את החזון האדריכלי שלהם.',
        'cta_title' => 'בואו נעצב יחד את החלל הבא שלכם',
        'cta_text' => 'בקרו באולם התצוגה שלנו או צרו קשר, והצוות שלנו יעזור לכם לבחור את האריחים המתאימים לפרויקט.',
    ];

    /**
     * Highlight title and description, keyed by the English title.
     *
     * @var array<string, array{0: string, 1: string|null}>
     */
    private const array HIGHLIGHTS = [
        'Quality' => ['איכות', 'אנחנו בוחרים חומרים מהמפעלים הטובים בעולם.'],
        'Integrity' => ['יושרה', 'אנחנו שקופים בכל התקשרות.'],
        'Innovation' => ['חדשנות', 'אנחנו עוקבים אחר מגמות העיצוב העדכניות.'],
        'Premium materials' => ['חומרים יוקרתיים', 'פורצלן וקרמיקה בתקנים בינלאומיים.'],
        'Wide selection' => ['מבחר רחב', 'מאות עיצובים, מידות וגימורים.'],
        'Expert advice' => ['ייעוץ מקצועי', 'צוות שעוזר לכם לבחור את המתאים ביותר לפרויקט.'],
        'Reliable delivery' => ['אספקה אמינה', 'משלוחים מסודרים ובזמן.'],
        'Quality guarantee' => ['אחריות לאיכות', 'מוצרים באחריות שעומדים במפרט.'],
        'Project solutions' => ['פתרונות לפרויקטים', 'כמויות ואספקה לפרויקטים גדולים.'],
        'Years of experience' => ['שנות ניסיון', null],
        'Products' => ['מוצרים', null],
        'Completed projects' => ['פרויקטים שהושלמו', null],
        'Clients' => ['לקוחות', null],
    ];

    /**
     * Category names keyed by the English name.
     *
     * @var array<string, string>
     */
    private const array CATEGORIES = [
        'Tiles' => 'אריחים',
        'Porcelain' => 'פורצלן',
        'Marble Effect' => 'מראה שיש',
        'Calacatta' => 'קלקטה',
        'Gold' => 'זהב',
        'White' => 'לבן',
        'Carrara' => 'קררה',
        'Stone Effect' => 'מראה אבן',
        'Natural Stone' => 'אבן טבעית',
        'Slate' => 'צפחה',
        'Wood Effect' => 'מראה עץ',
        'Wall Tiles' => 'חיפוי קירות',
        'Kitchen' => 'מטבחים',
        'Bathroom' => 'חדרי רחצה',
        'Decorative' => 'דקורטיבי',
        'Floor Tiles' => 'אריחי רצפה',
        'Indoor' => 'פנים',
        'Living Room' => 'סלון',
        'Bedroom' => 'חדר שינה',
        'Outdoor' => 'חוץ',
        'Garden' => 'גינה',
        'Entrance' => 'כניסה',
    ];

    /**
     * Product name and finish, keyed by the English name.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const array PRODUCTS = [
        'Calacatta Gold 60x120' => ['קלקטה זהב 60×120', 'מבריק'],
        'Calacatta Gold 120x120' => ['קלקטה זהב 120×120', 'מבריק'],
        'Calacatta White 60x120' => ['קלקטה לבן 60×120', 'מט'],
        'Carrara Classic 60x60' => ['קררה קלאסי 60×60', 'סאטן'],
        'Slate Graphite 60x120' => ['צפחה גרפיט 60×120', 'מחוספס'],
        'Oak Natural 20x120' => ['אלון טבעי 20×120', 'מט'],
        'Zellige Sage 10x10' => ['זליג׳ מרווה 10×10', 'מבריק'],
        'Travertine Outdoor 60x90' => ['טרוורטין חוץ 60×90', 'נגד החלקה'],
    ];

    private const array SPECIFICATION_LABELS = [
        'Size' => 'מידה',
        'Thickness' => 'עובי',
        'Finish' => 'גימור',
        'Material' => 'חומר',
    ];

    private const array SPECIFICATION_VALUES = [
        'Porcelain' => 'פורצלן',
        'Polished' => 'מבריק',
        'Matte' => 'מט',
        'Satin' => 'סאטן',
        'Structured' => 'מחוספס',
        'Glossy' => 'מבריק',
        'Anti-slip' => 'נגד החלקה',
        ' cm' => ' ס״מ',
        ' mm' => ' מ״מ',
    ];

    /**
     * Slide texts keyed by the English title.
     *
     * @var array<string, array<string, string>>
     */
    private const array SLIDES = [
        'Calacatta marble with a golden touch' => ['eyebrow' => 'קולקציית 2026', 'title' => 'שיש קלקטה בנגיעה זהובה', 'text' => 'עורקים זהובים וחמים על רקע לבן בוהק, לחללים שמשדרים יוקרה.', 'button_label' => 'לגלות את הקולקציה'],
        'Porcelain without limits' => ['eyebrow' => 'פורמט גדול', 'title' => 'פורצלן ללא גבולות', 'text' => 'לוחות 120 × 120 ס״מ עם פוגות כמעט בלתי נראות, לחללים פתוחים ורציפים.', 'button_label' => 'לקולקציית הפורצלן'],
        'Floors made to last in the sun' => ['eyebrow' => 'לחללי חוץ', 'title' => 'רצפות שעומדות בשמש', 'text' => 'אריחים נגד החלקה, עמידים בפגעי מזג האוויר, לגינות ולכניסות.', 'button_label' => 'אריחי חוץ'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->translate(Company::current(), self::COMPANY);

        foreach (CompanyHighlight::query()->with('translations')->get() as $highlight) {
            $hebrew = self::HIGHLIGHTS[(string) $highlight->translate('en')?->title] ?? null;

            if ($hebrew !== null) {
                $this->translate($highlight, ['title' => $hebrew[0], 'description' => $hebrew[1]]);
            }
        }

        foreach (Category::query()->with('translations')->get() as $category) {
            $name = self::CATEGORIES[(string) $category->translate('en')?->name] ?? null;

            if ($name !== null) {
                $this->translate($category, [
                    'name' => $name,
                    'slug' => Slug::make($name, self::LOCALE),
                    'description' => "קולקציית {$name} של נסק אריחים וקרמיקה.",
                ]);
            }
        }

        foreach (Product::query()->with(['translations', 'specifications.translations'])->get() as $product) {
            $this->translateProduct($product);
        }

        foreach (Slide::query()->with('translations')->get() as $slide) {
            $texts = self::SLIDES[(string) $slide->translate('en')?->title] ?? null;

            if ($texts !== null) {
                $this->translate($slide, $texts);
            }
        }

        $this->translateSettings();
    }

    private function translateProduct(Product $product): void
    {
        $hebrew = self::PRODUCTS[(string) $product->translate('en')?->name] ?? null;

        if ($hebrew === null) {
            return;
        }

        [$name, $finish] = $hebrew;

        $this->translate($product, [
            'name' => $name,
            'slug' => Slug::make($name, self::LOCALE),
            'short_description' => "אריח פורצלן יוקרתי בגימור {$finish}, המעניק לכל חלל אופי אדריכלי.",
            'description' => "עשוי מפורצלן בצפיפות גבוהה, עמיד בפני שחיקה וכתמים.\nמתאים לחללי מגורים ולחללים מסחריים.",
        ]);

        $product->specifications->each(function (ProductSpecification $specification): void {
            $english = $specification->translate('en');

            $label = self::SPECIFICATION_LABELS[(string) $english?->label] ?? null;

            if ($label !== null) {
                $this->translate($specification, ['label' => $label, 'value' => strtr($english->value, self::SPECIFICATION_VALUES)]);
            }
        });
    }

    /**
     * Fill the Hebrew settings texts that are still empty.
     */
    private function translateSettings(): void
    {
        $contact = app(ContactSettings::class);
        $contact->address = $this->withHebrew($contact->address, 'דרך המלך פהד, ריאד, ערב הסעודית');
        $contact->working_hours = $this->withHebrew($contact->working_hours, 'שבת – חמישי, 9:00 – 21:00');
        $contact->save();

        $seo = app(SeoSettings::class);
        $seo->meta_title = $this->withHebrew($seo->meta_title, 'נסק אריחים וקרמיקה | אריחים ופורצלן יוקרתיים');
        $seo->meta_description = $this->withHebrew($seo->meta_description, 'גלו את קולקציות הפורצלן, הקרמיקה, חיפויי הקיר והרצפה של נסק לפרויקטים למגורים ולמסחר.');
        $seo->save();
    }

    /**
     * @param  array<string, string>  $values  Texts keyed by locale.
     * @return array<string, string>
     */
    private function withHebrew(array $values, string $hebrew): array
    {
        return [...$values, self::LOCALE => filled($values[self::LOCALE] ?? null) ? $values[self::LOCALE] : $hebrew];
    }

    /**
     * Add the Hebrew translation unless the model already has one.
     *
     * @param  Model&Translatable  $model
     * @param  array<string, string|null>  $attributes
     */
    private function translate(Model $model, array $attributes): void
    {
        if (! $model->hasTranslation(self::LOCALE)) {
            $model->fill([self::LOCALE => $attributes])->save();
        }
    }
}
