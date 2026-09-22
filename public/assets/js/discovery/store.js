import { PER_PAGE, applyHostConfig } from './config.js';
import { countFromEvents, eventKey, parseDate, compareDiscoveryOrder } from './utils.js';

function normalizeCounts(raw, events) {
  raw = raw || {};
  let upcoming = raw.upcoming;
  let live = raw.live != null ? raw.live : raw.current;
  let past = raw.past;
  const hasApi = [upcoming, live, past].some((v) => typeof v === 'number');
  if (!hasApi) return countFromEvents(events);

  upcoming = typeof upcoming === 'number' ? upcoming : 0;
  live = typeof live === 'number' ? live : 0;
  past = typeof past === 'number' ? past : 0;

  const local = countFromEvents(events);
  // ERP quirk: live counted inside upcoming
  if (live > 0 && upcoming === local.upcoming + live) {
    upcoming = local.upcoming;
  }

  const all = typeof raw.all === 'number' && !(live > 0 && raw.upcoming === local.upcoming + live)
    ? raw.all
    : (upcoming + live + past);

  return { upcoming, live, past, all };
}

/**
 * Central discovery store — single source of truth for events, paging, filters, view.
 */
export function createStore(data) {
  const DATA = data || {};
  const hostCfg = applyHostConfig(DATA.config);

  let events = Array.isArray(DATA.events) ? DATA.events.slice() : [];
  const categories = Array.isArray(DATA.categories) ? DATA.categories : [];
  const locations = DATA.locations || { cities: [], states: [], countries: [] };
  const featured = Array.isArray(DATA.featured) ? DATA.featured.slice() : [];
  let paging = DATA.paging ? JSON.parse(JSON.stringify(DATA.paging)) : {};
  const feedUrl = DATA.feed_url || '/events/discovery';
  const perPage = DATA.per_page || PER_PAGE;
  const schemaVersion = typeof DATA.schema_version === 'number' ? DATA.schema_version : 1;

  const initialFilters = DATA.filters || {};
  const hasApiCounts = (() => {
    const c = DATA.counts || {};
    return ['upcoming', 'live', 'past', 'current', 'all'].some((k) => typeof c[k] === 'number');
  })();

  let baseCounts = normalizeCounts(DATA.counts || {}, events);
  let counts = Object.assign({}, baseCounts);

  let today = DATA.today ? new Date(DATA.today) : new Date();
  if (Number.isNaN(today.getTime())) today = new Date();

  const ui = {
    view: hostCfg.default_view,
    tab: hostCfg.default_tab,
    cat: initialFilters.tag ? String(initialFilters.tag) : '',
    city: initialFilters.city ? String(initialFilters.city) : '',
    stateF: initialFilters.state ? String(initialFilters.state) : '',
    country: initialFilters.country ? String(initialFilters.country) : '',
    q: initialFilters.q ? String(initialFilters.q) : '',
  };

  let loadingMore = false;
  let loadMoreError = '';
  let listeners = [];

  function notify() {
    listeners.forEach((fn) => {
      try { fn(); } catch (e) { /* ignore */ }
    });
  }

  function mergeEvents(incoming) {
    if (!Array.isArray(incoming) || !incoming.length) return 0;
    const rank = { live: 3, upcoming: 2, past: 1 };
    const map = {};
    events.forEach((e) => {
      const k = eventKey(e);
      if (k !== '0:') map[k] = e;
    });
    let added = 0;
    incoming.forEach((e) => {
      const k = eventKey(e);
      if (k === '0:') return;
      if (!map[k]) {
        map[k] = e;
        added++;
        return;
      }
      const prevScore = rank[map[k].status] || 0;
      const nextScore = rank[e.status] || 0;
      if (nextScore > prevScore) map[k] = e;
      else if (nextScore === prevScore && e.featured && !map[k].featured) map[k] = e;
    });
    events = Object.keys(map).map((k) => map[k]);
    return added;
  }

  function filtersActive() {
    return !!(ui.cat || ui.city || ui.stateF || ui.country || ui.q);
  }

  function applyFilters(list) {
    return list.filter((e) => {
      if (ui.cat && String(e.cat || '').trim() !== String(ui.cat).trim()) return false;
      if (ui.city && e.city !== ui.city) return false;
      if (ui.stateF && String(e.state || '') !== ui.stateF) return false;
      if (ui.country) {
        const c = String(e.country || '');
        if (c !== ui.country && !(ui.country === 'United States' && (c === 'US' || c === 'USA'))) return false;
      }
      if (ui.q) {
        const q = ui.q.toLowerCase();
        const hay = ((e.title || '') + ' ' + (e.loc || '') + ' ' + (e.cat || '') + ' ' + (e.city || '')).toLowerCase();
        if (hay.indexOf(q) === -1) return false;
      }
      return true;
    });
  }

  function baseByTab() {
    if (ui.tab === 'all') return events.slice();
    return events.filter((e) => e.status === ui.tab);
  }

  function filtered() {
    return applyFilters(baseByTab()).sort(compareDiscoveryOrder);
  }

  function filteredAll() {
    return applyFilters(events).sort(compareDiscoveryOrder);
  }

  function loadedCountForTab(tab) {
    tab = tab || ui.tab;
    if (tab === 'all') return events.length;
    return events.filter((e) => e.status === tab).length;
  }

  function refreshCounts() {
    if (filtersActive()) {
      counts = countFromEvents(applyFilters(events));
    } else if (hasApiCounts) {
      counts = Object.assign({}, baseCounts);
    } else {
      counts = countFromEvents(events);
    }
    return counts;
  }

  function tabHasMore(tab) {
    tab = tab || ui.tab;
    if (tab === 'live') return false;
    if (!hostCfg.features.load_more) return false;
    const p = paging[tab] || {};
    if (!p.has_more) return false;

    const loaded = loadedCountForTab(tab);
    if (filtersActive()) {
      if (hasApiCounts && typeof baseCounts[tab] === 'number' && loaded >= baseCounts[tab]) return false;
      return true;
    }
    let total = null;
    if (hasApiCounts && typeof baseCounts[tab] === 'number') total = baseCounts[tab];
    else if (typeof p.total === 'number') total = p.total;
    if (typeof total === 'number' && loaded >= total) return false;
    return true;
  }

  function applyFeedMeta(tab, data) {
    if (!data) return;
    const pageCount = Array.isArray(data.events) ? data.events.length : 0;
    paging[tab] = paging[tab] || {};
    paging[tab].page = data.page || ((paging[tab].page || 1) + 1);
    paging[tab].last_page = data.last_page || paging[tab].last_page || 1;
    let more = !!data.has_more;
    if (pageCount === 0) more = false;
    paging[tab].has_more = more;

    if (data.counts && typeof data.counts === 'object' && !filtersActive()) {
      baseCounts = normalizeCounts(Object.assign({}, baseCounts, data.counts), events);
    }

    if (tab === 'upcoming' || tab === 'past') {
      paging.all = paging.all || {};
      if (tab === 'upcoming') paging.all.upcoming_page = paging[tab].page;
      if (tab === 'past') paging.all.past_page = paging[tab].page;
      let allMore = !!(paging.upcoming && paging.upcoming.has_more) || !!(paging.past && paging.past.has_more);
      const allLoaded = loadedCountForTab('all');
      if (hasApiCounts && typeof baseCounts.all === 'number' && allLoaded >= baseCounts.all) allMore = false;
      paging.all.has_more = allMore;
    }
  }

  function firstDateHint() {
    const first = events
      .filter((e) => e.status === 'upcoming' || e.status === 'live')
      .sort((a, b) => parseDate(a.date) - parseDate(b.date))[0];
    return first ? parseDate(first.date) : new Date(today);
  }

  return {
    get events() { return events; },
    get categories() { return categories; },
    get locations() { return locations; },
    get featured() { return featured; },
    get paging() { return paging; },
    get feedUrl() { return feedUrl; },
    get perPage() { return perPage; },
    get schemaVersion() { return schemaVersion; },
    get hostConfig() { return hostCfg; },
    get counts() { return counts; },
    get baseCounts() { return baseCounts; },
    get hasApiCounts() { return hasApiCounts; },
    get today() { return today; },
    get ui() { return ui; },
    get loadingMore() { return loadingMore; },
    set loadingMore(v) { loadingMore = !!v; },
    get loadMoreError() { return loadMoreError; },
    set loadMoreError(v) { loadMoreError = v ? String(v) : ''; },
    get prefetching() { return false; },

    subscribe(fn) { listeners.push(fn); return () => { listeners = listeners.filter((x) => x !== fn); }; },
    notify,
    mergeEvents,
    filtersActive,
    applyFilters,
    baseByTab,
    filtered,
    filteredAll,
    loadedCountForTab,
    refreshCounts,
    tabHasMore,
    applyFeedMeta,
    firstDateHint,
    setTab(t) { ui.tab = t || hostCfg.default_tab; },
    setView(v) { ui.view = v || hostCfg.default_view; },
  };
}
