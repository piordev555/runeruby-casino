<?php

namespace App\Jobs;

use App\Events\MultiplayerGameFinished;
use App\Events\MultiplayerTimerStart;
use App\Games\Crash;
use App\Games\Kernel\Game;
use App\Games\Kernel\ProvablyFair;
use App\Settings;
use App\Token\PersonalAccessToken;
use App\WebSocket\WebSocketWhisper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhisperJob implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message) {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $message = json_decode($this->message);
        if(!isset($message->event)) return;
        $event = str_replace('client-', '', $message->event);

        $whisper = WebSocketWhisper::find($event);
        if($whisper == null) return;

        $token = $message->data->token;

        $privateKey = openssl_pkey_get_private('file://'.storage_path('private.key'));
        openssl_private_decrypt(base64_decode($token), $token, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);

        $whisper->id = $message->data->id;

        $accessToken = PersonalAccessToken::findToken($token);
        $whisper->user = $message->data->token ? $accessToken->tokenable->withAccessToken(tap($accessToken->forceFill(['last_used_at' => now()]))->save()) : null;

        if($whisper->user) auth()->login($whisper->user);

        //$this->info('Event ' . $event . ' with data ' . json_encode($message->data->data) . ' -> to @' . ($whisper->user ? $whisper->user->name : 'guest'));

        $response = $whisper->process($message->data->data);
        $whisper->sendResponse($response);
    }

}
