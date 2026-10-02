<?php

namespace App\Http\Controllers;

use App\Enums\HighlightType;
use App\Http\Resources\CompanyHighlightResource;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Models\CompanyHighlight;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    /**
     * Show the about page: introduction, story, vision, mission, values, reasons and gallery.
     */
    public function __invoke(): Response
    {
        $highlights = CompanyHighlight::query()->with('translations')->ordered()->get();

        $byType = fn (HighlightType $type) => CompanyHighlightResource::collection($highlights->where('type', $type)->values());

        return Inertia::render('About', [
            'company' => CompanyResource::make(Company::current()),
            'values' => $byType(HighlightType::Value),
            'features' => $byType(HighlightType::Feature),
            'statistics' => $byType(HighlightType::Statistic),
        ]);
    }
}
