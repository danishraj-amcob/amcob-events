/**
 * Discovery frontend layout
 * ────────────────────────
 * app.js              — UI wiring + render loop (load this as type=module)
 * store.js            — events, paging, filters, counts
 * feed.js             — /events/discovery client (Load more)
 * config.js           — constants + host config (detail URL, features)
 * utils.js            — escape, dates, colors, keys
 * templates.js        — card HTML builders
 * views/hero.js       — featured slider
 * views/agenda.js     — 2-week agenda
 * views/grid.js       — card grid
 * views/list.js       — day-grouped list
 * views/calendar.js   — month / week / day
 *
 * Contract: docs/discovery-module-contract.md (schema_version)
 * Server:   App\Support\EventsDiscovery\
 * Payload:  window.AMCOB_DISCOVERY
 */
