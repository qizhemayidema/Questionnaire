<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Model\Manager;

class LoginController extends BaseController
{
    public function index()
    {
        return view('admin.login.index');
    }

    public function loginCheck(Request $request)
    {
        $data = $request->post();

        $rule = [
            'username'  => 'required',
            'password'  => 'required',
            'captcha'   => 'required|captcha',
        ];

        $message = [
            'username.required'     => '用户名必须填写',
            'password.required'     => '密码必须填写',
            'captcha.required'      => '验证码必须填写',
            'captcha.captcha'       => '验证码不正确',
        ];

        $validate = Validator::make($data,$rule,$message);
        if ($validate->fails()){
            return json_encode(['code'=>0,'msg'=>$validate->errors()->first()],256);
        }
        //检查登录信息
        $res = Manager::where(function($query) use ($data){
            return $query->where(['username'=>$data['username']])->where(['password'=>md5($data['password'])]);
        })->first()->toArray();
        if ($res){
            session(['admin.login'=>$res]);
            return json_encode(['code'=>1,'msg'=>'success'],256);
        }else{
            return json_encode(['code'=>0,'msg'=>'帐号或密码不正确，请重新登录'],256);
        }
    }

    public function changePasswd(Request $request)
    {
        $password = $request->post('password');
        $user_id = session('admin.login')['id'];
        Manager::where(['id'=>$user_id])->update(['password'=>md5($password)]);
        return json_encode(['code'=>1,'msg'=>'success']);
    }

    public function logout()
    {
        session(['admin.login'=>null]);
        return redirect('admin/login');
    }
}
