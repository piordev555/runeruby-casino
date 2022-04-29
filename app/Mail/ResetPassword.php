<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable {

    use Queueable, SerializesModels;

    private string $user;
    private string $token;

    public function __construct(string $user, string $token) {
        $this->user = $user;
        $this->token = $token;
    }

    public function build() {
        return $this->view('mail.resetPassword')->with(['user' => $this->user, 'token' => $this->token]);
    }

}
