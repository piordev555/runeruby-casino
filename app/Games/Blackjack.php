<?php namespace App\Games;

use App\Currency\Currency;
use App\Games\Kernel\Extended\ContinueGame;
use App\Games\Kernel\Extended\ExtendedGame;
use App\Games\Kernel\Extended\FinishGame;
use App\Games\Kernel\Extended\Turn;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Transaction;
use App\User;

class Blackjack extends ExtendedGame {

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return 'blackjack';
            }

            function name(): string {
                return 'Blackjack';
            }

            function icon(): string {
                return 'blackjack';
            }

            public function category(): array {
                return [GameCategory::$originals, GameCategory::$table];
            }
        };
    }

    private function nextCard(\App\Game $game, $hidden = false, string $server_seed = null) {
        $index = ($this->gameData($game)['index'] ?? 0) + 1;
        if($server_seed == null) $this->pushData($game, ['index' => $index]);

        $index = (new ProvablyFair($this, $server_seed == null ? $game->server_seed : $server_seed))->result()->result()[$index];
        $deck = $this->deck()[$index + 1];
        return [
            'index' => $index,
            'type' => $deck['type'],
            'value' => $deck['value'],
            'blackjack_value' => $deck['blackjackValue'],
            'hidden' => $hidden
        ];
    }

    public function start(\App\Game $game) {
        $this->pushData($game, [
            'player' => [
                $this->nextCard($game),
                $this->nextCard($game)
            ],
            'dealer' => [
                $this->nextCard($game),
                $this->nextCard($game, true)
            ],
            'split' => [],
            'currentHand' => 0
        ]);
    }

    public function turn(\App\Game $game, array $turnData): Turn {
        $user = User::where('_id', $game->user)->first();

        $updateRestore = function() use(&$game) {
            $data = $game->data['user_data'];
            $data = array_merge($data, [
                'player' => $this->gameData($game)['player'],
                'dealer' => $this->gameData($game)['dealer'],
                'split'  => $this->gameData($game)['split'],
                'currentHand' => $this->gameData($game)['currentHand']
            ]);
            $game->update([
                'data' => [
                    'turn' => $game->data['turn'],
                    'history' => $game->data['history'],
                    'game_data' => $game->data['game_data'],
                    'user_data' => $data
                ]
            ]);
        };

        switch ($turnData['type']) {
            case 'info':
                $updateRestore();
                return new ContinueGame($game, ['player' => $this->gameData($game)['player'], 'dealer' => $this->gameData($game)['dealer'][0]]);
            case 'split':
                if ($user != null && $user->balance(Currency::find($game->currency))->demo($game->demo)->get() < $game->wager) return new ContinueGame($game, ['error' => true]);
                $user->balance(Currency::find($game->currency))->demo($game->demo)->subtract($game->wager, Transaction::builder()->game($this->metadata()->id())->message('Split')->get());

                $game->update([
                    'wager' => $game->wager * 2
                ]);

                $this->pushData($game, [
                    'player' => [
                        $this->gameData($game)['player'][0],
                        $this->nextCard($game)
                    ],
                    'split' => [
                        $this->gameData($game)['player'][0],
                        $this->nextCard($game)
                    ]
                ]);

                $updateRestore();
                return new ContinueGame($game, [
                    'split' => $this->gameData($game)['split'],
                    'player' => $this->gameData($game)['player']
                ]);
            case 'hit':
                $card = $this->nextCard($game);
                $player = $this->gameData($game)[$this->gameData($game)['currentHand'] == 0 ? 'player' : 'split'];
                array_push($player, $card);
                $this->pushData($game, [
                    $this->gameData($game)['currentHand'] == 0 ? 'player' : 'split' => $player
                ]);
                $updateRestore();
                return new ContinueGame($game, [
                    'player' => $card
                ]);
            case 'double':
                if ($user != null && $user->balance(Currency::find($game->currency))->demo($game->demo)->get() < $game->wager) return new ContinueGame($game, ['error' => true]);
                $user->balance(Currency::find($game->currency))->demo($game->demo)->subtract($game->wager, Transaction::builder()->game($game->game)->message('Double')->get());
                $game->update([
                    'wager' => $game->wager * 2
                ]);
                $updateRestore();
                return new ContinueGame($game, []);
            case 'insurance':
                if (isset($this->gameData($game)['insurance'])) return new ContinueGame($game, []);

                if ($user != null && $user->balance(Currency::find($game->currency))->demo($game->demo)->get() < $game->wager / 2) return new ContinueGame($game, ['error' => true]);
                $user->balance(Currency::find($game->currency))->demo($game->demo)->subtract($game->wager / 2, Transaction::builder()->game($game->game)->message('Insurance')->get());
                $game->update([
                    'wager' => $game->wager / 2
                ]);
                $this->pushData($game, ['insurance' => true]);
                $updateRestore();
                return new ContinueGame($game, []);
            case 'stand':
                $player = $this->gameData($game)[$this->gameData($game)['currentHand'] == 0 ? 'player' : 'split'];
                $dealer = $this->gameData($game)['dealer'];

                $playerScore = $this->getScore($player);
                $dealerScore = $this->getScore($dealer);

                $playerHandSize = count($player);

                $dealerDraw = [];

                $checkDealerScore = function () use (&$checkDealerScore, &$dealerScore, &$playerScore, &$dealer, &$game, &$dealerDraw) {
                    if ($dealerScore < 17 && $playerScore <= 21) {
                        $card = $this->nextCard($game);
                        array_push($dealerDraw, $card);
                        array_push($dealer, $card);
                        $dealerScore = $this->getScore($dealer);
                        $checkDealerScore();
                    }
                };

                $checkDealerScore();

                $multiplier = 0;

                if ($playerScore == $dealerScore) {
                    if ($playerScore <= 21) $multiplier = 1;
                } else if ($playerScore > $dealerScore) {
                    if ($playerScore == 21 && $playerHandSize < 3) $multiplier = HouseEdgeModule::apply($this, 2);
                    else if ($playerScore <= 21) $multiplier = HouseEdgeModule::apply($this, 2);
                } else if ($playerScore < $dealerScore) {
                    if ($playerScore <= 21 && $dealerScore > 21) $multiplier = HouseEdgeModule::apply($this, 2);
                }

                if ($multiplier == 0 && count($this->gameData($game)['split']) > 0 && $this->gameData($game)['currentHand'] == 0) {
                    $this->pushData($game, [
                        'currentHand' => 1
                    ]);
                    return new ContinueGame($game, []);
                }

                $dealer[1]['hidden'] = false;

                $this->pushData($game, [
                    $this->gameData($game)['currentHand'] == 0 ? 'player' : 'split' => $player,
                    'dealer' => $dealer
                ]);

                $game->update([
                    'multiplier' => $multiplier
                ]);
                $updateRestore();
                return new FinishGame($game, [
                    'dealerReveal' => $dealer[1],
                    'dealerDraw' => $dealerDraw
                ]);
        }

        $updateRestore();
        return new ContinueGame($game, []);
    }

    public function isLoss(ProvablyFairResult $result, \App\Game $game, array $turnData): bool {
        $hand = $this->gameData($game)[$this->gameData($game)['currentHand'] == 0 ? 'player' : 'split'];
        array_push($hand, $this->nextCard($game, false, $result->server_seed()));
        if($this->getScore($hand) < 13) return true;

        return $this->getScore($hand) > 21;
    }

    function result(ProvablyFairResult $result): array {
        return $this->getCards($result, 52);
    }

    private function getScore($hand) {
        $score = 0; $aces = 0;

        foreach ($hand as $value) {
            $score += $value['blackjack_value'];
            if($value['blackjack_value'] == 11) $aces += 1;
            if($score > 21 && $aces > 0) {
                $score -= 10;
                $aces--;
            }
        }

        return $score;
    }

    private function deck() {
        return [
            1 => ['type' => 'spades', 'value' => 'A', 'rank' => 0, 'slot' => 1, 'blackjackValue' => 11],
            2 => ['type' => 'spades', 'value' => '2', 'rank' => 1, 'slot' => 2, 'blackjackValue' => 2],
            3 => ['type' => 'spades', 'value' => '3', 'rank' => 2, 'slot' => 3, 'blackjackValue' => 3],
            4 => ['type' => 'spades', 'value' => '4', 'rank' => 3, 'slot' => 4, 'blackjackValue' => 4],
            5 => ['type' => 'spades', 'value' => '5', 'rank' => 4, 'slot' => 5, 'blackjackValue' => 5],
            6 => ['type' => 'spades', 'value' => '6', 'rank' => 5, 'slot' => 6, 'blackjackValue' => 6],
            7 => ['type' => 'spades', 'value' => '7', 'rank' => 6, 'slot' => 7, 'blackjackValue' => 7],
            8 => ['type' => 'spades', 'value' => '8', 'rank' => 7, 'slot' => 8, 'blackjackValue' => 8],
            9 => ['type' => 'spades', 'value' => '9', 'rank' => 8, 'slot' => 9, 'blackjackValue' => 9],
            10 => ['type' => 'spades', 'value' => '10', 'rank' => 9, 'slot' => 10, 'blackjackValue' => 10],
            11 => ['type' => 'spades', 'value' => 'J', 'rank' => 10, 'slot' => 11, 'blackjackValue' => 10],
            12 => ['type' => 'spades', 'value' => 'Q', 'rank' => 11, 'slot' => 12, 'blackjackValue' => 10],
            13 => ['type' => 'spades', 'value' => 'K', 'rank' => 12, 'slot' => 13, 'blackjackValue' => 10],
            14 => ['type' => 'hearts', 'value' => 'A', 'rank' => 0, 'slot' => 1, 'blackjackValue' => 11],
            15 => ['type' => 'hearts', 'value' => '2', 'rank' => 1, 'slot' => 2, 'blackjackValue' => 2],
            16 => ['type' => 'hearts', 'value' => '3', 'rank' => 2, 'slot' => 3, 'blackjackValue' => 3],
            17 => ['type' => 'hearts', 'value' => '4', 'rank' => 3, 'slot' => 4, 'blackjackValue' => 4],
            18 => ['type' => 'hearts', 'value' => '5', 'rank' => 4, 'slot' => 5, 'blackjackValue' => 5],
            19 => ['type' => 'hearts', 'value' => '6', 'rank' => 5, 'slot' => 6, 'blackjackValue' => 6],
            20 => ['type' => 'hearts', 'value' => '7', 'rank' => 6, 'slot' => 7, 'blackjackValue' => 7],
            21 => ['type' => 'hearts', 'value' => '8', 'rank' => 7, 'slot' => 8, 'blackjackValue' => 8],
            22 => ['type' => 'hearts', 'value' => '9', 'rank' => 8, 'slot' => 9, 'blackjackValue' => 9],
            23 => ['type' => 'hearts', 'value' => '10', 'rank' => 9, 'slot' => 10, 'blackjackValue' => 10],
            24 => ['type' => 'hearts', 'value' => 'J', 'rank' => 10, 'slot' => 11, 'blackjackValue' => 10],
            25 => ['type' => 'hearts', 'value' => 'Q', 'rank' => 11, 'slot' => 12, 'blackjackValue' => 10],
            26 => ['type' => 'hearts', 'value' => 'K', 'rank' => 12, 'slot' => 13, 'blackjackValue' => 10],
            27 => ['type' => 'clubs', 'value' => 'A', 'rank' => 0, 'slot' => 1, 'blackjackValue' => 11],
            28 => ['type' => 'clubs', 'value' => '2', 'rank' => 1, 'slot' => 2, 'blackjackValue' => 2],
            29 => ['type' => 'clubs', 'value' => '3', 'rank' => 2, 'slot' => 3, 'blackjackValue' => 3],
            30 => ['type' => 'clubs', 'value' => '4', 'rank' => 3, 'slot' => 4, 'blackjackValue' => 4],
            31 => ['type' => 'clubs', 'value' => '5', 'rank' => 4, 'slot' => 5, 'blackjackValue' => 5],
            32 => ['type' => 'clubs', 'value' => '6', 'rank' => 5, 'slot' => 6, 'blackjackValue' => 6],
            33 => ['type' => 'clubs', 'value' => '7', 'rank' => 6, 'slot' => 7, 'blackjackValue' => 7],
            34 => ['type' => 'clubs', 'value' => '8', 'rank' => 7, 'slot' => 8, 'blackjackValue' => 8],
            35 => ['type' => 'clubs', 'value' => '9', 'rank' => 8, 'slot' => 9, 'blackjackValue' => 9],
            36 => ['type' => 'clubs', 'value' => '10', 'rank' => 9, 'slot' => 10, 'blackjackValue' => 10],
            37 => ['type' => 'clubs', 'value' => 'J', 'rank' => 10, 'slot' => 11, 'blackjackValue' => 10],
            38 => ['type' => 'clubs', 'value' => 'Q', 'rank' => 11, 'slot' => 12, 'blackjackValue' => 10],
            39 => ['type' => 'clubs', 'value' => 'K', 'rank' => 12, 'slot' => 13, 'blackjackValue' => 10],
            40 => ['type' => 'diamonds', 'value' => 'A', 'rank' => 0, 'slot' => 1, 'blackjackValue' => 11],
            41 => ['type' => 'diamonds', 'value' => '2', 'rank' => 1, 'slot' => 2, 'blackjackValue' => 2],
            42 => ['type' => 'diamonds', 'value' => '3', 'rank' => 2, 'slot' => 3, 'blackjackValue' => 3],
            43 => ['type' => 'diamonds', 'value' => '4', 'rank' => 3, 'slot' => 4, 'blackjackValue' => 4],
            44 => ['type' => 'diamonds', 'value' => '5', 'rank' => 4, 'slot' => 5, 'blackjackValue' => 5],
            45 => ['type' => 'diamonds', 'value' => '6', 'rank' => 5, 'slot' => 6, 'blackjackValue' => 6],
            46 => ['type' => 'diamonds', 'value' => '7', 'rank' => 6, 'slot' => 7, 'blackjackValue' => 7],
            47 => ['type' => 'diamonds', 'value' => '8', 'rank' => 7, 'slot' => 8, 'blackjackValue' => 8],
            48 => ['type' => 'diamonds', 'value' => '9', 'rank' => 8, 'slot' => 9, 'blackjackValue' => 9],
            49 => ['type' => 'diamonds', 'value' => '10', 'rank' => 9, 'slot' => 10, 'blackjackValue' => 10],
            50 => ['type' => 'diamonds', 'value' => 'J', 'rank' => 10, 'slot' => 11, 'blackjackValue' => 10],
            51 => ['type' => 'diamonds', 'value' => 'Q', 'rank' => 11, 'slot' => 12, 'blackjackValue' => 10],
            52 => ['type' => 'diamonds', 'value' => 'K', 'rank' => 12, 'slot' => 13, 'blackjackValue' => 10]
        ];
    }

    public function getBotTurnData($turnId): array {
        return [
            'type' => mt_rand(0, 100) <= 60 ? 'stand' : 'hit'
        ];
    }

}
