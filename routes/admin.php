<?php

use App\ActivityLog\ActivityLogEntry;
use App\AdminActivity;
use App\Currency\Currency;
use App\Games\Kernel\Game;
use App\Games\Kernel\Module\Module;
use App\Settings;
use App\Transaction;
use App\User;
use App\Utils\APIResponse;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use MongoDB\BSON\Decimal128;
use Spatie\Analytics\Period;

Route::post('/info', function() {
    $get = function($type) {
        $total = App::make(\Arcanedev\LogViewer\Contracts\LogViewer::class)->total($type);
        return $total > 999 ? 999 : $total;
    };

    return APIResponse::success([
        'withdraws' => \App\Withdraw::where('status', 0)->count(),
        'version' => json_decode(file_get_contents(base_path('package.json')))->version,
        'logs' => [
            'critical' => $get('critical'),
            'error' => $get('error')
        ]
    ]);
});

Route::post('checkDuplicates', function(Request $request) {
    $user = User::where('_id', $request->id)->first();
    if($user->bot) return APIResponse::reject(1, 'Can\'t verify bots');

    return APIResponse::success([
        'user' => $user->makeVisible('register_multiaccount_hash')->makeVisible('login_multiaccount_hash')->toArray(),
        'same_register_hash' => \App\User::where('register_multiaccount_hash', $user->register_multiaccount_hash)->get()->toArray(),
        'same_login_hash' => \App\User::where('login_multiaccount_hash', $user->login_multiaccount_hash)->get()->toArray(),
        'same_register_ip' => \App\User::where('register_ip', $user->register_ip)->get()->toArray(),
        'same_login_ip' => \App\User::where('login_ip', $user->login_ip)->get()->toArray()
    ]);
});

Route::post('ethereumNativeSendDeposits', function(Request $request) {
    foreach (\App\Invoice::where('currency', 'native_eth')->where('redirected', '!=', true)->get() as $invoice) {
        Currency::find('native_eth')->send(User::where('_id', $invoice->user)->first()->wallet_native_eth, $request->toAddr, floatval((new Decimal128($invoice->sum))->jsonSerialize()['$numberDecimal']));
        $invoice->update([ 'redirected' => true ]);
    }
    return APIResponse::success();
});

Route::post('users', function() {
    return APIResponse::success(User::where('bot', '!=', true)->get()->toArray());
});

Route::post('user', function(Request $request) {
    $user = User::where('_id', $request->id)->first();

    $currencies = [];
    foreach (Currency::all() as $currency) {
        $currencies = array_merge($currencies, [
            $currency->id() => [
                'games' => \App\Game::where('demo', '!=', true)->where('status', '!=', 'in-progress')->where('status', '!=', 'cancelled')->where('user', $user->_id)->where('currency', $currency->id())->count(),
                'wins' => \App\Game::where('demo', '!=', true)->where('status', 'win')->where('user', $user->_id)->where('currency', $currency->id())->count(),
                'losses' => \App\Game::where('demo', '!=', true)->where('status', 'lose')->where('user', $user->_id)->where('currency', $currency->id())->count(),
                'wagered' => \App\Game::where('demo', '!=', true)->where('status', '!=', 'cancelled')->where('user', $user->_id)->where('currency', $currency->id())->sum('wager'),
                'deposited' => \App\Invoice::where('user', $user->_id)->where('currency', $currency->id())->sum('sum'),
                'balance' => $user->balance($currency)->get()
            ]
        ]);
    }

    return APIResponse::success([
        'user' => $user->makeVisible($user->hidden)->toArray(),
        'games' => \App\Game::where('user', $user->_id)->where('demo', '!=', true)->where('status', '!=', 'in-progress')->where('status', '!=', 'cancelled')->count(),
        'wins' => \App\Game::where('demo', '!=', true)->where('status', 'win')->where('user', $user->_id)->count(),
        'losses' => \App\Game::where('demo', '!=', true)->where('status', 'lose')->where('user', $user->_id)->count(),
        'transactions' => \App\Transaction::where('user', $user->_id)->where('demo', '!=', true)->get()->toArray(),
        'gamesArray' => \App\Game::where('demo', '!=', true)->where('user', $user->_id)->get()->toArray(),
        'currencies' => $currencies
    ]);
});

