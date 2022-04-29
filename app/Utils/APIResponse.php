<?php namespace App\Utils;

use Illuminate\Http\Response;

class APIResponse {

    public static function success(array $data = []): Response {
        return response($data, 200);
    }

    public static function reject(int $code, string $message = 'Unknown error message'): Response {
        return response(['code' => $code, 'message' => $message], 400);
    }

    public static function invalid2FASession() {
        return self::reject(-1024, 'Invalid 2FA session');
    }

}
