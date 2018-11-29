<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\controller\Constant;
use app\index\model\CommentModel;
use app\index\model\LessonModel;
use app\index\model\PayLessonModel;
use app\index\model\TimeLineModel;
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

    /**
     * 获取免费课程七天内增长率
     */
    public function getFreeLastSevenDayInfo()
    {
        $lessonModel = new LessonModel();
        $timeLineModel = new TimeLineModel();

        $today = date('Y-m-d');//今天日期
        $sevenAgo = date('Y-m-d', strtotime('-6 days'));//7天前（因为包括今天所以是-6）

        //未写完
    }

    /**
     * 获取超过10万学习人数的免费课程列表
     */
    public function moreThenTenFreeLesson()
    {
        $lessonModel = new LessonModel();

        $result = $lessonModel->getLessonListByWhere(['studyNum' => ['gt', 100000]]);

        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取超过10万学习人数的付费课程列表
     */
    public function moreThenTenPayLesson()
    {
        $payLessonModel = new PayLessonModel();

        $result = $payLessonModel->getLessonListByWhere(['studyNum' => ['gt', 100000]]);

        $this->ajaxReturn(1000, 'ok', $result);
    }
}

