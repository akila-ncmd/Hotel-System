<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Report - {{ $report->report_date->format('Y-m-d') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Daily Report - {{ $report->report_date->format('Y-m-d') }}</h1>
    <div class="summary">
        <p><strong>Branch:</strong> {{ Auth::user()->branch->name }}</p>
        <p><strong>Date:</strong> {{ $report->report_date->format('Y-m-d') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Occupancy</th>
                <th>Total Revenue ($)</th>
                <th>No-Show Count</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $report->report_date->format('Y-m-d') }}</td>
                <td>{{ $report->total_occupancy }}</td>
                <td>{{ number_format($report->total_revenue, 2) }}</td>
                <td>{{ $report->no_show_count }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>