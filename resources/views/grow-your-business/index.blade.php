<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>AMCOB — Grow Your Business With AMCOB Community</title>
    <meta name="description"
        content="Meet the right people, collaborate across chapters and grow — Allied Muslim Chamber of Business.">
    <link rel="canonical" href="{{ route('grow-your-business') }}">

    <meta property="og:site_name" content="AMCOB">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="AMCOB — Grow Your Business With AMCOB Community">
    <meta property="og:description"
        content="Meet the right people, collaborate across chapters and grow — Allied Muslim Chamber of Business.">
    <meta property="og:url" content="{{ route('grow-your-business') }}">
    <meta property="og:image" content="{{ asset('assets/img/home/benefitsSecImg-2x.webp') }}">
    <meta property="og:image:width" content="800">
    <meta property="og:image:height" content="566">
    <meta property="og:image:alt" content="AMCOB — Grow Your Business With AMCOB Community">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AMCOB — Grow Your Business With AMCOB Community">
    <meta name="twitter:description"
        content="Meet the right people, collaborate across chapters and grow — Allied Muslim Chamber of Business.">
    <meta name="twitter:image" content="{{ asset('assets/img/home/benefitsSecImg-2x.webp') }}">
    <meta name="twitter:image:alt" content="AMCOB — Grow Your Business With AMCOB Community">

    <link rel="icon" type="image/jpeg" href="{{ asset('assets/img/favicon.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon.jpg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/grow-your-business.css') }}?v={{ filemtime(public_path('assets/css/grow-your-business.css')) }}">

    @php
        $growJsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => 'AMCOB',
                    'url' => 'https://amcob.org',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('assets/img/logo-white.webp'),
                    ],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    'name' => 'AMCOB',
                    'operatingSystem' => 'ANDROID, IOS',
                    'applicationCategory' => 'BusinessApplication',
                    'url' => route('grow-your-business'),
                    'description' =>
                        'Meet the right people, collaborate across chapters and grow — Allied Muslim Chamber of Business.',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'USD',
                    ],
                    'sameAs' => [
                        'https://apps.apple.com/us/app/amcob/id6793501674',
                        'https://play.google.com/store/apps/details?id=com.amcob.events.org',
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    'mainEntity' => [
                        [
                            '@type' => 'Question',
                            'name' => 'Who can join AMCOB?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' =>
                                    'AMCOB is for members of the Allied Muslim Chamber of Business and their chapters — business owners, founders, investors and professionals across the network.',
                            ],
                        ],
                        [
                            '@type' => 'Question',
                            'name' => 'Is the app free to use?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' =>
                                    'Yes. The AMCOB app is free for all verified members — simply download it and confirm your membership to get started.',
                            ],
                        ],
                        [
                            '@type' => 'Question',
                            'name' => 'How does AI networking work?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' =>
                                    'Tell AMCOB your goals and industry, and the app ranks members worth meeting by shared interests, industry and mutual connections.',
                            ],
                        ],
                        [
                            '@type' => 'Question',
                            'name' => "Can I manage my chapter's events?",
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' =>
                                    'Chapter organizers can create events, sell tickets, track RSVPs and message attendees directly from the app.',
                            ],
                        ],
                        [
                            '@type' => 'Question',
                            'name' => 'Which platforms are supported?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' =>
                                    'AMCOB is available on both Android and iOS, with your profile and connections synced across devices.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($growJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>

<body class="gyb-page" id="top">

    @include('partials.header')

    <main id="main">

        <!-- ============ HERO ============ -->
        <section class="hero" id="community">
            <div class="hero-glow" aria-hidden="true"></div>
            <div class="container hero-inner" data-hero>
                <div class="hero-copy">
                    <span class="hero-eyebrow" style="--d: 0">
                        <span class="hero-eyebrow-dot" aria-hidden="true"></span>
                        The official AMCOB member app
                    </span>
                    <h1 class="hero-title" style="--d: 1">
                        <span class="hero-title-line">Grow Your Business With</span>
                        <span class="hero-title-accent">AMCOB Community</span>
                    </h1>
                    <p class="hero-sub" style="--d: 2">Meet the right people, collaborate across chapters and grow — all in one app for
                        the
                        Allied Muslim Chamber of Business.</p>
                    <div class="store-buttons" style="--d: 3">
                        <a href="https://apps.apple.com/us/app/amcob/id6793501674" target="_blank" rel="noopener" class="store-btn">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M16.7 12.4c0-2.7 2.2-4 2.3-4.1-1.3-1.8-3.2-2.1-3.9-2.1-1.7-.2-3.2 1-4.1 1-.9 0-2.1-.9-3.5-.9-1.8 0-3.5 1-4.4 2.6-1.9 3.3-.5 8.1 1.3 10.8.9 1.3 2 2.8 3.4 2.7 1.4-.1 1.9-.9 3.5-.9s2.1.9 3.5.8c1.5 0 2.4-1.3 3.3-2.6.9-1.4 1.3-2.8 1.3-2.9-.1 0-2.6-1-2.7-3.9zM14.1 4.5c.7-.9 1.2-2.1 1.1-3.3-1.1 0-2.4.7-3.1 1.6-.7.8-1.3 2-1.1 3.2 1.2.1 2.4-.6 3.1-1.5z" />
                            </svg>
                            <span><small>Download on the</small>App Store</span>
                        </a>
                        <a href="https://play.google.com/store/apps/details?id=com.amcob.events.org" target="_blank"
                            rel="noopener" class="store-btn">
                            <svg viewBox="0 0 512 512" aria-hidden="true">
                                <path
                                    d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z" />
                            </svg>
                            <span><small>GET IT ON</small>Google Play</span>
                        </a>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="orbit-rings" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <div class="floating-card card-community" data-depth="26" style="--d: 0" aria-hidden="true">
                        <div class="fc-top">
                            <span class="fc-dot"></span>
                            <strong>Community</strong>
                            <span class="pill pill-green">+2.4k</span>
                        </div>
                        <p class="fc-headline">1,204 posts today</p>
                        <svg class="line-draw hero-line" width="180" height="30" viewBox="0 0 180 30"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="line-draw-path"
                                d="M20.7692 25.3845L41.5385 18.4614L60 23.0768L80.7692 10.3845L101.538 16.1537L122.308 5.76912L143.077 11.5383L159.231 3.46143"
                                stroke="#5FE0A8" stroke-width="2.88462" />
                        </svg>

                    </div>

                    <div class="phone phone-hero phone-hero-frame" data-depth="12">
                        <span class="phone-hero-notch" aria-hidden="true"></span>
                        <div class="phone-hero-screen">
                            <img src="{{ asset('assets/img/home/home-1x.webp') }}"
                                srcset="{{ asset('assets/img/home/home-1x.webp') }} 1x, {{ asset('assets/img/home/home-2x.webp') }} 2x"
                                width="330" height="695" alt="AMCOB app on a mobile phone" aria-hidden="true"
                                loading="eager" fetchpriority="high">
                        </div>
                        <span class="phone-hero-gloss" aria-hidden="true"></span>
                    </div>

                    <div class="floating-card card-summit" data-depth="34" style="--d: 1" aria-hidden="true">
                        <span class="avatar avatar-sm avatar-blue"></span>
                        <div style="display: flex; flex-direction: column; gap: 0px;"><strong>AMCOB
                                Summit</strong><small>Dubai ·
                                Nov 2026</small></div>
                    </div>

                    <div class="floating-card card-profile" data-depth="20" style="--d: 2" aria-hidden="true">
                        <span class="avatar avatar-md avatar-navy">SH</span>
                        <div style="display: flex; flex-direction: column; gap: 0px;"><strong>Sana
                                Habib</strong><small>Partner ·
                                Nexa</small></div>
                        <span class="pct pct-lg">94%</span>
                    </div>
                </div>
            </div>

            <div class="container">
                <ul class="pillars">
                    <li><strong>Connect</strong>
                        <p>Meet members across every chapter, by industry and interest.</p>
                    </li>
                    <li><strong>Collaborate</strong>
                        <p>Message, meet and work together in channels and events.</p>
                    </li>
                    <li><strong>Grow</strong>
                        <p>Turn connections into partnerships and lasting relationships.</p>
                    </li>
                </ul>
            </div>
        </section>

        <!-- ============ 3 STEPS ============ -->
        <section class="steps">
            <div class="container">
                <header class="section-head">
                    <h2>Get started in 3 simple steps</h2>
                    <div class="dash-container">
                        <span class="dash-white"></span>
                        <span class="dash"></span>
                    </div>
                    <p>It only takes a few minutes — join on your phone, connect with members and grow across every
                        AMCOB chapter.
                    </p>
                </header>

                <div class="steps-grid">
                    <div class="step">
                        <span class="step-icon step-icon-blue">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 3v12m0 0l-4-4m4 4l4-4" />
                                <path d="M5 17v2a2 2 0 002 2h10a2 2 0 002-2v-2" />
                            </svg>
                        </span>
                        <h3>Download &amp; Join</h3>
                        <p>Get the app and verify your AMCOB membership in minutes.</p>
                    </div>
                    <div class="step">
                        <span class="step-icon step-icon-gold">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="9" cy="8" r="3.2" />
                                <path d="M2.5 20c0-3.6 3-6.2 6.5-6.2s6.5 2.6 6.5 6.2" />
                                <path d="M16 8.2a3 3 0 110 5.9" />
                                <path d="M17.5 14c2.5.4 4 2.4 4 5.6" />
                            </svg>
                        </span>
                        <h3>Connect &amp; Network</h3>
                        <p>Get AI-matched to members worth meeting across chapters.</p>
                    </div>
                    <div class="step">
                        <span class="step-icon step-icon-green">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 17l6-6 4 4 8-8" />
                                <path d="M15 6h6v6" />
                            </svg>
                        </span>
                        <h3>Collaborate &amp; Grow</h3>
                        <p>Join events and turn introductions into real business.</p>
                    </div>
                </div>

                <!-- ============ EVERYTHING YOUR COMMUNITY NEEDS ============ -->
                <div class="needs-card" data-reveal-group>
                    <div class="needs-copy" data-reveal style="--d: 0">
                        <h2 id="needs-title">Everything your community needs</h2>
                        <p>Discover what's happening across chapters — and jump into the conversations and events
                            that matter to you.</p>
                        <div class="needs-actions">
                            <a href="#download" class="btn btn-gold">EXPLORE APP</a>
                            <a href="#features" class="explore-more">EXPLORE MORE <span class="explore-more-arrow" aria-hidden="true">→</span></a>
                        </div>
                    </div>

                    <ul class="needs-stats" aria-labelledby="needs-title">
                        <li data-reveal style="--d: 1">
                            <a class="needs-stat" href="#download">
                                <span class="needs-stat-figure"><span class="counter" data-target="120">0</span><span class="needs-stat-plus">+</span></span>
                                <span class="needs-stat-label">Chapters <span class="needs-stat-arrow" aria-hidden="true">→</span></span>
                                <span class="needs-stat-desc">Find the chapter closest to you and see what's on its calendar.</span>
                            </a>
                        </li>
                        <li data-reveal style="--d: 2">
                            <a class="needs-stat" href="#features">
                                <span class="needs-stat-figure"><span class="counter" data-target="50">0</span>K<span class="needs-stat-plus">+</span></span>
                                <span class="needs-stat-label">Members <span class="needs-stat-arrow" aria-hidden="true">→</span></span>
                                <span class="needs-stat-desc">Professionals and business owners, introduced by shared goals.</span>
                            </a>
                        </li>
                        <li data-reveal style="--d: 3">
                            <a class="needs-stat" href="#features">
                                <span class="needs-stat-figure"><span class="counter" data-target="500">0</span><span class="needs-stat-plus">+</span></span>
                                <span class="needs-stat-label">Events <span class="needs-stat-arrow" aria-hidden="true">→</span></span>
                                <span class="needs-stat-desc">Meetups, mixers and summits — RSVP in a tap.</span>
                            </a>
                        </li>
                        <li data-reveal style="--d: 4">
                            <a class="needs-stat" href="#benefits">
                                <span class="needs-stat-figure"><span class="counter" data-target="8">0</span>K<span class="needs-stat-plus">+</span></span>
                                <span class="needs-stat-label">Founders <span class="needs-stat-arrow" aria-hidden="true">→</span></span>
                                <span class="needs-stat-desc">Owners swapping playbooks and turning introductions into business.</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ============ FEATURES ============ -->
        <section class="features" id="features">
    <div class="container">

        <header class="section-head">
            <span class="bg-word">FEATURES</span>
            <span class="bg-word bg-word-stroke" aria-hidden="true">FEATURES</span>

            <h2>Pro-level tools to grow<br>your network in one app</h2>

            <div class="dash-container">
                <span class="dash-white"></span>
                <span class="dash"></span>
            </div>

            <p>
                Manage your profile, discover members, join events and message your connections —
                on Android and iOS.
            </p>
        </header>


        <!-- STACKING CARDS: each card pins near the top and the next one slides over it -->
        <div class="features-stack" data-feature-stack>

            <article class="feature-card" style="--i: 0; --accent-rgb: 242, 192, 120">
                <div class="feature-card-inner">
                    <div class="feature-card-media">
                        <img src="{{ asset('assets/img/home/smartNetworkingImg-1x.webp') }}"
                            srcset="{{ asset('assets/img/home/smartNetworkingImg-1x.webp') }} 1x,
                                    {{ asset('assets/img/home/smartNetworkingImg-2x.webp') }} 2x"
                            width="510"
                            height="606"
                            alt="Smart Networking Image"
                            aria-hidden="true"
                            loading="lazy">
                    </div>

                    <div class="feature-copy">
                        <span class="feature-card-index">01 <span>/ 02</span></span>

                        <h3 class="text-yellow">
                            <span class="bullet bullet-gold"></span>
                            Smart networking
                        </h3>

                        <p>
                            Tell AMCOB your goals and it introduces you to the members worth knowing —
                            ranked by shared interests, industry and mutual connections.
                        </p>

                        <p>
                            Connect, message and book meetings without ever leaving the app.
                        </p>
                    </div>
                </div>
            </article>

            <article class="feature-card feature-card-rev" style="--i: 1; --accent-rgb: 143, 176, 238">
                <div class="feature-card-inner">
                    <div class="feature-card-media">
                        <img src="{{ asset('assets/img/home/oneLivingCommunityFieldImg2.webp') }}"
                            width="500"
                            height="597"
                            alt="Living Community Image"
                            aria-hidden="true"
                            loading="lazy">
                    </div>

                    <div class="feature-copy">
                        <span class="feature-card-index">02 <span>/ 02</span></span>

                        <h3 class="text-blue">
                            <span class="bullet bullet-blue"></span>
                            One living community feed
                        </h3>

                        <p>
                            Announcements, wins and conversations from every chapter —
                            all in one always-updated feed.
                        </p>

                        <p>
                            Share a post, start a discussion or celebrate a member,
                            and never miss what's happening across the network.
                        </p>
                    </div>
                </div>
            </article>

        </div>

    </div>
</section>

        <!-- ============ SEE HOW IT WORKS ============ -->
        {{-- <section class="how-it-works" id="how-it-works">
            <div class="container">
                <header class="section-head">
                    <h2 class="gold-heading">See how it works</h2>
                    <div class="dash-container">
                        <span class="dash-white"></span>
                        <span class="dash"></span>
                    </div>
                    <p>One tap into the whole community — the feed, your network, messages and events, together.</p>
                </header>

                <div class="showcase">

                    <button class="play-btn" type="button" aria-label="Play video">
                        <svg viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </button>

                    <img src="{{ asset('assets/img/home/seeHowItsWorksImg.webp') }}" alt="" aria-hidden="true" loading="lazy"
                        class="phone phone-showcase phone-show-c">

                </div>
            </div>
        </section> --}}

        <!-- ============ BENEFITS ============ -->
        <section class="benefits" id="benefits">
            <div class="container">
                <header class="section-head">
                    <span class="bg-word">BENEFITS</span>
                    <span class="bg-word bg-word-stroke" aria-hidden="true">BENEFITS</span>
                    <h2>You deserve easy access<br>to your community</h2>
                    <div class="dash-container">
                        <span class="dash-white"></span>
                        <span class="dash"></span>
                    </div>
                </header>

                <div class="benefits-showcase" data-reveal-group>
                    <ul class="benefit-list benefit-list-l">
                        <li class="benefit-card" data-reveal
                            style="--d: 1; --accent: var(--blue-500); --accent-rgb: 143, 176, 238">
                            <div class="benefit-text">
                                <h3>Real-time feed</h3>
                                <p>Every chapter update as it happens.</p>
                            </div>
                        </li>
                        <li class="benefit-card" data-reveal data-benefit="verified"
                            style="--d: 3; --accent: var(--green-500); --accent-rgb: 95, 224, 168">
                            <div class="benefit-text">
                                <h3>Secure &amp; verified</h3>
                                <p>Members-only, verified access.</p>
                            </div>
                        </li>
                        <li class="benefit-card" data-reveal
                            style="--d: 5; --accent: var(--gold-400); --accent-rgb: 242, 192, 120">
                            <div class="benefit-text">
                                <h3>Member directory</h3>
                                <p>Find anyone in seconds.</p>
                            </div>
                        </li>
                    </ul>

                    <div class="benefits-stage" data-reveal style="--d: 0" aria-hidden="true">
                        <span class="stage-orb stage-orb-blue"></span>
                        <span class="stage-orb stage-orb-gold"></span>
                        <img class="stage-person stage-person-man"
                            src="{{ asset('assets/img/home/benefitsMan-1x.webp') }}"
                            srcset="{{ asset('assets/img/home/benefitsMan-1x.webp') }} 1x, {{ asset('assets/img/home/benefitsMan-2x.webp') }} 2x"
                            width="240" height="357" alt="" loading="lazy" decoding="async">
                        <img class="stage-person stage-person-woman"
                            src="{{ asset('assets/img/home/benefitsWoman-1x.webp') }}"
                            srcset="{{ asset('assets/img/home/benefitsWoman-1x.webp') }} 1x, {{ asset('assets/img/home/benefitsWoman-2x.webp') }} 2x"
                            width="280" height="307" alt="" loading="lazy" decoding="async">
                        <span class="stage-chip stage-chip-verified" data-chip="verified">
                            <span class="stage-chip-icon"><svg viewBox="8 8 16 16">
                                    <path d="M16 10l5.333 2v3.333C21.333 18.667 19 20.667 16 22c-3-1.333-5.333-3.333-5.333-6.667V12L16 10z" />
                                    <path d="M14 16l1.333 1.333L18 14.667" />
                                </svg></span>
                            Verified member
                        </span>
                        <span class="stage-chip stage-chip-rsvp" data-chip="rsvp">
                            <span class="stage-chip-icon"><svg viewBox="8 8 16 16">
                                    <path d="M18 11.333v1.334M18 15.333v1.334M18 19.333v1.334M11.333 11.333h9.334c.736 0 1.333.597 1.333 1.334v2a1.333 1.333 0 000 2.666v2c0 .737-.597 1.334-1.333 1.334h-9.334A1.333 1.333 0 0110 19.333v-2a1.333 1.333 0 000-2.666v-2c0-.737.597-1.334 1.333-1.334z" />
                                </svg></span>
                            RSVP confirmed
                        </span>
                    </div>

                    <ul class="benefit-list benefit-list-r">
                        <li class="benefit-card" data-reveal
                            style="--d: 2; --accent: var(--gold-400); --accent-rgb: 242, 192, 120">
                            <div class="benefit-text">
                                <h3>Easy onboarding</h3>
                                <p>Set up your profile in minutes.</p>
                            </div>
                        </li>
                        <li class="benefit-card" data-reveal
                            style="--d: 4; --accent: var(--blue-500); --accent-rgb: 143, 176, 238">
                            <div class="benefit-text">
                                <h3>Messaging &amp; meetings</h3>
                                <p>Chat and book 1:1s in-app.</p>
                            </div>
                        </li>
                        <li class="benefit-card" data-reveal data-benefit="rsvp"
                            style="--d: 6; --accent: var(--green-500); --accent-rgb: 95, 224, 168">
                            <div class="benefit-text">
                                <h3>Events &amp; tickets</h3>
                                <p>RSVP and check in with QR.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                {{-- <ul class="stat-row">
                    <li><strong>+<span class="counter" data-target="120">0</span></strong><span>Chapters</span><i
                            class="under under-blue"></i></li>
                    <li><strong>+<span class="counter" data-target="50">0</span>K</strong><span>Members</span><i
                            class="under under-gold"></i></li>
                    <li><strong>+<span class="counter" data-target="1">0</span>M</strong><span>Connections</span><i
                            class="under under-blue"></i></li>
                </ul> --}}
            </div>
        </section>

        <!-- ============ GET THE APP NOW ============ -->
        <section class="get-app" id="download">
            <div class="container">
                <div class="get-app-card" data-reveal-group>
                    <span class="get-app-rings" aria-hidden="true"></span>

                    <div class="get-app-copy">
                        <span class="get-app-eyebrow" data-reveal style="--d: 0">
                            <span class="get-app-eyebrow-dot" aria-hidden="true"></span>
                            Available on iOS &amp; Android
                        </span>
                        <h2 data-reveal style="--d: 1">Get the app <span class="get-app-accent">now</span></h2>
                        <p data-reveal style="--d: 2">Download the AMCOB app and step into the community — connect,
                            collaborate and grow, wherever you are.</p>
                        <div class="store-buttons" data-reveal style="--d: 3">
                            <a href="https://apps.apple.com/us/app/amcob/id6793501674" target="_blank"
                                rel="noopener" class="store-btn store-btn-dark">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M16.7 12.4c0-2.7 2.2-4 2.3-4.1-1.3-1.8-3.2-2.1-3.9-2.1-1.7-.2-3.2 1-4.1 1-.9 0-2.1-.9-3.5-.9-1.8 0-3.5 1-4.4 2.6-1.9 3.3-.5 8.1 1.3 10.8.9 1.3 2 2.8 3.4 2.7 1.4-.1 1.9-.9 3.5-.9s2.1.9 3.5.8c1.5 0 2.4-1.3 3.3-2.6.9-1.4 1.3-2.8 1.3-2.9-.1 0-2.6-1-2.7-3.9zM14.1 4.5c.7-.9 1.2-2.1 1.1-3.3-1.1 0-2.4.7-3.1 1.6-.7.8-1.3 2-1.1 3.2 1.2.1 2.4-.6 3.1-1.5z" />
                                </svg>
                                <span><small>Download on the</small>App Store</span>
                            </a>
                            <a href="https://play.google.com/store/apps/details?id=com.amcob.events.org"
                                target="_blank" rel="noopener" class="store-btn store-btn-dark">
                                <svg viewBox="0 0 512 512" aria-hidden="true">
                                    <path
                                        d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z" />
                                </svg>
                                <span><small>GET IT ON</small>Google Play</span>
                            </a>
                        </div>
                        <ul class="get-app-perks" data-reveal style="--d: 4">
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 12.5l4.5 4.5L19 7.5" />
                                </svg>
                                Free for verified members
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 12.5l4.5 4.5L19 7.5" />
                                </svg>
                                Synced across devices
                            </li>
                        </ul>
                    </div>

                    <div class="get-app-visual" aria-hidden="true">
                        <div class="get-app-phone-wrap">
                            <div class="get-app-phone">
                                <div class="get-app-screen">
                                    <span class="get-app-status"><span>9:41</span><span>100%</span></span>
                                    <img src="{{ asset('assets/img/home/getTheAppScreen-1x.webp') }}"
                                        srcset="{{ asset('assets/img/home/getTheAppScreen-1x.webp') }} 1x, {{ asset('assets/img/home/getTheAppScreen-2x.webp') }} 2x"
                                        width="218" height="348" alt="" loading="lazy" decoding="async">
                                </div>
                                <span class="get-app-notch"></span>
                                <span class="get-app-gloss"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ FAQ ============ -->
                <div class="faq-section" id="faq" data-reveal-group>
                    <div class="faq-side">
                        <div class="faq-head" data-reveal style="--d: 0">
                            {{-- <span class="faq-eyebrow">Help center</span> --}}
                            <h2>Frequently asked<br>questions</h2>
                            <div class="dash-container">
                                <span class="dash-white"></span>
                                <span class="dash"></span>
                            </div>
                            <p class="faq-intro">Quick answers about joining AMCOB, using the app and running your chapter.</p>
                        </div>

                        <div class="faq-help" data-reveal style="--d: 2">
                            <div class="faq-help-copy">
                                <h3>Still have questions?</h3>
                                <p>Our team is happy to help — drop us a line.</p>
                                <a href="mailto:info@amcob.org" class="faq-help-link">info@amcob.org <span aria-hidden="true">→</span></a>
                            </div>
                            <div class="faq-photo"><img src="{{ asset('assets/img/home/woman-faq-1x.webp') }}"
                                    srcset="{{ asset('assets/img/home/woman-faq-1x.webp') }} 1x, {{ asset('assets/img/home/woman-faq-2x.webp') }} 2x"
                                    width="220" height="241" alt="AMCOB member smiling" loading="lazy"></div>
                        </div>
                    </div>

                    <div class="faq-list">
                        <div class="faq-item is-open" data-reveal style="--d: 1">
                            <button class="faq-q" type="button" id="faq-q-1" aria-expanded="true" aria-controls="faq-a-1">
                                <span class="faq-num" aria-hidden="true">01</span>
                                <span class="faq-q-text">Who can join AMCOB?</span>
                                <span class="faq-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg></span>
                            </button>
                            <div class="faq-a" id="faq-a-1" role="region" aria-labelledby="faq-q-1">
                                <div class="faq-a-inner">
                                    <p>AMCOB is for members of the Allied Muslim Chamber of Business and their chapters — business owners, founders, investors and professionals across the network.</p>
                                </div>
                            </div>
                        </div>
                        <div class="faq-item" data-reveal style="--d: 2">
                            <button class="faq-q" type="button" id="faq-q-2" aria-expanded="false" aria-controls="faq-a-2">
                                <span class="faq-num" aria-hidden="true">02</span>
                                <span class="faq-q-text">Is the app free to use?</span>
                                <span class="faq-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg></span>
                            </button>
                            <div class="faq-a" id="faq-a-2" role="region" aria-labelledby="faq-q-2">
                                <div class="faq-a-inner">
                                    <p>Yes. The AMCOB app is free for all verified members — simply download it and confirm your membership to get started.</p>
                                </div>
                            </div>
                        </div>
                        <div class="faq-item" data-reveal style="--d: 3">
                            <button class="faq-q" type="button" id="faq-q-3" aria-expanded="false" aria-controls="faq-a-3">
                                <span class="faq-num" aria-hidden="true">03</span>
                                <span class="faq-q-text">How does AI networking work?</span>
                                <span class="faq-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg></span>
                            </button>
                            <div class="faq-a" id="faq-a-3" role="region" aria-labelledby="faq-q-3">
                                <div class="faq-a-inner">
                                    <p>Tell AMCOB your goals and industry, and the app ranks members worth meeting by shared interests, industry and mutual connections.</p>
                                </div>
                            </div>
                        </div>
                        <div class="faq-item" data-reveal style="--d: 4">
                            <button class="faq-q" type="button" id="faq-q-4" aria-expanded="false" aria-controls="faq-a-4">
                                <span class="faq-num" aria-hidden="true">04</span>
                                <span class="faq-q-text">Can I manage my chapter's events?</span>
                                <span class="faq-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg></span>
                            </button>
                            <div class="faq-a" id="faq-a-4" role="region" aria-labelledby="faq-q-4">
                                <div class="faq-a-inner">
                                    <p>Chapter organizers can create events, sell tickets, track RSVPs and message attendees directly from the app.</p>
                                </div>
                            </div>
                        </div>
                        <div class="faq-item" data-reveal style="--d: 5">
                            <button class="faq-q" type="button" id="faq-q-5" aria-expanded="false" aria-controls="faq-a-5">
                                <span class="faq-num" aria-hidden="true">05</span>
                                <span class="faq-q-text">Which platforms are supported?</span>
                                <span class="faq-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg></span>
                            </button>
                            <div class="faq-a" id="faq-a-5" role="region" aria-labelledby="faq-q-5">
                                <div class="faq-a-inner">
                                    <p>AMCOB is available on both Android and iOS, with your profile and connections synced across devices.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ NEWSLETTER ============ -->
        {{-- <section class="newsletter">
            <div class="container">
                <h2>Stay connected with the AMCOB community — news, events and member spotlights.</h2>
                <form class="subscribe-form" id="subscribe-form">
                    <input type="email" name="email" placeholder="Your Email" required
                        aria-label="Email address">
                    <button type="submit" class="btn btn-gold">SUBSCRIBE</button>
                </form>
                <p class="form-note" id="form-note" role="status"></p>
            </div>
        </section> --}}

    </main>

    @include('partials.footer')

    <button class="to-top" id="to-top" type="button" aria-label="Back to top">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 19V5M5 12l7-7 7 7" />
        </svg>
    </button>



    <script src="{{ asset('assets/js/grow-your-business.js') }}?v={{ filemtime(public_path('assets/js/grow-your-business.js')) }}"></script>

</body>

</html>
