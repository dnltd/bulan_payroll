<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;
    public $role;

    /**
     * Create a new message instance.
     */
    public function __construct(array $credentials)
    {
        $this->email = $credentials['email'];
        $this->password = $credentials['password'];
        $this->role = $credentials['role'];
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Account Credentials')
                    ->view('emails.credentials');
    }
}
