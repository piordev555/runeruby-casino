<?php namespace App\Games\Kernel\Module\General\Wrapper;

use App\Game;
use App\Games\Kernel\Data;
use App\Games\Kernel\ProvablyFairResult;

interface MultiplierCanBeLimited {

    public function multiplier(?Game $game, ?Data $data, ProvablyFairResult $result): float;

}
