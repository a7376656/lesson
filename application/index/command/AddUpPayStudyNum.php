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

class AddUpPayStudyNum extends Command
{
    protected function configure()
    {
        $this->setName('addUpPayStudyNum')->setDescription('统计付费课程昨天一整天学习人数');
    }

    /**
     * 统计昨天一整天学习人数
     * @param Input $input
     * @param Output $output
     * @return int|null|void
     */
    protected function execute(Input $input, Output $output)
    {
        $payLessonModel = new PayLessonModel();
        $timeLineModel = new TimeLineModel();

        $payLessonIds = $payLessonModel->getAllLessonIds();

        $yesterday = date('Y-m-d', strtotime('yesterday'));//昨天日期
        $twoDaysAgo = date('Y-m-d', strtotime('-2 days'));//前天日期，计算增长率

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        for ($i = 1; $i < 4; $i++) {
            $url = 'https://coding.imooc.com/?sort=3&page='. $i;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($ch);
            curl_close($ch);

            $html = $ql->html($content)->rules([
                'url' => ['div.shizhan-course-list div.shizhan-course-wrap a', 'href'],
                'name' => ['div.index-list-wrap p.shizan-name', 'html'],
                'studyNum' => ['div.shizhan-course-list div.evaluation-box div.shizhan-info', 'text', '-span:eq(0), -span:eq(2)'],
            ])->query()->getData();

            foreach ($html as $v) {
                $info = [
                    'id' => explode('.', explode('/', $v['url'])[2])[0],//课程ID（慕课网上的ID）
                    'name' => $v['name'],
                    'todayNum' => $v['studyNum'],
                    'date' => $yesterday,
                    'flag' => Constant::PAY_LESSON,
                ];

                if (in_array($info['id'], $payLessonIds)) {
                    //获取前天的学习人数
                    $twoDaysAgoNum = $timeLineModel->getInfoByWhere([
                        'id' => $info['id'],
                        'date' => $twoDaysAgo,
                        'flag' => Constant::PAY_LESSON,
                    ], 'todayNum')['todayNum'];
                    //计算增长率
                    $info['rate'] = sprintf('%.7f', ($info['todayNum'] - $twoDaysAgoNum) / $twoDaysAgoNum);
                    //判断数据库中是否已有当天的数据
                    $result = $timeLineModel->getInfoByWhere([
                        'id' => $info['id'],
                        'date' => $yesterday,
                        'flag' => Constant::PAY_LESSON,
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

