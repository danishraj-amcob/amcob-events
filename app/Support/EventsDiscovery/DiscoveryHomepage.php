<?php

namespace App\Support\EventsDiscovery;

use Illuminate\Support\Facades\Cache;

/**
 * Homepage discovery orchestrator.
 *
 * Responsibilities:
 * - Fetch ERP buckets (upcoming / live / past / featured)
 * - Build normalized discovery payload for SSR
 * - Serve JSON feed pages for client Load more
 *
 * Host apps inject EventsCatalogClient + pass feed_url / config via $options
 * so this class stays portable across sites.
 */
final class DiscoveryHomepage
{
    public const PER_PAGE = 12;

    public const SCHEMA_VERSION = 1;

    public const CACHE_VERSION = 'v7';

    public function __construct(protected EventsCatalogClient $events) {}

    /**
     * @param  array{
     *   tag?: string|null,
     *   q?: string|null,
     *   direction?: string,
     *   city?: string|null,
     *   state?: string|null,
     *   country?: string|null,
     *   platform?: string|null,
     *   page?: int
     * }  $filters
     * @param  array{
     *   feed_url?: string,
     *   config?: array<string, mixed>
     * }  $options
     * @return array<string, mixed>
     */
    public function build(array $filters = [], array $options = []): array
    {
        $directionRaw = $filters['direction'] ?? 'asc';
        $direction = in_array($directionRaw, ['asc', 'desc'], true) ? $directionRaw : 'asc';
        $sharedFilters = $this->sharedListFilters($filters);

        $upcomingParams = array_filter([
            'upcoming' => 1,
            'sort' => 'starts_at',
            'direction' => $direction,
            'page' => 1,
            'per_page' => self::PER_PAGE,
            ...$sharedFilters,
        ]);

        $upcomingResult = $this->remember('upcoming', $upcomingParams, 300, fn () => $this->events->listEvents($upcomingParams));
        $upcoming = $this->rows($upcomingResult);

        $filtersPayload = Cache::remember(
            'amcob:events:filters:' . self::CACHE_VERSION,
            3600,
            fn () => $this->events->filters()
        );
        $locations = [
            'countries' => $filtersPayload['countries'] ?? [],
            'states' => $filtersPayload['states'] ?? [],
            'cities' => $filtersPayload['cities'] ?? [],
        ];
        $categories = $filtersPayload['categories'] ?? [];

        $pastParams = array_filter([
            'past' => 1,
            'sort' => 'starts_at',
            'direction' => 'desc',
            'per_page' => self::PER_PAGE,
            'page' => 1,
            ...$sharedFilters,
        ]);
        $pastResult = $this->remember('past', $pastParams, 300, fn () => $this->events->listEvents($pastParams));
        $pastEvents = $this->rows($pastResult);

        $currentParams = array_filter([
            'current' => 1,
            'sort' => 'starts_at',
            'direction' => 'asc',
            'per_page' => 50,
            ...$sharedFilters,
        ]);
        $currentResult = $this->remember('current', $currentParams, 60, fn () => $this->events->listEvents($currentParams));
        $currentEvents = $this->rows($currentResult);

        $featuredParams = [
            'featured' => 1,
            'sort' => 'starts_at',
            'direction' => 'asc',
            'per_page' => 8,
        ];
        $featuredResult = $this->remember('featured', $featuredParams, 300, fn () => $this->events->listEvents($featuredParams));
        $featuredPool = $this->rows($featuredResult);

        if (!$featuredPool) {
            $featuredPool = collect($upcoming)
                ->filter(fn ($e) => is_array($e) && (!empty($e['is_featured']) || !empty($e['featured'])))
                ->values()
                ->all();
        }
        if (!$featuredPool) {
            $featuredPool = array_slice($upcoming, 0, 5);
        }

        // Do NOT merge off-page featured into the catalog (keeps paging totals honest).
        // Only stamp featured flags onto events already in the discovery pool.
        $apiCounts = null;
        foreach ([$pastResult, $upcomingResult, $currentResult] as $bucket) {
            $raw = is_array($bucket['meta']['counts'] ?? null) ? $bucket['meta']['counts'] : null;
            if ($raw) {
                $apiCounts = $raw;
                break;
            }
        }

        $discovery = DiscoveryDataBuilder::build(
            $upcoming,
            $currentEvents,
            $pastEvents,
            is_array($categories) ? $categories : [],
            $locations,
            $apiCounts,
        );

        $featuredIds = collect($featuredPool)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
        if ($featuredIds) {
            $discovery['events'] = array_map(function ($e) use ($featuredIds) {
                if (in_array((int) ($e['id'] ?? 0), $featuredIds, true)) {
                    $e['featured'] = true;
                }

                return $e;
            }, $discovery['events']);
        }

        // Hero may need featured rows not on page 1 — expose separately (normalized).
        $discovery['featured'] = DiscoveryEventNormalizer::many($featuredPool);

        $upcomingPaging = PaginationMeta::from(
            is_array($upcomingResult['meta'] ?? null) ? $upcomingResult['meta'] : null,
            count($upcoming),
            self::PER_PAGE
        );
        $pastPaging = PaginationMeta::from(
            is_array($pastResult['meta'] ?? null) ? $pastResult['meta'] : null,
            count($pastEvents),
            self::PER_PAGE
        );

        $allLoaded = count($discovery['events'] ?? []);
        $allTotal = (int) ($discovery['counts']['all'] ?? 0);
        $allHasMore = (!empty($upcomingPaging['has_more']) || !empty($pastPaging['has_more']))
            && ($allTotal <= 0 || $allLoaded < $allTotal);

        $discovery['paging'] = [
            'all' => [
                'page' => 1,
                'has_more' => $allHasMore,
                'upcoming_page' => (int) ($upcomingPaging['page'] ?? 1),
                'past_page' => (int) ($pastPaging['page'] ?? 1),
                'total' => $allTotal ?: null,
            ],
            'upcoming' => $upcomingPaging,
            'live' => [
                'page' => 1,
                'last_page' => 1,
                'has_more' => false,
                'total' => (int) ($discovery['counts']['live'] ?? 0),
            ],
            'past' => $pastPaging,
        ];
        $discovery['feed_url'] = is_string($options['feed_url'] ?? null) ? (string) $options['feed_url'] : '';
        $discovery['per_page'] = self::PER_PAGE;
        $discovery['schema_version'] = self::SCHEMA_VERSION;
        $discovery['config'] = self::normalizeHostConfig(is_array($options['config'] ?? null) ? $options['config'] : []);
        $discovery['filters'] = [
            'tag' => $filters['tag'] ?? null,
            'city' => $filters['city'] ?? null,
            'state' => $filters['state'] ?? null,
            'country' => $filters['country'] ?? null,
            'q' => $filters['q'] ?? null,
        ];

        return $discovery;
    }

