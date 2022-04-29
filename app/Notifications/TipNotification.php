<?php namespace App\Notifications;

use App\Currency\Currency;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TipNotification extends Notification {

    use Queueable;

    private $message;
    private $data;

    public function __construct($from, $currency, $amount) {
        $this->message = 'general.chat_commands.modal.tip.notify';
        $this->data = [
            'name' => $from->name,
            'value' => $amount,
            'currency' => $currency->name()
        ];
    }

    public function via($notifiable) {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable) {
        return [
            'title' => 'runeruby.com',
            'message' => $this->message,
            'data' => $this->data
        ];
    }

}
