<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MissingSheriffReportNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $monthLabel;
    public $missingCases;

    public function __construct($recipientName, $monthLabel, $missingCases)
    {
        $this->recipientName = $recipientName;
        $this->monthLabel    = $monthLabel;
        $this->missingCases  = $missingCases;
    }

    public function build()
    {
        return $this->markdown('emails.missing-sheriff-report')
                    ->subject('Missing Sheriff Reports — ' . $this->monthLabel)
                    ->with([
                        'recipientName' => $this->recipientName,
                        'monthLabel'    => $this->monthLabel,
                        'missingCases'  => $this->missingCases,
                    ]);
    }
}