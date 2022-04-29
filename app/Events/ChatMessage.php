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

class ChatMessage implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $message;

    public function __construct(Chat $message) {
        $this->message = $message;
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
            'message' => $this->message
        ];
    }

}
