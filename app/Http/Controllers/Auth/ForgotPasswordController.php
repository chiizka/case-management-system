<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user.
        // Once we have attempted to send the link, we will examine the response
        // and see the message we need to show to the user.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Password reset link sent', ['email' => $request->email]);
            return back()->with('status', __($status));
        }

        // If an error was returned by the password broker, we will get this response
        // back to the user with their email address so they can attempt again.
        Log::warning('Password reset link failed', [
            'email' => $request->email,
            'status' => $status,
        ]);

        return back()->withErrors(['email' => __($status)]);
    }
}