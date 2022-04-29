<?php namespace App\Games\Kernel\Multiplayer;

use App\Currency\Currency;
use App\Events\BalanceModification;
use App\Events\LiveFeedGame;
use App\Events\MultiplayerGameBet;
use App\Game;
use App\Games\Kernel\Data;
use App\Games\Kernel\Extended\ContinueGame;
use App\Games\Kernel\Extended\ExtendedGame;
use App\Games\Kernel\Extended\Turn;
use App\Games\Kernel\ProvablyFairResult;
use App\Leaderboard;
use App\Transaction;
use App\User;

abstract class MultiplayerGame extends ExtendedGame {

    public abstract function nextGame();
    public abstract function onDispatchedFinish();
    public abstract function startChain();

    protected function getPlayerData(Game $game): array {
        return [];
    }

    public function state(): MultiplayerGameStateBuilder {
        return new MultiplayerGameStateBuilder($this);
    }

    public function getActiveGames() {
        return Game::where('game', $this->metadata()->id())->where('status', 'in-progress')
            ->where('server_seed', $this->state()->serverSeed())
            ->where('client_seed', $this->state()->clientSeed())->get();
    }

    public function start(Game $game) {
        $data = $this->getPlayerData($game);
        $user = User::where('_id', $game->user)->first();
        $this->state()->players([
            'user' => $user->toArray(),
            'game' => $game->toArray(),
            'data' => $this->getPlayerData($game)
        ]);
        event(new MultiplayerGameBet($user, $game, $data));
    }

    public function turn(Game $game, array $turnData): Turn {
        return new ContinueGame($game, []);
    }

    protected function win(Game $game, float $multiplier, int $delay) {
        $game->update([
            'status' => $multiplier == 0 ? 'lose' : 'win',
            'profit' => $game->wager * $multiplier,
            'multiplier' => $multiplier
        ]);

        event(new LiveFeedGame($game, $delay));

        if($multiplier > 0) {
            $user = User::where('_id', $game->user)->first();
            $currency = Currency::find($game->currency);
            $user->balance($currency)->demo($game->demo)->quiet(true)
                ->add($game->profit, Transaction::builder()->message($this->metadata()->name())->game($this->metadata()->id())->get());
            event(new BalanceModification($user, $currency, 'add', $game->demo, $game->profit, $delay));
        }

        Leaderboard::insert($game);
    }

    protected function acceptBet(Data $data) {
        return $this->state()->betting();
    }

    protected function canBeFinished(): bool {
        return false;
    }

    protected function allowCancellation(): bool {
        return false;
    }

    protected function acceptsDemo() {
        return false;
    }

    public function ignoresMultipleClientTabs() {
        return true;
    }

    public function isLoss(ProvablyFairResult $result, \App\Game $game, array $turnData): bool {
        return true;
    }

    public function client_seed() {
        return $this->state()->clientSeed();
    }

    public function server_seed() {
        return $this->state()->serverSeed();
    }

    public function nonce() {
        return $this->state()->nonce();
    }

    public function data(): array {
        return $this->state()->toArray();
    }

}
