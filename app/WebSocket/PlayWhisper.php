<?php namespace App\WebSocket;

use App\Currency\Currency;
use App\Games\Kernel\Data;
use App\Games\Kernel\Game;
use Illuminate\Support\Facades\DB;

class PlayWhisper extends WebSocketWhisper {

    public function event(): string {
        return "Play";
    }

    public function process($data): array {
        $game = Game::find($data->api_id);
        if($game == null) return ['code' => -3, 'message' => 'Unknown API game id'];
        if($game->isDisabled()) return ['code' => -5, 'message' => 'Game is disabled'];

        if($this->user != null && !$game->ignoresMultipleClientTabs() && DB::table('games')->where('game', $data->api_id)->where('user', $this->user->_id)->where('demo', false)->where('status', 'in-progress')->count() > 0) return ['code' => -8, 'message' => 'Game already has started'];

        if($this->user == null && !$data->demo) return ['code' => -2, 'message' => 'Not authorized'];
        if(!$game->usesCustomWagerCalculations() && floatval($data->bet) < Currency::find($data->currency)->minBet()) return ['code' => -1, 'message' => 'Invalid wager value'];
        if($this->user != null && ($this->user->balance(Currency::find($data->currency))->demo($data->demo)->get() < floatval($data->bet))) return ['code' => -4, 'message' => 'Not enough money'];

        $data = new Data($this->user, [
            'api_id' => $data->api_id,
            'bet' => $data->bet ?? 0,
            'currency' => $data->currency,
            'demo' => $data->demo,
            'quick' => $data->quick ?? false,
            'data' => (array) $data->data
        ]);

        if($this->user != null && $this->user->referral != null && $this->user->games() >= floatval(\App\Settings::get('referrer_activity_requirement', 100))) {
            $referrer = \App\User::where('_id', $this->user->referral)->first();
            $referrals = $referrer->referral_wager_obtained ?? [];
            if(!in_array($this->user->_id, $referrals)) {
                array_push($referrals, $this->user->_id);
                $referrer->update(['referral_wager_obtained' => $referrals]);
                $referrer->balance(Currency::find('btc'))->add(floatval(Currency::find('btc')->option('referral_bonus')), \App\Transaction::builder()->message('Active referral bonus')->get());
            }
        }

        if($this->user != null && $this->user->vipLevel() > 0 && $this->user->vip_discord_notified == null) {
            $this->user->notify(new \App\Notifications\VipDiscordNotification());
            $this->user->update(['vip_discord_notified' => true]);
        }

        return $game->process($data);
    }

}
