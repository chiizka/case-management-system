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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user with null password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => null, // No password initially
            'two_factor_enabled' => false, // Default to false
        ]);

        // Send password reset notification
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'User created successfully! Password setup email sent.');
        }

        return back()->with('error', 'User created but failed to send password setup email.');
    }
}