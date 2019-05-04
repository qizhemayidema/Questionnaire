<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Model\PollQuestion;
use App\Model\PollOption;
use App\Http\Controllers\Controller;
use View;

class BaseController extends Controller
{
    public function __construct()
    {
        View::share("__STATIC__","/static/admin/");
    }

    //上传图片
    public function uploadImg(Request $request)
    {
        $file = $request->file('file');
        $url_path = 'static/admin/image';
        $rule = ['jpg', 'png', 'gif'];
        if ($file->isValid()) {
            $clientName = $file->getClientOriginalName();
            $tmpName = $file->getFileName();
            $realPath = $file->getRealPath();
            $entension = $file->getClientOriginalExtension();
            if (!in_array($entension, $rule)) {
                return '图片格式为jpg,png,gif';
            }
            $newName = md5(date("Y-m-d H:i:s") . $clientName) . "." . $entension;
            $path = $file->move($url_path, $newName);
            $namePath = $url_path . '/' . $newName;
;
            return json_encode(['code' => 1, 'data' => '/' . $namePath]);
        }
    }

    //组合问题与选项成一个数组返回 依照排序
    public function margeQuestionOption($poll_id,$question = null,$option = null)   //如果问题与选项没有则直接查
    {
        if (!$question && !$option){
            //查询投票问题
            $question = PollQuestion::where(['poll_id'=>$poll_id])->orderBy('question_sort_num')->get()->toArray();

            //查询投票选项
            $option = PollOption::where(['poll_id'=>$poll_id])->orderBy('option_sort_num')->get()->toArray();
        }
        foreach ($question as $key => &$value){
            foreach ($option as $key1 => $value1){
                if ($value1['question_id'] == $value['id']){
                    $value['option'][] = $value1;
                }
            }
        }
        unset($value);
        return $question;
    }
}
