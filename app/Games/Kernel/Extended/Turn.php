<?php namespace App\Games\Kernel\Extended;

abstract class Turn {

    private $game;
    private $data;

    public function __construct(\App\Game $game, array $data) {
        $this->game = $game;
        $this->data = $data;
    }

    abstract public function type(): string;

    public function toArray(): array {
        return [
            'type' => $this->type(),
            'data' => $this->data
        ];
    }

}
