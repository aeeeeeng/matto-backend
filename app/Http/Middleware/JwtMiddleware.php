<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

use Response;


class JwtMiddleware
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
        } catch (Exception $e) {
            $responseJson = [];
            if ($e instanceof TokenInvalidException){
                $responseJson = Response::error($e->getMessage());
            } else if ($e instanceof TokenExpiredException) {
                $responseJson = Response::error($e->getMessage());
            } else{
                $responseJson = Response::error('Authorization Token not found');
            }
            return response()->json($responseJson, 401);
        }
        return $next($request);
    }
}
