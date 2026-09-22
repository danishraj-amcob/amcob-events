@php
    $hasSearch  = $activeCity || $activeState || $activeCountry;
    $searchDesc = implode(', ', array_filter([$activeCity, $activeState, $activeCountry]));
@endphp

<section class="blk" id="events" style="padding-top:10px">
    <div class="wrap">
        <div class="sec-head">
            <div>
                <span class="eyebrow">Discover What's On</span>
                <h2 class="sec-t">Explore Events</h2>
                <p class="sec-s" id="evSub">Dinner mixers, networking nights and workshops across every AMCOB chapter.</p>
            </div>
        </div>

        <div class="ev-layout">
            <div class="ev-main">
                <div class="ev-toolbar">
                    <div class="seg" id="evSeg" role="tablist">
                        <button class="seg-btn active" data-tab="all" aria-selected="true">
                            All
                            <span class="cnt" id="cnt-all">…</span>
                        </button>
                        <button class="seg-btn" data-tab="current" aria-selected="false">
                            <span class="livedot"></span>
                            Happening now
                            <span class="cnt" id="cnt-current">{{ count($currentEvents ?? []) }}</span>
                        </button>
                        <button class="seg-btn" data-tab="upcoming" aria-selected="false">
                            Upcoming
                            <span class="cnt" id="cnt-upcoming">{{ count($events ?? []) }}</span>
                        </button>
                        <button class="seg-btn" data-tab="past" aria-selected="false">
                            Past
                            <span class="cnt" id="cnt-past">{{ count($pastEvents ?? []) }}</span>
                        </button>
                    </div>
                    <div class="toolbar-filters">
                        @if (!empty($platforms))
                        <div class="sortsel">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            <select id="platformFilter">
                                <option value="">All platforms</option>
                                @foreach ($platforms as $pl)
                                    @continue(empty($pl['id']) || empty($pl['name']))
                                    <option value="{{ $pl['id'] }}" {{ ($activePlatform ?? '') === $pl['id'] ? 'selected' : '' }}>{{ strtoupper($pl['name']) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="sortsel">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
                            <select id="sortSelect">
                                <option value="asc"  {{ $activeDirection === 'asc'  ? 'selected' : '' }}>Soonest</option>
                                <option value="desc" {{ $activeDirection === 'desc' ? 'selected' : '' }}>Latest first</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Active filter chips — above all panels, populated by JS --}}
                <div id="activeFilters" class="active-filters"></div>

                {{-- ALL PANEL --}}
                <div id="panel-all">
                    <div class="egrid" id="grid-all"></div>
                    <div id="lw-all"></div>
                </div>

                {{-- UPCOMING PANEL --}}
                <div id="panel-upcoming" style="display:none">
                    <div class="egrid" id="grid-upcoming">
                        @forelse ($events as $event)
                            @include('events._card', ['past' => false])
                        @empty
                            <p class="sec-s" style="grid-column:1/-1;padding:40px 0;text-align:center">No upcoming events found.</p>
                        @endforelse
                    </div>
                    <div id="lw-upcoming">
                        @if ($meta && ($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
                            <div class="loadmore">
                                <button class="btn btn-navy" id="loadMoreBtn-upcoming" data-tab="upcoming" data-page="{{ ($meta['current_page'] ?? 1) + 1 }}">
                                    Load more events
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" width="16" height="16"><path d="M6 9l6 6 6-6"/></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- HAPPENING NOW PANEL --}}
                <div id="panel-current" style="display:none">
                    <div class="egrid" id="grid-current">
                        @if (count($currentEvents))
                            @foreach ($currentEvents as $event)
                                @include('events._card', ['past' => false])
                            @endforeach
                        @else
                            <p class="sec-s" style="grid-column:1/-1;padding:60px 0;text-align:center">No live events right now — check back soon!</p>
                        @endif
                    </div>
                    <div id="lw-current"></div>
                </div>

                {{-- PAST PANEL --}}
                <div id="panel-past" style="display:none">
                    <div class="egrid" id="grid-past">
                        @if (count($pastEvents))
                            @foreach ($pastEvents as $event)
                                @include('events._card', ['past' => true])
                            @endforeach
                        @else
                            <p class="sec-s" style="grid-column:1/-1;padding:60px 0;text-align:center">No past events yet.</p>
                        @endif
                    </div>
                    <div id="lw-past"></div>
                </div>
            </div>

            @if (!empty($categories))
            <aside class="ev-catlist" aria-label="Categories">
                <div class="ev-catlist-head">
                    <h3 class="ev-catlist-title">Explore Categories</h3>
                    <p class="ev-catlist-sub">Filter events by type</p>
                </div>
                <ul class="ev-catlist-items" id="evCatList">
                    <li>
                        <button type="button" class="ev-catlink {{ empty($activeTag) ? 'is-active' : '' }}" data-tag="" data-label="All">
                            <span>All Events</span>
                        </button>
                    </li>
                    @foreach ($categories as $cat)
                        @php
                            $catValue = $cat['value'] ?? '';
                            $catLabel = $cat['label'] ?? $cat['name'] ?? $catValue;
                        @endphp
                        <li>
                            <button type="button"
                                class="ev-catlink {{ ($activeTag ?? '') === $catValue ? 'is-active' : '' }}"
                                data-tag="{{ $catValue }}"
                                data-label="{{ $catLabel }}">
                                <span>{{ $catLabel }}</span>
                                @if (isset($cat['count']))
                                    <em>{{ $cat['count'] }}</em>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </aside>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    'use strict';

    var CARDS_URL = '{{ route("events.cards") }}';

    // ── Shared filter state ───────────────────────────────────────────────────
    var F = {
        tag:       '{{ $activeTag ?? "" }}',
        direction: '{{ $activeDirection ?? "asc" }}',
        platform:  '{{ $activePlatform ?? "" }}',
        city:      '{{ $activeCity ?? "" }}',
        state:     '{{ $activeState ?? "" }}',
        country:   '{{ $activeCountry ?? "" }}',
    };

    // Per-tab page cursor
    var tabPage  = { all: 1, upcoming: 1, current: 1, past: 1 };
    // Whether a tab's content is out of date with current filters
    var tabStale = { all: false, upcoming: false, current: false, past: false };

    var activeTab = 'all';

    var TAB_SUBS = {
        all:      'Browse every AMCOB event — happening now, upcoming, and past.',
        upcoming: 'Dinner mixers, networking nights and workshops across every AMCOB chapter.',
        current:  'Happening right now — jump into a live AMCOB session.',
        past:     'Relive the highlights — photos, turnout and moments from recent AMCOB gatherings.',
    };

    // Server-rendered upcoming/current/past content may not reflect filters; mark stale so
    // they refresh when visited. All tab is always fetched fresh on page load.
    tabStale.upcoming = true;
    tabStale.current  = true;
    tabStale.past     = true;

    // ── Skeleton ─────────────────────────────────────────────────────────────
    function skeleton(n) {
        var h = '';
        for (var i = 0; i < n; i++) {
            h += '<article class="ecard sk-card">' +
                 '<div class="thumb" style="background:var(--line)"></div>' +
                 '<div class="body">' +
                 '<div class="sk-line" style="width:45%;margin-bottom:10px"></div>' +
                 '<div class="sk-line" style="width:90%;height:18px;margin-bottom:8px"></div>' +
                 '<div class="sk-line" style="width:70%;margin-bottom:20px"></div>' +
                 '<div class="sk-line" style="width:50%"></div>' +
                 '</div></article>';
        }
        return h;
    }

    // ── Build query string from current filters + tab ────────────────────────
    function buildQS(tab, page) {
        var p = { tab: tab };
        if (F.tag)       p.tag       = F.tag;
        if (F.direction && F.direction !== 'asc') p.direction = F.direction;
        if (F.platform)  p.platform  = F.platform;
        if (F.city)      p.city      = F.city;
        if (F.state)     p.state     = F.state;
        if (F.country)   p.country   = F.country;
        if (page > 1)    p.page      = page;
        return new URLSearchParams(p).toString();
    }

    // ── Fetch a tab's cards ───────────────────────────────────────────────────
    function fetchTab(tab, append) {
        var grid = document.getElementById('grid-' + tab);
        var lw   = document.getElementById('lw-' + tab);
        var page = tabPage[tab];

        if (!grid) return;

        if (!append) {
            grid.innerHTML = skeleton(tab === 'current' ? 3 : 6);
            if (lw) lw.innerHTML = '';
        } else {
            if (lw) lw.innerHTML = '<div class="loadmore"><span class="sk-line" style="width:140px;height:44px;border-radius:99px;display:block;margin:auto"></span></div>';
        }

        var qs = buildQS(tab, page);

        fetch(CARDS_URL + '?' + qs)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                tabStale[tab] = false;

                var empty = tab === 'current'
                    ? '<p class="sec-s" style="grid-column:1/-1;padding:60px 0;text-align:center">No live events right now — check back soon!</p>'
                    : tab === 'past'
                    ? '<p class="sec-s" style="grid-column:1/-1;padding:60px 0;text-align:center">No past events found.</p>'
                    : tab === 'all'
                    ? '<p class="sec-s" style="grid-column:1/-1;padding:40px 0;text-align:center">No events found.</p>'
                    : '<p class="sec-s" style="grid-column:1/-1;padding:40px 0;text-align:center">No upcoming events found.</p>';

                if (append) {
                    grid.insertAdjacentHTML('beforeend', data.html || '');
                } else {
                    grid.innerHTML = data.html || empty;
                }

                // Update count badge — prefer meta.total (full match count) over page slice
                var cnt = document.getElementById('cnt-' + tab);
                if (cnt) cnt.textContent = (data.meta && data.meta.total !== undefined) ? data.meta.total : data.count;

                // Load more
                if (lw) {
                    if (data.has_more) {
                        lw.innerHTML =
                            '<div class="loadmore"><button class="btn btn-navy lm-btn" data-tab="' + tab + '" data-page="' + data.next_page + '">' +
                            'Load more events ' +
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" width="16" height="16"><path d="M6 9l6 6 6-6"/></svg>' +
                            '</button></div>';
                        lw.querySelector('.lm-btn').addEventListener('click', function () {
                            tabPage[tab] = parseInt(this.dataset.page);
                            fetchTab(tab, true);
                        });
                    } else {
                        lw.innerHTML = '';
                    }
                }
            })
            .catch(function () {
                tabStale[tab] = false;
                if (!append && grid) {
                    grid.innerHTML = '<p class="sec-s" style="grid-column:1/-1;padding:40px 0;text-align:center">Error loading events. Please refresh.</p>';
                }
                if (lw) lw.innerHTML = '';
            });
    }

    // ── On any filter change ──────────────────────────────────────────────────
    function onFilterChange() {
        // Immediately blank all count badges — synchronous, no network wait
        ['all', 'upcoming', 'current', 'past'].forEach(function (t) {
            var c = document.getElementById('cnt-' + t);
            if (c) c.textContent = '…';
        });

        tabPage.all = tabPage.upcoming = tabPage.current = tabPage.past = 1;
        tabStale.all = tabStale.upcoming = tabStale.current = tabStale.past = false;

        // Fetch all four tabs in parallel; each fills in its own count badge
        fetchTab('all',      false);
        fetchTab('upcoming', false);
        fetchTab('current',  false);
        fetchTab('past',     false);

        setCategoryActive(F.tag);
        renderActiveFilters();
    }

    // ── Tab switching ─────────────────────────────────────────────────────────
    function setTab(tab) {
        activeTab = tab;
        document.querySelectorAll('#evSeg .seg-btn').forEach(function (b) {
            var on = b.dataset.tab === tab;
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
            if (on) b.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        });
        ['all', 'upcoming', 'current', 'past'].forEach(function (key) {
            var el = document.getElementById('panel-' + key);
            if (el) el.style.display = key === tab ? '' : 'none';
        });
        var subEl = document.getElementById('evSub');
        if (subEl) subEl.textContent = TAB_SUBS[tab] || '';

        // Reload if this tab hasn't been refreshed with the current filters yet
        // (only happens when filters were active at page load — see init below)
        if (tabStale[tab]) {
            tabPage[tab] = 1;
            tabStale[tab] = false;
            fetchTab(tab, false);
        }
    }

    document.querySelectorAll('#evSeg .seg-btn').forEach(function (b) {
        b.addEventListener('click', function () { setTab(b.dataset.tab); });
    });

    // ── Sort ──────────────────────────────────────────────────────────────────
    var sortSel = document.getElementById('sortSelect');
    if (sortSel) {
        sortSel.addEventListener('change', function () {
            F.direction = this.value;
            onFilterChange();
        });
    }

    // ── Platform ──────────────────────────────────────────────────────────────
    var platformSel = document.getElementById('platformFilter');
    if (platformSel) {
        platformSel.addEventListener('change', function () {
            F.platform = this.value;
            onFilterChange();
        });
    }

    // ── Category sidebar (blog-style list) ────────────────────────────────────
    function setCategoryActive(tag) {
        var val = tag || '';
        document.querySelectorAll('#evCatList .ev-catlink').forEach(function (btn) {
            btn.classList.toggle('is-active', (btn.dataset.tag || '') === val);
        });
    }

    document.querySelectorAll('#evCatList .ev-catlink').forEach(function (btn) {
        btn.addEventListener('click', function () {
            F.tag = btn.dataset.tag || '';
            onFilterChange();
        });
    });

    // ── Active filter chips ───────────────────────────────────────────────────
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function getPlatformLabel() {
        if (!F.platform) return '';
        var opt = document.querySelector('#platformFilter option[value="' + F.platform + '"]');
        return opt ? opt.textContent.trim() : F.platform.toUpperCase();
    }

    function getCategoryLabel() {
        if (!F.tag) return '';
        var btn = document.querySelector('#evCatList .ev-catlink[data-tag="' + F.tag + '"]');
        return (btn && (btn.dataset.label || btn.textContent.trim())) || F.tag;
    }

    function renderActiveFilters() {
        var container = document.getElementById('activeFilters');
        if (!container) return;

        var chips = [];
        if (F.tag) chips.push({ key: 'tag', label: 'Category', value: getCategoryLabel() });
        if (F.platform) chips.push({ key: 'platform', label: 'Platform', value: getPlatformLabel() });

        var locParts = [F.city, F.state, F.country].filter(Boolean);
        if (locParts.length) chips.push({ key: 'location', label: 'Location', value: locParts.join(', ') });

        if (F.direction && F.direction !== 'asc') {
            chips.push({ key: 'direction', label: 'Sort', value: 'Latest first' });
        }

        if (!chips.length) { container.innerHTML = ''; return; }

        var xIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" width="11" height="11"><path d="M18 6 6 18M6 6l12 12"/></svg>';
        var html = '';

        chips.forEach(function (chip) {
            html += '<button class="filter-chip" data-filter-key="' + chip.key + '" type="button">' +
                    '<span>' + escHtml(chip.label) + ': <b>' + escHtml(chip.value) + '</b></span>' +
                    xIcon + '</button>';
        });

        if (chips.length > 1) {
            html += '<button class="filter-chip-clearall" id="clearAllBtn" type="button">Clear all filters</button>';
        }

        container.innerHTML = html;

        container.querySelectorAll('.filter-chip[data-filter-key]').forEach(function (btn) {
            btn.addEventListener('click', function () { removeFilter(this.dataset.filterKey); });
        });

        var ca = document.getElementById('clearAllBtn');
        if (ca) ca.addEventListener('click', clearFilters);
    }

    function removeFilter(key) {
        if (key === 'tag') {
            F.tag = '';
        } else if (key === 'platform') {
            F.platform = '';
            if (platformSel) platformSel.value = '';
        } else if (key === 'location') {
            F.city = ''; F.state = ''; F.country = '';
            ['s-city','s-state','s-country'].forEach(function (id) {
                var el = document.getElementById(id); if (el) el.value = '';
            });
        } else if (key === 'direction') {
            F.direction = 'asc';
            var ss = document.getElementById('sortSelect'); if (ss) ss.value = 'asc';
        }
        onFilterChange();
    }

    function clearFilters(e) {
        if (e) e.preventDefault();
        F.tag = ''; F.platform = ''; F.city = ''; F.state = ''; F.country = '';
        F.direction = 'asc';
        ['s-city','s-state','s-country'].forEach(function (id) {
            var el = document.getElementById(id); if (el) el.value = '';
        });
        if (platformSel) platformSel.value = '';
        var ss = document.getElementById('sortSelect'); if (ss) ss.value = 'asc';
        onFilterChange();
    }

    // ── Expose API for search bar ─────────────────────────────────────────────
    window.evFilters        = F;
    window.evOnFilterChange = onFilterChange;

    // Render chips for any filters already active on page load
    setCategoryActive(F.tag);
    renderActiveFilters();

    // Populate the All tab immediately on page load
    fetchTab('all', false);

})();
</script>
@endpush
