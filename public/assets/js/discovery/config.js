/** @typedef {'all'|'upcoming'|'live'|'past'} DiscoveryTab */
/** @typedef {'agenda'|'grid'|'calendar'|'list'} DiscoveryView */

export const PER_PAGE = 12;
export const SCHEMA_VERSION = 1;

export const FALLBACK_GRADIENT = 'linear-gradient(135deg, #fb923c 0%, #ea580c 100%)';
export const DEFAULT_COLOR = '#2D3C69';

export const MO = ['January','February','March','April','May','June','July','August','September','October','November','December'];
export const MOA = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
export const WD = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

export const ICONS = {
  pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
  arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
  clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
  cal: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
  agenda: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>'
};

/** @type {{ default_view: DiscoveryView, default_tab: DiscoveryTab, detail_url_template: string, features: { hero: boolean, load_more: boolean } }} */
const DEFAULT_HOST_CONFIG = {
  default_view: 'calendar',
  default_tab: 'all',
  detail_url_template: '/{slug}',
  features: { hero: true, load_more: true },
};

let activeHostConfig = Object.assign({}, DEFAULT_HOST_CONFIG, {
  features: Object.assign({}, DEFAULT_HOST_CONFIG.features),
});

/**
 * Normalize host config from AMCOB_DISCOVERY.config (portable across sites).
 * @param {Record<string, unknown>|null|undefined} raw
 */
export function resolveHostConfig(raw) {
  raw = raw && typeof raw === 'object' ? raw : {};
  const features = raw.features && typeof raw.features === 'object' ? raw.features : {};
  const view = raw.default_view;
  const tab = raw.default_tab;
  const template = raw.detail_url_template;

  return {
    // Agenda UI is temporarily disabled — map leftover config to grid.
    default_view: (view === 'grid' || view === 'calendar' || view === 'list')
      ? view
      : DEFAULT_HOST_CONFIG.default_view,
    default_tab: (tab === 'all' || tab === 'upcoming' || tab === 'live' || tab === 'past')
      ? tab
      : DEFAULT_HOST_CONFIG.default_tab,
    detail_url_template: (typeof template === 'string' && template)
      ? template
      : DEFAULT_HOST_CONFIG.detail_url_template,
    features: {
      hero: features.hero !== false,
      load_more: features.load_more !== false,
    },
  };
}

export function applyHostConfig(raw) {
  activeHostConfig = resolveHostConfig(raw);
  return activeHostConfig;
}

export function hostConfig() {
  return activeHostConfig;
}
