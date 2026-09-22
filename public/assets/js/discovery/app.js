/**
 * AMCOB Events Discovery — application entry
 *
 * Module map:
 *   config.js      constants
 *   utils.js       pure helpers (escape, dates, colors)
 *   store.js       state (events, paging, filters, counts)
 *   feed.js        /events/discovery client + catalog prefetch
 *   templates.js   shared HTML builders
 *   views/*        Grid | List | Calendar | Hero  (Agenda module kept, UI disabled)
 *   app.js         wire UI + render loop (this file)
 */
import { createStore } from './store.js';
import { createFeed } from './feed.js';
import { escapeHtml, sanitizeColor } from './utils.js';
import { renderGrid } from './views/grid.js';
import { renderList } from './views/list.js';
// Agenda view kept as backup — re-enable with the Agenda button/#viewAgenda markup
// import { createAgendaView } from './views/agenda.js';
import { createCalendarView } from './views/calendar.js';
import { initHero } from './views/hero.js';

function boot() {
  if (!document.getElementById('dxExplorer')) return;

  const store = createStore(window.AMCOB_DISCOVERY || {});
  const feed = createFeed(store);
  // const agenda = createAgendaView(store);
  const calendar = createCalendarView(store);

  function paintTabCounts() {
    const counts = store.refreshCounts();
    ['all', 'upcoming', 'live', 'past'].forEach((tab) => {
      const btn = document.querySelector('#tabSeg [data-tab="' + tab + '"]');
      if (!btn) return;
      const n = typeof counts[tab] === 'number' ? counts[tab] : 0;
      let span = btn.querySelector('.cnt');
      if (!span) {
        span = document.createElement('span');
        span.className = 'cnt';
        btn.appendChild(document.createTextNode(' '));
        btn.appendChild(span);
      }
      span.textContent = String(n);
    });
  }

  function updateLoadMore() {
    const wrap = document.getElementById('loadmoreWrap');
    const btn = document.getElementById('loadmore');
    const err = document.getElementById('loadmoreError');
    if (!wrap) return;
    const show = store.hostConfig.features.load_more
      && (store.ui.view === 'grid' || store.ui.view === 'list')
      && (store.tabHasMore(store.ui.tab) || !!store.loadMoreError);
    wrap.hidden = !show;
    if (err) {
      err.hidden = !store.loadMoreError;
      err.textContent = store.loadMoreError || '';
    }
    if (btn && !store.loadingMore) {
      btn.disabled = !store.tabHasMore(store.ui.tab);
      btn.innerHTML = 'Load more events <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
    }
  }

  function updateMeta() {
    if (store.ui.view === 'calendar') {
      // Calendar sets its own "in view" counts.
    } else {
      const n = store.filtered().length;
      const el = document.getElementById('resultCount');
      if (el) {
        const dirty = store.filtersActive();
        const total = store.counts[store.ui.tab];
        if (!dirty && typeof total === 'number' && total > n) {
          el.innerHTML = '<b>' + n + '</b> of <b>' + total + '</b> events';
        } else {
          el.innerHTML = '<b>' + n + '</b> ' + (n === 1 ? 'event' : 'events');
        }
      }
    }
    const dirty = store.filtersActive();
    const clear = document.getElementById('clearFilters');
    if (clear) {
      clear.classList.toggle('show', dirty);
      clear.setAttribute('aria-hidden', dirty ? 'false' : 'true');
    }
    paintTabCounts();
    updateLoadMore();
  }

  function renderAll() {
    updateMeta();
    if (store.ui.view === 'grid') renderGrid(store);
    else if (store.ui.view === 'list') renderList(store);
    else if (store.ui.view === 'calendar') calendar.render();
    // else if (store.ui.view === 'agenda') agenda.render();
  }

  function setView(v) {
    // Agenda UI is disabled — fall back to calendar if requested.
    if (v === 'agenda') v = 'calendar';
    store.setView(v);
    document.querySelectorAll('#viewSw button').forEach((b) => {
      b.classList.toggle('active', b.getAttribute('data-view') === v);
    });
    const agendaEl = document.getElementById('viewAgenda');
    const grid = document.getElementById('viewGrid');
    const cal = document.getElementById('viewCalendar');
    const list = document.getElementById('viewList');
    if (agendaEl) agendaEl.hidden = v !== 'agenda';
    if (grid) grid.hidden = v !== 'grid';
    if (cal) cal.hidden = v !== 'calendar';
    if (list) list.hidden = v !== 'list';
    const tabs = document.getElementById('tabSeg');
    if (tabs) tabs.style.display = (v === 'calendar') ? 'none' : '';
    renderAll();
  }

  function setTab(t) {
    store.setTab(t);
    document.querySelectorAll('#tabSeg .seg-btn').forEach((b) => {
      const on = b.getAttribute('data-tab') === store.ui.tab;
      b.classList.toggle('active', on);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    renderAll();
  }

  function renderCatChips() {
    const host = document.getElementById('catChips');
    if (!host) return;
    const cats = [{ name: 'All', color: null }].concat(store.categories);
    host.innerHTML = cats.map((c) => {
      const active = (c.name === 'All' && !store.ui.cat) || c.name === store.ui.cat;
      const color = sanitizeColor(c.color);
      const dot = c.name === 'All' ? '' : '<span class="dotc" style="background:' + color + '"></span>';
      const val = c.name === 'All' ? '' : c.name;
      return '<button type="button" class="fchip ' + (active ? 'active' : '') + '" data-cat="' + escapeHtml(val) + '">' + dot + escapeHtml(c.name) + '</button>';
    }).join('');
    host.querySelectorAll('.fchip').forEach((b) => {
      b.addEventListener('click', () => {
        const raw = b.getAttribute('data-cat');
        store.ui.cat = raw == null ? '' : String(raw).trim();
        renderCatChips();
        renderAll();
      });
    });
  }

  function fillLocationSelects() {
    function fill(id, values, blank) {
      const sel = document.getElementById(id);
      if (!sel) return;
      const current = sel.value || (id === 'cityInput' ? store.ui.city : id === 'stateInput' ? store.ui.stateF : store.ui.country);
      sel.innerHTML = '<option value="">' + blank + '</option>' + (values || []).map((v) =>
        '<option value="' + escapeHtml(v) + '">' + escapeHtml(v) + '</option>'
      ).join('');
      if (current) sel.value = current;
    }
    fill('cityInput', store.locations.cities || [], 'All Cities');
    fill('stateInput', store.locations.states || [], 'All States');
    fill('countryInput', store.locations.countries || [], 'All Countries');
  }

  function updateLocBtn() {
    const n = [store.ui.city, store.ui.stateF, store.ui.country].filter(Boolean).length;
    const btn = document.getElementById('locBtn');
    const count = document.getElementById('locCount');
    if (btn) btn.classList.toggle('on', n > 0);
    if (count) count.textContent = n ? ' \u00B7 ' + n : '';
  }

  function clearAllFilters() {
    store.ui.cat = '';
    store.ui.city = '';
    store.ui.stateF = '';
    store.ui.country = '';
    store.ui.q = '';
    ['cityInput', 'stateInput', 'countryInput'].forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.selectedIndex = 0;
      el.value = '';
    });
    const search = document.getElementById('eventSearch');
    const clearSearch = document.getElementById('eventSearchClear');
    if (search) search.value = '';
    if (clearSearch) clearSearch.hidden = true;
    const locPop = document.getElementById('locPop');
    const locBtn = document.getElementById('locBtn');
    if (locPop) locPop.hidden = true;
    if (locBtn) locBtn.setAttribute('aria-expanded', 'false');
    updateLocBtn();
    renderCatChips();
    renderAll();
  }

  function bindUi() {
    document.querySelectorAll('#viewSw button').forEach((b) => {
      b.addEventListener('click', () => setView(b.getAttribute('data-view')));
    });
    document.querySelectorAll('#tabSeg .seg-btn').forEach((b) => {
      b.addEventListener('click', () => setTab(b.getAttribute('data-tab')));
    });

    const search = document.getElementById('eventSearch');
    const clearSearch = document.getElementById('eventSearchClear');
    if (search) {
      if (store.ui.q) {
        search.value = store.ui.q;
        if (clearSearch) clearSearch.hidden = false;
      }
      search.addEventListener('input', () => {
        store.ui.q = search.value.trim();
        if (clearSearch) clearSearch.hidden = !store.ui.q;
        renderAll();
      });
    }
    if (clearSearch) {
      clearSearch.addEventListener('click', () => {
        if (search) search.value = '';
        store.ui.q = '';
        clearSearch.hidden = true;
        renderAll();
        if (search) search.focus();
      });
    }

    function runLoc() {
      const cityEl = document.getElementById('cityInput');
      const stateEl = document.getElementById('stateInput');
      const countryEl = document.getElementById('countryInput');
      store.ui.city = cityEl ? cityEl.value : '';
      store.ui.stateF = stateEl ? stateEl.value : '';
      store.ui.country = countryEl ? countryEl.value : '';
      updateLocBtn();
      renderAll();
    }
    ['cityInput', 'stateInput', 'countryInput'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('change', runLoc);
    });

    const clearFilters = document.getElementById('clearFilters');
    if (clearFilters) {
      clearFilters.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        clearAllFilters();
      });
    }

    const locBtn = document.getElementById('locBtn');
    const locPop = document.getElementById('locPop');
    if (locBtn && locPop) {
      locBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = locPop.hidden;
        locPop.hidden = !open;
        locBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      document.addEventListener('click', (e) => {
        if (!locPop.hidden && !e.target.closest('.loc-wrap')) {
          locPop.hidden = true;
          locBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }

    const loadmore = document.getElementById('loadmore');
    if (loadmore) {
      loadmore.addEventListener('click', async () => {
        loadmore.disabled = true;
        loadmore.textContent = 'Loading...';
        try {
          await feed.loadMore();
        } finally {
          renderAll();
        }
      });
    }

    // agenda.bind();
    calendar.bind();
  }

  // Init
  fillLocationSelects();
  updateLocBtn();
  renderCatChips();
  bindUi();
  if (store.hostConfig.features.hero) {
    initHero(store);
  } else {
    const heroRoot = document.getElementById('dxHero');
    if (heroRoot) heroRoot.style.display = 'none';
  }
  setTab(store.ui.tab || 'all');
  // Sync default view UI without auto-prefetching the catalog.
  // Extra pages load only via Load more on Grid/List.
  setView(store.ui.view || 'calendar');
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}
