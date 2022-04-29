<?php namespace App\Games;

use App\Game;
use App\Games\Kernel\Data;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Games\Kernel\Quick\QuickGame;
use App\Games\Kernel\Quick\SuccessfulQuickGameResult;

class Diamonds extends QuickGame implements MultiplierCanBeLimited {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'diamonds';
            }

            function name(): string {
                return 'Diamonds';
            }

            function icon(): string {
                return 'fad fa-gem';
            }

            public function category(): array {
                return [GameCategory::$originals, GameCategory::$table, GameCategory::$instant];
            }
        };
    }

    function start($user, Data $data) {
        return new SuccessfulQuickGameResult((new ProvablyFair($this))->result(), function(SuccessfulQuickGameResult $result, array $transformedResults) use($user, $data) {
            $result->win($user, $data, $this->getMultiplier($transformedResults));
            $result->delay(200 * 5);

            return [
                'diamonds' => $transformedResults
            ];
        });
    }

    private function getMultiplier($transformedResults) {
        $o = array_count_values($transformedResults);
        $validate = 0;
        foreach($o as $color => $occur) {
            if(count($o) == 1) return HouseEdgeModule::apply($this, 50);
            if(count($o) == 2) {
                if($occur == 4) return HouseEdgeModule::apply($this, 5);
                if($occur == 3 || $occur == 2) {
                    $validate++;
                    if($validate > 1) return HouseEdgeModule::apply($this, 4);
                }
            }
            if(count($o) == 3) {
                if($occur == 3) return HouseEdgeModule::apply($this, 3);
                if($occur == 2) {
                    $validate++;
                    if($validate > 1) return HouseEdgeModule::apply($this, 2);
                }
            }
            if(count($o) == 4 && $occur == 2) return HouseEdgeModule::apply($this, 0.10);
        }
        return 0;
    }

    public function isLoss(ProvablyFairResult $result, Data $data): bool {
        return $this->getMultiplier($result->result()) <= 1;
    }

    function result(ProvablyFairResult $result): array {
        $gems = ['green', 'purple', 'yellow', 'red', 'light_blue', 'pink', 'blue'];
        return array_map(function($value) use($gems) {
            return $gems[floor($value * 7)];
        }, $result->extractFloats(5));
    }

    public function multiplier(?Game $game, ?Data $data, ProvablyFairResult $result): float {
        return $this->getMultiplier($this->result($result));
    }

}
