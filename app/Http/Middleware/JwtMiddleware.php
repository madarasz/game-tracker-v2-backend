<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
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
        unset($request['user']); // not accepting user paremeter, getting rid of it
        $authproblem = null;
        $token = $request->header('Authorization');
        $token = substr($token, 7-strlen($token));
        
        // Unauthorized response if token not there
        if(!$token) {
            $authproblem = 'Token not provided.';
        } else { 
            try {
                // Try decoding JWT token
                $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
                $user = User::find($credentials->sub);
                // Putting user in the request
                $request->merge(['user' => $user]);
            } catch(ExpiredException $e) {
                $authproblem = 'Provided token is expired.';
            } catch(Exception $e) {
                $authproblem = 'An error while decoding token.';
            }
        }

        if (!is_null($authproblem)) {
            $request->merge(['authproblem' => $authproblem]);
        }

        $response = $next($request);
        return $response;
    }
}
