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

class MultiplayerFinishAndSetupNextGame implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private \App\Games\Kernel\Game $game;
    private array $data;
    private $nextGameDelay;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Games\Kernel\Game $game, array $data, $nextGameDelay) {
        $this->game = $game;
        $this->data = $data;
        $this->nextGameDelay = $nextGameDelay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        event(new MultiplayerGameFinished($this->game, $this->data));

        $this->game->onDispatchedFinish();

        dispatch((new MultiplayerNextGame($this->game))->delay($this->nextGameDelay));
    }

}
