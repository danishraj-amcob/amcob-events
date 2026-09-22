@if (!empty($featured) && is_array($featured))
@php
    $featured = is_array($featured) ? $featured : [];
    $featuredDetail = is_array($featuredDetail ?? null) ? $featuredDetail : [];

    $title    = $featured['title'] ?? 'Featured event';
    $slug     = $featured['slug'] ?? null;
    $timezone = $featured['timezone'] ?? 'UTC';
    $eventUrl = $slug ? route('events.show', $slug) : '#';

    $cover    = is_array($featured['cover'] ?? null) ? $featured['cover'] : [];
    $coverUrl = $cover['url'] ?? null;
    $heroGradient = $cover['gradient'] ?? 'linear-gradient(135deg,#2D3C69,#3a4b82)';

    $spotsLeft        = $featured['spots_remaining'] ?? null;
    $registrationOpen = !empty($featured['registration_open']);
    $featuredIsOnline = !empty($featured['is_online']);
    $registrationCount = (int) ($featured['registration_count'] ?? 0);
    $locationLabel    = $featured['location_label'] ?? null;
    $eventType        = $featured['event_type'] ?? null;
    $shortDescription = $featured['short_description'] ?? null;

    $heroDate = null;
    if (!empty($featured['starts_at'])) {
        try {
            $heroDate = \Illuminate\Support\Carbon::parse($featured['starts_at'])->timezone($timezone);
        } catch (\Throwable $e) {
            $heroDate = null;
        }
    }

    $featuredHasFree = !empty(array_filter(
        is_array($featuredDetail['tickets'] ?? null) ? $featuredDetail['tickets'] : [],
        fn($t) => ($t['is_free'] ?? false) || (($t['price'] ?? 0) == 0)
    ));
@endphp
<header class="hero">
    <div class="wrap hero-grid reveal show">
        <div class="hero-media">
            <div class="frame" style="background:{{ $heroGradient }}">
                @if ($coverUrl)
                    <img src="{{ $coverUrl }}" alt="{{ $title }}" fetchpriority="high" width="800" height="500" style="max-width:100%;height:100%;object-fit:cover">
                @endif
            </div>
            <span class="hpill dark p-tl">
                <span class="d"></span>
                {{ $registrationOpen ? 'REGISTRATION OPEN' : 'REGISTRATION CLOSED' }}
            </span>
            @if ($registrationCount > 0)
            <span class="hpill light p-bl">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                {{ $registrationCount }} going
            </span>
            @endif
            <span class="hpill gold p-br">
                @if ($featuredIsOnline)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" width="14" height="14"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Live stream
                @elseif ($featuredHasFree)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" width="14" height="14"><path d="M20 6 9 17l-5-5"/></svg>
                    Free · RSVP
                @elseif ($registrationOpen && is_numeric($spotsLeft) && $spotsLeft > 0 && $spotsLeft <= 20)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" width="14" height="14"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    {{ $spotsLeft }} spots left
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" width="14" height="14"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.123 2.123 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                    Featured
                @endif
            </span>
        </div>
        <div class="hero-copy">
            <span class="kick">✦ Featured{{ $eventType ? ' · ' . ucfirst(str_replace('_', ' ', $eventType)) : '' }}</span>
            <h1>{{ $title }}</h1>
            <div class="hero-meta">
                @if ($heroDate)
                <div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    {{ $heroDate->format('D, M j, Y · g:i A') }} {{ $heroDate->format('T') }}
                </div>
                @endif
                @if ($locationLabel)
                <div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    {{ $locationLabel }}
                </div>
                @endif
                @if ($shortDescription)
                <div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7h-9M14 17H5M17 3l3 4-3 4M7 21l-3-4 3-4"/>
                    </svg>
                    {{ Str::limit($shortDescription, 60) }}
                </div>
                @endif
            </div>
            @if ($slug)
            <div class="hero-cta">
                @if ($registrationOpen)
                    <a class="btn btn-gold" href="{{ $eventUrl }}">
                        Register Now
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                @else
                    <a class="btn btn-glass" href="{{ $eventUrl }}">View Details</a>
                @endif
            </div>
            @endif
            <div class="hero-price">
                <div class="avstack">
                    <i style="background:#4b5fa0"></i>
                    <i style="background:#2d3c69"></i>
                    <i style="background:#c99b3f"></i>
                    <i style="background:#3a4b82"></i>
                </div>
                <span>
                    @if (!empty($priceTiers) && is_array($priceTiers))
                        @foreach ($priceTiers as $i => $tier)
                            @if ($i > 0) &middot; @endif
                            {{ $tier['name'] ?? 'Ticket' }} <b>${{ number_format((float) ($tier['price'] ?? 0), 0) }}</b>
                        @endforeach
                    @elseif (($priceLabel ?? null) === 'Free')
                        <b>Free</b> event &middot; RSVP required
                    @elseif ($priceLabel ?? null)
                        @if ($registrationCount > 0)
                            {{ $registrationCount }} registered &middot; <b>{{ $priceLabel }}</b>
                        @else
                            <b>{{ $priceLabel }}</b>
                        @endif
                    @elseif ($registrationCount > 0)
                        {{ $registrationCount }} registered
                    @endif
                </span>
            </div>
        </div>
    </div>
</header>
@endif
