<!DOCTYPE html>
<html>
<head>
    <title>Reservation Confirmation</title>
</head>
<body>
    <h1>Reservation Confirmation</h1>
    <p>Dear {{ $reservation->user->name }},</p>
    <p>Thank you for your reservation. Here are the details:</p>
    <ul>
        <li><strong>Branch:</strong> {{ $reservation->branch->name }}</li>
        <li><strong>Room Type:</strong> {{ $reservation->roomType->name }}</li>
        <li><strong>Check-in Date:</strong> {{ $reservation->check_in_date->format('Y-m-d') }}</li>
        <li><strong>Duration:</strong> {{ $durationText }}</li>
        <li><strong>Number of Occupants:</strong> {{ $reservation->number_of_occupants }}</li>
        <li><strong>Total Cost:</strong> ${{ number_format($totalCost, 2) }}</li>
        <li><strong>Status:</strong> {{ ucfirst($reservation->status) }}</li>
    </ul>
    <p>We look forward to welcoming you!</p>
    <p>Best regards,<br>Hotel Reservation System</p>
</body>
</html>