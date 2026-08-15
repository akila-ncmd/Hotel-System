<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Occupancy Report - {{ date('Y-m-d') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        .filters { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Occupancy Report - {{ date('Y-m-d') }}</h1>
    <div class="filters">
        <p><strong>Branch:</strong> {{ $branch->name ?? 'Unknown' }}</p>
        <p><strong>Date Range:</strong> {{ $filters['date_range'] }}</p>
        <p><strong>Room Type:</strong> {{ $filters['room_type'] }}</p>
        <p><strong>Status:</strong> {{ $filters['status'] }}</p>
    </div>
    @if($reservations->isEmpty())
        <p>No reservations found for the selected criteria.</p>
    @else
        <table>
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
                        <td>{{ $reservation->user->name ?? 'Unknown' }}</td>
                        <td>{{ $reservation->roomType->name ?? 'Unknown' }}</td>
                        <td>{{ $reservation->check_in_date->format('Y-m-d') }}</td>
                        <td>{{ $reservation->check_out_date->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($reservation->status) }}</td>
                        <td>{{ number_format($reservation->total_amount ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>