<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.verification-email')
            ->text('emails.verification-email_plain')
            ->with(['verificationCode' => $this->user->verification_code])
            ->subject('Fifty years of the Auckland 4WD Club, Login');
    }
}
