<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2017/12/19
 * Time: 18:27
 */

/**
 * 权限列表
 * 格式一：
 * 'user'——代表是整个user模块都需要验证,
 * 'user/info'——代表是整个user模块下的Info控制器都需要验证,
 * 'user/info/getCompanyDetail'——代表只有user模块下的info控制器下的getCompanyDetail才需要验证，
 *
 * 格式二：
 * ['user/info', 'except' => ['getCompanyDetail']]
 * 第一个数组部分一定是模块名/控制器名，except代表除了，
 * except后也一定是数组格式，也就是user/info下的除了getCompanyDetail方法外其余都需要验证
 *
 * 格式三：
 * ['user/info', 'only' => ['getCompanyDetail']]
 * 第一个数组部分一定是模块名/控制器名，only代表除了，
 * only后也一定是数组格式，也就是user/info下的只有getCompanyDetail方法需要验证，其余都不需要验证
 */
return [
    ['api/user', 'except' => ['register', 'login', 'forgetPassword', 'getUserBasicDetail', 'isLogin', 'logout','wxLogin']],
    ['user/info'],
    //['data'],
];