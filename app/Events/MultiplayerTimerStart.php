<?php namespace App\Events;

use App\Chat;
use App\Game;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class MultiplayerTimerStart implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private \App\Games\Kernel\Game $game;

    public function __construct(\App\Games\Kernel\Game $game) {
        $this->game = $game;
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
            'client_seed' => $this->game->client_seed(),
            'nonce' => $this->game->nonce()
        ];
    }

}
