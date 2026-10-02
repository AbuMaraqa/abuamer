<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['category_id', 'sku', 'status', 'featured', 'sort_order'])]
class Product extends Model implements HasMedia, TranslatableContract
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia, Translatable;

    public const string MAIN_IMAGE_COLLECTION = 'product_main_image';

    public const string GALLERY_COLLECTION = 'product_gallery';

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug', 'short_description', 'description', 'seo_title', 'seo_description'];

    protected $attributes = [
        'status' => true,
        'featured' => false,
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
            'category_id' => 'integer',
            'status' => 'boolean',
            'featured' => 'boolean',
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

    /**
     * @return HasMany<ProductSpecification, $this>
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order')->orderBy('id');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', true);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('featured', true);
    }

    /**
     * Limit the query to products assigned to any of the given categories.
     *
     * @param  list<int>  $categoryIds
     */
    #[Scope]
    protected function inCategories(Builder $query, array $categoryIds): void
    {
        $query->whereIn('category_id', $categoryIds);
    }

    /**
     * Match the term against the SKU or the product name in any language.
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $pattern = '%'.addcslashes($term, '\\%_').'%';

        $query->where(function (Builder $query) use ($pattern) {
            $query->where('sku', 'like', $pattern)
                ->orWhereHas('translations', fn (Builder $translations) => $translations->where('name', 'like', $pattern));
        });
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MAIN_IMAGE_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection(self::GALLERY_COLLECTION)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 600)
            ->format('webp')
            ->performOnCollections(self::MAIN_IMAGE_COLLECTION, self::GALLERY_COLLECTION);

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1800, 1800)
            ->format('webp')
            ->performOnCollections(self::MAIN_IMAGE_COLLECTION, self::GALLERY_COLLECTION);
    }
}
