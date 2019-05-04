<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $table = 'manager';

    public $timestamps = false;

    protected $guarded = [];
}
