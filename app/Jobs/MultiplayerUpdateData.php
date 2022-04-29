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

class MultiplayerUpdateData implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private \App\Games\Kernel\Game $game;
    private array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Games\Kernel\Game $game, array $data) {
        $this->game = $game;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $state = new MultiplayerGameStateBuilder($this->game);
        $state->data($this->data);
    }

}
