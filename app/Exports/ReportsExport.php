<?php

namespace App\Exports;

use App\Support\Money;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

/**
 * Excel export for a single saved daily report.
 *
 * Used by ManagerController::downloadReport() for the 'excel' format. Accepts either a
 * single Report model or a collection of them, so it works for one day or a date range.
 */
class ReportsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $reports;

    public function __construct($reports)
    {
        // downloadReport() passes a single model; normalise so collection() is uniform.
        $this->reports = $reports instanceof Collection ? $reports : collect([$reports]);
    }

    public function collection()
    {
        return $this->reports->map(function ($report) {
            return [
                'Date' => optional($report->report_date)->format('Y-m-d'),
                'Total Occupancy' => $report->total_occupancy,
                'No-Show Count' => $report->no_show_count,
                'Total Revenue (' . Money::code() . ')' => Money::plain($report->total_revenue),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Total Occupancy',
            'No-Show Count',
            'Total Revenue (' . Money::code() . ')',
        ];
    }
}
