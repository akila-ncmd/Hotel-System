@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Occupancy Report #{{ $report->id }}</h2>
    <p><strong>Generated:</strong> {{ $report->created_at->format('Y-m-d H:i:s') }}</p>
    <p><strong>Date Range:</strong> {{ $filters['date_range'] ?? 'All Dates' }}</p>
    <p><strong>Room Type:</strong> {{ $filters['room_type_id'] ? ($roomTypes->find($filters['room_type_id'])->name ?? 'N/A') : 'All Room Types' }}</p>
    <p><strong>Status:</strong> {{ $filters['status'] ?? 'All Statuses' }}</p>

    <!-- Download Buttons -->
    <div class="mb-4">
        <a href="{{ route('manager.download-individual-report', ['id' => $report->id, 'format' => 'pdf']) }}" class="btn btn-primary">Download PDF</a>
        <a href="{{ route('manager.download-individual-report', ['id' => $report->id, 'format' => 'excel']) }}" class="btn btn-success">Download Excel</a>
    </div>

    <!-- Reservations Table -->
    @if($reservations->isEmpty())
        <p>No reservations found for the selected criteria.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Guest Name</th>
                    <th>Room Type</th>
                    <th>Check-In Date</th>
                    <th>Check-Out Date</th>
                    <th>Status</th>
                    <th>Total Amount ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->id }}</td>
                        <td>{{ $reservation->user->name ?? 'N/A' }}</td>
                        <td>{{ $reservation->roomType->name ?? 'N/A' }}</td>
                        <td>{{ $reservation->check_in_date }}</td>
                        <td>{{ $reservation->check_out_date }}</td>
                        <td>{{ ucfirst($reservation->status) }}</td>
                        <td>{{ number_format($reservation->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('manager.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
</div>
@endsection