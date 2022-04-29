<?php namespace App\Games\Kernel\Module;

use App\Games\Kernel\Data;
use App\Games\Kernel\Game;
use App\Games\Kernel\Module\General\HouseEdgeModule;
use App\Games\Kernel\Module\General\LossProbabilityModule;
use App\Games\Kernel\Module\General\MinesModule;
use App\Games\Kernel\Module\General\MultiplierLimiterModule;
use App\Games\Kernel\Module\General\StairsModule;
use App\Games\Kernel\Module\General\TowerModule;
use App\Games\Kernel\ProvablyFairResult;

abstract class Module {

    protected ?Game $game = null;
    protected ?\App\Game $dbGame = null;
    protected ?ProvablyFairResult $result = null;
    protected ?Data $data = null;

    public function __construct(?Game $game, ?\App\Game $dbGame, ?Data $data, ?ProvablyFairResult $result) {
        $this->game = $game;
        $this->dbGame = $dbGame;
        $this->data = $data;
        $this->result = $result;
    }

    abstract function id(): string;

    abstract function name(): string;

    abstract function description(): string;

    /**
     * @return ModuleConfigurationOption[]
     */
    abstract function settings(): array;

    abstract function supports(): bool;

    abstract function lose(bool $demo): bool;

    protected function chance(float $chance): bool {
        if($chance > 100) $chance = 100;
        if($chance < 1) $chance = 1;
        return mt_rand(1, 100) <= $chance;
    }

    public static function modules(): array {
        return [
            MultiplierLimiterModule::class,
            HouseEdgeModule::class,
            LossProbabilityModule::class,

            MinesModule::class,
            StairsModule::class,
            TowerModule::class
        ];
    }

    public static function find(string $id): ?Module {
        foreach (self::modules() as $module) {
            $module = new $module(null, null, null, null);
            if($module->id() === $id) return $module;
        }
        return null;
    }

}
