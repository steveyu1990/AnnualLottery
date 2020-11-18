<?php
/**
 * 去除数组中的null，变为字符串
 * @param $arr
 */
function set_array_null_to_empty(&$arr)
{
    if (is_array($arr)) {
        foreach ($arr as &$item) {
            if (is_array($item)) {
                set_array_null_to_empty($item);
            } else if (is_null($item)) {
                $item = '';
            }
        }
    }
}

/**
 * 公用的方法  返回请求响应数据，进行信息的提示
 * @param $code 状态
 * @param string $msg 提示信息
 * @param array $result 返回数据
 */
function msg($code = 0, $msg = '', $result = array())
{
    response_result($code, $msg, $result);
}

/**
 * 公用的方法  返回请求响应数据，进行信息的提示
 * @param $code 状态
 * @param string $msg 提示信息
 * @param array $result 返回数据
 */
function msg_succ($msg, $result = array())
{
    response_result(0, $msg, $result);
}

function response_result($code = 0, $msg = '', $result = array())
{
    set_array_null_to_empty($result);
    response(array(
        'code' => $code,
        'msg' => $msg,
        'result' => $result ? $result : (object)[]
    ))->send();
    exit;
}

/* return name values */
function get_key_array(array $list, $key_name)
{
    return array_values(array_filter(array_unique(array_column($list, $key_name))));
}

/* return names values */
function get_keys_array(array $list, array $key_names)
{
    $return = array();
    foreach($key_names AS $key_name)
    {
        $return[$key_name] = array();
    }
    foreach($list AS $key=>$value)
    {
        foreach($key_names AS $key_name)
        {
            $return[$key_name][] = $value[$key_name];
        }
    }
    foreach($key_names AS $key_name)
    {
        $return[$key_name] = array_filter(array_unique($return[$key_name]));
    }
    return array_values($return);
}

/* convert key */
function array_convert_key(array $list, $key_name)
{
    return array_column($list, NULL, $key_name);
}

/* convert key array */
function array_convert_key_array(array $list, $key_name)
{
    $return = array();
    foreach($list AS $key=>$value)
    {
        if(!isset($return[$value[$key_name]])) $return[$value[$key_name]] = array();
        $return[$value[$key_name]][] = $value;
    }
    return $return;
}

/**
 * post请求raw方式请求
 * @param string $url
 * @param array $params
 * @return bool|string
 */
function curl_post_raw(string $url, array $params)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,            $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST,           1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($params));// 必须为字符串
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));// 必须声明请求头
    $return = curl_exec($ch);

    return $return;
}

//获取当前时间毫秒
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

/**
 * object对象转换成数组
 *
 * @param $objects
 * @return array
 */
function to_array($objects)
{
    if (empty($objects)) {
        return [];
    } else {
        return array_map('get_object_vars', $objects);
    }
}

/**
 * 取小数点两位
 * @param $num
 * @param int $precision
 * @return false|float
 */
function round_point($num, $precision = 2)
{
    return round($num, $precision);
}

/*********************************************************************
函数名称:encrypt
函数作用:加密解密字符串
使用方法:
加密   :encrypt('str','E','nowamagic');
解密   :encrypt('被加密过的字符串','D','nowamagic');
参数说明:
$string  :需要加密解密的字符串
$operation:判断是加密还是解密:E:加密  D:解密
$key   :加密的钥匙(密匙);
 *********************************************************************/
function encrypt_token($string, $operation, $key='')
{
    $key=md5($key);
    $key_length=strlen($key);
    $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
    $string_length=strlen($string);
    $rndkey=$box=array();
    $result='';
    for($i=0;$i<=255;$i++)
    {
        $rndkey[$i]=ord($key[$i%$key_length]);
        $box[$i]=$i;
    }
    for($j=$i=0;$i<256;$i++)
    {
        $j=($j+$box[$i]+$rndkey[$i])%256;
        $tmp=$box[$i];
        $box[$i]=$box[$j];
        $box[$j]=$tmp;
    }
    for($a=$j=$i=0;$i<$string_length;$i++)
    {
        $a=($a+1)%256;
        $j=($j+$box[$a])%256;
        $tmp=$box[$a];
        $box[$a]=$box[$j];
        $box[$j]=$tmp;
        $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
    }
    if($operation=='D')
    {
        if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8))
        {
            return substr($result,8);
        }
        else
        {
            return '';
        }
    }
    else
    {
        return str_replace('=','', base64_encode($result));
    }
}
