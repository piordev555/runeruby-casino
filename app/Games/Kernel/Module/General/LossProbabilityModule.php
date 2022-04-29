<?php namespace App\Games\Kernel\Module\General;

use App\Games\Crash;
use App\Games\Kernel\Module\Module;
use App\Games\Kernel\Module\ModuleConfigurationOption;
use App\Games\Kernel\Multiplayer\MultiplayerGame;
use App\Modules;

class LossProbabilityModule extends Module {

    function id(): string {
        return "loss_probability";
    }

    function name(): string {
        return "Static Loss %";
    }

    function description(): string {
        return "Additional static loss %.<br>Recommended only for *Quick games.";
    }

    function settings(): array {
        return [
            new class extends ModuleConfigurationOption {
                function id(): string {
                    return "static_percent";
                }

                function name(): string {
                    return "Loss %";
                }

                function description(): string {
                    return "Loss %";
                }

                function defaultValue(): ?string {
                    return "1";
                }

                function type(): string {
                    return "input";
                }
            }
        ];
    }

    function supports(): bool {
        return !($this->game instanceof MultiplayerGame);
    }

    function lose(bool $demo): bool {
        return $this->chance(floatval(Modules::get($this->game, $demo)->get($this, 'static_percent')));
    }

}
