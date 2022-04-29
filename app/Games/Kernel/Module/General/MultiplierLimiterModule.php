<?php namespace App\Games\Kernel\Module\General;

use App\Games\Kernel\Data;
use App\Games\Kernel\Game;
use App\Games\Kernel\Module\Module;
use App\Games\Kernel\Module\General\Wrapper\MultiplierCanBeLimited;
use App\Games\Kernel\Module\ModuleConfigurationOption;
use App\Games\Kernel\ProvablyFairResult;
use App\Modules;

class MultiplierLimiterModule extends Module {

    function id(): string {
        return "multiplier_limiter";
    }

    function name(): string {
        return "Payout limit";
    }

    function description(): string {
        return "Lose game if payout reaches specific value.<br>It's possible to generate more real results.";
    }

    function supports(): bool {
        return $this->game instanceof MultiplierCanBeLimited;
    }

    function lose(bool $demo): bool {
        return Modules::get($this->game, $demo)->get($this, 'pass') !== '-1' && $this->chance(floatval(Modules::get($this->game, $demo)->get($this, 'pass'))) ? false
            : $this->game->multiplier($this->dbGame, $this->data, $this->result) > floatval(Modules::get($this->game, $demo)->get($this, 'multiplier'));
    }

    function settings(): array {
        return [
            new class extends ModuleConfigurationOption {
                function id(): string {
                    return 'multiplier';
                }

                function name(): string {
                    return "Max payout";
                }

                function description(): string {
                    return "Instantly lose if payout reaches specific value.";
                }

                function defaultValue(): ?string {
                    return "100.00";
                }

                function type(): string {
                    return "input";
                }
            },
            new class extends ModuleConfigurationOption {
                public function id(): string {
                    return 'pass';
                }

                function name(): string {
                    return '% that this module won\'t work';
                }

                function description(): string {
                    return 'Use -1 to disable.';
                }

                function defaultValue(): ?string {
                    return '-1';
                }

                function type(): string {
                    return 'input';
                }
            }
        ];
    }

}
