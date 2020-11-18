<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GiftLog;

class LotteryController extends Controller
{
    private $token_encrypt_key = 'lottery2020token';
    private $sms_encrypt_key = 'lottery2020sms';

    /**
     * 首页
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $mobile = $this->getMobileByToken();

        $user = User::where('mobile', $mobile)->first();

        if (!empty($user)) {
            return view('lottery', ['mobile' => $user->mobile]);
        }

        return view('index');
    }

    public function checkMobile(Request $request)
    {
        $mobile = $request->input('mobile');

        if (preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
            $user = User::where('mobile', $mobile)->first();

            $code = $this->generateCode();

            for ($i = 0; $i < 3; $i++) {
                $sms_result = $this->sendSms($mobile, $code);

                if ($sms_result) {
                    break;
                }
            }

            if ($sms_result) {

                $has_content = false;

                if (!empty($user->content)) {
                    $has_content = true;
                }

                response('')->withCookie('sms_code', $this->generateCodeCookie($mobile, $code), 60)->send();
                msg_succ('手机短信验证码已发送', ['has_content' => $has_content]);
            } else {
                msg('100', '服务器繁忙，请稍后重试');
            }

        } else {
            msg('100', '手机号格式不正确');
        }
    }

    public function register(Request $request)
    {
        $mobile = $request->input('mobile');
        $code = $request->input('code');
        $content = $request->input('content');

        if (empty($mobile)) {
            msg('100', '手机号必填');
        }

        $encrypted_code_cookie = request()->cookie('sms_code');
        $encrypted_code = $this->generateCodeCookie($mobile, $code);

        if ($encrypted_code_cookie != $encrypted_code) {
            msg('100', '验证码不正确，或者验证超时，请重试');
        }

        $user = User::where('mobile', $mobile)->first();

        if (empty($user)) {

            if (empty($content) || mb_strlen($content) > 500) {
                msg('100', '征文内容不能为空，且不能超过500字');
            }

            $user  = new User();
            $user->mobile = $mobile;
            $user->content = $content;
            $user->save();
        }

        $token = encrypt_token($mobile, 'E', $this->token_encrypt_key);
        response('')->withCookie('token', $token, 60*24*30)->send();

        return msg_succ('', ['url' => '/lottery']);
    }

    public function lottery(Request $request)
    {
        $mobile = $this->getMobileByToken();

        $user = User::where('mobile', $mobile)->first();

        if (empty($user)) {
            return redirect()->to('/');
        }

        return view('lottery', ['mobile' => $mobile]);
    }

    public function lucky(Request $request)
    {
        $mobile = $this->getMobileByToken();

        $user = User::where('mobile', $mobile)->first();

        if (empty($user)) {
            msg('100', '手机号信息异常，请回首页');
        }

        //TODO 分配礼物
        GiftLog::where('mobile', $mobile);
    }

    public function quite()
    {
        setcookie('token', '');

        return msg_succ('成功退出手机号');
    }

    public function getMobileByToken()
    {
        $token_cookie = request()->cookie('token');

        return encrypt_token($token_cookie, 'D', $this->token_encrypt_key);
    }

    /**
     * 发送短信验证码
     * @param $mobile
     * @param $code
     * @return bool
     */
    public function sendSms($mobile, $code)
    {
        return true;
    }

    /**
     * 生产短信验证码
     * @return int
     */
    public function generateCode()
    {
        return 6666;
    }

    public function generateCodeCookie($mobile, $code)
    {

        return md5($this->sms_encrypt_key . $mobile . $code);
    }
}
