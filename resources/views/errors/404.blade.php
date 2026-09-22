@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:60px 20px;text-align:center">
    <div>
        <div style="font-size:96px;font-weight:900;line-height:1;background:linear-gradient(135deg,#E4A63F,#CE8924);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-family:'Poppins',sans-serif;margin-bottom:8px">
            404
        </div>
        <h1 style="font-size:24px;font-weight:800;color:var(--navy);margin-bottom:12px">
            This event doesn't exist
        </h1>
        <p style="color:var(--t2);font-size:15px;max-width:380px;margin:0 auto 32px">
            The page you're looking for may have been removed, renamed, or the event is no longer public.
        </p>
        <a href="{{ route('home') }}" class="btn btn-navy" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="width:16px;height:16px">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
            Back to all events
        </a>
    </div>
</div>
@endsection
