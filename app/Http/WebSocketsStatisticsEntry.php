<?php

namespace App\Http;

use Jenssegers\Mongodb\Eloquent\Model;

class WebSocketsStatisticsEntry extends Model {

    protected $guarded = [];

    protected $connection = 'mongodb';

    protected $collection = 'websockets_statistics_entries';

}
