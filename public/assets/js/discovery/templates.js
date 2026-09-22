import { ICONS, MOA } from './config.js';
import {
  escapeHtml,
  eventHref,
  fmtIcon,
  fmtLabel,
  imageUrl,
  parseDate,
  sanitizeColor,
  thumbGradient,
  thumbImg,
  attendanceLabel,
} from './utils.js';

export function catColor(store, name) {
  for (let i = 0; i < store.categories.length; i++) {
    if (store.categories[i].name === name) {
      return sanitizeColor(store.categories[i].color);
    }
  }
  return sanitizeColor(null);
}

export function noResultHtml() {
  return '<div class="noresult" style="grid-column:1/-1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/></svg><b>No events match your filters</b>Try a different category, city or clear all filters.</div>';
}

function avatarStack(att) {
  const ids = (att && att.length) ? att : [];
  if (!ids.length) return '';
  return ids.slice(0, 4).map((i) => '<i style="background-image:url(https://i.pravatar.cc/48?img=' + i + ')"></i>').join('');
}

function cardOpen(e, extraClass) {
  const href = eventHref(e);
  const cls = 'ecard reveal show' + (extraClass ? (' ' + extraClass) : '');
  if (!href || href === '#') {
    return '<article class="' + cls + '" aria-disabled="true">';
  }
  return '<a class="' + cls + '" href="' + escapeHtml(href) + '">';
}

function cardClose(e) {
  const href = eventHref(e);
  return (!href || href === '#') ? '</article>' : '</a>';
}

export function cardHtml(store, e) {
  if (e.status === 'past') return pastHtml(store, e);
  if (e.status === 'live') return liveHtml(store, e);
  const d = parseDate(e.date);
  const src = imageUrl(e);
  const color = catColor(store, e.cat);
  const going = attendanceLabel(e);
  return cardOpen(e) +
    '<div class="thumb" style="background:' + escapeHtml(thumbGradient(e)) + '">' +
      thumbImg(src, e.title) +
      '<div class="datebadge"><b>' + String(d.getDate()).padStart(2, '0') + '</b><span>' + MOA[d.getMonth()] + '</span></div>' +
      '<div class="tag" style="background:' + color + '">' + escapeHtml(e.cat) + '</div>' +
      (e.price ? '<div class="pricepill">' + escapeHtml(e.price) + '</div>' : '') +
      '<div class="fmt">' + fmtIcon(e.format) + fmtLabel(e.format) + '</div>' +
    '</div>' +
    '<div class="body">' +
      '<h3>' + escapeHtml(e.title) + '</h3>' +
      '<div class="loc">' + ICONS.pin + escapeHtml(e.loc) + '</div>' +
      '<div class="foot">' +
        '<div class="attend">' + avatarStack(e.att) + '<span>' + (going ? ('+' + going + ' going') : 'Register') + '</span></div>' +
        '<span class="reg">Register ' + ICONS.arrow + '</span>' +
      '</div>' +
    '</div>' +
  cardClose(e);
}

function liveHtml(store, e) {
  const src = imageUrl(e);
  return cardOpen(e, 'live') +
    '<div class="thumb" style="background:' + escapeHtml(thumbGradient(e)) + '">' +
      thumbImg(src, e.title) +
      '<div class="datebadge"><b style="font-size:12px;color:#DC4C4C">LIVE</b><span>NOW</span></div>' +
      '<div class="tag"><i></i>LIVE</div>' +
      (e.price ? '<div class="pricepill">' + escapeHtml(e.price) + '</div>' : '') +
      '<div class="fmt">' + fmtIcon(e.format) + fmtLabel(e.format) + '</div>' +
    '</div>' +
    '<div class="body">' +
      '<h3>' + escapeHtml(e.title) + '</h3>' +
      '<div class="loc">' + ICONS.pin + escapeHtml(e.loc) + '</div>' +
      '<div class="foot">' +
        '<div class="attend">' + avatarStack(e.att) + '<span>' + escapeHtml(e.when || 'Live now') + '</span></div>' +
        '<span class="reg join">Join now ' + ICONS.arrow + '</span>' +
      '</div>' +
    '</div>' +
  cardClose(e);
}

function pastHtml(store, e) {
  const d = parseDate(e.date);
  const src = imageUrl(e);
  const attended = attendanceLabel(e);
  return cardOpen(e, 'past') +
    '<div class="thumb" style="background:' + escapeHtml(thumbGradient(e)) + '">' +
      thumbImg(src, e.title) +
      '<div class="datebadge"><b>' + String(d.getDate()).padStart(2, '0') + '</b><span>' + MOA[d.getMonth()] + '</span></div>' +
      '<div class="tag" style="background:var(--navy)"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" style="margin-right:3px;vertical-align:-1px"><path d="M20 6 9 17l-5-5"/></svg>Held</div>' +
      (e.photos && e.photos !== '0' ? '<div class="photos"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="15" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M8 5l1.5-2h5L16 5"/></svg>' + escapeHtml(e.photos) + ' photos</div>' : '') +
    '</div>' +
    '<div class="body">' +
      '<h3>' + escapeHtml(e.title) + '</h3>' +
      '<div class="loc">' + ICONS.pin + escapeHtml(e.loc) + '</div>' +
      '<div class="foot">' +
        (attended
          ? ('<div class="attend"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="var(--gold)" stroke-width="2" style="margin-right:6px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg><span style="margin-left:0">' + escapeHtml(attended) + ' attended</span></div>')
          : '<div></div>') +
        '<span class="recap">View event ' + ICONS.arrow + '</span>' +
      '</div>' +
    '</div>' +
  cardClose(e);
}
