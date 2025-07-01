<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // Log user info to verify if the user is being authenticated
            \Log::info('Authenticated user:', [Auth::user()]);
            
            // Check if the username is 'admin'
            if (Auth::user()->username === 'admin') {
                return $next($request);
            }
        }

        // Redirect if not an admin
        abort(403, 'Unauthorized access');
    }
}
