# AMCOB Events API Reference

**Base URL:** `https://erp.amcob.org/api/v1`  
**Auth header:** `X-Events-Api-Key: {key}` or `Authorization: Bearer {key}`  
**API key:** `ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X` _(do not commit to git)_

---

## Table of Contents

### Website API (events.amcob.org)
1. [Ticketing Guide](#ticketing-guide)
2. [GET /events/filters](#get-eventsfilters) — Step 1 · Build filter dropdowns
3. [GET /events](#get-events) — Step 2 · Browse / list events
4. [GET /events/{slug}](#get-eventsslug) — Step 3 · Event page + tickets
5. [POST /events/{slug}/preview-coupon](#post-eventsslugpreview-coupon) — Step 4 · Preview coupon
6. [POST /events/{slug}/register](#post-eventsslugregister) — Step 5 · Checkout
7. [Post-event feedback](#post-event-feedback) — Website only
8. [GET /events/ticket-types](#get-eventsticket-types) — Optional · Catalog only
9. [GET /events/locations](#get-eventslocations) — Legacy

### Shared
10. [POST /auth/lookup](#post-authlookup) — Role lookup by email

### Coordinator Mobile API
11. [Auth](#coordinator-auth)
12. [Dashboard](#coordinator-dashboard)
13. [Events](#coordinator-events)
14. [Invitations](#coordinator-invitations)
15. [Guests / Registrations](#coordinator-guests)
16. [Check-in](#coordinator-check-in)
17. [Tickets](#coordinator-tickets)
18. [Newsfeed](#coordinator-newsfeed)
19. [Chat with attendees](#coordinator-chat)

### Attendee Mobile API
20. [Auth (OTP)](#attendee-auth)
21. [Profile](#attendee-profile)
22. [Dashboard](#attendee-dashboard)
23. [Events / Hub](#attendee-events)
24. [My Tickets](#attendee-tickets)
25. [Chat](#attendee-chat)

### Shared
26. [Notifications & Push (FCM)](#notifications--push)

27. [General Notes](#general-notes)

---

## Changelog

### Latest · 19 Aug 2026
- **GET /events/{slug}/feedback** — load post-event feedback form (token = guest `check_in_token` from email link). Returns event, attendee prefill, `already_submitted`, `existing_feedback`.
- **POST /events/{slug}/feedback** — submit rating (1–5) + feedback text. One submission per attendee per event. Website page: `/{slug}/feedback?token=…`.

### Latest · 12 Aug 2026
- **GET /events/{slug}** — `allow_multiple_guests` (bool) and `max_guests_per_registration` (int). When multi is on, website checkout can collect multiple guests.
- **POST /events/{slug}/register** — multi-guest body: `ticket_id` + `guests: [{ email, first_name, last_name, phone?, job_title?, company_name?, linkedin_url? }, …]` (up to `max_guests_per_registration`). Single-guest flat fields still supported when multi is off. Charge = ticket price × guest count. Success payload may include `registrations[]`, `tickets[]`, and `guest_count` (singular `ticket` / `registration` still returned for single-guest).

### Latest · 5 Aug 2026
- **GET /events/filters** — response now includes `platforms[]` (`{ id, name, url, value, label }`) for platforms with published public events. Use `platforms[].value` (id) with list filter `platform_id=`.
- **GET /events** — filter by platform: `platform_id={id}` or `platform={id|name}`. Echoed in `meta.filters.platform_id` / `meta.filters.platform`. Each list item includes `platform: { id, name, url } | null`.
- **GET /events/{slug}** — same platform object on detail (unchanged shape).
- **POST /events/{slug}/preview-coupon** and **POST /events/{slug}/register** — coupons may be restricted in CRM to specific ticket types. Always send the chosen `ticket_id` with `coupon_code`. Invalid ticket returns 422.

### Earlier · Aug 2026 · Changed APIs
- **GET /events** — added `platform`, `is_current`; new filters `current=1`, `category=` (alias of `tag=`), `country=`, `state=`, `city=`. Still no `tickets[]` on list — see Ticketing Guide.
- **GET /events/{slug}** — `tickets[]` is checkout source of truth. Also added `platform`, `is_current`, `speakers[]`, flat `agenda[]` + grouped `agendas[]`, `categories` (= tags), `date_label`, `is_online`, `online_url`, capacity counts.
- **POST /events/{slug}/register** — optional `coupon_code`; `ticket_id` must be event `tickets[].id`.

### Earlier · Aug 2026 · New APIs
- **GET /events/ticket-types** — CRM catalog templates only (`?platform_id=`). Never use as checkout `ticket_id`.
- **GET /events/filters** — single response for all filter dropdowns (countries, states, cities, categories; platforms added in Latest above).
- **GET /events/locations** — legacy location strings. Prefer `/events/filters`.
- **POST /events/{slug}/preview-coupon** — preview discount before register.

---

## Ticketing Guide

> **Source of truth for listing, displaying, and selling tickets on the website.**

### Who does what

| Role | Responsibility |
|------|---------------|
| CRM admin | Creates Platforms, Ticket Types (catalog defaults), and Events. Sets each event's price/capacity. |
| Website / API client | Lists events, loads `tickets[]` from event detail, previews coupons, registers with `ticket_id` + payment. |

### Two different "ticket" objects

| Object | Endpoint | Use |
|--------|----------|-----|
| **Catalog ticket type** | `GET /events/ticket-types` | Platform-level template. **Never** use `id` as `ticket_id` in register. |
| **Event ticket (checkout)** | `GET /events/{slug}` → `data.tickets[]` | Per-event snapshot. **Always** use `tickets[].id` as `ticket_id`. |

### Price isolation

Event A sells VIP at $500. If an admin later changes the catalog VIP default to $300, Event A's `tickets[].price` stays $500. Each event owns its copied price.

```
// WRONG — catalog id
POST /events/{slug}/register  { "ticket_id": 4 }   // 4 is ticket_type catalog id

// CORRECT — event ticket id from detail
GET  /events/annual-gala-2026
// → data.tickets[0].id = 88, price = 500
POST /events/annual-gala-2026/register  { "ticket_id": 88, ... }
```

### Recommended website flow

1. **Filters** — `GET /events/filters` to build country / state / city / category / platform dropdowns.
2. **Browse** — `GET /events` with filters. List has `platform` but no `tickets[]`.
3. **Event page** — `GET /events/{slug}`. Render `tickets[]`: name, `price_label`, `available`, `is_free`, `requires_approval`.
4. **Optional coupon** — `POST /events/{slug}/preview-coupon` with `ticket_id` + `coupon_code`.
5. **Checkout** — `POST /events/{slug}/register` with same `ticket_id`, guest fields (or `guests[]` when multi is on), and `payment` (omit if free). Total charged = ticket price × guest count.

### Event ticket field map

| Field | Use on website |
|-------|---------------|
| `id` | Send as `ticket_id` to register / preview-coupon |
| `ticket_type_id` | Informational link to catalog origin — not for checkout |
| `name`, `description` | UI labels |
| `price`, `price_label`, `currency` | Display + what server charges (source of truth) |
| `capacity`, `sold_count`, `available` | Stock UI; `null` capacity = unlimited |
| `is_free` | Skip payment UI when `true` |
| `requires_approval` | Warn guest that registration may need CRM approval |

### Rules of thumb
- Always re-fetch `GET /events/{slug}` before checkout so price/availability are current.
- Never hard-code ticket prices — display API values; server charges server-side.
- List endpoint intentionally omits `tickets[]` (lighter payload for listings).
- Same catalog VIP on two events → two different `tickets[].id` values (possibly different prices).

---

## Website API

### GET /events/filters

**Step 1 · Build filter dropdowns**

Returns dropdown values that exist on published public events. Supports cascade: `?country=US`, `?country=US&state=IL`, `?country=US&state=IL&city=Chicago`.

```http
GET https://erp.amcob.org/api/v1/events/filters HTTP/1.1
Accept: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```

**Response `200 OK`**
```json
{
  "data": {
    "countries": [{ "value": "US", "label": "US" }],
    "states":    [{ "value": "IL", "label": "IL" }],
    "cities":    [{ "value": "Chicago", "label": "Chicago" }],
    "categories": [
      { "id": 1, "name": "Dinner Mixers", "value": "Dinner Mixers", "label": "Dinner Mixers" },
      { "id": 2, "name": "Networking",    "value": "Networking",    "label": "Networking" }
    ],
    "platforms": [
      { "id": 1, "name": "AMCOB Events", "url": "https://events.amcob.org", "value": 1, "label": "AMCOB Events" },
      { "id": 2, "name": "Lead",         "url": "https://lead.amcob.org",   "value": 2, "label": "Lead" }
    ]
  }
}
```

---

### GET /events

**Step 2 · Browse / list events**

Paginated list of published events. No `tickets[]` on list — use detail for pricing.

**Query params:**

| Param | Values | Notes |
|-------|--------|-------|
| `upcoming` | `1` | Upcoming events |
| `past` | `1` | Past events |
| `current` | `1` | Happening now (`is_current = true`) |
| `category` | name | Alias of `tag=` |
| `tag` | name | Category filter |
| `platform_id` | id | Filter by platform id |
| `platform` | id or name | Filter by platform |
| `country` | string | |
| `state` | string | |
| `city` | string | |
| `sort` | `starts_at` | |
| `direction` | `asc` \| `desc` | |
| `per_page` | int | Default 20 |
| `page` | int | |

```http
GET https://erp.amcob.org/api/v1/events HTTP/1.1
Accept: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "slug": "annual-gala-2026",
      "title": "Annual Gala 2026",
      "short_description": "An evening of networking and celebration.",
      "status": "published",
      "visibility": "public",
      "event_type": "in_person",
      "starts_at": "2026-09-15T18:00:00+00:00",
      "ends_at": "2026-09-15T22:00:00+00:00",
      "timezone": "America/New_York",
      "location_label": "Chicago, IL",
      "location": {
        "label": "Chicago, IL",
        "full_address": "AMCOB Center, 123 Main St, Chicago, IL, US",
        "type": "in_person",
        "venue_name": "AMCOB Center",
        "venue_address": "123 Main St",
        "venue_city": "Chicago",
        "venue_state": "IL",
        "venue_country": "US",
        "coordinate": { "latitude": 41.8781, "longitude": -87.6298 },
        "online_url": null
      },
      "tags": [{ "id": 1, "name": "Dinner Mixers" }],
      "platform": { "id": 1, "name": "AMCOB Events", "url": "https://events.amcob.org" },
      "is_current": true,
      "theme": { "preset": "sunset", "color": "#f97316", "gradient": "..." },
      "cover": { "type": "image", "url": "https://...", "gradient": null },
      "capacity": 200,
      "registration_count": 47,
      "spots_remaining": 153,
      "public_url": "https://events.amcob.org/annual-gala-2026",
      "published_at": "2026-06-01T12:00:00+00:00",
      "created_at": "2026-05-28T10:00:00+00:00",
      "hosted_by": { "id": 18, "name": "AMCOB", "email": "events@amcob.org" }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1,
    "filters": {
      "country": null, "state": null, "city": null,
      "tag": null, "category": null, "platform_id": null, "platform": null
    }
  },
  "links": {
    "first": "https://erp.amcob.org/api/v1/events?page=1",
    "last":  "https://erp.amcob.org/api/v1/events?page=1",
    "prev": null,
    "next": null
  }
}
```

> **Note:** `spots_remaining` is present on the list payload. `tickets[]` is not — load from detail for checkout.

---

### GET /events/{slug}

**Step 3 · Event page + tickets (checkout source of truth)**

Full event payload including `tickets[]`, `speakers[]`, `agendas[]`. Invite-only: pass `?invite={token}`.

> **Always use `data.tickets[].id` as `ticket_id`** — never catalog ids.

```http
GET https://erp.amcob.org/api/v1/events/annual-gala-2026 HTTP/1.1
Accept: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```

**Response `200 OK`**
```json
{
  "data": {
    "id": 1,
    "slug": "annual-gala-2026",
    "title": "Annual Gala 2026",
    "description": "<p>Full HTML description...</p>",
    "short_description": "An evening of networking.",
    "status": "published",
    "visibility": "public",
    "event_type": "in_person",
    "starts_at": "2026-09-15T18:00:00+00:00",
    "date_label": "Tue, Sep 15, 2026 · 6:00 PM – 10:00 PM",
    "is_current": true,
    "is_online": false,
    "online_url": null,
    "location_label": "Chicago, IL",
    "location": {
      "label": "Chicago, IL",
      "full_address": "AMCOB Center, 123 Main St, Chicago, IL, US",
      "type": "in_person",
      "venue_name": "AMCOB Center",
      "venue_address": "123 Main St",
      "venue_city": "Chicago",
      "venue_state": "IL",
      "venue_country": "US",
      "coordinate": { "latitude": 41.8781, "longitude": -87.6298 },
      "online_url": null
    },
    "tags":       [{ "id": 1, "name": "Dinner Mixers" }],
    "categories": [{ "id": 1, "name": "Dinner Mixers" }],
    "platform": { "id": 1, "name": "AMCOB Events", "url": "https://events.amcob.org" },
    "capacity": 200,
    "registration_count": 47,
    "require_registration_approval": false,
    "waitlist_enabled": false,
    "allow_multiple_guests": false,
    "max_guests_per_registration": 1,
    "hosted_by": { "id": 18, "name": "AMCOB", "email": "events@amcob.org" },
    "host": { "id": 18, "name": "AMCOB", "email": "events@amcob.org" },
    "tickets_count": 2,
    "tickets": [
      {
        "id": 88,
        "ticket_type_id": 4,
        "name": "VIP",
        "description": null,
        "price": 500,
        "price_label": "$500.00",
        "currency": "USD",
        "capacity": 100,
        "sold_count": 12,
        "available": 88,
        "is_free": false,
        "requires_approval": false
      },
      {
        "id": 89,
        "ticket_type_id": 5,
        "name": "General Admission",
        "price": 0,
        "price_label": "Free",
        "currency": "USD",
        "capacity": 200,
        "sold_count": 47,
        "available": 153,
        "is_free": true,
        "requires_approval": false
      }
    ],
    "speakers_count": 1,
    "speakers": [
      {
        "id": 1,
        "name": "Dr. Amina Hassan",
        "designation": "Keynote Speaker",
        "bio": "Advocate for inclusive entrepreneurship.",
        "image_url": "https://...",
        "linkedin_url": "https://linkedin.com/in/...",
        "sort_order": 10
      }
    ],
    "agenda_count": 2,
    "agenda": [
      { "id": 11, "title": "Opening keynote",  "sort_order": 0, "agenda_id": 3, "agenda_title": "Day 1 Outline" },
      { "id": 12, "title": "Networking break", "sort_order": 1, "agenda_id": 3, "agenda_title": "Day 1 Outline" }
    ],
    "agendas": [
      {
        "id": 3,
        "title": "Day 1 Outline",
        "items": [
          { "id": 11, "title": "Opening keynote",  "sort_order": 0 },
          { "id": 12, "title": "Networking break", "sort_order": 1 }
        ]
      }
    ],
    "public_url": "https://events.amcob.org/annual-gala-2026",
    "published_at": "2026-06-01T12:00:00+00:00",
    "created_at": "2026-05-28T10:00:00+00:00",
    "updated_at": "2026-06-01T12:00:00+00:00"
  }
}
```

**Key fields for the website:**

| Field | Notes |
|-------|-------|
| `date_label` | Pre-formatted date string — use instead of manual Carbon parse |
| `location_label` | Short location for display |
| `is_current` | Show "Happening Now" badge |
| `is_online` + `online_url` | Show virtual link instead of venue |
| `tickets[].available` | `null` = unlimited; `0` = sold out |
| `tickets[].is_free` | Skip payment fields when `true` |
| `tickets[].requires_approval` | Warn user registration needs approval |
| `require_registration_approval` | Global approval notice |
| `waitlist_enabled` | Show waitlist button when all tickets sold out |
| `allow_multiple_guests` | When `true`, checkout collects `guests[]` (up to max) |
| `max_guests_per_registration` | Max guests in one register call when multi is on |
| `hosted_by` / `host` | Organizer display object; use either with fallback |
| `agendas[]` | Grouped agenda sections (prefer over flat `agenda[]`) |

---

### POST /events/{slug}/preview-coupon

**Step 4 · Preview coupon discount (before checkout)**

Does not charge a card or create a registration. Send the same `ticket_id` you will use at register — coupon codes may be restricted to specific ticket types.

**Errors:** `422` for invalid, expired, wrong event, wrong ticket type, or max-uses reached.

```http
POST https://erp.amcob.org/api/v1/events/annual-gala-2026/preview-coupon HTTP/1.1
Accept: application/json
Content-Type: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```
```json
{
  "ticket_id": 88,
  "coupon_code": "AMCOB20"
}
```

**Response `200 OK`**
```json
{
  "data": {
    "ticket_id": 88,
    "ticket_name": "VIP",
    "pricing": {
      "original": 500,
      "discount": 100,
      "amount": 400,
      "currency": "USD",
      "coupon": {
        "id": 3,
        "code": "AMCOB20",
        "type": "percent",
        "label": "20% off"
      }
    }
  }
}
```

**Pricing fields:**

| Field | Description |
|-------|-------------|
| `pricing.original` | Original ticket price |
| `pricing.discount` | Amount saved |
| `pricing.amount` | Final price to charge |
| `pricing.coupon.label` | Human-readable discount label (e.g. "20% off") |

---

### POST /events/{slug}/register

**Step 5 · Checkout — Register + pay (Authorize.net)**

Charges Authorize.net for paid tickets using the event's snapshotted price, then creates the registration and emails the QR ticket. **Failed payments never create a registration or send email.**

**Order of operations:**
1. Validate attendee + ticket (published event, capacity, duplicate email)
2. Paid tickets: charge Authorize.net → `422` on decline (no enrollment, no email; attempt saved to CRM Revenue)
3. Payment succeeds (or free ticket): create registration, send confirmation email with QR

**Request fields:**

| Field | Required | Notes |
|-------|----------|-------|
| `ticket_id` | ✓ | Must be `GET /events/{slug}` → `tickets[].id` |
| `email` | ✓ (single) | Required when **not** sending `guests[]` |
| `first_name` | — | Single-guest flat field |
| `last_name` | — | Single-guest flat field |
| `phone` | — | Single-guest flat field |
| `job_title` | — | Attendee job title |
| `company_name` | — | Attendee company |
| `linkedin_url` | — | LinkedIn profile URL |
| `guests` | ✓ (multi) | When `allow_multiple_guests` is true: array of guest objects (1…`max_guests_per_registration`). Each guest: `email` (required), `first_name`, `last_name`, optional `phone`, `job_title`, `company_name`, `linkedin_url`. Do **not** also send top-level `email` / name fields. |
| `invite` | — | For invite-only events |
| `coupon_code` | — | Must use same `ticket_id` as preview. Discount applies per ticket; total charge scales with guest count. |
| `payment.card` | Paid tickets | `number`, `expiration` (MM/YY or MMYY), optional `cvv` |
| `payment.opaque_data` | Paid tickets (preferred) | Accept.js `data_descriptor` + `data_value` |

> Free tickets (`is_free: true` / `price: 0`): omit `payment` entirely — enrolled immediately.  
> Accept.js preferred in production so raw card data never hits your server.  
> **Charge total** = event ticket `price` × number of guests (after coupon, if any).

```http
POST https://erp.amcob.org/api/v1/events/annual-gala-2026/register HTTP/1.1
Accept: application/json
Content-Type: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```

**Single-guest body** (when `allow_multiple_guests` is false):
```json
{
  "email": "guest@example.com",
  "first_name": "Alex",
  "last_name": "Guest",
  "phone": "+1 555 0100",
  "job_title": "Founder",
  "company_name": "Acme Co",
  "linkedin_url": "https://linkedin.com/in/alexguest",
  "ticket_id": 88,
  "coupon_code": "AMCOB20",
  "payment": {
    "card": {
      "number": "4111111111111111",
      "expiration": "12/28",
      "cvv": "123"
    }
  }
}
```

**Multi-guest body** (when `allow_multiple_guests` is true):
```json
{
  "ticket_id": 88,
  "coupon_code": "AMCOB20",
  "guests": [
    {
      "email": "alex@example.com",
      "first_name": "Alex",
      "last_name": "Guest",
      "job_title": "Founder",
      "company_name": "Acme Co",
      "linkedin_url": "https://linkedin.com/in/alexguest"
    },
    {
      "email": "sam@example.com",
      "first_name": "Sam",
      "last_name": "Lee",
      "job_title": "COO",
      "company_name": "Acme Co"
    }
  ],
  "payment": {
    "card": {
      "number": "4111111111111111",
      "expiration": "12/28",
      "cvv": "123"
    }
  }
}

/* Accept.js alternative (recommended in production):
"payment": {
  "opaque_data": {
    "data_descriptor": "COMMON.ACCEPT.INAPP.PAYMENT",
    "data_value": "eyJjb2RlIjoiNTBfMl8…"
  }
} */
```

**Response `201 Created`**
```json
{
  "message": "Registration successful. A confirmation email with your ticket and QR code has been sent.",
  "data": {
    "guest_count": 1,
    "registration": {
      "id": 88,
      "email": "guest@example.com",
      "first_name": "Alex",
      "last_name": "Guest",
      "full_name": "Alex Guest",
      "status": "confirmed",
      "status_label": "Confirmed",
      "registered_at": "2026-07-28T14:00:00+00:00"
    },
    "registrations": [
      {
        "id": 88,
        "email": "guest@example.com",
        "first_name": "Alex",
        "last_name": "Guest",
        "full_name": "Alex Guest",
        "status": "confirmed",
        "status_label": "Confirmed",
        "registered_at": "2026-07-28T14:00:00+00:00"
      }
    ],
    "payment": {
      "id": 15,
      "amount": 75,
      "currency": "USD",
      "status": "paid",
      "status_label": "Paid",
      "transaction_id": "60123456789",
      "card_last_four": "1111",
      "card_type": "Visa",
      "paid_at": "2026-07-28T14:00:00+00:00"
    },
    "ticket": {
      "registration_id": 88,
      "event_id": 1,
      "event_title": "Annual Gala 2026",
      "ticket_name": "General Admission",
      "status": "confirmed",
      "status_label": "Confirmed",
      "guest_name": "Alex Guest",
      "email": "guest@example.com",
      "check_in_token": "…",
      "qr_payload": "amcob-event:1:…",
      "qr_image_base64": "iVBOR…"
    },
    "tickets": [
      {
        "registration_id": 88,
        "event_id": 1,
        "event_title": "Annual Gala 2026",
        "ticket_name": "General Admission",
        "status": "confirmed",
        "status_label": "Confirmed",
        "guest_name": "Alex Guest",
        "email": "guest@example.com",
        "check_in_token": "…",
        "qr_payload": "amcob-event:1:…",
        "qr_image_base64": "iVBOR…"
      }
    ]
  }
}
```

> Multi-guest success: prefer `data.tickets[]`, `data.registrations[]`, and `data.guest_count`. Singular `ticket` / `registration` remain for backward compatibility (typically the first guest).

**Error `422` — Declined payment**
```json
{
  "message": "The credit card has declined.",
  "payment": {
    "id": 16,
    "amount": 75,
    "currency": "USD",
    "status": "failed",
    "status_label": "Failed",
    "transaction_id": null,
    "card_last_four": null,
    "card_type": null,
    "paid_at": null
  }
}
```

> **QR format:** `amcob-event:{event_id}:{check_in_token}` — usable with CRM desk check-in and coordinator mobile app.  
> **Post-registration:** display `data.ticket.qr_image_base64` as the confirmation QR image.  
> **Server .env:** `AUTHORIZENET_API_LOGIN_ID`, `AUTHORIZENET_TRANSACTION_KEY`. Sandbox vs live follows `APP_ENV` (production → live).

---

## Post-event feedback

> **Website only** — not used by mobile apps.  
> **Page URL:** `https://events.amcob.org/{slug}/feedback?token={check_in_token}`  
> Token = guest `check_in_token` from the post-event email (~2 hours after event ends).

### GET /events/{slug}/feedback

Load form prefill and validate token.

```http
GET https://erp.amcob.org/api/v1/events/annual-gala-2026/feedback?token=abc123 HTTP/1.1
Accept: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```

**Response `200 OK`**
```json
{
  "data": {
    "event": {
      "id": 1,
      "slug": "annual-gala-2026",
      "title": "Annual Gala 2026",
      "starts_at": "2026-09-15T18:00:00+00:00",
      "ends_at": "2026-09-15T22:00:00+00:00",
      "public_url": "https://events.amcob.org/annual-gala-2026"
    },
    "attendee": {
      "email": "guest@example.com",
      "first_name": "Alex",
      "last_name": "Guest",
      "full_name": "Alex Guest"
    },
    "already_submitted": false,
    "existing_feedback": null
  }
}
```

**UI logic:**
- `already_submitted === true` → hide form; show thank-you / existing feedback
- `attendee === null` → invalid token
- Event not ended → `422`: *"Feedback is available after the event has ended."*

---

### POST /events/{slug}/feedback

```http
POST https://erp.amcob.org/api/v1/events/annual-gala-2026/feedback HTTP/1.1
Accept: application/json
Content-Type: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```
```json
{
  "token": "guest-check-in-token-from-url",
  "email": "guest@example.com",
  "first_name": "Alex",
  "last_name": "Guest",
  "rating": 5,
  "feedback": "Great networking and well organized sessions."
}
```

| Field | Required | Rules |
|-------|----------|-------|
| `token` | ✓ | From URL `?token=` |
| `email` | ✓ | Must match registration email |
| `first_name` | — | max 100 |
| `last_name` | — | max 100 |
| `rating` | ✓ | integer 1–5 |
| `feedback` | ✓ | string 3–5000 |

**Response `201 Created`**
```json
{
  "message": "Thank you for your feedback.",
  "data": {
    "id": 12,
    "event_id": 1,
    "rating": 5,
    "feedback": "Great networking and well organized sessions.",
    "submitted_at": "2026-09-15T23:30:00+00:00"
  }
}
```

**Errors `422` (examples):** feedback not open yet, invalid token, already submitted, email mismatch.

---

### GET /events/ticket-types

**Optional · Catalog templates only**

CRM platform-level defaults. **Do not use catalog `id` as `ticket_id` in register.** Only needed for admin UIs.

```http
GET https://erp.amcob.org/api/v1/events/ticket-types?platform_id=1 HTTP/1.1
Accept: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 4,
      "platform_id": 1,
      "name": "VIP",
      "description": null,
      "price": 300,
      "price_label": "$300.00",
      "currency": "USD",
      "capacity": 100,
      "requires_approval": false
    }
  ],
  "meta": {
    "note": "Catalog templates only. For checkout: GET /events/{slug} → tickets[].id as ticket_id.",
    "checkout": "Use event detail tickets[], never this catalog id, when calling register or preview-coupon.",
    "platform_id": 1
  }
}
```

---

### GET /events/locations

**Legacy — prefer GET /events/filters**

Returns string arrays only (no objects, no categories).

```http
GET https://erp.amcob.org/api/v1/events/locations HTTP/1.1
Accept: application/json
X-Events-Api-Key: ev_uAPMQUqG3nboEDqAEhCQAQ39mxI5je2p6vT2WO6X
```

**Response `200 OK`**
```json
{
  "data": {
    "countries": ["CA", "US"],
    "states":    ["DC", "IL"],
    "cities":    ["Chicago", "Washington"]
  }
}
```

---

## Shared

### POST /auth/lookup

Identifies whether an email belongs to a coordinator or attendee. For attendees, also sends the OTP login code immediately.

> No authentication required.

```http
POST https://erp.amcob.org/api/v1/auth/lookup HTTP/1.1
Accept: application/json
Content-Type: application/json
```
```json
{ "email": "guest@example.com" }
```

**Response `200 OK`**
```json
{ "success": true, "role": "attendee", "message": "Login code sent to your email.", "expires_in_minutes": 10 }

/* Coordinator: */
{ "success": true, "role": "coordinator", "message": "Coordinator account found. Continue with password login." }
```

**Error `404`**
```json
{ "success": false, "message": "No attendee or coordinator account found for this email." }
```

---

## Coordinator Mobile API

> Auth prefix: `/auth/coordinator` · Data prefix: `/mobile/coordinator`  
> **Do not use these routes in the attendee app.**

### Coordinator Auth

#### POST /auth/coordinator/login

```http
POST https://erp.amcob.org/api/v1/auth/coordinator/login HTTP/1.1
Content-Type: application/json
```
```json
{ "email": "coordinator@company.com", "password": "your_password", "device_name": "ios-coordinator" }
```

**Response `200 OK`**
```json
{
  "token": "1|abc123...",
  "token_type": "Bearer",
  "expires_in_days": 30,
  "user": {
    "id": 5, "name": "Jane Coordinator", "email": "coordinator@company.com",
    "company_id": 1, "company_name": "AMCOB", "is_company_admin": false,
    "permissions": {
      "view_events": true, "create_events": true, "edit_events": true,
      "manage_event_invitations": true, "manage_event_checkin": true
    }
  }
}
```

#### GET /auth/coordinator/me

Returns current coordinator profile. Send `Authorization: Bearer {token}` from login.

#### POST /auth/coordinator/logout

Revokes the current coordinator token. Send `Authorization: Bearer {token}`.

---

### Coordinator Dashboard

#### GET /mobile/coordinator/dashboard

KPIs for the current month: total events, published/draft/upcoming counts, registration totals.

**Response `200 OK`**
```json
{
  "data": {
    "range": { "label": "Jun 2026", "preset": "month" },
    "kpis": {
      "total_events": 12, "published_events": 8, "draft_events": 3,
      "upcoming_events": 5, "total_registrations": 340
    },
    "recent_events": [],
    "upcoming_events": []
  }
}
```

---

### Coordinator Events

#### GET /mobile/coordinator/events

All company events (draft, published, cancelled). Query: `?status=draft|published|cancelled`.

#### GET /mobile/coordinator/events/{id}

Full event detail for coordinators including tickets, registration counts, and check-in stats.

**Response `200 OK`**
```json
{
  "data": {
    "id": 1, "slug": "annual-gala-2026", "title": "Annual Gala 2026",
    "status": "published", "starts_at": "2026-09-15T18:00:00+00:00",
    "registration_count": 47,
    "tickets": [{ "id": 1, "name": "General Admission", "price": 0, "sold_count": 47 }],
    "stats": { "registration_count": 47, "invitation_count": 120, "checked_in_count": 32 }
  }
}
```

---

### Coordinator Invitations

#### GET /mobile/coordinator/events/{id}/invitations

Invitees with delivery and response tracking. Query params: `status=pending|sent|opened|accepted|registered|declined|failed`, `search={name or email}`.

**Response** includes `data[]` + `stats` (total, pending, sent, opened, accepted, registered, declined, failed).

#### POST /mobile/coordinator/events/{id}/invitations

Add invitees by email (comma or newline separated). Creates pending invitations without sending.

```json
{ "emails": "guest1@example.com, guest2@example.com", "personal_message": "We hope to see you there!" }
```

**Response `201`:** `{ "message": "2 invitation(s) added.", "added": 2, "skipped": 0, "errors": [] }`

#### POST /mobile/coordinator/events/{id}/invitations/send

Send pending invitation emails. Optionally pass specific `invitation_ids`; otherwise sends all pending.

```json
{ "invitation_ids": [10, 11] }
```

**Response:** `{ "message": "2 sent, 0 failed.", "sent": 2, "failed": 0 }`

---

### Coordinator Guests

#### GET /mobile/coordinator/events/{id}/registrations

Guest list. Query: `status=confirmed|pending|waitlisted|cancelled`, `search={text}`.

#### POST /mobile/coordinator/events/{id}/registrations

Manually register a guest (walk-in). Status: `confirmed`, `pending`, or `waitlisted`.

```json
{ "email": "newguest@example.com", "first_name": "Sam", "last_name": "Lee", "event_ticket_id": 1, "status": "confirmed" }
```

**Response `201`** includes registration with `check_in_token`, `qr_payload`.

#### POST /mobile/coordinator/events/{id}/registrations/from-invitations

Convert selected invitations into confirmed registrations.

```json
{ "invitation_ids": [10, 11, 12] }
```

#### PATCH /mobile/coordinator/events/{id}/registrations/{registration_id}/status

Update guest status to `confirmed`, `pending`, `waitlisted`, or `cancelled`.

```json
{ "status": "confirmed" }
```

---

### Coordinator Check-in

#### POST /mobile/coordinator/events/{id}/check-in/scan

Check in by QR payload. Payload format: `amcob-event:{event_id}:{check_in_token}`. Requires `manage_event_checkin` permission.

```json
{ "qr_payload": "amcob-event:1:a1b2c3d4e5f6..." }
```

#### POST /mobile/coordinator/events/{id}/registrations/{registration_id}/check-in

Manual check-in by registration ID (no QR needed).

#### POST /mobile/coordinator/events/{id}/registrations/{registration_id}/undo-check-in

Reverses a check-in.

#### GET /mobile/coordinator/events/{id}/stats

Live door stats: total, confirmed, checked in, pending, waitlisted.

**Response `200 OK`**
```json
{ "data": { "total_registrations": 47, "confirmed": 45, "checked_in": 32, "pending": 2, "waitlisted": 0 } }
```

---

### Coordinator Tickets

#### GET /mobile/coordinator/events/{id}/tickets

Every non-cancelled guest ticket for an event, with `check_in_token` and `qr_payload`.

---

### Coordinator Newsfeed

> Requires `edit_events` permission. Media: upload to S3 first via `POST /mobile/coordinator/news/media`, then pass `media_url` + `media_type`.

#### POST /mobile/coordinator/news/media

Upload image or video (multipart/form-data, field `media`). Max 50 MB. Allowed: jpeg, png, webp, gif, mp4, mov, webm. Returns `url`, `thumbnail_url` (video), `type`, `mime`.

#### GET /mobile/coordinator/news — List company news posts
#### POST /mobile/coordinator/news — Create company-wide or event-scoped post

```json
{ "title": "Welcome", "body": "Doors open at 9am.", "event_id": 1, "media_type": "image", "media_url": "https://...", "is_published": true }
```

#### GET /mobile/coordinator/events/{id}/news — Event-scoped posts
#### POST /mobile/coordinator/events/{id}/news — Create event post
#### PATCH /mobile/coordinator/news/{id} — Update post
#### DELETE /mobile/coordinator/news/{id} — Soft-delete post

---

### Coordinator Chat

#### GET /mobile/coordinator/events/{id}/chat-peers

Confirmed guests for the event with `peer_account_id` (those who have an attendee login).

#### GET /mobile/coordinator/events/{id}/chats — List conversations
#### GET /mobile/coordinator/events/{id}/chats/{peer_account_id} — Thread (marks read)
#### POST /mobile/coordinator/events/{id}/chats/{peer_account_id} — Send message

```json
{ "body": "Your badge is ready at registration." }
```

#### POST /mobile/coordinator/events/{id}/registrations/{registration_id}/chat

Message a guest by registration ID. Creates an attendee account if needed so they can reply.

---

## Attendee Mobile API

> Auth prefix: `/auth/attendee` · Data prefix: `/mobile/attendee`  
> **Separate from coordinator — different tokens, different routes.**

### Attendee Auth

**Login flow:**
1. (Optional) `POST /auth/lookup` — if role is `attendee`, OTP already sent; if `coordinator`, use password login.
2. `POST /auth/attendee/otp/request` — sends 6-digit code to email (only if confirmed registration exists). Skip if used `/auth/lookup`.
3. On "Resend code": `POST /auth/attendee/otp/resend` (60 s cooldown).
4. `POST /auth/attendee/otp/verify` — validate code → receive Bearer token (30 days).
5. Store token; send `Authorization: Bearer {token}` on all `/auth/attendee/*` and `/mobile/attendee/*` routes.
6. On app launch: `GET /auth/attendee/me` to restore session.
7. On sign-out: `POST /auth/attendee/logout`.

> **Dev helper:** Universal OTP `123456` is configured for confirmed guests — no emailed code required. Clear in CRM when not needed.

#### POST /auth/attendee/otp/request

Sends OTP. Only works when a confirmed registration exists for the email.

```json
{ "email": "guest@example.com" }
```
**Response:** `{ "success": true, "message": "Login code sent to your email.", "expires_in_minutes": 10 }`

#### POST /auth/attendee/otp/resend

Same eligibility as request. Issues a new code, invalidates the previous. 60 s cooldown.

**Response:** includes `resend_available_in_seconds: 60`  
**Error `422`:** `{ "message": "Please wait 42 seconds before requesting another code.", "retry_after_seconds": 42 }`

#### POST /auth/attendee/otp/verify

```json
{ "email": "guest@example.com", "code": "123456" }
```

**Response `200 OK`**
```json
{
  "token": "2|xyz789...",
  "token_type": "Bearer",
  "expires_in_days": 30,
  "attendee": {
    "id": 12, "email": "guest@example.com",
    "first_name": "Amina", "last_name": "Khan", "full_name": "Amina Khan",
    "phone": null, "company_name": null, "job_title": null, "avatar_url": null, "bio": null,
    "email_verified_at": "2026-06-23T14:30:00+00:00"
  }
}
```

#### GET /auth/attendee/me — Current attendee profile (same as GET /mobile/attendee/profile)
#### POST /auth/attendee/logout — Revoke token

---

### Attendee Profile

#### GET /mobile/attendee/profile

Returns editable profile fields (same shape as `/auth/attendee/me`).

#### PATCH /mobile/attendee/profile

Email cannot be changed. Updatable: `first_name`, `last_name`, `phone`, `company_name`, `job_title`, `avatar_url`, `bio`.

---

### Attendee Dashboard

#### GET /mobile/attendee/dashboard

Home screen payload after login.

| Key | Description |
|-----|-------------|
| `current_event` | Live registered event or `null` |
| `fellow_attendees` | Other confirmed guests on that current event |
| `past_events` | Past registered events |
| `upcoming_events` | Upcoming published events (registered and not) |
| `available_events` | Live events the attendee can browse but isn't registered for |

Use `is_registered` / `can_open_event_hub` to gate the Event Hub (hub only when registered + confirmed).

---

### Attendee Events

#### GET /mobile/attendee/events — My registered events

Query: `?filter=all|upcoming|current|past`. Returns `is_registered`, `can_open_event_hub`, `registration_id`, `registration_status`.

#### GET /mobile/attendee/events/browse — Browse all company events

Same filter options. Shows events the attendee may or may not be registered for.

#### GET /mobile/attendee/events/{id} — Event Hub detail

Returns 404 if not registered. Confirmed guests get `hub_sections` flags.

**Response includes:**
```json
{
  "hub_sections": {
    "overview": true, "ticket": true, "people": true,
    "news": true, "agenda": true, "speakers": true, "chat": true
  }
}
```

#### GET /mobile/attendee/events/{id}/people — Attendee directory

Confirmed attendees. `account_id` is the chat peer id.

#### GET /mobile/attendee/events/{id}/news — Event news (read-only)
#### GET /mobile/attendee/events/{id}/agenda — Event agenda

Flat bullet list of sessions.

#### GET /mobile/attendee/events/{id}/speakers — Event speakers

Ordered by `sort_order` (ascending).

#### GET /mobile/attendee/events/{id}/ticket — Event QR ticket

Guest must be confirmed. Returns `qr_payload`, `qr_image_base64`, `check_in_token`, `is_checked_in`.

---

### Attendee Tickets

#### GET /mobile/attendee/tickets — All confirmed tickets

All confirmed registrations across events, each with `qr_payload` and `check_in_token`.

---

### Attendee Chat

1:1 chat between confirmed attendees of the same event. Peer id = `account_id` from the people directory.

#### GET /mobile/attendee/events/{id}/chats — Conversation list (last message + unread count)
#### GET /mobile/attendee/events/{id}/chats/{peer_account_id} — Thread (marks incoming as read)
#### POST /mobile/attendee/events/{id}/chats/{peer_account_id} — Send message

```json
{ "body": "See you at registration!" }
```

---

## Notifications & Push

> Prefix: `/api/notifications` and `/api/device-token` (not under `/api/v1`).  
> Both coordinator and attendee tokens are accepted on all notification routes.  
> Configure Firebase in CRM: Developer Doc → Firebase / Push.

### Device Tokens

#### POST /api/device-token/register

```json
{ "token": "fcm-device-token-here", "device_type": "android", "device_id": "optional-id", "device_name": "Pixel 8" }
```

#### POST /api/device-token/remove

```json
{ "token": "fcm-device-token-here" }
```

### Notifications

**List query params:** `per_page`, `page`, `unread_only` (bool), `type` (`new_message`, `event_news`), `sort` (`latest|oldest`).

| Endpoint | Method | Action |
|----------|--------|--------|
| `/api/notifications/unread-count` | GET | Unread count |
| `/api/notifications/types` | GET | Available type keys + labels |
| `/api/notifications/list` | GET | Paginated inbox |
| `/api/notifications/` | GET | Alias of list |
| `/api/notifications/read-multiple` | POST | Mark multiple read (`{ "ids": [1,2] }`) |
| `/api/notifications/read-all` | POST | Mark all read |
| `/api/notifications/delete/multiple` | DELETE | Delete multiple (`{ "ids": [1,2] }`) |
| `/api/notifications/delete/all` | DELETE | Delete all |
| `/api/notifications/{id}` | GET | Get one |
| `/api/notifications/{id}/read` | POST | Mark one read |
| `/api/notifications/{id}` | DELETE | Delete one |

---

## General Notes

- All JSON responses use `Content-Type: application/json`.
- Validation errors → `422` with `message` and optional `errors` object.
- Unauthorized key/token → `401`; missing permission → `403`.
- Coordinator and attendee apps use separate auth endpoints and tokens — never mix on `/mobile/*` routes. Notification & device-token routes accept either.
- Attendee login requires a **confirmed** event registration (pending and waitlisted cannot sign in).
- Push notifications: configure Firebase in CRM → Developer Doc → Firebase / Push (not `.env`). Without credentials, in-app notifications still save; FCM push is skipped.
- Notification routes live under `/api/notifications` (not `/api/v1`).
- **QR format:** `amcob-event:{event_id}:{check_in_token}` — email wallet + coordinator scan.
- **Public registration payments:** Authorize.net charges first for paid tickets; enrollment + email only after payment succeeds. Declined → `422`, no registration. `.env`: `AUTHORIZENET_API_LOGIN_ID`, `AUTHORIZENET_TRANSACTION_KEY`. Live vs sandbox follows `APP_ENV`.
- CORS: configure `EVENTS_API_ALLOWED_ORIGINS` in server environment.
