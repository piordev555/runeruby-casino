<?php namespace App\Games\Kernel\Quick;

use App\Currency\Currency;
use App\Games\Kernel\Data;
use App\Games\Kernel\GameResult;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Transaction;
use App\User;

class SuccessfulQuickGameResult implements GameResult {

    private $db_data;
    private $server_seed;
    private $profit = 0;
    private $multiplier = 0;
    public $delay = 0;

    public function __construct(?ProvablyFairResult $server_seed, $callback) {
        $this->server_seed = $server_seed;
        $this->db_data = $callback($this, $server_seed->result());
    }

    public function delay($delay) {
        $this->delay = $delay;
    }

    public function lose() {
        $this->profit = 0;
    }

    public function winCustom(?User $user, Data $data, float $profit, float $displayMultiplier) {
        $this->multiplier = $displayMultiplier;
        $this->profit = $profit;

        if(!$data->guest() && $profit > 0) $user->balance(Currency::find($data->currency()))->quiet()->demo($data->demo())->add($profit, Transaction::builder()->game($data->id())->message('Win (Custom)')->get());
    }

    public function win(?User $user, Data $data, float $multiplier, float $bet = null) {
        $this->multiplier = $multiplier;
        $bet = $bet == null ? $data->bet() : $bet;

        if($multiplier < 1 && $bet <= Currency::find($data->currency())->minBet()) $this->profit = 0;
        else $this->profit = ($bet * $multiplier);

        if(!$data->guest() && $this->profit > 0) $user->balance(Currency::find($data->currency()))->quiet()->demo($data->demo())->add($this->profit, Transaction::builder()->game($data->id())->message('Win')->get());
    }

    public function profit(): float {
        return $this->profit ?? 0;
    }

    public function multiplier(): float {
        return $this->multiplier;
    }

    public function database_data(): array {
        return $this->db_data ?? [];
    }

    public function provablyFairResult(): ProvablyFairResult {
        return $this->server_seed;
    }

    public function seed(): string {
        return $this->server_seed->server_seed();
    }

    public function nonce(): int {
        return $this->server_seed->nonce();
    }

    public function toArray(Data $data): array {
        return ['response' => [
            'game' => [
                'win' =>  $this->profit > 0 && $this->multiplier >= 1,
                'profit' => $this->profit,
                'multiplier' => $this->multiplier,
                'currency' => $data->guest() ? 'btc' : $data->currency(),
                'data' => $this->database_data(),
                'delay' => $this->delay
            ],
            'server_seed' => [
                'result' => $this->server_seed->result(),
                'nonce' => $this->server_seed->nonce(),
                'server_seed' => $this->server_seed->server_seed(),
                'client_seed' => auth('sanctum')->guest() ? ProvablyFair::generateServerSeed() : auth('sanctum')->user()->client_seed
            ]
        ]];
    }

}
