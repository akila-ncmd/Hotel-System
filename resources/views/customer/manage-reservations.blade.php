@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Your Reservations</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Branch</th>
                <th>Room Type</th>
                <th>Check-in Date</th>
                <th>Duration</th>
                <th>Occupants</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->branch->name }}</td>
                    <td>{{ $reservation->roomType->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('Y-m-d') }}</td>
                    <td>
                        @if($reservation->roomType->is_suite)
                            {{ $reservation->duration_value }} {{ Str::plural($reservation->duration_type, $reservation->duration_value) }}
                        @else
                            {{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) }} day(s)
                        @endif
                    </td>
                    <td>{{ $reservation->number_of_occupants }}</td>
                    <td>{{ ucfirst($reservation->status) }}</td>
                    <td>
                        @if($reservation->status === 'pending' || $reservation->status === 'confirmed')
                            <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Cancel</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
        <a href="{{ route('reservations.room') }}" class="btn btn-primary">Book Room</a>
    <a href="{{ route('reservations.suite') }}" class="btn btn-primary">Book Suite</a>
</div>
@endsection