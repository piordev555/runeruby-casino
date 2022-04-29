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

class MultiplayerGameFinished implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private array $data;

    public function __construct(\App\Games\Kernel\Game $game, array $data) {
        $this->game = $game;
        $this->data = $data;
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
            'data' => $this->data,
            'client_seed' => $this->game->client_seed(),
            'server_seed' => $this->game->server_seed(),
            'nonce' => $this->game->nonce()
        ];
    }

}
