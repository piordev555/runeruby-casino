<?php namespace App\Games;

use App\Game;
use App\Games\Kernel\Data;
use App\Games\Kernel\Extended\ContinueGame;
use App\Games\Kernel\Extended\ExtendedGame;
use App\Games\Kernel\Extended\FailedTurn;
use App\Games\Kernel\Extended\LoseGame;
use App\Games\Kernel\Extended\Turn;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;

class HiLo extends ExtendedGame implements MultiplierCanBeLimited {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'hilo';
            }

            function name(): string {
                return 'HiLo';
            }

            function icon(): string {
                return 'hilo';
            }

            public function category(): array {
                return [GameCategory::$originals, GameCategory::$table];
            }
        };
    }

    public function start(\App\Game $game) {
        $card = intval($this->userData($game)['data']['starting']);
        $this->pushData($game, ['current' => $card]);
        $this->pushHistory($game, $card);
    }

    public function turn(\App\Game $game, array $turnData): Turn {
        $card = (new ProvablyFair($this, $game->server_seed))->result()->result()[$this->getTurn($game) - 1] + 1;
        $generated = $this->deck()[$card];
        $current = $this->deck()[$this->gameData($game)['current']];

        if($turnData['type'] === 'skip') {
            if($this->getTurn($game) >= 3 + 1) return new FailedTurn($game, []);
            if($game->multiplier === 0) $game->update(['multiplier' => 1]);
            $this->pushData($game, [strval($this->getTurn($game)) => $card]);
            $this->pushData($game, ['current' => $card]);
            $this->pushHistory($game, $card);
            return new ContinueGame($game, ['current' => $card]);
        }

        if(($turnData['type'] === 'higher' && $current['rank'] >= $generated['rank'])
            || ($turnData['type'] === 'lower' && $current['rank'] <= $generated['rank'])
            || ($turnData['type'] === 'same' && $current['rank'] != $generated['rank']))
            return new LoseGame($game, ['current' => $card]);

        if($turnData['type'] === 'higher') $multiplier = HouseEdgeModule::apply($this, 12.350 / (13 - ($current['slot'] - 1)));
        else if($turnData['type'] === 'lower') $multiplier = HouseEdgeModule::apply($this, (12.350 / ($current['slot'])));
        else if($turnData['type'] === 'same') $multiplier = HouseEdgeModule::apply($this, 16.83);

        $game->update([
            'multiplier' => $this->getTurn($game) == 1 ? $multiplier : $game->multiplier + $multiplier
        ]);

        $this->pushData($game, [strval($this->getTurn($game)) => $card]);
        $this->pushData($game, ['current' => $card]);
        $this->pushHistory($game, $card);

        return new ContinueGame($game, ['current' => $card]);
    }

    private function deck() {
        return [
            1 => ['type' => 'spades', 'value' => 'A', 'rank' => 0, 'slot' => 1],
            2 => ['type' => 'spades', 'value' => '2', 'rank' => 1, 'slot' => 2],
            3 => ['type' => 'spades', 'value' => '3', 'rank' => 2, 'slot' => 3],
            4 => ['type' => 'spades', 'value' => '4', 'rank' => 3, 'slot' => 4],
            5 => ['type' => 'spades', 'value' => '5', 'rank' => 4, 'slot' => 5],
            6 => ['type' => 'spades', 'value' => '6', 'rank' => 5, 'slot' => 6],
            7 => ['type' => 'spades', 'value' => '7', 'rank' => 6, 'slot' => 7],
            8 => ['type' => 'spades', 'value' => '8', 'rank' => 7, 'slot' => 8],
            9 => ['type' => 'spades', 'value' => '9', 'rank' => 8, 'slot' => 9],
            10 => ['type' => 'spades', 'value' => '10', 'rank' => 9, 'slot' => 10],
            11 => ['type' => 'spades', 'value' => 'J', 'rank' => 10, 'slot' => 11],
            12 => ['type' => 'spades', 'value' => 'Q', 'rank' => 11, 'slot' => 12],
            13 => ['type' => 'spades', 'value' => 'K', 'rank' => 12, 'slot' => 13],
            14 => ['type' => 'hearts', 'value' => 'A', 'rank' => 0, 'slot' => 1],
            15 => ['type' => 'hearts', 'value' => '2', 'rank' => 1, 'slot' => 2],
            16 => ['type' => 'hearts', 'value' => '3', 'rank' => 2, 'slot' => 3],
            17 => ['type' => 'hearts', 'value' => '4', 'rank' => 3, 'slot' => 4],
            18 => ['type' => 'hearts', 'value' => '5', 'rank' => 4, 'slot' => 5],
            19 => ['type' => 'hearts', 'value' => '6', 'rank' => 5, 'slot' => 6],
            20 => ['type' => 'hearts', 'value' => '7', 'rank' => 6, 'slot' => 7],
            21 => ['type' => 'hearts', 'value' => '8', 'rank' => 7, 'slot' => 8],
            22 => ['type' => 'hearts', 'value' => '9', 'rank' => 8, 'slot' => 9],
            23 => ['type' => 'hearts', 'value' => '10', 'rank' => 9, 'slot' => 10],
            24 => ['type' => 'hearts', 'value' => 'J', 'rank' => 10, 'slot' => 11],
            25 => ['type' => 'hearts', 'value' => 'Q', 'rank' => 11, 'slot' => 12],
            26 => ['type' => 'hearts', 'value' => 'K', 'rank' => 12, 'slot' => 13],
            27 => ['type' => 'clubs', 'value' => 'A', 'rank' => 0, 'slot' => 1],
            28 => ['type' => 'clubs', 'value' => '2', 'rank' => 1, 'slot' => 2],
            29 => ['type' => 'clubs', 'value' => '3', 'rank' => 2, 'slot' => 3],
            30 => ['type' => 'clubs', 'value' => '4', 'rank' => 3, 'slot' => 4],
            31 => ['type' => 'clubs', 'value' => '5', 'rank' => 4, 'slot' => 5],
            32 => ['type' => 'clubs', 'value' => '6', 'rank' => 5, 'slot' => 6],
            33 => ['type' => 'clubs', 'value' => '7', 'rank' => 6, 'slot' => 7],
            34 => ['type' => 'clubs', 'value' => '8', 'rank' => 7, 'slot' => 8],
            35 => ['type' => 'clubs', 'value' => '9', 'rank' => 8, 'slot' => 9],
            36 => ['type' => 'clubs', 'value' => '10', 'rank' => 9, 'slot' => 10],
            37 => ['type' => 'clubs', 'value' => 'J', 'rank' => 10, 'slot' => 11],
            38 => ['type' => 'clubs', 'value' => 'Q', 'rank' => 11, 'slot' => 12],
            39 => ['type' => 'clubs', 'value' => 'K', 'rank' => 12, 'slot' => 13],
            40 => ['type' => 'diamonds', 'value' => 'A', 'rank' => 0, 'slot' => 1],
            41 => ['type' => 'diamonds', 'value' => '2', 'rank' => 1, 'slot' => 2],
            42 => ['type' => 'diamonds', 'value' => '3', 'rank' => 2, 'slot' => 3],
            43 => ['type' => 'diamonds', 'value' => '4', 'rank' => 3, 'slot' => 4],
            44 => ['type' => 'diamonds', 'value' => '5', 'rank' => 4, 'slot' => 5],
            45 => ['type' => 'diamonds', 'value' => '6', 'rank' => 5, 'slot' => 6],
            46 => ['type' => 'diamonds', 'value' => '7', 'rank' => 6, 'slot' => 7],
            47 => ['type' => 'diamonds', 'value' => '8', 'rank' => 7, 'slot' => 8],
            48 => ['type' => 'diamonds', 'value' => '9', 'rank' => 8, 'slot' => 9],
            49 => ['type' => 'diamonds', 'value' => '10', 'rank' => 9, 'slot' => 10],
            50 => ['type' => 'diamonds', 'value' => 'J', 'rank' => 10, 'slot' => 11],
            51 => ['type' => 'diamonds', 'value' => 'Q', 'rank' => 11, 'slot' => 12],
            52 => ['type' => 'diamonds', 'value' => 'K', 'rank' => 12, 'slot' => 13]
        ];
    }

    public function isLoss(ProvablyFairResult $result, \App\Game $game, array $turnData): bool {
        $card = (new ProvablyFair($this, $result->server_seed()))->result()->result()[$this->getTurn($game)] + 1;
        $generated = $this->deck()[$card];
        $current = $this->deck()[$this->gameData($game)['current']];

        /*if($this->getTurn($game) > 1) for($i = 1; $i < $this->getTurn($game); $i++) {
            $card = (new ProvablyFair($this, $result->server_seed()))->result()->result()[$i] + 1;
            if($card != $this->gameData($game)[strval($i)]) return false;
        }*/

        return ($current['rank'] == 0 || $current['rank'] == 12)
            || ($turnData['type'] === 'higher' && $current['rank'] > $generated['rank'])
            || ($turnData['type'] === 'lower' && $current['rank'] < $generated['rank']);
    }

    function result(ProvablyFairResult $result): array {
        return $this->getCards($result, 52);
    }

    public function multiplier(?Game $game, ?Data $data, ProvablyFairResult $result): float {
        return $this->getTurn($game) === 0 ? 0 : $game->multiplier;
    }

    public function getBotData(): array {
        return [
            'starting' => mt_rand(1, 52)
        ];
    }

    public function getBotTurnData($turnId): array {
        return [
            'type' => mt_rand(0, 100) <= 50 ? 'higher' : (mt_rand(0, 100) <= 75 ? 'lower' : 'same')
        ];
    }

}
