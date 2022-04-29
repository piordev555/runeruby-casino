<?php namespace App\Http\Middleware;

use Closure;

class Api2FAMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if(!auth('sanctum')->guest() && !auth('sanctum')->user()->validate2FA(true)
            && !$request->is('api/user/2fa_validate')
            && !$request->is('auth/token')) return \App\Utils\APIResponse::invalid2FASession();
        return $next($request);
    }

}
