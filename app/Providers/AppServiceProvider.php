<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use App\Settings\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    use LoadsTranslatedCachedRoutes;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        // Single resources and plain collections are sent as-is; paginated collections keep data/links/meta.
        JsonResource::withoutWrapping();

        // Localized routes are registered per locale, so they must be cached with
        // `php artisan route:trans:cache` instead of `php artisan route:cache`.
        RouteServiceProvider::loadCachedRoutesUsing(fn () => $this->loadCachedRoutes());

        Gate::before(fn (User $user): ?bool => $user->hasRole(Role::Admin) ? true : null);

        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinutes(10, 5)->by($request->ip()));

        $this->applyDefaultLocale();
    }

    /**
     * Use the default language chosen in the control panel. It must be applied before the
     * localized routes are registered, which is when mcamara reads "app.locale".
     */
    private function applyDefaultLocale(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $locale = $this->app->make(SiteSettings::class)->default_locale;
        } catch (Throwable) {
            // Settings are not migrated yet; keep the configured locale.
            return;
        }

        if (in_array($locale, config('translatable.locales'), true)) {
            config(['app.locale' => $locale]);
        }
    }
}
