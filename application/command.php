<?php
return [
    'app\index\command\Test',//测试定时事件
    'app\index\command\Grab',//抓取慕课免费课程及评论
    'app\index\command\GrabPay',//抓取慕课付费课程及评论
    'app\index\command\AddUpStudyNum',//统计昨天一整天免费课程学习人数
    'app\index\command\AddUpPayStudyNum',//统计昨天一整天付费课程学习人数
    'app\index\command\Clear',//清除数据库课程与评论内容
];
