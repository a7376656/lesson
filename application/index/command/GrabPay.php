<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5
 * Time: 13:08
 */
namespace app\index\command;

use app\index\controller\IndexController;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class GrabPay extends Command
{
    protected function configure()
    {
        $this->setName('grabPay')->setDescription('抓取慕课付费课程及评论');
    }

    /**
     * 抓取慕课网免费课程，热门排序中90个课程及其评论
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     */
    protected function execute(Input $input, Output $output)
    {
        $indexController = new IndexController();
        $result = $indexController->grabPayLesson();

        $output->writeln($result['msg']);
    }
}

