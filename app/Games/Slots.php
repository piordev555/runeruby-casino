<?php namespace App\Games;

use App\Games\Kernel\Data;
use App\Games\Kernel\Game;
use App\Games\Kernel\GameCategory;
use App\Games\Kernel\Metadata;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\Multiplayer\MultiplayerGame;
use App\Games\Kernel\ProvablyFair;
use App\Games\Kernel\ProvablyFairResult;
use App\Games\Kernel\Quick\QuickGame;
use App\Games\Kernel\Quick\SuccessfulQuickGameResult;
use App\Games\Kernel\RejectedGameResult;
use Illuminate\Support\Facades\Log;

class Slots extends QuickGame implements MultiplierCanBeLimited {

    private array $icons = ['apple', 'bananas', 'cherry', 'grapes', 'orange', 'pineapple', 'strawberry', 'watermelon', 'lemon', 'kiwi', 'raspberry', 'wild', 'scatter'];

    private array $payTable;

    private array $lines = [
        [1, 1, 1, 1, 1],
        [0, 0, 0, 0, 0],
        [2, 2, 2, 2, 2],
        [0, 1, 2, 1, 0],
        [2, 1, 0, 1, 2],
        [0, 0, 1, 2, 2],
        [2, 2, 1, 0, 0],
        [1, 0, 1, 2, 1],
        [1, 2, 1, 0, 1],
        [1, 0, 0, 1, 0],
        [1, 2, 2, 1, 2],
        [0, 1, 0, 0, 1],
        [2, 1, 2, 2, 1],
        [0, 2, 0, 2, 0],
        [2, 0, 2, 0, 2],
        [1, 0, 2, 0, 1],
        [1, 2, 0, 2, 1],
        [0, 1, 1, 1, 0],
        [2, 1, 1, 1, 2],
        [0, 2, 2, 2, 0]
    ];

    private int $scatterMultiplier = 1;

    public function __construct() {
        $this->payTable = [
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            $this->applyHouseEdge([0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 2, 10, 2]),
            $this->applyHouseEdge([5, 5, 5, 10, 10, 10, 15, 15, 25, 25, 50, 250, 5]),
            $this->applyHouseEdge([25, 25, 25, 50, 50, 50, 75, 75, 125, 125, 250, 2500, 0]),
            $this->applyHouseEdge([125, 125, 125, 250, 250, 250, 500, 500, 750, 750, 1250, 10000, 0])
        ];
    }

    function metadata(): Metadata {
        return new class extends Metadata {
            function id(): string {
                return "slots";
            }

            function name(): string {
                return "Slots";
            }

            function icon(): string {
                return "fab fa-raspberry-pi";
            }

            public function category(): array {
                return [GameCategory::$originals, GameCategory::$instant, GameCategory::$slots];
            }

            public function isPlaceholder(): bool {
                return true;
            }
        };
    }

    private function convertToView($result) {
        $out = [];
        foreach ($result as $item) {
            $column = [];
            foreach ($item as $value) array_push($column, array_search($value, $this->icons));
            array_push($out, $column);
        }
        return $out;
    }

    private function wildLineWin($singleLineBet, $line, $multiplierOnly = false) {
        $values = [11, 0, 0];
        if($line[0] != $values[0]) return $values;

        for($i = 0; $i < count($line); $i++) {
            if($line[$i] != $values[0]) break;
            $values[1]++;
        }

        $values[2] = $multiplierOnly
            ? $this->payTable[$values[1]][$values[0]]
            : $singleLineBet * $this->payTable[$values[1]][$values[0]];
        return $values;
    }

