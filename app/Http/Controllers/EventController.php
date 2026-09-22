<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRegistrationRequest;
use App\Models\EventRegistrationLog;
use App\Services\AmcobEventsService;
use App\Support\Events\EventDetailNormalizer;
use App\Support\EventsDiscovery\DiscoveryHomepage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    public function __construct(
        protected AmcobEventsService $events,
        protected DiscoveryHomepage $discoveryHomepage,
    ) {}

    public function index(Request $request)
    {
        $discovery = $this->discoveryHomepage->build([
            'tag' => $request->query('tag'),
            'q' => $request->query('q'),
            'direction' => $request->query('direction'),
            'city' => $request->query('city'),
            'state' => $request->query('state'),
            'country' => $request->query('country'),
            'platform' => $request->query('platform'),
            'page' => $request->query('page', 1),
        ], [
            'feed_url' => route('events.discovery'),
            'config' => [
                'default_view' => 'calendar',
                'default_tab' => 'all',
                'detail_url_template' => '/{slug}',
                'features' => [
                    'hero' => true,
                    'load_more' => true,
                ],
            ],
        ]);

        return view('events.index', [
            'discovery' => $discovery,
        ]);
    }

    /**
     * JSON feed for discovery Load more / tab pagination.
     */
    public function discovery(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->discoveryHomepage->feed([
            'tab' => $request->query('tab'),
            'page' => $request->query('page', 1),
            'per_page' => $request->query('per_page', DiscoveryHomepage::PER_PAGE),
            'tag' => $request->query('tag'),
            'city' => $request->query('city'),
            'state' => $request->query('state'),
            'country' => $request->query('country'),
            'q' => $request->query('q'),
        ]));
    }

    public function cards(Request $request): \Illuminate\Http\JsonResponse
    {
        $tab       = in_array($request->query('tab'), ['upcoming', 'current', 'past', 'all']) ? $request->query('tab') : 'upcoming';
        $page      = max((int) $request->query('page', 1), 1);
        $direction = in_array($request->query('direction'), ['asc', 'desc']) ? $request->query('direction') : 'asc';
        $tag       = $request->query('tag');
        $city      = $request->query('city');
        $state     = $request->query('state');
        $country   = $request->query('country');
        $platform  = $request->query('platform');

        $params = array_filter([
            'sort'      => 'starts_at',
            'direction' => $direction,
            'per_page'  => 6,
            'tag'       => $tag,
            'city'      => $city,
            'state'     => $state,
            'country'   => $country,
            'platform'  => $platform,
        ]);

        if ($tab !== 'all') {
            match ($tab) {
                'current' => $params['current']  = 1,
                'past'    => $params['past']     = 1,
                default   => $params['upcoming'] = 1,
            };
        }

        if ($tab !== 'current' && $page > 1) {
            $params['page'] = $page;
        }

        $cacheKey = 'amcob:events:cards:' . md5(json_encode($params));
        $result   = Cache::remember($cacheKey, 120, fn () => $this->events->listEvents($params));
        $events   = $result['data'] ?? [];
        $meta     = $result['meta'] ?? null;
        $isPast   = $tab === 'past';

        $html = '';
        foreach ($events as $event) {
            $html .= view('events._card', ['event' => $event, 'past' => $isPast])->render();
        }

        $hasMore = $tab !== 'current' && $meta && ($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1);

        return response()->json([
            'html'      => $html,
            'meta'      => $meta,
            'count'     => count($events),
            'has_more'  => $hasMore,
            'next_page' => $hasMore ? (($meta['current_page'] ?? 1) + 1) : null,
        ]);
    }

    public function filterOptions(Request $request): \Illuminate\Http\JsonResponse
    {
        $params = array_filter([
            'country' => $request->query('country'),
            'state'   => $request->query('state'),
            'city'    => $request->query('city'),
        ]);

        $key  = 'amcob:events:filters:' . md5(json_encode($params));
        $data = Cache::remember($key, 3600, fn () => $this->events->filters($params));

        return response()->json($data);
    }

    public function show(string $slug)
    {
        $event = $this->cachedEvent($slug);

        abort_if(is_null($event), 404);

        return view('events.show', [
            'event' => EventDetailNormalizer::normalize($event),
        ]);
    }

    private function cachedEvent(string $slug): ?array
    {
        $key = 'amcob:event:' . $slug;
        $cached = Cache::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $event = $this->events->find($slug);

        if ($event !== null) {
            Cache::put($key, $event, 300);
        }

        return $event;
    }

    public function previewCoupon(Request $request, string $slug): \Illuminate\Http\JsonResponse
    {
        $couponCode = trim((string) $request->input('coupon_code', ''));
        $ticketId   = $request->input('ticket_id');

        if ($couponCode === '' || !$ticketId) {
            return response()->json(['message' => 'coupon_code and ticket_id are required.'], 422);
        }

        $result = $this->events->previewCoupon($slug, [
            'coupon_code' => $couponCode,
            'ticket_id'   => (int) $ticketId,
        ]);

        return response()->json(
            ['success' => $result['success'], 'message' => $result['message'], 'data' => $result['data']],
            $result['success'] ? 200 : ($result['status'] ?: 422)
        );
    }

    public function register(EventRegistrationRequest $request, string $slug)
    {
        $data = $request->validated();
        $event = $this->cachedEvent($slug) ?? [];
        $allowMultipleGuests = !empty($event['allow_multiple_guests']);
        $maxGuestsPerRegistration = max(1, (int) ($event['max_guests_per_registration'] ?? 1));

        $guestFields = ['email', 'first_name', 'last_name', 'phone', 'job_title', 'company_name', 'linkedin_url'];
        $cleanGuest = function (array $guest) use ($guestFields): array {
            $row = [];
            foreach ($guestFields as $field) {
                if (!array_key_exists($field, $guest)) {
                    continue;
                }
                $value = is_string($guest[$field]) ? trim($guest[$field]) : $guest[$field];
                if ($value !== null && $value !== '') {
                    $row[$field] = $value;
                }
            }

            return $row;
        };

        if (!empty($data['guests']) && is_array($data['guests'])) {
            if (!$allowMultipleGuests) {
                return back()
                    ->withInput()
                    ->withErrors(['registration' => 'This event does not allow multi-guest registration.']);
            }

            $guests = array_values(array_filter(array_map(
                fn ($g) => is_array($g) ? $cleanGuest($g) : [],
                $data['guests']
            )));
            if (count($guests) > $maxGuestsPerRegistration) {
                return back()
                    ->withInput()
                    ->withErrors(['registration' => 'You can register up to ' . $maxGuestsPerRegistration . ' guests for this event.']);
            }

            $payload = array_filter([
                'ticket_id'   => $data['ticket_id'],
                'guests'      => $guests,
                'coupon_code' => $data['coupon_code'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            $primary = $guests[0] ?? [];
        } else {
            $primary = $cleanGuest($data);
            $payload = array_filter(array_merge($primary, [
                'ticket_id'   => $data['ticket_id'],
                'coupon_code' => $data['coupon_code'] ?? null,
            ]), fn ($v) => $v !== null && $v !== '');
        }

        if (!empty($data['card_number'])) {
            $payload['payment'] = [
                'card' => [
                    'number'     => $data['card_number'],
                    'expiration' => $data['card_expiration'],
                    'cvv'        => $data['card_cvv'] ?? null,
                ],
            ];
        }

        $result = $this->events->register($slug, $payload);
        $resultData = is_array($result['data'] ?? null) ? $result['data'] : [];

        $tickets = [];
        if (!empty($resultData['tickets']) && is_array($resultData['tickets'])) {
            $tickets = array_values(array_filter($resultData['tickets'], 'is_array'));
        } elseif (!empty($resultData['ticket']) && is_array($resultData['ticket'])) {
            $tickets = [$resultData['ticket']];
        }

        $primaryTicket = $tickets[0] ?? null;
        $guestCount = (int) ($resultData['guest_count']
            ?? (!empty($data['guests']) ? count($data['guests']) : 1));

        EventRegistrationLog::create([
            'event_slug' => $slug,
            'event_id' => $primaryTicket['event_id'] ?? null,
            'event_title' => $primaryTicket['event_title'] ?? null,
            'ticket_id' => $data['ticket_id'],
            'ticket_name' => $primaryTicket['ticket_name'] ?? null,
            'email' => $primary['email'] ?? null,
            'first_name' => $primary['first_name'] ?? null,
            'last_name' => $primary['last_name'] ?? null,
            'phone' => $primary['phone'] ?? null,
            'status' => $result['success'] ? 'confirmed' : 'failed',
            'amount' => $resultData['payment']['amount'] ?? null,
            'currency' => $resultData['payment']['currency'] ?? null,
            'transaction_id' => $resultData['payment']['transaction_id'] ?? null,
            'check_in_token' => $primaryTicket['check_in_token'] ?? null,
            'raw_response' => $resultData,
        ]);

        if (!$result['success']) {
            return back()
                ->withInput()
                ->withErrors(['registration' => $result['message'] ?? 'Registration could not be completed.']);
        }

        Cache::forget('amcob:event:' . $slug);

        return redirect()
            ->route('events.show', $slug)
            ->with('success', $result['message'] ?? 'Registration successful! Check your email for your ticket.')
            ->with('registration_ticket', $primaryTicket)
            ->with('registration_tickets', $tickets)
            ->with('registration_guest_count', $guestCount);
    }
}
