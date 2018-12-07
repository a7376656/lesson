<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\controller\Constant;
use app\index\model\ClassificationModel;
use app\index\model\CommentModel;
use app\index\model\DataModel;
use app\index\model\LessonModel;
use app\index\model\PayLessonModel;
use app\index\model\TimeLineModel;
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
     * 获取免费课程七天内增长率最高的30个课程
     */
    public function getFreeLastSevenDayInfo()
    {
        $lessonModel = new LessonModel();
        $timeLineModel = new TimeLineModel();

        $today = date('Y-m-d');//今天日期
        $sevenAgo = date('Y-m-d', strtotime('-6 days'));//7天前（因为包括今天所以是-6）

        //未写完
        $result = [];
        $result = [
            [
                "name" => "基于Spring Boot技术栈博客系统企业级前后端实战",
                "rate" => "300",
                "id" => 161,
                "author" => "穆雪峰",
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
                "name" => "Python接口自动化测试框架实战",
                "rate" => "266",
                "id" => 161,
                "author" => "阮一峰",
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
                "name" => "SpringBoot 仿抖音短视频小程序开发 全栈式实战项目",
                "rate" => "243",
                "id" => 161,
                "author" => "老实人",
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
                "name" => "微信小游戏入门与实战 刷爆朋友圈",
                "rate" => "177",
                "author" => "祁连山",
                "introduction" => "简介：本课程将带你学习有关ps调色的技能，例如色彩调整层基础知识，亮度对比，自动调整颜色，色阶，曲线，曝光度，自然饱和度，色相饱和度，色彩平衡度等 。相信童鞋们通过本课程的学习，一定能制作出最绚丽的美图。",
                "curriculumClassification" => "UI设计&amp;多媒体",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时 7分",
                "studyNum" => 136740,
                "commentNum" => 100,
                "comprehensiveScore" => "9.50",
                "url" => "https://www.imooc.com/learn/152",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>47"
            ],
            [
                "name" => "Java秒杀系统方案优化 高性能高并发实战",
                "rate" => "135",
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
                "name" => "前端跳槽必备 揭秘一线互联网公司高级前端JavaScript面试",
                "rate" => "300",
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
                "rate" => "266",
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
                "rate" => "243",
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
                "rate" => "201",
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
                "name" => "Vue2.5开发去哪儿网App 从零基础入门到实战项目",
                "rate" => "21",
                "id" => 161,
                "author" => "老师2",
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
                "name" => "手把手开发一个完整即时通讯APP",
                "rate" => "20",
                "id" => 161,
                "author" => "老师8",
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
            ]
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取付费课程七天内增长率最高的10个课程
     */
    public function getPayLastSevenDayInfo()
    {
        $lessonModel = new LessonModel();
        $timeLineModel = new TimeLineModel();

        $today = date('Y-m-d');//今天日期
        $sevenAgo = date('Y-m-d', strtotime('-6 days'));//7天前（因为包括今天所以是-6）

        //未写完
        $result = [];
        $result = [
            [
                "name" => "基于Spring Boot技术栈博客系统企业级前后端实战",
                "rate" => "300",
                "id" => 148,
                "author" => "King",
                "introduction" => "简介：本系统从慕课网电子商务系统的需求分析、数据表设计入手，从后台搭建，到后台模块实现，由浅入深教你如何搭建电子商务系统，包括前台数据的显示并详细讲解了电商系统开发流程以及开发过程中会遇到的问题及如何解决。",
                "curriculumClassification" => "后端开发",
                "difficulty" => "中级",
                "price" => "0.00",
                "totalTime" => "10小时 0分",
                "studyNum" => 112446,
                "commentNum" => 95,
                "comprehensiveScore" => "9.80",
                "url" => "https://www.imooc.com/learn/148",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>57"

            ],
            [
                "name" => "Python接口自动化测试框架实战",
                "rate" => "266",
                "id" => 152,
                "author" => "祁连山",
                "introduction" => "简介：本课程将带你学习有关ps调色的技能，例如色彩调整层基础知识，亮度对比，自动调整颜色，色阶，曲线，曝光度，自然饱和度，色相饱和度，色彩平衡度等 。相信童鞋们通过本课程的学习，一定能制作出最绚丽的美图。",
                "curriculumClassification" => "UI设计&amp;多媒体",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时 7分",
                "studyNum" => 136740,
                "commentNum" => 100,
                "comprehensiveScore" => "9.50",
                "url" => "https://www.imooc.com/learn/152",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>47"
            ],
            [
                "name" => "SpringBoot 仿抖音短视频小程序开发 全栈式实战项目",
                "rate" => "243",
                "author" => "祁连山",
                "introduction" => "简介：本课程将带你学习有关ps调色的技能，例如色彩调整层基础知识，亮度对比，自动调整颜色，色阶，曲线，曝光度，自然饱和度，色相饱和度，色彩平衡度等 。相信童鞋们通过本课程的学习，一定能制作出最绚丽的美图。",
                "curriculumClassification" => "UI设计&amp;多媒体",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时 7分",
                "studyNum" => 136740,
                "commentNum" => 100,
                "comprehensiveScore" => "9.50",
                "url" => "https://www.imooc.com/learn/152",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>47"
            ],
            [
                "name" => "Python Flask高级编程",
                "rate" => "201",
                "author" => "祁连山",
                "introduction" => "简介：本课程将带你学习有关ps调色的技能，例如色彩调整层基础知识，亮度对比，自动调整颜色，色阶，曲线，曝光度，自然饱和度，色相饱和度，色彩平衡度等 。相信童鞋们通过本课程的学习，一定能制作出最绚丽的美图。",
                "curriculumClassification" => "UI设计&amp;多媒体",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时 7分",
                "studyNum" => 136740,
                "commentNum" => 100,
                "comprehensiveScore" => "9.50",
                "url" => "https://www.imooc.com/learn/152",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>47"
            ],
            [
                "name" => "微信小游戏入门与实战 刷爆朋友圈",
                "rate" => "177",
                "author" => "祁连山",
                "introduction" => "简介：本课程将带你学习有关ps调色的技能，例如色彩调整层基础知识，亮度对比，自动调整颜色，色阶，曲线，曝光度，自然饱和度，色相饱和度，色彩平衡度等 。相信童鞋们通过本课程的学习，一定能制作出最绚丽的美图。",
                "curriculumClassification" => "UI设计&amp;多媒体",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "1小时 7分",
                "studyNum" => 136740,
                "commentNum" => 100,
                "comprehensiveScore" => "9.50",
                "url" => "https://www.imooc.com/learn/152",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>46=>47"
            ],
            [
                "name" => "Java秒杀系统方案优化 高性能高并发实战",
                "rate" => "135",
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
                "name" => "纯正商业级应用-微信小程序开发实战",
                "rate" => "99",
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
                "name" => "微信小程序商城构建全栈应用",
                "rate" => "23",
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
                "name" => "Vue2.5开发去哪儿网App 从零基础入门到实战项目",
                "rate" => "21",
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
                "rate" => "20",
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
                "rate" => "300",
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
                "rate" => "266",
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
                "rate" => "243",
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
                "rate" => "201",
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
                "rate" => "177",
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
                "rate" => "135",
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
                "rate" => "99",
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
                "rate" => "23",
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
                "rate" => "21",
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
                "rate" => "20",
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
                "name" => "新浪微博资深大牛全方位剖析 iOS 高级面试",
                "rate" => "300",
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
                "name" => "Spring Security技术栈开发企业级认证与授权",
                "rate" => "266",
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
                "name" => "PHP开发高可用高安全App后端",
                "rate" => "243",
                "id" => 9,
                "author" => "慕课官方号...",
                "introduction" => "简介：本课程从最基本的概念开始讲起，步步深入，带领大家学习HTML、CSS样式基础知识，了解各种常用标签的意义以及基本用法，后半部分教程主要讲解CSS样式代码添加，为后面的案例课程打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "9小时18分",
                "studyNum" => 967919,
                "commentNum" => 8959,
                "comprehensiveScore" => "9.50",
                "url" => "http://www.imooc.com/learn/9",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>45=>45"
            ],
            [
                "name" => "Vue.js 2.5 + cube-ui 重构饿了么 App",
                "rate" => "201",
                "id" => 9,
                "author" => "慕课官方号...",
                "introduction" => "简介：本课程从最基本的概念开始讲起，步步深入，带领大家学习HTML、CSS样式基础知识，了解各种常用标签的意义以及基本用法，后半部分教程主要讲解CSS样式代码添加，为后面的案例课程打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "9小时18分",
                "studyNum" => 967919,
                "commentNum" => 8959,
                "comprehensiveScore" => "9.50",
                "url" => "http://www.imooc.com/learn/9",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>45=>45"
            ],
            [
                "name" => "微信小游戏入门与实战 刷爆朋友圈",
                "rate" => "177",
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
                "name" => "React Native技术精讲与高质量上线APP开发",
                "rate" => "135",
                "id" => 9,
                "author" => "慕课官方号...",
                "introduction" => "简介：本课程从最基本的概念开始讲起，步步深入，带领大家学习HTML、CSS样式基础知识，了解各种常用标签的意义以及基本用法，后半部分教程主要讲解CSS样式代码添加，为后面的案例课程打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "9小时18分",
                "studyNum" => 967919,
                "commentNum" => 8959,
                "comprehensiveScore" => "9.50",
                "url" => "http://www.imooc.com/learn/9",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>45=>45"
            ],
            [
                "name" => "HTTP协议原理+实践 Web开发工程师必学",
                "rate" => "99",
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
                "name" => "玩转算法面试 从真题到思维全面提升算法思维",
                "rate" => "23",
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
                "name" => "Redux+React Router+Node.js全栈开发",
                "rate" => "21",
                "id" => 9,
                "author" => "慕课官方号...",
                "introduction" => "简介：本课程从最基本的概念开始讲起，步步深入，带领大家学习HTML、CSS样式基础知识，了解各种常用标签的意义以及基本用法，后半部分教程主要讲解CSS样式代码添加，为后面的案例课程打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "9小时18分",
                "studyNum" => 967919,
                "commentNum" => 8959,
                "comprehensiveScore" => "9.50",
                "url" => "http://www.imooc.com/learn/9",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>45=>45"
            ],
            [
                "name" => "全面系统讲解CSS 工作应用+面试一步搞定",
                "rate" => "20",
                "id" => 9,
                "author" => "慕课官方号...",
                "introduction" => "简介：本课程从最基本的概念开始讲起，步步深入，带领大家学习HTML、CSS样式基础知识，了解各种常用标签的意义以及基本用法，后半部分教程主要讲解CSS样式代码添加，为后面的案例课程打下基础。",
                "curriculumClassification" => "前端开发",
                "difficulty" => "入门",
                "price" => "0.00",
                "totalTime" => "9小时18分",
                "studyNum" => 967919,
                "commentNum" => 8959,
                "comprehensiveScore" => "9.50",
                "url" => "http://www.imooc.com/learn/9",
                "authorUrl" => "",
                "grabTime" => "2018-11-29 00=>45=>45"
            ],
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }


    /**
     * 获取免费课程七天内增长率最高的30个课程
     */
    public function getHotAuthorInfo()
    {
        $result = [
            [
                "id" => 161,
                "name" => "阮一峰",
                "fans" => "200",
                "lessonNumber" => 5,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "id" => 161,
                "name" => "廖雪峰",
                "fans" => "200",
                "lessonNumber" => 1,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "id" => 161,
                "name" => "老师1",
                "fans" => "20",
                "lessonNumber" => 10,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "id" => 161,
                "name" => "老师2",
                "fans" => "20",
                "lessonNumber" => 6,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "id" => 161,
                "name" => "老师3",
                "fans" => "65",
                "lessonNumber" => 6,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "id" => 161,
                "name" => "老师4",
                "fans" => "65",
                "lessonNumber" => 4,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "id" => 161,
                "name" => "lili",
                "fans" => "35",
                "lessonNumber" => 6,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
            [
                "id" => 161,
                "name" => "usbthuangyi",
                "fans" => "35",
                "lessonNumber" => 6,
                "url" => "https://www.imooc.com/learn/161",
                "grabTime" => "2018-11-29 00=>46=>56"
            ],
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取一门课程详情信息
     *
     */
    public function getLessonDetail()
    {
        $comment = [
            [
                "class" => "入门/新手/小白",
                "comment" => [
                    [
                        "content" => "简单易懂，非常适合小白入门前端呀！",
                        "score" => 8,
                        "lessonId" => 9
                    ],
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
                "class" => "挺好的/不错",
                "comment" => [
                    [
                        "content" => "挺好的，有其它语言基础理解起来不难，边敲代码能够很快进入状态",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                    [
                        "content" => "挺好的，对以前零零碎碎的知识串了一遍，以前主要做后台的，页面就是二把刀，学完了以后，还是二把刀，哈哈。。学以致用，慢慢练习吧", "score" => 10,
                        "lessonId" => 9
                    ],
                    [
                        "content" => "挺好的入门教程~需要的可以看看~",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                    [
                        "content" => "不错，基本的东西还是有了解，谢谢！",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                    [
                        "content" => "很棒 做一个一个小白都能看得懂学得会",
                        "score" => 10,
                        "lessonId" => 9
                    ],
                ]
            ],
            [
                "class" => "简单/易懂/基础",
                "comment" => [
                    [
                        "content" => "总体的感觉是对的，相比于其他的学习平台好多了。但是如果精益求精的话，还是有改进的地方，比如作业太少了，对应知识点的练习太少了，希望增加一个课后练习的环节，让那些渴望得到进阶的同学有机会学的更扎实一些。课程讲的很直白了，通俗易懂，对于初学者来说真的很适合。支持！！！",
                        "score" => 8,
                        "lessonId" => 9
                    ],
                    [
                        "content" => "希望可以更复杂一点，感觉太简单了。",
                        "score" => 8,
                        "lessonId" => 9
                    ],
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
            ]
        ];
        $score = [
            0 => 0,
            2 => 0,
            4 => 2,
            6 => 20,
            8 => 2100,
            10 => 3030,
        ];
        $result = [
            "id" => 148,
            "name" => "基于Spring Boot技术栈博客系统企业级前后端实战",
            "author" => "King",
            "introduction" => "简介：本系统从慕课网电子商务系统的需求分析、数据表设计入手，从后台搭建，到后台模块实现，由浅入深教你如何搭建电子商务系统，包括前台数据的显示并详细讲解了电商系统开发流程以及开发过程中会遇到的问题及如何解决。",
            "curriculumClassification" => "后端开发",
            "difficulty" => "中级",
            "price" => "0.00",
            "totalTime" => "10小时 0分",
            "studyNum" => 112446,
            "commentNum" => 95,
            "comprehensiveScore" => "9.80",
            "rate" => "300",
            "studyRank" => 13,
            "commentRank" => 25,
            "scoreRank" => 7,
            "sameScoreNum" => 2,
            "totalMember" => 66,
            "url" => "https://www.imooc.com/learn/148",
            "authorUrl" => "https://www.imooc.com",
            "grabTime" => "2018-11-29 00=>46=>57",
            "lastSevenDayInfo" => [
                "2018-11-27" => 200,
                "2018-11-28" => 300,
                "2018-11-29" => 100,
                "2018-11-30" => 155,
                "2018-12-01" => 166,
                "2018-12-02" => 321,
                "2018-12-03" => 400
            ],
            "comment" => $comment,
            "score" => $score,
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 课程搜索
     *
     */
    public function searchLessonResult()
    {

    }

    /**
     * 评论搜索
     *
     */
    public function searchCommentResult()
    {

    }

}

