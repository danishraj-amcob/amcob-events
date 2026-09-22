<?php

namespace App\Services;

use App\Support\EventsDiscovery\EventsCatalogClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class AmcobEventsService implements EventsCatalogClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.amcob_events.base_url'), '/');
        $this->apiKey = config('services.amcob_events.api_key');
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['X-Events-Api-Key' => $this->apiKey])
            ->acceptJson()
            ->timeout(15);
    }

    /**
     * GET /events — list published events.
     */
    public function listEvents(array $params = []): array
    {
        $response = $this->client()->get('/events', $params);

        if ($response->failed()) {
            report(new \RuntimeException('AMCOB events list failed: ' . $response->status()));
            return ['data' => [], 'meta' => null];
        }

        return $response->json();
    }

    /**
     * GET /events/filters — countries, states, cities, categories for dropdowns.
     * Supports optional cascade: ?country=US, ?country=US&state=IL, etc.
     */
    public function filters(array $params = []): array
    {
        $response = $this->client()->get('/events/filters', $params);

        if ($response->failed()) {
            return ['countries' => [], 'states' => [], 'cities' => [], 'categories' => []];
        }

        return $response->json('data') ?? ['countries' => [], 'states' => [], 'cities' => [], 'categories' => []];
    }

    /**
     * GET /events/{slug} — single event detail.
     */
    public function find(string $slug, ?string $invite = null): ?array
    {
        $response = $this->client()->get("/events/{$slug}", array_filter([
            'invite' => $invite,
        ]));

        if ($response->status() === 404) {
            return null;
        }

        if ($response->failed()) {
            report(new \RuntimeException("AMCOB event detail failed for {$slug}: " . $response->status()));
            return null;
        }

        return $response->json('data');
    }

    /**
     * POST /events/{slug}/preview-coupon — validate coupon + preview discounted price.
     */
    public function previewCoupon(string $slug, array $payload): array
    {
        $response = $this->client()->post("/events/{$slug}/preview-coupon", $payload);

        return [
            'success' => $response->successful(),
            'status'  => $response->status(),
            'message' => $response->json('message'),
            'data'    => $response->json('data'),
        ];
    }

    /**
     * POST /events/{slug}/register — register + pay.
     * Returns a normalized array so the controller doesn't need to know
     * about HTTP status codes.
     */
    public function register(string $slug, array $payload): array
    {
        $response = $this->client()->post("/events/{$slug}/register", $payload);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'message' => $response->json('message'),
            'data' => $response->json('data'),
        ];
    }

    /**
     * GET /events/{slug}/feedback — load feedback form (prefill + token validation).
     */
    public function getFeedback(string $slug, string $token): array
    {
        $response = $this->client()->get("/events/{$slug}/feedback", [
            'token' => $token,
        ]);

        return [
            'success' => $response->successful(),
            'status'  => $response->status(),
            'message' => $response->json('message'),
            'data'    => $response->json('data'),
        ];
    }

    /**
     * POST /events/{slug}/feedback — submit post-event feedback.
     */
    public function submitFeedback(string $slug, array $payload): array
    {
        $response = $this->client()->post("/events/{$slug}/feedback", $payload);

        return [
            'success' => $response->successful(),
            'status'  => $response->status(),
            'message' => $response->json('message'),
            'data'    => $response->json('data'),
        ];
    }
}
