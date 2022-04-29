<?php namespace App\WebSocket;

use App\Games\Kernel\Extended\ExtendedGame;
use App\Games\Kernel\Game;

class FinishWhisper extends WebSocketWhisper {

    public function event(): string {
        return 'Finish';
    }

    public function process($data): array {
        $game = \App\Game::where('_id', $data->id)->first();
        if($game == null) return ['code' => 1, 'message' => 'Invalid game id'];
        if($game->status !== 'in-progress') return ['code' => 2, 'message' => 'Game is finished'];

        $api_game = Game::find($game->game);
        if(!($api_game instanceof ExtendedGame)) return ['code' => 3, 'message' => 'Unsupported game operation'];

        $api_game->finish($game);
        return [
            'game' => $game->toArray()
        ];
    }

}
