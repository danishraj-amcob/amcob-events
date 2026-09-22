{{--
    Expects:
    - $event (array) — single event item from GET /events
    - $past  (bool)  — true when rendering the past-events panel

    All API keys are optional during development — missing fields never crash the view.
--}}
@php
    use Illuminate\Support\Carbon;

    $event = is_array($event ?? null) ? $event : [];
    $past  = (bool) ($past ?? false);

    $title    = $event['title'] ?? 'Untitled event';
    $slug     = $event['slug'] ?? null;
    $timezone = $event['timezone'] ?? 'UTC';
    $eventUrl = $slug ? route('events.show', $slug) : '#';

    $cover    = is_array($event['cover'] ?? null) ? $event['cover'] : [];
    $platform = is_array($event['platform'] ?? null) ? $event['platform'] : [];
    $tags     = is_array($event['tags'] ?? null) ? $event['tags'] : [];

    $coverUrl      = $cover['url'] ?? null;
    $coverGradient = $cover['gradient'] ?? 'linear-gradient(135deg,#2D3C69,#3a4b82)';
    $bgStyle       = 'background:' . $coverGradient;
    $platformName  = $platform['name'] ?? null;
    $hostData      = is_array($event['hosted_by'] ?? null)
        ? $event['hosted_by']
        : (is_array($event['host'] ?? null) ? $event['host'] : []);
    $hostName      = $hostData['name'] ?? null;

    $isLive           = !$past && !empty($event['is_current']);
    $isOnline         = !empty($event['is_online']);
    $registrationOpen = !empty($event['registration_open']);
    $spotsRemaining   = $event['spots_remaining'] ?? null;
    $isSoldOut        = is_numeric($spotsRemaining) && (int) $spotsRemaining <= 0;
    $registrationCount = (int) ($event['registration_count'] ?? 0);
    $locationLabel    = $event['location_label'] ?? null;
    $eventType        = $event['event_type'] ?? null;

    $catLabel = !empty($tags[0]['name'])
        ? $tags[0]['name']
        : ($eventType ? ucfirst(str_replace('_', ' ', $eventType)) : null);

    $dt = null;
    if (!empty($event['starts_at'])) {
        try {
            $dt = Carbon::parse($event['starts_at'])->timezone($timezone);
        } catch (\Throwable $e) {
            $dt = null;
        }
    }

    $cardPrice = null;
    if (!$past) {
        if (!empty($event['price_label'])) {
            $cardPrice = $event['price_label'];
        } elseif (array_key_exists('min_price', $event) && $event['min_price'] !== null) {
            $cardPrice = ((float) $event['min_price'] == 0.0)
                ? 'Free'
                : 'From $' . number_format((float) $event['min_price'], 0);
        }
    }
@endphp
<article class="ecard reveal show{{ $past ? ' past' : ($isLive ? ' live' : '') }}">
    <div class="thumb" style="{{ $bgStyle }}">
        @if ($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $title }}" loading="lazy" class="thumb-img" onload="this.classList.add('img-loaded')">
        @endif

        {{-- Date badge — top left --}}
        @if ($isLive)
            <div class="datebadge">
                <b style="font-size:13px;color:#DC4C4C">LIVE</b>
                <span>NOW</span>
            </div>
        @elseif ($dt)
            <div class="datebadge">
                <b>{{ $dt->format('j') }}</b>
                <span>{{ strtoupper($dt->format('M')) }}</span>
            </div>
        @endif

        {{-- Status tag — top right --}}
        @if ($isLive)
            <div class="tag livetag"><i></i>LIVE</div>
        @elseif ($past)
            <div class="tag done">
                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" style="vertical-align:-1px"><path d="M20 6 9 17l-5-5"/></svg>
                Held
            </div>
        @elseif (!$registrationOpen)
            <div class="tag" style="background:var(--t3)">Closed</div>
        @elseif ($isSoldOut)
            <div class="tag" style="background:var(--red)">Sold out</div>
        @elseif ($catLabel)
            <div class="tag">{{ $catLabel }}</div>
        @endif

        @if ($cardPrice)
            <div class="pricepill">{{ $cardPrice }}</div>
        @endif

        @if ($isOnline)
            <div class="onlinepill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                Online
            </div>
        @endif
    </div>

    <div class="body">
        @if ($dt || $platformName)
        <div class="card-date-row">
            @if ($dt)
                <span class="card-date">{{ $dt->format('D, M j, Y · g:i A') }} <span class="card-tz">{{ $dt->format('T') }}</span></span>
            @else
                <span></span>
            @endif
            @if ($platformName)
                <span class="cbadge cbadge-platform card-platform-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    {{ strtoupper($platformName) }}
                </span>
            @endif
        </div>
        @endif

        <h3>
            @if ($slug)
                <a href="{{ $eventUrl }}" style="color:inherit;text-decoration:none">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
        </h3>

        <div class="cbadges">
            @if ($catLabel)
                <span class="cbadge cbadge-cat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    {{ $catLabel }}
                </span>
            @endif
            @if ($isOnline)
                <span class="cbadge cbadge-online">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Online
                </span>
            @else
                <span class="cbadge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    In-Person
                </span>
            @endif
        </div>

        @if ($locationLabel)
        <div class="loc">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            {{ $locationLabel }}
        </div>
        @endif
        @if ($hostName)
        <div class="card-host">
            Hosted by {{ $hostName }}
        </div>
        @endif

        <div class="foot">
            @if ($registrationCount > 0)
            <div class="attend">
                <i style="background:#4b5fa0"></i>
                <i style="background:#2d3c69"></i>
                <i style="background:#c99b3f"></i>
                <span>{{ $registrationCount }} {{ $past ? 'attended' : 'going' }}</span>
            </div>
            @else
            <div></div>
            @endif
            @if ($slug)
                @if ($past)
                    <a class="reg" href="{{ $eventUrl }}">
                        View recap
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                @elseif ($registrationOpen)
                    <a class="reg{{ $isLive ? ' join' : '' }}" href="{{ $eventUrl }}">
                        {{ $isLive ? 'Join now' : 'Register' }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                @endif
            @endif
        </div>
    </div>
</article>
