@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Clerk Dashboard</h2>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <a href="{{ route('clerk.room-reservation') }}" class="btn btn-primary btn-lg w-100">Room Reservation</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('clerk.residential-suite-reservation') }}" class="btn btn-primary btn-lg w-100">Residential Suite Reservation</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('clerk.travel-agency') }}" class="btn btn-primary btn-lg w-100">Travel Agency Booking</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('clerk.room-availability') }}" class="btn btn-primary btn-lg w-100">Room Availability</a>
        </div>
    </div>

    <h3>Individual Reservations</h3>
    @if($reservations->isEmpty())
        <p>No individual reservations found.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Room Type</th>
                    <th>Branch</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->id }}</td>
                        <td>{{ $reservation->user->name }}</td>
                        <td>{{ $reservation->roomType->name }}</td>
                        <td>{{ $reservation->branch->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('Y-m-d') }}</td>
						<td>{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('Y-m-d') }}</td>

                        <td>{{ ucfirst($reservation->status) }}</td>
                        <td>
                            @if($reservation->status === 'pending' || $reservation->status === 'confirmed')
                                <a href="{{ route('clerk.check-in', $reservation->id) }}" class="btn btn-primary btn-sm">Check-in</a>
                                <a href="{{ route('clerk.reservation.edit', $reservation->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                <form action="{{ route('clerk.reservation.cancel', $reservation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                </form>
                            @endif
                            @if($reservation->status === 'checked_in')
                                <a href="{{ route('clerk.check-out', $reservation->id) }}" class="btn btn-primary btn-sm">Check-out</a>
                            @endif
                            @if($reservation->status === 'checked_out' && $reservation->updated_at >= now()->subHours(24))
                                <a href="{{ route('clerk.edit-check-out', $reservation->id) }}" class="btn btn-primary btn-sm">Edit Checkout</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>Travel Agency Bookings</h3>
    @if($travelAgencyBookings->isEmpty())
        <p>No travel agency bookings found.</p>
    @else
        @foreach($travelAgencyBookings as $agencyId => $bookings)
            <h4>{{ $bookings->first()->travelAgency->name }}</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <!--<th>Customer</th>-->
                        <th>Room Type</th>
                        <th>Branch</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->reservation->id }}</td>
                           <!-- <td>{{ $booking->reservation->user->name }}</td> -->
                            <td>{{ $booking->reservation->roomType->name }}</td>
                            <td>{{ $booking->reservation->branch->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('Y-m-d') }}</td>
						<td>{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($booking->reservation->status) }}</td>
                            <td>
                                @if($booking->reservation->status === 'pending' || $booking->reservation->status === 'confirmed')
                                    <a href="{{ route('clerk.check-in', $booking->reservation->id) }}" class="btn btn-primary btn-sm">Check-in</a>
                                    <a href="{{ route('clerk.reservation.edit', $booking->reservation->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <form action="{{ route('clerk.reservation.cancel', $booking->reservation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                    </form>
                                @endif
                                @if($booking->reservation->status === 'checked_in')
                                    <a href="{{ route('clerk.check-out', $booking->reservation->id) }}" class="btn btn-primary btn-sm">Check-out</a>
                                @endif
                                @if($booking->reservation->status === 'checked_out' && $booking->reservation->updated_at >= now()->subHours(24))
                                    <a href="{{ route('clerk.edit-check-out', $booking->reservation->id) }}" class="btn btn-primary btn-sm">Edit Checkout</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</div>
@endsection