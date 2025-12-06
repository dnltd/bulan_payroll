<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Otp;
use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class LoginController extends Controller
{
    // --- Security tuning knobs ---
    private const LOGIN_MAX_ATTEMPTS      = 5;     // per minute
    private const LOGIN_DECAY_SECONDS     = 60;

    private const OTP_MAX_ATTEMPTS        = 5;     // per 10 minutes window
    private const OTP_ATTEMPT_DECAY       = 600;

    private const RESEND_MAX_ATTEMPTS     = 3;     // per hour
    private const RESEND_DECAY_SECONDS    = 3600;

    private const OTP_TTL_MINUTES         = 5;     // OTP validity

    /**
     * Show login form (for Admin & Dispatcher).
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request (Admin + Dispatcher).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // Throttle by email + IP
        $loginKey = $this->loginThrottleKey($request);
        if (RateLimiter::tooManyAttempts($loginKey, self::LOGIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($loginKey);
            return back()->withErrors([
                'email' => "Too many attempts. Try again in {$seconds} seconds."
            ])->withInput(['email' => $request->email]);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($loginKey, self::LOGIN_DECAY_SECONDS);
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput(['email' => $request->email]);
        }

        // Authenticated with password; do NOT keep the session authenticated until OTP completes.
        $user = Auth::user();

        // Gate by role + verification flag
        if (!in_array($user->role, ['admin', 'dispatcher']) || !$user->is_verified) {
            Auth::logout();
            return back()->withErrors(['email' => 'Unauthorized or unverified account.']);
        }

        // Invalidate any previous unused OTPs
        Otp::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate a fresh OTP
        $otp = random_int(100000, 999999);

        Otp::create([
            'user_id'    => $user->id,
            'otp_code'   => $otp,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_TTL_MINUTES),
            'is_used'    => false,
        ]);

        // Harden session for OTP step (minimize stored data)
        Session::regenerate(); // prevent fixation
        Session::put('otp_user_id', $user->id);

        // We immediately logout the password session; OTP will re-authenticate
        Auth::logout();

        // Send OTP (best effort; failures logged)
        $this->sendOtpEmail($user->email, $otp);

        // Clear login throttling on success path
        RateLimiter::clear($loginKey);

        return redirect()->route('auth.otp.form');
    }

    /**
     * Show OTP form
     */
    public function showOtpForm()
    {
        // Require pending OTP session
        if (!Session::has('otp_user_id')) {
            return redirect('/login')->withErrors(['otp' => 'Session expired. Please login again.']);
        }
        return view('auth.otp_form');
    }

    /**
     * Verify OTP and redirect based on role
     */
    public function verifyOtp(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6'
    ]);

    $userId = Session::get('otp_user_id');
    if (!$userId) {
        return redirect()->route('login')->with('error', 'Session expired. Please login again.');
    }

    // Throttle OTP attempts per user
    $otpKey = $this->otpThrottleKey($userId, $request);
    if (RateLimiter::tooManyAttempts($otpKey, self::OTP_MAX_ATTEMPTS)) {
        $seconds = RateLimiter::availableIn($otpKey);
        return back()->with('error', "Too many OTP attempts. Try again in {$seconds} seconds.");
    }

    $otp = Otp::where('user_id', $userId)
        ->where('otp_code', $request->otp)
        ->where('is_used', false)
        ->latest()
        ->first();

    if (!$otp) {
        RateLimiter::hit($otpKey, self::OTP_ATTEMPT_DECAY);
        return back()->with('error', 'Invalid OTP.');
    }

    if ($otp->expires_at->isPast()) {
        $otp->update(['is_used' => true]); // Burn expired OTP
        RateLimiter::hit($otpKey, self::OTP_ATTEMPT_DECAY);
        return back()->with('error', 'OTP expired.');
    }

    // ✅ OTP valid → mark as used
    $otp->update(['is_used' => true]);

    // Authenticate user and harden session
    Auth::loginUsingId($userId);
    Session::forget('otp_user_id');
    Session::regenerate();

    // Clear OTP attempt counter
    RateLimiter::clear($otpKey);

    // ✅ Role-based redirect
    // Role-based redirect
$role = Auth::user()->role;
$name = Auth::user()->full_name ?? Auth::user()->name;

if ($role === 'admin') {
    return redirect()->route('login.success')->with([
        'role' => 'admin',
        'name' => $name,
    ]);
}
if ($role === 'dispatcher') {
    return redirect()->route('login.success')->with([
        'role' => 'dispatcher',
        'name' => $name,
    ]);
}



    // ❌ Unexpected role → logout and return to login
    Auth::logout();
    return redirect()->route('/login')->with('error', 'Unauthorized role access.');
}

    /**
     * Resend OTP (rate-limited)
     */
    public function resendOtp(Request $request)
{
    $userId = Session::get('otp_user_id');
    if (!$userId) {
        return redirect('/login')->with('error', 'Session expired. Please login again.');
    }

    $resendKey = $this->resendThrottleKey($userId, $request);
    if (RateLimiter::tooManyAttempts($resendKey, self::RESEND_MAX_ATTEMPTS)) {
        $seconds = RateLimiter::availableIn($resendKey);
        return back()->with('error', "⏳ Too many OTP requests. Try again in {$seconds} seconds.");
    }

    $user = User::find($userId);
    if (!$user) {
        Session::forget('otp_user_id');
        return redirect('/login')->with('error', 'Session expired. Please login again.');
    }

    // Invalidate old OTPs
    Otp::where('user_id', $userId)->where('is_used', false)->update(['is_used' => true]);

    // New OTP
    $otp = random_int(100000, 999999);

    Otp::create([
        'user_id'    => $user->id,
        'otp_code'   => $otp,
        'expires_at' => Carbon::now()->addMinutes(self::OTP_TTL_MINUTES),
        'is_used'    => false,
    ]);

    // Send OTP email
    $this->sendOtpEmail($user->email, $otp);

    RateLimiter::hit($resendKey, self::RESEND_DECAY_SECONDS);

    return back()->with('status', '📩 A new OTP has been sent to your email.');
}

    /**
     * Send OTP using PHPMailer (best-effort; errors logged only)
     */
    private function sendOtpEmail(string $email, int $otp): void
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME');
        $mail->Password   = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
        $mail->Port       = (int) env('MAIL_PORT', 587);

        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Bulan Transport Cooperative'));
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'OTP Code for Login';

        // Render Blade template as email body
        $ttl = self::OTP_TTL_MINUTES;
        $htmlBody = view('emails.otp', [
            'otp' => $otp,
            'ttl' => $ttl
        ])->render();

        $mail->Body = $htmlBody;

        $mail->send();
    } catch (MailException $e) {
        \Log::error('PHPMailer Error (OTP send): ' . $e->getMessage());
    }
}

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('status', 'Logged out successfully.');
    }

    // ----------------- Helpers -----------------

    private function loginThrottleKey(Request $request): string
    {
        return 'login:' . Str::lower($request->input('email')) . '|' . $request->ip();
        // You can also use user agent if needed: . '|' . substr($request->userAgent(), 0, 120)
    }

    private function otpThrottleKey(int $userId, Request $request): string
    {
        return "otp_verify:{$userId}|{$request->ip()}";
    }

    private function resendThrottleKey(int $userId, Request $request): string
    {
        return "otp_resend:{$userId}|{$request->ip()}";
    }
}
