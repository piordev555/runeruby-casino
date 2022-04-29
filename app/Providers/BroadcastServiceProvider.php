<?php

namespace App\Providers;

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Route::post('/broadcasting/auth', function() {
            $user = auth('sanctum')->guest() ? new GenericUser(['_id' => microtime()]) : auth('sanctum')->user();

            request()->setUserResolver(function() use ($user) {
                return $user;
            });

            return Broadcast::auth(request());
        });

        require base_path('routes/channels.php');
    }
}
