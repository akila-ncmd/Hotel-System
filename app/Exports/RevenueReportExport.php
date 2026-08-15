<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RevenueReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $reports;

    public function __construct($reports)
    {
        $this->reports = $reports;
    }

    public function collection()
    {
        return $this->reports->map(function ($report) {
            return [
                'Date' => $report->date,
                'Total Revenue ($)' => number_format($report->total_revenue ?? 0, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Total Revenue ($)',
        ];
    }
}