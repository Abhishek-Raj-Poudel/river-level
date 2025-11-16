<?php

namespace App\Mail;

use App\Models\RiverLevel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RiverLevelAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RiverLevel $river)
    {
    }

    public function build()
    {
        return $this->subject('River Level Alert')
            ->view('emails.river-alert');
    }
}
