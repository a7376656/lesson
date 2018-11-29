<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/16
 * Time: 10:13
 */
namespace app\common\controller;

class Constant
{
    //phantomjs在不同环境上的地址
    const WINDOWS_PHANTOM_URL = 'D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe';//windows
    const LINUX_PHANTOM_URL = '/usr/phantomjs';//linux

    //课程类型
    const FREE_LESSON = 1;//免费课程
    const PAY_LESSON = 2;//付费课程
}
