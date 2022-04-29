<?php namespace App\Games;

use App\Currency\Currency;
use App\Events\MultiplayerTimerStart;
use App\Games\Kernel\Data;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Multiplayer\MultiplayerGame;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Jobs\MultiplayerDisableBetAccepting;
use App\Jobs\MultiplayerFinishAndSetupNextGame;
use App\Transaction;
use App\User;

class Crash extends MultiplayerGame {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'crash';
            }

            function name(): string {
                return 'Crash';
            }

            function icon(): string {
                return 'crash';
            }

            public function category(): array {
                return [GameCategory::$originals];
            }
        };
    }

    public function startChain() {
        dispatch(new MultiplayerFinishAndSetupNextGame($this, ['multiplier' => 1], now()->addSeconds(6)));
    }

    public function nextGame() {
        $this->state()->resetPlayers();
        $this->state()->clientSeed(ProvablyFair::generateServerSeed());
        $this->state()->serverSeed(ProvablyFair::generateServerSeed());
        $this->state()->nonce(now()->timestamp);

        $multiplier = (new ProvablyFair($this))->result()->result()[0];
        if($multiplier < 1) $multiplier = 1.1;

        $timeInMilliseconds = 0;
        $simulation = 1; $suS =  0;

        while($simulation < $multiplier) {
            $simulation += 0.05 / 15 + $suS;
            $timeInMilliseconds += 2000 / 15 / 3;
            if($simulation >= 5.5) {
                $suS += 0.05 / 15;
                $timeInMilliseconds += 4000 / 15 / 3;
            }
        }

        $this->state()->betting(true);
        $this->state()->timestamp(now()->addSeconds(6)->timestamp);

        dispatch((new MultiplayerDisableBetAccepting($this))->delay(now()->addSeconds(6)));

        event(new MultiplayerTimerStart($this));

        dispatch((new MultiplayerFinishAndSetupNextGame($this, ['multiplier' => $multiplier], now()->addSeconds(6)))
            ->delay(now()->addMilliseconds($timeInMilliseconds + 6000)));
    }

    public function onDispatchedFinish() {
        $this->state()->history([
            'server_seed' => $this->server_seed(),
            'client_seed' => $this->client_seed(),
            'nonce' => $this->nonce(),
            'multiplier' => $this->getCurrentMultiplier()
        ]);

        foreach($this->getActiveGames() as $game) {
            $game->update(['status' => 'lose']);
            event(new \App\Events\LiveFeedGame($game, 0));
        }
    }

    protected function allowCancellation(): bool {
        return true;
    }

    protected function canBeFinished(): bool {
        return true;
    }

    protected function handleCancellation(\App\Game $game) {
        if($game->server_seed !== $this->state()->serverSeed()) return ['error' => [1, 'This Crash game is invalid']];

        $multiplier = $this->getCurrentMultiplier();
        if($multiplier > 1000) $multiplier = 1;

        $game->update([
            'status' => 'win',
            'profit' => $game->wager * $multiplier,
            'multiplier' => $multiplier
        ]);
        User::where('_id', $game->user)->first()->balance(Currency::find($game->currency))->demo($game->demo)
            ->add($game->profit, Transaction::builder()->message('Crash (Take)')->game($this->metadata()->id())->get());
        event(new \App\Events\LiveFeedGame($game, 0));
        event(new \App\Events\MultiplayerBetCancellation($game, User::where('_id', $game->user)->first()));
    }

    private function getCurrentMultiplier() {
        $start_timestamp = $this->state()->timestamp();
        if($start_timestamp < 0) return 1;

        $diffS = now()->timestamp - $start_timestamp;
        $timeInMilliseconds = 0;
        $simulation = 1; $suS =  0;

        while(true) {
            $simulation += 0.05 / 15 + $suS;
            $timeInMilliseconds += 2000 / 15 / 3;
            if($simulation >= 5.5) {
                $suS += 0.05 / 15;
                $timeInMilliseconds += 4000 / 15 / 3;
            }
            if($timeInMilliseconds >= ($diffS * 1000) || $simulation > 1000) {
                if($simulation > 1000) $simulation = 1;
                break;
            }
        }

        return HouseEdgeModule::apply($this, $simulation);
    }

    public function isLoss(ProvablyFairResult $result, \App\Game $game, array $turnData): bool {
        return $result->result()[0] <= 1.1;
    }

    function result(ProvablyFairResult $result): array {
        $max_multiplier = 1000; $house_edge = HouseEdgeModule::get($this, 0.99);
        $float_point = $max_multiplier / ($result->extractFloat() * $max_multiplier) * $house_edge;
        return [floor($float_point * 100) / 100];
    }

}
