# AMCOB Event Systems — Technical Analysis

> Reference document for migrating native event/booking systems in three AMCOB properties into the centralised ERP CRM backend.

---

## 1. lead.amcob.org-new

### 1.1 Stack
Laravel 11 · PHP · MySQL · Tailwind CSS · Vite · Authorize.net (payments)

### 1.2 What this site is
A multi-day conference/expo site (AMCOB Leadership Summit). Attendees register for the conference, exhibitors book expo booths, sponsors reserve sponsorship tiers, speakers can apply, and hotel add-ons are sold separately. Everything is tied to an `events` record added late in the project's life — the core tables predate the `events` table and were originally single-conference.

---

### 1.3 Database Schema

#### `events`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | Event title |
| `slug` | string unique | URL identifier |
| `edition` | string | e.g. "2026" |
| `tagline` | string nullable | Short tagline |
| `description` | text nullable | Full description |
| `venue_name` | string nullable | Venue name |
| `venue_city` | string nullable | City |
| `venue_address` | string nullable | Street address |
| `start_date` | date | |
| `end_date` | date | |
| `date_label` | string nullable | Display string e.g. "May 23–25, 2026" |
| `featured_image` | string nullable | Image path |
| `status` | enum(upcoming, active, past) | Default: upcoming |
| `is_current` | boolean | Default: false |
| `attendees_count` | integer nullable | Denormalised display count |
| `speakers_count` | integer nullable | Denormalised display count |
| `exhibitors_count` | integer nullable | Denormalised display count |
| `highlights` | text nullable | Freeform JSON/text highlights |
| `sort_order` | integer | Default: 0 |

#### `ticket_types`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | e.g. "General Admission" |
| `slug` | string unique | |
| `price` | decimal(8,2) | Base price |
| `early_bird_price` | decimal(10,2) nullable | |
| `regular_price` | decimal(10,2) nullable | |
| `premium_price` | decimal(10,2) nullable | |
| `early_bird_ends` | date nullable | Cutoff for early bird |
| `regular_ends` | date nullable | Cutoff for regular pricing |
| `description` | text nullable | |
| `features` | json | Array of feature strings |
| `is_group` | boolean | Default: false |
| `min_group_size` | integer nullable | For group tickets |
| `capacity` | integer | Total available |
| `image` | string nullable | Optional ticket image |
| `is_active` | boolean | Default: true |
| `sort_order` | integer | Default: 0 |

> **Note:** `ticket_types` has no `event_id` — they are global to the site (one conference). Multi-event use would require adding an `event_id` FK.

#### `registrations`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `uuid` | char(36) unique | Public-facing reference |
| `ticket_type_id` | FK → ticket_types | |
| `first_name` | string | |
| `last_name` | string | |
| `email` | string | |
| `phone` | string | |
| `business_name` | string | |
| `website` | string(500) nullable | |
| `linkedin_url` | string(500) nullable | |
| `logo_path` | string(500) nullable | Company logo upload |
| `industry` | string | |
| `dietary_requirements` | string nullable | |
| `group_size` | integer nullable | |
| `ticket_count` | integer | Default: 1 |
| `quantity` | smallint | Default: 1 (tickets in basket) |
| `guest_details` | json nullable | Array of guest name/email objects |
| `hotel_reserved` | boolean | Default: false |
| `hotel_amount` | decimal(8,2) nullable | Hotel add-on amount charged |
| `country` | string(100) nullable | Attendee location |
| `state` | string(100) nullable | |
| `city` | string(100) nullable | |
| `amount_paid` | decimal(8,2) | Final amount charged |
| `coupon_code` | string(50) nullable | Applied coupon |
| `discount_amount` | decimal(8,2) | Default: 0 |
| `original_amount` | decimal(8,2) nullable | Pre-discount amount |
| `status` | enum(pending, confirmed, cancelled, waitlisted) | Default: pending |
| `payment_status` | enum(unpaid, paid, failed, refunded) | Default: unpaid |
| `transaction_id` | string nullable | Authorize.net transaction ID |
| `payment_method` | string nullable | e.g. "credit_card" |
| `card_last_four` | char(4) nullable | |
| `is_amcob_member` | boolean | Default: false |
| `amcob_member_id` | string nullable | |
| `confirmed_at` | timestamp nullable | |
| `confirmation_sent_at` | timestamp nullable | |
| `notes` | text nullable | Internal admin notes |
| `ip_address` | string nullable | |
| `user_agent` | text nullable | |
| `deleted_at` | timestamp nullable | Soft delete |

