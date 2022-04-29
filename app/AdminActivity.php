<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class AdminActivity extends Model {

    protected $collection = 'admin_activities';
    protected $connection = 'mongodb';

    protected $fillable = [
        'user', 'type', 'data', 'time'
    ];

}
