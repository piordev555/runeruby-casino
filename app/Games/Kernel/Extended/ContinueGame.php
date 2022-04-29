<?php namespace App\Games\Kernel\Extended;

class ContinueGame extends Turn {

    public function type(): string {
        return 'continue';
    }

}
