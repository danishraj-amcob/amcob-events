@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
@if (session('cache_cleared'))
    <div class="alert" style="background:#dcfce7;color:#166534;margin-bottom:20px">
        Event cache cleared — the site will fetch fresh data from the ERP on the next page load.
    </div>
@endif

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Total registrations</div>
        <div class="value">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="stat-card confirmed">
        <div class="label">Confirmed</div>
        <div class="value">{{ number_format($stats['confirmed']) }}</div>
    </div>
    <div class="stat-card failed">
        <div class="label">Failed</div>
        <div class="value">{{ number_format($stats['failed']) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Quick actions</h2>
    </div>
    <div style="padding:20px;display:flex;align-items:center;gap:20px;flex-wrap:wrap">
        <a href="{{ route('admin.registrations.index') }}" style="font-size:13px;font-weight:600;color:var(--navy)">View all registrations →</a>

        <form method="POST" action="{{ route('admin.cache.clear') }}" onsubmit="return confirm('Clear the event cache? The site will re-fetch all events from the ERP on the next load.')">
            @csrf
            <button type="submit" style="background:none;border:1px solid var(--line);border-radius:7px;padding:7px 14px;font-family:inherit;font-size:13px;font-weight:600;color:var(--t2);cursor:pointer">
                Clear event cache
            </button>
        </form>
    </div>
</div>
@endsection
