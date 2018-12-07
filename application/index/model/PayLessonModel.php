<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class PayLessonModel extends Model
{
    protected $table = 'l_pay_lesson';
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
     * 根据条件获取课程列表
     * @param $where array 条件
     * @param $field string 字段
     * @return array
     */
    public function getLessonListByWhere($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->select()->toArray();
    }

    /**
     * 更新信息
     * @param $where array 条件
     * @param $update array 更新语句
     * @return LessonModel
     */
    public function updateInfo($where = [], $update)
    {
        return $this->where($where)->update($update);
    }

    /**
     * 获取所有课程评论总数
     * @return float|int
     */
    public function getTotalCommentNum()
    {
        return $this->sum('commentNum');
    }
}
