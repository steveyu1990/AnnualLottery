<?php
    require_once(dirname(__FILE__) . '/common/mysql.php');
    require_once(dirname(__FILE__) . '/common/common.php');

    $result = ['error' => false, 'msg' => ''];
    $mobile = $_REQUEST['mobile'];

    if (preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
        $query = mysqli_prepare($mysql, "select ");
    } else {
        $result['error'] = true;
        $result['msg'] = '手机号格式不正确';

        msg($result);
    }