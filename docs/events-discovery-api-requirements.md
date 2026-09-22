# Events Discovery & Calendar — Backend API Requirements

**Product:** AMCOB Events Website (`events.amcob.org`)  
**Audience:** Backend / ERP API developers  
**Related frontend:** Events discovery page (Agenda · Grid · Calendar · List)  
**Auth (existing):** `X-Events-Api-Key: {key}` or `Authorization: Bearer {key}`  
**Base URL (existing):** `{CRM_API}/api/v1`  
**Document version:** 1.0  
**Date:** 7 September 2026  

---

## 1. Purpose

The website is adopting a new **events discovery** experience. Visitors will browse published events in four views:

| View | Behaviour |
|------|-----------|
| **Agenda** | Date strip + chronological event list |
| **Grid** | Card grid with pagination / “Load more” |
| **Calendar** | Month / Week / Day calendar with events placed by date |
| **List** | Events grouped by calendar day |

All four views share the same filter state (search, category, location, lifecycle tab). The frontend will render calendars **client-side**; it does **not** require Google Calendar, Outlook, or any third-party calendar sync. It only needs a reliable, filterable **list of dated events** from the ERP API.

This document specifies what the website needs from the API so the discovery UI can go live against real data instead of mock data.

---

## 2. Scope

### In scope
- Public event listing for the discovery homepage
- Filter options for category and location
- Lifecycle tabs: **Upcoming**, **Live** (happening now), **Past**
- Date-range support for calendar month / week / day
- Featured events for the homepage hero slider
- Fields required to render cards, agenda rows, and calendar cells

### Out of scope (already covered elsewhere)
- Event detail / tickets / registration (`GET /events/{slug}`, `POST /events/{slug}/register`)
- Coupon preview, payment capture
- Post-event feedback
- Mobile coordinator / attendee APIs
- External calendar OAuth or ICS export (not required for v1)

---

## 3. Existing APIs we will reuse

The discovery page will continue to use the existing Website API surface where possible:

| Endpoint | Role on discovery page |
|----------|------------------------|
| `GET /events/filters` | Build category chips and City / State / Country dropdowns |
| `GET /events` | Primary data source for Agenda, Grid, Calendar, and List |
| `GET /events/{slug}` | Target of “Register” / “View details” links (detail page) |

Please treat the sections below as **confirmations and additive requirements** on top of the current `GET /events` and `GET /events/filters` contracts — not a replacement of the whole Website API.

---

## 4. Functional requirements

### 4.1 Filters endpoint

**Endpoint:** `GET /api/v1/events/filters`

**Required behaviour (confirm existing):**
- Return countries, states, cities, and categories derived from **published public** events
- Support cascade query params (e.g. `?country=US`, `?country=US&state=IL`)

**Response shape (expected):**
```json
{
  "data": {
    "countries": [{ "value": "US", "label": "United States" }],
    "states":    [{ "value": "IL", "label": "Illinois" }],
    "cities":    [{ "value": "Chicago", "label": "Chicago" }],
    "categories": [
      { "id": 1, "name": "Networking", "value": "Networking", "label": "Networking" }
    ]
  }
}
```

**Optional enhancement:** include `color` on each category (hex) so the calendar legend and chips can match CRM branding. If omitted, the website will map colours client-side.

---

### 4.2 Events list endpoint (primary)

**Endpoint:** `GET /api/v1/events`

This is the **single list endpoint** shared by all discovery views. No separate calendar endpoint is required for v1 if date-range filters are supported.

#### 4.2.1 Query parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `upcoming` | `1` | No | Return upcoming events only |
| `current` | `1` | No | Return live / happening-now events (`is_current = true`) |
| `past` | `1` | No | Return past events only |
| `category` / `tag` | string | No | Filter by category / tag name |
| `country` | string | No | Location filter |
| `state` | string | No | Location filter |
| `city` | string | No | Location filter |
| `q` or `search` | string | **Please add if missing** | Free-text search across title, location label, category |
| `from` | date (`YYYY-MM-DD`) | **Please add** | Inclusive start of date range (by event start date in event timezone or UTC — document which) |
| `to` | date (`YYYY-MM-DD`) | **Please add** | Inclusive end of date range |
| `featured` | `1` | **Please add** | Featured events for the hero slider |
| `sort` | string | No | Default `starts_at` |
| `direction` | `asc` \| `desc` | No | Default `asc` for upcoming; `desc` for past is acceptable |
| `per_page` | int | No | Pagination page size (Grid “Load more”) |
| `page` | int | No | Page number |

**Calendar usage note:**  
When the user opens Month / Week / Day, the website will call list with `from` / `to` covering that visible range. Lifecycle tabs (Upcoming / Live / Past) are hidden on Calendar view in the mock; the calendar may need events of **any lifecycle** inside the range. Please confirm that `from` / `to` can be combined without forcing a single lifecycle flag, or document the recommended query pattern (e.g. omit `upcoming`/`past`/`current` when ranging).

#### 4.2.2 Lifecycle mapping (tabs)