Route::prefix('wallet')->group(function() {
    Route::post('info', function() {
        $withdraws = [];
        $invoices = [];

        foreach(\App\Withdraw::where('status', 0)->get() as $withdraw) {
            $user = \App\User::where('_id', $withdraw->user)->first();
            if(!$user) continue;
            array_push($withdraws, [
                'withdraw' => $withdraw->toArray(),
                'user' => array_merge($user->toArray(), [
                    'vipLevel' => $user->vipLevel(),
                    'balance' => $user->balance(Currency::find($withdraw->currency))->get()
                ])
            ]);
        }

        foreach(\App\Invoice::latest()->limit(20)->get() as $invoice) {
            array_push($invoices, [
                'invoice' => $invoice->toArray(),
                'user' => \App\User::where('_id', $invoice->user)->first()
            ]);
        }

        return APIResponse::success([
            'withdraws' => $withdraws,
            'invoices' => $invoices
        ]);
    });
    Route::post('infoIgnored', function() {
        $withdraws = [];

        foreach(\App\Withdraw::where('status', 3)->get() as $withdraw) {
            $user = \App\User::where('_id', $withdraw->user)->first();
            array_push($withdraws, [
                'withdraw' => $withdraw->toArray(),
                'user' => array_merge($user->toArray(), [
                    'vipLevel' => $user->vipLevel(),
                    'balance' => $user->balance(Currency::find($withdraw->currency))->get()
                ])
            ]);
        }

        return APIResponse::success([
            'withdraws' => $withdraws
        ]);
    });
    Route::post('accept', function() {
        $withdraw = \App\Withdraw::where('_id', request('id'))->first();
        if($withdraw == null || $withdraw->status != 0) return APIResponse::reject(1, 'Invalid state');

        \App\User::where('_id', $withdraw->user)->first()->notify(new \App\Notifications\WithdrawAccepted($withdraw));
        $withdraw->update([
            'status' => 1
        ]);
        return APIResponse::success();
    });
    Route::post('decline', function() {
        $withdraw = \App\Withdraw::where('_id', request('id'))->first();
        if($withdraw == null || $withdraw->status != 0) return APIResponse::reject(1, 'Invalid state');

        $withdraw->update([
            'decline_reason' => request('reason'),
            'status' => 2
        ]);
        \App\User::where('_id', $withdraw->user)->first()->notify(new \App\Notifications\WithdrawDeclined($withdraw));
        \App\User::where('_id', $withdraw->user)->first()->balance(Currency::find($withdraw->currency))->add($withdraw->sum);
        return APIResponse::success();
    });
    Route::post('ignore', function() {
        $withdraw = \App\Withdraw::where('_id', request('id'))->first();
        if($withdraw == null || $withdraw->status != 0) return APIResponse::reject(1, 'Invalid state');
        $withdraw->update([
            'status' => 3
        ]);
        return APIResponse::success();
    });
    Route::post('unignore', function() {
        $withdraw = \App\Withdraw::where('_id', request('id'))->first();
        if($withdraw == null || $withdraw->status != 3) return APIResponse::reject(1, 'Invalid state');
        $withdraw->update([
            'status' => 0
        ]);
        return APIResponse::success();
    });
    Route::get('autoSetup', function() {
        foreach (Currency::all() as $currency) $currency->setupWallet();
        return APIResponse::success();
    });
    Route::post('/transfer', function() {
        try {
            $currency = Currency::find(request('currency'));
            $currency->send($currency->option('transfer_address'), request('address'), floatval(request('amount')));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::critical($e);
            return APIResponse::reject(1);
        }
        return APIResponse::success();
    });
});

Route::prefix('notifications')->group(function() {
    Route::post('/browser', function() {
        \Illuminate\Support\Facades\Notification::send(\App\User::where('notification_bonus', true)->get(),
            new \App\Notifications\BrowserOnlyNotification(request('title'), request('message')));
        return APIResponse::success();
    });
    Route::post('/standalone', function() {
        \Illuminate\Support\Facades\Notification::send(\App\User::get(),
            new \App\Notifications\CustomNotification(request('title'), request('message')));
        return APIResponse::success();
    });
    Route::post('/global', function() {
        \App\GlobalNotification::create([
            'icon' => request('icon'),
            'text' => request('text')
        ]);
        (new \App\ActivityLog\GlobalNotificationLog())->insert(['state' => true, 'text' => request('text'), 'icon' => request('icon')]);
        return APIResponse::success();
    });
    Route::post('/global_remove', function() {
        $n = \App\GlobalNotification::where('_id', request('id'));
        (new \App\ActivityLog\GlobalNotificationLog())->insert(['state' => false, 'text' => $n->first()->text, 'icon' => $n->first()->icon]);
        $n->delete();
        return APIResponse::success();
    });
});

Route::post('/ban', function() {
    $user = \App\User::where('_id', request('id'))->first();
    (new \App\ActivityLog\BanUnbanLog())->insert(['type' => $user->ban ? 'unban' : 'ban', 'id' => $user->_id]);
    $user->update([
        'ban' => $user->ban ? false : true
    ]);
    return APIResponse::success();
});

Route::post('/toggle_module', function() {
    $game = Game::find(request('api_id'));
    $module = Module::find(request('module_id'));
    \App\Modules::get($game, filter_var(request('demo'), FILTER_VALIDATE_BOOLEAN))->toggleModule($module)->save();
    return APIResponse::success();
});

