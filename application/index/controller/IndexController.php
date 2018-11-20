<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\controller\Constant;
use app\index\model\CommentModel;
use app\index\model\LessonModel;
use QL\Ext\PhantomJs;
use QL\QueryList;
use think\Db;

class IndexController extends BaseController
{
    /**
     * 默认显示
     */
    public function index()
    {
        echo '你好';
    }

    /**
     * 抓取入口
     */
    public function grab()
    {
        $result = $this->grabMOOC();

        $this->ajaxReturn($result['code'], $result['msg'], $result['data']);
    }

    /**
     * 抓取慕课课程及评论
     * @return array
     */
    public function grabMOOC()
    {
        $lessonModel = new LessonModel();
        $commentModel = new CommentModel();

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        $count = 0;
        Db::startTrans();
        try {
            //抓取前3页课程，一共90个。（每页30个，如果想抓120个则将3改为4）
            for ($i = 1; $i <= 3; $i++) {
                $url = 'https://www.imooc.com/course/list?sort=pop&page=' . $i;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $content = curl_exec($ch);
                curl_close($ch);

                $html = $ql->html($content)->rules([
                    'url' => ['.course-list .course-card', 'href'],
                    'studyNum' => ['div.container div.course-card-info', 'text', '-span:eq(0)'],
                ])->query()->getData();

                //循环每个课程
                foreach ($html as $v) {
                    $info['studyNum'] = $v['studyNum'];
                    $info['id'] = explode('/', $v['url'])[2];//课程ID（慕课网上的ID）
                    $info['url'] = 'https://www.imooc.com' . $v['url'];//课程网址
                    $info['grabTime'] = date('Y-m-d H:i:s');

                    //查找数据库，判断当前课程是否已存在，如果存在则跳过此课程
                    $result = $lessonModel->getLessonCount(['id' => $info['id']]);
                    if ($result != 0) {
                        continue;
                    }
                    $count += 1;
                    if ($i == 1 && $count == 5) {
                        break;
                    }
                    //抓取课程信息，并存入数据库
                    $data = $this->grabMOOCLessonInfo($info['url']);
                    $lessonInfo = array_merge($info, $data);
                    /* TODO 如果想存入数据库，则将以下注释去了 */
//                    $lessonModel->addLesson($lessonInfo);

                    //抓取课程评论，并存入数据库
                    $commentUrl = 'https://www.imooc.com/coursescore/' . $info['id'];
                    $commentInfo = $this->grabMOOCComment($commentUrl);
                    $nowTime = date('Y-m-d H:i:s');

                    foreach ($commentInfo as $value) {
                        //添加评论
                        $value['lessonId'] = $info['id'];
                        $value['grabTime'] = $nowTime;
                        /* TODO 如果想存入数据库，则将以下注释去了 */
                        $commentModel->addComment($value);
                    }
                }
                if ($i == 1 && $count == 5) {
                    break;
                }
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return return_array(1002, '抓取失败，请重试。'. '原因：'. $e->getMessage());
        }

        return return_array(1000, 'ok');
    }

    /**
     * 抓取慕课课程信息
     * @param $url string 课程地址
     * @return mixed
     */
    protected function grabMOOCLessonInfo($url)
    {
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        $html = $ql->html($content)->rules([
            'name' => ['div#main h2.l', 'html'],
            'introduction' => ['div.content div.course-description', 'html'],
            'curriculumClassification' => ['div.course-infos div.path a:eq(1)', 'html'],
            'difficulty' => ['div.course-infos div.static-item:first span.meta-value', 'html'],
            'totalTime' => ['div.course-infos div.static-item:eq(1) span.meta-value', 'html'],
            'comprehensiveScore' => ['div.course-infos div.static-item:eq(3) span.meta-value:first', 'html'],
            'commentNum' => ['div.course-info-menu ul.course-menu li:eq(3) span', 'html'],
        ])->query()->getData();

        return $html[0];
    }

    /**
     * 抓取慕课评论
     * @param $url string 抓取的页面网址（不带page）
     * @param $lessonId int 课程ID
     * return array
     */
    protected function grabMOOCComment($url)
    {
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        //根据第一页，获取总页数
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        $href = $ql->html($content)->find('.page > a:last')->attr('href');
        $totalPage = explode('=', $href)[1];

        $data = [];
        $ch = curl_init();
        for ($i = 1; $i <= $totalPage; $i++) {
            $urls = $url. '?page='. $i;

            curl_setopt($ch, CURLOPT_URL, $urls);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($ch);

            $html = $ql->html($content)->rules([
                'score' => ['div.evaluation-list div.star-box span', 'html'],
                'content' => ['div.evaluation-list div.evaluate div.content-box p.content', 'html'],
            ])->query()->getData();

            //转换数组
            $arr = [];
            foreach ($html as $v) {
                $arr[] = [
                    'score' => intval(str_replace('分', '', $v['score'])),
                    'content' => trim($v['content']),
                ];
            }

            $data = array_merge($data, $arr);
        }
        curl_close($ch);

        return $data;
    }
}

