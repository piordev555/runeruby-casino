<?php namespace App\Games\Kernel\Module;

abstract class ModuleConfigurationOption {

    abstract function id(): string;

    abstract function name(): string;

    abstract function description(): string;

    abstract function defaultValue(): ?string;

    /**
     * Supported values:
     *  - input
     * @return string
     */
    abstract function type(): string;

}
