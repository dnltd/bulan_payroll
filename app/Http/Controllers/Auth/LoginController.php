<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class LoginController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login request
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role !== 'admin' || !$user->is_verified) {
                Auth::logout();
                return back()->withErrors(['email' => 'Unauthorized or unverified account.']);
            }

            // Generate OTP
            $otp = rand(100000, 999999);

            // Store OTP and expiry in session
            Session::put('otp_user_id', $user->id);
            Session::put('otp_code', $otp);
            Session::put('otp_expires_at', now()->addMinutes(5));

            // Send OTP via PHPMailer
            $this->sendOtpEmail($user->email, $otp);

            return redirect()->route('otp.form');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    // Show OTP verification form
    public function showOtpForm()
    {
        return view('auth.otp_form');
    }

    // Handle OTP verification
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $enteredOtp = $request->input('otp');
        $storedOtp = Session::get('otp_code');
        $expiresAt = Session::get('otp_expires_at');
        $userId = Session::get('otp_user_id');

        if (!$storedOtp || !$expiresAt || now()->gt($expiresAt)) {
            Auth::logout();
            Session::forget(['otp_code', 'otp_expires_at', 'otp_user_id']);
            return redirect('/login')->withErrors(['otp' => 'OTP expired. Please log in again.']);
        }

        if ($enteredOtp == $storedOtp) {
    Session::forget(['otp_code', 'otp_expires_at']);
    Auth::loginUsingId($userId);

    $role = Auth::user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($role === 'dispatcher') {
        return redirect()->route('dispatcher.scan');
    } else {
        Auth::logout();
        return redirect('/login')->with('error', 'Unauthorized role access.');
    }
}


        return back()->with('error', 'Invalid OTP');
    }

    // Resend OTP
    public function resendOtp()
    {
        $userId = Session::get('otp_user_id');
        if (!$userId) {
            return redirect('/login');
        }

        $otp = rand(100000, 999999);
        $user = User::find($userId);

        Session::put('otp_code', $otp);
        Session::put('otp_expires_at', now()->addMinutes(5));
        $this->sendOtpEmail($user->email, $otp);

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    // PHPMailer setup to send OTP
    private function sendOtpEmail($email, $otp)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'letadadan12@gmail.com'; // Your email
        $mail->Password = 'zlhm norg dqrt kiyw';   // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('letadadan12@gmail.com', 'Bulan Transport Cooperative');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'OTP Code for Login';

        $logoUrl = asset('images/logo.png'); // Path to logo in /public/images

        $mail->Body = "
            <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;\">
                <div style=\"background-color: #17007C; color: #fff; padding: 20px; text-align: center;\">
                    <img src=\"$logoUrl\" alt=\"Logo\" style=\"max-height: 80px; margin-bottom: 10px;\">
                    <h2 style=\"margin: 0;\">Payroll Management System</h2>
                    <p style=\"margin: 0; font-size: 14px;\">Bulan Transport Cooperative</p>
                </div>
                <div style=\"padding: 30px; background-color: #ffffff;\">
                    <h3 style=\"color: #17007C;\">Hello!</h3>
                    <p style=\"font-size: 16px; color: #333;\">
                        Your One-Time Password (OTP) is:
                    </p>
                    <p style=\"font-size: 30px; font-weight: bold; color: #17007C; text-align: center; margin: 20px 0;\">
                        $otp
                    </p>
                    <p style=\"font-size: 14px; color: #555;\">
                        This code is valid for 5 minutes. Please do not share it with anyone.
                    </p>
                    <p style=\"font-size: 13px; color: #aaa; text-align: center; margin-top: 40px;\">
                        &copy; " . date('Y') . " Bulan Transport Cooperative. All rights reserved.
                    </p>
                </div>
            </div>
        ";

        $mail->send();
    } catch (MailException $e) {
        \Log::error('PHPMailer Error: ' . $e->getMessage());
    }
}


    // Logout
    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect('/login');
    }
}
