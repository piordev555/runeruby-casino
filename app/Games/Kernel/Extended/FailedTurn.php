<?php namespace App\Games\Kernel\Extended;

/**
 * If turn returns this instance, then game turnId will not be changed.
 * @package App\Games\Kernel\Extended
 */
class FailedTurn extends Turn {

    public function __construct(\App\Game $game, array $data)
    {
        parent::__construct($game, $data);
    }

    public function type(): string {
        return 'fail';
    }

}
