<?php

namespace App\Support\EventsDiscovery;

/**
 * Sanitize values used in CSS / HTML attributes from ERP data.
 */
final class DiscoverySanitize
{
    /**
     * Allow only safe hex colors (3/4/6/8 digit) or null.
     */
    public static function hexColor(mixed $value, string $fallback = '#2D3C69'): string
    {
        if (!is_string($value)) {
            return $fallback;
        }
        $v = trim($value);
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $v)) {
            return $v;
        }

        return $fallback;
    }
}
