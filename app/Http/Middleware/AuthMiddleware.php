<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class AuthMiddleware
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
        $check = User::where('token', $request->token)->first();
        if(!$check) return response()->json(['message' => 'unauthorized user'], 401);

        return $next($request);
    }
}
