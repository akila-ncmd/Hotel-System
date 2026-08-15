<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OccupancyReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $reservations;

    public function __construct($reservations)
    {
        $this->reservations = $reservations;
    }

    public function collection()
    {
        return $this->reservations->map(function ($reservation) {
            return [
                'Guest Name' => $reservation->user->name,
                'Room Type' => $reservation->roomType->name,
                'Check-In Date' => $reservation->check_in_date->format('Y-m-d'),
                'Check-Out Date' => $reservation->check_out_date->format('Y-m-d'),
                'Status' => ucfirst($reservation->status),
                'Total Price ($)' => number_format($reservation->total_amount ?? 0, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Guest Name',
            'Room Type',
            'Check-In Date',
            'Check-Out Date',
            'Status',
            'Total Price ($)',
        ];
    }
}