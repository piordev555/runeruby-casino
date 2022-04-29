<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class ResetWeeklyBonus extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'win5x:resetWeeklyBonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset weekly VIP bonus progress';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        User::query()->update([
            'weekly_bonus' => 0,
            'weekly_bonus_obtained' => false
        ]);
    }

}
