<!-- NAV -->
<nav>
  <div class="wrap in">
    <a class="logo" href="{{ route('home') }}">
      <img src="{{ asset('assets/img/logo-white.webp') }}" alt="AMCOB">
    </a>
    <div class="navcta">
      @if (request()->routeIs('grow-your-business'))
        <a href="{{ route('home') }}" class="btn btn-gold btn-sm">EVENTS</a>
      @else
        <a href="{{ route('grow-your-business') }}" class="btn btn-gold btn-sm">GET THE APP</a>
      @endif
    </div>
  </div>
</nav>
