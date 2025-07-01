<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email not found.']);
        }

        $otp = rand(100000, 999999);
        Session::put('reset_email', $user->email);
        Session::put('reset_otp', $otp);

        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Your OTP Code');
        });

        return redirect()->route('password.verify')->with('success', 'OTP sent to your email.');
    }

    public function showVerifyForm()
    {
        return view('auth.verify');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        if ($request->otp == Session::get('reset_otp')) {
            return redirect()->route('password.reset');
        }

        return back()->withErrors(['otp' => 'Invalid OTP.']);
    }

    public function showResetForm()
    {
        if (!Session::has('reset_email')) return redirect()->route('password.request');
        return view('auth.create_password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8|max:20|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@#$!.*]/',
        ]);

        $user = User::where('email', Session::get('reset_email'))->first();
        if (!$user) return redirect()->route('password.request');

        $user->password = Hash::make($request->password);
        $user->save();

        Session::forget(['reset_email', 'reset_otp']);
        return redirect()->route('login')->with('success', 'Password updated. You can now log in.');
    }
}
