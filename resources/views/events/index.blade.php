@extends('layouts.app')

@section('title', 'AMCOB Events — Business Networking, Mixers & Workshops')
@section('seo_description', 'Find and register for AMCOB networking events, business mixers, professional workshops, and more. Connect with professionals across every AMCOB chapter.')
@section('seo_canonical', route('home'))

@php
    $discovery = is_array($discovery ?? null) ? $discovery : [
        'events' => [],
        'categories' => [],
        'locations' => ['cities' => [], 'states' => [], 'countries' => []],
        'counts' => ['all' => 0, 'upcoming' => 0, 'live' => 0, 'past' => 0],
        'paging' => [],
        'feed_url' => route('events.discovery'),
        'source' => 'api',
    ];
    $dxEvents = is_array($discovery['events'] ?? null) ? $discovery['events'] : [];
    $dxFeatured = is_array($discovery['featured'] ?? null) ? $discovery['featured'] : [];
    $featuredSlide = collect($dxFeatured)->first(fn ($e) => ($e['status'] ?? '') === 'upcoming' || ($e['status'] ?? '') === 'live')
        ?? collect($dxEvents)->first(fn ($e) => !empty($e['featured']) && (($e['status'] ?? '') === 'upcoming' || ($e['status'] ?? '') === 'live'))
        ?? collect($dxEvents)->first(fn ($e) => ($e['status'] ?? '') === 'upcoming')
        ?? collect($dxEvents)->first();

    $dxCssPath = public_path('assets/css/events-discovery.css');
    $dxJsPath = public_path('assets/js/discovery/app.js');
    $dxCssVer = file_exists($dxCssPath) ? filemtime($dxCssPath) : time();
    $dxJsVer = file_exists($dxJsPath) ? filemtime($dxJsPath) : time();
@endphp

@section('seo_image', !empty($featuredSlide['img']) ? $featuredSlide['img'] : asset('assets/images/og-default.jpg'))

@push('seo_jsonld')
@php
$homeJsonLd = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type' => 'Organization',
            'name'  => 'AMCOB',
            'url'   => 'https://amcob.org',
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => 'https://amcob.org/assets/images/logo-white.webp',
            ],
        ],
        [
            '@type' => 'WebSite',
            'name'  => 'AMCOB Events',
            'url'   => url('/'),
        ],
        [
            '@type'       => 'CollectionPage',
            'name'        => 'AMCOB Events — Business Networking, Mixers & Workshops',
            'description' => 'Find and register for AMCOB networking events, business mixers, professional workshops, and more.',
            'url'         => route('home'),
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($homeJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/events-discovery.css') }}?v={{ $dxCssVer }}">
@endpush

@section('content')
<div id="events-discovery" data-source="{{ $discovery['source'] ?? 'api' }}">
  @include('home.discovery._hero')
  @include('home.discovery._explorer')
  @include('home._appband')
</div>
@endsection

@push('scripts')
@php
    $discoveryPayload = [
        'schema_version' => $discovery['schema_version'] ?? 1,
        'events' => $dxEvents,
        'featured' => $discovery['featured'] ?? [],
        'categories' => $discovery['categories'] ?? [],
        'locations' => $discovery['locations'] ?? ['cities' => [], 'states' => [], 'countries' => []],
        'counts' => $discovery['counts'] ?? ['all' => 0, 'upcoming' => 0, 'live' => 0, 'past' => 0],
        'paging' => $discovery['paging'] ?? [],
        'filters' => $discovery['filters'] ?? [],
        'config' => $discovery['config'] ?? [
            'default_view' => 'calendar',
            'default_tab' => 'all',
            'detail_url_template' => '/{slug}',
            'features' => ['hero' => true, 'load_more' => true],
        ],
        'per_page' => $discovery['per_page'] ?? 12,
        'feed_url' => $discovery['feed_url'] ?? route('events.discovery'),
        'today' => now()->toIso8601String(),
        'source' => $discovery['source'] ?? 'api',
    ];
@endphp
<script>
  window.AMCOB_DISCOVERY = @json($discoveryPayload);
</script>
<script type="module" charset="utf-8" src="{{ asset('assets/js/discovery/app.js') }}?v={{ $dxJsVer }}"></script>
@endpush
