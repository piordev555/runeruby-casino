<?php namespace App\Events;

use App\Games\Kernel\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MultiplayerDataUpdate implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Game $game;
    private array $data;

    public function __construct(Game $game, array $data) {
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
            'data' => $this->data
        ];
    }

}
