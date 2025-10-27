<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\PasswordSetConfirmation;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // FIXED: Hash the password with bcrypt
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->email_verified_at = now();
                $user->save();

                // Send confirmation email (only if mail is working)
                try {
                    Mail::to($user->email)->send(new PasswordSetConfirmation($user));
                } catch (\Exception $e) {
                    Log::error('Failed to send password confirmation email: ' . $e->getMessage());
                    // Continue anyway - password was set successfully
                }

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password has been set successfully! You can now login.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}