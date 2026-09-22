<?php

namespace Tests\Unit;

use App\Support\EventsDiscovery\DiscoveryHomepage;
use App\Support\EventsDiscovery\PaginationMeta;
use PHPUnit\Framework\TestCase;

class DiscoveryModuleTest extends TestCase
{
    public function test_pagination_prefers_api_last_page(): void
    {
        $meta = PaginationMeta::from([
            'current_page' => 1,
            'last_page' => 7,
            'total' => 79,
        ], 10, 12);

        $this->assertTrue($meta['has_more']);
        $this->assertSame(1, $meta['page']);
        $this->assertSame(7, $meta['last_page']);
    }

    public function test_pagination_stops_when_loaded_reaches_total(): void
    {
        $meta = PaginationMeta::from([
            'current_page' => 7,
            'last_page' => 7,
            'total' => 79,
        ], 7, 12);

        $this->assertFalse($meta['has_more']);
    }

    public function test_counts_correct_live_double_count_in_upcoming(): void
    {
        $counts = PaginationMeta::normalizeCounts(
            ['upcoming' => 2, 'live' => 1, 'past' => 79],
            ['upcoming' => 1, 'live' => 1, 'past' => 12]
        );

        $this->assertSame(1, $counts['upcoming']);
        $this->assertSame(1, $counts['live']);
        $this->assertSame(79, $counts['past']);
        $this->assertSame(81, $counts['all']);
    }

    public function test_host_config_defaults_and_clamps(): void
    {
        $cfg = DiscoveryHomepage::normalizeHostConfig([
            'default_view' => 'nope',
            'default_tab' => 'upcoming',
            'detail_url_template' => '/events/{slug}',
            'features' => ['hero' => false],
        ]);

        $this->assertSame('grid', $cfg['default_view']);
        $this->assertSame('upcoming', $cfg['default_tab']);
        $this->assertSame('/events/{slug}', $cfg['detail_url_template']);
        $this->assertFalse($cfg['features']['hero']);
        $this->assertTrue($cfg['features']['load_more']);
    }
}
