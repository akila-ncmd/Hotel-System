<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Mail\ReservationCancelled;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelNoCreditReservations extends Command
{
    protected $signature = 'reservations:cancel-no-credit';
    protected $description = 'Cancel reservations without credit card details at 7 PM daily';

    public function handle()
    {
        try {
            $today = Carbon::today()->format('Y-m-d');

            // Find reservations without credit card details
            $reservations = Reservation::whereNull('credit_card_details')
                                       ->where('check_in_date', $today)
                                       ->whereIn('status', ['pending', 'confirmed'])
                                       ->with('user')
                                       ->get();

            $cancelledCount = 0;
            foreach ($reservations as $reservation) {
                $reservation->update(['status' => 'cancelled']);
                $cancelledCount++;

                // Notify customer
                if ($reservation->user && $reservation->user->email) {
                    try {
                        Mail::to($reservation->user->email)->send(new ReservationCancelled($reservation));
                    } catch (\Exception $e) {
                        Log::warning("Failed to send cancellation email for reservation {$reservation->id}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info('No-credit reservations processed', [
                'date' => $today,
                'cancelled_count' => $cancelledCount
            ]);

            $this->info("Cancelled {$cancelledCount} reservations without credit card details for {$today}");
        } catch (\Exception $e) {
            Log::error('Error in CancelNoCreditReservations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Failed to process cancellations: ' . $e->getMessage());
        }
    }
}