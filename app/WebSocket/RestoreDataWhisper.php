<?php namespace App\WebSocket;

use App\Games\Kernel\Game;

class RestoreDataWhisper extends WebSocketWhisper {

    public function event(): string {
        return 'RestoreData';
    }

    public function process($data): array {
        if(!$this->user) return ['code' => 3, 'message' => 'Nothing to restore'];

        $game = Game::find($data->api_id);
        if($game == null) return ['code' => 1, 'message' => 'Unknown API game id'];
        if($game->isDisabled()) return ['code' => 2, 'message' => 'Game is disabled'];

        $latest_game = \App\Game::latest()->where('game', $game->metadata()->id())->where('demo', false)->where('user', $this->user->_id)->where('status', 'in-progress')->first();
        if(!$latest_game) return ['code' => 3, 'message' => 'Nothing to restore'];

        return [
            'game' => $latest_game->makeHidden('server_seed')->makeHidden('nonce')->makeHidden('data')->toArray(),
            'history' => $latest_game->data['history'],
            'user_data' => $latest_game->data['user_data']
        ];
    }

}
