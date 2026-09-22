@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;

    $state = $state ?? 'invalid';
    $slug  = $slug ?? null;
    $token = $token ?? null;
    $event = is_array($event ?? null) ? $event : [];
    $attendee = is_array($attendee ?? null) ? $attendee : [];
    $existingFeedback = is_array($existingFeedback ?? null) ? $existingFeedback : null;

    $eventTitle = $event['title'] ?? 'Event';
    $eventUrl   = $slug ? route('events.show', $slug) : route('home');

    $eventDateLabel = null;
    $eventTimeLabel = null;
    if (!empty($event['starts_at'])) {
        try {
            $start = Carbon::parse($event['starts_at']);
            $eventDateLabel = $start->format('l, F j, Y');
            $eventTimeLabel = $start->format('g:i A');
            if (!empty($event['ends_at'])) {
                try {
                    $end = Carbon::parse($event['ends_at']);
                    if ($start->isSameDay($end)) {
                        $eventTimeLabel = $start->format('g:i A') . ' – ' . $end->format('g:i A');
                    } else {
                        $eventTimeLabel = $start->format('M j, g:i A') . ' – ' . $end->format('M j, g:i A');
                    }
                } catch (\Throwable $e) {
                    // keep start-only
                }
            }
        } catch (\Throwable $e) {
            $eventDateLabel = null;
            $eventTimeLabel = null;
        }
    }

    $guestName = trim($attendee['first_name'] ?? '');
    if ($guestName === '' && !empty($attendee['full_name'])) {
        $guestName = explode(' ', trim($attendee['full_name']))[0] ?? '';
    }

    $prefillEmail = old('email', $attendee['email'] ?? '');
    $prefillFirst = old('first_name', $attendee['first_name'] ?? '');
    $prefillLast  = old('last_name', $attendee['last_name'] ?? '');
    $prefillRating = (int) old('rating', 0);
    $prefillFeedback = old('feedback', '');
    $previewMode = !empty($previewMode);

    $showHero = in_array($state, ['form', 'already_submitted'], true) && !empty($event);
    $ratingLabels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Great', 5 => 'Excellent'];
@endphp

@section('title', 'Event Feedback — ' . $eventTitle)
@section('seo_description', 'Share your feedback for ' . $eventTitle . '.')
@section('seo_canonical', $slug && $token ? route('events.feedback', ['slug' => $slug, 'token' => $token]) : url()->current())
@section('seo_type', 'website')

@push('styles')
<style>
.fb-page{background:var(--bg);min-height:calc(100vh - 72px);padding-bottom:80px}
.fb-hero{
    position:relative;overflow:hidden;
    background:var(--grad-hero);color:#fff;
    padding:clamp(36px,6vw,56px) 0 clamp(48px,7vw,72px);
}
.fb-hero::before{content:"";position:absolute;inset:0;pointer-events:none;background:
    radial-gradient(640px 380px at 100% 0%,rgba(206,137,36,.45),transparent 58%),
    radial-gradient(520px 420px at -5% 100%,rgba(58,75,130,.55),transparent 62%)}
.fb-hero-in{position:relative;z-index:2;max-width:760px;margin:0 auto;text-align:center}
.fb-hero .kick{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);
    padding:7px 16px;border-radius:99px;font-size:11.5px;font-weight:800;
    letter-spacing:1.4px;text-transform:uppercase;color:var(--gold-soft);margin-bottom:14px
}
.fb-hero h1{font-size:clamp(28px,4.5vw,40px);font-weight:800;line-height:1.12;margin:0 0 12px}
.fb-hero .sub{font-size:16px;color:rgba(255,255,255,.82);line-height:1.6;max-width:520px;margin:0 auto}
.fb-hero-meta{display:flex;flex-wrap:wrap;justify-content:center;gap:10px 20px;margin-top:22px}
.fb-hero-meta span{
    display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:600;
    color:rgba(255,255,255,.9);background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);
    padding:8px 14px;border-radius:99px
}
.fb-hero-meta svg{width:16px;height:16px;color:var(--gold-l);flex:none}

