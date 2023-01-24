<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Registrasi Berhasil',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.registered',
            with: [
                'user' => $this->user,
                'password' => $this->password,
            ],
        );
    }

    public function attachments()
    {
        return [];
    }
}
