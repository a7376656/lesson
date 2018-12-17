<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class ClassificationModel extends Model
{
    protected $table = 'l_classification';
    protected $resultSetType = 'collection';

    /**
     * 根据条件获取分类数量
     * @param array $where
     * @return int|string
     */
    public function getClassificationCount($where = [])
    {
        return $this->where($where)->count();
    }

    /**
     * 获取所有分类名称
     * @return array
     */
    public function getClassificationList()
    {
        return $this->where('isDeleted', 0)->column('name');
    }

    /**
     * 根据条件获取分类列表
     * @param $where
     * @param $field
     */
    public function getList($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->select()->toArray();
    }
}