.fb-main{max-width:760px;margin:-36px auto 0;padding:0 clamp(20px,4vw,32px);position:relative;z-index:3}
.fb-shell{
    background:var(--card);border:1px solid var(--line);border-radius:24px;
    box-shadow:0 24px 60px rgba(28,38,74,.12),0 4px 16px rgba(28,38,74,.06);
    overflow:hidden
}

.fb-devbar{
    display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;
    background:linear-gradient(90deg,#141E38,#1E2B4C);color:rgba(255,255,255,.85);
    font-size:12px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.08)
}
.fb-devbar strong{color:#fff;font-weight:800}
.fb-devtabs{display:flex;flex-wrap:wrap;gap:6px;margin-left:auto}
.fb-devtabs a{
    font-size:11px;font-weight:700;padding:5px 11px;border-radius:99px;
    background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.75);
    text-decoration:none;transition:.15s
}
.fb-devtabs a:hover,.fb-devtabs a.is-on{background:rgba(206,137,36,.25);border-color:rgba(206,137,36,.45);color:#fff}

.fb-body{padding:clamp(28px,4vw,40px) clamp(24px,4vw,36px) clamp(32px,4vw,44px)}

/* Welcome strip */
.fb-welcome{
    display:flex;align-items:flex-start;gap:16px;padding:18px 20px;margin-bottom:28px;
    background:linear-gradient(135deg,rgba(45,60,105,.06),rgba(206,137,36,.05));
    border:1px solid rgba(45,60,105,.1);border-radius:16px
}
.fb-welcome-av{
    width:48px;height:48px;border-radius:14px;flex:none;
    background:linear-gradient(145deg,var(--navy),#3a4b82);color:#fff;
    display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;
    box-shadow:0 8px 20px rgba(45,60,105,.25)
}
.fb-welcome b{display:block;font-size:16px;color:var(--navy);margin-bottom:4px;font-weight:800}
.fb-welcome p{font-size:14px;color:var(--t2);line-height:1.55;margin:0}

/* Form sections */
.fb-section{margin-bottom:28px;padding-bottom:28px;border-bottom:1px solid var(--line)}
.fb-section:last-of-type{border-bottom:none;margin-bottom:0;padding-bottom:0}
.fb-section-hd{display:flex;align-items:center;gap:10px;margin-bottom:18px}
.fb-section-ic{
    width:36px;height:36px;border-radius:10px;flex:none;
    background:rgba(206,137,36,.12);color:var(--gold);
    display:flex;align-items:center;justify-content:center
}
.fb-section-ic svg{width:18px;height:18px}
.fb-section-hd h3{font-family:'Source Sans 3',sans-serif;font-size:15px;font-weight:800;color:var(--navy);letter-spacing:.2px;margin:0}
.fb-section-hd p{font-size:12.5px;color:var(--t3);margin:2px 0 0}

/* Rating */
.fb-rating-box{
    text-align:center;padding:28px 20px 24px;
    background:linear-gradient(180deg,#fafbfd,#fff);
    border:1.5px solid var(--line);border-radius:18px
}
.fb-stars{display:flex;justify-content:center;gap:4px;margin:0 0 12px}
.fb-star-btn{
    appearance:none;border:none;background:transparent;padding:6px;cursor:pointer;
    color:#d4d8e8;transition:color .18s,transform .15s;line-height:1;border-radius:10px
}
.fb-star-btn svg{width:42px;height:42px;display:block;filter:drop-shadow(0 2px 4px rgba(0,0,0,.06))}
.fb-star-btn:hover,.fb-star-btn.is-on,.fb-star-btn.is-hover{color:var(--gold)}
.fb-star-btn:hover,.fb-star-btn.is-hover{transform:scale(1.08)}
.fb-star-btn:focus-visible{outline:2px solid var(--gold);outline-offset:3px}
.fb-rating-label{font-size:15px;font-weight:800;color:var(--navy);min-height:22px;letter-spacing:.2px}
.fb-rating-hint{font-size:12.5px;color:var(--t3);margin-top:4px}

/* Fields */
.fb-fld{margin-bottom:16px}
.fb-fld:last-child{margin-bottom:0}
.fb-fld label{display:block;font-size:12px;font-weight:700;color:var(--t2);letter-spacing:.3px;margin-bottom:7px}
.fb-fld label .req{color:var(--red);margin-left:2px}
.fb-fld input,.fb-fld textarea{
    width:100%;border:1.5px solid var(--line);border-radius:12px;padding:12px 14px;
    font-family:inherit;font-size:15px;color:var(--t1);outline:none;background:#fff;transition:border-color .15s,box-shadow .15s
}
.fb-fld input::placeholder,.fb-fld textarea::placeholder{color:var(--t3);opacity:.85}
.fb-fld input:focus,.fb-fld textarea:focus{border-color:var(--gold);box-shadow:0 0 0 4px rgba(206,137,36,.1)}
.fb-fld textarea{min-height:140px;resize:vertical;line-height:1.6}
.fb-fld-hint{font-size:12px;color:var(--t3);margin-top:6px;line-height:1.45}
.fb-fld-foot{display:flex;justify-content:space-between;align-items:center;margin-top:6px;gap:12px}
.fb-char-count{font-size:11.5px;color:var(--t3);font-weight:600;white-space:nowrap}
.fb-char-count.is-warn{color:var(--gold)}
.fb-fld label.error{font-size:12px;color:var(--red);font-weight:600;margin-top:6px;display:block}
.fb-r2{display:grid;grid-template-columns:1fr 1fr;gap:16px}

/* Alerts */
.fb-alert{
    display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:14px;
    font-size:14px;line-height:1.5;margin-bottom:24px
}
.fb-alert svg{width:20px;height:20px;flex:none;margin-top:1px}
.fb-alert.ok{background:rgba(30,158,106,.08);border:1px solid rgba(30,158,106,.22);color:#157a52}
.fb-alert.err{background:rgba(220,76,76,.07);border:1px solid rgba(220,76,76,.22);color:#b23030}

/* Submit */
.fb-actions{margin-top:32px;padding-top:28px;border-top:1px solid var(--line);text-align:center}
.fb-submit{
    width:100%;max-width:360px;justify-content:center;font-size:15.5px;padding:16px 28px;
    border-radius:14px;font-weight:800;letter-spacing:.02em
}
.fb-submit:disabled{opacity:.65;cursor:progress;transform:none!important}
.fb-note{
    display:flex;align-items:center;justify-content:center;gap:6px;
    font-size:12.5px;color:var(--t3);margin-top:16px;line-height:1.5
}
.fb-note svg{width:14px;height:14px;color:var(--grn);flex:none}

/* Status screens */
.fb-status{text-align:center;padding:clamp(20px,4vw,36px) clamp(8px,3vw,24px)}
.fb-status-ic{
    width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    margin:0 auto 22px;position:relative
}
.fb-status-ic::after{
    content:"";position:absolute;inset:-8px;border-radius:50%;opacity:.35;
    background:inherit;filter:blur(12px);z-index:-1
}
.fb-status-ic.ok{background:rgba(30,158,106,.14);color:var(--grn)}
.fb-status-ic.warn{background:rgba(206,137,36,.14);color:var(--gold)}
.fb-status-ic.err{background:rgba(220,76,76,.12);color:var(--red)}
.fb-status-ic svg{width:32px;height:32px}
.fb-status h2{font-size:clamp(22px,3.5vw,28px);color:var(--navy);margin-bottom:12px;font-weight:800}
.fb-status p{font-size:15.5px;color:var(--t2);line-height:1.65;max-width:440px;margin:0 auto 28px}
.fb-status-actions{display:flex;flex-wrap:wrap;justify-content:center;gap:12px}

/* Already submitted summary */
.fb-summary{
    max-width:420px;margin:0 auto 28px;text-align:left;
    background:linear-gradient(145deg,rgba(45,60,105,.04),rgba(206,137,36,.04));
    border:1px solid rgba(45,60,105,.12);border-radius:18px;padding:22px 24px;
    position:relative;overflow:hidden
}
.fb-summary::before{
    content:"";position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,var(--navy),var(--gold))
}
.fb-summary-lbl{font-size:11px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;color:var(--t3);margin-bottom:10px}
.fb-prev-rating{display:flex;gap:3px;margin-bottom:14px;color:var(--gold)}
.fb-prev-rating svg{width:24px;height:24px}
.fb-prev-text{
    font-size:15px;color:var(--t1);line-height:1.65;margin:0;
    padding-left:14px;border-left:3px solid var(--gold-soft);font-style:italic
}

@media(max-width:640px){
    .fb-main{margin-top:-28px}
    .fb-r2{grid-template-columns:1fr}
    .fb-welcome{flex-direction:column;align-items:center;text-align:center}
    .fb-devtabs{margin-left:0;width:100%}
    .fb-star-btn svg{width:36px;height:36px}
}
</style>
@endpush

@section('content')
<section class="fb-page">

    @if ($showHero)
    <div class="fb-hero">
        <div class="wrap fb-hero-in">
            <span class="kick">Post-event feedback</span>
            <h1>{{ $eventTitle }}</h1>
            @if ($state === 'form')
            <p class="sub">Your opinion shapes how we design future AMCOB experiences.</p>
            @else
            <p class="sub">Thank you for sharing your experience with us.</p>
            @endif
            @if ($eventDateLabel ?? null)
            <div class="fb-hero-meta">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    {{ $eventDateLabel }}
                </span>
                @if (!empty($eventTimeLabel))
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    {{ $eventTimeLabel }}
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>
    @elseif (!in_array($state, ['form', 'already_submitted'], true))
    <div class="fb-hero" style="padding-bottom:56px">
        <div class="wrap fb-hero-in">
            <span class="kick">Event feedback</span>
            <h1>Share your experience</h1>
            <p class="sub">We value every attendee's perspective on our events.</p>
        </div>
    </div>
    @endif

    <div class="fb-main">
        <div class="fb-shell reveal show">

            @if ($previewMode)
            <div class="fb-devbar">
                <strong>UI preview</strong>
                <span>Mock data — nothing is saved.</span>
                @if (Route::has('dev.feedback.preview'))
                <div class="fb-devtabs">
                    @foreach (['form' => 'Form', 'already_submitted' => 'Submitted', 'invalid' => 'Invalid', 'not_available' => 'Not open', 'missing_token' => 'No token'] as $key => $label)
                    <a href="{{ route('dev.feedback.preview', $key) }}" class="{{ $state === $key ? 'is-on' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <div class="fb-body">

                @if ($state === 'missing_token' || $state === 'invalid')
                <div class="fb-status">
                    <div class="fb-status-ic err">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                    </div>
                    <h2>{{ $state === 'missing_token' ? 'Link incomplete' : 'Link not valid' }}</h2>
                    <p>{{ $message ?? 'Invalid or expired feedback link. Please use the link from your confirmation email.' }}</p>
                    <div class="fb-status-actions">
                        <a class="btn btn-navy" href="{{ route('home') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                            Browse events
                        </a>
                    </div>
                </div>

                @elseif ($state === 'not_available')
                <div class="fb-status">
                    <div class="fb-status-ic warn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <h2>Feedback opens soon</h2>
                    <p>{{ $message ?? "Feedback is available after the event has ended. Check back shortly — we'll email you a link when it's ready." }}</p>
                    <div class="fb-status-actions">
                        @if ($slug)
                        <a class="btn btn-navy" href="{{ route('events.show', $slug) }}">View event details</a>
                        @endif
                        <a class="btn btn-ghost" href="{{ route('home') }}">Browse events</a>
                    </div>
                </div>

                @elseif ($state === 'already_submitted')
                <div class="fb-status">
                    <div class="fb-status-ic ok">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                    <h2>Thank you for your feedback</h2>
                    <p>We've received your response for this event. Your input helps us create better experiences for the community.</p>

                    @if ($existingFeedback)
                    @php $prevRating = (int) ($existingFeedback['rating'] ?? 0); @endphp
                    <div class="fb-summary">
                        <div class="fb-summary-lbl">Your submission</div>
                        @if ($prevRating > 0)
                        <div class="fb-prev-rating" aria-label="{{ $prevRating }} out of 5 stars">
                            @for ($i = 1; $i <= 5; $i++)
                            <svg viewBox="0 0 24 24" fill="{{ $i <= $prevRating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            @endfor
                            <span style="font-size:13px;font-weight:700;color:var(--t2);margin-left:8px;align-self:center">{{ $ratingLabels[$prevRating] ?? $prevRating . '/5' }}</span>
                        </div>
                        @endif
                        @if (!empty($existingFeedback['feedback']))
                        <p class="fb-prev-text">{{ $existingFeedback['feedback'] }}</p>
                        @endif
                    </div>
                    @endif

                    <div class="fb-status-actions">
                        <a class="btn btn-navy" href="{{ $eventUrl }}">Back to event</a>
                        <a class="btn btn-ghost" href="{{ route('home') }}">Browse more events</a>
                    </div>
                </div>

                @elseif ($state === 'form')

                @if (session('feedback_success'))
                <div class="fb-alert ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>{{ session('feedback_success') }}</span>
                </div>
                @endif

                @if ($errors->has('feedback_submit'))
                <div class="fb-alert err">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <span>{{ $errors->first('feedback_submit') }}</span>
                </div>
                @endif

                @if ($guestName !== '')
                <div class="fb-welcome">
                    <div class="fb-welcome-av" aria-hidden="true">{{ strtoupper(substr($guestName, 0, 1)) }}</div>
                    <div>
                        <b>Welcome back, {{ $guestName }}</b>
                        <p>Thanks for attending. A few minutes of your time helps us improve future events for everyone in the AMCOB community.</p>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ $previewMode && Route::has('dev.feedback.preview.store') ? route('dev.feedback.preview.store') : route('events.feedback.store', $slug) }}" id="feedbackForm" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="fb-section">
                        <div class="fb-section-hd">
                            <div class="fb-section-ic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div>
                                <h3>Rate your experience</h3>
                                <p>How would you overall rate this event?</p>
                            </div>
                        </div>

                        <div class="fb-rating-box">
                            <div class="fb-stars" id="starRating" role="radiogroup" aria-label="Rating 1 to 5 stars">
                                @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="fb-star-btn{{ $prefillRating >= $i ? ' is-on' : '' }}"
                                        data-star="{{ $i }}" aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }} — {{ $ratingLabels[$i] }}">
                                    <svg viewBox="0 0 24 24" fill="{{ $prefillRating >= $i ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </button>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="{{ $prefillRating ?: '' }}" required>
                            <div class="fb-rating-label" id="ratingLabel">{{ $prefillRating ? ($ratingLabels[$prefillRating] ?? '') : 'Select a rating' }}</div>
                            <div class="fb-rating-hint" id="ratingHint">{{ $prefillRating ? $prefillRating . ' out of 5 stars' : 'Click or tap a star to rate' }}</div>
                            @error('rating')<label class="error">{{ $message }}</label>@enderror
                        </div>
                    </div>

                    <div class="fb-section">
                        <div class="fb-section-hd">
                            <div class="fb-section-ic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div>
                                <h3>Tell us more</h3>
                                <p>What stood out? What could we do better?</p>
                            </div>
                        </div>

                        <div class="fb-fld">
                            <label for="feedbackText">Your feedback <span class="req">*</span></label>
                            <textarea id="feedbackText" name="feedback" required maxlength="5000"
                                      placeholder="Share highlights from the event, suggestions for improvement, or anything else you'd like us to know…">{{ $prefillFeedback }}</textarea>
                            <div class="fb-fld-foot">
                                <span class="fb-fld-hint" style="margin:0">Minimum 3 characters</span>
                                <span class="fb-char-count" id="charCount">0 / 5,000</span>
                            </div>
                            @error('feedback')<label class="error">{{ $message }}</label>@enderror
                        </div>
                    </div>

                    <div class="fb-section">
                        <div class="fb-section-hd">
                            <div class="fb-section-ic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <div>
                                <h3>Confirm your details</h3>
                                <p>Must match your registration information</p>
                            </div>
                        </div>

                        <div class="fb-fld">
                            <label for="feedbackEmail">Email address <span class="req">*</span></label>
                            <input type="email" id="feedbackEmail" name="email" value="{{ $prefillEmail }}"
                                   required maxlength="255" autocomplete="email" placeholder="you@example.com">
                            @error('email')<label class="error">{{ $message }}</label>@enderror
                        </div>

                        <div class="fb-r2">
                            <div class="fb-fld">
                                <label for="feedbackFirst">First name</label>
                                <input type="text" id="feedbackFirst" name="first_name" value="{{ $prefillFirst }}"
                                       maxlength="100" autocomplete="given-name" placeholder="Optional">
                                @error('first_name')<label class="error">{{ $message }}</label>@enderror
                            </div>
                            <div class="fb-fld">
                                <label for="feedbackLast">Last name</label>
                                <input type="text" id="feedbackLast" name="last_name" value="{{ $prefillLast }}"
                                       maxlength="100" autocomplete="family-name" placeholder="Optional">
                                @error('last_name')<label class="error">{{ $message }}</label>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="fb-actions">
                        <button type="submit" class="btn btn-gold fb-submit" id="feedbackSubmit">
                            Submit feedback
                        </button>
                        <p class="fb-note">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            One submission per attendee · Secure &amp; confidential
                        </p>
                    </div>
                </form>
                @endif

            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@if ($state === 'form')
