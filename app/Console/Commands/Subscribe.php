<?php namespace App\Console\Commands;

use App\Jobs\WhisperJob;
use Clue\React\Redis\Factory;
use Illuminate\Console\Command;

class Subscribe extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'win5x:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to redis updates';

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
        $privateKey = openssl_pkey_get_private('file://'.storage_path('private.key'));
        if(!$privateKey) return;

        $loop = \React\EventLoop\Factory::create();
        $factory = new Factory($loop);

        $client = $factory->createLazyClient(env('APP_DEBUG') ? 'localhost' : "redis://:".env('REDIS_PASSWORD').'@'.env('REDIS_HOST').":".env('REDIS_PORT'));
        $channel = 'whisper.private-Whisper';
        $client->subscribe($channel)->then(function() use($channel) {
            $this->info('Subscribed to '.$channel.PHP_EOL);
        }, function (\Exception $e) use ($client) {
            $client->close();
            $this->error('Unable to subscribe: ' . $e->getMessage() . PHP_EOL);
        });

        $queue = function($message) {
            dispatch(new WhisperJob($message));
        };

        $client->on('message', function ($channel, $message) use(&$queue) {
            $queue($message);
        });

        $loop->run();
    }

}
