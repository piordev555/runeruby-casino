<?php namespace App\ActivityLog;

use App\AdminActivity;

class DisableGameActivity extends ActivityLogEntry {

    public function id() {
        return "disable";
    }

    protected function format(AdminActivity $data) {
        return ($data->data['state'] ? 'Disabled' : 'Enabled').' game "'.$data->data['api_id'].'"';
    }

}
