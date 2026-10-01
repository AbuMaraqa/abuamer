<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mcamara\LaravelLocalization\LaravelLocalization;

abstract class TestCase extends BaseTestCase
{
    /**
     * The locale the localized routes are registered for while the application boots.
     * Null registers them without a prefix, as for a request to a URL without a locale.
     */
    protected ?string $routingLocale = 'ar';

    protected function setUp(): void
    {
        // Localized routes are registered with the locale prefix of the current URL.
        // Console requests have no URL, so the prefix is forced before booting.
        putenv($this->routingLocale === null
            ? LaravelLocalization::ENV_ROUTE_KEY
            : LaravelLocalization::ENV_ROUTE_KEY.'='.$this->routingLocale);

        parent::setUp();

        // The root Blade view references Vite assets that only exist after a frontend build.
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv(LaravelLocalization::ENV_ROUTE_KEY);
    }

    /**
     * Re-boot the application with routes registered for another locale.
     * Records created before calling this method are discarded.
     */
    protected function useRoutingLocale(?string $locale): static
    {
        $this->routingLocale = $locale;

        $this->tearDown();
        $this->setUp();

        return $this;
    }
}
