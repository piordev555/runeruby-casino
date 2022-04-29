<?php namespace App\Events;

use App\Chat;
use App\Game;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MultiplayerGameBet implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User $user;
    private Game $game;
    private array $data;

    public function __construct(User $user, Game $game, array $data) {
        $this->user = $user;
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
            'user' => array_merge($this->user->toArray(), ['vipLevel' => $this->user->vipLevel()]),
            'game' => $this->game->toArray(),
            'data' => $this->data
        ];
    }

}
