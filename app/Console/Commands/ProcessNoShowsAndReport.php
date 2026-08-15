<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Billing;
use App\Models\Room;
use App\Mail\DailyManagerReport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessNoShowsAndReport extends Command
{
    protected $signature = 'reservations:process-no-shows-and-report';
    protected $description = 'Process no-show reservations and generate daily manager report at 7:00 PM';

    public function handle()
    {
        try {
            $yesterday = Carbon::yesterday()->format('Y-m-d');
            $today = Carbon::today()->format('Y-m-d');

            // Process no-show reservations
            $noShowReservations = Reservation::whereIn('status', ['pending', 'confirmed'])
                ->where('check_in_date', $yesterday)
                ->whereDoesntHave('billing')
                ->with(['roomType', 'user', 'branch'])
                ->get();

            $noShowBillings = [];
            foreach ($noShowReservations as $reservation) {
                if (!$reservation->roomType) {
                    Log::warning("No room type for reservation ID {$reservation->id}");
                    continue;
                }

                // Calculate base cost
                $baseCost = $this->calculateTotal($reservation);

                // Create billing record
                $billing = Billing::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => $reservation->user_id,
                    'branch_id' => $reservation->branch_id,
                    'total_amount' => $baseCost,
                    'payment_method' => 'credit_card',
                    'payment_status' => 'pending',
                    'restaurant_charges' => 0,
                    'room_service_charges' => 0,
                    'laundry_charges' => 0,
                    'telephone_charges' => 0,
                    'club_facility_charges' => 0,
                ]);

                // Update reservation status
                $reservation->update(['status' => 'no_show']);
                $noShowBillings[] = $billing;
            }

            // Calculate occupancy for previous night
            $occupiedRooms = Reservation::where('check_in_date', '<=', $yesterday)
                ->where('check_out_date', '>', $yesterday)
                ->where('status', 'checked_in')
                ->count();

            $totalRooms = Room::count();
            $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

            // Calculate revenue from previous night's check-outs and no-shows
            $checkOutRevenue = Billing::whereHas('reservation', function ($query) use ($yesterday) {
                    $query->where('check_out_date', $yesterday)
                          ->where('status', 'checked_out');
                })
                ->where('payment_status', 'paid')
                ->sum('total_amount');

            $noShowRevenue = Billing::whereHas('reservation', function ($query) use ($yesterday) {
                    $query->where('check_in_date', $yesterday)
                          ->where('status', 'no_show');
                })
                ->where('payment_status', 'pending')
                ->whereDate('created_at', $today)
                ->sum('total_amount');

            $totalRevenue = $checkOutRevenue + $noShowRevenue;

            // Prepare data for report
            $data = [
                'date' => $yesterday,
                'occupied_rooms' => $occupiedRooms,
                'total_rooms' => $totalRooms,
                'occupancy_rate' => number_format($occupancyRate, 2),
                'check_out_revenue' => number_format($checkOutRevenue, 2),
                'no_show_revenue' => number_format($noShowRevenue, 2),
                'total_revenue' => number_format($totalRevenue, 2),
                'no_shows' => count($noShowBillings),
                'pdfPath' => 'reports/daily_report_' . $yesterday . '.pdf',
            ];

            // Generate PDF
            $pdf = Pdf::loadView('reports.daily', $data);
            $pdfPath = $data['pdfPath'];
            Storage::put($pdfPath, $pdf->output());

            // Email report to manager
            $managers = \App\Models\User::where('role', 'manager')->get();
            if ($managers->isEmpty()) {
                Log::warning('No managers found to send daily report', ['date' => $yesterday]);
                // Fallback: Store report without emailing
                $this->info("Report generated but no managers found for {$yesterday}");
            } else {
                foreach ($managers as $manager) {
                    try {
                        Mail::to($manager->email)->send(new DailyManagerReport($data, $pdfPath));
                    } catch (\Exception $e) {
                        Log::warning("Failed to send report to manager {$manager->email}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Store report in database
            \App\Models\Report::create([
                'branch_id' => null, // System-wide report
                'report_date' => $yesterday,
                'total_occupancy' => $occupiedRooms,
                'total_revenue' => $totalRevenue,
                'no_show_count' => count($noShowBillings)
            ]);

            Log::info('No-shows and daily report processed', [
                'date' => $yesterday,
                'no_shows' => count($noShowBillings),
                'occupancy_rate' => $occupancyRate,
                'total_revenue' => $totalRevenue
            ]);

            $this->info("Processed {$data['no_shows']} no-shows and generated report for {$yesterday}");
        } catch (\Exception $e) {
            Log::error('Error in ProcessNoShowsAndReport', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Failed to process no-shows and report: ' . $e->getMessage());
        }
    }

    private function calculateTotal($reservation)
    {
        $roomType = $reservation->roomType;
        $total = 0;

        if ($roomType->is_suite) {
            if ($reservation->duration_type === 'weeks') {
                $total = $roomType->weekly_rate * $reservation->duration_value;
            } elseif ($reservation->duration_type === 'months') {
                $total = $roomType->monthly_rate * $reservation->duration_value;
            }
        } else {
            $days = Carbon::parse($reservation->check_out_date)->diffInDays(Carbon::parse($reservation->check_in_date));
            $total = $roomType->price_per_night * $days;
        }

        return $total;
    }
}