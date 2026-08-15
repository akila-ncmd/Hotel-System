<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $branches = Branch::all();
        $branchData = [];

        foreach ($branches as $branch) {
            $branchData[$branch->id] = [
                'name' => $branch->name,
                'reservations' => Reservation::where('branch_id', $branch->id)->with('user')->get(),
                'users' => User::where('branch_id', $branch->id)->get(),
                'reservation_count' => Reservation::where('branch_id', $branch->id)->count(),
                'user_count' => User::where('branch_id', $branch->id)->count(),
            ];
        }
        

        return view('admin.dashboard', compact('branches', 'branchData'));
    }

    public function generateReport(Request $request)
    {
        try {
            $branchId = $request->input('branch_id', session('admin_selected_branch'));
            if (!$branchId) {
                return back()->withErrors(['branch_id' => 'Please select a branch.']);
            }

            $reportDate = $request->input('report_date', Carbon::today()->toDateString());
            $reportDate = Carbon::parse($reportDate)->startOfDay();

            $branch = Branch::findOrFail($branchId);

            // Calculate total occupancy (checked-in reservations)
            $totalOccupancy = Reservation::where('branch_id', $branchId)
                ->where('status', 'checked_in')
                ->whereDate('check_in_date', '<=', $reportDate)
                ->whereDate('check_out_date', '>=', $reportDate)
                ->count();

            // Calculate no-show count
            $noShowCount = Reservation::where('branch_id', $branchId)
                ->where('status', 'no_show')
                ->whereDate('check_in_date', $reportDate)
                ->count();

            // Calculate total revenue from paid billings
            $totalRevenue = Billing::where('branch_id', $branchId)
                ->where('payment_status', 'paid')
                ->whereHas('reservation', function ($query) use ($reportDate) {
                    $query->whereDate('check_out_date', $reportDate);
                })
                ->sum('total_amount');

            // Create report
            $report = Report::create([
                'branch_id' => $branchId,
                'report_date' => $reportDate,
                'total_occupancy' => $totalOccupancy,
                'no_show_count' => $noShowCount,
                'total_revenue' => $totalRevenue,
            ]);

            Log::info('Report Generated', [
                'report_id' => $report->id,
                'branch_id' => $branchId,
                'report_date' => $reportDate->toDateString(),
                'total_occupancy' => $totalOccupancy,
                'no_show_count' => $noShowCount,
                'total_revenue' => $totalRevenue,
            ]);

            return redirect()->route('admin.reports')->with('success', 'Report generated successfully');
        } catch (\Exception $e) {
            Log::error('Report Generation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to generate report: ' . $e->getMessage()])->withInput();
        }
    }
}