#### `expo_booth_types`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | e.g. "Standard Booth" |
| `slug` | string unique | |
| `price` | decimal(8,2) | |
| `size_description` | string | e.g. "10x10 ft" |
| `badge_count` | integer | Staff badges included |
| `days_access` | string | e.g. "Friday + Saturday" |
| `features` | json | Array of feature strings |
| `is_featured` | boolean | Default: false |
| `capacity` | integer nullable | Total booths available |
| `is_active` | boolean | Default: true |
| `sort_order` | integer | Default: 0 |

#### `expo_reservations`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `uuid` | char(36) unique | |
| `event_id` | FK → events nullable | Added later |
| `booth_type_id` | FK → expo_booth_types | |
| `business_name` | string | |
| `contact_first_name` | string | |
| `contact_last_name` | string | |
| `email` | string | |
| `phone` | string | |
| `showcase_description` | text | What they're exhibiting |
| `industry_category` | string | |
| `status` | enum(pending, confirmed, cancelled, waitlisted) | Default: pending |
| `payment_status` | enum(unpaid, paid, failed, refunded) | Default: unpaid |
| `amount_paid` | decimal(8,2) | |
| `coupon_code` | string nullable | |
| `discount_amount` | decimal(8,2) | Default: 0 |
| `original_amount` | decimal(8,2) nullable | |
| `transaction_id` | string nullable | |
| `card_last_four` | char(4) nullable | |
| `hotel_reserved` | boolean nullable | |
| `confirmed_at` | timestamp nullable | |
| `confirmation_sent_at` | timestamp nullable | |
| `notes` | text nullable | |
| `ip_address` | string nullable | |
| `deleted_at` | timestamp nullable | Soft delete |

#### `sponsorship_tiers`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | e.g. "Gold Sponsor" |
| `type` | enum(main, other) | Default: main |
| `price` | decimal(10,2) nullable | |
| `description` | text nullable | |
| `features` | json nullable | |
| `slots_available` | integer nullable | |
| `is_active` | boolean | Default: true |
| `sort_order` | integer | Default: 0 |

#### `sponsorship_reservations` (key columns)
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `uuid` | uuid unique | |
| `event_id` | FK → events nullable | |
| `sponsorship_tier_id` | FK → sponsorship_tiers | |
| `addon_tier_ids` | json nullable | Additional tiers selected |
| `first_name` | string(100) | |
| `last_name` | string(100) | |
| `email` | string(255) | |
| `phone` | string(25) | |
| `company_name` | string(200) | |
| `industry` | string(100) | |
| `total_amount` | decimal(10,2) | |
| `status` | enum(pending, confirmed, cancelled) | |
| `transaction_id` | string nullable | |
| `coupon_code` | string nullable | |
| `discount_amount` | decimal | |
| `ad_size` | string nullable | Magazine/programme ad size |
| `quantity` | integer nullable | Additional guest count |
| `website` | string nullable | |
| `linkedin_url` | string nullable | |
| `logo_path` | string nullable | |
| `company_description` | text nullable | |
| `show_on_website` | boolean | Default: false |
| `hotel_reserved` | boolean nullable | |

#### `coupons`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `code` | string(50) unique | |
| `description` | string nullable | |
| `type` | enum(percentage, fixed) | |
| `value` | decimal(8,2) | Discount amount/% |
| `min_amount` | decimal(8,2) nullable | Minimum order to qualify |
| `max_uses` | integer unsigned nullable | null = unlimited |
| `used_count` | integer unsigned | Default: 0 |
| `applicable_ticket_type_ids` | json nullable | Restrict to specific tickets |
| `applicable_booth_type_ids` | json nullable | Restrict to specific booth types |
| `valid_from` | timestamp nullable | |
| `valid_until` | timestamp nullable | |
| `timezone` | string(50) | Default: America/Chicago |
| `is_active` | boolean | Default: true |

