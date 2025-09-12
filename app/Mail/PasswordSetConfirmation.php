<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordSetConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->markdown('emails.password-set-confirmation')
                    ->subject('Password Set Successfully - Welcome!')
                    ->with([
                        'userName' => $this->user->fname . ' ' . $this->user->lname,
                        'loginUrl' => route('login')
                    ]);
    }
}