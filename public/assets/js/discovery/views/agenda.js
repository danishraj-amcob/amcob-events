import { ICONS, MO, MOA, WD } from '../config.js';
import { addDays, escapeHtml, eventHref, iso, parseDate } from '../utils.js';
import { catColor } from '../templates.js';

export function createAgendaView(store) {
  let ag2Start = (() => {
    const hint = store.firstDateHint();
    return addDays(hint, -3);
  })();
  let ag2ShowAll = false;

  function agStatus(e) {
    if (e.status === 'live') return { t: 'In progress', cls: 'st-prog' };
    if (e.status === 'past') return { t: 'Expired', cls: 'st-exp' };
    return { t: 'Starting soon', cls: 'st-soon' };
  }

  function windowList() {
    const startT = +new Date(ag2Start.getFullYear(), ag2Start.getMonth(), ag2Start.getDate());
    const end = addDays(ag2Start, 13);
    const endT = +new Date(end.getFullYear(), end.getMonth(), end.getDate(), 23, 59, 59);
    return store.filtered().filter((e) => {
      const t = +parseDate(e.date);
      return t >= startT && t <= endT;
    });
  }

  function render() {
    const todayStr = iso(store.today);
    const eventDates = {};
    store.filtered().forEach((e) => { eventDates[e.date] = true; });
    let cells = '';
    for (let i = 0; i < 14; i++) {
      const d = addDays(ag2Start, i);
      const ds = iso(d);
      cells += '<div class="amcell ' + (eventDates[ds] ? 'has' : '') + ' ' + (ds === todayStr ? 'today' : '') + '" data-d="' + ds + '"><div class="mwd">' + WD[d.getDay()].slice(0, 3) + '</div><div class="mnum">' + d.getDate() + '</div></div>';
    }
    const strip = document.getElementById('ag2Strip');
    if (strip) {
      strip.innerHTML = cells;
      strip.querySelectorAll('.amcell').forEach((c) => {
        c.addEventListener('click', () => {
          ag2ShowAll = true;
          render();
          const row = document.querySelector('.agrow[data-d="' + c.getAttribute('data-d') + '"]');
          if (row) {
            row.scrollIntoView({ block: 'center' });
            row.classList.add('flash');
            setTimeout(() => row.classList.remove('flash'), 700);
          }
        });
      });
    }
    const monthEl = document.getElementById('ag2Month');
    if (monthEl) monthEl.textContent = MO[ag2Start.getMonth()] + ' ' + ag2Start.getFullYear();
    const sub = document.getElementById('ag2Sub');
    if (sub) {
      const end = addDays(ag2Start, 13);
      sub.textContent = MOA[ag2Start.getMonth()] + ' ' + ag2Start.getDate() + ' \u2013 ' + MOA[end.getMonth()] + ' ' + end.getDate() + ', ' + end.getFullYear()
        + (store.prefetching ? ' \u00B7 Loading more events...' : '');
    }

    const list = windowList();
    const host = document.getElementById('ag2Events');
    const legend = document.getElementById('ag2Legend');
    if (!host) return list.length;

    if (!list.length) {
      host.innerHTML = '<div class="cal-empty2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><div>' + (store.prefetching ? 'Loading events...' : 'No events in this 2-week window.') + '</div></div>';
      if (legend) legend.innerHTML = '';
      return 0;
    }

    const shown = ag2ShowAll ? list : list.slice(0, 4);
    let html = '';
    let curKey = '';
    shown.forEach((e) => {
      const d = parseDate(e.date);
      const key = d.getFullYear() + '-' + d.getMonth();
      const st = agStatus(e);
      if (key !== curKey) {
        curKey = key;
        html += '<div class="ag2-mgroup">' + MO[d.getMonth()] + ', ' + d.getFullYear() + '</div>';
      }
      html += '<a class="agrow" href="' + escapeHtml(eventHref(e)) + '" data-d="' + escapeHtml(e.date) + '"><span class="agi" style="background:' + catColor(store, e.cat) + '">' + ICONS.agenda + '</span><div class="agbody"><b>' + escapeHtml(e.title) + '</b><div class="agtime">' + ICONS.clock + WD[d.getDay()].slice(0, 3) + ', ' + MOA[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear() + ' &middot; ' + escapeHtml(e.time) + ' &middot; ' + escapeHtml(e.loc) + '</div></div><span class="agstatus ' + st.cls + '">' + st.t + '</span></a>';
    });
    if (list.length > 4) {
      html += '<button class="ag2-showmore" id="ag2ShowMore" type="button">' + (ag2ShowAll ? 'Show less' : 'Show more (' + list.length + ')') + '</button>';
    }
    host.innerHTML = html;
    const sm = document.getElementById('ag2ShowMore');
    if (sm) sm.addEventListener('click', () => { ag2ShowAll = !ag2ShowAll; render(); });
    if (legend) legend.innerHTML = '<span class="st-soon">Starting soon</span><span class="st-prog">In progress</span><span class="st-exp">Expired</span>';
    return list.length;
  }

  function bind() {
    const prev = document.getElementById('ag2Prev');
    const next = document.getElementById('ag2Next');
    const todayBtn = document.getElementById('ag2Today');
    if (prev) prev.addEventListener('click', () => { ag2Start = addDays(ag2Start, -14); ag2ShowAll = false; render(); updateCount(); });
    if (next) next.addEventListener('click', () => { ag2Start = addDays(ag2Start, 14); ag2ShowAll = false; render(); updateCount(); });
    if (todayBtn) todayBtn.addEventListener('click', () => { ag2Start = addDays(new Date(store.today), -3); ag2ShowAll = false; render(); updateCount(); });
  }

  function updateCount() {
    const n = windowList().length;
    const el = document.getElementById('resultCount');
    if (el) el.innerHTML = '<b>' + n + '</b> ' + (n === 1 ? 'event' : 'events') + ' in view';
  }

  return {
    render() { const n = render(); updateCount(); return n; },
    bind,
    windowCount: () => windowList().length,
  };
}
