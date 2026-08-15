<!DOCTYPE html>
<html>
<head>
    <title>Reservation Cancelled</title>
</head>
<body>
    <h1>Reservation Cancelled</h1>
    <p>Dear {{ $reservation->user->name }},</p>
    <p>We regret to inform you that your reservation has been cancelled as no credit card details were provided.</p>
    <p><strong>Reservation Details:</strong></p>
    <ul>
        <li>Reservation ID: {{ $reservation->id }}</li>
        <li>Check-in Date: {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('Y-m-d') }}</li>
        <li>Branch: {{ $reservation->branch->name }}</li>
        <li>Room Type: {{ $reservation->roomType->name }}</li>
    </ul>
    <p>Please contact us if you have any questions.</p>
    <p>Best regards,<br>{{ $hotelName }}</p>
</body>
</html>