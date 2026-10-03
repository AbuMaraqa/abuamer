<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A manufacturer, such as the maker of a range of mixers or wash basins.
 *
 * A hidden brand keeps its products on the website, but has no page and is not
 * named on them.
 */
#[Fillable(['name', 'slug', 'country', 'website', 'status', 'sort_order'])]
class Brand extends Model implements HasMedia, TranslatableContract
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    public const string LOGO_COLLECTION = 'brand_logo';

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['description'];

    protected $attributes = [
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
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Limit the query to brands with at least one active product in the given categories.
     *
     * @param  list<int>  $categoryIds
     */
    #[Scope]
    protected function withProductsIn(Builder $query, array $categoryIds): void
    {
        $query->whereHas('products', fn (Builder $products) => $products->active()->inCategories($categoryIds));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::LOGO_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // WebP keeps the transparency of PNG logos.
        $this->addMediaConversion('thumb')
            ->fit(Fit::Max, 480, 240)
            ->format('webp')
            ->performOnCollections(self::LOGO_COLLECTION);
    }
}
