<?php

namespace Tests\Unit;

use App\Support\Events\EventDetailNormalizer;
use PHPUnit\Framework\TestCase;

class EventDetailNormalizerTest extends TestCase
{
    public function test_it_adapts_the_current_detail_contract(): void
    {
        $event = EventDetailNormalizer::normalize([
            'status' => 'published',
            'visibility' => 'public',
            'event_type' => 'in_person',
            'starts_at' => '2099-09-15T18:00:00+00:00',
            'location' => [
                'label' => 'Chicago, IL',
                'online_url' => null,
            ],
            'categories' => [['id' => 1, 'name' => 'Dinner Mixers']],
            'host' => ['id' => 18, 'name' => 'AMCOB'],
            'tickets' => [['id' => 88, 'name' => 'VIP', 'available' => 10]],
            'speakers' => [['id' => 1, 'name' => 'Speaker']],
            'sponsors' => [['id' => 3, 'name' => 'Acme Corp']],
            'agendas' => [[
                'id' => 3,
                'title' => 'Friday',
                'items' => [['id' => 11, 'title' => 'Opening keynote']],
            ]],
        ]);

        $this->assertTrue($event['registration_open']);
        $this->assertFalse($event['is_online']);
        $this->assertFalse($event['is_past']);
        $this->assertSame('Chicago, IL', $event['location_label']);
        $this->assertSame('Dinner Mixers', $event['tags'][0]['name']);
        $this->assertSame('AMCOB', $event['hosted_by']['name']);
        $this->assertSame('Acme Corp', $event['sponsors'][0]['name']);
        $this->assertSame('Opening keynote', $event['agendas'][0]['items'][0]['title']);
    }

    public function test_it_preserves_explicit_registration_closed_state(): void
    {
        $event = EventDetailNormalizer::normalize([
            'status' => 'published',
            'visibility' => 'public',
            'starts_at' => '2099-09-15T18:00:00+00:00',
            'registration_open' => false,
            'tickets' => [['id' => 88]],
        ]);

        $this->assertFalse($event['registration_open']);
    }

    public function test_it_builds_grouped_agendas_from_flat_rows(): void
    {
        $event = EventDetailNormalizer::normalize([
            'agenda' => [
                [
                    'id' => 11,
                    'title' => 'Opening keynote',
                    'agenda_id' => 3,
                    'agenda_title' => 'Friday',
                    'day_date_label' => 'September 18, 2026',
                ],
                [
                    'id' => 12,
                    'title' => 'Networking break',
                    'agenda_id' => 3,
                    'agenda_title' => 'Friday',
                ],
            ],
        ]);

        $this->assertCount(1, $event['agendas']);
        $this->assertCount(2, $event['agendas'][0]['items']);
        $this->assertSame('Friday', $event['agendas'][0]['title']);
        $this->assertSame('September 18, 2026', $event['agendas'][0]['day_date_label']);
    }

    public function test_it_honors_accepting_registrations_and_featured_flags(): void
    {
        $event = EventDetailNormalizer::normalize([
            'accepting_registrations' => false,
            'tickets' => [['id' => 15]],
            'is_featured' => true,
            'is_past' => false,
        ]);

        $this->assertFalse($event['registration_open']);
        $this->assertTrue($event['is_featured']);
        $this->assertFalse($event['is_past']);
    }
}
