@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="display-5 fw-bold mb-4">Branch Data: {{ $branch->name }}</h1>

    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mb-4">Back to Dashboard</a>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Branch-Specific Actions -->
    <div class="row g-4 mb-4">
        <!-- Customer Actions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h4 mb-3">Customer Actions for {{ $branch->name }}</h3>
                    <p class="text-muted">Perform customer tasks for this branch.</p>
                    <a href="{{ route('reservations.room') }}?branch_id={{ $branch->id }}" class="btn btn-outline-primary me-2 mb-2">Room Reservation</a>
                    <a href="{{ route('reservations.suite') }}?branch_id={{ $branch->id }}" class="btn btn-outline-primary me-2 mb-2">Suite Reservation</a>
                    <a href="{{ route('customer.reservations') }}?branch_id={{ $branch->id }}" class="btn btn-outline-primary me-2 mb-2">View Reservations</a>
                </div>
            </div>
        </div>

        <!-- Clerk Actions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h4 mb-3">Clerk Actions for {{ $branch->name }}</h3>
                    <p class="text-muted">Perform clerk tasks for this branch.</p>
                    <a href="{{ route('clerk.dashboard') }}?branch_id={{ $branch->id }}" class="btn btn-outline-primary mb-2">Clerk Dashboard</a>
                </div>
            </div>
        </div>

        <!-- Manager Actions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h4 mb-3">Manager Actions for {{ $branch->name }}</h3>
                    <p class="text-muted">View reports for this branch.</p>
                    <a href="{{ route('manager.occupancy-report') }}?branch_id={{ $branch->id }}" class="btn btn-outline-primary me-2 mb-2">Occupancy Report</a>
                    <a href="{{ route('manager.revenue-report') }}?branch_id={{ $branch->id }}" class="btn btn-outline-primary me-2 mb-2">Revenue Report</a>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection