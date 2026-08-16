@extends('layouts.app')

@section('content')
<div class="ds-page-head ds-reveal">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <span class="ds-eyebrow">Your account</span>
            <h1>Reservations</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reservations.room') }}" class="btn btn-primary">Book a room</a>
            <a href="{{ route('reservations.suite') }}" class="btn btn-outline-dark">Book a suite</a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success ds-reveal">{{ session('success') }}</div>
@endif

@if($reservations->isEmpty())
    <div class="ds-empty ds-reveal">
        <i class="bi bi-calendar2-week"></i>
        <h3 class="mb-2">No reservations yet</h3>
        <p class="ds-muted mb-4">When you book a stay it will appear here.</p>
        <a href="{{ route('reservations.room') }}" class="ds-link">Book your first stay</a>
    </div>
@else
    <div class="ds-stays" data-ds-stagger="80">
        @foreach($reservations as $reservation)
            @php
                $checkIn  = \Carbon\Carbon::parse($reservation->check_in_date);
                $isSuite  = $reservation->roomType->is_suite;
                $checkOut = $reservation->check_out_date
                    ? \Carbon\Carbon::parse($reservation->check_out_date)
                    : null;
                // Suites are sold by duration; rooms by night.
                $duration = $isSuite
                    ? $reservation->duration_value . ' ' . Str::plural($reservation->duration_type, $reservation->duration_value)
                    : ($checkOut ? $checkIn->diffInDays($checkOut) . ' ' . Str::plural('night', $checkIn->diffInDays($checkOut)) : '—');
                $canChange = in_array($reservation->status, ['pending', 'confirmed'], true);
            @endphp

            <article class="ds-stay ds-reveal ds-card-hover">
                {{-- Date block: the thing a guest scans for. --}}
                <div class="ds-stay__date">
                    <span class="ds-stay__month">{{ $checkIn->format('M') }}</span>
                    <span class="ds-stay__day">{{ $checkIn->format('d') }}</span>
                    <span class="ds-stay__year">{{ $checkIn->format('Y') }}</span>
                </div>

                <div class="ds-stay__body">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h3 class="mb-0">{{ $reservation->roomType->name }}</h3>
                        <span class="ds-status ds-status--{{ $reservation->status }}">
                            {{ str_replace('_', ' ', $reservation->status) }}
                        </span>
                    </div>

                    <p class="ds-muted mb-3">{{ $reservation->branch->name }}</p>

                    <div class="ds-stay__meta">
                        <span><strong>{{ $duration }}</strong></span>
                        <span>{{ $reservation->number_of_occupants }} {{ Str::plural('guest', $reservation->number_of_occupants) }}</span>
                        @if($checkOut)
                            <span>Out {{ $checkOut->format('j M Y') }}</span>
                        @endif
                        @if($reservation->credit_card_details)
                            <span class="ds-faint">Card on file</span>
                        @endif
                    </div>
                </div>

                <div class="ds-stay__actions">
                    @if($canChange)
                        <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn btn-outline-dark btn-sm">Change</a>
                        <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST"
                              onsubmit="return confirm('Cancel this reservation? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                    @else
                        <span class="ds-faint" style="font-size:.8rem;">No changes available</span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif

<style>
    .ds-stays { display: grid; gap: 1rem; }

    .ds-stay {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: clamp(1rem, 3vw, 2rem);
        align-items: center;
        background: var(--ds-white);
        border: 1px solid var(--ds-sand);
        border-radius: var(--ds-radius);
        padding: clamp(1.25rem, 2.5vw, 1.75rem);
        transition: box-shadow var(--ds-med) var(--ds-ease),
                    transform var(--ds-med) var(--ds-ease),
                    border-color var(--ds-med) var(--ds-ease);
    }
    @media (min-width: 768px) {
        .ds-stay { grid-template-columns: auto 1fr auto; }
    }

    /* Calendar-leaf date, separated by a hairline. */
    .ds-stay__date {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 4.5rem;
        padding-right: clamp(1rem, 3vw, 2rem);
        border-right: 1px solid var(--ds-sand);
        line-height: 1;
    }
    .ds-stay__month {
        font-size: .68rem;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--ds-clay);
        margin-bottom: .35rem;
    }
    .ds-stay__day {
        font-family: var(--ds-serif);
        font-size: 2.5rem;
        font-weight: 400;
        color: var(--ds-charcoal);
    }
    .ds-stay__year {
        font-size: .68rem;
        letter-spacing: .14em;
        color: var(--ds-faint);
        margin-top: .35rem;
    }

    .ds-stay__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        font-size: .88rem;
        color: var(--ds-muted);
    }
    .ds-stay__meta strong { font-weight: 500; color: var(--ds-charcoal); }

    .ds-stay__actions {
        display: flex;
        align-items: center;
        gap: .5rem;
        grid-column: 1 / -1;
        padding-top: 1rem;
        border-top: 1px solid var(--ds-shell);
    }
    @media (min-width: 768px) {
        .ds-stay__actions {
            grid-column: auto;
            padding-top: 0;
            border-top: 0;
            flex-direction: column;
            align-items: stretch;
        }
    }
    .ds-stay__actions form { margin: 0; }
    .ds-stay__actions .btn { width: 100%; }
</style>
@endsection
