<?php

namespace App\Http\Controllers\Admin;

use App\Model\Poll;
use App\Model\PollOption;
use App\Model\PollQuestion;
use Illuminate\Http\Request;
use App\Model\UserInputAnswer;
use App\Model\UserFileUpload;
use App\Http\Controllers\Controller;
use Mockery\Exception;
use Validator;
use DB;

//投票管理
class PollController extends BaseController
{
    protected $page_num = 15;

    public function index()
    {
        $poll_info = Poll::select(['id','title','desc','is_on','select_num_day','start_time','end_time'])->orderBy('id','desc')->paginate($this->page_num);
        return view('admin.poll.index',compact('poll_info'));
    }

    public function add()
    {
        return view('admin.poll.add');
    }

    public function addChange(Request $request)
    {
        $data = $request->all();

        $rule = [
            'title'         => 'required',
            'select_num_day'=> 'required|integer',
            'desc'          => 'required',
            'desc_position' => 'required|integer',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'question_title'=> 'required',
            'option_name'   => 'required',
        ];

        $message = [
            'title.required'            => '投票标题必须填写',
            'select_num_day.required'   => '用户每天作答次数必须填写',
            'select_num_day.integer'    => '用户每天作答次数必须为数字',
            'desc.required'             => '说明必须填写',
            'desc_position'             => '说明位置必须选择',
            'start_time.required'       => '开始时间必须填写',
            'end_time.required'         => '结束时间必须填写',
            'question_title.required'   => '必须至少有一个问题',
            'option_name.required'      => '必须至少有一个选项',
        ];
        $validator = Validator::make($data,$rule,$message);
        if ($validator->fails()){
            return json_encode(['code'=>0,'msg'=>$validator->errors()->first()],256);
        }
        DB::beginTransaction();
        try{
            //处理数据
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            if ($data['start_time'] > time()){
                $data['is_on'] = 3;
            }elseif ($data['end_time'] < time()){
                $data['is_on'] = 2;
            }else{
                $data['is_on'] = 1;
            }
            //生成poll数据
            $poll_data = [
                'title' => $data['title'],
                'desc'  => $data['desc'],
                'desc_position' => $data['desc_position'],
                'is_on'         => $data['is_on'],
                'select_num_day'    => $data['select_num_day'],
                'start_time'    => $data['start_time'],
                'end_time'      => $data['end_time']
            ];
            $poll = Poll::create($poll_data);
            $poll_id = $poll->id;   //问卷id
            foreach ($data['question_title'] as $key => $value){    //key为问题的索引
                //生成poll_question数据
                $question_data = [
                    'poll_id'   => $poll_id,
                    'question_sort_num' => $data['question_sort_num'][$key],
                    'question_title'    => $data['question_title'][$key],
                    'question_type'     => $data['question_type'][$key],
                    'is_must'           => $data['is_must'][$key],
                ];
                //根据选择的问题类型不一样进行判断
                if ($data['question_type'][$key] == 2){   //多选
                    if (!$data['select_num'][$key] || !is_numeric($data['select_num'][$key]) || $data['select_num'] < 0){
                        return json_encode(['code'=>0,'msg'=>'第'.$data['question_sort_num'].'个问题的投票多选数量必须为数字且大于等于0']);
                    }
                    $question_data['select_num'] = $data['select_num'][$key];
                }
                $question = PollQuestion::create($question_data);
                $question_id = $question->id;
                $option_data = [];  //问卷问题选项表
                foreach ($data['option_name'][$key] as $key1 => $value1){   // $key1为当前循环的问题的选项
                    //生成poll_option数据
                    $option_temp = [
                        'poll_id'   => $poll_id,
                        'question_id' => $question_id,
                        'option_sort_num' => $data['option_sort_num'][$key][$key1],
                        'is_default'    => $data['is_default'][$key][$key1] ?? 0,
                        'upload_minetype' => $data['upload_minetype'][$key][$key1] ?? '',
                        'option_name'   => $data['option_name'][$key][$key1] ?? '',
                        'option_img'    => $data['option_img'][$key][$key1] ?? '',
                    ];
                    $option_data[] = $option_temp;
                }
                PollOption::insert($option_data);
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            return json_encode(['code'=>0,'msg'=>$e->getMessage()],256);
        }
        return json_encode(['code'=>1,'msg'=>'success']);
    }

    public function edit(Poll $poll_id)
    {
        //判断是否可以编辑 状态不为1 则表示都可以编辑
        $poll_info = Poll::checkStatus($poll_id['id'],$poll_id);
        if ($poll_info['is_on'] == 1){
            die("<script>alert('投票进行时无法更改')</script>");
        }
        //取出问题与选项数据
        //查询投票问题
        $question_data = PollQuestion::where(['poll_id'=>$poll_id])->orderBy('question_sort_num')->get()->toArray();

        //查询投票选项
        $option = PollOption::where(['poll_id'=>$poll_id])->orderBy('option_sort_num')->get()->toArray();
        //融合数据
        $question = $this->margeQuestionOption($poll_info['id'],$question_data,$option);

        return view('admin.poll.edit',compact('poll_info','question'));
    }

    public function editChange(Request $request)
    {
        $data = $request->all();
//        return json_encode($data,true);
        $rule = [
            'poll_id'       => 'required',
            'title'         => 'required',
            'select_num_day'=> 'required|integer',
            'desc'          => 'required',
            'desc_position' => 'required|integer',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'question_title'=> 'required',
            'option_name'   => 'required',
        ];

        $message = [
            'poll_id.required'          => '操作非法1',
            'title.required'            => '投票标题必须填写',
            'select_num_day.required'   => '用户每天作答次数必须填写',
            'select_num_day.integer'    => '用户每天作答次数必须为数字',
            'desc.required'             => '说明必须填写',
            'desc_position'             => '说明位置必须选择',
            'start_time.required'       => '开始时间必须填写',
            'end_time.required'         => '结束时间必须填写',
            'question_title.required'   => '必须至少有一个问题',
            'option_name.required'      => '必须至少有一个选项',
        ];
        $validator = Validator::make($data,$rule,$message);
        if ($validator->fails()){
            return json_encode(['code'=>0,'msg'=>$validator->errors()->first()],256);
        }

        DB::beginTransaction();
        try{
            //处理数据
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            //生成将要修改的poll数据
            $poll_data = [
                'title' => $data['title'],
                'desc'  => $data['desc'],
                'desc_position' => $data['desc_position'],
                'select_num_day'    => $data['select_num_day'],
                'start_time'    => $data['start_time'],
                'end_time'      => $data['end_time']
            ];
            Poll::where(['id'=>$data['poll_id']])->update($poll_data);
            $poll_id = $data['poll_id'];   //问卷id
            $update_question_ids = [];      //将要修改的问题
            $update_option_ids = [];    //此问题下将要修改的选项
            if (isset($data['question_id'])){
                foreach ($data['question_id'] as $key => $value){       //value 为 question_id
                    $update_question_ids[] = $value;
                    foreach ($data['option_name'][$key] as $key1 => $value1){
                        if (isset($data['option_id'][$key][$key1])){        //说明是老数据 要修改的
                            $update_option_ids[] = $data['option_id'][$key][$key1];
                            //生成将要修改的option数据
                            $update_option_data = [
                                'option_sort_num'    => $data['option_sort_num'][$key][$key1],
                                'is_default'         => $data['is_default'][$key][$key1] ?? 0,
                                'upload_minetype'    => $data['upload_minetype'][$key][$key1] ?? '',
                                'option_name'        => $data['option_name'][$key][$key1] ?? '',
                                'option_img'         => $data['option_img'][$key][$key1] ?? '',
                            ];
                            PollOption::where(['id'=>$data['option_id'][$key][$key1]])->update($update_option_data);
                        }else{      //说明是新增的选项
                            $old_question_new_option_data = [
                                'poll_id'   => $poll_id,
                                'question_id' => $value,
                                'option_sort_num'    => $data['option_sort_num'][$key][$key1],
                                'is_default'         => $data['is_default'][$key][$key1] ?? 0,
                                'upload_minetype'    => $data['upload_minetype'][$key][$key1] ?? '',
                                'option_name'        => $data['option_name'][$key][$key1] ?? '',
                                'option_img'         => $data['option_img'][$key][$key1] ?? '',
                            ];
                            $old_question_new_option = PollOption::create($old_question_new_option_data);
                            $update_option_ids[] = $old_question_new_option->id;
                        }
                        unset($data['option_name'][$key][$key1]);
                    }
                    //查询投票总数量 为问题得用户投票总量修改用
                    if (isset($data['option_id'][$key])){
                        $update_question_data['user_select_num'] = array_sum(PollOption::where(['poll_id'=>$poll_id])->where(['question_id'=>$value])->pluck('num')->toArray());
                    }
                    //删除此选项没有的option数据
                    PollOption::where(['poll_id'=>$poll_id])->where(['question_id'=>$value])->whereNotIn('id',$update_option_ids)->delete();

                    //生成将要修改的question数据 表里存在的
                    $update_question_data['question_sort_num'] = $data['question_sort_num'][$key];
                    $update_question_data['question_title'] = $data['question_title'][$key] ?? '';
                    $update_question_data['question_type'] = $data['question_type'][$key];
                    $update_question_data['is_must'] = $data['is_must'][$key];

                    //根据选择的问题类型不一样进行判断
                    if ($data['question_type'][$key] == 2){   //多选
                        if (!$data['select_num'][$key] || !is_numeric($data['select_num'][$key]) || $data['select_num'] < 0){
                            throw new Exception('第'.$data['question_sort_num'].'个问题的投票多选数量必须为数字且大于等于0');
                        }
                        $update_question_data['select_num'] = $data['select_num'][$key];
                    }else{
                        $update_question_data['select_num'] = 0;
                    }
                    PollQuestion::where(['id'=>$value])->update($update_question_data);
                    unset($data['question_title'][$key]);
                }
            }
            //删除没有的question数据
            PollQuestion::where(['poll_id'=>$poll_id])->whereNotIn('id',$update_question_ids)->delete();
            //新增新的questin数据
            foreach ($data['question_title'] as $key => $value){
                //组成新增的question数据
                $new_question_data = [
                    'poll_id'   => $poll_id,
                    'question_sort_num' => $data['question_sort_num'][$key],
                    'question_title'    => $data['question_title'][$key] ?? '',
                    'question_type'     => $data['question_type'][$key],
                    'is_must'           => $data['is_must'][$key],
                    'user_select_num'   => 0,
                ];
                //根据选择的问题类型不一样进行判断
                if ($data['question_type'][$key] == 2){   //多选
                    if (!$data['select_num'][$key] || !is_numeric($data['select_num'][$key]) || $data['select_num'] < 0){
                        throw new Exception('第'.$data['question_sort_num'].'个问题的投票多选数量必须为数字且大于等于0');
                    }
                    $new_question_data['select_num'] = $data['select_num'][$key];
                }

                $poll_question = PollQuestion::create($new_question_data);
                $question_id = $poll_question->id;
                //新增新的option数据
                $new_option_data = [];
                foreach ($data['option_name'][$key] as $key1 => $value1){
                    $new_option_data[] = [
                        'poll_id'            => $poll_id,
                        'question_id'        => $question_id,
                        'option_sort_num'    => $data['option_sort_num'][$key][$key1],
                        'is_default'         => $data['is_default'][$key][$key1] ?? 0,
                        'upload_minetype'    => $data['upload_minetype'][$key][$key1] ?? '',
                        'option_name'        => $data['option_name'][$key][$key1] ?? '',
                        'option_img'         => $data['option_img'][$key][$key1] ?? '',
                        'num'                => 0
                    ];
                }
                PollOption::insert($new_option_data);
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            return json_encode(['code'=>0,'msg'=>$e->getMessage()],256);
        }
        return json_encode(['code'=>1,'msg'=>'success']);

    }

    public function getOptions(Request $request)
    {
        $poll_id = $request->get('poll_id');
        return json_encode((new PollOption())->getPollOptionInfo($poll_id),256);
    }

    //改变投票状态接口
    public function changePollStatus(Request $request)
    {
        $poll_id = $request->get('poll_id');
        $poll_status = $request->get('poll_status');

        //改变状态前先修正状态
        $poll_info = Poll::checkStatus($poll_id);

        $start_time = $poll_info->start_time;
        $end_time = $poll_info->end_time;
        $time = time();



        if ($start_time > $time || $end_time < $time){
            return json_encode(['code'=>0,'msg'=>'该投票不在投票期限 无法更改'.$start_time],256);
        }

        $status_arr = [0,1,2,3];
        if (!in_array($poll_status,$status_arr)){
            return json_encode(['code'=>0,'msg'=>'操作非法'],256);
        }
        //改变状态
        $poll_info->is_on = $poll_status;
        $poll_info->save();

        return json_encode(['code'=>1,'msg'=>'success','poll_id'=>$poll_id],256);
    }

    //查看详细信息
    public function seePollResult(Poll $poll_id)
    {
        //查询问题与题目
        $question = $this->margeQuestionOption($poll_id->id);
//        dd($question);
//        dd($question);
        return  view('admin.poll.result',compact('question'));
    }

    //查看某个填空题的回答
    public function seePollInput($option_id)
    {
        $option = UserInputAnswer::where(['option_id'=>$option_id])->get()->toArray();
        return view('admin/poll/see_poll_input',compact('option'));
    }
    //查看某个上传题的回答
    public function seePollUpload($option_id)
    {
        $option = UserFileUpload::where(['option_id'=>$option_id])->get()->toArray();
        return view('admin/poll/see_poll_upload',compact('option'));

    }
}
