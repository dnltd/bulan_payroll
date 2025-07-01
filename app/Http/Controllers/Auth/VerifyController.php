<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class VerifyController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.forgot');
    }

    public function sendOTP(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $otp = rand(100000, 999999);
        Session::put('reset_email', $request->email);
        Session::put('otp', $otp);
        Session::put('otp_expires_at', now()->addMinutes(5));

        // Send OTP via email
        Mail::raw("Your OTP Code: $otp", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Your OTP Code');
        });

        return redirect()->route('password.otp.verify')->with('success', 'OTP sent to your email.');
    }

    public function showOTPForm()
    {
        return view('auth.verify');
    }

    public function verifyOTP(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        if (Session::get('otp') == $request->otp && now()->lt(Session::get('otp_expires_at'))) {
            Session::put('otp_verified', true);
            return redirect()->route('password.create');
        }

        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }

    public function showCreatePassword()
    {
        if (!Session::get('otp_verified')) return redirect()->route('password.request');
        return view('auth.create_password');
    }

    public function saveNewPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8|max:20|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[@#\.\!\*\$]/'
        ]);

        $user = User::where('email', Session::get('reset_email'))->first();
        $user->password = Hash::make($request->password);
        $user->save();

        Session::forget(['otp', 'otp_verified', 'reset_email', 'otp_expires_at']);
        return redirect()->route('login')->with('success', 'Password updated. You can now login.');
    }
}
