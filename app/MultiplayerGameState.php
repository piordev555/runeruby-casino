<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class MultiplayerGameState extends Model {

    protected $fillable = [
        'game', 'history', 'players', 'server_seed', 'client_seed', 'nonce', 'can_bet', 'timestamp', 'data'
    ];

    protected $hidden = [
        '_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'history' => 'json',
        'player' => 'json',
        'data' => 'json'
    ];

}