| UI tab | Expected API filter / fields |
|--------|------------------------------|
| Upcoming | `upcoming=1` — events that have not started (or not ended — please document rule) |
| Live | `current=1` — `is_current = true` |
| Past | `past=1` — events that have ended |

Please expose clear boolean flags on each item:

- `is_current` (live / happening now)
- `is_past` (**please confirm or add**)

Alternatively, a single field is acceptable:

```json
"lifecycle": "upcoming" | "live" | "past"
```

#### 4.2.3 Required list item fields

Each object in `data[]` must include enough information to render cards and calendar cells **without** calling detail for every row.

| Field | Type | Required | Used for |
|-------|------|----------|----------|
| `id` | int | Yes | Stable key |
| `slug` | string | Yes | Links to `/{slug}` |
| `title` | string | Yes | All views + calendar cell title |
| `starts_at` | ISO 8601 datetime | Yes | Date grouping, calendar placement, agenda ordering |
| `ends_at` | ISO 8601 datetime | Yes | Live/past logic, day spanning (if any) |
| `timezone` | string (IANA) | Yes | Correct local display |
| `short_description` | string \| null | Optional | Secondary copy |
| `event_type` | string | Yes | Format badge: `in_person` / `online` / `hybrid` (or equivalent) |
| `is_online` | bool | Optional | Alternate to `event_type` |
| `location_label` | string \| null | Yes | Short location line |
| `location` | object \| null | Yes | City / state / country / venue for filters & display |
| `tags` or `categories` | array | Yes | Category chip / calendar colour key |
| `cover.url` | string \| null | Yes | Card / hero / list thumbnails |
| `registration_count` | int | Recommended | “X going” on cards |
| `is_current` | bool | Yes | Live tab / badges |
| `is_past` | bool | Yes | Past tab / badges |
| `featured` | bool | **Please add** | Hero eligibility |
| `price_label` | string \| null | **Strongly recommended** | Card price pill (`Free`, `From $75`) without loading tickets |
| `public_url` | string | Optional | Canonical public URL |
| `hosted_by` | object \| null | Optional | Host display |
| `platform` | object \| null | Optional | Platform badge |

**Tickets:** Keep `tickets[]` off the list payload (current design). Checkout remains on `GET /events/{slug}`. Providing `price_label` on list avoids N+1 detail calls for the discovery grid.

**Past / recap (optional for v1):**

| Field | Type | Notes |
|-------|------|-------|
| `attended_count` | int \| null | Shown as “X attended” on past cards |
| `photos_count` | int \| null | Optional recap affordance |
| `recap_url` | string \| null | Optional “View recap” link |

**Live (optional for v1):**

| Field | Type | Notes |
|-------|------|-------|
| `live_label` | string \| null | e.g. “Ends 11:00 AM”, “Session 3 of 5” |

#### 4.2.4 Example list item (target contract)

```json
{
  "id": 12,
  "slug": "fall-quarterly-dinner-mixer",
  "title": "Fall Quarterly Dinner Mixer",
  "short_description": "An evening of networking and celebration.",
  "status": "published",
  "visibility": "public",
  "event_type": "in_person",
  "starts_at": "2026-10-24T18:30:00-04:00",
  "ends_at": "2026-10-24T21:30:00-04:00",
  "timezone": "America/New_York",
  "location_label": "The Ritz-Carlton · Tysons, DMV",
  "location": {
    "venue_name": "The Ritz-Carlton",
    "venue_city": "Tysons",
    "venue_state": "VA",
    "venue_country": "US",
    "label": "The Ritz-Carlton · Tysons, DMV"
  },
  "tags": [{ "id": 3, "name": "Dinner Mixer", "color": "#CE8924" }],
  "cover": { "type": "image", "url": "https://cdn.example.com/events/12/cover.jpg" },
  "price_label": "From $75",
  "registration_count": 86,
  "is_current": false,
  "is_past": false,
  "featured": true,
  "public_url": "https://events.amcob.org/fall-quarterly-dinner-mixer",
  "hosted_by": { "id": 18, "name": "AMCOB" }
}
```

#### 4.2.5 Pagination & meta

Continue returning standard pagination (`meta.current_page`, `last_page`, `per_page`, `total`, `links`).

**Recommended addition for tab badges:**

```json
"meta": {
  "current_page": 1,
  "last_page": 3,
  "per_page": 20,
  "total": 42,
  "counts": {
    "upcoming": 24,
    "live": 2,
    "past": 16
  },
  "filters": {
    "country": null,
    "state": null,
    "city": null,
    "category": null,
    "from": null,
    "to": null,
    "featured": null,
    "q": null
  }
}
```

`counts` should respect the same location / category / search filters currently applied (excluding the lifecycle tab itself), so badge numbers stay accurate as users filter.

---

### 4.3 Featured / hero events

The homepage hero is a slider of featured events.

**Preferred:** `GET /api/v1/events?featured=1&per_page=5&sort=starts_at&direction=asc`

**Alternative:** `GET /api/v1/events/featured`

Each featured item must include at least: `title`, `slug`, `starts_at`, `location_label`, `cover.url`, `price_label`, and category (`tags`).

Only **published**, **public**, and typically **upcoming** (or live) events should appear in the hero.

