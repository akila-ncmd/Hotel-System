@extends('legal.layout')

@section('eyebrow', 'Before you book')
@section('heading', 'Cancellation Policy')
@section('updated', date('F Y'))

@section('body')
    <p class="ds-lead">
        Plans change. This is exactly what happens when yours do, with no small
        print held back for the desk.
    </p>

    <h2>Cancelling a room</h2>
    <p>
        Cancel free of charge up to <strong>{{ config('hotel.cancellation_hours') }} hours</strong>
        before your arrival date. Cancel from your reservations page and the room
        is released immediately &mdash; there is nothing to call about and nothing
        to pay.
    </p>
    <p>
        Inside {{ config('hotel.cancellation_hours') }} hours we charge the first
        night at the room rate. The rest of the stay is released.
    </p>

    <h2>Residential suites</h2>
    <p>
        Suites are let by the week or the month and are cancelled on the same
        {{ config('hotel.cancellation_hours') }}-hour notice. Inside that window
        one week of the agreed rate is charged, whichever duration was booked.
    </p>

    <h2>If you do not arrive</h2>
    <p>
        A guaranteed booking that is not claimed by the end of the arrival day is
        recorded as a no-show and charged at the full room rate for the first
        night. This is applied automatically at 19:00 the following day and
        appears on your folio.
    </p>

    <h2>Bookings held without a card</h2>
    <p>
        A same-day booking with no card guarantee is <strong>released
        automatically at 19:00</strong> on the day of arrival, and we will email
        you when that happens. Nothing is charged. If you are arriving late, add a
        card guarantee to the booking or call reception and we will hold the room.
    </p>

    <h2>Shortening a stay</h2>
    <p>
        Tell reception before {{ config('hotel.check_out_time') }} on the day you
        want to leave and only the nights you stayed are charged. Departing
        earlier without notice is charged as booked.
    </p>

    <h2>Group and travel agency bookings</h2>
    <p>
        Block bookings of three rooms or more are cancelled under the terms of the
        agency quotation, which take precedence over this page. Partial release of
        a block is arranged through the agency.
    </p>

    <h2>If we have to cancel</h2>
    <p>
        Very rarely a room becomes unusable. If we cannot honour a confirmed
        booking we will find you an equivalent room at another of our houses, or
        at a comparable hotel nearby, and cover the difference in rate for the
        first night. Nothing is charged for the room we could not provide.
    </p>

    <div class="ds-panel ds-legal__callout">
        <p class="mb-0">
            Anything unusual &mdash; a delayed flight, a medical reason, a change
            of dates rather than a cancellation &mdash; is worth a phone call.
            Reception answers around the clock on
            <a href="tel:{{ config('hotel.hotline_tel') }}">{{ config('hotel.hotline') }}</a>.
        </p>
    </div>
@endsection
