<?php namespace App\WebSocket;

class ChatStickerMessageWhisper extends WebSocketWhisper {

    public function event(): string {
        return 'ChatStickerMessage';
    }

    public function process($data): array {
        if($this->user->mute != null && !$this->user->mute->isPast()) return ['code' => 2, 'message' => 'User is banned'];

        $message = \App\Chat::create([
            'user' => $this->user->toArray(),
            'vipLevel' => $this->user->vipLevel(),
            'data' => $data->url,
            'type' => 'gif',
            'channel' => $data->channel
        ]);

        event(new \App\Events\ChatMessage($message));
        return [];
    }

}
