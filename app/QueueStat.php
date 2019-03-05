<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueueStat extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'queue_stats';
    protected $primaryKey = 'queue_stats_id';
}
