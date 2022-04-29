<?php namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BrowserOnlyNotification extends Notification {

    private $title;
    private $message;

    public function __construct($title, $message) {
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable) {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification) {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->message);
    }

}
