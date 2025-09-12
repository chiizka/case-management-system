<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class UserController extends Controller {
    public function store(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        // Create user with null password
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'password' => null,
        ]);

        Log::info('User created: ' . $user->email); // Debug log

        // Send password reset notification
        $status = Password::sendResetLink(['email' => $user->email]);

        Log::info('Password reset status: ' . $status); // Debug log

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'User created successfully! Password setup email sent to ' . $user->email);
        }

        return back()->with('error', 'User created but failed to send password setup email. Status: ' . $status);
    }

    public function login(Request $request)
    { 
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user needs to set password
        if ($user && is_null($user->password)) {
            return back()->withErrors(['email' => 'Please check your email to set up your password first.']);
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}