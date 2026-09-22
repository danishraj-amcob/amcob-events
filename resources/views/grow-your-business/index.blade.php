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
            <div class="container hero-inner">
                <div class="hero-copy">
                    <h1 class="hero-title">
                        Grow Your Business With
                        <span>AMCOB Community</span>
                        <span></span>
                    </h1>
                    <p class="hero-sub">Meet the right people, collaborate across chapters and grow — all in one app for
                        the
                        Allied Muslim Chamber of Business.</p>
                    <div class="store-buttons">
                        <a href="https://apps.apple.com/us/app/amcob/id6793501674" target="_blank" class="store-btn">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M16.7 12.4c0-2.7 2.2-4 2.3-4.1-1.3-1.8-3.2-2.1-3.9-2.1-1.7-.2-3.2 1-4.1 1-.9 0-2.1-.9-3.5-.9-1.8 0-3.5 1-4.4 2.6-1.9 3.3-.5 8.1 1.3 10.8.9 1.3 2 2.8 3.4 2.7 1.4-.1 1.9-.9 3.5-.9s2.1.9 3.5.8c1.5 0 2.4-1.3 3.3-2.6.9-1.4 1.3-2.8 1.3-2.9-.1 0-2.6-1-2.7-3.9zM14.1 4.5c.7-.9 1.2-2.1 1.1-3.3-1.1 0-2.4.7-3.1 1.6-.7.8-1.3 2-1.1 3.2 1.2.1 2.4-.6 3.1-1.5z" />
                            </svg>
                            <span><small>Download on the</small>App Store</span>
                        </a>
                        <a href="https://play.google.com/store/apps/details?id=com.amcob.events.org" target="_blank"
                            class="store-btn">
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

                    <div class="floating-card card-community" aria-hidden="true">
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

                    <div class="phone phone-hero phone-hero-frame">
                        <span class="phone-hero-notch" aria-hidden="true"></span>
                        <div class="phone-hero-screen">
                            <img src="{{ asset('assets/img/home/home-1x.webp') }}"
                                srcset="{{ asset('assets/img/home/home-1x.webp') }} 1x, {{ asset('assets/img/home/home-2x.webp') }} 2x"
                                width="330" height="695" alt="AMCOB app on a mobile phone" aria-hidden="true"
                                loading="eager" fetchpriority="high">
                        </div>
                        <span class="phone-hero-gloss" aria-hidden="true"></span>
                    </div>

                    <div class="floating-card card-summit" aria-hidden="true">
                        <span class="avatar avatar-sm avatar-blue"></span>
                        <div style="display: flex; flex-direction: column; gap: 0px;"><strong>AMCOB
                                Summit</strong><small>Dubai ·
                                Nov 2026</small></div>
                    </div>

                    <div class="floating-card card-profile" aria-hidden="true">
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
                    <li><strong><span class="dot"></span> Connect</strong>
                        <p>Meet members across every chapter, by industry and interest.</p>
                    </li>
                    <li><strong><span class="dot"></span> Collaborate</strong>
                        <p>Message, meet and work together in channels and events.</p>
                    </li>
                    <li><strong><span class="dot"></span> Grow</strong>
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
                <div class="needs-card">
                    <div class="needs-copy">
                        <h2>Everything your community needs</h2>
                        <p>Discover what's happening across chapters — and jump into the conversations and events that
                            matter to
                            you.</p>
                        <a href="#download" class="btn btn-gold">EXPLORE APP</a>
                    </div>

                    <ul class="needs-list">
                        <li>
                            <span class="needs-info">
                                <span class="needs-icon icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 21V9l8-5 8 5v12" />
                                        <path d="M9 21v-6h6v6" />
                                    </svg></span>
                                <span class="needs-label">Chapters</span>
                            </span>
                            <span class="needs-chart">
                                <img class="line-reveal-img" src="{{ asset('assets/img/home/chapterIcon.svg') }}"
                                    width="150" height="34" alt="Chapter count growth trend">
                                <span class="counter-parent"><span class="counter" data-target="120">0</span>+</span>
                            </span>
                        </li>
                        <li>
                            <span class="needs-info">
                                <span class="needs-icon icon-gold"><svg viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="8" r="4" />
                                        <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7" />
                                    </svg></span>
                                <span class="needs-label">Members</span>
                            </span>
                            <span class="needs-chart">
                                <img class="line-reveal-img" src="{{ asset('assets/img/home/membersIcon.png') }}"
                                    width="150" height="34" alt="Member growth trend">
                                <span class="counter-parent"><span class="counter" data-target="50">0</span>K+</span>
                            </span>
                        </li>
                        <li>
                            <span class="needs-info">
                                <span class="needs-icon icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="4" y="5" width="16" height="15" rx="2" />
                                        <path d="M8 3v4M16 3v4M4 10h16" />
                                    </svg></span>
                                <span class="needs-label">Events</span>
                            </span>
                            <span class="needs-chart">
                                <img class="line-reveal-img" src="{{ asset('assets/img/home/eventsIcon.png') }}"
                                    width="150" height="34" alt="Events hosted growth trend">
                                <span class="counter-parent"><span class="counter" data-target="500">0</span>+</span>
                            </span>
                        </li>
                        <li>
                            <span class="needs-info">
                                <span class="needs-icon icon-green"><svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="7" width="18" height="13" rx="2" />
                                        <path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                                    </svg></span>
                                <span class="needs-label">Founders</span>
                            </span>
                            <span class="needs-chart">
                                <img class="line-reveal-img" src="{{ asset('assets/img/home/foundersIcon.png') }}"
                                    width="150" height="34" alt="Founders and business owners growth trend">
                                <span class="counter-parent"><span class="counter" data-target="8">0</span>K+</span>
                            </span>
                        </li>
                    </ul>

                    <a href="#features" class="explore-more">EXPLORE MORE →</a>
                </div>
            </div>
        </section>

        <!-- ============ FEATURES ============ -->
        <section class="features" id="features">
            <div class="container">
                <header class="section-head">
                    <span class="bg-word">FEATURES</span>
                    <h2>Pro-level tools to grow<br>your network in one app</h2>
                    <div class="dash-container">
                        <span class="dash-white"></span>
                        <span class="dash"></span>
                    </div>
                    <p>Manage your profile, discover members, join events and message your connections — on Android and
                        iOS.</p>
                </header>

                <div class="feature-row">
                    <img src="{{ asset('assets/img/home/smartNetworkingImg-1x.webp') }}"
                        srcset="{{ asset('assets/img/home/smartNetworkingImg-1x.webp') }} 1x, {{ asset('assets/img/home/smartNetworkingImg-2x.webp') }} 2x"
                        width="510" height="606" alt="Smart Networking Image" aria-hidden="true" loading="lazy"
                        class="phone phone-tilt-l large">

                    <div class="feature-copy">
                        <h3 class="text-yellow"><span class="bullet bullet-gold"></span>Smart networking</h3>
                        <p>Tell AMCOB your goals and it introduces you to the members worth knowing — ranked by shared
                            interests,
                            industry and mutual connections.</p>
                        <p>Connect, message and book meetings without ever leaving the app.</p>
                        {{-- <a href="#" class="btn btn-outline">LEARN MORE</a> --}}
                    </div>
                </div>

                <div class="feature-row feature-row-rev">
                    <img src="{{ asset('assets/img/home/oneLivingCommunityFieldImg-1x.webp') }}"
                        srcset="{{ asset('assets/img/home/oneLivingCommunityFieldImg-1x.webp') }} 1x, {{ asset('assets/img/home/oneLivingCommunityFieldImg-2x.webp') }} 2x"
                        width="510" height="609" alt="Living Community Image" aria-hidden="true" loading="lazy"
                        class="phone phone-tilt-r large">

                    <div class="feature-copy">
                        <h3 class="text-blue"><span class="bullet bullet-blue"></span>One living community feed</h3>
                        <p>Announcements, wins and conversations from every chapter — all in one always-updated feed.
                        </p>
                        <p>Share a post, start a discussion or celebrate a member, and never miss what's happening
                            across the
                            network.</p>
                        {{-- <a href="#" class="btn btn-outline blue">LEARN MORE</a> --}}
                    </div>
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
                    <h2>You deserve easy access<br>to your community</h2>
                    <div class="dash-container">
                        <span class="dash-white"></span>
                        <span class="dash"></span>
                    </div>
                </header>

                <div class="benefits-showcase">
                    <ul class="benefit-list benefit-list-l">
                        <li>
                            <img src="{{ asset('assets/img/home/realtimeIcon.svg') }}" width="46" height="46"
                                alt="Real Time Icon">
                            <div><strong>Real-time feed</strong><small>Every chapter update as it happens.</small></div>
                        </li>
                        <li>
                            <img src="{{ asset('assets/img/home/secureNVerifiedIcon.svg') }}" width="46" height="46"
                                alt="Secure & Verified Icon">
                            <div><strong>Secure &amp; verified</strong><small>Members-only, verified access.</small>
                            </div>
                        </li>
                        <li>
                            <img src="{{ asset('assets/img/home/memberDirectoryIcon.svg') }}" width="46" height="46"
                                alt="Member Directory Icon">
                            <div><strong>Member directory</strong><small>Find anyone in seconds.</small></div>
                        </li>
                    </ul>

                    <div class="benefits-photos" aria-hidden="true">
                        <!-- <div class="orbit-rings orbit-rings-lg"><span></span><span></span><span></span></div> -->
                        <div class="photo-circle photo-circle-a"><img
                                src="{{ asset('assets/img/home/benefitsSecImg-1x.webp') }}"
                                srcset="{{ asset('assets/img/home/benefitsSecImg-1x.webp') }} 1x, {{ asset('assets/img/home/benefitsSecImg-2x.webp') }} 2x"
                                width="400" height="283" alt="Benefits Image" loading="lazy"></div>
                        <!-- <div class="photo-circle photo-circle-b"><img src="{{ asset('assets/img/home/woman-benefits.png') }}" alt=""></div> -->
                    </div>

                    <ul class="benefit-list benefit-list-r">
                        <li>
                            <img src="{{ asset('assets/img/home/easyOnboardingIcon.svg') }}" width="46" height="46"
                                alt="Easy Onboarding Icon">
                            <div><strong>Easy onboarding</strong><small>Set up your profile in minutes.</small></div>
                        </li>
                        <li> <img src="{{ asset('assets/img/home/messageNMeetingsIcon.svg') }}" width="46" height="46"
                                alt="Message Icon">
                            <div><strong>Messaging &amp; meetings</strong><small>Chat and book 1:1s in-app.</small>
                            </div>
                        </li>
                        <li>
                            <img src="{{ asset('assets/img/home/eventsNTicketsIcon.svg') }}" width="46" height="46"
                                alt="Events & Tickets Icon">
                            <div><strong>Events &amp; tickets</strong><small>RSVP and check in with QR.</small></div>
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
                <div class="get-app-card">
                    <img src="{{ asset('assets/img/home/getTheAppNowImg-1x.webp') }}"
                        srcset="{{ asset('assets/img/home/getTheAppNowImg-1x.webp') }} 1x, {{ asset('assets/img/home/getTheAppNowImg-2x.webp') }} 2x"
                        width="230" height="363" alt="Get The App Image" aria-hidden="true" loading="lazy"
                        class="phone phone-cta">

                    <div class="get-app-copy">
                        <h2>Get the app now</h2>
                        <p>Download the AMCOB app and step into the community — connect, collaborate and grow, wherever
                            you are.</p>
                        <div class="store-buttons">
                            <a href="https://apps.apple.com/us/app/amcob/id6793501674" target="_blank"
                                class="store-btn store-btn-dark">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M16.7 12.4c0-2.7 2.2-4 2.3-4.1-1.3-1.8-3.2-2.1-3.9-2.1-1.7-.2-3.2 1-4.1 1-.9 0-2.1-.9-3.5-.9-1.8 0-3.5 1-4.4 2.6-1.9 3.3-.5 8.1 1.3 10.8.9 1.3 2 2.8 3.4 2.7 1.4-.1 1.9-.9 3.5-.9s2.1.9 3.5.8c1.5 0 2.4-1.3 3.3-2.6.9-1.4 1.3-2.8 1.3-2.9-.1 0-2.6-1-2.7-3.9zM14.1 4.5c.7-.9 1.2-2.1 1.1-3.3-1.1 0-2.4.7-3.1 1.6-.7.8-1.3 2-1.1 3.2 1.2.1 2.4-.6 3.1-1.5z" />
                                </svg>
                                <span><small>Download on the</small>App Store</span>
                            </a>
                            <a href="https://play.google.com/store/apps/details?id=com.amcob.events.org"
                                target="_blank" class="store-btn store-btn-dark">
                                <svg viewBox="0 0 512 512" aria-hidden="true">
                                    <path
                                        d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z" />
                                </svg>
                                <span><small>GET IT ON</small>Google Play</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ============ FAQ ============ -->
                <div class="faq-section" id="faq">
                    <div class="faq-side">
                        <h2>Frequently asked<br>questions</h2>
                        <div class="dash-container">
                            <span class="dash-white"></span>
                            <span class="dash"></span>
                        </div>
                        <div class="faq-photo"><img src="{{ asset('assets/img/home/woman-faq-1x.webp') }}"
                                srcset="{{ asset('assets/img/home/woman-faq-1x.webp') }} 1x, {{ asset('assets/img/home/woman-faq-2x.webp') }} 2x"
                                width="220" height="241" alt="AMCOB member smiling" loading="lazy"></div>
                        {{-- <a href="#" class="btn btn-gold">Visit FAQ Center <img
                                src="{{ asset('assets/img/home/btnArrowIcon.svg') }}" width="18" height="18"
                                alt=""></a> --}}
                    </div>

                    <div class="faq-list">
                        <div class="faq-item is-open">
                            <button class="faq-q" type="button" id="faq-q-1" aria-expanded="true" aria-controls="faq-a-1">
                                <span>Who can join AMCOB?</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                            <div class="faq-a" id="faq-a-1" role="region" aria-labelledby="faq-q-1">
                                <p>AMCOB is for members of the Allied Muslim Chamber of Business and their chapters —
                                    business owners,
                                    founders, investors and professionals across the network.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-q" type="button" id="faq-q-2" aria-expanded="false" aria-controls="faq-a-2">
                                <span>Is the app free to use?</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                            <div class="faq-a" id="faq-a-2" role="region" aria-labelledby="faq-q-2">
                                <p>Yes. The AMCOB app is free for all verified members — simply download it and confirm
                                    your membership
                                    to get started.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-q" type="button" id="faq-q-3" aria-expanded="false" aria-controls="faq-a-3">
                                <span>How does AI networking work?</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                            <div class="faq-a" id="faq-a-3" role="region" aria-labelledby="faq-q-3">
                                <p>Tell AMCOB your goals and industry, and the app ranks members worth meeting by shared
                                    interests,
                                    industry and mutual connections.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-q" type="button" id="faq-q-4" aria-expanded="false" aria-controls="faq-a-4">
                                <span>Can I manage my chapter's events?</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                            <div class="faq-a" id="faq-a-4" role="region" aria-labelledby="faq-q-4">
                                <p>Chapter organizers can create events, sell tickets, track RSVPs and message attendees
                                    directly from
                                    the app.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-q" type="button" id="faq-q-5" aria-expanded="false" aria-controls="faq-a-5">
                                <span>Which platforms are supported?</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                            <div class="faq-a" id="faq-a-5" role="region" aria-labelledby="faq-q-5">
                                <p>AMCOB is available on both Android and iOS, with your profile and connections synced
                                    across devices.
                                </p>
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



    <script src="{{ asset('assets/js/grow-your-business.js') }}"></script>

</body>

</html>
