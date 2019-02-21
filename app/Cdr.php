<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cdr extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'cdr';
    protected $primaryKey = 'uniqueid';
}
