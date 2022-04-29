<?php namespace App\Http\Middleware;

use Closure;

class BanCheck {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if(!auth('sanctum')->guest() && auth('sanctum')->user()->ban) return response()->view('errors.ban');
        return $next($request);
    }

}
