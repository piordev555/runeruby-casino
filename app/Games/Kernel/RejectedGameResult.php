<?php namespace App\Games\Kernel;

class RejectedGameResult implements GameResult {

    private $errorCode;
    private $errorDescription;

    public function __construct(int $errorCode, string $errorDescription = 'Unknown error description') {
        $this->errorCode = $errorCode;
        $this->errorDescription = $errorDescription;
    }

    public function toArray(Data $data): array {
        return ['code' => $this->errorCode, 'message' => $this->errorDescription];
    }

}
