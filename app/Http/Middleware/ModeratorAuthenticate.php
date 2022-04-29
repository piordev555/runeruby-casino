<?php

namespace App\Http\Middleware;

use Closure;

class ModeratorAuthenticate {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if(!auth('sanctum')->guest()) if(auth('sanctum')->user()->access === 'admin' || auth('sanctum')->user()->access === 'moderator') return $next($request);
        return redirect('/');
    }

}
