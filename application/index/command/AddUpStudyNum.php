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
use app\index\model\LessonModel;
use app\index\model\PayLessonModel;
use app\index\model\TimeLineModel;
use QL\Ext\PhantomJs;
use QL\QueryList;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class AddUpStudyNum extends Command
{
    protected function configure()
    {
        $this->setName('addUpStudyNum')->setDescription('每天凌晨0:00执行一次');
    }

    /**
     * 统计昨天一整天学习人数
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     */
    protected function execute(Input $input, Output $output)
    {
        $lessonModel = new LessonModel();
        $timeLineModel = new TimeLineModel();

        $freeLessonIds = $lessonModel->getAllLessonIds();//获取所有课程id

        $yesterday = date('Y-m-d', strtotime('yesterday'));//昨天日期
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));//昨天的七天前日期

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        for ($i = 1; $i < 5; $i++) {
            $url = 'https://www.imooc.com/course/list?sort=pop&page='. $i;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($ch);
            curl_close($ch);

            $html = $ql->html($content)->rules([
                'url' => ['.course-list .course-card', 'href'],
                'name' => ['h3.course-card-name', 'html'],
                'studyNum' => ['div.container div.course-card-info', 'text', '-span:eq(0)'],
            ])->query()->getData();

            foreach ($html as $v) {
                $info = [
                    'id' => explode('/', $v['url'])[2],//课程ID（慕课网上的ID）
                    'name' => $v['name'],
                    'todayNum' => $v['studyNum'],
                    'date' => $yesterday,
                    'flag' => Constant::FREE_LESSON,
                ];

                if (in_array($info['id'], $freeLessonIds)) {
                    //获取七天前的学习人数
                    $sevenDaysAgoNum = $timeLineModel->getInfoByWhere([
                        'id' => $info['id'],
                        'date' => $sevenDaysAgo,
                        'flag' => Constant::FREE_LESSON,
                    ], 'todayNum')['todayNum'];
                    //计算增长率
                    $info['rate'] = $info['todayNum'] - $sevenDaysAgoNum;
                    //判断数据库中是否已有当天的数据
                    $result = $timeLineModel->getInfoByWhere([
                        'id' => $info['id'],
                        'date' => $yesterday,
                        'flag' => Constant::FREE_LESSON,
                    ], 'id');
                    if ($result) {
                        continue;
                    }
                    //添加进数据库
                    $timeLineModel->addTimeLine($info);
                }
            }
        }

        $output->writeln('ok');
    }
}

