<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventFeedbackRequest;
use App\Services\AmcobEventsService;
use Illuminate\Http\Request;

class EventFeedbackController extends Controller
{
    public function __construct(protected AmcobEventsService $events) {}

    /**
     * Dev-only UI preview — no API calls. Registered only when APP_ENV=local or APP_DEBUG=true.
     */
    public function preview(Request $request, string $scenario = 'form')
    {
        $allowed = ['form', 'already_submitted', 'invalid', 'not_available', 'missing_token'];
        if (!in_array($scenario, $allowed, true)) {
            abort(404);
        }

        $slug = 'annual-gala-2026';
        $token = 'preview-token';

        if ($scenario === 'missing_token') {
            return view('events.feedback', [
                'state'       => 'missing_token',
                'slug'        => $slug,
                'token'       => null,
                'message'     => 'This feedback link is missing a token. Please use the link from your confirmation email.',
                'previewMode' => true,
            ]);
        }

        $mock = $this->mockFeedbackData($slug);

        return view('events.feedback', array_merge($mock, [
            'state'       => $scenario,
            'slug'        => $slug,
            'token'       => $token,
            'previewMode' => true,
            'message'     => match ($scenario) {
                'invalid'        => 'Invalid or expired feedback link. Use the link from your email.',
                'not_available'  => 'Feedback is available after the event has ended.',
                default          => null,
            },
        ]));
    }

    public function previewStore(EventFeedbackRequest $request)
    {
        return redirect()
            ->route('dev.feedback.preview', ['scenario' => 'already_submitted'])
            ->with('feedback_success', 'Thank you for your feedback. (Preview — not saved)');
    }

    public function show(Request $request, string $slug)
    {
        $token = trim((string) $request->query('token', ''));

        if ($token === '') {
            return view('events.feedback', [
                'state'   => 'missing_token',
                'slug'    => $slug,
                'token'   => null,
                'message' => 'This feedback link is missing a token. Please use the link from your confirmation email.',
            ]);
        }

        $result = $this->events->getFeedback($slug, $token);

        if ($result['status'] === 404) {
            abort(404);
        }

        if (!$result['success']) {
            $state = match ($result['status']) {
                422     => 'not_available',
                default => 'invalid',
            };

            return view('events.feedback', [
                'state'   => $state,
                'slug'    => $slug,
                'token'   => $token,
                'message' => $result['message'] ?? 'Unable to load the feedback form.',
            ]);
        }

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];
        $event = is_array($data['event'] ?? null) ? $data['event'] : [];
        $attendee = is_array($data['attendee'] ?? null) ? $data['attendee'] : null;
        $existingFeedback = is_array($data['existing_feedback'] ?? null)
            ? $data['existing_feedback']
            : null;

        if ($attendee === null) {
            return view('events.feedback', [
                'state'   => 'invalid',
                'slug'    => $slug,
                'token'   => $token,
                'message' => 'Invalid or expired feedback link. Use the link from your email.',
            ]);
        }

        $state = !empty($data['already_submitted']) ? 'already_submitted' : 'form';

        return view('events.feedback', [
            'state'            => $state,
            'slug'             => $slug,
            'token'            => $token,
            'event'            => $event,
            'attendee'         => $attendee,
            'existingFeedback' => $existingFeedback,
            'message'          => null,
        ]);
    }

    public function store(EventFeedbackRequest $request, string $slug)
    {
        $data = $request->validated();

        $payload = array_filter([
            'token'      => $data['token'],
            'email'      => $data['email'],
            'first_name' => $data['first_name'] ?? null,
            'last_name'  => $data['last_name'] ?? null,
            'rating'     => (int) $data['rating'],
            'feedback'   => $data['feedback'],
        ], fn ($v) => $v !== null && $v !== '');

        $result = $this->events->submitFeedback($slug, $payload);

        if (!$result['success']) {
            return back()
                ->withInput()
                ->withErrors(['feedback_submit' => $result['message'] ?? 'Feedback could not be submitted. Please try again.']);
        }

        return redirect()
            ->route('events.feedback', ['slug' => $slug, 'token' => $data['token']])
            ->with('feedback_success', $result['message'] ?? 'Thank you for your feedback.');
    }

    /** @return array{event: array<string, mixed>, attendee: array<string, mixed>, existingFeedback: ?array<string, mixed>} */
    protected function mockFeedbackData(string $slug): array
    {
        return [
            'event' => [
                'id'         => 1,
                'slug'       => $slug,
                'title'      => 'Annual Gala 2026',
                'starts_at'  => '2026-09-15T18:00:00+00:00',
                'ends_at'    => '2026-09-15T22:00:00+00:00',
                'public_url' => url('/' . $slug),
            ],
            'attendee' => [
                'email'      => 'guest@example.com',
                'first_name' => 'Alex',
                'last_name'  => 'Guest',
                'full_name'  => 'Alex Guest',
            ],
            'existingFeedback' => [
                'rating'   => 5,
                'feedback' => 'Great networking and well organized sessions.',
            ],
        ];
    }
}
