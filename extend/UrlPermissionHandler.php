<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2018/1/2
 * Time: 15:39
 */
class UrlPermissionHandler
{
    /**
     * 判断不登录的情况下是否有权限查看
     * @author huxinlu
     * @return bool
     * @throws Exception
     */
    public function hasPermission()
    {
        $request = \think\Request::instance();//获取当前请求的路径
        $module = lcfirst($request->module());//获取当前请求的模块名
        $controller = lcfirst($request->controller());//获取当前请求的控制器名
        $action = lcfirst($request->action());//获取当前请求的方法名

        $rbacFile = \think\Config::get('rbac_config_file');//需要登录才能查看的权限列表
        if (is_file(CONF_PATH . $rbacFile . CONF_EXT)) {
            // 导入权限配置
            $rbacArr = include CONF_PATH . $rbacFile . CONF_EXT;
            if (is_array($rbacArr)) {
                //如果权限列表中不存在配置信息，则默认是不需要权限验证
                $res = empty($rbacArr) ? false : true;
                foreach ($rbacArr as $rbac) {
//                    //数组子项中是否含有数组
                    if (is_array($rbac)) {
                        //判断模块控制器是否和子项中的模块控制器相等，相等的情况下才会验证之后的所有选项
                        if ($module.'/'.$controller === $rbac[0]) {
                            if (isset($rbac['except']) && is_array($rbac['except'])) {
                                if (in_array($action, $rbac['except'])) {
                                    return false;//不需要验证
                                } else {
                                    return true;//需要验证
                                }
                            } elseif (isset($rbac['only']) && is_array($rbac['only'])) {
                                if (in_array($action, $rbac['only'])) {
                                    return true;//需要验证
                                } else {
                                    return false;//不需要验证
                                }
                            }
                        }
                        //如果以上都不满足直接验证数组中第一个值
                        $res = $this->validatePermission($module, $controller, $action, [$rbac[0]]);
                        if($res) {
                            return true;
                        }
                    } else {
                        //如果不是数组就直接匹配所有的权限列表
                        $res = $this->validatePermission($module, $controller, $action, $rbacArr);
                        if($res) {
                            return true;
                        }
                    }
                }
                return $res;
            } else {
                exception_throw('系统内部错误，权限文件格式不正确');
            }
        } else {
            exception_throw('系统内部错误，请确认权限文件是否正确');
        }
    }

    /**
     * 验证路径是否需要登录权限
     * @author huxinlu
     * @param $module string 模块名
     * @param $controller string 控制器名
     * @param $action string 方法名
     * @param $rbacArr array 权限列表
     * @return bool
     */
    private function validatePermission($module, $controller, $action, $rbacArr)
    {
        if (in_array($module, $rbacArr)) {//该模块是否在权限列表中
            return true;//需要验证是否登录
        } elseif (in_array($module.'/'.$controller, $rbacArr)) {//该模块控制器是否在权限列表中
            return true;//需要验证是否登录
        } elseif (in_array($module.'/'.$controller.'/'.$action, $rbacArr)) {//该整个url是否在权限列表中
            return true;//需要验证是否登录
        } else {
            return false;
        }
    }
}