<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use app\common\controller\Constant;
use think\Model;

class TimeLineModel extends Model
{
    protected $table = 'l_time_line';
    protected $resultSetType = 'collection';

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

    /**
     * 获取七天内增长数最高的前30名免费课程
     * @return array
     */
    public function getWeekRaterFreeList()
    {
        return $this->alias('a')->where([
            'a.date' => date('Y-m-d', strtotime('yesterday')),//昨天日期
            'a.flag' => Constant::FREE_LESSON,
        ])->field('a.id,a.name,a.rate,b.curriculumClassification,b.difficulty,b.price,b.totalTime')
            ->join('l_lesson b', 'a.id=b.id', 'LEFT')
            ->order('rate desc')->limit(30)->select()->toArray();
    }

    /**
     * 获取七天内增长数最高的前30名付费课程
     * @return array
     */
    public function getWeekRaterPayList()
    {
        return $this->alias('a')->where([
            'a.date' => date('Y-m-d', strtotime('yesterday')),//昨天日期
            'a.flag' => Constant::PAY_LESSON,
        ])->field('a.id,a.name,a.rate,b.curriculumClassification,b.difficulty,b.price,b.totalTime')
            ->join('l_pay_lesson b', 'a.id=b.id', 'LEFT')
            ->order('rate desc')->limit(10)->select()->toArray();
    }
}
