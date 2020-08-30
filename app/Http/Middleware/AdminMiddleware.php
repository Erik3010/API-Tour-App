<?php

namespace App\Http\Middleware;

use Closure;

use App\User;

class AdminMiddleware
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

        if($check->role !== 'ADMIN') return response()->json(['message' => 'unauthorized user'], 401);

        return $next($request);
    }
}
