@extends('layouts.app')

@section('content')
<div class="ds-page-head ds-reveal">
    <span class="ds-eyebrow">Reservation</span>
    <h1>Book a room</h1>
    <p class="ds-lead mt-3 mb-0">
        Choose a house and a room type. We assign your specific room when you arrive,
        so you always get the best one available on the day.
    </p>
</div>

@if ($errors->any())
    <div class="alert alert-danger ds-reveal">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('reservations.store') }}" id="reservationForm" novalidate>
    @csrf
    <input type="hidden" name="is_suite" value="0">

    <div class="row g-5">
        {{-- ---------------- form ---------------- --}}
        <div class="col-lg-7" data-ds-stagger="70">

            <section class="mb-5 ds-reveal">
                <h3 class="mb-4">Where</h3>

                <div class="mb-4">
                    <label for="branch_id" class="form-label">House</label>
                    <select name="branch_id" id="branch_id" class="form-select form-select-lg" required>
                        <option value="">Select a house</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-2">
                    <label for="room_type_id" class="form-label">Room type</label>
                    <select name="room_type_id" id="room_type_id" class="form-select form-select-lg" required>
                        <option value="">Select a room type</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}"
                                    data-max-occupants="{{ $type->max_occupants }}"
                                    data-rate="{{ $type->price_per_night }}"
                                    data-name="{{ $type->name }}"
                                    @selected(old('room_type_id') == $type->id)>
                                {{ $type->name }} — @money($type->price_per_night)/night · up to {{ $type->max_occupants }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </section>

            <hr class="ds-rule">

            <section class="my-5 ds-reveal">
                <h3 class="mb-4">When</h3>
                <div class="row g-4">
                    <div class="col-sm-6">
                        <label for="check_in_date" class="form-label">Arriving</label>
                        <input type="date" name="check_in_date" id="check_in_date"
                               class="form-control form-control-lg" required
                               value="{{ old('check_in_date') }}"
                               min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}">
                        @error('check_in_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-6">
                        <label for="check_out_date" class="form-label">Departing</label>
                        <input type="date" name="check_out_date" id="check_out_date"
                               class="form-control form-control-lg" required
                               value="{{ old('check_out_date') }}"
                               min="{{ \Carbon\Carbon::tomorrow()->addDay()->toDateString() }}">
                        @error('check_out_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="number_of_occupants" class="form-label">Guests</label>
                    <input type="number" name="number_of_occupants" id="number_of_occupants"
                           class="form-control form-control-lg" min="1" required
                           value="{{ old('number_of_occupants') }}"
                           placeholder="Select a room type first">
                    <div id="occupants-error" class="invalid-feedback d-block d-none"></div>
                    @error('number_of_occupants') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </section>

            <hr class="ds-rule">

            <section class="my-5 ds-reveal">
                <h3 class="mb-2">Guarantee</h3>
                <p class="ds-muted mb-4" style="font-size: .92rem;">
                    Optional, but a card holds your room past 7pm on the day you arrive.
                    We store only the last four digits and the expiry date &mdash; never the full number.
                </p>

                <div class="row g-4">
                    <div class="col-sm-7">
                        <label for="credit_card_details" class="form-label">Card number</label>
                        <input type="text" name="credit_card_details" id="credit_card_details"
                               class="form-control form-control-lg" maxlength="19"
                               placeholder="4242 4242 4242 4242" inputmode="numeric" autocomplete="off">
                        <div id="credit-card-error" class="invalid-feedback d-block d-none"></div>
                        @error('credit_card_details') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-5">
                        <label for="card_expiry" class="form-label">Expiry</label>
                        <input type="text" name="card_expiry" id="card_expiry"
                               class="form-control form-control-lg" maxlength="5"
                               placeholder="MM/YY" value="{{ old('card_expiry') }}" autocomplete="off">
                        @error('card_expiry') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </section>
        </div>

        {{-- ---------------- live summary ---------------- --}}
        <div class="col-lg-5">
            <aside class="ds-summary ds-reveal">
                <div class="ds-figure ds-ratio-16-9 mb-4">
                    <img src="{{ asset('images/hotelOne.jpg') }}" alt="" loading="lazy">
                </div>

                <span class="ds-eyebrow">Your stay</span>

                <dl class="ds-summary__list">
                    <div><dt>House</dt><dd data-summary="branch">&mdash;</dd></div>
                    <div><dt>Room</dt><dd data-summary="room">&mdash;</dd></div>
                    <div><dt>Arriving</dt><dd data-summary="in">&mdash;</dd></div>
                    <div><dt>Departing</dt><dd data-summary="out">&mdash;</dd></div>
                    <div><dt>Nights</dt><dd data-summary="nights">&mdash;</dd></div>
                    <div><dt>Guests</dt><dd data-summary="guests">&mdash;</dd></div>
                </dl>

                <hr class="ds-rule my-4">

                <div class="d-flex justify-content-between align-items-end">
                    <span class="ds-eyebrow mb-0">Estimate</span>
                    <span class="ds-price" data-summary="total">&mdash;</span>
                </div>
                <p class="form-text mt-2 mb-0">
                    Room charge only. Extras are settled at check-out.
                </p>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Reserve</button>
                    <a href="{{ route('customer.reservations') }}" class="btn btn-link">Cancel</a>
                </div>
            </aside>
        </div>
    </div>
</form>

<style>
    .ds-summary {
        background: var(--ds-white);
        border: 1px solid var(--ds-sand);
        border-radius: var(--ds-radius);
        padding: clamp(1.25rem, 2.5vw, 2rem);
    }
    @media (min-width: 992px) {
        /* Keeps the price in view while the guest works down the form. */
        .ds-summary { position: sticky; top: 6.5rem; }
    }
    .ds-summary__list { margin: 0; }
    .ds-summary__list > div {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: .6rem 0;
        border-bottom: 1px solid var(--ds-shell);
    }
    .ds-summary__list > div:last-child { border-bottom: 0; }
    .ds-summary__list dt {
        font-size: .7rem;
        font-weight: 400;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--ds-faint);
    }
    .ds-summary__list dd {
        margin: 0;
        text-align: right;
        font-weight: 400;
        color: var(--ds-charcoal);
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form        = document.getElementById('reservationForm');
    var branch      = document.getElementById('branch_id');
    var roomType    = document.getElementById('room_type_id');
    var occupants   = document.getElementById('number_of_occupants');
    var checkIn     = document.getElementById('check_in_date');
    var checkOut    = document.getElementById('check_out_date');
    var card        = document.getElementById('credit_card_details');
    var occErr      = document.getElementById('occupants-error');
    var cardErr     = document.getElementById('credit-card-error');

    var currency = @json(\App\Support\Money::symbol());

    // ---------- live summary ----------
    // Purely a courtesy: the server recalculates the real total on submit.
    function summary(key) { return document.querySelector('[data-summary="' + key + '"]'); }

    function nightsBetween() {
        if (!checkIn.value || !checkOut.value) return 0;
        var a = new Date(checkIn.value), b = new Date(checkOut.value);
        var n = Math.round((b - a) / 86400000);
        return n > 0 ? n : 0;
    }

    function formatDate(value) {
        if (!value) return '—';
        return new Date(value + 'T00:00:00').toLocaleDateString('en-GB', {
            day: 'numeric', month: 'short', year: 'numeric'
        });
    }

    function updateSummary() {
        var option = roomType.selectedOptions[0];
        var rate = option ? parseFloat(option.dataset.rate || 0) : 0;
        var nights = nightsBetween();

        summary('branch').textContent = branch.value ? branch.selectedOptions[0].text : '—';
        summary('room').textContent   = option && option.value ? option.dataset.name : '—';
        summary('in').textContent     = formatDate(checkIn.value);
        summary('out').textContent    = formatDate(checkOut.value);
        summary('nights').textContent = nights || '—';
        summary('guests').textContent = occupants.value || '—';

        summary('total').textContent = (rate && nights)
            ? currency + (rate * nights).toLocaleString('en-US', {
                  minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '—';
    }

    // ---------- occupancy cap ----------
    function maxOccupants() {
        var option = roomType.selectedOptions[0];
        return parseInt(option && option.dataset.maxOccupants, 10) || 1;
    }

    function validateOccupants() {
        var value = parseInt(occupants.value, 10) || 0;
        var max = maxOccupants();

        occErr.classList.add('d-none');

        if (!roomType.value) {
            occErr.textContent = 'Please choose a room type first.';
            occErr.classList.remove('d-none');
            occupants.value = '';
            return false;
        }
        if (value < 1) {
            occErr.textContent = 'At least one guest is required.';
            occErr.classList.remove('d-none');
            return false;
        }
        if (value > max) {
            // The server enforces this too; correcting inline avoids a round trip.
            occErr.textContent = 'This room type sleeps at most ' + max + '.';
            occErr.classList.remove('d-none');
            occupants.value = max;
            return false;
        }
        return true;
    }

    occupants.disabled = !roomType.value;

    roomType.addEventListener('change', function () {
        var max = maxOccupants();
        occupants.disabled = !roomType.value;
        occupants.max = max;
        occupants.placeholder = 'Up to ' + max;
        validateOccupants();
        updateSummary();
    });

    occupants.addEventListener('input', function () { validateOccupants(); updateSummary(); });
    branch.addEventListener('change', updateSummary);

    // ---------- dates ----------
    checkIn.addEventListener('change', function () {
        var next = new Date(checkIn.value);
        next.setDate(next.getDate() + 1);
        checkOut.min = next.toISOString().split('T')[0];
        if (checkOut.value && checkOut.value <= checkIn.value) checkOut.value = '';
        updateSummary();
    });
    checkOut.addEventListener('change', updateSummary);

    // ---------- card ----------
    card.addEventListener('input', function (e) {
        var digits = e.target.value.replace(/\D/g, '').slice(0, 16);
        e.target.value = digits.replace(/(.{4})/g, '$1 ').trim();
        validateCard();
    });

    function luhn(number) {
        var sum = 0, even = false;
        for (var i = number.length - 1; i >= 0; i--) {
            var d = parseInt(number[i], 10);
            if (even) { d *= 2; if (d > 9) d -= 9; }
            sum += d;
            even = !even;
        }
        return sum % 10 === 0;
    }

    function validateCard() {
        var value = card.value.replace(/\D/g, '');
        cardErr.classList.add('d-none');

        if (!value) return true;                       // optional field
        if (value.length < 13 || value.length > 19) {
            cardErr.textContent = 'A card number is 13 to 19 digits.';
            cardErr.classList.remove('d-none');
            return false;
        }
        if (!luhn(value)) {
            cardErr.textContent = 'That card number is not valid.';
            cardErr.classList.remove('d-none');
            return false;
        }
        return true;
    }

    form.addEventListener('submit', function (e) {
        if (!validateOccupants() || !validateCard()) e.preventDefault();
    });

    updateSummary();
});
</script>
@endsection
