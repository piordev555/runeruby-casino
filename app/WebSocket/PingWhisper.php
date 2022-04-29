<?php namespace App\WebSocket;

class PingWhisper extends WebSocketWhisper {

    public function event(): string {
        return "Ping";
    }

    public function process($data): array {
        $json = json_decode(file_get_contents(base_path('package.json')));

        return [
            'info' => $json->name,
            'version' => $json->version
        ];
    }

}
