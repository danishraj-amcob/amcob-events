@php
$iconMap = [
    'Dinner Mixer'  => ['bg' => 'linear-gradient(135deg,#E4A63F,#CE8924)', 'icon' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/>'],
    'Dinner Mixers' => ['bg' => 'linear-gradient(135deg,#E4A63F,#CE8924)', 'icon' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/>'],
    'Networking'    => ['bg' => 'linear-gradient(135deg,#3a4b82,#2D3C69)', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
    'Workshop'      => ['bg' => 'linear-gradient(135deg,#E4A63F,#CE8924)', 'icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2zM22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>'],
    'Workshops'     => ['bg' => 'linear-gradient(135deg,#E4A63F,#CE8924)', 'icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2zM22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>'],
    'Gala'          => ['bg' => 'linear-gradient(135deg,#3a4b82,#2D3C69)', 'icon' => '<path d="M12 15a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM8.21 13.89 7 23l5-3 5 3-1.21-9.12"/>'],
    'Galas'         => ['bg' => 'linear-gradient(135deg,#3a4b82,#2D3C69)', 'icon' => '<path d="M12 15a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM8.21 13.89 7 23l5-3 5 3-1.21-9.12"/>'],
    'Galas & Awards'=> ['bg' => 'linear-gradient(135deg,#3a4b82,#2D3C69)', 'icon' => '<path d="M12 15a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM8.21 13.89 7 23l5-3 5 3-1.21-9.12"/>'],
    'Webinar'       => ['bg' => 'linear-gradient(135deg,#E4A63F,#CE8924)', 'icon' => '<path d="M23 7l-7 5 7 5V7zM1 5h15v14H1z"/>'],
    'Webinars'      => ['bg' => 'linear-gradient(135deg,#E4A63F,#CE8924)', 'icon' => '<path d="M23 7l-7 5 7 5V7zM1 5h15v14H1z"/>'],
    'Conference'    => ['bg' => 'linear-gradient(135deg,#3a4b82,#2D3C69)', 'icon' => '<path d="M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>'],
    'Conferences'   => ['bg' => 'linear-gradient(135deg,#3a4b82,#2D3C69)', 'icon' => '<path d="M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>'],
];
$defaultIcon = ['bg' => 'linear-gradient(135deg,#3a4b82,#2D3C69)', 'icon' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>'];
@endphp
{{-- BACKUP: Categories browse strip — temporarily retired in favor of the events filter sidebar.
     Re-enable via @include('home._categories') in events/index.blade.php --}}
@if (false && !empty($categories))
<section class="blk" id="categories" style="padding:40px 0 8px">
    <div class="wrap">
        <div class="sec-head" style="margin-bottom:2px">
            <div>
                <span class="eyebrow">Browse</span>
                <h2 class="sec-t">Explore by category</h2>
            </div>
        </div>
        <div class="cats reveal">
            @foreach ($categories as $cat)
            @php $meta = $iconMap[$cat['name']] ?? $iconMap[$cat['value']] ?? $iconMap[$cat['label']] ?? $defaultIcon; @endphp
            <a class="cat" href="{{ route('home', ['tag' => $cat['value']]) }}" data-tag="{{ $cat['value'] }}">
                <div class="ci" style="background:{{ $meta['bg'] }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $meta['icon'] !!}</svg>
                </div>
                <div class="ct">
                    <b>{{ $cat['label'] }}</b>
                    <span>{{ isset($cat['count']) ? $cat['count'] . ' ' . Str::plural('event', $cat['count']) : 'Events' }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
