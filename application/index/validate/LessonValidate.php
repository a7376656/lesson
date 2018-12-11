<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/7
 * Time: 17:06
 */
namespace app\index\validate;

use think\Validate;

class LessonValidate extends Validate
{
    //规则
    protected $rule = [
        ['id', 'require|integer', '课程id不能为空|课程id只能是整数'],
        ['flag', 'require|integer|between:1,2', '课程类型不能为空|课程类型只能是整数|课程类型只能为1,2'],
        ['filter', 'chsAlphaNum', '关键词只能为汉字、字母和数字'],
    ];

    //场景
    protected $scene = [
        'getLessonDetail' => ['id', 'flag'],//获取一门课程详情信息
        'searchLessonResult' => ['flag', 'filter'],//搜索课程
        'searchCommentResult' => ['filter'],//搜索评论
    ];
}
