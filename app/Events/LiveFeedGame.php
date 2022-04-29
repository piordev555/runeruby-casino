<?php namespace App\Events;

use App\Game;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveFeedGame implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $game;
    private $delay;

    public function __construct(Game $game, $delay) {
        $this->game = $game;
        $this->delay = $delay;
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
            'user' => User::where('_id', $this->game->user)->first()->toArray(),
            'metadata' => \App\Games\Kernel\Game::find($this->game->game)->metadata()->toArray(),
            'delay' => $this->delay
        ];
    }

}
