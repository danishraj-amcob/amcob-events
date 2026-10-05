{{-- Hero slider — featured discovery events, or a placeholder when none qualify --}}
@php
    $heroEvents = is_array($dxEvents ?? null) ? $dxEvents : [];
    $heroFeatured = is_array($dxFeatured ?? null) ? $dxFeatured : [];
    $heroSlides = collect($heroFeatured)->filter(fn ($e) => in_array($e['status'] ?? '', ['upcoming', 'live'], true));
    if ($heroSlides->isEmpty()) {
        $heroSlides = collect($heroEvents)->filter(fn ($e) => !empty($e['featured']) && in_array($e['status'] ?? '', ['upcoming', 'live'], true));
    }
    if ($heroSlides->isEmpty()) {
        $heroSlides = collect($heroEvents)->filter(fn ($e) => ($e['status'] ?? '') === 'upcoming');
    }
    $heroIsEmpty = $heroSlides->isEmpty();
@endphp
<header class="hero" id="dxHero">
  <div class="heroslider{{ $heroIsEmpty ? ' is-empty' : '' }}">
    <div class="slides" id="heroSlides">
      @if ($heroIsEmpty)
        <div class="slide slide-empty active" id="heroEmpty">
          <div class="sbg" aria-hidden="true"></div>
          <div class="sov" aria-hidden="true"></div>
          <div class="sin">
            <span class="seye"><span class="sdot"></span>Mixers · Workshops · Conferences</span>
            <h2>Where the chapter meets</h2>
            <p class="slead">Dinner mixers, networking nights and workshops across every AMCOB chapter.</p>
            <div class="scta">
              <a class="btn btn-gold" href="{{ route('grow-your-business') }}">Get the app</a>
              <a class="btn btn-glass" href="#events">See the calendar</a>
            </div>
          </div>
        </div>
      @endif
    </div>
    <button class="hs-arrow prev{{ $heroIsEmpty ? ' is-hidden' : '' }}" id="hsPrev" type="button" aria-label="Previous" @if($heroIsEmpty) hidden @endif>‹</button>
    <button class="hs-arrow next{{ $heroIsEmpty ? ' is-hidden' : '' }}" id="hsNext" type="button" aria-label="Next" @if($heroIsEmpty) hidden @endif>›</button>
    <div class="hs-dots{{ $heroIsEmpty ? ' is-hidden' : '' }}" id="heroDots" @if($heroIsEmpty) hidden @endif></div>
  </div>
</header>
