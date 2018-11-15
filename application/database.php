<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    'default' => [
        // 数据库类型
        'type'            => 'mysql',
        // 服务器地址
        'hostname'        => '120.79.227.193',
        // 数据库名
        'database'        => 'lesson',
        // 用户名
        'username'        => 'root',
        // 密码
        'password'        => '1170db',
        // 端口
        'hostport'        => '3306',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => 'l_',
        // 数据库调试模式
        'debug'           => true,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate'     => false,
        // 读写分离后 主服务器数量
        'master_num'      => 1,
        // 指定从服务器序号
        'slave_no'        => '',
        // 是否严格检查字段是否存在
        'fields_strict'   => false,
        // 数据集返回类型
        'resultset_type'  => 'array',
        // 自动写入日期时间格式，支持datetime,date,timestamp,这边默认的数据库字段是createTime和updateTime
        'auto_timestamp'  => 'datetime',
        // 时间字段取出和存入的默认时间格式，如果自动写入的是非时间戳格式的话就必须写明一种格式，否则就默认成时间戳了
        'datetime_format' => 'Y-m-d H:i:s',
        // 是否需要进行SQL性能分析
        'sql_explain'     => false,
    ],
];
