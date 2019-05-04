<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserFileUpload extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'user_file_upload';
}
