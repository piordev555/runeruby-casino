<?php namespace App\Games;

use App\Currency\Currency;
use App\Events\MultiplayerTimerStart;
use App\Game;
use App\Games\Kernel\Data;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Multiplayer\MultiplayerGame;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Jobs\MultiplayerDisableBetAccepting;
use App\Jobs\MultiplayerFinishAndSetupNextGame;
use App\Jobs\MultiplayerUpdateData;
use App\Jobs\MultiplayerUpdateTimestamp;

class Baccarat extends MultiplayerGame {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'baccarat';
            }

            function name(): string {
                return 'Baccarat';
            }

            function icon(): string {
                return 'baccarat';
            }

            public function category(): array {
                return [GameCategory::$originals, GameCategory::$table];
            }
        };
    }

    private function getScore($hand) {
        return ($hand[0]['baccaratValue'] + $hand[1]['baccaratValue']) % 10;
    }

    private function getResult(array $result) {
        $player = [
            $this->deck()[$result[0] + 1],
            $this->deck()[$result[2] + 1]
        ];

        $dealer = [
            $this->deck()[$result[1] + 1],
            $this->deck()[$result[3] + 1]
        ];

        $index = 3;

        $drawThirdCards = function(&$playerScore, &$bankerScore) use(&$player, &$dealer, $result, &$index) {
            if($playerScore <= 5) {
                $index++;
                array_push($player, $this->deck()[$result[$index] + 1]);
            }
            if(!isset($player[2]) && $bankerScore <= 5) {
                $index++;
                array_push($dealer, $this->deck()[$result[$index] + 1]);
            }
            if(isset($player[2])) {
                if($bankerScore <= 2) {
                    $index++;
                    array_push($dealer, $this->deck()[$result[$index] + 1]);
                } else if($bankerScore === 3 && $player[2]['baccaratValue'] != 8) {
                    $index++;
                    array_push($dealer, $this->deck()[$result[$index] + 1]);
                } else if($bankerScore === 3 && $player[2]['baccaratValue'] == 8) {
                    // Banker 3 vs 8
                } else if($bankerScore == 4 && in_array($player[2]['baccaratValue'], [2, 3, 4, 5, 6, 7])) {
                    $index++;
                    array_push($dealer, $this->deck()[$result[$index] + 1]);
                } else if($bankerScore == 5 && in_array($player[2]['baccaratValue'], [4, 5, 6, 7])) {
                    $index++;
                    array_push($dealer, $this->deck()[$result[$index] + 1]);
                } else if($bankerScore == 6 && in_array($player[2]['baccaratValue'], [6, 7])) {
                    $index++;
                    array_push($dealer, $this->deck()[$result[$index] + 1]);
                }

                $playerScore = isset($player[2]) ? ($player[0]['baccaratValue'] + $player[1]['baccaratValue'] + $player[2]['baccaratValue']) % 10
                    : ($player[0]['baccaratValue'] + $player[1]['baccaratValue']) % 10;
                $bankerScore = isset($dealer[2]) ? ($dealer[0]['baccaratValue'] + $dealer[1]['baccaratValue'] + $dealer[2]['baccaratValue']) % 10
                    : ($dealer[0]['baccaratValue'] + $dealer[1]['baccaratValue']) % 10;
            }
        };

        $playerScore = $this->getScore($player);
        $dealerScore = $this->getScore($dealer);

        if(!($playerScore == 8 || $playerScore == 9 || $dealerScore == 8 || $dealerScore == 9)) $drawThirdCards($playerScore, $dealerScore);

        return [
            'player' => $player,
            'dealer' => $dealer,
            'score' => [
                'player' => $playerScore,
                'dealer' => $dealerScore
            ],
            'status' => $playerScore == $dealerScore ? 'draw' : ($playerScore > $dealerScore ? 'player' : 'banker')
        ];
    }

    function result(ProvablyFairResult $result): array {
        return $this->getCards($result, 6);
    }

    protected function getPlayerData(Game $game): array {
        return ['bet' => $this->userData($game)['data']['bet']];
    }

    public function nextGame() {
        $this->state()->resetPlayers();
        $this->state()->clientSeed(ProvablyFair::generateServerSeed());
        $this->state()->serverSeed(ProvablyFair::generateServerSeed());
        $this->state()->nonce(now()->timestamp);
        $this->state()->timestamp(now()->timestamp);
        $this->state()->betting(true);

        dispatch((new MultiplayerDisableBetAccepting($this))->delay(now()->addSeconds(15 - 1)));

        event(new MultiplayerTimerStart($this));

        $result = $this->getResult((new ProvablyFair($this))->result()->result());

        $delay = now()->addMilliseconds(((count($result['player']) + count($result['dealer'])) * 900) + 500 + 300)->addSeconds(15);

        dispatch((new MultiplayerFinishAndSetupNextGame($this, $this->getResult((new ProvablyFair($this))->result()->result()),
            $delay))->delay(now()->addSeconds(15)));
        dispatch((new MultiplayerUpdateTimestamp($this, -1))->delay(now()->addSeconds(15)));
    }

    public function onDispatchedFinish() {
        $result = $this->getResult((new ProvablyFair($this))->result()->result());
        $this->state()->data($result);

        foreach($this->getActiveGames() as $game) {
            $multiplier = 0;
            $profit = 0;

            foreach((array) $this->userData($game)['data']['bet'] as $key => $value) {
                if($value == 0) continue;

                if($result['status'] === 'draw' && $key === 'draw') {
                    $multiplier += HouseEdgeModule::apply($this, 8);
                    $profit += $value * HouseEdgeModule::apply($this, 8);
                } else if($result['status'] === 'player' && $key === 'player') {
                    $multiplier += HouseEdgeModule::apply($this, 2);
                    $profit += $value * HouseEdgeModule::apply($this, 2);
                } else if($result['status'] === 'banker' && $key === 'banker') {
                    $multiplier += HouseEdgeModule::apply($this, 1.95);
                    $profit += $value * HouseEdgeModule::apply($this, 1.95);
                }

                if(($key === 'pair_player' && (isset($result['player'][2]) ? ($result['player'][2]['baccaratValue'] == $result['player'][1]['baccaratValue']
                            || $result['player'][0]['baccaratValue'] == $result['player'][1]['baccaratValue'] || $result['player'][0] == $result['player'][2]['baccaratValue'])
                            : $result['player'][0]['baccaratValue'] == $result['player'][1]['baccaratValue']))
                    || ($key === 'pair_banker' && (isset($result['dealer'][2]) ? ($result['dealer'][2]['baccaratValue'] == $result['dealer'][1]['baccaratValue']
                            || $result['dealer'][0]['baccaratValue'] == $result['dealer'][1]['baccaratValue'] || $result['dealer'][0] == $result['dealer'][2]['baccaratValue'])
                            : $result['dealer'][0]['baccaratValue'] == $result['dealer'][1]['baccaratValue']))) {
                    $multiplier += HouseEdgeModule::apply($this, 11);
                    $profit += $value * HouseEdgeModule::apply($this, 11);
                }
            }

            $this->win($game, $multiplier, ((count($result['player']) + count($result['dealer'])) * 900) + 500 + 300);
        }
    }

    public function startChain() {
        dispatch(new MultiplayerFinishAndSetupNextGame($this, [
            'player' => [],
            'dealer' => [],
            'score' => [
                'player' => 0,
                'dealer' => 0
            ],
            'status' => 'player'
        ], now()));
    }

    private function deck() {
        return [
            1 => ['type' => 'spades', 'value' => 'A', 'baccaratValue' => 1],
            2 => ['type' => 'spades', 'value' => '2', 'baccaratValue' => 2],
            3 => ['type' => 'spades', 'value' => '3', 'baccaratValue' => 3],
            4 => ['type' => 'spades', 'value' => '4', 'baccaratValue' => 4],
            5 => ['type' => 'spades', 'value' => '5', 'baccaratValue' => 5],
            6 => ['type' => 'spades', 'value' => '6', 'baccaratValue' => 6],
            7 => ['type' => 'spades', 'value' => '7', 'baccaratValue' => 7],
            8 => ['type' => 'spades', 'value' => '8', 'baccaratValue' => 8],
            9 => ['type' => 'spades', 'value' => '9', 'baccaratValue' => 9],
            10 => ['type' => 'spades', 'value' => '10', 'baccaratValue' => 10],
            11 => ['type' => 'spades', 'value' => 'J', 'baccaratValue' => 11],
            12 => ['type' => 'spades', 'value' => 'Q', 'baccaratValue' => 12],
            13 => ['type' => 'spades', 'value' => 'K', 'baccaratValue' => 13],
            14 => ['type' => 'hearts', 'value' => 'A', 'baccaratValue' => 1],
            15 => ['type' => 'hearts', 'value' => '2', 'baccaratValue' => 2],
            16 => ['type' => 'hearts', 'value' => '3', 'baccaratValue' => 3],
            17 => ['type' => 'hearts', 'value' => '4', 'baccaratValue' => 4],
            18 => ['type' => 'hearts', 'value' => '5', 'baccaratValue' => 5],
            19 => ['type' => 'hearts', 'value' => '6', 'baccaratValue' => 6],
            20 => ['type' => 'hearts', 'value' => '7', 'baccaratValue' => 7],
            21 => ['type' => 'hearts', 'value' => '8', 'baccaratValue' => 8],
            22 => ['type' => 'hearts', 'value' => '9', 'baccaratValue' => 9],
            23 => ['type' => 'hearts', 'value' => '10', 'baccaratValue' => 10],
            24 => ['type' => 'hearts', 'value' => 'J', 'baccaratValue' => 11],
            25 => ['type' => 'hearts', 'value' => 'Q', 'baccaratValue' => 12],
            26 => ['type' => 'hearts', 'value' => 'K', 'baccaratValue' => 13],
            27 => ['type' => 'clubs', 'value' => 'A', 'baccaratValue' => 1],
            28 => ['type' => 'clubs', 'value' => '2', 'baccaratValue' => 2],
            29 => ['type' => 'clubs', 'value' => '3', 'baccaratValue' => 3],
            30 => ['type' => 'clubs', 'value' => '4', 'baccaratValue' => 4],
            31 => ['type' => 'clubs', 'value' => '5', 'baccaratValue' => 5],
            32 => ['type' => 'clubs', 'value' => '6', 'baccaratValue' => 6],
            33 => ['type' => 'clubs', 'value' => '7', 'baccaratValue' => 7],
            34 => ['type' => 'clubs', 'value' => '8', 'baccaratValue' => 8],
            35 => ['type' => 'clubs', 'value' => '9', 'baccaratValue' => 9],
            36 => ['type' => 'clubs', 'value' => '10', 'baccaratValue' => 10],
            37 => ['type' => 'clubs', 'value' => 'J', 'baccaratValue' => 11],
            38 => ['type' => 'clubs', 'value' => 'Q', 'baccaratValue' => 12],
            39 => ['type' => 'clubs', 'value' => 'K', 'baccaratValue' => 13],
            40 => ['type' => 'diamonds', 'value' => 'A', 'baccaratValue' => 1],
            41 => ['type' => 'diamonds', 'value' => '2', 'baccaratValue' => 2],
            42 => ['type' => 'diamonds', 'value' => '3', 'baccaratValue' => 3],
            43 => ['type' => 'diamonds', 'value' => '4', 'baccaratValue' => 4],
            44 => ['type' => 'diamonds', 'value' => '5', 'baccaratValue' => 5],
            45 => ['type' => 'diamonds', 'value' => '6', 'baccaratValue' => 6],
            46 => ['type' => 'diamonds', 'value' => '7', 'baccaratValue' => 7],
            47 => ['type' => 'diamonds', 'value' => '8', 'baccaratValue' => 8],
            48 => ['type' => 'diamonds', 'value' => '9', 'baccaratValue' => 9],
            49 => ['type' => 'diamonds', 'value' => '10', 'baccaratValue' => 10],
            50 => ['type' => 'diamonds', 'value' => 'J', 'baccaratValue' => 11],
            51 => ['type' => 'diamonds', 'value' => 'Q', 'baccaratValue' => 12],
            52 => ['type' => 'diamonds', 'value' => 'K', 'baccaratValue' => 13]
        ];
    }

    public function customWagerCalculation(Data $data): ?bool {
        $totalBet = 0;
        foreach((array) $data->game()->bet as $key => $value) $totalBet += $value;
        if($totalBet < Currency::find($data->currency())->minBet() || ($data->user() != null && $data->user()->balance(Currency::find($data->currency()))->demo($data->demo())->get() < $totalBet)) return false;
        $data->bet($totalBet);
        return true;
    }
}