#### `hotel_add_ons`
Polymorphic — can attach to either a `registration` or `expo_reservation`.
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `uuid` | uuid unique | |
| `reservable_type` | string(100) | Polymorphic type |
| `reservable_id` | bigint unsigned | Polymorphic ID |
| `email` | string | |
| `first_name` | string | |
| `last_name` | string | |
| `nights` | tinyint unsigned | Default: 2 |
| `amount` | decimal(10,2) | |
| `payment_method` | string | credit_card \| wire_transfer |
| `transaction_id` | string nullable | |
| `card_last_four` | char(4) nullable | |
| `wire_transfer_proof` | string nullable | Upload path |
| `status` | string | pending \| confirmed \| cancelled |
| `ip_address` | char(45) nullable | |
| `confirmed_at` | timestamp nullable | |

#### `speakers` + `event_speaker` pivot
- Speaker profile: name, title, organization, bio, avatar_initials, avatar_color, workshop_title, workshop_time, workshop_day (fri/sat/sun), workshop_type (keynote/workshop/panel/social), image, is_tba, is_active, sort_order
- Many-to-many with `events` via `event_speaker` (event_id, speaker_id)

#### `presenters`
Newer, richer speaker/presenter table (replacing speakers for future use):
name, title, organization, bio, bio_short, image_path, linkedin_url, presentation_title, presentation_type, is_tba, is_active, sort_order

#### `agenda_items`
Scheduled programme items: day (fri/sat/sun), start_time, end_time, title, description, speaker_id (nullable FK), type (keynote/workshop/panel/social/meal/break/ceremony), duration_minutes, image, is_active, sort_order

#### `exhibitors`
Display-only directory linked optionally to an expo_reservation: name, category, description, logo_initials, website, expo_reservation_id, image, is_confirmed, is_active, sort_order

---

### 1.4 Registration / Booking Flow

**Attendee tickets:**
1. Home page shows ticket types with pricing windows (early-bird → regular → premium)
2. `POST /register` → `RegistrationController@store`
3. Validates form, applies coupon if provided, calculates final amount
4. Charges Authorize.net; on success: creates `registration` (status=confirmed, payment_status=paid), sends confirmation email
5. Success page at `/register/success/{uuid}`

**Expo booths:**
1. `POST /expo/reserve` → `ExpoController@store`
2. Same payment flow; success at `/expo/success/{uuid}`

**Sponsorships:**
1. `POST /sponsorship/reserve` → `SponsorshipController@store`
2. No immediate card charge — creates pending reservation, admin follows up (wire transfer or offline payment)

**Hotel add-ons:**
1. Available to confirmed registrants and expo reservers
2. `POST /hotel/process` — polymorphic attachment; supports both credit card and wire transfer proof upload

**Speaker applications:**
1. `POST /speaker/apply` — creates speaker record with application fields (is_tba=true); admin reviews and activates

**Coupon validation:** `POST /coupon/validate` — AJAX endpoint returning discount amount

---

### 1.5 Status & Approval Workflows

| Entity | Status Values |
|---|---|
| Registrations | pending → confirmed / waitlisted / cancelled |
| Payment | unpaid → paid / failed / refunded |
| Expo reservations | Same as registrations |
| Sponsorship reservations | pending → confirmed / cancelled |
| Hotel add-ons | pending → confirmed / cancelled |
| Events | upcoming → active → past |

---

### 1.6 Category / Tag System
None. Ticket types and booth types serve as the only classification. No event tags.

---

### 1.7 Location Handling
In-person only. Location stored on the `events` table as `venue_name`, `venue_city`, `venue_address`. No online/hybrid support. Attendee location (country, state, city) stored on `registrations` for demographic reporting.

---

### 1.8 Notifications
Confirmation emails sent after successful payment for registrations and expo reservations. `confirmation_sent_at` timestamp tracked. No queued jobs apparent — sent synchronously in controller.

---

### 1.9 Admin Interface
Full admin panel (Laravel Blade, auth-protected) with management screens for: events, ticket types, registrations, expo booth types, expo reservations, sponsorship tiers, sponsorship reservations, hotel add-ons, speakers, presenters, agenda, exhibitors, coupons, site settings, blog, contact messages.

---

## 2. prism.amcob.org-new-1

