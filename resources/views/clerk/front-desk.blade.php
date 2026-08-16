@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h2 class="mb-0">Front Desk</h2>
        <div class="text-muted">{{ $branch->name }} &middot; {{ $today->format('D, j M Y') }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('clerk.walk-in') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Walk-in
        </a>
        <a href="{{ route('clerk.dashboard') }}" class="btn btn-outline-secondary">All reservations</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Today at a glance --}}
<div class="row g-3 mb-4">
    @php
        $stats = [
            ['label' => 'Arrivals',      'value' => $arrivals->count(),   'icon' => 'box-arrow-in-right', 'tone' => 'primary'],
            ['label' => 'Departures',    'value' => $departures->count(), 'icon' => 'box-arrow-right',    'tone' => 'info'],
            ['label' => 'In house',      'value' => $inHouse->count(),    'icon' => 'people',             'tone' => 'success'],
            ['label' => 'Rooms free',    'value' => $roomsAvailable,      'icon' => 'door-open',          'tone' => 'secondary'],
        ];
    @endphp
    @foreach($stats as $stat)
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="badge bg-{{ $stat['tone'] }} bg-opacity-10 text-{{ $stat['tone'] }} fs-4 p-3">
                        <i class="bi bi-{{ $stat['icon'] }}"></i>
                    </span>
                    <div>
                        <div class="fs-3 fw-semibold lh-1">{{ $stat['value'] }}</div>
                        <div class="text-muted small">{{ $stat['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($overdue->isNotEmpty())
    <div class="alert alert-warning d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>
            <strong>{{ $overdue->count() }} overdue {{ Str::plural('departure', $overdue->count()) }}.</strong>
            These guests were due to leave before today and are still checked in, so their rooms are counted as occupied:
            {{ $overdue->map(fn ($r) => $r->room->room_number ?? '#' . $r->id)->join(', ') }}.
        </div>
    </div>
@endif

<ul class="nav nav-tabs mb-3" role="tablist">
    @php
        $tabs = [
            ['id' => 'arrivals',   'label' => 'Arrivals',   'count' => $arrivals->count()],
            ['id' => 'departures', 'label' => 'Departures', 'count' => $departures->count()],
            ['id' => 'in-house',   'label' => 'In house',   'count' => $inHouse->count()],
        ];
    @endphp
    @foreach($tabs as $i => $tab)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $i === 0 ? 'active' : '' }}" id="{{ $tab['id'] }}-tab"
                    data-bs-toggle="tab" data-bs-target="#{{ $tab['id'] }}" type="button" role="tab">
                {{ $tab['label'] }}
                <span class="badge rounded-pill text-bg-secondary ms-1">{{ $tab['count'] }}</span>
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    {{-- Arrivals --}}
    <div class="tab-pane fade show active" id="arrivals" role="tabpanel">
        @if($arrivals->isEmpty())
            <p class="text-muted">No arrivals due today.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Room type</th>
                            <th>Nights</th>
                            <th>Guests</th>
                            <th>Guarantee</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($arrivals as $reservation)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $reservation->user->name }}</div>
                                    <div class="text-muted small">{{ $reservation->user->contact_number }}</div>
                                </td>
                                <td>{{ $reservation->roomType->name }}</td>
                                <td>
                                    {{ $reservation->check_out_date
                                        ? \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays($reservation->check_out_date)
                                        : '—' }}
                                </td>
                                <td>{{ $reservation->number_of_occupants }}</td>
                                <td>
                                    @if($reservation->credit_card_details)
                                        <span class="badge text-bg-success-subtle text-success-emphasis">On file</span>
                                    @else
                                        {{-- Unguaranteed same-day arrivals are auto-cancelled at 19:00. --}}
                                        <span class="badge text-bg-warning">None</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('clerk.check-in', $reservation->id) }}" class="btn btn-primary btn-sm">Check in</a>
                                    <a href="{{ route('clerk.reservation.edit', $reservation->id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Departures --}}
    <div class="tab-pane fade" id="departures" role="tabpanel">
        @if($departures->isEmpty())
            <p class="text-muted">No departures due today.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Room type</th>
                            <th>Checked in</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departures as $reservation)
                            <tr>
                                <td class="fw-semibold">{{ $reservation->room->room_number ?? '—' }}</td>
                                <td>{{ $reservation->user->name }}</td>
                                <td>{{ $reservation->roomType->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('j M') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('clerk.check-out', $reservation->id) }}" class="btn btn-primary btn-sm">Check out</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- In house --}}
    <div class="tab-pane fade" id="in-house" role="tabpanel">
        @if($inHouse->isEmpty())
            <p class="text-muted">Nobody is currently checked in.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Room type</th>
                            <th>Guests</th>
                            <th>Departing</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inHouse as $reservation)
                            @php
                                $out = $reservation->check_out_date ? \Carbon\Carbon::parse($reservation->check_out_date) : null;
                                $isOverdue = $out && $out->lt($today);
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-warning' : '' }}">
                                <td class="fw-semibold">{{ $reservation->room->room_number ?? '—' }}</td>
                                <td>{{ $reservation->user->name }}</td>
                                <td>{{ $reservation->roomType->name }}</td>
                                <td>{{ $reservation->number_of_occupants }}</td>
                                <td>
                                    {{ $out ? $out->format('j M') : '—' }}
                                    @if($isOverdue)
                                        <span class="badge text-bg-warning ms-1">Overdue</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('clerk.check-out', $reservation->id) }}" class="btn btn-outline-primary btn-sm">Check out</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<p class="text-muted small mt-4 mb-0">
    Rooms: {{ $roomsOccupied }} occupied, {{ $roomsAvailable }} free, {{ $roomsOutOfService }} out of service, {{ $roomsTotal }} total.
</p>
@endsection
