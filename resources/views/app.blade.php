<!DOCTYPE html>
@php
    $seo = $page['props']['seo'] ?? null;
    $site = $page['props']['site'] ?? null;
    $font = \App\Enums\SiteFont::tryFrom($site['font'] ?? '') ?? \App\Enums\SiteFont::IbmPlexSansArabic;
    // The control panel (entered through the login page without a reload) loads every
    // body font so the font setting can be previewed.
    $isControlPanel = str_starts_with($page['component'], 'Admin/') || str_starts_with($page['component'], 'Auth/');
@endphp
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}"
    style="--font-site: var(--font-{{ $font->value }})"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1d1b18">
        <link rel="icon" href="{{ $site['favicon'] ?? asset('favicon.ico') }}">

        @if ($isControlPanel)
            @fonts
        @else
            @fonts($font->publicFontAliases())
        @endif
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js', "resources/js/pages/{$page['component']}.vue"])

        <x-inertia::head>
            <title>{{ $seo['title'] ?? config('app.name') }}</title>
        </x-inertia::head>

        @if ($seo)
            @include('partials.seo', ['seo' => $seo])
        @endif
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>