### 2.1 Stack
Laravel 11 · PHP · MySQL · Authorize.net · User accounts (registration required)

### 2.2 What this site is
A membership/conference site. Unlike `lead`, users must create an account to register. A `payment` record IS the registration — there is no separate registrations table. Supports subscription plans (membership) alongside one-time event tickets. Events have a single flat price and can optionally be linked to a "dinner event" (sibling event).

---

### 2.3 Database Schema

#### `events`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `title` | string | |
| `slug` | string unique | |
| `description` | text nullable | |
| `price` | decimal(10,2) | Single flat price — no ticket tiers |
| `event_date` | datetime nullable | Start datetime |
| `event_start_time` | time nullable | Redundant time-only field |
| `event_end_date` | datetime nullable | End datetime |
| `event_end_time` | time nullable | Redundant time-only field |
| `refund_policy` | text nullable | |
| `gallery_link` | string(500) nullable | External gallery URL |
| `image` | string nullable | Cover image path |
| `venue` | string nullable | Venue name |
| `venue_description` | text nullable | |
| `city` | string nullable | |
| `state` | string nullable | |
| `country` | string | Default: US |
| `timezone` | string | Default: America/Chicago |
| `max_attendees` | integer nullable | Capacity |
| `is_active` | boolean | Default: true |
| `invitation_only` | boolean | Default: false |
| `registration_open` | boolean | Default: true |
| `linked_dinner_event_id` | bigint unsigned nullable | FK to another event |
| `metadata` | json nullable | Arbitrary extra fields |
| `deleted_at` | timestamp nullable | Soft delete |

#### `event_types`
Polymorphic taxonomy table. Events linked via `event_event_type` pivot.
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string unique | e.g. "Networking" |
| `slug` | string unique | |
| `description` | text nullable | |
| `icon` | string nullable | Icon class/name for frontend |
| `color` | char(7) | Hex color, Default: #3B82F6 |
| `is_active` | boolean | Default: true |
| `sort_order` | integer | Default: 0 |
| `deleted_at` | timestamp nullable | Soft delete |

#### `users` (extended)
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `parent_user_id` | FK → users nullable | Links guest users to primary registrant |
| `first_name` | string nullable | |
| `last_name` | string nullable | |
| `slug` | string unique nullable | |
| `email` | string | |
| `phone` | string nullable | |
| `street_address` | string nullable | |
| `country` | string nullable | |
| `state` | string nullable | |
| `city` | string nullable | |
| `zip_code` | string nullable | |
| `is_amcob_member` | boolean | Default: false |
| `status` | string | Default: active |
| `password` | string | |
| `deleted_at` | timestamp nullable | Soft delete |

#### `plans`
Membership/subscription plans (separate from event tickets):
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | |
| `price` | decimal(10,2) | |
| `plan_type` | enum(onetime, subscription) | |
| `duration_type` | enum(monthly, yearly) | |
| `duration_value` | integer | e.g. 1 (monthly) or 12 (yearly) |
| `description` | text nullable | |
| `features` | json nullable | |
| `is_active` | boolean | Default: true |

#### `payments` (also serves as the registration record)
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users | The registrant |
| `plan_id` | FK → plans nullable | If purchasing membership |
| `event_id` | FK → events nullable | If registering for event |
| `amount` | decimal(10,2) | Final amount charged |
| `quantity` | integer | Default: 1 (ticket count) |
| `currency` | string(10) | Default: USD |
| `transaction_id` | string nullable | Authorize.net transaction ID |
| `auth_code` | string nullable | |
| `payment_method` | string nullable | e.g. "credit_card" |
| `card_last4` | char(4) nullable | |
| `card_brand` | string nullable | |
| `billing_address` | text nullable | |
| `coupon_code` | string nullable | |
| `discount_amount` | decimal(10,2) | Default: 0 |
| `original_amount` | decimal(10,2) nullable | Pre-discount amount |
| `status` | string | completed / failed / refunded |