<script>
(function () {
    var labels = { 1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Great', 5: 'Excellent' };
    var stars = document.querySelectorAll('.fb-star-btn');
    var input = document.getElementById('ratingInput');
    var labelEl = document.getElementById('ratingLabel');
    var hint    = document.getElementById('ratingHint');
    var form    = document.getElementById('feedbackForm');
    var submit  = document.getElementById('feedbackSubmit');
    var textarea = document.getElementById('feedbackText');
    var charCount = document.getElementById('charCount');

    function paintStars(n, hover) {
        stars.forEach(function (btn) {
            var v = parseInt(btn.dataset.star, 10);
            var on = v <= n;
            btn.classList.toggle('is-on', !hover && on);
            btn.classList.toggle('is-hover', hover && on);
            var svg = btn.querySelector('svg');
            if (svg) svg.setAttribute('fill', on ? 'currentColor' : 'none');
        });
    }

    function setRating(n) {
        input.value = String(n);
        paintStars(n, false);
        if (labelEl) labelEl.textContent = labels[n] || 'Select a rating';
        if (hint) hint.textContent = n + ' out of 5 stars';
    }

    stars.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setRating(parseInt(btn.dataset.star, 10));
        });
        btn.addEventListener('mouseenter', function () {
            paintStars(parseInt(btn.dataset.star, 10), true);
        });
    });

    var ratingBox = document.querySelector('.fb-rating-box');
    if (ratingBox) {
        ratingBox.addEventListener('mouseleave', function () {
            var current = parseInt(input.value, 10) || 0;
            paintStars(current, false);
        });
    }

    if (textarea && charCount) {
        function updateCount() {
            var len = textarea.value.length;
            charCount.textContent = len.toLocaleString() + ' / 5,000';
            charCount.classList.toggle('is-warn', len > 4500);
        }
        textarea.addEventListener('input', updateCount);
        updateCount();
    }

    if (form) {
        form.addEventListener('submit', function () {
            if (submit) {
                submit.disabled = true;
                submit.textContent = 'Submitting…';
            }
        });
    }

    if (input.value) {
        setRating(parseInt(input.value, 10));
    }
})();
</script>
@endif
@endpush
