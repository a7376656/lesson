<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\controller\Constant;
use app\index\model\CommentModel;
use app\index\model\LessonModel;
use QL\Ext\PhantomJs;
use QL\QueryList;
use think\Db;

class LessonController extends BaseController
{
    /**
     * 默认显示
     */
    public function index()
    {
        echo '你好';
    }

    public function getFreeLastSevenDayInfo()
    {
        $lessonModel = new LessonModel();

        $today = date('Y-m-d');//今天日期
        $sevenAgo = date('Y-m-d', strtotime('-6 days'));//7天前（因为包括今天所以是-6）

        
    }
}

