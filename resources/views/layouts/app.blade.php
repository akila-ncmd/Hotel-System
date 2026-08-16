<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Diamond Shine') }}</title>
    <link rel="icon" href="{{ asset('images/logo.svg') }}" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Brand typefaces: a display serif and a geometric sans for the
         uppercase micro-type. Preconnect so they are not render-blocking. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">

    {{-- The design layer. Loaded after Bootstrap so it can override it. --}}
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}">

    {{-- Gate for the scroll-reveal styles. Reveal hides content by default, so
         that rule must only apply when JS is actually running — otherwise a
         script error or a blocked file leaves the page blank. Inline and
         render-blocking on purpose: it has to win before first paint. --}}
    <script>document.documentElement.classList.add('ds-js');</script>
    <style>
        :root {
            /* Legacy aliases. Older markup still references these; they now
               point at the design tokens so nothing renders off-palette. */
            --primary-color: var(--ds-wine);
            --secondary-color: var(--ds-wine-deep);
            --accent-color: var(--ds-clay);
            --light-color: var(--ds-shell);
            --dark-color: var(--ds-charcoal);
        }

        /* Layout scaffolding only — everything cosmetic lives in theme.css. */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main { flex: 1; }

        .btn-link { text-decoration: none !important; }

        .brand-mark {
            color: var(--ds-charcoal);
            transition: transform var(--ds-med) var(--ds-ease),
                        color var(--ds-med) var(--ds-ease);
        }

        .navbar-brand:hover .brand-mark {
            transform: rotate(-8deg) scale(1.06);
            color: var(--ds-wine);
        }

        .brand-name {
            font-family: var(--ds-serif);
            font-weight: 500;
            font-size: 1.3rem;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--ds-charcoal);
            white-space: nowrap;
            transition: color var(--ds-med) var(--ds-ease);
        }

        .navbar-brand:hover .brand-name { color: var(--ds-wine); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                {{-- Text-free emblem; the name comes from config('app.name')
                     so a rename never leaves a stale wordmark in an image. --}}
                <img src="{{ asset('images/logo.svg') }}" alt="" width="36" height="36" class="brand-mark">
                <span class="brand-name">{{ config('app.name') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                <i class="bi bi-house me-1"></i>Home
                            </a>
                        </li>
                        @if (auth()->user()->role === 'customer')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customer.reservations') ? 'active' : '' }}" href="{{ route('customer.reservations') }}">
                                    <i class="bi bi-calendar-check me-1"></i>My Reservations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customer.edit-profile') ? 'active' : '' }}" href="{{ route('customer.edit-profile') }}">
                                    <i class="bi bi-person-circle me-1"></i>Edit Profile
                                </a>
                            </li>
                        @elseif (auth()->user()->role === 'clerk')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('clerk.dashboard') ? 'active' : '' }}" href="{{ route('clerk.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i>Clerk Dashboard
                                </a>
                            </li>
                        @elseif (auth()->user()->role === 'manager')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}" href="{{ route('manager.dashboard') }}">
                                    <i class="bi bi-graph-up me-1"></i>Manager Dashboard
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link logout-btn">
                                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                <i class="bi bi-house me-1"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main>
        {{-- Editorial pages need to bleed to the viewport edge, so they push
             into @section('fullwidth') and manage their own containers.
             Everything else keeps the original centred container. --}}
        @hasSection('fullwidth')
            @yield('fullwidth')
        @else
            <div class="container py-4">
                @yield('content')
            </div>
        @endif
    </main>

    {{-- Footer.
         Bento grid of hairline cells, monochromatic on ink, closing with an
         oversized wordmark that draws itself in on scroll. --}}
    <footer class="ds-footer">
        <div class="ds-wide">

            {{-- Marquee: a slow ticker so the footer has a pulse before you
                 interact with it. Duplicated once so the loop is seamless. --}}
            <div class="ds-marquee ds-reveal" aria-hidden="true">
                <div class="ds-marquee__track">
                    @for ($i = 0; $i < 2; $i++)
                        <span>Colombo</span><span class="ds-marquee__dot">&#9670;</span>
                        <span>Kandy</span><span class="ds-marquee__dot">&#9670;</span>
                        <span>Galle</span><span class="ds-marquee__dot">&#9670;</span>
                        <span>Reception open 24 hours</span><span class="ds-marquee__dot">&#9670;</span>
                    @endfor
                </div>
            </div>

            <div class="ds-bento" data-ds-stagger="80">

                {{-- Lead cell --}}
                <section class="ds-cell ds-cell--lead ds-reveal">
                    <span class="ds-cell__label">Diamond Shine</span>
                    <p class="ds-cell__statement">
                        A small chain of quiet hotels on the Sri Lankan coast &mdash;
                        kept simple, and run with care.
                    </p>
                    <a href="{{ route('home') }}" class="ds-cell__cta">
                        <span>Plan a stay</span>
                        <svg width="26" height="10" viewBox="0 0 26 10" fill="none" aria-hidden="true">
                            <path d="M0 5h24M20 1l4 4-4 4" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                    </a>
                </section>

                {{-- Navigation --}}
                <nav class="ds-cell ds-reveal" aria-label="Footer">
                    <span class="ds-cell__label">Index</span>
                    <ul class="ds-cell__list">
                        <li><a href="{{ route('home') }}"><span>Home</span></a></li>
                        @auth
                            @if (auth()->user()->role === 'customer')
                                <li><a href="{{ route('customer.reservations') }}"><span>Reservations</span></a></li>
                                <li><a href="{{ route('reservations.room') }}"><span>Book a room</span></a></li>
                                <li><a href="{{ route('reservations.suite') }}"><span>Book a suite</span></a></li>
                            @else
                                <li><a href="{{ route('dashboard') }}"><span>Dashboard</span></a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}"><span>Sign in</span></a></li>
                            <li><a href="{{ route('register') }}"><span>Create account</span></a></li>
                        @endauth
                    </ul>
                </nav>

                {{-- Houses, with a live local clock --}}
                <section class="ds-cell ds-reveal">
                    <span class="ds-cell__label">Our houses</span>
                    <ul class="ds-cell__houses">
                        <li><strong>Colombo</strong><span>Galle Road</span></li>
                        <li><strong>Kandy</strong><span>Peradeniya Road</span></li>
                        <li><strong>Galle</strong><span>Lighthouse Street</span></li>
                    </ul>
                    <div class="ds-clock">
                        <span class="ds-clock__dot"></span>
                        <span>Reception &middot; <time id="ds-clock-time">&mdash;&mdash;:&mdash;&mdash;</time> local</span>
                    </div>
                </section>

                {{-- Contact --}}
                <section class="ds-cell ds-reveal">
                    <span class="ds-cell__label">Reception</span>
                    <a class="ds-cell__big" href="tel:+94112345678">+94 11 234 5678</a>
                    <a class="ds-cell__big" href="mailto:stay@diamondshine.test">stay@diamondshine.test</a>
                    <p class="ds-cell__note mb-0">Open every hour of every day.</p>
                </section>

                {{-- Emblem cell: the illustrative note the references all share --}}
                <section class="ds-cell ds-cell--mark ds-reveal" aria-hidden="true">
                    <img src="{{ asset('images/logo.svg') }}" alt="" width="64" height="64">
                </section>
            </div>

            {{-- Oversized wordmark. Letters rise in sequence as the footer
                 enters. Grouped by word so the layout script can stack the
                 words onto separate lines when the footer is narrow, rather
                 than shrinking the type into illegibility. --}}
            @php $charIndex = 0; @endphp
            <div class="ds-wordmark ds-reveal" aria-hidden="true">
                @foreach (preg_split('/\s+/', config('app.name'), -1, PREG_SPLIT_NO_EMPTY) as $word)
                    <span class="ds-wordmark__word">
                        @foreach (mb_str_split($word) as $char)
                            <span class="ds-wordmark__char" style="--i: {{ $charIndex++ }};">{{ $char }}</span>
                        @endforeach
                    </span>
                @endforeach
            </div>

            <div class="ds-footer-base">
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
                <a href="#top" class="ds-totop">
                    <span>Back to top</span>
                    <svg width="10" height="26" viewBox="0 0 10 26" fill="none" aria-hidden="true">
                        <path d="M5 26V2M1 6l4-4 4 4" stroke="currentColor" stroke-width="1.2"/>
                    </svg>
                </a>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict';

            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            document.addEventListener('DOMContentLoaded', function () {
                // --- active nav item ---------------------------------------
                document.querySelectorAll('.nav-link').forEach(function (link) {
                    if (link.getAttribute('href') === window.location.pathname) {
                        link.classList.add('active');
                    }
                });

                // --- navbar condenses once the page scrolls ----------------
                var navbar = document.querySelector('.navbar');
                if (navbar) {
                    var onScroll = function () {
                        navbar.classList.toggle('is-stuck', window.scrollY > 24);
                    };
                    onScroll();
                    window.addEventListener('scroll', onScroll, { passive: true });
                }

                // --- footer clock ------------------------------------------
                // Reception is open 24h, so the house's local time is real
                // information rather than decoration. Set up before the reveal
                // logic, which can return early under reduced motion.
                var clock = document.getElementById('ds-clock-time');
                if (clock) {
                    var tick = function () {
                        clock.textContent = new Date().toLocaleTimeString('en-GB', {
                            hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Colombo'
                        });
                    };
                    tick();
                    window.setInterval(tick, 30000);
                }

                // --- fit the footer wordmark -------------------------------
                // Measure the glyphs at a known size, then scale so the word
                // exactly spans its row. Needed because the size depends on
                // both the viewport and how many letters the brand name has —
                // a fixed vw value clips a long name and under-fills a short one.
                var wordmark = document.querySelector('.ds-wordmark');
                if (wordmark) {
                    var words = wordmark.querySelectorAll('.ds-wordmark__word');
                    var fitAttempts = 0;

                    // Below this the type stops reading as a statement and just
                    // looks shrunken, so the words stack onto their own lines
                    // instead — each then fills the full width.
                    var MIN_INLINE_SIZE = 46;

                    var fitWordmark = function () {
                        var available = wordmark.clientWidth;

                        // Layout may not have happened yet (hidden document,
                        // fonts still loading). Retry rather than give up —
                        // bailing here would leave the fallback size, which
                        // clips a long name.
                        if (!available) {
                            if (fitAttempts++ < 20) window.setTimeout(fitWordmark, 150);
                            return;
                        }

                        var probe = 100;

                        // Measure both candidate layouts at a known size.
                        wordmark.classList.remove('is-stacked');
                        wordmark.style.fontSize = probe + 'px';

                        var inlineNatural = 0;
                        var widestWord = 0;
                        Array.prototype.forEach.call(words, function (w) {
                            var width = w.getBoundingClientRect().width;
                            inlineNatural += width;
                            if (width > widestWord) widestWord = width;
                        });
                        if (!inlineNatural || !widestWord) return;

                        // Account for the inter-word gap (0.2em per gap).
                        inlineNatural += 0.2 * probe * Math.max(words.length - 1, 0);

                        // 0.98 leaves a hair of slack so the last glyph never clips.
                        var inlineSize = probe * (available / inlineNatural) * 0.98;
                        var size;

                        if (inlineSize < MIN_INLINE_SIZE && words.length > 1) {
                            wordmark.classList.add('is-stacked');
                            size = probe * (available / widestWord) * 0.98;
                        } else {
                            size = inlineSize;
                        }

                        wordmark.style.fontSize =
                            Math.max(24, Math.min(Math.floor(size), 280)) + 'px';
                    };

                    fitWordmark();
                    // Webfonts change glyph metrics, so re-fit once they land.
                    if (document.fonts && document.fonts.ready) {
                        document.fonts.ready.then(fitWordmark);
                    }
                    // ResizeObserver rather than a window resize listener: it
                    // also catches layout changes that never resize the window
                    // (a scrollbar appearing, the footer reflowing).
                    // Observe the *parent*, not the wordmark: resizing the type
                    // changes the wordmark's own height, which would retrigger
                    // the observer and loop.
                    if ('ResizeObserver' in window && wordmark.parentElement) {
                        var fitTimer;
                        new ResizeObserver(function () {
                            window.clearTimeout(fitTimer);
                            fitTimer = window.setTimeout(fitWordmark, 120);
                        }).observe(wordmark.parentElement);
                    } else {
                        window.addEventListener('resize', fitWordmark, { passive: true });
                    }
                }

                // --- back to top -------------------------------------------
                var toTop = document.querySelector('.ds-totop');
                if (toTop) {
                    toTop.addEventListener('click', function (e) {
                        e.preventDefault();
                        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
                    });
                }

                // --- scroll reveals ----------------------------------------
                // Elements opt in with .ds-reveal. Without IntersectionObserver,
                // or with reduced motion, everything is simply shown — content
                // must never be left stranded at opacity 0.
                var revealables = document.querySelectorAll('.ds-reveal, .ds-reveal-img');

                var revealAll = function () {
                    revealables.forEach(function (el) { el.classList.add('is-visible'); });
                };

                if (reduceMotion || !('IntersectionObserver' in window)) {
                    revealAll();
                    return;
                }

                // Failsafe. IntersectionObserver does not fire while a document
                // is hidden (background tab, non-compositing embed), and we must
                // never leave content stranded at opacity 0. If nothing has
                // revealed shortly after load, just show everything.
                var failsafe = window.setTimeout(function () {
                    if (!document.querySelector('.ds-reveal.is-visible, .ds-reveal-img.is-visible')) {
                        revealAll();
                    }
                }, 2500);

                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        window.clearTimeout(failsafe);
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);   // reveal once, then stop
                    });
                }, { rootMargin: '0px 0px -12% 0px', threshold: 0.05 });

                revealables.forEach(function (el) {
                    // Stagger groups: data-ds-stagger on a parent cascades a
                    // delay across its revealing children.
                    var parent = el.closest('[data-ds-stagger]');
                    if (parent) {
                        var siblings = Array.prototype.slice.call(
                            parent.querySelectorAll('.ds-reveal, .ds-reveal-img')
                        );
                        var step = parseInt(parent.dataset.dsStagger, 10) || 90;
                        el.style.setProperty('--ds-delay', (siblings.indexOf(el) * step) + 'ms');
                    }
                    observer.observe(el);
                });
            });
        })();
    </script>

    {{-- Page-specific scripts. Views that need their own JS push it into this section. --}}
    @yield('scripts')
</body>
</html>
