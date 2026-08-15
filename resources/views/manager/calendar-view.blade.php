@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Room Availability Calendar ({{ Auth::user()->branch->name }})</h2>
    <div id="calendar"></div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.15/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [
                @foreach($reservations as $reservation)
                {
                    title: '{{ $reservation->roomType->name }} ({{ $reservation->status }})',
                    start: '{{ $reservation->check_in_date->format('Y-m-d') }}',
                    end: '{{ $reservation->check_out_date->addDay()->format('Y-m-d') }}', // Add 1 day for exclusive end date
                    color: '{{ $reservation->status === 'checked_in' ? '#28a745' : ($reservation->status === 'pending' ? '#ffc107' : '#007bff') }}'
                },
                @endforeach
            ],
            eventClick: function(info) {
                alert('Reservation: ' + info.event.title + '\nStart: ' + info.event.start.toISOString().split('T')[0] + '\nEnd: ' + info.event.end.toISOString().split('T')[0]);
            }
        });
        calendar.render();
    });
</script>
@endsection