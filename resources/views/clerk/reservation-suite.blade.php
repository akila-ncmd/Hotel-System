@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Reserve a Residential Suite</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('clerk.reservation.store') }}" id="reservationForm">
        @csrf
        <input type="hidden" name="is_suite" value="1">
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
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="room_type_id" class="form-label">Room Type</label>
            <select name="room_type_id" id="room_type_id" class="form-control" required>
                <option value="">Select Suite</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" data-max-occupants="{{ $type->max_occupants }}">{{ $type->name }} (Weekly: @money($type->weekly_rate), Monthly: @money($type->monthly_rate))</option>
                @endforeach
            </select>
            @error('room_type_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="check_in_date" class="form-label">Check-in Date</label>
            <input type="date" name="check_in_date" id="check_in_date" class="form-control" required min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
            @error('check_in_date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="duration_value" class="form-label">Duration</label>
            <div class="input-group">
                <input type="number" name="duration_value" id="duration_value" class="form-control" min="1" required placeholder="Enter number">
                <select name="duration_type" id="duration_type" class="form-control" required>
                    <option value="weeks">Weeks</option>
                    <option value="months">Months</option>
                </select>
            </div>
            <div id="duration-error" class="text-danger d-none"></div>
            <small class="form-text text-muted">Note: A 4-week reservation will be charged at the monthly rate.</small>
            @error('duration_value') <span class="text-danger">{{ $message }}</span> @enderror
            @error('duration_type') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="number_of_occupants" class="form-label">Number of Occupants</label>
            <input type="number" name="number_of_occupants" id="number_of_occupants" class="form-control" min="1" required placeholder="Select suite type first">
            <div id="occupants-error" class="text-danger d-none"></div>
            @error('number_of_occupants') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="credit_card_details" class="form-label">Credit Card Details (Optional)</label>
            <input type="text" name="credit_card_details" id="credit_card_details" class="form-control" maxlength="19" placeholder="e.g., 1234-5678-9012-3456">
            <label for="card_expiry" class="form-label mt-2">Card Expiry (MM/YY)</label>
            <input type="text" name="card_expiry" id="card_expiry" class="form-control" maxlength="5" placeholder="MM/YY" value="{{ old('card_expiry') }}">
            <div class="form-text">Only the last four digits and the expiry date are stored.</div>
            @error('card_expiry') <span class="text-danger">{{ $message }}</span> @enderror
            <div id="credit-card-error" class="text-danger d-none"></div>
            @error('credit_card_details') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Reserve Suite</button>
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
    const durationValueInput = document.getElementById('duration_value');
    const durationTypeSelect = document.getElementById('duration_type');
    const occupantsError = document.getElementById('occupants-error');
    const creditCardError = document.getElementById('credit-card-error');
    const durationError = document.getElementById('duration-error');

    // Initialize occupants input as disabled until room type is selected
    occupantsInput.disabled = true;

    // Update occupants max based on room type
    roomTypeSelect.addEventListener('change', () => {
        const selectedOption = roomTypeSelect.selectedOptions[0];
        const maxOccupants = parseInt(selectedOption?.dataset.maxOccupants) || 1;
        occupantsInput.disabled = !selectedOption.value;
        occupantsInput.max = maxOccupants;
        occupantsInput.placeholder = `Max ${maxOccupants} occupants`;
        validateOccupants();
    });

    // Validate occupants in real-time
    occupantsInput.addEventListener('input', validateOccupants);
    function validateOccupants() {
        const value = parseInt(occupantsInput.value) || 0;
        const maxOccupants = parseInt(roomTypeSelect.selectedOptions[0]?.dataset.maxOccupants) || 1;
        if (!roomTypeSelect.value) {
            occupantsError.textContent = 'Please select a suite type first.';
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