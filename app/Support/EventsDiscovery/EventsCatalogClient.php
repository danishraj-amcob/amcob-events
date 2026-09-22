<?php

namespace App\Support\EventsDiscovery;

/**
 * Minimal catalog client for discovery (list + filter options).
 * Host apps bind this to their ERP/HTTP service.
 */
interface EventsCatalogClient
{
    /**
     * @param  array<string, mixed>  $params
     * @return array{data?: array<int, array<string, mixed>>, meta?: array<string, mixed>|null}
     */
    public function listEvents(array $params = []): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array{countries?: mixed, states?: mixed, cities?: mixed, categories?: mixed}
     */
    public function filters(array $params = []): array;
}
