<?php namespace App\Games\Kernel\Multiplayer;

use App\Events\MultiplayerBettingStateChange;
use App\Events\MultiplayerDataUpdate;
use App\Games\Kernel\Game;
use App\Games\Kernel\ProvablyFair;
use App\MultiplayerGameState;
use Carbon\Carbon;

class MultiplayerGameStateBuilder {

    private Game $game;
    private MultiplayerGameState $state;

    public function __construct(Game $game) {
        $state = MultiplayerGameState::where('game', $game->metadata()->id())->first();
        if($state == null) $state = MultiplayerGameState::create([
            'game' => $game->metadata()->id(),
            'history' => [],
            'players' => [],
            'data' => [],
            'timestamp' => Carbon::minValue()->timestamp,
            'can_bet' => false,
            'server_seed' => ProvablyFair::generateServerSeed(),
            'client_seed' => ProvablyFair::generateServerSeed(),
            'nonce' => mt_rand(1, 10000000)
        ]);

        $this->game = $game;
        $this->state = $state;
    }

    public function clientSeed($set = null): string {
        if($set != null) $this->state->update(['client_seed' => $set]);
        return $this->state->client_seed;
    }

    public function serverSeed($set = null): string {
        if($set != null) $this->state->update(['server_seed' => $set]);
        return $this->state->server_seed;
    }

    public function nonce($set = null): int {
        if($set != null) $this->state->update(['nonce' => $set]);
        return $this->state->nonce;
    }

    public function history($add = null): array {
        if($add != null) {
            $history = $this->state->history;
            array_unshift($history, $add);
            if(count($history) >= 15) array_pop($history);

            $this->state->update(['history' => $history]);
        }
        return $this->state->history;
    }

    public function hasBetFrom(string $userId) {
        foreach ($this->players() as $player) if($player['user']['_id'] === $userId) return true;
        return false;
    }

    public function players(array $add = null): array {
        if($add != null) {
            $players = $this->state->players;
            array_push($players, $add);
            $this->state->update(['players' => $players]);
        }
        return $this->state->players;
    }

    /**
     * Updates data array and erases previous data.
     * @param array|null $set
     * @return array
     */
    public function data(array $set = null) {
        if($set != null) $this->state->update(['data' => $set]);
        return $this->state->data;
    }

    /**
     * Updates data array without erasing previous data.
     * @param array $data
     * @return MultiplayerGameStateBuilder
     */
    public function pushData(array $data) {
        $this->state->update(['data' => array_merge($this->state->data ?? [], $data)]);
        return $this;
    }

    public function sendDataUpdateEvent() {
        event(new MultiplayerDataUpdate($this->game, $this->state->data ?? []));
        return $this;
    }

    public function resetPlayers() {
        $this->state->update(['players' => []]);
    }

    public function betting($enable = null): bool {
        if($enable !== null) {
            $this->state->update(['can_bet' => $enable]);
            event(new MultiplayerBettingStateChange($this->game, $enable));
        }
        return $this->state->can_bet;
    }

    public function timestamp($set = null): int {
        if($set != null) $this->state->update(['timestamp' => $set]);
        return $this->state->timestamp;
    }

    public function toArray(): array {
        return $this->state->makeHidden('server_seed')->toArray();
    }

}
