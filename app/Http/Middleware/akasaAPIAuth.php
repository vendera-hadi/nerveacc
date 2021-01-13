<?php

namespace App\Http\Middleware;

use Closure;

class akasaAPIAuth
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
        $token = 'RV8wf3mAgkhbnGfh';
        $token_tokped = 'VJ3R2tEoBlQloFqv';
        if(!$request->has('key') || @$request->key != $token){
            return response()->json([
                            'resp_code' => 401,
                            'resp_status' => 'error',
                            'resp_message' => 'Token is required'
                        ],401);
        }
        return $next($request);
    }
}
