<?php

namespace App\Jobs\Bot;

use App\Currency\Currency;
use App\Game;
use App\Games\Kernel\Multiplayer\MultiplayerGame;
use App\Games\Roulette;
use App\Settings;
use App\User;
use Faker\Factory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class BotNewAccount implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $games;
    private $username;
    private $iFromMs;
    private $iToMs;

    public function __construct($games, $iFromMs, $iToMs) {
        $createUsername = function() use(&$createUsername) {
            $faker = Factory::create();
            $username = $faker->userName;
            if(User::where('name', $username)->first() != null) return $createUsername();
            return str_replace('.', mt_rand(0, 2) === 1 ? '_' : '', $username);
        };

        $this->games = $games;
        $this->username = $createUsername();
        $this->iFromMs = $iFromMs;
        $this->iToMs = $iToMs;
    }

    public function handle() {
        $user = User::create([
            'name' => $this->username,
            'password' => Hash::make(uniqid()),
            'avatar' => '/avatar/' . uniqid(),
            'email' => null,
            'client_seed' => \App\Games\Kernel\ProvablyFair::generateServerSeed(),
            'access' => 'user',
            'name_history' => [['time' => \Carbon\Carbon::now(), 'name' => $this->username]],
            'register_ip' => null,
            'login_ip' => null,
            'bot' => true,
            'register_multiaccount_hash' => null,
            'login_multiaccount_hash' => null,
            'private_bets' => mt_rand(0, 100) <= floatval(Settings::get('hidden_bets_probability', 20, true)),
            'private_profile' => mt_rand(0, 100) <= floatval(Settings::get('hidden_profile_probability', 20, true))
        ]);

        $getGame = function() use(&$getGame) {
            $games = \App\Games\Kernel\Game::list();
            $gameInstance = $games[mt_rand(0, count($games) - 1)];
            if($gameInstance->isDisabled() || $gameInstance instanceof MultiplayerGame
                || $gameInstance instanceof Roulette) return $getGame();
            return $gameInstance;
        };

        $currencies = Currency::all();
        $currency = $currencies[mt_rand(0, count($currencies) - 1)];

        $gameInstance = $getGame();
        $interval = 0;
        for($game = 0; $game < $this->games; $game++) {
            $bet = $currency->getBotBet();
            $interval += mt_rand($this->iFromMs, $this->iToMs);
            dispatch((new BotPlayGame($bet, $currency->id(), $gameInstance, $user))->delay(now()->addMilliseconds($interval)));
        }
    }

}
