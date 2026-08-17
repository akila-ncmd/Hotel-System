@extends('layouts.app')

{{-- Declares that this page opens with a full-bleed hero. The layout reads it
     to decide two things: the header overlays transparently instead of sitting
     on ivory, and <main> does NOT reserve the fixed bar's height. "fullwidth"
     cannot answer that — the auth pages are full-width too, but they open on an
     ivory panel, where ivory nav type would be invisible. --}}
@section('hero', 'true')

@section('fullwidth')
<!-- Hero Section - Updated with Image Background -->
<section class="modern-hero">
    <!-- The hero photograph. One layer, no shader.
         It used to be three stacked photos under a WebGL prism: the top layer
         was opaque and full-bleed, so the two beneath it were never visible and
         only cost two image downloads, and the shader's refraction was what
         made the building look soft. Depth now comes from pointer parallax
         (see the hero script below), which leaves the photo pixel-sharp. -->
    <div class="parallax-container">
        <div class="parallax-layer foreground-layer" style="background-image: url('{{ asset('images/heroThree.jpg') }}');"></div>
        <div class="color-overlay"></div>
    </div>

    <!-- Hero Content with Animated Elements -->
    <div class="hero-content-wrapper">
        <div class="ds-wide">
            <div class="hero-content">
                <!-- Animated Badge -->
                <div class="luxury-badge animate__animated animate__fadeIn">
                    <span>5-Star Luxury</span>
                    <svg width="40" height="10" viewBox="0 0 40 10">
                        <path d="M0,5 L40,5" stroke="var(--ds-clay)" stroke-width="2" stroke-dasharray="5,3"/>
                    </svg>
                </div>

                <!-- Main Heading with Split Animation -->
                <h1 class="hero-heading">
                    <span class="line"><span class="word">{{ config('app.name') }}</span></span>
                    <span class="line"><span class="word">Hotel</span> <span class="word">Experience</span></span>
                </h1>

                <!-- Subtle Decorative Elements -->
                <div class="hero-decoration">
                    <div class="deco-line left"></div>
                    <div class="deco-dot"></div>
                    <div class="deco-line right"></div>
                </div>

                <!-- Location Indicator -->
                <div class="location-indicator animate__animated animate__fadeIn animate__delay-1s">
                    <svg width="12" height="12" viewBox="0 0 12 12">
                        <circle cx="6" cy="6" r="5" fill="none" stroke="#fff" stroke-width="1"/>
                        <circle cx="6" cy="6" r="2" fill="#fff"/>
                    </svg>
                    <span>Mediterranean Coastline</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Proof -->
    <div class="social-proof">
        <div class="proof-item">
            <div class="proof-value">#1</div>
            <div class="proof-label">In Hospitality</div>
        </div>
        <div class="proof-item">
            <div class="proof-value">24/7</div>
            <div class="proof-label">Concierge</div>
        </div>
    </div>
