<?php

namespace App\Services;

use App\Enums\HighlightType;
use App\Models\Company;
use App\Models\CompanyHighlight;
use Illuminate\Support\Facades\DB;

/**
 * Persists the complete state submitted by the company form.
 */
class CompanyService
{
    /**
     * @param  array<string, array<string, string|null>>  $translations
     * @param  array<string, list<array{id: int|null, icon: string|null, value: string|null, translations: array<string, array{title: string, description: string|null}>}>>  $highlights  Keyed by HighlightType value.
     */
    public function save(Company $company, ?int $foundedYear, array $translations, array $highlights): void
    {
        DB::transaction(function () use ($company, $foundedYear, $translations, $highlights) {
            $company->fill(['founded_year' => $foundedYear, ...$translations])->save();

            foreach ($highlights as $type => $items) {
                $this->syncHighlights(HighlightType::from($type), $items);
            }
        });
    }

    /**
     * Update kept highlights of one type in their new order, create new ones and delete the rest.
     *
     * @param  list<array{id: int|null, icon: string|null, value: string|null, translations: array<string, array{title: string, description: string|null}>}>  $items
     */
    private function syncHighlights(HighlightType $type, array $items): void
    {
        $existing = CompanyHighlight::query()->where('type', $type)->with('translations')->get()->keyBy('id');
        $keptIds = [];

        foreach ($items as $index => $item) {
            // Ids of another type (or unknown ids) are treated as new rows.
            $highlight = $existing->get($item['id'] ?? 0) ?? new CompanyHighlight(['type' => $type]);

            $highlight->fill([
                'icon' => $item['icon'],
                'value' => $item['value'],
                'sort_order' => $index + 1,
                ...$item['translations'],
            ])->save();

            $keptIds[] = $highlight->id;
        }

        CompanyHighlight::query()->where('type', $type)->whereKeyNot($keptIds)->delete();
    }
}
