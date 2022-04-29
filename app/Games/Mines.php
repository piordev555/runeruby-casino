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
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Mines extends ExtendedGame implements MultiplierCanBeLimited {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'mines';
            }

            function name(): string {
                return 'Mines';
            }

            function icon(): string {
                return 'fas fa-bomb';
            }

            public function category(): array {
                return [GameCategory::$originals];
            }
        };
    }

    public function start(\App\Game $game) {}

    public function getModuleData(\App\Game $game) {
        return floatval($this->userData($game)['data']['mines']);
    }

    public function turn(\App\Game $game, array $turnData): Turn {
        if(is_array($turnData['id'])) {
            foreach ($turnData['id'] as $id) {
                $state = $this->turn($game, ['id' => $id]);
                if(!($state instanceof ContinueGame)) return $state;
                $this->setTurn($game, $this->getTurn($game) + 1);
            }
            return new ContinueGame($game, []);
        }

        if($this->inHistory($game, $turnData['id'])) return new FailedTurn($game, []);
        $this->pushHistory($game, $turnData['id']);

        if($this->getGrid($game, $game->server_seed)[intval($turnData['id'])] == 1) return new LoseGame($game, ['grid' => $this->getGrid($game, $game->server_seed)]);

        $game->update([
            'multiplier' => $this->data()[$this->userData($game)['data']['mines']][$this->getTurn($game)]
        ]);

        $this->pushData($game, [strval($this->getTurn($game)) => intval($turnData['id'])]);

        if($this->getTurn($game) - 1 == (5 * 5) - 1 - intval($this->userData($game)['data']['mines'])) return new FinishGame($game, $this->getGrid($game, $game->server_seed));
        return new ContinueGame($game, []);
    }

    public function data(): array {
        if(!Cache::has('win5x:minesMultipliers:'.HouseEdgeModule::get($this, 0.99))) {
            $output = [];

            $factorial = function ($n) use (&$factorial) {
                return $n == 0 ? 1 : $n * $factorial($n - 1);
            };

            $nCr = function ($n, $r) use ($factorial) {
                return $factorial($n) / $factorial($r) / $factorial($n - $r);
            };

            $calculateMultiplier = function ($mines, $diamonds) use ($nCr) {
                return HouseEdgeModule::get($this, 0.99) * $nCr(25, $diamonds) / $nCr(25 - $mines, $diamonds);
            };

            for ($mines = 0; $mines <= 24; $mines++) {
                $grid = [];
                for ($i = 1; $i <= 25 - $mines; $i++)
                    $grid[$i] = $calculateMultiplier($mines, $i);
                $output[$mines] = $grid;
            }

            Cache::forever('win5x:minesMultipliers:'.HouseEdgeModule::get($this, 0.99), json_encode($output));
        }
        return json_decode(Cache::get('win5x:minesMultipliers:'.HouseEdgeModule::get($this, 0.99)), true);
    }

    private function getGrid(Game $game, string $server_seed) {
        $bombs = $this->userData($game)['data']['mines'];
        $bombs_grid = (new ProvablyFair($this, $server_seed))->result()->result()[$bombs - 2];
        $grid = [];

        for($i = 0; $i < 5 * 5; $i++) array_push($grid, 0);
        foreach($bombs_grid as $value) $grid[$value] = 1;

        $this->pushData($game, ['grid' => $grid]);
        return $grid;
    }

    public function isLoss(ProvablyFairResult $result, \App\Game $game, array $turnData): bool {
        if($this->getTurn($game) > 1) for($i = 1; $i < $this->getTurn($game); $i++) {
            if($this->getGrid($game, $result->server_seed())[$this->gameData($game)[strval($i)]] != 0) return false;
        }
        return $this->getGrid($game, $result->server_seed())[intval($turnData['id'])] == 1;
    }

    function result(ProvablyFairResult $result): array {
        $max_squares = 24;

        $output = [];
        for($bombs = 2; $bombs <= 24; $bombs++) {
            $squares = range(0, $max_squares);

            $tiles = [];
            for ($i = 0; $i < $bombs; $i++) array_push($tiles, array_splice($squares, floor($result->extractFloats($bombs)[$i] * ($max_squares - $i + 1)), 1)[0]);
            array_push($output, $tiles);
        }

        return $output;
    }

    public function multiplier(?Game $game, ?Data $data, ProvablyFairResult $result): float {
        return $this->data()[$this->userData($game)['data']['mines']][$this->getTurn($game) + 1];
    }

    public function getBotData(): array {
        return [
            'mines' => mt_rand(2, 24)
        ];
    }

    public function getBotTurnData($turnId): array {
        return [
            'id' => mt_rand(0, 5 * 5 - 1)
        ];
    }

}
