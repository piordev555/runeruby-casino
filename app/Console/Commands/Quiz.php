<?php

namespace App\Console\Commands;

use App\Events\NewQuiz;
use App\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Quiz extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'win5x:quiz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send quiz to chat';

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
        /*$getSuitableQuiz = function() use(&$getSuitableQuiz) {
            $json = json_decode(file_get_contents('https://opentdb.com/api.php?amount=1&type=multiple'))->results[0];
            if(str_contains($json->question, 'which') || str_contains($json->question, 'following')) return $getSuitableQuiz();
            return $json;
        };

        $json = $getSuitableQuiz();

        Settings::set('quiz_question', $json->question);
        Settings::set('quiz_answer', $json->correct_answer);
        Settings::set('quiz_active', 'true');

        $this->info('Answer: ' . $json->correct_answer);
        event(new NewQuiz($json->question));*/
    }

}
