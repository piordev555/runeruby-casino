<?php namespace App\Events;

use App\Currency\Currency;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Deposit implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User $user;
    private Currency $currency;
    private float $amount;

    public function __construct(User $user, Currency $currency, float $amount) {
        $this->user = $user;
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn() {
        return new PrivateChannel('App.User.'.$this->user->id);
    }

    public function broadcastWith() {
        return [
            'currency' => $this->currency->name(),
            'amount' => $this->amount
        ];
    }

}
