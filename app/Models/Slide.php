<?php

namespace App\Models;

use App\Enums\SlideLinkType;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\SlideFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A slide of the home page slider.
 */
#[Fillable(['link_type', 'category_id', 'status', 'sort_order'])]
class Slide extends Model implements HasMedia, TranslatableContract
{
    /** @use HasFactory<SlideFactory> */
    use HasFactory, InteractsWithMedia, Translatable;

    public const string IMAGE_COLLECTION = 'slide_image';

    /**
     * Optional portrait image for phones; the wide image is cropped otherwise.
     */
    public const string MOBILE_IMAGE_COLLECTION = 'slide_image_mobile';

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['eyebrow', 'title', 'text', 'button_label', 'button_url'];

    protected $attributes = [
        'link_type' => 'none',
        'status' => true,
        'sort_order' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'link_type' => SlideLinkType::class,
            'category_id' => 'integer',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $images = ['image/jpeg', 'image/png', 'image/webp'];

        $this->addMediaCollection(self::IMAGE_COLLECTION)->singleFile()->acceptsMimeTypes($images);
        $this->addMediaCollection(self::MOBILE_IMAGE_COLLECTION)->singleFile()->acceptsMimeTypes($images);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 640, 360)
            ->format('webp')
            ->performOnCollections(self::IMAGE_COLLECTION, self::MOBILE_IMAGE_COLLECTION);

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 2560, 2560)
            ->format('webp')
            ->quality(82)
            ->performOnCollections(self::IMAGE_COLLECTION, self::MOBILE_IMAGE_COLLECTION);
    }
}
