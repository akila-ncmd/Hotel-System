@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Daily Reports</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mb-4">
        <form method="GET" action="{{ route('manager.reports') }}">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_range" class="form-label">Date Range</label>
                    <input type="text" name="date_range" id="date_range" class="form-control" placeholder="Select date range" value="{{ request('date_range') }}">
                </div>
                <div class="col-md-6 mb-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('manager.reports') }}" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>

    @if($reports->isEmpty())
        <p>No reports available for the selected criteria.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Occupancy</th>
                    <th>Revenue</th>
                    <th>No-Shows</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->report_date->format('Y-m-d') }}</td>
                        <td>{{ $report->total_occupancy }}</td>
                        <td>${{ number_format($report->total_revenue, 2) }}</td>
                        <td>{{ $report->no_show_count }}</td>
                        <td>
                            <a href="{{ route('manager.download-report', ['date' => $report->report_date->format('Y-m-d'), 'format' => 'pdf']) }}" class="btn btn-sm btn-primary">PDF</a>
                            <a href="{{ route('manager.download-report', ['date' => $report->report_date->format('Y-m-d'), 'format' => 'excel']) }}" class="btn btn-sm btn-success">Excel</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    $('#date_range').daterangepicker({
        opens: 'left',
        locale: {
            format: 'YYYY-MM-DD'
        }
    });
});
</script>
@endsection