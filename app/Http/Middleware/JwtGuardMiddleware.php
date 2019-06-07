<?php

namespace App\Http\Middleware;

use Closure;

class JwtGuardMiddleware
{
    /**
     * Only let JWT authenticated users in
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // handle auth problems given by JwtMiddleware
        if ($request->has('authproblem')) {
            return response()->json([
                'error' => $request->authproblem
            ], 400);
        }

        $response = $next($request);
        return $response;
    }
}
