import { DEFAULT_COLOR, FALLBACK_GRADIENT, ICONS, hostConfig } from './config.js';

export function escapeHtml(str) {
  return String(str == null ? '' : str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/** Allow only safe hex colors for style attributes. */
export function sanitizeColor(value, fallback) {
  const fb = fallback || DEFAULT_COLOR;
  if (typeof value !== 'string') return fb;
  const v = value.trim();
  if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(v)) {
    return v;
  }
  return fb;
}

export function parseDate(s) {
  return new Date(String(s) + 'T00:00:00');
}

/**
 * Featured → live → upcoming → past.
 * Active groups: date ascending. Past: date descending.
 */
export function compareDiscoveryOrder(a, b) {
  const rank = (e) => {
    const status = e && e.status ? String(e.status) : '';
    if (e && e.featured && status !== 'past') return 0;
    if (status === 'live') return 1;
    if (status === 'upcoming') return 2;
    return 3;
  };
  const ra = rank(a);
  const rb = rank(b);
  if (ra !== rb) return ra - rb;

  const da = parseDate(a && a.date).getTime();
  const db = parseDate(b && b.date).getTime();
  if ((a && a.status) === 'past') return db - da;
  return da - db;
}

/** Positive attendance count string, or null when API omitted / zero. */
export function attendanceLabel(e) {
  if (!e) return null;
  if (e.attended != null && String(e.attended).trim() !== '') {
    const n = Number(e.attended);
    if (Number.isFinite(n) && n > 0) return String(Math.floor(n));
  }
  if (e.registration_count != null && e.registration_count !== '') {
    const n = Number(e.registration_count);
    if (Number.isFinite(n) && n > 0) return String(Math.floor(n));
  }
  return null;
}

export function iso(d) {
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

export function addDays(d, n) {
  const x = new Date(d);
  x.setDate(x.getDate() + n);
  return x;
}

export function startOfWeek(d) {
  const x = new Date(d);
  x.setHours(0, 0, 0, 0);
  x.setDate(x.getDate() - x.getDay());
  return x;
}

export function imageUrl(e, w) {
  const src = e && e.img ? String(e.img).trim() : '';
  if (!src || src === 'null' || src === 'undefined') return null;
  if (src.indexOf('images.unsplash.com') !== -1 && src.indexOf('w=') === -1) {
    return src + (src.indexOf('?') === -1 ? '?' : '&') + 'w=' + (w || 640);
  }
  return src;
}

export function thumbGradient(e) {
  const g = e && e.gradient ? String(e.gradient).trim() : '';
  return g || FALLBACK_GRADIENT;
}

export function thumbImg(src, alt, cls) {
  if (!src) return '';
  const klass = cls ? (' class="' + cls + '"') : '';
  return '<img loading="lazy"' + klass + ' src="' + escapeHtml(src) + '" alt="' + escapeHtml(alt || '') + '" onerror="this.onerror=null;this.remove();">';
}

/**
 * Build event detail URL using host config template (default `/{slug}`).
 * Prefer explicit e.url when present.
 */
export function eventHref(e) {
  if (e && e.url && e.url !== '#') return e.url;
  const slug = e && e.slug ? String(e.slug) : '';
  if (!slug) return '#';
  const tpl = hostConfig().detail_url_template || '/{slug}';
  if (tpl.indexOf('{slug}') !== -1) {
    return tpl.split('{slug}').join(slug);
  }
  return tpl.replace(/\/?$/, '/') + slug;
}

export function fmtIcon(f) {
  if (f === 'online') {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 21h8M12 18v3"/></svg>';
  }
  if (f === 'hybrid') {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M4 6h16M4 18h16"/></svg>';
  }
  return ICONS.pin;
}

export function fmtLabel(f) {
  if (f === 'online') return 'Online';
  if (f === 'hybrid') return 'Hybrid';
  return 'In person';
}

export function eventKey(e) {
  return String(e && e.id != null ? e.id : 0) + ':' + String(e && e.slug ? e.slug : '');
}

export function countFromEvents(list) {
  let upcoming = 0, live = 0, past = 0;
  (list || []).forEach((e) => {
    if (e.status === 'upcoming') upcoming++;
    else if (e.status === 'live') live++;
    else if (e.status === 'past') past++;
  });
  return { upcoming, live, past, all: upcoming + live + past };
}
