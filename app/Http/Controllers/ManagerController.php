<?php

namespace App\Http\Controllers;

use App\Exports\OccupancyReportExport;
use App\Exports\ReportsExport;
use App\Exports\RevenueReportExport;
use App\Models\Report;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:manager,admin');
    }

    protected function getBranchId(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            $branchId = session('admin_selected_branch', $request->query('branch_id'));
            if (!$branchId) {
                abort(400, 'No branch selected.');
            }
            return $branchId;
        }
        return Auth::user()->branch_id;
    }

    public function dashboard(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $reports = Report::where('branch_id', $branchId)->latest()->take(10)->get();
        $branch = Branch::findOrFail($branchId);
        return view('manager.dashboard', compact('reports', 'branch'));
    }

    public function reports(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $query = Report::where('branch_id', $branchId);
        $branch = Branch::findOrFail($branchId);

        if ($request->date_range) {
            [$start, $end] = explode(' - ', $request->date_range);
            $query->whereBetween('report_date', [$start, $end]);
        }

        $reports = $query->latest()->get();

        return view('manager.reports', compact('reports', 'branch'));
    }

    public function downloadReport(Request $request, $date, $format)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $report = Report::where('branch_id', $branchId)
                        ->where('report_date', $date)
                        ->firstOrFail();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('manager.daily-report', compact('report', 'branch'));
            return $pdf->download('daily_report_' . $date . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReportsExport($report), 'daily_report_' . $date . '.xlsx');
        }

        abort(400, 'Invalid format');
    }

    public function occupancyReport(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $query = Reservation::where('reservations.branch_id', $branchId)
                            ->join('billings', 'reservations.id', '=', 'billings.reservation_id')
                            ->with(['roomType', 'user'])
                            ->select('reservations.*', 'billings.total_amount');
        $roomTypes = RoomType::all();

        if ($request->date_range) {
            $request->validate([
                'date_range' => 'required|regex:/^\d{4}-\d{2}-\d{2} - \d{4}-\d{2}-\d{2}$/',
            ]);
            [$start, $end] = explode(' - ', $request->date_range);
            $query->whereBetween('reservations.check_in_date', [$start, $end]);
        }
        if ($request->room_type_id) {
            $query->where('reservations.room_type_id', $request->room_type_id);
        }
        if ($request->status) {
            $query->where('reservations.status', $request->status);
        }

        $reservations = $query->get();

        // Save to reports table
        if ($request->date_range) {
            $startDate = Carbon::parse(explode(' - ', $request->date_range)[0]);
            $endDate = Carbon::parse(explode(' - ', $request->date_range)[1]);
            $dates = [];
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dates[] = $date->toDateString();
            }

            foreach ($dates as $date) {
                $count = $reservations->where('check_in_date', $date)->count();
                $totalRevenue = $reservations->where('check_in_date', $date)->sum('total_amount');
                Report::updateOrCreate(
                    [
                        'branch_id' => $branchId,
                        'report_date' => $date,
                    ],
                    [
                        'total_occupancy' => $count,
                        'total_revenue' => $totalRevenue,
                        'no_show_count' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return view('manager.occupancy-report', compact('reservations', 'roomTypes', 'branch'));
    }

    public function downloadOccupancyReport(Request $request, $format)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $query = Reservation::where('reservations.branch_id', $branchId)
                            ->join('billings', 'reservations.id', '=', 'billings.reservation_id')
                            ->with(['user', 'roomType'])
                            ->select('reservations.*', 'billings.total_amount');
        $roomTypes = RoomType::all();

        if ($request->date_range) {
            $request->validate([
                'date_range' => 'required|regex:/^\d{4}-\d{2}-\d{2} - \d{4}-\d{2}-\d{2}$/',
            ]);
            [$start, $end] = explode(' - ', $request->date_range);
            $query->whereBetween('reservations.check_in_date', [$start, $end]);
        }
        if ($request->room_type_id) {
            $query->where('reservations.room_type_id', $request->room_type_id);
        }
        if ($request->status) {
            $query->where('reservations.status', $request->status);
        }

        $reservations = $query->get();
        $filters = [
            'date_range' => $request->input('date_range') ?? 'All Dates',
            'room_type' => $request->room_type_id && $roomTypes->find($request->room_type_id) ? RoomType::find($request->room_type_id)->name : 'All Room Types',
            'status' => $request->status ?: 'All Statuses',
        ];

        // Save to reports table
        if ($request->date_range) {
            $startDate = Carbon::parse(explode(' - ', $request->date_range)[0]);
            $endDate = Carbon::parse(explode(' - ', $request->date_range)[1]);
            $dates = [];
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dates[] = $date->toDateString();
            }

            foreach ($dates as $date) {
                $count = $reservations->where('check_in_date', $date)->count();
                $totalRevenue = $reservations->where('check_in_date', $date)->sum('total_amount');
                Report::updateOrCreate(
                    [
                        'branch_id' => $branchId,
                        'report_date' => $date,
                    ],
                    [
                        'total_occupancy' => $count,
                        'total_revenue' => $totalRevenue,
                        'no_show_count' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('manager.occupancy-report-pdf', compact('reservations', 'filters', 'branch'));
            return $pdf->download('occupancy_report_' . date('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new OccupancyReportExport($reservations), 'occupancy_report_' . date('Y-m-d') . '.xlsx');
        }

        abort(400, 'Invalid format');
    }

    public function revenueReport(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $query = Reservation::where('reservations.branch_id', $branchId)
                           ->join('billings', 'reservations.id', '=', 'billings.reservation_id')
                           ->select(
                               DB::raw('DATE(reservations.check_in_date) as date'),
                               DB::raw('SUM(billings.total_amount) as total_revenue')
                           )
                           ->groupBy(DB::raw('DATE(reservations.check_in_date)'));

        if ($request->date_range) {
            $request->validate([
                'date_range' => 'required|regex:/^\d{4}-\d{2}-\d{2} - \d{4}-\d{2}-\d{2}$/',
            ]);
            [$start, $end] = explode(' - ', $request->date_range);
            $startDate = Carbon::parse($start)->startOfDay()->toDateString();
            $endDate = Carbon::parse($end)->endOfDay()->toDateString();
            Log::info('Revenue Report Filter', [
                'date_range' => $request->date_range,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'branch_id' => $branchId,
            ]);
            $query->whereBetween('reservations.check_in_date', [$startDate, $endDate]);

            $sql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info('Revenue Report SQL', [
                'sql' => $sql,
                'bindings' => $bindings,
            ]);
        }

        $reports = $query->orderBy('date', 'desc')->get();

        // Save to reports table
		foreach ($reports as $report) {
			$occupancyCount = Reservation::where('branch_id', $branchId)
				->whereDate('check_in_date', $report->date)
				->count();
		
			Report::updateOrCreate(
				[
					'branch_id' => $branchId,
					'report_date' => $report->date,
				],
				[
					'total_occupancy' => $occupancyCount,
					'total_revenue' => $report->total_revenue,
					'no_show_count' => 0,
					'created_at' => now(),
					'updated_at' => now(),
				]
			);
		}


        Log::info('Revenue Report Results', [
            'count' => $reports->count(),
            'reports' => $reports->map(function ($report) {
                return [
                    'date' => $report->date,
                    'total_revenue' => $report->total_revenue,
                ];
            })->toArray(),
        ]);

        return view('manager.revenue-report', compact('reports', 'branch'));
    }

    public function downloadRevenueReport(Request $request, $format)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $query = Reservation::where('reservations.branch_id', $branchId)
                           ->join('billings', 'reservations.id', '=', 'billings.reservation_id')
                           ->select(
                               DB::raw('DATE(reservations.check_in_date) as date'),
                               DB::raw('SUM(billings.total_amount) as total_revenue')
                           )
                           ->groupBy(DB::raw('DATE(reservations.check_in_date)'));

        if ($request->date_range) {
            $request->validate([
                'date_range' => 'required|regex:/^\d{4}-\d{2}-\d{2} - \d{4}-\d{2}-\d{2}$/',
            ]);
            [$start, $end] = explode(' - ', $request->date_range);
            $startDate = Carbon::parse($start)->startOfDay()->toDateString();
            $endDate = Carbon::parse($end)->endOfDay()->toDateString();
            Log::info('Download Revenue Report Filter', [
                'date_range' => $request->date_range,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'branch_id' => $branchId,
            ]);
            $query->whereBetween('reservations.check_in_date', [$startDate, $endDate]);

            $sql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info('Download Revenue Report SQL', [
                'sql' => $sql,
                'bindings' => $bindings,
            ]);
        }

        $reports = $query->orderBy('date', 'desc')->get();

        // Save to reports table
		foreach ($reports as $report) {
			$occupancyCount = Reservation::where('branch_id', $branchId)
				->whereDate('check_in_date', $report->date)
				->count();
		
			Report::updateOrCreate(
				[
					'branch_id' => $branchId,
					'report_date' => $report->date,
				],
				[
					'total_occupancy' => $occupancyCount,
					'total_revenue' => $report->total_revenue,
					'no_show_count' => 0,
					'created_at' => now(),
					'updated_at' => now(),
				]
			);
		}


        Log::info('Download Revenue Report Results', [
            'count' => $reports->count(),
            'reports' => $reports->map(function ($report) {
                return [
                    'date' => $report->date,
                    'total_revenue' => $report->total_revenue,
                ];
            })->toArray(),
        ]);

        $filters = [
            'date_range' => $request->input('date_range') ?? 'All Dates',
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('manager.revenue-report-pdf', compact('reports', 'filters', 'branch'));
            return $pdf->download('revenue_report_' . date('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new RevenueReportExport($reports), 'revenue_report_' . date('Y-m-d') . '.xlsx');
        }

        abort(400, 'Invalid format');
    }

    public function calendarView(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $rooms = Room::where('branch_id', $branchId)->with('roomType')->get();
        $reservations = Reservation::where('branch_id', $branchId)
                                   ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                                   ->with('roomType')
                                   ->get();
        return view('manager.calendar-view', compact('rooms', 'reservations', 'branch'));
    }
}