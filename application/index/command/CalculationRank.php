<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5
 * Time: 13:08
 */
namespace app\index\command;

use app\common\controller\Constant;
use app\index\controller\IndexController;
use app\index\model\ClassificationModel;
use app\index\model\LessonModel;
use app\index\model\PayLessonModel;
use app\index\model\TimeLineModel;
use QL\Ext\PhantomJs;
use QL\QueryList;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class CalculationRank extends Command
{
    protected function configure()
    {
        $this->setName('setRank')->setDescription('每天凌晨0:00执行一次');
    }

    /**
     * 统计昨天为止同类下的学习人数名次、评论人数名次、评分名次
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     */
    protected function execute(Input $input, Output $output)
    {
        $lessonModel = new LessonModel();
        $payLessonModel = new PayLessonModel();
        $classificationModel = new ClassificationModel();

        //获取所有的分类
        $classInfo = $classificationModel->getClassificationList();

        Db::startTrans();
        try {
            foreach ($classInfo as $v) {
                //免费课程
                $ids = $lessonModel->getLessonListByWhere(['curriculumClassification' => $v], 'id,studyNum,commentNum,comprehensiveScore');

            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $output->writeln($e->getMessage());
        }

        $output->writeln('ok');
    }
}

