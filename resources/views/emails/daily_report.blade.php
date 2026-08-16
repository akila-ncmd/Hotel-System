@component('mail::message')
# Daily Hotel Report - {{ $data['date'] }}

Dear Manager,

Please find the daily report for {{ $data['date'] }} attached as a PDF. Summary:

- **Occupied Rooms**: {{ $data['occupied_rooms'] }} / {{ $data['total_rooms'] }}
- **Occupancy Rate**: {{ $data['occupancy_rate'] }}%
- **Revenue**: @money($data['total_revenue'])
- **No-Shows**: {{ $data['no_shows'] }}

Thank you,
{{ config('app.name') }}
@endcomponent