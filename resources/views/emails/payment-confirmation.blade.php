<!DOCTYPE html>
<html>
<head>
    <title>Payment Confirmation</title>
</head>
<body>
    <h1>Payment Confirmation</h1>
    <p>Dear {{ $reservation->user->name }},</p>
    <p>Your payment has been processed. Here are the details:</p>
    <ul>
        <li><strong>Branch:</strong> {{ $reservation->branch->name }}</li>
        <li><strong>Room Type:</strong> {{ $reservation->roomType->name }}</li>
        <li><strong>Check-in Date:</strong> {{ $reservation->check_in_date->format('Y-m-d') }}</li>
        <li><strong>Duration:</strong> {{ $durationText }}</li>
        <li><strong>Number of Occupants:</strong> {{ $reservation->number_of_occupants }}</li>
        <li><strong>Payment Method:</strong> {{ ucfirst($billing->payment_method) }}</li>
        <li><strong>Payment Status:</strong> {{ ucfirst($billing->payment_status) }}</li>
        <li><strong>Base Amount:</strong> ${{ number_format($totalCost - ($billing->restaurant_charges + $billing->room_service_charges + $billing->laundry_charges + $billing->telephone_charges + $billing->club_facility_charges), 2) }}</li>
        <li><strong>Restaurant Charges:</strong> ${{ number_format($billing->restaurant_charges, 2) }}</li>
        <li><strong>Room Service Charges:</strong> ${{ number_format($billing->room_service_charges, 2) }}</li>
        <li><strong>Laundry Charges:</strong> ${{ number_format($billing->laundry_charges, 2) }}</li>
        <li><strong>Telephone Charges:</strong> ${{ number_format($billing->telephone_charges, 2) }}</li>
        <li><strong>Club Facility Charges:</strong> ${{ number_format($billing->club_facility_charges, 2) }}</li>
        <li><strong>Total Amount:</strong> ${{ number_format($totalCost, 2) }}</li>
    </ul>
    <p>Thank you for staying with us!</p>
    <p>Best regards,<br>Hotel Reservation System</p>
</body>
</html>