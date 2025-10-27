<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
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

        // Create user with null password
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'role' => $request->role,
            'password' => null,
            'two_factor_enabled' => true,
        ]);

        // Log user creation
        ActivityLogger::logAction(
            action: 'CREATE',
            resourceType: 'User',
            resourceId: $user->id,
            description: "Created user account for {$user->fname} {$user->lname} ({$user->email})",
            metadata: [
                'email' => $user->email,
                'role' => $user->role,
                'full_name' => "{$user->fname} {$user->lname}"
            ]
        );

        // Send password reset notification
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            // Update the timestamp
            $user->update(['password_reset_sent_at' => now()]);
            
            // Log password reset sent
            ActivityLogger::logAction(
                action: 'UPDATE',
                resourceType: 'User',
                resourceId: $user->id,
                description: "Sent password reset link to {$user->fname} {$user->lname}",
                metadata: [
                    'email' => $user->email,
                    'action_type' => 'password_reset_sent'
                ]
            );
            
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
            
            // Log password reset sent
            ActivityLogger::logAction(
                action: 'UPDATE',
                resourceType: 'User',
                resourceId: $user->id,
                description: "Sent password reset link to {$user->fname} {$user->lname}",
                metadata: [
                    'email' => $user->email,
                    'action_type' => 'password_reset_sent'
                ]
            );
            
            return back()->with('success', 'Password reset link sent to ' . $user->email);
        }

        return back()->with('error', 'Failed to send password reset link.');
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Store old values for comparison
        $oldData = [
            'fname' => $user->fname,
            'lname' => $user->lname,
            'email' => $user->email,
            'role' => $user->role,
        ];
        
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

        // Track changes for logging
        $changes = [];
        $changesText = [];
        
        if ($oldData['fname'] !== $request->fname) {
            $changes['first_name_old'] = $oldData['fname'];
            $changes['first_name_new'] = $request->fname;
            $changesText[] = "First Name: {$oldData['fname']} → {$request->fname}";
        }
        
        if ($oldData['lname'] !== $request->lname) {
            $changes['last_name_old'] = $oldData['lname'];
            $changes['last_name_new'] = $request->lname;
            $changesText[] = "Last Name: {$oldData['lname']} → {$request->lname}";
        }
        
        if ($oldData['email'] !== $request->email) {
            $changes['email_old'] = $oldData['email'];
            $changes['email_new'] = $request->email;
            $changesText[] = "Email: {$oldData['email']} → {$request->email}";
        }
        
        if ($oldData['role'] !== $request->role) {
            $changes['role_old'] = $oldData['role'];
            $changes['role_new'] = $request->role;
            $changesText[] = "Role: {$oldData['role']} → {$request->role}";
        }

        // Update user
        $user->update([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        // Log the update with changes
        $description = "Updated user account for {$request->fname} {$request->lname}";
        if (!empty($changesText)) {
            $description .= " - Changes: " . implode(', ', $changesText);
        }

        ActivityLogger::logAction(
            action: 'UPDATE',
            resourceType: 'User',
            resourceId: $user->id,
            description: $description,
            metadata: array_merge($changes, [
                'full_name' => "{$request->fname} {$request->lname}",
                'current_email' => $request->email,
            ])
        );

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
        $userEmail = $user->email;
        $userRole = $user->role;
        $userId = $user->id;
        
        // Log the deletion BEFORE deleting (so we still have user data)
        ActivityLogger::logAction(
            action: 'DELETE',
            resourceType: 'User',
            resourceId: $userId,
            description: "Deleted user account: {$userName} ({$userEmail})",
            metadata: [
                'deleted_user_name' => $userName,
                'deleted_user_email' => $userEmail,
                'deleted_user_role' => $userRole,
            ]
        );
        
        // Delete the user
        $user->delete();
        
        return back()->with('success', 'User "' . $userName . '" deleted successfully!');
    }
}