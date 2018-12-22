<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5
 * Time: 13:08
 */
namespace app\index\command;

use app\index\model\ClassificationModel;
use app\index\model\LessonModel;
use app\index\model\PayLessonModel;
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
                //学习人数
                $freeStudyIds = $lessonModel->getIdsByStudyNum($v);
                $payStudyIds = $payLessonModel->getIdsByStudyNum($v);
                $num = 1;
                foreach ($freeStudyIds as $value) {
                    $lessonModel->updateInfo(['id' => $value], ['studyRank' => $num]);
                    $num ++;
                }
                $num = 1;
                foreach ($payStudyIds as $value) {
                    $payLessonModel->updateInfo(['id' => $value], ['studyRank' => $num]);
                    $num ++;
                }

                //评论人数
                $freeCommentIds = $lessonModel->getIdsByCommentNum($v);
                $payCommentIds = $payLessonModel->getIdsByCommentNum($v);
                $num = 1;
                foreach ($freeCommentIds as $value) {
                    $lessonModel->updateInfo(['id' => $value], ['commentRank' => $num]);
                    $num ++;
                }
                $num = 1;
                foreach ($payCommentIds as $value) {
                    $payLessonModel->updateInfo(['id' => $value], ['commentRank' => $num]);
                    $num ++;
                }

                //评分
                $freeScoreIds = $lessonModel->getIdsByScore($v);
                $payScoreIds = $payLessonModel->getIdsByScore($v);
                $num = 1;
                foreach ($freeScoreIds as $value) {
                    $lessonModel->updateInfo(['id' => $value], ['scoreRank' => $num]);
                    $num ++;
                }
                $num = 1;
                foreach ($payScoreIds as $value) {
                    $payLessonModel->updateInfo(['id' => $value], ['scoreRank' => $num]);
                    $num ++;
                }
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $output->writeln($e->getMessage());
        }

        $output->writeln('ok');
    }
}

