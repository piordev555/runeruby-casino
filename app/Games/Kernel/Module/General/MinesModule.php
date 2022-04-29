<?php namespace App\Games\Kernel\Module\General;

use App\Games\Crash;
use App\Games\Kernel\Module\Module;
use App\Games\Kernel\Module\ModuleConfigurationOption;
use App\Games\Mines;
use App\Modules;

class MinesModule extends Module {

    function id(): string {
        return 'mines';
    }

    function name(): string {
        return 'Mines-specific';
    }

    function description(): string {
        return 'This module lets you configure static loss % for every game mode (2-24 mines).';
    }

    function settings(): array {
        $settings = [];
        for($mines = 2; $mines <= 24; $mines++)
            array_push($settings, new class($mines) extends ModuleConfigurationOption {
                private $mines;

                public function __construct($mines) {
                    $this->mines = $mines;
                }

                function id(): string {
                    return 'mines_'.$this->mines;
                }

                function name(): string {
                    return 'Number of mines: '.$this->mines;
                }

                function description(): string {
                    return 'Loss % with '.$this->mines.' mine(s) in the field';
                }

                function defaultValue(): ?string {
                    return '1';
                }

                function type(): string {
                    return 'input';
                }
            });
        return $settings;
    }

    function supports(): bool {
        return $this->game instanceof Mines;
    }

    function lose(bool $demo): bool {
        return $this->chance(floatval(Modules::get($this->game, $demo)->get($this, 'mines_'.$this->game->getModuleData($this->dbGame))));
    }

}
