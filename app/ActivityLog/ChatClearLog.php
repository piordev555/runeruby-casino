<?php namespace App\ActivityLog;

use App\AdminActivity;

class ChatClearLog extends ActivityLogEntry {

    public function id() {
        return 'chat_clear';
    }

    protected function format(AdminActivity $data) {
        if($data->data['type'] === 'all') return 'Removed all messages in chat from @'.$data->data['id'];
        else return 'Removed message "'.(is_string($data->data['message']) ? $data->data['message'] : '(array)').'" from @'.$data->data['id'];
    }

}
