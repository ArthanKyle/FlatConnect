<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ClientMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user() instanceof \App\Models\Client) {
            return redirect()->route('login'); // Redirect unauthorized users
        }

        return $next($request);
    }
}
