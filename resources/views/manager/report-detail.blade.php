```html
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>{{ ucfirst($type) }} Report ({{ $dateRange }})</h2>

    <!-- Download Buttons -->
    <div class="mb-4">
        <a href="{{ route($type === 'occupancy' ? 'manager.download-occupancy-report' : 'manager.download-revenue-report', ['format' => 'pdf', 'date_range' => $dateRange]) }}" class="btn btn-primary">Download PDF</a>
        <a href="{{ route($type === 'occupancy' ? 'manager.download-occupancy-report' : 'manager.download-revenue-report', ['format' => 'excel', 'date_range' => $dateRange]) }}" class="btn btn-success">Download Excel</a>
        <a href="{{ route('manager.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    @if($type === 'occupancy')
        <!-- Occupancy Report -->
        @if($reservations->isEmpty())
            <p>No reservations found for {{ $dateRange }}.</p>
        @else
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Guest</th>
                        <th>Room Type</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th>Total Amount ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $reservation)
                        <tr>
                            <td>{{ $reservation->id }}</td>
                            <td>{{ $reservation->user->name }}</td>
                            <td>{{ $reservation->roomType->name }}</td>
                            <td>{{ $reservation->check_in_date }}</td>
                            <td>{{ $reservation->check_out_date }}</td>
                            <td>{{ ucfirst($reservation->status) }}</td>
                            <td>{{ number_format($reservation->total_amount ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @else
        <!-- Revenue Report -->
        @if($reports->isEmpty())
            <p>No revenue data found for {{ $dateRange }}.</p>
        @else
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Revenue ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
                            <td>{{ number_format($report->total_revenue ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</div>
@endsection