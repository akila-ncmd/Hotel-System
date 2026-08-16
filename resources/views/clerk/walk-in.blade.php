@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Walk-in</h2>
        <div class="text-muted">{{ $branch->name }} &middot; checking in today</div>
    </div>
    <a href="{{ route('clerk.front-desk') }}" class="btn btn-outline-secondary">Back to front desk</a>
</div>

<p class="text-muted">
    Books the stay and checks the guest in immediately. Residential suites are excluded —
    they are priced by duration and are not a walk-in product.
</p>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('clerk.walk-in.store') }}" method="POST" class="row g-3" style="max-width: 720px;">
    @csrf

    <div class="col-12">
        <label for="user_id" class="form-label">Guest</label>
        <select name="user_id" id="user_id" class="form-select" required>
            <option value="">Select a guest…</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('user_id') == $customer->id)>
                    {{ $customer->name }} ({{ $customer->email }})
                </option>
            @endforeach
        </select>
        <div class="form-text">The guest must already have an account.</div>
    </div>

    <div class="col-md-6">
        <label for="room_type_id" class="form-label">Room type</label>
        <select name="room_type_id" id="room_type_id" class="form-select" required>
            <option value="">Select a room type…</option>
            @foreach($roomTypes as $type)
                <option value="{{ $type->id }}"
                        data-max-occupants="{{ $type->max_occupants }}"
                        @selected(old('room_type_id') == $type->id)>
                    {{ $type->name }} (@money($type->price_per_night)/night, max {{ $type->max_occupants }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="room_id" class="form-label">Room</label>
        <select name="room_id" id="room_id" class="form-select" required disabled>
            <option value="">Select a room type first</option>
        </select>
        <div class="form-text" id="room-help">Only rooms free right now are listed.</div>
    </div>

    <div class="col-md-6">
        <label for="number_of_occupants" class="form-label">Occupants</label>
        <input type="number" name="number_of_occupants" id="number_of_occupants" class="form-control"
               min="1" value="{{ old('number_of_occupants', 1) }}" required>
        <div class="form-text" id="occupants-help"></div>
    </div>

    <div class="col-md-6">
        <label for="check_out_date" class="form-label">Departing</label>
        <input type="date" name="check_out_date" id="check_out_date" class="form-control"
               min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}"
               value="{{ old('check_out_date', \Carbon\Carbon::tomorrow()->toDateString()) }}" required>
    </div>

    <div class="col-md-6">
        <label for="credit_card_details" class="form-label">Card number (optional)</label>
        <input type="text" name="credit_card_details" id="credit_card_details" class="form-control"
               maxlength="19" placeholder="e.g. 4242 4242 4242 4242" value="{{ old('credit_card_details') }}">
    </div>

    <div class="col-md-6">
        <label for="card_expiry" class="form-label">Card expiry (MM/YY)</label>
        <input type="text" name="card_expiry" id="card_expiry" class="form-control"
               maxlength="5" placeholder="MM/YY" value="{{ old('card_expiry') }}">
        <div class="form-text">Only the last four digits and the expiry date are stored.</div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Check in guest</button>
        <a href="{{ route('clerk.front-desk') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const roomTypeSelect = document.getElementById('room_type_id');
    const roomSelect = document.getElementById('room_id');
    const roomHelp = document.getElementById('room-help');
    const occupants = document.getElementById('number_of_occupants');
    const occupantsHelp = document.getElementById('occupants-help');

    // Rooms free right now depend on the chosen type, so fetch them on change
    // rather than embedding the whole room list in the page.
    async function loadRooms(roomTypeId, preselect) {
        roomSelect.disabled = true;
        roomSelect.innerHTML = '<option value="">Loading…</option>';

        if (!roomTypeId) {
            roomSelect.innerHTML = '<option value="">Select a room type first</option>';
            return;
        }

        try {
            const response = await fetch(`/clerk/room-types/${roomTypeId}/available-rooms`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) throw new Error(response.statusText);

            const rooms = await response.json();

            if (rooms.length === 0) {
                roomSelect.innerHTML = '<option value="">No rooms of this type are free</option>';
                roomHelp.textContent = 'Nothing available in this room type right now.';
                return;
            }

            roomSelect.innerHTML = '<option value="">Select a room…</option>'
                + rooms.map(room =>
                    `<option value="${room.id}" ${String(room.id) === String(preselect) ? 'selected' : ''}>`
                    + `Room ${room.room_number}</option>`
                  ).join('');
            roomSelect.disabled = false;
            roomHelp.textContent = `${rooms.length} room(s) free right now.`;
        } catch (error) {
            roomSelect.innerHTML = '<option value="">Could not load rooms</option>';
            roomHelp.textContent = 'Could not load rooms — refresh and try again.';
        }
    }

    function syncOccupantCap() {
        const option = roomTypeSelect.selectedOptions[0];
        const max = option?.dataset.maxOccupants;
        if (max) {
            occupants.max = max;
            occupantsHelp.textContent = `This room type allows at most ${max}.`;
            if (Number(occupants.value) > Number(max)) occupants.value = max;
        } else {
            occupants.removeAttribute('max');
            occupantsHelp.textContent = '';
        }
    }

    roomTypeSelect.addEventListener('change', () => {
        loadRooms(roomTypeSelect.value, null);
        syncOccupantCap();
    });

    // Repopulate after a validation redirect so the form keeps its state.
    if (roomTypeSelect.value) {
        loadRooms(roomTypeSelect.value, @json(old('room_id')));
        syncOccupantCap();
    }
});
</script>
@endsection
