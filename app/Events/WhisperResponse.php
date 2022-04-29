<?php namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhisperResponse implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private ?User $user;
    private string $id;
    private array $response;

    public function __construct($user, string $id, array $response) {
        $this->user = $user;
        $this->id = $id;
        $this->response = $response;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn() {
        return new PrivateChannel('App.User.'.($this->user == null ? 'Guest' : $this->user->_id));
    }

    public function broadcastWith() {
        return [
            'id' => $this->id,
            'data' => $this->response
        ];
    }

}
