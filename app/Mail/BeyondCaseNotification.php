<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BeyondCaseNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $beyondCases;
    public $upcomingCases;
    public $reportDate;

    public function __construct($recipientName, $beyondCases, $upcomingCases, $reportDate)
    {
        $this->recipientName = $recipientName;
        $this->beyondCases   = $beyondCases;
        $this->upcomingCases = $upcomingCases;
        $this->reportDate    = $reportDate;
    }

    public function build()
    {
        return $this->markdown('emails.beyond-case-notification')
                    ->subject('Case Deadline Report — ' . $this->reportDate)
                    ->with([
                        'recipientName' => $this->recipientName,
                        'beyondCases'   => $this->beyondCases,
                        'upcomingCases' => $this->upcomingCases,
                        'reportDate'    => $this->reportDate,
                    ]);
    }
}