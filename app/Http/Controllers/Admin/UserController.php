<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'role' => 'required|in:admin,user,province,malsu,case_management',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user with null password (no hashing needed for null)
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'role' => $request->role,
            'password' => null, // This stays null
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
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user,province,malsu,case_management',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update user
        $user->update([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        return back()->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if (Auth::check() && Auth::id() == $user->id) {
            return back()->with('error', 'You cannot delete your own account!');
        }
        
        $userName = $user->fname . ' ' . $user->lname;
        $user->delete();
        
        return back()->with('success', 'User "' . $userName . '" deleted successfully!');
    }
}