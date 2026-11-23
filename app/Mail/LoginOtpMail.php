<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LoginOtpMail extends Mailable
{
    public function __construct(public User $user, public string $code, public int $minutes)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode verifikasi masuk Routerverse');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.login-otp', text: 'emails.login-otp-text');
    }
}
