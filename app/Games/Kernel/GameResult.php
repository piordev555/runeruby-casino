<?php namespace App\Games\Kernel;

interface GameResult {

    function toArray(Data $data): array;

}
