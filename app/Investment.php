<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class Investment extends Model {

    protected $connection = 'mongodb';
    protected $collection = 'investments';

    protected $fillable = [
        'user', 'status', 'amount', 'currency', 'site_bankroll', 'disinvest_profit', 'disinvest_share', 'realShare'
    ];

    public function getShare() {
        if($this->realShare != null && $this->realShare <= 0) return 0;
        if($this->status == 1) return $this->disinvest_share;
        return ($this->amount / (DB::table('investments')->where('status', 0)->where('currency', $this->currency)->sum('amount') + 1)) * 100;
    }

    public function getRealShare($profit, $globalBankroll) {
        if($profit == 0 || $globalBankroll == 0 || ($this->realShare != null && $this->realShare <= 0)) {
            $this->update(['realShare' => 0]);
            return 0;
        }

        $share = ($profit / ($globalBankroll + 1)) * 100;
        if($share < 0) $share = 0;
        if($share > 100) $share = 100;
        $this->update([
            'realShare' => $share
        ]);
        return $share;
    }

    public function getProfit() {
        if($this->status === 1) return $this->disinvest_profit;
        $share = $this->realShare == null ? $this->getShare() : $this->realShare;
        $amount = $this->amount + (($share / 100) * self::getSiteProfitSince(\App\Currency\Currency::find($this->currency), $this->created_at));
        return $amount <= 0 ? 0 : $amount;
    }

    public static function getGlobalBankroll(\App\Currency\Currency $currency) {
        $bankroll = 0;
        foreach(Investment::where('status', 0)->where('currency', $currency->id())->get() as $investment) $bankroll += $investment->getProfit();
        return $bankroll;
    }

    public static function getUserBankroll(\App\Currency\Currency $currency, $user) {
        $bankroll = 0;
        foreach(Investment::where('status', 0)->where('user', $user->_id)->where('currency', $currency->id())->get() as $investment) $bankroll += $investment->getProfit();
        return $bankroll;
    }

    public static function getSiteProfitSince(\App\Currency\Currency $currency, $created_at) {
        $usersWon = DB::table('games')->where('created_at', '>=', $created_at)->where('demo', '!=', true)->where('currency', $currency->id())->where('status', 'win')->sum('profit');
        $usersWonWager = DB::table('games')->where('created_at', '>=', $created_at)->where('demo', '!=', true)->where('currency', $currency->id())->where('status', 'win')->sum('wager');
        $usersLost = DB::table('games')->where('created_at', '>=', $created_at)->where('demo', '!=', true)->where('currency', $currency->id())->where('status', 'lose')->sum('wager');
        return $usersLost - ($usersWon - $usersWonWager);
    }

    public static function getUserProfit(\App\Currency\Currency $currency, $created_at, $user = null, $till_created_at = null) {
        $user = $user == null ? '*' : $user->_id;
        $usersWon = DB::table('games')->where('created_at', '>=', $created_at)->where('created_at', '<=', $till_created_at == null ? now() : $till_created_at)->where('demo', '!=', true)->where('currency', $currency->id())->where('status', 'win')->where('user', $user)->sum('profit');
        $usersWonWager = DB::table('games')->where('created_at', '>=', $created_at)->where('created_at', '<=', $till_created_at == null ? now() : $till_created_at)->where('demo', '!=', true)->where('currency', $currency->id())->where('status', 'win')->where('user', $user)->sum('wager');
        $usersLost = DB::table('games')->where('created_at', '>=', $created_at)->where('created_at', '<=', $till_created_at == null ? now() : $till_created_at)->where('demo', '!=', true)->where('currency', $currency->id())->where('status', 'lose')->where('user', $user)->sum('wager');
        return $usersLost - ($usersWon - $usersWonWager);
    }

}
