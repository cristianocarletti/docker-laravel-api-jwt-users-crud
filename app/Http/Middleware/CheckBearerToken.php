<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBearerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    // App\Http\Middleware\CheckBearerToken.php

    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token ausente ou inválido'
            ], 401);
        }

        return $next($request);
    }
}
