<?php namespace App\Currency\Option;

abstract class WalletOption {

    abstract function id();

    abstract function name(): string;

    function readOnly(): bool {
        return false;
    }

}
