<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserInputAnswer extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'user_input_answer';
}
