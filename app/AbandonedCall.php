<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AbandonedCall extends Model
{
    protected $fillable = ['number', 'status', 'abandontime', 'queue', 'originalposition', 'position', 'holdtime', 'uniqueid', 'callbacktime'];
}
