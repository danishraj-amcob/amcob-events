<?php

namespace App\Http\Controllers;

use App\Services\AmcobEventsService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __construct(protected AmcobEventsService $events) {}

    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('grow-your-business'), 'priority' => '0.6'],
            ['loc' => route('privacy'), 'priority' => '0.3'],
        ];

        $urls = array_merge($urls, Cache::remember('amcob:sitemap:events', 900, function () {
            $eventUrls = [];

            try {
                $page = 1;

                do {
                    $result = $this->events->listEvents(['per_page' => 100, 'page' => $page]);
                    $items  = $result['data'] ?? [];

                    foreach ($items as $event) {
                        if (!empty($event['slug'])) {
                            $eventUrls[] = [
                                'loc'      => route('events.show', $event['slug']),
                                'priority' => '0.8',
                            ];
                        }
                    }

                    $lastPage = (int) ($result['meta']['last_page'] ?? 1);
                    $page++;
                } while ($page <= $lastPage && $page <= 20);
            } catch (\Throwable $e) {
                report($e);
            }

            return $eventUrls;
        }));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
