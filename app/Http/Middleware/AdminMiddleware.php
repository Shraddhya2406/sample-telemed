<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Allow only users with admin role. Assumes users have role_id or role relationship.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return Redirect::route('login');
        }

        // If role relationship exists and role has a 'name' field
        if (method_exists($user, 'role') && $user->role && 
            (isset($user->role->name) ? strtolower($user->role->name) === 'admin' : false)) {
            return $next($request);
        }

        // If role_id is present and admin role id is 1 (common), allow
        if (isset($user->role_id) && (int) $user->role_id === 1) {
            return $next($request);
        }

        // not admin
        session()->flash('error', 'You are not authorized to access that page.');

        // Redirect to a sensible dashboard route if exists
        if ($user && method_exists($user, 'role') && strtolower($user->role->name ?? '') === 'patient') {
            return Redirect::route('dashboard.patient');
        }

        return Redirect::route('dashboard.patient');
    }
}
