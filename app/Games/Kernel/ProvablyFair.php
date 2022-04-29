<?php namespace App\Games\Kernel;

class ProvablyFair {

    private $game;
    private $client_seed;
    private $server_seed;
    private $nonce;

    public function __construct(Game $game, string $server_seed = null) {
        $this->game = $game;
        $this->client_seed = $game->client_seed();
        $this->server_seed = $server_seed == null ? $game->server_seed() : $server_seed;
        $this->nonce = $game->nonce();
    }

    public static function generateServerSeed(): string {
        $length = 32;
        $bytes = random_bytes(ceil($length / 2));
        return substr(bin2hex($bytes), 0, $length);
    }

    public function result(): ProvablyFairResult {
        return new class($this->game, $this->client_seed, $this->server_seed, $this->nonce) extends ProvablyFairResult {

            private $server_seed;
            private $client_seed;
            private $nonce;
            private $results;

            public function __construct(Game $game, $client_seed, $server_seed, $nonce) {
                $this->server_seed = $server_seed;
                $this->client_seed = $client_seed;
                $this->nonce = $nonce;

                $this->results = $game->result($this);
            }

            public function extractFloats($count): array {
                $generator = $this->byteGenerator();
                $bytes = [];
                for($i = 0; $i < $count * 4; $i++) {
                    array_push($bytes, $generator->current());
                    $generator->next();
                }

                $chunks = array_chunk($bytes, 4);

                $result = [];
                array_map(function($byte) use(&$result) {
                    $index = -1;
                    array_push($result, array_reduce($byte, function($result, $value) use(&$index) {
                        $index++;
                        return $result + ($value / (256 ** ($index + 1)));
                    }, 0));
                }, $chunks);

                return $result;
            }

            public function extractFloat(): float {
                return $this->extractFloats(1)[0];
            }

            private function byteGenerator() {
                $currentRound = 0;
                while(true) {
                    $hash = hash_init('sha256', HASH_HMAC, $this->server_seed);
                    hash_update($hash, "{$this->client_seed}:{$this->nonce}:{$currentRound}");

                    $final = hash_final($hash);

                    $currentRound++;
                    $buffer = $this->digestSHA256($final);
                    for($i = 0; $i < 32; $i++) yield $buffer[$i];
                }
            }

            private function digestSHA256($hash) {
                $chunks = str_split($hash, 8);

                for($i = 0; $i < count($chunks); $i++) $chunks[$i] = hexdec($chunks[$i]);

                $h0 = $chunks[0]; $h1 = $chunks[1]; $h2 = $chunks[2]; $h3 = $chunks[3]; $h4 = $chunks[4]; $h5 = $chunks[5];
                $h6 = $chunks[6]; $h7 = $chunks[7];

                $arr = [
                    ($h0 >> 24) & 0xFF, ($h0 >> 16) & 0xFF, ($h0 >> 8) & 0xFF, $h0 & 0xFF,
                    ($h1 >> 24) & 0xFF, ($h1 >> 16) & 0xFF, ($h1 >> 8) & 0xFF, $h1 & 0xFF,
                    ($h2 >> 24) & 0xFF, ($h2 >> 16) & 0xFF, ($h2 >> 8) & 0xFF, $h2 & 0xFF,
                    ($h3 >> 24) & 0xFF, ($h3 >> 16) & 0xFF, ($h3 >> 8) & 0xFF, $h3 & 0xFF,
                    ($h4 >> 24) & 0xFF, ($h4 >> 16) & 0xFF, ($h4 >> 8) & 0xFF, $h4 & 0xFF,
                    ($h5 >> 24) & 0xFF, ($h5 >> 16) & 0xFF, ($h5 >> 8) & 0xFF, $h5 & 0xFF,
                    ($h6 >> 24) & 0xFF, ($h6 >> 16) & 0xFF, ($h6 >> 8) & 0xFF, $h6 & 0xFF
                ];

                array_push($arr, ($h7 >> 24) & 0xFF, ($h7 >> 16) & 0xFF, ($h7 >> 8) & 0xFF, $h7 & 0xFF);
                return $arr;
            }

            function server_seed(): string {
                return $this->server_seed;
            }

            function nonce(): int {
                return $this->nonce;
            }

            function result(): array {
                return $this->results;
            }

        };
    }

}

abstract class ProvablyFairResult {

    abstract function extractFloats($count): array;
    abstract function extractFloat(): float;

    abstract function server_seed(): string;
    abstract function nonce(): int;
    abstract function result(): array;

}
