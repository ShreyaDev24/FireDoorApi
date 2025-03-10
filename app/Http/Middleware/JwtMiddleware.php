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
            // Refresh the token
            $token = JWTAuth::refresh();
            $user = JWTAuth::setToken($token)->toUser(); // Retrieve the correct user

            // Return the new token to the client
            return response()->json([
                'message' => 'Token refreshed',
                'token' => $token,
                'user_id' => $user->id // Log the user ID
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired and cannot be refreshed'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        }
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['error' => 'Token is invalid or missing'], 401);
    }

    return $next($request);
}


}
