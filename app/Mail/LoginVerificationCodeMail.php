<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
    ) {
    }

    public function build()
    {
        return $this->subject('Your Harborline Provisions login code')
            ->view('emails.login-code')
            ->with([
                'name' => $this->user->name,
                'code' => $this->code,
            ]);
    }
}
