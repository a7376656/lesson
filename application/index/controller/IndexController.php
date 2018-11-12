<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\index\model\LessonModel;
use JonnyW\PhantomJs\Client;
use QL\Ext\PhantomJs;
use QL\QueryList;
use think\Db;

class IndexController extends BaseController
{
    /**
     * TODO 默认方法
     */
    public function index()
    {
        $this->ajaxReturn(1000, 'ok');
    }
    
    /**
     * TODO 抓取淘宝/天猫商品图片，这里不需要模拟加载，因此直接用Vquery就行了
     */
    public function grabAliGoodsPic()
    {
        $url = 'https://item.taobao.com/item.htm?spm=a217h.9580640.831236.9.354025aadOK1X2&id=559553768063&scm=1007.12144.81309.69881_0';

        $result = file_get_contents($url);

        $res = mb_convert_encoding($result, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//解决中文乱码

        //抓取图片
        $vq = new \Vquery($res);
        $vq = $vq->find('id=\"J_UlThumb\"')->find('a');
        $vq = $vq->attr('src');

        //将图片分辨率转为400*400
        $arr = [];
        foreach ($vq[0] as $v) {
            $str = htmlspecialchars($v);
            $str = str_replace('50x50', '400x400', $str);
            $str = str_replace('60x60', '400x400', $str);

            $arr[] = $str;
        }

        $this->ajaxReturn(1000, 'ok', $arr);
    }

    /**
     * TODO 抓取淘宝商品详情，因为需要模拟加载，使用Phantomjs+Vquery，Phantomjs可以模拟加载
     */
    public function grabTbDetail()
    {
        $url = 'https://item.taobao.com/item.htm?spm=a217h.9580640.831236.9.354025aadOK1X2&id=559553768063&scm=1007.12144.81309.69881_0';

        $client = Client::getInstance();
        $client->isLazy(); // 让客户端等待所有资源加载完毕
        $client->getEngine()->setPath('D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe');//phantomjs路径，使用时将这里换成你电脑上的路径，服务器上的路径也是不一样的

        $request = $client->getMessageFactory()->createRequest();
        $response = $client->getMessageFactory()->createResponse();
        //设置超时时间
        $request->setTimeout(5000);
        $request->setDelay(10);
        //设置请求方法
        $request->setMethod('GET');
        //设置请求连接
        $request->setUrl($url);

        //发送请求获取响应
        $client->send($request, $response);
        if($response->getStatus() === 200) {
            //抓取内容
            $content = $response->getContent();
            //获取内容后的处理
            $vq = new \Vquery($content);
            $vq = $vq->find('id=\"description\"')->html();
        }

        $imgArray = [];
        $str = $vq[0][0];
        //正则匹配详情图片
        if (preg_match_all('/https:\/\/img\.alicdn(.*?)\!\!(.*?)\.(jpg|png|jpeg|gif)/i', $str, $match)) {
            $imgArray = $match[0];
        }

        $this->ajaxReturn(1000, 'ok', $imgArray);
    }

    /**
     * TODO 抓取课程
     */
    public function grabLesson()
    {
        $url = 'https://www.coursetalk.com/search?filters=&sort=-rating';

        $content = file_get_contents($url);

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, 'D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe');
        //抓取详情页面
        $html = $ql->html($content)->rules([
            'content' => ['div#js-top-reviews-container div.course-listing-card div.course-listing div.course-listing-summary__name span', 'html']
        ])->query()->getData();

        $arr = [];
        foreach ($html as $v) {
            $arr[]  = trim($v['content']);
        }

        print_r($arr);
        die();
    }

    /**
     * TODO 抓取课程评论
     */
    public function grabLessonComment()
    {
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, 'D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe');

        //根据第一页，获取总页数
        $url = 'https://www.coursetalk.com/providers/coursera/courses/an-introduction-to-interactive-programming-in-python?page=1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);
        $totalPage = $ql->html($content)->find('.reviews-list__pagination input')->attr('value');

        $data = [];
        $ch = curl_init();
        for ($i = 1; $i <= 2; $i++) {
            $url = 'https://www.coursetalk.com/providers/coursera/courses/an-introduction-to-interactive-programming-in-python?page='. $i;

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($ch);

            //抓取详情页面
            $html = $ql->html($content)->rules([
                'content' => ['div.reviews-list div.review__content span.more-less-trigger__text--full', 'html'],
            ])->query()->getData();

            //转换数组
            $arr = [];
            foreach ($html as $v) {
                $arr[]  = trim($v['content']);
            }

            $data = array_merge($data, $arr);
        }
        curl_close($ch);

        print_r($data);
        die();

    }

    public function grabMOOCLesson()
    {
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, 'D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe');

        $url = 'https://www.imooc.com/course/list?sort=pop&page=1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        $html = $ql->html($content)->rules([
            'content' => ['div.course-list h3.course-card-name', 'html'],
            'href' => ['.course-list .course-card', 'href']
        ])->query()->getData();

        $arr = [];
        foreach ($html as $v) {
            $arr[] = [
                'content' => $v['content'],
                'href' => $v['href']
            ];
        }

        print_r($arr);
        die();
    }

    /**
     * TODO 抓取慕课课程及评论
     */
    public function grabMOOC()
    {
        $lessonModel = new LessonModel();

        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, 'D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe');

        $lessonInfo = [];
        for ($i = 1; $i <= 1; $i++) {
            $url = 'https://www.imooc.com/course/list?sort=pop&page='. $i;
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

                //查找数据库，判断当前课程是否已存在，如果存在则跳过此课程
                $result = $lessonModel->getLessonCount(['id' => $info['id']]);
                if ($result != 0) {
                    continue;
                }

                //抓取课程信息，并存入数据库
                $data = $this->grabMOOCLessonInfo($info['url']);
                $lessonInfo = array_merge($info, $data);
                $lessonModel->addLesson($lessonInfo);

                $this->ajaxReturn(1000, 'ok');
                die();
                //抓取课程评论，并存入数据库
                $commentUrl = 'https://www.imooc.com/coursescore/'. $info['id'];
                $commentInfo = $this->grabMOOCComment($commentUrl);

                foreach ($commentInfo as $value) {

                }
            }
            break;
        }

        print_r($lessonInfo);
        die();
    }

    /**
     * TODO 抓取慕课课程信息
     * @param $url string 课程地址
     * @return mixed
     */
    public function grabMOOCLessonInfo($url)
    {
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, 'D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe');

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
            'commentNum' => ['div.course-info-menu ul.course-menu li:last span', 'html'],
        ])->query()->getData();

        return $html[0];
    }

    /**
     * TODO 抓取慕课评论
     * @param $url string 抓取的页面网址（不带page）
     * @param $lessonId int 课程ID
     * return array
     */
    public function grabMOOCComment($url)
    {
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, 'D:\xampp\htdocs\lesson\runtime\phantomjs\phantomjs.exe');

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
                'content' => ['div.evaluation-list div.evaluate div.content-box p.content', 'html'],
            ])->query()->getData();

            //转换数组
            $arr = [];
            foreach ($html as $v) {
                $arr[]  = trim($v['content']);
            }

            $data = array_merge($data, $arr);
        }
        curl_close($ch);

        return $data;
    }
}

