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
use app\index\model\TimeLineModel;
use app\index\validate\LessonValidate;
use QL\Ext\PhantomJs;
use QL\QueryList;
use think\Db;

class LessonController extends BaseController
{
    /**
     * 默认显示
     */
    public function index()
    {
        echo '你好';
    }

    /**
     * 首页数据
     */
    public function moocOverview()
    {
        $dataModel = new DataModel();

        //获取慕课网教师数、分类数、课程数目、评论数
        $result = $dataModel->getInfo();

        $data = [
            "lessonNum" => [
                "free" => $result['freeNum'],
                "pay" => $result['payNum'],
            ],
            "lecturerNum" => $result['lecturerNum'],
            "classNum" => $result['classNum'],
            "commentNum" => $result['commentNum']
        ];

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取所有免费课程的评价概况
     */
    public function freeGradeOverview()
    {
        $lessonModel = new LessonModel();
        $classificationModel = new ClassificationModel();

        //初始化最终输出的数据
        $data = [
            'grade' => [
                'title' => '评分范围分布',
                'category' => [],
            ],
        ];

        //获取所有的分类
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $gradeArray = [];

            //评分。每个分类下 各个评分段的课程数量 评分段为左闭右开['<6', '6-8', '8-9', '9-9.5', '9.5-10', '10']
            $gradeArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => ['<', 6]]);
            $gradeArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 6], ['<', 8]]]);
            $gradeArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 8], ['<', 9]]]);
            $gradeArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 9], ['<', 9.5]]]);
            $gradeArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 9.5], ['<', 10]]]);
            $gradeArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => ['>=', 10]]);
            //赋值
            $data['grade']['category'][] = [
                'name' => $v,
                'values' => $gradeArray,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取所有免费课程的时长概况
     */
    public function freeDurationOverview()
    {
        $lessonModel = new LessonModel();
        $classificationModel = new ClassificationModel();

        //初始化最终输出的数据
        $data = [
            'duration' => [
                'title' => '时长数据分布',
                'category' => [],
            ],
        ];

        //获取所有的分类
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $durationArray = [0, 0, 0, 0, 0, 0, 0, 0];
            //时长。每个分类下 各个时长段的课程数量 时长段为（左开右闭）['<1','1-3', '3-6', '6-10', '10-15','15-20','20-30',>=30]
            //取出所有时长值，进行字符串处理
            $durationList = $lessonModel->getLessonListByWhere(['curriculumClassification' => $v], 'totalTime');
            foreach ($durationList as $value) {
                $num = explode('小时', $value['totalTime'])[0];
                switch ($num) {
                    case $num < 1:
                        $durationArray[0] += 1;
                        break;
                    case $num >= 1 && $num < 3:
                        $durationArray[1] += 1;
                        break;
                    case $num >= 3 && $num < 6:
                        $durationArray[2] += 1;
                        break;
                    case $num >= 6 && $num < 10:
                        $durationArray[3] += 1;
                        break;
                    case $num >= 10 && $num < 15:
                        $durationArray[4] += 1;
                        break;
                    case $num >= 15 && $num < 20:
                        $durationArray[5] += 1;
                        break;
                    case $num >= 20 && $num < 30:
                        $durationArray[6] += 1;
                        break;
                    case $num >= 30:
                        $durationArray[7] += 1;
                        break;
                }
            }
            //赋值
            $data['duration']['category'][] = [
                'name' => $v,
                'values' => $durationArray,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取所有免费课程的难度概况
     */
    public function freeDifficultyOverview()
    {
        $lessonModel = new LessonModel();
        $classificationModel = new ClassificationModel();

        //初始化最终输出的数据
        $data = [
            'difficulty' => [
                'title' => '难度等级分布',
                'category' => [],
            ],
        ];

        //获取所有的分类
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $difficultyArray = [];
            //难度。每个分类下 各个评分段的课程数量 难度段为['入门', '初级', '中级', '高级']
            $difficultyArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '入门']);
            $difficultyArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '初级']);
            $difficultyArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '中级']);
            $difficultyArray[] = $lessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '高级']);
            //赋值
            $data['difficulty']['category'][] = [
                'name' => $v,
                'values' => $difficultyArray,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取所有免费课程的评价概况
     */
    public function payGradeOverview()
    {
        $payLessonModel = new PayLessonModel();
        $classificationModel = new ClassificationModel();

        //初始化最终输出的数据
        $data = [
            'grade' => [
                'title' => '评分范围分布',
                'category' => [],
            ],
        ];

        //获取所有的分类
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $gradeArray = [];

            //评分。每个分类下 各个评分段的课程数量 评分段为左闭右开['<6', '6-8', '8-9', '9-9.5', '9.5-10', '10']
            $gradeArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => ['<', 6]]);
            $gradeArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 6], ['<', 8]]]);
            $gradeArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 8], ['<', 9]]]);
            $gradeArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 9], ['<', 9.5]]]);
            $gradeArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => [['>=', 9.5], ['<', 10]]]);
            $gradeArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'comprehensiveScore' => ['>=', 10]]);
            //赋值
            $data['grade']['category'][] = [
                'name' => $v,
                'values' => $gradeArray,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取所有免费课程的时长概况
     */
    public function payDurationOverview()
    {
        $payLessonModel = new PayLessonModel();
        $classificationModel = new ClassificationModel();

        //初始化最终输出的数据
        $data = [
            'duration' => [
                'title' => '时长数据分布',
                'category' => [],
            ],
        ];

        //获取所有的分类
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $durationArray = [0, 0, 0, 0, 0, 0, 0, 0];
            //时长。每个分类下 各个时长段的课程数量 时长段为（左开右闭）['<1','1-3', '3-6', '6-10', '10-15','15-20','20-30',>=30]
            //取出所有时长值，进行字符串处理
            $durationList = $payLessonModel->getLessonListByWhere(['curriculumClassification' => $v], 'totalTime');
            foreach ($durationList as $value) {
                $num = explode('小时', $value['totalTime'])[0];
                switch ($num) {
                    case $num < 1:
                        $durationArray[0] += 1;
                        break;
                    case $num >= 1 && $num < 3:
                        $durationArray[1] += 1;
                        break;
                    case $num >= 3 && $num < 6:
                        $durationArray[2] += 1;
                        break;
                    case $num >= 6 && $num < 10:
                        $durationArray[3] += 1;
                        break;
                    case $num >= 10 && $num < 15:
                        $durationArray[4] += 1;
                        break;
                    case $num >= 15 && $num < 20:
                        $durationArray[5] += 1;
                        break;
                    case $num >= 20 && $num < 30:
                        $durationArray[6] += 1;
                        break;
                    case $num >= 30:
                        $durationArray[7] += 1;
                        break;
                }
            }
            //赋值
            $data['duration']['category'][] = [
                'name' => $v,
                'values' => $durationArray,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取所有免费课程的难度概况
     */
    public function payDifficultyOverview()
    {
        $payLessonModel = new PayLessonModel();
        $classificationModel = new ClassificationModel();

        //初始化最终输出的数据
        $data = [
            'difficulty' => [
                'title' => '难度等级分布',
                'category' => [],
            ],
        ];

        //获取所有的分类
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $difficultyArray = [];
            //难度。每个分类下 各个评分段的课程数量 难度段为['入门', '初级', '中级', '高级']
            $difficultyArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '入门']);
            $difficultyArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '初级']);
            $difficultyArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '中级']);
            $difficultyArray[] = $payLessonModel->getLessonCount(['curriculumClassification' => $v, 'difficulty' => '高级']);
            //赋值
            $data['difficulty']['category'][] = [
                'name' => $v,
                'values' => $difficultyArray,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取超过10万学习人数的免费课程列表
     */
    public function moreThenTenFreeLesson()
    {
        $lessonModel = new LessonModel();
        $classificationModel = new ClassificationModel();

        $data = [];
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $lessonInfo = $lessonModel->getLessonListByWhere([
                'curriculumClassification' => $v,
                'studyNum' => ['gt', 100000]], 'id,name,studyNum,difficulty,comprehensiveScore,totalTime,price');

            $data[] = [
                'class' => $v,
                'lesson' => $lessonInfo,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取超过2000学习人数的付费课程列表
     */
    public function moreThenTenPayLesson()
    {
        $payLessonModel = new PayLessonModel();
        $classificationModel = new ClassificationModel();

        $data = [];
        $classInfo = $classificationModel->getClassificationList();
        foreach ($classInfo as $v) {
            $lessonInfo = $payLessonModel->getLessonListByWhere([
                'curriculumClassification' => $v,
                'studyNum' => ['gt', 2000]], 'id,name,studyNum,difficulty,comprehensiveScore,totalTime,price');

            $data[] = [
                'class' => $v,
                'lesson' => $lessonInfo,
            ];
        }

        $this->ajaxReturn(1000, 'ok', $data);
    }

    /**
     * 获取免费课程七天内增长量最高的30个课程
     */
    public function getFreeLastSevenDayInfo()
    {
        $timeLineModel = new TimeLineModel();

        $result = $timeLineModel->getWeekRaterFreeList();

        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取付费课程七天内增长量最高的10个课程
     */
    public function getPayLastSevenDayInfo()
    {
        $timeLineModel = new TimeLineModel();

        $result = $timeLineModel->getWeekRaterPayList();

        $this->ajaxReturn(1000, 'ok', $result);
    }


    /**
     * 获取人气最高的10个讲师
     */
    public function getHotAuthorInfo()
    {
        $authorModel = new AuthorModel();

        $result = $authorModel->getHotAuthor();

        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取一门课程详情信息
     */
    public function getLessonDetail()
    {
        $params = input('get.');

        $validate = new LessonValidate();
        if (!$validate->scene('getLessonDetail')->check($params)) {
            $this->ajaxReturn($validate->getError());
        }

        $lessonModel = new LessonModel();
        $payLessonModel = new PayLessonModel();
        $commentModel = new CommentModel();
        $payCommentModel = new PayCommentModel();

        $lessonInfo = [];
        switch ($params['flag']) {
            case Constant::FREE_LESSON:
                $lessonInfo = $this->getLessonInfo($lessonModel, $commentModel, $params);
                break;
            case Constant::PAY_LESSON:
                $lessonInfo = $this->getLessonInfo($payLessonModel, $payCommentModel, $params);
                break;
        }

        $this->ajaxReturn(1000, 'ok', $lessonInfo);
    }

    /**
     * 封装课程信息
     * @param $lessonModel
     * @param $commentModel
     * @param $params
     * @return mixed
     */
    private function getLessonInfo($lessonModel, $commentModel, $params)
    {
        $timeLineModel = new TimeLineModel();

        //判断课程存不存在
        $result = $lessonModel->getLessonCount(['id' => $params['id']]);
        if ($result == 0) {
            $this->ajaxReturn(1002, '课程不存在');
        }

        //获取课程信息
        $lessonInfo = $lessonModel->getLessonDetail($params['id']);
        if (!$lessonInfo) {
            $this->ajaxReturn(1002, '课程不存在');
        }
        $lessonInfo['sameScoreNum'] = $lessonModel->getLessonCount([
            'comprehensiveScore' => $lessonInfo['comprehensiveScore'],
        ]);//与该课程分数相同的课程数
        $lessonInfo['totalMember'] = $lessonModel->getLessonCount([
            'curriculumClassification' => $lessonInfo['curriculumClassification'],
        ]);//与该课程同分类的课程数

        //过去七天学习人数
        $lessonInfo['lastSevenDayInfo'] = [
            date('Y-m-d', strtotime('-7 days')) => $timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('-7 days')),
                'flag' => $params['flag'],
            ], 'todayNum')['todayNum'],
            date('Y-m-d', strtotime('-6 days')) => $timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('-6 days')),
                'flag' => $params['flag'],
            ], 'todayNum')['todayNum'],
            date('Y-m-d', strtotime('-5 days')) => $timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('-5 days')),
                'flag' => $params['flag'],
            ], 'todayNum')['todayNum'],
            date('Y-m-d', strtotime('-4 days')) => $timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('-4 days')),
                'flag' => $params['flag'],
            ], 'todayNum')['todayNum'],
            date('Y-m-d', strtotime('-3 days')) => $timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('-3 days')),
                'flag' => $params['flag'],
            ], 'todayNum')['todayNum'],
            date('Y-m-d', strtotime('-2 days')) => $timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('-2 days')),
                'flag' => $params['flag'],
            ], 'todayNum')['todayNum'],
            date('Y-m-d', strtotime('yesterday')) => $timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('yesterday')),
                'flag' => $params['flag'],
            ], 'todayNum')['todayNum'],
        ];

        //评论信息
        $lessonInfo['comment'] = [
            [
                'class' => "入门/新手/小白",
                'comment' => $commentModel->getCommentListByWhere([
                    'lessonId' => $params['id'],
                    'content' => ['like', '%入门%'],
                    'content' => ['like', '%新手%'],
                    'content' => ['like', '%小白%'],
                ], 'content,score,lessonId'),
            ],
            [
                'class' => "挺好的/不错",
                'comment' => $commentModel->getCommentListByWhere([
                    'lessonId' => $params['id'],
                    'content' => ['like', '%挺好的%'],
                    'content' => ['like', '%不错%'],
                ], 'content,score,lessonId'),
            ],
            [
                'class' => "简单/易懂/基础",
                'comment' => $commentModel->getCommentListByWhere([
                    'lessonId' => $params['id'],
                    'content' => ['like', '%简单%'],
                    'content' => ['like', '%易懂%'],
                    'content' => ['like', '%基础%'],
                ], 'content,score,lessonId'),
            ],
        ];

        //评论分数信息
        $lessonInfo['score'] = [
            '0' => $commentModel->getCommentCount([
                'lessonId' => $params['id'],
                'score' => 0,
            ]),
            '2' => $commentModel->getCommentCount([
                'lessonId' => $params['id'],
                'score' => 2,
            ]),
            '4' => $commentModel->getCommentCount([
                'lessonId' => $params['id'],
                'score' => 4,
            ]),
            '6' => $commentModel->getCommentCount([
                'lessonId' => $params['id'],
                'score' => 6,
            ]),
            '8' => $commentModel->getCommentCount([
                'lessonId' => $params['id'],
                'score' => 8,
            ]),
            '10' => $commentModel->getCommentCount([
                'lessonId' => $params['id'],
                'score' => 10,
            ]),
        ];

        return $lessonInfo;
    }

    /**
     * 课程搜索
     */

    public function getLessonClass(){
        //课程分类信息返回
        $result = [
            [
                "id"=>"1",
                "name"=>"前沿技术"
            ],
            [
                "id"=>"2",
                "name"=>"前端开发"
            ],
            [
                "id"=>"3",
                "name"=>"后端开发"
            ],
            [
                "id"=>"4",
                "name"=>"移动端开发"
            ],
            //不想一个个写了
            [
                "id"=>"10",
                "name"=>"游戏"
            ],
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }
    public function searchLessonResult()
    {
        $result = [];
        $result = [
            [
                "name" => "Vue2.5开发去哪儿网App 从零基础入门到实战项目",
                "timeLine" => [
                    "2018-11-27" => 160,
                    "2018-11-28" => 100,
                    "2018-11-29" => 400,
                    "2018-11-30" => 355,
                    "2018-12-01" => 266,
                    "2018-12-02" => 121,
                    "2018-12-03" => 60
                ],
                "id" => 6,
                "author" => "江老实",
                "introduction" => "简介：每个网站都包含导航菜单，它们形式多样。本课程将由浅到深的介绍各种常见的导航条菜单的制作方法，从垂直方向的到水平方向的，再到用CSS样式的圆角导航条，最后讲解动态交互功能且具有拉伸效果的导航条菜单，对比着学习以上内容让您的技术探索之路更高效！",
                "curriculumClassification" => "前端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "0小时23分",
                "studyNum" => 118371,
                "commentNum" => 987,
                "comprehensiveScore" => "9.50",
                "url" => "https//:www.imooc.com/learn/6",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>54"
            ],
            [
                "name" => "手把手开发一个完整即时通讯APP",
                "timeLine" => [
                    "2018-11-27" => 200,
                    "2018-11-28" => 300,
                    "2018-11-29" => 100,
                    "2018-11-30" => 155,
                    "2018-12-01" => 166,
                    "2018-12-02" => 321,
                    "2018-12-03" => 400
                ],
                "id" => 161,
                "author" => "汤小洋",
                "introduction" => "简介：网络无处不在，移动互联时代也早已到来，单机版程序慢慢的已没有生命力，所有的程序都要能够访问网络，比如 QQ 网络聊天程序、迅雷下载程序等，这些程序都要同网络打交道，本次将与各位小伙伴们分享的就是 Java 中的网络编程—— Socket 通信",
                "curriculumClassification" => "后端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "2小时 0分",
                "studyNum" => 113511,
                "commentNum" => 471,
                "comprehensiveScore" => "9.80",
                "url" => "https://www.imooc.com/learn/161",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "name" => "前端跳槽必备 揭秘一线互联网公司高级前端JavaScript面试",
                "timeLine" => [
                    "2018-11-27" => 270,
                    "2018-11-28" => 309,
                    "2018-11-29" => 170,
                    "2018-11-30" => 105,
                    "2018-12-01" => 106,
                    "2018-12-02" => 351,
                    "2018-12-03" => 310
                ],
                "id" => 36,
                "author" => "慕课官方号...",
                "introduction" => "简介：本教程让您快速认识JavaScript，熟悉JavaScript基本语法、窗口交互方法和通过DOM进行网页元素的操作，学会如何编写JS代码，如何运用JavaScript去操作HTML元素和CSS样式，本JavaScript教程分为四个章节，能够让您快速入门，为JavaScript深入学习打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时35分",
                "studyNum" => 611147,
                "commentNum" => 5220,
                "comprehensiveScore" => "9.60",
                "url" => "http://www.imooc.com/learn/36",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>12"

            ],
            [
                "name" => "四大维度解锁 Webpack 前端工程化",
                "timeLine" => [
                    "2018-11-27" => 230,
                    "2018-11-28" => 300,
                    "2018-11-29" => 160,
                    "2018-11-30" => 135,
                    "2018-12-01" => 156,
                    "2018-12-02" => 321,
                    "2018-12-03" => 100
                ],
                "id" => 26,
                "author" => "Jason",
                "introduction" => "简介：通过PHP学习的进阶篇的学习，你可以对PHP的理论知识由浅入深有更深一步的掌握，这些知识能够使您更加全面的掌握PHP，从而助您在实际工作中使用PHP快速开发网站程序。",
                "curriculumClassification" => "后端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "9小时28分",
                "studyNum" => 162000,
                "commentNum" => 550,
                "comprehensiveScore" => "8.90",
                "url" => "http://www.imooc.com/learn/26",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>40"
            ],
            [
                "name" => "玩转数据结构 从入门到进阶",
                "timeLine" => [
                    "2018-11-27" => 203,
                    "2018-11-28" => 303,
                    "2018-11-29" => 103,
                    "2018-11-30" => 135,
                    "2018-12-01" => 166,
                    "2018-12-02" => 321,
                    "2018-12-03" => 300
                ],
                "id" => 26,
                "author" => "Jason",
                "introduction" => "简介：通过PHP学习的进阶篇的学习，你可以对PHP的理论知识由浅入深有更深一步的掌握，这些知识能够使您更加全面的掌握PHP，从而助您在实际工作中使用PHP快速开发网站程序。",
                "curriculumClassification" => "后端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "9小时28分",
                "studyNum" => 162000,
                "commentNum" => 550,
                "comprehensiveScore" => "8.90",
                "url" => "http://www.imooc.com/learn/26",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>40"
            ],
            [
                "name" => "Google资深工程师深度讲解Go语言",
                "timeLine" => [
                    "2018-11-27" => 230,
                    "2018-11-28" => 330,
                    "2018-11-29" => 103,
                    "2018-11-30" => 153,
                    "2018-12-01" => 136,
                    "2018-12-02" => 331,
                    "2018-12-03" => 430
                ],
                "id" => 33,
                "author" => "大漠",
                "introduction" => "简介：本课程为CSS3教程，对于有一定CSS2经验的伙伴，能让您系统的学习CSS3，快速的理解掌握并应用于工作之中。在学习教程的过程中实例演示结合在线编程完成任务的方式来学习，相信自己动手会让你理解的更快，本教程能够让您学习效果更好！",
                "curriculumClassification" => "前端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "5小时 0分",
                "studyNum" => 205535,
                "commentNum" => 456,
                "comprehensiveScore" => "9.40",
                "url" => "http://www.imooc.com/learn/33",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>30"
            ],
            [
                "name" => "React 16.4 开发简书项目 从零基础入门到实战",
                "timeLine" => [
                    "2018-11-27" => 300,
                    "2018-11-28" => 200,
                    "2018-11-29" => 200,
                    "2018-11-30" => 125,
                    "2018-12-01" => 166,
                    "2018-12-02" => 221,
                    "2018-12-03" => 400
                ],
                "id" => 36,
                "author" => "慕课官方号...",
                "introduction" => "简介：本教程让您快速认识JavaScript，熟悉JavaScript基本语法、窗口交互方法和通过DOM进行网页元素的操作，学会如何编写JS代码，如何运用JavaScript去操作HTML元素和CSS样式，本JavaScript教程分为四个章节，能够让您快速入门，为JavaScript深入学习打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时35分",
                "studyNum" => 611147,
                "commentNum" => 5220,
                "comprehensiveScore" => "9.60",
                "url" => "http://www.imooc.com/learn/36",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>12"
            ],
            [
                "name" => "Kotlin打造完整电商APP 模块化+MVP+主流框架",
                "timeLine" => [
                    "2018-11-27" => 240,
                    "2018-11-28" => 340,
                    "2018-11-29" => 150,
                    "2018-11-30" => 155,
                    "2018-12-01" => 166,
                    "2018-12-02" => 341,
                    "2018-12-03" => 410
                ],
                "id" => 26,
                "author" => "Jason",
                "introduction" => "简介：通过PHP学习的进阶篇的学习，你可以对PHP的理论知识由浅入深有更深一步的掌握，这些知识能够使您更加全面的掌握PHP，从而助您在实际工作中使用PHP快速开发网站程序。",
                "curriculumClassification" => "后端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "9小时28分",
                "studyNum" => 162000,
                "commentNum" => 550,
                "comprehensiveScore" => "8.90",
                "url" => "http://www.imooc.com/learn/26",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>40"
            ],
            [
                "name" => "快速上手Linux 玩转典型应用",
                "timeLine" => [
                    "2018-11-27" => 20,
                    "2018-11-28" => 30,
                    "2018-11-29" => 100,
                    "2018-11-30" => 105,
                    "2018-12-01" => 106,
                    "2018-12-02" => 301,
                    "2018-12-03" => 420
                ],
                "id" => 161,
                "author" => "汤小洋",
                "introduction" => "简介：网络无处不在，移动互联时代也早已到来，单机版程序慢慢的已没有生命力，所有的程序都要能够访问网络，比如 QQ 网络聊天程序、迅雷下载程序等，这些程序都要同网络打交道，本次将与各位小伙伴们分享的就是 Java 中的网络编程—— Socket 通信",
                "curriculumClassification" => "后端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "2小时 0分",
                "studyNum" => 113511,
                "commentNum" => 471,
                "comprehensiveScore" => "9.80",
                "url" => "https://www.imooc.com/learn/161",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "name" => "韩天峰力荐 Swoole入门到实战打造高性能赛事直播平台",
                "timeLine" => [
                    "2018-11-27" => 200,
                    "2018-11-28" => 300,
                    "2018-11-29" => 120,
                    "2018-11-30" => 195,
                    "2018-12-01" => 236,
                    "2018-12-02" => 381,
                    "2018-12-03" => 400
                ],
                "id" => 12,
                "author" => "zongran",
                "introduction" => "简介：本课程从易到难，循循渐进，从静态网页布局，到运用HTML/CSS、JavaScript、jQuery不同技术实现动态下拉菜单，让您掌握下拉菜单的制作及在不同浏览器间进行代码调试，解决浏览器兼容问题。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "3小时21分",
                "studyNum" => 102238,
                "commentNum" => 138,
                "comprehensiveScore" => "9.60",
                "url" => "http://www.imooc.com/learn/12",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>47=>20"
            ],
            [
                "name" => "前端面试项目冲刺，京东金融Vue组件化实战",
                "timeLine" => [
                    "2018-11-27" => 270,
                    "2018-11-28" => 390,
                    "2018-11-29" => 200,
                    "2018-11-30" => 145,
                    "2018-12-01" => 116,
                    "2018-12-02" => 301,
                    "2018-12-03" => 210
                ],
                "id" => 26,
                "author" => "Jason",
                "introduction" => "简介：通过PHP学习的进阶篇的学习，你可以对PHP的理论知识由浅入深有更深一步的掌握，这些知识能够使您更加全面的掌握PHP，从而助您在实际工作中使用PHP快速开发网站程序。",
                "curriculumClassification" => "后端开发",
                "difficulty" => "初级",
                "price" => "0.00",
                "totalTime" => "9小时28分",
                "studyNum" => 162000,
                "commentNum" => 550,
                "comprehensiveScore" => "8.90",
                "url" => "http://www.imooc.com/learn/26",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>40"
            ],
            [
                "name" => "Spring Cloud微服务实战",
                "timeLine" => [
                    "2018-11-27" => 200,
                    "2018-11-28" => 380,
                    "2018-11-29" => 90,
                    "2018-11-30" => 155,
                    "2018-12-01" => 106,
                    "2018-12-02" => 321,
                    "2018-12-03" => 400
                ],
                "id" => 36,
                "author" => "慕课官方号...",
                "introduction" => "简介：本教程让您快速认识JavaScript，熟悉JavaScript基本语法、窗口交互方法和通过DOM进行网页元素的操作，学会如何编写JS代码，如何运用JavaScript去操作HTML元素和CSS样式，本JavaScript教程分为四个章节，能够让您快速入门，为JavaScript深入学习打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时35分",
                "studyNum" => 611147,
                "commentNum" => 5220,
                "comprehensiveScore" => "9.60",
                "url" => "http://www.imooc.com/learn/36",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>12"
            ],
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 评论搜索
     */
    public function searchCommentResult()
    {
        //把匹配的评论按照课程分组
        $result = [
            [
                "lessonName" => "手把手教你实现个人网站",
                "comment" => [
                    [
                        "content" => "简单易懂，非常适合小白入门。",
                        "score" => 8,
                        "lessonId" => 9
                    ],
                    ["content" => "对于入门的人来说，可以看一下，感觉还是可以的。",
                        "score" => 6,
                        "lessonId" => 9
                    ],
                    ["content" => "入门到初级，学以致用，慢慢学习",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                    ["content" => "新手必看，十分详细了",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                ]
            ],
            [
                "lessonName" => "html基础",
                "comment" => [
                    [
                        "content" => "简单易懂对于新手入门来说很好",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                    [
                        "content" => "很基础 也很好理解 挺好",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                ]
            ],
            [
                "lessonName" => "vue实战",
                "comment" => [
                    [
                        "content" => "简单易懂，非常适合小白入门。",
                        "score" => 8,
                        "lessonId" => 9
                    ],
                    ["content" => "对于入门的人来说，可以看一下，感觉还是可以的。",
                        "score" => 6,
                        "lessonId" => 9
                    ],
                    ["content" => "入门到初级，学以致用，慢慢学习",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                    ["content" => "新手必看，十分详细了",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                ]
            ],
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }

}

