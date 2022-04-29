<?php

namespace App\Jobs;

use App\Events\MultiplayerGameFinished;
use App\Game;
use App\Games\Kernel\Multiplayer\MultiplayerGameStateBuilder;
use App\Games\Kernel\ProvablyFair;
use App\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MultiplayerUpdateTimestamp implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private \App\Games\Kernel\Game $game;
    private int $timestamp;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Games\Kernel\Game $game, int $timestamp) {
        $this->game = $game;
        $this->timestamp = $timestamp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $state = new MultiplayerGameStateBuilder($this->game);
        $state->timestamp($this->timestamp);
    }

}
