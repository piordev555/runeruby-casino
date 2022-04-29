<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SupportChat extends Model {

    protected $connection = 'mongodb';
    protected $collection = 'support_chats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user', 'status', 'messages', 'user_read', 'support_read'
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
        'messages' => 'json'
    ];

}
