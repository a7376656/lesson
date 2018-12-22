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
    private function classInfo($lessonModel){
        $classList =["前端开发","后端开发","移动开发","前沿技术","运维&测试","算法&数学","云计算&大数据","UI设计&多媒体","数据库","游戏"];
        $classInfo= [];
        foreach ($classList as $v){
            $temp = [
                "key" => $v,
                "value" => $lessonModel->getLessonCount(['curriculumClassification' => $v,])
            ];
            array_push($classInfo ,$temp);
        }
        return $classInfo;
    }
    private function printPre($arr){
        echo "<pre>";print_r($arr);echo "<pre>";
    }
    private function eachDayStudyNum($cur,$pre,$id,$flag){
        $timeLineModel = new TimeLineModel();
        return $timeLineModel->getInfoByWhere([
            'date' => date('Y-m-d', strtotime($cur)),
            'flag' => $flag,
            'id' => $id,
        ], 'todayNum')['todayNum']-$timeLineModel->getInfoByWhere([
            'date' => date('Y-m-d', strtotime($pre)),
            'flag' => $flag,
            'id' => $id,
        ], 'todayNum')['todayNum'];
    }
    /**
     * 首页数据
     */
    public function moocOverview()
    {
        $dataModel = new DataModel();

        //获取慕课网教师数、分类数、课程数目、评论数
        $result = $dataModel->getInfo();
        $freeClass = $this->classInfo( new LessonModel());
        $payClass = $this->classInfo( new PayLessonModel());
        $data = [
            "lessonNum" => [
                "free" => $result['freeNum'],
                "pay" => $result['payNum'],
            ],
            "lecturerNum" => '614',
            "classNum" => $result['classNum'],
            "commentNum" => $result['commentNum'],
            "freeClass" => $freeClass,
            "payClass" => $payClass,
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
            $sum = 0;
            foreach ($gradeArray as $value) {
                $sum += $value;
            }
            if ($sum == 0) {
                continue;
            }
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
            //每个值乘以8
            foreach ($durationArray as &$value) {
                $value = 8*$value;
            }
            //过滤调全部都是0的分类
            $sum = 0;
            foreach ($durationArray as $value) {
                $sum += $value;
            }
            if ($sum == 0) {
                continue;
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
            $sum = 0;
            foreach ($difficultyArray as $value) {
                $sum += $value;
            }
            if ($sum == 0) {
                continue;
            }
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
            $sum = 0;
            foreach ($gradeArray as $value) {
                $sum += $value;
            }
            if ($sum == 0) {
                continue;
            }
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
            $sum = 0;
            foreach ($durationArray as $value) {
                $sum += $value;
            }
            if ($sum == 0) {
                continue;
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
            $sum = 0;
            foreach ($difficultyArray as $value) {
                $sum += $value;
            }
            if ($sum == 0) {
                continue;
            }
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

//        $this->printPre($lessonInfo);

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
            'curriculumClassification' => $lessonInfo['curriculumClassification'],
        ]);//与该课程分数相同的课程数
        $lessonInfo['sameCommentNum'] = $lessonModel->getLessonCountReal([
            'commentNum' => [['>=',  $lessonInfo['commentNum']*0.9], ['<',  $lessonInfo['commentNum']*1.1]],
            'curriculumClassification' => $lessonInfo['curriculumClassification'],
        ]);//与该课程评论数相同的课程数

        $lessonInfo['sameStudyNum'] = $lessonModel->getLessonCountReal([
            'studyNum' => [['>=',  $lessonInfo['studyNum']*0.9], ['<',  $lessonInfo['studyNum']*1.1]],
            'curriculumClassification' => $lessonInfo['curriculumClassification'],
        ]);//与该课程学习数相同的课程数
        $lessonInfo['totalMember'] = $lessonModel->getLessonCount([
            'curriculumClassification' => $lessonInfo['curriculumClassification'],
        ]);//与该课程同分类的课程数

        //过去七天学习人数
        $lessonInfo['lastSevenDayInfo'] = [
            //七天前
            date('Y-m-d', strtotime('-7 days')) => $this->eachDayStudyNum('-7 days','-8 days',$params['id'],$params['flag']),
            //6天前
            date('Y-m-d', strtotime('-6 days')) => $this->eachDayStudyNum('-6 days','-7 days',$params['id'],$params['flag']),
            //5天前
            date('Y-m-d', strtotime('-5 days')) => $this->eachDayStudyNum('-5 days','-6 days',$params['id'],$params['flag']),
            //4天前
            date('Y-m-d', strtotime('-4 days')) => $this->eachDayStudyNum('-4 days','-5 days',$params['id'],$params['flag']),
            //3天前
            date('Y-m-d', strtotime('-3 days')) => $this->eachDayStudyNum('-3 days','-4 days',$params['id'],$params['flag']),
            //2天前
            date('Y-m-d', strtotime('-2 days')) => $this->eachDayStudyNum('-2 days','-3 days',$params['id'],$params['flag']),
            //1天前
            date('Y-m-d', strtotime('yesterday')) => $this->eachDayStudyNum('yesterday','-2 days',$params['id'],$params['flag']),
        ];

        //评论信息
        $arr = json_decode(file_get_contents(__DIR__. '/commentKeyword.json'), true);

        foreach ($arr as $v) {
            $info['class'] = $v['label'];
            $info['comment'] = [];
            foreach ($v['list'] as $val) {
                $info['comment'] = array_merge($commentModel->getCommentListByWhere([
                    'lessonId' => $params['id'],
                    'content' => ['like', '%'. $val .'%'],
                ], 'content,score,lessonId'), $info['comment']);
            }

            $lessonInfo['comment'][] = $info;
        }

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
     * 获取分类列表
     */
    public function getLessonClass()
    {
        $classificationModel = new ClassificationModel();

        //课程分类信息返回
        $result = $classificationModel->getList(['isDeleted' => 0], 'id,name');

        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 课程搜索
     */
    public function searchLessonResult()
    {
        $params = input('get.');

        $validate = new LessonValidate();
        if (!$validate->scene('searchLessonResult')->check($params)) {
            $this->ajaxReturn($validate->getError());
        }

        $lessonModel = new LessonModel();
        $timeLineModel = new TimeLineModel();
        $payLessonModel = new PayLessonModel();

        //查询条件
        $where = [];
        if (isset($params['type'])) {
            $where['curriculumClassification'] = $params['type'];
        }
        if (isset($params['filter'])) {
            $where['name'] = ['like', '%'. $params['filter'] .'%'];
        }

        switch ($params['flag']) {
            case Constant::FREE_LESSON:
                $result = $lessonModel->getLessonListByWhere($where, 'id,name,author,introduction,curriculumClassification,difficulty,price,totalTime,studyNum,commentNum,comprehensiveScore,url,authorUrl,grabTime,1 as flag');
                break;
            case Constant::PAY_LESSON:
                $result = $payLessonModel->getLessonListByWhere($where, 'id,name,author,introduction,curriculumClassification,difficulty,price,totalTime,studyNum,commentNum,comprehensiveScore,url,authorUrl,grabTime,2 as flag');
                break;
        }

        foreach ($result as &$v) {
            $v['timeLine'] = [
                //七天前
                date('Y-m-d', strtotime('-7 days')) => $this->eachDayStudyNum('-7 days','-8 days',$v['id'],$params['flag']),
                //6天前
                date('Y-m-d', strtotime('-6 days')) => $this->eachDayStudyNum('-6 days','-7 days',$v['id'],$params['flag']),
                //5天前
                date('Y-m-d', strtotime('-5 days')) => $this->eachDayStudyNum('-5 days','-6 days',$v['id'],$params['flag']),
                //4天前
                date('Y-m-d', strtotime('-4 days')) => $this->eachDayStudyNum('-4 days','-5 days',$v['id'],$params['flag']),
                //3天前
                date('Y-m-d', strtotime('-3 days')) => $this->eachDayStudyNum('-3 days','-4 days',$v['id'],$params['flag']),
                //2天前
                date('Y-m-d', strtotime('-2 days')) => $this->eachDayStudyNum('-2 days','-3 days',$v['id'],$params['flag']),
                //1天前
                date('Y-m-d', strtotime('yesterday')) => $this->eachDayStudyNum('yesterday','-2 days',$v['id'],$params['flag']),
            ];


            $v['rate']=$timeLineModel->getInfoByWhere([
                'date' => date('Y-m-d', strtotime('yesterday')),
                'flag' => $params['flag'],
                'id' => $v['id'],
            ], 'rate')['rate'];
        }


        $this->ajaxReturn(1000, 'ok', $result);
    }

    /**
     * 评论搜索
     */
    public function searchCommentResult()
    {
        $params = input('get.');

        $validate = new LessonValidate();
        if (!$validate->scene('searchCommentResult')->check($params)) {
            $this->ajaxReturn($validate->getError());
        }

        $commentModel = new CommentModel();
        $payCommentModel = new PayCommentModel();


        if (!isset($params['filter']) || $params['filter'] == '') {
            $this->ajaxReturn(1001, '关键词不能为空');
        }

        $freeArray = $commentModel->searchCommentResult($params['filter']);
        $payArray = $payCommentModel->searchCommentResult($params['filter']);

        $result = array_merge($freeArray, $payArray);

        $this->ajaxReturn(1000, 'ok', $result);
    }
}

