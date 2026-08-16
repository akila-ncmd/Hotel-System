<!DOCTYPE html>
<html>
<head>
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body>
    <h1>Welcome, {{ $user->name }}!</h1>
    <p>Thank you for registering with {{ config('app.name') }}. Your account has been successfully created.</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Nationality:</strong> {{ $user->nationality }}</p>
    <p><strong>Contact Number:</strong> {{ $user->contact_number }}</p>
    <p>You can now log in to your account and start making reservations. Visit our <a href="{{ url('/dashboard') }}">dashboard</a> to get started.</p>
    <p>If you have any questions, feel free to contact our support team.</p>
    <p>Best regards,<br>{{ config('app.name') }}</p>
</body>
</html>