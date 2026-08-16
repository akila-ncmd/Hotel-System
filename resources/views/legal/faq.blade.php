@extends('legal.layout')

@section('eyebrow', 'Guest information')
@section('heading', 'Frequently asked questions')

@section('body')
    <p class="ds-lead">
        The questions reception is asked most. If yours is not here, the desk is
        staffed at every hour of the day.
    </p>

    {{-- Native <details>: keyboard-operable, screen-reader-announced and
         findable by the browser's own in-page search, with no JS at all. --}}
    <div class="ds-faq">
        <details class="ds-faq__item">
            <summary>What time can I check in and check out?</summary>
            <p>
                Check-in is from {{ config('hotel.check_in_time') }} and check-out is by
                {{ config('hotel.check_out_time') }}. Arriving earlier is usually fine
                &mdash; we will hold your luggage even if the room is not ready.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>Do I choose my room when I book?</summary>
            <p>
                You choose a room <em>type</em>. The particular room is assigned by
                reception on the day, which lets us give you the best one available
                of that type. If you need a specific room &mdash; a quiet floor, an
                accessible bathroom, two rooms together &mdash; call us after
                booking and we will note it against your reservation.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>When am I charged?</summary>
            <p>
                At the property, on departure. A card given at the time of booking
                is held only as a guarantee; nothing is taken from it in advance.
                Rates are quoted in {{ \App\Support\Money::code() }}.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>Do I have to give a card to book?</summary>
            <p>
                Not for a future date. For a booking made for the same day, a card
                guarantee is what keeps the room after 19:00 &mdash; unguaranteed
                same-day bookings are released automatically at that hour and we
                email you to say so.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>Is my card number stored?</summary>
            <p>
                No. It is checked at the time you enter it and then discarded. All
                that remains against your booking is the last four digits and the
                expiry date, so reception can identify the guarantee.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>How do I cancel or change a booking?</summary>
            <p>
                From your reservations page, at any time. Free up to
                {{ config('hotel.cancellation_hours') }} hours before arrival; the
                full terms are on the
                <a href="{{ route('legal.cancellation') }}">cancellation policy</a> page.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>How many people can stay in a room?</summary>
            <p>
                Each room type has a published maximum occupancy, and the booking
                form will not let you exceed it. Children count towards it. If your
                party does not fit a single type, book two rooms or call us.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>What is a residential suite?</summary>
            <p>
                A suite let by the week or the month rather than by the night, for
                long stays. You choose the duration and we work out the departure
                date; four weeks is automatically billed as one month, at the lower
                monthly rate.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>Can you take a group booking?</summary>
            <p>
                Yes, from three rooms upwards, through a travel agency or directly
                with us. A booking may include up to three residential suites.
                Call the hotline and we will quote.
            </p>
        </details>

        <details class="ds-faq__item">
            <summary>Where are you?</summary>
            <p>
                Three houses: Colombo (Galle Road), Kandy (Peradeniya Road) and
                Galle (Lighthouse Street). Our registered address is
                {{ config('hotel.address') }}.
            </p>
        </details>
    </div>
@endsection
