<?php namespace App\Games;

use App\Events\MultiplayerTimerStart;
use App\Games\Kernel\Data;
use App\Games\Kernel\Game;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Multiplayer\MultiplayerGame;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Jobs\MultiplayerDisableBetAccepting;
use App\Jobs\MultiplayerFinishAndSetupNextGame;
use App\Jobs\MultiplayerUpdateData;
use App\Jobs\MultiplayerUpdateTimestamp;
use Illuminate\Support\Facades\Log;

class Slide extends MultiplayerGame {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return "slide";
            }

            function name(): string {
                return "Slide";
            }

            function icon(): string {
                return "fas fa-rectangle-portrait";
            }

            public function category(): array {
                return [GameCategory::$originals];
            }
        };
    }

    protected function getPlayerData(\App\Game $game): array {
        return ['target' => $this->userData($game)['data']['target']];
    }

    public function nextGame() {
        $this->state()->resetPlayers();
        $this->state()->clientSeed(ProvablyFair::generateServerSeed());
        $this->state()->serverSeed(ProvablyFair::generateServerSeed());
        $this->state()->nonce(now()->timestamp);
        $this->state()->timestamp(now()->timestamp);


        $slides = [];
        $index = mt_rand(0, 20);
        $result = (new ProvablyFair($this, $this->server_seed()))->result()->result()[0];

        for($i = 0; $i < 25 - $index; $i++) array_push($slides, (new ProvablyFair($this, $this->server_seed().'_'.$i))->result()->result()[0]);
        array_push($slides, $result);
        for($i = 25 - $index; $i < 25; $i++) array_push($slides, (new ProvablyFair($this, $this->server_seed().'_'.$i))->result()->result()[0]);

        $this->state()->betting(true);

        $data = [
            'slides' => $slides,
            'index' => 25 - $index,
            '_result' => $result
        ];

        dispatch((new MultiplayerDisableBetAccepting($this))->delay(now()->addSeconds(6)));
        dispatch((new MultiplayerUpdateData($this, $data))->delay(now()->addSeconds(6)));
        dispatch((new MultiplayerUpdateTimestamp($this, -1))->delay(now()->addSeconds(6)));

        event(new MultiplayerTimerStart($this));

        dispatch((new MultiplayerFinishAndSetupNextGame($this, $data, now()->addSeconds(12)))->delay(6));
    }

    public function onDispatchedFinish() {
        $current = (new ProvablyFair($this, $this->server_seed()))->result()->result()[0];

        $this->state()->history([
            'server_seed' => $this->server_seed(),
            'client_seed' => $this->client_seed(),
            'nonce' => $this->nonce(),
            'multiplier' => $current
        ]);

        foreach($this->getActiveGames() as $game) {
            $target = $this->userData($game)['data']['target'];
            $multiplier = 0;

            if($target < $current) $multiplier = $target;

            $this->win($game, $multiplier, 6000);
        }
    }

    public function startChain() {
        dispatch(new MultiplayerFinishAndSetupNextGame($this, [
            'slides' => [], 'index' => 0
        ], now()));
    }

    function result(ProvablyFairResult $result): array {
        $max_multiplier = 1000; $house_edge = HouseEdgeModule::get($this, 0.99);
        $float_point = $max_multiplier / ($result->extractFloat() * $max_multiplier) * $house_edge;
        return [floor($float_point * 100) / 100];
    }

}
