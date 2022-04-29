<?php namespace App\Currency\Local;

use App\Currency\Currency;

/**
 * Not a cryptocurrency, used alongside with a Aggregator to process payments
 * @package App\Currency\Local
 */
abstract class LocalCurrency extends Currency {

     function style(): string {
        return 'lightgray';
     }

     function newWalletAddress(): string {
         return 'Unsupported operation';
     }

     function isRunning(): bool {
        return true;
     }

     function process(string $wallet = null) {
     }

     function send(string $from, string $to, float $sum) {
     }

     function setupWallet() {
     }

     function coldWalletBalance(): float {
        return -1;
     }

     function hotWalletBalance(): float {
        return -1;
     }

     public function minBet(): float {
         return 1000;
     }

     public function tokenPrice() {
         return 1;
     }


}
