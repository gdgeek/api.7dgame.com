-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： db
-- 生成日期： 2023-03-05 01:25:56
-- 服务器版本： 10.4.27-MariaDB-1:10.4.27+maria~ubu2004
-- PHP 版本： 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `yii2advanced`
--

-- --------------------------------------------------------

--
-- 表的结构 `auth_assignment`
--

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_assignment`
--

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
('@manager/*', '3', 1643286771),
('@restful/*', '3', 1677978410),
('root', '3', 1641975720);

-- --------------------------------------------------------

--
-- 表的结构 `auth_item`
--

CREATE TABLE `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text DEFAULT NULL,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_item`
--

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('@manager/*', 2, NULL, NULL, NULL, 1643275290, 1643275290),
('@restful/*', 2, NULL, NULL, NULL, 1677978400, 1677978400),
('@restful/a1/verse/*', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/a1/verse/view', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/e1/*', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/meta/*', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/meta/create', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/meta/delete', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/meta/index', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/meta/options', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/meta/update', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/meta/view', 2, NULL, NULL, NULL, 1673786181, 1673786181),
('@restful/e1/verse/*', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/e1/verse/create', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/e1/verse/delete', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/e1/verse/options', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/e1/verse/update', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/e1/verse/view', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/p1/*', 2, NULL, NULL, NULL, 1673786548, 1673786548),
('@restful/p1/site/*', 2, NULL, NULL, NULL, 1673786548, 1673786548),
('@restful/p1/site/login', 2, NULL, NULL, NULL, 1673786548, 1673786548),
('@restful/p1/site/logout', 2, NULL, NULL, NULL, 1673786548, 1673786548),
('@restful/p1/site/user', 2, NULL, NULL, NULL, 1673786548, 1673786548),
('@restful/p1/verse-open/*', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/p1/verse-open/index', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/p1/verse-publish/*', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/p1/verse-publish/index', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/p1/verse-share/*', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/p1/verse-share/index', 2, NULL, NULL, NULL, 1673536687, 1673536687),
('@restful/resource/*', 2, NULL, NULL, NULL, 1673516080, 1673516080),
('@restful/resource/create', 2, NULL, NULL, NULL, 1673516080, 1673516080),
('@restful/resource/delete', 2, NULL, NULL, NULL, 1673516080, 1673516080),
('@restful/resource/index', 2, NULL, NULL, NULL, 1673516080, 1673516080),
('@restful/resource/options', 2, NULL, NULL, NULL, 1673516080, 1673516080),
('@restful/resource/update', 2, NULL, NULL, NULL, 1673516080, 1673516080),
('@restful/resource/view', 2, NULL, NULL, NULL, 1673516080, 1673516080),
('@restful/server/*', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/server/bind-email', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/server/logout', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/server/menu', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/server/reset-password', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/server/sts', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/server/token', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/server/user', 2, NULL, NULL, NULL, 1673515953, 1673515953),
('@restful/v1/cyber/*', 2, NULL, NULL, NULL, 1673512039, 1673512039),
('@restful/v1/cyber/create', 2, NULL, NULL, NULL, 1673512039, 1673512039),
('@restful/v1/cyber/delete', 2, NULL, NULL, NULL, 1673512039, 1673512039),
('@restful/v1/cyber/index', 2, NULL, NULL, NULL, 1673512039, 1673512039),
('@restful/v1/cyber/options', 2, NULL, NULL, NULL, 1673512039, 1673512039),
('@restful/v1/cyber/update', 2, NULL, NULL, NULL, 1673512039, 1673512039),
('@restful/v1/cyber/view', 2, NULL, NULL, NULL, 1673512039, 1673512039),
('@restful/v1/knight/*', 2, NULL, NULL, NULL, 1673512242, 1673512242),
('@restful/v1/knight/create', 2, NULL, NULL, NULL, 1673512242, 1673512242),
('@restful/v1/knight/delete', 2, NULL, NULL, NULL, 1673512242, 1673512242),
('@restful/v1/knight/index', 2, NULL, NULL, NULL, 1673512242, 1673512242),
('@restful/v1/knight/options', 2, NULL, NULL, NULL, 1673512242, 1673512242),
('@restful/v1/knight/update', 2, NULL, NULL, NULL, 1673512242, 1673512242),
('@restful/v1/knight/view', 2, NULL, NULL, NULL, 1673512242, 1673512242),
('@restful/v1/like/*', 2, NULL, NULL, NULL, 1673512284, 1673512284),
('@restful/v1/like/create', 2, NULL, NULL, NULL, 1673512284, 1673512284),
('@restful/v1/like/delete', 2, NULL, NULL, NULL, 1673512284, 1673512284),
('@restful/v1/like/index', 2, NULL, NULL, NULL, 1673512284, 1673512284),
('@restful/v1/like/options', 2, NULL, NULL, NULL, 1673512284, 1673512284),
('@restful/v1/like/remove', 2, NULL, NULL, NULL, 1673526070, 1673526070),
('@restful/v1/like/update', 2, NULL, NULL, NULL, 1673512284, 1673512284),
('@restful/v1/like/view', 2, NULL, NULL, NULL, 1673512284, 1673512284),
('@restful/v1/message-tags/*', 2, NULL, NULL, NULL, 1673519141, 1673519141),
('@restful/v1/message-tags/create', 2, NULL, NULL, NULL, 1673519141, 1673519141),
('@restful/v1/message-tags/delete', 2, NULL, NULL, NULL, 1673519141, 1673519141),
('@restful/v1/message-tags/index', 2, NULL, NULL, NULL, 1673519141, 1673519141),
('@restful/v1/message-tags/options', 2, NULL, NULL, NULL, 1673519141, 1673519141),
('@restful/v1/message-tags/update', 2, NULL, NULL, NULL, 1673519141, 1673519141),
('@restful/v1/message-tags/view', 2, NULL, NULL, NULL, 1673519141, 1673519141),
('@restful/v1/message/*', 2, NULL, NULL, NULL, 1673519092, 1673519092),
('@restful/v1/message/create', 2, NULL, NULL, NULL, 1673519092, 1673519092),
('@restful/v1/message/delete', 2, NULL, NULL, NULL, 1673519092, 1673519092),
('@restful/v1/message/index', 2, NULL, NULL, NULL, 1673519092, 1673519092),
('@restful/v1/message/options', 2, NULL, NULL, NULL, 1673519092, 1673519092),
('@restful/v1/message/update', 2, NULL, NULL, NULL, 1673519092, 1673519092),
('@restful/v1/message/view', 2, NULL, NULL, NULL, 1673519092, 1673519092),
('@restful/v1/meta-event/*', 2, NULL, NULL, NULL, 1673597815, 1673597815),
('@restful/v1/meta-event/create', 2, NULL, NULL, NULL, 1673597815, 1673597815),
('@restful/v1/meta-event/delete', 2, NULL, NULL, NULL, 1673597815, 1673597815),
('@restful/v1/meta-event/index', 2, NULL, NULL, NULL, 1673597815, 1673597815),
('@restful/v1/meta-event/options', 2, NULL, NULL, NULL, 1673597815, 1673597815),
('@restful/v1/meta-event/update', 2, NULL, NULL, NULL, 1673597815, 1673597815),
('@restful/v1/meta-event/view', 2, NULL, NULL, NULL, 1673597815, 1673597815),
('@restful/v1/meta-knight/*', 2, NULL, NULL, NULL, 1673600062, 1673600062),
('@restful/v1/meta-knight/create', 2, NULL, NULL, NULL, 1673600062, 1673600062),
('@restful/v1/meta-knight/delete', 2, NULL, NULL, NULL, 1673600062, 1673600062),
('@restful/v1/meta-knight/index', 2, NULL, NULL, NULL, 1673600062, 1673600062),
('@restful/v1/meta-knight/options', 2, NULL, NULL, NULL, 1673600062, 1673600062),
('@restful/v1/meta-knight/update', 2, NULL, NULL, NULL, 1673600062, 1673600062),
('@restful/v1/meta-knight/view', 2, NULL, NULL, NULL, 1673600062, 1673600062),
('@restful/v1/meta-resource/*', 2, NULL, NULL, NULL, 1677122132, 1677122132),
('@restful/v1/meta-resource/create', 2, NULL, NULL, NULL, 1677122132, 1677122132),
('@restful/v1/meta-resource/delete', 2, NULL, NULL, NULL, 1677122132, 1677122132),
('@restful/v1/meta-resource/options', 2, NULL, NULL, NULL, 1677122132, 1677122132),
('@restful/v1/meta-resource/resources', 2, NULL, NULL, NULL, 1677122132, 1677122132),
('@restful/v1/meta-resource/update', 2, NULL, NULL, NULL, 1677122132, 1677122132),
('@restful/v1/meta-resource/view', 2, NULL, NULL, NULL, 1677122132, 1677122132),
('@restful/v1/meta/*', 2, NULL, NULL, NULL, 1673600056, 1673600056),
('@restful/v1/meta/create', 2, NULL, NULL, NULL, 1673600056, 1673600056),
('@restful/v1/meta/delete', 2, NULL, NULL, NULL, 1673600056, 1673600056),
('@restful/v1/meta/index', 2, NULL, NULL, NULL, 1673600056, 1673600056),
('@restful/v1/meta/options', 2, NULL, NULL, NULL, 1673600056, 1673600056),
('@restful/v1/meta/update', 2, NULL, NULL, NULL, 1673600056, 1673600056),
('@restful/v1/meta/view', 2, NULL, NULL, NULL, 1673600056, 1673600056),
('@restful/v1/person/*', 2, NULL, NULL, NULL, 1673979647, 1673979647),
('@restful/v1/person/index', 2, NULL, NULL, NULL, 1673979647, 1673979647),
('@restful/v1/reply/*', 2, NULL, NULL, NULL, 1673519204, 1673519204),
('@restful/v1/reply/create', 2, NULL, NULL, NULL, 1673519204, 1673519204),
('@restful/v1/reply/delete', 2, NULL, NULL, NULL, 1673519204, 1673519204),
('@restful/v1/reply/index', 2, NULL, NULL, NULL, 1673519204, 1673519204),
('@restful/v1/reply/options', 2, NULL, NULL, NULL, 1673519204, 1673519204),
('@restful/v1/reply/update', 2, NULL, NULL, NULL, 1673519204, 1673519204),
('@restful/v1/reply/view', 2, NULL, NULL, NULL, 1673519204, 1673519204),
('@restful/v1/space/*', 2, NULL, NULL, NULL, 1673624243, 1673624243),
('@restful/v1/space/create', 2, NULL, NULL, NULL, 1673624243, 1673624243),
('@restful/v1/space/delete', 2, NULL, NULL, NULL, 1673624243, 1673624243),
('@restful/v1/space/index', 2, NULL, NULL, NULL, 1673624243, 1673624243),
('@restful/v1/space/options', 2, NULL, NULL, NULL, 1673624243, 1673624243),
('@restful/v1/space/update', 2, NULL, NULL, NULL, 1673624243, 1673624243),
('@restful/v1/space/view', 2, NULL, NULL, NULL, 1673624243, 1673624243),
('@restful/v1/tags/*', 2, NULL, NULL, NULL, 1673518618, 1673518618),
('@restful/v1/tags/index', 2, NULL, NULL, NULL, 1673518618, 1673518618),
('@restful/v1/user/*', 2, NULL, NULL, NULL, 1673513704, 1673513704),
('@restful/v1/user/creation', 2, NULL, NULL, NULL, 1673513704, 1673513704),
('@restful/v1/user/get-data', 2, NULL, NULL, NULL, 1673513704, 1673513704),
('@restful/v1/user/set-data', 2, NULL, NULL, NULL, 1673513704, 1673513704),
('@restful/v1/verse-event/*', 2, NULL, NULL, NULL, 1673536698, 1673536698),
('@restful/v1/verse-event/create', 2, NULL, NULL, NULL, 1673536698, 1673536698),
('@restful/v1/verse-event/delete', 2, NULL, NULL, NULL, 1673536698, 1673536698),
('@restful/v1/verse-event/index', 2, NULL, NULL, NULL, 1673536698, 1673536698),
('@restful/v1/verse-event/options', 2, NULL, NULL, NULL, 1673536698, 1673536698),
('@restful/v1/verse-event/update', 2, NULL, NULL, NULL, 1673536698, 1673536698),
('@restful/v1/verse-event/view', 2, NULL, NULL, NULL, 1673536698, 1673536698),
('@restful/v1/verse-knight/*', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-knight/create', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-knight/delete', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-knight/knights', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-knight/options', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-knight/update', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-knight/view', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-open/*', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-open/create', 2, NULL, NULL, NULL, 1673584155, 1673584155),
('@restful/v1/verse-open/delete', 2, NULL, NULL, NULL, 1673584155, 1673584155),
('@restful/v1/verse-open/index', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-open/options', 2, NULL, NULL, NULL, 1673584155, 1673584155),
('@restful/v1/verse-open/update', 2, NULL, NULL, NULL, 1673584155, 1673584155),
('@restful/v1/verse-open/verses', 2, NULL, NULL, NULL, 1677424736, 1677424736),
('@restful/v1/verse-open/view', 2, NULL, NULL, NULL, 1673584155, 1673584155),
('@restful/v1/verse-share/*', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-share/create', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-share/delete', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-share/index', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-share/list', 2, NULL, NULL, NULL, 1673594049, 1673594049),
('@restful/v1/verse-share/options', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-share/remove', 2, NULL, NULL, NULL, 1673595208, 1673595208),
('@restful/v1/verse-share/update', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-share/verses', 2, NULL, NULL, NULL, 1677424736, 1677424736),
('@restful/v1/verse-share/view', 2, NULL, NULL, NULL, 1673536694, 1673536694),
('@restful/v1/verse-space/*', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-space/create', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-space/delete', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-space/options', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-space/spaces', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-space/update', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse-space/view', 2, NULL, NULL, NULL, 1677122142, 1677122142),
('@restful/v1/verse/*', 2, NULL, NULL, NULL, 1673535356, 1673535356),
('@restful/v1/verse/create', 2, NULL, NULL, NULL, 1673535356, 1673535356),
('@restful/v1/verse/delete', 2, NULL, NULL, NULL, 1673535356, 1673535356),
('@restful/v1/verse/index', 2, NULL, NULL, NULL, 1673535356, 1673535356),
('@restful/v1/verse/options', 2, NULL, NULL, NULL, 1673535356, 1673535356),
('@restful/v1/verse/update', 2, NULL, NULL, NULL, 1673535356, 1673535356),
('@restful/v1/verse/view', 2, NULL, NULL, NULL, 1673535356, 1673535356),
('/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin-blockly/*', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/admin-blockly/create', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/admin-blockly/delete', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/admin-blockly/index', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/admin-blockly/update', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/admin-blockly/view', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/admin/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/assignment/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/assignment/assign', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/assignment/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/assignment/revoke', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/assignment/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/default/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/default/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/menu/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/menu/create', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/menu/delete', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/menu/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/menu/update', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/menu/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/assign', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/create', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/delete', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/get-users', 2, NULL, NULL, NULL, 1621827728, 1621827728),
('/admin/permission/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/remove', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/update', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/permission/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/assign', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/create', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/delete', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/get-users', 2, NULL, NULL, NULL, 1621827728, 1621827728),
('/admin/role/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/remove', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/update', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/role/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/route/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/route/assign', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/route/create', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/route/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/route/refresh', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/route/remove', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/rule/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/rule/create', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/rule/delete', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/rule/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/rule/update', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/rule/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/activate', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/change-password', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/delete', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/login', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/logout', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/request-password-reset', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/reset-password', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/signup', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/admin/user/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/blockly/*', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/blockly/advanced-save', 2, NULL, NULL, NULL, 1580148460, 1580148460),
('/blockly/code-js', 2, NULL, NULL, NULL, 1573440306, 1573440306),
('/blockly/index', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/blockly/index-advanced', 2, NULL, NULL, NULL, 1580148460, 1580148460),
('/blockly/save', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/blog/*', 2, NULL, NULL, NULL, 1574237232, 1574237232),
('/blog/index', 2, NULL, NULL, NULL, 1574237232, 1574237232),
('/content-type/*', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content-type/create', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content-type/delete', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content-type/index', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content-type/update', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content-type/view', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content/*', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content/create', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content/delete', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content/index', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content/update', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/content/view', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/cos/*', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/cos/auth', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/cos/delete', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/cos/index', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/cos/sts', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/cos/test', 2, NULL, NULL, NULL, 1573031003, 1573031003),
('/debug/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/create', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/debug/default/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/default/db-explain', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/default/download-mail', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/default/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/default/toolbar', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/default/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/delete', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/debug/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/debug/update', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/debug/user/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/user/reset-identity', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/user/set-identity', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/debug/view', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/document/*', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/document/index', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/document/school', 2, NULL, NULL, NULL, 1621827721, 1621827721),
('/editor-reader/*', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/editor-reader/index', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/editor-reader/test', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/editor/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/editor/component-js', 2, NULL, NULL, NULL, 1573440306, 1573440306),
('/editor/database', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/editor/idx', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/editor/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/editor/internal', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/editor/removed-node', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/editor/submit', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/editor/update', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/entry/*', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/error', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/home', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/index', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/login', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/logout', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/request-password-reset', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/resend-verification-email', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/reset-password', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/signup', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/test', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/entry/verify-email', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/feedback-describe/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-describe/create', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-describe/delete', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-describe/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-describe/update', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-describe/view', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-state/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-state/create', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-state/delete', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-state/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-state/update', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback-state/view', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/create', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/delete', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/list', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/submit', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/update', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/feedback/view', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/file/*', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/file/create', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/file/upload', 2, NULL, NULL, NULL, 1621827718, 1621827718),
('/gii/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/gii/default/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/gii/default/action', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/gii/default/diff', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/gii/default/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/gii/default/preview', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/gii/default/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/home/*', 2, NULL, NULL, NULL, 1558630057, 1558630057),
('/home/add-invitation', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/home/index', 2, NULL, NULL, NULL, 1558630057, 1558630057),
('/home/invitation', 2, NULL, NULL, NULL, 1558630057, 1558630057),
('/ide/*', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/ide/index', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/ide/submit', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/info/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/create', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/create-invitation', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/delete', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/info', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/no-invitation', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/update', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/info/view', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/input/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/input/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/invitation/*', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/invitation/create', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/invitation/delete', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/invitation/index', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/invitation/update', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/invitation/view', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/list/*', 2, NULL, NULL, NULL, 1621827728, 1621827728),
('/list/index', 2, NULL, NULL, NULL, 1621827718, 1621827718),
('/list/qrcode', 2, NULL, NULL, NULL, 1621827718, 1621827718),
('/lua/*', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/lua/default/*', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/lua/default/index', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/main/*', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/main/blog', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/main/index', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/main/projects', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/main/qrcode', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/main/site', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/main/video', 2, NULL, NULL, NULL, 1574237232, 1574237232),
('/maker/*', 2, NULL, NULL, NULL, 1580148460, 1580148460),
('/maker/index', 2, NULL, NULL, NULL, 1580148460, 1580148460),
('/material-editor/*', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material-editor/index', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material-editor/test', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material-editor/update', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material-editor/upload', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material/*', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/material/create', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material/delete', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material/index', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/material/test', 2, NULL, NULL, NULL, 1571882978, 1571882978),
('/material/update', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/material/upload', 2, NULL, NULL, NULL, 1571739430, 1571739430),
('/material/view', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/method/*', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/method/create', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/method/delete', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/method/index', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/method/update', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/method/view', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/picture/*', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/picture/change-data', 2, NULL, NULL, NULL, 1578043009, 1578043009),
('/picture/create', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/picture/delete', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/picture/index', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/picture/prepare', 2, NULL, NULL, NULL, 1621827714, 1621827714),
('/picture/update', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/picture/upload', 2, NULL, NULL, NULL, 1578034710, 1578034710),
('/picture/view', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/pictures/*', 2, NULL, NULL, NULL, 1572851302, 1572851302),
('/pictures/default/*', 2, NULL, NULL, NULL, 1573031003, 1573031003),
('/pictures/default/index', 2, NULL, NULL, NULL, 1573031003, 1573031003),
('/polygen/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygen/compressed-upload', 2, NULL, NULL, NULL, 1577938891, 1577938891),
('/polygen/create', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygen/delete', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygen/gltf-upload', 2, NULL, NULL, NULL, 1612111264, 1612111264),
('/polygen/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygen/list', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/polygen/prepare', 2, NULL, NULL, NULL, 1621827714, 1621827714),
('/polygen/sharing', 2, NULL, NULL, NULL, 1564713160, 1564713160),
('/polygen/update', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygen/upload', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygen/view', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygens/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygens/default/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygens/default/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/polygon/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/polygon/create', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/polygon/delete', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/polygon/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/polygon/update', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/polygon/upload', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/polygon/view', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/project/*', 2, NULL, NULL, NULL, 1557147473, 1557147473),
('/project/add-project', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/project/change', 2, NULL, NULL, NULL, 1557147473, 1557147473),
('/project/change-sharing', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/project/change-title', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/project/copy', 2, NULL, NULL, NULL, 1621827714, 1621827714),
('/project/create', 2, NULL, NULL, NULL, 1557147473, 1557147473),
('/project/delete', 2, NULL, NULL, NULL, 1557147473, 1557147473),
('/project/index', 2, NULL, NULL, NULL, 1557147473, 1557147473),
('/project/qrcode', 2, NULL, NULL, NULL, 1568996487, 1568996487),
('/project/update', 2, NULL, NULL, NULL, 1557147473, 1557147473),
('/project/view', 2, NULL, NULL, NULL, 1557147473, 1557147473),
('/project2/*', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/project2/create', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/project2/delete', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/project2/index', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/project2/update', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/project2/view', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/reader/*', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/reader/default/*', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/reader/default/index', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/reporter/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/reporter/default/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/reporter/default/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/scripts/*', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/scripts/create', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/scripts/delete', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/scripts/index', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/scripts/update', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/scripts/view', 2, NULL, NULL, NULL, 1559195859, 1559195859),
('/setup/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/setup/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/site/*', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/site/error', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/site/index', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/site/language', 2, NULL, NULL, NULL, 1573440306, 1573440306),
('/site/login', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/site/logout', 2, NULL, NULL, NULL, 1557226590, 1557226590),
('/site/request-password-reset', 2, NULL, NULL, NULL, 1558595617, 1558595617),
('/site/resend-verification-email', 2, NULL, NULL, NULL, 1558595617, 1558595617),
('/site/reset-password', 2, NULL, NULL, NULL, 1558595617, 1558595617),
('/site/signup', 2, NULL, NULL, NULL, 1558595617, 1558595617),
('/site/verify-email', 2, NULL, NULL, NULL, 1558595617, 1558595617),
('/storage/*', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/storage/index', 2, NULL, NULL, NULL, 1567732541, 1567732541),
('/template/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/template/default/*', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/template/default/index', 2, NULL, NULL, NULL, 1561604203, 1561604203),
('/video/*', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/video/create', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/video/delete', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/video/index', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/video/prepare', 2, NULL, NULL, NULL, 1621827714, 1621827714),
('/video/update', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/video/upload', 2, NULL, NULL, NULL, 1578043009, 1578043009),
('/video/view', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/videos/*', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/videos/default/*', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/videos/default/index', 2, NULL, NULL, NULL, 1572580180, 1572580180),
('/wechat/*', 2, NULL, NULL, NULL, 1621827714, 1621827714),
('/wechat/index', 2, NULL, NULL, NULL, 1621827714, 1621827714),
('admin', 1, '管理员', NULL, NULL, 1552152101, 1665713267),
('developer', 1, '开发者，破冰用户', NULL, NULL, 1559652693, 1643286294),
('manager', 1, '网站管理者', NULL, NULL, 1643284050, 1643286320),
('root', 1, '超级管理员', NULL, NULL, 1557226615, 1673979671),
('tags增删', 2, '我的massage的tags', 'tags的massage是我的', NULL, 1673534105, 1674500521),
('user', 1, '基本权限,  模型管理，工程管理', NULL, NULL, 1552196894, 1673513866),
('临时资源访问权限', 2, NULL, NULL, NULL, 1673682657, 1674500578),
('共享宇宙', 2, '别人共享给我的宇宙', '共享的Verse', NULL, 1673586122, 1674500655),
('回复操作', 2, '删除自己发表的回复', '私有回复', NULL, 1673529335, 1674500747),
('基础操作', 2, '任何登陆网站的人都可以进行的操作，包括用户资料等', NULL, NULL, 1673513845, 1677424747),
('开放宇宙', 2, '被设置为开放的宇宙', '开放的Verse', NULL, 1673584125, 1674501747),
('绑定权限', 2, NULL, '绑定规则', NULL, 1677122179, 1677239179),
('自有宇宙', 2, '我自己的宇宙进行操作', '私有宇宙', NULL, 1673571576, 1677572697),
('自有消息', 2, '修改和删除我的消息', '私有Message', NULL, 1673533773, 1674501344),
('自有空间', 2, '管理自己的空间', '私有空间', NULL, 1673627744, 1674501258),
('资源操作', 2, '对自己的资源进行操作，包括查看，删除，修改', '私有资源', NULL, 1673516681, 1674501215);

-- --------------------------------------------------------

--
-- 表的结构 `auth_item_child`
--

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_item_child`
--

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('root', '/*'),
('tags增删', '@restful/v1/message-tags/create'),
('tags增删', '@restful/v1/message-tags/delete'),
('user', '基础操作'),
('临时资源访问权限', '@restful/resource/view'),
('临时资源访问权限', '@restful/v1/space/view'),
('共享宇宙', '@restful/v1/verse-share/delete'),
('回复操作', '@restful/v1/reply/delete'),
('基础操作', '@restful/p1/site/logout'),
('基础操作', '@restful/p1/site/user'),
('基础操作', '@restful/p1/verse-publish/index'),
('基础操作', '@restful/p1/verse-share/index'),
('基础操作', '@restful/resource/create'),
('基础操作', '@restful/resource/index'),
('基础操作', '@restful/server/logout'),
('基础操作', '@restful/server/token'),
('基础操作', '@restful/v1/knight/index'),
('基础操作', '@restful/v1/knight/view'),
('基础操作', '@restful/v1/like/create'),
('基础操作', '@restful/v1/like/remove'),
('基础操作', '@restful/v1/message-tags/index'),
('基础操作', '@restful/v1/message/create'),
('基础操作', '@restful/v1/message/index'),
('基础操作', '@restful/v1/message/view'),
('基础操作', '@restful/v1/reply/create'),
('基础操作', '@restful/v1/reply/index'),
('基础操作', '@restful/v1/space/create'),
('基础操作', '@restful/v1/space/index'),
('基础操作', '@restful/v1/tags/index'),
('基础操作', '@restful/v1/user/creation'),
('基础操作', '@restful/v1/user/get-data'),
('基础操作', '@restful/v1/user/set-data'),
('基础操作', '@restful/v1/verse-open/verses'),
('基础操作', '@restful/v1/verse-share/verses'),
('基础操作', '@restful/v1/verse/create'),
('基础操作', '@restful/v1/verse/index'),
('基础操作', 'tags增删'),
('基础操作', '临时资源访问权限'),
('基础操作', '共享宇宙'),
('基础操作', '回复操作'),
('基础操作', '开放宇宙'),
('基础操作', '绑定权限'),
('基础操作', '自有宇宙'),
('基础操作', '自有消息'),
('基础操作', '自有空间'),
('基础操作', '资源操作'),
('开放宇宙', '@restful/v1/verse/view'),
('绑定权限', '@restful/v1/meta-resource/create'),
('绑定权限', '@restful/v1/meta-resource/delete'),
('绑定权限', '@restful/v1/meta-resource/resources'),
('绑定权限', '@restful/v1/meta-resource/update'),
('绑定权限', '@restful/v1/verse-knight/create'),
('绑定权限', '@restful/v1/verse-knight/delete'),
('绑定权限', '@restful/v1/verse-knight/knights'),
('绑定权限', '@restful/v1/verse-knight/update'),
('绑定权限', '@restful/v1/verse-space/create'),
('绑定权限', '@restful/v1/verse-space/delete'),
('绑定权限', '@restful/v1/verse-space/spaces'),
('绑定权限', '@restful/v1/verse-space/update'),
('自有宇宙', '@restful/e1/meta/view'),
('自有宇宙', '@restful/e1/verse/view'),
('自有宇宙', '@restful/v1/cyber/create'),
('自有宇宙', '@restful/v1/cyber/update'),
('自有宇宙', '@restful/v1/meta-event/create'),
('自有宇宙', '@restful/v1/meta-event/index'),
('自有宇宙', '@restful/v1/meta-event/update'),
('自有宇宙', '@restful/v1/meta-event/view'),
('自有宇宙', '@restful/v1/meta-knight/create'),
('自有宇宙', '@restful/v1/meta-knight/delete'),
('自有宇宙', '@restful/v1/meta-knight/update'),
('自有宇宙', '@restful/v1/meta-knight/view'),
('自有宇宙', '@restful/v1/meta/create'),
('自有宇宙', '@restful/v1/meta/delete'),
('自有宇宙', '@restful/v1/meta/update'),
('自有宇宙', '@restful/v1/meta/view'),
('自有宇宙', '@restful/v1/verse-event/create'),
('自有宇宙', '@restful/v1/verse-event/index'),
('自有宇宙', '@restful/v1/verse-event/update'),
('自有宇宙', '@restful/v1/verse-share/create'),
('自有宇宙', '@restful/v1/verse-share/index'),
('自有宇宙', '@restful/v1/verse-share/list'),
('自有宇宙', '@restful/v1/verse-share/update'),
('自有宇宙', '@restful/v1/verse-share/verses'),
('自有宇宙', '@restful/v1/verse-share/view'),
('自有宇宙', '@restful/v1/verse/delete'),
('自有宇宙', '@restful/v1/verse/update'),
('自有宇宙', '@restful/v1/verse/view'),
('自有消息', '@restful/v1/message/delete'),
('自有消息', '@restful/v1/message/update'),
('自有空间', '@restful/v1/space/delete'),
('自有空间', '@restful/v1/space/update'),
('自有空间', '@restful/v1/space/view'),
('资源操作', '@restful/resource/delete'),
('资源操作', '@restful/resource/update'),
('资源操作', '@restful/resource/view');

-- --------------------------------------------------------

--
-- 表的结构 `auth_rule`
--

CREATE TABLE `auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_rule`
--

INSERT INTO `auth_rule` (`name`, `data`, `created_at`, `updated_at`) VALUES
('tags的massage是我的', 0x4f3a34313a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c4d6573736167655461677352756c65223a333a7b733a343a226e616d65223b733a32333a2274616773e79a846d617373616765e698afe68891e79a84223b733a393a22637265617465644174223b693a313634363738333530333b733a393a22757064617465644174223b693a313634363738333530333b7d, 1646783503, 1646783503),
('共享的Verse', 0x4f3a34303a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c5665727365536861726552756c65223a333a7b733a343a226e616d65223b733a31343a22e585b1e4baabe79a845665727365223b733a393a22637265617465644174223b693a313637333538363039383b733a393a22757064617465644174223b693a313637333538363039383b7d, 1673586098, 1673586098),
('开放的Verse', 0x4f3a33393a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c56657273654f70656e52756c65223a333a7b733a343a226e616d65223b733a31343a22e5bc80e694bee79a845665727365223b733a393a22637265617465644174223b693a313637333538343038363b733a393a22757064617465644174223b693a313637333538343038363b7d, 1673584086, 1673584086),
('私有like', 0x4f3a33343a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c4c696b6552756c65223a333a7b733a343a226e616d65223b733a31303a22e7a781e69c896c696b65223b733a393a22637265617465644174223b693a313634373734303836323b733a393a22757064617465644174223b693a313637343530303337313b7d, 1647740862, 1674500371),
('私有Message', 0x4f3a33373a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c4d65737361676552756c65223a333a7b733a343a226e616d65223b733a31333a22e7a781e69c894d657373616765223b733a393a22637265617465644174223b693a313634343633303330373b733a393a22757064617465644174223b693a313637333533333933383b7d, 1644630307, 1673533938),
('私有元', 0x4f3a33343a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c4d65746152756c65223a333a7b733a343a226e616d65223b733a393a22e7a781e69c89e58583223b733a393a22637265617465644174223b693a313634343633303234373b733a393a22757064617465644174223b693a313634343633303234373b7d, 1644630247, 1644630247),
('私有回复', 0x4f3a33353a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c5265706c7952756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e59b9ee5a48d223b733a393a22637265617465644174223b693a313634343633303431353b733a393a22757064617465644174223b693a313634343633303431353b7d, 1644630415, 1644630415),
('私有宇宙', 0x4f3a33353a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c566572736552756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e5ae87e5ae99223b733a393a22637265617465644174223b693a313634343633303230373b733a393a22757064617465644174223b693a313634343633323538323b7d, 1644630207, 1644632582),
('私有空间', 0x4f3a33353a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c537061636552756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e7a9bae997b4223b733a393a22637265617465644174223b693a313637333632373732313b733a393a22757064617465644174223b693a313637333632373732313b7d, 1673627721, 1673627721),
('私有资源', 0x4f3a33303a22636f6d6d6f6e5c636f6d706f6e656e74735c5265736f7572636552756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e8b584e6ba90223b733a393a22637265617465644174223b693a313634343531333830333b733a393a22757064617465644174223b693a313637333531363633393b7d, 1644513803, 1673516639),
('私有赛博', 0x4f3a34303a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c5665727365437962657252756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e8b59be58d9a223b733a393a22637265617465644174223b693a313634343633303238313b733a393a22757064617465644174223b693a313634343633303238313b7d, 1644630281, 1644630281),
('绑定规则', 0x4f3a33373a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c42696e64696e6752756c65223a333a7b733a343a226e616d65223b733a31323a22e7bb91e5ae9ae8a784e58899223b733a393a22637265617465644174223b693a313637373132323136333b733a393a22757064617465644174223b693a313637373132323136333b7d, 1677122163, 1677122163);

-- --------------------------------------------------------

--
-- 表的结构 `cyber`
--

CREATE TABLE `cyber` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `meta_id` int(11) DEFAULT NULL,
  `create_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `script` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `cyber_script`
--

CREATE TABLE `cyber_script` (
  `id` int(11) NOT NULL,
  `cyber_id` int(11) NOT NULL,
  `language` varchar(255) NOT NULL,
  `script` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `reporter` int(11) NOT NULL,
  `repairer` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `describe_id` int(11) NOT NULL,
  `bug` text NOT NULL,
  `debug` text DEFAULT NULL,
  `infomation` text DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT current_timestamp(),
  `fixed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback_describe`
--

CREATE TABLE `feedback_describe` (
  `id` int(11) NOT NULL,
  `describe` varchar(128) DEFAULT NULL,
  `order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback_state`
--

CREATE TABLE `feedback_state` (
  `id` int(11) NOT NULL,
  `state` varchar(128) DEFAULT NULL,
  `order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `file`
--

CREATE TABLE `file` (
  `id` int(11) NOT NULL,
  `md5` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `filename` varchar(255) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'nokey'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `file_store`
--

CREATE TABLE `file_store` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `info`
--

CREATE TABLE `info` (
  `id` int(11) NOT NULL,
  `name` varchar(10) NOT NULL,
  `company` varchar(50) NOT NULL,
  `tel` varchar(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `invitation` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `info`
--

INSERT INTO `info` (`id`, `name`, `company`, `tel`, `reason`, `invitation`) VALUES
(1, '邸锐', '上海游七网络科技有限公司', '15000159790', '希望能看到你的', '3_1561309277'),
(2, 'dirui', 'dirui', '15601920021', 'tesetesresr', '3_1561879917'),
(3, '王铭杰', '智塾', '13788910059', '试一下，要是好用，推荐上汽使用', '3_1561882384'),
(4, '刘茹', '上海火魂网络科技有限公司', '15000409274', 'unity前端，对这个比较感兴趣，想要多了解一下', '3_1561883431'),
(5, '张燕龙', '中国电科38所', '15771922797', '目前也在做这一块的开发，想看看其他行友做的咋样！就酱紫。', '3_1561883926'),
(6, '黄腾磊', '广州四三九九信息科技网络有限公司', '15876529110', '学习新技术', '3_1561884039'),
(7, '章怀宙', '爱奇艺', '13917833626', '申请试一下看看', '3_1561885795'),
(8, '王', '北京华清易通科技有限公司', '13621189852', '1.网友，关注很久了，测试一下看看效果；\r\n2. 我有教育渠道，平台好用可以推广', '3_1561904638'),
(9, '军营连排班', '易鑫', '18511045041', 'test', '3_1561909164'),
(10, '钟彬', '上海蕴石通信技术有限公司', '15921550326', '看好MR 想了解相关技术', '3_1561934654'),
(11, '子龙', '无', '17621083949', '探索mr', '3_1561948988'),
(12, '潘辉的泰迪世界', '环球网', '15652907029', '关注您得产品很久了，最近研究融媒体技术', '3_1561991830'),
(13, '付军豪', '上海嘉语文化传播有限公司', '13883251641', '很喜欢在微博看到的平台概念，希望可以永改平台来位我们的客户创做ar内容', '3_1562290606'),
(14, '李晗', '南京航空航天大学机电学院', '18640434717', '高校项目相关', '3_1563268621'),
(15, '韩涛', '瑞晟医疗科技有限公司', '13657630028', '测试医学三维重建在混合现实中的应用效果', '3_1563785512'),
(16, '郑利建', '中国移动通信集团浙江有限公司', '18368860711', '对混合现实感兴趣，认为在不久的将来会对很多领域产生重大影响，无奈无法说服领导，所以决定自己做个样板出来。本人曾经是某小公司的全栈猿，主攻微软路线，但荒废好多年，最近刚捡起来，搭好了hololens2仿真环境，急需找个组织一同前行。另:如说服领导，可谈商务合作！', '3_1564543428'),
(17, '姚立成', '苏州市至诚软件业服务有限公司', '13951122633', '有混合现实应用需求', '3_1564711148'),
(18, 'Jason', '启页科技', '17600243103', 'hololens爱好者', '3_1564713233'),
(19, '冯广', '长城新媒体集团', '17717190633', 'MR开发人员，了解使用先进技术平台。', '3_1564715082'),
(20, '姚文康', '上海不加班', '18848957787', '姚文康', '3_1564976125'),
(21, 'jack', 'uniteddata', '13760895342', '看到你的视频，学习hololens编程', '3_1565066783'),
(22, '王毅', '炽橙科技', '13824344953', '公司合作', '3_1565074977'),
(23, '费涛', 'siemens', '18818264769', '感兴趣', '3_1565138959'),
(24, '金书书', '万达', '18357374409', '好奇\r\nps\r\n~微信号也不一定只是手机啦\r\n', '3_1565252853'),
(25, '马乾坤', '淮北爱思创智能科技有限公司', '15064031230', '我们目前在做研学工作，目前研学课程正在开发中，得知有这种新技术，所以我们想先测试一下，如果可以，我们再找您开发。', '3_1565578210'),
(26, '阿楚', '北京盛诺一家医院管理咨询有限公司', '15010866391', '了解混合显示在医疗领域的应用', '3_1566546512'),
(27, '刘先生', '哈尔滨工业大学', '18576741935', '想试一下平台功能是否满足我们开发需求', '3_1566570342'),
(28, 'wgs', '个人', '13801051672', '有兴趣', '3_1566574606'),
(29, '夏天', '国外留学，学习的专业是电脑编程，gaming', '13166166336', '很早以前就关注混合现实的东西，最开始的是Google眼镜，因为太贵买不起，就一直去了解关于这些的东西，也选择了关于编程的学校和课程，并且想加入，所以我想为了虚拟现实出点力，我的微信号Bradexia-hahaha12138', '3_1566593128'),
(30, '宋皓', '山东天利和软件', '13173263305', '学习', '3_1566652516'),
(31, '侯青元', '亿浪', '13564314303', '对一种新事物和产品的期待', '3_1566826615'),
(32, '张武豪', 'Aubo智能', '17620752663', '纯属个人爱好', '3_1566873426'),
(33, '郝海淘', '北京华源格林科技有限公司', '17736122927', 'hololens开发', '3_1566896057'),
(34, '郭清顺', '汕头市中心医院', '15521010710', '看看是否可应用于医学领域的地方', '3_1566982812'),
(35, '赵杰', '天津市滨海新区汉沽第九中学', '18622021278', '信息化教学的课件制作', '3_1567058607'),
(36, '王岭峰', '北京华源格林科技有限公司', '15188550106', '由于隐私问题，我的手机号搜不到我的微信，请理解，谢谢。\r\n本人是Hololens开发者，微博看到贵平台加上同事推荐，特来申请测试。', '3_1567060686'),
(37, '姜凯', '光素科技', '18770818053', '觉得这个项目有点东西，学习借鉴。互相交流', '3_1567130519'),
(38, 'Peter Liu', 'Continental', '13820859135', '传统制造企业，现致力于公司的数字化转型，其中一个重要部分就是智能可穿戴设备，之中眼镜又是一个重要突破点，但发现想开发一个应用颇具难度，所以也在寻找有没有简单易上手的开发平台得以应用', '3_1567132500'),
(39, '李坚弘', '贝壳找房', '15201398918', '开发HoloLens', '3_1567137405'),
(40, '张镇', '方妙科技', '13913831282', '制作MR', '3_1567152297'),
(41, 'AlexWang', 'Xtarlab', '13052272707', '正在寻找能快速实现MR原型的平台，来申请尝试下', '3_1567164164'),
(42, '贾斯霏', 'VRgate', '18513871207', '我的团队一直和MR有着密切的关系但是很多新来的不会用Unreal，我们想使用MRPp这个平台来训练新员工', '3_1567176626'),
(43, '谢荣福', '学校', '17669129772', '学习一下', '3_1567205261'),
(44, '杨炳宏', '武汉易锐科技', '13871026896', '希望能用于公司项目', '3_1567216020'),
(45, '宋俊宇', '中数通信息有限公司', '18312102577', '新奇，想变程序猿', '3_1567221205'),
(46, '李世强', '无', '15914985375', '想体验下你的平台。', '3_1567235502'),
(47, '朱天宇', '无', '15550343702', '微博，朱天宇_zty，我来了', '3_1567294771'),
(48, '夏力维', 'HOTPOOR', '15201950688', '探寻建筑设计行业客户的可能性', '3_1567305612'),
(49, '陶健', '深圳科澳汽车科技有限公司', '13392888977', '学习', '3_1567385361'),
(50, '姬龙兴', '西安绿创', '18049288522', '想要了解MR，希望有机会能加入开发', '3_1567489426'),
(51, '梁铭成', '广州幻境科技有限公司', '13763390893', '测试及试用平台，开发MR 应用', '3_1567492646'),
(52, '王涛', '常州雷慕', '13283238223', 'mr开发', '3_1567664154'),
(53, '敖燕娇', '奈良先端科学技术大学院大学', '17781221693', '目前做的研究想要应用HoloLens, 目前初学者，想要了解如何进行HoloLens开发。', '3_1567705490'),
(54, 'owen', '上海理工大学', '18818262405', '想要实现使用pad或手机，显示HoloLens的第三视角', '3_1567789544'),
(55, '王勇盛', '学生', '15303454484', '学习体验', '3_1567871448'),
(56, '张智超', '西安聚合万维网络科技有限公司', '18829342262', '慕名已久，微博互关了大佬', '3_1568123222'),
(57, '陈军', '河北大学', '13784189562', '教学研究', '3_1568184930'),
(58, '刘源', '个人爱好', '18500875984', '尝鲜体验', '3_1568629709'),
(59, '梁传圣', '北航', '18401303989', '研究学习', '3_1568645622'),
(60, '张义文', '同济大学', '17862995275', '基于hololens开发项目，探究mr中的创新交互', '3_1568685017'),
(61, '全宏伟', '大思科技', '18034167623', '做Mr开发，服务客户', '3_1568691102'),
(62, '李勇', '北邮', '17888821299', '想试验一下用来开发HoloLens效果如何', '3_1568696656'),
(63, '海贼王', '隧唐科技', '18280129539', '玩微博看到，过来学习', '3_1568697734'),
(64, '李振学', '没入职', '13072522180', '在微博上看到有点好奇，所以想注册。非计算机专业，但正在学习计算机有关课程', '3_1568720539'),
(65, '邢文凯', '新疆云威智联科技有限公司', '18116995653', '学习', '3_1568777258'),
(66, '张亮', '深圳市恩斯宝科技有限公司', '13922157873', '想尝试用HoloLens是否可以用于教育目的', '3_1568794337'),
(67, '陈敏泽', '清华大学', '18811071309', '测试', '3_1568813267'),
(68, '陈军', 'YSU', '13785266952', '测试', '3_1568813493'),
(69, '付晓豹', '郑州科技学院', '13523716365', '教学测试', '3_1568818014'),
(70, '杨东', '无锡皮皮虾数字科技有限公司', '17633652386', '想体验一下mrpp的可视化编辑', '3_1568856712'),
(71, '黄绍添', '广州可唯科技有限公司', '15920516664', '了解混合现实编程平台，以后可能对工作有帮助', '3_1568941234'),
(72, '王贯山', '北京铁路电气化学校', '13381062388', '感兴趣，学习，偿试做课件玩，有1代hololens，向您们学习，谢谢！', '3_1568949249'),
(73, '黄锐', '青岛科技大学', '13908530541', '学习，感兴趣。了解一下。', '3_1569046329'),
(74, '闫东超', '西安交通大学', '15566045466', '觉得很好玩，刚好实验室有这个设备', '3_1569329045'),
(75, '田婷婷', '上海寓思教育', '17521522334', 'AR应用场景产品经理，想了解开发', '3_1569454098'),
(76, '朱金衡', '深圳惠牛科技有限公司', '18566229658', '想做 AR 开发', '3_1569497060'),
(77, '张亚男', '中科院空间应用工程与技术中心', '18310685785', '我们小组有这样的需求，想进一步了解', '3_1569722359'),
(78, '余延基', '广州宾模工程管理有限公司', '18819437135', '学习该技术，探索该技术用于工程展示的可能性', '3_1570446871'),
(79, '张亚男', '中科院空间应用工程与技术中心', '15081573083', '我们需要这样的平台，寻求合作机会', '3_1570521384'),
(80, '冯康', '优安', '18017109521', '123', '3_1570589703'),
(81, '黄金仁', '广州狮鹫', '18664515331', '学习', '3_1570590436'),
(82, '张彦平', 'Longtek', '15234920620', '从事三维图形开发，最近想要进行hololens 2的开发', '3_1571049176'),
(83, '罗凯佳', '中科院空间应用工程与技术中心', '17810201454', '用于hololens开发学习', '3_1571189550'),
(84, '兰新全', '颍上县水利局', '13865868590', '对mr等新科技很感兴趣，也许能应用到政府部门工作', '3_1571367724'),
(85, '李明', '深圳云教智能科技有限公司', '13772529361', '了解MR、推广应用教学中', '3_1571540111'),
(86, 'Victorsong', '北京凯盛建材工程有限公司', '15901000912', '在b站上看到了up主的视频，很受触动，希望能够通过在网站上的测试，实现建筑模型的更多的展示可能。', '3_1571593082'),
(87, 'zhouxu', '德阳瑞方智能', '18781059365', '目前在研究智能眼镜', '3_1571619736'),
(88, '盖卫东', '歌尔股份', '13665427001', 'hololens设备测试', '3_1571636378'),
(89, '谢翔宇', '淮南市龙湖中学', '18389555001', '原来在微软体系工作，非常关注MR技术，现在到学校工作，开始尝试MR开发', '3_1572174652'),
(90, '曹', '北京国发曼英智能科技有限公司', '15801449774', '测试解决方案', '3_1572228437'),
(91, '赵声威', '黑龙江拓盟科技有限公司', '18646259525', '测试', '3_1572428462'),
(92, '王博智', '武汉大学', '13080688628', '想结合专业，地理，开发一个应用', '3_1572579083'),
(93, '于斌', 'Rootlives 如来', '15067176318', '正在找寻能够快速落地一个基于地理共享建模项目的方式。', '3_1573177187'),
(94, '王玉', '北京国发曼英智能科技有限公司', '15050022732', '正在学习hololens开发', '3_1573184090'),
(95, '罗林', '重庆市渝北区双湖小学校', '13594794987', '我非常喜欢前沿技术，并热衷于把技术应用到教学中', '3_1573484601'),
(96, '王贺', '滕州市智云网络科技有限公司', '18266293105', '混合现实编程相关研究', '3_1573540905'),
(97, '吴明轩', '教育', '15038377196', '看看能在教育培训中使用否', '3_1573546278'),
(98, '刘成源', '上海未元网络科技有限公司', '18661700590', 'study', '3_1573611033'),
(99, '徐宁宁', '江南大学', '18906629366', '本来想申请完研究生空闲了再研究这个的，转念一想多点亮一个技能充实CV更有助于申请hhhhhh', '3_1573713260'),
(100, '张皓翔', '厦门梦加网络科技有限公司', '18844701880', '开发混合现实游戏', '3_1573981260'),
(101, '赵亚超', '华侨大学', '17629290642', '建筑设计与沙盘展示', '3_1574043136'),
(102, '文华', '浙江卫视', '18058765605', '探索hololens在广电的应用。', '3_1574342016'),
(103, '李杭', '中央美术学院', '17801081886', '课程需要', '3_1574732178'),
(104, '文先生', '绵阳网安科技有限公司', '18608090387', '学习', '3_1575260191'),
(105, '济宁', '济宁电网', '17717488519', '项目账号', '3_1575427866'),
(106, 'MrPP', '上海不加班网络科技有限公司', '18202748285', '测试', '3_1575441917'),
(107, '王文远', '南京睿悦信息技术有限公司', '15601592977', '行业从业者，AR产品经理', '3_1575620287'),
(108, '史伟翔', '北京保利盛世科技有限公司', '18079156622', '学习了解', '3_1575948922'),
(109, 'jon.yang', '深科技', '18566772990', '测试，推广！', '3_1575980033'),
(110, '白天宇', '清华大学工业工程系', '13031019905', '我的研究方向是AR在智能工厂里的应用，在知乎看到了这个开发平台的消息，刚好实验室也是用的hololens，特来想体验一下', '3_1576068123'),
(111, '白晓明', '烟台航大', '13105247312', '试用，看是否有合作机会', '3_1576322890'),
(112, '普柯涵', '武汉大学', '18388490809', '想看一看，试一试hololens手势识别与交互技术。', '3_1577325314'),
(113, '程科', '成都虚拟世纪网络科技有限公司', '18615770776', '开发HOLOLENS程序', '3_1577863349'),
(114, '郭子军', '学生一枚', '18435221967', '做课题', '3_1620733232'),
(115, '钱基德', '中国民用航空飞行学院', '13882161442', '学习。', '3_1620750052'),
(116, '刘正', '燕山大学', '13292397098', '科研使用', '3_1620823523'),
(117, '张立', '哈尔滨工业大学', '17729606564', '学习HoloLens2', '3_1620957654'),
(118, '钟华健', '瑞立视科技', '15012996192', 'hololens内容开发调研', '3_1621492375'),
(119, '陈飞', '在校学生', '18232004310', 'hololens开发学习', '3_1621951878'),
(120, '陈子昂', '哩哩', '18678574368', '学习', '3_1621951931'),
(121, '马梓焱', '华中科技大学', '13163216568', '在读研究生，课题方向是混合现实方面，以前看过贵公司的一些demo，想深入了解。', '3_1622620072'),
(122, '梁安娜', '影像艺术研究生', '13222757055', '在知乎上看到相关文章，想学习关于混合现实技术和互动艺术融合的知识', '3_1622825210'),
(123, '马爽予', '西安建筑科技大学', '18829224579', '学习hololens2相关知识，向大佬学习', '3_1623122245'),
(124, '邓兵', '重庆微鹏科技有限公司', '15723122692', '做个MR沙盘', '3_1623210681'),
(125, '肖鲁', '共享加科技', '18910376250', '看看你们的案例', '3_1623230753'),
(126, 'hartin', '君石网络', '15589510642', '学习混合现实开发', '3_1623829143'),
(127, '袁亚', '斐米克（武汉）信息科技有限公司', '18062642692', '学习hololens', '3_1623932035'),
(128, '毛宁波', '西安交通大学', '13669230782', '项目开发学习', '3_1623987944'),
(129, '石文静', '学生', '15727620407', '目前正在做关于HoloLens人脸识别的小项目 但是因为是第一次接触 所以在不断就学习中 ', '3_1636593106'),
(130, '刘子晨', '在读大学生，还没有工作', '18101162586', '对AR感兴趣，在B站上看到了up的视频，想能够多了解一些', '3_1636618850'),
(131, '鲁炳辉', '四川美术学院', '15683936973', '做毕设', '3_1636642607'),
(132, '黄嘉兴', '江西沐光智慧科技有限公司', '15083724080', '公司刚跟杭州的MR眼镜公司ROKID合作，拿到他们的眼镜，看看咱们的平台制作的内容能否在ROKID眼镜上使用', '3_1636697523'),
(133, 'dwei', '携程', '13122789369', '知乎看到元宇宙问题下您的回答，好奇而来ing', '3_1636853367'),
(134, '林粤河', '广州市万物理论跨境电商有限公司', '15322238450', '想努力进步呀，主要也想赚大钱 先发制人', '3_1637027077'),
(135, '郭祺', '目前离职', '17674110942', '想看一下', '3_1637065359'),
(136, '邹韬', '华为', '13510538085', '看看，学习学习', '3_1638156397'),
(137, '饶德奕', '上海核工院', '18930171583', '测试', '3_1638414721'),
(138, '庄睿', '北京容积视觉', '13898467203', '为了学习开发hololens', '3_1638417011'),
(139, '王成良', '中央美术学院', '13612043242', '学习', '3_1638430823'),
(140, '邱零', '学生', '13939020523', '学习交流', '3_1638516423'),
(141, '刘行', '中规院深圳分院', '15361683538', '要实现共同编辑模型方向的业务  希望能试用一下产品', '3_1638525673'),
(142, '吴昌健', '南通永联信息科技有限公司', '18361502494', '向前辈学习hololens开发', '3_1638585430'),
(143, '江欣城', '湖南大学媒介实验室', '13358222643', '想了解关于基于hololens平台的混合现实应用开发', '3_1638589209'),
(144, '严斌', '中控科技集团有限公司', '18811300610', '研究前沿技术', '3_1638691392'),
(145, '李豪', '数据可视化', '18300678743', '项目应用', '3_1638865215'),
(146, '曹漧亞', '中央美术学院', '18513388290', '学习', '3_1638889513'),
(147, '金正勋', '中央美术学院', '15501255063', '学习', '3_1638938078'),
(148, '陈玲', 'msi', '15999566441', '协助公司hololens项目', '3_1639230268'),
(149, '余洋', 'cscec3b', '17771439877', '测试建筑模型使用效果', '3_1639237731'),
(150, '王超', '罗庄区鑫恒商贸服务中心', '13355095155', '体验一下', '3_1639291454'),
(151, '邢茂淋', '山东省盈盛数据技术有限公司', '17615650156', '邸总，我们已回山东。', '3_1639371888'),
(152, '李梓贤', '没有', '13135552950', '看看', '3_1639426827'),
(153, '付明星', '制造业', '15606104418', '学习hololens开发', '3_1639451361'),
(154, '李银彬', '北京中科优辰星宇科技有限公司', '13121602946', '想体验Fragments游戏', '3_1639472173'),
(155, '张佩', '图灵Ai少儿编程教育', '13869886680', '关注你的油管很久了，也给你留过言，对MR有兴趣想学习', '3_1639490614'),
(156, '张军伟', '北京航天长峰科技工业集团有限公司', '15010064679', '学习', '3_1639879265'),
(157, '叶飞跃', '江苏理工学院', '13584508761', '学校的HoloLens2开发，\r\n数据挖掘相关工作', '3_1639887437'),
(158, '蔡正阳', '合肥工业大学', '15810539120', '我是合肥工业大学的副教授蔡正阳，我们课题组采购了一台Hololens2，目的是做一个装配协同的MR程序，目前想探索一下这个眼镜的功能。', '3_1639988506'),
(159, '师谦', '厦门市第一医院', '13004674748', '学习混合现实开发及软件测试，用于医疗相关方面的科研！', '3_1640189192'),
(160, '韦伟', '武汉润尼尔科技有限公司', '18755655738', '学习HoloLens2第三人称视角开发', '3_1640249733'),
(161, '陈圣杰', '武汉理工大学', '13628604982', '毕设学习', '3_1640361163'),
(162, '薛孝志', '中国医科大学附属盛京医院', '15140073996', '在研究医学三维影像VR以及MR展示，发现贵网站非常符合我的要求', '3_1640401897'),
(163, 'jhy陈刚', '珠海市人民医院', '13862152096', '手术测试', '3_1640426022'),
(164, '安迪', '倍特威视', '13627200255', '看看vr如何与视频分析结合，开展业务', '3_1640494408'),
(165, '卢立鑫', '吉林七中', '15981160909', '好奇，看看能不能领学生玩玩', '3_1640520932'),
(166, '付精琪', '西飞', '18966750035', '买了个眼镜不会用', '3_1640525480'),
(167, '张杰', '山东联建云', '18354800726', '有一些关于混合现实的项目', '3_1640657605'),
(168, '刘鹏飞', '中国联通', '17737161669', '对第三视角有兴趣', '3_1640762626'),
(169, 'Scarlett', '重庆邮电大学', '15213365003', '学习', '3_1640871686'),
(170, '缪灿', '苏州博众机器人', '13636501402', '开发hololens2应用', '3_1640943305'),
(171, '曾进', '万科采筑B2B电商平台', '13410220286', '为平台上建材供应商开发vr应用，比如vr数字工厂', '3_1641314792'),
(172, '王海桅', '上海起曜网络科技有限公司', '18621277572', '游戏客户端开发者，对虚拟现实很感兴趣，希望找到一个组织共同学习。', '3_1641734025'),
(173, '王海', '赫里奥（苏州）科技有限公司', '18915277542', '一个刚入行的菜鸡程序员，关注贵司一两年了，看到极客大叔的最新vlog，前来支持一波', '3_1641739126'),
(174, '刘俊杰', '惟觉科技', '13923804057', '项目需求', '3_1641780157'),
(175, '刘金昌', '微盟', '18015139885', '感兴趣', '3_1641783248'),
(176, '大空意', '顺丰速运', '13574873171', '开发快递AR应用场景', '3_1641938618');

-- --------------------------------------------------------

--
-- 表的结构 `invitation`
--

CREATE TABLE `invitation` (
  `id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `used` tinyint(1) DEFAULT NULL,
  `auth_item_name` varchar(255) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `knight`
--

CREATE TABLE `knight` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `mesh_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `like`
--

CREATE TABLE `like` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `local`
--

CREATE TABLE `local` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `data` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 转存表中的数据 `menu`
--

INSERT INTO `menu` (`id`, `name`, `parent`, `route`, `order`, `data`) VALUES
(5, '权限管理', NULL, '@manager/admin/default/index', 5, 0x7b2269636f6e223a202275736572222c202276697369626c65223a20747275657d),
(6, '角色列表', 5, '@manager/admin/role/index', 2, 0x7b2269636f6e223a20227573657273222c202276697369626c65223a20747275657d),
(7, '权限列表', 5, '@manager/admin/permission/index', 3, 0x7b2269636f6e223a20226b6579222c202276697369626c65223a20747275657d),
(8, '路由列表', 5, '@manager/admin/route/index', 4, 0x7b2269636f6e223a20226c696e6b222c202276697369626c65223a20747275657d),
(9, '规则管理', 5, '@manager/admin/rule/index', 5, 0x7b2269636f6e223a2022636865636b2d737175617265222c202276697369626c65223a20747275657d),
(10, '分配用户到角色', 5, '@manager/admin/assignment/index', 6, 0x7b2269636f6e223a2022746167222c202276697369626c65223a20747275657d),
(11, '菜单管理', 5, '@manager/admin/menu/index', 7, 0x7b2269636f6e223a20226c697374222c202276697369626c65223a20747275657d),
(14, '退出登录', NULL, '@manager/site/logout', 10, 0x7b2269636f6e223a20227265706c79222c202276697369626c65223a20747275657d),
(31, '相关文档', NULL, '@manager/document/index', -100, 0x7b2269636f6e223a2022626f6f6b222c202276697369626c65223a20747275657d),
(32, '欢迎页面', 31, '@manager/document/index', 0, 0x7b2269636f6e223a20226e6577737061706572222c202276697369626c65223a20747275657d),
(37, '标签管理', NULL, '@manager/tags/index', NULL, 0x7b2269636f6e223a202274616773222c202276697369626c65223a20747275657d);

-- --------------------------------------------------------

--
-- 表的结构 `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `message_tags`
--

CREATE TABLE `message_tags` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `meta`
--

CREATE TABLE `meta` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verse_id` int(11) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `image_id` int(11) DEFAULT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `meta_event`
--

CREATE TABLE `meta_event` (
  `id` int(11) NOT NULL,
  `meta_id` int(11) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `meta_knight`
--

CREATE TABLE `meta_knight` (
  `id` int(11) NOT NULL,
  `verse_id` int(11) NOT NULL,
  `knight_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `create_at` datetime DEFAULT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `meta_resource`
--

CREATE TABLE `meta_resource` (
  `id` int(11) NOT NULL,
  `meta_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `meta_rete`
--

CREATE TABLE `meta_rete` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `meta_id` int(11) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 转存表中的数据 `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1550974410),
('m130524_201442_init', 1550974413),
('m140506_102106_rbac_init', 1551027932),
('m170907_052038_rbac_add_index_on_auth_assignment_user_id', 1551027932),
('m180523_151638_rbac_updates_indexes_without_prefix', 1551027932),
('m140602_111327_create_menu_table', 1551029855),
('m160312_050000_create_user', 1551029855),
('m190124_110200_add_verification_token_column_to_user_table', 1556777523),
('m190526_064549_create_invitation_table', 1558854142),
('m190526_145637_add_used_column_auth_item_name_column_to_invitation_table', 1558888573),
('m190527_063623_add_user_id_column_to_project_table', 1558942661),
('m190527_072419_create_scripts_table', 1558942661),
('m190528_142525_add_fk_user_id', 1559841358),
('m190604_145824_create_debug_table', 1559841358),
('m190606_162627_add_introduce_column_to_project_table', 1559841358),
('m190611_144411_create_script_data_table', 1560425669),
('m190611_145820_add_create_at_column_to_invitation_table', 1560425669),
('m190612_174335_create_editor_data_table', 1560425669),
('m190612_215717_add_serialization_column_to_editor_data_table', 1560425669),
('m190621_052542_create_question_table', 1561308032),
('m190621_053008_create_info_table', 1561308032),
('m190624_105005_create_feedback_state_table', 1561602815),
('m190624_105011_create_feedback_describe_table', 1561602815),
('m190624_114432_create_feedback_table', 1561603160),
('m190626_094428_create_polygen_table', 1561603160),
('m190626_155223_add_file_name_column_to_polygen_table', 1561603160),
('m190722_153757_add_sharing_column_to_polygen_table', 1563902208),
('m190803_125110_drop_script_data_table', 1565216134),
('m190803_130451_create_script_data_table', 1565216135),
('m190811_081555_create_method_table', 1565931951),
('m190818_181028_add_url_column_to_polygen_table', 1566426883),
('m190904_051543_add_sharing_column_to_project_table', 1567581346),
('m190919_101407_create_blockly_table', 1568988071),
('m191018_081723_create_material_table', 1571650985),
('m191018_083804_add_material_id_column_to_polygen_table', 1571650986),
('m191018_142108_create_file_table', 1571650986),
('m191021_090421_create_file_reference_table', 1571651253),
('m191021_090820_drop_albedo_column_metallic_column_normal_column_occlusion_column_emission_column_from_material_table', 1571651253),
('m191021_091019_add_albedo_column_metallic_column_normal_column_occlusion_column_emission_column_to_material_table', 1571651255),
('m191021_141913_add_user_id_column_to_file_table', 1571668301),
('m191021_142715_drop_albedo_column_metallic_column_normal_column_occlusion_column_emission_column_from_material_table', 1571668302),
('m191021_142823_add_albedo_column_metallic_column_normal_column_occlusion_column_emission_column_to_material_table', 1571668302),
('m191021_143017_drop_file_reference_table', 1571668302),
('m191021_183332_add_user_id_column_to_material_table', 1571824694),
('m191023_094253_drop_md5_index_from_file_table', 1571824694),
('m191023_151527_add_polygen_id_column_to_material_table', 1571854194),
('m191022_033650_create_picture', 1572580021),
('m191024_062650_create_video_table', 1572580021),
('m191024_133900_drop_material_id_column_from_polygen_table', 1572580022),
('m191024_145107_add_material_id_column_to_polygen_table', 1572580022),
('m191024_145146_drop_polygen_id_column_from_material_table', 1572580023),
('m191029_094258_add_polygen_id_column_to_material_table', 1572580024),
('m191029_094345_drop_material_id_column_from_polygen_table', 1572580024),
('m191029_102458_add_idx_column_to_material_table', 1572580024),
('m191029_131337_drop_polygen_id_column_from_material_table', 1572580025),
('m191029_131356_add_polygen_id_column_to_material_table', 1572580025),
('m191029_175522_drop_idx_column_from_material_table', 1572580025),
('m191029_175533_add_name_column_to_material_table', 1572580026),
('m191103_065922_add_file_id_column_to_polygen_table', 1572850298),
('m191120_074714_create_content_type_table', 1574260782),
('m191120_074843_create_content_table', 1574260782),
('m191125_101251_add_color_column_to_material_table', 1575358736),
('m191125_101352_add_smoothness_column_to_material_table', 1575358736),
('m200111_160043_add_value_column_to_blockly_table', 1578758449),
('m200115_144408_create_logic_table', 1579099476),
('m200126_035457_create_maker_table', 1580011057),
('m200201_202009_add_polygen_id_column_to_maker_table', 1580588418),
('m200204_162610_drop_maker_column_from_maker_table', 1580833646),
('m200204_162715_add_snapshots_column_to_maker_table', 1580833646),
('m200206_181454_drop_snapshots_column_from_maker_table', 1581012935),
('m200206_181528_add_data_column_tomaker_table', 1581012935),
('m200207_072743_add_alpha_column_to_material_table', 1582974512),
('m200229_110519_create_goods_table', 1582974512),
('m200229_134259_add_access_token_column_to_user_table', 1582983785),
('m200304_134413_create_projects_table', 1583329810),
('m200304_152048_drop_config_column_logic_column_from_maker_table', 1583335319),
('m200304_152138_add_project_id_column_to_maker_table', 1583335319),
('m200311_132133_drop_project_id_column_from_maker_table', 1583933234),
('m200311_132242_create_programme_table', 1583933234),
('m200311_132401_drop_projects_table', 1583933234),
('m200311_132647_add_programme_id_column_to_maker_table', 1583933234),
('m200312_122903_add_programme_id_column_to_project_table', 1584016156),
('m200314_120551_add_polygen_material_table', 1584187642),
('m200314_120839_create_polygen_material_table', 1584187930),
('m210503_174040_create_version_table', 1620119305),
('m210503_174243_create_url_table', 1620119305),
('m210512_162939_drop_file_name_column_url_column_md5_column_sharing_column_type_column_from_polygen_table', 1620895181),
('m210512_185833_add_width_column_height_column_depth_column_to_polygen_table', 1620895181),
('m210516_132930_add_image_id_column_to_polygen_table', 1621355988),
('m210516_140247_add_created_at_column_to_picture_table', 1621355988),
('m210518_080417_add_created_at_column_to_file_table', 1621355988),
('m210518_081931_drop_created_at_column_from_picture_table', 1621355988),
('m210518_151722_add_filename_column_to_file_table', 1621355988),
('m210518_152004_add_indexs_to_table', 1621355988),
('m210521_072250_add_created_at_column_to_picture_table', 1621698303),
('m210521_083927_add_created_at_column_to_video_table', 1621698303),
('m210521_163836_add_image_id_column_to_video_table', 1621698303),
('m210521_171717_add_image_id_column_to_picture_table', 1621698303),
('m210615_183159_add_image_id_column_to_project_table', 1623782617),
('m210615_190039_add_created_at_column_to_project_table', 1636524403),
('m210615_193338_add_name_column_to_project_table', 1636524403),
('m211101_071434_add_info_column_to_polygen_table', 1636524403),
('m211102_062127_create_resource_table', 1636524403),
('m211104_091347_create_project_data_table', 1636524403),
('m211104_092715_create_project_index_table', 1636524404),
('m211129_083014_add_updater_id_column_to_resource_table', 1638320269),
('m211201_235053_create_wx_table', 1638634037),
('m211204_052923_add_wx_openid_tcolumn_avatar_column_to_user_table', 1638634037),
('m211204_155122_add_created_at_column_to_wx_table', 1638634037),
('m211205_210923_add_nickname_column_to_user_table', 1638739003),
('m211207_232654_create_mrpp_project_table', 1638919802),
('m211207_232655_create_verse_table', 1639450759),
('m211207_232656_create_meta_table', 1639450759),
('m211213_232656_create_verse_rete_table', 1639450759),
('m211216_065236_add_info_column_to_user_table', 1641942630),
('m211217_232656_create_meta_rete_table', 1641942631),
('m211227_082211_drop_avatar_column_from_user_table', 1641942631),
('m211227_082518_add_avatar_id_column_to_user_table', 1641942631),
('m211227_145551_drop_image_column_from_verse_table', 1641942631),
('m211227_145658_add_image_id_column_to_verse_table', 1641942631),
('m211227_145802_drop_image_column_from_meta_table', 1641942631),
('m211227_145810_add_image_id_column_to_meta_table', 1641942631),
('m220102_092508_create_verse_cyber_table', 1641942631),
('m220117_061233_create_message_table', 1642729792),
('m220118_061233_create_reply_table', 1642729792),
('m220123_005220_add_size_column_to_file_table', 1643249232),
('m220125_100753_create_file_store_table', 1643249233),
('m220125_134442_add_key_column_to_file_table', 1643249233),
('m220217_063339_create_tags_table', 1645164617),
('m220217_073405_create_message_tags_table', 1645164618),
('m220218_062529_drop_type_column_from_tags_table', 1645165916),
('m220314_024024_create_verse_share_table', 1647739186),
('m220314_024610_create_user_info_table', 1647739187),
('m220315_061111_create_verse_open_table', 1647739187),
('m220316_092100_add_avatar_id_column_to_user_info_table', 1647739187),
('m220316_092238_drop_avatar_column_from_user_info_table', 1647739187),
('m220316_100307_drop_avatar_id_column_from_user_table', 1647739187),
('m220316_100351_drop_info_column_from_user_table', 1647739187),
('m220317_071805_create_like_table', 1647739187),
('m220317_090247_add_created_at_column_to_like_table', 1647739187),
('m220323_071920_create_token_table', 1649043950),
('m220323_225546_create_trade_table', 1649043950),
('m220326_105029_create_order_table', 1649043950),
('m220329_061716_add_info_column_to_trade_table', 1649043950),
('m220329_064116_add_gold_column_points_column_to_user_info_table', 1649043950),
('m220401_043204_add_managed_column_to_tags_table', 1649043950),
('m220403_064651_create_verse_info_table', 1649043950),
('m220511_225137_add_uuid_column_to_resource_table', 1654668032),
('m220616_072750_create_local_table', 1664289951),
('m220917_102325_create_space_table', 1664293644),
('m220930_092422_add_version_column_to_verse_table', 1664976635),
('m221015_053502_drop_name_column_from_meta_table', 1665812741),
('m221019_123650_create_knight_table', 1666222665),
('m221020_175746_create_meta_knight_table', 1666305122),
('m221107_161044_add_name_column_to_space_table', 1667838970),
('m221108_172213_drop_image_id_column_from_knight_table', 1667928401),
('m221108_172315_add_image_id_column_to_knight_table', 1667928402),
('m221111_170046_create_cyber_table', 1668188607),
('m221111_171450_create_cyber_script_table', 1668188607),
('m221211_094510_add_event_column_to_meta_table', 1670752136),
('m221211_134045_drop_event_column_from_meta_table', 1670771712),
('m221211_134317_create_meta_event_table', 1670779814),
('m221216_051444_drop_links_column_from_meta_event_table', 1671168768),
('m221216_051715_create_verse_event_table', 1671168769),
('m221217_064817_drop_slots_column_from_meta_event_table', 1671259961),
('m221217_064920_add_data_column_to_meta_event_table', 1671259961),
('m221217_165731_add_uuid_column_to_meta_table', 1671298044),
('m221217_173141_add_uuid_column_to_meta_knight_table', 1671299625),
('m230114_044941_add_script_column_to_cyber_table', 1673672302),
('m230217_053717_create_meta_resource_table', 1676737749),
('m230222_110255_create_verse_space_table', 1677117499),
('m230222_110437_create_verse_knight_table', 1677117499),
('m230222_111021_add_editable_column_to_verse_share_table', 1677117499);

-- --------------------------------------------------------

--
-- 表的结构 `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prepay_id` varchar(255) DEFAULT NULL,
  `trade_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `state` int(11) DEFAULT 0,
  `payed_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `reply`
--

CREATE TABLE `reply` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `resource`
--

CREATE TABLE `resource` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `author_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_id` int(11) NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `updater_id` int(11) DEFAULT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `space`
--

CREATE TABLE `space` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `mesh_id` int(11) NOT NULL,
  `dat_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_id` int(11) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `managed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `tags`
--

INSERT INTO `tags` (`id`, `name`, `info`, `managed`) VALUES
(1, '交流', '{\n\"color\":\"#edf5ff\",\n\"type\":\"null\",\n\"explan\":\"主板，综合显示\"\n\n}', 0),
(2, '展示', '{\r\n\"color\":\"#f1f9eb\",\r\n\"type\":\"success\",\r\n\"explan\":\"分享优秀的作品\"\r\n}', 1),
(3, '建议', '{\r\n\"color\":\"#e7f7fd\",\r\n\"type\":\"info\",\r\n\"explan\":\"听取建议共同致力社区生态\"\r\n}', 0),
(4, '问题', '{\r\n\"color\":\"#fcf6ec\",\r\n\"type\":\"warning\",\r\n\"explan\":\"回复并帮助解决问题\"\r\n}', 0),
(5, '错误', '{\r\n\"color\":\"#fcf0f0\",\r\n\"type\":\"danger\",\r\n\"explan\":\"收集反馈定期处理\"\r\n}', 0);

-- --------------------------------------------------------

--
-- 表的结构 `token`
--

CREATE TABLE `token` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `token` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `overdue_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `trade`
--

CREATE TABLE `trade` (
  `id` int(11) NOT NULL,
  `out_trade_no` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `notify_url` varchar(255) DEFAULT NULL,
  `amount` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`amount`)),
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `auth_key` varchar(32) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 10,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL,
  `wx_openid` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `created_at`, `updated_at`, `verification_token`, `access_token`, `wx_openid`, `nickname`) VALUES
(3, 'dirui', 'iNZYA1VxZOpyavsREAeFqLnF-HowC8UL', '$2y$13$OZY0OxP8HG5b9HlJBK2MseH8pW1agWRn9CcVL/C3grNXtZFkM7YM2', 'C5hj3HJ70qHt1JuD_jMyP4QPV2yeloyb_1637221641', 'dirui1981@gmail.com', 10, 1552152063, 1674008434, NULL, NULL, 'ol2S459vnAQOAHKfy_tRPnitgStc', '游戏开发极客');

-- --------------------------------------------------------

--
-- 表的结构 `user_info`
--

CREATE TABLE `user_info` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `avatar_id` int(11) DEFAULT NULL,
  `gold` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `user_info`
--

INSERT INTO `user_info` (`id`, `user_id`, `info`, `avatar_id`, `gold`, `points`) VALUES
(142, 3, NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `verse`
--

CREATE TABLE `verse` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `image_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse_event`
--

CREATE TABLE `verse_event` (
  `id` int(11) NOT NULL,
  `verse_id` int(11) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse_knight`
--

CREATE TABLE `verse_knight` (
  `id` int(11) NOT NULL,
  `verse_id` int(11) NOT NULL,
  `knight_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse_open`
--

CREATE TABLE `verse_open` (
  `id` int(11) NOT NULL,
  `verse_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse_share`
--

CREATE TABLE `verse_share` (
  `id` int(11) NOT NULL,
  `verse_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `editable` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse_space`
--

CREATE TABLE `verse_space` (
  `id` int(11) NOT NULL,
  `verse_id` int(11) NOT NULL,
  `space_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wx`
--

CREATE TABLE `wx` (
  `id` int(11) NOT NULL,
  `wx_openid` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `wx`
--

INSERT INTO `wx` (`id`, `wx_openid`, `token`, `user_id`, `created_at`) VALUES
(253, 'ol2S45-6gRaNFiZd4RUOwfEE-Bw0', 'cyyOvdBwoAU_JiFfOcM2caEAti8aEveP', NULL, '2022-03-19 16:21:58'),
(277, 'ol2S4548g5d3QwBnLLh-C6WOI9Xc', '', NULL, '2022-03-21 06:46:08'),
(278, 'ol2S454C1RtXeviwP9g4_Q6_5dDE', 'UAlaahJJjIsj-4i0G2ydefJQl7GF_3Qg', NULL, '2022-03-21 07:41:24'),
(279, 'ol2S457Dpxi4oUPPNJfXQf25KTF4', '2ysBQq3i_Db5dZ68KcRIdGHZwbidlhuH', NULL, '2022-03-21 08:41:01'),
(280, 'ol2S45zbR-W6cJpMIBMIgMAd1hoI', 'M-fFSTmHcBZ1YstjhzgueRFgGDLyfvAh', NULL, '2022-03-22 05:32:26'),
(281, 'ol2S451XWRJ6a4PHbUVWmwZwLkfQ', 'DRd94Ls0LKA_w31eZmIc2cbYFogSQD00', NULL, '2022-03-22 08:37:48'),
(282, 'ol2S453t2j7cfrBOM7CBG1KSD0N8', 'Rtqw2xn0Jq1s-0k35XvsH4uh9Lg4OrkR', NULL, '2022-03-22 10:14:56'),
(285, 'ol2S45_ZHu6aMhMcUKfIRPmcjzOg', 'PMzsCaleiOjdJ_JhdxCtQM8IWvKPikux', NULL, '2022-03-24 01:37:35'),
(286, 'ol2S45yx_spJwlq5N_jOrHOSilG4', 'Tkgw0I7OQSyHqQZaD0j-9q_fG3ac9dWn', NULL, '2022-03-24 15:53:51'),
(287, 'ol2S451z0bM8M6EacddfWpm-GjS8', 'uv5YwlRD6DsT7bTiruoC8bHMiZN8tg4a', NULL, '2022-03-25 01:31:32'),
(288, 'ol2S45xGu9TTlM49FfSHViQ7kKJI', '3sVSdkaN_1U6bzsXvb6MLMMGALsEqLs4', NULL, '2022-03-25 04:31:54'),
(289, 'ol2S451WK7jf3y8sj-9LTeCqA6tQ', 'PdkoQBugkZqJQ4WzQbKVvgHtaxQAb9Ar', NULL, '2022-03-25 05:35:38'),
(290, 'ol2S45ykO-HlFX9C0Re4BPDtUCcQ', 'phGcNsRFkCAHG7ZxQniPspjLCpNM13-J', NULL, '2022-03-26 02:32:36'),
(293, 'ol2S45wo1J5aqIKX6Wx-rUf4KZOg', '_-q8zPRcMMV65aGytZcsbpQaIjZ9eejB', NULL, '2022-03-26 08:54:32'),
(294, 'ol2S4586KJJPpXUWBN_UDgT5wnJY', 'hrfG1P82Rv1xYoej95jX5QDjZWQno6dg', NULL, '2022-03-26 11:48:30'),
(295, 'ol2S4534ReOM-IAvTHte2kZLSdys', 'aKuNnAsJEHfU-Jo0KFid6b1sUdDPFfoz', NULL, '2022-03-27 07:54:41'),
(296, 'ol2S459X0IldMC7BhJ00_tvbMQHE', 'ID-V5U7_n2nyfU8hqix9uZjqO-rCmfLX', NULL, '2022-03-27 07:54:50'),
(297, 'ol2S45_dnozk0StWTbdk2ElRKdtI', 'lpWx4QTt4VnbWvp64ztrKBm0yOiKonCE', NULL, '2022-03-27 07:55:40'),
(298, 'ol2S45yokyYdGq4G8nQWmEksJTsM', '2Nei_KNynuKFvyH0C7mTaEC4PEQ_4MgH', NULL, '2022-03-27 08:01:31'),
(299, 'ol2S453TAeoRSmNk1j3Y6-vLpGmM', '0x5BjUlX1S9-AwNZ5YJ48UYH9LOUXqAW', NULL, '2022-03-27 08:03:15'),
(300, 'ol2S4556dlcRSH_ut24B2eVs4AzM', '', NULL, '2022-03-27 08:04:53'),
(301, 'ol2S45yehWDXjN1LUHZ3wckWSWaE', 'KR3hNDKFBMnQM80VF56ALeWVsIZCGVyH', NULL, '2022-03-27 08:10:12'),
(302, 'ol2S459Sek4qKoZS8Yhf1o4UgSJ0', 'yofN3KB0rETGXmgBKfNPortdblZ3V-pq', NULL, '2022-03-27 08:10:44'),
(303, 'ol2S454BeyaoIDgbhfeUM5GVJ53w', 'Clv88-Mwr0oyr6Cl2NJ7WRb6hXgj3r9P', NULL, '2022-03-27 08:11:24'),
(304, 'ol2S454BeyaoIDgbhfeUM5GVJ53w', '5wb3Pk_rRgI9okoSQ9T9j4RqhtX23A4E', NULL, '2022-03-27 08:12:14'),
(305, 'ol2S450ReGLD8CCM-3bd2PuPHdbQ', 'wQzg6mcBiVlxiGU6qb-2qParn5aQ-NLN', NULL, '2022-03-27 08:17:50'),
(307, 'ol2S454pacdsH6AQWtLaLYN8qvfw', 'ZXWlSnwFoFJzLqGsjHVyi2f_8jrU6rRR', NULL, '2022-03-27 08:43:44'),
(309, 'ol2S45yvDrY3NnXrv4GN9u1FAmkE', '', NULL, '2022-03-27 11:48:23'),
(310, 'ol2S45yvDrY3NnXrv4GN9u1FAmkE', 'WMVqXkH5LlukfkxrJQ8Bwi2CIwSebRUH', NULL, '2022-03-27 11:51:03'),
(311, 'ol2S452dKgLiN7nZCErHoOuHFj4o', 'LDb4FB1Mf_rZnSYzeLBaNFMOIhgowb1q', NULL, '2022-03-27 14:25:06'),
(312, 'ol2S457VJ-U5uDjzp61NWvTEAxv4', 'w-RPKP3Asx4xkfDTZX00wQKqT0abxSDv', NULL, '2022-03-28 01:09:30'),
(313, 'ol2S451dPh60kV7lyYbNHmV9NW1M', '', NULL, '2022-03-28 07:14:59'),
(314, 'ol2S451dPh60kV7lyYbNHmV9NW1M', 'IAusGWfErukwbnAOq-B2BWgndJpBuN0x', NULL, '2022-03-28 07:15:26'),
(315, 'ol2S452Mm3ZA7IKmaMai0-bzFoUY', 'HHfuUlYd4Of4l2e257ox_0KROiQuukqX', NULL, '2022-03-28 23:53:49'),
(319, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'BB4sGc03yR2J1HunnK5SsLJRYKYbb_ue', 3, '2022-03-29 09:17:25'),
(320, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'mL2QKHD9O9hTpb3br8WaECgOZ3pYQCEv', 3, '2022-03-29 09:17:36'),
(322, 'ol2S459vnAQOAHKfy_tRPnitgStc', '9AVA_h-8ewtQs0OdiEfLI2SMalaZ869T', 3, '2022-03-29 09:24:27'),
(323, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'UV7O24oqhic0YpVoNKfT505VmZ0ZjYm6', 3, '2022-03-29 09:27:05'),
(324, 'ol2S459qpqlSoTHJPJK9G-AdJuUQ', 'qM_pQbL-O4nqbVwo0uBx8LoGYyISCMH_', NULL, '2022-03-29 11:13:20'),
(325, 'ol2S45wPLFBUKlGzo0Q9nY4zG97c', '1QyCa_5d3iynNBEovE3b8dQDsYIOOY-t', NULL, '2022-03-29 12:03:52'),
(326, 'ol2S45wPLFBUKlGzo0Q9nY4zG97c', 'XXhrr65ZEiWG4FSMSyN_9tCGawfU7c-o', NULL, '2022-03-29 12:04:14'),
(327, 'ol2S454HDnzTciUIZTC9xDR9Ls0o', 'h50nawFyP9vaIOHzB9q7pO5nkFOj8-Vd', NULL, '2022-03-29 12:14:31'),
(328, 'ol2S45wPLFBUKlGzo0Q9nY4zG97c', 'HZQqNknVIM11M7s746oJa_yeJMN-5rk0', NULL, '2022-03-29 12:22:07'),
(330, 'ol2S4535cKtTXgcsGJ4yDCgdcWrQ', 'iToPqCJzimmrSwCvrP3PvTymt9cIvjNh', NULL, '2022-03-29 15:29:29'),
(331, 'ol2S4535cKtTXgcsGJ4yDCgdcWrQ', 'yCAulEbDcMjy1t0qWYk8PWxogmBZgDLc', NULL, '2022-03-29 15:29:47'),
(334, 'ol2S4558h90xiG5xXTZY7EEQlA64', 'FNCY80WaAxNeDT-pKWDaZSQfqm2AZ6WD', NULL, '2022-03-30 05:03:05'),
(335, 'ol2S45wVwvNZFEgVFVls5dXc1wqs', '82VdX1QQ_t5huMZ3UIFECJ2MJM72eYaF', NULL, '2022-03-30 06:52:22'),
(336, 'ol2S45wPLFBUKlGzo0Q9nY4zG97c', 'Ajn8mo7-RgbwUde1kWGx9_WJaXtFDHx8', NULL, '2022-03-30 07:21:45'),
(337, 'ol2S456xwpwR-vCUbI0C1s38PPcQ', 'ay7XhFDc2-xd_3y-7sX8Po1Ho2kbaJ2A', NULL, '2022-03-30 09:47:35'),
(338, 'ol2S451HXDRIZPtZ9W67H2yXuAk4', 'SvurI6C0P2OcDkDDgApoNPJQ1Fnm68cO', NULL, '2022-03-30 14:50:30'),
(339, 'ol2S458ltQfwv0IwTOTWMVoGiwpI', 'o-fXheQb2EugDOW15ukf4dBPJHcPf5H1', NULL, '2022-03-31 01:28:55'),
(340, 'ol2S45xeGMJ1MIFDFExiyuv-j6Ww', 'xkqZfx2LxajZ7Jq9mizBpdlUo6cHaemy', NULL, '2022-03-31 02:47:32'),
(341, 'ol2S45-1xcdxVgAe6KzyIbQeWjP8', 'xQQvQfvoL-d-FQ_cwquzFvkbEn4_vR42', NULL, '2022-03-31 07:41:05'),
(343, 'ol2S456sRqDumt9SQ26Sa9FsugSY', 'rbSpqPTbIsOLEmG6U_Rg5XUaqLGfNd0n', NULL, '2022-03-31 23:55:01'),
(344, 'ol2S451jB5cBT8xvob2S6A1X9lXQ', 'Zr0PqBszUz4lYAYAYMFuGCJTkt70uDyD', NULL, '2022-03-31 23:56:00'),
(345, 'ol2S451jB5cBT8xvob2S6A1X9lXQ', 'BGJDVVTFTrcbVePsiyFGKcffQ5aO15VJ', NULL, '2022-03-31 23:56:16'),
(346, 'ol2S451jB5cBT8xvob2S6A1X9lXQ', '4M345Dl344RCLERBkuoKGBLlA7hTQyCT', NULL, '2022-03-31 23:57:13'),
(347, 'ol2S451praMFch7jyzWRs89GkCXo', 'ee-MLIMd-uB5fzUKzqVkWzn2LjhVM56x', NULL, '2022-04-01 06:20:57'),
(348, 'ol2S4525g80KKcXtQ_ZRZslPxlvw', '3HgIBgP8vd78sJcMV9Qd3mOsu__diOxS', NULL, '2022-04-01 08:11:14'),
(349, 'ol2S45wPLFBUKlGzo0Q9nY4zG97c', 'yHryYHpy3NPJ7kkWXs22N-o4AUjc9oHI', NULL, '2022-04-01 10:50:41'),
(350, 'ol2S459d5ml03DUY1h_6sK28dpEg', '', NULL, '2022-04-01 12:56:43'),
(351, 'ol2S455x39Oe6eA69wdVDgwF9uMg', 'sFP6zeOQaxmZf6RdNOrFTWGyhIQXZ-YZ', NULL, '2022-04-01 14:04:12'),
(352, 'ol2S456CCtcs717OKrk3GOFLMF7c', 'dkCVOJhg1s8SGYojNtZNs-lrU-b4Itzk', NULL, '2022-04-02 02:31:57'),
(353, 'ol2S453FQ40KqWtPZyrPpMZobTVw', 'FSzGshje69dQ0mvHuWkj5Rkvsp-xhRe_', NULL, '2022-04-02 08:39:04'),
(355, 'ol2S450XsxEfQM5iiNyrhLLcYbMA', 'RJaSjGJ-KdhEe47wAav7vprSw_X0w26f', NULL, '2022-04-05 13:34:13'),
(356, 'ol2S458--pRLFlnwiHKuVgFddsdo', '', NULL, '2022-04-06 14:49:25'),
(357, 'ol2S458--pRLFlnwiHKuVgFddsdo', 'kkOQVWf8lrH6XQOP2Rj01DrzRTsE4rx9', NULL, '2022-04-06 14:50:02'),
(358, 'ol2S458ZrpbIpVcy_9tY_tYef0Q8', 'CaxOrcfV_FQghRS8XoUU0CEAQrnnvEuW', NULL, '2022-04-06 16:21:22'),
(359, 'ol2S4580pRo72QfxZHKtFvHDouhY', 'VpxXrlBed7XDrd7xu-2HAUy8u7PPupKi', NULL, '2022-04-07 05:50:00'),
(360, 'ol2S450XSDTdCA356OM3QiPqZBaw', '', NULL, '2022-04-07 06:49:21'),
(361, 'ol2S450XSDTdCA356OM3QiPqZBaw', '68DVKZ_vTlUmWmR4BZ8Q5RiDkEceZfsk', NULL, '2022-04-07 06:50:14'),
(363, 'ol2S458ZY8sDhgQjZcqNl1AbcoUo', '', NULL, '2022-04-08 01:31:35'),
(364, 'ol2S450CWgm2W_sn7tx674AiIAbk', '-z_iLlhP6QkzfR15-PpJvnXsQYIwHgY9', NULL, '2022-04-08 01:33:45'),
(365, 'ol2S454le4NF9nEF_-1KToYMqkuk', '', NULL, '2022-04-08 01:51:48'),
(366, 'ol2S45-wkcwhjzLjiuxFEwGm99VM', '', NULL, '2022-04-08 02:11:33'),
(367, 'ol2S45-wkcwhjzLjiuxFEwGm99VM', 'kd7rkltwcLMD20IH2snRaEQbQhW7dBha', NULL, '2022-04-08 02:15:00'),
(368, 'ol2S455YeS5ogjYTFhZQoG5ZQKJU', '', NULL, '2022-04-08 04:47:06'),
(369, 'ol2S458f9fOtbKTR-moWiUJq3eOs', '', NULL, '2022-04-08 05:36:52'),
(370, 'ol2S45x37TUFdFPsskADKSH06CQM', '', NULL, '2022-04-08 06:32:37'),
(371, 'ol2S45woxRFuTtG0fIOm5fzL7Chc', '', NULL, '2022-04-08 06:52:34'),
(372, 'ol2S450CHW-MX9GxUVywqbtHL7kc', '', NULL, '2022-04-08 08:17:43'),
(373, 'ol2S450CHW-MX9GxUVywqbtHL7kc', 'z6khGh37ShDDCcoe1AWaBtgOgaY08DPj', NULL, '2022-04-08 08:29:30'),
(374, 'ol2S456Uj6_xxJuL2XE039Y_prdU', 'SIm0VRGXedjBF0d0Ntzi_X_bbPph1jd7', NULL, '2022-04-08 09:13:44'),
(375, 'ol2S456G_lQxWvs7JvoxZsEzEKVM', 'FhhwV4bv7VmsEYXw3Q5SG0WZjZK0Q73u', NULL, '2022-04-08 10:04:40'),
(376, 'ol2S457ei9ml1oTgxW8zVdqurRao', '', NULL, '2022-04-08 11:01:02'),
(377, 'ol2S456XbE6A3vxRGyAeNwcJjN4M', '', NULL, '2022-04-08 11:19:35'),
(378, 'ol2S45z9mqa2mUmJgOzuVrc8a0Do', 'ShINzIhqbllXpxY1iSY9OX0vbR0T4avp', NULL, '2022-04-08 12:38:26'),
(379, 'ol2S45-7rPCFiETsSkaiXeXAlVxQ', '', NULL, '2022-04-08 13:12:15'),
(380, 'ol2S459_L_eoZo9mAVfL5IrZz30c', '', NULL, '2022-04-08 13:12:31'),
(381, 'ol2S454bmxwF4fbdKK_gbkBSOsXE', '', NULL, '2022-04-08 13:21:43'),
(382, 'ol2S4581H3zP7ojL2JC6qAVXt_e8', '', NULL, '2022-04-08 13:42:52'),
(383, 'ol2S45yNAoL9ntW-yMTzuZuNZRw8', '', NULL, '2022-04-08 16:23:12'),
(384, 'ol2S45xBVqZv4wrdD5_mio3b_z8s', '', NULL, '2022-04-09 11:33:41'),
(385, 'ol2S45xlikQBfdUel3I_8vkEkAfA', '', NULL, '2022-04-09 11:35:58'),
(386, 'ol2S454D19fAiIr_zqaOfmt8ttOE', '_Ny9myhMf0n1eZKZO3M5GQRwbXJ7i_TG', NULL, '2022-04-10 07:02:11'),
(387, 'ol2S451FbAGHqb_yxFAyMlozUMvM', 'iWkhEKq6sfO3JTWLsT_S4eXk99fAUOOh', NULL, '2022-04-10 08:48:21'),
(388, 'ol2S456FuzwN4XwC3LMHgi0tYrvg', '-H4fhgttmpsT5NtQrcu-3PoTez8BVWQF', NULL, '2022-04-10 10:26:09'),
(389, 'ol2S456FuzwN4XwC3LMHgi0tYrvg', 'tOdZ6SmvUW8vbBs8YAIcKpHLr8EJbj8V', NULL, '2022-04-10 12:56:55'),
(390, 'ol2S451z0bM8M6EacddfWpm-GjS8', 'lfll2hCTMAf3u0hoIZNeeFyuCnG-R-Mx', NULL, '2022-04-10 13:04:22'),
(391, 'ol2S456GBkApWG8Ezm9IpwwFRZoU', '', NULL, '2022-04-10 17:40:47'),
(392, 'ol2S454GA_t_33G3JFU1QaC6YZVg', 'LXPk0LeLRXG7cDLyjvGSHTmk6O0vKrqd', NULL, '2022-04-11 03:20:15'),
(393, 'ol2S452otRGaynLTFQtapXtpziew', '', NULL, '2022-04-11 05:46:39'),
(394, 'ol2S45ztHQy-jTmNRIxurjR6VX08', 'xw1LDLLKNS0vYdLMaSAaK8qMTAV7nosj', NULL, '2022-04-11 09:33:15'),
(395, 'ol2S45yf7xtBLMNyawExTjcDP7fU', 'ehPWAgV5aH8yEnd-8e3YC1gIb1HOVsgM', NULL, '2022-04-11 09:34:02'),
(396, 'ol2S452SwNorouop4kQ7wTYKJ69U', '', NULL, '2022-04-11 09:40:50'),
(397, 'ol2S451Dr9u5gC_Bxg1d2mIsbO24', '', NULL, '2022-04-11 09:41:03'),
(398, 'ol2S45_r-TNiIh0UYooKE4FVeWVM', '4Frov_yXnLyMT7CVJoSpoB1ve1wQiunS', NULL, '2022-04-11 09:41:55'),
(399, 'ol2S455MtGjbGKxOmjtlleGzjaww', '', NULL, '2022-04-11 09:58:36'),
(400, 'ol2S45ycPR8aTM0nxaIjE_oeqHr0', '', NULL, '2022-04-11 10:08:22'),
(401, 'ol2S45_S3b954EZ0HfqfDfPmwH6g', 'wgUIIzia9WrdeYHpgYH3PNtyLGrAWnM2', NULL, '2022-04-11 10:13:11'),
(402, 'ol2S456F84RXit6selTRNsfJ1t3I', '', NULL, '2022-04-11 10:29:53'),
(403, 'ol2S459CSZyuYJP-OxoQjVIf3gic', '', NULL, '2022-04-11 10:42:03'),
(404, 'ol2S452bPRqJIs-qvSdinsZTyiKY', 'ELXTiziR4PhsPwpoiK6M0nK0lxlNlUAH', NULL, '2022-04-11 10:57:27'),
(405, 'ol2S45x8GbdILzfMZSIxPqIYxUR8', '', NULL, '2022-04-11 11:12:21'),
(406, 'ol2S45__DWVUGhkqaI-5txBXcrVU', 'AmdDedPZAnmo6J1Kj7NsabeSY5OOBMPm', NULL, '2022-04-11 11:43:55'),
(410, 'ol2S455Vh_jLuI9klJa2uWcAJQh0', '', NULL, '2022-04-11 12:09:43'),
(411, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'zTKixu33FwsDfYXIRBlIsKtgddAk-klG', NULL, '2022-04-11 12:11:08'),
(413, 'ol2S4552TLUk8Y2gsKIirOav9Xik', 'zpMQIzEE9qKbcQvHsEDCuTP8cfuFkywR', NULL, '2022-04-11 12:12:48'),
(414, 'ol2S45_dMv-L3uUZc8-F2hN2pcE8', 'lBSRkFZeYp_NvLtl9cMa-wywpx1I8PCc', NULL, '2022-04-11 12:40:18'),
(415, 'ol2S45_WcvQQqKDdxPP4Jaxz9Deo', '', NULL, '2022-04-11 12:54:09'),
(416, 'ol2S45zJLSR_YxnyMbHPk2DHwzsQ', '', NULL, '2022-04-11 13:05:04'),
(417, 'ol2S45wsjx7ErbMaYzAPUWBxXA3w', '', NULL, '2022-04-12 01:01:14'),
(418, 'ol2S459vF9C0rarp5MFwktP7utuw', 'Ik9Y5PdhcOV4OES_IWUyTCkA7td27iV6', NULL, '2022-04-12 02:01:43'),
(419, 'ol2S458lYMf_ImSFoR4aXbDAG4XE', '', NULL, '2022-04-12 02:05:22'),
(420, 'ol2S45xecHnsTuBrrLD89q7uDRO8', 'FW9px1voEBLbrJrJfKIjzpIZpVpMGgZm', NULL, '2022-04-12 03:13:01'),
(421, 'ol2S454F5xqhwetnNBqsqkdgKmLo', '', NULL, '2022-04-12 08:39:02'),
(422, 'ol2S454n0j1alYv5Fyie80S9k77U', 'ZkHOKMRIpqRAHamVLpUe3etylKw0zKh2', NULL, '2022-04-12 20:28:15'),
(423, 'ol2S454n0j1alYv5Fyie80S9k77U', 'RYXCBWdoV3wxSKyDDT7a1Va01tBoTUKT', NULL, '2022-04-12 20:28:35'),
(426, 'ol2S458PN5OlHUJjD63W1Dr3CKyo', '1y2LvyWS_vQjinQ-DmugqgcctFFxhPUS', NULL, '2022-04-13 08:28:55'),
(427, 'ol2S45_GIKYQtw4XzMLmH8HNA35w', '', NULL, '2022-04-13 09:15:34'),
(428, 'ol2S45_sneGpnx14fCM5-DzF9J2g', '', NULL, '2022-04-13 09:18:31'),
(429, 'ol2S450LCpmTWo8yd4o9NxxwRjTs', 'Cb7leVVVx3ACAHSyYebNCoxgJAI8s94A', NULL, '2022-04-13 09:54:59'),
(430, 'ol2S454n03TmFSWJ7DSf3eFni0HA', 'i5SZ3jK2xFJoOWr2yFVNisLYHRJcRKuX', NULL, '2022-04-13 10:26:26'),
(431, 'ol2S45whxvNfzfzamScu0v0FNQZ8', '', NULL, '2022-04-14 00:17:07'),
(432, 'ol2S450n_SVT7M8rzqM-VX6gf42E', 'OcN5w2dwVV6WDrzkrUmKC4TK4KRfBhox', NULL, '2022-04-14 01:40:49'),
(433, 'ol2S453Kl8H7yTDIoJBUPR-Vu29k', 'h3knxOEo8lPm7xKw5Xa90cXQHe5Eitvn', NULL, '2022-04-14 03:16:49'),
(434, 'ol2S455-LTFZQBk5AYTzPlxks5vU', '41CDiKt1-hvXdvNV-vK9tyfZQ9xgb9hS', NULL, '2022-04-14 07:42:27'),
(435, 'ol2S45yhBj31lWlpeENqRImYJ1s0', 'mVe81qADFG2gcLhFkd0nvxgs8T6GiL6S', NULL, '2022-04-15 14:55:17'),
(436, 'ol2S454vNEw5azVtNOFvHwGqiHLg', '', NULL, '2022-04-18 03:09:33'),
(437, 'ol2S450sadqpFayXz6kvIlQ9OJck', 'JiVHLComZNXcaqavqvD1eycXzcTkYKsT', NULL, '2022-04-19 12:43:46'),
(438, 'ol2S456GiBAOWgiFbqgPJGMfe44o', '', NULL, '2022-04-20 07:22:12'),
(439, 'ol2S45xicAC-CIT0slpovhpeiPpQ', '', NULL, '2022-04-20 07:22:29'),
(440, 'ol2S45-skPpVOh7-KgRvnUn8AatI', 'tbAwd3EdUSqR4jauaB9JNyz8WbTFgkyS', NULL, '2022-04-20 08:34:43'),
(441, 'ol2S452LO8N8DBxcGTcR1tXUOMuM', 'JzcT6zKWKAvI_t3pldb5uSGMiwnEzJ5a', NULL, '2022-04-20 13:33:31'),
(442, 'ol2S456TPN9mR5BuWeL3af_ZThU8', 'PeuwwpkESTCyjOJMTCNRieMQuwD581_M', NULL, '2022-04-20 14:51:24'),
(443, 'ol2S456HW_c1V_ipEDI1fn3pFtNM', 'uLAxHkiJbyQe47oVcYAaFOOGOG8G2uMd', NULL, '2022-04-21 14:38:33'),
(444, 'ol2S456vmTuzzXlZqOft1uJ1URQQ', '4IbCeQKDp5X3hst1S5ciuGhKMgKKomz1', NULL, '2022-04-22 08:11:12'),
(445, 'ol2S45-dFShTvsd7t8FzjKJkNSDg', '', NULL, '2022-04-24 01:37:52'),
(446, 'ol2S455MHhd3d4AqnsdnnGh4EeWY', '', NULL, '2022-04-24 04:30:52'),
(447, 'ol2S45zrxmxctkt9JoBcME8eKxCY', '', NULL, '2022-04-26 00:36:36'),
(448, 'ol2S451wZrYSjZKdzFzwvY9WTCW4', '3bVbGBOzkMbaE3h51WMNeRcYSfbAwH72', NULL, '2022-04-26 01:31:08'),
(449, 'ol2S455o_rDc5euX6vC8aoAbjHQc', 'W41mQQdld3NJB6tlxyrY8cT_VL7kYUtG', NULL, '2022-04-26 02:49:48'),
(450, 'ol2S455o_rDc5euX6vC8aoAbjHQc', 'euNz0lgJFn7qaoLTRqIzkzFTVIsMWM_5', NULL, '2022-04-26 02:51:20'),
(453, 'ol2S451wZrYSjZKdzFzwvY9WTCW4', 'yq1nuLtyHm5fN5BZD3Np8wja_j7Rwu_Q', NULL, '2022-04-26 10:16:52'),
(457, 'ol2S45y--KbZjg4D4eqEStDWxC5c', '', NULL, '2022-04-29 02:39:49'),
(459, 'ol2S456xN4hyKOvPzBvI96oLv5Bg', '', NULL, '2022-04-29 18:53:30'),
(461, 'ol2S459e98tHIHy7smHc8YZ8e76E', 'eWw2UPvt69m3gQoH5UgBqnb3pmXuPoLr', NULL, '2022-05-01 14:15:58'),
(462, 'ol2S457FbfWT_jdhX-xAv3cCKjUM', '', NULL, '2022-05-06 10:40:09'),
(463, 'ol2S457FbfWT_jdhX-xAv3cCKjUM', 'qfAB9aeDD3YyG6wgtXXw8NQtH2_c6Bi9', NULL, '2022-05-06 10:40:34'),
(464, 'ol2S457FbfWT_jdhX-xAv3cCKjUM', 'yjMdCenvxpvGuYigTJ6wXh5kQjgqmOJN', NULL, '2022-05-06 10:40:50'),
(466, 'ol2S45xawdp7wI2Y7U2EKIrjJMQE', 'vBGdAJxWeYHkgh_Hp9UPigFpCIeKBAr3', NULL, '2022-05-07 10:07:57'),
(467, 'ol2S45yvcAOL5PbZDVtR17dyW7Rk', 'Kqkcg8Bz5iok1mPyWKereL15jDbwoxxS', NULL, '2022-05-08 03:07:04'),
(469, 'ol2S45z-pzcMnorHbzQMR3wmHvew', 'Xfr1utw8m_u9i8bHZu4-tbyxzas5P0rz', NULL, '2022-05-09 14:05:22'),
(470, 'ol2S452yh9A16leGvk31JSepYBjA', '', NULL, '2022-05-09 14:37:43'),
(471, 'ol2S45_FtGIzllbn3CLEFQlcx0PU', 'ECrrY1GB1p-DNstyeldmiD_SCpmggAnS', NULL, '2022-05-10 08:16:13'),
(472, 'ol2S450HxR3cPxErpbAub8CfvvFg', 'Mh1mx_lxLjh-bXP720xxz2Nh_0lpM4d7', NULL, '2022-05-11 00:49:29'),
(473, 'ol2S45yQ_kztpZ5_mo7f3irMGQMQ', 'UMCMuQgoTipN1j8p5HbUI5Pi_u25FL4L', NULL, '2022-05-11 11:44:58'),
(474, 'ol2S45-VWH2aLgZAClGpzH8X0_qw', '9Yo5PSm6VukLOspzk9j3BuSLMok5VuJF', NULL, '2022-05-11 12:07:58'),
(475, 'ol2S45-yjvCurEQnA51qu6gppu4Q', '', NULL, '2022-05-13 05:56:06'),
(476, 'ol2S45wFurZZq4ZoU8lLvqAY2rPs', '', NULL, '2022-05-14 05:04:22'),
(477, 'ol2S45zTmCKqWydj9euAZjI6Zzig', '', NULL, '2022-05-14 06:43:05'),
(482, 'ol2S456yFlaANiw_mzV5EfjxPcY4', '', NULL, '2022-05-17 05:37:12'),
(484, 'ol2S454ncbgtYp4GsfsbEO-GnNZY', 'Oc7BSaZ6yi96nQWy5XzXYRUHFjrhNI_J', NULL, '2022-05-17 09:32:23'),
(485, 'ol2S45zLRFWUfRQ5TQeYF198wMMU', '', NULL, '2022-05-18 00:35:43'),
(486, 'ol2S459C1mAy4uFZls5bi0tcSQeQ', 'vzqmKiFVa7BPQGL49yBPng5hO8d2USQJ', NULL, '2022-05-19 14:49:23'),
(487, 'ol2S45zTmCKqWydj9euAZjI6Zzig', '2ZrmaQzh5LvP3y78dP01EX4ojZ-dHXQ_', NULL, '2022-05-20 06:55:55'),
(488, 'ol2S457u3RwSsP6DD9nNiCO1dUeU', 'JO7SFgyvpu0BVluvEDkDMgTkMrPnfZkb', NULL, '2022-05-20 08:00:38'),
(491, 'ol2S45_oDmT8IVrj2blM_3CS6B7I', 'KnjVVnWZcr_8itn7zvVqYIavKLTW8_e-', NULL, '2022-05-25 05:33:56'),
(492, 'ol2S45_oDmT8IVrj2blM_3CS6B7I', 'lMFpVdxLFWX80yTnALu2zjuWj4C5E-hD', NULL, '2022-05-25 05:37:34'),
(493, 'ol2S450tkJfuUdBagLejwj6S4j7I', 'ES7RKxpqC59a2zXPlcwQrDOOwb-2uakj', NULL, '2022-05-26 06:49:22'),
(494, 'ol2S459rNIL7GmX7azKAV7NVcPNg', '5dZCOwt6PVF8qEhFsrTMq3jc3rZVIz90', NULL, '2022-05-27 02:59:43'),
(495, 'ol2S455y8mAUwtkwsXnJc48TVn1Q', 'jMyjI_62SpU7teL3i3oXhnTg6FxKgKK9', NULL, '2022-05-28 04:59:51'),
(496, 'ol2S450tIuf88gPb008HePPOBy6Q', 'frviq_z4viLQqsPINOI4wJELF2zc8PK6', NULL, '2022-05-30 04:51:37'),
(497, 'ol2S45y3R1KJKgA9jT5NHE63ygmI', 'M4g5T9RHM7VSP_fr0k40u8lQh1CuUCoq', NULL, '2022-05-30 05:44:30'),
(498, 'ol2S458R9_kMmAh3bLAuLjY0BeGE', 'lF-fZ1WrNyIHGVKQt_7Oei65g55bA6_M', NULL, '2022-05-30 06:38:05'),
(499, 'ol2S45y3R1KJKgA9jT5NHE63ygmI', '1XaVBmt2cF1AwdjyFSI9bKn6ZdEr4wCa', NULL, '2022-06-01 05:52:47'),
(501, 'ol2S459mk7cIvw97ulmaNw6OaGEw', 'nkcTnaBBevihvevA6OUm8S6fI7V9GBlw', NULL, '2022-06-05 07:47:40'),
(502, 'ol2S45-1to1Is851MKQgzUYAKg-0', 'uMeXKfpWs_gvzxDsaLSbR-RKlQsN4MGt', NULL, '2022-06-07 01:01:14'),
(503, 'ol2S45-UBZ1owS9tf7B8hMV72jKk', 'qKHBK-pMC0bNJGTR34cJtD3AW5ES8TPU', NULL, '2022-06-07 03:25:05'),
(504, 'ol2S4570N3i_uWh1l7UiIvJvxnnc', 'dD1SSInF4dhpRZXmijsmE_w10Gv0Qq2-', NULL, '2022-06-07 06:46:17'),
(505, 'ol2S453Dy_7XN5BlM5UIGFbpPxeU', '', NULL, '2022-06-07 11:38:21'),
(506, 'ol2S453Dy_7XN5BlM5UIGFbpPxeU', '8Mx-favoC_6jkulq-OFj7DnfZsB0aTt1', NULL, '2022-06-07 12:02:13'),
(507, 'ol2S456PJ0yyktc0CQTiR3tsGcm8', '', NULL, '2022-06-08 01:01:25'),
(508, 'ol2S454_nzDLIumj6PgB_b0vmbNk', 'L9YchX7VLfOIoOcbo04NoZwYRbGCijA-', NULL, '2022-06-10 01:41:58'),
(509, 'ol2S45wgDuEuy1xI96ptoTPAXTh4', 'OUkM2z9-KnZFm1i-p0AjOQQbLAccIBB8', NULL, '2022-06-10 08:12:28'),
(510, 'ol2S459QTVZUsx_G5RnUfqBHXiis', 'NkDVNz0yI0oe7PH2OvdtCvd1XHK1ad12', NULL, '2022-06-10 09:59:07'),
(511, 'ol2S450TwhhYKOC_6LYIwbzelA-c', 'AEpIrGFaE_5j817ora1uQvhih3HPXwNY', NULL, '2022-06-13 06:21:51'),
(512, 'ol2S452AbvJ1MQmi7Iew6sYlYDLA', 'NmSMJOlwU6z7oT6Jcdf7aQSRhNzyslkX', NULL, '2022-06-13 06:30:44'),
(513, 'ol2S454YBCY2f5oAEXi_o-18UcZQ', 'p-We0kc3tuBPrwPZHxz43W2QQKuj_cNN', NULL, '2022-06-13 08:51:40'),
(514, 'ol2S458xCaZd5C7XtV5MfvRuYOeI', '', NULL, '2022-06-13 09:53:46'),
(515, 'ol2S456UH9zS4K7N9X2vS28QYllI', '', NULL, '2022-06-13 10:07:54'),
(516, 'ol2S451dCSKjKQYfmhTapQ7_D_HY', '', NULL, '2022-06-13 10:33:38'),
(517, 'ol2S45w89_-mFDrOI6E6AyCE7dak', '', NULL, '2022-06-13 10:55:24'),
(518, 'ol2S452_HatnqIL-E8mpQm18F6FA', '', NULL, '2022-06-13 10:57:31'),
(519, 'ol2S450yMIzTWrwNC1WrYS0-jKrk', '', NULL, '2022-06-13 11:07:30'),
(520, 'ol2S45xZdDBcisE3LFFXWBHu3n14', '', NULL, '2022-06-13 11:15:26'),
(521, 'ol2S4592BKL1QjI5yjMWrPqFSGvU', '', NULL, '2022-06-13 12:36:32'),
(522, 'ol2S458UFdNqfZanwydU0QDzA8Ig', '', NULL, '2022-06-13 13:00:55'),
(523, 'ol2S45140ID0sEGfpuJ6RyiFjqM0', '', NULL, '2022-06-13 13:08:26'),
(524, 'ol2S456iAXGFI78WhQyVYExKQcBc', '', NULL, '2022-06-13 13:24:02'),
(525, 'ol2S454Vzc8c9CyDw-LOAV8MJ42c', '', NULL, '2022-06-13 14:00:01'),
(526, 'ol2S4506XZkINpA7BW8qBdV9-hqk', '', NULL, '2022-06-13 14:04:40'),
(527, 'ol2S454WWA5Q7c8rwpDikib7dvhw', '', NULL, '2022-06-13 14:34:25'),
(528, 'ol2S456b16GqpVf9rg6DnMhyIzeA', '', NULL, '2022-06-13 15:04:23'),
(529, 'ol2S451mvenAc0ofEsTCZNvPwApc', '', NULL, '2022-06-13 15:31:52'),
(530, 'ol2S451mvenAc0ofEsTCZNvPwApc', 'obnz4tzpwNfK3Fu6-Wjr5WT3x3kxjnQK', NULL, '2022-06-13 15:32:22'),
(531, 'ol2S453ZXR7hYps3qlKkkJ8tDf2w', '', NULL, '2022-06-13 18:22:07'),
(532, 'ol2S45yrx1XYpVGlMmXkiB8jR7yI', '', NULL, '2022-06-13 21:20:42'),
(533, 'ol2S452LZrBo3NRiuJ251_9NcgV4', '', NULL, '2022-06-13 23:36:53'),
(534, 'ol2S455cLbNj4O_eOCu-UEBvknwQ', '', NULL, '2022-06-14 00:53:59'),
(535, 'ol2S45yB64YSSWK5aWEo_NENrdv8', '', NULL, '2022-06-14 02:57:24'),
(536, 'ol2S450_bCROZKrF0KjNfKZv3MIE', '', NULL, '2022-06-14 06:01:07'),
(537, 'ol2S45z60k2JkpO71rmjGVpoM5cs', '', NULL, '2022-06-14 06:38:47'),
(539, 'ol2S45wb62fxX9CbMkksMZpFP7ZA', 'aTSLqHcyu6UgQslUelwV6w3v2DI76hLd', NULL, '2022-06-14 08:14:26'),
(540, 'ol2S454IlCFLgJRiQibghK-N12yU', '', NULL, '2022-06-14 08:41:56'),
(541, 'ol2S45yQ4jNhumOESy3iVfd-MpSM', '', NULL, '2022-06-14 08:48:42'),
(543, 'ol2S45y7bWpFFEeTAcyVZH252TA0', '', NULL, '2022-06-14 09:40:58'),
(544, 'ol2S456RThUGkN7_l1fId7VGVPy4', '', NULL, '2022-06-14 12:19:20');

--
-- 转储表的索引
--

--
-- 表的索引 `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD PRIMARY KEY (`item_name`,`user_id`),
  ADD KEY `idx-auth_assignment-user_id` (`user_id`);

--
-- 表的索引 `auth_item`
--
ALTER TABLE `auth_item`
  ADD PRIMARY KEY (`name`),
  ADD KEY `rule_name` (`rule_name`),
  ADD KEY `idx-auth_item-type` (`type`);

--
-- 表的索引 `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD PRIMARY KEY (`parent`,`child`),
  ADD KEY `child` (`child`);

--
-- 表的索引 `auth_rule`
--
ALTER TABLE `auth_rule`
  ADD PRIMARY KEY (`name`);

--
-- 表的索引 `cyber`
--
ALTER TABLE `cyber`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-cyber-author_id` (`author_id`),
  ADD KEY `idx-cyber-updater_id` (`updater_id`),
  ADD KEY `idx-cyber-meta_id` (`meta_id`);

--
-- 表的索引 `cyber_script`
--
ALTER TABLE `cyber_script`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx-cyber_script-cyber_id-language` (`cyber_id`,`language`),
  ADD KEY `idx-cyber_script-cyber_id` (`cyber_id`);

--
-- 表的索引 `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-file-user_id` (`user_id`),
  ADD KEY `idx-file-md5` (`md5`),
  ADD KEY `idx-file-url` (`url`),
  ADD KEY `idx-file-filename` (`filename`),
  ADD KEY `idx-file-key` (`key`);

--
-- 表的索引 `info`
--
ALTER TABLE `info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tel` (`tel`);

--
-- 表的索引 `invitation`
--
ALTER TABLE `invitation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx-post-sender_id` (`sender_id`),
  ADD KEY `fk-post-recipient_id` (`recipient_id`),
  ADD KEY `idx-invitation-auth_item_name` (`auth_item_name`);

--
-- 表的索引 `knight`
--
ALTER TABLE `knight`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-knight-author_id` (`author_id`),
  ADD KEY `idx-knight-updater_id` (`updater_id`),
  ADD KEY `idx-knight-mesh_id` (`mesh_id`),
  ADD KEY `idx-knight-image_id` (`image_id`);

--
-- 表的索引 `like`
--
ALTER TABLE `like`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-like-user_id` (`user_id`),
  ADD KEY `idx-like-message_id` (`message_id`);

--
-- 表的索引 `local`
--
ALTER TABLE `local`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- 表的索引 `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent` (`parent`);

--
-- 表的索引 `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-message-author_id` (`author_id`),
  ADD KEY `idx-message-updater_id` (`updater_id`);

--
-- 表的索引 `message_tags`
--
ALTER TABLE `message_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-message_tags-message_id` (`message_id`),
  ADD KEY `idx-message_tags-tag_id` (`tag_id`);

--
-- 表的索引 `meta`
--
ALTER TABLE `meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx-meta-verse_id` (`verse_id`),
  ADD KEY `idx-meta-author_id` (`author_id`),
  ADD KEY `idx-meta-updater_id` (`updater_id`),
  ADD KEY `idx-meta-image_id` (`image_id`);

--
-- 表的索引 `meta_event`
--
ALTER TABLE `meta_event`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meta_id` (`meta_id`),
  ADD KEY `idx-meta_event-meta_id` (`meta_id`);

--
-- 表的索引 `meta_knight`
--
ALTER TABLE `meta_knight`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx-meta_knight-verse_id` (`verse_id`),
  ADD KEY `idx-meta_knight-knight_id` (`knight_id`),
  ADD KEY `idx-meta_knight-user_id` (`user_id`);

--
-- 表的索引 `meta_resource`
--
ALTER TABLE `meta_resource`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-meta_resource-meta_id` (`meta_id`),
  ADD KEY `idx-meta_resource-resource_id` (`resource_id`);

--
-- 表的索引 `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- 表的索引 `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `prepay_id` (`prepay_id`),
  ADD KEY `idx-order-trade_id` (`trade_id`),
  ADD KEY `idx-order-user_id` (`user_id`);

--
-- 表的索引 `reply`
--
ALTER TABLE `reply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-reply-message_id` (`author_id`),
  ADD KEY `fk-reply-message_id` (`message_id`),
  ADD KEY `idx-reply-author_id` (`author_id`),
  ADD KEY `idx-reply-updater_id` (`updater_id`);

--
-- 表的索引 `resource`
--
ALTER TABLE `resource`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx-resource-type` (`type`),
  ADD KEY `idx-resource-author_id` (`author_id`),
  ADD KEY `idx-resource-file_id` (`file_id`),
  ADD KEY `idx-resource-image_id` (`image_id`),
  ADD KEY `idx-resource-updater_id` (`updater_id`);

--
-- 表的索引 `space`
--
ALTER TABLE `space`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-space-author_id` (`author_id`),
  ADD KEY `idx-space-sample_id` (`sample_id`),
  ADD KEY `idx-space-mesh_id` (`mesh_id`),
  ADD KEY `idx-space-dat_id` (`dat_id`),
  ADD KEY `idx-space-image_id` (`image_id`);

--
-- 表的索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- 表的索引 `trade`
--
ALTER TABLE `trade`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `out_trade_no` (`out_trade_no`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `password_reset_token` (`password_reset_token`),
  ADD KEY `user-wx_openid` (`wx_openid`);

--
-- 表的索引 `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-user_info-user_id` (`user_id`),
  ADD KEY `idx-user_info-avatar_id` (`avatar_id`);

--
-- 表的索引 `verse`
--
ALTER TABLE `verse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse-author_id` (`author_id`),
  ADD KEY `idx-verse-updater_id` (`updater_id`),
  ADD KEY `idx-verse-image_id` (`image_id`);

--
-- 表的索引 `verse_event`
--
ALTER TABLE `verse_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_event-verse_id` (`verse_id`);

--
-- 表的索引 `verse_knight`
--
ALTER TABLE `verse_knight`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_knight-verse_id` (`verse_id`),
  ADD KEY `idx-verse_knight-knight_id` (`knight_id`);

--
-- 表的索引 `verse_open`
--
ALTER TABLE `verse_open`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_open-verse_id` (`verse_id`),
  ADD KEY `idx-verse_open-user_id` (`user_id`),
  ADD KEY `idx-verse_open-message_id` (`message_id`);

--
-- 表的索引 `verse_share`
--
ALTER TABLE `verse_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_share-verse_id` (`verse_id`),
  ADD KEY `idx-verse_share-user_id` (`user_id`);

--
-- 表的索引 `verse_space`
--
ALTER TABLE `verse_space`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_space-verse_id` (`verse_id`),
  ADD KEY `idx-verse_space-space_id` (`space_id`);

--
-- 表的索引 `wx`
--
ALTER TABLE `wx`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-wx-user_id` (`user_id`),
  ADD KEY `idx-wx-token` (`token`),
  ADD KEY `idx-wx-wx_openid` (`wx_openid`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `cyber`
--
ALTER TABLE `cyber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- 使用表AUTO_INCREMENT `cyber_script`
--
ALTER TABLE `cyber_script`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- 使用表AUTO_INCREMENT `file`
--
ALTER TABLE `file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1926;

--
-- 使用表AUTO_INCREMENT `info`
--
ALTER TABLE `info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- 使用表AUTO_INCREMENT `invitation`
--
ALTER TABLE `invitation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- 使用表AUTO_INCREMENT `knight`
--
ALTER TABLE `knight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `like`
--
ALTER TABLE `like`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- 使用表AUTO_INCREMENT `local`
--
ALTER TABLE `local`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- 使用表AUTO_INCREMENT `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- 使用表AUTO_INCREMENT `message_tags`
--
ALTER TABLE `message_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- 使用表AUTO_INCREMENT `meta`
--
ALTER TABLE `meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=562;

--
-- 使用表AUTO_INCREMENT `meta_event`
--
ALTER TABLE `meta_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- 使用表AUTO_INCREMENT `meta_knight`
--
ALTER TABLE `meta_knight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- 使用表AUTO_INCREMENT `meta_resource`
--
ALTER TABLE `meta_resource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- 使用表AUTO_INCREMENT `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `reply`
--
ALTER TABLE `reply`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- 使用表AUTO_INCREMENT `resource`
--
ALTER TABLE `resource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=675;

--
-- 使用表AUTO_INCREMENT `space`
--
ALTER TABLE `space`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `token`
--
ALTER TABLE `token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `trade`
--
ALTER TABLE `trade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=496;

--
-- 使用表AUTO_INCREMENT `user_info`
--
ALTER TABLE `user_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- 使用表AUTO_INCREMENT `verse`
--
ALTER TABLE `verse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=273;

--
-- 使用表AUTO_INCREMENT `verse_event`
--
ALTER TABLE `verse_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- 使用表AUTO_INCREMENT `verse_knight`
--
ALTER TABLE `verse_knight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `verse_open`
--
ALTER TABLE `verse_open`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `verse_share`
--
ALTER TABLE `verse_share`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `verse_space`
--
ALTER TABLE `verse_space`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wx`
--
ALTER TABLE `wx`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=545;

--
-- 限制导出的表
--

--
-- 限制表 `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `auth_item`
--
ALTER TABLE `auth_item`
  ADD CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 限制表 `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `cyber`
--
ALTER TABLE `cyber`
  ADD CONSTRAINT `fk-cyber-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-cyber-meta_id` FOREIGN KEY (`meta_id`) REFERENCES `meta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-cyber-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `cyber_script`
--
ALTER TABLE `cyber_script`
  ADD CONSTRAINT `fk-cyber_script-cyber_id` FOREIGN KEY (`cyber_id`) REFERENCES `cyber` (`id`) ON DELETE CASCADE;

--
-- 限制表 `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `fk-file-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `invitation`
--
ALTER TABLE `invitation`
  ADD CONSTRAINT `fk-invitation-auth_item_name` FOREIGN KEY (`auth_item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-post-recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-post-sender_id` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `knight`
--
ALTER TABLE `knight`
  ADD CONSTRAINT `fk-knight-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-knight-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-knight-mesh_id` FOREIGN KEY (`mesh_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-knight-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `like`
--
ALTER TABLE `like`
  ADD CONSTRAINT `fk-like-message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-like-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 限制表 `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk-message-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-message-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `message_tags`
--
ALTER TABLE `message_tags`
  ADD CONSTRAINT `fk-message_tags-message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-message_tags-tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- 限制表 `meta`
--
ALTER TABLE `meta`
  ADD CONSTRAINT `fk-meta-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `meta_event`
--
ALTER TABLE `meta_event`
  ADD CONSTRAINT `fk-meta_event-meta_id` FOREIGN KEY (`meta_id`) REFERENCES `meta` (`id`) ON DELETE CASCADE;

--
-- 限制表 `meta_knight`
--
ALTER TABLE `meta_knight`
  ADD CONSTRAINT `fk-meta_knight-knight_id` FOREIGN KEY (`knight_id`) REFERENCES `knight` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta_knight-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta_knight-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `meta_resource`
--
ALTER TABLE `meta_resource`
  ADD CONSTRAINT `fk-meta_resource-meta_id` FOREIGN KEY (`meta_id`) REFERENCES `meta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta_resource-resource_id` FOREIGN KEY (`resource_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE;

--
-- 限制表 `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `fk-order-trade_id` FOREIGN KEY (`trade_id`) REFERENCES `trade` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-order-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `reply`
--
ALTER TABLE `reply`
  ADD CONSTRAINT `fk-reply-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-reply-message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-reply-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `resource`
--
ALTER TABLE `resource`
  ADD CONSTRAINT `fk-resource-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-resource-file_id` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-resource-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-resource-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `space`
--
ALTER TABLE `space`
  ADD CONSTRAINT `fk-space-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-space-dat_id` FOREIGN KEY (`dat_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-space-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-space-mesh_id` FOREIGN KEY (`mesh_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-space-sample_id` FOREIGN KEY (`sample_id`) REFERENCES `file` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `fk-user_info-avatar_id` FOREIGN KEY (`avatar_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-user_info-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse`
--
ALTER TABLE `verse`
  ADD CONSTRAINT `fk-verse-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_event`
--
ALTER TABLE `verse_event`
  ADD CONSTRAINT `fk-verse_event-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_knight`
--
ALTER TABLE `verse_knight`
  ADD CONSTRAINT `fk-verse_knight-knight_id` FOREIGN KEY (`knight_id`) REFERENCES `knight` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_knight-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_open`
--
ALTER TABLE `verse_open`
  ADD CONSTRAINT `fk-verse_open-message_id` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_open-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_open-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_share`
--
ALTER TABLE `verse_share`
  ADD CONSTRAINT `fk-verse_share-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_share-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_space`
--
ALTER TABLE `verse_space`
  ADD CONSTRAINT `fk-verse_space-space_id` FOREIGN KEY (`space_id`) REFERENCES `space` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_space-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `wx`
--
ALTER TABLE `wx`
  ADD CONSTRAINT `fk-wx-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
