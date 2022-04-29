<?php namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizAnswered implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;
    private string $correct, $question;

    public function __construct($user, string $question, string $correct) {
        $this->user = $user;
        $this->question = $question;
        $this->correct = $correct;
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
            'user' => $this->user->toArray(),
            'question' => $this->question,
            'correct' => $this->correct,
            'currency' => $this->user->clientCurrency()->id(),
            'reward' => floatval($this->user->clientCurrency()->option('quiz'))
        ];
    }

}
