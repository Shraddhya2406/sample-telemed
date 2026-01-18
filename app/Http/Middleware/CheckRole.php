<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!in_array($request->user()->role->name, $roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}