---

## 5. Calendar behaviour (how the website will call the API)

This section is for implementation clarity — no Google/Outlook integration is required.

| UI mode | Frontend behaviour | Expected API call |
|---------|--------------------|-------------------|
| Month | Render days of month; place events on matching `starts_at` date | `GET /events?from={first_day}&to={last_day}&per_page=100` (or paginate) |
| Week | 7 columns from Sunday–Saturday | `GET /events?from={week_start}&to={week_end}` |
| Day | List events for one date | `GET /events?from={day}&to={day}` |
| Agenda | Rolling date strip + list from a start date | `GET /events?upcoming=1` (plus filters) or `from=` |
| Grid / List | Filtered lifecycle tab | `upcoming=1` / `current=1` / `past=1` + filters + pagination |

**Multi-day events:** If an event spans more than one calendar day, please document whether it appears only on `starts_at` day or on every day through `ends_at`. The website will follow the documented rule.

**Timezone rule:** Please document whether `from` / `to` are evaluated in **UTC** or in each event’s `timezone`. Prefer evaluating against the event’s local calendar date so a 6:30 PM ET dinner lands on the correct day for US visitors.

---

## 6. Non-functional requirements

| Area | Requirement |
|------|-------------|
| Auth | Existing Website API key (`X-Events-Api-Key`) |
| Visibility | Only `published` + `public` events |
| Performance | List payload should remain lean (no tickets, agendas, or speakers arrays) |
| Caching | Responses may be cached briefly by the website; prefer stable field names |
| Errors | Standard JSON error body with `message` for `4xx` / `5xx` |
| CORS | Not required for server-side website calls; required only if a browser SPA calls ERP directly |
| Pagination defaults | Document default `per_page` and max allowed (calendar may need a higher cap or cursor for dense months) |

---

## 7. Gap analysis vs current Website API

| Capability | Status | Action needed |
|------------|--------|---------------|
| `GET /events/filters` | Exists | Confirm cascade + categories |
| `GET /events` with `upcoming` / `current` / `past` | Exists | Confirm lifecycle definitions |
| Location filters `country` / `state` / `city` | Exists | Confirm |
| Category / tag filter | Exists | Confirm |
| `starts_at` / `ends_at` / `timezone` on list | Exists | Confirm |
| Cover + location + tags on list | Exists | Confirm |
| `from` / `to` date range | **Missing / unconfirmed** | **Add** |
| `featured` flag or endpoint | **Missing / unconfirmed** | **Add** |
| Free-text `q` / `search` | **Missing / unconfirmed** | **Add if not present** |
| `is_past` on list items | **Confirm** | Add if absent |
| `price_label` on list | **Confirm** | Strongly recommended |
| `meta.counts` for tabs | **Nice to have** | Recommended |
| Category `color` | **Nice to have** | Optional |
| Past recap fields | **Nice to have** | Optional for v1 |

---

## 8. Acceptance criteria

Backend delivery is complete for discovery v1 when:

1. Website can load filters and populate category + location controls from `GET /events/filters`.
2. Website can load Upcoming / Live / Past lists via `GET /events` with existing lifecycle params.
3. Website can request a calendar month via `from` / `to` and receive all relevant public events in that range.
4. Each list item includes the required fields in §4.2.3 so Grid, List, Agenda, and Calendar render without per-card detail requests.
5. Featured/hero events are available via `featured=1` (or a dedicated endpoint).
6. Search (if implemented) filters by title / location / category as documented.
7. Auth, pagination, and error contracts remain compatible with the existing Website API documentation.

---

## 9. Open questions for backend

Please answer these so frontend wiring can be finalized:

1. Exact rule for **Upcoming** vs **Live** vs **Past** (based on `starts_at` / `ends_at` / timezone)?
2. Are `from` / `to` applied in UTC or event-local dates?
3. Do multi-day events appear on every day in range, or only on start day?
4. Is free-text search already supported? If yes, which param name (`q` vs `search`) and which fields?
5. Can we add `price_label` on list without exposing full `tickets[]`?
6. Maximum `per_page` allowed for calendar month loads?
7. Should `featured` events be manually flagged in CRM, or auto-selected (e.g. next N upcoming)?

---

## 10. Suggested implementation priority

| Priority | Item |
|----------|------|
| P0 | Confirm existing list + filters work for discovery tabs |
| P0 | Add `from` / `to` date-range filters |
| P0 | Confirm / add `is_past` (or `lifecycle`) on list items |
| P1 | Add `featured` support for hero |
| P1 | Add / confirm `price_label` on list |
| P1 | Add free-text search (`q` / `search`) |
| P2 | `meta.counts` for tab badges |
| P2 | Category colours; past recap fields |

---

## 11. Contact / handoff

- **Frontend consumer:** AMCOB Events website (Laravel) at `events.amcob.org`
- **Existing API reference:** `docs/event-api.md` (Website API section)
- **UI reference:** Events discovery mock (Agenda / Grid / Calendar / List)

Once the gaps in §7 are confirmed or implemented, the website team can replace mock event data with live ERP responses and ship the discovery page.

---

*End of document*
