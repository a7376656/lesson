<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2017/11/22
 * Time: 14:59
 */

use think\exception\ErrorException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\RouteNotFoundException;
use think\exception\DbException;

class Http extends Handle
{
    /**
     * 异常处理
     * @author huxinlu
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     */
    public function render(\Exception $e)
    {
        $mySqlLog = new \MySqlLog();
        $route = \think\Request::instance()->path();//当前访问的路由
        if ($e instanceof RouteNotFoundException) {
            //路由错误
            $code = 1017;
            $message = '找不到该方法，路由有误';
        } elseif ($e instanceof DbException) {
            //数据库操作异常
            $code = 1016;
            $message = '内部操作有误';
        } elseif ($e instanceof ErrorException) {
            //请求过程中异常
            $message = '请求过程中出错，请联系客服';
        } elseif ($e instanceof HttpException) {
            //返回过程中异常
            $message = '请求返回过程中出错，请重试';
        } elseif ($e instanceof Exception) {
            //请求过程中异常
            $message = '请求操作有误';
        } else {
            return parent::render($e);
        }

        //返回的异常中如果有自定义的错误信息则显示自定义的错误信息和状态码，否则显示异常统一的状态码和统一报错信息
        if ($e->getPrevious() !== null) {
            $messageLog = $e->getPrevious()->getMessage();
            $code = $e->getCode();
            $message = $e->getMessage();
        } else {
            $messageLog = $e->getMessage();
            $code = isset($code) ? $code : 1001;
        }

        $mySqlLog->saveErrorLog($route, $messageLog, $e->getTrace());
        return json($this->getReturnData($code, $message), $code);
    }

    /**
     * 返回值
     * @author huxinlu
     * @param $code integer 错误码
     * @param $msg string 错误信息
     * @return array
     */
    private function getReturnData($code, $msg)
    {
        return [
            'code' => $code,
            'msg'  => $msg,
            'time' => date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']),
            'data' => [],
        ];
    }
}
