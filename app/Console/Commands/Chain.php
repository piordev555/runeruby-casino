<?php

namespace App\Console\Commands;

use App\Events\MultiplayerGameFinished;
use App\Events\MultiplayerTimerStart;
use App\Games\Kernel\Game;
use App\Games\Kernel\Multiplayer\MultiplayerGame;
use App\Jobs\MultiplayerFinishAndSetupNextGame;
use Illuminate\Console\Command;

class Chain extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:chain {api_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start MultiplayerGame dispatched job chain';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        if(strtolower($this->argument('api_id')) === 'all') {
            foreach (Game::list() as $game) {
                if(!($game instanceof MultiplayerGame)) continue;
                $game->startChain();
                $this->info('Started event chain for ' . $game->metadata()->name());
            }
            return;
        }

        $game = Game::find($this->argument('api_id'));

        if($game == null) {
            $this->error('Unknown game id');
            return;
        }
        if(!($game instanceof MultiplayerGame)) {
            $this->error('Invalid game type');
            return;
        }

        $game->startChain();
        $this->info('Started event chain for ' . $game->metadata()->name());
    }

}
