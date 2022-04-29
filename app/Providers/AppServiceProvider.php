<?php

namespace App\Providers;

use App\Settings;
use App\Token\PersonalAccessToken;
use App\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Torann\GeoIP\Facades\GeoIP;

class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot() {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

}
