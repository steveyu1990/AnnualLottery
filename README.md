# AnnualLottery

##拉取代码，进入项目根目录执行命令
```
composer install 
```

##配置host
```
127.0.0.1 local.lottery.com
```

##nginx配置，替换项目路径
```
server {
    listen       80;
    server_name  local.lottery.com;
    root   "/项目路径/AnnualLottery/public";

    location / {
        index index.php index.html index.htm;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    location ~ ^/(js|css|fonts)/ {
        root "/项目路径/AnnualLottery/resources";
    }

}
```

##修改.env.production文件的mysql、redis配置信息
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lottery
DB_USERNAME=root
DB_PASSWORD=123456

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DATABASE_NUM=8

REDIS_PREFIX=lottery_
REDIS_DB=8
REDIS_CACHE_DB=1
```


##创建数据库
```
CREATE DATABASE IF NOT EXISTS lottery
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_general_ci;

use lottery;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` char(11) NOT NULL COMMENT '手机号',
  `content` varchar(500) DEFAULT '' COMMENT '征文内容',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uidx_mobile` (`mobile`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `gift_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` char(11) NOT NULL COMMENT '手机号',
  `gift_name` varchar(16) DEFAULT '' COMMENT '礼物名称',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;
```

## redis数据初始化,设置奖品数量上限
```
select 8
set phone 5
set phone_card 100
```

## 使用说明

登入页：http://local.lottery.com/   
手机号符合规则会发送验证码，默认正确验证码为：6666  
未参与过互动的会让填写征文信息，然后提交后调整抽奖页面  
此时会记录用户手机号到cookie作为之后的抽奖准入凭证  
cookie记录抽奖准入凭证之后，进入登入页会自动跳转抽奖页  

抽奖页面：http://local.lottery.com/lottery  
抽奖页可以退出当前手机号，回首页重复上面的流程  
抽奖会按照概率中奖并记录

活动数据详情页：http://local.lottery.com/content  
可以查看征文信息，下载征文、奖品记录

