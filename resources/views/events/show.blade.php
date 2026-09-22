@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    // All API keys optional during development — missing fields never crash the view.
    $event = is_array($event ?? null) ? $event : [];

    $title    = $event['title'] ?? 'Event';
    $slug     = $event['slug'] ?? null;
    $timezone = $event['timezone'] ?? 'UTC';
    $eventUrl = $slug ? route('events.show', $slug) : route('home');

    $tickets  = is_array($event['tickets'] ?? null) ? $event['tickets'] : [];
    $speakers = is_array($event['speakers'] ?? null) ? $event['speakers'] : [];
    $sponsors = is_array($event['sponsors'] ?? null) ? $event['sponsors'] : [];
    $agendas  = is_array($event['agendas'] ?? null) ? $event['agendas'] : [];
    $tags     = is_array($event['tags'] ?? null)
        ? $event['tags']
        : (is_array($event['categories'] ?? null) ? $event['categories'] : []);

    $location = is_array($event['location'] ?? null) ? $event['location'] : [];
    $cover    = is_array($event['cover'] ?? null) ? $event['cover'] : [];
    $platformData = is_array($event['platform'] ?? null) ? $event['platform'] : [];

    $isOnline  = !empty($event['is_online']);
    $platform  = $platformData['name'] ?? null;
    $isCurrent = !empty($event['is_current']);
    $isFeatured = !empty($event['is_featured']) || !empty($event['featured']);
    $theme     = is_array($event['theme'] ?? null) ? $event['theme'] : [];
    $coverUrl  = $cover['url'] ?? null;
    $coverGradient = $cover['gradient'] ?? ($theme['gradient'] ?? null);
    $registrationOpen = !empty($event['registration_open']);
    $registrationCount = (int) ($event['registration_count'] ?? 0);
    $waitlistEnabled = !empty($event['waitlist_enabled']);
    $requireApproval = !empty($event['require_registration_approval']);
    $allowMultipleGuests = !empty($event['allow_multiple_guests']);
    $maxGuestsPerRegistration = max(1, (int) ($event['max_guests_per_registration'] ?? 1));
    $hostData = is_array($event['hosted_by'] ?? null)
        ? $event['hosted_by']
        : (is_array($event['host'] ?? null) ? $event['host'] : []);
    $hostName = $hostData['name'] ?? null;
    if (!$allowMultipleGuests) {
        $maxGuestsPerRegistration = 1;
    }
    $onlineUrl = $event['online_url'] ?? ($location['online_url'] ?? null);
    $description = $event['description'] ?? null;
    $shortDescription = $event['short_description'] ?? null;
    $dateLabel = $event['date_label'] ?? null;
    $typeLabel = $location['type_label']
        ?? (($event['event_type'] ?? null) === 'hybrid' ? 'Hybrid' : ($isOnline ? 'Online' : 'In Person'));
    $agendaItemCount = collect($agendas)->sum(fn ($g) => is_array($g) ? count($g['items'] ?? []) : 0);

    // Prefer new venue_* keys; fall back to legacy city/venue/address and display labels.
    $venueName    = $location['venue_name'] ?? $location['venue'] ?? null;
    $venueAddress = $location['venue_address'] ?? $location['address'] ?? null;
    $venueCity    = $location['venue_city'] ?? $location['city'] ?? null;
    $venueState   = $location['venue_state'] ?? $location['state'] ?? null;
    $venueCountry = $location['venue_country'] ?? $location['country'] ?? null;
    $venueZip     = $location['zip'] ?? $location['postal_code'] ?? null;
    $locationLabel = $event['location_label']
        ?? $location['label']
        ?? $location['full_address']
        ?? null;
    $fullAddress = $location['full_address']
        ?? implode(', ', array_filter([$venueName, $venueAddress, $venueCity, $venueState, $venueCountry]));
    $mapsUrl = $fullAddress
        ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($fullAddress)
        : null;

    // Lowest paid ticket price for hero badge
    $paidTix = array_values(array_filter($tickets, function ($t) {
        if (!is_array($t)) {
            return false;
        }
        return !(($t['is_free'] ?? false) || (($t['price'] ?? 0) == 0));
    }));
    $minPrice = !empty($paidTix)
        ? min(array_map(fn ($t) => (float) ($t['price'] ?? 0), $paidTix))
        : null;
    $priceLabel = $event['price_label']
        ?? (empty($tickets) ? null : (empty($paidTix) ? 'Free' : 'From $' . number_format((float) $minPrice, 0)));

    // Multi-tier price display: "Member $75 · Guest $95" for 2+ paid tiers
    $tierPriceDisplay = null;
    if (count($paidTix) >= 2) {
        $sorted = $paidTix;
        usort($sorted, fn ($a, $b) => ((float) ($a['price'] ?? 0)) <=> ((float) ($b['price'] ?? 0)));
        $parts = [];
        foreach (array_slice($sorted, 0, 2) as $t) {
            $parts[] = ($t['name'] ?? 'Ticket') . ' $' . number_format((float) ($t['price'] ?? 0), 0);
        }
        $tierPriceDisplay = implode(' · ', $parts);
    }

    // Sold out = every ticket has an explicit available ≤ 0 (null = unlimited)
    $soldOut = !empty($tickets) && collect($tickets)->every(
        fn ($t) => is_array($t) && isset($t['available']) && $t['available'] <= 0
    );

    $eventDate = null;
    $eventEndDate = null;
    if (!empty($event['starts_at'])) {
        try {
            $eventDate = Carbon::parse($event['starts_at'])->timezone($timezone);
        } catch (\Throwable $e) {
            $eventDate = null;
        }
    }
    if (!empty($event['ends_at'])) {
        try {
            $eventEndDate = Carbon::parse($event['ends_at'])->timezone($timezone);
        } catch (\Throwable $e) {
            $eventEndDate = null;
        }
    }

    $regTicket  = session('registration_ticket');
    $regTickets = session('registration_tickets');
    if (!is_array($regTickets) || empty($regTickets)) {
        $regTickets = is_array($regTicket) ? [$regTicket] : [];
    }
    $regGuestCount = (int) (session('registration_guest_count') ?: count($regTickets) ?: 1);
    $regPayment = session('registration_payment');

    // Past event detection: API flag first, then fall back to date comparison
    $isPast = !empty($event['is_past']);
    if (!array_key_exists('is_past', $event) && !$isCurrent) {
        $compare = $eventEndDate ?: $eventDate;
        $isPast = $compare ? $compare->isPast() : false;
    }

    // ── SEO values ────────────────────────────────────────────────────────────
    $seoRawDesc  = strip_tags($description ?? $event['summary'] ?? $event['short_description'] ?? '');
    $seoPageDesc = $seoRawDesc !== ''
        ? Str::limit($seoRawDesc, 155)
        : 'Register for ' . $title . ' — an AMCOB event. Find details, tickets, speakers, and agenda.';

    $seoEventStatus = 'https://schema.org/EventScheduled';

    $hasPhysicalPlace = !empty($venueCity) || !empty($venueAddress) || !empty($locationLabel);
    $seoAttendanceMode = $isOnline
        ? ($hasPhysicalPlace
            ? 'https://schema.org/MixedEventAttendanceMode'
            : 'https://schema.org/OnlineEventAttendanceMode')
        : 'https://schema.org/OfflineEventAttendanceMode';

    $seoOffers = [];
    foreach ($tickets as $t) {
        if (!is_array($t)) {
            continue;
        }
        $isFree = ($t['is_free'] ?? false) || (($t['price'] ?? 0) == 0);
        $avail  = isset($t['available'])
            ? ($t['available'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut')
            : 'https://schema.org/InStock';
        $seoOffers[] = [
            '@type'         => 'Offer',
            'name'          => $t['name'] ?? 'Ticket',
            'price'         => $isFree ? '0' : number_format((float) ($t['price'] ?? 0), 2, '.', ''),
            'priceCurrency' => 'USD',
            'availability'  => $avail,
            'validFrom'     => $event['starts_at'] ?? null,
            'url'           => $eventUrl,
        ];
    }

    $seoPlace = [
        '@type'   => 'Place',
        'name'    => $venueName ?? $locationLabel ?? '',
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $venueAddress ?? '',
            'addressLocality' => $venueCity ?? '',
            'addressRegion'   => $venueState ?? '',
            'postalCode'      => $venueZip ?? '',
            'addressCountry'  => $venueCountry ?? 'US',
        ],
    ];

    $seoLocation = [];
    if ($isOnline && !$hasPhysicalPlace) {
        $seoLocation = [
            '@type' => 'VirtualLocation',
            'url'   => $onlineUrl ?: $eventUrl,
        ];
    } elseif ($isOnline) {
        $seoLocation = [
            ['@type' => 'VirtualLocation', 'url' => $onlineUrl ?: $eventUrl],
            $seoPlace,
        ];
    } else {
        $seoLocation = $seoPlace;
    }

    $seoEventJsonLd = array_filter([
        '@context'            => 'https://schema.org',
        '@type'               => 'Event',
        'name'                => $title,
        'description'         => $seoRawDesc ?: null,
        'image'               => $coverUrl,
        'url'                 => $eventUrl,
        'startDate'           => $event['starts_at'] ?? null,
        'endDate'             => $event['ends_at'] ?? null,
        'eventStatus'         => $seoEventStatus,
        'eventAttendanceMode' => $seoAttendanceMode,
        'location'            => $seoLocation ?: null,
        'organizer'           => ['@type' => 'Organization', 'name' => 'AMCOB', 'url' => 'https://amcob.org'],
        'offers'              => !empty($seoOffers) ? $seoOffers : null,
    ]);
@endphp

@section('title', $title . ' — AMCOB Events')
@section('seo_description', $seoPageDesc)
@section('seo_canonical',   $eventUrl)
@section('seo_image',       $coverUrl ?? asset('assets/images/og-default.jpg'))
@section('seo_type',        'article')

@push('seo_jsonld')
<script type="application/ld+json">{!! json_encode($seoEventJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('styles')
@php
    $evDetailCss = public_path('assets/css/events-detail.css');
    $evDetailCssVer = file_exists($evDetailCss) ? filemtime($evDetailCss) : time();
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/css/intlTelInput.css">
<link rel="stylesheet" href="{{ asset('assets/css/events-detail.css') }}?v={{ $evDetailCssVer }}">
@endpush

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────────────────── --}}
<header class="ev-hero">
    @if ($coverUrl)
        <img class="ev-cover" src="{{ $coverUrl }}" alt="{{ $title }}" fetchpriority="high" width="1200" height="500">
    @elseif ($coverGradient)
        <div class="ev-cover-fallback" style="background:{{ $coverGradient }}"></div>
    @endif

    <div class="wrap ev-hero-in">
        {{-- Back link --}}
        <a class="ev-back-hero" href="{{ route('home') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            All events
        </a>

        {{-- Badges --}}
        <div class="ev-badges">
            @if ($isPast)
                <span class="ev-badge ev-badge-past">Past Event</span>
            @elseif ($isCurrent)
                <span class="ev-badge ev-badge-red">● Happening Now</span>
            @endif
            @if ($isFeatured && !$isPast)
                <span class="ev-badge ev-badge-gold">Featured</span>
            @endif
            @foreach ($tags as $tag)
                @if (!empty($tag['name']))
                    <span class="ev-badge ev-badge-gold">{{ $tag['name'] }}</span>
                @endif
            @endforeach
            {{-- Platform badge (AMCOB platform source, e.g. "Lead") --}}
            @if ($platform)
                <span class="ev-badge">{{ $platform }}</span>
            @endif
            {{-- Online / In-Person badge --}}
            @if ($isOnline)
                <span class="ev-badge">
                    <svg class="ev-badge-ic" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>Online
                </span>
            @else
                <span class="ev-badge">In-Person</span>
            @endif
            {{-- Price badge --}}
            @if (!$isPast)
                @if ($tierPriceDisplay)
                    <span class="ev-badge">{{ $tierPriceDisplay }}</span>
                @elseif ($priceLabel)
                    <span class="ev-badge">{{ $priceLabel }}</span>
                @endif
            @endif
        </div>

        <h1>{{ $title }}</h1>
        @if ($shortDescription && $shortDescription !== strip_tags((string) $description))
            <p class="ev-hero-lead">{{ $shortDescription }}</p>
        @endif

        <div class="ev-hero-meta">
            @if ($dateLabel || $eventDate)
            <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                @if ($dateLabel)
                    {{ $dateLabel }}
                @elseif ($eventDate)
                    {{ $eventDate->format('D, M j, Y · g:i A') }} <span class="ev-meta-tz">{{ $eventDate->format('T') }}</span>
                @endif
            </div>
            @endif
            @if ($locationLabel || ($isOnline && $onlineUrl))
            <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $locationLabel }}
                @if ($isOnline && $onlineUrl)
                    @if ($locationLabel)&middot; @endif<a href="{{ $onlineUrl }}" target="_blank" rel="noopener">Join link</a>
                @endif
            </div>
            @endif
            @if ($registrationCount > 0)
            <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                {{ $registrationCount }}+ attending
            </div>
            @endif
            @if ($hostName)
            <div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 6 6 .9-4.5 4.4 1 6.2L12 17l-5.5 2.5 1-6.2L3 8.9 9 8z"/></svg>
                Hosted by {{ $hostName }}
            </div>
            @endif
        </div>

        <div class="ev-hero-cta">
            @if ($isPast)
                <span class="ev-hero-price ev-hero-price-muted">
                    @if ($registrationCount > 0)
                        {{ $registrationCount }} people attended this event.
                    @else
                        This event has ended.
                    @endif
                </span>
            @elseif ($registrationOpen)
                <a href="#register-form" class="btn btn-gold" id="heroCta">
                    @if ($isCurrent)
                        Join now
                    @elseif ($soldOut && !$waitlistEnabled)
                        Sold out
                    @elseif ($soldOut)
                        Join waitlist
                    @else
                        Register now
                    @endif
                </a>
                @if ($tierPriceDisplay)
                    <span class="ev-hero-price">{{ $tierPriceDisplay }}</span>
                @elseif ($priceLabel)
                    <span class="ev-hero-price">
                        @if ($priceLabel === 'Free')
                            <b>Free</b> event &middot; RSVP required
                        @else
                            <b>{{ $priceLabel }}</b>
                        @endif
                    </span>
                @endif
            @endif
        </div>
    </div>
