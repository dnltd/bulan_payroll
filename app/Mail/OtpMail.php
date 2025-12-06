<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Build the message.
     */
    public function build()
{
    $logoUrl = asset('images/logo.png');

    $body = "
        <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;\">
            <div style=\"background-color: #17007C; color: #fff; padding: 20px; text-align: center;\">
                <img src=\"$logoUrl\" alt=\"Logo\" style=\"max-height: 80px; margin-bottom: 10px;\">
                <h2 style=\"margin: 0;\">Payroll Management System</h2>
                <p style=\"margin: 0; font-size: 14px;\">Bulan Transport Cooperative</p>
            </div>
            <div style=\"padding: 30px; background-color: #ffffff;\">
                <h3 style=\"color: #17007C;\">Password Reset Request</h3>
                <p style=\"font-size: 16px; color: #333;\">
                    You have requested to reset your password. Use the OTP code below to proceed:
                </p>
                <p style=\"font-size: 32px; font-weight: bold; color: #17007C; text-align: center; margin: 20px 0;\">
                    {$this->otp}
                </p>
                <p style=\"font-size: 14px; color: #555;\">
                    This code will expire in 5 minutes. If you did not request a password reset, please ignore this email.
                </p>
                <p style=\"font-size: 13px; color: #aaa; text-align: center; margin-top: 40px;\">
                    &copy; " . date('Y') . " Bulan Transport Cooperative. All rights reserved.
                </p>
            </div>
        </div>
    ";

    return $this->subject('Password Reset OTP')
                ->html($body);
}

}
