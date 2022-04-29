<?php namespace App\Events;

use App\Currency\Currency;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class BalanceModification implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User $user;
    private Currency $currency;
    private string $action;
    private float $value;
    private int $delay;
    private bool $demo;

    public function __construct($user, Currency $currency, string $action, bool $demo, float $value, int $delay) {
        $this->user = $user;
        $this->currency = $currency;
        $this->action = $action;
        $this->value = $value;
        $this->delay = $delay;
        $this->demo = $demo;
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
            'diff' => [
                'action' => $this->action,
                'value' => $this->value
            ],
            'currency' => $this->currency->id(),
            'balance' => $this->user->balance($this->currency)->get(),
            'demo_balance' => $this->user->balance($this->currency)->demo()->get(),
            'delay' => $this->delay
        ];
    }

}
