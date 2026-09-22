<?php

namespace App\Support\EventsDiscovery;

/**
 * Builds the homepage discovery payload from live ERP API data only.
 */
final class DiscoveryDataBuilder
{
    /**
     * @param  array<int, array<string, mixed>>  $upcoming
     * @param  array<int, array<string, mixed>>  $current
     * @param  array<int, array<string, mixed>>  $past
     * @param  array<int, array<string, mixed>>  $filterCategories
     * @param  array{countries?: mixed, states?: mixed, cities?: mixed}  $locations
     * @param  array{upcoming?: int, live?: int, past?: int}|null  $counts
     * @return array{
     *   events: array<int, array<string, mixed>>,
     *   categories: array<int, array{name: string, color: string}>,
     *   locations: array{cities: array<int, string>, states: array<int, string>, countries: array<int, string>},
     *   counts: array{all: int, upcoming: int, live: int, past: int},
     *   source: string
     * }
     */
    public static function build(
        array $upcoming,
        array $current,
        array $past,
        array $filterCategories = [],
        array $locations = [],
        ?array $counts = null,
    ): array {
        // Trust API lifecycle flags — do not force bucket labels (a live event can
        // appear in upcoming payloads with is_current/lifecycle=live).
        $normalized = array_merge(
            DiscoveryEventNormalizer::many($current),
            DiscoveryEventNormalizer::many($upcoming),
            DiscoveryEventNormalizer::many($past),
        );

        $rank = ['live' => 3, 'upcoming' => 2, 'past' => 1];
        $byKey = [];
        foreach ($normalized as $event) {
            $key = ($event['id'] ?: 0) . ':' . ($event['slug'] ?: '');
            if ($key === '0:') {
                continue;
            }
            if (!isset($byKey[$key])) {
                $byKey[$key] = $event;
                continue;
            }
            $prev = $byKey[$key];
            $prevScore = $rank[$prev['status'] ?? ''] ?? 0;
            $nextScore = $rank[$event['status'] ?? ''] ?? 0;
            if ($nextScore > $prevScore) {
                $byKey[$key] = $event;
            }
        }
        $events = array_values($byKey);
        $events = self::sortForDiscovery($events);

        // Counts: prefer ERP meta.counts so badges match total available (not just this page).
        $localCounts = [
            'upcoming' => count(array_filter($events, fn ($e) => ($e['status'] ?? '') === 'upcoming')),
            'live' => count(array_filter($events, fn ($e) => ($e['status'] ?? '') === 'live')),
            'past' => count(array_filter($events, fn ($e) => ($e['status'] ?? '') === 'past')),
        ];

        $apiCounts = PaginationMeta::normalizeCounts(
            is_array($counts) ? $counts : null,
            $localCounts
        ) ?? [];
        $finalCounts = [
            'upcoming' => (int) ($apiCounts['upcoming'] ?? $localCounts['upcoming']),
            'live' => (int) ($apiCounts['live'] ?? $localCounts['live']),
            'past' => (int) ($apiCounts['past'] ?? $localCounts['past']),
        ];
        $finalCounts['all'] = (int) ($apiCounts['all'] ?? ($finalCounts['upcoming'] + $finalCounts['live'] + $finalCounts['past']));

        return [
            'events' => $events,
            'categories' => self::categoriesFrom($events, $filterCategories),
            'locations' => self::locationOptions($events, $locations),
            'counts' => $finalCounts,
            'source' => 'api',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @param  array<int, array<string, mixed>>  $filterCategories
     * @return array<int, array{name: string, color: string}>
     */
    protected static function categoriesFrom(array $events, array $filterCategories): array
    {
        $map = [];

        foreach ($filterCategories as $cat) {
            $name = is_array($cat)
                ? ($cat['name'] ?? $cat['label'] ?? $cat['value'] ?? null)
                : (is_string($cat) ? $cat : null);
            if (!$name) {
                continue;
            }
            $color = is_array($cat) ? ($cat['color'] ?? null) : null;
            $map[$name] = [
                'name' => $name,
                'color' => DiscoverySanitize::hexColor(
                    $color ?: (DiscoveryEventNormalizer::CATEGORY_COLORS[$name] ?? null)
                ),
            ];
        }

        foreach ($events as $e) {
            $name = $e['cat'] ?? null;
            if (!$name) {
                continue;
            }
            if (isset($map[$name])) {
                if (empty($map[$name]['color']) || $map[$name]['color'] === '#2D3C69') {
                    $map[$name]['color'] = DiscoverySanitize::hexColor(
                        $e['color'] ?? (DiscoveryEventNormalizer::CATEGORY_COLORS[$name] ?? null)
                    );
                }
                continue;
            }
            $map[$name] = [
                'name' => $name,
                'color' => DiscoverySanitize::hexColor(
                    $e['color'] ?? (DiscoveryEventNormalizer::CATEGORY_COLORS[$name] ?? null)
                ),
            ];
        }

        return array_values($map);
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @param  array{countries?: mixed, states?: mixed, cities?: mixed}  $locations
     * @return array{cities: array<int, string>, states: array<int, string>, countries: array<int, string>}
     */
    protected static function locationOptions(array $events, array $locations): array
    {
        $pick = function ($list): array {
            $out = [];
            foreach (is_array($list) ? $list : [] as $row) {
                if (is_string($row) && $row !== '') {
                    $out[] = $row;
                } elseif (is_array($row)) {
                    $v = $row['label'] ?? $row['value'] ?? $row['name'] ?? null;
                    if (is_string($v) && $v !== '') {
                        $out[] = $v;
                    }
                }
            }

            return array_values(array_unique($out));
        };

        $cities = $pick($locations['cities'] ?? []);
        $states = $pick($locations['states'] ?? []);
        $countries = $pick($locations['countries'] ?? []);

        foreach ($events as $e) {
            if (!empty($e['city']) && $e['city'] !== 'Online' && $e['city'] !== 'TBD') {
                $cities[] = $e['city'];
            }
            if (!empty($e['state'])) {
                $states[] = $e['state'];
            }
            if (!empty($e['country'])) {
                $countries[] = $e['country'];
            }
        }

        $cities = array_values(array_unique(array_filter($cities)));
        $states = array_values(array_unique(array_filter($states)));
        $countries = array_values(array_unique(array_filter($countries)));
        sort($cities);
        sort($states);
        sort($countries);

        return compact('cities', 'states', 'countries');
    }

    /**
     * Featured → live → upcoming → past (date asc within active groups, desc for past).
     *
     * @param  array<int, array<string, mixed>>  $events
     * @return array<int, array<string, mixed>>
     */
    public static function sortForDiscovery(array $events): array
    {
        usort($events, function (array $a, array $b): int {
            $rankA = self::discoveryRank($a);
            $rankB = self::discoveryRank($b);
            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            $dateA = (string) ($a['date'] ?? $a['starts_at'] ?? '');
            $dateB = (string) ($b['date'] ?? $b['starts_at'] ?? '');
            $status = (string) ($a['status'] ?? '');
            if ($status === 'past') {
                return $dateB <=> $dateA;
            }

            return $dateA <=> $dateB;
        });

        return $events;
    }

    /**
     * @param  array<string, mixed>  $event
     */
    protected static function discoveryRank(array $event): int
    {
        $status = (string) ($event['status'] ?? '');
        if (!empty($event['featured']) && $status !== 'past') {
            return 0;
        }

        return match ($status) {
            'live' => 1,
            'upcoming' => 2,
            default => 3,
        };
    }
}