</header>

{{-- ── TWO-COLUMN LAYOUT ─────────────────────────────────────────────────── --}}
<div class="wrap ev-layout">

    {{-- ─── LEFT: Main content ────────────────────────────────────────────── --}}
    <div class="ev-main">

        {{-- About --}}
        @if (!empty($description))
        <div class="ev-block">
            <h2>About this event</h2>
            <div class="ev-about">
                {!! $description !!}
            </div>
        </div>
        @endif

        {{-- Location: full address + maps (hero already shows short label) --}}
        @if (($fullAddress && $fullAddress !== $locationLabel) || ($venueName && $venueName !== $locationLabel) || $mapsUrl || ($isOnline && $onlineUrl))
        <div class="ev-block">
            <h2>Location</h2>
            <p class="ev-sub">{{ $typeLabel ?: 'Venue details' }}</p>
            <div class="ev-place">
                <div>
                    <b>{{ $venueName ?: ($locationLabel ?: 'Venue') }}</b>
                    @if ($fullAddress)
                        <p>{{ $fullAddress }}</p>
                    @elseif ($locationLabel && $locationLabel !== $venueName)
                        <p>{{ $locationLabel }}</p>
                    @endif
                </div>
                @if ($mapsUrl && !$isOnline)
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer">Open in Maps</a>
                @elseif ($isOnline && $onlineUrl)
                    <a href="{{ $onlineUrl }}" target="_blank" rel="noopener">Join link</a>
                @endif
            </div>
        </div>
        @endif

        {{-- Agenda --}}
        @if (!empty($agendas))
        <div class="ev-block">
            <h2>Agenda</h2>
            <p class="ev-sub">{{ $agendaItemCount }} {{ Str::plural('session', $agendaItemCount) }} on the programme.</p>
            @foreach ($agendas as $agenda)
                @php $agenda = is_array($agenda) ? $agenda : []; @endphp
                @if (!empty($agenda['title']) || !empty($agenda['day_date_label']) || !empty($agenda['day_label']))
                    <h4 class="ag-day">
                        {{ $agenda['title'] ?? $agenda['day_label'] ?? 'Schedule' }}
                        @if (!empty($agenda['day_date_label']))
                            <span>{{ $agenda['day_date_label'] }}</span>
                        @endif
                    </h4>
                @endif
                <div class="ag-list">
                    @foreach ($agenda['items'] ?? [] as $item)
                    @php
                        $item = is_array($item) ? $item : [];
                        $timeLabel = $item['start_time_label'] ?? null;
                        $durationLabel = $item['duration_label'] ?? null;
                        $agendaSpeakerNames = $item['speaker_names'] ?? null;
                        if (!$agendaSpeakerNames && !empty($item['speakers']) && is_array($item['speakers'])) {
                            $agendaSpeakerNames = collect($item['speakers'])
                                ->filter('is_array')
                                ->pluck('name')
                                ->filter()
                                ->implode(', ');
                        }
                    @endphp
                    <div class="ag-row">
                        @if ($timeLabel)
                            <div class="ag-pill" aria-label="Starts {{ $timeLabel }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                {{ $timeLabel }}
                            </div>
                        @else
                            <div class="ag-pill is-index" aria-label="Session {{ $loop->iteration }}">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        @endif
                        <div class="ag-body">
                            <b>{{ $item['title'] ?? 'Session' }}</b>
                            @if (!empty($item['description']))
                                <p>{{ $item['description'] }}</p>
                            @endif
                            @if ($agendaSpeakerNames || $durationLabel)
                                <div class="ag-meta">
                                    @if ($agendaSpeakerNames)
                                        <span class="ag-speaker">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            Speaker: {{ $agendaSpeakerNames }}
                                        </span>
                                    @endif
                                    @if ($durationLabel)
                                        <span class="ag-dur">{{ $durationLabel }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        @endif

        {{-- Speakers --}}
        @if (!empty($speakers))
        <div class="ev-block">
            <div class="spk-slider-head">
                <div>
                    <h2>Speakers</h2>
                    <p class="ev-sub">Hosts and guests you'll hear from.</p>
                </div>
                <div class="spk-arrows">
                    <button class="spk-arr" id="spkPrev" aria-label="Previous speakers">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <button class="spk-arr" id="spkNext" aria-label="Next speakers">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
            <div class="swiper spk-swiper" id="spkSwiper">
                <div class="swiper-wrapper">
                    @foreach ($speakers as $speaker)
                    @php
                        $speaker = is_array($speaker) ? $speaker : [];
                        $spkName = $speaker['name'] ?? 'Speaker';
                        $spkInitial = mb_strtoupper(mb_substr($spkName, 0, 1));
                    @endphp
                    <div class="swiper-slide">
                        <div class="spk">
                            @if (!empty($speaker['image_url']))
                                <div class="spk-av" style="background-image:url('{{ $speaker['image_url'] }}')"></div>
                            @else
                                <div class="spk-av-init">{{ $spkInitial }}</div>
                            @endif
                            <b>{{ $spkName }}</b>
                            @if (!empty($speaker['designation']))
                                <div class="spk-role">{{ $speaker['designation'] }}</div>
                            @endif
                            @if (!empty($speaker['bio']))
                                <div class="spk-co">{{ $speaker['bio'] }}</div>
                            @endif
                            <button class="spk-more"
                                data-name="{{ e($spkName) }}"
                                data-designation="{{ e($speaker['designation'] ?? '') }}"
                                data-bio="{{ e($speaker['bio'] ?? '') }}"
                                data-image="{{ e($speaker['image_url'] ?? '') }}"
                                data-initial="{{ $spkInitial }}"
                                data-linkedin="{{ e($speaker['linkedin_url'] ?? '') }}">
                                Read more
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Speaker modal (shared, populated by JS) --}}
            <div class="spk-modal" id="spkModal" role="dialog" aria-modal="true" aria-labelledby="spkModalName">
                <div class="spk-modal-box">
                    <button class="spk-modal-close" id="spkModalClose" aria-label="Close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="16" height="16"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                    <div class="spk-modal-av" id="spkModalAv"></div>
                    <div class="spk-modal-name" id="spkModalName"></div>
                    <div class="spk-modal-role" id="spkModalRole"></div>
                    <p class="spk-modal-bio" id="spkModalBio"></p>
                    <a class="btn btn-navy spk-modal-li ev-is-hidden" id="spkModalLi" href="#" target="_blank" rel="noopener" aria-label="View on LinkedIn">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Sponsors --}}
        @if (!empty($sponsors))
        <div class="ev-block">
            <h2>Sponsors</h2>
            <p class="ev-sub">Organizations supporting this event.</p>
            <div class="ev-sponsors">
                @foreach ($sponsors as $sponsor)
                    @php
                        $sponsor = is_array($sponsor) ? $sponsor : [];
                        $sponsorName = $sponsor['name'] ?? 'Event sponsor';
                        $sponsorUrl = $sponsor['website_url'] ?? null;
                    @endphp
                    @if ($sponsorUrl)
                        <a class="ev-sponsor" href="{{ $sponsorUrl }}" target="_blank" rel="noopener noreferrer">
                            @if (!empty($sponsor['logo_url']))
                                <img src="{{ $sponsor['logo_url'] }}" alt="{{ $sponsorName }}" loading="eager" decoding="async">
                            @else
                                <span>{{ $sponsorName }}</span>
                            @endif
                        </a>
                    @else
                        <div class="ev-sponsor">
                            @if (!empty($sponsor['logo_url']))
                                <img src="{{ $sponsor['logo_url'] }}" alt="{{ $sponsorName }}" loading="eager" decoding="async">
                            @else
                                <span>{{ $sponsorName }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Ticket type overview (left column) --}}
        @if (!$isPast && $registrationOpen && !empty($tickets))
        <div class="ev-block">
            <h2>Ticket types</h2>
            <p class="ev-sub">Pick a ticket, then complete registration on the right.</p>
            <div class="ev-tickets-list">
                @foreach ($tickets as $ticket)
                @php
                    $ticket    = is_array($ticket) ? $ticket : [];
                    $tcAvail   = $ticket['available'] ?? null;
                    $tcSoldOut = array_key_exists('available', $ticket) && $tcAvail !== null && $tcAvail <= 0;
                    $tcFree    = ($ticket['is_free'] ?? false) || (($ticket['price'] ?? 0) == 0);
                    $tcPrice   = (float) ($ticket['price'] ?? 0);
                    $tcName    = $ticket['name'] ?? 'Ticket';
                    $tcId      = $ticket['id'] ?? null;
                @endphp
                <div class="ev-tc{{ $tcSoldOut ? ' ev-tc-sold' : '' }}">
                    <div class="ev-tc-info">
                        <b>{{ $tcName }}</b>
                        @if (!empty($ticket['description']))<p>{{ $ticket['description'] }}</p>@endif
                        <div class="ev-tc-meta">
                            @if (($ticket['available'] ?? null) === null)
                                <span class="ev-chip">Open availability</span>
                            @elseif (!$tcSoldOut)
                                <span class="ev-chip">{{ (int) $ticket['available'] }} left</span>
                            @endif
                            @if (isset($ticket['sold_count']) && (int) $ticket['sold_count'] > 0)
                                <span class="ev-chip">{{ (int) $ticket['sold_count'] }} sold</span>
                            @endif
                        </div>
                    </div>
                    <div class="ev-tc-right">
                        <div class="ev-tc-price">
                            {{ $tcFree ? 'Free' : '$' . number_format($tcPrice, 0) }}
                            @if (!$tcFree)<small>/ ticket</small>@endif
                        </div>
                        @if ($tcSoldOut)
                            <span class="ev-sold-pill">Sold out</span>
                        @elseif ($tcId)
                            <button class="ev-selbtn" type="button"
                                    data-selticket="{{ $tcId }}"
                                    data-price="{{ $tcPrice }}">Select</button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /ev-main --}}

    {{-- ─── RIGHT: Sticky sidebar ─────────────────────────────────────────── --}}
    <aside class="ev-side" id="register-form">
        <div class="ev-fc" id="evFormCard">

            @if ($isPast && empty($regTickets))
            {{-- ── PAST EVENT STATE ────────────────────────────────────────── --}}
            <div class="ev-fc-head">
                <span class="eyebrow">Event recap</span>
                <h3>{{ Str::limit($title, 44) }}</h3>
            </div>
            <div class="ev-fc-body">
                <div class="ev-infobox">
                    <b>This event has ended.</b>
                    @if ($registrationCount > 0)
                        {{ $registrationCount }} people attended.
                    @endif
                    Check out our upcoming events below.
                </div>
                <a class="btn btn-navy ev-submit" href="{{ route('home') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Browse upcoming events
                </a>
            </div>

            @elseif (!empty($regTickets))
            {{-- ── SUCCESS STATE ──────────────────────────────────────────── --}}
            <div class="ev-fc-head">
                <span class="eyebrow">Registration confirmed</span>
                <h3>You're in!</h3>
            </div>
            <div class="ev-fc-body">
                <div class="ev-done">
                    <div class="ev-done-ic">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                    <h4>{{ $regGuestCount > 1 ? $regGuestCount . ' guests registered!' : "You're registered!" }}</h4>
                    <p>
                        A confirmation email with {{ $regGuestCount > 1 ? 'check-in QRs' : 'your check-in QR' }} is on its way
                        @php
                            $confirmEmails = array_values(array_unique(array_filter(array_map(
                                fn ($t) => is_array($t) ? ($t['email'] ?? null) : null,
                                $regTickets
                            ))));
                        @endphp
                        @if (count($confirmEmails) === 1)
                            to <b>{{ $confirmEmails[0] }}</b>.
                        @elseif (count($confirmEmails) > 1)
                            to each guest.
                        @else
                            .
                        @endif
                    </p>

                    @foreach ($regTickets as $doneTicket)
                        @continue(!is_array($doneTicket))
                        @if (!empty($doneTicket['qr_image_base64']))
                        <div class="ev-qr">
                            @if ($regGuestCount > 1)
                                <div class="ev-qr-guest">
                                    {{ $doneTicket['guest_name'] ?? $doneTicket['email'] ?? ('Guest ' . $loop->iteration) }}
                                </div>
                            @endif
                            <img src="data:image/png;base64,{{ $doneTicket['qr_image_base64'] }}" alt="Check-in QR code">
                            @if (!empty($doneTicket['check_in_token']))
                                <div class="ev-qr-tok">{{ $doneTicket['check_in_token'] }}</div>
                            @endif
                        </div>
                        @endif
                    @endforeach

                    @if ($regPayment && ($regPayment['status'] ?? '') === 'paid')
                    <p class="ev-pay-confirm">
                        Paid ${{ number_format((float) ($regPayment['amount'] ?? 0), 2) }}
                        via {{ $regPayment['card_type'] ?? 'card' }} ····{{ $regPayment['card_last_four'] ?? '' }}
                    </p>
                    @endif

                    <div class="ev-done-actions">
                        <a class="btn btn-navy" href="{{ route('home') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                            Back to events
                        </a>
                    </div>
                </div>
            </div>

            @elseif (!$registrationOpen)
            {{-- ── REGISTRATION CLOSED ─────────────────────────────────────── --}}
            <div class="ev-fc-head">
                <span class="eyebrow">Registration</span>
                <h3>{{ Str::limit($title, 44) }}</h3>
            </div>
            <div class="ev-fc-body">
                <div class="ev-infobox">
                    <b>Registration is closed for this event.</b>
                    Tickets are not available to purchase right now.
                </div>
                <a class="btn btn-navy ev-submit" href="{{ route('home') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Browse other events
                </a>
            </div>

            @else
            {{-- ── REGISTRATION FORM ──────────────────────────────────────── --}}
            <div class="ev-fc-head">
                <span class="eyebrow">{{ $isCurrent ? 'Happening now' : 'Register for' }}</span>
                <h3>{{ Str::limit($title, 44) }}</h3>
            </div>
            <div class="ev-fc-body">

                @if ($soldOut)
                    @if ($waitlistEnabled)
                        <p class="ev-side-copy">
                            This event is at capacity. Join the waitlist and we'll notify you if a spot opens up.
                        </p>
                    @else
                        <p class="ev-side-copy">
                            This event is at capacity. Check back later or browse our other events.
                        </p>
                    @endif
                    <a class="btn btn-navy ev-side-cta" href="{{ route('home') }}">Browse all events</a>
                @else

                    @if ($errors->has('registration'))
                    <div class="ev-errbox">{{ $errors->first('registration') }}</div>
                    @endif

                    @if ($requireApproval)
                    <div class="ev-infobox">
                        Registration for this event requires approval. You will be notified once your spot is confirmed.
                    </div>
                    @endif

                    <form method="POST" action="{{ $slug ? route('events.register', $slug) : '#' }}"
                          id="registrationForm" novalidate>
                        @csrf

                        {{-- Ticket selector --}}
                        <div class="ev-tk-list" id="tkList">
                            @php $firstAvail = true; @endphp
                            @foreach ($tickets as $ticket)
                            @php
                                $ticket   = is_array($ticket) ? $ticket : [];
                                $tAvail   = $ticket['available'] ?? null;
                                $tSoldOut = array_key_exists('available', $ticket) && $tAvail !== null && $tAvail <= 0;
                                $tChecked = !$tSoldOut && $firstAvail && !empty($ticket['id']);
                                if ($tChecked) $firstAvail = false;
                                $tFree    = ($ticket['is_free'] ?? false) || (($ticket['price'] ?? 0) == 0);
                                $tPrice   = (float) ($ticket['price'] ?? 0);
                                $tPriceLabel = $ticket['price_label']
                                    ?? ($tFree ? 'Free' : '$' . number_format($tPrice, 0));
                            @endphp
                            @continue(empty($ticket['id']))
                            <label class="ev-tk-opt{{ $tChecked ? ' ev-on' : '' }}{{ $tSoldOut ? ' ev-tk-dis' : '' }}"
                                   data-tk="{{ $ticket['id'] }}" data-price="{{ $tPrice }}">
                                <span class="ev-tk-left">
                                    <input class="ev-tk-radio ev-tk-real" type="radio" name="ticket_id"
                                           value="{{ $ticket['id'] }}"
                                           data-price="{{ $tPrice }}"
                                           {{ $tChecked ? 'checked' : '' }}
                                           {{ $tSoldOut ? 'disabled' : '' }}>
                                    <span class="ev-tk-nm">
                                        {{ $ticket['name'] ?? 'Ticket' }}
                                        @if ($tSoldOut) <span class="ev-tk-hint">(Sold out)</span>@endif
                                        @if (!$tSoldOut && !empty($ticket['requires_approval'])) <span class="ev-tk-hint-gold">(Approval req.)</span>@endif
                                    </span>
                                </span>
                                <span class="ev-tk-pr">{{ $tPriceLabel }}</span>
                            </label>
                            @endforeach
                        </div>

                        {{-- Attendee / guest details --}}
                        @if ($allowMultipleGuests)
                        @php
                            $oldGuests = old('guests');
                            if (!is_array($oldGuests) || empty($oldGuests)) {
                                $oldGuests = [[
                                    'first_name' => old('first_name'),
                                    'last_name' => old('last_name'),
                                    'email' => old('email'),
                                    'phone' => old('phone'),
                                    'job_title' => old('job_title'),
                                    'company_name' => old('company_name'),
                                    'linkedin_url' => old('linkedin_url'),
                                ]];
                            }
                        @endphp
                        <div id="guestsWrap"
                             data-max-guests="{{ $maxGuestsPerRegistration }}"
                             data-allow-multiple="1">
                            <div class="ev-guest-total" id="guestTotalHint">
                                Add up to <b>{{ $maxGuestsPerRegistration }}</b> guests. Total = ticket price × guests.
                            </div>
                            <div class="ev-guests" id="guestsList">
                                @foreach ($oldGuests as $gi => $oldGuest)
                                @php $oldGuest = is_array($oldGuest) ? $oldGuest : []; @endphp
                                <div class="ev-guest" data-guest-index="{{ $gi }}">
                                    <div class="ev-guest-hd">
                                        <b>Guest {{ $gi + 1 }}</b>
                                        <button type="button" class="ev-guest-rm" data-remove-guest {{ $gi === 0 ? 'hidden' : '' }}>Remove</button>
                                    </div>
                                    <div class="ev-r2">
                                        <div class="ev-fld">
                                            <label>First name <span class="ev-req">*</span></label>
                                            <input type="text" name="guests[{{ $gi }}][first_name]" value="{{ $oldGuest['first_name'] ?? '' }}"
                                                   required autocomplete="given-name" maxlength="100" spellcheck="false">
                                        </div>
                                        <div class="ev-fld">
                                            <label>Last name <span class="ev-req">*</span></label>
                                            <input type="text" name="guests[{{ $gi }}][last_name]" value="{{ $oldGuest['last_name'] ?? '' }}"
                                                   required autocomplete="family-name" maxlength="100" spellcheck="false">
                                        </div>
                                    </div>
                                    <div class="ev-fld">
                                        <label>Email <span class="ev-req">*</span></label>
                                        <input type="email" name="guests[{{ $gi }}][email]" value="{{ $oldGuest['email'] ?? '' }}"
                                               required autocomplete="email" maxlength="255" spellcheck="false">
                                    </div>
                                    <div class="ev-fld">
                                        <label>Phone</label>
                                        <input type="tel" name="guests[{{ $gi }}][phone]" value="{{ $oldGuest['phone'] ?? '' }}"
                                               class="guest-phone" autocomplete="tel">
                                    </div>
                                    <div class="ev-r2">
                                        <div class="ev-fld">
                                            <label>Job title</label>
                                            <input type="text" name="guests[{{ $gi }}][job_title]" value="{{ $oldGuest['job_title'] ?? '' }}"
                                                   autocomplete="organization-title" maxlength="255" spellcheck="false">
                                        </div>
                                        <div class="ev-fld">
                                            <label>Company</label>
                                            <input type="text" name="guests[{{ $gi }}][company_name]" value="{{ $oldGuest['company_name'] ?? '' }}"
                                                   autocomplete="organization" maxlength="255" spellcheck="false">
                                        </div>
                                    </div>
                                    <div class="ev-fld">
                                        <label>LinkedIn</label>
                                        <input type="url" name="guests[{{ $gi }}][linkedin_url]" value="{{ $oldGuest['linkedin_url'] ?? '' }}"
                                               autocomplete="url" maxlength="500" spellcheck="false"
                                               placeholder="https://linkedin.com/in/…">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-navy ev-guest-add" id="addGuestBtn"
                                    {{ count($oldGuests) >= $maxGuestsPerRegistration ? 'hidden' : '' }}>
                                + Add another guest
                            </button>
                        </div>
                        <template id="guestBlockTpl">
                            <div class="ev-guest" data-guest-index="__INDEX__">
                                <div class="ev-guest-hd">
                                    <b>Guest __NUM__</b>
                                    <button type="button" class="ev-guest-rm" data-remove-guest>Remove</button>
                                </div>
                                <div class="ev-r2">
                                    <div class="ev-fld">
                                        <label>First name <span class="ev-req">*</span></label>
                                        <input type="text" name="guests[__INDEX__][first_name]" required autocomplete="given-name" maxlength="100" spellcheck="false">
                                    </div>
                                    <div class="ev-fld">
                                        <label>Last name <span class="ev-req">*</span></label>
                                        <input type="text" name="guests[__INDEX__][last_name]" required autocomplete="family-name" maxlength="100" spellcheck="false">
                                    </div>
                                </div>
                                <div class="ev-fld">
                                    <label>Email <span class="ev-req">*</span></label>
                                    <input type="email" name="guests[__INDEX__][email]" required autocomplete="email" maxlength="255" spellcheck="false">
                                </div>
                                <div class="ev-fld">
                                    <label>Phone</label>
                                    <input type="tel" name="guests[__INDEX__][phone]" class="guest-phone" autocomplete="tel">
                                </div>
                                <div class="ev-r2">
                                    <div class="ev-fld">
                                        <label>Job title</label>
                                        <input type="text" name="guests[__INDEX__][job_title]" autocomplete="organization-title" maxlength="255" spellcheck="false">
                                    </div>
                                    <div class="ev-fld">
                                        <label>Company</label>
                                        <input type="text" name="guests[__INDEX__][company_name]" autocomplete="organization" maxlength="255" spellcheck="false">
                                    </div>
                                </div>
                                <div class="ev-fld">
                                    <label>LinkedIn</label>
                                    <input type="url" name="guests[__INDEX__][linkedin_url]" autocomplete="url" maxlength="500" spellcheck="false"
                                           placeholder="https://linkedin.com/in/…">
                                </div>
                            </div>
                        </template>
                        <div class="ev-fld" id="couponWrap">
                            <label for="couponField">Coupon code</label>
                            <input type="text" id="couponField" name="coupon_code"
                                   value="{{ old('coupon_code') }}" placeholder="Optional"
                                   autocomplete="off" maxlength="64"
                                   class="ev-fld-upper" spellcheck="false">
                            <div id="couponStatus" class="ev-cpn-status"></div>
                            <div id="couponPricePrev" class="ev-price-preview"></div>
                        </div>
                        @else
                        <div class="ev-r2">
                            <div class="ev-fld">
                                <label for="firstName">First name <span class="ev-req">*</span></label>
                                <input type="text" id="firstName" name="first_name" value="{{ old('first_name') }}"
                                       required autocomplete="given-name" maxlength="100" spellcheck="false">
                            </div>
                            <div class="ev-fld">
                                <label for="lastName">Last name <span class="ev-req">*</span></label>
                                <input type="text" id="lastName" name="last_name" value="{{ old('last_name') }}"
                                       required autocomplete="family-name" maxlength="100" spellcheck="false">
                            </div>
                        </div>
                        <div class="ev-fld">
                            <label for="emailInput">Email <span class="ev-req">*</span></label>
                            <input type="email" id="emailInput" name="email" value="{{ old('email') }}"
                                   required autocomplete="email" maxlength="255" spellcheck="false">
                        </div>
                        <div class="ev-r2">
                            <div class="ev-fld">
                                <label for="phoneInput">Phone</label>
                                <input type="tel" id="phoneInput" name="phone"
                                       value="{{ old('phone') }}" autocomplete="tel">
                            </div>
                            <div class="ev-fld" id="couponWrap">
                                <label for="couponField">Coupon code</label>
                                <input type="text" id="couponField" name="coupon_code"
                                       value="{{ old('coupon_code') }}" placeholder="Optional"
                                       autocomplete="off" maxlength="64"
                                       class="ev-fld-upper" spellcheck="false">
                                <div id="couponStatus" class="ev-cpn-status"></div>
                                <div id="couponPricePrev" class="ev-price-preview"></div>
                            </div>
                        </div>
                        <div class="ev-r2">
                            <div class="ev-fld">
                                <label for="jobTitle">Job title</label>
                                <input type="text" id="jobTitle" name="job_title" value="{{ old('job_title') }}"
                                       autocomplete="organization-title" maxlength="255" spellcheck="false"
                                       >
                            </div>
                            <div class="ev-fld">
                                <label for="companyName">Company</label>
                                <input type="text" id="companyName" name="company_name" value="{{ old('company_name') }}"
                                       autocomplete="organization" maxlength="255" spellcheck="false"
                                       >
                            </div>
                        </div>
                        <div class="ev-fld">
                            <label for="linkedinUrl">LinkedIn</label>
                            <input type="url" id="linkedinUrl" name="linkedin_url" value="{{ old('linkedin_url') }}"
                                   autocomplete="url" maxlength="500" spellcheck="false"
                                   placeholder="https://linkedin.com/in/…">
                        </div>
                        @endif

                        <input type="hidden" name="requires_payment" id="requiresPayment" value="0">

                        {{-- Payment fields (shown for paid tickets) --}}
                        <div class="ev-pay ev-is-hidden" id="paymentFields">
                            <div class="ev-pay-hd">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                                Card payment
                            </div>
                            <div class="ev-fld">
                                <label for="cardNumber">Card number <span class="ev-req">*</span></label>
                                <div class="ev-card-wrap">
                                    <input type="text" id="cardNumber" name="card_number"
                                           inputmode="numeric" placeholder="4111 1111 1111 1111"
                                           autocomplete="cc-number" maxlength="23"
                                           value="{{ old('card_number') }}" spellcheck="false">
                                    <span id="cardBrandBadge"></span>
                                </div>
                            </div>
                            <div class="ev-r2">
                                <div class="ev-fld">
                                    <label for="cardExpiry">Expiry (MM/YY) <span class="ev-req">*</span></label>
                                    <input type="text" id="cardExpiry" name="card_expiration"
                                           placeholder="MM/YY" autocomplete="cc-exp"
                                           maxlength="5" inputmode="numeric"
                                           value="{{ old('card_expiration') }}">
                                </div>
                                <div class="ev-fld">
                                    <label for="cardCvv">CVV <span class="ev-req">*</span></label>
                                    <input type="text" id="cardCvv" name="card_cvv"
                                           inputmode="numeric" placeholder="123"
                                           autocomplete="cc-csc" maxlength="4" spellcheck="false">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gold ev-submit" id="regSubmit">
                            {{ $isCurrent ? 'Join now' : 'Confirm registration' }}
                        </button>
                        <p class="ev-note">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                            Secure registration. A check-in QR is emailed on success.
                        </p>
                    </form>

                @endif
            </div>
            @endif

        </div>{{-- /ev-fc --}}
    </aside>

