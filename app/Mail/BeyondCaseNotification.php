<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BeyondCaseNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $cases;         // collection of beyond cases for this recipient
    public $reportDate;

    public function __construct($recipientName, $cases, $reportDate)
    {
        $this->recipientName = $recipientName;
        $this->cases         = $cases;
        $this->reportDate    = $reportDate;
    }

    public function build()
    {
        return $this->markdown('emails.beyond-case-notification')
                    ->subject('Beyond Deadline Cases — ' . $this->reportDate)
                    ->with([
                        'recipientName' => $this->recipientName,
                        'cases'         => $this->cases,
                        'reportDate'    => $this->reportDate,
                    ]);
    }
}