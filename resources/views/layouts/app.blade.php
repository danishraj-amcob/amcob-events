<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AMCOB Events')</title>
    <link rel="icon" type="image/jpeg"   href="{{ asset('assets/img/favicon.jpg') }}">
    <link rel="apple-touch-icon"         href="{{ asset('assets/img/favicon.jpg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @include('partials.seo')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800;900&family=Source+Sans+3:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @stack('styles')
</head>

<body>

    @include('partials.header')

    {{-- Suppress global alerts when the event registration sidebar handles them --}}
    @if (session('success') && !session('registration_ticket'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any() && !$errors->has('registration') && !request()->routeIs('events.feedback', 'events.feedback.store'))
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    @yield('content')



    @include('partials.footer')


    <script>
        // reveal-on-scroll
        const obs = new IntersectionObserver(els => els.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('show'); obs.unobserve(e.target); }
        }), { threshold: 0.1 });
        document.querySelectorAll('.reveal:not(.show)').forEach(el => obs.observe(el));

        // Mobile hamburger menu
        (function () {
            const nav = document.querySelector('nav');
            const burger = document.querySelector('.burger');
            if (!nav || !burger) return;
            burger.addEventListener('click', function (e) {
                e.stopPropagation();
                nav.classList.toggle('nav-open');
            });
            document.addEventListener('click', function (e) {
                if (!e.target.closest('nav')) nav.classList.remove('nav-open');
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') nav.classList.remove('nav-open');
            });
        })();
    </script>
    @stack('scripts')
</body>

</html>
