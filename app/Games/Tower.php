<?php namespace App\Games;

use App\Game;
use App\Games\Kernel\Data;
use App\Games\Kernel\Extended\ContinueGame;
use App\Games\Kernel\Extended\ExtendedGame;
use App\Games\Kernel\Extended\FailedTurn;
use App\Games\Kernel\Extended\FinishGame;
use App\Games\Kernel\Extended\LoseGame;
use App\Games\Kernel\Extended\Turn;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;

class Tower extends ExtendedGame implements MultiplierCanBeLimited {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'tower';
            }

            function name(): string {
                return 'Tower';
            }

            function icon(): string {
                return 'fad fa-gopuram';
            }

            public function category(): array {
                return [GameCategory::$originals];
            }
        };
    }

    public function start(\App\Game $game) {
        $this->pushData($game, [
            'mines' => intval($this->userData($game)['data']['mines'])
        ]);
    }

    public function getModuleData(\App\Game $game) {
        return floatval($this->gameData($game)['mines']);
    }

    public function turn(\App\Game $game, array $turnData): Turn {
        if(intval($turnData['cell']) < 0 || intval($turnData['cell']) > 4) return new FailedTurn($game, []);

        $this->pushHistory($game, intval($turnData['cell']));

        $grid = (new ProvablyFair($this, $game->server_seed))->result()->result()[$this->gameData($game)['mines'] - 1];
        $row = $grid[$this->getTurn($game) - 1];

        if(in_array(intval($turnData['cell']), $row)) {
            $this->pushData($game, ['grid' => $grid]);
            return new LoseGame($game, ['death' => $row, 'grid' => $grid]);
        }

        $game->update([
            'multiplier' => $this->data()[$this->gameData($game)['mines']][$this->getTurn($game)]
        ]);

        $this->pushData($game, [strval($this->getTurn($game)) => intval($turnData['cell'])]);

        if($this->getTurn($game) >= 10) {
            $this->pushData($game, ['grid' => $grid]);
            return new FinishGame($game, ['death' => $row, 'grid' => $grid]);
        }
        return new ContinueGame($game, ['death' => $row]);
    }

    public function isLoss(ProvablyFairResult $result, \App\Game $game, array $turnData): bool {
        /*if($this->getTurn($game) > 1) for($i = 1; $i < $this->getTurn($game); $i++) {
            if(in_array($this->gameData($game)[strval($i)], (new ProvablyFair($this, $result->server_seed()))->result()->result()[$this->gameData($game)['mines'] - 1][$i - 1])) return false;
        }*/
        return in_array(intval($turnData['cell']), (new ProvablyFair($this, $result->server_seed()))->result()->result()[$this->gameData($game)['mines'] - 1][$this->getTurn($game)]);
    }

    function result(ProvablyFairResult $result): array {
        $output = [];
        $columns = 4; $rows = 10;
        for($mines = 1; $mines <= 4; $mines++) {
            $row = [];
            for($i = 1; $i <= $rows; $i++) {
                $array = range(0, $columns);
                $floats = $result->extractFloats($columns * $i);
                $floats = array_slice($floats, $columns * ($i - 1), $columns * $i);
                $index = -1;
                array_push($row, array_slice(array_map(function($float) use(&$array, &$floats, &$mines, &$i, &$index, &$columns) {
                    $index = $index + 1;
                    return array_splice($array, floor($float * ($columns - $index + 1)), 1)[0] ?? -1;
                }, $floats), 0, $mines));
            }

            array_push($output, $row);
        }
        return $output;
    }

    public function data(): array {
        return [
            1 => $this->applyHouseEdge([
                1 => 1.19,
                2 => 1.48,
                3 => 1.86,
                4 => 2.32,
                5 => 2.90,
                6 => 3.62,
                7 => 4.53,
                8 => 5.66,
                9 => 7.08,
                10 => 8.85
            ]),
            2 => $this->applyHouseEdge([
                1 => 1.58,
                2 => 2.64,
                3 => 4.40,
                4 => 7.33,
                5 => 12.22,
                6 => 20.36,
                7 => 33.94,
                8 => 56.56,
                9 => 94.27,
                10 => 157.11
            ]),
            3 => $this->applyHouseEdge([
                1 => 2.38,
                2 => 5.94,
                3 => 14.84,
                4 => 37.11,
                5 => 92.77,
                6 => 231.93,
                7 => 579.83,
                8 => 1449.58,
                9 => 3623.96,
                10 => 9059.91
            ]),
            4 => $this->applyHouseEdge([
                1 => 4.75,
                2 => 23,
                3 => 118,
                4 => 593,
                5 => 2968,
                6 => 14843,
                7 => 74218,
                8 => 371093,
                9 => 1855468,
                10 => 9277343
            ])
        ];
    }

    public function multiplier(?Game $game, ?Data $data, ProvablyFairResult $result): float {
        return $this->data()[$this->gameData($game)['mines']][$this->getTurn($game) + 1];
    }

    public function getBotData(): array {
        return [
            'mines' => mt_rand(1, 4)
        ];
    }

    public function getBotTurnData($turnId): array {
        return [
            'cell' => mt_rand(0, 4)
        ];
    }

}
