<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\controller\Constant;
use app\index\model\CommentModel;
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

    public function moocOverview()
    {
        $result = [];
        //慕课网教师数、分类数、课程数目、评论数
        $result = [
            "lessonNum" => [
                "free" => 2450,
                "pay" => 300
            ],
            "lecturerNum" => 100,
            "classNum" => 10,
            "commentNum" => 290127
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取所有免费课程的评价、时长、难度概况
     */
    public function freeCourseOverview()
    {
        $lessonModel = new LessonModel();
        $result = [];
        //每个分类下 各个评分段的课程数量 评分段为左闭右开['<6', '6-8', '8-9', '9-9.5', '9.5-10', '10']
        $result['grade'] = [
            "title" => "评分范围分布",
            "category" => [
                [
                    "name" => "前沿技术",
                    "values" => [8, 3, 5, 10, 10, 2]
                ],
                [
                    "name" => "前端开发",
                    "values" => [8, 2, 4, 20, 10, 1]
                ],
                [
                    "name" => "后端开发",
                    "values" => [8, 3, 5, 10, 10, 2]
                ],
                [
                    "name" => "移动开发",
                    "values" => [3, 2, 3, 10, 10, 1]
                ],
                [
                    "name" => "算法&数学",
                    "values" => [0, 3, 5, 1, 10, 2]
                ],
                [
                    "name" => "云计算&大数据",
                    "values" => [0, 2, 4, 3, 10, 1]
                ],
                [
                    "name" => "运维&测试",
                    "values" => [0, 3, 0, 2, 10, 2]
                ],
                [
                    "name" => "数据库",
                    "values" => [1, 2, 4, 3, 10, 1]
                ],
                [
                    "name" => "UI设计&多媒体",
                    "values" => [1, 3, 5, 10, 10, 2]
                ],
                [
                    "name" => "游戏",
                    "values" => [1, 1, 5, 2, 2, 10]
                ],
            ]
        ];
        //每个分类下 各个时长段的课程数量 时长段为（左开右闭）['<1','1-3', '3-6', '6-10', '10-15','15-20','20-30',>=30]
        $result['duration'] = [
            "title" => "时长数据分布",
            "category" => [
                [
                    "name" => "前沿技术",
                    "values" => [18, 1, 0, 0, 0, 0, 1, 1]
                ],
                [
                    "name" => "前端开发",
                    "values" => [8, 3, 14, 20, 10, 1, 0, 0]
                ],
                [
                    "name" => "后端开发",
                    "values" => [8, 13, 5, 10, 10, 2, 0, 0]
                ],
                [
                    "name" => "移动开发",
                    "values" => [5, 2, 4, 1, 1, 1, 0, 0]
                ],
                [
                    "name" => "算法&数学",
                    "values" => [3, 3, 2, 1, 0, 1, 0, 0]
                ],
                [
                    "name" => "云计算&大数据",
                    "values" => [3, 2, 1, 1, 2, 0, 0, 0]
                ],
                [
                    "name" => "运维&测试",
                    "values" => [2, 0, 0, 1, 1, 1, 0, 0]
                ],
                [
                    "name" => "数据库",
                    "values" => [2, 5, 2, 1, 3, 0, 0, 0]
                ],
                [
                    "name" => "UI设计&多媒体",
                    "values" => [8, 3, 2, 1, 3, 1, 0, 0]
                ],
                [
                    "name" => "游戏",
                    "values" => [1, 1, 1, 5, 2, 3, 0, 0]
                ],
            ]
        ];
        //每个分类下 各个评分段的课程数量 难度段为['入门', '初级', '中级', '高级']
        $result['difficulty'] = [
            "title" => "难度等级分布",
            "category" => [
                [
                    "name" => "前沿技术",
                    "values" => [2, 3, 5, 10,]
                ],
                [
                    "name" => "前端开发",
                    "values" => [8, 32, 25, 2,]
                ],
                [
                    "name" => "后端开发",
                    "values" => [7, 17, 39, 2,]
                ],
                [
                    "name" => "移动开发",
                    "values" => [2, 3, 5, 10,]
                ],
                [
                    "name" => "算法&数学",
                    "values" => [2, 1, 5, 5,]
                ],
                [
                    "name" => "云计算&大数据",
                    "values" => [2, 3, 1, 6,]
                ],
                [
                    "name" => "运维&测试",
                    "values" => [5, 3, 0, 0,]
                ],
                [
                    "name" => "数据库",
                    "values" => [2, 5, 1, 0,]
                ],
                [
                    "name" => "UI设计&多媒体",
                    "values" => [8, 1, 1, 0,]
                ],
                [
                    "name" => "游戏",
                    "values" => [0, 3, 5, 10,]
                ],
            ]
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取所有付费课程的评价、时长、难度概况
     */
    public function payCourseOverview()
    {
        $lessonModel = new LessonModel();

        $result = [];
        //每个分类下 各个评分段的课程数量 评分段为['<8', '8-8.4', '8.5-8.9', '9-9.4', '9.5-9.7', '9.8-10']
        $result['grade'] = [
            "title" => "评分范围分布",
            "category" => [
                [
                    "name" => "前沿技术",
                    "values" => [3, 0, 0, 0, 3, 0]
                ],
                [
                    "name" => "前端开发",
                    "values" => [3, 2, 4, 15, 5, 1]
                ],
                [
                    "name" => "后端开发",
                    "values" => [3, 3, 0, 5, 5, 2]
                ],
                [
                    "name" => "移动开发",
                    "values" => [0, 2, 0, 5, 5, 1]
                ],
                [
                    "name" => "算法&数学",
                    "values" => [0, 0, 0, 1, 5, 2]
                ],
                [
                    "name" => "云计算&大数据",
                    "values" => [0, 2, 4, 3, 10, 1]
                ],
                [
                    "name" => "运维&测试",
                    "values" => [0, 3, 0, 2, 10, 2]
                ],
                [
                    "name" => "数据库",
                    "values" => [1, 2, 4, 3, 10, 1]
                ],
                [
                    "name" => "UI设计&多媒体",
                    "values" => [1, 3, 5, 10, 10, 2]
                ],
                [
                    "name" => "游戏",
                    "values" => [1, 1, 5, 2, 2, 10]
                ],
            ]
        ];
        //每个分类下 各个时长段的课程数量 时长段为（左开右闭）['<1','1-3', '3-6', '6-10', '10-15','15-20','20-30',>=30]
        $result['duration'] = [
            "title" => "时长数据分布",
            "category" => [
                [
                    "name" => "前沿技术",
                    "values" => [18, 1, 0, 0, 0, 0, 1, 1]
                ],
                [
                    "name" => "前端开发",
                    "values" => [8, 3, 2, 20, 10, 30, 14, 14]
                ],
                [
                    "name" => "后端开发",
                    "values" => [0, 2, 5, 10, 10, 32, 22, 3]
                ],
                [
                    "name" => "移动开发",
                    "values" => [0, 2, 4, 1, 1, 1, 0, 0]
                ],
                [
                    "name" => "算法&数学",
                    "values" => [3, 3, 2, 1, 0, 1, 0, 0]
                ],
                [
                    "name" => "云计算&大数据",
                    "values" => [0, 2, 1, 1, 2, 0, 0, 0]
                ],
                [
                    "name" => "运维&测试",
                    "values" => [2, 0, 0, 1, 1, 1, 0, 0]
                ],
                [
                    "name" => "数据库",
                    "values" => [2, 5, 2, 1, 3, 0, 0, 0]
                ],
                [
                    "name" => "UI设计&多媒体",
                    "values" => [8, 3, 2, 1, 3, 1, 0, 0]
                ],
                [
                    "name" => "游戏",
                    "values" => [1, 1, 1, 5, 2, 3, 0, 0]
                ],
            ]
        ];
        //每个分类下 各个评分段的课程数量 难度段为['入门', '初级', '中级', '高级']
        $result['difficulty'] = [
            "title" => "难度等级分布",
            "category" => [
                [
                    "name" => "前沿技术",
                    "values" => [2, 3, 5, 10,]
                ],
                [
                    "name" => "前端开发",
                    "values" => [8, 32, 25, 2,]
                ],
                [
                    "name" => "后端开发",
                    "values" => [7, 17, 39, 2,]
                ],
                [
                    "name" => "移动开发",
                    "values" => [2, 3, 5, 10,]
                ],
                [
                    "name" => "算法&数学",
                    "values" => [2, 1, 5, 5,]
                ],
                [
                    "name" => "云计算&大数据",
                    "values" => [2, 3, 1, 6,]
                ],
                [
                    "name" => "运维&测试",
                    "values" => [5, 3, 0, 0,]
                ],
                [
                    "name" => "数据库",
                    "values" => [2, 5, 1, 0,]
                ],
                [
                    "name" => "UI设计&多媒体",
                    "values" => [8, 1, 1, 0,]
                ],
                [
                    "name" => "游戏",
                    "values" => [0, 3, 5, 10,]
                ],
            ]
        ];
        $this->ajaxReturn(1000, 'ok', $result);
    }


    /**
     * 获取超过10万学习人数的免费课程列表
     */
    public function moreThenTenFreeLesson()
    {
        $lessonModel = new LessonModel();

        $result = $lessonModel->getLessonListByWhere(['studyNum' => ['gt', 100000]]);

        $result = [
            [
                "class" => "前沿技术",
                "lesson" => [
                    [
                        "id" => 148,
                        "name" => "手把手教你实现电商网站后台开发",
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
                        "id" => 152,
                        "name" => "PS入门基础-魔幻调色",
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
                        "id" => 156,
                        "name" => "AngularJS实战",
                        "author" => "大漠穷秋",
                        "introduction" => "简介：欢迎大家与大漠穷秋老师一起学习AngularJS的基础教程，让我们一起通过实例学习并学会AngularJS！",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "高级",
                        "price" => "0.00",
                        "totalTime" => "7小时13分",
                        "studyNum" => 207299,
                        "commentNum" => 301,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/156",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>27"
                    ],
                    [
                        "id" => 159,
                        "name" => "PS大神通关教程",
                        "author" => "祁连山",
                        "introduction" => "简介：祁大湿带你梳理PS知识体系，分享实战经验，让你真正掌握PS使用方法。工具使用，图层操作，色彩调整，十八般武艺样样精通。真正实现菜鸟到大神的华丽转身。",
                        "curriculumClassification" => "UI设计&amp;多媒体",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "8小时12分",
                        "studyNum" => 312506,
                        "commentNum" => 159,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/159",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>19"
                    ],
                ]

            ],
            [
                "class" => "前端开发",
                "lesson" => [
                    [
                        "id" => 6,
                        "name" => "导航条菜单的制作",
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
                        "id" => 9,
                        "name" => "初识HTML+CSS",
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
                        "id" => 10,
                        "name" => "JavaScript进阶篇",
                        "author" => "慕课官方号...",
                        "introduction" => "简介：做为WEB攻城师必备技术JavaScript，本课程从如何插入JS代码开始，学习JS基础语法、语法、函数、方法等，让你掌握JS编程思路、知识的使用等，实现运用JS语言为网页增加动态效果，达到与用户交互的目的。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "8小时55分",
                        "studyNum" => 390105,
                        "commentNum" => 2443,
                        "comprehensiveScore" => "9.50",
                        "url" => "http://www.imooc.com/learn/10",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>16"
                    ],
                    [
                        "id" => 12,
                        "name" => "形形色色的下拉菜单",
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
                        "id" => 20,
                        "name" => "网页简单布局之结构与表现原则",
                        "author" => "江老实",
                        "introduction" => "简介：在网页制作当中，结构与表现分离的思想，不仅仅是将html、css分别写在不同文件当中这么简单，要从更深层次上去进行理解。本课程通过3个案例，分别从不同角度，对结构和表现分离的思想进行了展示和分析。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "0小时22分",
                        "studyNum" => 104591,
                        "commentNum" => 1399,
                        "comprehensiveScore" => "9.70",
                        "url" => "http://www.imooc.com/learn/20",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>05"
                    ],
                    [
                        "id" => 24,
                        "name" => "HTML5之元素与标签结构",
                        "author" => "Alex",
                        "introduction" => "简介：知识与实例相结合，本部分是HTML5课程的基础内容，主要讲解HTML5的标签结构，与传统的HTML4相比，新增和删去的标签及相关属性，并深入拓展了全局属性的相关知识。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时 0分",
                        "studyNum" => 171401,
                        "commentNum" => 439,
                        "comprehensiveScore" => "9.20",
                        "url" => "http://www.imooc.com/learn/24",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>36"
                    ],
                    [
                        "id" => 26,
                        "name" => "PHP进阶篇",
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
                        "id" => 33,
                        "name" => "十天精通CSS3",
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
                        "id" => 36,
                        "name" => "JavaScript入门篇",
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
                ]
            ],
            [
                "class" => "后端开发",
                "lesson" => [
                    [
                        "id" => 161,
                        "name" => "Java Socket应用---通信是这样练成的",
                        "author" => "lili",
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
                        "id" => 166,
                        "name" => "JAVA遇见HTML——JSP篇",
                        "author" => "milanlover",
                        "introduction" => "简介：亲，这里有资深讲师为初学者量身打造的Java Web入门级课程JSP，讲师将通过大量的案例向您展示JavaWeb开发环境搭建、工具使用和JSP的基本语法，深入理解Java Web开发思想，最终使您能独立开发简单的Java Web应用。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "7小时 0分",
                        "studyNum" => 210562,
                        "commentNum" => 798,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/166",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>26"
                    ],
                    [
                        "id" => 167,
                        "name" => "JS动画效果",
                        "author" => "vivian",
                        "introduction" => "简介：通过本课程JS动画的学习，从简单动画开始，逐步深入缓冲动画、多物体动画、链式动画、多动画同时运动到完美运动框架的过程，每一个效果封装成一个小运动框架，逐渐培养和锻炼封装运动框架和编程的思想，让您的逻辑思维不断增强。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 8分",
                        "studyNum" => 102810,
                        "commentNum" => 526,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/167",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>08"
                    ],
                    [
                        "id" => 175,
                        "name" => "Linux达人养成计划 I",
                        "author" => "Tony",
                        "introduction" => "简介：本课程以通俗易懂的语言、风趣幽默的实例、清晰严谨的逻辑介绍了Linux的基础内容。课程以CentOS操作系统为例，为你带来Linux的简介、系统安装和常用命令等内容。让您在轻松的氛围中感受到Linux之美。",
                        "curriculumClassification" => "运维&amp;测试",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "6小时 0分",
                        "studyNum" => 333675,
                        "commentNum" => 1382,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/175",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>18"
                    ],
                    [
                        "id" => 177,
                        "name" => "初识Python",
                        "author" => "廖雪峰",
                        "introduction" => "简介：Python教程基础分《Python入门》和《Python进阶》两门课程，本视频教程是Python第一门课程，是Python开发的入门教程，将介绍Python语言的特点和适用范围，Python基本的数据类型，条件判断和循环，函数，以及Python特有的切片和列表生成式。希望本python教程能够让您快速入门并编写简单的Python程序。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "5小时 0分",
                        "studyNum" => 568715,
                        "commentNum" => 3434,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/177",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>12"
                    ],
                    [
                        "id" => 182,
                        "name" => "基于bootstrap的网页开发",
                        "author" => "姜维_Wayne",
                        "introduction" => "简介：Bootstrap是用于前端开发的工具包，提供了优雅的HTML和CSS规范，并基于jQuery开发了丰富的Web组件。课程介绍了Bootstrap框架的基本知识，并基于Bootstrap框架，实现了一个浏览器介绍的单页面网页，同时网页支持移动设备，通过案例的实现让您对Bootstrap有更深入的了解。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时14分",
                        "studyNum" => 170721,
                        "commentNum" => 394,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/182",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>37"
                    ],
                    [
                        "id" => 196,
                        "name" => "Spring入门篇",
                        "author" => "moocer",
                        "introduction" => "简介：Spring是为解决企业应用程序开发复杂性而创建的一个Java开源框架，应用非常广泛。业内非常流行的SSH架构中的其中一个\"S\"指的就是Spring。本门课程作为Spring的入门级课程，将结合实例为您带来依赖注入、IOC和AOP的基本概念及用法，为后续高级课程的学习打下基础。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "7小时 0分",
                        "studyNum" => 219385,
                        "commentNum" => 372,
                        "comprehensiveScore" => "8.50",
                        "url" => "https://www.imooc.com/learn/196",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>25"
                    ],
                    [
                        "id" => 199,
                        "name" => "反射——Java高级开发必须懂的",
                        "author" => "Cedar",
                        "introduction" => "简介：反射是Java开发中一个非常重要的概念，掌握了反射的知识，才能更好的学习Java高级课程，因此必须要学习——你懂的！本门课程主要介绍Class类的使用，方法和成员变量的反射，以及通过反射了解集合泛型的本质等知识。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时20分",
                        "studyNum" => 128981,
                        "commentNum" => 699,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/199",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>49"
                    ],
                    [
                        "id" => 202,
                        "name" => "深入浅出Java多线程",
                        "author" => "Arthur",
                        "introduction" => "简介：多线程是日常开发中的常用知识，也是难用知识。通过本视频，你可以了解Java中多线程相关的基本概念，如何创建，启动和停止线程？什么是正确的多线程，怎样编写多线程程序。在掌握基础之后，将为你展望进阶路线，为进一步的学习提供方向。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时 0分",
                        "studyNum" => 158529,
                        "commentNum" => 596,
                        "comprehensiveScore" => "9.20",
                        "url" => "https://www.imooc.com/learn/202",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>41"
                    ],
                    [
                        "id" => 206,
                        "name" => "与Android Studio的第一次亲密接触",
                        "author" => "eclipse_xu",
                        "introduction" => "简介：Android Studio是Google在I/O大会上发布的一个新的集成开发环境，可以让Android开发变的更简单。本课程会详细的向您介绍Android Studio的安装配置、使用技巧以及相对于Eclipse开发的优势，并通过实际的操作让大家快速熟悉Android Studio的使用，让您体验更强大的开发工具",
                        "curriculumClassification" => "移动开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "0小时30分",
                        "studyNum" => 105230,
                        "commentNum" => 279,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/206",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>04"
                    ],
                    [
                        "id" => 208,
                        "name" => "版本管理工具介绍—Git篇",
                        "author" => "龙猫",
                        "introduction" => "简介：本课程主要讲解了git在各平台的安装和基本使用，Git能够帮助我们解决文件的提交、检出、回溯历史、冲突解决、多人协作模式等问题，并且大大提升我们的工作效率。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "0小时50分",
                        "studyNum" => 140640,
                        "commentNum" => 308,
                        "comprehensiveScore" => "9.00",
                        "url" => "https://www.imooc.com/learn/208",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>45"
                    ],
                    [
                        "id" => 248,
                        "name" => "Linux C语言编程基本原理与实践",
                        "author" => "DavidChin",
                        "introduction" => "简介：介绍C语言基本工作原理以及适用与C的实际开发方式，并指导童鞋们能在Linux环境下编写并运行符合实际商业开发环境下的C语言程序。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 0分",
                        "studyNum" => 164350,
                        "commentNum" => 322,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/248",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>39"
                    ],
                    [
                        "id" => 249,
                        "name" => "C语言入门",
                        "author" => "milanlover",
                        "introduction" => "简介：本C语言教程从以下几个模块来贯穿主要知识点：初始C程序、数据类型、运算符、语句结构、函数和数组。每个阶段都配有练习题同时提供在线编程任务。希望通过本教程帮助C语言入门学习者迅速掌握程序逻辑并开始C语言编程。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时50分",
                        "studyNum" => 675500,
                        "commentNum" => 2589,
                        "comprehensiveScore" => "9.40",
                        "url" => "https://www.imooc.com/learn/249",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>11"
                    ],
                    [
                        "id" => 250,
                        "name" => "Ajax全接触",
                        "author" => "姜维_Wayne",
                        "introduction" => "简介：本课程通过一个简单的例子，由浅入深，循序渐进的介绍了Ajax的相关概念、原理、实现方式和应用方法，包含HTTP请求的概念、PHP的简单语法、JSON数据格式、Ajax的原生和jQuery实现、跨域等知识点。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "2小时10分",
                        "studyNum" => 209231,
                        "commentNum" => 857,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/250",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>26"
                    ],
                    [
                        "id" => 262,
                        "name" => "玩转Bootstrap（JS插件篇）",
                        "author" => "大漠",
                        "introduction" => "简介：你可以把这门课程看成是《玩转Bootstrap》的补充篇，带领大家学习怎么使用JS自由控制Bootstrap中提供的组件（插件）。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "6小时25分",
                        "studyNum" => 117266,
                        "commentNum" => 133,
                        "comprehensiveScore" => "9.20",
                        "url" => "https://www.imooc.com/learn/262",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>55"
                    ],
                    [
                        "id" => 269,
                        "name" => "JAVA遇见HTML——Servlet篇",
                        "author" => "milanlover",
                        "introduction" => "简介：Servlet是JAVA Web开发的核心基础，在项目中的应用非常广泛。本门课程在JSP课程的基础上，深入介绍Servlet的基础知识。包括Servlet的执行流程和生命周期，Tomcat对Servlet的装载情况，如何获取表单数据以及Servlet路径跳转。最后会带大家使用流行的MVC架构进行项目开发。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "3小时10分",
                        "studyNum" => 141355,
                        "commentNum" => 574,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/269",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>44"
                    ],
                    [
                        "id" => 277,
                        "name" => "JavaScript深入浅出",
                        "author" => "Bosn",
                        "introduction" => "简介：这是一个帮助您系统学习JavaScript编程语言的课程，该课由浅入深的介绍JavaScript的语言特性，结合实际例子解析常见误区，启发你的思考，帮助学习者从入门到掌握，提升您的 JavaScript 技能。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "5小时28分",
                        "studyNum" => 246863,
                        "commentNum" => 450,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/277",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>22"
                    ],
                    [
                        "id" => 313,
                        "name" => "Unity3D快速入门",
                        "author" => "HenryLiang",
                        "introduction" => "简介：Unity 3D是目前非常流行的游戏引擎，上手容易，功能强大，而且跨平台。unity3d教程将从零开始教大家使用Unity，从头开始开发一款小游戏。本课程为Unity 3D入门教程，将实例和理论结合起来，注重实用性， 是一门Unity基础的教程。",
                        "curriculumClassification" => "游戏",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "3小时18分",
                        "studyNum" => 110569,
                        "commentNum" => 93,
                        "comprehensiveScore" => "8.60",
                        "url" => "https://www.imooc.com/learn/313",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>59"
                    ],
                    [
                        "id" => 317,
                        "name" => "python进阶",
                        "author" => "廖雪峰",
                        "introduction" => "简介：Python基础分《Python入门》和《Python进阶》两门课程，《Python进阶》是第二门课程，学习该课程前，请先学习《Python入门》,效果会更好。《Python进阶》课程详细介绍Python强大的函数式编程和面向对象编程，掌握Python高级程序设计的方法。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "3小时33分",
                        "studyNum" => 207241,
                        "commentNum" => 532,
                        "comprehensiveScore" => "9.20",
                        "url" => "https://www.imooc.com/learn/317",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>28"
                    ],
                    [
                        "id" => 337,
                        "name" => "Oracle数据库开发必备利器之SQL基础",
                        "author" => "AnnyQin",
                        "introduction" => "简介：Oracle Database，又名Oracle RDBMS，或简称Oracle，是甲骨文公司的一款关系数据库管理系统。本课程主要介绍Oracle的SQL基础，包括表空间的概念，如何登录Oracle数据库，如何管理表及表中的数据，以及约束的应用。为后续课程的学习打下一个良好的基础。",
                        "curriculumClassification" => "数据库",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时14分",
                        "studyNum" => 133063,
                        "commentNum" => 317,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/337",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>47"
                    ],
                    [
                        "id" => 342,
                        "name" => "C++远征之起航篇",
                        "author" => "james_yuan",
                        "introduction" => "简介：本教程是C++的初级教程，是在C语言基础上的一个延伸，讲述了包括新增数据类型、命名空间等内容，最后通过一个通俗易懂的例子将所述知识点融会贯通，以达到知识灵活运用，最终得以升华的目的。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时 6分",
                        "studyNum" => 242908,
                        "commentNum" => 931,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/342",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>23"
                    ],
                    [
                        "id" => 348,
                        "name" => "进击Node.js基础（一）",
                        "author" => "Scott",
                        "introduction" => "简介：Node.js 的推出，不仅从工程化的角度自动化掉更多琐碎费时的工作，更打破了前端后端的语言边界，让 JavaScript 流畅的运行在服务器端，本系列教程旨在引导前端开发工程师，以及 Node.js 初学者走进这个活泼而有富有生命力的新世界。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "3小时27分",
                        "studyNum" => 220314,
                        "commentNum" => 637,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/348",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>24"
                    ],
                ]
            ],
            [
                "class" => "移动开发",
                "lesson" => [
                    [
                        "id" => 368,
                        "name" => "初识Java微信公众号开发",
                        "author" => "程序猿老毕...",
                        "introduction" => "简介：微信拥有庞大的用户基础，微信公众号的相关开发也比较热门，本套课程就带领大家进入Java微信公众号开发的精彩世界，学习微信公众号开发的相关概念，编辑模式和开发模式应用，以及百度BAE的使用。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时36分",
                        "studyNum" => 141529,
                        "commentNum" => 246,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/368",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>43"
                    ],
                    [
                        "id" => 381,
                        "name" => "C++远征之离港篇",
                        "author" => "james_yuan",
                        "introduction" => "简介：本课程是C++起航篇的延伸，讲述了引用、const、函数默认值、函数重载、内存管理等内容，最后通过一个通俗易懂的例子将所述知识点融会贯通，以达到知识灵活运用，最终得以升华的目的。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时27分",
                        "studyNum" => 104462,
                        "commentNum" => 813,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/381",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>06"
                    ],
                    [
                        "id" => 390,
                        "name" => "版本控制入门 – 搬进 Github",
                        "author" => "happypeter",
                        "introduction" => "简介：版本控制能够大大提高程序员的工作效率，但是通常会涉及到命令行操作，学习曲线陡峭。本课程中使用 Github 网站和图形化客户端来完成版本控制工作，提供一套简单实用的流程，配合图解方式的原理讲解，让大家以最短的时间上手 Git 和 Github 。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时39分",
                        "studyNum" => 116346,
                        "commentNum" => 334,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/390",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>55"
                    ],
                    [
                        "id" => 391,
                        "name" => "认识Hadoop--基础篇",
                        "author" => "Kit_Ren",
                        "introduction" => "简介：大数据时代已经到来，越来越多的行业面临着大量数据需要存储以及分析的挑战。Hadoop，作为一个开源的分布式并行处理平台，以其高扩展、高效率、高可靠等优点，得到越来越广泛的应用。本课旨在培养学员理解Hadoop的架构设计以及掌握Hadoop的运用能力。",
                        "curriculumClassification" => "云计算&amp;大数据",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时22分",
                        "studyNum" => 119786,
                        "commentNum" => 187,
                        "comprehensiveScore" => "9.10",
                        "url" => "https://www.imooc.com/learn/391",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>52"
                    ],
                    [
                        "id" => 397,
                        "name" => "Python开发环境搭建",
                        "author" => "Meshare_huan...",
                        "introduction" => "简介：学习一门语言，首先要把环境准备好，本课程主要讲解在不同系统（Window、Linux）中搭建Python开发环境，及Eclipse配置和 Python 文件类型，让您快速了解和应用Python开发环境及工具，为后续Python学习打好基础。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "0小时16分",
                        "studyNum" => 131496,
                        "commentNum" => 382,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/397",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>49"
                    ],
                    [
                        "id" => 398,
                        "name" => "MySQL开发技巧（一）",
                        "author" => "sqlercn",
                        "introduction" => "简介：MySQL教程，开发技巧（一）告诉你一个不懂SQL技巧的程序员不是一个好程序员。",
                        "curriculumClassification" => "数据库",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "0小时58分",
                        "studyNum" => 123889,
                        "commentNum" => 158,
                        "comprehensiveScore" => "8.80",
                        "url" => "https://www.imooc.com/learn/398",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>50"
                    ],
                ]
            ],
            [
                "class" => "算法&数学",
                "lesson" => [
                    [
                        "id" => 443,
                        "name" => "项目管理利器——maven",
                        "author" => "Eleven_Lee",
                        "introduction" => "简介：maven是优秀的项目管理和构建工具，能让我们更为方便的来管理和构建项目，从最基础的环境配置，到maven核心知识点的应用，本套视频将带领大家进行一段轻松的maven之旅。让我们一起使用maven来构建和管理Java项目吧！",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 1分",
                        "studyNum" => 152473,
                        "commentNum" => 455,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/443",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>42"
                    ],
                    [
                        "id" => 453,
                        "name" => "H5+JS+CSS3实现七夕言情",
                        "author" => "Aaron艾伦",
                        "introduction" => "简介：七夕邻近，才子佳人们又要开始约会了，为了心爱的她献上一份不一样的浪漫。本课程中使用面向接口的编程方式，采用H5+JS+CSS3的混合使用实现整个功能。课程当中总共分为3个主题场景图，多个精灵图以及雪碧图，实现了静与动的完美结合，并且由浅入深的将整个案例拆分讲解。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "2小时25分",
                        "studyNum" => 186661,
                        "commentNum" => 82,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/453",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>33"
                    ],
                ]
            ],
            [
                "class" => "云计算&大数据",
                "lesson" => [
                    [
                        "id" => 456,
                        "name" => "全面解析Java注解",
                        "author" => "刘果国",
                        "introduction" => "简介：在项目开发中，注解的使用无处不在。注解的使用简化了代码，减少了程序员的工作量。本课程带领小伙伴们全面认识Java的注解，包括为什么使用注解、Java中的常见注解、注解的分类和如何自定义注解，最后通过一个实战案例来演示注解在实际项目中的应用。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时 7分",
                        "studyNum" => 108172,
                        "commentNum" => 469,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/456",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>01"
                    ],
                    [
                        "id" => 494,
                        "name" => "Hello，移动WEB",
                        "author" => "碧仔",
                        "introduction" => "简介：在新的移动互联网的浪潮中，移动web的份额将会逐渐超越PC端。身为Web前端工程师您，更应该站在时代和技术的最前缘，拥抱移动web所带来的变革。课程介绍移动web的开发基础，高效的排版布局，常见的移动web问题，终端触摸交互，各种bug坑如何解决等多方面。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "2小时 1分",
                        "studyNum" => 102184,
                        "commentNum" => 228,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/494",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>08"
                    ],
                ]
            ],
            [
                "class" => "运维&测试",
                "lesson" => [
                    [
                        "id" => 418,
                        "name" => "jQuery基础 (一)—样式篇",
                        "author" => "Aaron艾伦",
                        "introduction" => "简介：jQuery基础课程总共分为4个部分，分别是样式篇、事件篇、动画篇、DOM篇。此为第一个部分—样式篇，本课程主要介绍jQuery的基础语法，选择器以及jQuery的一些属性和样式，通过本课程的学习，我们可以用最少的代码做更多的事，让我们一起出发学习吧！",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "2小时23分",
                        "studyNum" => 190182,
                        "commentNum" => 977,
                        "comprehensiveScore" => "9.30",
                        "url" => "https://www.imooc.com/learn/418",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>32"
                    ],
                    [
                        "id" => 422,
                        "name" => "C#开发轻松入门",
                        "author" => "绿豆开门",
                        "introduction" => "简介：本门课程是C#语言的入门教程，在课程中，将从.NET平台和C#的基本概念开始，深入的介绍C#开发的基础语法、简单程序逻辑、Visual Studio工具的使用技巧以及常用的算法的实现。同时，也希望通过与课程相关的练习题和编程练习，帮助小伙伴们快速步入C#语言的大门。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时43分",
                        "studyNum" => 178206,
                        "commentNum" => 713,
                        "comprehensiveScore" => "9.40",
                        "url" => "https://www.imooc.com/learn/422",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>35"
                    ],
                    [
                        "id" => 435,
                        "name" => "SQL Server基础--T-SQL语句",
                        "author" => "小雨老师",
                        "introduction" => "简介：本教程通过对微软SQL Server数据库工具的介绍以及关系型数据库的理解，分析讲解TSQL的基本查询语句和基本用法。其中穿插大量一线实例讲解。",
                        "curriculumClassification" => "数据库",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时37分",
                        "studyNum" => 139840,
                        "commentNum" => 319,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/435",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>46"
                    ],
                ]
            ],
            [
                "class" => "数据库",
                "lesson" => [
                    [
                        "id" => 504,
                        "name" => "React入门",
                        "author" => "Materliu",
                        "introduction" => "简介：学习React出现的背景，React自身的优势与不足，同jQuery, AngularJS等库和框架相比差异点在哪里。React教程分为：React入门\"、\"React实践图片画廊应用(上)\"、\"React实践图片画廊应用(下)\"三门课程，该教程是第一门教程。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "0小时54分",
                        "studyNum" => 122403,
                        "commentNum" => 316,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/504",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>51"
                    ],
                    [
                        "id" => 506,
                        "name" => "前端工程师必备的PS技能——切图篇",
                        "author" => "爱米",
                        "introduction" => "简介：本课程将介绍一些基本的Photoshop操作并重点结合前端的需求做展开。结合实际例子教会大家从PSD入手到获取所需资源的实际实现方式。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 8分",
                        "studyNum" => 236601,
                        "commentNum" => 532,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/506",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>23"
                    ],
                ]
            ],
            [
                "class" => "UI设计&多媒体",
                "lesson" => [
                    [
                        "id" => 563,
                        "name" => "Python开发简单爬虫",
                        "author" => "疯狂的蚂蚁cr...",
                        "introduction" => "简介：爬虫技术用来从互联网上自动获取需要的数据。课程从对爬虫的介绍出发，引入一个简单爬虫的技术架构，然后通过是什么、怎么做、现场演示三步骤，解释爬虫技术架构中的三个模块。最后，一套优雅精美的爬虫代码实战编写，向大家演示了实战抓取百度百科1000个页面的数据全过程",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时14分",
                        "studyNum" => 186066,
                        "commentNum" => 612,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/563",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>34"
                    ],
                    [
                        "id" => 694,
                        "name" => "vue.js入门基础",
                        "author" => "fishenal",
                        "introduction" => "简介：本课程主要讲解了vuejs 是如何站在前端巨人肩膀上，进行新项目的搭建，教程中通过一个简单的todolist讲解vuejs基本用法和常用接口。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时50分",
                        "studyNum" => 205620,
                        "commentNum" => 301,
                        "comprehensiveScore" => "9.20",
                        "url" => "https://www.imooc.com/learn/694",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>29"
                    ],
                ]
            ],
            [
                "class" => "游戏",
                "lesson" => [
                    [
                        "id" => 717,
                        "name" => "初识机器学习-理论篇",
                        "author" => "stonedog",
                        "introduction" => "简介：大数据时代背景下，机器学习在各行各业都有广泛应用。本课对机器学习做入门级介绍，主要介绍机器学习的概念、典型的行业案例，并对比机器学习和传统数据分析的差别，一些经典的算法，最后是Demo演示",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时48分",
                        "studyNum" => 108490,
                        "commentNum" => 253,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/717",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>01"
                    ]
                ]
            ]
        ];

        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取超过10万学习人数的付费课程列表
     */
    public function moreThenTenPayLesson()
    {
        $payLessonModel = new PayLessonModel();

        $result = $payLessonModel->getLessonListByWhere(['studyNum' => ['gt', 100000]]);

        $result = [
            [
                "class" => "前沿技术",
                "lesson" => [
                    [
                        "id" => 24,
                        "name" => "HTML5之元素与标签结构",
                        "author" => "Alex",
                        "introduction" => "简介：知识与实例相结合，本部分是HTML5课程的基础内容，主要讲解HTML5的标签结构，与传统的HTML4相比，新增和删去的标签及相关属性，并深入拓展了全局属性的相关知识。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时 0分",
                        "studyNum" => 171401,
                        "commentNum" => 439,
                        "comprehensiveScore" => "9.20",
                        "url" => "http://www.imooc.com/learn/24",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>36"
                    ],
                    [
                        "id" => 26,
                        "name" => "PHP进阶篇",
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
                ]
            ],
            [
                "class" => "前端开发",
                "lesson" => [
                    [
                        "id" => 47,
                        "name" => "Spring MVC起步",
                        "author" => "Arthur",
                        "introduction" => "简介：Spring MVC为我们提供了一个基于组件和松耦合的MVC实现框架。在使用Java中其它MVC框架多年之后，面对Spring MVC有一种相见恨晚的感觉。Spring MVC是如此的优雅，轻盈与简洁， 让人从其它框架的桎梏解脱出来。本课程将带你步入Spring MVC。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "2小时 6分",
                        "studyNum" => 170026,
                        "commentNum" => 487,
                        "comprehensiveScore" => "8.50",
                        "url" => "https://www.imooc.com/learn/47",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>38"
                    ],
                    [
                        "id" => 48,
                        "name" => "IT菜鸟逆袭指南（江湖篇）",
                        "author" => "慕课官方号...",
                        "introduction" => "简介：每个挨踢菜鸟都渴望成功逆袭，摆脱悲催的现状成为技术大咖。近日，慕课新闻报道了一则关于IT大侠慕无忌的神奇历练历程。想必会给正在启程的你一些启发。屌丝逆袭，不是传说！",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "0小时4分",
                        "studyNum" => 107393,
                        "commentNum" => 498,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/48",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>02"
                    ],
                    [
                        "id" => 54,
                        "name" => "PHP入门篇",
                        "author" => "Kings",
                        "introduction" => "简介：本教程带领大家轻松学习PHP基础知识，了解PHP中的变量、变量的类型、常量等概念，认识PHP中的运算符，通过本教程学习能够掌握PHP中顺序结构、条件结构、循环结构三种语言结构语句。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "5小时57分",
                        "studyNum" => 406596,
                        "commentNum" => 739,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/54",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>15"
                    ],
                    [
                        "id" => 57,
                        "name" => "如何用CSS进行网页布局",
                        "author" => "江老实",
                        "introduction" => "简介：如何用CSS进行网页布局？这可是前端工程师最最基本的技能，本课程教你怎么制作一列布局、二列布局、三列布局当然还有最通用的混合布局，而且你还可以选择让它固定还是自适应。用CSS重新规划你的网页，让你的网页从此更美观、更友好。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "0小时22分",
                        "studyNum" => 195371,
                        "commentNum" => 1954,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/57",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>31"
                    ],
                    [
                        "id" => 85,
                        "name" => "Java入门第一季",
                        "author" => "老实人",
                        "introduction" => "简介：本教程为Java入门第一季，欢迎来到精彩的Java编程世界！Java语言已经成为当前软件开发行业中主流的开发语言。本教程将介绍Java环境搭建、工具使用、基础语法。带领大家一步一步的踏入Java达人殿堂！Let’s go!",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "5小时 0分",
                        "studyNum" => 909311,
                        "commentNum" => 5668,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/85",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>10"
                    ],
                    [
                        "id" => 110,
                        "name" => "Java入门第三季",
                        "author" => "陈码农",
                        "introduction" => "简介：在本课程中，@陈码农 携手 @laurenyany 将带领小伙伴们进一步探索 Java 的奥秘，希望通过本次课程的学习，能够帮助小伙伴们快速掌握关于Java中的异常处理、集合框架、字符串的操作和常用类的使用。不容错过的精彩，快来加入吧！！",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "5小时 0分",
                        "studyNum" => 346118,
                        "commentNum" => 1058,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/110",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>17"
                    ],
                    [
                        "id" => 111,
                        "name" => "Linux 达人养成计划 II",
                        "author" => "壞大叔badUnc...",
                        "introduction" => "简介：本课程介绍Linux系统下操作VI编辑器、创建文本文件、VI的三种操作模式、磁盘分区与格式化、用户及用户组权限的相关操作与管理等，让童鞋们对Linux系统有进一步的理解，对Linux服务器的维护操作更加得心应手。",
                        "curriculumClassification" => "运维&amp;测试",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "2小时30分",
                        "studyNum" => 109215,
                        "commentNum" => 375,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/111",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>00"
                    ],
                    [
                        "id" => 117,
                        "name" => "数据库设计那些事",
                        "author" => "sqlercn",
                        "introduction" => "简介：数据库作为大多数应用的存储组件，对程序猿所开发的程序是否可以稳定高效的运行起着至关重要的作用。本课程从数据库设计的基本理论入手结合简单的实例，简单明了的告诉您如何设计出一个简洁明了同时又高效稳定的数据库结构。",
                        "curriculumClassification" => "数据库",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时50分",
                        "studyNum" => 105177,
                        "commentNum" => 190,
                        "comprehensiveScore" => "9.30",
                        "url" => "https://www.imooc.com/learn/117",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>04"
                    ],
                    [
                        "id" => 122,
                        "name" => "与MySQL的零距离接触",
                        "author" => "平然",
                        "introduction" => "简介：本课程涵盖全部MySQL数据库的基础，主要学习MySQL数据库的基础知识、数据表的常用操作及各种约束的使用，以及综合的运用各种命令实现记录进行CURD等操作，本课程的目标就是“看得懂、学得会、做得出”，为后续的学习打下夯实的基础。",
                        "curriculumClassification" => "数据库",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "8小时29分",
                        "studyNum" => 346696,
                        "commentNum" => 894,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/122",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>17"
                    ],
                    [
                        "id" => 123,
                        "name" => "文件传输基础——Java IO流",
                        "author" => "Cedar",
                        "introduction" => "简介：如何在Java中进行文件的读写，Java IO流是必备的知识。本门课程主要为您带来Java中的输入输出流的内容，包括文件编码、使用File类对文件和目录进行管理、字节流和字符流的基本操作，以及对象的序列化和反序列化的内容。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "2小时 0分",
                        "studyNum" => 119178,
                        "commentNum" => 637,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/123",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>53"
                    ],
                    [
                        "id" => 124,
                        "name" => "Java入门第二季",
                        "author" => "小慕",
                        "introduction" => "简介：本课程是程序猿质变课程，理解面向对象的思想，掌握面向对象的基本原则以及 Java 面向对象编程基本实现原理，熟练使用封装、继承、多态面向对象三大特性，带你进一步探索 Java 世界的奥秘！",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时 0分",
                        "studyNum" => 438945,
                        "commentNum" => 1996,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/124",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>14"
                    ],
                    [
                        "id" => 139,
                        "name" => "PS入门教程——新手过招",
                        "author" => "Oeasy",
                        "introduction" => "简介：慕课网推出的PS入门教程，PS入门学习必备课程，本课程将带你从PS的基本界面开始熟悉，ps入门课程主要分为三个章节，ps基本工具，ps选择与变形，ps色彩调整，通过小案例来学习简单的工具，终极目标就是先揭开PS的面纱，让你掌握PS的基本用法。",
                        "curriculumClassification" => "UI设计&amp;多媒体",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "9小时45分",
                        "studyNum" => 546102,
                        "commentNum" => 411,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/139",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>13"
                    ],
                    [
                        "id" => 141,
                        "name" => "玩转Bootstrap（基础）",
                        "author" => "大漠",
                        "introduction" => "简介：本Bootstrap教程能够让您了解到，Bootstrap框架是一个非常受欢迎的前端开发框架，他能让后端程序员和不懂设计的前端人员制作出优美的Web页面或Web应用程序。在这个Bootstrap教程中，将带领大家了解Bootstrap框架以及如何使用Bootstrap框架，通过本教程学习能够独立定制出适合自己的Bootstrap。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "10小时 0分",
                        "studyNum" => 281226,
                        "commentNum" => 667,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/141",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>21"
                    ],
                    [
                        "id" => 147,
                        "name" => "企业网站综合布局实战",
                        "author" => "zkpplpp",
                        "introduction" => "简介：本课程重点介绍HTML/CSS实现常见企业网站布局的方法、布局中常用的基本盒子模型、三列布局、两列自适应高度及基于jQ的开源图片幻灯片切换效果插件的使用。让您快速掌握企业网站的基本布局方法，同时对HTML、CSS、JS、jQ等知识的综合运用和提升。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时55分",
                        "studyNum" => 141290,
                        "commentNum" => 518,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/147",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>45"
                    ],
                    [
                        "id" => 148,
                        "name" => "手把手教你实现电商网站后台开发",
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
                        "id" => 152,
                        "name" => "PS入门基础-魔幻调色",
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
                        "id" => 156,
                        "name" => "AngularJS实战",
                        "author" => "大漠穷秋",
                        "introduction" => "简介：欢迎大家与大漠穷秋老师一起学习AngularJS的基础教程，让我们一起通过实例学习并学会AngularJS！",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "高级",
                        "price" => "0.00",
                        "totalTime" => "7小时13分",
                        "studyNum" => 207299,
                        "commentNum" => 301,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/156",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>27"
                    ],
                    [
                        "id" => 159,
                        "name" => "PS大神通关教程",
                        "author" => "祁连山",
                        "introduction" => "简介：祁大湿带你梳理PS知识体系，分享实战经验，让你真正掌握PS使用方法。工具使用，图层操作，色彩调整，十八般武艺样样精通。真正实现菜鸟到大神的华丽转身。",
                        "curriculumClassification" => "UI设计&amp;多媒体",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "8小时12分",
                        "studyNum" => 312506,
                        "commentNum" => 159,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/159",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>19"
                    ],
                ]
            ],
            [
                "class" => "后端开发",
                "lesson" => [
                    [
                        "id" => 167,
                        "name" => "JS动画效果",
                        "author" => "vivian",
                        "introduction" => "简介：通过本课程JS动画的学习，从简单动画开始，逐步深入缓冲动画、多物体动画、链式动画、多动画同时运动到完美运动框架的过程，每一个效果封装成一个小运动框架，逐渐培养和锻炼封装运动框架和编程的思想，让您的逻辑思维不断增强。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 8分",
                        "studyNum" => 102810,
                        "commentNum" => 526,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/167",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>08"
                    ],
                    [
                        "id" => 175,
                        "name" => "Linux达人养成计划 I",
                        "author" => "Tony",
                        "introduction" => "简介：本课程以通俗易懂的语言、风趣幽默的实例、清晰严谨的逻辑介绍了Linux的基础内容。课程以CentOS操作系统为例，为你带来Linux的简介、系统安装和常用命令等内容。让您在轻松的氛围中感受到Linux之美。",
                        "curriculumClassification" => "运维&amp;测试",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "6小时 0分",
                        "studyNum" => 333675,
                        "commentNum" => 1382,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/175",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>18"
                    ],
                    [
                        "id" => 196,
                        "name" => "Spring入门篇",
                        "author" => "moocer",
                        "introduction" => "简介：Spring是为解决企业应用程序开发复杂性而创建的一个Java开源框架，应用非常广泛。业内非常流行的SSH架构中的其中一个\"S\"指的就是Spring。本门课程作为Spring的入门级课程，将结合实例为您带来依赖注入、IOC和AOP的基本概念及用法，为后续高级课程的学习打下基础。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "7小时 0分",
                        "studyNum" => 219385,
                        "commentNum" => 372,
                        "comprehensiveScore" => "8.50",
                        "url" => "https://www.imooc.com/learn/196",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>25"
                    ],
                    [
                        "id" => 199,
                        "name" => "反射——Java高级开发必须懂的",
                        "author" => "Cedar",
                        "introduction" => "简介：反射是Java开发中一个非常重要的概念，掌握了反射的知识，才能更好的学习Java高级课程，因此必须要学习——你懂的！本门课程主要介绍Class类的使用，方法和成员变量的反射，以及通过反射了解集合泛型的本质等知识。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时20分",
                        "studyNum" => 128981,
                        "commentNum" => 699,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/199",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>49"
                    ],
                    [
                        "id" => 202,
                        "name" => "深入浅出Java多线程",
                        "author" => "Arthur",
                        "introduction" => "简介：多线程是日常开发中的常用知识，也是难用知识。通过本视频，你可以了解Java中多线程相关的基本概念，如何创建，启动和停止线程？什么是正确的多线程，怎样编写多线程程序。在掌握基础之后，将为你展望进阶路线，为进一步的学习提供方向。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时 0分",
                        "studyNum" => 158529,
                        "commentNum" => 596,
                        "comprehensiveScore" => "9.20",
                        "url" => "https://www.imooc.com/learn/202",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>41"
                    ],
                    [
                        "id" => 248,
                        "name" => "Linux C语言编程基本原理与实践",
                        "author" => "DavidChin",
                        "introduction" => "简介：介绍C语言基本工作原理以及适用与C的实际开发方式，并指导童鞋们能在Linux环境下编写并运行符合实际商业开发环境下的C语言程序。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 0分",
                        "studyNum" => 164350,
                        "commentNum" => 322,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/248",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>39"
                    ],
                    [
                        "id" => 249,
                        "name" => "C语言入门",
                        "author" => "milanlover",
                        "introduction" => "简介：本C语言教程从以下几个模块来贯穿主要知识点：初始C程序、数据类型、运算符、语句结构、函数和数组。每个阶段都配有练习题同时提供在线编程任务。希望通过本教程帮助C语言入门学习者迅速掌握程序逻辑并开始C语言编程。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时50分",
                        "studyNum" => 675500,
                        "commentNum" => 2589,
                        "comprehensiveScore" => "9.40",
                        "url" => "https://www.imooc.com/learn/249",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>11"
                    ],
                    [
                        "id" => 269,
                        "name" => "JAVA遇见HTML——Servlet篇",
                        "author" => "milanlover",
                        "introduction" => "简介：Servlet是JAVA Web开发的核心基础，在项目中的应用非常广泛。本门课程在JSP课程的基础上，深入介绍Servlet的基础知识。包括Servlet的执行流程和生命周期，Tomcat对Servlet的装载情况，如何获取表单数据以及Servlet路径跳转。最后会带大家使用流行的MVC架构进行项目开发。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "3小时10分",
                        "studyNum" => 141355,
                        "commentNum" => 574,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/269",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>44"
                    ],
                    [
                        "id" => 277,
                        "name" => "JavaScript深入浅出",
                        "author" => "Bosn",
                        "introduction" => "简介：这是一个帮助您系统学习JavaScript编程语言的课程，该课由浅入深的介绍JavaScript的语言特性，结合实际例子解析常见误区，启发你的思考，帮助学习者从入门到掌握，提升您的 JavaScript 技能。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "5小时28分",
                        "studyNum" => 246863,
                        "commentNum" => 450,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/277",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>22"
                    ],
                    [
                        "id" => 313,
                        "name" => "Unity3D快速入门",
                        "author" => "HenryLiang",
                        "introduction" => "简介：Unity 3D是目前非常流行的游戏引擎，上手容易，功能强大，而且跨平台。unity3d教程将从零开始教大家使用Unity，从头开始开发一款小游戏。本课程为Unity 3D入门教程，将实例和理论结合起来，注重实用性， 是一门Unity基础的教程。",
                        "curriculumClassification" => "游戏",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "3小时18分",
                        "studyNum" => 110569,
                        "commentNum" => 93,
                        "comprehensiveScore" => "8.60",
                        "url" => "https://www.imooc.com/learn/313",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>59"
                    ],
                    [
                        "id" => 317,
                        "name" => "python进阶",
                        "author" => "廖雪峰",
                        "introduction" => "简介：Python基础分《Python入门》和《Python进阶》两门课程，《Python进阶》是第二门课程，学习该课程前，请先学习《Python入门》,效果会更好。《Python进阶》课程详细介绍Python强大的函数式编程和面向对象编程，掌握Python高级程序设计的方法。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "3小时33分",
                        "studyNum" => 207241,
                        "commentNum" => 532,
                        "comprehensiveScore" => "9.20",
                        "url" => "https://www.imooc.com/learn/317",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>28"
                    ],
                    [
                        "id" => 337,
                        "name" => "Oracle数据库开发必备利器之SQL基础",
                        "author" => "AnnyQin",
                        "introduction" => "简介：Oracle Database，又名Oracle RDBMS，或简称Oracle，是甲骨文公司的一款关系数据库管理系统。本课程主要介绍Oracle的SQL基础，包括表空间的概念，如何登录Oracle数据库，如何管理表及表中的数据，以及约束的应用。为后续课程的学习打下一个良好的基础。",
                        "curriculumClassification" => "数据库",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时14分",
                        "studyNum" => 133063,
                        "commentNum" => 317,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/337",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>47"
                    ],
                    [
                        "id" => 342,
                        "name" => "C++远征之起航篇",
                        "author" => "james_yuan",
                        "introduction" => "简介：本教程是C++的初级教程，是在C语言基础上的一个延伸，讲述了包括新增数据类型、命名空间等内容，最后通过一个通俗易懂的例子将所述知识点融会贯通，以达到知识灵活运用，最终得以升华的目的。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时 6分",
                        "studyNum" => 242908,
                        "commentNum" => 931,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/342",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>23"
                    ],
                    [
                        "id" => 348,
                        "name" => "进击Node.js基础（一）",
                        "author" => "Scott",
                        "introduction" => "简介：Node.js 的推出，不仅从工程化的角度自动化掉更多琐碎费时的工作，更打破了前端后端的语言边界，让 JavaScript 流畅的运行在服务器端，本系列教程旨在引导前端开发工程师，以及 Node.js 初学者走进这个活泼而有富有生命力的新世界。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "3小时27分",
                        "studyNum" => 220314,
                        "commentNum" => 637,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/348",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>24"
                    ],
                ]
            ],
            [
                "class" => "移动开发",
                "lesson" => [
                    [
                        "id" => 368,
                        "name" => "初识Java微信公众号开发",
                        "author" => "程序猿老毕...",
                        "introduction" => "简介：微信拥有庞大的用户基础，微信公众号的相关开发也比较热门，本套课程就带领大家进入Java微信公众号开发的精彩世界，学习微信公众号开发的相关概念，编辑模式和开发模式应用，以及百度BAE的使用。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时36分",
                        "studyNum" => 141529,
                        "commentNum" => 246,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/368",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>43"
                    ],
                    [
                        "id" => 381,
                        "name" => "C++远征之离港篇",
                        "author" => "james_yuan",
                        "introduction" => "简介：本课程是C++起航篇的延伸，讲述了引用、const、函数默认值、函数重载、内存管理等内容，最后通过一个通俗易懂的例子将所述知识点融会贯通，以达到知识灵活运用，最终得以升华的目的。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时27分",
                        "studyNum" => 104462,
                        "commentNum" => 813,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/381",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>06"
                    ],
                    [
                        "id" => 390,
                        "name" => "版本控制入门 – 搬进 Github",
                        "author" => "happypeter",
                        "introduction" => "简介：版本控制能够大大提高程序员的工作效率，但是通常会涉及到命令行操作，学习曲线陡峭。本课程中使用 Github 网站和图形化客户端来完成版本控制工作，提供一套简单实用的流程，配合图解方式的原理讲解，让大家以最短的时间上手 Git 和 Github 。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时39分",
                        "studyNum" => 116346,
                        "commentNum" => 334,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/390",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>55"
                    ],
                    [
                        "id" => 391,
                        "name" => "认识Hadoop--基础篇",
                        "author" => "Kit_Ren",
                        "introduction" => "简介：大数据时代已经到来，越来越多的行业面临着大量数据需要存储以及分析的挑战。Hadoop，作为一个开源的分布式并行处理平台，以其高扩展、高效率、高可靠等优点，得到越来越广泛的应用。本课旨在培养学员理解Hadoop的架构设计以及掌握Hadoop的运用能力。",
                        "curriculumClassification" => "云计算&amp;大数据",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时22分",
                        "studyNum" => 119786,
                        "commentNum" => 187,
                        "comprehensiveScore" => "9.10",
                        "url" => "https://www.imooc.com/learn/391",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>52"
                    ],
                ]
            ],
            [
                "class" => "算法&数学",
                "lesson" => [
                    [
                        "id" => 443,
                        "name" => "项目管理利器——maven",
                        "author" => "Eleven_Lee",
                        "introduction" => "简介：maven是优秀的项目管理和构建工具，能让我们更为方便的来管理和构建项目，从最基础的环境配置，到maven核心知识点的应用，本套视频将带领大家进行一段轻松的maven之旅。让我们一起使用maven来构建和管理Java项目吧！",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 1分",
                        "studyNum" => 152473,
                        "commentNum" => 455,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/443",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>42"
                    ],
                    [
                        "id" => 453,
                        "name" => "H5+JS+CSS3实现七夕言情",
                        "author" => "Aaron艾伦",
                        "introduction" => "简介：七夕邻近，才子佳人们又要开始约会了，为了心爱的她献上一份不一样的浪漫。本课程中使用面向接口的编程方式，采用H5+JS+CSS3的混合使用实现整个功能。课程当中总共分为3个主题场景图，多个精灵图以及雪碧图，实现了静与动的完美结合，并且由浅入深的将整个案例拆分讲解。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "2小时25分",
                        "studyNum" => 186661,
                        "commentNum" => 82,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/453",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>33"
                    ],
                ]
            ],
            [
                "class" => "云计算&大数据",
                "lesson" => [
                    [
                        "id" => 456,
                        "name" => "全面解析Java注解",
                        "author" => "刘果国",
                        "introduction" => "简介：在项目开发中，注解的使用无处不在。注解的使用简化了代码，减少了程序员的工作量。本课程带领小伙伴们全面认识Java的注解，包括为什么使用注解、Java中的常见注解、注解的分类和如何自定义注解，最后通过一个实战案例来演示注解在实际项目中的应用。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时 7分",
                        "studyNum" => 108172,
                        "commentNum" => 469,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/456",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>01"
                    ],
                    [
                        "id" => 494,
                        "name" => "Hello，移动WEB",
                        "author" => "碧仔",
                        "introduction" => "简介：在新的移动互联网的浪潮中，移动web的份额将会逐渐超越PC端。身为Web前端工程师您，更应该站在时代和技术的最前缘，拥抱移动web所带来的变革。课程介绍移动web的开发基础，高效的排版布局，常见的移动web问题，终端触摸交互，各种bug坑如何解决等多方面。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "2小时 1分",
                        "studyNum" => 102184,
                        "commentNum" => 228,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/494",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>08"
                    ],
                ]
            ],
            [
                "class" => "运维&测试",
                "lesson" => [
                    [
                        "id" => 418,
                        "name" => "jQuery基础 (一)—样式篇",
                        "author" => "Aaron艾伦",
                        "introduction" => "简介：jQuery基础课程总共分为4个部分，分别是样式篇、事件篇、动画篇、DOM篇。此为第一个部分—样式篇，本课程主要介绍jQuery的基础语法，选择器以及jQuery的一些属性和样式，通过本课程的学习，我们可以用最少的代码做更多的事，让我们一起出发学习吧！",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "2小时23分",
                        "studyNum" => 190182,
                        "commentNum" => 977,
                        "comprehensiveScore" => "9.30",
                        "url" => "https://www.imooc.com/learn/418",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>32"
                    ],
                    [
                        "id" => 422,
                        "name" => "C#开发轻松入门",
                        "author" => "绿豆开门",
                        "introduction" => "简介：本门课程是C#语言的入门教程，在课程中，将从.NET平台和C#的基本概念开始，深入的介绍C#开发的基础语法、简单程序逻辑、Visual Studio工具的使用技巧以及常用的算法的实现。同时，也希望通过与课程相关的练习题和编程练习，帮助小伙伴们快速步入C#语言的大门。",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "4小时43分",
                        "studyNum" => 178206,
                        "commentNum" => 713,
                        "comprehensiveScore" => "9.40",
                        "url" => "https://www.imooc.com/learn/422",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>35"
                    ],
                    [
                        "id" => 435,
                        "name" => "SQL Server基础--T-SQL语句",
                        "author" => "小雨老师",
                        "introduction" => "简介：本教程通过对微软SQL Server数据库工具的介绍以及关系型数据库的理解，分析讲解TSQL的基本查询语句和基本用法。其中穿插大量一线实例讲解。",
                        "curriculumClassification" => "数据库",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时37分",
                        "studyNum" => 139840,
                        "commentNum" => 319,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/435",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>46"
                    ],
                ]
            ],
            [
                "class" => "数据库",
                "lesson" => [
                    [
                        "id" => 504,
                        "name" => "React入门",
                        "author" => "Materliu",
                        "introduction" => "简介：学习React出现的背景，React自身的优势与不足，同jQuery, AngularJS等库和框架相比差异点在哪里。React教程分为：React入门\"、\"React实践图片画廊应用(上)\"、\"React实践图片画廊应用(下)\"三门课程，该教程是第一门教程。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "0小时54分",
                        "studyNum" => 122403,
                        "commentNum" => 316,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/504",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>51"
                    ],
                    [
                        "id" => 506,
                        "name" => "前端工程师必备的PS技能——切图篇",
                        "author" => "爱米",
                        "introduction" => "简介：本课程将介绍一些基本的Photoshop操作并重点结合前端的需求做展开。结合实际例子教会大家从PSD入手到获取所需资源的实际实现方式。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "2小时 8分",
                        "studyNum" => 236601,
                        "commentNum" => 532,
                        "comprehensiveScore" => "9.60",
                        "url" => "https://www.imooc.com/learn/506",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>23"
                    ],
                ]
            ],
            [
                "class" => "UI设计&多媒体",
                "lesson" => [
                    [
                        "id" => 563,
                        "name" => "Python开发简单爬虫",
                        "author" => "疯狂的蚂蚁cr...",
                        "introduction" => "简介：爬虫技术用来从互联网上自动获取需要的数据。课程从对爬虫的介绍出发，引入一个简单爬虫的技术架构，然后通过是什么、怎么做、现场演示三步骤，解释爬虫技术架构中的三个模块。最后，一套优雅精美的爬虫代码实战编写，向大家演示了实战抓取百度百科1000个页面的数据全过程",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "初级",
                        "price" => "0.00",
                        "totalTime" => "1小时14分",
                        "studyNum" => 186066,
                        "commentNum" => 612,
                        "comprehensiveScore" => "9.70",
                        "url" => "https://www.imooc.com/learn/563",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>34"
                    ],
                    [
                        "id" => 694,
                        "name" => "vue.js入门基础",
                        "author" => "fishenal",
                        "introduction" => "简介：本课程主要讲解了vuejs 是如何站在前端巨人肩膀上，进行新项目的搭建，教程中通过一个简单的todolist讲解vuejs基本用法和常用接口。",
                        "curriculumClassification" => "前端开发",
                        "difficulty" => "中级",
                        "price" => "0.00",
                        "totalTime" => "1小时50分",
                        "studyNum" => 205620,
                        "commentNum" => 301,
                        "comprehensiveScore" => "9.20",
                        "url" => "https://www.imooc.com/learn/694",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>46=>29"
                    ],
                ]
            ],
            [
                "class" => "游戏",
                "lesson" => [
                    [
                        "id" => 717,
                        "name" => "初识机器学习-理论篇",
                        "author" => "stonedog",
                        "introduction" => "简介：大数据时代背景下，机器学习在各行各业都有广泛应用。本课对机器学习做入门级介绍，主要介绍机器学习的概念、典型的行业案例，并对比机器学习和传统数据分析的差别，一些经典的算法，最后是Demo演示",
                        "curriculumClassification" => "后端开发",
                        "difficulty" => "入门",
                        "price" => "0.00",
                        "totalTime" => "1小时48分",
                        "studyNum" => 108490,
                        "commentNum" => 253,
                        "comprehensiveScore" => "9.50",
                        "url" => "https://www.imooc.com/learn/717",
                        "authorUrl" => "",
                        "grabTime" => "2018-11-29 00=>47=>01"
                    ]
                ]
            ]
        ];
        $this->ajaxReturn(1000, 'ok', $result);
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