</div>{{-- /ev-layout --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
// Init Speakers carousel immediately so the layout doesn't collapse later
// (which made the Sponsors section appear to pop in after a delay).
if (document.getElementById('spkSwiper') && typeof Swiper !== 'undefined') {
    new Swiper('#spkSwiper', {
        slidesPerView: 3,
        spaceBetween: 16,
        navigation: {
            prevEl: '#spkPrev',
            nextEl: '#spkNext',
            disabledClass: 'swiper-button-disabled',
        },
        breakpoints: {
            0:   { slidesPerView: 1, spaceBetween: 12 },
            560: { slidesPerView: 2, spaceBetween: 16 },
            900: { slidesPerView: 3, spaceBetween: 16 },
        },
    });
}
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/js/intlTelInputWithUtils.js"></script>
<script>
(function ($) {
    'use strict';

    const PREVIEW_URL = '{{ $slug ? route('events.preview-coupon', $slug) : '' }}';
    const CSRF        = '{{ csrf_token() }}';
    const ALLOW_MULTI = {{ $allowMultipleGuests ? 'true' : 'false' }};
    const MAX_GUESTS  = {{ (int) $maxGuestsPerRegistration }};
    var lastCouponUnitAmount = null;

    function guestCount() {
        if (!ALLOW_MULTI) return 1;
        return Math.max(1, document.querySelectorAll('#guestsList .ev-guest').length);
    }

    function unitTicketPrice() {
        var sel = document.querySelector('.ev-tk-real:checked');
        return sel ? parseFloat(sel.dataset.price || '0') : 0;
    }

    function totalTicketPrice() {
        return unitTicketPrice() * guestCount();
    }

    // ── Custom ticket-selector UI ─────────────────────────────────────────────
    document.querySelectorAll('.ev-tk-opt:not(.ev-tk-dis)').forEach(function (label) {
        label.addEventListener('click', function () {
            document.querySelectorAll('.ev-tk-opt').forEach(function (l) { l.classList.remove('ev-on'); });
            label.classList.add('ev-on');
            var radio = label.querySelector('.ev-tk-real');
            if (radio) { radio.checked = true; }
            togglePayment();
            resetCoupon();
        });
    });

    // ── Multi-guest add / remove ──────────────────────────────────────────────
    var guestsList = document.getElementById('guestsList');
    var guestTpl   = document.getElementById('guestBlockTpl');
    var addGuestBtn = document.getElementById('addGuestBtn');

    function refreshCouponTotals() {
        if (lastCouponUnitAmount == null) {
            updateSubmitLabel();
            return;
        }
        var count = guestCount();
        var unit = lastCouponUnitAmount;
        var $prev = $('#couponPricePrev');
        if ($prev.is(':visible')) {
            // Keep the applied coupon; recompute total for current guest count.
            $prev.html('<strong>$' + (unit * count).toFixed(2) + '</strong>' +
                (count > 1 ? ' <span style="color:var(--t2)">· ' + count + ' guests</span>' : ''));
        }
        updateSubmitLabel('Pay $' + (unit * count).toFixed(0) + ' & register');
    }

    function reindexGuests() {
        if (!guestsList) return;
        var blocks = guestsList.querySelectorAll('.ev-guest');
        blocks.forEach(function (block, i) {
            block.setAttribute('data-guest-index', String(i));
            var title = block.querySelector('.ev-guest-hd b');
            if (title) title.textContent = 'Guest ' + (i + 1);
            var rm = block.querySelector('[data-remove-guest]');
            if (rm) rm.hidden = blocks.length <= 1;
            block.querySelectorAll('[name^="guests["]').forEach(function (input) {
                input.name = input.name.replace(/guests\[\d+]/, 'guests[' + i + ']');
            });
        });
        if (addGuestBtn) addGuestBtn.hidden = blocks.length >= MAX_GUESTS;
        refreshCouponTotals();
    }

    function bindGuestBlock(block) {
        block.querySelectorAll('[name*="[first_name]"],[name*="[last_name]"]').forEach(function (el) {
            el.addEventListener('input', function () {
                el.value = el.value.replace(/[0-9!@#$%^&*()_+=\[\]{};:"\\|,<>?/~`]/g, '');
            });
        });
        var rm = block.querySelector('[data-remove-guest]');
        if (rm) {
            rm.addEventListener('click', function () {
                if (guestsList.querySelectorAll('.ev-guest').length <= 1) return;
                block.remove();
                reindexGuests();
            });
        }
    }

    // Guest list init runs after payment helpers are defined (see below).

    // ── intl-tel-input (single-guest phone only) ──────────────────────────────
    const phoneEl = document.getElementById('phoneInput');
    let iti = null;
    if (phoneEl) {
        iti = window.intlTelInput(phoneEl, {
            initialCountry: 'us',
            separateDialCode: true,
            preferredCountries: ['us', 'ca', 'gb'],
            autoPlaceholder: 'polite',
        });
        if (phoneEl.value) iti.setNumber(phoneEl.value);

        // Digits only — strip anything that isn't 0–9 as the user types
        phoneEl.addEventListener('input', function () {
            var cleaned = this.value.replace(/\D/g, '');
            if (this.value !== cleaned) {
                var pos = this.selectionStart - (this.value.length - cleaned.length);
                this.value = cleaned;
                try { this.setSelectionRange(Math.max(0, pos), Math.max(0, pos)); } catch(e) {}
            }
        });
        phoneEl.addEventListener('paste', function (e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text');
            document.execCommand('insertText', false, text.replace(/\D/g, ''));
        });
    }

    // ── Card brand detection ──────────────────────────────────────────────────
    var CARD_SVGS = {
        visa: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 750 471" width="42" height="26"><rect width="750" height="471" rx="40" fill="#1A1F71"/><text x="375" y="345" text-anchor="middle" font-family="Arial,sans-serif" font-style="italic" font-weight="800" font-size="280" fill="#fff" letter-spacing="-8">VISA</text></svg>',
        mastercard: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 24" width="38" height="24"><rect width="38" height="24" rx="3" fill="#231F20"/><circle cx="15" cy="12" r="9" fill="#EB001B"/><circle cx="23" cy="12" r="9" fill="#F79E1B"/><path d="M19 4.8a9 9 0 0 1 0 14.4A9 9 0 0 1 19 4.8z" fill="#FF5F00"/></svg>',
        amex: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 24" width="38" height="24"><rect width="38" height="24" rx="3" fill="#007BC1"/><text x="19" y="16" text-anchor="middle" font-family="Arial,sans-serif" font-weight="800" font-size="9.5" fill="#fff" letter-spacing="0.8">AMEX</text></svg>',
        discover: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 32" width="52" height="32"><rect width="52" height="32" rx="3" fill="#fff" stroke="#ddd" stroke-width="1"/><text x="5" y="14" font-family="Arial,sans-serif" font-weight="700" font-size="6" fill="#231F20" letter-spacing="0.2">DISCOVER</text><circle cx="38" cy="18" r="11" fill="#F76F20"/></svg>',
        jcb: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 24" width="38" height="24"><rect width="38" height="24" rx="3" fill="#fff" stroke="#ddd" stroke-width="1"/><rect x="5" y="3" width="8" height="18" rx="2" fill="#003087"/><rect x="15" y="3" width="8" height="18" rx="2" fill="#CC0000"/><rect x="25" y="3" width="8" height="18" rx="2" fill="#009F6B"/></svg>',
        diners: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 24" width="38" height="24"><rect width="38" height="24" rx="3" fill="#fff" stroke="#ddd" stroke-width="1"/><circle cx="17" cy="12" r="8" fill="none" stroke="#004A97" stroke-width="2"/><circle cx="21" cy="12" r="8" fill="none" stroke="#004A97" stroke-width="2"/></svg>',
    };

    function detectCardType(num) {
        var n = num.replace(/\D/g, '');
        if (!n.length) return null;
        if (/^4/.test(n)) return 'visa';
        if (/^(5[1-5]|2[2-7])/.test(n)) return 'mastercard';
        if (/^3[47]/.test(n)) return 'amex';
        if (/^(6011|622[12][6-9]\d|622[2-8]\d{2}|6229[01]\d|62292[0-5]|64[4-9]|65)/.test(n)) return 'discover';
        if (/^35(2[89]|[3-8])/.test(n)) return 'jcb';
        if (/^3[068]/.test(n)) return 'diners';
        return null;
    }

    function updateCardBrand(value) {
        var type = detectCardType(value);
        var $badge = $('#cardBrandBadge');
        if (type && CARD_SVGS[type]) {
            $badge.html(CARD_SVGS[type]).css('display', 'inline-flex');
        } else {
            $badge.hide().html('');
        }
    }

    // ── Input formatters ──────────────────────────────────────────────────────

    // Card number: digits only, auto-space every 4 digits (e.g. "4111 1111 1111 1111")
    $('#cardNumber').on('input', function () {
        var pos = this.selectionStart;
        var before = $(this).val().substring(0, pos).replace(/\D/g, '').length;
        var digits = $(this).val().replace(/\D/g, '').substring(0, 19);
        var fmt = digits.replace(/(.{4})(?=.)/g, '$1 ');
        $(this).val(fmt);
        // Restore cursor accounting for added spaces
        var spaces = (fmt.substring(0, Math.min(pos + Math.floor(before / 4), fmt.length)).match(/ /g) || []).length;
        var newPos = Math.min(before + spaces, fmt.length);
        try { this.setSelectionRange(newPos, newPos); } catch(e) {}
        updateCardBrand($(this).val());
    });

    // Card expiry: auto-insert slash after 2 digits (12 → 12/ → 12/2 → 12/28)
    $('#cardExpiry').on('input', function (e) {
        var raw = $(this).val().replace(/\D/g, '').substring(0, 4);
        $(this).val(raw.length > 2 ? raw.substring(0, 2) + '/' + raw.substring(2) : raw);
    });

    // CVV: digits only, max 4
    $('#cardCvv').on('input', function () {
        $(this).val($(this).val().replace(/\D/g, '').substring(0, 4));
    });

    // Coupon: uppercase on input
    $('#couponField').on('input', function () {
        var pos = this.selectionStart;
        $(this).val($(this).val().toUpperCase());
        try { this.setSelectionRange(pos, pos); } catch(e) {}
    });

    // Name fields: strip digits and most symbols on paste
    $('[name="first_name"],[name="last_name"]').on('input', function () {
        $(this).val($(this).val().replace(/[0-9!@#$%^&*()_+=\[\]{};:"\\|,<>?/~`]/g, ''));
    });

    // ── Custom validators ─────────────────────────────────────────────────────

    // Phone via intl-tel-input
    $.validator.addMethod('validPhone', function (value, element) {
        if (!iti || !phoneEl.value.trim()) return true;
        return iti.isValidNumber();
    }, 'Please enter a valid phone number with country code.');

    // Name: letters (incl. accented), spaces, hyphens, apostrophes, periods only
    $.validator.addMethod('nameChars', function (value, element) {
        return this.optional(element) || /^[A-Za-zÀ-ÖØ-öø-ÿ'\-\. ]+$/.test(value.trim());
    }, 'Please use letters, spaces, hyphens and apostrophes only.');

    // Card number: strip spaces then check 12–19 digits
    $.validator.addMethod('cardNumber', function (value, element) {
        if (this.optional(element)) return true;
        return /^\d{12,19}$/.test(value.replace(/\s/g, ''));
    }, 'Please enter a valid card number (12–19 digits).');

    // Card expiry: MM/YY format, valid month, not expired
    $.validator.addMethod('cardExpiry', function (value, element) {
        if (this.optional(element)) return true;
        var m = value.trim().match(/^(\d{2})\/?(\d{2})$/);
        if (!m) return false;
        var month = parseInt(m[1], 10);
        var year  = parseInt(m[2], 10) + 2000;
        if (month < 1 || month > 12) return false;
        var now = new Date();
        return new Date(year, month - 1, 1) >= new Date(now.getFullYear(), now.getMonth(), 1);
    }, 'Expiry date is invalid or the card has expired.');

    // Coupon: uppercase alphanumeric + hyphens + underscores
    $.validator.addMethod('couponFormat', function (value, element) {
        return this.optional(element) || /^[A-Z0-9\-_]+$/i.test(value.trim());
    }, 'Coupon may only contain letters, numbers, hyphens and underscores.');

    // ── Payment toggle ────────────────────────────────────────────────────────
    var $payFields = $('#paymentFields');
    var $reqPay    = $('#requiresPayment');
    var $submitBtn = $('#regSubmit');

    function isPaid() { return $reqPay.val() === '1'; }

    function resetCoupon() {
        lastCouponUnitAmount = null;
        $('#couponField').val('');
        $('#couponStatus').hide().text('').removeClass('ok err');
        $('#couponPricePrev').hide().html('');
        updateSubmitLabel();
    }

    function updateSubmitLabel(override) {
        if (!$submitBtn.length) return;
        if (override !== undefined) { $submitBtn.text(override); return; }
        var price = totalTicketPrice();
        var count = guestCount();
        if (price > 0) {
            var label = 'Pay $' + price.toFixed(0) + ' & register';
            if (ALLOW_MULTI && count > 1) label += ' (' + count + ' guests)';
            $submitBtn.text(label);
        } else {
            $submitBtn.text(count > 1 ? ('Confirm free registration (' + count + ' guests)') : 'Confirm free registration');
        }
    }

    function clearCardValidation() {
        if (!window._validator) return;
        ['card_number', 'card_expiration', 'card_cvv'].forEach(function (n) {
            var $el = $('[name="' + n + '"]');
            if (!$el.length) return;
            var el = $el[0];
            $el.removeClass('error valid');
            window._validator.errorsFor(el).remove();
            if (window._validator.invalid) delete window._validator.invalid[n];
            if (window._validator.submitted) delete window._validator.submitted[n];
        });
    }

    function togglePayment() {
        var price = unitTicketPrice();
        var paid  = price > 0;
        $payFields.toggleClass('ev-is-hidden', !paid);
        $reqPay.val(paid ? '1' : '0');
        // Sync HTML required attribute so browser/AT know these are mandatory when visible
        ['cardNumber', 'cardExpiry', 'cardCvv'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.required = paid;
        });
        updateSubmitLabel();
        // Don't re-validate card fields on ticket change — only clear stale errors.
        // Card validation should run on blur / submit after the user interacts.
        clearCardValidation();
    }
    togglePayment();

    // Multi-guest UI init (needs updateSubmitLabel)
    if (guestsList) {
        guestsList.querySelectorAll('.ev-guest').forEach(bindGuestBlock);
        reindexGuests();
    }
    if (addGuestBtn && guestTpl) {
        addGuestBtn.addEventListener('click', function () {
            if (guestCount() >= MAX_GUESTS) return;
            var html = guestTpl.innerHTML
                .replace(/__INDEX__/g, String(guestCount()))
                .replace(/__NUM__/g, String(guestCount() + 1));
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            var block = wrap.firstElementChild;
            guestsList.appendChild(block);
            bindGuestBlock(block);
            reindexGuests();
        });
    }

    // ── jQuery Validate ───────────────────────────────────────────────────────
    var $form = $('#registrationForm');
    if ($form.length) {
        var rules = {
            ticket_id:       { required: true },
            card_number:     { required: { depends: isPaid }, cardNumber: true },
            card_expiration: { required: { depends: isPaid }, cardExpiry: true },
            card_cvv:        { digits: true, minlength: 3, maxlength: 4 },
            coupon_code:     { couponFormat: true, maxlength: 64 },
        };
        var messages = {
            ticket_id: {
                required: 'Please select a ticket to continue.',
            },
            card_number: {
                required:   'Card number is required.',
                cardNumber: 'Please enter a valid card number (12–19 digits).',
            },
            card_expiration: {
                required:   'Expiry date is required.',
                cardExpiry: 'Expiry date is invalid or the card has expired.',
            },
            card_cvv: {
                digits:    'CVV must contain digits only.',
                minlength: 'CVV must be 3 or 4 digits.',
                maxlength: 'CVV must be 3 or 4 digits.',
            },
            coupon_code: {
                couponFormat: 'Coupon may only contain letters, numbers, hyphens and underscores.',
                maxlength:    'Coupon code cannot exceed 64 characters.',
            },
        };

        if (ALLOW_MULTI) {
            // Dynamic guest fields validated via HTML5 required + custom submit checks.
        } else {
            rules.email        = { required: true, email: true, maxlength: 255 };
            rules.first_name   = { required: true, nameChars: true, maxlength: 100 };
            rules.last_name    = { required: true, nameChars: true, maxlength: 100 };
            rules.phone        = { validPhone: true };
            rules.job_title    = { maxlength: 255 };
            rules.company_name = { maxlength: 255 };
            rules.linkedin_url = { url: true, maxlength: 500 };
            messages.email = {
                required:  'Email address is required.',
                email:     'Please enter a valid email address.',
                maxlength: 'Email address is too long.',
            };
            messages.first_name = {
                required:  'First name is required.',
                nameChars: 'Please use letters, spaces, hyphens and apostrophes only.',
                maxlength: 'First name cannot exceed 100 characters.',
            };
            messages.last_name = {
                required:  'Last name is required.',
                nameChars: 'Please use letters, spaces, hyphens and apostrophes only.',
                maxlength: 'Last name cannot exceed 100 characters.',
            };
            messages.linkedin_url = {
                url: 'Please enter a valid LinkedIn URL.',
            };
        }

        window._validator = $form.validate({
            errorElement: 'label',
            errorClass: 'error',
            validClass: 'valid',

            errorPlacement: function (error, element) {
                if (element.attr('type') === 'radio') {
                    error.insertAfter($('[name="ticket_id"]').last().closest('.ev-tk-opt'));
                } else {
                    var $fld = element.closest('.ev-fld');
                    if ($fld.length) $fld.append(error);
                    else error.insertAfter(element);
                }
            },

            highlight: function (element) {
                $(element).addClass('error').removeClass('valid');
                if ($(element).attr('id') === 'phoneInput') {
                    $(element).closest('.ev-fld').find('.iti').addClass('iti-err');
                }
            },
            unhighlight: function (element) {
                $(element).removeClass('error').addClass('valid');
                if ($(element).attr('id') === 'phoneInput') {
                    $(element).closest('.ev-fld').find('.iti').removeClass('iti-err');
                }
            },

            rules: rules,
            messages: messages,

            submitHandler: function (form) {
                if (ALLOW_MULTI && guestCount() > MAX_GUESTS) {
                    alert('You can register at most ' + MAX_GUESTS + ' guests for this event.');
                    return;
                }
                // E.164 phone number (single-guest mode)
                if (iti && phoneEl && phoneEl.value.trim()) {
                    phoneEl.value = iti.getNumber();
                }
                // Strip card number spaces before sending to API
                var cardEl = document.getElementById('cardNumber');
                if (cardEl) cardEl.value = cardEl.value.replace(/\s/g, '');
                $submitBtn.prop('disabled', true).text('Processing…');
                form.submit();
            },
        });
    }

    // ── Coupon preview on blur ────────────────────────────────────────────────
    var cpnTimer = null;
    $('#couponField').on('blur', function () {
        var code = $(this).val().trim();
        if (!code) { resetCoupon(); return; }
        var sel = document.querySelector('.ev-tk-real:checked');
        if (!sel) return;

        clearTimeout(cpnTimer);
        cpnTimer = setTimeout(function () {
            $('#couponStatus').text('Checking coupon…').removeClass('ok err').show();
            $('#couponPricePrev').hide().html('');

            fetch(PREVIEW_URL, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  CSRF,
                },
                body: JSON.stringify({ coupon_code: code, ticket_id: parseInt(sel.value) }),
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (res.ok && res.data && res.data.data) {
                    var pricing  = (res.data.data && res.data.data.pricing) || {};
                    var label    = (pricing.coupon && pricing.coupon.label) ? pricing.coupon.label : '✓ Coupon applied!';
                    var original = pricing.original != null ? parseFloat(pricing.original) : null;
                    var final    = pricing.amount   != null ? parseFloat(pricing.amount)   : null;
                    var discount = pricing.discount != null ? parseFloat(pricing.discount) : null;
                    var count    = guestCount();

                    lastCouponUnitAmount = final;
                    $('#couponStatus').text(label).addClass('ok').removeClass('err');

                    if (final !== null) {
                        var html = '';
                        if (original !== null) html += '<s style="color:var(--t3)">$' + (original * count).toFixed(2) + '</s> → ';
                        html += '<strong>$' + (final * count).toFixed(2) + '</strong>';
                        if (discount !== null) html += ' <span style="color:var(--t2)">(saving $' + (discount * count).toFixed(2) + ')</span>';
                        if (count > 1) html += ' <span style="color:var(--t2)">· ' + count + ' guests</span>';
                        $('#couponPricePrev').html(html).show();
                        updateSubmitLabel('Pay $' + (final * count).toFixed(0) + ' & register');
                    }
                } else {
                    lastCouponUnitAmount = null;
                    var msg = (res.data && res.data.message) ? res.data.message : 'Invalid or expired coupon.';
                    $('#couponStatus').text(msg).addClass('err').removeClass('ok');
                    updateSubmitLabel(); // restore original price label
                }
            })
            .catch(function () {
                lastCouponUnitAmount = null;
                $('#couponStatus').text('Could not check coupon. Try again.').addClass('err').removeClass('ok');
            });
        }, 300);
    });

    $('#couponField').on('input', function () {
        if (!$(this).val().trim()) resetCoupon();
    });

    // ── "Select" buttons in left-column ticket cards ──────────────────────────
    document.querySelectorAll('.ev-selbtn[data-selticket]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = parseInt(btn.dataset.selticket);
            var matched = false;
            document.querySelectorAll('.ev-tk-opt:not(.ev-tk-dis)').forEach(function (opt) {
                if (parseInt(opt.dataset.tk) === id) { opt.click(); matched = true; }
            });
            if (!matched) return; // ticket is sold out — no sidebar entry
            var fc = document.getElementById('evFormCard');
            if (fc) fc.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // ── Hero CTA → scroll to sidebar ─────────────────────────────────────────
    var $heroCta = document.getElementById('heroCta');
    if ($heroCta) {
        $heroCta.addEventListener('click', function (e) {
            var target = document.getElementById('register-form');
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    }

}(jQuery));

// ── Speaker modal ─────────────────────────────────────────────────────────
(function () {
    var modal   = document.getElementById('spkModal');
    var btnClose = document.getElementById('spkModalClose');
    if (!modal) return;

    function openModal(btn) {
        var name    = btn.dataset.name        || '';
        var desig   = btn.dataset.designation || '';
        var bio     = btn.dataset.bio         || '';
        var image   = btn.dataset.image       || '';
        var initial = btn.dataset.initial     || '';
        var li      = btn.dataset.linkedin    || '';

        var av = document.getElementById('spkModalAv');
        if (image) {
            av.className = 'spk-modal-av';
            av.style.backgroundImage = 'url(' + image + ')';
            av.textContent = '';
        } else {
            av.className = 'spk-modal-av init';
            av.style.backgroundImage = '';
            av.textContent = initial;
        }

        document.getElementById('spkModalName').textContent = name;
        document.getElementById('spkModalRole').textContent = desig;
        document.getElementById('spkModalBio').textContent  = bio;

        var liBtn = document.getElementById('spkModalLi');
        if (li) {
            liBtn.href = li;
            liBtn.classList.remove('ev-is-hidden');
        } else {
            liBtn.classList.add('ev-is-hidden');
        }

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.spk-more');
        if (btn) { openModal(btn); return; }
        if (e.target === modal) closeModal();
    });

    btnClose.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });
}());
</script>
@endpush
