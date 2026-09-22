<?php

namespace App\Support\Events;

use Illuminate\Support\Carbon;

/**
 * Adapts ERP event-detail responses to the stable shape consumed by Blade.
 *
 * The API may expose both legacy and current aliases (tags/categories,
 * hosted_by/host, agenda/agendas). Keeping those fallbacks here prevents the
 * view from becoming coupled to a single response revision.
 */
final class EventDetailNormalizer
{
    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public static function normalize(array $event): array
    {
        $location = self::array($event['location'] ?? null);
        $tickets = self::rows($event['tickets'] ?? null);
        $tags = self::rows($event['tags'] ?? null);
        $categories = self::rows($event['categories'] ?? null);
        $hostedBy = self::array($event['hosted_by'] ?? null);
        $host = self::array($event['host'] ?? null);

        $isCurrent = (bool) ($event['is_current'] ?? false);
        $isOnline = (bool) ($event['is_online'] ?? false)
            || ($event['event_type'] ?? null) === 'online'
            || ($location['type'] ?? null) === 'online';
        $isPast = self::isPast($event, $isCurrent);

        $event['tickets'] = $tickets;
        $event['speakers'] = self::rows($event['speakers'] ?? null);
        $event['sponsors'] = self::rows($event['sponsors'] ?? null);
        $event['tags'] = $tags ?: $categories;
        $event['categories'] = $categories ?: $tags;
        $event['location'] = $location;
        $event['hosted_by'] = $hostedBy ?: $host;
        $event['host'] = $host ?: $hostedBy;
        $event['is_current'] = $isCurrent;
        $event['is_online'] = $isOnline;
        $event['is_past'] = $isPast;
        $event['is_featured'] = !empty($event['is_featured']) || !empty($event['featured']);
        $event['online_url'] = $event['online_url'] ?? $location['online_url'] ?? null;
        $event['location_label'] = $event['location_label']
            ?? $location['label']
            ?? $location['full_address']
            ?? null;
        $event['agendas'] = self::agendas($event);

        // The current detail contract no longer guarantees registration_open.
        // When omitted, published public events with ticket choices are open
        // unless their lifecycle has already ended.
        if (!array_key_exists('registration_open', $event) && array_key_exists('accepting_registrations', $event)) {
            $event['registration_open'] = (bool) $event['accepting_registrations'];
        } elseif (!array_key_exists('registration_open', $event)) {
            $event['registration_open'] = !$isPast
                && ($event['status'] ?? 'published') === 'published'
                && ($event['visibility'] ?? 'public') === 'public'
                && $tickets !== [];
        } else {
            $event['registration_open'] = (bool) $event['registration_open'];
        }

        if (array_key_exists('accepting_registrations', $event) && !$event['accepting_registrations']) {
            $event['registration_open'] = false;
        }

        return $event;
    }

    /**
     * Prefer grouped agendas; reconstruct them from flat agenda rows when
     * needed for compatibility with older/current API variants.
     *
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, mixed>>
     */
    private static function agendas(array $event): array
    {
        $groups = self::rows($event['agendas'] ?? null);
        if ($groups !== []) {
            return array_map(function (array $group): array {
                $group['items'] = self::rows($group['items'] ?? null);

                return $group;
            }, $groups);
        }

        $flat = self::rows($event['agenda'] ?? null);
        $byGroup = [];

        foreach ($flat as $item) {
            $key = (string) ($item['agenda_id'] ?? $item['agenda_title'] ?? 'agenda');
            if (!isset($byGroup[$key])) {
                $byGroup[$key] = [
                    'id' => $item['agenda_id'] ?? null,
                    'title' => $item['agenda_title'] ?? null,
                    'day_date' => $item['day_date'] ?? null,
                    'day_label' => $item['day_label'] ?? null,
                    'day_date_label' => $item['day_date_label'] ?? null,
                    'items' => [],
                ];
            }

            $byGroup[$key]['items'][] = $item;
        }

        return array_values($byGroup);
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private static function isPast(array $event, bool $isCurrent): bool
    {
        if (array_key_exists('is_past', $event)) {
            return (bool) $event['is_past'];
        }

        if ($isCurrent) {
            return false;
        }

        $value = $event['ends_at'] ?? $event['starts_at'] ?? null;
        if (!is_string($value) || $value === '') {
            return false;
        }

        try {
            return Carbon::parse($value)->isPast();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function array(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function rows(mixed $value): array
    {
        return array_values(array_filter(
            is_array($value) ? $value : [],
            'is_array'
        ));
    }
}
