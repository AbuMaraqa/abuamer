<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the branded error page for a missing page in production', function () {
    config(['app.debug' => false]);

    $response = $this->get(route('products.category', ['path' => 'does-not-exist']));

    $response->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', 404)
            ->where('homeUrl', route('home'))
            ->has('site.name'));
});

it('keeps the detailed error page in debug mode', function () {
    config(['app.debug' => true]);

    $response = $this->get(route('products.category', ['path' => 'does-not-exist']));

    $response->assertNotFound();
    expect($response->headers->get('X-Inertia'))->toBeNull()
        ->and($response->getContent())->not->toContain('"component":"Error"');
});
