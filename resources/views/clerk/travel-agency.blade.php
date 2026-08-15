@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Travel Agency Booking</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('clerk.travel-agency.store') }}" id="travelAgencyForm">
        @csrf
        <div class="mb-3">
            <label for="travel_agency_id" class="form-label">Travel Agency</label>
            <select name="travel_agency_id" id="travel_agency_id" class="form-control" required>
                <option value="">Select an agency</option>
                @foreach($agencies as $agency)
                    <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                @endforeach
            </select>
            @error('travel_agency_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
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
            <label class="form-label">Room Types and Quantities (Minimum 3 Total Rooms)</label>
            @foreach($roomGroups as $group => $rooms)
                <div class="mb-2">
                    <h5>{{ ucfirst($group) }}</h5>
                    @foreach($rooms as $roomType)
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input room-type-check" name="room_types[{{ $roomType->id }}][selected]" id="roomType_{{ $roomType->id }}" value="1" data-rate="{{ $roomType->is_suite ? $roomType->weekly_rate : $roomType->price_per_night }}" data-is-suite="{{ $roomType->is_suite ? 1 : 0 }}" data-max-occupants="{{ $roomType->max_occupants }}" data-max-quantity="{{ $roomType->is_suite ? 3 : 100 }}">
                            <label class="form-check-label" for="roomType_{{ $roomType->id }}">{{ $roomType->name }} ({{ $roomType->is_suite ? 'Weekly: $' . number_format($roomType->weekly_rate, 2) : '$' . number_format($roomType->price_per_night, 2) . '/night' }})</label>
                            <div class="mt-1">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="room_types[{{ $roomType->id }}][quantity]" class="form-control room-quantity" min="0" placeholder="Number of rooms" disabled>
                                @error("room_types.{$roomType->id}.quantity") <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mt-1">
                                <label class="form-label">Occupants per Room (Max {{ $roomType->max_occupants }})</label>
                                <input type="number" name="room_types[{{ $roomType->id }}][occupants]" class="form-control room-occupants" min="1" max="{{ $roomType->max_occupants }}" placeholder="Number of occupants" disabled>
                                @error("room_types.{$roomType->id}.occupants") <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mt-1">
                                <label class="form-label">Check-in Date</label>
                                <input type="date" name="room_types[{{ $roomType->id }}][check_in_date]" class="form-control check-in-date" disabled min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                                @error("room_types.{$roomType->id}.check_in_date") <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            @if($roomType->is_suite)
                                <div class="mt-1">
                                    <label class="form-label">Duration</label>
                                    <div class="input-group">
                                        <input type="number" name="room_types[{{ $roomType->id }}][duration_value]" class="form-control duration-value" min="1" placeholder="Enter number" disabled>
                                        <select name="room_types[{{ $roomType->id }}][duration_type]" class="form-control duration-type" disabled>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">Months</option>
                                        </select>
                                    </div>
                                    <small class="form-text text-muted">Note: A 4-week reservation will be charged at the monthly rate.</small>
                                    @error("room_types.{$roomType->id}.duration_value") <span class="text-danger">{{ $message }}</span> @enderror
                                    @error("room_types.{$roomType->id}.duration_type") <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @else
                                <div class="mt-1">
                                    <label class="form-label">Check-out Date</label>
                                    <input type="date" name="room_types[{{ $roomType->id }}][check_out_date]" class="form-control check-out-date" disabled min="{{ \Carbon\Carbon::today()->addDay()->format('Y-m-d') }}">
                                    @error("room_types.{$roomType->id}.check_out_date") <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div id="total-rooms-error" class="text-danger d-none">Please select at least 3 rooms total.</div>
            @error('room_types') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="discount_percentage" class="form-label">Discount Percentage</label>
            <input type="number" name="discount_percentage" id="discount_percentage" class="form-control" min="0" max="100" step="0.01" required value="0">
            @error('discount_percentage') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="quotation_amount" class="form-label">Quotation Amount (Total for All Rooms)</label>
            <input type="number" name="quotation_amount" id="quotation_amount" class="form-control" min="0" step="0.01" readonly value="0">
            @error('quotation_amount') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Create Booking</button>
            <a href="{{ route('clerk.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('travelAgencyForm');
    const checkboxes = document.querySelectorAll('.room-type-check');
    const quantities = document.querySelectorAll('.room-quantity');
    const occupants = document.querySelectorAll('.room-occupants');
    const checkInDates = document.querySelectorAll('.check-in-date');
    const checkOutDates = document.querySelectorAll('.check-out-date');
    const durationValues = document.querySelectorAll('.duration-value');
    const durationTypes = document.querySelectorAll('.duration-type');
    const discountInput = document.getElementById('discount_percentage');
    const quotationInput = document.getElementById('quotation_amount');
    const totalRoomsError = document.getElementById('total-rooms-error');

    // Enable/disable fields
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const container = checkbox.parentElement;
            const quantityInput = container.querySelector('.room-quantity');
            const occupantsInput = container.querySelector('.room-occupants');
            const checkInInput = container.querySelector('.check-in-date');
            const checkOutInput = container.querySelector('.check-out-date');
            const durationValueInput = container.querySelector('.duration-value');
            const durationTypeInput = container.querySelector('.duration-type');
            const isEnabled = checkbox.checked;

            [quantityInput, occupantsInput, checkInInput, checkOutInput, durationValueInput, durationTypeInput].forEach(input => {
                if (input) input.disabled = !isEnabled;
            });

            if (!isEnabled) {
                quantityInput.value = '';
                occupantsInput.value = '';
                checkInInput.value = '';
                if (checkOutInput) checkOutInput.value = '';
                if (durationValueInput) durationValueInput.value = '';
                if (durationTypeInput) durationTypeInput.selectedIndex = 0;
            }

            updateQuotation();
            validateTotalRooms();
        });
    });

    // Validate quantity and occupants
    quantities.forEach(quantity => {
        quantity.addEventListener('input', () => {
            const container = quantity.parentElement.parentElement;
            const checkbox = container.querySelector('.room-type-check');
            const maxQuantity = parseInt(checkbox.dataset.maxQuantity);
            const value = parseInt(quantity.value) || 0;

            if (value > maxQuantity) {
                quantity.value = maxQuantity;
            }

            updateQuotation();
            validateTotalRooms();
        });
    });

    occupants.forEach(occupant => {
        occupant.addEventListener('input', () => {
            const container = occupant.parentElement.parentElement;
            const checkbox = container.querySelector('.room-type-check');
            const maxOccupants = parseInt(checkbox.dataset.maxOccupants);
            const value = parseInt(occupant.value) || 0;

            if (value > maxOccupants) {
                occupant.value = maxOccupants;
            }
        });
    });

    // Update check-out date min based on check-in
    checkInDates.forEach(checkIn => {
        checkIn.addEventListener('change', () => {
            const container = checkIn.parentElement.parentElement;
            const checkOut = container.querySelector('.check-out-date');
            if (checkOut) {
                const checkInDate = new Date(checkIn.value);
                checkInDate.setDate(checkInDate.getDate() + 1);
                checkOut.min = checkInDate.toISOString().split('T')[0];
                if (checkOut.value && checkOut.value <= checkIn.value) {
                    checkOut.value = '';
                }
            }
            updateQuotation();
        });
    });

    // Update quotation
    function updateQuotation() {
        let baseTotal = 0;
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const container = checkbox.parentElement;
                const quantity = parseInt(container.querySelector('.room-quantity').value) || 0;
                const isSuite = checkbox.dataset.isSuite === '1';
                const rate = parseFloat(checkbox.dataset.rate);

                if (isSuite) {
                    const durationValue = parseInt(container.querySelector('.duration-value').value) || 1;
                    const durationType = container.querySelector('.duration-type').value;
                    if (durationType === 'months') {
                        baseTotal += quantity * rate * durationValue * 4; // Monthly rate as 4 weeks
                    } else {
                        baseTotal += quantity * rate * durationValue;
                    }
                } else {
                    const checkIn = container.querySelector('.check-in-date').value;
                    const checkOut = container.querySelector('.check-out-date').value;
                    if (checkIn && checkOut) {
                        const days = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
                        baseTotal += quantity * rate * days;
                    }
                }
            }
        });

        const discount = parseFloat(discountInput.value) || 0;
        const discountedTotal = baseTotal * (1 - discount / 100);
        quotationInput.value = discountedTotal.toFixed(2);
    }

    // Validate total rooms
    function validateTotalRooms() {
        let total = 0;
        quantities.forEach(quantity => {
            if (!quantity.disabled && quantity.value) {
                total += parseInt(quantity.value) || 0;
            }
        });
        totalRoomsError.classList.toggle('d-none', total >= 3);
        return total >= 3;
    }

    // Update quotation on input changes
    quantities.forEach(q => q.addEventListener('input', updateQuotation));
    occupants.forEach(o => o.addEventListener('input', updateQuotation));
    checkInDates.forEach(c => c.addEventListener('change', updateQuotation));
    checkOutDates.forEach(c => c.addEventListener('change', updateQuotation));
    durationValues.forEach(d => d.addEventListener('input', updateQuotation));
    durationTypes.forEach(d => d.addEventListener('change', updateQuotation));
    discountInput.addEventListener('input', updateQuotation);

    // Form submission validation
    form.addEventListener('submit', (e) => {
        if (!validateTotalRooms()) {
            e.preventDefault();
            totalRoomsError.classList.remove('d-none');
        }
    });
});
</script>
@endsection