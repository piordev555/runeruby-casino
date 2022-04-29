<?php namespace App\ActivityLog;

use App\AdminActivity;

class GlobalNotificationLog extends ActivityLogEntry {

    public function id() {
        return "global_notification";
    }

    protected function format(AdminActivity $data) {
        return ($data->data['state'] ? 'Created' : 'Removed').' global notification ("'.$data->data['text'].'"/"'.$data->data['icon'].'")';
    }

}
