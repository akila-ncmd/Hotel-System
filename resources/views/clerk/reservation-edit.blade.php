@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Reservation</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('clerk.reservation.update', $reservation->id) }}" id="reservationForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="is_suite" value="{{ $reservation->roomType->is_suite ? 1 : 0 }}">
        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
        <div class="mb-3">
            <label class="form-label">Branch</label>
            <input type="text" class="form-control" value="{{ $branch->name }}" disabled>
        </div>
        <div class="mb-3">
            <label for="user_id" class="form-label">Customer</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="">Select Customer</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $reservation->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="room_type_id" class="form-label">Room Type</label>
            <select name="room_type_id" id="room_type_id" class="form-control" required>
                <option value="">Select Room Type</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" data-max-occupants="{{ $type->max_occupants }}" data-is-suite="{{ $type->is_suite ? 1 : 0 }}" {{ $reservation->room_type_id == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                        @if($type->is_suite)
                            (Weekly: ${{ number_format($type->weekly_rate, 2) }}, Monthly: ${{ number_format($type->monthly_rate, 2) }})
                        @else
                            (${{ number_format($type->price_per_night, 2) }}/night)
                        @endif
                    </option>
                @endforeach
            </select>
            @error('room_type_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="check_in_date" class="form-label">Check-in Date</label>
            <input type="date" name="check_in_date" id="check_in_date" class="form-control" value="{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('Y-m-d') }}" required min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
            @error('check_in_date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3" id="duration-group" style="display: {{ $reservation->roomType->is_suite ? 'block' : 'none' }};">
            <label for="duration_value" class="form-label">Duration</label>
            <div class="input-group">
                <input type="number" name="duration_value" id="duration_value" class="form-control" min="1" value="{{ $reservation->duration_value ?? 1 }}" required>
                <select name="duration_type" id="duration_type" class="form-control" required>
                    <option value="weeks" {{ $reservation->duration_type == 'weeks' ? 'selected' : '' }}>Weeks</option>
                    <option value="months" {{ $reservation->duration_type == 'months' ? 'selected' : '' }}>Months</option>
                </select>
            </div>
            <div id="duration-error" class="text-danger d-none"></div>
            <small class="form-text text-muted">Note: A 4-week reservation will be charged at the monthly rate.</small>
            @error('duration_value') <span class="text-danger">{{ $message }}</span> @enderror
            @error('duration_type') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3" id="check-out-group" style="display: {{ $reservation->roomType->is_suite ? 'none' : 'block' }};">
            <label for="check_out_date" class="form-label">Check-out Date</label>
            <input type="date" name="check_out_date" id="check_out_date" class="form-control" value="{{ $reservation->check_out_date ? \Carbon\Carbon::parse($reservation->check_out_date)->format('Y-m-d') : '' }}" required min="{{ \Carbon\Carbon::today()->addDay()->format('Y-m-d') }}">
            @error('check_out_date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="number_of_occupants" class="form-label">Number of Occupants</label>
            <input type="number" name="number_of_occupants" id="number_of_occupants" class="form-control" min="1" value="{{ $reservation->number_of_occupants }}" required placeholder="Select room type first">
            <div id="occupants-error" class="text-danger d-none"></div>
            @error('number_of_occupants') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="credit_card_details" class="form-label">Credit Card Details (Optional)</label>
            <input type="text" name="credit_card_details" id="credit_card_details" class="form-control" maxlength="19" value="{{ $reservation->credit_card_details }}" placeholder="e.g., 1234-5678-9012-3456">
            <div id="credit-card-error" class="text-danger d-none"></div>
            @error('credit_card_details') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update Reservation</button>
            <a href="{{ route('clerk.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservationForm');
    const roomTypeSelect = document.getElementById('room_type_id');
    const occupantsInput = document.getElementById('number_of_occupants');
    const creditCardInput = document.getElementById('credit_card_details');
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    const durationValueInput = document.getElementById('duration_value');
    const durationTypeSelect = document.getElementById('duration_type');
    const durationGroup = document.getElementById('duration-group');
    const checkOutGroup = document.getElementById('check-out-group');
    const occupantsError = document.getElementById('occupants-error');
    const creditCardError = document.getElementById('credit-card-error');
    const durationError = document.getElementById('duration-error');

    // Initialize occupants input
    occupantsInput.disabled = !roomTypeSelect.value;
    updateFormFields();

    // Toggle duration/check-out fields based on room type
    roomTypeSelect.addEventListener('change', () => {
        updateFormFields();
        validateOccupants();
    });

    function updateFormFields() {
        const selectedOption = roomTypeSelect.selectedOptions[0];
        const isSuite = selectedOption?.dataset.isSuite === '1';
        durationGroup.style.display = isSuite ? 'block' : 'none';
        checkOutGroup.style.display = isSuite ? 'none' : 'block';
        const maxOccupants = parseInt(selectedOption?.dataset.maxOccupants) || 1;
        occupantsInput.max = maxOccupants;
        occupantsInput.placeholder = `Max ${maxOccupants} occupants`;
        occupantsInput.disabled = !selectedOption.value;
        if (isSuite) {
            durationValueInput.required = true;
            durationTypeSelect.required = true;
            checkOutInput.required = false;
        } else {
            durationValueInput.required = false;
            durationTypeSelect.required = false;
            checkOutInput.required = true;
        }
        validateDuration();
    }

    // Validate occupants in real-time
    occupantsInput.addEventListener('input', validateOccupants);
    function validateOccupants() {
        const value = parseInt(occupantsInput.value) || 0;
        const maxOccupants = parseInt(roomTypeSelect.selectedOptions[0]?.dataset.maxOccupants) || 1;
        if (!roomTypeSelect.value) {
            occupantsError.textContent = 'Please select a room type first.';
            occupantsError.classList.remove('d-none');
            occupantsInput.value = '';
            return false;
        } else if (value < 1) {
            occupantsError.textContent = 'At least 1 occupant is required.';
            occupantsError.classList.remove('d-none');
            return false;
        } else if (value > maxOccupants) {
            occupantsError.textContent = `Maximum ${maxOccupants} occupants allowed.`;
            occupantsError.classList.remove('d-none');
            occupantsInput.value = maxOccupants;
            return false;
        } else {
            occupantsError.classList.add('d-none');
            return true;
        }
    }

    // Validate duration in real-time
    durationValueInput.addEventListener('input', validateDuration);
    durationTypeSelect.addEventListener('change', validateDuration);
    function validateDuration() {
        const isSuite = roomTypeSelect.selectedOptions[0]?.dataset.isSuite === '1';
        if (!isSuite) {
            durationError.classList.add('d-none');
            return true;
        }
        const value = parseInt(durationValueInput.value) || 0;
        const type = durationTypeSelect.value;
        const maxDuration = type === 'weeks' ? 52 : 12; // Max 52 weeks or 12 months
        if (value < 1) {
            durationError.textContent = 'Duration must be at least 1.';
            durationError.classList.remove('d-none');
            durationValueInput.value = 1;
            return false;
        } else if (value > maxDuration) {
            durationError.textContent = `Maximum ${maxDuration} ${type} allowed.`;
            durationError.classList.remove('d-none');
            durationValueInput.value = maxDuration;
            return false;
        } else {
            durationError.classList.add('d-none');
            return true;
        }
    }

    // Format and validate credit card in real-time
    creditCardInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/[\s-]/g, ''); // Remove spaces and hyphens
        if (value.length > 16) {
            value = value.slice(0, 16); // Limit to 16 digits
        }
        // Add hyphens every 4 digits
        let formatted = '';
        for (let i = 0; i < value.length; i += 4) {
            formatted += value.slice(i, i + 4) + (i + 4 < value.length ? '-' : '');
        }
        e.target.value = formatted;
        validateCreditCard();
    });

    function validateCreditCard() {
        const value = creditCardInput.value.replace(/[\s-]/g, ''); // Remove spaces and hyphens
        if (!value) {
            creditCardError.classList.add('d-none');
            return true; // Optional field
        }
        const isNumeric = /^\d+$/.test(value);
        const isValidLength = value.length === 15 || value.length === 16;
        const isValidLuhn = checkLuhn(value);
        if (!isNumeric) {
            creditCardError.textContent = 'Credit card must contain only digits.';
            creditCardError.classList.remove('d-none');
            return false;
        } else if (!isValidLength) {
            creditCardError.textContent = 'Credit card must be 15 or 16 digits.';
            creditCardError.classList.remove('d-none');
            return false;
        } else if (!isValidLuhn) {
            creditCardError.textContent = 'Invalid credit card number.';
            creditCardError.classList.remove('d-none');
            return false;
        } else {
            creditCardError.classList.add('d-none');
            return true;
        }
    }

    // Luhn algorithm for credit card validation
    function checkLuhn(cardNumber) {
        let sum = 0;
        let isEven = false;
        for (let i = cardNumber.length - 1; i >= 0; i--) {
            let digit = parseInt(cardNumber[i]);
            if (isEven) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            sum += digit;
            isEven = !isEven;
        }
        return sum % 10 === 0;
    }

    // Update check-out date min based on check-in
    checkInInput.addEventListener('change', () => {
        const checkInDate = new Date(checkInInput.value);
        checkInDate.setDate(checkInDate.getDate() + 1);
        checkOutInput.min = checkInDate.toISOString().split('T')[0];
        if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
            checkOutInput.value = '';
        }
    });

    // Form submission validation
    form.addEventListener('submit', (e) => {
        const isOccupantsValid = validateOccupants();
        const isCreditCardValid = validateCreditCard();
        const isDurationValid = validateDuration();
        if (!isOccupantsValid || !isCreditCardValid || !isDurationValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection