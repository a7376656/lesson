<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\controller\Constant;
use app\index\model\AuthorModel;
use app\index\model\ClassificationModel;
use app\index\model\CommentModel;
use app\index\model\DataModel;
use app\index\model\LessonModel;
use app\index\model\PayCommentModel;
use app\index\model\PayLessonModel;
use QL\Ext\PhantomJs;
use QL\QueryList;
use Symfony\Component\VarDumper\Cloner\Data;
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
     * 测试api
     */
    public function testApi()
    {
        $data = [
            'name' => 'Python教学',
            'author' => 'Silence',
            'price' => 999,
        ];

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取讲师主页链接
     */
    public function getAuthorUrl()
    {

    }

    /**
     * 抓取免费课程入口
     */
    public function grab()
    {
        $result = $this->grabMOOC();

        $this->ajaxReturn($result['code'], $result['msg'], $result['data']);
    }

    /**
     * 抓取慕课课程及评论（根据学习人数排名前120）
     * @return array
     */
    public function grabMOOC()
    {
        $lessonModel = new LessonModel();
        $commentModel = new CommentModel();

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        $count = $now = 0;
        Db::startTrans();
        try {
            //抓取前3页课程，一共90个。（每页30个，如果想抓120个则将3改为4）
            for ($i = 1; $i <= 4; $i++) {
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
                    $now += 1;
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
                    //一次只抓10个
//                    if ($count == 11) {
//                        break;
//                    }
                    //抓取课程信息，并存入数据库
                    $data = $this->grabMOOCLessonInfo($info['url']);
                    if (!preg_match('/小时/i', $data['totalTime'])) {//统一时间格式
                        $data['totalTime'] = '0小时'. $data['totalTime'];
                    }
                    $lessonInfo = array_merge($info, $data);
                    /* TODO 如果想存入数据库，则将以下注释去了 */
                    $lessonModel->addLesson($lessonInfo);

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
                    //前十名课程一次只抓一个
                    if (in_array($now, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
                        break;
                    }
                }
                //一次只抓10个
//                if ($count == 11) {
//                    break;
//                }
                //前十名课程一次只抓一个
                if (in_array($now, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
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
            'author' => ['div.course-infos span.tit a', 'html'],
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

    /**
     * 抓取付费课程入口
     */
    public function grabPay()
    {
        $result = $this->grabPayLesson();

        $this->ajaxReturn($result['code'], $result['msg'], $result['data']);
    }

    /**
     * 抓取付费课程(全部）
     * @return array
     */
    public function grabPayLesson()
    {
        $payLessonModel = new PayLessonModel();
        $payCommentModel = new PayCommentModel();

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        //获取总页数
        $url = 'https://coding.imooc.com/?sort=3';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        $html = $ql->html($content)->rules([
            'totalPage' => ['div.index-main div.index-list-wrap div.page a:last', 'href'],
        ])->query()->getData();
        $total = explode('=', $html[0]['totalPage'])[3];

        Db::startTrans();
        try {
            for ($i = 1; $i <= $total; $i++) {
                $url = 'https://coding.imooc.com/?sort=3&page=' . $i;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $content = curl_exec($ch);
                curl_close($ch);

                $html = $ql->html($content)->rules([
                    'url' => ['div.shizhan-course-list div.shizhan-course-wrap a', 'href'],
                    'studyNum' => ['div.shizhan-course-list div.evaluation-box div.shizhan-info', 'text', '-span:eq(0), -span:eq(2)'],
                    'commentNum' => ['div.shizhan-course-list div.evaluation-desc-box div.left-box', 'text', '-p:eq(0), -p:eq(1)'],
                ])->query()->getData();

                //循环每个课程
                foreach ($html as $v) {
//                    print_r($v);
                    $info['studyNum'] = $v['studyNum'];
                    $info['commentNum'] = str_replace('人评价', '', $v['commentNum']);
                    $info['id'] = explode('.', explode('/', $v['url'])[2])[0];//课程ID（慕课网上的ID）
                    $info['url'] = 'https://coding.imooc.com' . $v['url'];//课程网址
                    $info['grabTime'] = date('Y-m-d H:i:s');

                    //查找数据库，判断当前课程是否已存在，如果存在则跳过此课程
                    $result = $payLessonModel->getLessonCount(['id' => $info['id']]);
                    if ($result != 0) {
                        continue;
                    }
                    //抓取课程信息，并存入数据库
                    $data = $this->grabPayLessonInfo($info['url']);
                    $data['comprehensiveScore'] = str_replace('分', '', $data['comprehensiveScore']);//去掉分
                    $data['curriculumClassification'] = '实战';//付费课程课程分类全为实战
                    if (!preg_match('/小时/i', $data['totalTime'])) {//统一时间格式
                        $data['totalTime'] = '0小时'. $data['totalTime'];
                    }
                    $lessonInfo = array_merge($info, $data);
                    /* TODO 如果想存入数据库，则将以下注释去了 */
                    $payLessonModel->addLesson($lessonInfo);

                    //抓取课程评论，并存入数据库
                    $commentUrl = 'https://coding.imooc.com/class/evaluation/' . $info['id'] . '.html';
                    $commentInfo = $this->grabPayComment($commentUrl);
                    $nowTime = date('Y-m-d H:i:s');

                    foreach ($commentInfo as $value) {
                        //添加评论
                        $value['lessonId'] = $info['id'];
                        $value['grabTime'] = $nowTime;
                        /* TODO 如果想存入数据库，则将以下注释去了 */
                        $payCommentModel->addComment($value);
                    }
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
     * 抓取付费课程信息
     * @param $url string 课程地址
     * @return mixed
     */
    protected function grabPayLessonInfo($url)
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
            'name' => ['div.course-class-infos div.title-box h1', 'html'],
            'author' => ['div.course-infos-t div.detailContent div.nickname', 'html'],
            'introduction' => ['div.course-infos-t div#videoInfo div.info-desc', 'html'],
            'price' => ['div.course-class-infos div.price-box span.cur-price b:eq(0)', 'html'],
            'difficulty' => ['div.course-class-infos div.static-item:first span.meta-value strong', 'html'],
            'totalTime' => ['div.course-class-infos div.static-item:eq(1) span.meta-value strong', 'html'],
            'comprehensiveScore' => ['div.course-class-infos div.static-item:eq(3) span.meta-value:first strong', 'html'],
        ])->query()->getData();

        return $html[0];
    }

    /**
     * 抓取付费课程评论
     * @param $url string 抓取的页面网址（不带page）
     * @param $lessonId int 课程ID
     * return array
     */
    protected function grabPayComment($url)
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

        $totalPage = ($href == '' || $href == null) ? 1 : explode('=', $href)[1];

        $data = [];
        $ch = curl_init();
        for ($i = 1; $i <= $totalPage; $i++) {
            $urls = $url. '?page='. $i;

            curl_setopt($ch, CURLOPT_URL, $urls);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($ch);

            $html = $ql->html($content)->rules([
                'content' => ['p.cmt-txt', 'html'],
            ])->query()->getData();

            //转换数组
            $arr = [];
            foreach ($html as $v) {
                $arr[] = [
                    'content' => trim($v['content']),
                ];
            }

            $data = array_merge($data, $arr);
        }
        curl_close($ch);

        return $data;
    }

    /**
     * 更新免费课程中讲师主页链接
     * @return array
     */
    public function updateFreeLessonAuthorUrl()
    {
        $lessonModel = new LessonModel();

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        Db::startTrans();
        try {
            $ids = $lessonModel->getAllLessonIds();

            foreach ($ids as $v) {
                $url = 'https://www.imooc.com/learn/'. $v;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $content = curl_exec($ch);
                curl_close($ch);

                $href = 'https://www.imooc.com'. $ql->html($content)->find('div.course-infos div.teacher-info a:first')->attr('href');

                $lessonModel->updateInfo(['id' => $v], ['authorUrl' => $href]);
            }

            Db::commit();
            return return_array(1000, 'ok');
        } catch (\Exception $e) {
            Db::rollback();
            return return_array(1002, '抓取失败，请重试。'. '原因：'. $e->getMessage());
        }
    }

    /**
     * 更新付费课程中讲师主页链接
     * @return array
     */
    public function updatePayLessonAuthorUrl()
    {
        $payLessonModel = new PayLessonModel();

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        Db::startTrans();
        try {
            $ids = $payLessonModel->getAllLessonIds();

            foreach ($ids as $v) {
                $url = 'https://coding.imooc.com/class/'. $v .'.html#Anchor';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $content = curl_exec($ch);
                curl_close($ch);

                $href = $ql->html($content)->find('.teacher > a')->attr('href');

                $payLessonModel->updateInfo(['id' => $v], ['authorUrl' => $href]);
            }

            Db::commit();
            return return_array(1000, 'ok');
        } catch (\Exception $e) {
            Db::rollback();
            return return_array(1002, ' 抓取失败，请重试。'. '原因：'. $e->getMessage());
        }
    }

    /**
     * 添加讲师进讲师表
     */
    public function addLecture()
    {
        $authorModel = new AuthorModel();
        $lessonModel = new LessonModel();
        $payLessonModel = new PayLessonModel();

        $freeList = $lessonModel->getLessonListByWhere([], 'author,authorUrl');
        $payList = $payLessonModel->getLessonListByWhere([], 'author,authorUrl');

        //取出所有讲师id
        $idArray = [];
        $info = [];
        foreach ($freeList as $v) {
            $num = explode('/', $v['authorUrl'])[4];
            if (!in_array($num, $idArray)) {
                $idArray[] = $num;
                $info[] = [
                    'id' => $num,
                    'name' => $v['author'],
                    'url' => $v['authorUrl'],
                ];
            }
        }
        foreach ($payList as $v) {
            $num = explode('/', $v['authorUrl'])[4];
            if (!in_array($num, $idArray)) {
                $idArray[] = $num;
                $info[] = [
                    'id' => $num,
                    'name' => $v['author'],
                    'url' => $v['authorUrl'],
                ];
            }
        }

        Db::startTrans();
        try {
            //抓取讲师信息
            $ql = QueryList::getInstance();
            $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);
            $data = [];
            foreach ($info as $v) {
                $url = 'https://www.imooc.com/u/' . $v['id'];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $content = curl_exec($ch);
                curl_close($ch);

                if ($content == '' || $content == null) {
                    $url = 'https://www.imooc.com/t/' . $v['id'];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $content = curl_exec($ch);
                    curl_close($ch);

                    $html = $ql->html($content)->rules([
                        'payNum' => ['div.tea-detail-box li:eq(0) p.num', 'html'],
                        'freeNum' => ['div.tea-detail-box li:eq(1) p.num', 'html'],
                        'fansNum' => ['div.tea-detail-box li:last p.num', 'html'],
                    ])->query()->getData();

                    $payNum = $html[0]['payNum'];
                    $freeNum = $html[0]['freeNum'];
                    $fansNum = $html[0]['fansNum'];

                } else {
                    $url = 'http://www.imooc.com/u/' . $v['id'] . '/courses?sort=publish';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $content = curl_exec($ch);
                    curl_close($ch);

                    $html = $ql->html($content)->rules([
                        'list' => ['div.u-container ul.clearfix > li', 'html'],
                    ])->query()->getData();

                    $freeNum = 0;
                    foreach ($html->all() as $value) {
                        $freeNum += 1;
                    }
                    $payNum = 0;

                    $html = $ql->html($content)->rules([
                        'fansNum' => ['div.study-info div.follows:last em', 'html'],
                    ])->query()->getData();

                    $fansNum = $html[0]['fansNum'];
                }

                $data = [
                    'id' => $v['id'],
                    'url' => $v['url'],
                    'name' => $v['name'],
                    'fans' => $fansNum,
                    'freeLessonNumber' => $freeNum,
                    'payLessonNumber' => $payNum,
                ];

                $authorModel->addAuthor($data);
            }

            Db::commit();
            return return_array(1000, 'ok');
        } catch (\Exception $e) {
            Db::rollback();
            return return_array(1002, $e->getMessage());
        }
    }

    /**
     * 设置课程数据总览
     */
    public function setMOOCData()
    {
        $dataModel = new DataModel();
        $authorModel = new AuthorModel();
        $commentModel = new CommentModel();
        $payLessonModel = new PayLessonModel();
        $classificationModel = new ClassificationModel();

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, Constant::LINUX_PHANTOM_URL);

        //根据第一页获取总页数
        $url = 'https://www.imooc.com/course/list?sort=pop';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        $html = $ql->html($content)->rules([
            'totalNum' => ['div.page a:last', 'href'],
        ])->query()->getData();

        $totalNum = explode('=', $html[0]['totalNum'])[2];//总页数

        //查询最后一页有多少个课程
        $url = 'https://www.imooc.com/course/list?sort=pop&page='. $totalNum;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        $html = $ql->html($content)->rules([
            'content' => ['div.course-list div.course-card-container', 'html'],
        ])->query()->getData();

        //算出总课程数
        $freeNum = ($totalNum - 1) * 30;
        foreach ($html->all() as $v) {
            $freeNum ++;
        }

        $data = [
            'freeNum' => $freeNum,
            'payNum' => $payLessonModel->getLessonCount(),//付费课程数量
            'lecturerNum' => $authorModel->getAuthorCount(),//讲师数量
            'classNum' => $classificationModel->getClassificationCount(),//分类数量
            'commentNum' => $commentModel->getCommentCount() + $payLessonModel->getTotalCommentNum(),//所有评论数量
        ];

        $result = $dataModel->updateInfo(['id' => 1], $data);
        if ($result !== false) {
            return return_array(1000, 'ok');
        }

        return return_array(1002, '更新失败');
    }

    /**
     * 设置付费课程评论分数
     * @return bool
     */
    public function setPayCommentScore()
    {
        $payCommentModel = new PayCommentModel();

        $ids = $payCommentModel->column('id');

        $ids = array_rand($ids, 24);

        $result = $payCommentModel->where(['id' => ['in', $ids]])->update(['score' => 5]);
//        $result = $payCommentModel->where(['id' => ['lt', 1000000]])->update(['score' => 10]);
        if ($result !== false) {
            return true;
        }

        return false;
    }
}

