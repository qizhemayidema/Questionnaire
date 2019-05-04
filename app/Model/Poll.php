<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\PollOption;
use Illuminate\Support\Facades\Request;

class Poll extends Model
{
    protected $table = 'poll';

    public $timestamps = false;

    protected $guarded = [];

    //修正状态
    public static function checkStatus($poll_id,$poll_info = null)
    {
        if (!$poll_info){
            $poll_info = self::find($poll_id);
        }
        //根据时间检查 如果不在投票时间范围内则改变状态
        if ($poll_info['start_time'] > time()){
            $poll_info->is_on = 3;
            $poll_info->save();
        }elseif($poll_info['end_time'] < time()){
            $poll_info->is_on = 2;
            $poll_info->save();
        }elseif($poll_info['start_time'] < time() && $poll_info['end_time'] > time() && $poll_info['is_on'] != 0){
            $poll_info->is_on = 1;
            $poll_info->save();
        }
        //返回的是修改后的结果集
        return $poll_info;
    }
}
