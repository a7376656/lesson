<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class LessonModel extends Model
{
    protected $table = 'l_lesson';
    protected $resultSetType = 'collection';

    /**
     * 添加课程
     * @param $data
     * @return int|string
     */
    public function addLesson($data)
    {
        return $this->insert($data);
    }

    /**
     * 根据条件获取课程数量
     * @param array $where
     * @return int|string
     */
    public function getLessonCount($where = [])
    {
        return $this->where($where)->count();
    }

    /**
     * 获取所有课程的id
     * @return array
     */
    public function getAllLessonIds()
    {
        return $this->column('id');
    }

    /**
     * 根据条件获取某个课程信息
     * @author wenjie.lei
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function getLessonInfoByWhere($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }
}
