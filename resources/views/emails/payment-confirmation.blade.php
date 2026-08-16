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
        <li><strong>Base Amount:</strong> @money($totalCost - ($billing->restaurant_charges + $billing->room_service_charges + $billing->laundry_charges + $billing->telephone_charges + $billing->club_facility_charges))</li>
        <li><strong>Restaurant Charges:</strong> @money($billing->restaurant_charges)</li>
        <li><strong>Room Service Charges:</strong> @money($billing->room_service_charges)</li>
        <li><strong>Laundry Charges:</strong> @money($billing->laundry_charges)</li>
        <li><strong>Telephone Charges:</strong> @money($billing->telephone_charges)</li>
        <li><strong>Club Facility Charges:</strong> @money($billing->club_facility_charges)</li>
        <li><strong>Total Amount:</strong> @money($totalCost)</li>
    </ul>
    <p>Thank you for staying with us!</p>
    <p>Best regards,<br>{{ config('app.name') }}</p>
</body>
</html>