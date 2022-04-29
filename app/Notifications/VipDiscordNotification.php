<?php namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class VipDiscordNotification extends Notification {

    use Queueable;

    private $message;

    public function __construct() {
        $this->message = 'general.notifications.vip_discord.message';
    }

    public function via($notifiable) {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable) {
        return [
            'title' => 'general.notifications.vip_discord.title',
            'message' => $this->message
        ];
    }

}
