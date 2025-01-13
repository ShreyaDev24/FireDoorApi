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
            // Authenticate the user with the token
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            try {
                // Attempt to refresh the token
                $token = JWTAuth::refresh();
                JWTAuth::setToken($token)->toUser();

                // Set the refreshed token in the response headers
                return response()->json([
                    'message' => 'Token refreshed',
                    'token' => $token
                ]);
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                // If refresh fails, respond with an error
                return response()->json(['error' => 'Token has expired and cannot be refreshed'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                // Catch other JWT-related exceptions
                return response()->json(['error' => 'Token is invalid'], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token is invalid or missing'], 401);
        }

        return $next($request);
    }

}
