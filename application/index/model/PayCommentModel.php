<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:23
 */
namespace app\index\model;

use think\Model;

class PayCommentModel extends Model
{
    protected $table = 'l_pay_comment';

    /**
     * 添加评论
     * @param $data
     * @return int|string
     */
    public function addComment($data)
    {
        return $this->insert($data);
    }
}
