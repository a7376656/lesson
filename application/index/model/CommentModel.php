<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class CommentModel extends Model
{
    protected $table = 'l_comment';
    protected $resultSetType = 'collection';

    /**
     * 添加评论
     * @param $data
     * @return int|string
     */
    public function addComment($data)
    {
        return $this->insert($data);
    }

    /**
     * 根据条件获取评论数量
     * @param array $where
     * @return int|string
     */
    public function getCommentCount($where = [])
    {
        return $this->where($where)->count();
    }

    /**
     * 根据条件获取评论数量
     * @param array $where
     * @return int|string
     */
    public function getCommentListByWhere($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->select()->toArray();
    }
}
