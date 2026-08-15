<!DOCTYPE html>
<html>
<head>
    <title>Daily Manager Report</title>
</head>
<body>
    <h1>Daily Manager Report - {{ $data['date'] }}</h1>
    <p>Dear Manager,</p>
    <p>Please find the daily report for {{ $data['date'] }}:</p>
    <ul>
        <li><strong>Occupied Rooms:</strong> {{ $data['occupied_rooms'] }} / {{ $data['total_rooms'] }}</li>
        <li><strong>Occupancy Rate:</strong> {{ $data['occupancy_rate'] }}%</li>
        <li><strong>Check-Out Revenue:</strong> ${{ $data['check_out_revenue'] }}</li>
        <li><strong>No-Show Revenue:</strong> ${{ $data['no_show_revenue'] }}</li>
        <li><strong>Total Revenue:</strong> ${{ $data['total_revenue'] }}</li>
        <li><strong>No-Shows:</strong> {{ $data['no_shows'] }}</li>
    </ul>
    <p>The detailed report is attached as a PDF.</p>
    <p>Best regards,<br>{{ config('app.name', 'Hotel Reservation System') }}</p>
</body>
</html>