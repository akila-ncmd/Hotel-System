@extends('layouts.app')

@section('content')
<div class="mb-4">
    <form method="GET" action="{{ route('manager.occupancy-report') }}" id="occupancy-report-form">
        <div class="row">
            <!-- Updated Date Range Selection -->
            <div class="col-md-4 mb-3">
                <label for="date_range" class="form-label">Date Range</label>
                <div class="input-group">
                    <input type="date" name="start_date" id="start_date" class="form-control"
                           value="{{ request('start_date') ?? old('start_date') }}"
                           placeholder="Start date">
                    <span class="input-group-text">to</span>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                           value="{{ request('end_date') ?? old('end_date') }}"
                           placeholder="End date">
                    <!-- Hidden input for the combined date range -->
                    <input type="hidden" name="date_range" id="date_range" value="">
                </div>
                <small class="text-muted">Select start and end dates</small>
            </div>

            <!-- Rest of the form remains the same -->
            <div class="col-md-3 mb-3">
                <label for="room_type_id" class="form-label">Room Type</label>
                <select name="room_type_id" id="room_type_id" class="form-control">
                    <option value="">All Room Types</option>
                    @foreach($roomTypes as $roomType)
                    <option value="{{ $roomType->id }}" {{ request('room_type_id') == $roomType->id ? 'selected' : '' }}>{{ $roomType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 mb-3 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('manager.occupancy-report') }}" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>
</div>
    <!-- Download Buttons -->
    <div class="mb-4">
        <a href="{{ route('manager.download-occupancy-report', ['format' => 'pdf', 'date_range' => request('date_range'), 'room_type_id' => request('room_type_id'), 'status' => request('status')]) }}" class="btn btn-primary">Download PDF</a>
        <a href="{{ route('manager.download-occupancy-report', ['format' => 'excel', 'date_range' => request('date_range'), 'room_type_id' => request('room_type_id'), 'status' => request('status')]) }}" class="btn btn-success">Download Excel</a>
    </div>

    <!-- Reservations Table -->
    @if($reservations->isEmpty())
        <p>No reservations found for the selected criteria.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Guest Name</th>
                    <th>Room Type</th>
                    <th>Check-In Date</th>
                    <th>Check-Out Date</th>
                    <th>Status</th>
                    <th>Total Price ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->user->name }}</td>
                        <td>{{ $reservation->roomType->name }}</td>
                        <td>{{ $reservation->check_in_date->format('Y-m-d') }}</td>
                        <td>{{ $reservation->check_out_date->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($reservation->status) }}</td>
                        <td>@money($reservation->total_amount ?? 0)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- DateRangePicker Dependencies -->
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('occupancy-report-form');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const dateRangeInput = document.getElementById('date_range');

        // Set default dates if not already set
        if (!startDateInput.value) {
            const defaultStart = new Date();
            startDateInput.valueAsDate = defaultStart;
        }

        if (!endDateInput.value) {
            const defaultEnd = new Date();
            defaultEnd.setDate(defaultEnd.getDate() + 7);
            endDateInput.valueAsDate = defaultEnd;
        }

        // Update the hidden date_range input before form submission
        form.addEventListener('submit', function(e) {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (startDate && endDate) {
                dateRangeInput.value = `${startDate} - ${endDate}`;
            } else if (startDate) {
                dateRangeInput.value = `${startDate} - ${startDate}`;
            } else if (endDate) {
                dateRangeInput.value = `${endDate} - ${endDate}`;
            }
        });

        // Add event listeners to enforce start date <= end date
        startDateInput.addEventListener('change', function() {
            if (endDateInput.value && new Date(this.value) > new Date(endDateInput.value)) {
                endDateInput.value = this.value;
            }
        });

        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && new Date(this.value) < new Date(startDateInput.value)) {
                startDateInput.value = this.value;
            }
        });

        // For browsers that don't support native date picker, you could initialize a polyfill here
        if (window.Pikaday) {
            new Pikaday({
                field: document.getElementById('start_date'),
                format: 'YYYY-MM-DD',
                onSelect: function() {
                    startDateInput.dispatchEvent(new Event('change'));
                }
            });

            new Pikaday({
                field: document.getElementById('end_date'),
                format: 'YYYY-MM-DD',
                onSelect: function() {
                    endDateInput.dispatchEvent(new Event('change'));
                }
            });
        }
    });
</script>
@endsection
