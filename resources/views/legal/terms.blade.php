@extends('legal.layout')

@section('eyebrow', 'Legal')
@section('heading', 'Terms & Conditions')
@section('updated', date('F Y'))

@section('body')
    <p class="ds-lead">
        These terms govern your use of this website and any reservation you make
        through it with {{ config('hotel.legal_entity') }} (&ldquo;{{ config('app.name') }}&rdquo;,
        &ldquo;we&rdquo;, &ldquo;us&rdquo;), company registration {{ config('hotel.registration_no') }}.
        By making a booking you accept them.
    </p>

    <h2>1. Bookings and confirmation</h2>
    <p>
        A reservation request is made against a <em>room type</em>, not a specific
        room. The physical room is assigned by reception at check-in, and we
        reserve the right to allocate any room of the type booked. A booking is
        held from the moment it is submitted and remains subject to availability
        until confirmed.
    </p>
    <p>
        You must be 18 or over to make a reservation, and the guest named on the
        booking must present matching photo identification at check-in.
    </p>

    <h2>2. Rates and payment</h2>
    <p>
        All rates are quoted per room in {{ \App\Support\Money::code() }} and, unless
        stated otherwise, exclude government taxes and service charges applicable
        at the time of stay. Residential suites are charged at a weekly or monthly
        rate according to the duration you select.
    </p>
    <p>
        A stay booked as four consecutive weeks is automatically converted to a
        one-month booking and charged at the monthly rate, which is the lower of
        the two. You do not need to request this.
    </p>
    <p>
        Payment is settled at the property on departure. Any card provided at the
        time of booking is held as a guarantee only; see
        <a href="{{ route('legal.cancellation') }}">our cancellation policy</a>.
    </p>

    <h2>3. Card guarantees</h2>
    <p>
        We do not store your full card number. A card supplied to guarantee a
        booking is validated and then retained only as the last four digits and
        the expiry date, which is what reception sees. Reservations for the same
        day that carry no card guarantee are automatically released at 19:00.
    </p>

    <h2>4. Arrival and departure</h2>
    <p>
        Check-in is from {{ config('hotel.check_in_time') }} and check-out is by
        {{ config('hotel.check_out_time') }}. Late departure may be available on
        request and may be charged. A guaranteed room not claimed by the end of
        the arrival day is treated as a no-show and charged at the full room rate
        for the first night.
    </p>

    <h2>5. Occupancy</h2>
    <p>
        Every room type has a maximum occupancy, which is shown when you book and
        enforced at the time of booking. We cannot accommodate more guests in a
        room than that maximum, and reception may refuse check-in where the party
        exceeds it.
    </p>

    <h2>6. Group and travel agency bookings</h2>
    <p>
        Block bookings placed through a travel agency require a minimum of three
        rooms and are limited to three residential suites per booking. Group
        quotations are apportioned evenly across the rooms reserved.
    </p>

    <h2>7. Conduct and property</h2>
    <p>
        Guests are responsible for damage caused to a room or to hotel property
        during their stay, which may be added to the folio. We may end a stay
        without refund where a guest's conduct endangers or seriously disturbs
        other guests or staff. All our properties are non-smoking indoors.
    </p>

    <h2>8. Liability</h2>
    <p>
        We are not liable for loss or damage to personal belongings left
        unattended in public areas. Nothing in these terms limits liability for
        death or personal injury caused by our negligence, or for fraud, where
        such limitation is not permitted by law.
    </p>

    <h2>9. Your account</h2>
    <p>
        You are responsible for keeping your account credentials confidential and
        for activity carried out under your account. Tell us immediately if you
        believe your account has been used without your permission.
    </p>

    <h2>10. Changes to these terms</h2>
    <p>
        We may update these terms. The version in force is the one published on
        this page at the time your booking is made. Continued use of the site
        after a change means you accept the revised terms.
    </p>

    <h2>11. Governing law</h2>
    <p>
        These terms are governed by the laws of Sri Lanka, and the courts of Sri
        Lanka have exclusive jurisdiction over any dispute arising from them.
    </p>
@endsection
