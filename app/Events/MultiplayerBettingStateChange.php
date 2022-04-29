<?php namespace App\Events;

use App\Chat;
use App\Game;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MultiplayerBettingStateChange implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private \App\Games\Kernel\Game $game;
    private bool $state;

    public function __construct(\App\Games\Kernel\Game $game, bool $state) {
        $this->game = $game;
        $this->state = $state;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn() {
        return new Channel('Everyone');
    }

    public function broadcastWith() {
        return [
            'game' => $this->game->metadata()->id(),
            'state' => $this->state
        ];
    }

}
