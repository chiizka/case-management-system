<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        Log::info('=== LOGIN ATTEMPT START ===');
        Log::info('Email: ' . $request->email);
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning('Login failed: User not found', ['email' => $request->email]);
            return back()->withErrors(['email' => 'No account found with this email address.']);
        }

        if ($user->needsPasswordSetup()) {
            Log::warning('Login failed: Password setup needed', ['user_id' => $user->id]);
            return back()->withErrors(['email' => 'Please check your email to set up your password first.']);
        }

        // Delete ALL existing sessions for this user
        Log::info('Checking for existing sessions', ['user_id' => $user->id]);
        
        if (config('session.driver') === 'database') {
            $existingSessions = DB::table('sessions')->where('user_id', $user->id)->count();
            Log::info('Found existing database sessions', ['count' => $existingSessions]);
            
            $deleted = DB::table('sessions')->where('user_id', $user->id)->delete();
            Log::info('Deleted existing sessions', ['deleted_count' => $deleted]);
        }

        // Attempt authentication
        if (Auth::attempt($request->only('email', 'password'), false)) {  // Always false = no remember me
            Log::info('✓ Authentication successful', ['user_id' => $user->id]);
            
            $user = Auth::user();

            if ($user->two_factor_enabled) {
                Log::info('2FA enabled - sending code', ['user_id' => $user->id]);
                
                session(['2fa_user_id' => $user->id]);
                session(['2fa_in_progress' => true]);
                
                Auth::logout();
                session()->forget('2fa_in_progress');

                $otpCode = $this->generateOTPForUser($user);
                $this->send2FACode($user, $otpCode);

                return redirect()->route('2fa.verify')->with('success', 'A 6-digit verification code has been sent to your email.');
            }

            // Regenerate session
            $request->session()->regenerate();
            
            // Set initial last activity
            session(['last_activity' => now()->timestamp]);
            
            Log::info('✓ Login successful', [
                'user_id' => $user->id,
                'session_id' => session()->getId(),
                'session_driver' => config('session.driver'),
                'last_activity' => now()->timestamp,
            ]);
            
            Log::info('=== LOGIN ATTEMPT END - SUCCESS ===');
            
            return redirect()->intended('/');
        }

        Log::warning('✗ Authentication failed - Invalid credentials', ['email' => $request->email]);
        Log::info('=== LOGIN ATTEMPT END - FAILED ===');
        
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
        Log::info('=== 2FA VERIFICATION START ===');
        
        $request->validate([
            'otp_code' => 'required|digits:6',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            Log::warning('2FA failed: No user_id in session');
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            Log::warning('2FA failed: User not found', ['user_id' => $userId]);
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        if ($this->verifyOTPForUser($user, $request->otp_code)) {
            Log::info('✓ 2FA verification successful', ['user_id' => $user->id]);
            
            session()->forget('2fa_user_id');
            
            // Delete existing sessions
            if (config('session.driver') === 'database') {
                $deleted = DB::table('sessions')->where('user_id', $user->id)->delete();
                Log::info('Deleted existing sessions after 2FA', ['count' => $deleted]);
            }
            
            session(['skip_login_log' => true]);
            Auth::login($user, false);  // FIXED: Changed from true to false
            session()->forget('skip_login_log');
            
            $request->session()->regenerate();
            session(['last_activity' => now()->timestamp]);
            
            Log::info('✓ 2FA login complete', [
                'user_id' => $user->id,
                'session_id' => session()->getId(),
            ]);
            Log::info('=== 2FA VERIFICATION END - SUCCESS ===');
            
            return redirect()->intended('/')->with('success', 'Login successful!');
        }

        if ($this->isOTPExpiredForUser($user)) {
            Log::warning('2FA failed: OTP expired', ['user_id' => $user->id]);
            return back()->withErrors(['otp_code' => 'Verification code has expired. Please request a new one.'])
                        ->with('show_resend', true);
        }

        Log::warning('2FA failed: Invalid OTP', ['user_id' => $user->id]);
        Log::info('=== 2FA VERIFICATION END - FAILED ===');
        
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

        Log::info('2FA code resent', ['user_id' => $user->id]);

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
        $userId = Auth::id();
        
        Log::info('=== LOGOUT START ===', ['user_id' => $userId]);
        
        // Delete from database if using database driver
        if (config('session.driver') === 'database' && $userId) {
            $deleted = DB::table('sessions')->where('user_id', $userId)->delete();
            Log::info('Deleted sessions on logout', ['count' => $deleted]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('✓ Logout successful');
        Log::info('=== LOGOUT END ===');
        
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}