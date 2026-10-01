<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Pages whose URL differs per language (translated slugs) override
     * `localeUrls` with their own alternate URLs.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'locale' => [
                'current' => LaravelLocalization::getCurrentLocale(),
                'direction' => LaravelLocalization::getCurrentLocaleDirection(),
                'supported' => collect(LaravelLocalization::getLocalesOrder())
                    ->map(fn (array $properties, string $code): array => [
                        'code' => $code,
                        'native' => $properties['native'],
                    ])
                    ->values(),
            ],
            'localeUrls' => fn (): array => $this->localeUrls(),
            'translations' => Inertia::once(fn (): array => $this->translations()),
            'auth' => [
                'user' => fn (): ?array => $this->authenticatedUser($request->user()),
            ],
        ];
    }

    /**
     * The current page's URL in every supported locale.
     *
     * @return array<string, string>
     */
    private function localeUrls(): array
    {
        return collect(LaravelLocalization::getSupportedLanguagesKeys())
            ->mapWithKeys(fn (string $locale): array => [
                $locale => LaravelLocalization::getLocalizedURL($locale, null, [], true),
            ])
            ->all();
    }

    /**
     * The JSON interface strings for the current locale.
     *
     * @return array<string, string>
     */
    private function translations(): array
    {
        $path = lang_path(app()->getLocale().'.json');

        return is_file($path) ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR) : [];
    }

    /**
     * @return array{id: int, name: string, email: string, can: array<string, bool>}|null
     */
    private function authenticatedUser(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'can' => collect(Permission::cases())
                ->mapWithKeys(fn (Permission $permission): array => [$permission->value => $user->can($permission->value)])
                ->all(),
        ];
    }
}
