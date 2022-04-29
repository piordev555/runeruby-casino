<?php namespace App\Games;

use App\Games\Kernel\Game;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\ProvablyFairResult;
use App\Games\Kernel\Quick\QuickGame;
use App\Games\Kernel\Quick\SuccessfulQuickGameResult;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\Data;
use App\Games\Kernel\RejectedGameResult;
use App\User;

class Wheel extends QuickGame implements MultiplierCanBeLimited {

    function metadata(): Metadata {
        return new class extends Metadata {
            public function id(): string {
                return "wheel";
            }

            public function name(): string{
                return "Wheel";
            }

            public function icon(): string {
                return "wheel";
            }

            public function category(): array {
                return [GameCategory::$originals, GameCategory::$instant];
            }
        };
    }

    public function start($user, Data $data) {
        if($data->game()->mode === 'double') {
            return new SuccessfulQuickGameResult((new ProvablyFair($this))->result(), function(SuccessfulQuickGameResult $result) use($user, $data) {
                $green = [0];
                $red = [1, 3, 5, 7, 9, 11, 13];
                $black = [2, 4, 6, 8, 10, 12, 14];

                $target = null;
                if($data->game()->target === 'green') $target = $green;
                else if($data->game()->target === 'red') $target = $red;
                else if($data->game()->target === 'black') $target = $black;

                if($this->isLoss($result->provablyFairResult(), $data)) $result->lose();
                else $result->win($user, $data, $target == $red || $target == $black ? HouseEdgeModule::apply($this, 2) : HouseEdgeModule::apply($this, 14));

                if(in_array($result->provablyFairResult()->result()[0], $green)) $color = 'green';
                else if(in_array($result->provablyFairResult()->result()[0], $red)) $color = 'red';
                else if(in_array($result->provablyFairResult()->result()[0], $black)) $color = 'black';

                $result->delay($data->quick() ? 1500 : 8000);

                return [
                    'target' => $data->game()->target,
                    'mode' => $data->game()->mode,
                    'segment' => $result->provablyFairResult()->result()[0],
                    'color' => $color
                ];
            });
        }
        else if($data->game()->mode === 'x50') {
            return new SuccessfulQuickGameResult((new ProvablyFair($this, $this->client_seed()))->result(), function(SuccessfulQuickGameResult $result, array $transformedResults) use($user, $data) {
                $yellow = [0];
                $green = [1, 9, 11, 19, 21, 33, 35, 43, 45, 55];
                $red = [3, 5, 7, 13, 15, 17, 23, 25, 27, 29, 31, 37, 39, 41, 47, 49, 51, 53];
                $black = [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54];

                $target = null;
                if($data->game()->target === 'green') $target = $green;
                else if($data->game()->target === 'red') $target = $red;
                else if($data->game()->target === 'black') $target = $black;
                else if($data->game()->target === 'yellow') $target = $yellow;

                if($this->isLoss($result->provablyFairResult(), $data)) $result->lose();
                else $result->win($user, $data, $target == $yellow ? HouseEdgeModule::apply($this, 50) : ($target == $red ? HouseEdgeModule::apply($this, 3) : ($target == $green ? HouseEdgeModule::apply($this, 5) : HouseEdgeModule::apply($this, 2))));

                if(in_array($result->provablyFairResult()->result()[1], $yellow)) $color = 'yellow';
                else if(in_array($result->provablyFairResult()->result()[1], $green)) $color = 'green';
                else if(in_array($result->provablyFairResult()->result()[1], $red)) $color = 'red';
                else if(in_array($result->provablyFairResult()->result()[1], $black)) $color = 'black';

                $result->delay($data->quick() ? 1500 : 8000);

                return [
                    'target' => $data->game()->target,
                    'mode' => $data->game()->mode,
                    'segment' => $result->provablyFairResult()->result()[1],
                    'color' => $color
                ];
            });
        }
        else return new RejectedGameResult(1, 'Invalid game mode');
    }

    public function isLoss(ProvablyFairResult $result, Data $data): bool {
        if($data->game()->mode === 'double') {
            $green = [0];
            $red = [1, 3, 5, 7, 9, 11, 13];
            $black = [2, 4, 6, 8, 10, 12, 14];

            $target = null;
            if($data->game()->target === 'green') $target = $green;
            else if($data->game()->target === 'red') $target = $red;
            else if($data->game()->target === 'black') $target = $black;

            return !in_array($result->result()[0], $target);
        } else {//if($data->game()->mode === 'x50') {
            $yellow = [0];
            $green = [1, 9, 11, 19, 21, 33, 35, 43, 45, 55];
            $red = [3, 5, 7, 13, 15, 17, 23, 25, 27, 29, 31, 37, 39, 41, 47, 49, 51, 53];
            $black = [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54];

            $target = null;
            if($data->game()->target === 'green') $target = $green;
            else if($data->game()->target === 'red') $target = $red;
            else if($data->game()->target === 'black') $target = $black;
            else if($data->game()->target === 'yellow') $target = $yellow;

            return !in_array($result->result()[1], $target);
        }
    }

    function result(ProvablyFairResult $result): array {
        return [
            floor($result->extractFloat() * 14),
            floor($result->extractFloat() * 56)
        ];
    }

    public function multiplier(?\App\Game $game, ?Data $data, ProvablyFairResult $result): float {
        $target = $data->game()->mode;
        if($target === 'x50') return $data->game()->target === 'yellow' ? HouseEdgeModule::apply($this, 50) : ($target === 'red' ? HouseEdgeModule::apply($this, 3) : ($target === 'green' ? HouseEdgeModule::apply($this, 5) : HouseEdgeModule::apply($this, 2)));
        else return $target === 'red' || $target === 'black' ? HouseEdgeModule::apply($this, 2) : HouseEdgeModule::apply($this, 14);
    }

    public function getBotData(): array {
        return [
            'mode' => mt_rand(0, 100) <= 50 ? 'x50' : 'double',
            'target' => mt_rand(0, 100) <= 50 ? 'red' : 'black'
        ];
    }

}
