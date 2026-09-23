<!-- NAV -->
{{-- Pages that open with a full-bleed dark hero get a transparent nav laid over it --}}
<nav @class(['nav-overlay' => request()->routeIs('home', 'grow-your-business')])>
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
@if (request()->routeIs('home', 'grow-your-business'))
  <script>
    // Overlay nav: transparent over the hero, blurred backdrop once the page is scrolled
    (() => {
      const nav = document.querySelector('nav.nav-overlay');
      if (!nav) return;
      let ticking = false;
      const update = () => {
        nav.classList.toggle('is-scrolled', window.scrollY > 8);
        ticking = false;
      };
      window.addEventListener('scroll', () => {
        if (!ticking) {
          ticking = true;
          requestAnimationFrame(update);
        }
      }, { passive: true });
      update();
    })();
  </script>
@endif
