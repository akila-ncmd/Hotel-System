<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyManagerReport extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $pdfPath;

    public function __construct($data, $pdfPath)
    {
        $this->data = $data;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject('Daily Manager Report - ' . $this->data['date'])
                    ->view('emails.daily_manager_report')
                    ->attachFromStorage($this->pdfPath, 'daily_report.pdf', [
                        'mime' => 'application/pdf',
                    ])
                    ->with(['data' => $this->data]);
    }
}