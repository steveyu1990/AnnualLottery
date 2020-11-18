<?php
namespace App\Service;

class SmsService
{
    /**
     * 发送短信方法
     *
     * @param $template_id
     * @param $mobile
     * @param array $params
     * @param string $areaflag
     * @param int $channelId
     * @param string $smsType
     * @return bool
     */
    public static function send_template_sms($template_id, $mobile, $params = array(), $areaflag = 'other', $channelId=14, $smsType = 'SendTempletSmsAndCheckSource')
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        ksort($params);
        $temp = array();
        $i = 1;
        foreach ($params as $val) {
            $temp["{$i}"] = $val;
            $i++;
        }
        $params = $temp;

        try {
            $requestParams = array(
                "p_strId" => $template_id,
                "p_strJson" => json_encode($params, JSON_FORCE_OBJECT),
                "p_strMobile" => $mobile,
                "p_area" => $areaflag,
                "p_channel" => $channelId,
                "p_IpAddress" => config('qeeka.system_ip'),
                "p_sysCode" => '005',
                'password' => 'psdDecorate'
            );
//            \Log::debug('短信发送请求参数', $requestParams);
            $client = new \SoapClient("http://" . config('qeeka.sms_request_url') . "/SmsTemplet.asmx?WSDL", array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
            $result = $client->__soapCall($smsType, array('parameters ' => $requestParams));
            //记录发送失败日志
            $callRet = @json_decode(json_encode($result), true);
            \Log::info('短信发送返回结果', compact('requestParams', 'callRet'));

            if (-16 == $callRet[$smsType.'Result']) {
                $remove_restrictions_result = self::sms_remove_restrictions($template_id, $mobile);
                \Log::info('移出手机号限制结果', [$remove_restrictions_result]);
            }

            if (isset($callRet[$smsType.'Result']) && 1 == $callRet[$smsType.'Result']) {
                return true;
            }

            if(!$callRet || 1 != $callRet[$smsType.'Result']) {
                if(empty($callRet)) {
                    $callRet = array();
                }
                $logArr = array_merge($requestParams, $callRet);
                \Log::error('sms_send_fail', $logArr);
            }
        } catch (\Exception $e) {
            $msg = "发生错误:" . $e->getMessage();
            \Log::error('sms_send_fail', array('msg' => $msg));
            echo $msg;
        }

        return false;
    }

    // 解除手机号发送限制
    public static function sms_remove_restrictions($template_id, $mobile){
        $client = new \SoapClient("http://" . config('qeeka.sms_request_url') . "/SmsTemplet.asmx?WSDL", array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
        $params=array();
        $params['Mobile']=$mobile;
        $params['TempletId']=$template_id;
        $result = $client->__soapCall( "RemoveRestrictions" , array('parameters'=>$params) );
        $res = $result->RemoveRestrictionsResult;
        return $res;
    }
}
