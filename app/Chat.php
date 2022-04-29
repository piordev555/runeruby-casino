<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Chat extends Model {

    protected $connection = 'mongodb';
    protected $collection = 'chat_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user', 'data', 'type', 'deleted', 'vipLevel', 'channel', 'user_id'
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
        'user' => 'json'
    ];

}
