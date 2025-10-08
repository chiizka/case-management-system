<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
   public function store(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'fname' => 'required|string|max:255',
        'lname' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Create user with null password
    $user = User::create([
        'fname' => $request->fname,
        'lname' => $request->lname,
        'email' => $request->email,
        'password' => null,
        'two_factor_enabled' => true,
    ]);

    // Send password reset notification
    $status = Password::sendResetLink(['email' => $user->email]);

    if ($status === Password::RESET_LINK_SENT) {
        // Update the timestamp
        $user->update(['password_reset_sent_at' => now()]);
        return back()->with('success', 'User created successfully! Password setup email sent.');
    }

    return back()->with('error', 'User created but failed to send password setup email.');
}

public function resetPassword($id)
{
    // Find the user
    $user = User::findOrFail($id);

    // Send password reset link
    $status = Password::sendResetLink(['email' => $user->email]);

    if ($status === Password::RESET_LINK_SENT) {
        // Update the timestamp
        $user->update(['password_reset_sent_at' => now()]);
        return back()->with('success', 'Password reset link sent to ' . $user->email);
    }

    return back()->with('error', 'Failed to send password reset link.');
}
}