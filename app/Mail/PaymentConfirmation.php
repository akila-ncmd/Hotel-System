<?php

namespace App\Mail;

use App\Models\Billing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $billing;

    public function __construct(Billing $billing)
    {
        $this->billing = $billing;
    }

    public function build()
    {
        $reservation = $this->billing->reservation;
        $totalCost = $this->billing->total_amount;
        $durationText = $reservation->roomType->is_suite
            ? "{$reservation->duration_value} " . Str::plural($reservation->duration_type, $reservation->duration_value)
            : $reservation->check_in_date->diffInDays($reservation->check_out_date) . ' day(s)';

        return $this->view('emails.payment-confirmation')
                    ->subject('Payment Confirmation')
                    ->with([
                        'billing' => $this->billing,
                        'reservation' => $reservation,
                        'totalCost' => $totalCost,
                        'durationText' => $durationText,
                    ]);
    }
}