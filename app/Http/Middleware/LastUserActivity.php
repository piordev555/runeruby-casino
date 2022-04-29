<?php

namespace App\Http\Middleware;

use App\ActivityLog\ActivityLogEntry;
use Closure;
use Illuminate\Support\Facades\Cache;

class LastUserActivity
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
        if(!auth('sanctum')->guest()) {
            auth('sanctum')->user()->update([
                'latest_activity' => now()
            ]);
        }

        return $next($request);
    }
}
