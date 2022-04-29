<?php namespace App\Games\Kernel\Extended;

class LoseGame extends Turn {

    public function type(): string {
        return 'lose';
    }

}
