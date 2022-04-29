<?php namespace App\Notifications;

use App\Currency\Currency;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WithdrawDeclined extends Notification {

    use Queueable;

    private $message;
    private $data;

    public function __construct($withdraw) {
        $this->message = 'general.notifications.withdraw_declined.message';
        $this->data = [
            'diff' => $withdraw->created_at->diffForHumans(),
            'sum' => $withdraw->sum,
            'currency' => Currency::find($withdraw->currency)->name(),
            'reason' => $withdraw->decline_reason
        ];
    }

    public function via($notifiable) {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable) {
        return [
            'title' => 'general.notifications.withdraw_declined.title',
            'message' => $this->message,
            'data' => $this->data
        ];
    }

}
