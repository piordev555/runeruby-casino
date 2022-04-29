<?php namespace App\ActivityLog;

use App\AdminActivity;

class BanUnbanLog extends ActivityLogEntry {

    public function id() {
        return "ban";
    }

    protected function format(AdminActivity $data) {
        if($data->data['type'] === 'ban') return 'Banned user @'.$data->data['id'];
        else return 'Unbanned user @'.$data->data['id'];
    }

}
