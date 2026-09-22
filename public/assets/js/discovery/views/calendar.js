import { ICONS, MO, MOA, WD } from '../config.js';
import {
  addDays,
  escapeHtml,
  eventHref,
  fmtLabel,
  imageUrl,
  iso,
  parseDate,
  startOfWeek,
  thumbGradient,
  thumbImg,
  attendanceLabel,
} from '../utils.js';
import { catColor } from '../templates.js';

export function createCalendarView(store) {
  let calMode = 'month';
  let calCur = store.firstDateHint();

  function eventsOn(dateStr) {
    return store.filteredAll().filter((e) => e.date === dateStr);
  }

  function calTitleText() {
    if (calMode === 'month') return MO[calCur.getMonth()] + ' ' + calCur.getFullYear();
    if (calMode === 'day') return WD[calCur.getDay()] + ', ' + MOA[calCur.getMonth()] + ' ' + calCur.getDate() + ', ' + calCur.getFullYear();
    const sow = startOfWeek(calCur);
    const eow = addDays(sow, 6);
    const b = (sow.getMonth() === eow.getMonth()) ? String(eow.getDate()) : (MOA[eow.getMonth()] + ' ' + eow.getDate());
    return MOA[sow.getMonth()] + ' ' + sow.getDate() + ' \u2013 ' + b + ', ' + eow.getFullYear();
  }

  function calRangeCount() {
    if (calMode === 'day') return eventsOn(iso(calCur)).length;
    if (calMode === 'week') {
      const sow = startOfWeek(calCur);
      let n = 0;
      for (let i = 0; i < 7; i++) n += eventsOn(iso(addDays(sow, i))).length;
      return n;
    }
    return store.filteredAll().filter((e) => {
      const d = parseDate(e.date);
      return d.getFullYear() === calCur.getFullYear() && d.getMonth() === calCur.getMonth();
    }).length;
  }

  function dowHeader(hasTodayCol, todayDow) {
    return '<div class="c2dow">' + WD.map((x, i) =>
      '<span class="' + (hasTodayCol && i === todayDow ? 'today' : '') + '">' + x + '</span>'
    ).join('') + '</div>';
  }

  function monthHTML() {
    const y = calCur.getFullYear();
    const m = calCur.getMonth();
    const startDow = new Date(y, m, 1).getDay();
    const daysIn = new Date(y, m + 1, 0).getDate();
    const prevDays = new Date(y, m, 0).getDate();
    const todayStr = iso(store.today);
    const hasTodayCol = (y === store.today.getFullYear() && m === store.today.getMonth());
    let cells = '';
    let i;
    for (i = startDow - 1; i >= 0; i--) cells += '<div class="c2 other"><span class="dn">' + (prevDays - i) + '</span></div>';
    for (let day = 1; day <= daysIn; day++) {
      const ds = y + '-' + String(m + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
      const evs = eventsOn(ds);
      const isToday = ds === todayStr;
      let bars = '';
      evs.slice(0, 2).forEach((e) => {
        bars += '<span class="c2evt" style="--cc:' + catColor(store, e.cat) + '">' + escapeHtml(e.title) + '</span>';
      });
      const more = evs.length > 2 ? '<span class="c2more">+' + (evs.length - 2) + ' more</span>' : '';
      cells += '<div class="c2 ' + (evs.length ? 'has' : '') + ' ' + (isToday ? 'today' : '') + '" ' + (evs.length ? 'data-d="' + ds + '"' : '') + '><span class="dn">' + day + '</span>' + bars + more + '</div>';
    }
    const trail = (7 - ((startDow + daysIn) % 7)) % 7;
    for (i = 1; i <= trail; i++) cells += '<div class="c2 other"><span class="dn">' + i + '</span></div>';
    return '<div class="c2cal">' + dowHeader(hasTodayCol, store.today.getDay()) + '<div class="c2grid">' + cells + '</div></div>';
  }

  function weekHTML() {
    const sow = startOfWeek(calCur);
    const todayStr = iso(store.today);
    let heads = '';
    let nums = '';
    let bars = '';
    for (let i = 0; i < 7; i++) {
      const d = addDays(sow, i);
      const ds = iso(d);
      const evs = eventsOn(ds);
      const isToday = ds === todayStr;
      heads += '<span class="' + (isToday ? 'today' : '') + '">' + WD[d.getDay()] + '</span>';
      nums += '<div class="wknum ' + (isToday ? 'today' : '') + '"><span>' + d.getDate() + '</span></div>';
      const items = evs.map((e) =>
        '<a class="wkbar" href="' + escapeHtml(eventHref(e)) + '" style="--cc:' + catColor(store, e.cat) + '"><span class="wt">' + escapeHtml(e.time) + '</span><b>' + escapeHtml(e.title) + '</b></a>'
      ).join('');
      bars += '<div class="wkcol">' + items + '</div>';
    }
    return '<div class="c2wk"><div class="wkhead">' + heads + '</div><div class="wknums">' + nums + '</div><div class="wkbars">' + bars + '</div></div>';
  }

  function dayHTML() {
    const ds = iso(calCur);
    const evs = eventsOn(ds);
    const head = '<div class="c2day-h">' + ICONS.clock + evs.length + ' event' + (evs.length !== 1 ? 's' : '') + ' &middot; ' + WD[calCur.getDay()] + ', ' + MOA[calCur.getMonth()] + ' ' + calCur.getDate() + '</div>';
    if (!evs.length) {
      return '<div class="c2day">' + head + '<div class="cal-empty2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><div>' + (store.prefetching ? 'Loading events...' : 'No events on this day.') + '</div></div></div>';
    }
    const rows = evs.map((e) => {
      const attended = attendanceLabel(e);
      const priceLabel = e.status === 'past'
        ? (attended ? (attended + ' went') : 'Held')
        : (e.price || 'Free');
      return '<a class="drow" href="' + escapeHtml(eventHref(e)) + '">' +
        '<div class="dtime">' + escapeHtml(e.time) + '</div>' +
        '<span class="dbar" style="background:' + catColor(store, e.cat) + '"></span>' +
        (function () {
          const src = imageUrl(e, 240);
          if (src) return thumbImg(src, e.title, 'dthumb');
          return '<div class="dthumb" style="background:' + escapeHtml(thumbGradient(e)) + '"></div>';
        })() +
        '<div class="dinfo"><span class="dcat" style="color:' + catColor(store, e.cat) + '">' + escapeHtml(e.cat) + ' &middot; ' + fmtLabel(e.format) + '</span><b>' + escapeHtml(e.title) + '</b><div class="dmeta">' + ICONS.pin + escapeHtml(e.loc) + '</div></div>' +
        '<span class="dprice">' + escapeHtml(priceLabel) + '</span>' +
      '</a>';
    }).join('');
    return '<div class="c2day">' + head + rows + '</div>';
  }

  function render() {
    const title = document.getElementById('calTitle');
    if (title) title.textContent = calTitleText() + (store.prefetching ? ' \u00B7 \u2026' : '');
    document.querySelectorAll('#calModes button').forEach((b) => {
      b.classList.toggle('active', b.getAttribute('data-mode') === calMode);
    });
    const body = document.getElementById('calBody');
    if (!body) return;
    body.className = 'cal2-body mode-' + calMode;
    body.innerHTML = calMode === 'month' ? monthHTML() : (calMode === 'week' ? weekHTML() : dayHTML());
    if (calMode === 'month') {
      body.querySelectorAll('.c2.has').forEach((c) => {
        c.addEventListener('click', () => {
          calCur = parseDate(c.getAttribute('data-d'));
          calMode = 'day';
          render();
        });
      });
    }
    const legend = document.getElementById('calLegend');
    if (legend) {
      legend.innerHTML = store.categories.map((c) =>
        '<span><i style="background:' + catColor(store, c.name) + '"></i>' + escapeHtml(c.name) + '</span>'
      ).join('');
    }
    const n = calRangeCount();
    const rc = document.getElementById('resultCount');
    if (rc) rc.innerHTML = '<b>' + n + '</b> ' + (n === 1 ? 'event' : 'events') + ' in view';
  }

  function step(dir) {
    if (calMode === 'month') calCur = new Date(calCur.getFullYear(), calCur.getMonth() + dir, 1);
    else if (calMode === 'week') calCur = addDays(calCur, 7 * dir);
    else calCur = addDays(calCur, dir);
    render();
  }

  function bind() {
    document.querySelectorAll('#calModes button').forEach((b) => {
      b.addEventListener('click', () => { calMode = b.getAttribute('data-mode'); render(); });
    });
    const prev = document.getElementById('calPrev');
    const next = document.getElementById('calNext');
    const todayBtn = document.getElementById('calToday');
    if (prev) prev.addEventListener('click', () => step(-1));
    if (next) next.addEventListener('click', () => step(1));
    if (todayBtn) todayBtn.addEventListener('click', () => { calCur = new Date(store.today); render(); });
  }

  return { render, bind };
}
