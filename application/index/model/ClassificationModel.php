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
     * 获取分类列表
     * @return array
     */
    public function getClassificationList()
    {
        return $this->where('isDeleted', 0)->column('name');
    }
}
