<?php namespace App\Games\Kernel\Extended;

class FinishGame extends Turn {

    public function type(): string {
        return 'finish';
    }

}
