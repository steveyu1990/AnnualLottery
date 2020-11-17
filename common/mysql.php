<?php
    require_once(dirname(dirname(__FILE__)) . '/config/config.php');

    header("content-type:text/html;charset=utf8");

    $host = $mysql_config['host'];
    $user_name = $mysql_config['user_name'];
    $password = $mysql_config['password'];
    $db = $mysql_config['db'];

    $mysql = new mysqli($host, $user_name, $password, $db);

    if ($mysql->connect_error) {
        die("数据库连接失败:" . $mysql->connect_error);
    }

    $mysql->query("set name utf8");