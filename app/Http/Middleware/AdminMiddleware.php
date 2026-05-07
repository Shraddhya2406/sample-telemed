<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (strtolower($user->role?->name ?? '') === 'admin') {
            return $next($request);
        }

        session()->flash('error', 'You are not authorized to access that page.');

        if (strtolower($user->role?->name ?? '') === 'patient') {
            return redirect()->route('dashboard.patient');
        }

        return redirect('/');
    }
}
