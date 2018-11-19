<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 13:32
 */
namespace app\index\command;

use app\index\model\CommentModel;
use app\index\model\LessonModel;
use app\index\model\UserModel;
use think\console\command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Clear extends Command
{
    protected function configure()
    {
        $this->setName('clear')->setDescription('清除数据库里所有课程以及评论');
    }

    protected function execute(Input $input, Output $output)
    {
        $lessonModel = new LessonModel();

        Db::startTrans();
        try {
            $lessonModel->execute($sql = 'TRUNCATE table l_lesson');
            $lessonModel->execute($sql = 'TRUNCATE table l_comment');

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $output->writeln('删除失败');
        }

        $output->writeln('ok');
    }
}
