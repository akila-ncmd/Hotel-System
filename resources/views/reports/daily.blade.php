<!DOCTYPE html>
<html>
<head>
    <title>Daily Report - {{ $date }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Daily Report - {{ $date }}</h1>
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Occupied Rooms</td>
            <td>{{ $occupied_rooms }} / {{ $total_rooms }}</td>
        </tr>
        <tr>
            <td>Occupancy Rate</td>
            <td>{{ $occupancy_rate }}%</td>
        </tr>
        <tr>
            <td>Check-Out Revenue</td>
            <td>${{ $check_out_revenue }}</td>
        </tr>
        <tr>
            <td>No-Show Revenue</td>
            <td>${{ $no_show_revenue }}</td>
        </tr>
        <tr>
            <td>Total Revenue</td>
            <td>${{ $total_revenue }}</td>
        </tr>
        <tr>
            <td>No-Shows</td>
            <td>{{ $no_shows }}</td>
        </tr>
    </table>
</body>
</html>