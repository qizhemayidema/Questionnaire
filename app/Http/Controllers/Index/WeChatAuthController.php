<?php
/**
 * Created by PhpStorm.
 * User: fycy
 * Date: 2018/12/10
 * Time: 10:21
 */

namespace App\Http\Controllers\Index;


use Illuminate\Http\Request;

class WeChatAuthController
{

    private $appid = "xxx";
    private $appsecret = "xxx";


    //构造方法，对成员属性进行赋值操作
    public function valid(Request $request)
    {
        dd($request->all());
//        $echostr = $request->get('echostr');//随机字符串
//        if ($this->checkSignature($request)) {
//            echo $request->get('echostr');
//        } else {
//            die('error');
//        }
    }

    private function checkSignature(Request $request)
    {  // 验证微信签名的函数
        $signature = $request->get('signature');//微信加密签名
        $timestamp = $request->get('timestamp');//时间戳
        $nonce = $request->get('nonce');//随机数
        //2、加密、校验
        //1.将tonken、timestamp、nonce进行排序
        $tmpArr = array(TONKEN, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        //2.将三个参数字符串拼接成一个字符串进行sha1加密
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        //3.开发者获得加密后的字符串和signature对比
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }


    public function https_request($url, $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $outopt = curl_exec($ch);
        curl_close($ch);
        return json_decode($outopt, true);//返回数组结果
    }

    public function getAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";

        $result = $this->https_request($url);

        return $result['access_token'];  //获取 access_token
    }

    public function snsapi_base($hd_url)//用户静默授权
    {
        $snsapi_base_url = " https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$hd_url}&response_type=code&scope=snsapi_base&state=123#wechat_redirect ";

        $request = new Request();
        if (!$request->get('code')) {  //获取code

            header("location:{$snsapi_base_url}");
        }
        $code = $request->get('code');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->appsecret}&code={$code}&grant_type=authorization_code";
        return $this->https_request($url);
    }

    public function snsapi_userinfo($hd_url)//用户手动同意授权
    {
        $hd_url = urlencode($hd_url);
        $snsapi_userinfo_url = " https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$hd_url}&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect ";


        if (!isset($_GET['code'])) {  //获取code

            header("location:{$snsapi_userinfo_url}");
        }
        $code = $_GET['code'];
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->appsecret}&code={$code}&grant_type=authorization_code";
        $result = $this->https_request($url);

        if (@!$result['access_token']) {
            $access_token = $_SESSION['access_token'];
            $openid = $_SESSION['openid'];

        } else {
            $access_token = $result['access_token'];
            $openid = $result['openid'];
            $_SESSION['access_token'] = $access_token;
            $_SESSION['openid'] = $openid;

        }
        //拉取用户信息
        $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        return $this->https_request($userinfo_url);


    }
}