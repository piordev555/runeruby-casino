<?php

namespace App\Jobs\Bot;

use App\Currency\Currency;
use App\Games\Kernel\Data;
use App\Games\Kernel\Extended\ExtendedGame;
use App\Games\Kernel\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BotPlayGame implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $game;
    private $bet;
    private $currency;

    public function __construct($bet, $currency, $game, $user) {
        if(!$user->bot) throw new \Exception('This user is not a bot!');
        $this->user = $user;
        $this->game = $game;
        $this->bet = $bet;
        $this->currency = $currency;
    }

    public function handle() {
        $dataArray = $this->game->getBotData();

        $data = new Data($this->user, [
            'api_id' => $this->game->metadata()->id(),
            'bet' => $this->bet,
            'currency' => $this->currency,
            'demo' => false,
            'quick' => false,
            'data' => $dataArray
        ]);

        auth()->login($this->user);

        $result = $this->game->process($data);

        if($this->game instanceof ExtendedGame) {
            $dbGame = \App\Game::where('_id', $result['response']['id'])->first();

            $this->game->setTurn($dbGame, 1);

            $turns = mt_rand(0, 10);
            for($turnId = 1; $turnId <= $turns; $turnId++) {
                $turnResult = $this->game->turn($dbGame, $this->game->getBotTurnData($turnId));

                if($turnResult->type() === 'continue' && $turnId === $turns) $this->game->finish($dbGame);
            }
        }
    }

}
