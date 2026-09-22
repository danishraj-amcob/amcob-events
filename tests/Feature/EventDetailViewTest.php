<?php

namespace Tests\Feature;

use App\Support\Events\EventDetailNormalizer;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class EventDetailViewTest extends TestCase
{
    public function test_current_detail_contract_renders_registration_agenda_and_sponsors(): void
    {
        $event = EventDetailNormalizer::normalize([
            'id' => 1,
            'slug' => 'annual-gala-2026',
            'title' => 'Annual Gala 2026',
            'description' => '<p>Full HTML description.</p>',
            'status' => 'published',
            'visibility' => 'public',
            'event_type' => 'in_person',
            'starts_at' => '2099-09-15T18:00:00+00:00',
            'date_label' => 'Tue, Sep 15, 2099 · 6:00 PM – 10:00 PM',
            'location' => ['label' => 'Chicago, IL', 'type' => 'in_person'],
            'is_current' => true,
            'tickets' => [[
                'id' => 88,
                'name' => 'VIP',
                'price' => 500,
                'price_label' => '$500.00',
                'available' => 88,
                'is_free' => false,
            ]],
            'speakers' => [[
                'id' => 1,
                'name' => 'Dr. Amina Hassan',
                'designation' => 'Keynote Speaker',
            ]],
            'sponsors' => [[
                'id' => 3,
                'name' => 'Acme Corp',
                'website_url' => 'https://acme.example.com',
            ]],
            'agendas' => [[
                'id' => 3,
                'title' => 'Friday',
                'day_date_label' => 'September 18, 2099',
                'items' => [[
                    'id' => 11,
                    'title' => 'Opening keynote',
                    'start_time_label' => '9:00 AM',
                    'duration_label' => '45 min',
                    'speaker_names' => 'Omair Tariq',
                ]],
            ]],
        ]);

        $this->view('events.show', [
            'event' => $event,
            'errors' => new ViewErrorBag,
        ])
            ->assertSee('Annual Gala 2026')
            ->assertSee('Tue, Sep 15, 2099 · 6:00 PM – 10:00 PM')
            ->assertSee('Join now')
            ->assertSee('Acme Corp')
            ->assertSee('Opening keynote')
            ->assertSee('9:00 AM')
            ->assertSee('Speaker: Omair Tariq');
    }

    public function test_untitled_timed_agenda_and_open_tickets_render(): void
    {
        $event = EventDetailNormalizer::normalize([
            'id' => 6,
            'slug' => 'events-test',
            'title' => 'Events Test',
            'short_description' => 'Test',
            'description' => 'Test',
            'status' => 'published',
            'visibility' => 'public',
            'event_type' => 'in_person',
            'starts_at' => '2026-10-23T01:33:00+00:00',
            'ends_at' => '2026-11-11T02:33:00+00:00',
            'timezone' => 'America/New_York',
            'date_label' => 'Thu, Oct 22, 2026 · 9:33 PM – 9:33 PM EST',
            'location_label' => 'East & West Cafe, Irvine, CA',
            'location' => [
                'label' => 'East & West Cafe, Irvine, CA',
                'full_address' => 'East & West Cafe, 300 Spectrum Center Drive, Irvine, CA, United States',
                'type' => 'in_person',
                'type_label' => 'In Person',
                'venue_name' => 'East & West Cafe',
                'venue_address' => '300 Spectrum Center Drive',
                'venue_city' => 'Irvine',
                'venue_state' => 'CA',
                'venue_country' => 'United States',
            ],
            'is_featured' => true,
            'registration_open' => true,
            'allow_multiple_guests' => true,
            'max_guests_per_registration' => 4,
            'tickets' => [[
                'id' => 15,
                'name' => 'Random Ticket',
                'price' => 500,
                'price_label' => '$500.00',
                'available' => null,
                'sold_count' => 3,
                'is_free' => false,
            ]],
            'agendas' => [[
                'id' => 4,
                'title' => 'Virtual Trade Briefing Agenda',
                'items' => [
                    ['id' => 17, 'title' => 'Market overview'],
                    ['id' => 18, 'title' => 'Export readiness checklist'],
                ],
            ]],
        ]);

        $this->view('events.show', [
            'event' => $event,
            'errors' => new ViewErrorBag,
        ])
            ->assertSee('Events Test')
            ->assertSee('Featured')
            ->assertSee('Virtual Trade Briefing Agenda')
            ->assertSee('Market overview')
            ->assertSee('Export readiness checklist')
            ->assertSee('East & West Cafe')
            ->assertSee('300 Spectrum Center Drive')
            ->assertSee('Open availability')
            ->assertDontSee('Up to 4 per registration');
    }
}