</section>
<!-- Features Section - Enhanced -->
<section id="explore" class="py-5 bg-light">
    <div class="ds-wide py-5">
        <div class="text-center mb-5">
            <span class="text-warning fw-bold mb-2 d-block">EXPERIENCE LUXURY</span>
            <h2 class="display-5 fw-bold position-relative d-inline-block">Why Choose Us
                <span class="position-absolute bottom-0 start-50 translate-middle-x bg-warning" style="height: 4px; width: 80px;"></span>
            </h2>
            <p class="lead text-muted mt-3 mx-auto" style="max-width: 700px;">Discover the perfect blend of luxury, comfort, and exceptional service that sets us apart</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper rounded-circle mx-auto mb-4 position-relative overflow-hidden d-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; background: url('images/wifi.jpg') center/cover no-repeat;">
                            <!-- You can add content here that will be centered -->
                        </div>
                        <h3 class="h4 mb-3">Free High-Speed WiFi</h3>
                        <p class="text-muted mb-0">Stay connected with our complimentary high-speed internet access throughout the property.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper rounded-circle mx-auto mb-4 position-relative overflow-hidden d-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; background: url('images/concierge.jpg') center/cover no-repeat;">
                            <!-- You can add content here that will be centered -->
                        </div>
                        <h3 class="h4 mb-3">24/7 Concierge</h3>
                        <p class="text-muted mb-0">Our dedicated concierge team is available around the clock to assist with all your needs.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper rounded-circle mx-auto mb-4 position-relative overflow-hidden d-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; background: url('images/spa.jpg') center/cover no-repeat;">
                            <!-- You can add content here that will be centered -->
                        </div>
                        <h3 class="h4 mb-3">Luxury Spa</h3>
                        <p class="text-muted mb-0">Relax and rejuvenate at our full-service spa with expert therapists and premium treatments.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper rounded-circle mx-auto mb-4 position-relative overflow-hidden d-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; background: url('images/beach.jpg') center/cover no-repeat;">
                            <!-- You can add content here that will be centered -->
                        </div>
                        <h3 class="h4 mb-3">Beach Access</h3>
                        <p class="text-muted mb-0">Private beach access with premium amenities and exclusive services for our guests.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="rooms" class="py-5 bg-white">
    <div class="ds-wide py-5">
        <div class="text-center mb-5">
            <span class="text-warning fw-bold mb-2 d-block">ACCOMMODATIONS</span>
            <h2 class="display-5 fw-bold position-relative d-inline-block">Our Rooms
                <span class="position-absolute bottom-0 start-50 translate-middle-x bg-warning" style="height: 4px; width: 80px;"></span>
            </h2>
            <p class="lead text-muted mt-3 mx-auto" style="max-width: 700px;">Each space is meticulously designed to provide the ultimate in comfort and luxury</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden room-card">
                    <div class="position-relative overflow-hidden" style="height: 300px;">
                        <img src="images/hotelOne.jpg" class="object-fit-cover w-100 h-100" alt="Deluxe Room">
                        <div class="room-overlay d-flex align-items-end">
                            <div class="room-price bg-dark text-white p-3 w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5">From $50/night</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3 class="h4 mb-2">Single Room</h3>
                        <div class="d-flex mb-3">
                            <div class="me-3"><i class="fas fa-user-friends text-warning me-2"></i> 1 Guests</div>
                        </div>
                        <p class="text-muted mb-0">Spacious rooms with modern amenities, plush bedding, and stunning city or garden views.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden room-card">
                    <div class="position-relative overflow-hidden" style="height: 300px;">
                        <img src="images/hotelTwo.jpg" class="object-fit-cover w-100 h-100" alt="Executive Suite">
                        <div class="room-overlay d-flex align-items-end">
                            <div class="room-price bg-dark text-white p-3 w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5">From $80/night</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3 class="h4 mb-2">Double Room</h3>
                        <div class="d-flex mb-3">
                            <div class="me-3"><i class="fas fa-user-friends text-warning me-2"></i> 2 Guests</div>
                        </div>
                        <p class="text-muted mb-0">Luxurious suites with separate living areas, premium services, and panoramic city views.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden room-card">
                    <div class="position-relative overflow-hidden" style="height: 300px;">
                        <img src="images/hotelThree.jpg" class="object-fit-cover w-100 h-100" alt="Presidential Suite">
                        <div class="room-overlay d-flex align-items-end">
                            <div class="room-price bg-dark text-white p-3 w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5">From $100/night</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3 class="h4 mb-2">Family Room</h3>
                        <div class="d-flex mb-3">
                            <div class="me-3"><i class="fas fa-user-friends text-warning me-2"></i> 4 Guests</div>
                        </div>
                        <p class="text-muted mb-0">Comfortable and spacious, perfect for families with a queen bed, single bed, and cozy seating area.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ auth()->check() ? route('reservations.room') : route('register') }}" class="btn btn-outline-dark btn-lg px-4 py-3">Book Rooms</a>
        </div>
    </div>
</section>

