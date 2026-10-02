<?php

namespace App\Models;

use App\Services\Catalog\CategoryTree;
use App\Services\Catalog\CategoryTreeService;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\CategoryFactory;
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

/**
 * A node of the unlimited-depth category tree.
 *
 * Only the direct parent/children relations live here; tree-wide questions
 * (ancestors, descendants, depth, paths, visibility) are answered by
 * {@see CategoryTree}, which is built once from a single cached query.
 */
#[Fillable(['parent_id', 'status', 'sort_order'])]
class Category extends Model implements HasMedia, TranslatableContract
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, InteractsWithMedia, Translatable;

    public const string IMAGE_COLLECTION = 'category_image';

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug', 'description', 'seo_title', 'seo_description'];

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
            'parent_id' => 'integer',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Registered after Translatable's own "saved" listener, so translations are already stored.
        static::saved(fn () => app(CategoryTreeService::class)->flush());
        static::deleted(fn () => app(CategoryTreeService::class)->flush());
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Products assigned directly to this category (not to its descendants).
     *
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
    protected function roots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 480, 360)
            ->format('webp')
            ->performOnCollections(self::IMAGE_COLLECTION);

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->performOnCollections(self::IMAGE_COLLECTION);
    }
}
