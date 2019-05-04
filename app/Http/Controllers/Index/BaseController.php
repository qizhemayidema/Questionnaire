<?php

namespace App\Http\Controllers\Index;

use App\Model\PollQuestion;
use App\Model\PollOption;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Index\WeChatAuthController;

use View;

class BaseController extends Controller
{
    protected $redirect_url = 'xxx.xxx.com';

    public function __construct(Request $request)
    {
        //判断用户是否登录
        $user_info = Session::get('index.user_info');
        if (!$user_info){
//            //获取授权
//            $weChatController = new WeChatAuthController();
//            $weChatController->snsapi_base($this->redirect_url);
//            die;
            //判断openid是否存在
        }

        View::share("__STATIC__", "/static/index/");
    }


    //组合问题与选项成一个数组返回 依照排序
    public function margeQuestionOption($poll_id, $question = null, $option = null)   //如果问题与选项没有则直接查
    {
        if (!$question && !$option) {
            //查询投票问题
            $question = PollQuestion::where(['poll_id' => $poll_id])->orderBy('question_sort_num')->get()->toArray();

            //查询投票选项
            $option = PollOption::where(['poll_id' => $poll_id])->orderBy('option_sort_num')->get()->toArray();
        }
        foreach ($question as $key => &$value) {
            foreach ($option as $key1 => $value1) {
                if ($value1['question_id'] == $value['id']) {
                    $value['option'][] = $value1;
                }
            }
        }
        unset($value);
        return $question;
    }

    //上传文件
    public function uploadFile($file)       //通过request获取的文件对象
    {
        $url_path = 'static/index/userUpload';
        if ($file->isValid()) {
            $clientName = $file->getClientOriginalName();
            $tmpName = $file->getFileName();
            $realPath = $file->getRealPath();
            $entension = $file->getClientOriginalExtension();
            $newName = md5(date("Y-m-d H:i:s") . $clientName) . "." . $entension;
            $path = $file->move($url_path, $newName);
            $namePath = $url_path . '/' . $newName;
            return  '/' . $namePath;
        }else{
            return false;
        }
    }
}
