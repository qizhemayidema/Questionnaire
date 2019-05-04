<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PollQuestion extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'poll_question';
}
