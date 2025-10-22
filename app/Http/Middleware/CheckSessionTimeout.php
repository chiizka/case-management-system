<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');
            $sessionLifetime = config('session.lifetime') * 60; // Convert minutes to seconds
            
            // Check if session has expired
            if ($lastActivity && (now()->timestamp - $lastActivity) > $sessionLifetime) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your session has expired due to inactivity. Please login again.');
            }
            
            // Update last activity timestamp
            session(['last_activity' => now()->timestamp]);
        }
        
        return $next($request);
    }
}