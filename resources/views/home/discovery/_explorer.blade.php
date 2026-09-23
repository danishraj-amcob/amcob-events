{{-- Events explorer: Grid / Calendar / List (Agenda temporarily disabled) --}}
<section class="blk" id="events" style="padding-top:26px">
  <div class="wrap wrap-full">
    <div class="sec-head">
      <span class="bg-word" aria-hidden="true">EVENTS</span>
      <span class="bg-word bg-word-stroke" aria-hidden="true">EVENTS</span>
      <h2 class="sec-t">Browse all events</h2>
    </div>

    <div class="explorer" id="dxExplorer">
      <div class="ex-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/></svg>
        <input id="eventSearch" type="search" placeholder="Search events by name, category or location…" autocomplete="off">
        <button class="ex-clear" id="eventSearchClear" type="button" hidden aria-label="Clear search">✕</button>
      </div>

      <div class="ex-bar">
        <div class="viewsw" id="viewSw" role="group" aria-label="View mode">
          <button type="button" data-view="calendar" class="active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Calendar
          </button>
          <button type="button" data-view="grid">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Grid
          </button>
          {{-- Agenda view temporarily disabled — restore button + #viewAgenda below to re-enable
          <button type="button" data-view="agenda">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><rect x="2.5" y="4.5" width="3" height="3" rx=".8"/><rect x="2.5" y="10.5" width="3" height="3" rx=".8"/><rect x="2.5" y="16.5" width="3" height="3" rx=".8"/></svg>
            Agenda
          </button>
          --}}
          <button type="button" data-view="list">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            List
          </button>
        </div>

        <div class="seg" id="tabSeg" role="tablist" style="display:none">
          <button class="seg-btn active" type="button" data-tab="all" role="tab" aria-selected="true">All</button>
          <button class="seg-btn" type="button" data-tab="upcoming" role="tab" aria-selected="false">Upcoming</button>
          <button class="seg-btn" type="button" data-tab="live" role="tab" aria-selected="false"><span class="livedot"></span>Live</button>
          <button class="seg-btn" type="button" data-tab="past" role="tab" aria-selected="false">Past</button>
        </div>
      </div>

      <div class="filters" id="filters">
        <span class="flabel">Filter</span>
        <div id="catChips" style="display:flex;gap:8px;flex-wrap:wrap"></div>
        <div class="spacer"></div>
        <div class="loc-wrap">
          <button class="fchip loc-btn" id="locBtn" type="button" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            Location<span class="loc-count" id="locCount"></span>
            <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="loc-pop" id="locPop" hidden>
            <div class="lgrp">
              <label for="cityInput">City</label>
              <div class="lf">
                <select id="cityInput"><option value="">All Cities</option></select>
              </div>
            </div>
            <div class="lgrp">
              <label for="stateInput">State</label>
              <div class="lf">
                <select id="stateInput"><option value="">All States</option></select>
              </div>
            </div>
            <div class="lgrp">
              <label for="countryInput">Country</label>
              <div class="lf">
                <select id="countryInput"><option value="">All Countries</option></select>
              </div>
            </div>
          </div>
        </div>
        <span class="result-count" id="resultCount"></span>
        <button class="btn btn-ghost clearf" id="clearFilters" type="button" aria-label="Clear all filters">
          Clear all
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" width="14" height="14" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>

      {{-- Agenda panel backup — uncomment with the Agenda view button above to restore
      <div class="view" id="viewAgenda" hidden>
        <div class="ag2-bar">
          <button class="ag2-today" id="ag2Today" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Today
          </button>
          <div class="ag2-datenav">
            <button class="ag2-navb" id="ag2Prev" type="button" aria-label="Previous period"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg></button>
            <span class="ag2-month" id="ag2Month">Month</span>
            <button class="ag2-navb" id="ag2Next" type="button" aria-label="Next period"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 6l6 6-6 6"/></svg></button>
          </div>
        </div>
        <div class="ag2-strip" id="ag2Strip"></div>
        <div class="ag2-sub" id="ag2Sub"></div>
        <div class="ag2-list" id="ag2Events"></div>
        <div class="ag2-legend" id="ag2Legend"></div>
      </div>
      --}}

      <div class="view" id="viewCalendar">
        <div class="cal2-head">
          <div class="cal2-title" id="calTitle">Month</div>
          <div class="cal2-ctrls">
            <div class="cal2-modes" id="calModes">
              <button type="button" data-mode="month" class="active">Month</button>
              <button type="button" data-mode="week">Week</button>
              <button type="button" data-mode="day">Day</button>
            </div>
            <button class="btn btn-gold today" id="calToday" type="button">Today</button>
            <div class="cal-nav">
              <button id="calPrev" type="button" aria-label="Previous"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg></button>
              <button id="calNext" type="button" aria-label="Next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M9 6l6 6-6 6"/></svg></button>
            </div>
          </div>
        </div>
        <div class="cal2-body mode-month" id="calBody"></div>
        <div class="cal2-legend" id="calLegend"></div>
      </div>

      <div class="view" id="viewGrid" hidden>
        <div class="egrid" id="egrid"></div>
      </div>

      <div class="view" id="viewList" hidden>
        <div id="listHost"></div>
      </div>

      <div class="loadmore" id="loadmoreWrap" hidden>
        <button class="btn btn-navy" id="loadmore" type="button">Load more events
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <p class="loadmore-error" id="loadmoreError" hidden role="alert"></p>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
  // "EVENTS" background word: a gold outline revealed in a soft circle around the cursor
  // (same technique as the get-the-app section headings). Desktop pointers only.
  (() => {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
    const section = document.getElementById('events');
    const head = section && section.querySelector('.sec-head');
    const word = head && head.querySelector('.bg-word');
    if (!word) return;

    let targetX = 0, targetY = 0, currentX = 0, currentY = 0, raf = null;
    const render = () => {
      currentX += (targetX - currentX) * 0.18;
      currentY += (targetY - currentY) * 0.18;
      head.style.setProperty('--spot-x', currentX + 'px');
      head.style.setProperty('--spot-y', currentY + 'px');
      raf = Math.abs(targetX - currentX) > 0.5 || Math.abs(targetY - currentY) > 0.5
        ? requestAnimationFrame(render)
        : null;
    };

    head.addEventListener('mousemove', e => {
      const rect = word.getBoundingClientRect();
      targetX = e.clientX - rect.left;
      targetY = e.clientY - rect.top;
      head.style.setProperty('--spot-opacity', '1');
      if (!raf) raf = requestAnimationFrame(render);
    }, { passive: true });

    head.addEventListener('mouseleave', () => head.style.setProperty('--spot-opacity', '0'));
  })();
</script>
@endpush
