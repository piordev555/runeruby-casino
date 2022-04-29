<?php

namespace App\Jobs\Bot;

use App\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BotScheduler implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle() {
        $games = mt_rand(floatval(Settings::get('min_amount_of_games_from_one_bot', 20, true)), floatval(Settings::get('max_amount_of_games_from_one_bot', 50, true)));
        dispatch(new BotNewAccount($games, floatval(Settings::get('min_delay_between_games_from_one_bot_ms', 1000, true)), floatval(Settings::get('max_delay_between_games_from_one_bot_ms', 5000, true))));

        dispatch((new BotScheduler())->delay(now()->addMilliseconds(floatval(Settings::get('create_new_bot_every_ms', 20000, true)))));
    }

}
