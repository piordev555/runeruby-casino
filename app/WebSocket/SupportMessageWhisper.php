<?php namespace App\WebSocket;

use App\Events\SupportMessage;
use App\SupportChat;
use App\Utils\APIResponse;

class SupportMessageWhisper extends WebSocketWhisper {

    public function event(): string {
        return 'SupportMessage';
    }

    public function process($data): array {
        if(strlen($data->message) < 1 || strlen($data->message) > 10000) return ['code' => 1, 'message' => 'Message is too short or long'];
        if($this->user->mute != null && !$this->user->mute->isPast()) return ['code' => 2, 'message' => 'User is banned'];

        $chat = null;
        if($data->channel === 'new') $chat = SupportChat::create([
            'user' => $this->user->_id,
            'messages' => [],
            'status' => 0
        ]);
        else $chat = SupportChat::where('_id', $data->channel)->first();

        if($chat == null) return ['code' => 3, 'message' => 'Unknown chat'];
        if($chat->status == 1) return ['code' => 4, 'message' => 'Chat is closed'];

        $time = now()->timestamp;

        $message = [
            'user' => $this->user->toArray(),
            'message' => $data->message,
            'created_at' => $time
        ];

        $messages = $chat->messages;
        array_push($messages, $message);
        $chat->update([
            'messages' => $messages,
            $this->user->access === 'user' ? 'user_read' : 'support_read' => $time
        ]);

        event(new SupportMessage($chat, $message));

        return [
            'id' => $chat->_id,
            'user' => $this->user->toArray()
        ];
    }

}
