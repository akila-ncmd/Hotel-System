@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Revenue Report #{{ $report->id }}</h2>
    <p><strong>Generated:</strong> {{ $report->created_at->format('Y-m-d H:i:s') }}</p>
    <p><strong>Date Range:</strong> {{ $filters['date_range'] ?? 'All Dates' }}</p>

    <!-- Download Buttons -->
    <div class="mb-4">
        <a href="{{ route('manager.download-individual-report', ['id' => $report->id, 'format' => 'pdf']) }}" class="btn btn-primary">Download PDF</a>
        <a href="{{ route('manager.download-individual-report', ['id' => $report->id, 'format' => 'excel']) }}" class="btn btn-success">Download Excel</a>
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
                @foreach($reports as $reportItem)
                    <tr>
                        <td>{{ $reportItem->date }}</td>
                        <td>{{ number_format($reportItem->total_revenue ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('manager.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
</div>
@endsection