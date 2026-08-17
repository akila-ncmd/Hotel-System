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

    {{-- Brand typefaces, both from Google Fonts:
         Lora is the display serif, Plus Jakarta Sans is the text face.
         Preconnect so the stylesheet request isn't waiting on DNS + TLS. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

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
            font-weight: 400;
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
    @php
        // Staff get a lean, functional bar: a clerk wants their dashboard, not a
        // hotline and a booking CTA. Guests and customers get the full one.
        $isStaff = auth()->check() && in_array(auth()->user()->role, ['clerk', 'manager', 'admin'], true);

        // Section anchors live on the home page, so they must be addressed
        // absolutely — the nav is on every page, not only that one.
        $home = route('home');

        // "Book now" has to land somewhere real. Only customers can reach the
        // booking forms, so everyone else is sent to sign in first.
        $bookHref = auth()->check() && auth()->user()->role === 'customer'
            ? route('reservations.room')
            : route('login');
    @endphp

    {{-- Keyboard users should not have to tab through the whole nav on every
         page before reaching the content. --}}
    <a class="ds-skip" href="#main">Skip to content</a>

    {{-- The launcher bar. Deliberately almost empty: brand, hotline, socials,
         Book now and Menu. The four sections live in the full-screen menu
         below, which is what keeps this to one soft-cornered strip floating
         over the hero instead of a structure sitting on the photograph.
         The menu is the navigation at every width, so there is no separate
         mobile panel and no dropdown. --}}
    <header class="ds-header @hasSection('hero') ds-header--over @endif"
            id="top" data-ds-header>

        <div class="ds-bar" data-ds-bar>
            <a class="ds-bar__brand" href="{{ route('home') }}">
                {{-- Text-free emblem; the name comes from config('app.name')
                     so a rename never leaves a stale wordmark in an image. --}}
                <img src="{{ asset('images/logo.svg') }}" alt="" width="26" height="26" class="brand-mark">
                <span class="brand-name">{{ config('app.name') }}</span>
            </a>

            @if ($isStaff)
                {{-- Staff get their destinations inline. Making a clerk open a
                     menu to reach the front desk would be a worse tool. --}}
                <div class="ds-bar__staff">
                    @if (auth()->user()->role === 'clerk')
                        <a href="{{ route('clerk.dashboard') }}" @if (request()->routeIs('clerk.dashboard')) aria-current="page" @endif>Dashboard</a>
                        <a href="{{ route('clerk.front-desk') }}" @if (request()->routeIs('clerk.front-desk')) aria-current="page" @endif>Front desk</a>
                    @elseif (auth()->user()->role === 'manager')
                        <a href="{{ route('manager.dashboard') }}" @if (request()->routeIs('manager.dashboard')) aria-current="page" @endif>Dashboard</a>
                        <a href="{{ route('manager.reports') }}" @if (request()->routeIs('manager.reports')) aria-current="page" @endif>Reports</a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Sign out</button>
                    </form>
                </div>
            @else
                {{-- The hotline reads as a phone icon and widens on hover to
                     show the number, so it costs one icon of width but is never
                     ambiguous once you approach it. --}}
                <a class="ds-tel" href="tel:{{ config('hotel.hotline_tel') }}"
                   aria-label="Call reception on {{ config('hotel.hotline') }}">
                    <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                    <span>{{ config('hotel.hotline') }}</span>
                </a>

                <div class="ds-bar__icons">
                    {{-- A blank URL removes the channel rather than rendering a
                         dead icon. --}}
                    @foreach (array_filter(config('hotel.social'), fn ($s) => filled($s['url'])) as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                           aria-label="{{ $social['label'] }}">
                            <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>

                <a class="ds-book" href="{{ $bookHref }}">Book now</a>
            @endif

            <button class="ds-menu-btn" type="button"
                    aria-controls="dsMenu" aria-expanded="false" data-ds-menu-open>
                <span class="ds-menu-btn__glyph" aria-hidden="true">
                    <span></span><span></span>
                </span>
                <span class="ds-menu-btn__label">Menu</span>
            </button>

            {{-- Reading progress. Decorative, so it is allowed to simply not
                 exist where scroll-driven animations aren't supported. --}}
            <span class="ds-progress" aria-hidden="true"></span>
        </div>
    </header>

    {{-- The full-screen menu. This is the navigation — every section lives
         here and nowhere else, on every screen size. --}}
    <div class="ds-menu" id="dsMenu" data-ds-menu aria-label="Menu" role="dialog" aria-modal="true" inert>
        <button class="ds-menu__close" type="button" aria-label="Close menu" data-ds-menu-close>&times;</button>

        <div class="ds-menu__inner">
            <nav class="ds-menu__nav" aria-label="Sections">
                {{-- The label is wrapped so hover can slide it with a transform.
                     Sliding the link's own padding animates layout on every
                     frame, which is what made the hover stutter. --}}
                @if ($isStaff)
                    <a class="ds-menu__link" style="--i:0" href="{{ route('home') }}"><span>Home</span></a>
                    <a class="ds-menu__link" style="--i:1" href="{{ route('dashboard') }}"><span>Dashboard</span></a>
                @else
                    <a class="ds-menu__link" style="--i:0" href="{{ $home }}#rooms"><span>Stay</span></a>
                    <div class="ds-menu__sub">
                        <a href="{{ $home }}#rooms">Rooms</a>
                        <a href="{{ $home }}#residential-suites">Residential suites</a>
                        @auth
                            @if (auth()->user()->role === 'customer')
                                <a href="{{ route('customer.reservations') }}">My reservations</a>
                            @endif
                        @endauth
                    </div>
                    <a class="ds-menu__link" style="--i:1" href="{{ $home }}#explore"><span>Amenities</span></a>
                    <a class="ds-menu__link" style="--i:2" href="{{ $home }}#gallery"><span>Gallery</span></a>
                    <a class="ds-menu__link" style="--i:3" href="#contact"><span>Contact</span></a>
                @endif
            </nav>

            <div class="ds-menu__aside">
                @unless ($isStaff)
                    <div>
                        <span class="ds-menu__label">Our houses</span>
                        <ul class="ds-menu__houses">
                            <li><strong>Colombo</strong><span>Galle Road</span></li>
                            <li><strong>Kandy</strong><span>Peradeniya Road</span></li>
                            <li><strong>Galle</strong><span>Lighthouse Street</span></li>
                        </ul>
                    </div>

                    <div class="ds-menu__contact">
                        <span class="ds-menu__label">Reception</span>
                        <a href="tel:{{ config('hotel.hotline_tel') }}">{{ config('hotel.hotline') }}</a>
                        <a href="mailto:{{ config('hotel.email') }}">{{ config('hotel.email') }}</a>
                    </div>
                @endunless

                <div>
                    <span class="ds-menu__label">Account</span>
                    <div class="ds-menu__account">
                        @auth
                            @if (auth()->user()->role === 'customer')
                                <a href="{{ route('customer.reservations') }}">My reservations</a>
                                <a href="{{ route('customer.edit-profile') }}">Edit profile</a>
                            @else
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Sign out</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}">Sign in</a>
                            <a href="{{ route('register') }}">Create account</a>
                        @endauth
                    </div>
                </div>

                @unless ($isStaff)
                    <div>
                        <span class="ds-menu__label">Follow</span>
                        <div class="ds-menu__social">
                            @foreach (array_filter(config('hotel.social'), fn ($s) => filled($s['url'])) as $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                   aria-label="{{ $social['label'] }}">
                                    <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </div>

    {{-- The header is fixed, so it is out of flow. A hero page wants exactly
         that — the hero starts at y=0 and fills the screen with the bar over
         it. Every other page has to reserve the bar's height itself, or its
         first heading sits underneath the nav. --}}
    <main id="main" @unless (View::hasSection('hero')) class="ds-main--offset" @endunless>
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

            <div class="ds-bento" data-ds-stagger="80">

                {{-- Lead cell --}}
                <section class="ds-cell ds-cell--lead ds-reveal">
                    <span class="ds-cell__label">{{ config('app.name') }}</span>
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

                    {{-- Social. Driven by config/hotel.php so an account the
                         chain does not have simply leaves no dead icon behind. --}}
                    @php $ds_social = array_filter(config('hotel.social', []), fn ($s) => !empty($s['url'])); @endphp
                    @if ($ds_social)
                        <ul class="ds-social" aria-label="{{ config('app.name') }} on social media">
                            @foreach ($ds_social as $key => $channel)
                                <li>
                                    <a href="{{ $channel['url'] }}"
                                       class="ds-social__link"
                                       target="_blank"
                                       rel="noopener noreferrer me"
                                       aria-label="{{ $channel['label'] }}">
                                        <i class="{{ $channel['icon'] }}" aria-hidden="true"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
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

                {{-- Contact. The hotline is the single most-wanted thing in a
                     hotel footer, so it leads the cell and is tagged as such. --}}
                <section class="ds-cell ds-reveal" id="contact">
                    <span class="ds-cell__label">Reception</span>
                    <span class="ds-cell__eyebrow">24-hour hotline</span>
                    <a class="ds-cell__big ds-cell__hotline" href="tel:{{ config('hotel.hotline_tel') }}">
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <span>{{ config('hotel.hotline') }}</span>
                    </a>
                    <a class="ds-cell__big" href="mailto:{{ config('hotel.email') }}">{{ config('hotel.email') }}</a>
                    <address class="ds-cell__note mb-0">
                        {{ config('hotel.address') }}<br>
                        Open every hour of every day.
                    </address>
                </section>

                {{-- Before you book: the practical questions a guest asks a
                     hotel site before anything else. --}}
                <section class="ds-cell ds-cell--stay ds-reveal">
                    <span class="ds-cell__label">Your stay</span>
                    <ul class="ds-cell__facts">
                        <li><span>Check-in</span><strong>from {{ config('hotel.check_in_time') }}</strong></li>
                        <li><span>Check-out</span><strong>by {{ config('hotel.check_out_time') }}</strong></li>
                        <li><span>Free cancellation</span><strong>{{ config('hotel.cancellation_hours') }}h before</strong></li>
                    </ul>
                    <p class="ds-cell__note mb-0">
                        Rates quoted in {{ \App\Support\Money::code() }}. Cards are held as a
                        guarantee only &mdash; you settle the folio at the desk.
                    </p>
                </section>

                {{-- Guest care --}}
                <nav class="ds-cell ds-reveal" aria-label="Guest information">
                    <span class="ds-cell__label">Guest care</span>
                    <ul class="ds-cell__list">
                        <li><a href="{{ route('legal.faq') }}"><span>Frequently asked questions</span></a></li>
                        <li><a href="{{ route('legal.cancellation') }}"><span>Cancellation policy</span></a></li>
                        <li><a href="{{ route('legal.accessibility') }}"><span>Accessibility</span></a></li>
                        <li><a href="mailto:{{ config('hotel.email') }}?subject=Guest%20feedback"><span>Feedback &amp; complaints</span></a></li>
                    </ul>
                </nav>

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
                <span class="ds-footer-base__copy">
                    &copy; {{ date('Y') }} {{ config('hotel.legal_entity') }}. All rights reserved.
                </span>
                <nav class="ds-legal-links" aria-label="Legal">
                    <a href="{{ route('legal.terms') }}">Terms &amp; Conditions</a>
                    <a href="{{ route('legal.privacy') }}">Privacy Policy</a>
                    <a href="{{ route('legal.cancellation') }}">Cancellation</a>
                </nav>
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
                // Blade marks the unambiguous cases with aria-current. This
                // catches the rest by comparing paths only — nav hrefs are now
                // absolute and several carry a #fragment, so a raw string
                // comparison against the pathname would never match.
                document.querySelectorAll('.nav-link[href]').forEach(function (link) {
                    var href = link.getAttribute('href');
                    if (!href || href.charAt(0) === '#') return;
                    var url = new URL(href, window.location.origin);
                    if (url.pathname === window.location.pathname && !url.hash) {
                        link.classList.add('active');
                    }
                });

                // --- header behaviour --------------------------------------
                // Two things, off one scroll listener:
                //   is-stuck  — past the first sliver of the page, so the bar
                //               firms up and the progress hairline appears.
                //   is-hidden — retract on a downward scroll, return on any
                //               upward one, so long pages read unobstructed.
                var header = document.querySelector('[data-ds-header]');
                if (header) {
                    var lastY = window.scrollY;
                    var ticking = false;

                    // Only a header over a hero starts thin and earns its glass.
                    // Everywhere else the bar sits on ivory and is frosted from
                    // the first pixel, so CSS keeps --glass at 1.
                    var overHero = header.classList.contains('ds-header--over');

                    var GLASS_RUN = 90;   // px of scroll to reach full glass
                    var STUCK_AT  = 72;

                    var update = function () {
                        ticking = false;
                        var y = window.scrollY;
                        header.classList.toggle('is-stuck', y > STUCK_AT);

                        if (overHero) {
                            // Frost thickens with scroll rather than snapping
                            // on. One custom property drives the bar's opacity.
                            var glass = Math.min(y / GLASS_RUN, 1);
                            header.style.setProperty('--glass', glass.toFixed(3));
                        }

                        // Never retract while the menu is open or while focus is
                        // inside the bar — sliding it out from under an open
                        // menu is disorienting and can strand a keyboard user.
                        var busy = document.body.classList.contains('ds-menu-open')
                            || header.contains(document.activeElement);

                        // Only past a screenful, so short scrolls near the top
                        // never make the bar flicker.
                        var down = y > lastY && y > 240;
                        header.classList.toggle('is-hidden', down && !busy);

                        lastY = y;
                    };

                    var onScroll = function () {
                        if (ticking) return;
                        ticking = true;
                        // Coalesce to one update per frame: scroll fires far
                        // more often than the screen refreshes.
                        window.requestAnimationFrame(update);
                    };

                    update();
                    window.addEventListener('scroll', onScroll, { passive: true });
                }

                // --- the bar's tilt ----------------------------------------
                // The pane leans towards the cursor and a specular bloom
                // follows it across the surface. Both are driven from four
                // custom properties so the CSS owns every actual value and this
                // only reports where the pointer is.
                var bar = document.querySelector('[data-ds-bar]');
                if (bar) {
                    // Matches the CSS opt-outs exactly: no cursor to follow on
                    // touch, and a tilting bar is the sort of movement reduced
                    // motion is asking us not to make.
                    var canTilt = window.matchMedia('(hover: hover) and (pointer: fine)').matches
                        && !reduceMotion;

                    if (canTilt) {
                        var tiltPending = false, bx = 0, by = 0, px = 50, py = 50;

                        var applyTilt = function () {
                            tiltPending = false;
                            bar.style.setProperty('--tx', bx.toFixed(4));
                            bar.style.setProperty('--ty', by.toFixed(4));
                            bar.style.setProperty('--px', px.toFixed(2));
                            bar.style.setProperty('--py', py.toFixed(2));
                        };

                        bar.addEventListener('pointermove', function (e) {
                            var r = bar.getBoundingClientRect();
                            if (!r.width || !r.height) return;
                            var nx = (e.clientX - r.left) / r.width;
                            var ny = (e.clientY - r.top) / r.height;
                            px = nx * 100;
                            py = ny * 100;
                            // -1..1 about the centre. The Y tilt is negated so
                            // the pane leans *towards* the cursor rather than
                            // away from it, which is what reads as physical.
                            bx = (nx - 0.5) * 2;
                            by = -(ny - 0.5) * 2;
                            if (tiltPending) return;
                            tiltPending = true;
                            window.requestAnimationFrame(applyTilt);
                        });

                        bar.addEventListener('pointerleave', function () {
                            bx = by = 0; px = py = 50;
                            if (tiltPending) return;
                            tiltPending = true;
                            window.requestAnimationFrame(applyTilt);
                        });
                    }
                }

                // --- the menu ----------------------------------------------
                // The whole navigation. Hand-rolled rather than a Bootstrap
                // offcanvas because it is full-screen, ivory, and the same
                // component at every width — none of which the offcanvas gives
                // for free.
                var menu = document.querySelector('[data-ds-menu]');
                var openBtn = document.querySelector('[data-ds-menu-open]');
                if (menu && openBtn) {
                    var closeBtn = menu.querySelector('[data-ds-menu-close]');
                    var lastFocus = null;

                    var openMenu = function () {
                        lastFocus = document.activeElement;
                        menu.removeAttribute('inert');
                        menu.classList.add('is-open');
                        openBtn.setAttribute('aria-expanded', 'true');
                        // Lock the page behind it, and keep the bar in place.
                        document.body.classList.add('ds-menu-open');
                        document.body.style.overflow = 'hidden';
                        if (header) header.classList.remove('is-hidden');
                        // The menu is visibility:hidden until .is-open lands,
                        // and a hidden element cannot take focus. Reading a
                        // layout property forces the style recalc first, so the
                        // focus call below is not a no-op.
                        void menu.offsetWidth;
                        if (closeBtn) closeBtn.focus();
                    };

                    var closeMenu = function () {
                        menu.classList.remove('is-open');
                        openBtn.setAttribute('aria-expanded', 'false');
                        document.body.classList.remove('ds-menu-open');
                        document.body.style.overflow = '';
                        // inert keeps the closed menu out of the tab order and
                        // off the accessibility tree — visibility:hidden alone
                        // does that for AT but not reliably for focus.
                        menu.setAttribute('inert', '');
                        if (lastFocus && lastFocus.focus) lastFocus.focus();
                    };

                    openBtn.addEventListener('click', openMenu);
                    if (closeBtn) closeBtn.addEventListener('click', closeMenu);

                    // Following a link inside the menu must close it, or the
                    // overlay stays up over the destination on same-page anchors.
                    menu.addEventListener('click', function (e) {
                        if (e.target.closest('a[href]')) closeMenu();
                    });

                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && menu.classList.contains('is-open')) {
                            closeMenu();
                        }
                    });
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
