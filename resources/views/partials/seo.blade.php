{{--
╔══════════════════════════════════════════════════════════════════════════════╗
║  SEO PARTIAL  ·  resources/views/partials/seo.blade.php                    ║
║                                                                              ║
║  All meta, Open Graph, Twitter Card, and JSON-LD output lives here.         ║
║  Override defaults in any child view (before @section('content')):          ║
║                                                                              ║
║    @section('seo_description', 'Page description, max ~155 chars.')         ║
║    @section('seo_canonical',   route('events.show', $slug))                 ║
║    @section('seo_image',       'https://…/cover.jpg')  ← 1200×630 ideally  ║
║    @section('seo_type',        'article')              ← default: website   ║
║    @section('seo_robots',      'noindex, nofollow')    ← default: index,…  ║
║                                                                              ║
║  For structured data push per page:                                          ║
║    @push('seo_jsonld')                                                       ║
║    <script type="application/ld+json">…</script>                            ║
║    @endpush                                                                  ║
║                                                                              ║
║  Default OG image: place a 1200×630 image at                                ║
║    public/assets/images/og-default.jpg                                       ║
║                                                                              ║
║  Twitter handle: update the twitter:site tag below when known.              ║
╚══════════════════════════════════════════════════════════════════════════════╝
--}}
@php
    // Resolve section values with fallbacks
    $seoTitle     = $__env->yieldContent('title', 'AMCOB Events');
    $seoDesc      = $__env->yieldContent('seo_description',
                        'Browse and register for AMCOB networking events, business mixers, and professional workshops across the US.');
    $seoCanonical = $__env->yieldContent('seo_canonical', url()->current());
    $seoImage     = $__env->yieldContent('seo_image',     asset('assets/images/og-default.jpg'));
    $seoType      = $__env->yieldContent('seo_type',      'website');
    $seoRobots    = $__env->yieldContent('seo_robots',    'index, follow');
    $seoImageW    = $__env->yieldContent('seo_image_w',   '1200');
    $seoImageH    = $__env->yieldContent('seo_image_h',   '630');
@endphp

{{-- ── Core ──────────────────────────────────────────────────────────────── --}}
<meta name="description" content="{{ $seoDesc }}">
<meta name="robots"      content="{{ $seoRobots }}">
<link rel="canonical"    href="{{ $seoCanonical }}">

{{-- ── Open Graph ────────────────────────────────────────────────────────── --}}
<meta property="og:site_name"    content="AMCOB Events">
<meta property="og:locale"       content="en_US">
<meta property="og:type"         content="{{ $seoType }}">
<meta property="og:title"        content="{{ $seoTitle }}">
<meta property="og:description"  content="{{ $seoDesc }}">
<meta property="og:url"          content="{{ $seoCanonical }}">
<meta property="og:image"        content="{{ $seoImage }}">
<meta property="og:image:width"  content="{{ $seoImageW }}">
<meta property="og:image:height" content="{{ $seoImageH }}">
<meta property="og:image:alt"    content="{{ $seoTitle }}">

{{-- ── Twitter Card ─────────────────────────────────────────────────────── --}}
<meta name="twitter:card"        content="summary_large_image">
{{-- <meta name="twitter:site"   content="@AMCOBofficial"> --}}
<meta name="twitter:title"       content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
<meta name="twitter:image"       content="{{ $seoImage }}">
<meta name="twitter:image:alt"   content="{{ $seoTitle }}">

{{-- ── Structured data (JSON-LD) — pushed per page ────────────────────── --}}
@stack('seo_jsonld')
