@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Check Out Reservation #{{ $reservation->id }}</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('clerk.check-out', $reservation->id) }}" id="checkOutForm">
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
            <label class="form-label">Base Room Cost</label>
            <input type="text" class="form-control" value="${{ number_format($baseCost, 0) }}" disabled>
        </div>
        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-control" required>
                <option value="cash">Cash</option>
                <option value="credit_card">Credit Card</option>
            </select>
            @error('payment_method') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="restaurant_charges" class="form-label">Restaurant Charges ($)</label>
            <input type="number" name="restaurant_charges" id="restaurant_charges" class="form-control charge-input" value="{{ $billing->restaurant_charges ?? 0 }}" min="0" step="0.01">
            <div id="restaurant-charges-error" class="text-danger" style="display: none;"></div>
            @error('restaurant_charges') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="room_service_charges" class="form-label">Room Service Charges ($)</label>
            <input type="number" name="room_service_charges" id="room_service_charges" class="form-control charge-input" value="{{ $billing->room_service_charges ?? 0 }}" min="0" step="0.01">
            <div id="room-service-charges-error" class="text-danger" style="display: none;"></div>
            @error('room_service_charges') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="laundry_charges" class="form-label">Laundry Charges ($)</label>
            <input type="number" name="laundry_charges" id="laundry_charges" class="form-control charge-input" value="{{ $billing->laundry_charges ?? 0 }}" min="0" step="0.01">
            <div id="laundry-charges-error" class="text-danger" style="display: none;"></div>
            @error('laundry_charges') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="telephone_charges" class="form-label">Telephone Charges ($)</label>
            <input type="number" name="telephone_charges" id="telephone_charges" class="form-control charge-input" value="{{ $billing->telephone_charges ?? 0 }}" min="0" step="0.01">
            <div id="telephone-charges-error" class="text-danger" style="display: none;"></div>
            @error('telephone_charges') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="club_facility_charges" class="form-label">Club Facility Charges ($)</label>
            <input type="number" name="club_facility_charges" id="club_facility_charges" class="form-control charge-input" value="{{ $billing->club_facility_charges ?? 0 }}" min="0" step="0.01">
            <div id="club-facility-charges-error" class="text-danger" style="display: none;"></div>
            @error('club_facility_charges') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="total-amount" class="form-label">Total Amount ($)</label>
            <input type="text" id="total-amount" class="form-control" value="${{ number_format($baseCost, 0) }}" disabled>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Complete Check-out</button>
            <a href="{{ route('clerk.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const baseCost = {{ $baseCost }};
    const chargeInputs = document.querySelectorAll('.charge-input');
    const totalAmountInput = document.getElementById('total-amount');
    const chargeErrors = {
        restaurant_charges: document.getElementById('restaurant-charges-error'),
        room_service_charges: document.getElementById('room-service-charges-error'),
        laundry_charges: document.getElementById('laundry-charges-error'),
        telephone_charges: document.getElementById('telephone-charges-error'),
        club_facility_charges: document.getElementById('club-facility-charges-error')
    };

    function validateCharge(input) {
        const value = input.value.trim();
        const fieldName = input.id;
        const errorElement = chargeErrors[fieldName];
        const numericValue = parseFloat(value) || 0;

        // Reset error display
        errorElement.style.display = 'none';
        errorElement.textContent = '';

        // Check for negative values
        if (value && numericValue < 0) {
            errorElement.textContent = 'Charge cannot be negative.';
            errorElement.style.display = 'block';
            input.value = 0;
            return false;
        }
        // Check for maximum value
        if (value && numericValue > 10000) {
            errorElement.textContent = 'Charge cannot exceed $10,000.';
            errorElement.style.display = 'block';
            input.value = 10000;
            return false;
        }
        // Check for more than 2 decimal places
        if (value.includes('.') && value.split('.')[1]?.length > 2) {
            errorElement.textContent = 'Charge cannot have more than 2 decimal places.';
            errorElement.style.display = 'block';
            input.value = parseFloat(value).toFixed(2);
            return false;
        }
        return true;
    }

    function updateTotal() {
        let additionalCharges = 0;
        chargeInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            additionalCharges += value;
        });
        const total = Math.floor(baseCost + additionalCharges); // Remove decimal places
        totalAmountInput.value = `$${total}`;
    }

    // Validate and update total on input
    chargeInputs.forEach(input => {
        input.addEventListener('input', () => {
            validateCharge(input);
            updateTotal();
        });
        // Validate on blur to catch any manual edits
        input.addEventListener('blur', () => {
            if (input.value === '') {
                input.value = 0;
                validateCharge(input);
                updateTotal();
            }
        });
    });

    // Form submission validation
    document.getElementById('checkOutForm').addEventListener('submit', (e) => {
        let isValid = true;
        chargeInputs.forEach(input => {
            if (!validateCharge(input)) {
                isValid = false;
            }
        });
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Initial validation and total calculation
    chargeInputs.forEach(input => {
        validateCharge(input);
    });
    updateTotal();
});
</script>
@endsection