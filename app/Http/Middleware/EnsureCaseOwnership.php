<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cases;

class EnsureCaseOwnership
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
        $case = $request->route('case');
        
        if ($case && $case instanceof Cases) {
            // Check if the case belongs to the authenticated user
            if ($case->user_id !== Auth::id()) {
                abort(403, 'You do not have permission to access this case.');
            }
        }

        return $next($request);
    }
}