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
use think\Db;

class Test extends Command
{
    protected function configure()
    {
        $this->setName('test')->setDescription('测试定时事件');
    }

    /**
     * 抓取慕课网免费课程，热门排序中90个课程及其评论
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     */
    protected function execute(Input $input, Output $output)
    {
        Db::table('test')->insert([
            'msg' => '测试成功',
            'createTime' => date('Y-m-d H:i:s'),
        ]);
        $output->writeln('ok');
    }
}

