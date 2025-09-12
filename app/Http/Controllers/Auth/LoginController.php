<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user needs to set password
        if ($user && $user->needsPasswordSetup()) {
            return back()->withErrors(['email' => 'Please check your email to set up your password first.']);
        }

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $user = Auth::user();

            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                // Store user ID in session and logout temporarily
                session(['2fa_user_id' => $user->id]);
                Auth::logout();

                // Send 2FA code
                $this->send2FACode($user);

                return redirect()->route('2fa.verify')->with('message', '2FA code sent to your email.');
            }

            return redirect()->intended('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    private function send2FACode(User $user)
    {
        $code = rand(100000, 999999);
        session(['2fa_code' => $code, '2fa_expires' => now()->addMinutes(10)]);

        // Send email with 2FA code
        Mail::raw("Your 2FA code is: {$code}", function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('Your 2FA Code');
        });
    }
}