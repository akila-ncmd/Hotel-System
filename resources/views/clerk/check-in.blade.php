@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Check-in Reservation #{{ $reservation->id }}</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('clerk.check-in', $reservation->id) }}" id="checkInForm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Customer</label>
            <input type="text" class="form-control" value="{{ $reservation->user->name }}" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Room Type</label>
            <input type="text" class="form-control" value="{{ $reservation->roomType->name }}" disabled>
        </div>
        <div class="mb-3">
            <label for="room_id" class="form-label">Select Room</label>
            <select name="room_id" id="room_id" class="form-control" required>
                <option value="">Select Room</option>
                @foreach($availableRooms as $room)
                    <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                @endforeach
            </select>
            @error('room_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        @if($reservation->check_in_date->isToday())
            <div class="mb-3">
                <label for="credit_card_details" class="form-label">Credit Card Details (Optional)</label>
                <input type="text" name="credit_card_details" id="credit_card_details" class="form-control" maxlength="19" value="{{ $reservation->credit_card_details }}" placeholder="e.g., 1234-5678-9012-3456">
                <div id="credit-card-error" class="text-danger d-none"></div>
                @error('credit_card_details') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        @endif
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Check-in</button>
            <a href="{{ route('clerk.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkInForm');
    const creditCardInput = document.getElementById('credit_card_details');
    const creditCardError = document.getElementById('credit-card-error');

    // Format and validate credit card in real-time if present
    if (creditCardInput) {
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
    }

    // Form submission validation
    form.addEventListener('submit', (e) => {
        let isCreditCardValid = true;
        if (creditCardInput) {
            isCreditCardValid = validateCreditCard();
        }
        if (!isCreditCardValid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection