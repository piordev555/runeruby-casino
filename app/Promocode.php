<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Promocode extends Model {

    protected $connection = 'mongodb';
    protected $collection = 'promocodes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'used', 'currency', 'sum', 'usages', 'times_used', 'expires', 'vip'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'used' => 'json',
        'expires' => 'datetime'
    ];

    public static function generate() {
        return strtoupper(substr(str_shuffle(MD5(microtime())), 0, 8));
    }

}
