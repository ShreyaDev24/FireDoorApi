<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
    $user = JWTAuth::parseToken()->authenticate();
} catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    try {
        $token = JWTAuth::refresh(); // Try to refresh the token
        JWTAuth::setToken($token)->toUser();
        // Return the new token
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['error' => 'Token has expired and cannot be refreshed'], 401);
    }
}

        return $next($request);
    }
}