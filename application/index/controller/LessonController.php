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

    /**
     * 获取免费课程七天内增长率
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
                "name"=>"基于Spring Boot技术栈博客系统企业级前后端实战",
                "rate"=>"300"
            ],
            [
                "name"=>"Python接口自动化测试框架实战",
                "rate"=>"266"
            ],
            [
                "name"=>"SpringBoot 仿抖音短视频小程序开发 全栈式实战项目",
                "rate"=>"243"
            ],
            [
                "name"=>"Python Flask高级编程",
                "rate"=>"201"
            ],
            [
                "name"=>"微信小游戏入门与实战 刷爆朋友圈",
                "rate"=>"177"
            ],
            [
                "name"=>"Java秒杀系统方案优化 高性能高并发实战",
                "rate"=>"135"
            ],
            [
                "name"=>"纯正商业级应用-微信小程序开发实战",
                "rate"=>"99"
            ],
            [
                "name"=>"微信小程序商城构建全栈应用",
                "rate"=>"23"
            ],
            [
                "name"=>"Vue2.5开发去哪儿网App 从零基础入门到实战项目",
                "rate"=>"21"
            ],
            [
                "name"=>"手把手开发一个完整即时通讯APP",
                "rate"=>"20"
            ]
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
        //每个分类下 各个评分段的课程数量 评分段为['<8', '8-8.4', '8.5-8.9', '9-9.4', '9.5-9.7', '9.8-10']
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

        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 获取超过10万学习人数的付费课程列表
     */
    public function moreThenTenPayLesson()
    {
        $payLessonModel = new PayLessonModel();

        $result = $payLessonModel->getLessonListByWhere(['studyNum' => ['gt', 100000]]);

        $this->ajaxReturn(1000, 'ok', $result);
    }
}

