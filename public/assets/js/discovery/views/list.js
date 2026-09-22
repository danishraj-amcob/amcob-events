import { ICONS, MO, WD } from '../config.js';
import {
  escapeHtml,
  eventHref,
  fmtLabel,
  imageUrl,
  parseDate,
  thumbGradient,
  thumbImg,
  attendanceLabel,
} from '../utils.js';
import { catColor, noResultHtml } from '../templates.js';

export function renderList(store) {
  // Already ordered Featured → live → upcoming → past
  const list = store.filtered();
  const host = document.getElementById('listHost');
  if (!host) return;
  if (!list.length) { host.innerHTML = noResultHtml(); return; }

  // Keep day groups in discovery order (first appearance), not chronological.
  const groups = {};
  const dayOrder = [];
  list.forEach((e) => {
    const dt = e.date;
    if (!groups[dt]) {
      groups[dt] = [];
      dayOrder.push(dt);
    }
    groups[dt].push(e);
  });

  host.innerHTML = dayOrder.map((dt) => {
    const d = parseDate(dt);
    const rows = groups[dt].map((e) => {
      const attended = attendanceLabel(e);
      const rightLabel = e.status === 'past'
        ? (attended ? (attended + ' went') : 'Held')
        : (e.price || 'Free');
      return '<div class="lrow" data-href="' + escapeHtml(eventHref(e)) + '">' +
        (function () {
          const src = imageUrl(e, 300);
          if (src) return thumbImg(src, e.title, 'lthumb');
          return '<div class="lthumb" style="background:' + escapeHtml(thumbGradient(e)) + '"></div>';
        })() +
        '<div class="lbody">' +
          '<div class="ltop">' +
            '<span class="lcat" style="background:' + catColor(store, e.cat) + '">' + escapeHtml(e.cat) + '</span>' +
            '<span class="ltime">' + ICONS.clock + escapeHtml(e.time) + ' &middot; ' + fmtLabel(e.format) + '</span>' +
          '</div>' +
          '<h4>' + escapeHtml(e.title) + '</h4>' +
          '<div class="lloc">' + ICONS.pin + escapeHtml(e.loc) + '</div>' +
        '</div>' +
        '<div class="lright">' +
          '<span class="lprice">' + escapeHtml(rightLabel) + '</span>' +
          '<span class="lbtn">' + (e.status === 'live' ? 'Join' : (e.status === 'past' ? 'View' : 'Register')) + '</span>' +
        '</div>' +
      '</div>';
    }).join('');
    return '<div class="daygroup">' +
      '<div class="daycol"><div class="dnum">' + d.getDate() + '</div><div class="dwd">' + WD[d.getDay()].slice(0, 3) + '</div><div class="dmo">' + MO[d.getMonth()] + ' ' + d.getFullYear() + '</div></div>' +
      '<div class="listcol">' + rows + '</div>' +
    '</div>';
  }).join('');

  host.querySelectorAll('.lrow').forEach((row) => {
    row.addEventListener('click', () => {
      const href = row.getAttribute('data-href');
      if (href && href !== '#') window.location.href = href;
    });
  });
}
