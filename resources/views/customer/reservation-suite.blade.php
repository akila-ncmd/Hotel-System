@extends('layouts.app')

@section('content')
<div class="ds-page-head ds-reveal">
    <span class="ds-eyebrow">Extended stay</span>
    <h1>Book a residential suite</h1>
    <p class="ds-lead mt-3 mb-0">
        Suites are taken by the week or the month rather than by the night.
        Your departure date is worked out from the duration you choose.
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
    <input type="hidden" name="is_suite" value="1">

    <div class="row g-5">
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

                <div>
                    <label for="room_type_id" class="form-label">Suite</label>
                    <select name="room_type_id" id="room_type_id" class="form-select form-select-lg" required>
                        <option value="">Select a suite</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}"
                                    data-max-occupants="{{ $type->max_occupants }}"
                                    data-weekly="{{ $type->weekly_rate }}"
                                    data-monthly="{{ $type->monthly_rate }}"
                                    data-name="{{ $type->name }}"
                                    @selected(old('room_type_id') == $type->id)>
                                {{ $type->name }} — @money($type->weekly_rate)/week · @money($type->monthly_rate)/month
                            </option>
                        @endforeach
                    </select>
                    @error('room_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </section>

            <hr class="ds-rule">

            <section class="my-5 ds-reveal">
                <h3 class="mb-4">How long</h3>

                <div class="mb-4">
                    <label for="check_in_date" class="form-label">Arriving</label>
                    <input type="date" name="check_in_date" id="check_in_date"
                           class="form-control form-control-lg" required
                           value="{{ old('check_in_date') }}"
                           min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}">
                    @error('check_in_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="row g-4">
                    <div class="col-sm-5">
                        <label for="duration_value" class="form-label">Duration</label>
                        <input type="number" name="duration_value" id="duration_value"
                               class="form-control form-control-lg" min="1" required
                               value="{{ old('duration_value') }}" placeholder="e.g. 2">
                        @error('duration_value') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-7">
                        <label for="duration_type" class="form-label">Billed by</label>
                        <select name="duration_type" id="duration_type" class="form-select form-select-lg" required>
                            <option value="weeks"  @selected(old('duration_type') == 'weeks')>Weeks</option>
                            <option value="months" @selected(old('duration_type') == 'months')>Months</option>
                        </select>
                        @error('duration_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div id="duration-error" class="invalid-feedback d-block d-none"></div>

                {{-- Surfacing the 4-weeks rule up front: guests should not be
                     surprised that the system rewrote their booking. --}}
                <div class="ds-note mt-4" id="month-note" hidden>
                    <i class="bi bi-info-circle me-2"></i>
                    Four weeks is billed as one month, at the cheaper monthly rate.
                </div>
            </section>

            <hr class="ds-rule">

            <section class="my-5 ds-reveal">
                <h3 class="mb-4">Guests</h3>
                <label for="number_of_occupants" class="form-label">Occupants</label>
                <input type="number" name="number_of_occupants" id="number_of_occupants"
                       class="form-control form-control-lg" min="1" required
                       value="{{ old('number_of_occupants') }}"
                       placeholder="Select a suite first">
                <div id="occupants-error" class="invalid-feedback d-block d-none"></div>
                @error('number_of_occupants') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </section>

            <hr class="ds-rule">

            <section class="my-5 ds-reveal">
                <h3 class="mb-2">Guarantee</h3>
                <p class="ds-muted mb-4" style="font-size: .92rem;">
                    Optional. We store only the last four digits and the expiry date.
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

        <div class="col-lg-5">
            <aside class="ds-summary ds-reveal">
                <div class="ds-figure ds-ratio-16-9 mb-4">
                    <img src="{{ asset('images/hotelThree.jpg') }}" alt="" loading="lazy">
                </div>

                <span class="ds-eyebrow">Your stay</span>

                <dl class="ds-summary__list">
                    <div><dt>House</dt><dd data-summary="branch">&mdash;</dd></div>
                    <div><dt>Suite</dt><dd data-summary="room">&mdash;</dd></div>
                    <div><dt>Arriving</dt><dd data-summary="in">&mdash;</dd></div>
                    <div><dt>Departing</dt><dd data-summary="out">&mdash;</dd></div>
                    <div><dt>Billed as</dt><dd data-summary="billed">&mdash;</dd></div>
                    <div><dt>Guests</dt><dd data-summary="guests">&mdash;</dd></div>
                </dl>

                <hr class="ds-rule my-4">

                <div class="d-flex justify-content-between align-items-end">
                    <span class="ds-eyebrow mb-0">Estimate</span>
                    <span class="ds-price" data-summary="total">&mdash;</span>
                </div>
                <p class="form-text mt-2 mb-0">Suite charge only. Extras are settled at check-out.</p>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Reserve suite</button>
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
    @media (min-width: 992px) { .ds-summary { position: sticky; top: 6.5rem; } }
    .ds-summary__list { margin: 0; }
    .ds-summary__list > div {
        display: flex; justify-content: space-between; gap: 1rem;
        padding: .6rem 0; border-bottom: 1px solid var(--ds-shell);
    }
    .ds-summary__list > div:last-child { border-bottom: 0; }
    .ds-summary__list dt {
        font-size: .7rem; letter-spacing: .14em; text-transform: uppercase;
        color: var(--ds-faint); font-weight: 400;
    }
    .ds-summary__list dd { margin: 0; text-align: right; color: var(--ds-charcoal); }

    .ds-note {
        background: var(--ds-shell);
        border-left: 2px solid var(--ds-clay);
        padding: .9rem 1.1rem;
        font-size: .9rem;
        color: var(--ds-muted);
        border-radius: var(--ds-radius);
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form       = document.getElementById('reservationForm');
    var branch     = document.getElementById('branch_id');
    var roomType   = document.getElementById('room_type_id');
    var checkIn    = document.getElementById('check_in_date');
    var durValue   = document.getElementById('duration_value');
    var durType    = document.getElementById('duration_type');
    var occupants  = document.getElementById('number_of_occupants');
    var card       = document.getElementById('credit_card_details');
    var durErr     = document.getElementById('duration-error');
    var occErr     = document.getElementById('occupants-error');
    var cardErr    = document.getElementById('credit-card-error');
    var monthNote  = document.getElementById('month-note');

    var currency = @json(\App\Support\Money::symbol());

    function summary(key) { return document.querySelector('[data-summary="' + key + '"]'); }

    // Mirrors the server's rule: exactly 4 weeks becomes 1 month, billed at the
    // monthly rate. Shown here so the estimate matches what is actually stored.
    function effectiveDuration() {
        var value = parseInt(durValue.value, 10) || 0;
        var type = durType.value;
        if (type === 'weeks' && value === 4) return { value: 1, type: 'months', rewritten: true };
        return { value: value, type: type, rewritten: false };
    }

    function formatDate(d) {
        return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function updateSummary() {
        var option = roomType.selectedOptions[0];
        var eff = effectiveDuration();

        monthNote.hidden = !eff.rewritten;

        summary('branch').textContent = branch.value ? branch.selectedOptions[0].text : '—';
        summary('room').textContent   = option && option.value ? option.dataset.name : '—';
        summary('in').textContent     = checkIn.value ? formatDate(new Date(checkIn.value + 'T00:00:00')) : '—';
        summary('guests').textContent = occupants.value || '—';

        if (eff.value > 0) {
            summary('billed').textContent = eff.value + ' ' + (eff.value === 1
                ? eff.type.replace(/s$/, '')
                : eff.type);
        } else {
            summary('billed').textContent = '—';
        }

        // Derived departure date — the guest never types this.
        if (checkIn.value && eff.value > 0) {
            var out = new Date(checkIn.value + 'T00:00:00');
            if (eff.type === 'weeks') out.setDate(out.getDate() + eff.value * 7);
            else out.setMonth(out.getMonth() + eff.value);
            summary('out').textContent = formatDate(out);
        } else {
            summary('out').textContent = '—';
        }

        var rate = option
            ? parseFloat(eff.type === 'weeks' ? option.dataset.weekly : option.dataset.monthly) || 0
            : 0;

        summary('total').textContent = (rate && eff.value)
            ? currency + (rate * eff.value).toLocaleString('en-US', {
                  minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '—';
    }

    function maxOccupants() {
        var option = roomType.selectedOptions[0];
        return parseInt(option && option.dataset.maxOccupants, 10) || 1;
    }

    function validateOccupants() {
        var value = parseInt(occupants.value, 10) || 0;
        var max = maxOccupants();
        occErr.classList.add('d-none');

        if (!roomType.value) {
            occErr.textContent = 'Please choose a suite first.';
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
            occErr.textContent = 'This suite sleeps at most ' + max + '.';
            occErr.classList.remove('d-none');
            occupants.value = max;
            return false;
        }
        return true;
    }

    function validateDuration() {
        var value = parseInt(durValue.value, 10) || 0;
        durErr.classList.add('d-none');
        if (value < 1) {
            durErr.textContent = 'Enter how many weeks or months you are staying.';
            durErr.classList.remove('d-none');
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

    [branch, checkIn, durType].forEach(function (el) {
        el.addEventListener('change', updateSummary);
    });
    durValue.addEventListener('input', function () { validateDuration(); updateSummary(); });
    occupants.addEventListener('input', function () { validateOccupants(); updateSummary(); });

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
            sum += d; even = !even;
        }
        return sum % 10 === 0;
    }

    function validateCard() {
        var value = card.value.replace(/\D/g, '');
        cardErr.classList.add('d-none');
        if (!value) return true;
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
        if (!validateOccupants() || !validateDuration() || !validateCard()) e.preventDefault();
    });

    updateSummary();
});
</script>
@endsection