<!-- Residential Suites Section -->
<section id="residential-suites" class="py-5 bg-white">
    <div class="ds-wide py-5">
        <div class="text-center mb-5">
            <span class="text-warning fw-bold mb-2 d-block">EXTENDED STAY</span>
            <h2 class="display-5 fw-bold position-relative d-inline-block">Residential Suites
                <span class="position-absolute bottom-0 start-50 translate-middle-x bg-warning" style="height: 4px; width: 80px;"></span>
            </h2>
            <p class="lead text-muted mt-3 mx-auto" style="max-width: 700px;">
                Designed for those who desire the comforts of home with the luxury of hotel living
            </p>
        </div>

        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <div class="position-relative rounded-4 overflow-hidden">
                    <img src="{{ asset('images/hotelOne.jpg') }}"
                         class="img-fluid w-100"
                         alt="Residential Suite Living Area"
                         style="min-height: 400px; object-fit: cover;">
                    <div class="position-absolute bottom-0 start-0 p-4 text-white">
                        <h3 class="mb-0">Executive Residential Suite</h3>
                        <p class="mb-0">From $1,000 (week)/ $3,500 (month)</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-5">
                    <h3 class="mb-4">Luxury Designed for Extended Stays</h3>
                    <p class="lead text-muted mb-4">
                        Our residential suites combine the space and functionality of an apartment with the premium services of a luxury hotel.
                    </p>
                    <ul class="list-unstyled row">
                        <li class="col-md-6 mb-3 d-flex">
                            <i class="fas fa-check-circle text-warning me-2 mt-1"></i>
                            <span>Fully equipped gourmet kitchen</span>
                        </li>
                        <li class="col-md-6 mb-3 d-flex">
                            <i class="fas fa-check-circle text-warning me-2 mt-1"></i>
                            <span>Separate living and dining areas</span>
                        </li>
                        <li class="col-md-6 mb-3 d-flex">
                            <i class="fas fa-check-circle text-warning me-2 mt-1"></i>
                            <span>Weekly housekeeping included</span>
                        </li>
                        <li class="col-md-6 mb-3 d-flex">
                            <i class="fas fa-check-circle text-warning me-2 mt-1"></i>
                            <span>In-suite laundry facilities</span>
                        </li>
                        <li class="col-md-6 mb-3 d-flex">
                            <i class="fas fa-check-circle text-warning me-2 mt-1"></i>
                            <span>24/7 concierge service</span>
                        </li>
                        <li class="col-md-6 mb-3 d-flex">
                            <i class="fas fa-check-circle text-warning me-2 mt-1"></i>
                            <span>Access to all hotel amenities</span>
                        </li>
                    </ul>
                    <a href="{{ auth()->check() ? route('reservations.suite') : route('register') }}" class="btn btn-outline-dark btn-lg mt-3 px-4 py-3">Book Suite </a>
                </div>
            </div>
        </div>

<style>
.card-img-top {
    height: 250px;
    object-fit: cover;
}
</style>

<div class="container-fluid px-4">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <img src="{{ asset('images/hotelTwo.jpg') }}"
                     class="card-img-top"
                     alt="One-Bedroom Suite">
                <div class="card-body">
                    <h3 class="h4 mb-3">One-Bedroom Suite</h3>
                    <p class="text-muted mb-4">Perfect for individuals or couples, featuring a spacious bedroom, living area, and kitchenette.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">From $1,000(week) / $3,500(month)</span>
                        <a href="#" class="btn btn-sm btn-outline-dark">Details</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <img src="{{ asset('images/hotelThree.jpg') }}"
                     class="card-img-top"
                     alt="Two-Bedroom Suite">
                <div class="card-body">
                    <h3 class="h4 mb-3">Two-Bedroom Suite</h3>
                    <p class="text-muted mb-4">Ideal for families or colleagues, with two bedrooms, full kitchen, and dining area.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">From $1,500(week) / $5,500(month)</span>
                        <a href="#" class="btn btn-sm btn-outline-dark">Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
</section>

