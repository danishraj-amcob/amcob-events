<?php

namespace App\Support\EventsDiscovery;

/**
 * Canonical discovery-card shape used by the homepage explorer UI.
 * Maps live ERP list items into a stable, view-friendly DTO.
 */
final class DiscoveryEventNormalizer
{
    /** @var array<string, string> Fallback colours when API omits tags[].color */
    public const CATEGORY_COLORS = [
        'Dinner Mixer'  => '#CE8924',
        'Dinner Mixers' => '#CE8924',
        'Networking'    => '#3a4b82',
        'Workshop'      => '#1E9E6A',
        'Gala'          => '#7C55C6',
        'Webinar'       => '#2AA6C4',
        'Conference'    => '#DC4C4C',
    ];

    /**
     * @param  array<string, mixed>  $raw  API list item
     * @return array<string, mixed>|null
     */
    public static function fromApi(array $raw, ?string $forcedLifecycle = null): ?array
    {
        if (empty($raw['slug']) && empty($raw['id'])) {
            return null;
        }

        $startsAt = $raw['starts_at'] ?? null;
        if (!$startsAt) {
            return null;
        }

        try {
            $tz = $raw['timezone'] ?? 'UTC';
            $start = \Illuminate\Support\Carbon::parse($startsAt)->timezone($tz);
        } catch (\Throwable) {
            return null;
        }

        $location = is_array($raw['location'] ?? null) ? $raw['location'] : [];
        $cover = is_array($raw['cover'] ?? null) ? $raw['cover'] : [];
        $tags = is_array($raw['tags'] ?? null) ? $raw['tags'] : (
            is_array($raw['categories'] ?? null) ? $raw['categories'] : []
        );

        $category = 'Event';
        $categoryColor = null;
        foreach ($tags as $tag) {
            if (is_array($tag) && !empty($tag['name'])) {
                $category = (string) $tag['name'];
                if (!empty($tag['color'])) {
                    $categoryColor = (string) $tag['color'];
                }
                break;
            }
            if (is_string($tag) && $tag !== '') {
                $category = $tag;
                break;
            }
        }

        $eventType = (string) ($raw['event_type'] ?? '');
        $format = match (true) {
            !empty($raw['is_online']) || str_contains($eventType, 'online') => 'online',
            str_contains($eventType, 'hybrid') => 'hybrid',
            default => 'in-person',
        };

        $lifecycle = $forcedLifecycle ?? self::resolveLifecycle($raw, $start);

        $city = $location['venue_city'] ?? $location['city'] ?? null;
        if (!$city && !empty($raw['location_label'])) {
            $city = explode(',', (string) $raw['location_label'])[0] ?? null;
            $city = $city ? trim($city) : null;
        }

        $priceLabel = $raw['price_label'] ?? null;
        if ($priceLabel === null && isset($raw['min_price'])) {
            $priceLabel = ((float) $raw['min_price'] <= 0)
                ? 'Free'
                : 'From $' . number_format((float) $raw['min_price'], 0);
        }

        $slug = (string) ($raw['slug'] ?? '');
        $url = $slug !== '' ? route('events.show', $slug) : '#';

        return [
            'id' => (int) ($raw['id'] ?? 0),
            'slug' => $slug,
            'title' => (string) ($raw['title'] ?? 'Event'),
            'date' => $start->format('Y-m-d'),
            'time' => $start->format('g:i A'),
            'starts_at' => $start->toIso8601String(),
            'ends_at' => $raw['ends_at'] ?? null,
            'timezone' => $raw['timezone'] ?? 'UTC',
            'cat' => $category,
            'color' => DiscoverySanitize::hexColor(
                $categoryColor ?? (self::CATEGORY_COLORS[$category] ?? null),
                self::CATEGORY_COLORS[$category] ?? '#2D3C69'
            ),
            'city' => $city ?: 'TBD',
            'state' => (string) ($location['venue_state'] ?? $location['state'] ?? ''),
            'country' => (string) ($location['venue_country'] ?? $location['country'] ?? ''),
            'loc' => (string) ($raw['location_label']
                ?? $location['label']
                ?? $location['full_address']
                ?? 'Location TBA'),
            'format' => $format,
            'price' => (string) ($priceLabel ?? ''),
            'pv' => self::priceValue(is_string($priceLabel) ? $priceLabel : null),
            'img' => (!empty($cover['url']) && is_string($cover['url'])) ? $cover['url'] : null,
            'gradient' => $cover['gradient']
                ?? (is_array($raw['theme'] ?? null) ? ($raw['theme']['gradient'] ?? null) : null)
                ?? 'linear-gradient(135deg, #fb923c 0%, #ea580c 100%)',
            'att' => [],
            'featured' => !empty($raw['featured']) || !empty($raw['is_featured']),
            'status' => $lifecycle,
            'when' => $raw['live_label'] ?? null,
            'attended' => self::attendanceCount($raw, $lifecycle),
            'photos' => isset($raw['photos_count']) ? (string) $raw['photos_count'] : null,
            'url' => $url,
            // null when API omits counts — UI must not invent "0 attended"
            'registration_count' => array_key_exists('registration_count', $raw)
                ? (int) $raw['registration_count']
                : (array_key_exists('attended_count', $raw) ? (int) $raw['attended_count'] : null),
        ];
    }

    /**
     * Positive attendance/registration count for past events, or null when unknown.
     *
     * @param  array<string, mixed>  $raw
     */
    protected static function attendanceCount(array $raw, string $lifecycle): ?string
    {
        if (array_key_exists('attended_count', $raw) && $raw['attended_count'] !== null && $raw['attended_count'] !== '') {
            $n = (int) $raw['attended_count'];

            return $n > 0 ? (string) $n : null;
        }

        if ($lifecycle === 'past'
            && array_key_exists('registration_count', $raw)
            && $raw['registration_count'] !== null
            && $raw['registration_count'] !== ''
        ) {
            $n = (int) $raw['registration_count'];

            return $n > 0 ? (string) $n : null;
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function many(array $items, ?string $forcedLifecycle = null): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $n = self::fromApi($item, $forcedLifecycle);
            if ($n) {
                $out[] = $n;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    protected static function resolveLifecycle(array $raw, \Illuminate\Support\Carbon $start): string
    {
        $lifecycle = $raw['lifecycle'] ?? null;
        if (is_string($lifecycle) && in_array($lifecycle, ['upcoming', 'live', 'past'], true)) {
            return $lifecycle;
        }

        if (!empty($raw['is_current'])) {
            return 'live';
        }
        if (!empty($raw['is_past'])) {
            return 'past';
        }

        $now = now($raw['timezone'] ?? 'UTC');
        if ($start->greaterThan($now)) {
            return 'upcoming';
        }

        try {
            $end = !empty($raw['ends_at'])
                ? \Illuminate\Support\Carbon::parse($raw['ends_at'])->timezone($raw['timezone'] ?? 'UTC')
                : $start->copy()->addHours(3);
        } catch (\Throwable) {
            $end = $start->copy()->addHours(3);
        }

        if ($now->between($start, $end)) {
            return 'live';
        }

        return 'past';
    }

    protected static function priceValue(?string $label): float
    {
        if ($label === null || $label === '' || stripos($label, 'free') !== false) {
            return 0;
        }
        if (preg_match('/(\d+(?:\.\d+)?)/', $label, $m)) {
            return (float) $m[1];
        }

        return 0;
    }
}
