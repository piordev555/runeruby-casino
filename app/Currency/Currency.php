<?php namespace App\Currency;

use App\Currency\BitGo\BitGoCurrency;
use App\Currency\Option\WalletOption;
use App\Events\Deposit;
use App\Invoice;
use App\Settings;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\Decimal128;

abstract class Currency {

    abstract function id(): string;

    abstract function walletId(): string;

    abstract function name(): string;

    abstract function alias(): string;

    abstract function displayName(): string;

    abstract function icon(): string;

    abstract function style(): string;

    abstract function newWalletAddress(): string;

    protected abstract function options(): array;

    public function minBet(): float {
        return 0.00000001;
    }

    public function tokenPrice() {
        try {
            if (!Cache::has('conversions:' . $this->alias()))
                Cache::put('conversions:' . $this->alias(), file_get_contents("https://api.coingecko.com/api/v3/coins/{$this->alias()}?localization=false&market_data=true"), now()->addHours(1));
            $json = json_decode(Cache::get('conversions:' . $this->alias()));
            return $json->market_data->current_price->usd;
        } catch (\Exception $e) {
            return -1;
        }
    }

    public function convertUSDToToken(float $usdAmount) {
        return $usdAmount / $this->tokenPrice();
    }

    public function convertTokenToUSD(float $tokenAmount) {
        return $tokenAmount * $this->tokenPrice();
    }

    public function getBotBet() {
        return $this->randomBotBet($this->convertUSDToToken(1), $this->convertUSDToToken(25));
    }

    /**
     * Gets random bet value. Higher values are less common.
     * @param $min
     * @param $max
     * @return mixed
     */
    protected function randomBotBet(float $min, float $max) {
        try {
            $diff = 100000000;
            return min(mt_rand($min * $diff, $max * $diff) / $diff, mt_rand($min * $diff, $max * $diff) / $diff);
        } catch (\Exception $e) {
            return $this->randomBotBet(1, 100);
        }
    }

    /** @return WalletOption[] */
    public function getOptions(): array {
        return array_merge($this->options(), [
            new class extends WalletOption {
                function id() {
                    return "demo";
                }

                function name(): string {
                    return "Demo value";
                }
            },
            new class extends WalletOption {
                function id() {
                    return 'high_roller_requirement';
                }

                function name(): string {
                    return '"High Rollers" tab min bet amount';
                }
            },
            new class extends WalletOption {
                public function id() {
                    return 'quiz';
                }

                public function name(): string {
                    return 'Trivia answer reward';
                }
            },
            new class extends WalletOption {
                public function id() {
                    return 'weekly_bonus';
                }

                public function name(): string {
                    return 'Bronze VIP Weekly Bonus (Multiplied by user VIP level)';
                }
            },
            new class extends WalletOption {
                public function id() {
                    return 'rain';
                }

                public function name(): string {
                    return 'Rain reward';
                }
            },
            new class extends WalletOption {
                public function id() {
                    return 'referral_bonus';
                }

                public function name(): string {
                    return 'Active referral bonus';
                }
            },
            new class extends WalletOption {
                function id() {
                    return "withdraw";
                }

                function name(): string {
                    return "Minimal withdraw amount";
                }
            },
            new class extends WalletOption {
                public function id() {
                    return "vip_ruby";
                }

                public function name(): string {
                    return "Ruby VIP wager requirement";
                }
            },
            new class extends WalletOption {
                public function id() {
                    return "vip_emerald";
                }

                public function name(): string {
                    return "Emerald VIP wager requirement";
                }
            },
            new class extends WalletOption {
                public function id() {
                    return "vip_sapphire";
                }

                public function name(): string {
                    return "Sapphire VIP wager requirement";
                }
            },
            new class extends WalletOption {
                public function id() {
                    return "vip_diamond";
                }

                public function name(): string {
                    return "Diamond VIP wager requirement";
                }
            },
            new class extends WalletOption {
                public function id() {
                    return "vip_gold";
                }

                public function name(): string {
                    return "Gold VIP wager requirement";
                }
            }
        ]);
    }

    public function option(string $key, string $value = null): string {
        if($value == null) {
            if(Cache::has('currency:'.$this->walletId().':'.$key)) $val = json_decode(Cache::get('currency:'.$this->walletId().':'.$key), true)[$key] ?? '1';
            else $val = \App\Currency::where('currency', $this->walletId())->first()->data[$key] ?? '1';

            if(str_starts_with($val, "$")) {
                $val = floatval(substr($val, 1));
                return strval($this->convertUSDToToken($val));
            }

            return $val;
        }

        $data = \App\Currency::where('currency', $this->walletId())->first();

        if(!$data) $data = \App\Currency::create(['currency' => $this->walletId(), 'data' => []]);

        $data = $data->data;
        $data[$key] = $value;

        \App\Currency::where('currency', $this->walletId())->first()->update([
            'data' => $data
        ]);

        Cache::forget('currency:'.$this->walletId().':'.$key);
        Cache::put('currency:'.$this->walletId().':'.$key, json_encode($data), now()->addYear());
        return $value;
    }