<section class="py-5 bg-dark text-white position-relative" style="background-image: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('{{ asset('images/heroTwo.jpg') }}'); background-size: cover; background-attachment: fixed;">
    <div class="ds-wide py-5">
        <div class="text-center mb-5">
            <span class="text-warning fw-bold mb-2 d-block">GUEST EXPERIENCES</span>
            <h2 class="display-5 fw-bold position-relative d-inline-block">What Our Guests Say
                <span class="position-absolute bottom-0 start-50 translate-middle-x bg-warning" style="height: 4px; width: 80px;"></span>
            </h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="p-4 rounded-3 bg-white-10 backdrop-blur h-100">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="fs-5 mb-4 fst-italic">"Absolutely stunning hotel with exceptional service. The attention to detail was remarkable and the staff went above and beyond to make our stay perfect. The spa treatments were world-class!"</p>
                    <div class="d-flex align-items-center">
                        {{-- Monogram rather than a stock portrait: no external
                             dependency, and it suits the brand better. --}}
                        <div class="ds-monogram me-3" aria-hidden="true">SJ</div>
                        <div>
                            <h4 class="mb-0">Sarah Johnson</h4>
                            <small class="text-white-50">New York, USA</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="p-4 rounded-3 bg-white-10 backdrop-blur h-100">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="fs-5 mb-4 fst-italic">"The best hotel experience I've had in years. The infinity pool at sunset is something you have to see to believe. The executive suite was spacious and luxurious with amazing city views."</p>
                    <div class="d-flex align-items-center">
                        <div class="ds-monogram me-3" aria-hidden="true">MC</div>
                        <div>
                            <h4 class="mb-0">Michael Chen</h4>
                            <small class="text-white-50">Toronto, Canada</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="gallery" class="py-5 bg-light">
    <div class="ds-wide py-5">
        <div class="text-center mb-5 ds-reveal">
            <span class="ds-eyebrow ds-eyebrow--center">Gallery</span>
            <h2 class="mb-3">Moments at our houses</h2>
            <p class="ds-lead mx-auto">
                Move your cursor across a photograph &mdash; the light bends as it would through the stone.
            </p>
        </div>

        {{-- Local images only: a cross-origin photograph would taint the WebGL
             canvas and the shader upload would throw. --}}
        <div class="ds-gallery ds-reveal" data-ds-gallery>
            <figure class="ds-gl-tile">
                <img src="{{ asset('images/hotelOne.jpg') }}" alt="A guest room with a made bed and city view" loading="lazy">
                <figcaption class="ds-gl-caption">Guest room</figcaption>
            </figure>
            <figure class="ds-gl-tile">
                <img src="{{ asset('images/beach.jpg') }}" alt="The private beach at dusk" loading="lazy">
                <figcaption class="ds-gl-caption">Private beach</figcaption>
            </figure>
            <figure class="ds-gl-tile">
                <img src="{{ asset('images/spa.jpg') }}" alt="Treatment room at the spa" loading="lazy">
                <figcaption class="ds-gl-caption">Wellness spa</figcaption>
            </figure>
            <figure class="ds-gl-tile">
                <img src="{{ asset('images/hotelTwo.jpg') }}" alt="The lobby lounge" loading="lazy">
                <figcaption class="ds-gl-caption">Lobby lounge</figcaption>
            </figure>
            <figure class="ds-gl-tile">
                <img src="{{ asset('images/hotelThree.jpg') }}" alt="A residential suite living area" loading="lazy">
                <figcaption class="ds-gl-caption">Residential suite</figcaption>
            </figure>
        </div>
    </div>
</section>

<section class="py-5 bg-warning position-relative overflow-hidden">
    <div class="cta-pattern position-absolute top-0 start-0 w-100 h-100"></div>
    <div class="container py-5 text-center position-relative">
        <h2 class="display-5 fw-bold mb-4">Ready for an unforgettable experience?</h2>
        <p class="fs-4 mb-5">Book your stay today and discover luxury redefined</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ auth()->check() ? route('reservations.room') : route('register') }}" class="btn btn-dark btn-lg px-4 py-3 rounded-pill hover-lift">
                <i class="fas fa-calendar-check me-2"></i> Book Now
            </a>
            <a href="tel:+18005551234" class="btn btn-outline-dark btn-lg px-4 py-3 rounded-pill hover-lift">
                <i class="fas fa-phone me-2"></i> +1 (800) 555-1234
            </a>
            <a href="mailto:reservations@luxuryhotel.com" class="btn btn-outline-dark btn-lg px-4 py-3 rounded-pill hover-lift">
                <i class="fas fa-envelope me-2"></i> Email Us
            </a>
        </div>
    </div>
</section>

