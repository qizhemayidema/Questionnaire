<?php

namespace App\Http\Controllers\Index;

use App\Model\Poll;
use App\Model\PollQuestion;
use App\Model\PollOption;
use App\Model\User;
use App\Model\UserFileUpload;
use App\Model\UserInputAnswer;
use App\Model\UserHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class PollController extends BaseController
{
    //获取投票列表
    public function pollList(Poll $poll)
    {
        $time = time();
        $data = $poll::where('start_time','<',$time)->where('end_time','>',$time)->where(['is_on'=>1])->select(['id','title','desc','start_time','end_time'])->limit(10)->get();
        return json_encode(['code'=>1,'msg'=>$data],256);
    }

    //某个投票页面
    public function index(Request $request,Poll $poll)
    {
        $open_id = $request->get('open_id');
        $user_info = User::where(['open_id'=>$open_id])->first();
        //判断投票状态;
        if ($poll->is_on != 1) return json_encode(['code'=>0,'msg'=>'error'],256);
        //判断当前用户今日投票次数
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + 86400;
        $poll_num = UserHistory::where(['user_id'=>$user_info['id']])->where(['poll_id'=>$poll['id']])->where('create_time','>',$start_time)->where('create_time','<',$end_time)->count();

        if ($poll_num >= $poll->select_num_day){
            return json_encode(['code'=>0,'msg'=>'您今天问答次数已达上限，明天再来吧'],256);
        }
        $question = $this->margeQuestionOption($poll->id);
//        return view('index.poll.index',compact('poll','question'));
        return json_encode(['code'=>1,'msg'=>['poll'=>$poll,'question'=>$question]],256);
    }

    public function test(Request $request,Poll $poll)
    {
        $open_id = $request->get('open_id');
        $user_info = User::where(['open_id'=>$open_id])->first();
        //判断投票状态;
        if ($poll->is_on != 1) return json_encode(['code'=>0,'msg'=>'error'],256);
        //判断当前用户今日投票次数
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + 86400;
        $poll_num = UserHistory::where(['user_id'=>$user_info['id']])->where(['poll_id'=>$poll['id']])->where('create_time','>',$start_time)->where('create_time','<',$end_time)->count();

        if ($poll_num >= $poll->select_num_day){
            return json_encode(['code'=>0,'msg'=>'您今天问答次数已达上限，明天再来吧'],256);
        }
        $question = $this->margeQuestionOption($poll->id);
        return view('index.poll.index',compact('poll','question'));
//        return json_encode(['code'=>1,'msg'=>['poll'=>$poll,'question'=>$question]],256);
    }

    //用户投票动作
    public function userPoll(Request $request)
    {
        $open_id = $request->all('open_id');
        $user_info = User::where(['open_id'=>$open_id])->first();
        $user_id = $user_info['id'];

        $result = $request->all('result')['result'];
        $poll_id = $request->all('poll_id')['poll_id'];

        $poll_data = Poll::where(['id'=>$poll_id])->first();

        //判断当前用户今日投票次数
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + 86400;
        $poll_num = UserHistory::where(['user_id'=>$user_info['id']])->where(['poll_id'=>$poll_id])->where('create_time','>',$start_time)->where('create_time','<',$end_time)->count();

        if ($poll_num >= $poll_data->select_num_day){
            return json_encode(['code'=>0,'msg'=>'您今天问答次数已达上限，明天再来吧'],256);
        }
        //根据poll_id查询所有问题
        $question_data = PollQuestion::where(['poll_id' => $poll_id])->get()->toArray();
        DB::beginTransaction();
        try {
            $upload_data = [];  //上传要新增的数据
            $input_data = [];   //填空要新增的数据
            foreach ($question_data as $key => $value) {
                $question_type = $value['question_type'];
                //首先验证是否必填
                if ($value['is_must'] == 1) {
                    if ($value['question_type'] == 1) {
                        if (!isset($result[$value['id']])) throw new Exception('第' . $value['question_sort_num'] . '题为必填项');
                    } elseif ($value['question_type'] == 2) {
                        if (!isset($result[$value['id']])) throw new Exception('第' . $value['question_sort_num'] . '题为必填项');
                    } elseif ($value['question_type'] == 3) {
                        foreach ($result[$value['id']] as $key1 => $value1) {
                            if (!$value1) throw new Exception('第' . $value['question_sort_num'] . '题为必填项');
                        }
                    } elseif ($value['question_type'] == 4) {
                        foreach ($_FILES['file']['name'][$value['id']] as $key1 => $value1) {
                            if (!$value1) throw new Exception('第' . $value['question_sort_num'] . '题为必须上传文件');
                        }
                    }
                }
                //验证多选数量
                if ($value['question_type'] == 2 && isset($result[$value['id']])) {
                    if (count($result[$value['id']]) > $value['select_num']) {
                        throw new Exception('第' . $value['question_sort_num'] . '题多选最大数量为' . $value['select_num']);
                    }
                }
                //判断上传文件类型是否合法
                if ($value['question_type'] == 4 && isset($result[$value['id']])) {
                    $upload_minetype = PollOption::where(['poll_id' => $poll_id])->where(['question_id' => $value['id']])->pluck('upload_minetype', 'id');
                    $request_file = $request->file();
                    $file_index = 1;
                    foreach ($request_file['file'][$value['id']] as $key1 => $file) {
                        $fileTypes = explode(',', $upload_minetype[$key1]);
                        if ($fileTypes[0] != '') {
                            //获取文件类型后缀
                            $extension = $file->getClientOriginalExtension();
                            //是否是要求的文件
                            $isInFileType = in_array($extension, $fileTypes);
                            if (!$isInFileType) {
                                throw new Exception('第' . $value['question_sort_num'] . '题第' . $file_index . '问只能上传' . $upload_minetype[$key1] . '文件');
                            }
                        }
                        $file_index++;
                    }
                }
                //入库
                if ($question_type == 1 && isset($result[$value['id']])){   //如果是单选     则 value就是选中的值
                    PollOption::where(['id' => $result[$value['id']]])->increment('num');
                    PollQuestion::where(['id' => $value['id']])->increment('user_select_num');
                }
                elseif(isset($result[$value['id']])){
                    $user_select_num = 0;
                    foreach ($result[$value['id']] as $key1 => $value1){    //key1 option 的id
                        if ($question_type == 2){       //多选
                            if ($value1){
                                PollOption::where(['id'=>$key1])->increment('num');
                                $user_select_num ++;
                            }
                        }elseif ($question_type == 3){      //填空
                            if ($value1){
                                $input_data[] = [
                                    'user_id' => $user_id,
                                    'option_id' => $key1,
                                    'answer' => $value1,
                                ];
                            }
                        }
                    }
                    //用户单选 or 多选总量
                    if ($question_type == 2 && $user_select_num != 0) PollQuestion::where(['id' => $value['id']])->increment('user_select_num', $user_select_num);
                }
            }
            //上传文件的入库独立出来
            if (!empty($request->file())) {
                foreach ($request->file()['file'] as $key1 => $value1) {    //$key1 为 question的id
                    foreach ($value1 as $key2 => $value2) {     //$key2 为 optionid  $value2 为file对象
                        $file_path = $this->uploadFile($value2);
                        if (!$file_path){
                            throw new Exception('操作非法');
                        }
                        //存入上传的数组中
                        $upload_data[] = [
                            'user_id'       => $user_id,
                            'option_id'     => $key2,
                            'file_url'      => $file_path,
                        ];
                    }
                }
                UserFileUpload::insert($upload_data);
            }
            if (!empty($input_data)) UserInputAnswer::insert($input_data);

            //新增用户问答历史记录
            UserHistory::insert([
                'user_id'   => $user_id,
                'poll_id'   => $poll_id,
                'create_time' => time(),
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return json_encode(['code' => 0, 'msg' => $e->getMessage()], 256);
        }

        return json_encode(['code' => 1, 'msg' => 'success'], 256);
    }
}
