<?php namespace App\WebSocket;

use App\Chat;
use App\Settings;

class ChatMessageWhisper extends WebSocketWhisper {

    public function event(): string {
        return 'ChatMessage';
    }

    public function process($data): array {
        if(strlen($data->message) < 1 || strlen($data->message) > 10000) return ['code' => 1, 'message' => 'Message is too short or long'];
        if($this->user->mute != null && !$this->user->mute->isPast()) return ['code' => 2, 'message' => 'User is banned'];

        if(Chat::where('user_id', $this->user->_id)->where('created_at', '>=', now()->subSeconds(5))->first() != null) return [];

        $message = \App\Chat::create([
            'user' => $this->user->toArray(),
            'user_id' => $this->user->_id,
            'vipLevel' => $this->user->vipLevel(),
            'data' => mb_substr($data->message, 0, 400),
            'type' => 'message',
            'channel' => $data->channel
        ]);

        event(new \App\Events\ChatMessage($message));

        if(\App\Settings::get('quiz_active') === 'true') {
            $sanitize = function ($input) {
                return mb_strtolower(preg_replace("/[^A-Za-zА-Яа-я0-9\-]/u", '', $input));
            };

            if($sanitize($data->message) === $sanitize(\App\Settings::get('quiz_answer'))) {
                Settings::set('quiz_active', false);
                $this->user->balance($this->user->clientCurrency())->add(floatval($this->user->clientCurrency()->option('quiz')), \App\Transaction::builder()->message('Quiz')->get());
                event(new \App\Events\QuizAnswered($this->user, \App\Settings::get('quiz_question'), \App\Settings::get('quiz_answer')));
            }
        }

        return [];
    }

}