<!-- Custom Styles -->
<style>
    /* Modern Luxury Hero Styles */
    .modern-hero {
        position: relative;
        /* One screen exactly. svh is the small viewport height, so a phone's
           collapsing address bar cannot make the hero taller than what is
           actually visible; vh is the fallback where svh is unsupported. */
        height: 100vh;
        height: 100svh;
        /* Was 800px, which is taller than a 720p laptop viewport — it forced the
           hero past the fold on exactly the screens that could least afford it. */
        min-height: 560px;
        overflow: hidden;
        color: #fff;
        display: flex;
        align-items: center;
        /* Without this the layer's rotateX/rotateY are flat and the tilt does
           nothing — a 3D rotation needs a perspective on an ancestor. */
        perspective: 1200px;
    }

    /* Parallax Background Effect */
    .parallax-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .parallax-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        /* Bottom-weighted. With `cover`, a higher percentage slides the photo
           further up and shows more of its lower half — so this crops away the
           empty sky and brings the building's base into frame. (25% did the
           opposite: it pulled the photo down and put more sky on screen.) */
        background-position: center 95%;
        will-change: transform;
    }

    /* The photo drifts against the pointer and tilts slightly, so the hero has
       depth without anything blurring it. Scaled a little so the drift can
       never pull an edge into frame. The transition is what does the easing —
       the pointer sets a target and the layer catches up to it. */
    .foreground-layer {
        z-index: 3;
        transform:
            scale(1.08)
            translate3d(calc(var(--mx, 0) * -18px), calc(var(--my, 0) * -14px), 0)
            rotateX(calc(var(--my, 0) * 1.6deg))
            rotateY(calc(var(--mx, 0) * -1.6deg));
        transition: transform 500ms cubic-bezier(.22, .61, .36, 1);
    }

    .color-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(20,30,40,0.7) 0%, rgba(50,40,30,0.4) 100%);
        z-index: 4;
    }

    /* The content drifts the opposite way to the photo behind it. Opposed
       motion at different rates is what actually reads as depth — moving both
       the same way just slides the whole hero around. */
    .hero-content-wrapper {
        /* Sits below centre rather than on it, so the heading reads against the
           building now that the frame is bottom-weighted, instead of floating
           in the empty upper area. */
        margin-top: clamp(3rem, 11vh, 8rem);
        transform: translate3d(calc(var(--mx, 0) * 9px), calc(var(--my, 0) * 7px), 0);
        transition: transform 600ms cubic-bezier(.22, .61, .36, 1);
    }

    /* Parallax is a pointer affordance. Touch has no hover, and under reduced
       motion the drift is exactly the kind of movement being opted out of, so
       both get the photo sitting still. */
    @media (prefers-reduced-motion: reduce), (hover: none), (pointer: coarse) {
        .foreground-layer,
        .hero-content-wrapper { transform: scale(1.02); transition: none; }
        .hero-content-wrapper { transform: none; }
    }

    /* Hero Content Styling */
    .hero-content-wrapper {
        position: relative;
        z-index: 5;
        width: 100%;
    }

    .hero-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        text-align: center;
    }

    .luxury-badge {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 2rem;
        font-size: 0.9rem;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        color: var(--ds-clay);
    }

    /* Animated Heading */
    .hero-heading {
        /* Was 'Playfair Display', which was never actually loaded — the heading
           had been falling back to the generic serif. This is the largest
           display moment on the site, so it takes the brand display face. */
        font-family: var(--ds-display);
        font-weight: 400;
        font-size: clamp(2.5rem, 7vw, 5.5rem);
        line-height: 1.1;
        margin: 0 auto 1.5rem;
        max-width: 900px;
    }

    .hero-heading .line {
        display: block;
        overflow: hidden;
    }

    .hero-heading .word {
        display: inline-block;
        transform: translateY(100%);
        opacity: 0;
        animation: wordReveal 1s cubic-bezier(0.19, 1, 0.22, 1) forwards;
    }

    .hero-heading .line:nth-child(1) .word {
        animation-delay: 0.3s;
    }

    .hero-heading .line:nth-child(2) .word:nth-child(1) {
        animation-delay: 0.5s;
    }

    .hero-heading .line:nth-child(2) .word:nth-child(2) {
        animation-delay: 0.7s;
    }

    @keyframes wordReveal {
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Decorative Elements */
    .hero-decoration {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 2rem auto;
        max-width: 300px;
    }

    .deco-line {
        height: 1px;
        background: rgba(255,255,255,0.3);
        flex-grow: 1;
    }

    .deco-dot {
        width: 8px;
        height: 8px;
        background: var(--ds-clay);
        border-radius: 50%;
        margin: 0 1rem;
    }

    /* Location Indicator */
    .location-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.8);
    }

    /* Social Proof */
    .social-proof {
        position: absolute;
        right: 2rem;
        bottom: 2rem;
        z-index: 6;
        display: flex;
        gap: 2rem;
    }

    .proof-item {
        text-align: right;
    }

    .proof-value {
        font-size: 1.5rem;
        font-weight: 300;
        color: var(--ds-clay);
        line-height: 1;
    }

    .proof-label {
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        opacity: 0.8;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .modern-hero {
            min-height: 600px;
        }

        .hero-heading {
            font-size: clamp(2rem, 8vw, 3.5rem);
        }

        .social-proof {
            right: 1rem;
            bottom: 1rem;
            gap: 1rem;
        }

        .proof-value {
            font-size: 1.2rem;
        }
    }

    /* Feature Cards */
    .hover-lift {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .icon-wrapper {
        transition: all 0.3s ease;
    }

    .card:hover .icon-wrapper {
        transform: rotateY(180deg);
        background-color: var(--ds-clay) !important;
        color: #000 !important;
    }

    /* Room Cards */
    .room-card {
        transition: all 0.3s ease;
    }

    .room-card:hover {
        transform: translateY(-5px);
    }

    .room-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .room-card:hover .room-overlay {
        opacity: 1;
    }

    .room-price {
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }

    .room-card:hover .room-price {
        transform: translateY(0);
    }

    /* Gallery */
    .gallery-item {
        transition: all 0.3s ease;
    }

    .gallery-caption {
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }

    .gallery-item:hover .gallery-caption {
        transform: translateY(0);
    }

    .hover-zoom img {
        transition: transform 0.5s ease;
    }

    .hover-zoom:hover img {
        transform: scale(1.1);
    }

    /* Testimonials */
    .backdrop-blur {
        backdrop-filter: blur(10px);
    }

    .bg-white-10 {
        background-color: rgba(255, 255, 255, 0.1);
    }

    /* CTA Section */
    .cta-pattern {
        background-image: radial-gradient(rgba(0,0,0,0.1) 2px, transparent 2px);
        background-size: 20px 20px;
        opacity: 0.3;
    }
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.card, .gallery-item, .testimonial');
            const windowHeight = window.innerHeight;

            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementPosition < windowHeight - elementVisible) {
                    element.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        };

        animateOnScroll();
        window.addEventListener('scroll', animateOnScroll);
    } catch (error) {
        console.error('Error in home animations:', error);
    }
});
</script>
@endsection

