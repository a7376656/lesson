<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class AuthorModel extends Model
{
    protected $table = 'l_author';
    protected $resultSetType = 'collection';

    /**
     * 一次性添加多个作者
     * @param $data
     * @return int|string
     */
    public function addAuthor($data)
    {
        return $this->insert($data);
    }

    /**
     * 根据条件获取讲师数量
     * @param array $where
     * @return int|string
     */
    public function getAuthorCount($where = [])
    {
        return $this->where($where)->count();
    }

    /**
     * 获取人气最高的10个讲师
     */
    public function getHotAuthor()
    {
        return $this->order('fans desc')->limit(10)->select()->toArray();
    }
}
