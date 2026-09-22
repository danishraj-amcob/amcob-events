# Discovery module contract (schema v1)

Portable Events Discovery package used by AMCOB sites (Agenda / Grid / Calendar / List).

## Copy set

| Path | Role |
|------|------|
| `app/Support/EventsDiscovery/*` | PHP: homepage, feed, normalize, sanitize, catalog interface |
| `public/assets/js/discovery/*` | ES modules (entry: `app.js`) |
| `public/assets/css/events-discovery.css` | Self-contained tokens under `#events-discovery` |
| `resources/views/home/discovery/_hero.blade.php` | Hero markup |
| `resources/views/home/discovery/_explorer.blade.php` | Explorer markup |

## Host wiring

1. Bind `EventsCatalogClient` → your ERP HTTP service (`listEvents`, `filters`).
2. Call `DiscoveryHomepage::build($filters, $options)` with:

```php
$options = [
  'feed_url' => route('events.discovery'), // required — do not rely on package routes
  'config' => [
    'default_view' => 'agenda',       // agenda|grid|calendar|list
    'default_tab' => 'all',           // all|upcoming|live|past
    'detail_url_template' => '/{slug}',
    'features' => ['hero' => true, 'load_more' => true],
  ],
];
```

3. Expose JSON feed at `feed_url` via `DiscoveryHomepage::feed($query)`.
4. Render `#events-discovery`, set `window.AMCOB_DISCOVERY` from the build payload, load `discovery/app.js` as `type="module"`.

## Payload (`schema_version: 1`)

Required keys: `schema_version`, `events`, `categories`, `locations`, `counts`, `paging`, `feed_url`, `config`, `per_page`.  
Optional: `featured`, `filters`, `today`, `source`.

Bump `SCHEMA_VERSION` (PHP + JS) when breaking the payload shape. Bump `CACHE_VERSION` when normalize/fetch params change.

## CSS

Override brand colors on the host:

```css
#events-discovery {
  --navy: …;
  --gold: …;
}
```
