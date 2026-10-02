{{--
    Server-rendered metadata for crawlers and link previews. The data-inertia keys match
    the head-key attributes in resources/js/components/layout/SeoHead.vue, which takes
    these tags over during client-side navigation.
--}}
@if ($seo['description'])
    <meta data-inertia="description" name="description" content="{{ $seo['description'] }}">
@endif
@if ($seo['keywords'])
    <meta data-inertia="keywords" name="keywords" content="{{ $seo['keywords'] }}">
@endif
<meta data-inertia="robots" name="robots" content="{{ $seo['robots'] }}">
<link data-inertia="canonical" rel="canonical" href="{{ $seo['canonical'] }}">
@foreach ($seo['alternates'] as $locale => $url)
    <link data-inertia="alternate-{{ $locale }}" rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
@endforeach
<link data-inertia="alternate-x-default" rel="alternate" hreflang="x-default" href="{{ $seo['defaultUrl'] }}">

<meta data-inertia="og:type" property="og:type" content="{{ $seo['type'] }}">
<meta data-inertia="og:site_name" property="og:site_name" content="{{ $seo['siteName'] }}">
<meta data-inertia="og:title" property="og:title" content="{{ $seo['title'] }}">
@if ($seo['description'])
    <meta data-inertia="og:description" property="og:description" content="{{ $seo['description'] }}">
@endif
<meta data-inertia="og:url" property="og:url" content="{{ $seo['canonical'] }}">
<meta data-inertia="og:locale" property="og:locale" content="{{ $seo['locale'] }}">
@foreach ($seo['alternateLocales'] as $alternateLocale)
    <meta property="og:locale:alternate" content="{{ $alternateLocale }}">
@endforeach
@if ($seo['image'])
    <meta data-inertia="og:image" property="og:image" content="{{ $seo['image'] }}">
@endif
<meta data-inertia="twitter:card" name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">

@foreach ($seo['structuredData'] as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
