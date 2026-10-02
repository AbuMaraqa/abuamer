<!DOCTYPE html>
@php
    $seo = $page['props']['seo'] ?? null;
    $site = $page['props']['site'] ?? null;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1d1b18">
        <link rel="icon" href="{{ $site['favicon'] ?? asset('favicon.ico') }}">

        @fonts
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
