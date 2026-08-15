@extends('layouts.app')

@section('content')
<!-- Hero Section - Updated with Image Background -->
<section class="modern-hero">
    <!-- Parallax Background Layers -->
    <div class="parallax-container">
        <div class="parallax-layer background-layer" style="background-image: url('images/heroOne.jpg');"></div>
        <div class="parallax-layer mid-layer" style="background-image: url('images/heroTwo.jpg');"></div>
        <div class="parallax-layer foreground-layer" style="background-image: url('images/heroThree.jpg');"></div>
        <div class="color-overlay"></div>
    </div>

    <!-- Hero Content with Animated Elements -->
    <div class="hero-content-wrapper">
        <div class="container">
            <div class="hero-content">
                <!-- Animated Badge -->
                <div class="luxury-badge animate__animated animate__fadeIn">
                    <span>5-Star Luxury</span>
                    <svg width="40" height="10" viewBox="0 0 40 10">
                        <path d="M0,5 L40,5" stroke="#c8a97e" stroke-width="2" stroke-dasharray="5,3"/>
                    </svg>
                </div>

                <!-- Main Heading with Split Animation -->
                <h1 class="hero-heading">
                    <span class="line"><span class="word">Unparalleled</span></span>
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

    <!-- Scroll Indicator -->
    <div class="modern-scroll-indicator">
        <div class="scroll-text">Scroll</div>
        <div class="scroll-line"></div>
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
    <div class="container py-5">
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

<section class="py-5 bg-white">
    <div class="container py-5">
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
    <div class="container py-5">
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
                    <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a"
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
                <img src="https://images.unsplash.com/photo-1560448204-603b3fc33ddc"
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
                <img src="https://images.unsplash.com/photo-1583847268964-b28dc8f51f92"
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

<section class="py-5 bg-dark text-white position-relative" style="background-image: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('https://images.unsplash.com/photo-1535827841776-24afc1e255ac'); background-size: cover; background-attachment: fixed;">
    <div class="container py-5">
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
                        <div class="rounded-circle overflow-hidden me-3" style="width: 60px; height: 60px; background-image: url('https://randomuser.me/api/portraits/women/32.jpg'); background-size: cover;"></div>
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
                        <div class="rounded-circle overflow-hidden me-3" style="width: 60px; height: 60px; background-image: url('https://randomuser.me/api/portraits/men/75.jpg'); background-size: cover;"></div>
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

<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-warning fw-bold mb-2 d-block">GALLERY</span>
            <h2 class="display-5 fw-bold position-relative d-inline-block">Moments at Our Hotel
                <span class="position-absolute bottom-0 start-50 translate-middle-x bg-warning" style="height: 4px; width: 80px;"></span>
            </h2>
            <p class="lead text-muted mt-3 mx-auto" style="max-width: 700px;">Capturing the essence of luxury and unforgettable experiences</p>
        </div>

        <div class="row g-3">
            <div class="col-md-4 col-6">
                <div class="gallery-item rounded-3 overflow-hidden hover-zoom position-relative">
                    <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4" class="object-fit-cover w-100" style="height: 300px;" alt="Hotel lobby">
                    <div class="gallery-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                        <h5 class="mb-0">Grand Lobby</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="gallery-item rounded-3 overflow-hidden hover-zoom position-relative">
                    <img src="https://images.unsplash.com/photo-1535827841776-24afc1e255ac" class="object-fit-cover w-100" style="height: 300px;" alt="Restaurant">
                    <div class="gallery-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                        <h5 class="mb-0">Gourmet Restaurant</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="gallery-item rounded-3 overflow-hidden hover-zoom position-relative">
                    <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d" class="object-fit-cover w-100" style="height: 300px;" alt="Pool area">
                    <div class="gallery-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                        <h5 class="mb-0">Infinity Pool</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-6">
                <div class="gallery-item rounded-3 overflow-hidden hover-zoom position-relative">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945" class="object-fit-cover w-100" style="height: 300px;" alt="Room interior">
                    <div class="gallery-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                        <h5 class="mb-0">Luxury Suite</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="gallery-item rounded-3 overflow-hidden hover-zoom position-relative">
                    <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791" class="object-fit-cover w-100" style="height: 300px;" alt="Spa">
                    <div class="gallery-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                        <h5 class="mb-0">Wellness Spa</h5>
                    </div>
                </div>
            </div>
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
        height: 100vh;
        min-height: 800px;
        overflow: hidden;
        color: #fff;
        display: flex;
        align-items: center;
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
        background-position: center;
        will-change: transform;
    }

    .background-layer {
        transform: translateZ(-2px) scale(3);
        z-index: 1;
        filter: blur(2px);
    }

    .mid-layer {
        transform: translateZ(-1px) scale(2);
        z-index: 2;
        opacity: 0.8;
    }

    .foreground-layer {
        z-index: 3;
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
        color: #c8a97e;
    }

    /* Animated Heading */
    .hero-heading {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
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
        background: #c8a97e;
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

    /* Modern Scroll Indicator */
    .modern-scroll-indicator {
        position: absolute;
        left: 50%;
        bottom: 2rem;
        transform: translateX(-50%);
        z-index: 6;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .scroll-text {
        font-size: 0.75rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        color: rgba(255,255,255,0.6);
        animation: pulse 2s infinite;
    }

    .scroll-line {
        width: 1px;
        height: 50px;
        background: linear-gradient(to bottom, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 100%);
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
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
        color: #c8a97e;
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
        background-color: #ffc107 !important;
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