<?php

namespace App\Jobs;

use App\Events\MultiplayerGameFinished;
use App\Events\MultiplayerTimerStart;
use App\Games\Crash;
use App\Games\Kernel\Game;
use App\Games\Kernel\ProvablyFair;
use App\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MultiplayerNextGame implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Game $game;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Game $game) {
        $this->game = $game;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $this->game->nextGame();
    }

}
