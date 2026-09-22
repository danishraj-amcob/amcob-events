/**
 * Discovery feed client — Load more for Grid/List views.
 */
export function createFeed(store) {
  let abortCtrl = null;

  function feedQuery(tab, page) {
    const params = new URLSearchParams();
    params.set('tab', tab);
    params.set('page', String(page));
    params.set('per_page', String(store.perPage));
    const ui = store.ui;
    if (ui.cat) params.set('tag', ui.cat);
    if (ui.city) params.set('city', ui.city);
    if (ui.stateF) params.set('state', ui.stateF);
    if (ui.country) params.set('country', ui.country);
    if (ui.q) params.set('q', ui.q);
    const base = store.feedUrl;
    return base + (base.indexOf('?') === -1 ? '?' : '&') + params.toString();
  }

  function fetchFeed(tab, page, signal) {
    return fetch(feedQuery(tab, page), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      signal: signal || undefined,
    }).then((res) => {
      if (!res.ok) throw new Error('Feed failed');
      return res.json();
    });
  }

  async function loadMore() {
    if (store.loadingMore) return false;
    if (store.ui.view !== 'grid' && store.ui.view !== 'list') return false;
    if (!store.tabHasMore(store.ui.tab)) return false;

    const tab = store.ui.tab;
    store.loadingMore = true;
    store.loadMoreError = '';

    if (abortCtrl) {
      try { abortCtrl.abort(); } catch (e) { /* ignore */ }
    }
    abortCtrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
    const signal = abortCtrl ? abortCtrl.signal : undefined;

    try {
      if (tab === 'all') {
        const allP = store.paging.all || {};
        const jobs = [];
        const upPage = (allP.upcoming_page || 1) + 1;
        const pastPage = (allP.past_page || 1) + 1;
        const upHas = !!(store.paging.upcoming && store.paging.upcoming.has_more);
        const pastHas = !!(store.paging.past && store.paging.past.has_more);

        if (upHas) {
          jobs.push(fetchFeed('upcoming', upPage, signal).then((data) => {
            store.mergeEvents(data.events || []);
            store.applyFeedMeta('upcoming', data);
            allP.upcoming_page = data.page || upPage;
          }));
        }
        if (pastHas) {
          jobs.push(fetchFeed('past', pastPage, signal).then((data) => {
            store.mergeEvents(data.events || []);
            store.applyFeedMeta('past', data);
            allP.past_page = data.page || pastPage;
          }));
        }
        if (!jobs.length) {
          allP.has_more = false;
          store.paging.all = allP;
          return false;
        }
        await Promise.all(jobs);
        allP.has_more = !!(store.paging.upcoming && store.paging.upcoming.has_more)
          || !!(store.paging.past && store.paging.past.has_more);
        store.paging.all = allP;
        return true;
      }

      const next = ((store.paging[tab] && store.paging[tab].page) || 1) + 1;
      const data = await fetchFeed(tab, next, signal);
      store.mergeEvents(data.events || []);
      store.applyFeedMeta(tab, data);
      return true;
    } catch (err) {
      if (err && err.name === 'AbortError') return false;
      store.loadMoreError = 'Couldn’t load more events. Try again.';
      return false;
    } finally {
      store.loadingMore = false;
    }
  }

  return { loadMore, fetchFeed };
}
