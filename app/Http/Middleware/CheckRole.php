<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Simple debug logging
        Log::info('CheckRole Middleware Debug:', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role ?? 'null',
            'required_roles' => $roles
        ]);
        
        // Get fresh user data from database
        $freshUser = \App\Models\User::find($user->id);
        
        if ($freshUser) {
            Log::info('Fresh User Role: ' . ($freshUser->role ?? 'null'));
        }
        
        // Use fresh user role
        $userRole = $freshUser ? $freshUser->role : $user->role;
        
        if (!$userRole) {
            Log::error('User role is null', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
            abort(403, 'User role not defined. Please contact administrator.');
        }
        
        // Check if user role is in allowed roles
        if (!in_array($userRole, $roles)) {
            Log::warning('Access denied', [
                'user_role' => $userRole,
                'required_roles' => $roles
            ]);
            
            abort(403, 'Unauthorized. You do not have permission to access this resource.');
        }

        Log::info('Role check passed: ' . $userRole);

        return $next($request);
    }
}