    private function lineWin($singleLineBet, $line, $multiplierOnly = false) {
        $wildWin = $this->wildLineWin($singleLineBet, $line, $multiplierOnly);
        $multiplier = 1;
        $symbol = $line[0];

        for($i = 0; $i < count($line); $i++) {
            if($line[$i] != 11) {
                if($line[$i] != 12) $symbol = $line[$i];
                break;
            }

            if($line[$i] == 12) break;

            if($i < count($line) - 1) $multiplier = 2;
            else if($i == count($line) - 1) $multiplier = 1;
        }

        /*
         * Wild symbol substitution. Other wild are artificial they are not part of the pay table.
         */
        for($i = 0; $i < count($line); $i++) {
            if($line[$i] == 11) {
                $line[$i] = $symbol;
                $multiplier = 2;
            }
        }

        /*
         * Count symbols in winning line.
         */
        $number = 0;
        for($i = 0; $i < count($line); $i++) {
            if($line[$i] == $symbol) $number++;
            else break;
        }

        /*
         * Clear unused symbols.
         */
        for($i = $number; $i < count($line); $i++) $line[$i] = -1;

        $win = $multiplierOnly
            ? $this->payTable[$number][$symbol] * $multiplier
            : $singleLineBet * $this->payTable[$number][$symbol] * $multiplier;
        if($win < $wildWin[2]) $win = $wildWin[1];

        return $win;
    }

    private function linesWin($lines, $singleLineBet, $view, $multiplierOnly = false) {
        $win = 0; $indexes = [];
        for($l = 0; $l < count($this->lines); $l++) {
            if($l + 1 > $lines) continue;

            $line = [-1, -1, -1, -1, -1];
            for($i = 0; $i < count($line); $i++) {
                $index = $this->lines[$l][$i];
                $line[$i] = $view[$i][$index];
            }

            $lineWin = $this->lineWin($singleLineBet, $line, $multiplierOnly);
            $win += $lineWin;

            if($lineWin > 0) array_push($indexes, $l + 1);
        }

        return [
            'profit' => $win,
            'indexes' => $indexes
        ];
    }

    private function scatterWin($totalBet, $view, $multiplierOnly = false) {
        $numberOfScatters = 0;
        for($i = 0; $i < count($view); $i++) {
            for($j = 0; $j < count($view[$i]); $j++) {
                if($view[$i][$j] == 12) $numberOfScatters++;
            }
        }

        return $multiplierOnly
            ? $this->payTable[$numberOfScatters][12] * $this->scatterMultiplier
            : $this->payTable[$numberOfScatters][12] * $totalBet * $this->scatterMultiplier;
    }

    function start($user, Data $data) {
        if(floatval(number_format($data->bet() / $data->game()->lines, 8, '.', '')) <= 0.00000000)
            return new RejectedGameResult(1, 'Invalid bet amount per line');

        return new SuccessfulQuickGameResult((new ProvablyFair($this))->result(), function (SuccessfulQuickGameResult $result, array $transformedResults) use ($user, $data) {
            $profit = $this->getProfit($data->game()->lines, $data->bet(), $data->bet() / $data->game()->lines, $this->convertToView($result->provablyFairResult()->result()));
            $result->delay(3500);
            $result->winCustom($user, $data, $profit['profit'], $profit['multiplier']);
            return [
                'result' => $result->provablyFairResult()->result(),
                'lines' => $profit['lines']
            ];
        });
    }

    function getProfit($lines, $totalBet, $singleLineBet, array $view): array {
        return [
            'profit' => $this->linesWin($lines, $singleLineBet, $view)['profit'] + $this->scatterWin($totalBet, $view),
            'multiplier' => $this->linesWin($lines, $singleLineBet, $view, true)['profit'] + $this->scatterWin($totalBet, $view, true),
            'lines' => $this->linesWin($lines, $singleLineBet, $view)['indexes']
        ];
    }

    function result(ProvablyFairResult $result): array {
        $output = [];
        $floats = $result->extractFloats(5 * 3);
        $total = 0;

        for($i = 0; $i < 5; $i++) {
            $column = [];
            for($j = 0; $j < 3; $j++) {
                array_push($column, $this->icons[floor($floats[$total] * count($this->icons))]);
                $total++;
            }
            array_push($output, $column);
        }

        return $output;
    }

    function isLoss(ProvablyFairResult $result, Data $data): bool {
        return $this->getProfit($data->game()->lines, $data->bet(), $data->bet()/ $data->game()->lines, $result->result())['multiplier'] <= 1;
    }

    public function multiplier(?\App\Game $game, ?Data $data, ProvablyFairResult $result): float {
        return $this->getProfit($data->game()->lines, $data->bet(), $data->bet()/$data->game()->lines, $result->result())['multiplier'];
    }

    public function getBotData(): array {
        return [
            'lines' => mt_rand(1, 20)
        ];
    }

}
