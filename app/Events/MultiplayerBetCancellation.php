<?php namespace App\Events;

use App\Chat;
use App\Game;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MultiplayerBetCancellation implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Game $game;
    private User $user;

    public function __construct(Game $game, User $user) {
        $this->game = $game;
        $this->user = $user;
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
            'game' => $this->game->toArray(),
            'user' => $this->user
        ];
    }

}
