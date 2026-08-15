<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        $totalCost = 0;
        $durationText = '';

        if ($this->reservation->roomType->is_suite) {
            if ($this->reservation->duration_type === 'weeks') {
                $totalCost = $this->reservation->roomType->weekly_rate * $this->reservation->duration_value;
                $durationText = "{$this->reservation->duration_value} week(s)";
            } else {
                $totalCost = $this->reservation->roomType->monthly_rate * $this->reservation->duration_value;
                $durationText = "{$this->reservation->duration_value} month(s)";
            }
        } else {
            $days = $this->reservation->check_in_date->diffInDays($this->reservation->check_out_date);
            $totalCost = $this->reservation->roomType->price_per_night * $days;
            $durationText = "{$days} day(s)";
        }

        return $this->view('emails.reservation-confirmation')
                    ->subject('Reservation Confirmation')
                    ->with([
                        'reservation' => $this->reservation,
                        'totalCost' => $totalCost,
                        'durationText' => $durationText,
                    ]);
    }
}