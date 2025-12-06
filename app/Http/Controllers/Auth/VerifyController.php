<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class VerifyController extends Controller
{
    /**
     * Show Forgot Password Form
     */
    public function showForm()
    {
        return view('auth.forgotpass');
    }

    /**
     * Send OTP to user email for password reset
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No user found with this email.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $otp = rand(100000, 999999);
        $email = $request->email;

        Session::put('otp', $otp);
        Session::put('email', $email);
        Session::put('otp_expires_at', Carbon::now()->addMinutes(5));

        $this->sendOtpEmail($email, $otp, 'Reset Your Password - OTP Code');

        return redirect()->route('auth.verify.otp')->with('status', 'OTP has been sent to your email.');
    }

    /**
     * Show OTP Form
     */
    public function showOtpForm()
    {
        if (!Session::has('email')) {
            return redirect()->route('auth.verify.form')
                ->with('error', 'Please enter your email first.');
        }

        return view('auth.verify_otp');
    }

    /**
     * Verify OTP and allow reset
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6|integer'
        ]);

        $inputOtp   = $request->otp;
        $sessionOtp = Session::get('otp');
        $expiresAt  = Session::get('otp_expires_at');

        if (!$expiresAt || Carbon::now()->gt($expiresAt)) {
            Session::forget(['otp', 'otp_expires_at']);
            return back()->with('error', 'OTP expired. Please request a new one.');
        }

        if ($inputOtp == $sessionOtp) {
            Session::forget('otp');
            Session::put('otp_verified', true);
            Session::put('reset_email', Session::get('email'));

            return redirect()->route('auth.password.reset.form');
        }

        return back()->with('error', 'Incorrect OTP. Try again.');
    }

    /**
     * Show Reset Password Form
     */
    public function showResetPasswordForm()
    {
        if (!Session::has('reset_email') || !Session::has('otp_verified')) {
            return redirect()->route('auth.verify.form')
                ->with('error', 'No valid reset request found.');
        }

        return view('auth.reset_password');
    }

    /**
     * Reset Password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[a-z]/',      // at least one lowercase
                'regex:/[0-9]/',      // at least one digit
                'regex:/[@$!%*#?&]/', // at least one special character
                'confirmed'
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = Session::get('reset_email');

        if (!$email) {
            return redirect()->route('auth.verify.form')->with('error', 'Session expired. Please try again.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('auth.verify.form')->with('error', 'User not found.');
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clean up session
        Session::forget(['reset_email', 'otp_verified', 'email', 'otp', 'otp_expires_at']);

        return redirect()->route('login')->with('success', 'Password successfully reset. You can now login.');
    }

    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        $email = Session::get('email');

        if (!$email) {
            return redirect()->route('auth.verify.form')
                ->with('error', 'Session expired. Enter your email again.');
        }

        $otp = rand(100000, 999999);

        Session::put('otp', $otp);
        Session::put('otp_expires_at', Carbon::now()->addMinutes(5));

        $this->sendOtpEmail($email, $otp, 'Resend OTP - Password Reset');

        return back()->with('success', 'A new OTP has been sent to your email.');
    }

    /**
     * Send OTP using PHPMailer
     */
    private function sendOtpEmail($email, $otp, $subject)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $mail->Port       = env('MAIL_PORT', 587);

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Bulan Payroll System'));
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;

            $logoUrl = asset('images/logo.png');

            $mail->Body = "
                <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: auto;
                    border: 1px solid #ddd; border-radius: 8px; overflow: hidden;\">
                    <div style=\"background-color: #17007C; color: #fff; padding: 20px; text-align: center;\">
                        <img src=\"$logoUrl\" alt=\"Logo\" style=\"max-height: 80px; margin-bottom: 10px;\">
                        <h2 style=\"margin: 0;\">Bulan Transport Cooperative</h2>
                        <p style=\"margin: 0; font-size: 14px;\">Password Reset Verification</p>
                    </div>
                    <div style=\"padding: 30px; background-color: #ffffff;\">
                        <h3 style=\"color: #17007C;\">Hello!</h3>
                        <p style=\"font-size: 16px; color: #333;\">
                            Your One-Time Password (OTP) is:
                        </p>
                        <p style=\"font-size: 30px; font-weight: bold; color: #17007C;
                            text-align: center; margin: 20px 0;\">
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
}
