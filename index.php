<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';

//$allow_origin = array(
//    "http://localhost:8080",
//    "http://localhost:8081"
//);
//
//
//if(in_array($origin, $allow_origin)) {
//    header("Access-Control-Allow-Origin:".$origin);
//    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
//    header("Access-Control-Allow-Headers: Content-Type, Accept, Authorization");
//    header("Access-Control-Allow-Credentials: true");
//}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Authorization, platform");
header("Access-Control-Allow-Credentials: true");

if($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    exit;
}

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

//定义行为监听
//define('APP_HOOK',true);

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';

