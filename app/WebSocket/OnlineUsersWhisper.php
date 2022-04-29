<?php namespace App\WebSocket;

class OnlineUsersWhisper extends WebSocketWhisper {

    public function event(): string {
        return 'OnlineUsers';
    }

    public function process($data): array {
        $users = [];
        foreach (\App\ActivityLog\ActivityLogEntry::onlineUsers() as $user) array_push($users, $user->name);
        return $users;
    }

}
