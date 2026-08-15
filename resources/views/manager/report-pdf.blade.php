<!DOCTYPE html>
<html>
<head>
    <title>Daily Report - {{ $date }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Daily Report</h1>
    <div class="summary">
        <p><strong>Date:</strong> {{ $date }}</p>
        <p><strong>Total Revenue:</strong> ${{ number_format($total_revenue, 2) }}</p>
        <p><strong>Occupancy Rate:</strong> {{ number_format($occupancy_rate, 2) }}%</p>
    </div>

    <h2>Reservations</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Guest</th>
                <th>Room Type</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Status</th>
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
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Billings</h2>
    <table>
        <thead>
            <tr>
                <th>Reservation ID</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            @foreach($billings as $billing)
                <tr>
                    <td>{{ $billing->reservation_id }}</td>
                    <td>${{ number_format($billing->total_amount, 2) }}</td>
                    <td>{{ ucfirst($billing->payment_status) }}</td>
                    <td>{{ ucfirst($billing->payment_method) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>