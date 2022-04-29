<?php

namespace App\Console\Commands;

use App\Events\NewQuiz;
use App\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Keys extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'win5x:keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA public/private keys';

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
        $this->info('Generating key pair...');

        try {
            $config = [
                'digest_alg' => 'sha512',
                'private_key_bits' => 4096,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
                'config' => env('OPENSSL_CONFIG')
            ];

            $res = openssl_pkey_new($config);
            openssl_pkey_export_to_file($res, storage_path('private.key'));

            $pubKey = openssl_pkey_get_details($res)['key'];

            File::put(public_path('public.key'), $pubKey);

            $this->putEnv('BITGO_PASSPHRASE', uniqid());
            $this->info('Success. Please backup BITGO_PASSPHRASE string from .env file.');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error(openssl_error_string());
        }
    }

    private function putEnv($key, $value) {
        $path = app()->environmentFilePath();
        $escaped = preg_quote('='.env($key), '/');
        file_put_contents($path, preg_replace("/^{$key}{$escaped}/m", "{$key}={$value}", file_get_contents($path)));
    }

}
