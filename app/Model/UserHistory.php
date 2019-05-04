<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'user_history';
}
