<?php namespace App\ActivityLog;

use App\AdminActivity;
use App\User;

abstract class ActivityLogEntry {

    public abstract function id();

    protected abstract function format(AdminActivity $data);

    public function display(AdminActivity $data) {
        $string = $this->format($data);
        $string =  preg_replace("/((?<=^|\s)@\w+)/", "<a class='ml-1 mr-1' href='javascript:void(0)' onclick=\"redirect('/admin/user/'+('$1').substr(1))\">$1</a>" , $string);
        return $string;
    }

    public function insert(array $data = [], $user = null) {
        AdminActivity::insert([
            'user' => $user == null ? auth('sanctum')->user()->_id : $user,
            'type' => $this->id(),
            'data' => $data,
            'time' => now()->timestamp
        ]);
    }

    public static function list(): array {
        return [
            new BanUnbanLog(),
            new ChatClearLog(),
            new MuteLog(),
            new BalanceChangeActivity(),
            new DisableGameActivity(),
            new GlobalNotificationLog()
        ];
    }

    public static function find(string $id): ?ActivityLogEntry {
        foreach(ActivityLogEntry::list() as $entry) if($entry->id() === $id) return $entry;
        return null;
    }

    public static function onlineUsers() {
        return User::where('latest_activity', '>=', now()->subMinutes(5))->get();
    }

}
