<?php

namespace App\Models;

use App\Enums\HighlightType;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\CompanyHighlightFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A company value, a reason to choose the company, or a statistic.
 */
#[Fillable(['type', 'icon', 'value', 'sort_order'])]
class CompanyHighlight extends Model implements TranslatableContract
{
    /** @use HasFactory<CompanyHighlightFactory> */
    use HasFactory, Translatable;

    /**
     * Icons available for "why choose us" features (rendered by the Vue Icon component).
     */
    public const array FEATURE_ICONS = ['gem', 'award', 'ruler', 'truck', 'shield', 'sparkles', 'layers', 'leaf', 'palette', 'users'];

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'description'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => HighlightType::class,
            'sort_order' => 'integer',
        ];
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
