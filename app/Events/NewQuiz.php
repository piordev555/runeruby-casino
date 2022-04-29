<?php namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewQuiz implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $quiz;

    public function __construct(string $quiz) {
        $this->quiz = $quiz;
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
            'quiz' => $this->quiz
        ];
    }

}