    abstract function isRunning(): bool;

    /**
     * @param string|null $wallet Null for every transaction except local nodes
     * @return mixed
     */
    abstract function process(string $wallet = null);

    abstract function send(string $from, string $to, float $sum);

    abstract function setupWallet();

    abstract function coldWalletBalance(): float;

    abstract function hotWalletBalance(): float;

    public static function toCurrencyArray(array $array) {
        $currency = [];
        foreach($array as $c) {
            $currency = array_merge($currency, [
                $c->id() => [
                    'id' => $c->id(),
                    'walletId' => $c->walletId(),
                    'name' => $c->name(),
                    'displayName' => $c->displayName(),
                    'icon' => $c->icon(),
                    'style' => $c->style(),
                    'price' => $c->tokenPrice(),
                    'minimalWithdraw' => floatval($c->option('withdraw')),
                    'highRollerRequirement' => floatval($c->option('high_roller_requirement')),
                    'balance' => [
                        'real' => auth('sanctum')->guest() ? null : auth('sanctum')->user()->balance($c)->get(),
                        'demo' => auth('sanctum')->guest() ? null : auth('sanctum')->user()->balance($c)->demo(true)->get()
                    ],
                    'vip' => [
                        'breakpoints' => [
                            1 => floatval($c->option('vip_ruby')),
                            2 => floatval($c->option('vip_emerald')),
                            3 => floatval($c->option('vip_sapphire')),
                            4 => floatval($c->option('vip_diamond')),
                            5 => floatval($c->option('vip_vip'))
                        ]
                    ]
                ],
                'vipClosest' => auth('sanctum')->guest() ? Currency::all()[0]->id() : auth('sanctum')->user()->closestVipCurrency()->walletId(),
                'vipClosestWager' => auth('sanctum')->guest() ? 0 : DB::table('games')->where('user', auth('sanctum')->user()->_id)->where('currency', auth('sanctum')->user()->closestVipCurrency()->id())->where('demo', '!=', true)->where('status', '!=', 'in-progress')->where('status', '!=', 'cancelled')->sum('wager')
            ]);
        }

        return $currency;
    }

    public static function getAllSupportedCoins(): array {
        return [
            new Local\RS3(),
            new Local\OSRS()
        ];
    }

    public static function all(): array {
        return self::getAllSupportedCoins();
    }

    public static function getByWalletId($walletId): array {
        $result = [];
        foreach(self::getAllSupportedCoins() as $coin) if($coin->walletId() === $walletId) array_push($result, $coin);
        return $result;
    }

    public static function find(string $id): ?Currency {
        foreach (self::getAllSupportedCoins() as $currency) if($currency->id() === $id) {
            if(\App\Currency::where('currency', $currency->id())->first() == null) {
                \App\Currency::create([
                    'currency' => $currency->id(),
                    'data' => []
                ]);
            }

            return $currency;
        }

        return null;
    }

    protected function accept(int $confirmations, string $wallet, string $id, float $sum) {
        $user = User::where('wallet_'.$this->id(), $wallet)->first();
        if($user == null) return false;

        $invoice = Invoice::where('id', $id)->first();
        if($invoice == null) {
            $invoice = Invoice::create([
                'user' => $user->_id,
                'sum' => new Decimal128($sum),
                'type' => 'currency',
                'currency' => $this->id(),
                'id' => $id,
                'confirmations' => $confirmations,
                'status' => 0
            ]);
            event(new Deposit($user, $this, $sum));
        } else $invoice->update([
            'confirmations' => $confirmations
        ]);

        if($invoice->status == 0 && $invoice->confirmations >= intval($this->option('confirmations'))) {
            $invoice->update(['status' => 1]);
            $user->balance($this)->add($sum, Transaction::builder()->message('Deposit')->get());

            if(!($this instanceof BitGoCurrency)) $this->send($wallet, $this->option('transfer_address'), $sum);

            if($user->referral) {
                $referrer = User::where('_id', $user->referral)->first();

                $commissionPercent = 0;

                switch ($referrer->vipLevel()) {
                    case 0: $commissionPercent = 1; break;
                    case 1: $commissionPercent = 2; break;
                    case 2: $commissionPercent = 3; break;
                    case 3: $commissionPercent = 5; break;
                    case 4: $commissionPercent = 10; break;
                    case 5: $commissionPercent = 20; break;
                }

                if($commissionPercent !== 0) {
                    $commission = ($commissionPercent * $sum) / 100;
                    $referrer->balance($this)->add($commission, Transaction::builder()->message('Affiliate commission (' . $commissionPercent . '% from ' . $sum . ' .' . $this->name() . ')')->get());
                }
            }
        }

        return true;
    }

}
