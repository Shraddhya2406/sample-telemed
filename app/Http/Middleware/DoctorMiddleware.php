<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DoctorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role?->name !== 'doctor') {
            abort(403, 'Doctor access only.');
        }

        return $next($request);
    }
}
