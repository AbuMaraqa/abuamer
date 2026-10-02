<?php

use App\Models\CompanyHighlight;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the about page with each list of highlights', function () {
    CompanyHighlight::factory()->count(2)->create();
    CompanyHighlight::factory()->feature()->create();
    CompanyHighlight::factory()->statistic()->create();

    $response = $this->get(route('about'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('About')
        ->has('values', 2)
        ->has('features', 1)
        ->has('statistics', 1)
        ->where('values.0.title', 'الجودة'));
});