    /**
     * Host-overridable UI settings (safe defaults for this events site).
     *
     * @param  array<string, mixed>  $config
     * @return array{
     *   default_view: string,
     *   default_tab: string,
     *   detail_url_template: string,
     *   features: array{hero: bool, load_more: bool}
     * }
     */
    public static function normalizeHostConfig(array $config): array
    {
        $view = $config['default_view'] ?? 'calendar';
        $tab = $config['default_tab'] ?? 'all';
        $template = $config['detail_url_template'] ?? '/{slug}';
        $features = is_array($config['features'] ?? null) ? $config['features'] : [];

        return [
            // Agenda UI temporarily disabled — treat leftover agenda default as calendar.
            'default_view' => in_array($view, ['grid', 'calendar', 'list'], true)
                ? $view
                : 'calendar',
            'default_tab' => in_array($tab, ['all', 'upcoming', 'live', 'past'], true) ? $tab : 'all',
            'detail_url_template' => is_string($template) && $template !== '' ? $template : '/{slug}',
            'features' => [
                'hero' => ($features['hero'] ?? true) !== false,
                'load_more' => ($features['load_more'] ?? true) !== false,
            ],
        ];
    }

    /**
     * @param  array{
     *   tab?: string,
     *   page?: int,
     *   per_page?: int,
     *   tag?: string|null,
     *   city?: string|null,
     *   state?: string|null,
     *   country?: string|null,
     *   q?: string|null
     * }  $query
     * @return array<string, mixed>
     */
    public function feed(array $query = []): array
    {
        $tab = in_array($query['tab'] ?? 'all', ['all', 'upcoming', 'live', 'past'], true)
            ? $query['tab']
            : 'all';
        $page = max((int) ($query['page'] ?? 1), 1);
        $perPage = min(max((int) ($query['per_page'] ?? self::PER_PAGE), 1), 50);

        $params = array_filter([
            'sort' => 'starts_at',
            'direction' => $tab === 'past' ? 'desc' : 'asc',
            'per_page' => $perPage,
            'page' => $page,
            'tag' => $query['tag'] ?? null,
            'category' => $query['tag'] ?? null,
            'city' => $query['city'] ?? null,
            'state' => $query['state'] ?? null,
            'country' => $query['country'] ?? null,
            'q' => $query['q'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        if ($tab === 'upcoming') {
            $params['upcoming'] = 1;
        } elseif ($tab === 'live') {
            $params['current'] = 1;
            unset($params['page']);
        } elseif ($tab === 'past') {
            $params['past'] = 1;
        }

        $ttl = $tab === 'live' ? 60 : 120;
        $result = $this->remember('feed', $params, $ttl, fn () => $this->events->listEvents($params));
        $rows = $this->rows($result);
        $meta = is_array($result['meta'] ?? null) ? $result['meta'] : null;

        // Trust API lifecycle (same as SSR) — do not force tab labels.
        $events = DiscoveryEventNormalizer::many($rows, null);

        $paging = PaginationMeta::from($meta, count($rows), $perPage, $page);
        if ($tab === 'live') {
            $paging['has_more'] = false;
        }

        $local = [
            'upcoming' => count(array_filter($events, fn ($e) => ($e['status'] ?? '') === 'upcoming')),
            'live' => count(array_filter($events, fn ($e) => ($e['status'] ?? '') === 'live')),
            'past' => count(array_filter($events, fn ($e) => ($e['status'] ?? '') === 'past')),
        ];

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'events' => $events,
            'meta' => $meta,
            'counts' => PaginationMeta::normalizeCounts(
                is_array($meta['counts'] ?? null) ? $meta['counts'] : null,
                $local
            ),
            'has_more' => $paging['has_more'],
            'next_page' => $paging['has_more'] ? ($paging['page'] + 1) : null,
            'page' => $paging['page'],
            'last_page' => $paging['last_page'],
            'tab' => $tab,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function sharedListFilters(array $filters): array
    {
        return array_filter([
            'tag' => $filters['tag'] ?? null,
            'city' => $filters['city'] ?? null,
            'state' => $filters['state'] ?? null,
            'country' => $filters['country'] ?? null,
            'q' => $filters['q'] ?? null,
            'platform' => $filters['platform'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  callable(): array  $callback
     * @return array<string, mixed>
     */
    protected function remember(string $bucket, array $params, int $ttl, callable $callback): array
    {
        $key = 'amcob:events:' . $bucket . ':' . self::CACHE_VERSION . ':' . md5(json_encode($params));

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * @param  array<string, mixed>|null  $result
     * @return array<int, array<string, mixed>>
     */
    protected function rows(?array $result): array
    {
        return is_array($result['data'] ?? null) ? $result['data'] : [];
    }
}
