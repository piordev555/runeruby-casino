<?php namespace App\Currency\Local;

class OSRS extends LocalCurrency {

    function id(): string {
        return "local_osrs";
    }

    function walletId(): string {
        return "osrs";
    }

    function name(): string {
        return "OSRS";
    }

    function alias(): string {
        return "osrs";
    }

    function displayName(): string {
        return "OSRS";
    }

    function icon(): string {
        return "osrs";
    }

    protected function options(): array {
        return [];
    }

}
