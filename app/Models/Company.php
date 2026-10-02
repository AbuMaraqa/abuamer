<?php

namespace App\Models;

use App\Services\CompanyProfile;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The company profile shown on the home and about pages: a single row.
 */
#[Fillable(['founded_year'])]
class Company extends Model implements HasMedia, TranslatableContract
{
    use InteractsWithMedia, Translatable;

    public const string LOGO_COLLECTION = 'logo';

    public const string FAVICON_COLLECTION = 'favicon';

    public const string HERO_COLLECTION = 'hero_image';

    public const string ABOUT_COLLECTION = 'about_image';

    public const string GALLERY_COLLECTION = 'gallery';

    /**
     * @var list<string>
     */
    public array $translatedAttributes = [
        'name', 'tagline', 'hero_title', 'hero_subtitle', 'introduction',
        'story', 'vision', 'mission', 'cta_title', 'cta_text',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'founded_year' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => app(CompanyProfile::class)->forget());
    }

    /**
     * The company profile (loaded once per request), created on first use if the row is missing.
     */
    public static function current(): self
    {
        return app(CompanyProfile::class)->get();
    }

    public function registerMediaCollections(): void
    {
        $images = ['image/jpeg', 'image/png', 'image/webp'];

        $this->addMediaCollection(self::LOGO_COLLECTION)->singleFile()->acceptsMimeTypes(['image/png', 'image/webp']);
        $this->addMediaCollection(self::FAVICON_COLLECTION)->singleFile()->acceptsMimeTypes(['image/png']);
        $this->addMediaCollection(self::HERO_COLLECTION)->singleFile()->acceptsMimeTypes($images);
        $this->addMediaCollection(self::ABOUT_COLLECTION)->singleFile()->acceptsMimeTypes($images);
        $this->addMediaCollection(self::GALLERY_COLLECTION)->acceptsMimeTypes($images);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 600)
            ->format('webp')
            ->performOnCollections(self::HERO_COLLECTION, self::ABOUT_COLLECTION, self::GALLERY_COLLECTION);

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 2400, 2400)
            ->format('webp')
            ->performOnCollections(self::HERO_COLLECTION, self::ABOUT_COLLECTION, self::GALLERY_COLLECTION);
    }
}