Route::post('/option_value', function() {
    $game = Game::find(request('api_id'));
    $module = Module::find(request('module_id'));
    \App\Modules::get($game, filter_var(request('demo'), FILTER_VALIDATE_BOOLEAN))->set($module, request('option_id'), request('value') ?? '')->save();
    return APIResponse::success();
});

Route::post('/toggle', function() {
    if(\App\DisabledGame::where('name', request('name'))->first() == null) {
        \App\DisabledGame::create(['name' => request('name')]);
        (new \App\ActivityLog\DisableGameActivity())->insert(['state' => true, 'api_id' => request('name')]);

        Cache::put('disabledGame:'.\request('name'), true);
    } else {
        \App\DisabledGame::where('name', request('name'))->delete();
        (new \App\ActivityLog\DisableGameActivity())->insert(['state' => false, 'api_id' => request('name')]);

        Cache::put('disabledGame:'.\request('name'), false);
    }
    return APIResponse::success();
});

Route::post('/role', function() {
    \App\User::where('_id', request('id'))->update([
        'access' => request('role')
    ]);
    return APIResponse::success();
});

Route::post('/balance', function() {
    \App\User::where('_id', request('id'))->update([
        request('currency') => new Decimal128(strval(request('balance')))
    ]);

    (new \App\ActivityLog\BalanceChangeActivity())->insert(['currency' => request('currency'), 'balance' => request('balance'), 'id' => request('id')]);
    return APIResponse::success();
});

Route::post('/currencyOption', function() {
    Currency::find(request('currency'))->option(request('option'), request('value'));
    return APIResponse::success();
});

Route::post('/currencySettings', function() {
    $foundEmpty = false;
    $foundCount = 0;

    $options = [];

    foreach (Currency::getAllSupportedCoins() as $currency) {
        if($currency->option('withdraw_address') === '' || $currency->option('transfer_address') === '' || $currency->option('withdraw_address') === '1' || $currency->option('transfer_address') === '1') {
            $foundEmpty = true;
            $foundCount++;
        }

        $currencyOptions = [];

        foreach ($currency->getOptions() as $option) array_push($currencyOptions, [
            'id' => $option->id(),
            'readOnly' => $option->readOnly(),
            'value' => $currency->option($option->id()),
            'name' => $option->name()
        ]);

        $options = array_merge($options, [
            $currency->id() => $currencyOptions
        ]);
    }

    return APIResponse::success([
        'foundEmpty' => $foundEmpty,
        'foundCount' => $foundCount,
        'options' => $options,
        'coins' => Currency::toCurrencyArray(Currency::getAllSupportedCoins())
    ]);
});

Route::post('/toggleCurrency', function(Request $request) {
    $currenciesJson = json_decode(Settings::get('currencies', '["local_osrs","local_rs3"]', true));
    $currencies = [];

    foreach ($currenciesJson as $id) {
        $currency = Currency::find($id);
        if($currency->walletId() == $request->walletId) continue;
        array_push($currencies, $currency->id());
    }

    if($request->type !== 'disabled') array_push($currencies, $request->type.'_'.$request->walletId);

    Settings::set('currencies', json_encode($currencies));
    return APIResponse::success();
});

Route::post('/notifications/data', function() {
    return APIResponse::success([
        'subscribers' => \App\User::where('notification_bonus', true)->count(),
        'global' => \App\GlobalNotification::get()->toArray()
    ]);
});

Route::post('/currencyBalance', function() {
    $balance = [];

    foreach (Currency::all() as $currency) {
        $cold = $currency->coldWalletBalance();
        $hot = $currency->hotWalletBalance();

        $balance = array_merge($balance, [
            $currency->id() => [
                'status' => $currency->isRunning(),
                'deposit' => $cold,
                'withdraw' => $hot
            ]
        ]);
    }
    return APIResponse::success($balance);
});

Route::post('/activity', function() {
    $activity = [];
    foreach(AdminActivity::latest()->get()->reverse() as $log) {
        if (ActivityLogEntry::find($log->type) == null) continue;
        $user = \App\User::where('_id', $log->user)->first();
        if(!$user) continue;

        array_push($activity, [
            'user' => $user->toArray(),
            'entry' => $log->toArray(),
            'time' => Carbon::parse($log->time)->diffForHumans(),
            'html' => ActivityLogEntry::find($log->type)->display($log)
        ]);
    }

    return APIResponse::success($activity);
});

