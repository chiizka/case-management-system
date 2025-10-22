<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        // DEBUG: Log that middleware is running
        Log::info('CheckSessionTimeout middleware is running');
        
        if (Auth::check()) {
            Log::info('User is authenticated', ['user_id' => Auth::id()]);
            
            $lastActivity = session('last_activity');
            $sessionLifetime = config('session.lifetime') * 60;
            
            Log::info('Session data', [
                'last_activity' => $lastActivity,
                'current_time' => now()->timestamp,
                'lifetime' => $sessionLifetime,
                'session_expire_on_close' => config('session.expire_on_close')
            ]);
            
            if ($lastActivity && (now()->timestamp - $lastActivity) > $sessionLifetime) {
                Log::info('Session expired - logging out');
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your session has expired due to inactivity. Please login again.');
            }
            
            session(['last_activity' => now()->timestamp]);
        }
        
        return $next($request);
    }
}