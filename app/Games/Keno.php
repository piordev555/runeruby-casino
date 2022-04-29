<?php namespace App\Games;

use App\Game;
use App\Games\Kernel\Data;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Games\Kernel\Quick\QuickGame;
use App\Games\Kernel\Quick\SuccessfulQuickGameResult;
use App\Games\Kernel\RejectedGameResult;

class Keno extends QuickGame implements MultiplierCanBeLimited {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'keno';
            }

            function name(): string {
                return 'Keno';
            }

            function icon(): string {
                return 'keno';
            }

            public function category(): array {
                return [GameCategory::$originals, GameCategory::$table, GameCategory::$instant];
            }
        };
    }

    function start($user, Data $data) {
        if(!isset($data->game()->tiles) || sizeof($data->game()->tiles) < 1 || sizeof($data->game()->tiles) > 10) return new RejectedGameResult(1);

        return new SuccessfulQuickGameResult((new ProvablyFair($this))->result(), function(SuccessfulQuickGameResult $result, array $transformedResults) use($user, $data) {
            $hits = 0;
            foreach ($transformedResults as $tile)
                if(in_array($tile, $data->intArray('tiles'))) $hits++;

            $mul = $this->data()[count($data->intArray('tiles'))][$hits];
            if($mul < 1) $result->lose();
            else $result->win($user, $data, $mul);

            $result->delay(1000);

            return [
                'hits' => $hits,
                'tiles' => $transformedResults,
                'user_tiles' => $data->intArray('tiles')
            ];
        });
    }

    public function data(): array {
        return [
            1 => $this->applyHouseEdge([0, 3.8]),
            2 => $this->applyHouseEdge([0, 1.7, 5.2]),
            3 => $this->applyHouseEdge([0, 0, 2.7, 48]),
            4 => $this->applyHouseEdge([0, 0, 1.7, 10, 84]),
            5 => $this->applyHouseEdge([0, 0, 1.4, 4, 14, 290]),
            6 => $this->applyHouseEdge([0, 0, 0, 3, 9, 160, 720]),
            7 => $this->applyHouseEdge([0, 0, 0, 2, 7, 30, 280, 800]),
            8 => $this->applyHouseEdge([0, 0, 0, 2, 4, 10, 50, 300, 850]),
            9 => $this->applyHouseEdge([0, 0, 0, 2, 2.5, 4.5, 12, 60, 320, 900]),
            10 => $this->applyHouseEdge([0, 0, 0, 1.5, 2, 4, 6, 22, 80, 400, 1000])
        ];
    }

    private function getMultiplier($transformedResults, Data $data) {
        $hits = 0;
        foreach ($transformedResults as $tile)
            if(in_array($tile, $data->intArray('tiles'))) $hits++;
        return $this->data()[count($data->intArray('tiles'))][$hits];
    }

    public function isLoss(ProvablyFairResult $result, Data $data): bool {
        return $this->getMultiplier($result->result(), $data) <= 1;
    }

    function result(ProvablyFairResult $result): array {
        $max_squares = 39;
        $squares = range(0, $max_squares);

        $tiles = [];
        for($i = 0; $i < 10; $i++) array_push($tiles, array_splice($squares, floor($result->extractFloats(10)[$i] * ($max_squares - $i + 1)), 1)[0]);

        return $tiles;
    }

    public function multiplier(?Game $game, ?Data $data, ProvablyFairResult $result): float {
        return $this->getMultiplier($result->result(), $data);
    }

    public function getBotData(): array {
        $tiles = [];

        for($i = 0; $i < mt_rand(1, 10); $i++) {
            $rnd = mt_rand(1, 40);
            if(in_array($rnd, $tiles)) {
                $i--;
                continue;
            }

            array_push($tiles, $rnd);
        }

        return [
            'tiles' => $tiles
        ];
    }

}
