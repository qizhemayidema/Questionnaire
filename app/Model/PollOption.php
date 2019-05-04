<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    protected $table = 'poll_option';

    protected $guarded = [];

    public $timestamps = false;

    public function getPollOptionInfo($poll_id)     //查找某个投票下的选项
    {
        //查找选项数据
        return $this->where(['poll_id'=>$poll_id])->get()->toArray();
    }
}
