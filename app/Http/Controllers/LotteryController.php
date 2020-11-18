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

                response(['code' => 0,
                    'msg' => '手机短信验证码已发送',
                    'result' => ['has_content' => $has_content]])
                    ->withCookie('sms_code', $this->generateCodeCookie($mobile, $code), 60)
                    ->send();

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

        response(['code' => 0,
            'msg' => '',
            'result' => ['url' => '/lottery']])
            ->withCookie('token', $token, 60*24*30)
            ->send();

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

        $current_day = date('Y-m-d');

        //每天可以抽一次,redis判断当天手机号的key是否存在，不存在才可以抽奖, redis新增当天手机号记录
        $script = <<<SCRIPT
return redis.call('SET', KEYS[1], ARGV[1], 'NX', 'EX', ARGV[2])
SCRIPT;
        //
        $redis = app('redis')->connection('default');;
        $result = $redis->eval($script, 1, $current_day . ':' . $mobile, 1, 7*24*60*60);

        if (!$result) {
            msg('100', '每天只能抽奖一次');
        }

        //根据概率算出应得物品
        $git_num = mt_rand(1, 100);

        //抽到手机： redis判断当天手机是否有记录,redis手机数量减一，小于0就结束；数据库记录奖品
        if ($git_num == 1) {
            $script = <<<SCRIPT
local day_mobile = KEYS[1]
local day_phone_key = KEYS[2]
local expire_time = ARGV[1]

local rs = redis.call('SET', day_phone_key, 1, 'NX', 'EX', expire_time)

if(rs==false)
then
    return false
end

local phone_count = redis.call('DECR', 'phone')

if(phone_count<0)
then
    return false
end

return redis.call('SET', day_mobile, '手机')


SCRIPT;
            $result = $redis->eval($script, 2, $current_day . ':' . $mobile, $current_day . ':phone', 7*24*60*60);

            if ($result) {
                $gift_log = new GiftLog();
                $gift_log->mobile = $mobile;
                $gift_log->gift_name = '手机';
                $gift_log->save();

                return msg_succ('抽到手机');
            } else {
                $git_num = 100;
            }
        }

        //抽到电话卡：redis判断用户是否已经抽到2张，redis记录电话卡数量加一且数量要小于100，redis手机卡数量加一，redis增加用户电话卡记录，数据库增加奖品记录
        if ($git_num >= 2 && $git_num <= 5) {
            $script = <<<SCRIPT
local mobile_key = KEYS[1]
local mobile_phone_card_count = redis.call('INCR', mobile_key)

if(mobile_phone_card_count>2)
then
    return false
end

local phone_card_count = redis.call('DECR', 'phone_card')

if(phone_card_count<0)
then
    return false
end

return true
SCRIPT;
            $result = $redis->eval($script, 1, $mobile . ':phone_card');

            if ($result) {
                $gift_log = new GiftLog();
                $gift_log->mobile = $mobile;
                $gift_log->gift_name = '电话卡';
                $gift_log->save();

                return msg_succ('抽到电话卡');
            } else {
                $git_num = 100;
            }
        }

        //抽到贴纸：数据库记录奖品记录
        if ($git_num >= 6 && $git_num <= 100) {
            $gift_log = new GiftLog();
            $gift_log->mobile = $mobile;
            $gift_log->gift_name = '贴纸';
            $gift_log->save();

            return msg_succ('抽到贴纸');
        }
    }

    public function content(Request $request)
    {
        $users = User::orderBy('id', 'desc')->paginate(50);

        return view('contentList', ['list' => $users]);
    }

    public function giftLog(Request $request)
    {
        $gift_logs = GiftLog::get()->toArray();
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
