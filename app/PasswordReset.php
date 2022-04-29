<?php namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PasswordReset extends Model {

    protected $connection = 'mongodb';
    protected $collection = 'password_resets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user', 'token'
    ];

}
