<section class="blk" id="events" style="padding-top:10px">
    <div class="wrap">
        <div class="sec-head">
            <div>
                <span class="eyebrow">What's on</span>
                <h2 class="sec-t">Upcoming events</h2>
                <p class="sec-s">Dinner mixers, networking nights and workshops across every AMCOB chapter.</p>
            </div>
            <a class="btn btn-ghost" href="#calendar">View calendar</a>
        </div>

        @php
            $hasSearch  = $activeCity || $activeState || $activeCountry;
            $searchDesc = implode(', ', array_filter([$activeCity, $activeState, $activeCountry]));
        @endphp
        @if ($hasSearch)
        <div class="search-notice">
            <span>Showing events in <b>{{ $searchDesc }}</b></span>
            <a href="{{ route('home', array_filter(['tag' => $activeTag, 'direction' => $activeDirection !== 'asc' ? $activeDirection : null])) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
                Clear search
            </a>
        </div>
        @endif

        <div class="filters">
            <a href="{{ route('home') }}"
               class="pill {{ !$activeTag ? 'active' : '' }}">All</a>
            @foreach ([
                'Dinner Mixer' => 'Dinner Mixers',
                'Networking'   => 'Networking',
                'Workshop'     => 'Workshops',
                'Gala'         => 'Galas',
                'Webinar'      => 'Webinars',
            ] as $tagValue => $tagLabel)
                <a href="{{ route('home', ['tag' => $tagValue]) }}"
                   class="pill {{ $activeTag === $tagValue ? 'active' : '' }}">{{ $tagLabel }}</a>
            @endforeach
            <div class="sortsel">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M6 12h12M10 18h4"/>
                </svg>
                Sort: <select onchange="window.location.href=this.value">
                    <option value="{{ route('home', array_filter(['tag' => $activeTag])) }}"
                            {{ $activeDirection === 'asc' ? 'selected' : '' }}>Soonest</option>
                    <option value="{{ route('home', array_filter(['tag' => $activeTag, 'direction' => 'desc'])) }}"
                            {{ $activeDirection === 'desc' ? 'selected' : '' }}>Latest first</option>
                </select>
            </div>
        </div>

        <div class="egrid">
            @forelse ($events as $event)
                @include('events._card', ['past' => false])
            @empty
                <p class="sec-s" style="grid-column:1/-1;padding:40px 0;text-align:center">
                    No upcoming events found.
                    @if ($activeTag)
                        <a href="{{ route('home') }}">Clear filter</a>
                    @endif
                </p>
            @endforelse
        </div>

        @if ($meta && ($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
            <div class="loadmore">
                <a class="btn btn-navy"
                   href="{{ route('home', array_merge(request()->only('tag', 'direction'), ['page' => ($meta['current_page'] ?? 1) + 1])) }}">
                    Load more events
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</section>
