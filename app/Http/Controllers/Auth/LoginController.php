<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.']);
        }

        // Check if user needs to set password
        if ($user->needsPasswordSetup()) {
            return back()->withErrors(['email' => 'Please check your email to set up your password first.']);
        }

        // Attempt authentication - this will fire the Login event
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $user = Auth::user();

            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                // Store user ID in session and logout temporarily
                session(['2fa_user_id' => $user->id]);
                session(['2fa_in_progress' => true]); // Flag to prevent logout logging
                
                Auth::logout();
                session()->forget('2fa_in_progress'); // Clear flag immediately

                // Generate and send 2FA code
                $otpCode = $this->generateOTPForUser($user);
                $this->send2FACode($user, $otpCode);

                return redirect()->route('2fa.verify')->with('success', 'A 6-digit verification code has been sent to your email.');
            }

            // If 2FA is disabled, login directly (already logged in via Auth::attempt)
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Invalid email or password.']);
    }

    public function show2FAForm()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login')->withErrors(['email' => 'Please login first.']);
        }

        return view('auth.2fa-verify');
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|digits:6',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        if ($this->verifyOTPForUser($user, $request->otp_code)) {
            session()->forget('2fa_user_id');
            
            // Login WITHOUT firing the event (we already logged it during Auth::attempt)
            session(['skip_login_log' => true]); // Flag to skip logging
            Auth::login($user, true);
            session()->forget('skip_login_log'); // Clear flag
            
            $request->session()->regenerate();
            
            return redirect()->intended('/')->with('success', 'Login successful!');
        }

        if ($this->isOTPExpiredForUser($user)) {
            return back()->withErrors(['otp_code' => 'Verification code has expired. Please request a new one.'])
                        ->with('show_resend', true);
        }

        return back()->withErrors(['otp_code' => 'Invalid verification code.']);
    }

    public function resend2FA()
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        $otpCode = $this->generateOTPForUser($user);
        $this->send2FACode($user, $otpCode);

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    private function verifyOTPForUser(User $user, $code)
    {
        if ($user->otp_code === $code && $user->otp_expires_at && $user->otp_expires_at->isFuture()) {
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();
            return true;
        }
        return false;
    }

    private function isOTPExpiredForUser(User $user)
    {
        return $user->otp_expires_at && $user->otp_expires_at->isPast();
    }

    private function generateOTPForUser(User $user)
    {
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();
        
        return $otpCode;
    }

    private function send2FACode(User $user, $code)
    {
        Mail::raw("Your login verification code is: {$code}\n\nThis code will expire in 10 minutes.\n\nIf you didn't attempt to login, please ignore this email.", function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('Login Verification Code - Case Management System');
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}