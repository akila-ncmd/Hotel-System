<?php

namespace App\Exports;

use App\Support\Money;
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
                'Total Revenue (' . Money::code() . ')' => Money::plain($report->total_revenue),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Total Revenue (' . Money::code() . ')',
        ];
    }
}