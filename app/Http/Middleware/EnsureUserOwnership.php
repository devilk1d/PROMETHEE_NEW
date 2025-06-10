<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Alternative;
use App\Models\Decision;

class EnsureUserOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $routeName = $request->route()->getName();
        
        // Admin bisa akses semua
        if ($user->role === 'admin') {
            Log::info('Access granted: Admin privilege', ['route' => $routeName]);
            return $next($request);
        }
        
        // Cek kepemilikan berdasarkan route parameter
        $alternative = $request->route('alternative');
        $decision = $request->route('decision');
        
        Log::info('User ownership check:', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'route' => $routeName,
            'alternative_id' => $alternative ? $alternative->id : null,
            'decision_id' => $decision ? $decision->id : null
        ]);
        
        // **LOGIC UNTUK ALTERNATIVES:**
        if ($alternative && $alternative instanceof Alternative) {
            // User hanya bisa akses alternative mereka sendiri
            if ($alternative->user_id !== $user->id) {
                Log::warning('Access denied: Alternative not owned by user', [
                    'alternative_id' => $alternative->id,
                    'alternative_owner' => $alternative->user_id,
                    'current_user' => $user->id
                ]);
                abort(403, 'You do not have permission to access this alternative.');
            }
            
            Log::info('Access granted: Alternative owner', [
                'alternative_id' => $alternative->id,
                'user_id' => $user->id
            ]);
        }
        
        // **LOGIC UNTUK DECISIONS:**
        if ($decision && $decision instanceof Decision) {
            // User hanya bisa akses decision mereka sendiri
            if ($decision->user_id !== $user->id) {
                Log::warning('Access denied: Decision not owned by user', [
                    'decision_id' => $decision->id,
                    'decision_owner' => $decision->user_id,
                    'current_user' => $user->id
                ]);
                abort(403, 'You do not have permission to access this decision.');
            }
            
            Log::info('Access granted: Decision owner', [
                'decision_id' => $decision->id,
                'user_id' => $user->id
            ]);
        }
        
        // **ROUTES YANG PERLU FILTER BERDASARKAN USER:**
        $userSpecificRoutes = [
            'alternatives.index',       // Tampilkan hanya alternatives milik user
            'decisions.index',          // Tampilkan hanya decisions milik user
            'decisions.result'          // Tampilkan hanya hasil decisions milik user
        ];
        
        // Untuk route index, kita biarkan lewat tapi nanti di controller
        // akan di-filter berdasarkan user_id
        if (in_array($routeName, $userSpecificRoutes)) {
            Log::info('Access granted: User-specific route', [
                'route' => $routeName,
                'user_id' => $user->id
            ]);
        }

        return $next($request);
    }
}