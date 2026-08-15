<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue Report - {{ date('Y-m-d') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        .filters { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Revenue Report - {{ date('Y-m-d') }}</h1>
    <div class="filters">
        <p><strong>Branch:</strong> {{ $branch->name ?? 'Unknown' }}</p>
        <p><strong>Date Range:</strong> {{ $filters['date_range'] }}</p>
    </div>
    @if($reports->isEmpty())
        <p>No revenue reports found for the selected criteria.</p>
    @else
        <table>
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
                        <td>{{ number_format($report->total_revenue ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>