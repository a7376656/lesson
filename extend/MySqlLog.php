<?php

/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2017/11/23
 * Time: 17:47
 */
class MySqlLog
{
    private $db;

    public function __construct($config = [])
    {
        $database = \think\Config::get('database.log_db');
        $this->db = \think\Db::connect($database);
        $this->table = $database['table'] ? $database['table'] : 'log';
    }

    /**
     * 存入访问日志信息
     * @author huxinlu
     * @param array $log
     */
    public function save(array $log = [])
    {
        //用户信息
        $userInfo = $this->getUserInfo();

        $route = \think\Request::instance()->path();
        $appPath = str_replace('/', '\\', APP_PATH);//获取项目根目录
        $url = request()->module() . '\\' . request()->controller() . '\\' . request()->action();
        $logArr = [];
        foreach ($log as $type => $msgArr) {
            foreach ($msgArr as $msg) {
                $logArr[] = [
                    'uid' => $userInfo['uid'],
                    'username' => $userInfo['username'],
                    'route' => $route,
                    'type' => $type,
                    'msg' => $msg,
                    'file' => $appPath . $url,
                    'line' => '',
                    'function' => '',
                    'createTime' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->table($this->table)->insertAll($logArr);
    }

    /**
     * 存入错误信息
     * @author huxinlu
     * @param $route string 路由
     * @param $msg string 错误信息
     * @param $trace array 错误路径
     */
    public function saveErrorLog($route, $msg, $trace)
    {
        //用户信息
        $userInfo = $this->getUserInfo();

        //日志信息
        $logArr = [];
        $appPath = str_replace('/', '\\', substr(APP_PATH, 0, -1));//获取项目根目录
        foreach ($trace as $k => $v) {
            if (isset($v['file']) && is_string($v['file']) && strpos($v['file'], $appPath) !== false) {
                //file显示错误路径
                if (isset($v['file']) && is_string($v['file'])) {
                    $file = $v['file'];
                } else {
                    $file = '';
                }

                //line显示错误行数
                if (isset($v['line'])) {
                    $line = $v['line'];
                } else {
                    $line = '';
                }

                //function显示错误方法
                if (isset($v['function']) && is_string($v['function'])) {
                    $function = $v['function'];
                } else {
                    $function = '';
                }

                $logArr[] = [
                    'uid' => $userInfo['uid'],
                    'username' => $userInfo['username'],
                    'type' => 'error',
                    'route' => $route,
                    'msg' => $msg,
                    'file' => $file,
                    'line' => $line,
                    'function' => $function,
                    'createTime' => date('Y-m-d H:i:s'),
                ];
            }
        }

        //如果记录日志为空则记录一条已返回的错误信息
        if (empty($logArr) && !empty($msg)) {
            $this->saveLog($msg, 'error');
        }

        $this->db->table($this->table)->insertAll($logArr);
    }

    /**
     * 获取当前用户信息
     * @author huxinlu
     * @return array
     */
    private function getUserInfo()
    {
        $token = \think\Request::instance()->header('Authorization');
        if ($token) {
            //获取用户ID
            $tokenModel = new \app\common\logic\Token();
            $payload = $tokenModel->getPayload($token);
            $uid = $payload->uid;

            //获取用户名
            $userModel = new \app\user\model\UserModel();
            $userDetail = $userModel->where(['uid' => $uid])->field('username')->find();
            $username = $userDetail['username'] ? $userDetail['username'] : '';
        } else {
            $uid = 0;
            $username = '';
        }

        return ['uid' => $uid, 'username' => $username];
    }

    /**
     * 记录单条日志
     * @author huxinlu
     * @param string $msg 记录的信息内容
     * @param string $type 记录的信息类型
     */
    public function saveLog($msg, $type)
    {
        //用户信息
        $userInfo = $this->getUserInfo();

        //日志信息
        $route = \think\Request::instance();//当前所在的路由
        $logArr = [
            'uid' => $userInfo['uid'],
            'username' => $userInfo['username'],
            'type' => $type,
            'route' => $route->path(),
            'msg' => $msg,
            'file' => APP_PATH . lcfirst($route->module()) . '/' . lcfirst($route->controller()) . '/' . lcfirst($route->action()),
            'createTime' => date('Y-m-d H:i:s'),
        ];

        $this->db->table($this->table)->insert($logArr);
    }
}