<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class DataModel extends Model
{
    protected $table = 'l_data';

    /**
     * 获取慕课信息
     * @param $field string 字段
     * @return array|false|\PDOStatement|string|Model
     */
    public function getInfo($field = '*')
    {
        return $this->where('id', 1)->field($field)->find();
    }

    /**
     * 根据条件修改
     * @param $where array 条件
     * @param $update array 更新语句
     * @return int|string
     */
    public function updateInfo($where = [], $update)
    {
        return $this->where($where)->update($update);
    }
}
