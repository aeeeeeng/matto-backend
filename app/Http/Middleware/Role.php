<?php

namespace App\Http\Middleware;
use Auth;
use Closure;
use Response;
use App\Models\User;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        foreach ($roles as $role) {
            if($user->hasRole($role)) {
                return $next($request);
            }
        }
        $responseJson = Response::success('You dont have role to acces this route');
        return response()->json($responseJson, 400);
    }
}
