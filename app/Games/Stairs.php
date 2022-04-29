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
use Illuminate\Support\Facades\Log;

class Stairs extends ExtendedGame implements MultiplierCanBeLimited {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'stairs';
            }

            function name(): string {
                return 'Stairs';
            }

            function icon(): string {
                return 'stairs';
            }

            public function category(): array {
                return [GameCategory::$originals];
            }
        };
    }

    public function start(\App\Game $game) {
        $this->pushData($game, ['mines' => intval($this->userData($game)['data']['mines'])]);
    }

    public function getModuleData(\App\Game $game) {
        return floatval($this->gameData($game)['mines']);
    }

    public function turn(\App\Game $game, array $turnData): Turn {
        $rows = [20, 19, 19, 18, 19, 15, 17, 13, 12, 19, 10, 9, 8];
        $row = $rows[$this->getTurn($game) - 1];
        if(intval($turnData['cell']) >= $row || intval($turnData['cell']) < 0) return new FailedTurn($game, []);

        $this->pushHistory($game, intval($turnData['cell']));

        $row = (new ProvablyFair($this, $game->server_seed))->result()->result()[$this->gameData($game)['mines'] - 1][$this->getTurn($game) - 1];
        if(in_array(intval($turnData['cell']), $row)) return new LoseGame($game, ['death' => $row]);

        $game->update([
            'multiplier' => $this->data()[$this->gameData($game)['mines']][$this->getTurn($game)]
        ]);

        $this->pushData($game, [strval($this->getTurn($game)) => intval($turnData['cell'])]);

        if($this->getTurn($game) >= 13) return new FinishGame($game, ['death' => $row]);
        return new ContinueGame($game, ['death' => $row]);
    }

    public function isLoss(ProvablyFairResult $result, \App\Game $game, array $turnData): bool {
        /*if($this->getTurn($game) > 1) for($i = 1; $i < $this->getTurn($game); $i++) {
            if(in_array($this->gameData($game)[strval($i)], (new ProvablyFair($this, $result->server_seed()))->result()->result()[$this->gameData($game)['mines'] - 1][$i - 1])) return false;
        }*/
        return in_array(intval($turnData['cell']), (new ProvablyFair($this, $result->server_seed()))->result()->result()[$this->gameData($game)['mines'] - 1][$this->getTurn($game)]);
    }

    function result(ProvablyFairResult $result): array {
        $rows = [20, 19, 19, 18, 19, 15, 17, 13, 12, 19, 10, 9, 8];
        $output = [];
        for($mines = 1; $mines <= 7; $mines++) {
            $row = [];
            for($i = 1; $i <= count($rows); $i++) {
                $array = range(0, $rows[$i - 1]);
                $floats = $result->extractFloats($rows[$i - 1] * $i);
                $floats = array_splice($floats, $i - 1, $mines * $i);
                $index = 0;
                array_push($row, array_slice(array_map(function($float) use(&$array, &$floats, &$mines, &$rows, &$i, &$index) {
                    $index = $index + 1;
                    return array_splice($array, floor($float * ($rows[$i - 1] - $index + 1)), 1)[0] ?? 5;
                }, $floats), 0, $mines));
            }
            array_push($output, $row);
        }
        return $output;
    }

    public function data(): array {
        return [
            1 => $this->applyHouseEdge([
                13 => 2.71,
                12 => 2.38,
                11 => 2.11,
                10 => 1.90,
                9 => 1.73,
                8 => 1.58,
                7 => 1.46,
                6 => 1.36,
                5 => 1.27,
                4 => 1.19,
                3 => 1.12,
                2 => 1.06,
                1 => 1.00
            ]),
            2 => $this->applyHouseEdge([
                13 => 8.60,
                12 => 6.45,
                11 => 5.01,
                10 => 4.01,
                9 => 3.28,
                8 => 2.73,
                7 => 2.31,
                6 => 1.98,
                5 => 1.72,
                4 => 1.50,
                3 => 1.33,
                2 => 1.18,
                1 => 1.06
            ]),
            3 => $this->applyHouseEdge([
                13 => 30.94,
                12 => 19.34,
                11 => 12.89,
                10 => 9.03,
                9 => 6.56,
                8 => 4.92,
                7 => 3.79,
                6 => 2.98,
                5 => 2.38,
                4 => 1.93,
                3 => 1.59,
                2 => 1.33,
                1 => 1.12
            ]),
            4 => $this->applyHouseEdge([
                13 => 131.51,
                12 => 65.75,
                11 => 36.53,
                10 => 21.92,
                9 => 13.95,
                8 => 9.30,
                7 => 6.44,
                6 => 4.60,
                5 => 3.37,
                4 => 2.53,
                3 => 1.93,
                2 => 1.50,
                1 => 1.19
            ]),
            5 => $this->applyHouseEdge([
                13 => 701.37,
                12 => 263.01,
                11 => 116.90,
                10 => 58.45,
                9 => 31.88,
                8 => 18.60,
                7 => 11.44,
                6 => 7.36,
                5 => 4.90,
                4 => 3.37,
                3 => 2.38,
                2 => 1.72,
                1 => 1.27
            ]),
            6 => $this->applyHouseEdge([
                13 => 5260.29,
                12 => 1315.07,
                11 => 438.36,
                10 => 175.34,
                9 => 79.70,
                8 => 39.85,
                7 => 21.46,
                6 => 12.26,
                5 => 7.36,
                4 => 4.60,
                3 => 2.98,
                2 => 1.98,
                1 => 1.36
            ]),
            7 => $this->applyHouseEdge([
                13 => 73644.00,
                12 => 9205.00,
                11 => 2045.67,
                10 => 613.70,
                9 => 223.16,
                8 => 92.98,
                7 => 42.92,
                6 => 21.46,
                5 => 11.44,
                4 => 6.44,
                3 => 3.79,
                2 => 2.31,
                1 => 1.46
            ])
        ];
    }

    public function multiplier(?Game $game, ?Data $data, ProvablyFairResult $result): float {
        return $this->data()[$this->gameData($game)['mines']][$this->getTurn($game)];
    }

    public function getBotData(): array {
        return [
            'mines' => mt_rand(1, 7)
        ];
    }

    public function getBotTurnData($turnId): array {
        return [
            'cell' => mt_rand(0, 19)
        ];
    }

}
