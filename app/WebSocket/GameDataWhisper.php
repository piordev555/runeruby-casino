<?php namespace App\WebSocket;

use App\Games\Kernel\Game;

class GameDataWhisper extends WebSocketWhisper {

    public function event(): string {
        return "GameData";
    }

    public function process($data): array {
        $game = Game::find($data->api_id);
        if($game == null) return ['code' => -3, 'message' => 'Unknown API game id'];
        return $game->data();
    }

}
