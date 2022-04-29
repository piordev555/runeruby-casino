<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Transaction extends Model {

    protected $collection = 'transactions';
    protected $connection = 'mongodb';

    protected $fillable = [
        'user', 'demo', 'currency', 'data', 'new', 'old', 'amount', 'quiet'
    ];

    protected $casts = [
        'data' => 'json'
    ];

    public static function builder() {
        return new class {

            private array $result = [];

            public function game(string $game_id) {
                $this->result['game'] = $game_id;
                return $this;
            }

            public function message(string $message) {
                $this->result['message'] = $message;
                return $this;
            }

            public function get(): array {
                return $this->result;
            }

        };
    }

}
