<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Accepts one or more roles, e.g. 'role:manager' or 'role:manager,admin'.
     *
     * The variadic parameter matters: PHP silently discards extra arguments passed to a
     * single-parameter method, so 'role:manager,admin' previously behaved as 'role:manager'
     * and the second role was ignored.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        // Allow admins to access any role-protected route
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check if the user holds any of the required roles
        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}