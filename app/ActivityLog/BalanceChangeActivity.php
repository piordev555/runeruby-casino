<?php namespace App\ActivityLog;

use App\AdminActivity;

class BalanceChangeActivity extends ActivityLogEntry {

    public function id() {
        return "balance";
    }

    protected function format(AdminActivity $data) {
        return 'Changed balance of @'.$data->data['id'].': '.$data->data['balance'].' '.$data->data['currency'];
    }

}