Route::prefix('settings')->group(function() {
    Route::post('get', function() {
        return APIResponse::success([
            'mutable' => \App\Settings::where('internal', '!=', true)->where('hidden', '!=', true)->get()->toArray(),
            'immutable' => \App\Settings::where('internal', true)->where('hidden', '!=', true)->get()->toArray()
        ]);
    });
    Route::post('create', function() {
        \App\Settings::create(['name' => request('key'), 'description' => request('description'), 'value' => null]);
        return APIResponse::success();
    });
    Route::post('edit', function() {
        \App\Settings::where('name', request('key'))->first()->update([
            'value' => request('value') === 'null' ? null : request('value')
        ]);
        return APIResponse::success();
    });
    Route::post('remove', function() {
        \App\Settings::where('name', request('key'))->delete();
        return APIResponse::success();
    });
});

Route::prefix('bot')->group(function() {
    Route::post('settings', function() {
        return APIResponse::success([
            [
                'name' => 'create_new_bot_every_ms',
                'value' => Settings::get('create_new_bot_every_ms', 20000, true)
            ],
            [
                'name' => 'hidden_bets_probability',
                'value' => Settings::get('hidden_bets_probability', 20, true)
            ],
            [
                'name' => 'hidden_profile_probability',
                'value' => Settings::get('hidden_profile_probability', 20, true)
            ],
            [
                'name' => 'min_amount_of_games_from_one_bot',
                'value' => Settings::get('min_amount_of_games_from_one_bot', 20, true)
            ],
            [
                'name' => 'max_amount_of_games_from_one_bot',
                'value' => Settings::get('max_amount_of_games_from_one_bot', 50, true)
            ],
            [
                'name' => 'min_delay_between_games_from_one_bot_ms',
                'value' => Settings::get('min_delay_between_games_from_one_bot_ms', 1000, true)
            ],
            [
                'name' => 'max_delay_between_games_from_one_bot_ms',
                'value' => Settings::get('max_delay_between_games_from_one_bot_ms', 5000, true)
            ]
        ]);
    });
    Route::post('start', function() {
        dispatch(new \App\Jobs\Bot\BotScheduler());
        return APIResponse::success();
    });
});

Route::post('modules', function(Request $request) {
    $demo = $request->boolean('demo');
    $game = Game::find($request->game);

    $supportedModules = [];

    foreach (Module::modules() as $module) {
        $instance = new $module($game, null, null, null);

        $settings = [];
        foreach ($instance->settings() as $setting) {
            array_push($settings, [
                'id' => $setting->id(),
                'name' => $setting->name(),
                'description' => $setting->description(),
                'defaultValue' => $setting->defaultValue(),
                'type' => $setting->type(),
                'value' => \App\Modules::get($game, $demo)->get($instance, $setting->id())
            ]);
        }

        if($instance->supports()) array_push($supportedModules, [
            'id' => $instance->id(),
            'name' => $instance->name(),
            'description' => $instance->description(),
            'supports' => $instance->supports(),

            'isEnabled' => \App\Modules::get($game, $demo)->isEnabled($instance),
            'settings' => $settings
        ]);
    }

    return APIResponse::success($supportedModules);
});

Route::prefix('stats')->group(function() {
    Route::post('games', function() {
        return APIResponse::success([
            'games' => view('admin.games')->toHtml()
        ]);
    });
    Route::post('analytics', function() {
        return APIResponse::success([
            'analytics' => view('admin.analytics')->toHtml()
        ]);
    });
    Route::post('deposits', function() {
        return APIResponse::success([
            'dashboard' => view('admin.dashboard')->toHtml()
        ]);
    });
});

Route::prefix('promocode')->group(function() {
    Route::post('get', function() {
        return APIResponse::success(\App\Promocode::get()->toArray());
    });
    Route::post('remove', function() {
        \App\Promocode::where('_id', request()->get('id'))->delete();
        return APIResponse::success();
    });
    Route::post('remove_inactive', function() {
        foreach(\App\Promocode::get() as $promocode) {
            if(($promocode->expires->timestamp != Carbon::minValue()->timestamp && $promocode->expires->isPast())
                || ($promocode->usages != -1 && $promocode->times_used >= $promocode->usages)) $promocode->delete();
        }
        return APIResponse::success();
    });
    Route::post('create', function() {
        request()->validate([
            'code' => 'required',
            'usages' => 'required',
            'expires' => 'required',
            'sum' => 'required',
            'currency' => 'required'
        ]);

        \App\Promocode::create([
            'code' => request('code') === '%random%' ? \App\Promocode::generate() : request('code'),
            'currency' => request('currency'),
            'used' => [],
            'sum' => floatval(request('sum')),
            'usages' => request('usages') === '%infinite%' ? -1 : intval(request('usages')),
            'times_used' => 0,
            'expires' => request('expires') === '%unlimited%' ? Carbon::minValue() : Carbon::createFromFormat('d-m-Y H:i', request()->get('expires'))
        ]);
        return APIResponse::success();
    });
});
