<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class AverageResponseTime {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        Cache::remember('average_response_time', now()->addSeconds(10), function() {
            return microtime(true) - LARAVEL_START;
        });
        return $next($request);
    }

}