#### `coupons`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `code` | string unique | |
| `description` | string nullable | |
| `discount_type` | enum(percentage, fixed) | Default: percentage |
| `discount_value` | decimal(10,2) | |
| `event_ids` | json nullable | Restrict to specific event IDs; null = all |
| `usage_limit` | integer nullable | null = unlimited |
| `usage_count` | integer | Default: 0 |
| `per_user_limit` | integer nullable | null = unlimited per user |
| `timezone` | string | Default: UTC |
| `start_date` | datetime nullable | |
| `end_date` | datetime nullable | |
| `is_active` | boolean | Default: true |
| `deleted_at` | timestamp nullable | Soft delete |

#### `coupon_payment` pivot
Tracks which coupon was used on which payment, by whom: coupon_id, payment_id, user_id, discount_amount

#### `interest_form_submissions`
Pre-registration interest capture (name, email, event interest) for invite-only or not-yet-open events.

---

### 2.4 Registration / Booking Flow
1. Guest visits event page `/{event:slug}`
2. If no account → sign-up form (`POST /{event:slug}`) → `SignUpController@processSignup`
3. User account created; guest sub-users created with `parent_user_id` pointing to primary user
4. Payment charged via Authorize.net
5. `payment` record created (status = completed); this IS the registration record
6. Confirmation email sent
7. For members: separate flow via `/new/register` for plan-based purchasing

**Coupon flow:** `POST /validate-coupon` → AJAX validation; discount applied at checkout

**Interest flow:** `POST /interested/store` for capturing pre-registration interest before event opens

---

### 2.5 Status & Approval Workflows

| Entity | Values |
|---|---|
| Events | is_active (bool), invitation_only (bool), registration_open (bool) |
| Payments | completed / failed / refunded |
| Users | active / (soft deleted) |

No explicit waitlist or approval workflow — events rely on `max_attendees` capacity check.

---

### 2.6 Category / Tag System
`event_types` table with many-to-many pivot. Admin-manageable. Has icon and colour fields for frontend display. Equivalent to CRM's `tag` concept.

---

### 2.7 Location Handling
In-person and online implied but no explicit `event_type` enum. Location stored as `venue`, `venue_description`, `city`, `state`, `country`, `timezone` on the events table. `linked_dinner_event_id` allows pairing a networking event with a dinner sibling event.

---

### 2.8 Notifications
Confirmation emails triggered post-payment in `SignUpController`. No queue system apparent.

---

### 2.9 Admin Interface
Full Laravel resource admin at `/admin` (standard `auth` middleware) with screens for: dashboard, plans, payments, users, events, event-types, interest-submissions, coupons.

---

## 3. tours.amcob.org-new

### 3.1 Stack
Laravel 11 · PHP · MySQL · Authorize.net · No user accounts (guest checkout)

### 3.2 What this site is
A trade/business tour application site. Users apply (not buy tickets) for curated international tours. An application fee is charged upfront; approval is a manual admin process. Tours replace events as the core entity. Supports multiple attendees per application with guest details.

---

### 3.3 Database Schema

#### `tours`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `title` | string | |
| `slug` | string unique | |
| `tagline` | text nullable | Short hook line |
| `description` | text nullable | Full rich description |
| `region` | string nullable | e.g. "West Africa", "Southeast Asia" |
| `tour_type` | string nullable | e.g. "Trade Mission", "Study Tour" |
| `event_date` | date nullable | Departure date |
| `event_end_date` | date nullable | Return date |
| `days_count` | tinyint unsigned nullable | Trip duration in days |
| `dates_display` | string nullable | Human display string e.g. "Sept 12–22, 2026" |
| `status` | enum(open, interest, soon, closed) | Default: soon |
| `image` | string nullable | Hero image path |
| `is_active` | boolean | Default: true |
| `sort_order` | smallint | Default: 0 |
| `application_fee` | decimal(8,2) | Default: 300.00 — fee charged at application |
| `max_attendees` | smallint unsigned nullable | Total capacity across all applications |
| `criteria` | json nullable | Eligibility criteria array |
| `included` | json nullable | What's included in the tour array |
| `fee_note` | text nullable | Notes about fee/full-cost structure |
| `refund_policy` | text nullable | |
| `deleted_at` | timestamp nullable | Soft delete |

