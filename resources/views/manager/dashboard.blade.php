@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Manager Dashboard</h2>

    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <a href="{{ route('manager.occupancy-report') }}" class="btn btn-primary btn-lg w-100">Occupancy Reports</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('manager.revenue-report') }}" class="btn btn-primary btn-lg w-100">Revenue Reports</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('manager.calendar-view') }}" class="btn btn-primary btn-lg w-100">Calendar View</a>
        </div>
    </div>
</div>
@endsection