@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Revenue Report</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="mb-4">
        <form method="GET" action="{{ route('manager.revenue-report') }}" id="revenue-report-form">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date_range" class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" id="start_date" class="form-control"
                               value="{{ request('start_date') ?? old('start_date') }}"
                               placeholder="Start date">
                        <span class="input-group-text">to</span>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                               value="{{ request('end_date') ?? old('end_date') }}"
                               placeholder="End date">
                        <!-- Hidden input for the combined date range -->
                        <input type="hidden" name="date_range" id="date_range" value="">
                    </div>
                    <small class="text-muted">Select start and end dates</small>
                </div>
                <div class="col-md-2 mb-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('manager.revenue-report') }}" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Download Buttons -->
    <div class="mb-4">
        <a href="{{ route('manager.download-revenue-report', ['format' => 'pdf', 'date_range' => request('date_range')]) }}" class="btn btn-primary">Download PDF</a>
        <a href="{{ route('manager.download-revenue-report', ['format' => 'excel', 'date_range' => request('date_range')]) }}" class="btn btn-success">Download Excel</a>
    </div>

    <!-- Reports Table -->
    @if($reports->isEmpty())
        <p>No revenue reports found for the selected criteria.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Revenue ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>@money($report->total_revenue ?? 0)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- DateRangePicker Dependencies (only for styling, Pikaday as fallback) -->
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('revenue-report-form');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const dateRangeInput = document.getElementById('date_range');

    // Set default dates if not already set
    if (!startDateInput.value) {
        const defaultStart = new Date();
        startDateInput.valueAsDate = defaultStart;
    }

    if (!endDateInput.value) {
        const defaultEnd = new Date();
        defaultEnd.setDate(defaultEnd.getDate() + 7);
        endDateInput.valueAsDate = defaultEnd;
    }

    // Update the hidden date_range input before form submission
    form.addEventListener('submit', function(e) {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (startDate && endDate) {
            dateRangeInput.value = `${startDate} - ${endDate}`;
        } else if (startDate) {
            dateRangeInput.value = `${startDate} - ${startDate}`;
        } else if (endDate) {
            dateRangeInput.value = `${endDate} - ${endDate}`;
        }
    });

    // Add event listeners to enforce start date <= end date
    startDateInput.addEventListener('change', function() {
        if (endDateInput.value && new Date(this.value) > new Date(endDateInput.value)) {
            endDateInput.value = this.value;
        }
    });

    endDateInput.addEventListener('change', function() {
        if (startDateInput.value && new Date(this.value) < new Date(startDateInput.value)) {
            startDateInput.value = this.value;
        }
    });

    // For browsers that don't support native date picker, initialize Pikaday
    if (window.Pikaday) {
        new Pikaday({
            field: document.getElementById('start_date'),
            format: 'YYYY-MM-DD',
            onSelect: function() {
                startDateInput.dispatchEvent(new Event('change'));
            }
        });

        new Pikaday({
            field: document.getElementById('end_date'),
            format: 'YYYY-MM-DD',
            onSelect: function() {
                endDateInput.dispatchEvent(new Event('change'));
            }
        });
    }
});
</script>
@endsection