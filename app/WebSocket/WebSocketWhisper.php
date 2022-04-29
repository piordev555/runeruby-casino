<?php namespace App\WebSocket;

use App\Events\WhisperResponse;
use App\User;

abstract class WebSocketWhisper {

    public ?User $user;
    public string $id;

    public abstract function event(): string;

    public abstract function process($data): array;

    public function sendResponse(array $response) {
        event(new WhisperResponse($this->user, $this->id, $response));
    }

    public static function find(string $eventName): ?WebSocketWhisper {
        $list = self::list();
        return isset($list[$eventName]) ? (new $list[$eventName]) : null;
    }

    public static function list(): array {
        return [
            'Ping' => PingWhisper::class,
            'ChatMessage' => ChatMessageWhisper::class,
            'ChatStickerMessage' => ChatStickerMessageWhisper::class,
            'OnlineUsers' => OnlineUsersWhisper::class,
            'Play' => PlayWhisper::class,
            'Turn' => TurnWhisper::class,
            'Finish' => FinishWhisper::class,
            'GameData' => GameDataWhisper::class,
            'Info' => InfoWhisper::class,
            'RestoreData' => RestoreDataWhisper::class,
            'SupportMessage' => SupportMessageWhisper::class
        ];
    }

}