@section('scripts')
    {{-- Only the gallery loads WebGL now. hero.js is no longer mounted: its
         prism shader refracted the photograph, which is what made the building
         look soft and put colour fringing on its edges. The file is still in
         resources/js if the effect is ever wanted back. --}}
    @vite(['resources/js/gallery.js'])

    <script>
        (function () {
            'use strict';

            var hero = document.querySelector('.modern-hero');
            if (!hero) return;

            // Matches the CSS opt-outs above, so the listener is never even
            // attached on touch or under reduced motion.
            var enabled = window.matchMedia('(hover: hover) and (pointer: fine)').matches
                && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!enabled) return;

            var mx = 0, my = 0, ticking = false;

            var apply = function () {
                ticking = false;
                hero.style.setProperty('--mx', mx.toFixed(4));
                hero.style.setProperty('--my', my.toFixed(4));
            };

            hero.addEventListener('pointermove', function (e) {
                var r = hero.getBoundingClientRect();
                if (!r.width || !r.height) return;
                // Normalise to -1..1 about the centre, so the CSS can express
                // each layer's travel as a plain multiplier.
                mx = ((e.clientX - r.left) / r.width - 0.5) * 2;
                my = ((e.clientY - r.top) / r.height - 0.5) * 2;
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(apply);
            });

            // Settle back to centre when the pointer leaves, otherwise the hero
            // stays frozen at whatever angle it was last pushed to.
            hero.addEventListener('pointerleave', function () {
                mx = my = 0;
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(apply);
            });
        })();
    </script>
@endsection