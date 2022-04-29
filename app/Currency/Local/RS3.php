<?php namespace App\Currency\Local;

class RS3 extends LocalCurrency {

    function id(): string {
        return "local_rs3";
    }

    function walletId(): string {
        return "rs3";
    }

    function name(): string {
        return "RS3";
    }

    function alias(): string {
        return "rs3";
    }

    function displayName(): string {
        return 'RS3';
    }

    function icon(): string {
        return "rs3";
    }

    protected function options(): array {
        return [];
    }

}
