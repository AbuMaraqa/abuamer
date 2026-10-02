<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Container\Attributes\Scoped;

/**
 * Loads the company profile once per request: the page, the shared layout data and
 * the SEO metadata all need it.
 */
#[Scoped]
class CompanyProfile
{
    private ?Company $company = null;

    public function get(): Company
    {
        return $this->company ??= Company::query()->with(['translations', 'media'])->find(1)
            ?? Company::query()->forceCreate(['id' => 1]);
    }

    public function forget(): void
    {
        $this->company = null;
    }
}
