<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class TimeLineModel extends Model
{
    protected $table = 'l_time_line';

    /**
     * 根据条件获取信息
     * @author wenjie.lei
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function getInfoByWhere($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }

    /**
     * 添加信息
     * @param $data
     * @return int|string
     */
    public function addTimeLine($data)
    {
        return $this->insert($data);
    }
}