#### `tour_applications`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `tour_id` | FK → tours nullable | |
| `full_name` | string nullable | |
| `email` | string | |
| `phone` | string | |
| `phone_country` | char(2) nullable | Country code e.g. "US" |
| `company_role` | string nullable | Job title / role |
| `destination` | string nullable | Legacy field (pre-tours table) |
| `goals` | text nullable | Why they want to join |
| `linkedin` | string nullable | LinkedIn profile URL |
| `status` | enum(new, reviewed, approved, rejected) | Default: new — admin-driven |
| `payment_status` | enum(pending, paid, failed) | Default: pending |
| `attendees` | tinyint unsigned | Default: 1 — includes main applicant |
| `guests_json` | text nullable | JSON array of additional guest details |
| `billing_address` | string(255) nullable | |
| `billing_city` | string(100) nullable | |
| `billing_state` | string(100) nullable | |
| `billing_zip` | string(20) nullable | |
| `billing_country` | char(2) nullable | ISO 2-letter code |
| `admin_notes` | text nullable | Internal reviewer notes |

#### `tour_payments`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `tour_application_id` | FK → tour_applications nullable | |
| `tour_id` | FK → tours nullable | Denormalised for reporting |
| `transaction_id` | string nullable | Authorize.net transaction ID |
| `auth_code` | string(50) nullable | Authorize.net auth code |
| `coupon_code` | string nullable | Applied coupon code |
| `amount` | decimal(8,2) | Final amount charged |
| `original_amount` | decimal(10,2) nullable | Pre-discount amount |
| `discount_amount` | decimal(10,2) | Default: 0 |
| `currency` | string(10) | Default: USD |
| `card_last4` | char(4) nullable | |
| `card_brand` | string(50) nullable | |
| `status` | enum(success, failed, pending, refunded) | Default: pending |
| `response_message` | text nullable | Gateway response text |
| `billing_name` | string(255) nullable | |
| `billing_address` | string(255) nullable | |
| `billing_city` | string(100) nullable | |
| `billing_state` | string(100) nullable | |
| `billing_zip` | string(20) nullable | |
| `billing_country` | string(100) nullable | |

#### `coupons`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `code` | string unique | |
| `description` | string nullable | |
| `discount_type` | enum(percentage, fixed) | |
| `discount_value` | decimal(10,2) | |
| `discount_scope` | enum(total, main_applicant) | Default: total — whether discount applies to full group or just primary applicant's fee |
| `tour_ids` | json nullable | Restrict to specific tour IDs; null = all |
| `usage_limit` | integer unsigned nullable | |
| `usage_count` | integer unsigned | Default: 0 |
| `per_email_limit` | integer unsigned nullable | Limit per email address |
| `timezone` | string | Default: UTC |
| `start_date` | datetime nullable | |
| `end_date` | datetime nullable | |
| `is_active` | boolean | Default: true |
| `deleted_at` | timestamp nullable | Soft delete |

#### `coupon_tour_application` pivot
Links coupons to the applications they were used on.

