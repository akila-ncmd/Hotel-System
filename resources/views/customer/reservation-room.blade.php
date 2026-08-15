@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Room Reservation</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('reservations.store') }}" id="reservationForm">
        @csrf
        <input type="hidden" name="is_suite" value="0">
        <div class="mb-3">
            <label for="branch_id" class="form-label">Branch</label>
            <select name="branch_id" id="branch_id" class="form-control" required>
                <option value="">Select a branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branch_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="room_type_id" class="form-label">Room Type</label>
            <select name="room_type_id" id="room_type_id" class="form-control" required>
                <option value="">Select a room type</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" data-max-occupants="{{ $type->max_occupants }}">{{ $type->name }} (${{ number_format($type->price_per_night, 2) }}/night)</option>
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
            <label for="check_out_date" class="form-label">Check-out Date</label>
            <input type="date" name="check_out_date" id="check_out_date" class="form-control" required min="{{ \Carbon\Carbon::today()->addDay()->format('Y-m-d') }}">
            @error('check_out_date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="number_of_occupants" class="form-label">Number of Occupants</label>
            <input type="number" name="number_of_occupants" id="number_of_occupants" class="form-control" min="1" required placeholder="Select room type first">
            <div id="occupants-error" class="text-danger d-none"></div>
            @error('number_of_occupants') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="credit_card_details" class="form-label">Credit Card Details (Optional)</label>
            <input type="text" name="credit_card_details" id="credit_card_details" class="form-control" maxlength="19" placeholder="e.g., 1234-5678-9012-3456">
            <div id="credit-card-error" class="text-danger d-none"></div>
            @error('credit_card_details') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Reserve</button>
            <a href="{{ route('customer.reservations') }}" class="btn btn-secondary">Cancel</a>
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
    const occupantsError = document.getElementById('occupants-error');
    const creditCardError = document.getElementById('credit-card-error');

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
        if (!isOccupantsValid || !isCreditCardValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection