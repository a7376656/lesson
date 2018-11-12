/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 10.1.33-MariaDB : Database - lesson
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`lesson` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `lesson`;

/*Table structure for table `l_classification` */

DROP TABLE IF EXISTS `l_classification`;

CREATE TABLE `l_classification` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '名称',
  `isDeleted` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否删除：0-否，1-是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='分类表';

/*Table structure for table `l_comment` */

DROP TABLE IF EXISTS `l_comment`;

CREATE TABLE `l_comment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `content` text NOT NULL COMMENT '评论内容',
  `score` int(4) NOT NULL DEFAULT '0' COMMENT '评分（最高10）',
  `lessonId` int(11) NOT NULL DEFAULT '0' COMMENT '对应的课程ID',
  `grabTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '抓取时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;

/*Table structure for table `l_lesson` */

DROP TABLE IF EXISTS `l_lesson`;

CREATE TABLE `l_lesson` (
  `id` int(11) NOT NULL DEFAULT '0' COMMENT '课程ID（就是慕课网上的ID），唯一',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '课程名称',
  `introduction` text NOT NULL COMMENT '课程简介',
  `curriculumClassification` varchar(20) NOT NULL DEFAULT '' COMMENT '课程分类',
  `difficulty` varchar(10) NOT NULL DEFAULT '' COMMENT '课程难度',
  `totalTime` varchar(20) NOT NULL DEFAULT '' COMMENT '总时长',
  `studyNum` int(11) NOT NULL DEFAULT '0' COMMENT '学习人数',
  `commentNum` int(11) NOT NULL DEFAULT '0' COMMENT '评论人数',
  `comprehensiveScore` decimal(10,1) NOT NULL DEFAULT '0.0' COMMENT '综合评分',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '课程网址',
  `grabTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '抓取时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_users` */

DROP TABLE IF EXISTS `l_users`;

CREATE TABLE `l_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID，自增主键',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `createTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