#### `admins`
Separate admin user table (not Laravel's `users`). Simple email+password auth with a custom `admin` middleware.

#### `expedition_photos`
Gallery for past tour photo collections: tour reference, image path, caption, sort order.

#### `testimonials`
Testimonials from past tour participants: name, role, content, photo, is_active, sort_order.

#### `contact_messages`
General contact form submissions: name, email, subject, message, timestamps.

---

### 3.4 Registration / Booking Flow
1. User views tour listing (homepage) or tour detail `/{slug}`
2. Clicks "Apply Now" → `/apply-now` page (or inline form)
3. Fills application form: full_name, email, phone, company_role, goals, linkedin, attendees count, guest details (JSON), billing address
4. Optional coupon code validated via `POST /validate-coupon`
5. `POST /apply` → `HomeController@applyTour`
6. Charges `application_fee × attendees` (subject to coupon scope) via Authorize.net
7. `tour_payment` record created; on success, `tour_application` created with payment_status=paid
8. Application status remains `new` — admin reviews and moves to approved/rejected
9. Success redirect to `/thank-you`

> **Key difference from events:** Payment is an application fee only — it does not confirm a seat. Admin approval is required. Full tour cost is handled offline after approval.

---

### 3.5 Status & Approval Workflows

| Entity | Values | Notes |
|---|---|---|
| Tours | open / interest / soon / closed | Controls what actions are shown on frontend |
| Applications | new → reviewed → approved / rejected | Admin-driven progression |
| Payment | pending → paid / failed; (refunded) | Automatic on gateway response |

---

### 3.6 Category / Tag System
None formally. `region` (string) and `tour_type` (string) on `tours` serve as loose classification but are not normalised tables.

---

### 3.7 Location Handling
International destinations. Location is captured as `region` (continent/area) and `tour_type` on the tour itself. No venue address — tours are travel products not fixed venues. Billing address collected on the application for payment processing.

---

### 3.8 Notifications
No evidence of automated email beyond what Authorize.net provides. Thank-you page confirms submission. Admin reviews manually. No queue system.

---

### 3.9 Admin Interface
Custom admin panel at `/admin` (custom `admin` middleware, separate `admins` table). Full CRUD for: tours, applications (with status update), payments (view + edit), contact messages, testimonials, expedition photos, coupons.

---

## Summary Comparison

| Feature | lead.amcob.org-new | prism.amcob.org-new-1 | tours.amcob.org-new |
|---|---|---|---|
| Core entity | Conference/Expo | Event | Tour |
| Ticket tiers | ✅ Multi-tier + pricing windows | ❌ Single flat price | ❌ Application fee only |
| User accounts required | ❌ Guest checkout | ✅ Required | ❌ Guest checkout |
| Registration record | `registrations` table | `payments` table (is the registration) | `tour_applications` table |
| Payment table | Embedded in registration | `payments` table | `tour_payments` table |
| Approval workflow | Auto-confirm on payment | Auto-confirm on payment | Manual admin approval |
| Expo/booths | ✅ Full booth system | ❌ | ❌ |
| Sponsorships | ✅ Tier-based | ❌ | ❌ |
| Hotel add-ons | ✅ Polymorphic add-on | ❌ | ❌ |
| Speakers/Agenda | ✅ Full programme | ❌ | ❌ |
| Categories/Tags | ❌ None | ✅ `event_types` table | ❌ region/type strings only |
| Location type | In-person only | In-person (+ online implied) | International travel |
| Multi-attendee | ✅ Guest details JSON | ✅ parent_user_id guests | ✅ guests_json + attendees count |
| Coupon scope | Per ticket type or booth type | Per event | Per tour, with applicant-vs-total scope |
| Waitlist | ✅ status=waitlisted | ❌ | ❌ |
| Soft deletes | registrations, expo_reservations | events, coupons, payments | tours, coupons |
| Admin auth | App `users` table | App `users` table | Separate `admins` table |

---

## CRM Migration Notes

When building equivalent functionality in the ERP CRM backend (`erp.amcob.org` Events module), the following fields/concepts from these three sites are **not currently in the API** and would need to be added or mapped:

| Feature | Source | CRM Equivalent Needed |
|---|---|---|
| Ticket pricing windows (early-bird / regular / premium) | lead | `tickets[].price` only; need `early_bird_price`, `regular_price`, `premium_price`, `early_bird_ends`, `regular_ends` |
| Expo booth reservations | lead | New entity: `booths` + `booth_reservations` |
| Sponsorship tiers | lead | New entity: `sponsorship_tiers` + `sponsorship_reservations` |
| Hotel add-ons | lead | New entity: `hotel_addons` (polymorphic) |
| Speaker/presenter management | lead | New entity: `speakers` with event pivot |
| Agenda/programme | lead | New entity: `agenda_items` |
| AMCOB member flag + member ID | lead | Add `is_amcob_member`, `amcob_member_id` to registrations |
| Guest details on registration | lead, tours | Add `guests_json`, `attendees_count` to registrations |
| Registration location (country/state/city) | lead | Add to registrations |
| Event type taxonomy | prism | `tags[]` partially covers this; `event_types` with icon/color needs separate support |
| Linked sibling events | prism | `linked_dinner_event_id` — no CRM equivalent |
| Interest/waitlist capture | prism | New entity: `interest_submissions` |
| Tour/travel product | tours | New entity type distinct from events: `tours` |
| Application fee + manual approval | tours | Registrations support `require_registration_approval` already; fee structure needs `application_fee` field |
| Tour coupon scope (total vs main applicant) | tours | New field on coupons: `discount_scope` |
| Region / tour type classification | tours | Could map to `tags` or new `tour_type` field |
