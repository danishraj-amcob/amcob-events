<?php

namespace App\Support\EventsDiscovery;

/**
 * Shared pagination helpers for discovery list responses.
 */
final class PaginationMeta
{
    /**
     * @param  array<string, mixed>|null  $meta
     * @return array{page: int, last_page: int, has_more: bool, total: int|null}
     */
    public static function from(?array $meta, int $pageCount = 0, int $perPage = 12, int $fallbackPage = 1): array
    {
        $current = (int) ($meta['current_page'] ?? $fallbackPage);
        $last = max(1, (int) ($meta['last_page'] ?? 1));
        $total = isset($meta['total']) ? (int) $meta['total'] : null;

        // Prefer API pagination when present. Short pages are only a soft signal
        // when meta is missing (normalizer can drop rows and shrink pageCount).
        $hasApiPaging = is_array($meta) && (isset($meta['last_page']) || isset($meta['current_page']));
        $hasMore = $hasApiPaging
            ? ($current < $last)
            : ($pageCount >= $perPage);

        if ($total !== null) {
            $loadedThrough = max(0, ($current - 1) * $perPage) + max($pageCount, 0);
            if ($loadedThrough >= $total) {
                $hasMore = false;
            }
        }

        // Empty page with known last_page → definitely done.
        if ($pageCount === 0 && $current >= $last) {
            $hasMore = false;
        }

        return [
            'page' => $current,
            'last_page' => $last,
            'has_more' => $hasMore,
            'total' => $total,
        ];
    }

    /**
     * Normalize ERP meta.counts (maps current → live, adds all).
     * Corrects common ERP quirk where live events are also counted in upcoming.
     *
     * @param  array<string, mixed>|null  $counts
     * @param  array{upcoming?: int, live?: int, past?: int}|null  $local
     * @return array{all: int, upcoming: int, live: int, past: int}|null
     */
    public static function normalizeCounts(?array $counts, ?array $local = null): ?array
    {
        if (!is_array($counts)) {
            return null;
        }

        $upcoming = (int) ($counts['upcoming'] ?? 0);
        $live = (int) ($counts['live'] ?? $counts['current'] ?? 0);
        $past = (int) ($counts['past'] ?? 0);

        $localUpcoming = (int) ($local['upcoming'] ?? 0);
        $localLive = (int) ($local['live'] ?? 0);

        // If upcoming == localUpcoming + live, ERP double-counted live into upcoming.
        if ($live > 0 && $upcoming === ($localUpcoming + $live)) {
            $upcoming = $localUpcoming;
        } elseif ($live > 0 && $localUpcoming >= 0 && $upcoming === $localUpcoming + $localLive && $localLive === $live) {
            $upcoming = $localUpcoming;
        }

        $all = isset($counts['all'])
            ? (int) $counts['all']
            : ($upcoming + $live + $past);

        // Keep all consistent after upcoming correction when API all included the double-count.
        if (!isset($counts['all'])) {
            $all = $upcoming + $live + $past;
        } elseif ($all === ((int) ($counts['upcoming'] ?? 0) + $live + $past) && $upcoming !== (int) ($counts['upcoming'] ?? 0)) {
            $all = $upcoming + $live + $past;
        }

        return compact('all', 'upcoming', 'live', 'past');
    }
}
