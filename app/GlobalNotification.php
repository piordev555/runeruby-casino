<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class GlobalNotification extends Model {

    protected $collection = 'global_notifications';
    protected $connection = 'mongodb';

    protected $fillable = [
        'icon', 'text'
    ];

}
