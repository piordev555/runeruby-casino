<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Currency extends Model {

    protected $collection = 'currencies';
    protected $connection = 'mongodb';

    protected $fillable = [
        'currency', 'data'
    ];

    protected $casts = [
        'data' => 'json'
    ];

}
