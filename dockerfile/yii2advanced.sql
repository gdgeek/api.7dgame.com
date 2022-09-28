-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： db
-- 生成日期： 2022-02-25 06:30:14
-- 服务器版本： 10.4.21-MariaDB-1:10.4.21+maria~focal
-- PHP 版本： 7.4.20

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
  `item_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_assignment`
--

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
('@manager/*', '3', 1643286771),
('@manager/admin/*', '3', 1643286771),
('@restful/*', '3', 1643286776),
('admin', '21', 1559195883),
('developer', '316', 1643073243),
('root', '3', 1558602550),
('user', '100', 1568813347),
('user', '101', 1568813672),
('user', '102', 1568877704),
('user', '103', 1568941282),
('user', '104', 1568949442),
('user', '106', 1569329098),
('user', '107', 1569497134),
('user', '108', 1569722498),
('user', '109', 1570446906),
('user', '110', 1570589946),
('user', '111', 1570590491),
('user', '113', 1571049254),
('user', '114', 1571191205),
('user', '115', 1571367774),
('user', '116', 1571914778),
('user', '117', 1571593208),
('user', '118', 1571619821),
('user', '119', 1571636418),
('user', '120', 1571789372),
('user', '121', 1571793087),
('user', '122', 1572174744),
('user', '123', 1572232394),
('user', '124', 1572428561),
('user', '125', 1572579261),
('user', '127', 1573177223),
('user', '128', 1573184146),
('user', '129', 1573484733),
('user', '130', 1573540967),
('user', '131', 1573546536),
('user', '132', 1573816958),
('user', '134', 1573713605),
('user', '135', 1573981435),
('user', '136', 1574043168),
('user', '138', 1574321629),
('user', '139', 1574652504),
('user', '144', 1574342593),
('user', '145', 1574665574),
('user', '146', 1574665568),
('user', '149', 1574665910),
('user', '150', 1574665941),
('user', '184', 1574652627),
('user', '186', 1574731922),
('user', '188', 1574667213),
('user', '189', 1574669962),
('user', '190', 1574670498),
('user', '191', 1574670562),
('user', '193', 1574675128),
('user', '200', 1574671032),
('user', '201', 1574678282),
('user', '202', 1574684335),
('user', '211', 1574732053),
('user', '212', 1574732467),
('user', '216', 1574738004),
('user', '217', 1574738962),
('user', '218', 1574738667),
('user', '219', 1575260295),
('user', '22', 1572580381),
('user', '221', 1575334824),
('user', '222', 1575334688),
('user', '225', 1575334976),
('user', '227', 1575335041),
('user', '228', 1575335161),
('user', '23', 1558664928),
('user', '231', 1575620531),
('user', '233', 1575876185),
('user', '234', 1575948990),
('user', '235', 1576068209),
('user', '236', 1576323774),
('user', '237', 1577325549),
('user', '238', 1577863394),
('user', '24', 1558664948),
('user', '25', 1644632228),
('user', '26', 1561880991),
('user', '264', 1634181880),
('user', '265', 1634182124),
('user', '28', 1561885940),
('user', '285', 1637141982),
('user', '288', 1638320637),
('user', '289', 1638414778),
('user', '29', 1561901224),
('user', '290', 1638430982),
('user', '291', 1638527047),
('user', '292', 1638585511),
('user', '293', 1638589285),
('user', '3', 1641975720),
('user', '30', 1561942890),
('user', '302', 1638855291),
('user', '303', 1639230411),
('user', '304', 1639238115),
('user', '306', 1639291540),
('user', '307', 1639371964),
('user', '308', 1639426946),
('user', '309', 1639451485),
('user', '310', 1639472210),
('user', '311', 1639491140),
('user', '313', 1639879760),
('user', '314', 1639887497),
('user', '315', 1639988592),
('user', '316', 1640189352),
('user', '317', 1640249777),
('user', '318', 1640395702),
('user', '319', 1640401986),
('user', '32', 1561944782),
('user', '320', 1640426094),
('user', '321', 1640494480),
('user', '322', 1640521027),
('user', '323', 1640525612),
('user', '324', 1640657700),
('user', '325', 1640762654),
('user', '326', 1640871777),
('user', '328', 1641314899),
('user', '329', 1641436806),
('user', '33', 1561947325),
('user', '330', 1641734129),
('user', '331', 1641740154),
('user', '332', 1641780256),
('user', '333', 1641783331),
('user', '336', 1643623092),
('user', '337', 1643645909),
('user', '338', 1643767313),
('user', '339', 1643798924),
('user', '34', 1561947473),
('user', '340', 1643809421),
('user', '343', 1643858978),
('user', '344', 1643940629),
('user', '345', 1644032673),
('user', '346', 1644058335),
('user', '347', 1644150026),
('user', '348', 1644242503),
('user', '349', 1644286288),
('user', '35', 1561949211),
('user', '350', 1644297660),
('user', '351', 1644379563),
('user', '352', 1644414745),
('user', '353', 1644551515),
('user', '354', 1644555154),
('user', '355', 1644587656),
('user', '356', 1644651773),
('user', '357', 1644655456),
('user', '358', 1644659529),
('user', '359', 1644831797),
('user', '36', 1561988415),
('user', '360', 1644856306),
('user', '361', 1644912308),
('user', '362', 1644996977),
('user', '363', 1645084979),
('user', '364', 1645089475),
('user', '365', 1645108010),
('user', '367', 1645220027),
('user', '368', 1645515010),
('user', '37', 1561992923),
('user', '38', 1562140569),
('user', '40', 1562291851),
('user', '41', 1563249708),
('user', '42', 1563268682),
('user', '43', 1563785579),
('user', '44', 1564543467),
('user', '45', 1564711217),
('user', '46', 1564713719),
('user', '47', 1564714873),
('user', '48', 1564715128),
('user', '49', 1565584268),
('user', '50', 1565066863),
('user', '51', 1565075006),
('user', '52', 1565139068),
('user', '53', 1565252935),
('user', '54', 1565579370),
('user', '55', 1566548437),
('user', '56', 1566570893),
('user', '57', 1566593199),
('user', '58', 1566652590),
('user', '59', 1566831717),
('user', '60', 1566896569),
('user', '61', 1566982894),
('user', '62', 1567019283),
('user', '64', 1567060743),
('user', '65', 1567130593),
('user', '66', 1567132857),
('user', '67', 1567137445),
('user', '68', 1567152384),
('user', '69', 1567164203),
('user', '71', 1567205314),
('user', '72', 1567216171),
('user', '73', 1567219728),
('user', '74', 1567221360),
('user', '75', 1567235695),
('user', '77', 1567294831),
('user', '78', 1567305661),
('user', '79', 1567385540),
('user', '80', 1567489475),
('user', '81', 1567492709),
('user', '82', 1567664211),
('user', '83', 1567705929),
('user', '84', 1567734113),
('user', '86', 1567734336),
('user', '88', 1567789761),
('user', '89', 1567871511),
('user', '90', 1568123246),
('user', '91', 1568629781),
('user', '92', 1568645700),
('user', '93', 1568685209),
('user', '94', 1568691402),
('user', '95', 1568696700),
('user', '96', 1568697918),
('user', '98', 1568777290),
('user', '99', 1568794451),
('基本权限', '3', 1643298949),
('工程管理', '3', 1643298949),
('权限管理', '3', 1643298949),
('社交基本权限', '3', 1643298949),
('私有资源', '3', 1643298949),
('网站开发', '3', 1643298949),
('网站管理', '3', 1643298949),
('资源管理', '3', 1643298949);

-- --------------------------------------------------------

--
-- 表的结构 `auth_item`
--

CREATE TABLE `auth_item` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `rule_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_item`
--

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('@manager/*', 2, NULL, NULL, NULL, 1643275290, 1643275290),
('@manager/admin/*', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/assignment/*', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/assignment/assign', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/assignment/index', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/assignment/revoke', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/assignment/view', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/default/*', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/default/index', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/menu/*', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/menu/create', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/menu/delete', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/menu/index', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/menu/update', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/menu/view', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/permission/*', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/permission/assign', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/permission/create', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/permission/delete', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/permission/get-users', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/permission/index', 2, NULL, NULL, NULL, 1643275303, 1643275303),
('@manager/admin/permission/remove', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/permission/update', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/permission/view', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/*', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/assign', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/create', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/delete', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/get-users', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/index', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/remove', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/update', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/role/view', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/route/*', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/route/assign', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/route/create', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/route/index', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/route/refresh', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/route/remove', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/rule/*', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/rule/create', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/rule/delete', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/rule/index', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/rule/update', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/rule/view', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/*', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/activate', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/change-password', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/delete', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/index', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/login', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/logout', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/request-password-reset', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/reset-password', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/signup', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/admin/user/view', 2, NULL, NULL, NULL, 1643275304, 1643275304),
('@manager/document/*', 2, NULL, NULL, NULL, 1643283064, 1643283064),
('@manager/document/index', 2, NULL, NULL, NULL, 1643283064, 1643283064),
('@manager/document/info', 2, NULL, NULL, NULL, 1643283064, 1643283064),
('@manager/document/privacy', 2, NULL, NULL, NULL, 1643283064, 1643283064),
('@manager/document/school', 2, NULL, NULL, NULL, 1643283064, 1643283064),
('@manager/gii/*', 2, NULL, NULL, NULL, 1643283755, 1643283755),
('@manager/gii/default/*', 2, NULL, NULL, NULL, 1643283755, 1643283755),
('@manager/gii/default/action', 2, NULL, NULL, NULL, 1643283755, 1643283755),
('@manager/gii/default/diff', 2, NULL, NULL, NULL, 1643283755, 1643283755),
('@manager/gii/default/index', 2, NULL, NULL, NULL, 1643283755, 1643283755),
('@manager/gii/default/preview', 2, NULL, NULL, NULL, 1643283755, 1643283755),
('@manager/gii/default/view', 2, NULL, NULL, NULL, 1643283755, 1643283755),
('@manager/site/logout', 2, NULL, NULL, NULL, 1643283785, 1643283785),
('@manager/tags/*', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@manager/tags/create', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@manager/tags/delete', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@manager/tags/index', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@manager/tags/update', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@manager/tags/view', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@manager/test-it/*', 2, NULL, NULL, NULL, 1643283770, 1643283770),
('@manager/test-it/index', 2, NULL, NULL, NULL, 1643283770, 1643283770),
('@manager/test-it/test', 2, NULL, NULL, NULL, 1643283770, 1643283770),
('@restful/*', 2, NULL, NULL, NULL, 1643283812, 1643283812),
('@restful/file/*', 2, NULL, NULL, NULL, 1643283827, 1643283827),
('@restful/file/create', 2, NULL, NULL, NULL, 1643283827, 1643283827),
('@restful/file/delete', 2, NULL, NULL, NULL, 1643283827, 1643283827),
('@restful/file/index', 2, NULL, NULL, NULL, 1643283827, 1643283827),
('@restful/file/options', 2, NULL, NULL, NULL, 1643283827, 1643283827),
('@restful/file/update', 2, NULL, NULL, NULL, 1643283827, 1643283827),
('@restful/file/view', 2, NULL, NULL, NULL, 1643283827, 1643283827),
('@restful/menu/*', 2, NULL, NULL, NULL, 1643283834, 1643283834),
('@restful/menu/index', 2, NULL, NULL, NULL, 1643283834, 1643283834),
('@restful/menu/options', 2, NULL, NULL, NULL, 1643283834, 1643283834),
('@restful/menu/update', 2, NULL, NULL, NULL, 1643283834, 1643283834),
('@restful/menu/view', 2, NULL, NULL, NULL, 1643283834, 1643283834),
('@restful/resource/*', 2, NULL, NULL, NULL, 1643283846, 1643283846),
('@restful/resource/create', 2, NULL, NULL, NULL, 1643283846, 1643283846),
('@restful/resource/delete', 2, NULL, NULL, NULL, 1643283846, 1643283846),
('@restful/resource/index', 2, NULL, NULL, NULL, 1643283846, 1643283846),
('@restful/resource/options', 2, NULL, NULL, NULL, 1643283846, 1643283846),
('@restful/resource/update', 2, NULL, NULL, NULL, 1643283846, 1643283846),
('@restful/resource/view', 2, NULL, NULL, NULL, 1643283846, 1643283846),
('@restful/server/*', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/server/bind-email', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/server/logout', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/server/menu', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/server/reset-password', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/server/sts', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/server/token', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/server/user', 2, NULL, NULL, NULL, 1643283857, 1643283857),
('@restful/site/*', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/binded-email', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/error', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/login', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/menu', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/password-reset-token', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/request-password-reset', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/resend-verification-email', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/reset-password', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/signup', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/test', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/verify-email', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/wechat-openid', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/site/wechat-qrcode', 2, NULL, NULL, NULL, 1643283877, 1643283877),
('@restful/v1/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/message-tags/*', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/message-tags/create', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/message-tags/delete', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/message-tags/index', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/message-tags/options', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/message-tags/update', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/message-tags/view', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/message/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/message/create', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/message/delete', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/message/index', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/message/options', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/message/update', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/message/view', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta-verse/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta-verse/create', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta-verse/delete', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta-verse/index', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta-verse/options', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta-verse/update', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta-verse/view', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta/create', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta/delete', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta/index', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta/options', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta/update', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/meta/view', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/reply/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/reply/create', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/reply/delete', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/reply/index', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/reply/options', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/reply/update', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/reply/view', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/tags/*', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/tags/index', 2, NULL, NULL, NULL, 1645164651, 1645164651),
('@restful/v1/user/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/user/get-data', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/user/set-data', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse-cyber/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse-cyber/create', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse-cyber/delete', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse-cyber/index', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse-cyber/options', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse-cyber/update', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse-cyber/view', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse/*', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse/create', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse/delete', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse/index', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse/options', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse/update', 2, NULL, NULL, NULL, 1643283893, 1643283893),
('@restful/v1/verse/view', 2, NULL, NULL, NULL, 1643283893, 1643283893),
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
('admin', 1, '管理员', NULL, NULL, 1552152101, 1643718326),
('developer', 1, '开发者，破冰用户', NULL, NULL, 1559652693, 1643286294),
('manager', 1, '网站管理者', NULL, NULL, 1643284050, 1643286320),
('root', 1, '超级管理员', NULL, NULL, 1557226615, 1644504326),
('user', 1, '基本权限,  模型管理，工程管理', NULL, NULL, 1552196894, 1645166849),
('基本权限', 2, '针对登录用户的权限', NULL, NULL, 1552196910, 1644634130),
('增减Message标签', 2, '用户给有管理权限的文章增加或者减少标签', NULL, NULL, 1645166823, 1645173297),
('工程管理', 2, '模型，project，和编辑器的权限', NULL, NULL, 1557226453, 1644633913),
('权限管理', 2, '用户权限管理', NULL, NULL, 1552195104, 1643285356),
('标签列表', 2, '列出平台所有支持标签和信息', NULL, NULL, 1645166342, 1645545163),
('标签管理', 2, NULL, NULL, NULL, 1645164670, 1645164670),
('社交基本权限', 2, '社交权限', NULL, NULL, 1552152220, 1643285437),
('社区权限', 2, '社区使用者', NULL, NULL, 1644634003, 1644634094),
('私有信息', 2, '用户处理自己私有的信息', '私有信息', NULL, 1644634051, 1644634051),
('私有元', 2, '私有元数据管理', '私有元', NULL, 1644632409, 1644633760),
('私有回复', 2, '自己的回复信息', '私有回复', NULL, 1644634078, 1644634078),
('私有宇宙', 2, '自由宇宙管理', '私有宇宙', NULL, 1644632157, 1644633726),
('私有资源', 2, '针对自己的资源才能操作的权限', '资源', NULL, 1568996567, 1644513826),
('私有赛博', 2, '赛博私有管理', '私有赛博', NULL, 1644632368, 1644632368),
('网站开发', 2, 'gii编辑，只有root用户能用这个功能', NULL, NULL, 1558630136, 1643285958),
('网站管理', 2, '网站管理，邀请码，等', NULL, NULL, 1572581469, 1643286032),
('资源管理', 2, '所有的资源', NULL, NULL, 1643286078, 1644632192);

-- --------------------------------------------------------

--
-- 表的结构 `auth_item_child`
--

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_item_child`
--

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('admin', '权限管理'),
('admin', '网站开发'),
('manager', '网站管理'),
('root', '@manager/*'),
('root', '@restful/*'),
('user', '基本权限'),
('user', '增减Message标签'),
('user', '工程管理'),
('user', '标签列表'),
('user', '社交基本权限'),
('user', '社区权限'),
('user', '资源管理'),
('基本权限', '@manager/admin/user/logout'),
('基本权限', '@manager/document/*'),
('基本权限', '@manager/document/index'),
('基本权限', '@manager/document/info'),
('基本权限', '@manager/document/privacy'),
('基本权限', '@manager/document/school'),
('基本权限', '@manager/site/logout'),
('基本权限', '@restful/server/bind-email'),
('基本权限', '@restful/server/logout'),
('基本权限', '@restful/server/menu'),
('基本权限', '@restful/server/reset-password'),
('基本权限', '@restful/server/sts'),
('基本权限', '@restful/server/token'),
('基本权限', '@restful/server/user'),
('基本权限', '@restful/site/binded-email'),
('基本权限', '@restful/site/error'),
('基本权限', '@restful/site/login'),
('基本权限', '@restful/site/menu'),
('基本权限', '@restful/site/password-reset-token'),
('基本权限', '@restful/site/request-password-reset'),
('基本权限', '@restful/site/resend-verification-email'),
('基本权限', '@restful/site/reset-password'),
('基本权限', '@restful/site/signup'),
('基本权限', '@restful/site/test'),
('基本权限', '@restful/site/verify-email'),
('基本权限', '@restful/site/wechat-openid'),
('基本权限', '@restful/site/wechat-qrcode'),
('基本权限', '@restful/v1/user/get-data'),
('基本权限', '@restful/v1/user/set-data'),
('基本权限', '/site/logout'),
('增减Message标签', '@restful/v1/message-tags/create'),
('增减Message标签', '@restful/v1/message-tags/delete'),
('增减Message标签', '@restful/v1/message-tags/options'),
('增减Message标签', '@restful/v1/message-tags/update'),
('工程管理', '@restful/v1/meta-verse/index'),
('工程管理', '@restful/v1/meta-verse/options'),
('工程管理', '@restful/v1/meta-verse/view'),
('工程管理', '@restful/v1/meta/create'),
('工程管理', '@restful/v1/meta/index'),
('工程管理', '@restful/v1/meta/options'),
('工程管理', '@restful/v1/meta/view'),
('工程管理', '@restful/v1/verse-cyber/create'),
('工程管理', '@restful/v1/verse-cyber/index'),
('工程管理', '@restful/v1/verse-cyber/options'),
('工程管理', '@restful/v1/verse-cyber/view'),
('工程管理', '@restful/v1/verse/create'),
('工程管理', '@restful/v1/verse/index'),
('工程管理', '@restful/v1/verse/options'),
('工程管理', '@restful/v1/verse/view'),
('工程管理', '私有元'),
('工程管理', '私有宇宙'),
('工程管理', '私有赛博'),
('权限管理', '@manager/admin/*'),
('权限管理', '@manager/admin/assignment/*'),
('权限管理', '@manager/admin/assignment/assign'),
('权限管理', '@manager/admin/assignment/index'),
('权限管理', '@manager/admin/assignment/revoke'),
('权限管理', '@manager/admin/assignment/view'),
('权限管理', '@manager/admin/default/*'),
('权限管理', '@manager/admin/default/index'),
('权限管理', '@manager/admin/menu/*'),
('权限管理', '@manager/admin/menu/create'),
('权限管理', '@manager/admin/menu/delete'),
('权限管理', '@manager/admin/menu/index'),
('权限管理', '@manager/admin/menu/update'),
('权限管理', '@manager/admin/menu/view'),
('权限管理', '@manager/admin/permission/*'),
('权限管理', '@manager/admin/permission/assign'),
('权限管理', '@manager/admin/permission/create'),
('权限管理', '@manager/admin/permission/delete'),
('权限管理', '@manager/admin/permission/get-users'),
('权限管理', '@manager/admin/permission/index'),
('权限管理', '@manager/admin/permission/remove'),
('权限管理', '@manager/admin/permission/update'),
('权限管理', '@manager/admin/permission/view'),
('权限管理', '@manager/admin/role/*'),
('权限管理', '@manager/admin/role/assign'),
('权限管理', '@manager/admin/role/create'),
('权限管理', '@manager/admin/role/delete'),
('权限管理', '@manager/admin/role/get-users'),
('权限管理', '@manager/admin/role/index'),
('权限管理', '@manager/admin/role/remove'),
('权限管理', '@manager/admin/role/update'),
('权限管理', '@manager/admin/role/view'),
('权限管理', '@manager/admin/route/*'),
('权限管理', '@manager/admin/route/assign'),
('权限管理', '@manager/admin/route/create'),
('权限管理', '@manager/admin/route/index'),
('权限管理', '@manager/admin/route/refresh'),
('权限管理', '@manager/admin/route/remove'),
('权限管理', '@manager/admin/rule/*'),
('权限管理', '@manager/admin/rule/create'),
('权限管理', '@manager/admin/rule/delete'),
('权限管理', '@manager/admin/rule/index'),
('权限管理', '@manager/admin/rule/update'),
('权限管理', '@manager/admin/rule/view'),
('权限管理', '@manager/admin/user/*'),
('权限管理', '@manager/admin/user/activate'),
('权限管理', '@manager/admin/user/change-password'),
('权限管理', '@manager/admin/user/delete'),
('权限管理', '@manager/admin/user/index'),
('权限管理', '@manager/admin/user/login'),
('权限管理', '@manager/admin/user/logout'),
('权限管理', '@manager/admin/user/request-password-reset'),
('权限管理', '@manager/admin/user/reset-password'),
('权限管理', '@manager/admin/user/signup'),
('权限管理', '@manager/admin/user/view'),
('标签列表', '@restful/v1/message-tags/index'),
('标签列表', '@restful/v1/tags/index'),
('标签管理', '@manager/tags/*'),
('标签管理', '@manager/tags/create'),
('标签管理', '@manager/tags/delete'),
('标签管理', '@manager/tags/index'),
('标签管理', '@manager/tags/update'),
('标签管理', '@manager/tags/view'),
('社交基本权限', '@restful/v1/message/*'),
('社交基本权限', '@restful/v1/message/create'),
('社交基本权限', '@restful/v1/message/delete'),
('社交基本权限', '@restful/v1/message/index'),
('社交基本权限', '@restful/v1/message/options'),
('社交基本权限', '@restful/v1/message/update'),
('社交基本权限', '@restful/v1/message/view'),
('社交基本权限', '@restful/v1/reply/*'),
('社交基本权限', '@restful/v1/reply/create'),
('社交基本权限', '@restful/v1/reply/delete'),
('社交基本权限', '@restful/v1/reply/index'),
('社交基本权限', '@restful/v1/reply/options'),
('社交基本权限', '@restful/v1/reply/update'),
('社交基本权限', '@restful/v1/reply/view'),
('社区权限', '@restful/v1/message/create'),
('社区权限', '@restful/v1/message/index'),
('社区权限', '@restful/v1/message/options'),
('社区权限', '@restful/v1/message/view'),
('社区权限', '@restful/v1/reply/create'),
('社区权限', '@restful/v1/reply/index'),
('社区权限', '@restful/v1/reply/options'),
('社区权限', '@restful/v1/reply/view'),
('社区权限', '私有信息'),
('社区权限', '私有回复'),
('私有信息', '@restful/v1/message/delete'),
('私有信息', '@restful/v1/message/update'),
('私有元', '@restful/v1/meta/delete'),
('私有元', '@restful/v1/meta/update'),
('私有回复', '@restful/v1/reply/delete'),
('私有回复', '@restful/v1/reply/update'),
('私有宇宙', '@restful/v1/verse/delete'),
('私有宇宙', '@restful/v1/verse/update'),
('私有资源', '@restful/resource/delete'),
('私有资源', '@restful/resource/update'),
('私有赛博', '@restful/v1/verse-cyber/delete'),
('私有赛博', '@restful/v1/verse-cyber/update'),
('网站开发', '@manager/gii/*'),
('网站开发', '@manager/gii/default/*'),
('网站开发', '@manager/gii/default/action'),
('网站开发', '@manager/gii/default/diff'),
('网站开发', '@manager/gii/default/index'),
('网站开发', '@manager/gii/default/preview'),
('网站开发', '@manager/gii/default/view'),
('资源管理', '@restful/resource/create'),
('资源管理', '@restful/resource/index'),
('资源管理', '@restful/resource/options'),
('资源管理', '@restful/resource/view'),
('资源管理', '私有资源');

-- --------------------------------------------------------

--
-- 表的结构 `auth_rule`
--

CREATE TABLE `auth_rule` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_rule`
--

INSERT INTO `auth_rule` (`name`, `data`, `created_at`, `updated_at`) VALUES
('私有信息', 0x4f3a33373a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c4d65737361676552756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e4bfa1e681af223b733a393a22637265617465644174223b693a313634343633303330373b733a393a22757064617465644174223b693a313634343633303330373b7d, 1644630307, 1644630307),
('私有元', 0x4f3a33343a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c4d65746152756c65223a333a7b733a343a226e616d65223b733a393a22e7a781e69c89e58583223b733a393a22637265617465644174223b693a313634343633303234373b733a393a22757064617465644174223b693a313634343633303234373b7d, 1644630247, 1644630247),
('私有回复', 0x4f3a33353a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c5265706c7952756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e59b9ee5a48d223b733a393a22637265617465644174223b693a313634343633303431353b733a393a22757064617465644174223b693a313634343633303431353b7d, 1644630415, 1644630415),
('私有宇宙', 0x4f3a33353a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c566572736552756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e5ae87e5ae99223b733a393a22637265617465644174223b693a313634343633303230373b733a393a22757064617465644174223b693a313634343633323538323b7d, 1644630207, 1644632582),
('私有赛博', 0x4f3a34303a226170695c6d6f64756c65735c76315c636f6d706f6e656e74735c5665727365437962657252756c65223a333a7b733a343a226e616d65223b733a31323a22e7a781e69c89e8b59be58d9a223b733a393a22637265617465644174223b693a313634343633303238313b733a393a22757064617465644174223b693a313634343633303238313b7d, 1644630281, 1644630281),
('资源', 0x4f3a33303a22636f6d6d6f6e5c636f6d706f6e656e74735c5265736f7572636552756c65223a333a7b733a343a226e616d65223b733a363a22e8b584e6ba90223b733a393a22637265617465644174223b693a313634343531333830333b733a393a22757064617465644174223b693a313634343531333830333b7d, 1644513803, 1644513803);

-- --------------------------------------------------------

--
-- 表的结构 `blockly`
--

CREATE TABLE `blockly` (
  `id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `block` text DEFAULT NULL,
  `lua` text DEFAULT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `blockly`
--

INSERT INTO `blockly` (`id`, `type`, `title`, `block`, `lua`, `value`) VALUES
(1, 'action', 'action_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.setAlign(Blockly.ALIGN_RIGHT)\r\n.appendField(\"动作\")\r\n.appendField(new Blockly.FieldDropdown(<#Action>), \"OPTION\");\r\nthis.appendStatementInput(\"ACTION\")\r\n.setCheck(\"task\");\r\nthis.setColour(0);\r\nthis.setTooltip(\"动作\");\r\nthis.setHelpUrl(\"http://MrPP.com\");\r\n}\r\n}', 'function(block) {\r\nvar dropdown_option = block.getFieldValue(\'OPTION\');\r\n// var text_action = block.getFieldValue(\"ACTION\");\r\nvar statements_action = Blockly.Lua.statementToCode(block, \"ACTION\");\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \"if \'\"+dropdown_option+\"\' == key then\\n\"+statements_action+\" \\nend \";\r\nreturn code;\r\n};', ''),
(2, 'object', 'polygen_object_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"模型\")\r\n.appendField(new Blockly.FieldDropdown(<#Polygen>), \"POLYGEN\");\r\nthis.setOutput(true, null);\r\nthis.setColour(200);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar dropdown_polygen = block.getFieldValue(\'POLYGEN\');\r\n//alert(dropdown_polygen);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'\"polygen\",\"\'+dropdown_polygen+\'\"\';\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', NULL),
(3, 'object', 'native_object_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"本地物件\")\r\n.appendField(new Blockly.FieldDropdown(<#Native>), \"NATIVE\");\r\nthis.setOutput(true, null);\r\nthis.setColour(200);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar dropdown_native = block.getFieldValue(\'NATIVE\');\r\n//alert(dropdown_native);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'\"native\",\"\'+dropdown_native+\'\"\';\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', NULL),
(4, 'object', 'board_object_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"公告板\")\r\n.appendField(new Blockly.FieldDropdown(<#Board>), \"BOARD\");\r\nthis.setOutput(true, null);\r\nthis.setColour(200);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar dropdown_board = block.getFieldValue(\'BOARD\');\r\n//alert(dropdown_board);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'\"board\",\"\'+dropdown_board+\'\"\';\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', NULL),
(5, 'object', 'hint_object_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"触发点\")\r\n.appendField(new Blockly.FieldDropdown(<#Hint>), \"HINT\");\r\nthis.setOutput(true, null);\r\nthis.setColour(200);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar dropdown_hint = block.getFieldValue(\'HINT\');\r\n//alert(dropdown_hint);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'\"hint\",\"\'+dropdown_hint+\'\"\';\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', NULL),
(6, 'task', 'visual_task_block', '{\r\ninit: function() {\r\nthis.appendValueInput(\"INPUT\")\r\n.setCheck(null)\r\n.appendField(\"显示/隐藏\");\r\nthis.appendDummyInput()\r\n.appendField(new Blockly.FieldCheckbox(\"TRUE\"), \"VISUAL\");\r\nthis.setPreviousStatement(true, null);\r\nthis.setNextStatement(true, null);\r\nthis.setColour(100);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar text_type = block.getFieldValue(\'TYPE\');\r\nvar text_id = block.getFieldValue(\'ID\');\r\nvar input_name = Blockly.Lua.valueToCode(block, \'INPUT\', Blockly.Lua.ORDER_ATOMIC);\r\n//alert(input_name);\r\nvar checkbox_show = block.getFieldValue(\'VISUAL\') == \'TRUE\';\r\n// TODO: Assemble Lua into code variable.\r\nif(checkbox_show){\r\ncode = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:show\'+input_name+\'\\\'})\\n\';\r\n\r\n}else{\r\ncode = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:hide\'+input_name+\'\\\'})\\n\';\r\n\r\n}\r\nreturn code;\r\n};', NULL),
(7, 'task', 'trigger_task_block', '{\r\ninit: function() {\r\nthis.appendValueInput(\"INPUT\")\r\n.setCheck(null)\r\n.appendField(\"唯一打开\");\r\nthis.appendDummyInput();\r\nthis.setPreviousStatement(true, null);\r\nthis.setNextStatement(true, null);\r\nthis.setColour(100);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar text_type = block.getFieldValue(\'TYPE\');\r\nvar text_id = block.getFieldValue(\'ID\');\r\nvar input_name = Blockly.Lua.valueToCode(block, \'INPUT\', Blockly.Lua.ORDER_ATOMIC);\r\n//alert(input_name);\r\n// TODO: Assemble Lua into code variable.\r\n\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:trigger\'+input_name+\'\\\'})\\n\';\r\n\r\n\r\nreturn code;\r\n};', NULL),
(8, 'task', 'boom_task_block', '{\r\n  init: function() {\r\n    this.appendValueInput(\"INPUT\")\r\n        .setCheck(\"vector3\")\r\n        .appendField(\"爆炸动作，\")\r\n        .appendField(\"(整体\")\r\n        .appendField(new Blockly.FieldCheckbox(\"TRUE\"), \"NOT_PARTICAL\")\r\n        .appendField(\")\");\r\nthis.setPreviousStatement(true, null);\r\nthis.setNextStatement(true, null);\r\nthis.setColour(100);\r\n this.setTooltip(\"\");\r\n this.setHelpUrl(\"\");\r\n  }\r\n};\r\n', 'function(block) {\r\n// TODO: Assemble Lua into code variable.\r\nvar input_vector3 = Blockly.Lua.valueToCode(block, \'INPUT\', Blockly.Lua.ORDER_NONE );\r\n\r\n\r\nvar checkbox_not_partical = block.getFieldValue(\'NOT_PARTICAL\') == \'TRUE\';\r\nvar func = \"self:boom\";\r\nif(!checkbox_not_partical){\r\nfunc = \"self:boomPartical\";\r\n}\r\nif(typeof input_vector3 == \"undefined\" || input_vector3 == null || input_vector3 == \"\"){\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'\'+func+\'(\"\\\'\'+\'..parameter.sample..\'+\'\\\'\")\\\'})\\n\';\r\n}else{\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'\'+func+\'(\"\\\'\'+\'..parameter.sample..\'+\'\\\'\",\'+input_vector3 +\')\\\'})\\n\';\r\n}\r\nreturn code;\r\n};', ''),
(9, 'task', 'reset_task_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"爆炸还原\");\r\nthis.setPreviousStatement(true, null);\r\nthis.setNextStatement(true, null);\r\nthis.setColour(100);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:reset(\"\\\'\'+\'..parameter.sample..\'+\'\\\'\")\\\'})\\n\';\r\nreturn code;\r\n};', NULL),
(10, 'variable', 'variable_vector3_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.setAlign(Blockly.ALIGN_RIGHT)\r\n.appendField(\"x:\")\r\n.appendField(new Blockly.FieldNumber(0), \"X\")\r\n.appendField(\"y:\")\r\n.appendField(new Blockly.FieldNumber(0), \"Y\")\r\n.appendField(\"z:\")\r\n.appendField(new Blockly.FieldNumber(0), \"Z\");\r\nthis.setOutput(true, \"vector3\");\r\nthis.setColour(50);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar number_x = block.getFieldValue(\'X\');\r\nvar number_y = block.getFieldValue(\'Y\');\r\nvar number_z = block.getFieldValue(\'Z\');\r\n// TODO: Assemble Lua into code variable.\r\nvar code = [number_x, number_y , number_z ];\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE ];\r\n};', NULL),
(11, 'task', 'reset_task_block_sample', 'Blockly.Blocks[\'sample__\'] = {\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"sample还原\");\r\nthis.setPreviousStatement(true, null);\r\nthis.setNextStatement(true, null);\r\nthis.setColour(100);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'Blockly.Lua[\'sample__\'] = function(block) {\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:sampleReset(\"\\\'\'+\'..parameter.sample..\'+\'\\\'\")\\\'})\\n\';\r\nreturn code;\r\n};', NULL),
(12, 'task', 'function_task_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"函数\")\r\n.appendField(new Blockly.FieldDropdown([[\"redo\",\"redo\"], [\"undo\",\"undo\"]]), \"FUNCTION\");\r\nthis.setPreviousStatement(true, \"Task\");\r\nthis.setNextStatement(true, \"Task\");\r\nthis.setColour(100);\r\nthis.setTooltip(\"选择函数\");\r\nthis.setHelpUrl(\"http://MrPP.com\");\r\n}\r\n};', 'function(block) {\r\nvar dropdown_name = block.getFieldValue(\'FUNCTION\');\r\n// TODO: Assemble Lua into code variable.\r\nvar code=\"self:callback({key = \'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\', value = \'self:\"+dropdown_name +\"()\'})\\n\";\r\nreturn code;\r\n};', NULL),
(13, 'task', 'onOff_task_block', '{\r\ninit: function() {\r\nthis.appendValueInput(\"INPUT\")\r\n.setCheck(null)\r\n.appendField(\"开关\");\r\nthis.appendDummyInput();\r\nthis.setPreviousStatement(true, null);\r\nthis.setNextStatement(true, null);\r\nthis.setColour(100);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar text_type = block.getFieldValue(\'TYPE\');\r\nvar text_id = block.getFieldValue(\'ID\');\r\nvar input_name = Blockly.Lua.valueToCode(block, \'INPUT\', Blockly.Lua.ORDER_ATOMIC);\r\n//alert(input_name);\r\n// TODO: Assemble Lua into code variable.\r\n\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:onOff\'+input_name+\'\\\'})\\n\';\r\n\r\n\r\nreturn code;\r\n};', NULL),
(14, 'action', 'init_action_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.setAlign(Blockly.ALIGN_RIGHT)\r\n.appendField(\"初始化\");\r\nthis.appendStatementInput(\"ACTION\")\r\n.setCheck(\"Task\");\r\nthis.setColour(0);\r\nthis.setTooltip(\"初始化\");\r\nthis.setHelpUrl(\"http://MrPP.com\");\r\n}\r\n}', '	function(block) {\r\n// var text_action = block.getFieldValue(\"ACTION\");\r\nvar statements_action = Blockly.Lua.statementToCode(block, \"ACTION\");\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \"if \'@init\' == key then\\n\"+statements_action+\"\\nend \";\r\nreturn code;\r\n};', NULL),
(15, 'variable', 'variable_transform_block', '	{\r\ninit: function() {\r\nthis.appendValueInput(\"POS\")\r\n.setCheck(\"vector3\")\r\n.appendField(\"Position:\");\r\nthis.appendValueInput(\"SCA\")\r\n.setCheck(\"vector3\")\r\n.appendField(\"Scale:\");\r\nthis.appendValueInput(\"ROT\")\r\n.setCheck(\"vector3\")\r\n.appendField(\"Rotate:\");\r\nthis.setOutput(true, \"transform\");\r\nthis.setColour(50);\r\nthis.setTooltip(\"Trasnform\");\r\nthis.setHelpUrl(\"http://MrPP.com\");\r\n}\r\n};', 'function(block) {\r\nvar value_pos = Blockly.Lua.valueToCode(block, \'POS\', Blockly.Lua.ORDER_NONE );\r\nvar value_sca = Blockly.Lua.valueToCode(block, \'SCA\', Blockly.Lua.ORDER_NONE );\r\nvar value_rot = Blockly.Lua.valueToCode(block, \'ROT\', Blockly.Lua.ORDER_NONE );\r\nif(value_pos == \"\"){\r\nvalue_pos = \"0,0,0\";\r\n}\r\nif(value_sca == \"\"){\r\nvalue_sca = \"1,1,1\";\r\n}\r\nif(value_rot == \"\"){\r\nvalue_rot = \"0,0,0\";\r\n}\r\n// TODO: Assemble Lua into code variable.\r\nvar code = value_pos + \",\" + value_sca +\",\" + value_rot ;\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', NULL),
(16, 'task', 'tween_task_block', '{\r\ninit: function() {\r\nthis.appendValueInput(\"OBJ\")\r\n.setCheck(\"object\")\r\n.setAlign(Blockly.ALIGN_CENTRE)\r\n.appendField(\"把\");\r\nthis.appendDummyInput()\r\n.appendField(\"在\")\r\n.appendField(new Blockly.FieldNumber(0), \"SEC\")\r\n.appendField(\"秒\");\r\nthis.appendValueInput(\"TRA\")\r\n.setCheck(\"transform\")\r\n.setAlign(Blockly.ALIGN_CENTRE)\r\n.appendField(\"位移到\");\r\nthis.setPreviousStatement(true, \"task\");\r\nthis.setNextStatement(true, \"task\");\r\nthis.setColour(100);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar value_obj = Blockly.Lua.valueToCode(block, \'OBJ\', Blockly.Lua.ORDER_NONE);\r\nvar number_sec = block.getFieldValue(\'SEC\');\r\nvar value_tra = Blockly.Lua.valueToCode(block, \'TRA\', Blockly.Lua.ORDER_NONE);\r\n\r\n// TODO: Assemble Lua into code variable.\r\n\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:tween(\'+value_obj+\',\' +number_sec +\",\" + value_tra +\')\\\'})\\n\';\r\n\r\nreturn code;\r\n};', NULL),
(17, 'object', 'video_object_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"视频\")\r\n.appendField(new Blockly.FieldDropdown(<#Video>), \"VIDEO\");\r\nthis.setOutput(true, null);\r\nthis.setColour(200);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar dropdown_video = block.getFieldValue(\'VIDEO\');\r\n//alert(dropdown_video);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'\"video\",\"\'+dropdown_video+\'\"\';\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', NULL),
(18, 'object', 'picture_object_block', '{\r\ninit: function() {\r\nthis.appendDummyInput()\r\n.appendField(\"图片\")\r\n.appendField(new Blockly.FieldDropdown(<#Picture>), \"PICTURE\");\r\nthis.setOutput(true, null);\r\nthis.setColour(200);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar dropdown_picture = block.getFieldValue(\'PICTURE\');\r\n//alert(dropdown_picture);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'\"picture\",\"\'+dropdown_picture+\'\"\';\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', NULL),
(19, 'task', 'lined_task_block', '{\r\n  init: function() {\r\n    this.appendValueInput(\"FROM\")\r\n        .setCheck(\"object\")\r\n        .appendField(\"尝试把\");\r\n    this.appendValueInput(\"TO\")\r\n        .setCheck(\"object\")\r\n        .appendField(\"连线到\");\r\n    this.appendDummyInput();\r\n    this.setPreviousStatement(true, \"task\");\r\n    this.setNextStatement(true, \"task\");\r\n    this.setColour(100);\r\n this.setTooltip(\"1\");\r\n this.setHelpUrl(\"https://MrPP.com\");\r\n  }\r\n};', 'function(block) {\r\n  var value_name = Blockly.Lua.valueToCode(block, \'NAME\', Blockly.Lua.ORDER_ATOMIC);\r\n  var value_name = Blockly.Lua.valueToCode(block, \'NAME\', Blockly.Lua.ORDER_ATOMIC);\r\n  var checkbox_name = block.getFieldValue(\'NAME\') == \'TRUE\';\r\n\r\n\r\nvar from_obj = Blockly.Lua.valueToCode(block, \'FROM\', Blockly.Lua.ORDER_NONE);\r\nvar to_obj = Blockly.Lua.valueToCode(block, \'TO\', Blockly.Lua.ORDER_NONE);\r\n  // TODO: Assemble Lua into code variable.\r\n\r\n\r\nvar code = \'self:callback({key = \\\'MrPP.Lua.LuaObjectManager@MrPP.Common/Lua\\\', value = \\\'self:lined(\'+from_obj +\',\' +to_obj  +\')\\\'})\\n\';\r\n  return code;\r\n};', NULL),
(20, 'Data', 'vector3_data', '{\r\n  \"type\": \"block_type\",\r\n  \"message0\": \"X %1 Y %2 X %3\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"X\",\r\n      \"check\": \"Number\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"Y\",\r\n      \"check\": \"Number\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"Z\",\r\n      \"check\": \"Number\"\r\n    }\r\n  ],\r\n  \"inputsInline\": true,\r\n  \"output\": \"Vector3\",\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', 'function(block) {\r\n\r\nvar value_x = Blockly.Lua.valueToCode(block, \'X\', Blockly.Lua.ORDER_ATOMIC);\r\n var value_y = Blockly.Lua.valueToCode(block, \'Y\', Blockly.Lua.ORDER_ATOMIC);\r\n var value_z = Blockly.Lua.valueToCode(block, \'Z\', Blockly.Lua.ORDER_ATOMIC);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \"CS.UnityEngine.Vector3(\"+[value_x , value_y  , value_z ]+\")\";\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE ];\r\n};', '<value name=\"X\"> <shadow type=\"math_number\"><field name=\"NUM\">0</field></shadow></value><value name=\"Y\"> <shadow type=\"math_number\"><field name=\"NUM\">0</field></shadow></value><value name=\"Z\"><shadow type=\"math_number\"><field name=\"NUM\">0</field></shadow></value>'),
(21, 'Data', 'transform_data', '{\r\n  \"type\": \"transform_data\",\r\n  \"message0\": \"%{BKY_CAT_POSITION}  %1 %{BKY_CAT_SCALE}  %2 %{BKY_CAT_ROTATE}  %3\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"position\",\r\n      \"check\": \"Vector3\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"scale\",\r\n      \"check\": \"Vector3\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"rotate\",\r\n      \"check\": \"Vector3\"\r\n    }\r\n  ],\r\n  \"inputsInline\": false,\r\n  \"output\": \"Transform\",\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', 'function(block) {\r\n  var value_position = Blockly.Lua.valueToCode(block, \'position\', Blockly.Lua.ORDER_ATOMIC);\r\n  var value_scale = Blockly.Lua.valueToCode(block, \'scale\', Blockly.Lua.ORDER_ATOMIC);\r\n  var value_rotate = Blockly.Lua.valueToCode(block, \'rotate\', Blockly.Lua.ORDER_ATOMIC);\r\n  // TODO: Assemble Lua into code variable.\r\n  var code = \'CS.MrPP.Lua.Transform(\'+value_position+\', \' +value_scale+\', \' +value_rotate +\')\';\r\n  // TODO: Change ORDER_NONE to the correct strength.\r\n  return [code, Blockly.Lua.ORDER_NONE];\r\n};', '<value name=\"position\">\r\n<shadow type=\"vector3_data\">\r\n      <value name=\"X\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">0</field>\r\n          </shadow>\r\n        </value>\r\n        <value name=\"Y\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">0</field>\r\n          </shadow>\r\n        </value>\r\n        <value name=\"Z\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">0</field>\r\n          </shadow>\r\n        </value>\r\n      </shadow>\r\n </value>\r\n\r\n<value name=\"scale\">\r\n<shadow type=\"vector3_data\">\r\n      <value name=\"X\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">1</field>\r\n          </shadow>\r\n        </value>\r\n        <value name=\"Y\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">1</field>\r\n          </shadow>\r\n        </value>\r\n        <value name=\"Z\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">1</field>\r\n          </shadow>\r\n        </value>\r\n      </shadow>\r\n </value>\r\n\r\n\r\n<value name=\"rotate\">\r\n<shadow type=\"vector3_data\">\r\n      <value name=\"X\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">0</field>\r\n          </shadow>\r\n        </value>\r\n        <value name=\"Y\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">0</field>\r\n          </shadow>\r\n        </value>\r\n        <value name=\"Z\">\r\n          <shadow type=\"math_number\">\r\n            <field name=\"NUM\">0</field>\r\n          </shadow>\r\n        </value>\r\n      </shadow>\r\n </value>'),
(22, 'task', 'visual_func_block', '{\r\ninit: function() {\r\nthis.appendValueInput(\"INPUT\")\r\n.setCheck(null)\r\n.appendField(\"显示/隐藏\");\r\nthis.appendDummyInput()\r\n.appendField(new Blockly.FieldCheckbox(\"TRUE\"), \"VISUAL\");\r\nthis.setPreviousStatement(true, null);\r\nthis.setNextStatement(true, null);\r\nthis.setColour(100);\r\nthis.setTooltip(\"\");\r\nthis.setHelpUrl(\"\");\r\n}\r\n};', 'function(block) {\r\nvar text_type = block.getFieldValue(\'TYPE\');\r\nvar text_id = block.getFieldValue(\'ID\');\r\nvar input_name = Blockly.Lua.valueToCode(block,  \'INPUT\', Blockly.Lua.ORDER_NONE);\r\n//alert(input_name);\r\nvar checkbox_show = block.getFieldValue(block, \'VISUAL\') == \'TRUE\';\r\n// TODO: Assemble Lua into code variable.\r\n\r\ncode=\"CS.MrPP.Lua.LuaExecuter.SetVisual(\"+input_name +\",\"+block.getFieldValue(\'VISUAL\')+\" )\"\r\n\r\nreturn code;\r\n};', NULL),
(23, 'Entity', 'polygen_entity', '{\r\n  \"type\": \"polygen_entity\",\r\n  \"message0\": \"%{BKY_CAT_POLYGEN} %1\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"field_dropdown\",\r\n      \"name\": \"<#Polygen>\",\r\n      \"options\": [\r\n        [\r\n          \"None\",\r\n          \"None\"\r\n        ]\r\n      ]\r\n    }\r\n  ],\r\n  \"output\": \"Entity\",\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', 'function(block) {\r\nvar dropdown_polygen = block.getFieldValue(\'<#Polygen>\');\r\n//alert(dropdown_polygen);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'CS.MrPP.Lua.Handler(\"polygen\",  \"\'+dropdown_polygen+\'\")\'\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', ''),
(24, 'Execute', 'visual_execute', ' {\r\n \"type\": \"block_type\",\r\n  \"message0\": \"%{BKY_CATENTITY}  %1 %{BKY_CAT_SHOWHIDE}  %2 %3\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"entity\",\r\n      \"check\": \"Entity\"\r\n    },\r\n    {\r\n      \"type\": \"input_dummy\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"bool\",\r\n      \"check\": \"Boolean\"\r\n    }\r\n  ],\r\n  \"previousStatement\": null,\r\n  \"nextStatement\": null,\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}', 'Blockly.Lua[\'visual_func\'] = function(block) {\r\n  var value_entity = Blockly.Lua.valueToCode(block, \'entity\', Blockly.Lua.ORDER_NONE);\r\n  var value_bool = Blockly.Lua.valueToCode(block, \'bool\', Blockly.Lua.ORDER_ATOMIC);\r\n  // TODO: Assemble Lua into code variable.\r\n  var code = \'CS.MrPP.Lua.LuaExecuter.SetVisual(\'+value_entity+\', \'+ value_bool+\')\\n\';\r\n  return code;\r\n};', ''),
(25, 'Entity', 'hint_entity', '{\r\n\"type\": \"hint_entity\",\r\n\"message0\": \"%{BKY_CAT_HINT}  %1\",\r\n\"args0\": [\r\n{\r\n\"type\": \"field_dropdown\",\r\n\"name\": \"<#Hint>\",\r\n\"options\": [\r\n[\r\n\"None\",\r\n\"None\"\r\n]\r\n]\r\n}\r\n],\r\n\"output\": \"Entity\",\r\n\"colour\": 230,\r\n\"tooltip\": \"\",\r\n\"helpUrl\": \"\"\r\n}', 'function(block) {\r\nvar dropdown_hint = block.getFieldValue(\'<#Hint>\');\r\n//alert(dropdown_hint );\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'CS.MrPP.Lua.Handler(\"hint\", \"\'+dropdown_hint +\'\")\'\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', ''),
(26, 'Entity', 'native_entity', '{\r\n\"type\": \"native_entity\",\r\n\"message0\": \"%{BKY_CAT_NATIVE}  %1\",\r\n\"args0\": [\r\n{\r\n\"type\": \"field_dropdown\",\r\n\"name\": \"<#Native>\",\r\n\"options\": [\r\n[\r\n\"None\",\r\n\"None\"\r\n]\r\n]\r\n}\r\n],\r\n\"output\": \"Entity\",\r\n\"colour\": 230,\r\n\"tooltip\": \"\",\r\n\"helpUrl\": \"\"\r\n}', 'function(block) {\r\nvar dropdown_native = block.getFieldValue(\'<#Nathve>\');\r\n//alert(dropdown_native);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'CS.MrPP.Lua.Handler(\"native\", \"\'+dropdown_native+\'\")\';\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', ''),
(27, 'Entity', 'board_entity', '{\r\n\"type\": \"board_entity\",\r\n\"message0\": \"%{BKY_CAT_BOARD}  %1\",\r\n\"args0\": [\r\n{\r\n\"type\": \"field_dropdown\",\r\n\"name\": \"<#Board>\",\r\n\"options\": [\r\n[\r\n\"None\",\r\n\"None\"\r\n]\r\n]\r\n}\r\n],\r\n\"output\": \"Entity\",\r\n\"colour\": 230,\r\n\"tooltip\": \"\",\r\n\"helpUrl\": \"\"\r\n}', 'function(block) {\r\nvar dropdown_board = block.getFieldValue(\'<#Board>\');\r\n//alert(dropdown_board);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'CS.MrPP.Lua.Handler(\"board\", \"\'+dropdown_board +\'\")\'\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', ''),
(28, 'Entity', 'video_entity', '{\r\n\"type\": \"video_entity\",\r\n\"message0\": \"%{BKY_CAT_VIDEO}  %1\",\r\n\"args0\": [\r\n{\r\n\"type\": \"field_dropdown\",\r\n\"name\": \"<#Video>\",\r\n\"options\": [\r\n[\r\n\"None\",\r\n\"None\"\r\n]\r\n]\r\n}\r\n],\r\n\"output\": \"Entity\",\r\n\"colour\": 230,\r\n\"tooltip\": \"\",\r\n\"helpUrl\": \"\"\r\n}', 'function(block) {\r\nvar dropdown_video = block.getFieldValue(\'<#Video>\');\r\n//alert(dropdown_video);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'CS.MrPP.Lua.Handler(\"video\", \"\'+dropdown_video +\'\")\'\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', ''),
(29, 'Entity', 'picture_entity', '{\r\n\"type\": \"picture_entity\",\r\n\"message0\": \"%{BKY_CAT_PICTURE}  %1\",\r\n\"args0\": [\r\n{\r\n\"type\": \"field_dropdown\",\r\n\"name\": \"<#Picture>\",\r\n\"options\": [\r\n[\r\n\"None\",\r\n\"None\"\r\n]\r\n]\r\n}\r\n],\r\n\"output\": \"Entity\",\r\n\"colour\": 230,\r\n\"tooltip\": \"\",\r\n\"helpUrl\": \"\"\r\n}', 'function(block) {\r\nvar dropdown_picture = block.getFieldValue(\'<#Picture>\');\r\n//alert(dropdown_picture);\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \'CS.MrPP.Lua.Handler(\"picture\", \"\'+dropdown_picture +\'\")\'\r\n// TODO: Change ORDER_NONE to the correct strength.\r\nreturn [code, Blockly.Lua.ORDER_NONE];\r\n};', ''),
(31, 'Execute', 'boom_execute', '{\r\n  \"type\": \"boom_execute\",\r\n  \"message0\": \"%{BKY_CAT_BOOM} %1\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"boom\",\r\n      \"check\": \"Vector3\"\r\n    }\r\n  ],\r\n  \"inputsInline\": true,\r\n  \"previousStatement\": null,\r\n  \"nextStatement\": null,\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', 'function(block) { \r\n\r\n  var value_boom = Blockly.Lua.valueToCode(block, \'boom\', Blockly.Lua.ORDER_ATOMIC);\r\n  // TODO: Assemble Lua into code variable.\r\n\r\n  var code = \'CS.MrPP.Lua.LuaExecuter.Boom(parameter.sample, \'+ value_boom+\')\\n\';\r\n  return code;\r\n};', '<value name=\"boom\"><shadow  type=\"vector3_data\"><value name=\"X\"><shadow type=\"math_number\"><field name=\"NUM\">20</field></shadow></value><value name=\"Y\"><shadow type=\"math_number\"><field name=\"NUM\">0</field></shadow></value><value name=\"Z\"><shadow type=\"math_number\"><field name=\"NUM\">0</field></shadow></value></shadow></value>'),
(32, 'Execute', 'function_execute', '{\r\n  \"type\": \"function_exexute\",\r\n  \"message0\": \"%{BKY_CATDO} %1\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"field_dropdown\",\r\n      \"name\": \"function\",\r\n      \"options\": [\r\n        [\r\n          \"redo\",\r\n          \"CS.MrPP.Lua.LuaExecuter.Redo()\"\r\n        ],\r\n        [\r\n          \"undo\",\r\n          \"CS.MrPP.Lua.LuaExecuter.Undo()\"\r\n        ],\r\n        [\r\n          \"boom_reset\",\r\n          \"CS.MrPP.Lua.LuaExecuter.BoomReset(parameter.sample)\"\r\n        ],\r\n        [\r\n          \"sample_reset\",\r\n          \"CS.MrPP.Lua.LuaExecute.SampleReset(parameter.sample)\"\r\n        ]\r\n      ]\r\n    }\r\n  ],\r\n  \"inputsInline\": false,\r\n  \"previousStatement\": null,\r\n  \"nextStatement\": null,\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', ' function(block) {\r\n  var dropdown_function = block.getFieldValue(\'function\');\r\n  // TODO: Assemble Lua into code variable.\r\n  var code = dropdown_function  +\'\\n\';\r\n  return code;\r\n};', ''),
(33, 'Execute', 'tween_exexute', '{\r\n  \"type\": \"tween_exexute\",\r\n  \"message0\": \"%{BKY_CATENTITY} %1 %{BKY_CATPASSTIME} %2 %{BKY_CAT_MOVE} %3 %4\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"entity\",\r\n      \"check\": \"Entity\"\r\n    },\r\n    {\r\n      \"type\": \"field_number\",\r\n      \"name\": \"time\",\r\n      \"value\": 0.3,\r\n      \"min\": 0,\r\n      \"max\": 1000\r\n    },\r\n    {\r\n      \"type\": \"input_dummy\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"transform\",\r\n      \"check\": \"Transform\"\r\n    }\r\n  ],\r\n  \"inputsInline\": true,\r\n  \"previousStatement\": null,\r\n  \"nextStatement\": null,\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}', 'function(block) {\r\n  var value_entity = Blockly.Lua.valueToCode(block, \'entity\', Blockly.Lua.ORDER_ATOMIC);\r\n  var number_time = block.getFieldValue(\'time\');\r\n  var value_transform = Blockly.Lua.valueToCode(block, \'transform\', Blockly.Lua.ORDER_ATOMIC);\r\n  // TODO: Assemble Lua into code variable.\r\n  var code = \'CS.MrPP.Lua.LuaExecuter.Tween(\'+value_entity+\', \'+number_time+\', \'+value_transform+\')\\n\';\r\n  return code;\r\n};', ''),
(34, 'Execute', 'line_execute', '{\r\n  \"type\": \"line_execute\",\r\n  \"message0\": \"%{BKY_CAT_FROM} %1 %{BKY_CAT_LINETO}  %2\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"from\",\r\n      \"check\": \"Entity\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"to\",\r\n      \"check\": \"Entity\"\r\n    }\r\n  ],\r\n  \"inputsInline\": true,\r\n  \"previousStatement\": null,\r\n  \"nextStatement\": null,\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', 'function(block) {\r\n  var value_from = Blockly.Lua.valueToCode(block, \'from\', Blockly.Lua.ORDER_ATOMIC);\r\n  var value_to = Blockly.Lua.valueToCode(block, \'to\', Blockly.Lua.ORDER_ATOMIC);\r\n  // TODO: Assemble Lua into code variable.\r\n  var code = \'CS.MrPP.Lua.LuaExecuter.Lined(\'+value_from+\', \'+ value_to +\')\\n\';\r\n  return code;\r\n};', ''),
(35, 'Trigger', 'action_trigger', '{\r\n  \"type\": \"action_trigger\",\r\n  \"message0\": \"%{BKY_CAT_ACTION}  %1 %2 %3\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"field_dropdown\",\r\n      \"name\": \"<#Action>\",\r\n      \"options\": [\r\n        [\r\n          \"option\",\r\n          \"OPTIONNAME\"\r\n        ]\r\n      ]\r\n    },\r\n    {\r\n      \"type\": \"input_dummy\"\r\n    },\r\n    {\r\n      \"type\": \"input_statement\",\r\n      \"name\": \"content\"\r\n    }\r\n  ],\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', 'function(block) {\r\n\r\nvar dropdown_option = block.getFieldValue(\'<#Action>\');\r\nvar statements_content = Blockly.Lua.statementToCode(block, \"content\");\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \"..\";\r\n\r\n\r\n\r\nvar code = \"self.handling[\'\"+dropdown_option+\"\'] = function(self,  parameter) \\n\\\r\n  print(\'\"+dropdown_option+\"\')\\n\"\r\n+statements_content +\r\n\"end\\n\";\r\n\r\nreturn code;\r\n};', ''),
(36, 'Trigger', 'action_init', '{\r\n  \"type\": \"action_init\",\r\n  \"message0\": \"%{BKY_CAT_INIT} %1 %2\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_dummy\"\r\n    },\r\n    {\r\n      \"type\": \"input_statement\",\r\n      \"name\": \"content\"\r\n    }\r\n  ],\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}', 'function(block) {\r\n\r\nvar statements_content = Blockly.Lua.statementToCode(block, \"content\");\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \"self.handling[\'@init\'] = function(self) \\n\\\r\n  print(\'@init\')\\n\"\r\n+statements_content +\r\n\"end\\n\"\r\nreturn code;\r\n};', ''),
(37, 'Trigger', 'action_destroy', '{\r\n\"type\": \"action_init\",\r\n\"message0\": \"%{BKY_CAT_DESTROY} %1 %2\",\r\n\"args0\": [\r\n{\r\n\"type\": \"input_dummy\"\r\n},\r\n{\r\n\"type\": \"input_statement\",\r\n\"name\": \"content\"\r\n}\r\n],\r\n\"colour\": 230,\r\n\"tooltip\": \"\",\r\n\"helpUrl\": \"\"\r\n}', 'function(block) {\r\n\r\nvar statements_content = Blockly.Lua.statementToCode(block, \"content\");\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \"self.handling[\'@destroy\'] = function(self) \\n\\\r\n  print(\'@destroy\')\\n\"\r\n +statements_content +\r\n\"end\\n\"\r\nreturn code;\r\n};', ''),
(38, 'Trigger', 'update_trigger', '{\r\n  \"type\": \"action_init\",\r\n  \"message0\": \"%{BKY_CAT_UPDATE} %1 %2\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_dummy\"\r\n    },\r\n    {\r\n      \"type\": \"input_statement\",\r\n      \"name\": \"content\"\r\n    }\r\n  ],\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}', 'function(block) {\r\n\r\nvar statements_content = Blockly.Lua.statementToCode(block, \"content\");\r\n// TODO: Assemble Lua into code variable.\r\nvar code = \"self.handling[\'@update\'] = function(self, interval) \\n\\\r\n  print(\'@update\')\\n\"\r\n+statements_content +\r\n\"end\\n\"\r\nreturn code;\r\n};', ''),
(39, 'Method', 'diance_method', '{\r\n  \"type\": \"diance_method\",\r\n  \"message0\": \"from %1 to %2 diance\",\r\n  \"args0\": [\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"from\",\r\n      \"check\": \"Entity\"\r\n    },\r\n    {\r\n      \"type\": \"input_value\",\r\n      \"name\": \"to\",\r\n      \"check\": \"Entity\"\r\n    }\r\n  ],\r\n  \"inputsInline\": true,\r\n  \"output\": \"Number\",\r\n  \"colour\": 230,\r\n  \"tooltip\": \"\",\r\n  \"helpUrl\": \"\"\r\n}\r\n', 'function(block) {\r\n  var value_from = Blockly.Lua.valueToCode(block, \'from\', Blockly.Lua.ORDER_ATOMIC);\r\n  var value_to = Blockly.Lua.valueToCode(block, \'to\', Blockly.Lua.ORDER_ATOMIC);\r\n  // TODO: Assemble Lua into code variable.\r\n  var code = \'CS.MrPP.Lua.LuaExecuter.Diance(\'+value_from+\', \'+value_to+\')\';\r\n  // TODO: Change ORDER_NONE to the correct strength.\r\n  return [code, Blockly.Lua.ORDER_NONE];\r\n};', '');

-- --------------------------------------------------------

--
-- 表的结构 `content`
--

CREATE TABLE `content` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `blog` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `content`
--

INSERT INTO `content` (`id`, `title`, `type`, `picture`, `video`, `text`, `blog`, `created_at`) VALUES
(1, 'HoloLens 2开箱', 1, './image/bg01.png', '5285890795764899126', 'PP先生第一时间拿到HoloLens 2，带给我们一个不负责任的开箱。', '57', NULL),
(2, '5分钟 HoloLens 2开发', 2, './image/bg02.png', '5285890794102064510', 'PP先生打算在5分钟之内让你学会开发HoloLens 2的程序，他能做到吗？', '5', NULL),
(3, '混合现实设备横向评测', 1, './image/bg03.png', '5285890795764899126', '有幸深度体验了不同的混合现实设备，包括hololens, hololens2, nreal, magic leap, action one。文章内详细客观的列出不同设备的特点。', '102', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `content_type`
--

CREATE TABLE `content_type` (
  `id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `content_type`
--

INSERT INTO `content_type` (`id`, `type`) VALUES
(1, '硬件测评'),
(2, '开发教程'),
(3, '项目展示');

-- --------------------------------------------------------

--
-- 表的结构 `debug`
--

CREATE TABLE `debug` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `submitter_id` int(11) DEFAULT NULL,
  `solver_id` int(11) DEFAULT NULL,
  `reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `editor`
--

CREATE TABLE `editor` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `template` enum('configure','logic','mession','sample') NOT NULL,
  `data` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `editor`
--

INSERT INTO `editor` (`id`, `project`, `template`, `data`) VALUES
(7, 1, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[1011.9027987623089,279.84121584526775],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{\"name\":\"Sample1212\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":7,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[{\"node\":8,\"output\":\"hint\",\"data\":{}}]},\"boards\":{\"connections\":[{\"node\":10,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[698.3469959967113,237.18144302640394],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"position\":[\"3\",0,\"8\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[309.6202946788306,-99.24457979088015],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{\"scale\":[\"1\",\"1\",\"1\"],\"position\":[\"1\",0,0]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":8,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-81.78831715589894,414.91029667990927],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"position\":[\"7\",\"1\",\"3\"],\"scale\":[1,\"1\",\"1\"],\"angle\":[0,0,\"2\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":7,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-65.63218916743335,-77.7711003857173],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":10,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-534.7907266236763,600.4054733813936],\"name\":\"Local\"},\"7\":{\"id\":7,\"data\":{\"preview\":\"8\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[296.13065278320164,124.3664540844245],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{\"id\":\"efef\"},\"inputs\":{\"act\":{\"connections\":[{\"node\":11,\"output\":\"act\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"hint\":{\"connections\":[{\"node\":2,\"input\":\"hints\",\"data\":{}}]}},\"position\":[296.80081403891694,300.0338955273743],\"name\":\"Hint\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[1241.6934509939601,314.7223666794342],\"name\":\"Storable\"},\"10\":{\"id\":10,\"data\":{\"id\":\"111a\",\"title\":\"afeffe\",\"message\":\"123\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":6,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":2,\"input\":\"boards\",\"data\":{}}]}},\"position\":[276.04628075901894,553.5119874406731],\"name\":\"Board\"},\"11\":{\"id\":11,\"data\":{\"id\":\"0\",\"json\":\"{\\\"manager\\\"：\\\"ss\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"act\":{\"connections\":[{\"node\":8,\"input\":\"act\",\"data\":{}}]}},\"position\":[-44.094961236655664,196.49038816248952],\"name\":\"Action\"}},\"comments\":[]}'),
(10, 5, 'configure', '{}'),
(11, 3, 'configure', '{}'),
(12, 2, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[800,500],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{\"name\":\"abcd\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":7,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[{\"node\":8,\"output\":\"hint\",\"data\":{}}]},\"boards\":{\"connections\":[{\"node\":10,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[800,200],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[400,200],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":8,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,600],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":7,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,200],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":10,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-400,500],\"name\":\"Local\"},\"7\":{\"id\":7,\"data\":{\"preview\":\"1\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[400,420],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{},\"inputs\":{\"act\":{\"connections\":[{\"node\":11,\"output\":\"act\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"hint\":{\"connections\":[{\"node\":2,\"input\":\"hints\",\"data\":{}}]}},\"position\":[400,600],\"name\":\"Hint\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[751.5568323641214,902.7436275131217],\"name\":\"Storable\"},\"10\":{\"id\":10,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[{\"node\":6,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":2,\"input\":\"boards\",\"data\":{}}]}},\"position\":[-400,200],\"name\":\"Board\"},\"11\":{\"id\":11,\"data\":{\"id\":\"123\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"act\":{\"connections\":[{\"node\":8,\"input\":\"act\",\"data\":{}}]}},\"position\":[-133.24621337218355,397.29906324245576],\"name\":\"Action\"}},\"comments\":[]}'),
(13, 6, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[1053.0788151691359,324.9986916383635],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":7,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[{\"node\":8,\"output\":\"hint\",\"data\":{}}]},\"boards\":{\"connections\":[{\"node\":10,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[692.3667047449425,155.60126570728877],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"scale\":[\"1\",\"1\",\"1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[354.25042331143527,151.55937486171595],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":8,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,600],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"scale\":[\"0.1\",\"0.1\",\"0.1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":7,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-117.0648610228732,185.19869030501576],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{\"scale\":[0,\"2\",0],\"position\":[0,0,\"2\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":10,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-400,500],\"name\":\"Local\"},\"7\":{\"id\":7,\"data\":{\"preview\":\"8\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[356.88332037244766,421.347396238361],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{},\"inputs\":{\"act\":{\"connections\":[{\"node\":11,\"output\":\"act\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"hint\":{\"connections\":[{\"node\":2,\"input\":\"hints\",\"data\":{}}]}},\"position\":[400,600],\"name\":\"Hint\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[800,700],\"name\":\"Storable\"},\"10\":{\"id\":10,\"data\":{\"id\":\"啊手动阀手动阀\",\"title\":\"请问11\",\"message\":\"1212\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":6,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":2,\"input\":\"boards\",\"data\":{}}]}},\"position\":[-431.90527404610333,161.8798351605451],\"name\":\"Board\"},\"11\":{\"id\":11,\"data\":{},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"act\":{\"connections\":[{\"node\":8,\"input\":\"act\",\"data\":{}}]}},\"position\":[0,400],\"name\":\"Action\"}},\"comments\":[]}'),
(14, 4, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[998.0928367891862,471.4746036455248],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{\"name\":\"111\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":7,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[{\"node\":8,\"output\":\"hint\",\"data\":{}}]},\"boards\":{\"connections\":[{\"node\":10,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[711.5309406770693,423.8499041189614],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[378.07521991732835,128.70456473270266],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{\"position\":[\"2\",\"1\",0]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":8,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-120.58029378405405,804.314917753803],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"scale\":[\"0.01\",\"0.01\",\"0.01\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":7,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-62.31768469527268,294.0528879463455],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":10,\"input\":\"transform\",\"data\":{}}]}},\"position\":[110.49285925829037,1061.6425804441762],\"name\":\"Local\"},\"7\":{\"id\":7,\"data\":{\"preview\":\"8\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[388.3076420011279,386.92259887841146],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{\"id\":\"test\"},\"inputs\":{\"act\":{\"connections\":[{\"node\":11,\"output\":\"act\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"hint\":{\"connections\":[{\"node\":2,\"input\":\"hints\",\"data\":{}}]}},\"position\":[427.48623109119745,588.8262776264459],\"name\":\"Hint\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[1264.3298694309547,482.88994213249305],\"name\":\"Storable\"},\"10\":{\"id\":10,\"data\":{\"id\":\"bbb\",\"title\":\"guan\",\"message\":\"feifeifeifei\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":6,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":2,\"input\":\"boards\",\"data\":{}}]}},\"position\":[419.93900307490196,827.4709617984298],\"name\":\"Board\"},\"11\":{\"id\":11,\"data\":{\"id\":\"1\",\"json\":\"{\'a\':1}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"act\":{\"connections\":[{\"node\":8,\"input\":\"act\",\"data\":{}}]}},\"position\":[-46.31684823580228,572.5259993341574],\"name\":\"Action\"}},\"comments\":[]}'),
(15, 7, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[861.9870332450354,516.3873766050093],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{\"name\":\"samepe1\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":12,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[{\"node\":8,\"output\":\"hint\",\"data\":{}}]},\"boards\":{\"connections\":[{\"node\":10,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[692.310476795119,152.2172118827854],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"position\":[\"0\",0,0]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}},{\"node\":12,\"input\":\"transform\",\"data\":{}}]}},\"position\":[301.40418308643365,137.1273051565664],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":8,\"input\":\"transform\",\"data\":{}}]}},\"position\":[25.72063946311083,645.0110663892893],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":10,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-400,500],\"name\":\"Local\"},\"8\":{\"id\":8,\"data\":{\"id\":\"cool \"},\"inputs\":{\"act\":{\"connections\":[{\"node\":11,\"output\":\"act\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"hint\":{\"connections\":[{\"node\":2,\"input\":\"hints\",\"data\":{}}]}},\"position\":[400,600],\"name\":\"Hint\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[800,700],\"name\":\"Storable\"},\"10\":{\"id\":10,\"data\":{\"id\":\"board1\",\"title\":\"board one\",\"message\":\"messsage one \"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":6,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":2,\"input\":\"boards\",\"data\":{}}]}},\"position\":[-67.30951029399503,208.3529520721003],\"name\":\"Board\"},\"11\":{\"id\":11,\"data\":{\"id\":\"abcd\",\"json\":\"{\\\"a\\\":\\\"b\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"act\":{\"connections\":[{\"node\":8,\"input\":\"act\",\"data\":{}}]}},\"position\":[77.94037337478628,435.75246485081936],\"name\":\"Action\"},\"12\":{\"id\":12,\"data\":{\"preview\":\"8\",\"active\":false},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[420.19018304010154,390.91897310785356],\"name\":\"Polygen\"}},\"comments\":[]}'),
(16, 8, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":20,\"output\":\"serialize\",\"data\":{}},{\"node\":24,\"output\":\"serialize\",\"data\":{}},{\"node\":61,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[2928.8141547907285,-107.15022253746992],\"name\":\"Storable\"},\"14\":{\"id\":14,\"data\":{\"name\":\"汽车诊断\",\"active\":false},\"inputs\":{\"items\":{\"connections\":[{\"node\":15,\"output\":\"item\",\"data\":{}},{\"node\":17,\"output\":\"item\",\"data\":{}},{\"node\":18,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":16,\"output\":\"transform\",\"data\":{}}]},\"collection\":{\"connections\":[{\"node\":111,\"output\":\"collection\",\"data\":{}}]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[1166.3886441884347,-211.6121609688968],\"name\":\"Menu\"},\"15\":{\"id\":15,\"data\":{\"id\":\"Back\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"MainMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":14,\"input\":\"items\",\"data\":{}}]}},\"position\":[824.2957668024787,-403.2862345471753],\"name\":\"Item\"},\"16\":{\"id\":16,\"data\":{\"scale\":[\"1\",\"1\",\"1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":14,\"input\":\"transform\",\"data\":{}}]}},\"position\":[828.3310506733405,164.60393037260133],\"name\":\"Local\"},\"17\":{\"id\":17,\"data\":{\"id\":\"task2\",\"json\":\"{\\\"func\\\":\\\"executeTask\\\",\\\"arg\\\":\\\"task2\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":14,\"input\":\"items\",\"data\":{}}]}},\"position\":[833.3074200608406,-214.47348820445754],\"name\":\"Item\"},\"18\":{\"id\":18,\"data\":{\"id\":\"hahah\",\"json\":\"\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":14,\"input\":\"items\",\"data\":{}}]}},\"position\":[840.8135382288664,-18.002672360318932],\"name\":\"Item\"},\"20\":{\"id\":20,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":78,\"output\":\"sample\",\"data\":{}},{\"node\":84,\"output\":\"sample\",\"data\":{}},{\"node\":86,\"output\":\"sample\",\"data\":{}},{\"node\":90,\"output\":\"sample\",\"data\":{}},{\"node\":94,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[3604.1706765842396,212.61527682019445],\"name\":\"SampleRoot\"},\"24\":{\"id\":24,\"data\":{},\"inputs\":{\"menus\":{\"connections\":[{\"node\":14,\"output\":\"menu\",\"data\":{}},{\"node\":25,\"output\":\"menu\",\"data\":{}},{\"node\":29,\"output\":\"menu\",\"data\":{}},{\"node\":33,\"output\":\"menu\",\"data\":{}},{\"node\":40,\"output\":\"menu\",\"data\":{}},{\"node\":41,\"output\":\"menu\",\"data\":{}},{\"node\":42,\"output\":\"menu\",\"data\":{}},{\"node\":50,\"output\":\"menu\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[2596.949832497865,-366.08243805429714],\"name\":\"MenuRoot\"},\"25\":{\"id\":25,\"data\":{\"name\":\"汽车维修\",\"active\":false},\"inputs\":{\"items\":{\"connections\":[{\"node\":26,\"output\":\"item\",\"data\":{}},{\"node\":27,\"output\":\"item\",\"data\":{}},{\"node\":98,\"output\":\"item\",\"data\":{}},{\"node\":99,\"output\":\"item\",\"data\":{}},{\"node\":100,\"output\":\"item\",\"data\":{}},{\"node\":101,\"output\":\"item\",\"data\":{}},{\"node\":102,\"output\":\"item\",\"data\":{}},{\"node\":103,\"output\":\"item\",\"data\":{}},{\"node\":104,\"output\":\"item\",\"data\":{}},{\"node\":105,\"output\":\"item\",\"data\":{}},{\"node\":106,\"output\":\"item\",\"data\":{}},{\"node\":107,\"output\":\"item\",\"data\":{}},{\"node\":108,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":28,\"output\":\"transform\",\"data\":{}}]},\"collection\":{\"connections\":[{\"node\":110,\"output\":\"collection\",\"data\":{}}]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[1108.6401426016018,-1372.614242554267],\"name\":\"Menu\"},\"26\":{\"id\":26,\"data\":{\"id\":\"Back\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"MainMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1104.3389131828383,-1648.0048560360242],\"name\":\"Item\"},\"27\":{\"id\":27,\"data\":{\"id\":\"汽车操作杆的操作\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[848.4627946677019,-1498.6865410108774],\"name\":\"Item\"},\"28\":{\"id\":28,\"data\":{\"position\":[\"2\",\"2\",0]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":25,\"input\":\"transform\",\"data\":{}}]}},\"position\":[1090.3822341199998,-1027.8164633450626],\"name\":\"Local\"},\"29\":{\"id\":29,\"data\":{\"name\":\"MainMenu\",\"active\":true},\"inputs\":{\"items\":{\"connections\":[{\"node\":31,\"output\":\"item\",\"data\":{}},{\"node\":32,\"output\":\"item\",\"data\":{}},{\"node\":34,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":30,\"output\":\"transform\",\"data\":{}}]},\"collection\":{\"connections\":[{\"node\":112,\"output\":\"collection\",\"data\":{}}]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[2440.983568798896,-879.0161209577038],\"name\":\"Menu\"},\"30\":{\"id\":30,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":29,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2160.8745255457548,-950.2825526515117],\"name\":\"Local\"},\"31\":{\"id\":31,\"data\":{\"id\":\"汽车维修\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"汽车维修\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":29,\"input\":\"items\",\"data\":{}}]}},\"position\":[2204.0638027719074,-1515.0876019338334],\"name\":\"Item\"},\"32\":{\"id\":32,\"data\":{\"id\":\"汽车诊断\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"汽车诊断\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":29,\"input\":\"items\",\"data\":{}}]}},\"position\":[2204.804219162982,-1323.7160313873467],\"name\":\"Item\"},\"33\":{\"id\":33,\"data\":{\"name\":\"MainHelpMenu\",\"active\":false},\"inputs\":{\"items\":{\"connections\":[{\"node\":35,\"output\":\"item\",\"data\":{}},{\"node\":36,\"output\":\"item\",\"data\":{}},{\"node\":37,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":38,\"output\":\"transform\",\"data\":{}}]},\"collection\":{\"connections\":[{\"node\":113,\"output\":\"collection\",\"data\":{}}]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[1102.799686945817,823.1822847976867],\"name\":\"Menu\"},\"34\":{\"id\":34,\"data\":{\"id\":\"帮助\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"汽车诊断\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":29,\"input\":\"items\",\"data\":{}}]}},\"position\":[2201.505759053506,-1138.190656193166],\"name\":\"Item\"},\"35\":{\"id\":35,\"data\":{\"id\":\"UsingHelp\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"UsingHelpMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":33,\"input\":\"items\",\"data\":{}}]}},\"position\":[685.2057359576847,560.933395749423],\"name\":\"Item\"},\"36\":{\"id\":36,\"data\":{\"id\":\"PhoneHelp\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"PhoneHelpMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":33,\"input\":\"items\",\"data\":{}}]}},\"position\":[682.4931356605489,741.5913399102132],\"name\":\"Item\"},\"37\":{\"id\":37,\"data\":{\"id\":\"VersionHelp\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"VersionHelpMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":33,\"input\":\"items\",\"data\":{}}]}},\"position\":[682.7088864473509,936.4386084144965],\"name\":\"Item\"},\"38\":{\"id\":38,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":33,\"input\":\"transform\",\"data\":{}}]}},\"position\":[659.845925867258,1128.7556415136603],\"name\":\"Local\"},\"40\":{\"id\":40,\"data\":{\"name\":\"VersionHelpMenu\",\"active\":false},\"inputs\":{\"items\":{\"connections\":[{\"node\":45,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":49,\"output\":\"transform\",\"data\":{}}]},\"collection\":{\"connections\":[]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[150.63661534625984,1115.3682782565488],\"name\":\"Menu\"},\"41\":{\"id\":41,\"data\":{\"name\":\"UsingHelpMenu\",\"active\":false},\"inputs\":{\"items\":{\"connections\":[{\"node\":43,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":47,\"output\":\"transform\",\"data\":{}}]},\"collection\":{\"connections\":[]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[163.3428386188912,257.23166733808455],\"name\":\"Menu\"},\"42\":{\"id\":42,\"data\":{\"name\":\"PhoneHelpMenu\",\"active\":false},\"inputs\":{\"items\":{\"connections\":[{\"node\":44,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":48,\"output\":\"transform\",\"data\":{}}]},\"collection\":{\"connections\":[]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[158.6309791226662,561.8990961880298],\"name\":\"Menu\"},\"43\":{\"id\":43,\"data\":{\"id\":\"Back\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"MainHelpMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":41,\"input\":\"items\",\"data\":{}}]}},\"position\":[-214.4532458253043,180.97331798829237],\"name\":\"Item\"},\"44\":{\"id\":44,\"data\":{\"id\":\"Back\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"MainHelpMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":42,\"input\":\"items\",\"data\":{}}]}},\"position\":[-211.33186289658676,640.3246144510981],\"name\":\"Item\"},\"45\":{\"id\":45,\"data\":{\"id\":\"Back\",\"json\":\"{\\\"func\\\":\\\"jumpTo\\\",\\\"arg\\\":\\\"MainHelpMenu\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":40,\"input\":\"items\",\"data\":{}}]}},\"position\":[-183.88174339808296,1095.3249027626568],\"name\":\"Item\"},\"47\":{\"id\":47,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":41,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-240.0173986978691,365.96402455736],\"name\":\"Local\"},\"48\":{\"id\":48,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":42,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-235.29740517102272,820.718190917836],\"name\":\"Local\"},\"49\":{\"id\":49,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":40,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-217.04323749796632,1292.101298848775],\"name\":\"Local\"},\"50\":{\"id\":50,\"data\":{\"name\":\"menu_1_1\",\"active\":false},\"inputs\":{\"items\":{\"connections\":[{\"node\":51,\"output\":\"item\",\"data\":{}},{\"node\":52,\"output\":\"item\",\"data\":{}},{\"node\":53,\"output\":\"item\",\"data\":{}},{\"node\":54,\"output\":\"item\",\"data\":{}},{\"node\":55,\"output\":\"item\",\"data\":{}},{\"node\":56,\"output\":\"item\",\"data\":{}},{\"node\":57,\"output\":\"item\",\"data\":{}},{\"node\":58,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[]},\"collection\":{\"connections\":[]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":24,\"input\":\"menus\",\"data\":{}}]}},\"position\":[153.1009403190945,-711.6317718039448],\"name\":\"Menu\"},\"51\":{\"id\":51,\"data\":{\"id\":\"item_1_1_2\",\"json\":\"[{\\\"func\\\":\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"ceshishuju\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-224.63302459433183,-897.494741082154],\"name\":\"Item\"},\"52\":{\"id\":52,\"data\":{\"id\":\"item_1_1_3\",\"json\":\"[{\\\"func\\\":\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"ceshishuju\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-415.7317667710672,-781.5102726381514],\"name\":\"Item\"},\"53\":{\"id\":53,\"data\":{\"id\":\"item_1_1_1\",\"json\":\"[{\\\"func\\\":\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"ceshishuju\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-188.32774696374355,-1094.8987129553022],\"name\":\"Item\"},\"54\":{\"id\":54,\"data\":{\"id\":\"item_1_1_5\",\"json\":\"[{\\\"func\\\":\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"ceshishuju\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-277.51829560382913,-647.3732016798824],\"name\":\"Item\"},\"55\":{\"id\":55,\"data\":{\"id\":\"item_1_1_4\",\"json\":\"[{\\\"func\\\":\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"ceshishuju\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-190.0682476720838,-729.769758194284],\"name\":\"Item\"},\"56\":{\"id\":56,\"data\":{\"id\":\"item_1_1_6\",\"json\":\"[{\\\"func\\\":\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"gfhfghbvngfnfg\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-210.03367250248863,-581.6938063496827],\"name\":\"Item\"},\"57\":{\"id\":57,\"data\":{\"id\":\"item_1_1_7\",\"json\":\"[{\\\"func\\\":\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"54564568765434\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-235.28932371544317,-380.94633867506496],\"name\":\"Item\"},\"58\":{\"id\":58,\"data\":{\"id\":\"item_1_1_8\",\"json\":\"[{\\\"func\\\"\\\"showHideSample\\\",\\\"arg\\\":\\\"pokeball\\\"}, {\\\"func\\\":\\\"showHideImage\\\",\\\"arg\\\":\\\"item_1_1_1\\\"},{\\\"func\\\":\\\"showTextPanel\\\",\\\"arg\\\":\\\"abedcd\\\"}]\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":50,\"input\":\"items\",\"data\":{}}]}},\"position\":[-217.7589960879663,-186.878599281371],\"name\":\"Item\"},\"61\":{\"id\":61,\"data\":{},\"inputs\":{\"menus\":{\"connections\":[{\"node\":64,\"output\":\"menu\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[2579.33853648554,-170.3477190800436],\"name\":\"FormMenuRoot\"},\"64\":{\"id\":64,\"data\":{\"active\":false,\"name\":\"fm\"},\"inputs\":{\"items\":{\"connections\":[{\"node\":65,\"output\":\"item\",\"data\":{}},{\"node\":67,\"output\":\"item\",\"data\":{}},{\"node\":68,\"output\":\"item\",\"data\":{}},{\"node\":71,\"output\":\"item\",\"data\":{}},{\"node\":72,\"output\":\"item\",\"data\":{}},{\"node\":73,\"output\":\"item\",\"data\":{}},{\"node\":74,\"output\":\"item\",\"data\":{}},{\"node\":75,\"output\":\"item\",\"data\":{}},{\"node\":76,\"output\":\"item\",\"data\":{}},{\"node\":77,\"output\":\"item\",\"data\":{}}]},\"transform\":{\"connections\":[]}},\"outputs\":{\"menu\":{\"connections\":[{\"node\":61,\"input\":\"menus\",\"data\":{}}]}},\"position\":[2219.7263148865127,-182.52693746863713],\"name\":\"FormMenu\"},\"65\":{\"id\":65,\"data\":{\"id\":\"模型1\",\"text\":\"pokeball\",\"idd\":\"sss\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"pokeball\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1802.7329428849332,-99.34154387168732],\"name\":\"FormItem\"},\"67\":{\"id\":67,\"data\":{\"id\":\"模型2\",\"text\":\"car\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"car\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1533.4860828119658,92.17903064480265],\"name\":\"FormItem\"},\"68\":{\"id\":68,\"data\":{\"id\":\"模型3\",\"text\":\"plane2\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"plane2\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1772.5595457987022,230.56783673824],\"name\":\"FormItem\"},\"71\":{\"id\":71,\"data\":{\"id\":\"back\",\"text\":\"[{\\\"func\\\":\\\"jumpTo\\\", \\\"arg\\\":\\\"汽车维修\\\"}, {\\\"func\\\":\\\"hideAllModel\\\", \\\"arg\\\":\\\"\\\"}]\",\"json\":\"adas\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1708.7987315437558,-335.5671735248657],\"name\":\"FormItem\"},\"72\":{\"id\":72,\"data\":{\"id\":\"模型4\",\"text\":\"plane3\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"plane3\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1484.6942450389668,343.16440278312393],\"name\":\"FormItem\"},\"73\":{\"id\":73,\"data\":{\"id\":\"模型5\",\"text\":\"pokeball\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"pokeball\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1674.7163826767298,567.8823712799333],\"name\":\"FormItem\"},\"74\":{\"id\":74,\"data\":{\"id\":\"模型6\",\"text\":\"car\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"car\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1657.9895888571334,918.6008721092743],\"name\":\"FormItem\"},\"75\":{\"id\":75,\"data\":{\"id\":\"模型7\",\"text\":\"plane2\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"plane2\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[2060.0801156819275,153.44879157502302],\"name\":\"FormItem\"},\"76\":{\"id\":76,\"data\":{\"id\":\"模型8\",\"text\":\"plane3\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"plane3\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1968.3812214108923,515.729348303262],\"name\":\"FormItem\"},\"77\":{\"id\":77,\"data\":{\"id\":\"模型9\",\"text\":\"pokeball\",\"json\":\"{\\\"func\\\":\\\"showHideModel\\\", \\\"arg\\\":\\\"pokeball\\\"}\"},\"inputs\":{},\"outputs\":{\"item\":{\"connections\":[{\"node\":64,\"input\":\"items\",\"data\":{}}]}},\"position\":[1955.1509019722848,944.0912297782053],\"name\":\"FormItem\"},\"78\":{\"id\":78,\"data\":{\"name\":\"pokeball\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":81,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":79,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[{\"node\":114,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":20,\"input\":\"samples\",\"data\":{}}]}},\"position\":[3179.3143560741337,134.15040835045713],\"name\":\"Sample\"},\"79\":{\"id\":79,\"data\":{\"preview\":\"4\",\"active\":false},\"inputs\":{\"transform\":{\"connections\":[{\"node\":80,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":78,\"input\":\"poly\",\"data\":{}}]}},\"position\":[2639.09316506551,269.4839934326094],\"name\":\"Polygen\"},\"80\":{\"id\":80,\"data\":{\"scale\":[\"0.38458\",\"0.38458\",\"0.38458\"],\"position\":[\"107.2\",0,\"420\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":79,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2353.3111923220577,470.62103625836465],\"name\":\"Local\"},\"81\":{\"id\":81,\"data\":{\"scale\":[\"0.01\",\"0.01\",\"0.01\"],\"position\":[\"0\",\"0\",\"0\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":78,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2835.7237345559647,28.669391729747836],\"name\":\"Local\"},\"82\":{\"id\":82,\"data\":{\"active\":false,\"preview\":\"4\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":83,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":84,\"input\":\"poly\",\"data\":{}}]}},\"position\":[2942.8157892333465,702.1786644996029],\"name\":\"Polygen\"},\"83\":{\"id\":83,\"data\":{\"scale\":[\"0.01986274\",\"0.01986274\",\"0.01986274\"],\"position\":[\"2.227\",\"-1.4\",\"0.026573\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":82,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2633.958766958701,732.0737211823223],\"name\":\"Local\"},\"84\":{\"id\":84,\"data\":{\"name\":\"car\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":85,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":82,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":20,\"input\":\"samples\",\"data\":{}}]}},\"position\":[3252.1323078705677,446.15468349624115],\"name\":\"Sample\"},\"85\":{\"id\":85,\"data\":{\"position\":[\"0\",\"0\",\"0\"],\"scale\":[\"1\",\"1\",\"1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":84,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2908.4758495404785,469.6236888069512],\"name\":\"Local\"},\"86\":{\"id\":86,\"data\":{\"name\":\"plane2\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":88,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":87,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":20,\"input\":\"samples\",\"data\":{}}]}},\"position\":[3316.2198002372306,899.8735496204245],\"name\":\"Sample\"},\"87\":{\"id\":87,\"data\":{\"active\":false,\"preview\":\"1\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":89,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":86,\"input\":\"poly\",\"data\":{}}]}},\"position\":[2965.066196022872,1159.033093185464],\"name\":\"Polygen\"},\"88\":{\"id\":88,\"data\":{\"scale\":[\"1\",\"1\",\"1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":86,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2914.6895730517913,918.5917730762781],\"name\":\"Local\"},\"89\":{\"id\":89,\"data\":{\"scale\":[\"0.069\",\"0.069\",\"0.069\"],\"position\":[\"-0.211\",\"-9.698\",\"-6.89\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":87,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2599.4373306094685,1162.463414032113],\"name\":\"Local\"},\"90\":{\"id\":90,\"data\":{\"name\":\"plane2\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":92,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":91,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":20,\"input\":\"samples\",\"data\":{}}]}},\"position\":[3374.638736675679,1353.5240911189348],\"name\":\"Sample\"},\"91\":{\"id\":91,\"data\":{\"active\":false,\"preview\":\"1\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":93,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":90,\"input\":\"poly\",\"data\":{}}]}},\"position\":[3104.538645940042,1635.9272569761242],\"name\":\"Polygen\"},\"92\":{\"id\":92,\"data\":{\"scale\":[\"1\",\"1\",\"1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":90,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2963.6738062304994,1385.8547395915803],\"name\":\"Local\"},\"93\":{\"id\":93,\"data\":{\"scale\":[\"0.01083942\",\"0.01083942\",\"0.01083942\"],\"position\":[\"-0.737\",\"-2.016\",\"3.458\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":91,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2804.7876299255386,1778.1832432131678],\"name\":\"Local\"},\"94\":{\"id\":94,\"data\":{\"name\":\"plane3\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":96,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":95,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":20,\"input\":\"samples\",\"data\":{}}]}},\"position\":[3433.3251380350202,2148.3736608870618],\"name\":\"Sample\"},\"95\":{\"id\":95,\"data\":{\"active\":false,\"preview\":\"2\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":97,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":94,\"input\":\"poly\",\"data\":{}}]}},\"position\":[3112.4571257022067,2224.1603837552757],\"name\":\"Polygen\"},\"96\":{\"id\":96,\"data\":{\"scale\":[\"1\",\"1\",\"1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":94,\"input\":\"transform\",\"data\":{}}]}},\"position\":[3109.998374189767,1985.8358647122438],\"name\":\"Local\"},\"97\":{\"id\":97,\"data\":{\"scale\":[\"0.007492429\",\"0.007492429\",\"0.007492429\"],\"position\":[\"-1.752\",\"-1.1563\",\"3.4224\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":95,\"input\":\"transform\",\"data\":{}}]}},\"position\":[2763.4319430761934,2353.2805314970733],\"name\":\"Local\"},\"98\":{\"id\":98,\"data\":{\"id\":\"汽车舒适系统的操作\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[761.0858099888209,-1324.324269254726],\"name\":\"Item\"},\"99\":{\"id\":99,\"data\":{\"id\":\"冷却系统检测\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[760.1709197941124,-1149.969698242488],\"name\":\"Item\"},\"100\":{\"id\":100,\"data\":{\"id\":\"检查和更换发动机燃油滤清器\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[739.9702000758315,-916.283962830737],\"name\":\"Item\"},\"101\":{\"id\":101,\"data\":{\"id\":\"汽车轮胎检查\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[816.0898775544196,-718.6679554914149],\"name\":\"Item\"},\"102\":{\"id\":102,\"data\":{\"id\":\"使用钳式电流表检查电池启动电流\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1035.7104556559013,-683.9067838174121],\"name\":\"Item\"},\"103\":{\"id\":103,\"data\":{\"id\":\"使用充电机对电池充电\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1268.1717469381979,-653.1259967264],\"name\":\"Item\"},\"104\":{\"id\":104,\"data\":{\"id\":\"检查与更换空调滤芯\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1383.0289508271499,-837.5740302739592],\"name\":\"Item\"},\"105\":{\"id\":105,\"data\":{\"id\":\"更换机油及机油滤清器\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1408.6031448648143,-1034.372423903112],\"name\":\"Item\"},\"106\":{\"id\":106,\"data\":{\"id\":\"检查和更换发动机空气滤清器\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1448.4380766353497,-1217.45430356802],\"name\":\"Item\"},\"107\":{\"id\":107,\"data\":{\"id\":\"离合器踏板自由行程检查\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1400.9554042260395,-1422.1062811479349],\"name\":\"Item\"},\"108\":{\"id\":108,\"data\":{\"id\":\"制动踏板自用行程及制动系统检查\",\"json\":\"{\\\"func\\\":\\\"jumpToFormMenu\\\",\\\"arg\\\":\\\"fm\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"item\":{\"connections\":[{\"node\":25,\"input\":\"items\",\"data\":{}}]}},\"position\":[1327.1468320768195,-1611.857932006022],\"name\":\"Item\"},\"110\":{\"id\":110,\"data\":{\"LayoutType\":\"Column Then Row\",\"Rows\":8,\"CellWidth\":0.37,\"CellHeight\":0.1},\"inputs\":{},\"outputs\":{\"collection\":{\"connections\":[{\"node\":25,\"input\":\"collection\",\"data\":{}}]}},\"position\":[390.7069917016917,-1219.6967243892861],\"name\":\"Collection\"},\"111\":{\"id\":111,\"data\":{\"Rows\":8,\"CellWidth\":0.5,\"CellHeight\":0.05,\"LayoutType\":\"Column Then Row\"},\"inputs\":{},\"outputs\":{\"collection\":{\"connections\":[{\"node\":14,\"input\":\"collection\",\"data\":{}}]}},\"position\":[430.1293600277535,-176.02372725433978],\"name\":\"Collection\"},\"112\":{\"id\":112,\"data\":{\"Rows\":3,\"CellWidth\":0,\"CellHeight\":0.2,\"LayoutType\":\"Column Then Row\"},\"inputs\":{},\"outputs\":{\"collection\":{\"connections\":[{\"node\":29,\"input\":\"collection\",\"data\":{}}]}},\"position\":[2112.157816397521,-719.5684357599156],\"name\":\"Collection\"},\"113\":{\"id\":113,\"data\":{\"Rows\":3,\"CellWidth\":0,\"CellHeight\":0.2,\"LayoutType\":\"Column Then Row\"},\"inputs\":{},\"outputs\":{\"collection\":{\"connections\":[{\"node\":33,\"input\":\"collection\",\"data\":{}}]}},\"position\":[648.5773271249304,1362.2125024327977],\"name\":\"Collection\"},\"114\":{\"id\":114,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":78,\"input\":\"boards\",\"data\":{}}]}},\"position\":[3618.9607500382135,406.3538698852753],\"name\":\"Board\"}},\"comments\":[{\"text\":\"...\",\"position\":[-14.626355090514608,192.36855697831726],\"links\":[],\"type\":\"inline\"},{\"text\":\"...\",\"position\":[1058.6383766783485,-1065.3855465741287],\"links\":[28],\"type\":\"frame\",\"width\":315,\"height\":288},{\"text\":\"...\",\"position\":[-67.88936785948545,221.44585220504393],\"links\":[],\"type\":\"inline\"},{\"text\":\"...\",\"position\":[450.1524312170333,-855.7165416921264],\"links\":[],\"type\":\"inline\"},{\"text\":\"...\",\"position\":[1568.8782941460674,-704.2645690179125],\"links\":[],\"type\":\"inline\"},{\"text\":\"...\",\"position\":[1397.0361226775067,-813.9510614446535],\"links\":[],\"type\":\"inline\"},{\"text\":\"...\",\"position\":[694.5270318528724,-963.5540040479221],\"links\":[100],\"type\":\"frame\",\"width\":291,\"height\":263}]}');
INSERT INTO `editor` (`id`, `project`, `template`, `data`) VALUES
(17, 9, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[800,500],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{\"name\":\"pokeball\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":7,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[{\"node\":8,\"output\":\"hint\",\"data\":{}}]},\"boards\":{\"connections\":[{\"node\":10,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[800,200],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[400,200],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":8,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-27.380267630059418,597.2619861462811],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"scale\":[\"0.1\",\"0.1\",\"0.1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":7,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-24.642240453588784,131.54933207336455],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":10,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-400,500],\"name\":\"Local\"},\"7\":{\"id\":7,\"data\":{\"preview\":\"8\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[400,420],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{\"id\":\"b1\"},\"inputs\":{\"act\":{\"connections\":[{\"node\":11,\"output\":\"act\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"hint\":{\"connections\":[{\"node\":2,\"input\":\"hints\",\"data\":{}}]}},\"position\":[400,600],\"name\":\"Hint\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[800,700],\"name\":\"Storable\"},\"10\":{\"id\":10,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[{\"node\":6,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":2,\"input\":\"boards\",\"data\":{}}]}},\"position\":[-400,200],\"name\":\"Board\"},\"11\":{\"id\":11,\"data\":{\"id\":\"a1\",\"json\":\"{\\\"a\\\":\\\"b\\\"}\"},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"act\":{\"connections\":[{\"node\":8,\"input\":\"act\",\"data\":{}}]}},\"position\":[-1.3696142295508213,402.73922845910164],\"name\":\"Action\"}},\"comments\":[]}'),
(18, 10, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[1139.375447214647,247.48847571256417],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{\"name\":\"pokeball\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":7,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[800,200],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"scale\":[\"1\",\"1\",\"1\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[300.4743526564627,36.3356696413324],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"scale\":[\"0.001\",\"0.001\",\"0.001\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":7,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-62.026277712068364,355.207445550686],\"name\":\"Local\"},\"7\":{\"id\":7,\"data\":{\"preview\":\"8\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[312.62970892598463,456.05839781568034],\"name\":\"Polygen\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[1440.3690857745614,271.7405687345928],\"name\":\"Storable\"}},\"comments\":[]}'),
(19, 13, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[800,500],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":7,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[{\"node\":8,\"output\":\"hint\",\"data\":{}}]},\"boards\":{\"connections\":[{\"node\":10,\"output\":\"board\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[800,200],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[400,200],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":8,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,600],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":7,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,200],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":10,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-400,500],\"name\":\"Local\"},\"7\":{\"id\":7,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[400,420],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{},\"inputs\":{\"act\":{\"connections\":[{\"node\":11,\"output\":\"act\",\"data\":{}}]},\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"hint\":{\"connections\":[{\"node\":2,\"input\":\"hints\",\"data\":{}}]}},\"position\":[400,600],\"name\":\"Hint\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[800,700],\"name\":\"Storable\"},\"10\":{\"id\":10,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[{\"node\":6,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"board\":{\"connections\":[{\"node\":2,\"input\":\"boards\",\"data\":{}}]}},\"position\":[-400,200],\"name\":\"Board\"},\"11\":{\"id\":11,\"data\":{},\"inputs\":{\"json\":{\"connections\":[]}},\"outputs\":{\"act\":{\"connections\":[{\"node\":8,\"input\":\"act\",\"data\":{}}]}},\"position\":[0,400],\"name\":\"Action\"}},\"comments\":[]}'),
(20, 14, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[879.1568694129145,176.17655560975072],\"name\":\"SampleRoot\"},\"2\":{\"id\":2,\"data\":{\"name\":\"ball\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":14,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":13,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[515.9128402200815,216.18214722918643],\"name\":\"Sample\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[1220.9699948734847,133.30965923872088],\"name\":\"Storable\"},\"13\":{\"id\":13,\"data\":{\"active\":true,\"preview\":\"9\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":15,\"output\":\"transform\",\"data\":{}}]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":2,\"input\":\"poly\",\"data\":{}}]}},\"position\":[70.68277184526029,332.6273572585562],\"name\":\"Polygen\"},\"14\":{\"id\":14,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[106.4259069910729,23.42461119197023],\"name\":\"Local\"},\"15\":{\"id\":15,\"data\":{\"scale\":[\"0.003\",\"0.003\",\"0.003\"]},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":13,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-361.2332673092661,435.64705413348247],\"name\":\"Local\"}},\"comments\":[]}'),
(21, 15, 'configure', '{\"id\":\"demo@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":12,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{\"serialize\":{\"connections\":[{\"node\":9,\"input\":\"list\",\"data\":{}}]}},\"position\":[488.6317602967484,335.49024977129784],\"name\":\"SampleRoot\"},\"9\":{\"id\":9,\"data\":{},\"inputs\":{\"list\":{\"connections\":[{\"node\":1,\"output\":\"serialize\",\"data\":{}}]}},\"outputs\":{},\"position\":[755.5188437350402,507.24832285184044],\"name\":\"Storable\"},\"12\":{\"id\":12,\"data\":{},\"inputs\":{\"transform\":{\"connections\":[{\"node\":13,\"output\":\"transform\",\"data\":{}}]},\"poly\":{\"connections\":[{\"node\":14,\"output\":\"poly\",\"data\":{}}]},\"hints\":{\"connections\":[]},\"boards\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[179.7378731772169,284.357877414041],\"name\":\"Sample\"},\"13\":{\"id\":13,\"data\":{},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":12,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-166.2010182317229,207.86653033568342],\"name\":\"Local\"},\"14\":{\"id\":14,\"data\":{\"active\":true,\"preview\":\"1\"},\"inputs\":{\"transform\":{\"connections\":[]}},\"outputs\":{\"poly\":{\"connections\":[{\"node\":12,\"input\":\"poly\",\"data\":{}}]}},\"position\":[-182.52047334591325,532.2187225810942],\"name\":\"Polygen\"}},\"comments\":[]}'),
(22, 19, 'configure', NULL),
(23, 18, 'configure', NULL),
(24, 20, 'configure', NULL),
(25, 21, 'configure', NULL),
(26, 22, 'configure', NULL),
(27, 23, 'configure', NULL),
(28, 24, 'configure', NULL),
(29, 25, 'configure', NULL),
(30, 26, 'configure', NULL),
(31, 27, 'configure', NULL),
(32, 28, 'configure', NULL),
(33, 31, 'configure', NULL),
(34, 32, 'configure', NULL),
(35, 33, 'configure', NULL),
(36, 34, 'configure', NULL),
(37, 35, 'configure', NULL),
(38, 36, 'configure', NULL),
(39, 37, 'configure', NULL),
(40, 38, 'configure', NULL),
(41, 39, 'configure', NULL),
(42, 40, 'configure', NULL),
(43, 41, 'configure', NULL),
(44, 42, 'configure', NULL),
(45, 43, 'configure', NULL),
(46, 44, 'configure', NULL),
(47, 45, 'configure', NULL),
(48, 46, 'configure', NULL),
(49, 47, 'configure', NULL),
(50, 48, 'configure', NULL),
(51, 49, 'configure', NULL),
(52, 50, 'configure', NULL),
(53, 51, 'configure', NULL),
(54, 52, 'configure', NULL),
(55, 53, 'configure', NULL),
(56, 54, 'configure', NULL),
(57, 55, 'configure', NULL),
(58, 56, 'configure', NULL),
(59, 57, 'configure', NULL),
(60, 58, 'configure', NULL),
(61, 59, 'configure', NULL),
(62, 60, 'configure', NULL),
(63, 62, 'configure', NULL),
(64, 63, 'configure', NULL),
(65, 64, 'configure', NULL),
(66, 65, 'configure', NULL),
(67, 66, 'configure', NULL),
(68, 67, 'configure', NULL),
(69, 11, 'configure', NULL),
(70, 68, 'configure', NULL),
(71, 69, 'configure', NULL),
(72, 70, 'configure', NULL),
(73, 71, 'configure', NULL),
(74, 74, 'configure', NULL),
(75, 75, 'configure', NULL),
(76, 76, 'configure', NULL),
(77, 77, 'configure', NULL),
(78, 78, 'configure', NULL),
(79, 79, 'configure', NULL),
(80, 80, 'configure', NULL),
(81, 81, 'configure', NULL),
(82, 82, 'configure', NULL),
(83, 83, 'configure', NULL),
(84, 84, 'configure', NULL),
(85, 85, 'configure', NULL),
(86, 86, 'configure', NULL),
(87, 87, 'configure', NULL),
(88, 88, 'configure', NULL),
(89, 89, 'configure', NULL),
(90, 91, 'configure', NULL),
(91, 92, 'configure', NULL),
(92, 93, 'configure', NULL),
(93, 94, 'configure', NULL),
(94, 95, 'configure', NULL),
(95, 96, 'configure', NULL),
(96, 97, 'configure', NULL),
(97, 98, 'configure', NULL),
(98, 99, 'configure', NULL),
(99, 100, 'configure', NULL),
(100, 101, 'configure', NULL),
(101, 102, 'configure', NULL),
(102, 103, 'configure', NULL),
(103, 104, 'configure', NULL),
(104, 105, 'configure', NULL),
(105, 106, 'configure', NULL),
(106, 107, 'configure', NULL),
(107, 108, 'configure', NULL),
(108, 109, 'configure', NULL),
(109, 110, 'configure', NULL),
(110, 111, 'configure', NULL),
(111, 112, 'configure', NULL),
(112, 113, 'configure', NULL),
(113, 114, 'configure', NULL),
(114, 115, 'configure', NULL),
(115, 116, 'configure', NULL),
(116, 117, 'configure', NULL),
(117, 118, 'configure', NULL),
(118, 119, 'configure', NULL),
(119, 61, 'configure', NULL),
(120, 120, 'configure', NULL),
(121, 121, 'configure', NULL),
(122, 122, 'configure', NULL),
(123, 123, 'configure', NULL),
(124, 124, 'configure', NULL),
(125, 125, 'configure', NULL),
(126, 126, 'configure', NULL),
(127, 127, 'configure', NULL),
(128, 128, 'configure', NULL),
(129, 129, 'configure', NULL),
(130, 130, 'configure', NULL),
(131, 131, 'configure', NULL),
(132, 132, 'configure', NULL),
(133, 133, 'configure', NULL),
(134, 134, 'configure', NULL),
(135, 135, 'configure', NULL),
(136, 136, 'configure', NULL),
(137, 137, 'configure', NULL),
(138, 138, 'configure', NULL),
(139, 139, 'configure', NULL),
(140, 140, 'configure', NULL),
(141, 141, 'configure', NULL),
(142, 142, 'configure', NULL),
(143, 143, 'configure', NULL),
(144, 144, 'configure', NULL),
(145, 145, 'configure', NULL),
(146, 146, 'configure', NULL),
(147, 147, 'configure', NULL),
(148, 148, 'configure', NULL),
(149, 149, 'configure', NULL),
(150, 151, 'configure', NULL),
(151, 152, 'configure', NULL),
(152, 153, 'configure', NULL),
(153, 155, 'configure', NULL),
(154, 156, 'configure', NULL),
(155, 157, 'configure', NULL),
(156, 158, 'configure', NULL),
(157, 159, 'configure', NULL),
(158, 160, 'configure', NULL),
(159, 161, 'configure', NULL),
(160, 162, 'configure', NULL),
(161, 163, 'configure', NULL),
(162, 164, 'configure', NULL),
(163, 165, 'configure', NULL),
(164, 166, 'configure', NULL),
(165, 167, 'configure', NULL),
(166, 168, 'configure', NULL),
(167, 169, 'configure', NULL),
(168, 170, 'configure', NULL),
(169, 172, 'configure', NULL),
(170, 173, 'configure', NULL),
(171, 174, 'configure', NULL),
(172, 175, 'configure', NULL),
(173, 176, 'configure', NULL),
(174, 177, 'configure', NULL),
(175, 178, 'configure', NULL),
(176, 179, 'configure', NULL),
(177, 180, 'configure', NULL),
(178, 181, 'configure', NULL),
(179, 182, 'configure', NULL),
(180, 183, 'configure', NULL),
(181, 184, 'configure', NULL),
(182, 186, 'configure', NULL),
(183, 187, 'configure', NULL),
(184, 188, 'configure', NULL),
(185, 189, 'configure', NULL),
(186, 190, 'configure', NULL),
(187, 191, 'configure', NULL),
(188, 192, 'configure', NULL),
(189, 193, 'configure', NULL),
(190, 194, 'configure', NULL),
(191, 195, 'configure', NULL),
(192, 200, 'configure', NULL),
(193, 201, 'configure', NULL),
(194, 202, 'configure', NULL),
(195, 203, 'configure', NULL),
(196, 204, 'configure', NULL),
(197, 205, 'configure', NULL),
(198, 206, 'configure', NULL),
(199, 207, 'configure', NULL),
(200, 208, 'configure', NULL),
(201, 209, 'configure', NULL),
(202, 210, 'configure', NULL),
(203, 211, 'configure', NULL),
(204, 212, 'configure', NULL),
(205, 213, 'configure', NULL),
(206, 214, 'configure', NULL),
(207, 215, 'configure', NULL),
(208, 216, 'configure', NULL),
(209, 217, 'configure', NULL),
(210, 218, 'configure', NULL),
(211, 219, 'configure', NULL),
(212, 220, 'configure', NULL),
(213, 221, 'configure', NULL),
(214, 222, 'configure', NULL),
(215, 223, 'configure', NULL),
(216, 224, 'configure', NULL),
(217, 225, 'configure', NULL),
(218, 226, 'configure', NULL),
(219, 227, 'configure', NULL),
(220, 228, 'configure', NULL),
(221, 229, 'configure', NULL),
(222, 230, 'configure', NULL),
(223, 232, 'configure', NULL),
(224, 233, 'configure', NULL),
(225, 234, 'configure', NULL),
(226, 235, 'configure', NULL),
(227, 236, 'configure', NULL),
(228, 237, 'configure', NULL),
(229, 238, 'configure', NULL),
(230, 239, 'configure', NULL),
(231, 240, 'configure', NULL),
(232, 241, 'configure', NULL),
(233, 243, 'configure', NULL),
(234, 244, 'configure', NULL),
(235, 245, 'configure', NULL),
(236, 246, 'configure', NULL),
(237, 247, 'configure', NULL),
(238, 248, 'configure', NULL),
(239, 249, 'configure', NULL),
(240, 251, 'configure', NULL),
(241, 252, 'configure', NULL),
(242, 253, 'configure', NULL),
(243, 254, 'configure', NULL),
(244, 255, 'configure', NULL),
(245, 256, 'configure', NULL),
(246, 257, 'configure', NULL),
(247, 258, 'configure', NULL),
(248, 259, 'configure', NULL),
(249, 260, 'configure', NULL),
(250, 261, 'configure', NULL),
(251, 262, 'configure', NULL),
(252, 263, 'configure', NULL),
(253, 264, 'configure', NULL),
(254, 265, 'configure', NULL),
(255, 266, 'configure', NULL),
(256, 267, 'configure', NULL),
(257, 268, 'configure', NULL),
(258, 269, 'configure', NULL),
(259, 270, 'configure', NULL),
(260, 271, 'configure', NULL),
(261, 272, 'configure', NULL),
(262, 273, 'configure', NULL),
(263, 274, 'configure', NULL),
(264, 275, 'configure', NULL),
(265, 276, 'configure', NULL),
(266, 277, 'configure', NULL),
(267, 278, 'configure', NULL),
(268, 279, 'configure', NULL),
(269, 280, 'configure', NULL),
(270, 281, 'configure', NULL),
(271, 282, 'configure', NULL),
(272, 283, 'configure', NULL),
(273, 284, 'configure', NULL),
(274, 285, 'configure', NULL),
(275, 286, 'configure', NULL),
(276, 287, 'configure', NULL),
(277, 288, 'configure', NULL),
(278, 289, 'configure', NULL),
(279, 290, 'configure', NULL),
(280, 291, 'configure', NULL),
(281, 292, 'configure', NULL),
(282, 293, 'configure', NULL),
(283, 294, 'configure', NULL),
(284, 295, 'configure', NULL),
(285, 296, 'configure', NULL),
(286, 297, 'configure', NULL),
(287, 298, 'configure', NULL),
(288, 299, 'configure', NULL),
(289, 300, 'configure', NULL),
(290, 301, 'configure', NULL),
(291, 302, 'configure', NULL),
(292, 303, 'configure', NULL),
(293, 304, 'configure', NULL),
(294, 305, 'configure', NULL),
(295, 306, 'configure', NULL),
(296, 307, 'configure', NULL),
(297, 308, 'configure', NULL),
(298, 12, 'configure', NULL),
(299, 309, 'configure', NULL),
(300, 242, 'configure', NULL),
(301, 310, 'configure', NULL),
(302, 311, 'configure', NULL),
(303, 312, 'configure', NULL),
(304, 313, 'configure', NULL),
(305, 314, 'configure', NULL),
(306, 315, 'configure', NULL),
(307, 316, 'configure', NULL),
(308, 317, 'configure', NULL),
(309, 318, 'configure', NULL),
(310, 319, 'configure', NULL),
(311, 320, 'configure', NULL),
(312, 321, 'configure', NULL),
(313, 322, 'configure', NULL),
(314, 323, 'configure', NULL),
(315, 324, 'configure', NULL),
(316, 325, 'configure', NULL),
(317, 326, 'configure', NULL),
(318, 327, 'configure', NULL),
(319, 328, 'configure', NULL),
(320, 329, 'configure', NULL),
(321, 330, 'configure', NULL),
(322, 331, 'configure', NULL),
(323, 332, 'configure', NULL),
(324, 333, 'configure', NULL),
(325, 334, 'configure', NULL),
(326, 335, 'configure', NULL),
(327, 336, 'configure', NULL),
(328, 337, 'configure', NULL),
(329, 338, 'configure', NULL),
(330, 339, 'configure', NULL),
(331, 340, 'configure', NULL),
(332, 341, 'configure', NULL),
(333, 342, 'configure', NULL),
(334, 343, 'configure', NULL),
(335, 344, 'configure', NULL),
(336, 345, 'configure', NULL),
(337, 346, 'configure', NULL),
(338, 347, 'configure', NULL),
(339, 348, 'configure', NULL),
(340, 349, 'configure', NULL),
(341, 350, 'configure', NULL),
(342, 353, 'configure', NULL),
(343, 354, 'configure', NULL),
(344, 355, 'configure', NULL),
(345, 356, 'configure', NULL),
(346, 357, 'configure', NULL),
(347, 358, 'configure', NULL),
(348, 359, 'configure', NULL),
(349, 360, 'configure', NULL),
(350, 361, 'configure', NULL),
(351, 363, 'configure', NULL),
(352, 362, 'configure', NULL),
(353, 364, 'configure', NULL),
(354, 365, 'configure', NULL),
(355, 367, 'configure', NULL),
(356, 368, 'configure', NULL),
(357, 369, 'configure', NULL),
(358, 370, 'configure', NULL),
(359, 371, 'configure', NULL),
(360, 372, 'configure', NULL),
(361, 373, 'configure', NULL),
(362, 375, 'configure', NULL),
(363, 376, 'configure', NULL),
(364, 378, 'configure', NULL),
(365, 380, 'configure', NULL),
(366, 377, 'configure', NULL),
(367, 374, 'configure', NULL),
(368, 381, 'configure', NULL),
(369, 382, 'configure', NULL),
(370, 383, 'configure', NULL),
(371, 387, 'configure', NULL),
(372, 385, 'configure', NULL),
(373, 384, 'configure', NULL),
(374, 389, 'configure', NULL),
(375, 386, 'configure', NULL),
(376, 391, 'configure', NULL),
(377, 393, 'configure', NULL),
(378, 394, 'configure', NULL),
(379, 395, 'configure', NULL),
(380, 396, 'configure', NULL),
(381, 397, 'configure', NULL),
(382, 398, 'configure', NULL),
(383, 400, 'configure', NULL),
(384, 402, 'configure', NULL),
(385, 403, 'configure', NULL),
(386, 404, 'configure', NULL),
(387, 405, 'configure', NULL),
(388, 407, 'configure', NULL),
(389, 410, 'configure', NULL),
(390, 411, 'configure', NULL),
(391, 412, 'configure', NULL),
(392, 413, 'configure', NULL),
(393, 414, 'configure', NULL),
(394, 415, 'configure', NULL),
(395, 416, 'configure', NULL),
(396, 417, 'configure', NULL),
(397, 418, 'configure', NULL),
(398, 419, 'configure', NULL),
(399, 420, 'configure', NULL),
(400, 421, 'configure', NULL),
(401, 422, 'configure', NULL),
(402, 423, 'configure', NULL),
(403, 424, 'configure', NULL),
(404, 426, 'configure', NULL),
(405, 425, 'configure', NULL),
(406, 427, 'configure', NULL),
(407, 429, 'configure', NULL),
(408, 430, 'configure', NULL),
(409, 431, 'configure', NULL),
(410, 434, 'configure', NULL),
(411, 441, 'configure', NULL),
(412, 444, 'configure', NULL),
(413, 435, 'configure', NULL),
(414, 438, 'configure', NULL),
(415, 445, 'configure', NULL),
(416, 446, 'configure', NULL),
(417, 439, 'configure', NULL),
(418, 452, 'configure', NULL),
(419, 450, 'configure', NULL),
(420, 442, 'configure', NULL),
(421, 449, 'configure', NULL),
(422, 451, 'configure', NULL),
(423, 436, 'configure', NULL),
(424, 448, 'configure', NULL),
(425, 440, 'configure', NULL),
(426, 437, 'configure', NULL),
(427, 443, 'configure', NULL),
(428, 453, 'configure', NULL),
(429, 454, 'configure', NULL),
(430, 455, 'configure', NULL),
(431, 457, 'configure', NULL),
(432, 458, 'configure', NULL),
(433, 459, 'configure', NULL),
(434, 460, 'configure', NULL),
(435, 461, 'configure', NULL),
(436, 462, 'configure', NULL),
(437, 463, 'configure', NULL),
(438, 464, 'configure', NULL),
(439, 466, 'configure', NULL),
(440, 467, 'configure', NULL),
(441, 468, 'configure', NULL),
(442, 469, 'configure', NULL),
(443, 470, 'configure', NULL),
(444, 471, 'configure', NULL),
(445, 472, 'configure', NULL),
(446, 473, 'configure', NULL),
(447, 474, 'configure', NULL),
(448, 475, 'configure', NULL),
(449, 477, 'configure', NULL),
(450, 478, 'configure', NULL),
(451, 479, 'configure', NULL),
(452, 480, 'configure', NULL),
(453, 484, 'configure', NULL),
(454, 487, 'configure', NULL),
(455, 485, 'configure', NULL),
(456, 490, 'configure', NULL),
(457, 493, 'configure', NULL),
(458, 494, 'configure', NULL),
(459, 495, 'configure', NULL),
(460, 496, 'configure', NULL),
(461, 497, 'configure', NULL),
(462, 499, 'configure', NULL),
(463, 501, 'configure', NULL),
(464, 503, 'configure', NULL),
(465, 504, 'configure', NULL),
(466, 505, 'configure', NULL),
(467, 506, 'configure', NULL),
(468, 507, 'configure', NULL),
(469, 509, 'configure', NULL),
(470, 510, 'configure', NULL),
(471, 511, 'configure', NULL),
(472, 513, 'configure', NULL),
(473, 514, 'configure', NULL),
(474, 515, 'configure', NULL),
(475, 516, 'configure', NULL),
(476, 517, 'configure', NULL),
(477, 518, 'configure', NULL),
(478, 520, 'configure', NULL),
(479, 521, 'configure', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `editor_data`
--

CREATE TABLE `editor_data` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `serialization` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `editor_data`
--

INSERT INTO `editor_data` (`id`, `project_id`, `user_id`, `node_id`, `type`, `data`, `serialization`) VALUES
(911, 480, 3, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[-192.7425413614904,-437.3035229817624],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[-564.7658687749711,-442.45086858596335],\"name\":\"SampleEntity\"}}}', ''),
(912, 480, 3, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"id\":\"ID\",\"title\":\"first\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":42,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":48,\"output\":\"point\",\"data\":{}},{\"node\":80,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[810,-156],\"name\":\"Sample\"},\"42\":{\"id\":42,\"data\":{\"position\":[0,0,\"2\"],\"scale\":[\"1\",\"1\",\"1\"],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[540,-505],\"name\":\"Local\"},\"48\":{\"id\":48,\"data\":{\"title\":\"abcde\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":77,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]},\"point\":{\"connections\":[{\"node\":78,\"output\":\"point\",\"data\":{}}]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[540,-119],\"name\":\"Point\"},\"77\":{\"id\":77,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":48,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,-542],\"name\":\"Local\"},\"78\":{\"id\":78,\"data\":{\"title\":\"eeee\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":79,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]},\"point\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":48,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-156],\"name\":\"Point\"},\"79\":{\"id\":79,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":78,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-193],\"name\":\"Local\"},\"80\":{\"id\":80,\"data\":{\"title\":\"efg\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":81,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]},\"point\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[541.0918948818702,193],\"name\":\"Point\"},\"81\":{\"id\":81,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":80,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,156],\"name\":\"Local\"}}}', ''),
(920, 484, 3, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[125.45703125,218.58203125],\"name\":\"SampleEntity\"}}}', ''),
(921, 484, 3, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"沙盘\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":2,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":6,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":5,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[540,-155.5],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"position\":[0,0,\"1\"],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,-464.5],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[270,229.5],\"name\":\"Toolbar\"},\"6\":{\"id\":6,\"data\":{\"title\":\"Title\",\"mesh\":\"954\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":8,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-79.5],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":6,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-131.69678024828414,-194.65227136724127],\"name\":\"Local\"}}}', ''),
(922, 485, 3, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}},{\"node\":3,\"output\":\"sample\",\"data\":{}},{\"node\":4,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[1.948577880859375,269.8115539550781],\"name\":\"SampleEntity\"},\"3\":{\"id\":3,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[65.08683776855469,423.75636291503906],\"name\":\"SampleEntity\"},\"4\":{\"id\":4,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[98.04342651367188,119.7683334350586],\"name\":\"SampleEntity\"}}}', ''),
(923, 485, 3, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"沙盘\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":2,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":6,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":5,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[540,-155.5],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"position\":[0,0,\"1\"],\"scale\":[\"1\",1,1],\"angle\":[0,\"45\",0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,-464.5],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[270,229.5],\"name\":\"Toolbar\"},\"6\":{\"id\":6,\"data\":{\"title\":\"Title\",\"mesh\":\"953\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":8,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-79.5],\"name\":\"Polygen\"},\"8\":{\"id\":8,\"data\":{\"position\":[0,0,\"0\"],\"scale\":[\"20\",\"10\",\"10\"],\"angle\":[0,\"45\",0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":6,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-192.5],\"name\":\"Local\"}}}', ''),
(926, 487, 24, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"3\":{\"id\":3,\"data\":{},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[129.5,351.0859375],\"name\":\"SampleEntity\"}}}', ''),
(927, 485, 3, 3, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Title\"},\"inputs\":{\"transform\":{\"connections\":[]},\"point\":{\"connections\":[{\"node\":3,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":5,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[504,-155.5],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"title\":\"Title\",\"mesh\":\"953\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-272.3080957270358],\"name\":\"Polygen\"},\"4\":{\"id\":4,\"data\":{\"position\":[0,0,0],\"scale\":[\"5\",\"5\",\"5\"],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":3,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-192.5],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[270,37],\"name\":\"Toolbar\"}}}', ''),
(928, 485, 3, 4, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Title\"},\"inputs\":{\"transform\":{\"connections\":[]},\"point\":{\"connections\":[{\"node\":2,\"output\":\"point\",\"data\":{}},{\"node\":4,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":6,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[705,-155.5],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"title\":\"Title\",\"mesh\":\"1\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-552.5],\"name\":\"Polygen\"},\"3\":{\"id\":3,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-587.5],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{\"title\":\"Title\",\"width\":0.5,\"playOnAwake\":1,\"loop\":1,\"console\":true,\"video\":\"15\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-243.5],\"name\":\"Video\"},\"5\":{\"id\":5,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":4,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-202.5],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[{\"node\":7,\"output\":\"button\",\"data\":{}}]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[270,317.5],\"name\":\"Toolbar\"},\"7\":{\"id\":7,\"data\":{\"head\":\"Head\",\"action\":\"Action\",\"sort\":0,\"icon\":\"compress\"},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"button\":{\"connections\":[{\"node\":6,\"input\":\"button\",\"data\":{}}]}},\"position\":[0,182.5],\"name\":\"Button\"}}}', ''),
(929, 490, 290, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[615.199951171875,475.20001220703125],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[290.04998779296875,462.9250183105469],\"name\":\"SampleEntity\"}}}', ''),
(931, 493, 292, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"}}}', ''),
(932, 494, 293, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[161.07987280583436,506.74875806487904],\"name\":\"SampleEntity\"}}}', ''),
(933, 494, 293, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Title\"},\"inputs\":{\"transform\":{\"connections\":[]},\"point\":{\"connections\":[]},\"addon\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[400,400],\"name\":\"Sample\"}}}', ''),
(934, 490, 290, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Title\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":8,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":2,\"output\":\"point\",\"data\":{}},{\"node\":12,\"output\":\"point\",\"data\":{}},{\"node\":15,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":9,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[1402.0747694798981,-439.2371287402575],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"title\":\"Title\",\"mesh\":\"20\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[{\"node\":18,\"output\":\"rotate\",\"data\":{}}]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[466.2877522650545,-686.4014265855382],\"name\":\"Polygen\"},\"4\":{\"id\":4,\"data\":{\"position\":[\"1\",0,\"0\"],\"scale\":[\"0.01\",\"0.01\",\"0.01\"],\"angle\":[0,\"180\",0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-28.041128268969008,-892.0146777523564],\"name\":\"Local\"},\"8\":{\"id\":8,\"data\":{\"position\":[0,0,\"1\"],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[1095.757614331441,-960.3114274468383],\"name\":\"Local\"},\"9\":{\"id\":9,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[{\"node\":10,\"output\":\"button\",\"data\":{}}]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[601.7263312790761,559.6358007207806],\"name\":\"Toolbar\"},\"10\":{\"id\":10,\"data\":{\"head\":\"Head\",\"action\":\"Bell\",\"sort\":0,\"icon\":\"bell\"},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"button\":{\"connections\":[{\"node\":9,\"input\":\"button\",\"data\":{}}]}},\"position\":[382.7611169168563,638.9188942822634],\"name\":\"Button\"},\"12\":{\"id\":12,\"data\":{\"title\":\"视频\",\"width\":\"1\",\"playOnAwake\":1,\"loop\":1,\"console\":1,\"video\":\"24\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":13,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[{\"node\":19,\"output\":\"rotate\",\"data\":{}}]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[427.75993014177277,-342.8889267121279],\"name\":\"Video\"},\"13\":{\"id\":13,\"data\":{\"position\":[\"-1\",0,0],\"scale\":[1,1,1],\"angle\":[0,\"45\",0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":12,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-35.2227996803616,-297.2579365654969],\"name\":\"Local\"},\"15\":{\"id\":15,\"data\":{\"title\":\"Title\",\"width\":\"1\",\"picture\":\"11\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":16,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[{\"node\":20,\"output\":\"rotate\",\"data\":{}}]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[475.81509445649357,249.209128425862],\"name\":\"Picture\"},\"16\":{\"id\":16,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,\"135\",0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":15,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-1.9517679181785468,319.6323222224682],\"name\":\"Local\"},\"18\":{\"id\":18,\"data\":{\"speed\":[0,\"100\",0]},\"inputs\":{},\"outputs\":{\"rotate\":{\"connections\":[{\"node\":2,\"input\":\"effects\",\"data\":{}}]}},\"position\":[0,-498.3126680177428],\"name\":\"Rotate\"},\"19\":{\"id\":19,\"data\":{\"speed\":[0,\"100\",0]},\"inputs\":{},\"outputs\":{\"rotate\":{\"connections\":[{\"node\":12,\"input\":\"effects\",\"data\":{}}]}},\"position\":[-16.596128186158523,109.52106414153178],\"name\":\"Rotate\"},\"20\":{\"id\":20,\"data\":{\"speed\":[0,\"200\",0]},\"inputs\":{},\"outputs\":{\"rotate\":{\"connections\":[{\"node\":15,\"input\":\"effects\",\"data\":{}}]}},\"position\":[21.81707343651596,748.8831843081844],\"name\":\"Rotate\"}}}', ''),
(935, 495, 304, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":3,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"3\":{\"id\":3,\"data\":{},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[197.26128860313912,498.7216511526122],\"name\":\"SampleEntity\"}}}', ''),
(936, 496, 3, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"}}}', ''),
(937, 497, 306, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[92.14352754917587,355.4492220046101],\"name\":\"SampleEntity\"}}}', ''),
(938, 497, 306, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Title\"},\"inputs\":{\"transform\":{\"connections\":[]},\"point\":{\"connections\":[]},\"addon\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[400,400],\"name\":\"Sample\"}}}', ''),
(939, 499, 311, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":4,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"4\":{\"id\":4,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[337.75,659.2890625],\"name\":\"SampleEntity\"}}}', ''),
(944, 499, 311, 4, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Title\"},\"inputs\":{\"transform\":{\"connections\":[]},\"point\":{\"connections\":[{\"node\":2,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":5,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[504,-156],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"title\":\"Title\",\"width\":\"5.5\",\"picture\":\"85\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":3,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[{\"node\":4,\"output\":\"effect\",\"data\":{}}]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-306],\"name\":\"Picture\"},\"3\":{\"id\":3,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":true},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":2,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-295],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{\"alpha\":0.8},\"inputs\":{},\"outputs\":{\"effect\":{\"connections\":[{\"node\":2,\"input\":\"effects\",\"data\":{}}]}},\"position\":[0,91],\"name\":\"Transparent\"},\"5\":{\"id\":5,\"data\":{\"destroy\":true},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[270,70],\"name\":\"Toolbar\"}}}', ''),
(945, 501, 315, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[18.942828778929766,496.138385172181],\"name\":\"SampleEntity\"}}}', ''),
(947, 501, 315, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Human Cell\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":9,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":3,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":6,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[540,-156],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"title\":\"Human Cell\",\"mesh\":\"89\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":10,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-80],\"name\":\"Polygen\"},\"6\":{\"id\":6,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[270,230],\"name\":\"Toolbar\"},\"9\":{\"id\":9,\"data\":{\"position\":[0,0,\"0\"],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,-466],\"name\":\"Local\"},\"10\":{\"id\":10,\"data\":{\"position\":[0,0,\"0.3\"],\"scale\":[1,1,1],\"angle\":[0,\"180\",0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":3,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-123.66901587101317,-117.384203967053],\"name\":\"Local\"}}}', ''),
(948, 503, 315, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":2,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"2\":{\"id\":2,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[18.942828778929766,496.138385172181],\"name\":\"SampleEntity\"}}}', ''),
(949, 503, 315, 2, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Turbine Blade\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":9,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":3,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":11,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[540,-156],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"title\":\"Turbine Blade\",\"mesh\":\"88\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":10,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-80],\"name\":\"Polygen\"},\"9\":{\"id\":9,\"data\":{\"position\":[0,0,\"0\"],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,-466],\"name\":\"Local\"},\"10\":{\"id\":10,\"data\":{\"position\":[0,0,\"0.3\"],\"scale\":[\"0.3\",\"0.3\",\"0.3\"],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":3,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-396],\"name\":\"Local\"},\"11\":{\"id\":11,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[{\"node\":12,\"output\":\"button\",\"data\":{}}]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[270,230],\"name\":\"Toolbar\"},\"12\":{\"id\":12,\"data\":{\"head\":\"Head\",\"action\":\"Action\",\"sort\":0,\"icon\":\"bell\"},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"button\":{\"connections\":[{\"node\":11,\"input\":\"button\",\"data\":{}}]}},\"position\":[0,-10],\"name\":\"Button\"}}}', ''),
(962, 509, 316, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":3,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[891.0451307384209,-95.88810662424623],\"name\":\"Output\"},\"3\":{\"id\":3,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[360.35175005788415,-68.94833648224878],\"name\":\"SampleEntity\"}}}', ''),
(963, 509, 316, 3, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"混合现实\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":14,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":3,\"output\":\"point\",\"data\":{}},{\"node\":15,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":8,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[540,-156],\"name\":\"Sample\"},\"3\":{\"id\":3,\"data\":{\"title\":\"脑出血\",\"mesh\":\"111\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-423],\"name\":\"Polygen\"},\"4\":{\"id\":4,\"data\":{\"position\":[0,0,0],\"scale\":[\"0.001\",\"0.001\",\"0.001\"],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":3,\"input\":\"transform\",\"data\":{}},{\"node\":15,\"input\":\"transform\",\"data\":{}}]}},\"position\":[0,-599],\"name\":\"Local\"},\"8\":{\"id\":8,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[{\"node\":16,\"output\":\"button\",\"data\":{}},{\"node\":17,\"output\":\"button\",\"data\":{}}]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[299.31529109564707,230.23659642012683],\"name\":\"Toolbar\"},\"14\":{\"id\":14,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,-809],\"name\":\"Local\"},\"15\":{\"id\":15,\"data\":{\"title\":\"模型1\",\"mesh\":\"112\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[270,-113],\"name\":\"Polygen\"},\"16\":{\"id\":16,\"data\":{\"head\":\"关闭皮肤\",\"action\":\"关闭皮肤\",\"sort\":0,\"icon\":\"cube\"},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"button\":{\"connections\":[{\"node\":8,\"input\":\"button\",\"data\":{}}]}},\"position\":[0,-213],\"name\":\"Button\"},\"17\":{\"id\":17,\"data\":{\"head\":\"打开皮肤\",\"action\":\"打开皮肤\",\"sort\":\"1\",\"icon\":\"cube\"},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"button\":{\"connections\":[{\"node\":8,\"input\":\"button\",\"data\":{}}]}},\"position\":[0,193],\"name\":\"Button\"}}}', ''),
(972, 514, 320, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":3,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[597.1427448817755,269.71425946158865],\"name\":\"Output\"},\"3\":{\"id\":3,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[354.99995256507174,251.91080162094153],\"name\":\"SampleEntity\"}}}', ''),
(973, 514, 320, 3, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"1\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":4,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":6,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":5,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[709.1367337084425,-280.19130055578347],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"position\":[0,0,0],\"scale\":[\"0.001\",\"0.001\",\"0.001\"],\"angle\":[\"0\",0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":6,\"input\":\"transform\",\"data\":{}}]}},\"position\":[-304.40756634706713,-176.32790080560912],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{\"position\":[0,0,0],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[270,-466.5],\"name\":\"Local\"},\"5\":{\"id\":5,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[398.92228179028257,62.54595946774272],\"name\":\"Toolbar\"},\"6\":{\"id\":6,\"data\":{\"title\":\"Title\",\"mesh\":\"106\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":2,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[125.70181515446053,-39.28553995465281],\"name\":\"Polygen\"}}}', ''),
(977, 516, 321, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"}}}', ''),
(980, 518, 323, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":5,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"},\"5\":{\"id\":5,\"data\":{\"editor\":true},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[59.5,451.4765625],\"name\":\"SampleEntity\"}}}', ''),
(983, 518, 323, 5, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"1226\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":2,\"output\":\"transform\",\"data\":{}}]},\"point\":{\"connections\":[{\"node\":4,\"output\":\"point\",\"data\":{}}]},\"addon\":{\"connections\":[{\"node\":7,\"output\":\"addon\",\"data\":{}}]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[1137.9205014465101,-200.71533868787776],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"position\":[0,0,\"2\"],\"scale\":[1,1,1],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":0,\"input\":\"transform\",\"data\":{}}]}},\"position\":[747.4301063786636,-430.5445758139404],\"name\":\"Local\"},\"4\":{\"id\":4,\"data\":{\"title\":\"mt\",\"mesh\":\"114\"},\"inputs\":{\"transform\":{\"connections\":[{\"node\":5,\"output\":\"transform\",\"data\":{}}]},\"effects\":{\"connections\":[]}},\"outputs\":{\"point\":{\"connections\":[{\"node\":0,\"input\":\"point\",\"data\":{}}]}},\"position\":[410.2355013814335,-158.92644874839598],\"name\":\"Polygen\"},\"5\":{\"id\":5,\"data\":{\"position\":[0,0,0],\"scale\":[\"0.01\",\"0.01\",\"0.01\"],\"angle\":[0,0,0],\"active\":1},\"inputs\":{},\"outputs\":{\"transform\":{\"connections\":[{\"node\":4,\"input\":\"transform\",\"data\":{}}]}},\"position\":[156.24336385377012,-197.2274053986489],\"name\":\"Local\"},\"6\":{\"id\":6,\"data\":{\"head\":\"go\",\"action\":\"Action1\",\"sort\":0,\"icon\":\"cube\"},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"button\":{\"connections\":[{\"node\":7,\"input\":\"button\",\"data\":{}}]}},\"position\":[302.0910955368073,420.61728569023603],\"name\":\"Button\"},\"7\":{\"id\":7,\"data\":{\"destroy\":false},\"inputs\":{\"button\":{\"connections\":[{\"node\":6,\"output\":\"button\",\"data\":{}}]}},\"outputs\":{\"addon\":{\"connections\":[{\"node\":0,\"input\":\"addon\",\"data\":{}}]}},\"position\":[523.0606669271417,309.485162608768],\"name\":\"Toolbar\"}}}', ''),
(984, 520, 324, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[]}},\"outputs\":{},\"position\":[400,400],\"name\":\"Output\"}}}', ''),
(986, 521, 329, -1, 'configure', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"1\":{\"id\":1,\"data\":{},\"inputs\":{\"samples\":{\"connections\":[{\"node\":4,\"output\":\"sample\",\"data\":{}}]}},\"outputs\":{},\"position\":[-5,209],\"name\":\"Output\"},\"4\":{\"id\":4,\"data\":{},\"inputs\":{},\"outputs\":{\"sample\":{\"connections\":[{\"node\":1,\"input\":\"samples\",\"data\":{}}]}},\"position\":[-111.28572404755005,457.87946428571433],\"name\":\"SampleEntity\"}}}', ''),
(987, 487, 24, 3, 'Sample', '{\"id\":\"MrPP@0.1.0\",\"nodes\":{\"0\":{\"id\":0,\"data\":{\"title\":\"Title\"},\"inputs\":{\"transform\":{\"connections\":[]},\"point\":{\"connections\":[]},\"addon\":{\"connections\":[]}},\"outputs\":{\"sample\":{\"connections\":[]}},\"position\":[0,-156],\"name\":\"Sample\"},\"2\":{\"id\":2,\"data\":{\"alpha\":0.8},\"inputs\":{},\"outputs\":{\"effect\":{\"connections\":[]}},\"position\":[384,555.5859375],\"name\":\"Transparent\"},\"3\":{\"id\":3,\"data\":{\"head\":\"Head\",\"action\":\"Action\",\"sort\":0,\"icon\":\"calendar\"},\"inputs\":{\"button\":{\"connections\":[]}},\"outputs\":{\"button\":{\"connections\":[]}},\"position\":[171,476.5859375],\"name\":\"Button\"},\"4\":{\"id\":4,\"data\":{\"index\":0,\"position\":[0,0,0],\"angle\":[0,0,0]},\"inputs\":{},\"outputs\":{\"addon\":{\"connections\":[]}},\"position\":[2.6803573902202515,368.58621454668236],\"name\":\"Mark\"}}}', '');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `feedback_describe`
--

CREATE TABLE `feedback_describe` (
  `id` int(11) NOT NULL,
  `describe` varchar(128) DEFAULT NULL,
  `order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `feedback_state`
--

CREATE TABLE `feedback_state` (
  `id` int(11) NOT NULL,
  `state` varchar(128) DEFAULT NULL,
  `order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `file`
--

CREATE TABLE `file` (
  `id` int(11) NOT NULL,
  `md5` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `filename` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nokey'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `file`
--

INSERT INTO `file` (`id`, `md5`, `type`, `url`, `user_id`, `created_at`, `filename`, `size`, `key`) VALUES
(364, 'e1ad6ad5f9d8590d44decaf3ce934c1f', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/e1ad6ad5f9d8590d44decaf3ce934c1f', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(365, '0a9edc04c1e282d3face21e587d7f8c2', '.fbx', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/0a9edc04c1e282d3face21e587d7f8c2', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(366, 'e1ad6ad5f9d8590d44decaf3ce934c1f', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/e1ad6ad5f9d8590d44decaf3ce934c1f', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(369, '0d0ab4aad8e64c74064854917a950f93', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/0d0ab4aad8e64c74064854917a950f93', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(370, 'a9363a12b11dc1fa55534db4905a8e85', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/a9363a12b11dc1fa55534db4905a8e85', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(372, '0d0ab4aad8e64c74064854917a950f93', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/0d0ab4aad8e64c74064854917a950f93', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(379, '0bbc32e1443568d24e3f27ad7ac12b7f', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/0bbc32e1443568d24e3f27ad7ac12b7f', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(380, 'f78cc0422a0e0801f614786f25b48e57', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/f78cc0422a0e0801f614786f25b48e57', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(382, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(383, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(384, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(385, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(386, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(387, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(388, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(389, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(390, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(391, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(392, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(393, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(394, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(395, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(396, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(397, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(398, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(399, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(400, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(401, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(402, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(403, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(404, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(405, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(406, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(407, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(408, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(409, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(410, '911f680ca9e998925f7183d095b34424', '.fbx.zip', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/911f680ca9e998925f7183d095b34424', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(411, 'ce82c55d095157635a6c15c8362f60ab', 'image/png', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/ce82c55d095157635a6c15c8362f60ab', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(412, '11361594e237e32520cae503fc71d714', 'image/png', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/11361594e237e32520cae503fc71d714', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(417, '36651de1d2ce8254139db69dadee8e94', '.gif', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/36651de1d2ce8254139db69dadee8e94', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(422, '08e632fc9e09fa0b0eb80346d010da04', '.mp4', 'http://files-1257979353.cos.ap-chengdu.myqcloud.com/08e632fc9e09fa0b0eb80346d010da04.mp4', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(426, 'bef32228dc2bf8be91814a9068e927c3', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 3, '2021-05-18 16:39:48', NULL, NULL, 'nokey'),
(428, '672dca7dca68b5dac1c09f9e5e150480', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/672dca7dca68b5dac1c09f9e5e150480.jpg', 3, '2021-05-18 16:40:32', '672dca7dca68b5dac1c09f9e5e150480.jpg', NULL, 'nokey'),
(429, '525ee04a8126bf61b5f1d501e009e2fe', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/525ee04a8126bf61b5f1d501e009e2fe.jpg', 3, '2021-05-22 15:45:21', '525ee04a8126bf61b5f1d501e009e2fe.jpg', NULL, 'nokey'),
(430, '525ee04a8126bf61b5f1d501e009e2fe', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/525ee04a8126bf61b5f1d501e009e2fe.jpg', 3, '2021-05-22 15:45:28', '525ee04a8126bf61b5f1d501e009e2fe.jpg', NULL, 'nokey'),
(431, '3354a23c5fc1110b11935b01ae787ec3', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/3354a23c5fc1110b11935b01ae787ec3.jpg', 3, '2021-05-22 15:45:38', '3354a23c5fc1110b11935b01ae787ec3.jpg', NULL, 'nokey'),
(432, '21b2a239b95347340fae187b77466bb9', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/21b2a239b95347340fae187b77466bb9.jpg', 3, '2021-05-22 15:45:47', '21b2a239b95347340fae187b77466bb9.jpg', NULL, 'nokey'),
(433, '3793c12600b4fe809dd5ba05132b0961', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/3793c12600b4fe809dd5ba05132b0961.jpg', 3, '2021-05-22 15:46:11', '3793c12600b4fe809dd5ba05132b0961.jpg', NULL, 'nokey'),
(434, '3793c12600b4fe809dd5ba05132b0961', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/3793c12600b4fe809dd5ba05132b0961.jpg', 3, '2021-05-22 15:46:17', '3793c12600b4fe809dd5ba05132b0961.jpg', NULL, 'nokey'),
(435, '1a54a2c7606820d419ee90bb2a70a942', '.png', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/1a54a2c7606820d419ee90bb2a70a942.png', 24, '2021-05-24 03:43:18', '1a54a2c7606820d419ee90bb2a70a942.png', NULL, 'nokey'),
(436, '7f25a5d5411f53c62a79e09fa78bd78d', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/7f25a5d5411f53c62a79e09fa78bd78d.jpg', 24, '2021-05-24 03:43:20', '7f25a5d5411f53c62a79e09fa78bd78d.jpg', NULL, 'nokey'),
(437, '436987836cab90f519facc3d8ec16688', '.mp4', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/436987836cab90f519facc3d8ec16688.mp4', 24, '2021-05-24 03:43:49', '436987836cab90f519facc3d8ec16688.mp4', NULL, 'nokey'),
(438, '7462ca8d3dabdecf8194040af3b6a2d5', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/7462ca8d3dabdecf8194040af3b6a2d5.jpg', 24, '2021-05-24 03:43:59', '7462ca8d3dabdecf8194040af3b6a2d5.jpg', NULL, 'nokey'),
(445, 'bef32228dc2bf8be91814a9068e927c3', '', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 24, '2021-11-19 08:37:41', 'bef322aa.glb', NULL, 'nokey'),
(446, 'bef32228dc2bf8be91814a9068e927c3', '', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 24, '2021-11-19 08:41:15', 'bef322aa.glb', NULL, 'nokey'),
(447, 'bef32228dc2bf8be91814a9068e927c3', '', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 24, '2021-11-19 08:47:01', 'bef322bb.glb', NULL, 'nokey'),
(448, 'bef32228dc2bf8be91814a9068e927c3', '', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 24, '2021-11-19 09:21:14', 'bef322cc.glb', NULL, 'nokey'),
(449, 'bef32228dc2bf8be91814a9068e927c3', '', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 24, '2021-11-19 10:08:46', 'bef322cc.glb', NULL, 'nokey'),
(450, '97af247196d1610e999a337158975daa', '', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/97af247196d1610e999a337158975daa.glb', 3, '2021-11-22 07:17:51', '97af247196d1610e999a337158975daa.glb', NULL, 'nokey'),
(451, '83644f09cfc39efc05c515dbbefeec87', '.jpg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-11-24 08:25:35', '83644f09cfc39efc05c515dbbefeec87.jpg', NULL, 'nokey'),
(452, '6ad588a4456f61a0f7f17078e8faad8b', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/6ad588a4456f61a0f7f17078e8faad8b.jpg', 24, '2021-11-24 08:25:42', '6ad588a4456f61a0f7f17078e8faad8b.jpg', NULL, 'nokey'),
(455, '2f6d4b415e238db9fc9b427d3e447a08', '.png', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/2f6d4b415e238db9fc9b427d3e447a08.png', 288, '2021-12-01 03:04:15', '2f6d4b415e238db9fc9b427d3e447a08.png', NULL, 'nokey'),
(456, '1b263c9c5355c5a0244d76479d987d24', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/1b263c9c5355c5a0244d76479d987d24.jpg', 288, '2021-12-01 03:04:19', '1b263c9c5355c5a0244d76479d987d24.jpg', NULL, 'nokey'),
(457, '4682a7eef775160df1097f2626a78dc6', '.png', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/4682a7eef775160df1097f2626a78dc6.png', 290, '2021-12-02 09:41:25', '4682a7eef775160df1097f2626a78dc6.png', NULL, 'nokey'),
(458, '9b137e38ba731f0c34f41b17b01f7f20', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/9b137e38ba731f0c34f41b17b01f7f20.jpg', 290, '2021-12-02 09:41:33', '9b137e38ba731f0c34f41b17b01f7f20.jpg', NULL, 'nokey'),
(468, '3a6175c1a97042097e41bbdeedfbed86', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/3a6175c1a97042097e41bbdeedfbed86.jpg', 3, '2021-12-03 01:39:16', '3a6175c1a97042097e41bbdeedfbed86.jpg', NULL, 'nokey'),
(469, '99d5768eb8754b0cd481c36b01ead8da', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/99d5768eb8754b0cd481c36b01ead8da.jpg', 3, '2021-12-03 01:39:25', '99d5768eb8754b0cd481c36b01ead8da.jpg', NULL, 'nokey'),
(470, '86ea4a25ae37d5e669a4a8c0abf93f8b', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/86ea4a25ae37d5e669a4a8c0abf93f8b.jpg', 3, '2021-12-03 01:39:26', '86ea4a25ae37d5e669a4a8c0abf93f8b.jpg', NULL, 'nokey'),
(471, '1d9cb1f780e3a25bca37d80d27af7948', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/1d9cb1f780e3a25bca37d80d27af7948.jpg', 3, '2021-12-03 01:39:31', '1d9cb1f780e3a25bca37d80d27af7948.jpg', NULL, 'nokey'),
(472, '34836133f0082d17058178640a984c53', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/34836133f0082d17058178640a984c53.jpg', 3, '2021-12-03 01:39:31', '34836133f0082d17058178640a984c53.jpg', NULL, 'nokey'),
(473, '48de0b428e70b74a8cffa61c4849a715', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/48de0b428e70b74a8cffa61c4849a715.jpg', 3, '2021-12-03 01:39:39', '48de0b428e70b74a8cffa61c4849a715.jpg', NULL, 'nokey'),
(474, '9830843d456d9fd1498715024b0bfa64', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/9830843d456d9fd1498715024b0bfa64.jpg', 3, '2021-12-03 01:39:41', '9830843d456d9fd1498715024b0bfa64.jpg', NULL, 'nokey'),
(480, '6d9f3ea5b43c88b84b7d4406776dae45', '.gltf', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/6d9f3ea5b43c88b84b7d4406776dae45.gltf', 290, '2021-12-06 06:35:52', '6d9f3ea5b43c88b84b7d4406776dae45.gltf', NULL, 'nokey'),
(481, 'ee3c6f940315e71f15cebb8bf40ad6f0', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/ee3c6f940315e71f15cebb8bf40ad6f0.jpg', 290, '2021-12-06 06:36:04', 'ee3c6f940315e71f15cebb8bf40ad6f0.jpg', NULL, 'nokey'),
(482, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-06 06:37:03', 'testpicture.jpg', NULL, 'nokey'),
(483, 'dffab31f89170b4deb514fe7559d23bf', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/dffab31f89170b4deb514fe7559d23bf.jpg', 24, '2021-12-06 06:37:13', 'f1.jpg.jpg', NULL, 'nokey'),
(484, 'c4d9cda17fcd9979aa0e04fe4932e595', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c4d9cda17fcd9979aa0e04fe4932e595.jpg', 24, '2021-12-06 06:37:15', 'f1.jpg.jpg.thumbnail', NULL, 'nokey'),
(485, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-06 06:37:29', 'testpicture.jpg', NULL, 'nokey'),
(486, 'a6eca7cfe40b962f8b59ed67c249582c', '.mp4', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/a6eca7cfe40b962f8b59ed67c249582c.mp4', 290, '2021-12-06 06:40:59', 'a6eca7cfe40b962f8b59ed67c249582c.mp4', NULL, 'nokey'),
(487, '25b2aa76db094432f6da96df277e36c2', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/25b2aa76db094432f6da96df277e36c2.jpg', 290, '2021-12-06 06:48:54', '25b2aa76db094432f6da96df277e36c2.jpg', NULL, 'nokey'),
(488, '5148cedf5d72f3391e8c6fa47bc9601d', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/5148cedf5d72f3391e8c6fa47bc9601d.jpg', 290, '2021-12-06 06:48:54', '5148cedf5d72f3391e8c6fa47bc9601d.jpg', NULL, 'nokey'),
(489, '81277b67b25a06dd4152090fa6e60e0c', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/81277b67b25a06dd4152090fa6e60e0c.jpg', 290, '2021-12-06 06:48:54', '81277b67b25a06dd4152090fa6e60e0c.jpg', NULL, 'nokey'),
(490, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-06 06:52:39', 'testpicture.jpg', NULL, 'nokey'),
(491, 'dffab31f89170b4deb514fe7559d23bf', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/dffab31f89170b4deb514fe7559d23bf.jpg', 24, '2021-12-06 07:04:39', 'f1.jpg.jpg', NULL, 'nokey'),
(492, 'c4d9cda17fcd9979aa0e04fe4932e595', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c4d9cda17fcd9979aa0e04fe4932e595.jpg', 24, '2021-12-06 07:04:40', 'f1.jpg.jpg.thumbnail', NULL, 'nokey'),
(493, 'dffab31f89170b4deb514fe7559d23bf', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/dffab31f89170b4deb514fe7559d23bf.jpg', 24, '2021-12-06 07:06:26', 'f1.jpg.jpg', NULL, 'nokey'),
(494, 'c4d9cda17fcd9979aa0e04fe4932e595', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c4d9cda17fcd9979aa0e04fe4932e595.jpg', 24, '2021-12-06 07:06:28', 'f1.jpg.jpg.thumbnail', NULL, 'nokey'),
(495, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-06 07:12:45', 'testpicture.jpg', NULL, 'nokey'),
(496, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-06 07:13:37', 'testpicture.jpg', NULL, 'nokey'),
(502, 'fd450576b36a07c759e1b44c45047fc7', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/fd450576b36a07c759e1b44c45047fc7.jpeg', 3, '2021-12-06 08:06:21', 'src=http___www.btcha.com_uploadfile_2017_0316_20170316105335210.png&refer=http___www.btcha.jpeg', NULL, 'nokey'),
(503, 'fd450576b36a07c759e1b44c45047fc7', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/fd450576b36a07c759e1b44c45047fc7.jpeg', 3, '2021-12-06 08:06:40', 'src=http___www.btcha.com_uploadfile_2017_0316_20170316105335210.png&refer=http___www.btcha.jpeg', NULL, 'nokey'),
(513, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-06 08:29:45', 'testpicture.jpg', NULL, 'nokey'),
(514, '540892b35529a7e1e29db03fdb58ad33', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/540892b35529a7e1e29db03fdb58ad33.glb', 24, '2021-12-06 08:45:46', '5408A.glb', NULL, 'nokey'),
(515, '026efd4fdba1b91f448aa17392bd09b5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/026efd4fdba1b91f448aa17392bd09b5.jpg', 24, '2021-12-06 08:45:49', '5408A.glb', NULL, 'nokey'),
(516, '7cebe728e73cfd502d74171b921df02d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/7cebe728e73cfd502d74171b921df02d.jpg', 24, '2021-12-06 08:45:59', 'bef322aa.glb', NULL, 'nokey'),
(517, '7cebe728e73cfd502d74171b921df02d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/7cebe728e73cfd502d74171b921df02d.jpg', 24, '2021-12-06 08:46:05', 'bef322aa.glb', NULL, 'nokey'),
(518, '540892b35529a7e1e29db03fdb58ad33', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/540892b35529a7e1e29db03fdb58ad33.glb', 24, '2021-12-06 08:46:17', '54089e.glb', NULL, 'nokey'),
(519, '026efd4fdba1b91f448aa17392bd09b5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/026efd4fdba1b91f448aa17392bd09b5.jpg', 24, '2021-12-06 08:46:20', '54089e.glb', NULL, 'nokey'),
(520, '540892b35529a7e1e29db03fdb58ad33', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/540892b35529a7e1e29db03fdb58ad33.glb', 24, '2021-12-06 08:46:32', '54089e.glb', NULL, 'nokey'),
(521, '026efd4fdba1b91f448aa17392bd09b5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/026efd4fdba1b91f448aa17392bd09b5.jpg', 24, '2021-12-06 08:46:34', '54089e.glb', NULL, 'nokey'),
(522, 'bef32228dc2bf8be91814a9068e927c3', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 24, '2021-12-06 08:55:02', 'bef322aa.glb', NULL, 'nokey'),
(523, '88fec2b0d62ac0242d99037a725ce2aa', 'video/mp4', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/88fec2b0d62ac0242d99037a725ce2aa.mp4', 24, '2021-12-06 08:55:41', 'ITJC.mp4', NULL, 'nokey'),
(524, 'c6c7b3bc945bd19617c6086e0956240d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c6c7b3bc945bd19617c6086e0956240d.jpg', 24, '2021-12-06 08:55:43', 'ITJC.mp4.thumbnail', NULL, 'nokey'),
(526, '88fec2b0d62ac0242d99037a725ce2aa', 'video/mp4', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/88fec2b0d62ac0242d99037a725ce2aa.mp4', 24, '2021-12-06 09:00:08', 'ITJC.mp4', NULL, 'nokey'),
(527, 'c6c7b3bc945bd19617c6086e0956240d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c6c7b3bc945bd19617c6086e0956240d.jpg', 24, '2021-12-06 09:00:10', 'ITJC.mp4.thumbnail', NULL, 'nokey'),
(529, '5a162220d48016c4198cca19aaefd045', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/5a162220d48016c4198cca19aaefd045.jpg', 24, '2021-12-06 09:10:03', 'v2-5a162220d48016c4198cca19aaefd045_r.jpg', NULL, 'nokey'),
(530, '85bb2b374448510c42f46bcadd079a8c', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/85bb2b374448510c42f46bcadd079a8c.jpg', 24, '2021-12-06 09:10:05', 'v2-5a162220d48016c4198cca19aaefd045_r.jpg.thumbnail', NULL, 'nokey'),
(531, '1cfe84b8e57a52aaaaf70518db304f3f', 'image/png', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/1cfe84b8e57a52aaaaf70518db304f3f.png', 24, '2021-12-06 09:10:17', 'wallhaven-289k39.png', NULL, 'nokey'),
(532, 'bcab438bc5dba58d7fa2c2dcd81be1e5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bcab438bc5dba58d7fa2c2dcd81be1e5.jpg', 24, '2021-12-06 09:10:21', 'wallhaven-289k39.png.thumbnail', NULL, 'nokey'),
(533, '540892b35529a7e1e29db03fdb58ad33', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/540892b35529a7e1e29db03fdb58ad33.glb', 24, '2021-12-06 09:10:33', '54089f.glb', NULL, 'nokey'),
(534, '026efd4fdba1b91f448aa17392bd09b5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/026efd4fdba1b91f448aa17392bd09b5.jpg', 24, '2021-12-06 09:10:36', '54089f.glb', NULL, 'nokey'),
(535, '88fec2b0d62ac0242d99037a725ce2aa', 'video/mp4', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/88fec2b0d62ac0242d99037a725ce2aa.mp4', 24, '2021-12-06 09:10:45', 'ITJC.mp4', NULL, 'nokey'),
(536, 'c6c7b3bc945bd19617c6086e0956240d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c6c7b3bc945bd19617c6086e0956240d.jpg', 24, '2021-12-06 09:10:47', 'ITJC.mp4.thumbnail', NULL, 'nokey'),
(537, '88fec2b0d62ac0242d99037a725ce2aa', 'video/mp4', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/88fec2b0d62ac0242d99037a725ce2aa.mp4', 24, '2021-12-06 09:11:32', 'ITJC.mp4', NULL, 'nokey'),
(538, 'c6c7b3bc945bd19617c6086e0956240d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c6c7b3bc945bd19617c6086e0956240d.jpg', 24, '2021-12-06 09:11:34', 'ITJC.mp4.thumbnail', NULL, 'nokey'),
(539, '540892b35529a7e1e29db03fdb58ad33', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/540892b35529a7e1e29db03fdb58ad33.glb', 24, '2021-12-06 09:15:49', '54089f.glb', NULL, 'nokey'),
(540, '026efd4fdba1b91f448aa17392bd09b5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/026efd4fdba1b91f448aa17392bd09b5.jpg', 24, '2021-12-06 09:15:51', '54089f.glb', NULL, 'nokey'),
(541, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-06 09:16:26', 'testpicture.jpg', NULL, 'nokey'),
(542, 'dffab31f89170b4deb514fe7559d23bf', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/dffab31f89170b4deb514fe7559d23bf.jpg', 24, '2021-12-06 09:16:40', 'f1.jpg.jpg', NULL, 'nokey'),
(543, 'c4d9cda17fcd9979aa0e04fe4932e595', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c4d9cda17fcd9979aa0e04fe4932e595.jpg', 24, '2021-12-06 09:16:42', 'f1.jpg.jpg.thumbnail', NULL, 'nokey'),
(544, '88fec2b0d62ac0242d99037a725ce2aa', 'video/mp4', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/88fec2b0d62ac0242d99037a725ce2aa.mp4', 24, '2021-12-06 09:18:03', 'ITJC.mp4', NULL, 'nokey'),
(545, 'c6c7b3bc945bd19617c6086e0956240d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c6c7b3bc945bd19617c6086e0956240d.jpg', 24, '2021-12-06 09:18:05', 'ITJC.mp4.thumbnail', NULL, 'nokey'),
(555, '0b278cc540e2097fdd9b999bac7223eb', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/0b278cc540e2097fdd9b999bac7223eb.jpg', 3, '2021-12-06 10:00:17', 'src=http___images.ifanr.cn_wp-content_uploads_2016_08_linus-Nvidia-.jpg&refer=http___images.ifanr.jpeg.jpeg.thumbnail', NULL, 'nokey'),
(560, '88fec2b0d62ac0242d99037a725ce2aa', 'video/mp4', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/88fec2b0d62ac0242d99037a725ce2aa.mp4', 24, '2021-12-07 02:02:47', 'ITJC.mp4', NULL, 'nokey'),
(561, 'c6c7b3bc945bd19617c6086e0956240d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c6c7b3bc945bd19617c6086e0956240d.jpg', 24, '2021-12-07 02:02:50', 'ITJC.mp4.thumbnail', NULL, 'nokey'),
(562, '7cebe728e73cfd502d74171b921df02d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/7cebe728e73cfd502d74171b921df02d.jpg', 24, '2021-12-07 03:25:48', 'bef322bb.glb', NULL, 'nokey'),
(563, '7cebe728e73cfd502d74171b921df02d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/7cebe728e73cfd502d74171b921df02d.jpg', 24, '2021-12-07 03:25:58', 'bef322cc.glb', NULL, 'nokey'),
(564, '7cebe728e73cfd502d74171b921df02d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/7cebe728e73cfd502d74171b921df02d.jpg', 24, '2021-12-07 03:26:07', 'bef322cc.glb', NULL, 'nokey'),
(565, '540892b35529a7e1e29db03fdb58ad33', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/540892b35529a7e1e29db03fdb58ad33.glb', 24, '2021-12-07 03:26:23', '54089e.glb', NULL, 'nokey'),
(566, '026efd4fdba1b91f448aa17392bd09b5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/026efd4fdba1b91f448aa17392bd09b5.jpg', 24, '2021-12-07 03:26:26', '54089e.glb', NULL, 'nokey'),
(567, '83644f09cfc39efc05c515dbbefeec87', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83644f09cfc39efc05c515dbbefeec87.jpg', 24, '2021-12-07 03:26:41', 'testpicture.jpg', NULL, 'nokey'),
(568, '88fec2b0d62ac0242d99037a725ce2aa', 'video/mp4', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/88fec2b0d62ac0242d99037a725ce2aa.mp4', 24, '2021-12-07 03:27:08', 'ITJC.mp4', NULL, 'nokey'),
(569, 'c6c7b3bc945bd19617c6086e0956240d', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c6c7b3bc945bd19617c6086e0956240d.jpg', 24, '2021-12-07 03:27:10', 'ITJC.mp4.thumbnail', NULL, 'nokey'),
(570, '540892b35529a7e1e29db03fdb58ad33', '.glb', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/540892b35529a7e1e29db03fdb58ad33.glb', 24, '2021-12-07 06:31:42', '54089d.glb', NULL, 'nokey'),
(571, '026efd4fdba1b91f448aa17392bd09b5', 'image/jpeg', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/026efd4fdba1b91f448aa17392bd09b5.jpg', 24, '2021-12-07 06:31:45', '54089d.glb', NULL, 'nokey'),
(585, 'a8e851094d6530cbafc6359168fc8ae9', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/a8e851094d6530cbafc6359168fc8ae9.glb', 304, '2021-12-13 02:38:12', 'a8e851094d6530cbafc6359168fc8ae9.glb', NULL, 'nokey'),
(586, 'a89fc55098ccc868224b5423b65eeb15', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/a89fc55098ccc868224b5423b65eeb15.jpg', 304, '2021-12-13 02:38:41', 'a89fc55098ccc868224b5423b65eeb15.jpg', NULL, 'nokey'),
(589, 'a92b60849f6cef1221d1905edca0a176', '.jpg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/a92b60849f6cef1221d1905edca0a176.jpg', 311, '2021-12-16 14:29:39', 'a92b60849f6cef1221d1905edca0a176.jpg', NULL, 'nokey'),
(590, 'f1414a2d9eb377c505ccb10f4d1c2116', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/f1414a2d9eb377c505ccb10f4d1c2116.jpg', 311, '2021-12-16 14:29:50', 'f1414a2d9eb377c505ccb10f4d1c2116.jpg', NULL, 'nokey'),
(593, '3468113754eca1241ed363f3d83ba05e', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/3468113754eca1241ed363f3d83ba05e.glb', 311, '2021-12-16 14:33:51', '3468113754eca1241ed363f3d83ba05e.glb', NULL, 'nokey'),
(594, '93577fd39f21428e87dade5278456915', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/93577fd39f21428e87dade5278456915.jpg', 311, '2021-12-16 14:34:00', '93577fd39f21428e87dade5278456915.jpg', NULL, 'nokey'),
(595, '5e352e90645696f5a381f1eaa60a62ac', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/5e352e90645696f5a381f1eaa60a62ac.glb', 315, '2021-12-22 01:29:09', '5e352e90645696f5a381f1eaa60a62ac.glb', NULL, 'nokey'),
(596, 'fb7360fcbdc325f4237fe42337fd1741', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/fb7360fcbdc325f4237fe42337fd1741.jpg', 315, '2021-12-22 01:33:38', 'fb7360fcbdc325f4237fe42337fd1741.jpg', NULL, 'nokey'),
(597, '913f6ff0396a712365e9ca1e285a8b7e', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/913f6ff0396a712365e9ca1e285a8b7e.glb', 315, '2021-12-22 01:34:31', '913f6ff0396a712365e9ca1e285a8b7e.glb', NULL, 'nokey'),
(598, 'd9852ddf7c86ad7cb8a160b7cc027d91', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/d9852ddf7c86ad7cb8a160b7cc027d91.jpg', 315, '2021-12-22 01:34:46', 'd9852ddf7c86ad7cb8a160b7cc027d91.jpg', NULL, 'nokey'),
(599, 'f2ca1c653fbbb9ef3b37e3f14aae671b', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/f2ca1c653fbbb9ef3b37e3f14aae671b.glb', 315, '2021-12-22 01:36:26', 'f2ca1c653fbbb9ef3b37e3f14aae671b.glb', NULL, 'nokey'),
(600, '629115708d585180305c5acc6e3ed32a', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/629115708d585180305c5acc6e3ed32a.jpg', 315, '2021-12-22 01:37:01', '629115708d585180305c5acc6e3ed32a.jpg', NULL, 'nokey'),
(601, '2139cba2dee78b8d802b07a125fd863c', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/2139cba2dee78b8d802b07a125fd863c.glb', 315, '2021-12-22 02:05:44', '2139cba2dee78b8d802b07a125fd863c.glb', NULL, 'nokey'),
(602, 'bd8e46a83006db59a5e6008ecfb6e3d5', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/bd8e46a83006db59a5e6008ecfb6e3d5.jpg', 315, '2021-12-22 02:06:02', 'bd8e46a83006db59a5e6008ecfb6e3d5.jpg', NULL, 'nokey'),
(614, '72717920fe5e51d7123820c32aeb28be', '.PNG', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/72717920fe5e51d7123820c32aeb28be.PNG', 316, '2021-12-23 23:54:30', '72717920fe5e51d7123820c32aeb28be.PNG', NULL, 'nokey'),
(615, '3f44a097ad2082180e642744d43c3442', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/3f44a097ad2082180e642744d43c3442.jpg', 316, '2021-12-23 23:54:33', '3f44a097ad2082180e642744d43c3442.jpg', NULL, 'nokey'),
(618, 'a2be8efe66a71709ad8b766f80e3e55c', '.gltf', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/a2be8efe66a71709ad8b766f80e3e55c.gltf', 316, '2021-12-24 00:29:03', 'a2be8efe66a71709ad8b766f80e3e55c.gltf', NULL, 'nokey'),
(619, '580e0083972f618ce58b5bfc84cd06fd', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/580e0083972f618ce58b5bfc84cd06fd.jpg', 316, '2021-12-24 00:29:09', '580e0083972f618ce58b5bfc84cd06fd.jpg', NULL, 'nokey'),
(630, 'e93041769006523ce40489906e05ae37', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/e93041769006523ce40489906e05ae37.glb', 320, '2021-12-25 12:15:08', 'e93041769006523ce40489906e05ae37.glb', NULL, 'nokey'),
(631, 'b970e6097c4f046f678e89baf6a4e819', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/b970e6097c4f046f678e89baf6a4e819.jpg', 320, '2021-12-25 12:15:32', 'b970e6097c4f046f678e89baf6a4e819.jpg', NULL, 'nokey'),
(632, 'fad787015706bafbe7e8858221fb02aa', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/fad787015706bafbe7e8858221fb02aa.glb', 320, '2021-12-25 15:26:23', 'fad787015706bafbe7e8858221fb02aa.glb', NULL, 'nokey'),
(633, 'c8e8c3b77663d52c65607722b7b6f653', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/c8e8c3b77663d52c65607722b7b6f653.jpg', 320, '2021-12-25 15:26:33', 'c8e8c3b77663d52c65607722b7b6f653.jpg', NULL, 'nokey'),
(634, '0ede734a4c1f62896b577eacfe25b59a', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/0ede734a4c1f62896b577eacfe25b59a.glb', 320, '2021-12-25 15:29:40', '0ede734a4c1f62896b577eacfe25b59a.glb', NULL, 'nokey'),
(635, 'defe033815ee82d283c68e16a0ad9bff', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/defe033815ee82d283c68e16a0ad9bff.jpg', 320, '2021-12-25 15:29:53', 'defe033815ee82d283c68e16a0ad9bff.jpg', NULL, 'nokey'),
(640, '2471eadf3ab3d65465ff97a807f09198', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/2471eadf3ab3d65465ff97a807f09198.glb', 316, '2021-12-26 08:41:22', '2471eadf3ab3d65465ff97a807f09198.glb', NULL, 'nokey'),
(641, '7b22d00cfd13ca730517a6c118fc854c', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/7b22d00cfd13ca730517a6c118fc854c.jpg', 316, '2021-12-26 08:41:41', '7b22d00cfd13ca730517a6c118fc854c.jpg', NULL, 'nokey'),
(642, 'b2f73da51d3363b3c36ef477a0d3a39b', '.gltf', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/b2f73da51d3363b3c36ef477a0d3a39b.gltf', 316, '2021-12-26 08:56:30', 'b2f73da51d3363b3c36ef477a0d3a39b.gltf', NULL, 'nokey'),
(643, '6019ed4d382a362db456dd9e93511763', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/6019ed4d382a362db456dd9e93511763.jpg', 316, '2021-12-26 08:56:36', '6019ed4d382a362db456dd9e93511763.jpg', NULL, 'nokey'),
(644, '1f01f9a43789b93b12d3e435a79db5d1', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/1f01f9a43789b93b12d3e435a79db5d1.glb', 323, '2021-12-26 14:53:07', '1f01f9a43789b93b12d3e435a79db5d1.glb', NULL, 'nokey'),
(645, '6579acd38b3206a46351068d6c9307e6', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/6579acd38b3206a46351068d6c9307e6.jpg', 323, '2021-12-26 14:53:16', '6579acd38b3206a46351068d6c9307e6.jpg', NULL, 'nokey'),
(646, '8aa0f006e89e62b00a9c5f36a3c674ee', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/8aa0f006e89e62b00a9c5f36a3c674ee.glb', 323, '2021-12-27 12:43:01', '8aa0f006e89e62b00a9c5f36a3c674ee.glb', NULL, 'nokey'),
(647, '8a973e3e2bc60be4ed8f1c7199894473', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/8a973e3e2bc60be4ed8f1c7199894473.jpg', 323, '2021-12-27 12:43:05', '8a973e3e2bc60be4ed8f1c7199894473.jpg', NULL, 'nokey'),
(649, '92c49e0df91c3d6c6c054d0a3d7b56d0', '.glb', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/92c49e0df91c3d6c6c054d0a3d7b56d0.glb', 329, '2022-01-09 04:04:54', '92c49e0df91c3d6c6c054d0a3d7b56d0.glb', NULL, 'nokey'),
(650, 'bd8e46a83006db59a5e6008ecfb6e3d5', 'image/jpeg', 'https://files-1257979353.cos.ap-chengdu.myqcloud.com/bd8e46a83006db59a5e6008ecfb6e3d5.jpg', 329, '2022-01-09 04:04:58', 'bd8e46a83006db59a5e6008ecfb6e3d5.jpg', NULL, 'nokey'),
(652, '62837ea959b9d58818d75c794a7f5cb7', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/62837ea959b9d58818d75c794a7f5cb7.jpg', 3, '2022-01-13 07:05:02', 'dirui.avatar', NULL, 'nokey'),
(653, 'a8716eeefb935ab0363022acce81fcd8', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/a8716eeefb935ab0363022acce81fcd8.jpg', 3, '2022-01-14 16:02:59', 'dirui.avatar', NULL, 'nokey'),
(654, '52675c8d8b3e5202f9c101b9d5ffe169', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/52675c8d8b3e5202f9c101b9d5ffe169.jpg', 3, '2022-01-14 16:03:32', 'dirui.avatar', NULL, 'nokey'),
(655, '220a1356206a10e555e16e9961c4c6e9', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/220a1356206a10e555e16e9961c4c6e9.jpg', 3, '2022-01-27 02:20:22', 'verse.picture', 730345, '220a1356206a10e555e16e9961c4c6e9.jpg'),
(657, '8e4514bdad609ad34ecd50dd75505e6a', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/8e4514bdad609ad34ecd50dd75505e6a.jpg', 3, '2022-01-27 18:12:09', 'verse.picture', 112025, '8e4514bdad609ad34ecd50dd75505e6a.jpg'),
(658, 'bd58bd2da4f36df0d1896cf986bf174f', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bd58bd2da4f36df0d1896cf986bf174f.jpg', 3, '2022-01-27 18:12:50', 'verse.picture', 556015, 'bd58bd2da4f36df0d1896cf986bf174f.jpg'),
(659, 'c0e1a32f5e29bf1ddf76462d97e32711', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/c0e1a32f5e29bf1ddf76462d97e32711.jpg', 24, '2022-01-29 09:48:13', 'verse.picture', 261905, 'c0e1a32f5e29bf1ddf76462d97e32711.jpg'),
(660, '53286e3ea32c959186e4ee9d847c893a', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/53286e3ea32c959186e4ee9d847c893a.jpg', 24, '2022-01-30 10:11:08', 'guanfei.avatar', 140681, '53286e3ea32c959186e4ee9d847c893a.jpg'),
(661, 'e71caad5e5858ccef40ea31c3c9f176d', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/e71caad5e5858ccef40ea31c3c9f176d.jpg', 24, '2022-01-30 10:32:31', 'verse.picture', 139876, 'e71caad5e5858ccef40ea31c3c9f176d.jpg'),
(662, 'a6436c28fcceedf415fcb96e654c97e1', 'image/png', 'http://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/a6436c28fcceedf415fcb96e654c97e1.jpg', 336, '2022-01-31 10:00:10', 'dirui2022.avatar', 179460, 'a6436c28fcceedf415fcb96e654c97e1.jpg'),
(663, 'e1bfee761b3b4f97d1644b3d9f334e90', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/e1bfee761b3b4f97d1644b3d9f334e90.jpg', 316, '2022-01-31 21:26:07', 'brainslicer.avatar', 104807, 'e1bfee761b3b4f97d1644b3d9f334e90.jpg'),
(664, '8c5ecd2927556327f430ab0b94d811f1', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/8c5ecd2927556327f430ab0b94d811f1.jpg', 337, '2022-02-02 08:11:26', 'verse.picture', 175824, '8c5ecd2927556327f430ab0b94d811f1.jpg'),
(665, '92c49e0df91c3d6c6c054d0a3d7b56d0', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/92c49e0df91c3d6c6c054d0a3d7b56d0.glb', 337, '2022-02-02 08:14:56', '3232.glb', 645604, '92c49e0df91c3d6c6c054d0a3d7b56d0.glb'),
(666, '41ea8e2f8fe53c74b9bfd565c4b6ce48', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg', 337, '2022-02-02 08:15:00', '3232.glb', 13389, '41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg'),
(667, '41ea8e2f8fe53c74b9bfd565c4b6ce48', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg', 337, '2022-02-02 08:16:35', '3232.glb', 13389, '41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg'),
(669, '41ea8e2f8fe53c74b9bfd565c4b6ce48', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg', 337, '2022-02-02 08:39:39', '3232.glb', 13389, '41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg'),
(670, 'd2cc96db137df7ad4fc277650cdad747', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/d2cc96db137df7ad4fc277650cdad747.jpg', 339, '2022-02-02 10:50:10', 'ZX1999.avatar', 567989, 'd2cc96db137df7ad4fc277650cdad747.jpg'),
(671, 'a1bd5199151363353e5ceafcbea64e97', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/a1bd5199151363353e5ceafcbea64e97.glb', 338, '2022-02-02 15:03:07', 'SpaceShip.glb', 17993788, 'a1bd5199151363353e5ceafcbea64e97.glb'),
(672, '0d1080f5c0bdbb25b281c29adc36981b', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/0d1080f5c0bdbb25b281c29adc36981b.jpg', 338, '2022-02-02 15:03:50', 'SpaceShip.glb', 41716, '0d1080f5c0bdbb25b281c29adc36981b.jpg'),
(673, '0d1080f5c0bdbb25b281c29adc36981b', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/0d1080f5c0bdbb25b281c29adc36981b.jpg', 338, '2022-02-02 15:08:59', 'SpaceShip.glb', 41716, '0d1080f5c0bdbb25b281c29adc36981b.jpg'),
(674, '4a35bd2ad3a9c094c04fcbe1be7ff57d', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/4a35bd2ad3a9c094c04fcbe1be7ff57d.glb', 338, '2022-02-02 15:16:02', 'Clider.glb', 12332, '4a35bd2ad3a9c094c04fcbe1be7ff57d.glb'),
(675, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-02 15:16:05', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(676, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-02 15:18:48', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(677, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-02 15:20:54', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(678, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-02 15:21:58', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(679, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-02 15:22:12', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(680, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-02 15:23:23', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(681, '770ce85d40e3616f959b75b768d4eaf1', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/770ce85d40e3616f959b75b768d4eaf1.jpg', 338, '2022-02-02 15:24:56', 'Clider.glb', 34755, '770ce85d40e3616f959b75b768d4eaf1.jpg'),
(682, '669b4dd54f9ff236c8f460f7d11e38c8', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/669b4dd54f9ff236c8f460f7d11e38c8.jpg', 338, '2022-02-03 02:09:21', 'Clider.glb', 26601, '669b4dd54f9ff236c8f460f7d11e38c8.jpg'),
(683, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-03 03:13:45', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(684, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-03 14:23:53', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(685, 'eb0025ca356a4773304c08c99bd10cf1', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/eb0025ca356a4773304c08c99bd10cf1.jpg', 24, '2022-02-07 02:00:08', 'verse.picture', 305893, 'eb0025ca356a4773304c08c99bd10cf1.jpg'),
(686, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-07 03:43:22', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(687, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-07 03:49:03', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(689, 'a3dd31a431535d76ee5e3fb540f0f2ab', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/a3dd31a431535d76ee5e3fb540f0f2ab.jpg', 337, '2022-02-07 05:21:57', '1#常规岛（0207）.glb', 23949, 'a3dd31a431535d76ee5e3fb540f0f2ab.jpg'),
(690, '770ce85d40e3616f959b75b768d4eaf1', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/770ce85d40e3616f959b75b768d4eaf1.jpg', 338, '2022-02-07 15:46:47', 'Clider.glb', 34755, '770ce85d40e3616f959b75b768d4eaf1.jpg'),
(691, '230c1eaf06fdb94ebd205b848a6f1480', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/230c1eaf06fdb94ebd205b848a6f1480.jpg', 338, '2022-02-07 15:47:11', 'Clider.glb', 36158, '230c1eaf06fdb94ebd205b848a6f1480.jpg'),
(692, 'b4927ba687709d3b7200f425d949b011', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/b4927ba687709d3b7200f425d949b011.jpg', 315, '2022-02-08 02:14:18', 'verse.picture', 471158, 'b4927ba687709d3b7200f425d949b011.jpg'),
(693, 'bd8e46a83006db59a5e6008ecfb6e3d5', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bd8e46a83006db59a5e6008ecfb6e3d5.jpg', 337, '2022-02-08 04:12:43', '1#常规岛（0207）.glb', 6985, 'bd8e46a83006db59a5e6008ecfb6e3d5.jpg'),
(694, '4be835a8f730961b1a6ba0ec821dcc47', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/4be835a8f730961b1a6ba0ec821dcc47.jpg', 337, '2022-02-08 04:14:02', '1#常规岛（0207）.glb', 24726, '4be835a8f730961b1a6ba0ec821dcc47.jpg'),
(695, '630d5de3ff107d57ee26e91e3b872552', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/630d5de3ff107d57ee26e91e3b872552.jpg', 338, '2022-02-08 05:14:05', 'Clider.glb', 25911, '630d5de3ff107d57ee26e91e3b872552.jpg'),
(696, 'a590a5b25f718e5feee6d9380ee71a3c', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/a590a5b25f718e5feee6d9380ee71a3c.jpg', 350, '2022-02-08 05:22:43', 'verse.picture', 122997, 'a590a5b25f718e5feee6d9380ee71a3c.jpg'),
(697, '4fffba657740ab5cd5fcb17e2edd9644', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/4fffba657740ab5cd5fcb17e2edd9644.jpg', 3, '2022-02-08 09:40:04', 'verse.picture', 627474, '4fffba657740ab5cd5fcb17e2edd9644.jpg'),
(699, '57ffd443cf3223df11fcab4c3ceefa7e', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/57ffd443cf3223df11fcab4c3ceefa7e.jpg', 3, '2022-02-08 12:31:47', 'verse.picture', 495201, '57ffd443cf3223df11fcab4c3ceefa7e.jpg'),
(700, '75d10f093aa9b75a31b2006d5091a01b', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/75d10f093aa9b75a31b2006d5091a01b.jpg', 338, '2022-02-09 14:56:51', 'verse.picture', 3726, '75d10f093aa9b75a31b2006d5091a01b.jpg'),
(701, '630d5de3ff107d57ee26e91e3b872552', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/630d5de3ff107d57ee26e91e3b872552.jpg', 338, '2022-02-09 14:57:57', 'Clider.glb', 25911, '630d5de3ff107d57ee26e91e3b872552.jpg'),
(702, '751cc43eb12b4150182f5f2cd51f261f', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/751cc43eb12b4150182f5f2cd51f261f.jpg', 338, '2022-02-09 14:58:17', 'SpaceShip.glb', 41601, '751cc43eb12b4150182f5f2cd51f261f.jpg'),
(703, '8b2c893a47170019533cbfbf82310c00', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/8b2c893a47170019533cbfbf82310c00.jpg', 265, '2022-02-10 04:16:00', 'testdelancy1.avatar', 239039, '8b2c893a47170019533cbfbf82310c00.jpg'),
(704, '630d5de3ff107d57ee26e91e3b872552', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/630d5de3ff107d57ee26e91e3b872552.jpg', 338, '2022-02-10 10:09:07', 'Clider.glb', 25911, '630d5de3ff107d57ee26e91e3b872552.jpg'),
(705, '630d5de3ff107d57ee26e91e3b872552', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/630d5de3ff107d57ee26e91e3b872552.jpg', 338, '2022-02-10 10:10:14', 'Clider.glb', 25911, '630d5de3ff107d57ee26e91e3b872552.jpg'),
(706, '630d5de3ff107d57ee26e91e3b872552', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/630d5de3ff107d57ee26e91e3b872552.jpg', 338, '2022-02-10 10:10:28', 'Clider.glb', 25911, '630d5de3ff107d57ee26e91e3b872552.jpg'),
(707, '730151490e1343070ed6954bb5268816', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/730151490e1343070ed6954bb5268816.jpg', 338, '2022-02-10 12:36:02', 'Clider.glb', 26095, '730151490e1343070ed6954bb5268816.jpg'),
(708, '630d5de3ff107d57ee26e91e3b872552', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/630d5de3ff107d57ee26e91e3b872552.jpg', 338, '2022-02-11 13:03:11', 'Clider.glb', 25911, '630d5de3ff107d57ee26e91e3b872552.jpg'),
(709, '751cc43eb12b4150182f5f2cd51f261f', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/751cc43eb12b4150182f5f2cd51f261f.jpg', 338, '2022-02-11 13:03:57', 'SpaceShip.glb', 41601, '751cc43eb12b4150182f5f2cd51f261f.jpg'),
(710, 'd65bd5a2c689f3120cd5dc0ed0fb6444', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/d65bd5a2c689f3120cd5dc0ed0fb6444.jpg', 25, '2022-02-12 02:21:42', 'verse.picture', 719240, 'd65bd5a2c689f3120cd5dc0ed0fb6444.jpg'),
(711, '1808e8a40af02357eb75a4d61f3f4d40', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/1808e8a40af02357eb75a4d61f3f4d40.jpg', 25, '2022-02-12 02:51:22', 'dirui1981.avatar', 216748, '1808e8a40af02357eb75a4d61f3f4d40.jpg'),
(713, '3090b7ea5aafe244b52f2201c4ce7e5b', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/3090b7ea5aafe244b52f2201c4ce7e5b.jpeg', 25, '2022-02-12 02:52:58', 'src=http___inews.gtimg.com_newsapp_bt_0_13887652789_641.jpg&refer=http___inews.gtimg.jpeg', 21558, '3090b7ea5aafe244b52f2201c4ce7e5b.jpeg'),
(714, '98b4f7fbe2b422e656d6165e0045bb9a', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/98b4f7fbe2b422e656d6165e0045bb9a.glb', 357, '2022-02-13 01:21:17', 'new.glb', 2330352, '98b4f7fbe2b422e656d6165e0045bb9a.glb'),
(715, 'a82d9ee785f221e4221bf4b0ed644f22', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/a82d9ee785f221e4221bf4b0ed644f22.jpg', 357, '2022-02-13 01:21:21', 'new.glb', 39111, 'a82d9ee785f221e4221bf4b0ed644f22.jpg');
INSERT INTO `file` (`id`, `md5`, `type`, `url`, `user_id`, `created_at`, `filename`, `size`, `key`) VALUES
(716, '9951fd71d7e85490908835c30c9a2e84', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/9951fd71d7e85490908835c30c9a2e84.jpg', 357, '2022-02-13 06:07:11', '1.jpg', 524077, '9951fd71d7e85490908835c30c9a2e84.jpg'),
(717, 'd3ccd4c268af265b351c2ee1d44a5251', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/d3ccd4c268af265b351c2ee1d44a5251.jpg', 357, '2022-02-13 06:07:14', '1.jpg.thumbnail', 106941, 'd3ccd4c268af265b351c2ee1d44a5251.jpg'),
(718, 'b5267d11e179de2464f21b4e6f2a2482', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/b5267d11e179de2464f21b4e6f2a2482.jpg', 357, '2022-02-13 06:46:47', 'verse.picture', 171553, 'b5267d11e179de2464f21b4e6f2a2482.jpg'),
(724, '379167fac31586a9ba3c98389a644065', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/379167fac31586a9ba3c98389a644065.jpg', 3, '2022-02-14 02:48:58', 'verse.picture', 281233, '379167fac31586a9ba3c98389a644065.jpg'),
(729, '0b68138cf2f7b17f3fa37087b3292b81', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/0b68138cf2f7b17f3fa37087b3292b81.jpg', 24, '2022-02-14 06:44:55', 'verse.picture', 386173, '0b68138cf2f7b17f3fa37087b3292b81.jpg'),
(736, 'bffe45cfdfa8c80fd5b78a024ba9f73f', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bffe45cfdfa8c80fd5b78a024ba9f73f.glb', 357, '2022-02-15 05:49:58', 'shiba.glb', 1982544, 'bffe45cfdfa8c80fd5b78a024ba9f73f.glb'),
(737, '3412f089d26fa60dc0b1b665bb672841', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/3412f089d26fa60dc0b1b665bb672841.jpg', 357, '2022-02-15 05:50:01', 'shiba.glb', 49179, '3412f089d26fa60dc0b1b665bb672841.jpg'),
(738, '316c478f9129a17fdb12cd53d54e261d', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/316c478f9129a17fdb12cd53d54e261d.glb', 3, '2022-02-15 12:24:05', 'hololens2.glb', 32615264, '316c478f9129a17fdb12cd53d54e261d.glb'),
(739, '72c8b73fa179830a02284ce7e1ea1dff', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/72c8b73fa179830a02284ce7e1ea1dff.jpg', 3, '2022-02-15 12:24:18', 'hololens2.glb', 92588, '72c8b73fa179830a02284ce7e1ea1dff.jpg'),
(740, 'aa687cb93c7a13861d60f8fc7a5774bf', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/aa687cb93c7a13861d60f8fc7a5774bf.jpg', 3, '2022-02-15 12:25:01', 'verse.picture', 182648, 'aa687cb93c7a13861d60f8fc7a5774bf.jpg'),
(741, '4b39501769eaf76dfa89ce9107f72e07', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/4b39501769eaf76dfa89ce9107f72e07.jpeg', 3, '2022-02-16 08:59:50', 'Astronaut-scaled.jpeg', 66775, '4b39501769eaf76dfa89ce9107f72e07.jpeg'),
(743, 'e87e235cfbf7682e53cb8aa206a92b8d', 'video/mp4', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/e87e235cfbf7682e53cb8aa206a92b8d.mp4', 3, '2022-02-18 02:40:10', '504337197-1-208.mp4', 13921089, 'e87e235cfbf7682e53cb8aa206a92b8d.mp4'),
(744, 'abfdcd3fa48a057d2407b038b2c55ec2', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/abfdcd3fa48a057d2407b038b2c55ec2.jpg', 3, '2022-02-18 02:40:12', '504337197-1-208.mp4.thumbnail', 72722, 'abfdcd3fa48a057d2407b038b2c55ec2.jpg'),
(745, '41ea8e2f8fe53c74b9bfd565c4b6ce48', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg', 337, '2022-02-20 11:34:46', '3232.glb', 13389, '41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg'),
(746, '92c49e0df91c3d6c6c054d0a3d7b56d0', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/92c49e0df91c3d6c6c054d0a3d7b56d0.glb', 337, '2022-02-20 11:47:03', '3232.glb', 645604, '92c49e0df91c3d6c6c054d0a3d7b56d0.glb'),
(747, '41ea8e2f8fe53c74b9bfd565c4b6ce48', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg', 337, '2022-02-20 11:47:05', '3232.glb', 13389, '41ea8e2f8fe53c74b9bfd565c4b6ce48.jpg'),
(748, '5127a75213980068923d0bb6090b3074', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/5127a75213980068923d0bb6090b3074.glb', 337, '2022-02-20 11:53:46', '1#常规岛（0207）.glb', 79262256, '5127a75213980068923d0bb6090b3074.glb'),
(749, '4be835a8f730961b1a6ba0ec821dcc47', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/4be835a8f730961b1a6ba0ec821dcc47.jpg', 337, '2022-02-20 11:54:58', '1#常规岛（0207）.glb', 24726, '4be835a8f730961b1a6ba0ec821dcc47.jpg'),
(750, 'da12ef46905ca6883d3c5aaaf9adcb05', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/da12ef46905ca6883d3c5aaaf9adcb05.glb', 337, '2022-02-20 12:28:13', 'magic-leap-2.glb', 4753088, 'da12ef46905ca6883d3c5aaaf9adcb05.glb'),
(751, '13402737040d57dcce2050d1c2adbc2a', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/13402737040d57dcce2050d1c2adbc2a.jpg', 337, '2022-02-20 12:28:18', 'magic-leap-2.glb', 45683, '13402737040d57dcce2050d1c2adbc2a.jpg'),
(752, 'bef32228dc2bf8be91814a9068e927c3', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/bef32228dc2bf8be91814a9068e927c3.glb', 3, '2022-02-22 03:25:12', 'BoomBox.glb', 10945640, 'bef32228dc2bf8be91814a9068e927c3.glb'),
(753, '5552fbee21eb91a4fd59fc0822ad7bcd', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/5552fbee21eb91a4fd59fc0822ad7bcd.jpg', 3, '2022-02-22 03:25:16', 'BoomBox.glb', 55576, '5552fbee21eb91a4fd59fc0822ad7bcd.jpg'),
(754, '965a8e266a8f42299e6baf8537e02551', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/965a8e266a8f42299e6baf8537e02551.jpg', 3, '2022-02-22 03:38:51', 'verse.picture', 193645, '965a8e266a8f42299e6baf8537e02551.jpg'),
(755, 'f8fef39ea9a06f4bd8c379e6b54e5a5c', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/f8fef39ea9a06f4bd8c379e6b54e5a5c.jpg', 368, '2022-02-23 03:20:31', 'verse.picture', 125389, 'f8fef39ea9a06f4bd8c379e6b54e5a5c.jpg'),
(756, '62f2e8803e28cf83ccb3b2a7a1ba34fd', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/62f2e8803e28cf83ccb3b2a7a1ba34fd.jpg', 368, '2022-02-23 10:47:19', 'verse.picture', 32548, '62f2e8803e28cf83ccb3b2a7a1ba34fd.jpg'),
(757, 'ebe9ecc6e24b31f4d4275c17b3d8d3fe', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/ebe9ecc6e24b31f4d4275c17b3d8d3fe.glb', 368, '2022-02-23 13:04:51', '厕所架2.0.glb', 14612, 'ebe9ecc6e24b31f4d4275c17b3d8d3fe.glb'),
(758, '23a3a9a9f5847dcfcc05f3c67a7a0d05', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/23a3a9a9f5847dcfcc05f3c67a7a0d05.jpg', 368, '2022-02-23 13:04:54', '厕所架2.0.glb', 17869, '23a3a9a9f5847dcfcc05f3c67a7a0d05.jpg'),
(759, '9d9506b16938fa6e6ca509613e8d3136', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/9d9506b16938fa6e6ca509613e8d3136.jpg', 368, '2022-02-23 13:06:27', 'monster_by_mineworker-d419sgc.jpg', 240212, '9d9506b16938fa6e6ca509613e8d3136.jpg'),
(760, '8d5cdce78866e109ece9718da71dc9e7', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/8d5cdce78866e109ece9718da71dc9e7.jpg', 368, '2022-02-23 13:06:29', 'monster_by_mineworker-d419sgc.jpg.thumbnail', 27500, '8d5cdce78866e109ece9718da71dc9e7.jpg'),
(761, '565f1da0eeebc02130b965394711b2b4', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/565f1da0eeebc02130b965394711b2b4.glb', 368, '2022-02-23 13:27:22', '高模冰墩墩.glb', 10933436, '565f1da0eeebc02130b965394711b2b4.glb'),
(762, '83853813d1d3599ebbee861595a80af1', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/83853813d1d3599ebbee861595a80af1.jpg', 368, '2022-02-23 13:27:27', '高模冰墩墩.glb', 35763, '83853813d1d3599ebbee861595a80af1.jpg'),
(764, '5b5fdd537c4f6b3c84bdcae052e5e861', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/5b5fdd537c4f6b3c84bdcae052e5e861.glb', 337, '2022-02-24 01:41:53', 'pipe.glb', 5835712, '5b5fdd537c4f6b3c84bdcae052e5e861.glb'),
(765, '5befb18cd0e85c55d6d44b914a4a55a7', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/5befb18cd0e85c55d6d44b914a4a55a7.jpg', 337, '2022-02-24 01:44:17', 'pipe', 85788, '5befb18cd0e85c55d6d44b914a4a55a7.jpg'),
(766, '79027a77954facf2f404bc24133724ae', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/79027a77954facf2f404bc24133724ae.jpg', 337, '2022-02-24 01:50:24', 'rekcah.avatar', 196760, '79027a77954facf2f404bc24133724ae.jpg'),
(767, '64d8608f887dfd8dce626895c249ed8b', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/64d8608f887dfd8dce626895c249ed8b.jpg', 368, '2022-02-24 02:41:34', '13976.jpg', 30180, '64d8608f887dfd8dce626895c249ed8b.jpg'),
(768, 'dc564b0d56c1f4b1b5b5e5105c36b3fa', 'image/png', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/dc564b0d56c1f4b1b5b5e5105c36b3fa.jpg', 368, '2022-02-24 04:00:22', 'sem2022.avatar', 264957, 'dc564b0d56c1f4b1b5b5e5105c36b3fa.jpg'),
(769, '42803726707875a3a23344f663b308cb', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/42803726707875a3a23344f663b308cb.glb', 3, '2022-02-24 04:09:42', 'lymphatic.glb', 41758456, '42803726707875a3a23344f663b308cb.glb'),
(770, '15e62f276bf634807758a00db647a139', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/15e62f276bf634807758a00db647a139.jpg', 3, '2022-02-24 04:11:12', 'lymphatic.glb', 123135, '15e62f276bf634807758a00db647a139.jpg'),
(771, '559433a48bd081cada7013566234a21e', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/559433a48bd081cada7013566234a21e.glb', 3, '2022-02-24 04:11:20', 'craneo.glb', 3476864, '559433a48bd081cada7013566234a21e.glb'),
(772, '6a0f54d98534feacf9ff4bcacdc96d44', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/6a0f54d98534feacf9ff4bcacdc96d44.jpg', 3, '2022-02-24 04:11:25', 'craneo.glb', 36773, '6a0f54d98534feacf9ff4bcacdc96d44.jpg'),
(773, 'efbbeff16d0aee43de387cb898c704e0', 'application/octet-stream', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/efbbeff16d0aee43de387cb898c704e0.glb', 3, '2022-02-24 07:03:31', 'GLB-MODE.glb', 63994516, 'efbbeff16d0aee43de387cb898c704e0.glb'),
(774, '924a00757c1656ae58e3af7aa237a02d', 'image/jpeg', 'https://mrpp-1257979353.cos.ap-chengdu.myqcloud.com/924a00757c1656ae58e3af7aa237a02d.jpg', 3, '2022-02-24 07:03:42', 'GLB-MODE.glb', 46088, '924a00757c1656ae58e3af7aa237a02d.jpg');

-- --------------------------------------------------------

--
-- 表的结构 `file_store`
--

CREATE TABLE `file_store` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `goods`
--

CREATE TABLE `goods` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `used` tinyint(1) DEFAULT NULL,
  `auth_item_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `invitation`
--

INSERT INTO `invitation` (`id`, `code`, `sender_id`, `recipient_id`, `used`, `auth_item_name`, `create_at`) VALUES
(1, '3_1559272967', 3, 233, NULL, 'user', '2019-06-13 11:34:29'),
(2, '3_1559653089', 3, NULL, NULL, 'developer', '2019-06-13 11:34:29'),
(4, '3_1561879917', 3, 25, NULL, 'user', '2019-06-30 07:31:57'),
(5, '3_1561880434', 3, 26, NULL, 'user', '2019-06-30 07:40:34'),
(6, '3_1561882384', 3, 27, NULL, 'user', '2019-06-30 08:13:04'),
(7, '3_1561883431', 3, NULL, NULL, 'user', '2019-06-30 08:30:31'),
(8, '3_1561883926', 3, 29, NULL, 'user', '2019-06-30 08:38:46'),
(9, '3_1561884039', 3, 34, NULL, 'user', '2019-06-30 08:40:39'),
(10, '3_1561885795', 3, 28, NULL, 'user', '2019-06-30 09:09:55'),
(11, '3_1561904638', 3, 32, NULL, 'user', '2019-06-30 14:23:58'),
(12, '3_1561909164', 3, 36, NULL, 'user', '2019-06-30 15:39:24'),
(13, '3_1561934654', 3, 33, NULL, 'user', '2019-06-30 22:44:14'),
(14, '3_1561938118', 3, 31, NULL, 'user', '2019-06-30 23:41:58'),
(15, '3_1561948988', 3, 35, NULL, 'user', '2019-07-01 02:43:08'),
(16, '3_1561991830', 3, 37, NULL, 'user', '2019-07-01 14:37:10'),
(17, '3_1562140289', 3, 38, NULL, 'user', '2019-07-03 07:51:29'),
(18, '3_1562290606', 3, 40, NULL, 'user', '2019-07-05 01:36:46'),
(19, '3_1563249514', 3, 41, NULL, 'user', '2019-07-16 03:58:34'),
(20, '3_1563268621', 3, 42, NULL, 'user', '2019-07-16 09:17:01'),
(21, '3_1563785512', 3, 43, NULL, 'user', '2019-07-22 08:51:52'),
(22, '3_1564543428', 3, 44, NULL, 'user', '2019-07-31 03:23:48'),
(23, '3_1564711148', 3, 47, NULL, 'user', '2019-08-02 01:59:08'),
(24, '3_1564713233', 3, 46, NULL, 'user', '2019-08-02 02:33:53'),
(25, '3_1564715082', 3, 48, NULL, 'user', '2019-08-02 03:04:42'),
(26, '3_1564976125', 3, 49, NULL, 'user', '2019-08-05 03:35:25'),
(27, '3_1565066783', 3, 50, NULL, 'user', '2019-08-06 04:46:23'),
(28, '3_1565074977', 3, 51, NULL, 'user', '2019-08-06 07:02:57'),
(29, '3_1565138959', 3, 52, NULL, 'user', '2019-08-07 00:49:19'),
(30, '3_1565252853', 3, 53, NULL, 'user', '2019-08-08 08:27:33'),
(31, '3_1565578210', 3, 54, NULL, 'user', '2019-08-12 02:50:10'),
(32, '3_1566546512', 3, 55, NULL, 'user', '2019-08-23 07:48:32'),
(33, '3_1566570342', 3, 56, NULL, 'user', '2019-08-23 14:25:42'),
(34, '3_1566574606', 3, NULL, NULL, 'user', '2019-08-23 15:36:46'),
(35, '3_1566593128', 3, 57, NULL, 'user', '2019-08-23 20:45:28'),
(36, '3_1566652516', 3, 58, NULL, 'user', '2019-08-24 13:15:16'),
(37, '3_1566826615', 3, 59, NULL, 'user', '2019-08-26 13:36:55'),
(39, '3_1566896057', 3, 60, NULL, 'user', '2019-08-27 08:54:17'),
(40, '3_1566982812', 3, 61, NULL, 'user', '2019-08-28 09:00:12'),
(41, '3_1567019209', 3, 62, NULL, 'user', '2019-08-28 19:06:49'),
(42, '3_1567058607', 3, 63, NULL, 'user', '2019-08-29 06:03:27'),
(43, '3_1567060686', 3, 64, NULL, 'user', '2019-08-29 06:38:06'),
(44, '3_1567130519', 3, 65, NULL, 'user', '2019-08-30 02:01:59'),
(45, '3_1567132500', 3, 66, NULL, 'user', '2019-08-30 02:35:00'),
(46, '3_1567137405', 3, 67, NULL, 'user', '2019-08-30 03:56:45'),
(47, '3_1567152297', 3, 68, NULL, 'user', '2019-08-30 08:04:57'),
(48, '3_1567164164', 3, 69, NULL, 'user', '2019-08-30 11:22:44'),
(49, '3_1567176626', 3, 73, NULL, 'user', '2019-08-30 14:50:26'),
(50, '3_1567205261', 3, 71, NULL, 'user', '2019-08-30 22:47:41'),
(51, '3_1567216020', 3, 72, NULL, 'user', '2019-08-31 01:47:00'),
(52, '3_1567221205', 3, 74, NULL, 'user', '2019-08-31 03:13:25'),
(53, '3_1567235502', 3, 75, NULL, 'user', '2019-08-31 07:11:42'),
(54, '3_1567294771', 3, 77, NULL, 'user', '2019-08-31 23:39:31'),
(55, '3_1567305612', 3, 78, NULL, 'user', '2019-09-01 02:40:12'),
(56, '3_1567385361', 3, 79, NULL, 'user', '2019-09-02 00:49:21'),
(57, '3_1567489426', 3, 80, NULL, 'user', '2019-09-03 05:43:46'),
(58, '3_1567492646', 3, 81, NULL, 'user', '2019-09-03 06:37:26'),
(59, '3_1567664154', 3, 82, NULL, 'user', '2019-09-05 06:15:54'),
(60, '3_1567705490', 3, 83, NULL, 'user', '2019-09-05 17:44:50'),
(61, '3_1567733736', 3, 86, NULL, 'user', '2019-09-06 01:35:36'),
(62, '3_1567733740', 3, 84, NULL, 'user', '2019-09-06 01:35:40'),
(63, '3_1567789544', 3, 88, NULL, 'user', '2019-09-06 17:05:44'),
(64, '3_1567871448', 3, 89, NULL, 'user', '2019-09-07 15:50:48'),
(65, '3_1568123222', 3, 90, NULL, 'user', '2019-09-10 13:47:02'),
(66, '3_1568184930', 3, NULL, NULL, 'user', '2019-09-11 06:55:30'),
(67, '3_1568629709', 3, 91, NULL, 'user', '2019-09-16 10:28:29'),
(68, '3_1568645622', 3, 92, NULL, 'user', '2019-09-16 14:53:42'),
(69, '3_1568685017', 3, 93, NULL, 'user', '2019-09-17 01:50:17'),
(70, '3_1568691102', 3, 94, NULL, 'user', '2019-09-17 03:31:42'),
(71, '3_1568696656', 3, 95, NULL, 'user', '2019-09-17 05:04:16'),
(72, '3_1568697734', 3, 96, NULL, 'user', '2019-09-17 05:22:14'),
(73, '3_1568720539', 3, 97, NULL, 'user', '2019-09-17 11:42:19'),
(74, '3_1568777258', 3, 98, NULL, 'user', '2019-09-18 03:27:38'),
(75, '3_1568794337', 3, 99, NULL, 'user', '2019-09-18 08:12:17'),
(76, '3_1568813267', 3, 100, NULL, 'user', '2019-09-18 13:27:47'),
(77, '3_1568813493', 3, 101, NULL, 'user', '2019-09-18 13:31:33'),
(78, '3_1568818014', 3, NULL, NULL, 'user', '2019-09-18 14:46:54'),
(79, '3_1568856712', 3, 102, NULL, 'user', '2019-09-19 01:31:52'),
(80, '3_1568941234', 3, 103, NULL, 'user', '2019-09-20 01:00:34'),
(81, '3_1568949249', 3, 104, NULL, 'user', '2019-09-20 03:14:09'),
(82, '3_1569046329', 3, 105, NULL, 'user', '2019-09-21 06:12:09'),
(83, '3_1569329045', 3, 106, NULL, 'user', '2019-09-24 12:44:05'),
(84, '3_1569454098', 3, NULL, NULL, 'user', '2019-09-25 23:28:18'),
(85, '3_1569497060', 3, 107, NULL, 'user', '2019-09-26 11:24:20'),
(86, '3_1569722359', 3, 108, NULL, 'user', '2019-09-29 01:59:19'),
(87, '3_1570446871', 3, 109, NULL, 'user', '2019-10-07 11:14:31'),
(88, '3_1570521384', 3, NULL, NULL, 'user', '2019-10-08 07:56:24'),
(89, '3_1570589703', 3, 110, NULL, 'user', '2019-10-09 02:55:03'),
(90, '3_1570590436', 3, 111, NULL, 'user', '2019-10-09 03:07:16'),
(91, '3_1571049176', 3, 113, NULL, 'user', '2019-10-14 10:32:56'),
(92, '3_1571189550', 3, 114, NULL, 'user', '2019-10-16 01:32:30'),
(93, '3_1571367724', 3, 115, NULL, 'user', '2019-10-18 03:02:04'),
(94, '3_1571540111', 3, 116, NULL, 'user', '2019-10-20 02:55:11'),
(95, '3_1571593082', 3, 117, NULL, 'user', '2019-10-20 17:38:02'),
(96, '3_1571619736', 3, 118, NULL, 'user', '2019-10-21 01:02:16'),
(97, '3_1571636378', 3, 119, NULL, 'user', '2019-10-21 05:39:38'),
(98, '25_1571739634', 25, NULL, NULL, 'developer', '2019-10-22 10:20:34'),
(99, '25_1571747580', 25, NULL, NULL, 'user', '2019-10-22 12:33:00'),
(100, '63_1571788438', 63, 121, NULL, 'user', '2019-10-22 23:53:58'),
(101, '63_1571788458', 63, 198, NULL, 'user', '2019-10-22 23:54:18'),
(102, '63_1571788464', 63, 193, NULL, 'user', '2019-10-22 23:54:24'),
(103, '63_1571788557', 63, 146, NULL, 'user', '2019-10-22 23:55:57'),
(104, '63_1571788878', 63, 221, NULL, 'user', '2019-10-23 00:01:18'),
(105, '63_1571788881', 63, 223, NULL, 'user', '2019-10-23 00:01:21'),
(106, '63_1571788889', 63, 226, NULL, 'user', '2019-10-23 00:01:29'),
(107, '63_1571788890', 63, 222, NULL, 'user', '2019-10-23 00:01:30'),
(108, '63_1571788893', 63, 225, NULL, 'user', '2019-10-23 00:01:33'),
(109, '63_1571788895', 63, 228, NULL, 'user', '2019-10-23 00:01:35'),
(110, '63_1571788898', 63, 142, NULL, 'user', '2019-10-23 00:01:38'),
(111, '63_1571788899', 63, 141, NULL, 'user', '2019-10-23 00:01:39'),
(112, '63_1571788900', 63, 224, NULL, 'user', '2019-10-23 00:01:40'),
(113, '63_1571788902', 63, 227, NULL, 'user', '2019-10-23 00:01:42'),
(114, '63_1571788904', 63, 138, NULL, 'user', '2019-10-23 00:01:44'),
(115, '3_1572174652', 3, 122, NULL, 'user', '2019-10-27 11:10:52'),
(116, '3_1572228437', 3, 123, NULL, 'user', '2019-10-28 02:07:17'),
(117, '3_1572428462', 3, 124, NULL, 'user', '2019-10-30 09:41:02'),
(118, '3_1572579083', 3, 126, NULL, 'user', '2019-11-01 03:31:23'),
(119, '22_1573031053', 22, NULL, NULL, 'user', '2019-11-06 09:04:13'),
(120, '3_1573177187', 3, 127, NULL, 'user', '2019-11-08 01:39:47'),
(121, '3_1573184090', 3, 128, NULL, 'user', '2019-11-08 03:34:50'),
(122, '3_1573484601', 3, 129, NULL, 'user', '2019-11-11 15:03:21'),
(123, '3_1573540905', 3, 130, NULL, 'user', '2019-11-12 06:41:45'),
(124, '3_1573546278', 3, 131, NULL, 'user', '2019-11-12 08:11:18'),
(125, '3_1573611033', 3, 132, NULL, 'user', '2019-11-13 02:10:33'),
(126, '3_1573713260', 3, 134, NULL, 'user', '2019-11-14 06:34:20'),
(127, '3_1573981260', 3, 135, NULL, 'user', '2019-11-17 09:01:00'),
(128, '3_1574043136', 3, 136, NULL, 'user', '2019-11-18 02:12:16'),
(129, '3_1574342016', 3, 144, NULL, 'user', '2019-11-21 13:13:36'),
(130, '63_1574382058', 63, 153, NULL, 'user', '2019-11-22 00:20:58'),
(131, '63_1574382063', 63, 154, NULL, 'user', '2019-11-22 00:21:03'),
(132, '63_1574382093', 63, 155, NULL, 'user', '2019-11-22 00:21:33'),
(133, '63_1574382098', 63, 156, NULL, 'user', '2019-11-22 00:21:38'),
(134, '63_1574382100', 63, 158, NULL, 'user', '2019-11-22 00:21:40'),
(135, '63_1574382104', 63, 159, NULL, 'user', '2019-11-22 00:21:44'),
(136, '63_1574382116', 63, 160, NULL, 'user', '2019-11-22 00:21:56'),
(137, '63_1574382119', 63, 161, NULL, 'user', '2019-11-22 00:21:59'),
(138, '63_1574382121', 63, 162, NULL, 'user', '2019-11-22 00:22:01'),
(139, '63_1574382130', 63, 190, NULL, 'user', '2019-11-22 00:22:10'),
(140, '63_1574382133', 63, 164, NULL, 'user', '2019-11-22 00:22:13'),
(141, '63_1574382135', 63, 200, NULL, 'user', '2019-11-22 00:22:15'),
(142, '63_1574382138', 63, 166, NULL, 'user', '2019-11-22 00:22:18'),
(143, '63_1574382141', 63, 167, NULL, 'user', '2019-11-22 00:22:21'),
(144, '63_1574382143', 63, 168, NULL, 'user', '2019-11-22 00:22:23'),
(145, '63_1574382145', 63, 169, NULL, 'user', '2019-11-22 00:22:25'),
(146, '63_1574382147', 63, 195, NULL, 'user', '2019-11-22 00:22:27'),
(147, '63_1574382150', 63, 171, NULL, 'user', '2019-11-22 00:22:30'),
(148, '63_1574382152', 63, 172, NULL, 'user', '2019-11-22 00:22:32'),
(149, '63_1574382154', 63, 197, NULL, 'user', '2019-11-22 00:22:34'),
(150, '63_1574382157', 63, 174, NULL, 'user', '2019-11-22 00:22:37'),
(151, '63_1574382159', 63, 191, NULL, 'user', '2019-11-22 00:22:39'),
(152, '63_1574382161', 63, 176, NULL, 'user', '2019-11-22 00:22:41'),
(153, '63_1574382277', 63, 177, NULL, 'user', '2019-11-22 00:24:37'),
(154, '63_1574382280', 63, 178, NULL, 'user', '2019-11-22 00:24:40'),
(155, '63_1574382283', 63, 194, NULL, 'user', '2019-11-22 00:24:43'),
(156, '63_1574382286', 63, NULL, NULL, 'user', '2019-11-22 00:24:46'),
(157, '63_1574382288', 63, NULL, NULL, 'user', '2019-11-22 00:24:48'),
(158, '63_1574382290', 63, NULL, NULL, 'user', '2019-11-22 00:24:50'),
(159, '63_1574382302', 63, 188, NULL, 'user', '2019-11-22 00:25:02'),
(166, '181_1574652497', 181, 202, NULL, 'user', '2019-11-25 03:28:17'),
(167, '181_1574652500', 181, 201, NULL, 'user', '2019-11-25 03:28:20'),
(168, '182_1574652518', 182, 184, NULL, 'user', '2019-11-25 03:28:38'),
(169, '182_1574652522', 182, 211, NULL, 'user', '2019-11-25 03:28:42'),
(170, '180_1574652684', 180, 186, NULL, 'user', '2019-11-25 03:31:24'),
(171, '185_1574652786', 185, NULL, NULL, 'user', '2019-11-25 03:33:06'),
(172, '185_1574652930', 185, NULL, NULL, 'user', '2019-11-25 03:35:30'),
(173, '185_1574652950', 185, 189, NULL, 'user', '2019-11-25 03:35:50'),
(174, '182_1574731937', 182, NULL, NULL, 'user', '2019-11-26 01:32:17'),
(175, '3_1574732178', 3, 212, NULL, 'user', '2019-11-26 01:36:18'),
(176, '214_1574737213', 214, 217, NULL, 'user', '2019-11-26 03:00:13'),
(177, '214_1574737215', 214, 216, NULL, 'user', '2019-11-26 03:00:15'),
(178, '214_1574737218', 214, 218, NULL, 'user', '2019-11-26 03:00:18'),
(179, '213_1574737589', 213, NULL, NULL, 'user', '2019-11-26 03:06:29'),
(180, '213_1574738018', 213, NULL, NULL, 'user', '2019-11-26 03:13:38'),
(181, '22_1574759739', 22, NULL, NULL, 'user', '2019-11-26 09:15:39'),
(182, '3_1575260191', 3, 219, NULL, 'user', '2019-12-02 04:16:31'),
(183, '3_1575427866', 3, 229, NULL, 'user', '2019-12-04 02:51:06'),
(184, '3_1575441917', 3, NULL, NULL, 'user', '2019-12-04 06:45:17'),
(185, '22_1575527075', 22, 230, NULL, 'user', '2019-12-05 06:24:35'),
(186, '22_1575613082', 22, NULL, NULL, 'user', '2019-12-06 06:18:02'),
(187, '3_1575620287', 3, 231, NULL, 'user', '2019-12-06 08:18:07'),
(188, '3_1575872333', 3, NULL, NULL, 'user', '2019-12-09 06:18:53'),
(189, '3_1575948922', 3, 234, NULL, 'user', '2019-12-10 03:35:22'),
(190, '3_1575980033', 3, NULL, NULL, 'user', '2019-12-10 12:13:53'),
(191, '3_1576068123', 3, 235, NULL, 'user', '2019-12-11 12:42:03'),
(192, '3_1576322890', 3, 236, NULL, 'user', '2019-12-14 11:28:10'),
(193, '3_1577325314', 3, 237, NULL, 'user', '2019-12-26 01:55:14'),
(194, '3_1577863349', 3, 238, NULL, 'user', '2020-01-01 07:22:29'),
(195, '3_1620733232', 3, 241, NULL, 'user', '2021-05-11 11:40:32'),
(196, '3_1620750052', 3, 243, NULL, 'user', '2021-05-11 16:20:52'),
(197, '3_1620823523', 3, 244, NULL, 'user', '2021-05-12 12:45:23'),
(198, '3_1620957654', 3, 245, NULL, 'user', '2021-05-14 02:00:54'),
(199, '3_1621492375', 3, 246, NULL, 'user', '2021-05-20 06:32:55'),
(200, '3_1621951878', 3, NULL, NULL, 'user', '2021-05-25 14:11:18'),
(201, '3_1621951931', 3, 249, NULL, 'user', '2021-05-25 14:12:11'),
(202, '3_1622620072', 3, 250, NULL, 'user', '2021-06-02 07:47:52'),
(203, '3_1622825210', 3, 251, NULL, 'user', '2021-06-04 16:46:50'),
(204, '3_1623122245', 3, 252, NULL, 'user', '2021-06-08 03:17:25'),
(205, '3_1623210681', 3, 254, NULL, 'user', '2021-06-09 03:51:21'),
(206, '3_1623230753', 3, 255, NULL, 'user', '2021-06-09 09:25:53'),
(207, '3_1623829143', 3, 256, NULL, 'user', '2021-06-16 07:39:03'),
(208, '3_1623932035', 3, 258, NULL, 'user', '2021-06-17 12:13:55'),
(209, '3_1623987944', 3, 260, NULL, 'user', '2021-06-18 03:45:44'),
(210, '3_1634024873', 3, 263, NULL, 'user', '2021-10-12 07:47:53'),
(211, '3_1634105385', 3, 264, NULL, 'user', '2021-10-13 06:09:45'),
(212, '3_1634181913', 3, 265, NULL, 'user', '2021-10-14 03:25:13'),
(214, '3_1636618850', 3, 288, NULL, 'user', '2021-11-11 08:20:50'),
(215, '3_1636642607', 3, NULL, NULL, 'user', '2021-11-11 14:56:47'),
(216, '3_1636697523', 3, NULL, NULL, 'user', '2021-11-12 06:12:03'),
(217, '3_1636853367', 3, NULL, NULL, 'user', '2021-11-14 01:29:28'),
(218, '3_1637027077', 3, NULL, NULL, 'user', '2021-11-16 01:44:37'),
(219, '3_1637065359', 3, NULL, NULL, 'user', '2021-11-16 12:22:39'),
(221, '3_1638156397', 3, 287, NULL, 'user', '2021-11-29 03:26:37'),
(222, '3_1638414721', 3, 329, NULL, 'user', '2021-12-02 03:12:01'),
(223, '3_1638417011', 3, NULL, NULL, 'user', '2021-12-02 03:50:11'),
(224, '3_1638430823', 3, 290, NULL, 'user', '2021-12-02 07:40:23'),
(225, '3_1638516423', 3, NULL, NULL, 'user', '2021-12-03 07:27:03'),
(226, '3_1638525673', 3, 291, NULL, 'user', '2021-12-03 10:01:13'),
(227, '3_1638585430', 3, 292, NULL, 'user', '2021-12-04 02:37:10'),
(228, '3_1638589209', 3, 293, NULL, 'user', '2021-12-04 03:40:09'),
(229, '3_1638691392', 3, NULL, NULL, 'user', '2021-12-05 08:03:12'),
(230, '3_1638865215', 3, NULL, NULL, 'user', '2021-12-07 08:20:15'),
(231, '3_1638889513', 3, NULL, NULL, 'user', '2021-12-07 15:05:13'),
(232, '3_1638938078', 3, NULL, NULL, 'user', '2021-12-08 04:34:38'),
(233, '3_1639230268', 3, 303, NULL, 'user', '2021-12-11 13:44:28'),
(234, '3_1639237731', 3, 305, NULL, 'user', '2021-12-11 15:48:51'),
(235, '3_1639291454', 3, 306, NULL, 'user', '2021-12-12 06:44:14'),
(236, '3_1639371888', 3, 307, NULL, 'user', '2021-12-13 05:04:48'),
(237, '3_1639426827', 3, 308, NULL, 'user', '2021-12-13 20:20:27'),
(238, '3_1639451361', 3, 309, NULL, 'user', '2021-12-14 03:09:21'),
(239, '3_1639472173', 3, 310, NULL, 'user', '2021-12-14 08:56:13'),
(240, '3_1639490614', 3, 311, NULL, 'user', '2021-12-14 14:03:34'),
(241, '3_1639879265', 3, 313, NULL, 'user', '2021-12-19 02:01:05'),
(242, '3_1639887437', 3, 314, NULL, 'user', '2021-12-19 04:17:17'),
(243, '3_1639988506', 3, 315, NULL, 'user', '2021-12-20 08:21:46'),
(244, '3_1640189192', 3, 316, NULL, 'user', '2021-12-22 16:06:32'),
(245, '3_1640249733', 3, 317, NULL, 'user', '2021-12-23 08:55:33'),
(246, '3_1640361163', 3, 318, NULL, 'user', '2021-12-24 15:52:43'),
(247, '3_1640401897', 3, 319, NULL, 'user', '2021-12-25 03:11:37'),
(248, '3_1640426022', 3, 320, NULL, 'user', '2021-12-25 09:53:42'),
(249, '3_1640494408', 3, 321, NULL, 'user', '2021-12-26 04:53:29'),
(250, '3_1640520932', 3, 322, NULL, 'user', '2021-12-26 12:15:32'),
(251, '3_1640525480', 3, 323, NULL, 'user', '2021-12-26 13:31:20'),
(252, '3_1640657605', 3, 324, NULL, 'user', '2021-12-28 02:13:25'),
(253, '3_1640762626', 3, 325, NULL, 'user', '2021-12-29 07:23:46'),
(254, '3_1640871686', 3, 326, NULL, 'user', '2021-12-30 13:41:26'),
(255, '3_1640943305', 3, 327, NULL, 'user', '2021-12-31 09:35:05'),
(256, '3_1641314792', 3, 328, NULL, 'user', '2022-01-04 16:46:32'),
(257, '3_1641734025', 3, 330, NULL, 'user', '2022-01-09 13:13:45'),
(258, '3_1641739126', 3, 331, NULL, 'user', '2022-01-09 14:38:46'),
(259, '3_1641780157', 3, 332, NULL, 'user', '2022-01-10 02:02:37'),
(260, '3_1641783248', 3, 333, NULL, 'user', '2022-01-10 02:54:08'),
(261, '3_1641938618', 3, 335, NULL, 'user', '2022-01-11 22:03:38');

-- --------------------------------------------------------

--
-- 表的结构 `logic`
--

CREATE TABLE `logic` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `node_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `dom` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `logic`
--

INSERT INTO `logic` (`id`, `project_id`, `node_id`, `user_id`, `dom`, `code`) VALUES
(1, 475, -1, 3, '<xml xmlns=\"https://developers.google.com/blockly/xml\"><variables><variable id=\"~_-bUL8Q`hLN`l@]FH@@\">eat1</variable></variables><block type=\"action_init\" x=\"138\" y=\"188\"><statement name=\"content\"><block type=\"variables_set\"><field name=\"VAR\" id=\"~_-bUL8Q`hLN`l@]FH@@\">eat1</field><value name=\"VALUE\"><block type=\"logic_boolean\"><field name=\"BOOL\">FALSE</field></block></value></block></statement></block><block type=\"action_destroy\" x=\"113\" y=\"313\"/><block type=\"update_trigger\" x=\"138\" y=\"388\"><statement name=\"content\"><block type=\"controls_if\"><value name=\"IF0\"><block type=\"logic_operation\"><field name=\"OP\">AND</field><value name=\"A\"><block type=\"logic_compare\"><field name=\"OP\">LT</field><value name=\"A\"><block type=\"diance_method\"/></value><value name=\"B\"><block type=\"math_number\"><field name=\"NUM\">0.5</field></block></value></block></value><value name=\"B\"><block type=\"logic_negate\"><value name=\"BOOL\"><block type=\"variables_get\"><field name=\"VAR\" id=\"~_-bUL8Q`hLN`l@]FH@@\">eat1</field></block></value></block></value></block></value><statement name=\"DO0\"><block type=\"variables_set\"><field name=\"VAR\" id=\"~_-bUL8Q`hLN`l@]FH@@\">eat1</field><value name=\"VALUE\"><block type=\"logic_boolean\"><field name=\"BOOL\">TRUE</field></block></value></block></statement></block></statement></block></xml>', 'self.handling[\'@init\'] = function(self)\n  print(\'@init\')\n  eat1 = false\nend\n\nself.handling[\'@destroy\'] = function(self)\n  print(\'@destroy\')\nend\n\nself.handling[\'@update\'] = function(self, interval)\n  print(\'@update\')\n  if (...) < 0.5 and not eat1 then\n    eat1 = true\n  end\nend\n'),
(2, 480, -1, 3, '<xml xmlns=\"https://developers.google.com/blockly/xml\"/>', ''),
(5, 490, -1, 290, '<xml xmlns=\"https://developers.google.com/blockly/xml\"><variables><variable id=\"5{4xu0SGFLOa12}NdKmP\">播放</variable></variables><block type=\"action_init\" x=\"162\" y=\"138\"><statement name=\"content\"><block type=\"variables_set\"><field name=\"VAR\" id=\"5{4xu0SGFLOa12}NdKmP\">播放</field><value name=\"VALUE\"><block type=\"logic_boolean\"><field name=\"BOOL\">TRUE</field></block></value></block></statement></block><block type=\"action_trigger\" x=\"187\" y=\"262\"><field name=\"&lt;#Action&gt;\">2.10:Bell</field><statement name=\"content\"><block type=\"variables_set\"><field name=\"VAR\" id=\"5{4xu0SGFLOa12}NdKmP\">播放</field><value name=\"VALUE\"><block type=\"logic_negate\"><value name=\"BOOL\"><block type=\"variables_get\"><field name=\"VAR\" id=\"5{4xu0SGFLOa12}NdKmP\">播放</field></block></value></block></value><next><block type=\"visual_execute\"><value name=\"entity\"><block type=\"video_entity\"><field name=\"&lt;#Video&gt;\">2.12:视频</field></block></value><value name=\"bool\"><block type=\"variables_get\"><field name=\"VAR\" id=\"5{4xu0SGFLOa12}NdKmP\">播放</field></block></value></block></next></block></statement></block></xml>', 'self.handling[\'@init\'] = function(self)\n  print(\'@init\')\n  _E6_92_AD_E6_94_BE = true\nend\n\nself.handling[\'2.10:Bell\'] = function(self,  parameter)\n  print(\'2.10:Bell\')\n  _E6_92_AD_E6_94_BE = not _E6_92_AD_E6_94_BE\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"video\", \"2.12:视频\"), _E6_92_AD_E6_94_BE)\nend\n'),
(6, 494, -1, 293, '<xml xmlns=\"https://developers.google.com/blockly/xml\"><block type=\"controls_if\"><value name=\"IF0\"><block type=\"logic_boolean\"><field name=\"BOOL\">TRUE</field></block></value><statement name=\"DO0\"><block type=\"text_print\"><value name=\"TEXT\"><shadow type=\"text\"><field name=\"TEXT\">abc</field></shadow></value></block></statement></block></xml>', 'if true then\n  print(\'abc\')\nend\n'),
(8, 509, -1, 316, '<xml xmlns=\"https://developers.google.com/blockly/xml\"><block type=\"action_trigger\"><field name=\"&lt;#Action&gt;\">3.16:关闭皮肤</field><statement name=\"content\"><block type=\"visual_execute\"><value name=\"entity\"><block type=\"polygen_entity\"><field name=\"&lt;#Polygen&gt;\">3.15:模型1</field></block></value></block></statement></block></xml>', 'self.handling[\'3.16:关闭皮肤\'] = function(self,  parameter)\n  print(\'3.16:关闭皮肤\')\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"polygen\",  \"3.15:模型1\"), )\nend\n'),
(10, 518, -1, 323, '<xml xmlns=\"https://developers.google.com/blockly/xml\"><block type=\"action_trigger\"><field name=\"&lt;#Action&gt;\">5.6:Action1</field><statement name=\"content\"><block type=\"visual_execute\"><value name=\"entity\"><block type=\"polygen_entity\"><field name=\"&lt;#Polygen&gt;\">5.4:mt</field></block></value><value name=\"bool\"><block type=\"logic_boolean\"><field name=\"BOOL\">TRUE</field></block></value></block></statement></block></xml>', 'self.handling[\'5.6:Action1\'] = function(self,  parameter)\n  print(\'5.6:Action1\')\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"polygen\",  \"5.4:mt\"), true)\nend\n'),
(11, 503, -1, 315, '<xml xmlns=\"https://developers.google.com/blockly/xml\"><block type=\"action_trigger\"><field name=\"&lt;#Action&gt;\">2.12:Action</field><statement name=\"content\"><block type=\"visual_execute\"><value name=\"entity\"><block type=\"polygen_entity\"><field name=\"&lt;#Polygen&gt;\">2.3:Turbine Blade</field></block></value><value name=\"bool\"><block type=\"logic_boolean\"><field name=\"BOOL\">FALSE</field></block></value></block></statement></block></xml>', 'self.handling[\'2.12:Action\'] = function(self,  parameter)\n  print(\'2.12:Action\')\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"polygen\",  \"2.3:Turbine Blade\"), false)\nend\n'),
(9, 514, -1, 320, '<xml xmlns=\"https://developers.google.com/blockly/xml\"/>', '');

-- --------------------------------------------------------

--
-- 表的结构 `maker`
--

CREATE TABLE `maker` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `polygen_id` int(11) NOT NULL,
  `data` text DEFAULT NULL,
  `programme_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `material`
--

CREATE TABLE `material` (
  `id` int(11) NOT NULL,
  `albedo` int(11) DEFAULT NULL,
  `metallic` int(11) DEFAULT NULL,
  `normal` int(11) DEFAULT NULL,
  `occlusion` int(11) DEFAULT NULL,
  `emission` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `polygen_id` int(11) NOT NULL,
  `name` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `smoothness` float DEFAULT NULL,
  `alpha` float DEFAULT NULL
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `message`
--

INSERT INTO `message` (`id`, `title`, `body`, `author_id`, `updater_id`, `created_at`, `updated_at`, `info`) VALUES
(3, '新年第一贴', '<p>掐着时间来等新网站开放，邸兄，新年快乐，平台人气旺起来</p>', 337, 337, '2022-01-31 16:19:59', '2022-01-31 16:19:59', NULL),
(6, '还是不能创建元宇宙', '<p>是还没有开通吗</p>', 338, 338, '2022-02-08 05:13:50', '2022-02-08 05:13:50', NULL),
(7, '感谢不加班公司团队努力，平台很棒！', '<p>感谢不加班公司团队努力，平台很棒！</p>', 316, 316, '2022-02-09 14:16:44', '2022-02-09 14:16:44', NULL),
(8, '关于不能修改资源内容的bug', '<p>之前有朋友提出无法修改资源内容，检查后发现是权限错误。 目前开启权限可以使用了，相关安全问题也同步解决。感谢。</p>', 24, 24, '2022-02-10 16:40:44', '2022-02-16 02:24:30', NULL),
(9, '文档什么时候出来！', '<p>需要我们做点啥不</p>', 338, 338, '2022-02-11 13:06:42', '2022-02-11 13:06:42', NULL),
(10, '希望能给每个新手用户一个样例', '<p>我试着按照<strong class=\"card-title\">创建第一个场景 </strong><span class=\"card-title\">里教的来做 下载了app，打开我自己的项目，看不到我上传的模型。。</span></p>\n<p>一开始 进入元宇宙 编辑界面，怎么创建元数据都不知道，verse-进入编辑怎么看都不对，后来偶然才发现，元宇宙 编辑界面右键新增就可以了，可能需要视频教程。。</p>', 357, 357, '2022-02-13 06:15:33', '2022-02-13 06:15:33', NULL),
(11, '感谢各位关注，目前正在制作教程，以及最后的调试，这两天就会提供完整的例子给大家。', '<p>感谢各位关注，目前正在制作教程，以及最后的调试，这两天就会提供完整的例子给大家。</p>\n<p>因为团队人手比较少，所以工作效率确实有一点拉垮，还请大家见谅。</p>\n<p>感谢各位持续关注，有问题我们会尽快尽力解决，再次谢谢各位。</p>', 3, 3, '2022-02-14 09:13:50', '2022-02-14 09:13:50', NULL),
(13, '编辑场景的案例已经更新', '<p>在 探索/案例 里面更新了相应的文档和视频，提供了一个最简单的编辑过程。之后还有运行方法，将在今天更新完毕。感谢各位的持续关注。</p>', 3, 3, '2022-02-15 17:53:18', '2022-02-15 17:53:18', NULL),
(14, '能否实现扫二维码显示固定模型的功能', '<p>平台是否有这个功能，在平台里该如何操作呢？</p>\n<p>举个例子，我在房间的某个柱体某个位置设置了二维码，在模型中这个位置也设置了二维码，我用hololens扫描现实柱体上的二维码，模型就出现在这个对应位置上实现重影。</p>', 337, 337, '2022-02-16 03:00:40', '2022-02-16 03:00:40', NULL),
(15, '我想了解一下，这个可以支持在surface系列的平板电脑上使用吗？', '<p>我这边想搞一个能在windows平台&nbsp; 同时做AR演示和日常的工作事务处理。</p>\n<p>没找到技术方向，还请指点下</p>', 357, 357, '2022-02-17 09:27:04', '2022-02-17 09:27:04', NULL),
(16, '学习方法', '<p>基本没有unity和C#编程背景，目前需要给HoloLens2 开发一个简单的测量小程序。拿到别人的代码，也看不太明白。请问能指条学习道路吗？</p>', 365, 365, '2022-02-17 14:30:13', '2022-02-17 14:30:13', NULL),
(17, '抱歉，这周开始广泛测试其他功能，更多的教程下周给到大家', '<p>因为上线匆忙，很多功能还没有完整测试，下周会在更新一个版本，并尽量补充教程。</p>\n<p>我门目标是到月底之前提供比较完善的版本，目前的版本还是有一些虫子要捉。有问题在这边和我们说下，我们会尽快解决。</p>\n<p>感谢各位的持续关注。</p>', 3, 3, '2022-02-18 08:23:36', '2022-02-18 08:23:36', NULL),
(18, 'bug提交', '<p>ipad作为服务端和hololens2同一个局域网，加载模型后，用hololens2扫描ipad，显示连接成功，可是ipad上的开始运行一直点不了（灰色的，点不亮），进入不了第三视觉。</p>\n<p>换成iphone12pro测试，也是同样的问题。</p>', 337, 337, '2022-02-21 02:20:47', '2022-02-21 02:20:47', NULL),
(19, '重大好消息，新版本没啥大问题了。', '<p>我刚才测试了最新的版本，载入模型，视频，图片都没什么问题了。</p>\n<p>等下发布，然后明天，继续赶教程。</p>\n<p>感谢各位持续关注。</p>', 3, 3, '2022-02-21 10:02:08', '2022-02-21 10:02:08', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `message_tags`
--

CREATE TABLE `message_tags` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `message_tags`
--

INSERT INTO `message_tags` (`id`, `message_id`, `tag_id`) VALUES
(4, 16, 3),
(5, 16, 2),
(6, 17, 2),
(10, 6, 4),
(14, 15, 4),
(15, 16, 4);

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
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `verse_id` int(11) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `image_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `meta`
--

INSERT INTO `meta` (`id`, `author_id`, `updater_id`, `created_at`, `updated_at`, `name`, `verse_id`, `info`, `data`, `image_id`) VALUES
(2, 24, 24, '2022-01-29 09:48:28', '2022-01-29 09:49:11', 'chart', 2, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"219e5496-5c03-440b-9d43-df076c66fd3f\",\"name\":{\"name\":\"chart\",\"id\":2},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"a71f91a3-b14d-4c6e-8b0d-82babd5d40b9\",\"name\":\"visitor\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":\"\"},\"children\":{\"entities\":[],\"components\":[{\"type\":\"Rotate\",\"parameters\":{\"uuid\":\"1df4c12c-55cb-4a95-ba2b-6f0325304e0f\",\"speed\":{\"x\":0,\"y\":0,\"z\":0}},\"children\":{}}]}}],\"addons\":[]}}', NULL),
(3, 315, 315, '2022-02-08 02:14:49', '2022-02-08 06:38:22', 'model1', 6, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"d15d31ff-bb9d-467e-83b5-100f1a586d02\",\"name\":{\"name\":\"model1\",\"id\":3},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"2c11c144-71a0-4142-ab9d-2648ca756d0c\",\"name\":\"blademodel\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0.2},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":0.5,\"y\":0.5,\"z\":0.5}},\"active\":true,\"polygen\":88},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[{\"type\":\"Toolbar\",\"parameters\":{\"uuid\":\"f8379e82-47a8-4374-b07f-bfb880adcc5f\",\"destory\":true},\"children\":{\"buttons\":[{\"type\":\"Button\",\"parameters\":{\"uuid\":\"7dabd60f-01c8-495a-b316-8a3bf5bde7e5\",\"title\":\"hall\",\"icon\":\"book\",\"action\":\"attached\"},\"children\":{\"action\":[]}},{\"type\":\"Button\",\"parameters\":{\"uuid\":\"7d31bd5f-a3d3-44be-922c-bde40304597f\",\"title\":\"fence\",\"icon\":\"book\",\"action\":\"gray\"},\"children\":{\"action\":[]}}]}}]}}', NULL),
(6, 350, 350, '2022-02-08 05:22:58', '2022-02-08 05:24:18', 'root', 7, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"32df059b-7056-45c9-b83e-a45b7c7a5c58\",\"name\":{\"name\":\"root\",\"id\":6},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[],\"addons\":[]}}', NULL),
(10, 25, 25, '2022-02-12 02:45:27', '2022-02-12 02:45:27', 'start', 17, NULL, NULL, NULL),
(11, 357, 357, '2022-02-13 01:25:57', '2022-02-15 08:55:27', 'food', 18, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"67b36052-c396-4e26-be1a-d837349948b7\",\"name\":{\"name\":\"food\",\"id\":11},\"transform\":{\"position\":{\"x\":2,\"y\":2,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"9ec56d5d-89b8-477d-b70a-f97009256d91\",\"name\":\"sat\",\"transform\":{\"position\":{\"x\":2,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":10,\"y\":10,\"z\":10}},\"active\":true,\"polygen\":137},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[]}}', NULL),
(15, 344, 344, '2022-02-13 14:03:14', '2022-02-23 15:46:23', 'fff', 20, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"0c24d030-249c-463b-b63b-7c14ffd7cb2d\",\"name\":{\"name\":\"fff\",\"id\":15},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":false},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"409d9fc3-5e73-4c62-ab79-f9f1ac96aa3f\",\"name\":\"south\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":\"\"},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"409d9fc3-5e73-4c62-ab79-f9f1ac96aa3f\",\"name\":\"south\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":\"\"},\"children\":{\"entities\":[],\"components\":[]}}],\"components\":[]}}],\"addons\":[]}}', NULL),
(17, 24, 24, '2022-02-13 16:23:20', '2022-02-15 05:38:17', 'neighborhood', 5, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"bd9dd3e6-5898-4ba7-a660-3490a5b5b8e1\",\"name\":{\"name\":\"neighborhood\",\"id\":17},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Picture\",\"parameters\":{\"uuid\":\"3d97f335-2208-4775-8cd7-10509b7d2989\",\"name\":\"notice\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"picture\":59,\"width\":0.5},\"children\":{\"entities\":[{\"type\":\"Entity\",\"parameters\":{\"uuid\":\"51fba7f2-d566-4b24-8e29-736499dd1ea6\",\"name\":\"problem\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"8c4c33b0-cf3b-4b5d-bfd0-378672cb8e59\",\"name\":\"funny\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":\"\"},\"children\":{\"entities\":[],\"components\":[]}}],\"components\":[]}}],\"components\":[{\"type\":\"Transparent\",\"parameters\":{\"uuid\":\"6993eb30-0c0a-49f7-9f65-2f7ab3b80d9e\",\"alpha\":0.5},\"children\":{}}]}}],\"addons\":[]}}', NULL),
(20, 357, 357, '2022-02-15 05:54:05', '2022-02-15 09:05:43', 'fellow', 22, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"69e312df-fdd9-461e-b54c-c75a90d65ad1\",\"name\":{\"name\":\"fellow\",\"id\":20},\"transform\":{\"position\":{\"x\":2,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"a23b76c2-2a41-4f07-8b22-2567abc0cb97\",\"name\":\"owner\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":137},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[]}}', NULL),
(21, 338, 338, '2022-02-15 06:07:28', '2022-02-16 15:37:36', '圆柱', 14, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"eef0cdea-f1ee-46b2-b102-cae56a50e80a\",\"name\":{\"name\":\"圆柱\",\"id\":21},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":2},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"ffc7f6af-f0e5-490a-8d0f-74295b938c38\",\"name\":\"offer\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":122},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[]}}', NULL),
(22, 3, 3, '2022-02-15 12:25:24', '2022-02-24 07:28:49', 'kuagu', 23, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"7a69659f-d7f9-490f-8cc5-b21901b2a57a\",\"name\":{\"name\":\"kuagu\",\"id\":22},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":1},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"a30c5a6f-eecb-442a-8f58-72fde6514a7e\",\"name\":\"BoomBox\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":0.001,\"y\":0.001,\"z\":0.001}},\"active\":true,\"polygen\":154},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[{\"type\":\"Toolbar\",\"parameters\":{\"uuid\":\"ef590879-8e1b-4b46-966d-7dd3abcba732\",\"destory\":false},\"children\":{\"buttons\":[{\"type\":\"Button\",\"parameters\":{\"uuid\":\"5c60d2d3-4db5-4eb3-a05c-ddaccd8dc984\",\"title\":\" 爆炸\",\"icon\":\"bolt\",\"action\":\"quietly\",\"action_parameter\":\"\"},\"children\":{}}]}}]}}', NULL),
(25, 344, 344, '2022-02-23 04:45:07', '2022-02-23 05:01:53', 'ffffwwwwwwfff', 20, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"a60361ab-60dd-4ca7-9699-a103a8597323\",\"name\":{\"name\":\"ffffwwwwwwfff\",\"id\":25},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[],\"addons\":[]}}', NULL),
(30, 368, 368, '2022-02-23 13:28:32', '2022-02-24 02:38:06', 'shaking', 26, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"11561d38-3d38-4194-b0e5-cb294bf7161a\",\"name\":{\"name\":\"shaking\",\"id\":30},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"bb0de5c7-e079-4e97-aa76-38db26fed97f\",\"name\":\"wire\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":0.5,\"y\":0.5,\"z\":0.5}},\"active\":true,\"polygen\":148},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[]}}', NULL),
(32, 3, 3, '2022-02-24 07:18:09', '2022-02-24 08:38:00', 'tougu', 23, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"baf7eebd-1a92-4bf1-86ae-8c62ff62c3d4\",\"name\":{\"name\":\"tougu\",\"id\":32},\"transform\":{\"position\":{\"x\":-1,\"y\":0,\"z\":1},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"66eb06d0-41c1-434d-9f83-84ae01bc3fd5\",\"name\":\"child\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":0.01,\"y\":0.01,\"z\":0.01}},\"active\":true,\"polygen\":153},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[{\"type\":\"Toolbar\",\"parameters\":{\"uuid\":\"67c903d7-a933-4638-bb54-265185b30dc2\",\"destory\":false},\"children\":{\"buttons\":[]}}]}}', NULL),
(33, 337, 337, '2022-02-24 12:24:33', '2022-02-24 12:25:09', 'ok', 4, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"72769596-a0ec-4b57-9c2d-869199f0a18e\",\"name\":{\"name\":\"ok\",\"id\":33},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":1.5},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"96705c98-8da2-4c41-a984-97d5de7e601c\",\"name\":\"turn\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":150},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[]}}', NULL),
(34, 3, 3, '2022-02-25 02:07:54', '2022-02-25 02:11:33', 'library', 27, NULL, '{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"4c659b1e-85cb-4edb-8987-a658f433d600\",\"name\":{\"name\":\"library\",\"id\":34},\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true},\"children\":{\"entities\":[{\"type\":\"Polygen\",\"parameters\":{\"uuid\":\"a16feca6-7d34-4c6b-b847-9c69ce33a7f8\",\"name\":\"smallest\",\"transform\":{\"position\":{\"x\":0,\"y\":0,\"z\":0},\"rotate\":{\"x\":0,\"y\":0,\"z\":0},\"scale\":{\"x\":1,\"y\":1,\"z\":1}},\"active\":true,\"polygen\":153},\"children\":{\"entities\":[],\"components\":[]}}],\"addons\":[]}}', NULL),
(35, 3, 3, '2022-02-25 02:11:37', '2022-02-25 02:11:37', 'view', 27, NULL, NULL, NULL);

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
-- 表的结构 `method`
--

CREATE TABLE `method` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `definition` text DEFAULT NULL,
  `generator` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
('m220218_062529_drop_type_column_from_tags_table', 1645165916);

-- --------------------------------------------------------

--
-- 表的结构 `mrpp_project`
--

CREATE TABLE `mrpp_project` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `image` int(11) DEFAULT NULL,
  `data` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `picture`
--

CREATE TABLE `picture` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `picture`
--

INSERT INTO `picture` (`id`, `name`, `file_id`, `user_id`, `created_at`, `image_id`) VALUES
(43, '是的方法2', 369, 3, '2021-05-22 15:45:03', 429),
(44, '2500412jpg12', 370, 3, '2021-05-22 15:45:03', 431),
(48, 'nreal_city.jpg', 379, 3, '2021-05-22 15:45:03', 433),
(50, 'body.gif', 417, 3, '2021-05-22 15:45:03', 417),
(51, 'u=2123357348,1662358297&fm=26&gp=0.png', 435, 24, '2021-05-24 03:43:18', 436),
(52, 'v2-83644f09cfc39efc05c515dbbefeec87_r.jpg', 451, 24, '2021-11-24 08:25:35', 452);

-- --------------------------------------------------------

--
-- 表的结构 `polygen`
--

CREATE TABLE `polygen` (
  `id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `file_id` int(11) DEFAULT NULL,
  `width` float DEFAULT NULL,
  `height` float DEFAULT NULL,
  `depth` float DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `polygen`
--

INSERT INTO `polygen` (`id`, `name`, `user_id`, `created_at`, `file_id`, `width`, `height`, `depth`, `image_id`, `info`) VALUES
(953, '录音机', 3, '2021-05-14 16:33:33', 426, 0.0198423, 0.0195433, 0.0201525, 428, NULL),
(955, NULL, 3, '2021-10-30 16:39:51', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `polygen_material`
--

CREATE TABLE `polygen_material` (
  `id` int(11) NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `polygen_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `polygen_material`
--

INSERT INTO `polygen_material` (`id`, `key`, `polygen_id`, `material_id`) VALUES
(1, 'dfd', NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `programme`
--

CREATE TABLE `programme` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `information` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `configure` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `logic` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `programme`
--

INSERT INTO `programme` (`id`, `author_id`, `title`, `information`, `configure`, `logic`) VALUES
(1, 3, 'asdf', NULL, NULL, NULL),
(2, 3, 'asdf', NULL, NULL, NULL),
(3, 3, 'asdf', NULL, NULL, NULL),
(4, 3, 'asdf', NULL, NULL, NULL),
(5, 3, 'asdf', NULL, NULL, NULL),
(6, 3, 'asdf', NULL, NULL, NULL),
(7, 3, 'asdf', NULL, NULL, NULL),
(8, 3, 'asdf', NULL, NULL, NULL),
(9, 3, 'asdf', NULL, NULL, NULL),
(10, 3, 'asdf', NULL, NULL, NULL),
(11, 3, 'asdf', NULL, NULL, NULL),
(12, 3, 'asdf', NULL, NULL, NULL),
(13, 3, 'asdf', NULL, NULL, NULL),
(14, 3, 'asdf', NULL, NULL, NULL),
(15, 3, 'asdf', NULL, NULL, NULL),
(16, 3, 'asdf', NULL, NULL, NULL),
(17, 3, 'asdf', NULL, NULL, NULL),
(18, 3, 'asdf', NULL, NULL, NULL),
(19, 3, 'sdfdsf', NULL, NULL, NULL),
(20, 3, '123', '', NULL, NULL),
(21, 3, '123', '', NULL, NULL),
(22, 3, '123', '', NULL, NULL),
(23, 3, '123', 'defef', NULL, NULL),
(24, 3, '123', '', NULL, NULL),
(25, 3, '', '', NULL, NULL),
(26, 3, 'abcx', NULL, NULL, NULL),
(27, 3, '123', 'efg', NULL, NULL),
(28, NULL, NULL, NULL, NULL, NULL),
(29, NULL, NULL, NULL, NULL, NULL),
(30, NULL, NULL, NULL, NULL, NULL),
(31, NULL, NULL, NULL, NULL, NULL),
(32, NULL, NULL, NULL, NULL, NULL),
(33, NULL, NULL, NULL, NULL, NULL),
(34, NULL, NULL, NULL, NULL, NULL),
(35, NULL, NULL, NULL, NULL, NULL),
(36, NULL, NULL, NULL, NULL, NULL),
(37, NULL, NULL, NULL, NULL, NULL),
(38, NULL, NULL, NULL, NULL, NULL),
(39, NULL, NULL, NULL, NULL, NULL),
(40, NULL, NULL, NULL, NULL, NULL),
(41, NULL, NULL, NULL, NULL, NULL),
(42, NULL, NULL, NULL, NULL, NULL),
(43, NULL, NULL, NULL, NULL, NULL),
(44, NULL, NULL, NULL, NULL, NULL),
(45, NULL, NULL, NULL, NULL, NULL),
(46, NULL, NULL, NULL, NULL, NULL),
(47, NULL, NULL, NULL, NULL, NULL),
(48, NULL, NULL, NULL, NULL, NULL),
(49, NULL, NULL, NULL, NULL, NULL),
(50, NULL, NULL, NULL, NULL, NULL),
(51, NULL, NULL, NULL, NULL, NULL),
(52, NULL, NULL, NULL, NULL, NULL),
(53, NULL, NULL, NULL, NULL, NULL),
(54, NULL, NULL, NULL, NULL, NULL),
(55, NULL, NULL, NULL, NULL, NULL),
(56, NULL, NULL, NULL, NULL, NULL),
(57, NULL, NULL, NULL, NULL, NULL),
(58, NULL, NULL, NULL, NULL, NULL),
(59, NULL, NULL, NULL, NULL, NULL),
(60, NULL, NULL, NULL, NULL, NULL),
(61, NULL, NULL, NULL, NULL, NULL),
(62, NULL, NULL, NULL, NULL, NULL),
(63, NULL, NULL, NULL, NULL, NULL),
(64, NULL, NULL, NULL, NULL, NULL),
(65, NULL, NULL, NULL, NULL, NULL),
(66, NULL, NULL, NULL, NULL, NULL),
(67, NULL, NULL, NULL, NULL, NULL),
(68, NULL, NULL, NULL, NULL, NULL),
(69, NULL, NULL, NULL, NULL, NULL),
(70, NULL, NULL, NULL, NULL, NULL),
(71, 3, 'sdfsdf', NULL, NULL, NULL),
(72, 3, 'sdfsdf', NULL, NULL, NULL),
(73, 3, '', '', NULL, NULL),
(74, 3, '123', '', NULL, NULL),
(75, 3, '123', '', NULL, NULL),
(76, 3, '123', '', NULL, NULL),
(77, 3, '123', '', NULL, NULL),
(78, NULL, '123', 'asd', NULL, NULL),
(79, NULL, '123', '', NULL, NULL),
(80, NULL, '123', 'asdf', NULL, NULL),
(81, NULL, '123', '', NULL, NULL),
(82, NULL, '123', '', NULL, NULL),
(83, NULL, '123', '', NULL, NULL),
(84, NULL, '123', '', NULL, NULL),
(85, 3, '123', '', NULL, NULL),
(86, 3, '123', '', NULL, NULL),
(87, 3, '123', '', NULL, NULL),
(88, 3, '123', '', NULL, NULL),
(89, 3, '123', '', NULL, NULL),
(90, 3, '123', '', NULL, NULL),
(91, 3, '123', '', NULL, NULL),
(92, 3, 'What', 'Yes', '', ''),
(93, 3, '123aaa', 'bbb', '', ''),
(94, 3, '123', 'efeef', '', ''),
(95, 3, '123', '', '', ''),
(96, 3, 'efg', 'eeef', '', ''),
(97, 3, '123', 'dfasf', '', ''),
(98, 3, '123', '', '', ''),
(99, 3, '123', '', '', ''),
(100, NULL, NULL, NULL, NULL, NULL),
(101, NULL, NULL, NULL, NULL, NULL),
(102, NULL, NULL, NULL, NULL, NULL),
(103, NULL, NULL, NULL, NULL, NULL),
(104, NULL, NULL, NULL, NULL, NULL),
(105, NULL, NULL, NULL, NULL, NULL),
(106, NULL, NULL, NULL, NULL, NULL),
(107, NULL, NULL, NULL, NULL, NULL),
(108, NULL, NULL, NULL, NULL, NULL),
(109, NULL, NULL, NULL, NULL, NULL),
(110, NULL, NULL, NULL, NULL, NULL),
(111, NULL, NULL, NULL, NULL, NULL),
(112, NULL, NULL, NULL, NULL, NULL),
(113, NULL, NULL, NULL, NULL, NULL),
(114, NULL, NULL, NULL, NULL, NULL),
(115, NULL, NULL, NULL, NULL, NULL),
(116, NULL, NULL, NULL, NULL, NULL),
(117, NULL, NULL, NULL, NULL, NULL),
(118, NULL, NULL, NULL, NULL, NULL),
(119, NULL, NULL, NULL, NULL, NULL),
(120, NULL, NULL, NULL, NULL, NULL),
(121, NULL, NULL, NULL, NULL, NULL),
(122, NULL, NULL, NULL, NULL, NULL),
(123, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `project`
--

CREATE TABLE `project` (
  `id` int(11) NOT NULL,
  `title` varchar(20) NOT NULL,
  `logic` mediumtext NOT NULL,
  `configure` mediumtext NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `introduce` varchar(140) DEFAULT NULL,
  `sharing` tinyint(1) DEFAULT NULL,
  `programme_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `project`
--

INSERT INTO `project` (`id`, `title`, `logic`, `configure`, `user_id`, `introduce`, `sharing`, `programme_id`, `image_id`, `created_at`, `name`) VALUES
(480, '测试场景1', '	\nlocal logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			print(\'parameter:\'..json.encode(parameter))\n			\n            --{{sample code}}\n\n           if self.handling ~= nil and self.handling[key] ~=nil then\n               print(key .. \'!!!\')\n               self.handling[key](self, parameter);\n           end\n		end,\n        update = function(self, interval)\n\n           if self.handling ~= nil and self.handling[\'@update\'] ~=nil then\n               self.handling[\'@update\'](self, interval);\n           end\n        end,\n    \n        init = function(self)\n           if self.handling ~= nil and self.handling[\'@init\'] ~=nil then\n               self.handling[\'@init\'](self);\n           end\n        end,\n        setup = function(self)\n            self.handling = {}\n\n\n\n            \n\n        end,\n        version = function(self)\n            return 1;\n        end,\n        destroy = function(self)\n            if self.handling ~= nil and self.handling[\'@destroy\'] ~=nil then\n               self.handling[\'@destroy\'](self);\n           end\n        end,\n \n		callback = function(self, evt)\n            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n      \n        return setmetatable({}, logic_mt)\n    end\n}\n	', '{}', 3, '留着测试一下！！！！1212121\r\n', 1, 103, NULL, NULL, NULL),
(484, '沙盘', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 3, NULL, NULL, 104, NULL, NULL, NULL),
(485, '沙盘(copy)', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 3, NULL, NULL, 105, NULL, NULL, NULL),
(487, 'test', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 24, NULL, NULL, 107, NULL, NULL, NULL),
(488, 'test', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 24, NULL, NULL, 108, NULL, NULL, NULL),
(490, 'wanghololens_1', '	\nlocal logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			print(\'parameter:\'..json.encode(parameter))\n			\n            --{{sample code}}\n\n           if self.handling ~= nil and self.handling[key] ~=nil then\n               print(key .. \'!!!\')\n               self.handling[key](self, parameter);\n           end\n		end,\n        update = function(self, interval)\n\n           if self.handling ~= nil and self.handling[\'@update\'] ~=nil then\n               self.handling[\'@update\'](self, interval);\n           end\n        end,\n    \n        init = function(self)\n           if self.handling ~= nil and self.handling[\'@init\'] ~=nil then\n               self.handling[\'@init\'](self);\n           end\n        end,\n        setup = function(self)\n            self.handling = {}\n\n\n\n            self.handling[\'@init\'] = function(self)\n  print(\'@init\')\n  _E6_92_AD_E6_94_BE = true\nend\n\nself.handling[\'2.10:Bell\'] = function(self,  parameter)\n  print(\'2.10:Bell\')\n  _E6_92_AD_E6_94_BE = not _E6_92_AD_E6_94_BE\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"video\", \"2.12:视频\"), _E6_92_AD_E6_94_BE)\nend\n\n\n        end,\n        version = function(self)\n            return 1;\n        end,\n        destroy = function(self)\n            if self.handling ~= nil and self.handling[\'@destroy\'] ~=nil then\n               self.handling[\'@destroy\'](self);\n           end\n        end,\n \n		callback = function(self, evt)\n            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n      \n        return setmetatable({}, logic_mt)\n    end\n}\n	', '{}', 290, NULL, 0, 106, NULL, NULL, NULL),
(492, 'test1', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 291, NULL, NULL, 109, NULL, NULL, NULL),
(493, 'test', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 292, NULL, NULL, 110, NULL, NULL, NULL),
(494, 'TEXT', '	\nlocal logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			print(\'parameter:\'..json.encode(parameter))\n			\n            --{{sample code}}\n\n           if self.handling ~= nil and self.handling[key] ~=nil then\n               print(key .. \'!!!\')\n               self.handling[key](self, parameter);\n           end\n		end,\n        update = function(self, interval)\n\n           if self.handling ~= nil and self.handling[\'@update\'] ~=nil then\n               self.handling[\'@update\'](self, interval);\n           end\n        end,\n    \n        init = function(self)\n           if self.handling ~= nil and self.handling[\'@init\'] ~=nil then\n               self.handling[\'@init\'](self);\n           end\n        end,\n        setup = function(self)\n            self.handling = {}\n\n\n\n            if true then\n  print(\'abc\')\nend\n\n\n        end,\n        version = function(self)\n            return 1;\n        end,\n        destroy = function(self)\n            if self.handling ~= nil and self.handling[\'@destroy\'] ~=nil then\n               self.handling[\'@destroy\'](self);\n           end\n        end,\n \n		callback = function(self, evt)\n            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n      \n        return setmetatable({}, logic_mt)\n    end\n}\n	', '{}', 293, NULL, NULL, 111, NULL, NULL, NULL),
(495, '11', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 304, NULL, NULL, 112, NULL, NULL, NULL),
(496, 'test', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 3, NULL, NULL, 113, NULL, NULL, NULL),
(497, '20211212', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 306, NULL, NULL, 114, NULL, NULL, NULL),
(498, '数采仪', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 307, NULL, NULL, 115, NULL, NULL, NULL),
(499, 'test', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 311, '测试\r\n', NULL, 116, NULL, NULL, NULL),
(501, 'Human Cell', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 315, NULL, NULL, 117, NULL, NULL, NULL),
(503, 'Tubine Blade', '	\nlocal logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			print(\'parameter:\'..json.encode(parameter))\n			\n            --{{sample code}}\n\n           if self.handling ~= nil and self.handling[key] ~=nil then\n               print(key .. \'!!!\')\n               self.handling[key](self, parameter);\n           end\n		end,\n        update = function(self, interval)\n\n           if self.handling ~= nil and self.handling[\'@update\'] ~=nil then\n               self.handling[\'@update\'](self, interval);\n           end\n        end,\n    \n        init = function(self)\n           if self.handling ~= nil and self.handling[\'@init\'] ~=nil then\n               self.handling[\'@init\'](self);\n           end\n        end,\n        setup = function(self)\n            self.handling = {}\n\n\n\n            self.handling[\'2.12:Action\'] = function(self,  parameter)\n  print(\'2.12:Action\')\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"polygen\",  \"2.3:Turbine Blade\"), false)\nend\n\n\n        end,\n        version = function(self)\n            return 1;\n        end,\n        destroy = function(self)\n            if self.handling ~= nil and self.handling[\'@destroy\'] ~=nil then\n               self.handling[\'@destroy\'](self);\n           end\n        end,\n \n		callback = function(self, evt)\n            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n      \n        return setmetatable({}, logic_mt)\n    end\n}\n	', '{}', 315, NULL, NULL, 117, NULL, NULL, NULL),
(509, '0', '	\nlocal logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			print(\'parameter:\'..json.encode(parameter))\n			\n            --{{sample code}}\n\n           if self.handling ~= nil and self.handling[key] ~=nil then\n               print(key .. \'!!!\')\n               self.handling[key](self, parameter);\n           end\n		end,\n        update = function(self, interval)\n\n           if self.handling ~= nil and self.handling[\'@update\'] ~=nil then\n               self.handling[\'@update\'](self, interval);\n           end\n        end,\n    \n        init = function(self)\n           if self.handling ~= nil and self.handling[\'@init\'] ~=nil then\n               self.handling[\'@init\'](self);\n           end\n        end,\n        setup = function(self)\n            self.handling = {}\n\n\n\n            self.handling[\'3.16:关闭皮肤\'] = function(self,  parameter)\n  print(\'3.16:关闭皮肤\')\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"polygen\",  \"3.15:模型1\"), )\nend\n\n\n        end,\n        version = function(self)\n            return 1;\n        end,\n        destroy = function(self)\n            if self.handling ~= nil and self.handling[\'@destroy\'] ~=nil then\n               self.handling[\'@destroy\'](self);\n           end\n        end,\n \n		callback = function(self, evt)\n            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n      \n        return setmetatable({}, logic_mt)\n    end\n}\n	', '{}', 316, NULL, 1, 119, NULL, NULL, NULL),
(514, '1', '	\nlocal logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			print(\'parameter:\'..json.encode(parameter))\n			\n            --{{sample code}}\n\n           if self.handling ~= nil and self.handling[key] ~=nil then\n               print(key .. \'!!!\')\n               self.handling[key](self, parameter);\n           end\n		end,\n        update = function(self, interval)\n\n           if self.handling ~= nil and self.handling[\'@update\'] ~=nil then\n               self.handling[\'@update\'](self, interval);\n           end\n        end,\n    \n        init = function(self)\n           if self.handling ~= nil and self.handling[\'@init\'] ~=nil then\n               self.handling[\'@init\'](self);\n           end\n        end,\n        setup = function(self)\n            self.handling = {}\n\n\n\n            \n\n        end,\n        version = function(self)\n            return 1;\n        end,\n        destroy = function(self)\n            if self.handling ~= nil and self.handling[\'@destroy\'] ~=nil then\n               self.handling[\'@destroy\'](self);\n           end\n        end,\n \n		callback = function(self, evt)\n            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n      \n        return setmetatable({}, logic_mt)\n    end\n}\n	', '{}', 320, NULL, NULL, 121, NULL, NULL, NULL),
(516, 'andy', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 321, NULL, NULL, 123, NULL, NULL, NULL),
(518, 'test1', '	\nlocal logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			print(\'parameter:\'..json.encode(parameter))\n			\n            --{{sample code}}\n\n           if self.handling ~= nil and self.handling[key] ~=nil then\n               print(key .. \'!!!\')\n               self.handling[key](self, parameter);\n           end\n		end,\n        update = function(self, interval)\n\n           if self.handling ~= nil and self.handling[\'@update\'] ~=nil then\n               self.handling[\'@update\'](self, interval);\n           end\n        end,\n    \n        init = function(self)\n           if self.handling ~= nil and self.handling[\'@init\'] ~=nil then\n               self.handling[\'@init\'](self);\n           end\n        end,\n        setup = function(self)\n            self.handling = {}\n\n\n\n            self.handling[\'5.6:Action1\'] = function(self,  parameter)\n  print(\'5.6:Action1\')\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"polygen\",  \"5.4:mt\"), true)\nend\n\n\n        end,\n        version = function(self)\n            return 1;\n        end,\n        destroy = function(self)\n            if self.handling ~= nil and self.handling[\'@destroy\'] ~=nil then\n               self.handling[\'@destroy\'](self);\n           end\n        end,\n \n		callback = function(self, evt)\n            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n      \n        return setmetatable({}, logic_mt)\n    end\n}\n	', '{}', 323, '5xiaomentou &nbsp;\r\n', NULL, 122, NULL, NULL, NULL),
(520, 'Demo', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 324, NULL, NULL, NULL, NULL, NULL, NULL),
(521, 'work', 'local logic_mt = {\n	__index = {\n		post = function(self, key, json_string)\n			 print(\'key:\'..key)\n			parameter = json.decode(json_string)\n			 print(\'parameter:\'..json.encode(parameter))\n			--begin\n\n			--end\n			\n       \n		end,\n		add_result = function(self, delegate)\n			if self.callbacklist == nil then\n				self.callbacklist = {}\n			end\n			table.insert(self.callbacklist, delegate)\n\n			print(\'add\',delegate)\n		end,      \n		remove_result = function(self, delegate)\n			for i=1, #self.callbacklist do\n				if CS.System.Object.Equals(self.callbacklist[i], delegate) then\n					table.remove(self.callbacklist, i)\n					break\n				end\n			end\n			print(\'remove\', delegate)\n		end,\n        version = function(self)\n            return -1;\n        end,\n		callback = function(self, evt)\n			if self.callbacklist ~= nil then\n				for i=1, #self.callbacklist do\n					self.callbacklist[i](self, evt)\n				end\n			end	\n		end,\n	}\n}\n                   \n\nLogic = {\n	Creater = function ()\n        return setmetatable({}, logic_mt)\n    end\n}\n', '{}', 329, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `project_data`
--

CREATE TABLE `project_data` (
  `id` int(11) NOT NULL,
  `configuration` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `logic` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `project_index`
--

CREATE TABLE `project_index` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `question`
--

CREATE TABLE `question` (
  `id` int(11) NOT NULL,
  `question` varchar(255) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `answer` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `reply`
--

CREATE TABLE `reply` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `reply`
--

INSERT INTO `reply` (`id`, `message_id`, `body`, `author_id`, `updater_id`, `created_at`, `updated_at`, `info`) VALUES
(8, 6, '<p>目前：当不上传图片创建元宇宙会报错422。这个问题已经解决。</p>\n<p>感谢， 下次可以描述更详细点。</p>', 3, 3, '2022-02-08 12:30:48', '2022-02-08 12:30:48', '{\"signature\":\"游戏开发极客\"}'),
(9, 3, '<p>感谢，发现发帖里面竟然没有显示账号，我的问题。</p>', 3, 3, '2022-02-08 12:31:33', '2022-02-08 12:31:33', '{\"signature\":\"游戏开发极客\"}'),
(10, 7, '<p>感谢，目前还在修剪bug，以及补充教程，之后相关功能会逐步测试发布。</p>\n<p>感谢一直以来的关注。</p>', 3, 3, '2022-02-09 16:51:48', '2022-02-09 16:51:48', '{\"signature\":\"游戏开发极客\"}'),
(11, 9, '<p>感谢，现在正在录制和剪辑，下周一会发布出来。</p>\n<p>期待能提出宝贵意见，感谢</p>', 3, 3, '2022-02-12 02:50:21', '2022-02-12 02:50:21', '{\"signature\":\"游戏开发极客\"}'),
(13, 8, '<p>十分感谢，期待之后的进展！</p>', 25, 25, '2022-02-12 02:51:36', '2022-02-12 02:51:36', '{\"signature\":\"免费打工仔\"}'),
(14, 10, '<p>好的，教程正在补充，这周二左右能出来。</p>\n<p>感谢关注</p>', 3, 3, '2022-02-13 15:59:58', '2022-02-13 15:59:58', '{\"signature\":\"游戏开发极客\"}'),
(15, 9, '<p>最晚周二，在努力中。 T_T</p>', 3, 3, '2022-02-13 16:00:43', '2022-02-13 16:00:43', '{\"signature\":\"游戏开发极客\"}'),
(16, 11, '<p>支持，期待</p>', 359, 359, '2022-02-14 09:51:01', '2022-02-14 09:51:01', '{\"signature\":\"dachenbo\"}'),
(17, 13, '<p>终于出来了，支持。</p>', 337, 337, '2022-02-16 02:45:05', '2022-02-16 02:45:05', '{\"signature\":\"rekcah\"}'),
(18, 14, '<p>应该是设置锚点吧，这个功能有开放吗</p>', 337, 337, '2022-02-16 03:20:21', '2022-02-16 03:20:21', '{\"signature\":\"rekcah\"}'),
(19, 14, '<p>之前的系统里面有这个功能，是有64个内建识别图，然后选择一个做标记。</p>\n<p>因为vuforia的升级，支持从网上读取图案，所以现在可以实现的更好，可以自己上传图案（公司logo，项目图案等），实现等位功能。</p>\n<p>所以这块东西我们重新做了，大约两周内能上线，还需要一些测试。</p>\n<p>平台是预留这块接口的。两周内出教程。感谢关注。</p>', 3, 3, '2022-02-16 04:28:05', '2022-02-16 04:28:05', '{\"signature\":\"游戏开发极客\"}'),
(20, 13, '<p>感谢关注</p>', 3, 3, '2022-02-16 04:28:51', '2022-02-16 04:28:51', '{\"signature\":\"游戏开发极客\"}'),
(21, 14, '<p>果然专业！期待。</p>', 337, 337, '2022-02-16 07:47:57', '2022-02-16 07:47:57', '{\"signature\":\"rekcah\"}'),
(22, 16, '<p>顶顶顶</p>', 365, 365, '2022-02-18 02:39:11', '2022-02-18 02:39:11', '{\"signature\":\"zzhzzh\"}'),
(23, 16, '<p>你要是做简单场景，用这个平台就足够了。要是学开发，unity先学起来，那一些简单的例子学习</p>', 3, 3, '2022-02-18 07:06:57', '2022-02-18 07:06:57', '{\"signature\":\"游戏开发极客\"}'),
(24, 15, '<p>iPad可是我们目前能找到最好的AR支持的设备，如果让我们支持其他的Pad....... 得加钱！【脑补锦春刀截图】</p>\n<p>认真回答：目前团队太小没办法同时开展多个平台，iOS对AR支持明显比其他pad更好，而且之后还有一个传说中的 Apple AR Glass的存在，所以目前没有计划开展大范围支持。感谢。</p>', 3, 3, '2022-02-18 08:20:44', '2022-02-18 08:20:44', '{\"signature\":\"游戏开发极客\"}'),
(26, 18, '<p>是否缺少了用hololens 2 扫描ipad 图案的步骤？ 扫描之后得到定位运行就能开始了。</p>', 3, 3, '2022-02-21 02:25:41', '2022-02-21 02:25:41', '{\"signature\":\"游戏开发极客\"}'),
(27, 18, '<p>扫描ipad了，扫描以后会提示连接成功，然后一直卡在这里。</p>\n<p>而且ipad这个界面左上角的返回键非常难按，要点个十来次才会提示是否退出场景。</p>', 337, 337, '2022-02-21 02:37:53', '2022-02-21 02:37:53', '{\"signature\":\"rekcah\"}'),
(28, 18, '<p>好的，我整理下发个新的版本和视频出来。</p>', 3, 3, '2022-02-21 07:06:40', '2022-02-21 07:06:40', '{\"signature\":\"游戏开发极客\"}'),
(29, 18, '<p>我测试了，没发现问题。流程是这样的，只要在一个局域网内，就显示已经连接。扫描成功之后，开始运行才能点击，所以您的描述在我看来似乎是没有扫描成功，这边我之后会加入一个扫描成功的提示。您这边可以尝试多次扫描，扫描成功之后hololens这边的ui会改变。</p>', 3, 3, '2022-02-22 01:38:33', '2022-02-22 01:38:33', '{\"signature\":\"游戏开发极客\"}'),
(30, 19, '<p>我hololens2在扫描我的iphone12promax的时候，就出现一个分红色的圆球，也不提示扫描成功，就卡在这里。</p>', 337, 337, '2022-02-23 13:11:07', '2022-02-23 13:11:07', '{\"signature\":\"rekcah\"}'),
(31, 19, '<p>自己找到问题了，需要都在同一个5G路由下面，换了路由就好了。</p>', 337, 337, '2022-02-23 14:15:36', '2022-02-23 14:15:36', '{\"signature\":\"rekcah\"}'),
(32, 19, '<p>了解了，粉红色的球是用来标记摄像头位置的，因为ar设备的中心点在摄像头而不是几何中心点。我们这里适配了所有ios设备，横屏竖屏。你可以尝试横屏启动。自动会进入横屏状态。</p>\n<p>我相信其他同步可能不会做的这么细致，这个摄像头中心位置如果放在ipad上面会有7厘米的误差。</p>', 3, 3, '2022-02-24 03:18:50', '2022-02-24 03:18:50', '{\"signature\":\"游戏开发极客\"}'),
(33, 18, '<p>加油，我正在整理其他功能，下个月初会有一个完整的教程出来。感谢持续关注。</p>', 3, 3, '2022-02-24 03:19:34', '2022-02-24 03:19:34', '{\"signature\":\"游戏开发极客\"}');

-- --------------------------------------------------------

--
-- 表的结构 `resource`
--

CREATE TABLE `resource` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_id` int(11) NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `updater_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `resource`
--

INSERT INTO `resource` (`id`, `name`, `type`, `author_id`, `created_at`, `file_id`, `image_id`, `info`, `updater_id`) VALUES
(4, '阿福大是大非', 'polygen', 24, '2021-11-19 08:37:41', 445, 516, '{\"center\":{\"x\":0,\"y\":0,\"z\":0},\"size\":{\"x\":0.01984,\"y\":0.01954,\"z\":0.02015}}', 24),
(5, '23423432532423424324a.glb', 'polygen', 24, '2021-11-19 08:41:15', 446, 517, '{\"center\":{\"x\":0,\"y\":0,\"z\":0},\"size\":{\"x\":0.01984,\"y\":0.01954,\"z\":0.02015}}', 24),
(6, 'bef322bb.glb', 'polygen', 24, '2021-11-19 08:47:02', 447, 562, '{\"center\":{\"x\":0,\"y\":0,\"z\":0},\"size\":{\"x\":0.01984,\"y\":0.01954,\"z\":0.02015}}', 24),
(7, 'bef322cc.glb', 'polygen', 24, '2021-11-19 09:21:14', 448, 563, '{\"center\":{\"x\":0,\"y\":0,\"z\":0},\"size\":{\"x\":0.01984,\"y\":0.01954,\"z\":0.02015}}', 24),
(8, 'bef322cc.glb', 'polygen', 24, '2021-11-19 10:08:46', 449, 564, '{\"center\":{\"x\":0,\"y\":0,\"z\":0},\"size\":{\"x\":0.01984,\"y\":0.01954,\"z\":0.02015}}', 24),
(10, '9DLJP50A00AJ0003 (2).png', 'picture', 288, '2021-12-01 03:04:15', 455, 456, NULL, 288),
(11, 'r.png', 'picture', 290, '2021-12-02 09:41:25', 457, 458, NULL, 290),
(20, 'setgile.gltf', 'polygen', 290, '2021-12-06 06:35:52', 480, 481, '{\"size\":{\"x\":126.12770462036133,\"y\":179.11158818006516,\"z\":25.86788558959961},\"center\":{\"x\":0.0000019073486328125,\"y\":90.39182248711586,\"z\":0.5512571334838867}}', 290),
(21, 'testpicture.jpg', 'picture', 24, '2021-12-06 06:37:03', 482, 482, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(22, '2232434311f1jpg.jpg', 'picture', 24, '2021-12-06 06:37:13', 483, 484, '{\"size\":{\"x\":2048,\"y\":1350}}', 24),
(23, 'testpicture.jpg', 'picture', 24, '2021-12-06 06:37:29', 485, 485, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(24, '20211110_190534_HoloLens.mp4', 'video', 290, '2021-12-06 06:40:59', 486, 489, NULL, 290),
(25, 'testpicture.jpg', 'picture', 24, '2021-12-06 06:52:39', 490, 490, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(26, 'f1.jpg.jpg', 'picture', 24, '2021-12-06 07:04:39', 491, 492, '{\"size\":{\"x\":2048,\"y\":1350}}', 24),
(27, 'f1.jpg.jpg', 'picture', 24, '2021-12-06 07:06:27', 493, 494, '{\"size\":{\"x\":2048,\"y\":1350}}', 24),
(28, 'testpicture.jpg', 'picture', 24, '2021-12-06 07:12:45', 495, 495, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(29, 'testpicture.jpg', 'picture', 24, '2021-12-06 07:13:37', 496, 496, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(44, 'testpicture.jpg', 'picture', 24, '2021-12-06 08:29:45', 513, 513, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(45, '5408A.glb', 'polygen', 24, '2021-12-06 08:45:46', 514, 515, '{\"center\":{\"x\":0.00006,\"y\":2.17912,\"z\":-0.04714},\"size\":{\"x\":5.58117,\"y\":4.38126,\"z\":2.17048}}', 24),
(46, '54089e.glb', 'polygen', 24, '2021-12-06 08:46:18', 518, 519, '{\"center\":{\"x\":0.00006,\"y\":2.17912,\"z\":-0.04714},\"size\":{\"x\":5.58117,\"y\":4.38126,\"z\":2.17048}}', 24),
(47, '54089e.glb', 'polygen', 24, '2021-12-06 08:46:32', 520, 521, '{\"center\":{\"x\":0.00006,\"y\":2.17912,\"z\":-0.04714},\"size\":{\"x\":5.58117,\"y\":4.38126,\"z\":2.17048}}', 24),
(48, 'bef322aa.glb', 'video', 24, '2021-12-06 08:55:03', 522, NULL, NULL, 24),
(49, 'ITJC.mp4', 'video', 24, '2021-12-06 08:55:41', 523, 524, '{\"size\":{\"x\":1080,\"y\":800}}', 24),
(51, 'ITJC.mp4', 'video', 24, '2021-12-06 09:00:08', 526, 527, '{\"size\":{\"x\":1080,\"y\":800}}', 24),
(53, 'v2-5a162220d48016c4198cca19aaefd045_r.jpg', 'picture', 24, '2021-12-06 09:10:03', 529, 530, '{\"size\":{\"x\":1920,\"y\":1080}}', 24),
(54, 'wallhaven-289k39.png', 'picture', 24, '2021-12-06 09:10:17', 531, 532, '{\"size\":{\"x\":3200,\"y\":1800}}', 24),
(55, '54089f.glb', 'polygen', 24, '2021-12-06 09:10:34', 533, 534, '{\"center\":{\"x\":0.00006,\"y\":2.17912,\"z\":-0.04714},\"size\":{\"x\":5.58117,\"y\":4.38126,\"z\":2.17048}}', 24),
(56, 'ITJC.mp4', 'video', 24, '2021-12-06 09:10:45', 535, 536, '{\"size\":{\"x\":1080,\"y\":800}}', 24),
(57, 'ITJC.mp4', 'video', 24, '2021-12-06 09:11:32', 537, 538, '{\"size\":{\"x\":1080,\"y\":800}}', 24),
(58, '54089f.glb', 'polygen', 24, '2021-12-06 09:15:49', 539, 540, '{\"center\":{\"x\":0.00006,\"y\":2.17912,\"z\":-0.04714},\"size\":{\"x\":5.58117,\"y\":4.38126,\"z\":2.17048}}', 24),
(59, 'testpicture.jpg', 'picture', 24, '2021-12-06 09:16:26', 541, 541, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(60, 'f1.jpg.jpg', 'picture', 24, '2021-12-06 09:16:41', 542, 543, '{\"size\":{\"x\":2048,\"y\":1350}}', 24),
(61, 'ITJC.mp4', 'video', 24, '2021-12-06 09:18:03', 544, 545, '{\"size\":{\"x\":1080,\"y\":800}}', 24),
(68, 'ITJC.mp4', 'video', 24, '2021-12-07 02:02:47', 560, 561, '{\"size\":{\"x\":1080,\"y\":800}}', 24),
(69, '54089e.glb', 'polygen', 24, '2021-12-07 03:26:24', 565, 566, '{\"center\":{\"x\":0.00006,\"y\":2.17912,\"z\":-0.04714},\"size\":{\"x\":5.58117,\"y\":4.38126,\"z\":2.17048}}', 24),
(70, 'testpicture.jpg', 'picture', 24, '2021-12-07 03:26:42', 567, 567, '{\"size\":{\"x\":960,\"y\":600}}', 24),
(71, 'ITJC.mp4', 'video', 24, '2021-12-07 03:27:08', 568, 569, '{\"size\":{\"x\":1080,\"y\":800}}', 24),
(72, '阿宝地方！~', 'polygen', 24, '2021-12-07 06:31:43', 570, 571, '{\"center\":{\"x\":0.00006,\"y\":2.17912,\"z\":-0.04714},\"size\":{\"x\":5.58117,\"y\":4.38126,\"z\":2.17048}}', 24),
(83, '建筑-样板间-3D打印-三维视图-样板间.glb', 'polygen', 304, '2021-12-13 02:38:12', 585, 586, '{\"size\":{\"x\":0.3143417380493466,\"y\":0.16714587050884983,\"z\":0.6421244659949519},\"center\":{\"x\":-0.11230214778910802,\"y\":-0.037965912599232904,\"z\":0.32116601190589567}}', 304),
(85, '圣诞花环1.jpg', 'picture', 311, '2021-12-16 14:29:39', 589, 590, NULL, 311),
(87, '111', 'polygen', 311, '2021-12-16 14:33:51', 593, 594, '{\"size\":{\"x\":168.25,\"y\":33.5000000000002,\"z\":88.25000000000001},\"center\":{\"x\":-76.125,\"y\":16.749999999999893,\"z\":65.125}}', 311),
(88, 'turbine blade.glb', 'polygen', 315, '2021-12-22 01:29:09', 595, 596, '{\"size\":{\"x\":0.3600926367772904,\"y\":0.5119999248240958,\"z\":0.32670925696647046},\"center\":{\"x\":-2.1941977679240665e-9,\"y\":2.29770179771549e-7,\"z\":0.2559998184537964}}', 315),
(89, 'human cell.glb', 'polygen', 315, '2021-12-22 01:34:31', 597, 598, '{\"size\":{\"x\":0.5120000000695843,\"y\":0.4728267571583314,\"z\":0.5007047719809932},\"center\":{\"x\":3.8797840745674606e-8,\"y\":-5.037204871827239e-7,\"z\":0.2560019652844403}}', 315),
(90, 'Jupiter.glb', 'polygen', 315, '2021-12-22 01:36:26', 599, 600, '{\"size\":{\"x\":0.5119998894095446,\"y\":0.5119999870657921,\"z\":0.5119998405814208},\"center\":{\"x\":5.507018618899906e-8,\"y\":-4.930485602017143e-7,\"z\":0.2560019981384265}}', 315),
(91, 'DNA.glb', 'polygen', 315, '2021-12-22 02:05:44', 601, 602, '{\"size\":{\"x\":0.10976296572627042,\"y\":0.5120000163860929,\"z\":0.10853899174807902},\"center\":{\"x\":4.438887657443047e-8,\"y\":-4.808034592684862e-7,\"z\":0.25600196825876753}}', 315),
(98, '0', 'picture', 316, '2021-12-23 23:54:30', 614, 615, NULL, 316),
(100, '颈椎', 'polygen', 316, '2021-12-24 00:29:03', 618, 619, '{\"size\":{\"x\":70.16619873046875,\"y\":89.70680046081543,\"z\":122.8459939956665},\"center\":{\"x\":-8.873199462890625,\"y\":55.01780033111572,\"z\":-72.57399702072144}}', 316),
(106, '手术模型', 'polygen', 320, '2021-12-25 12:15:08', 630, 631, '{\"size\":{\"x\":165.84110260009766,\"y\":204.66919708251953,\"z\":329.32000732421875},\"center\":{\"x\":-6.013851165771484,\"y\":9.108402252197266,\"z\":-38.733001708984375}}', 320),
(107, '肿瘤.stl.glb', 'polygen', 320, '2021-12-25 15:26:23', 632, 633, '{\"size\":{\"x\":23.589248657226562,\"y\":27.649757385253906,\"z\":32.99153137207031},\"center\":{\"x\":114.12702941894531,\"y\":42.352256774902344,\"z\":-108.87992095947266}}', 320),
(108, '动脉血管.stl.glb', 'polygen', 320, '2021-12-25 15:29:40', 634, 635, '{\"size\":{\"x\":139.89338302612305,\"y\":135.69890055060387,\"z\":152.85285186767578},\"center\":{\"x\":102.70154762268066,\"y\":68.31004984676838,\"z\":-104.38994979858398}}', 320),
(111, '脑出血', 'polygen', 316, '2021-12-26 08:41:22', 640, 641, '{\"size\":{\"x\":104.04219913482666,\"y\":202.80579376220703,\"z\":144.1313018798828},\"center\":{\"x\":41.843799114227295,\"y\":-9.172100067138672,\"z\":106.94734954833984}}', 316),
(112, '脑出血皮肤', 'polygen', 316, '2021-12-26 08:56:30', 642, 643, '{\"size\":{\"x\":104.03180313110352,\"y\":202.80579376220703,\"z\":144.1313018798828},\"center\":{\"x\":41.84560203552246,\"y\":-9.172100067138672,\"z\":106.94734954833984}}', 316),
(113, '1226.glb', 'polygen', 323, '2021-12-26 14:53:07', 644, 645, '{\"size\":{\"x\":14909.31714630127,\"y\":5050,\"z\":3100},\"center\":{\"x\":-7395.341426849365,\"y\":2475,\"z\":0}}', 323),
(114, '122701.glb', 'polygen', 323, '2021-12-27 12:43:01', 646, 647, '{\"size\":{\"x\":2000,\"y\":1230.12939453125,\"z\":2000},\"center\":{\"x\":0,\"y\":615.064697265625,\"z\":0}}', 323),
(116, '3232.glb', 'polygen', 329, '2022-01-09 04:04:54', 649, 650, '{\"size\":{\"x\":61.1864013671875,\"y\":23.56781005859375,\"z\":20.63587188720703},\"center\":{\"x\":829.9264526367188,\"y\":1020.1427307128906,\"z\":110.31740188598633}}', 329),
(119, '3232.glb', 'polygen', 337, '2022-02-02 08:14:56', 665, 745, '{\"center\":{\"x\":829.92645,\"y\":1020.14273,\"z\":110.3174},\"size\":{\"x\":61.1864,\"y\":23.56781,\"z\":20.63587}}', 337),
(121, 'SpaceShip.glb', 'polygen', 338, '2022-02-02 15:03:07', 671, 709, '{\"center\":{\"x\":0,\"y\":0,\"z\":0.39027},\"size\":{\"x\":0.512,\"y\":0.37382,\"z\":0.78054}}', 338),
(122, 'Clider.glb', 'polygen', 338, '2022-02-02 15:16:02', 674, 708, '{\"center\":{\"x\":-0.00202,\"y\":-0.03998,\"z\":0.19985},\"size\":{\"x\":0.56719,\"y\":0.91945,\"z\":0.81871}}', 338),
(126, 'src=http___inews.gtimg.com_newsapp_bt_0_13887652789_641.jpg&refer=http___inews.gtimg.jpeg', 'picture', 25, '2022-02-12 02:52:58', 713, 713, '{\"size\":{\"x\":480,\"y\":421}}', 25),
(127, 'new.glb', 'polygen', 357, '2022-02-13 01:21:17', 714, 715, '{\"center\":{\"x\":0,\"y\":1.33005,\"z\":-0.03729},\"size\":{\"x\":3.19093,\"y\":2.67156,\"z\":1.49422}}', 357),
(128, '1.jpg', 'picture', 357, '2022-02-13 06:07:12', 716, 717, '{\"size\":{\"x\":1260,\"y\":1791}}', 357),
(137, 'shiba.glb', 'polygen', 357, '2022-02-15 05:49:58', 736, 737, '{\"center\":{\"x\":0,\"y\":-0.31478,\"z\":-0.52313},\"size\":{\"x\":0.9209,\"y\":1.38104,\"z\":1.77477}}', 357),
(138, 'HoloLens 2', 'polygen', 3, '2022-02-15 12:24:06', 738, 739, '{\"center\":{\"x\":0,\"y\":0.00001,\"z\":-0.00094},\"size\":{\"x\":0.7108,\"y\":0.4516,\"z\":1.09112}}', 3),
(139, 'Astronaut-scaled.jpeg', 'picture', 3, '2022-02-16 08:59:50', 741, 741, '{\"size\":{\"x\":550,\"y\":950}}', 3),
(141, '504337197-1-208.mp4', 'video', 3, '2022-02-18 02:40:10', 743, 744, '{\"size\":{\"x\":1920,\"y\":1080}}', 3),
(142, '999.glb', 'polygen', 337, '2022-02-20 11:47:03', 746, 747, '{\"center\":{\"x\":829.92645,\"y\":1020.14273,\"z\":110.3174},\"size\":{\"x\":61.1864,\"y\":23.56781,\"z\":20.63587}}', 337),
(143, '111.glb', 'polygen', 337, '2022-02-20 11:53:46', 748, 749, '{\"center\":{\"x\":0,\"y\":0.673,\"z\":8.61102},\"size\":{\"x\":200,\"y\":61.346,\"z\":217.22204}}', 337),
(144, 'magic-leap-2.glb', 'polygen', 337, '2022-02-20 12:28:13', 750, 751, '{\"center\":{\"x\":0,\"y\":-0.56737,\"z\":-0.98762},\"size\":{\"x\":2.29317,\"y\":1.34849,\"z\":3.63668}}', 337),
(145, 'BoomBox.glb', 'polygen', 3, '2022-02-22 03:25:13', 752, 753, '{\"center\":{\"x\":0,\"y\":0,\"z\":0},\"size\":{\"x\":0.01984,\"y\":0.01954,\"z\":0.02015}}', 3),
(146, '厕所架2.0.glb', 'polygen', 368, '2022-02-23 13:04:51', 757, 758, '{\"center\":{\"x\":0,\"y\":0,\"z\":0.256},\"size\":{\"x\":0.512,\"y\":0.37101,\"z\":0.37101}}', 368),
(147, 'monster_by_mineworker-d419sgc.jpg', 'picture', 368, '2022-02-23 13:06:27', 759, 760, '{\"size\":{\"x\":1599,\"y\":1078}}', 368),
(148, '高模冰墩墩.glb', 'polygen', 368, '2022-02-23 13:27:23', 761, 762, '{\"center\":{\"x\":0,\"y\":0,\"z\":0.256},\"size\":{\"x\":0.50425,\"y\":0.512,\"z\":0.35007}}', 368),
(150, 'pipe', 'polygen', 337, '2022-02-24 01:41:53', 764, 765, '{\"center\":{\"x\":-8.3825,\"y\":1.19872,\"z\":9.64431},\"size\":{\"x\":0.89167,\"y\":0.33912,\"z\":1.75013}}', 337),
(151, '13976.jpg', 'picture', 368, '2022-02-24 02:41:34', 767, 767, '{\"size\":{\"x\":400,\"y\":300}}', 368),
(152, 'lymphatic.glb', 'polygen', 3, '2022-02-24 04:09:42', 769, 770, '{\"center\":{\"x\":-0.34338,\"y\":-108.47964,\"z\":782.35611},\"size\":{\"x\":653.61212,\"y\":1711.4942,\"z\":244.35944}}', 3),
(153, 'craneo.glb', 'polygen', 3, '2022-02-24 04:11:20', 771, 772, '{\"center\":{\"x\":-0.09457,\"y\":2.88975,\"z\":-2.07657},\"size\":{\"x\":16.74868,\"y\":20.86491,\"z\":20.40672}}', 3),
(154, 'GLB-MODE.glb', 'polygen', 3, '2022-02-24 07:03:32', 773, 774, '{\"center\":{\"x\":-15.8904,\"y\":-116.54045,\"z\":-227.65085},\"size\":{\"x\":294.64851,\"y\":188.48087,\"z\":321.33721}}', 3);

-- --------------------------------------------------------

--
-- 表的结构 `scripts`
--

CREATE TABLE `scripts` (
  `id` int(11) NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `script` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `scripts`
--

INSERT INTO `scripts` (`id`, `key`, `script`) VALUES
(1, 'default', 'local logic_mt = {\r\n\r\n    __index = {\r\n        post = function(self, key, value)\r\n			print(key)\r\n            self:callback({key = \'MrPP.Lua.LuaTriggerManager@Manager\', value = \'self:trigging(\"\'..json.decode(value)[\'value\']..\'\")\'})\r\n        end,\r\n\r\n        add_result = function(self, delegate)\r\n	        if self.callbacklist == nil then\r\n		        self.callbacklist = {}\r\n	        end\r\n	        table.insert(self.callbacklist, delegate)\r\n\r\n            print(\'add\',delegate)\r\n        end,      \r\n        remove_result = function(self, delegate)\r\n            for i=1, #self.callbacklist do\r\n		        if CS.System.Object.Equals(self.callbacklist[i], delegate) then\r\n			        table.remove(self.callbacklist, i)\r\n			        break\r\n		        end\r\n	        end\r\n            print(\'remove\', delegate)\r\n        end,\r\n\r\n        callback = function(self, evt)\r\n	        if self.callbacklist ~= nil then\r\n		        for i=1, #self.callbacklist do\r\n			        self.callbacklist[i](self, evt)\r\n		        end\r\n	        end	\r\n        end,\r\n    }\r\n}\r\n                   \r\n\r\nLogic = {\r\n	Creater = function ()\r\n        return setmetatable({}, logic_mt)\r\n    end\r\n}');

-- --------------------------------------------------------

--
-- 表的结构 `script_data`
--

CREATE TABLE `script_data` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dom` text DEFAULT NULL,
  `code` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `tags`
--

INSERT INTO `tags` (`id`, `name`, `info`) VALUES
(1, '交流', '{\r\n\"color\":\"#edf5ff\",\r\n\"explan\":\"主板，综合显示\"\r\n}'),
(2, '展示', '{\r\n\"color\":\"#f1f9eb\",\r\n\"explan\":\"分享优秀的作品\"\r\n}'),
(3, '建议', '{\r\n\"color\":\"#e7f7fd\",\r\n\"explan\":\"听取建议共同致力社区生态\"\r\n}'),
(4, '问题', '{\r\n\"color\":\"#fcf6ec\",\r\n\"explan\":\"回复并帮助解决问题\"\r\n}'),
(5, '错误', '{\r\n\"color\":\"#fcf0f0\",\r\n\"explan\":\"收集反馈定期处理\"\r\n}');

-- --------------------------------------------------------

--
-- 表的结构 `url`
--

CREATE TABLE `url` (
  `id` int(11) NOT NULL,
  `version` int(11) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `url`
--

INSERT INTO `url` (`id`, `version`, `key`, `value`) VALUES
(1, 1, 'api', 'https://api.mrpp.com/'),
(2, 1, 'web', 'https://mrpp.com/'),
(3, 1, 'app', 'https://app.mrpp.com/'),
(4, 1, 'list', 'https://public.mrpp.com/'),
(5, 1, 'public', 'https://public.mrpp.com/');

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 10,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `verification_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wx_openid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `avatar_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `created_at`, `updated_at`, `verification_token`, `access_token`, `wx_openid`, `nickname`, `info`, `avatar_id`) VALUES
(3, 'dirui', 'iNZYA1VxZOpyavsREAeFqLnF-HowC8UL', '$2y$13$OZY0OxP8HG5b9HlJBK2MseH8pW1agWRn9CcVL/C3grNXtZFkM7YM2', 'C5hj3HJ70qHt1JuD_jMyP4QPV2yeloyb_1637221641', 'dirui1981@gmail.com', 10, 1552152063, 1644806493, NULL, NULL, 'ol2S459vnAQOAHKfy_tRPnitgStc', '游戏开发极客', '{\"sex\":\"man\",\"industry\":\"科技、信息技术\",\"selectedOptions\":[\"310000\",\"310100\",\"310117\"],\"textarea\":\"想要做游戏开发的\"}', 654),
(22, 'kurapika', 'QWg16B3YAFL5xoGQ4ZDwOYcpWyc1rGsS', '$2y$13$Vhff4n3p2ab2JHOeTti2neyy1GgSBgGccLDwhG.Twjnu29UD33gSO', NULL, '624967391@qq.com', 10, 1558664756, 1624334260, 'XXsemMMgkyXd_SzWrmbzEPCI16KcIacT_1558664756', 'gYpQNyUHD0y1ZE8M6mV9NECLeE3Tx40i', NULL, NULL, NULL, NULL),
(23, 'baby2nana', '8V3HXqZpMHKlNgORteM9Efz_-v2W7Ki4', '$2y$13$LODBrCUf2aBxW4bh3iI6KuuwPkHwMvM1WUzCd6woZqZ/eMFY4y25e', '2', 'jinbao5488@163.com', 10, 1558664797, 1567135305, 'sWubL8d498Ol9stRrxdfaSB1Z3HUSqRh_1558664797', NULL, NULL, NULL, NULL, NULL),
(24, 'guanfei', 'MbZmlCN2mF2U1oAoGucybtvZjRu4vxL1', '$2y$13$GwSfZiqygjNfpMYswBTWFeasEPGPEAJXMev3GLpHrNamj72oRc60q', '1', '1845008878@qq.com', 10, 1558664856, 1645597496, '-yo5EYbMXSq2unrDTvySRbQDMbvreVzi_1558664856', 'YQJT0V5RF47pt3M-sezW3aXk37_pioET', NULL, '临时管理员', '{\"sex\":\"man\",\"industry\":\"科技、信息技术\",\"selectedOptions\":[\"430000\",\"430100\",\"\"],\"textarea\":\"dajiahao\"}', 660),
(25, 'dirui1981', 'WgWaDtRd7erWUuLnltJzvanSdXeCzt7C', '$2y$13$mkUU1ZYSrkjCo9T/rEXrj.vGzogEKkSaU1njZmx/UwmVZ1S73Rf9q', NULL, 'dirui1@yeah.net', 10, 1561879946, 1644634282, '6rgK8_A2fGARX1-U6XKAqk7njGVo2k3J_1561879946', NULL, NULL, '免费打工仔', NULL, 711),
(26, 'orange030', 'yrAUJlRvGSD_y94R5lB9Pkn6wVQRDdrt', '$2y$13$cf1gYVLj4t5dVeu9MtGbpe5YmFiTcD9jdMX9llcmXaHqYEIJ.gHDe', NULL, '530161671@qq.com', 10, 1561880962, 1561880991, 'JtNm6VI7Q22l86iedAU_vYr34gURgVH__1561880962', NULL, NULL, NULL, NULL, NULL),
(27, 'drink', 'MBXhVcvQeEkRZ5rCZ1hH2xZogwWgv3-l', '$2y$13$.mFmVoWtutOKExtiQsfdVeK4vRdi/INELChdGpvur5XB5A62CGaKW', NULL, 'drinkw@outlook.com', 9, 1561882446, 1561882446, 'eA_Ndx_-5A0QzfAV3t4bLTx6SkYVbMpb_1561882446', NULL, NULL, NULL, NULL, NULL),
(28, 'littlezzuni', '1cjZn74ICkskiPaRjGU3RF9YMwWg-q_d', '$2y$13$J7YzIC4DUPhfz6lCBvLbBe24f.iS46wHdSEbB6WdEnAisWX93lcQm', NULL, '616522219@qq.com', 10, 1561885916, 1561885940, 'gbWqDD6WZVeoGxGiAkZ4SnSvvVBwbaTK_1561885916', NULL, NULL, NULL, NULL, NULL),
(29, 'desedematong', '75TSP-jcJxMGFOEv7tJqub-QbCWTAjSg', '$2y$13$8A20ZlN/7NA9yqXG3dQRc.D6DYcQTuxzGzJcAFVsYi5Gocl7qLHb.', NULL, '1074316837@qq.com', 10, 1561901180, 1561901224, 'PofiOw5YG5uZ5ffe_ZqSQZBozPPg_bmI_1561901180', NULL, NULL, NULL, NULL, NULL),
(30, 'sylt0475', 'E22Bqe_1dUCYpzI7ysymVcNrdcKIKz4x', '$2y$13$plWTsG2nu9oBJrl3tEF7q.TnR3XmVEZU4wHknmU8Q98CeLYboqZFm', NULL, 'sylt0475@163.com', 10, 1561940614, 1561941219, 'Y15LcYy_e6rYnui9Vi8OrTzl-qpUeCNN_1561940614', NULL, NULL, NULL, NULL, NULL),
(31, 'sylt0471', '76TZrwgmDTqulLfiP0XycRuWzDc8obJ4', '$2y$13$51ueqaaRifIrDgvFcNQxfOtYmYpUVQaDe55RHHKfuVpwgGQE8FeLS', NULL, '576668230@qq.com', 9, 1561941139, 1561941139, 'aTm2trilQvHIh1UjcuATjPyE6T8pupp-_1561941139', NULL, NULL, NULL, NULL, NULL),
(32, 'Feiwang', 'MYKqajBfneq-UQyXpa0NI3gvq41YSM6x', '$2y$13$60iFkHp91lw3er/TAE07J.2X63FxDL2esBIUM7ZWGPHPcu0FCOdhi', NULL, 'wangfei@hqetwin.com', 10, 1561944684, 1561944782, 'u4fzxW2fNTIVmqCnepW4OC0trupqAsve_1561944684', NULL, NULL, NULL, NULL, NULL),
(33, 'zhongbin', 'kwidaDNYJWG7ZTdtypWJS4VchmGnyc0s', '$2y$13$N6cou7aW72lR3XcG7GAcIOxlevXfYZn27c7e/swHQzqJdIWpuSgkO', NULL, '15921550326@139.com', 10, 1561947260, 1563798287, '6txr4_wbN6txvR6On1zTTFWJPnkSDV-A_1561947260', NULL, NULL, NULL, NULL, NULL),
(34, '武腾游戏', '21qgr20Nzrd1RXqiu5izMx2S8MeEG1sA', '$2y$13$ixhmWWY4PFgQP/p/yO79kedB/OxzsangVmjukLba/7dCBAFm/kwnm', NULL, 'huangtenglei@126.com', 10, 1561947354, 1561947473, 'MhX0BiKwTXAsAqYvWMhb1kmtgMyrtdYy_1561947354', NULL, NULL, NULL, NULL, NULL),
(35, '诗远', '_M0LzLo3ZnGNFPbSK9dTqocCZN1Zkdqv', '$2y$13$6LCYlg0fbNckAFMSFzz7CuNqWZa4uzccOfwMwgjZX5T4O8j5aOyum', NULL, '991959229@qq.com', 10, 1561949041, 1561949211, 'OCtlZtzG9tLfmz-Uctm3g4MvWdkl06zy_1561949041', NULL, NULL, NULL, NULL, NULL),
(36, 'test20190701', 'A8NDvIROVYqbzL9oH-mrnQ2F7sIeCa6Y', '$2y$13$Ce5EdE7kfwxhkfFwXx8IgOjr2v4PKOXaDUpoFQ3VXvGVjth8/rnGy', NULL, '838080757@qq.com', 10, 1561987758, 1561988415, 'JuWrbRcIr1Ic_xXuYz6h610U7QLIZal__1561987758', NULL, NULL, NULL, NULL, NULL),
(37, '潘辉的泰迪世界', 'cMpQFhGCruoZXq4i5NXw4O2ojGAsVMd6', '$2y$13$pJsnSEKOlRGOg0ywDLWsPeR6ImF7hwF9WM9yOgJXa6AqC20r76sWK', NULL, 'ph@teedvr.com', 10, 1561992111, 1561992923, '5INCRpJg4aQqcjsD3BTesiT0LrHEwkzF_1561992111', NULL, NULL, NULL, NULL, NULL),
(38, 'blazingorange', 'NPOXeeJ4FDA8rhDp6Z4qE9tkPve3P3I5', '$2y$13$hzL19yxm3hkdp8FITdECV.UlJlPCpuBQVvD/t5L4y9qFJoo4Dfun6', NULL, 'blazingorange001@163.com', 10, 1562140530, 1562140569, 'pi0uk9WNRRpIpXknoFvmD_Vi7QPyenPy_1562140530', NULL, NULL, NULL, NULL, NULL),
(39, 'hasalready', 'dnaI3pnUa0yEeaI17pdZeuSU6KHf9ya8', '$2y$13$RLgtUi7S1kAkOtrluMMMgOGW.L7mX5uFVrjBNjcFM2o9.bvK885Ky', NULL, 'hasalready@gmail.com', 9, 1562291657, 1562291657, 'g0P5tynAhxkgQrYjul6_GCZyx4c5ggiM_1562291657', NULL, NULL, NULL, NULL, NULL),
(40, '854077880', 'Si9JMP449QkLDsPbxeokx-u1L9P-WaSa', '$2y$13$ZvtFq26sSrVnG7WtgYtdEuoaa0NRFAaMLQG1z6c4Bb6KwlEopCBzi', NULL, '854077880@qq.com', 10, 1562291830, 1562291851, 'YrQEbKqupsb6VEbQHXlkB3qaSnLD39rQ_1562291830', NULL, NULL, NULL, NULL, NULL),
(41, 'liupeifeng0217', 'ZUatFJA3wooRA1SC8HuiN0H6ayid1iKH', '$2y$13$H5vI.R9TQWbT.6dKNG3j3u2j496YQW5wiRZOEzsWUSkBXgOTIfVRK', NULL, '1821521018@qq.com', 10, 1563249636, 1563249708, 'I33Bkxy7w0JUC97WiiaQvk2jS90lyVoB_1563249636', NULL, NULL, NULL, NULL, NULL),
(42, 'lh', '7mEikarhb4AkzTeNjZqqd6o3F6RG6QGC', '$2y$13$78qYuBtrZMPq43Rm7NdA.euCqNB1nCwFl/SVuqWX7Zk2djDnxKfSy', NULL, '784116211@qq.com', 10, 1563268653, 1563268682, '2mGfZIEoLfBCieFBM7pxQDg6mPuV-PMa_1563268653', NULL, NULL, NULL, NULL, NULL),
(43, '我要炸地球', 'W1XgqeiIyQC4DofiOPJQySRWBjU2hra4', '$2y$13$MOAYtzlR2SHj4qUcPFemc.M8h3Zb4Zj1tSyAfucihqDl8zHiAJhX.', NULL, '1002318317@qq.com', 10, 1563785563, 1620796983, 'CBVidpYZqHSKJvppDapLSpos-f8GhIwL_1563785563', 'k1W3Haesa_kZcxlHTRfHy1lmps0SOdlQ', NULL, NULL, NULL, NULL),
(44, 'dege301', 'bAbRA70SMETCzdh_9kSYZEFIePE8H0ZS', '$2y$13$LudbYzKIkYzrDU.sGd8bneisK5UxO3jUC6QnK9rKj1t/70eYS1zye', NULL, 'zhenglijiandege@163.com', 10, 1564543453, 1564543467, 'LNBA_u7W-dPHYixhTbOMoPVIBfbsNf3w_1564543453', NULL, NULL, NULL, NULL, NULL),
(45, 'yaolicheng', 'Vsdvdzw1H7r9Z4Fp5gdpmGX4aoaLGNra', '$2y$13$Bx8bpDMix/caD6Jj46oac.qcjnPzr7JkTPZm2FfLL9WTWbZlF6JKy', NULL, 'yaolicheng@vip.163.com', 10, 1564711194, 1564711217, '8o8tLpuz6eXPtez14gIv_s-rANhaMUyi_1564711194', NULL, NULL, NULL, NULL, NULL),
(46, 'Jason', 'ssiH5Dh5xfzY9DJOiygCUIhlpf71xVlz', '$2y$13$L2iYfXh70GkVCGBxmIZO5e5OOl7q4waxR2uUwp8xXVsDbL3MCv66a', NULL, '17600243103@163.com', 10, 1564713687, 1564713719, 'Mw_CvlUoixF-VK7QDnaykc-v1l9KHnl-_1564713687', NULL, NULL, NULL, NULL, NULL),
(47, 'yaolicheng_sz', 'fkj8ZO7sbSvHbB5Pv1bE6u87w3_HQXMM', '$2y$13$NPRh4P.zJE1C0JBgYTsuteZ1FN1sI8XfzLVx4XKCwzWUlC2doy262', NULL, 'yaolicheng@sztnit.com', 10, 1564714835, 1564714873, 'M94n8MRhJhaSAyHf9Qe1yOOuueEgs-gU_1564714835', NULL, NULL, NULL, NULL, NULL),
(48, '小学生', 'Nf3ZjBO7wlDc9hdj0JaYRAW74_-xL8Ez', '$2y$13$x9TpAWDbWsiN5B9qezp8B.OxIbJaHaO8nxqQqKsd/xuTj8qpdEGxO', NULL, '893358985@qq.com', 10, 1564715105, 1566812133, 'DAMupRUdGvmIy6BYg7-bvaE2BOeA63PQ_1564715105', NULL, NULL, NULL, NULL, NULL),
(49, 'Vincon', 'hr027AgLbL7HlGGBkXWgCl3vNyVscoai', '$2y$13$x0gzU4UNEQIHhvkeLSQTLOQoFLZkxzDvsRPdq9/vHheMGKLnQHORm', NULL, '18848957787@163.com', 10, 1564976202, 1565584268, 'zp3b25g6n2Dv4zpzSlLhaIluKCFlUu1u_1564976202', NULL, NULL, NULL, NULL, NULL),
(50, 'jack', '01Z0V8v2qlorkmrOw8ejtZvs3in62Uea', '$2y$13$cYmfcRqkc8dFJW/0A/6.LO/1P9RMD1Sq5MZ97PMnfmS0kGm0i0VuK', NULL, '13760895342@qq.com', 10, 1565066845, 1565066863, 'n6qjp9Wq6WipazRa0vGWifztfvYp7TS5_1565066845', NULL, NULL, NULL, NULL, NULL),
(51, 'w122479285', 'k_E3xo6_zLCbKUOUi9k3a8tfC-AnKpgn', '$2y$13$FKYVExepI5KyYmrlWje2seaKab7yBxaB8nPMLLp6biSkbaK1eU732', NULL, '122479285@qq.com', 10, 1565074986, 1565075006, 'WBytk0huTUZEHwGt2Z7FnlmcDbsLenmM_1565074986', NULL, NULL, NULL, NULL, NULL),
(52, 'feitao1990', 'oxy5q9ddcyY__sD20eQn4egkUxh6aZrG', '$2y$13$qUD8kQvGDlHF3QWWhz6F/exnx4BfkT0U8fVnMWWcnRsiX0smauIt6', NULL, '18818264769@163.com', 10, 1565139044, 1565139068, 'wDbHKbHfWhsrZSUDNCGqDXsa_B21Lfri_1565139044', NULL, NULL, NULL, NULL, NULL),
(53, 'jinshushu', '0tsDJfJCQUmI2yAQi4piSoxkYx53uOq3', '$2y$13$kTbpONhGisoiODOVYDSyhOYwenCMx2lq6QO4Jnq0ZGBmWigrZsSjW', NULL, '1003586164@qq.com', 10, 1565252889, 1565253015, 'j3K548_HsfGCx63ts2ajSEnwT-4PjDtv_1565252889', NULL, NULL, NULL, NULL, NULL),
(54, 'Quantum Ma', 'knDFBuiWXojFuP3bKf6VCQMoEuT7K9qG', '$2y$13$Nj0UixV/D.ImCpIaOORV6OBGVRDsAP6pyuRbg2DaUNP9v7.Po7B0i', NULL, '814498124@qq.com', 10, 1565579332, 1565579370, 'HDjxqCfclypGcvMLo_MjuZz8jmY1Y0bb_1565579332', NULL, NULL, NULL, NULL, NULL),
(55, 'hcs66', 'vQjd04LX1OCW9dROtikrum211Q2neYL0', '$2y$13$G0QB4d51DzGckknUNDSA8O45bnBv3I98kvzcriTAUhadC/VKLQZEm', NULL, '690090@qq.com', 10, 1566546560, 1566548437, '8C1BjPThk95EzmwHRMAzfx_qxKwM3q7J_1566546560', NULL, NULL, NULL, NULL, NULL),
(56, 'KENCHIKULIU', 'IkvdBa0hMoju6dIXZjnExGRPuITsaFJL', '$2y$13$ADmjM5DyRruhcudYDF1CvuSyrQPtNfkct5OfmIBmvtURXFOsw7V82', NULL, 'wetermelon2010@qq.com', 10, 1566570848, 1566570893, 'Adbs6TPov3oVt0Zul6I_3bBN1qaHAx55_1566570848', NULL, NULL, NULL, NULL, NULL),
(57, 'BradeSummerTT', 'c7N8yc4NCl_Ob6neRiWySN-U36HO1ZXM', '$2y$13$6krzjsTBLG4Yap62gwjDmuz4K6KpJr2hk5ULXnsBNzREbysKQ18lm', NULL, '1050412497@qq.com', 10, 1566593186, 1566593199, 'td-dZV6KBVUy1JJkUDtoyXhX_Z4yL9hp_1566593186', NULL, NULL, NULL, NULL, NULL),
(58, '宋皓', 'cwKsjQuq8wVP2cu1Snc7FduZ8miajq-T', '$2y$13$IYtGZU.oGHgBHVg9tAzb6eAkJXxEFiHaNxBP.lTGg.l6XWS28AsyC', NULL, '724361033@qq.com', 10, 1566652574, 1566652590, 'ZCr3Ld3a_mrfy6BVh7MPTJ0uKqo_t75Q_1566652574', NULL, NULL, NULL, NULL, NULL),
(59, 'houqingyuan', 'IxtVap8XOMUw07Ca0H3ESThRcz6AMFZi', '$2y$13$9CJTCQMe5gfInsPSl3jN4OsKKROplWrKUTemQ4fPFonHrh8g1z9t2', NULL, 'strive_to123@163.com', 10, 1566829651, 1566831717, 'tFg3ZmEe1u1ArzKIRfmZ36niiWOvZ1Ty_1566829651', NULL, NULL, NULL, NULL, NULL),
(60, 'haohaitao', 'IVEojW2VbeztyEmmOd6TgQy7PUI3wmai', '$2y$13$jCNoenE.tqQs1bTzvkIiyuWGM9mtV/lW9tul7awlrkb686dFfFIhG', NULL, '525707880@qq.com', 10, 1566896080, 1566896569, '4EapKvaHIf1W4ctQ2zhoqbFV0JVvjpr0_1566896080', NULL, NULL, NULL, NULL, NULL),
(61, 'guoqshun', 'wU7VdhkB8T7zQ40dxudKB5dvNL3rl79n', '$2y$13$2NeSNPmVlxU5daAeQC9KceXUwclNjjZacNK8JXS/Jbx8BDqTUHIv2', NULL, '450436164@qq.com', 10, 1566982866, 1566982894, 'ZA80nMJQVsggejj1N9ofLSvEELkCLbEe_1566982866', NULL, NULL, NULL, NULL, NULL),
(62, 'nethz', '8PyjaLpF_RjDF2EZcnjoJU8AqYAeVUQv', '$2y$13$cgmWlXllPFVmbr8WpRfvjuktylwi7Fy0vZegUvYKjOdGhKnNTQFMi', NULL, 'ceo@7dgame.com', 10, 1567019247, 1567019283, 'ygrw7kQA6tzmBb-GVMPdqNYNSTVMjkEZ_1567019247', NULL, NULL, NULL, NULL, NULL),
(63, 'ading19811126', 'W9iI4B8sDjXAbutk_XrwgMJxthc7DEN3', '$2y$13$ItI8ZaLmhN4B23.ImP3bqeyS9kgPpzFQUF1NRsnpPYqLlGlkXtgg6', NULL, 'zhaojienet@outlook.com', 10, 1567058633, 1567058709, 'q47oTuty5as6I19dyE6tlLWgFwu7Q5Lj_1567058633', NULL, NULL, NULL, NULL, NULL),
(64, 'limit7', 'jhMkQVqdqeGjqDpv99rR5F1IPHYQFmCc', '$2y$13$5hrUp8VeTAeUM9.54xvuDeKnScMR5GBa3rrG4PM3NNtvT2u5mH6mm', NULL, '397777940@qq.com', 10, 1567060726, 1567060743, '5QV_OstA_uKX7LhKJO5c_qT0yWHKZVvJ_1567060726', NULL, NULL, NULL, NULL, NULL),
(65, 'jkjkjk', 'QT0ot92oq_f7PTMSKZRmbO1g7o6LyXYr', '$2y$13$PlMplx.Hfx3y3lmXFmGX9eeG/f3E9wFUSKl3oaARFFzQBUCCGuLcm', NULL, '992243037@qq.com', 10, 1567130559, 1567130593, '_LceoYuqqF8qOCMjmZ5zBaxOPjVkyL_9_1567130559', NULL, NULL, NULL, NULL, NULL),
(66, 'surpassable', 'H9hVYmoWiXy6Ab_DuXml9Gbl8BC1qqNf', '$2y$13$tgsWdtYnZXN5tvrfTgIo8uZvOmWKnnxOSrofd1XYWjfIDZ8bI3PW2', NULL, 'peter.liu@continental-corporation.com', 10, 1567132802, 1567132857, 'UG11rE_2q8Wnp84QbzmFpIepDlEqd3qv_1567132802', NULL, NULL, NULL, NULL, NULL),
(67, 'ljhwh2011', '-k9J27yu0pxI4i06OHONVxLx3CElK-Mb', '$2y$13$VL7g0SWoXIPPX9Ngu4G0ION25myUrCW5R9lyo7q6xbKGPD.SbmoFq', NULL, 'ljhwh2011@foxmail.com', 10, 1567137430, 1567137445, 'c7O7yFt2zMQb0j4Z4wvm41DoGKwa7nqu_1567137430', NULL, NULL, NULL, NULL, NULL),
(68, 'zzeme', 'i-T0w2oVoVKPwZEcKpulheMBxh_mGqJt', '$2y$13$Yn8XlLpW.Xqnx26oqAH/6OQvwD7Gpdfktol7zWos7Ai/xHQPT8pc6', NULL, 'zzeme@126.com', 10, 1567152357, 1567152384, 'wN1tSgDidadoiHNYoraHVeA--KTgYvpG_1567152357', NULL, NULL, NULL, NULL, NULL),
(69, 'Alexwang', 'TcwzzGrBCybb8JnZE2VitarKbzcDydGP', '$2y$13$Iq4AbQVQdqKsNaFab21NV.aqR9gHqyeVMJepouBHLBEOWsSTqAAnO', NULL, '709555141@qq.com', 10, 1567164183, 1567164203, 'YTGgN3XnxbzZGd68qu6bBCisOrdIkFzV_1567164183', NULL, NULL, NULL, NULL, NULL),
(70, 'JoeJia', 'WWaEyDAif-Lnl6DuDTJ4YkKohq-ZgSpy', '$2y$13$qP6J9VLV769ubq5etdY77eCdbXeBUspUwLdjJ2.BON1Wnq.a8grky', NULL, 'Joejia1207@gmail.com', 9, 1567176665, 1567176665, 'TStzilTsCE8tzk0pz-AGHC1rkelJbBbX_1567176665', NULL, NULL, NULL, NULL, NULL),
(71, 'zhou75493736', 'I4g10yrXous7eWEEEu7X3-quqvbpoxRT', '$2y$13$rJPmDc4peRJSVyn1f/w3OOw.1qgbcRaCtY6ydUNlEMuhd.MosFy8i', NULL, '75493736@qq.com', 10, 1567205296, 1567205314, 'cH6bD0PF89BFiQ35m9FlXPtzuc9WPUX9_1567205296', NULL, NULL, NULL, NULL, NULL),
(72, 'ybh', 'XuHFia7LXELdz5h0VfhWI88wM-d-yyuo', '$2y$13$d5f//ZIFbe8cFlG8WaQlv.m1Mk0bnoYPulKoj76gp36341ocNj.Jy', NULL, '534401928@qq.com', 10, 1567216085, 1567216171, 'rJv4eIbkjNniiqNP4DQp6sv0apE-_A0M_1567216085', NULL, NULL, NULL, NULL, NULL),
(73, 'JoeJia1207', 'YTvcTDUJVBynyuZq-6IzBVYQNz92fr_5', '$2y$13$EnCzD0LREFohkqmZES/fceI.Dd7eL4hnlGk9CUEH4cPrLSdTyOtWC', NULL, 'joe_jiasifei@qq.com', 10, 1567219683, 1567219728, '8nob3VTt1P7fkiqNWvF7PjFWfVleheuH_1567219683', NULL, NULL, NULL, NULL, NULL),
(74, 'as4640', 'cH3_20rMOtmDBWZC_FKO-W3ga-pqkuq4', '$2y$13$3UCx3Zi7GQGV8Z7sw.VvF.eyYoxjuebD3sLYJkcrVMbwB8XOO6Yr2', NULL, 'yysama@foxmail.com', 10, 1567221249, 1567221360, 'jKFHmZlgQPxa65Ur7fByRgoamrg8Z1xw_1567221249', NULL, NULL, NULL, NULL, NULL),
(75, '1943828673@qq.com', 'VYLXjRdZEi32xyuNxoIBAflL-EPfgERt', '$2y$13$vaDdkXCosp5M6sJX6pi04.nxWqubqheok3MsaXT8jMV23r8vKWPjC', NULL, '1943828673@qq.com', 10, 1567235659, 1567235695, 'rqV4sFkpZK22_9e5XmMGChpGW3Y-9IVP_1567235659', NULL, NULL, NULL, NULL, NULL),
(76, 'wVcZrnRdCpPh', 'TQwNTitT4PR-FkcJcPmbXQjMEMboZEtB', '$2y$13$HGbkhRT/75lCM40u8o5gNuitfk4d0efzVKugrkDS9TgHEAvQzVJJa', NULL, 'michaelhicks10556@gmail.com', 9, 1567271419, 1567271419, '6bpQZhme38UxtHc_CgaX3xzPsenlz9fN_1567271419', NULL, NULL, NULL, NULL, NULL),
(77, 'zxzhuty', 'Yqbo-vWgvHlEMWktRgPRH6UOyk9RJq3v', '$2y$13$iRlFT5wzlAkkeOZje3Zw2.iF64jQ/X5Qas6t74carim56XPMUYNr6', NULL, '706415203@qq.com', 10, 1567294813, 1567294831, 'okvbCV-62hlG0xeKhaWi90UtEa1Fz2Ch_1567294813', NULL, NULL, NULL, NULL, NULL),
(78, 'hotpoor', 'SUKTO1MPo1-QbY8piCiGHFfgOpiEaSlr', '$2y$13$GLWQvISXPWE9HX9/owlf.ef2wEDL4IQz0Gu1GrSi.yPME4wpTj7xm', NULL, '1020517891@qq.com', 10, 1567305627, 1567305661, 'aYHS5tollR9sOWeY6cv6UGpESjNEobOk_1567305627', NULL, NULL, NULL, NULL, NULL),
(79, 'ficre', '5s4vpAOWdoB83X0FR12b4vMSZYIiofD4', '$2y$13$57XvktGEd8s0Ly1aBIeoeO3K.lhhLXwGot8aoZNIl/Me8EyBpdJ9G', NULL, 'ficre@qq.com', 10, 1567385437, 1567385540, 'HsgnTnrQ4gDt2pf0rBSSrIqFU_sK4mDF_1567385437', NULL, NULL, NULL, NULL, NULL),
(80, 'kurama', '3TfPy2rToFEm2EshWu_hdZ032CDVjmB4', '$2y$13$Tqo0W8vpXkDuDaQLC5bcVuWWW6NAnXlaFwOAok51UZ4rArbK/RcDK', NULL, '3035179@163.com', 10, 1567489450, 1567489475, 'w7n5xu6sCr8j-7utMnYt0YWiIT4ihKKA_1567489450', NULL, NULL, NULL, NULL, NULL),
(81, 'Happy', '0EM68UjyIoLq0qN7LVNPNWoqtApDOxCB', '$2y$13$RD/9yu0aRURsP7qgYW/hb./XSypszB370I5.xTUqiG9bORHbTffPm', NULL, '68680882@qq.com', 10, 1567492669, 1567492709, 'P82diY_bGzIsFb5_V0xosgNxvZjXh6KK_1567492669', NULL, NULL, NULL, NULL, NULL),
(82, '琅琊', '3xCRcWBRASd8qziFD6vcZlEGwbeZfWAn', '$2y$13$adP.Hfn1S45dRtKlrC4MgOOrnRXNtPp4FdrDhTws1ygzpihrqAdVe', NULL, '1223075960@qq.com', 10, 1567664172, 1567664211, '0G1xkovBPgrtVVm8Dk9ewEHA8bJ0qK0v_1567664172', NULL, NULL, NULL, NULL, NULL),
(83, 'Yanjiao_Ao', 'f0ePNeH1Ry0gleXkjuVZCWq2o21Y2v9N', '$2y$13$9AssUK68rD5pndrco.zQp..BPcrnK0Jm95/5bucEAy5rwoMJqxnP2', NULL, 'aoyanjiao@gmail.com', 10, 1567705571, 1567705929, 'ATn6swEgzNNjQepiSAEbOCsec4OMXkwP_1567705571', NULL, NULL, NULL, NULL, NULL),
(84, 'wanghong', 'vOciArGXrSIJkTRYqg8Eeuq_Fge4-GJB', '$2y$13$mlTwckxKWtMwg5KnvRkd2ODgHANPiLOHVAdGfpnhqqvFcLvtfX6Z.', NULL, '546217385@qq.com', 10, 1567733983, 1567734113, 'uKgEJGnFq8JVSmONJEgo9yR0AVVSd2MA_1567733983', NULL, NULL, NULL, NULL, NULL),
(85, 'li', 'PUhzGR9Mkg9UDlPsfTjwXFwJdpw-eXEF', '$2y$13$VBwfrA3k.dcvWS.yOE3Om.MqwqbO18WGPF9o/f0wZc57c2ngs50zy', NULL, '102514345@qq.com', 9, 1567734149, 1567734149, 'K4fm33RV1sYA6IPSWfHJE50lhdaVnVRd_1567734149', NULL, NULL, NULL, NULL, NULL),
(86, 'li lan', 'unEGaG9uNF_SnEan4shnN04-6wnVqrZ-', '$2y$13$LKhpp8CmnNIWFEfgRFpFS.YTh010eb0R0MbwEEb1j8lAcYJ6x8Uwu', NULL, '1025714345@qq.com', 10, 1567734321, 1567734336, '34SVSp75FwlbrVwtYIeZvsT1k-z4zyKb_1567734321', NULL, NULL, NULL, NULL, NULL),
(87, '18818262405', 'e56M8PjUiWqrFmReWvRlPvl86CdXTTRF', '$2y$13$i4nJID57CZQVgelbh3AVvuBwYTli9odFxCCrUIgukFvS1pgq3n5kG', NULL, '18818262405@139.COM', 9, 1567789618, 1567789618, 'QAiwbxN2QV1ufrxDN85OrZHRK7-rACOt_1567789618', NULL, NULL, NULL, NULL, NULL),
(88, '188182624051', 'P8Shs3n3nvcFoq4Tyv3eextcoAmvdI70', '$2y$13$0aMGgh.yKPov2RzBu/9QlOd/5aY6zz.BCIuC/juSd268ytN5ioAJO', NULL, '287894549@163.com', 10, 1567789715, 1567789761, 'Vm2uBgOTcOiX7ktBncV2vE7c-Pxj0hcz_1567789715', NULL, NULL, NULL, NULL, NULL),
(89, '王小盛', '3Eylj-cdwjfxW9w35Feq4vl0kJMD2OA7', '$2y$13$QdK6yOha1dopLLKGqglz..e5UbWw55LyYLENQlgO5hCyDV13yPdKK', NULL, '609917083@qq.com', 10, 1567871488, 1567871511, '4P6xaAicEspYysMvJGqI_p84MbwZ0emz_1567871488', NULL, NULL, NULL, NULL, NULL),
(90, 'JChao', '2JGb8pByiFBy9hhvptWGHMUHlKCW0v1b', '$2y$13$xQgVN7ZXhGGJozTK3vLJouGcKOAFNvADMO2.mRQN7ev2Uuh.6xxJC', NULL, '414324640@qq.com', 10, 1568123234, 1568123246, 'cMP8RjYo5VOCAfuigm8wIzfi4DSrBS-B_1568123234', NULL, NULL, NULL, NULL, NULL),
(91, '三千里', 'YyErCePnI2_qmr3VxJgfhwqoiEvnZF0y', '$2y$13$tEVElgyaeCOF5vlBitZF8O4Zi2fGGofs52EsdjqRQdGhl2fwJZ0NG', NULL, '497825478@qq.com', 10, 1568629758, 1568629781, 'vRlWo58YC4xiVXO1F3tOd_IRA1Qwz1Gk_1568629758', NULL, NULL, NULL, NULL, NULL),
(92, '梁传圣', 'Nmootif7jOkd2IC2Hh447py164ObmtKV', '$2y$13$V2D3kA9NH7.grfSP9g7vEuUc1IRaI9l.kV0JGlhCHA7FxFGRWtzu.', NULL, '635594293@qq.com', 10, 1568645673, 1568645700, 'dqt9Jr0abRMmvd2A7QB4oRga8iDy3hw9_1568645673', NULL, NULL, NULL, NULL, NULL),
(93, 'zyw2159', 'O1A37bt5LObdT34RzfRJOPqlq5XSJ0rV', '$2y$13$qsuIXutrldIA..Yt8G6Bbe69jDaA7pNzfz16d3jneARN/lX6xcbGi', NULL, 'zyw2159@foxmail.com', 10, 1568685185, 1568685209, 'du-u_Xi1tW851JTGoEPsfD9hRuMF5t_3_1568685185', NULL, NULL, NULL, NULL, NULL),
(94, 'ayaqhw', 'P2f_L98ac402IGLEk1yQjjgJf5dQ_Q-W', '$2y$13$bcGxJ8pOri9H8deATdiApuO1t.h1YXhHDftj3jsy0xBd/CHwTSOKS', NULL, '53903165@qq.com', 10, 1568691317, 1568691402, 'o_g69Iyhs6XPwrPWpmAipMkwyeyQNVRz_1568691317', NULL, NULL, NULL, NULL, NULL),
(95, 'Leon', 'XKL5XAXNniCKou9mY8-PTRhBcc0GuRVQ', '$2y$13$P.Fq1TPr4zYycTbfYdQlDeBWppihUREv0stIcIaS3iZkGbrsPLB4C', NULL, '1145163943@qq.com', 10, 1568696681, 1568696700, '4Q2A98Vl_WI2JxYDmmiM57uMEzC45kGh_1568696681', NULL, NULL, NULL, NULL, NULL),
(96, 'onepice', 'pKNqaA9kQ3iG9Z4Xc0-1i9un5lxkFV4O', '$2y$13$q0N01ZVGDF89c7PSFUC2Ae1ofgnpae.IeBI8OK87jX1PJQ.zqxr4O', NULL, 'stu_xyx@163.com', 10, 1568697773, 1568697918, 'br9gfUR2IyCkcMCxFwyRzF6kLamkm0nF_1568697773', NULL, NULL, NULL, NULL, NULL),
(97, 'juzishuya', 'f-5vcuMaIByu3ZS6Zwp5XtVxuJboCR-X', '$2y$13$1j5ONKOAeysEqqQQDDk7e.XTGfnsDsTEQgrMiWq04LAKJ2Is9GNOa', NULL, 'tanlizhenxue@outloom.com', 9, 1568720563, 1568720563, 'TQqoDS33xQsXFtLe20FQr_g6_ViLFd_b_1568720563', NULL, NULL, NULL, NULL, NULL),
(98, 'xingwenkai', 'eyR4AGUyMxbDd6EvAjMpFunxE_2CeGGr', '$2y$13$Dcz4GIkPM8cjs/nh3kf19u1q9n3b1v/bNq9t5EGa1MoCo.ftIbKP2', NULL, '1498505269@qq.com', 10, 1568777275, 1568777290, 'ValzV-C9i5hJb8o4zPT75kr__sqJWBkA_1568777275', NULL, NULL, NULL, NULL, NULL),
(99, 'zhang844', 'nQ5ic6Nf4heOzriA6yqFg_LbW0dV3iyG', '$2y$13$aQD6SqtDPBuWcXVQmL3dEeaLsNVq357uEp8ZqOrP.yvttnLRQU0c6', NULL, 'zhang8404@163.com', 10, 1568794357, 1568794451, 'L4AE-LHttqwpGwz3jdTnrRHNZE1eQjwE_1568794357', NULL, NULL, NULL, NULL, NULL),
(100, 'miko', 'TuTeR52Io-2cNbxNs7CigxILhHraQtmE', '$2y$13$UzzK6dVB0OdL3zbANlgioOFeYuH2kj.POfDpWAJv5tfGCq9.QNev6', NULL, '290638079@qq.com', 10, 1568813314, 1568813347, 'i2yOTJENtdh6KDntvK5s794IpjMWbleQ_1568813314', NULL, NULL, NULL, NULL, NULL),
(101, 'chen4490', 'VsS9-0P9jVP-mNP17FnMZXuZOXbsnMcp', '$2y$13$itU9KXLd1DuF64AoZcr3ROaafxsEnowuDkXEfYCudtY9wApe0RnMC', NULL, '47256244@qq.com', 10, 1568813641, 1568813672, 'owtpbWTCbNVM7_L9dUgTL0LYUHmY4wrC_1568813641', NULL, NULL, NULL, NULL, NULL),
(102, 'TLT', 'Rk17_s0goRr7tsqPAl2Qeex91YvPnlzQ', '$2y$13$HF5WxruElzm3HkaaJze0y.zzenEXsNu2007tel9TW0I5hgf2VesAC', NULL, '1359130069@qq.com', 10, 1568877681, 1568877704, 'palVMKjDGonhh9e6bzADC65LbThaEOuk_1568877681', NULL, NULL, NULL, NULL, NULL),
(103, 'qq280948982', 'oJv_TKboq-yr4AVNOS_WD9FMkxeEptdU', '$2y$13$Ohrb3gt9QcJ0DmekLjRbceEEfaTkUe6vIJb/Op5w1zepMVPxsQUpK', NULL, '280948982@qq.com', 10, 1568941254, 1568941282, 'rzZG6S2E2Tb4Ke1yvntBwShVsmqfaT9z_1568941254', NULL, NULL, NULL, NULL, NULL),
(104, '王贯山', 'LwJmoLvyeQYgz0_AKUcSUio5S9nmduHS', '$2y$13$djEvNBWsOtxvm8arfIdi/ON.iahGAZrMo4gtUZuGsjkytB79GTyo.', NULL, 'wgs49256@163.com', 10, 1568949410, 1568949442, '9ttMXh3PojYLY35L7pWtyffVpP8y82bW_1568949410', NULL, NULL, NULL, NULL, NULL),
(105, 'downtime', 'LqoZGr4yeqF-QUhiHpPVSW2GCf_jMhS2', '$2y$13$S3r19ZQfN0nv5EsFryuHreLX7gN4Rda1w7J3estRm0SCLCesUn90i', NULL, 'yranbxqc@foxmail.com', 9, 1569046344, 1569046344, 'MKMfXhULn7-2S9VW8Oa7_O8VtC2AGNgJ_1569046344', NULL, NULL, NULL, NULL, NULL),
(106, 'yandc', 'e93HD12GxrtEPecbkBc7LCvfPEylOAjI', '$2y$13$C7.TwrZR14rappQHc.RXzu0JdXRY9072dIJK.TOuwV5E5JhR3PSJW', NULL, 'ydc_zxhy@163.com', 10, 1569329072, 1569329098, 'zMA_hjUQYfCYnLwNwBH_m6RGmjnpo7kE_1569329072', NULL, NULL, NULL, NULL, NULL),
(107, 'zjh1943', 'V09MFIeuC-cFbr77kjCDPm0kD0ht5zp4', '$2y$13$Rjz3p2WdvOm/8.2DT0OSbOCMscGOux5O99ZywKAYEcLOXGeI7fU5y', NULL, 'zjh1943@126.com', 10, 1569497116, 1569497134, 'z053nWMcX2bWCeeu4JHcAziLGEl8S23F_1569497116', NULL, NULL, NULL, NULL, NULL),
(108, 'YananZhang', 'iUsWuKELMgi2rU2aZ80oVDqRHrJggcPl', '$2y$13$wtTI4ul1nwuexypqY2E2puTHDbBJAUGILtCIAW7rd/C/7Q.6rXrMm', NULL, '596889912@qq.com', 10, 1569722451, 1576552702, 'WpLJ0m9k5tFK3480CIfMDqn8hR1-ErKY_1569722451', NULL, NULL, NULL, NULL, NULL),
(109, 'Youngki', '3E99AMfGRbwagwRIlf_7rVcek-swbmO9', '$2y$13$mJv/8j7bBx7DRUDJXLUXKOAmQ0Esb1NKjXmxNedlSRolj9oieqnZi', NULL, '1165812598@qq.com', 10, 1570446892, 1570446906, 'mMrX68ONqzPMXDp7mFvXLoh1xuemRYUZ_1570446892', NULL, NULL, NULL, NULL, NULL),
(110, 'fk7456', 'E4aMnrRptkMTORDbkyOS8fn8hdHpj4lP', '$2y$13$zF92LojEWqNt5ksW7vZu3ehH4uxSQzTnF8b9o/7krWJpnqcGniNvK', NULL, '913969491@qq.com', 10, 1570589928, 1574057029, 'jHPpFIsf9MR1tOnkAFT0pyw0qSbVq6YD_1570589928', NULL, NULL, NULL, NULL, NULL),
(111, 'user_key1008', 'LzK8RCRpYpMba2cOF7Xg45X7bJKq54QT', '$2y$13$JB8wiUHTfGv8WRT1SaukA.T225IWrTPoOv0bvw7wP4jvekbaKJP4m', NULL, '598874521@qq.com', 10, 1570590468, 1570590491, 'VGg1N_--zXUPI6A3NYkoKZlXb-maUcZG_1570590468', NULL, NULL, NULL, NULL, NULL),
(112, 'snNDhQfm', 'OAjunV6HLssrIKg4qKgr91loaTC05AHM', '$2y$13$eSmOkfNxdaZVs5/e.DyaOuMYrECsfLdVzr.rXqdgAENwjOBqIRW1a', NULL, 'primrosegreene2433@gmail.com', 9, 1570615886, 1570615886, 'rs2YzUIzq-lL0zl1tQ4iS_O5brxR-9dx_1570615886', NULL, NULL, NULL, NULL, NULL),
(113, 'zhangyp', '6IEI0LzNzyT8FoGOEXo6t6HJVB7Tqaud', '$2y$13$9yG0QgbHJcvo2IPoeJWNxuJWUcd03DR6PzAtKUktWNBxUefUk8mrm', NULL, 'zhangyp.dev@outlook.com', 10, 1571049235, 1571049395, 'dhH7LHzyd91rk0gfer473i8n2HwbgW3o_1571049235', NULL, NULL, NULL, NULL, NULL),
(114, 'Dorothy0328', 'Eq7xPQZo4t0XuavM8PBspolqAlDkYfcU', '$2y$13$hFR70i1ZJfHemU0JEnSZhOM4e9PuneJiXAXOqc2bQBapU8I9P0DgW', NULL, '1114099245@qq.com', 10, 1571189621, 1571191205, '2pRCvXqQNPXFFRQ7QGiXsDkZpzD6qIS0_1571189621', NULL, NULL, NULL, NULL, NULL),
(115, 'lanxinquan', '9niDBoyPAWOQuuJx5zlMcAdoEEjMmPe1', '$2y$13$Dab8jN/SlP/ffSH9zAfzAe/B4fE.J8V/nQ5g9rDSEEBV9DKyI83c6', NULL, '602241387@qq.com', 10, 1571367749, 1571367774, 'Akgq29Mo4KaBRlKKzJFTvEoJhLADc7z6_1571367749', NULL, NULL, NULL, NULL, NULL),
(116, 'liming', '6OGxUAIyd_HGvcDQyUGJmx33lK4YZC7f', '$2y$13$6spEaQs06zoYH7RVwRpV..wuPc.pGJaD/5TaIJJS7rGFErL4w7mKW', NULL, '215683330@qq.com', 10, 1571540184, 1571914778, 'HeV9ECxq2iH56PtLMOuQc1oMtPuqvGaQ_1571540184', NULL, NULL, NULL, NULL, NULL),
(117, 'Victorsong', 'YikiopgQ9dtEYWenxxRP62HaVA57zcer', '$2y$13$7cU9I4NoVr2wHW4Mt.O77uB8jyzuaAhevIOCd8S7NUfh7L1vzzu12', NULL, 'sgwion200@aliyun.com', 10, 1571593137, 1571593208, 'xBbnKMbQUQexvMMiAQ-LwxgyL1odQMsq_1571593137', NULL, NULL, NULL, NULL, NULL),
(118, 'zhouxu', '1plVm8aJNPUKgLwVyGcLs0RjGglf7OZO', '$2y$13$RHKPSW8j89Hq7SoK/W1d2OJxUarD.3qvgmq89z5WvtuuOpLEiDQ3K', NULL, '979071631@qq.com', 10, 1571619803, 1571619821, 'rkCZp-K3PrFXKBaRwiFmwWiHkUU0pAj3_1571619803', NULL, NULL, NULL, NULL, NULL),
(119, '45238368', 'u_fE6lsOfBw_wdwnUeCHnZCiE7lHIgK_', '$2y$13$Y3NRImmfhScabwtmbc0TDuvFkKSF9KHKLPNoaEZdhju2OlmcMsRpq', NULL, '45238368@qq.com', 10, 1571636396, 1571636418, 'do9FHhnsD82kIZ3LVIMXLlHWSY8V7RTN_1571636396', NULL, NULL, NULL, NULL, NULL),
(120, 'Student1', 'M88EgqjIBCttquL11TUpdYicctCSjHS_', '$2y$13$Xhq9wYwRlEHvxSk9pdKATO67Me5P29uBGc4kXSCGFQIcLe24xMY7a', NULL, 'student1@hgjz.onmicrosoft.com', 10, 1571789084, 1571789372, 'zQjoABPiIPKtY2Uyg6YttP1KXD2BHf8b_1571789084', NULL, NULL, NULL, NULL, NULL),
(121, '陈希章', 'ipLDDPmIFWcrG4E6vzIaGuNnVeBXRub5', '$2y$13$HjYSD6Lzo4MNyg2bDBPVs.bh98aPWFO75qB6eab4XBBacqeD7KT9e', NULL, 'ares@xizhang.com', 10, 1571793066, 1571793087, 'QIAzitjO4vhQ7wqN2Qg2PegohoMI0Zub_1571793066', NULL, NULL, NULL, NULL, NULL),
(122, 'ShawnX', 'oRYLt9LKCKLEPAgTxJIU3syKwg_yyhjb', '$2y$13$vkZAO4EN0kfppB23GLayFueKw8g0D1ooWq1/2jBH9XgOplmiK4b8e', NULL, 'shawn_xie@outlook.com', 10, 1572174703, 1572174744, '8BX_yJGpLBFX1dBYmRLmvDOdpXtg-gk3_1572174703', NULL, NULL, NULL, NULL, NULL),
(123, 'czh', 'c-XHYhs6gjNw_y3vz07DXMflEi9UWHuC', '$2y$13$4a.7Qfr8cpWYPZeMra/c5.b5PO2rJRrch5rDsdhCmjHrt18uo8AuK', NULL, 'cao_zheng123@163.com', 10, 1572232349, 1572232394, 'tY5gxEv-IaikeIb-o6jDxYltjmN9yESF_1572232349', NULL, NULL, NULL, NULL, NULL),
(124, 'zsw', 'diat5_PJvyUO-KWPjYowYdUrCkZ3n0ZD', '$2y$13$bwwN5IOm7Uol7weVT4kYH.A2hgIXOWulKqTrgUZ.NYzeH1XrHHGRu', NULL, 'zhaoshengwei1@163.com', 10, 1572428515, 1572491755, 'q1EdEmKClAAyFTxl_T3-ZSf-DyKKmCG0_1572428515', NULL, NULL, NULL, NULL, NULL),
(125, '王博智', 'v6jtnKS7Xv0ASt9cdKRMLQEgk_E7wBaL', '$2y$13$4jI2mC./i4jL8pamstIZhuIppJKlY/UBVxVYc/4.6ieVh/JduDtCK', NULL, 'wbz810@126.com', 10, 1572579148, 1572604931, 'rP3XU1WMQglhsWXarSlAs86aSsUGCCLn_1572579148', NULL, NULL, NULL, NULL, NULL),
(126, 'wbz810', 'qjD_bUFtNXk-q_UecBmrj8tmv2b31Bzb', '$2y$13$AHIUPohraYZNwTldXmoGDusOBcc8yH4NOCN2Rc8f6f1.cM4d2H1oW', NULL, 'wbz1994@gmail.com', 9, 1572605044, 1572605044, 'ghudxIgPCvC0TrRQVG0spV_fYcEkXKcG_1572605044', NULL, NULL, NULL, NULL, NULL),
(127, 'rootlives', 's4cUPBsvwAn1wyJbB0NRviYHjd7bq7tS', '$2y$13$U3qD1hXKR8HIbnuZGo7lcOEFGmGIuskP0hXGES61NzxE5bVtZoJi6', NULL, 'b@talk2.it', 10, 1573177211, 1573177223, 'cRO3gBdF2opvmFRf0NXE0gHALogvGePn_1573177211', NULL, NULL, NULL, NULL, NULL),
(128, 'Jue', 'i877IgStKqCJkXc8gY5kYN7LQ8NgAQxq', '$2y$13$.zFXNfTwqWTaDRrirJmZbezeg6ecOEAggQSiAsDHYYM75kOcmgq0C', NULL, '1796204041@qq.com', 10, 1573184129, 1573184146, 'SQnqZ-g6g6DHPOwiNXazLCT1_JJ2kfN4_1573184129', NULL, NULL, NULL, NULL, NULL),
(129, 'lqss80', 'QufOTu4MDiTtIcAbJsZiCbjqnm3KoVXM', '$2y$13$cqNMK5umuTcViMFJJ02FIeiLV.TY4KzI9TkADT0kZL5p9om4TYScu', NULL, 'lqss80@126.com', 10, 1573484644, 1573484733, '-V6w0JKswqSnOMJVeO1GDHnz2X4kNGGH_1573484644', NULL, NULL, NULL, NULL, NULL),
(130, 'viphehe', 'SbuMcp5TI94jaRkNQADeDwJMxzW4T8Q5', '$2y$13$pOqRZ/TmoGiukv/8yTiwq.0F2jpMtUrkBdqeMc3aX20XuUOz.t1Va', NULL, '615593023@qq.com', 10, 1573540937, 1573540967, '-N8BwAT66lE779JxZcrqxBGhaQBahirA_1573540937', NULL, NULL, NULL, NULL, NULL),
(131, 'layne', 'kvftFKIdC-YnGos0WYNG0Z__oh1JWIcj', '$2y$13$LiB8yVokAeXUgHCyMWD6PejhsNMbNAyv8kpTqAzSeu1rmJhHvr5vi', NULL, 'wqlbuaa@163.com', 10, 1573546359, 1573546536, '4u4XLeUB-vlpHjMw2p0EpQ0F2Uhf-ou8_1573546359', NULL, NULL, NULL, NULL, NULL),
(132, '1866170090', 'eFlyRYRoU08yVsb7G2-WkljPVD2yt2Ws', '$2y$13$raj17mGpjfCRKa.N3.lB7.1YwRI6XEKjctrnDD4A4QMwXSz9Hq14C', NULL, '315499696@qq.com', 10, 1573611087, 1573816958, 'yXTiDD8sqI413CFxI1AAQa94WuhTT9VG_1573611087', NULL, NULL, NULL, NULL, NULL),
(133, 'Axiosly', 'CtuM7OrMdjLQ7HbJGE7gbaMbRogzz2Gd', '$2y$13$5B13Q1Xp85MQ3CxFfkRvterxovx/dZwlGtsgsBX6FlDAraIRkMVme', NULL, 'Axiosly@gmail.com', 9, 1573713325, 1573713325, 'QfTEWc5qU4iO3DOvYLH2gar9fwgsqSw__1573713325', NULL, NULL, NULL, NULL, NULL),
(134, 'Axios', 'r-RB9EoCPceWWID-VbHUhc4Y6X9FZWsG', '$2y$13$U8rXgszZQtF0ztGgVNTI7OA//i9Lr1/k7wozRuhxy31XqgVwCw6w.', NULL, '872604947@qq.com', 10, 1573713591, 1573713605, 'CSs4usNjxY_7qwRHUZRWgypTw1ncTxme_1573713591', NULL, NULL, NULL, NULL, NULL),
(135, 'wszhhx', 'i2YhjxoP0wRm99E869PpMe-9dUjqEQYc', '$2y$13$.tz4GiNpcVDKfR3I6kT8GuqscYR7scttfr8HqITj5Bab35n8RvXYG', NULL, 'wszhhx@163.com', 10, 1573981290, 1573981435, 'nP6rGrZ6WqN-3AkZDl7qn04ielttB3CU_1573981290', NULL, NULL, NULL, NULL, NULL),
(136, 'Acher', 'dCaRnrpXhRmfOEr_3lIHdThE_w7xhIoU', '$2y$13$J1tQIJLBnj/hIEr47UGZf.dXXnUC/7nDGoLsAwJ.ky1VoH2OmO0..', NULL, '923040084@qq.com', 10, 1574043150, 1574043168, 'vsDdTdNfM-89Yz8rQ8JVGHXXE0aB5mI0_1574043150', NULL, NULL, NULL, NULL, NULL),
(137, '郭润桔', 'b44tiYyLJhSvsnYPLlB4c8TKgyr2Lc81', '$2y$13$cYvUN5AEyGFYp5ezVq1.Devt4rA3qQ1piqn9HvIsPimnJHio49.rG', NULL, 'guorunju@tjbhxqhgjz.partner.onmchina.cn', 9, 1574321238, 1574321238, 'cPqr7hEXDp65RvII0QaLSTIDU_AeVGOQ_1574321238', NULL, NULL, NULL, NULL, NULL),
(138, 'guorunju', 'aqw9OpNhxChwDZogmY9PmWBbSH-yKQkm', '$2y$13$wh9L.ciG4u5FyF11sYw3IuvldU3I8U5HH2dfO7pl9GYE0qA3IK.vK', NULL, 'guorunju@tjbhxqhgjz.partner.onmschina.cn', 10, 1574321602, 1574321629, '-66czWn0Ey0OwmwXs_zJHNx2h9VbHroM_1574321602', NULL, NULL, NULL, NULL, NULL),
(139, 'gaoxiang', '8k1nfwxw744ZqCjvpQ1-TniD_KcwINzi', '$2y$13$i57yHURuHSsCw9aGEHKjHOcQYakPjIqmPY74aDN58i28l.ZmayIBK', NULL, 'gaoxiang@tjbhxqhgjz.partner.onmschina.cn', 10, 1574321734, 1574652504, 'TZ7N3ZSbGSwxPFQ2MA5thH0IEZScZsQz_1574321734', NULL, NULL, NULL, NULL, NULL),
(140, 'zhangziyu', '8YNmumFxa5nPmvnSRdNV3EccNlZWDEkO', '$2y$13$q0jCnpBanlMTL0ytU1BMTO6NqndtZLpfJ5sWZrzbp0JgpHZ4V3Jwm', NULL, 'zhangziyu@tjbhxqhgjz.partner.onmchina.cn', 9, 1574321854, 1574321854, 'DJ8jYn1rnZTqL0BAg7yQ2XrWlBDbltir_1574321854', NULL, NULL, NULL, NULL, NULL),
(141, 'zhongxin', 'TVKVff5IviLk0R4CyZDT4D1PcuxFGNYq', '$2y$13$Dtt3365vKL/C0Ex4HZhoJOvU2Dik.myNuwt3XVLqXgJNhC62TaA..', NULL, 'zhongxin@tjbhxqhgjz.partner.onmchina.cn', 9, 1574322096, 1574322096, 'tlTab3XfWHLK5Rab6uQPCdhmqm9C1rQS_1574322096', NULL, NULL, NULL, NULL, NULL),
(142, 'liangenxuan', 'r1rVS8aVwOiDnyuu-leMAc_bxCv2JQ5N', '$2y$13$qPfZYMfVk./HLW.7Psg27.JgVYvYNul4FmWtW6XNY1fnraik7wF5i', NULL, 'liangenxuan@tjbhxqhgjz.partner.onmchina.cn', 9, 1574322153, 1574322153, 'RrzC2SAjh9ol05vCrswergBRAeKGigZd_1574322153', NULL, NULL, NULL, NULL, NULL),
(143, 'wenhua', 'tfn_H9oPxpAZBpBeypS1QUh-P9nKkO91', '$2y$13$M4evzEZxTjNhds2YcsgKSOTNgwS.g33CYD6XKRSRT9uHR.O/d04Ba', NULL, 'wenhuadraem@163.com', 9, 1574342274, 1574342274, 'GuwWKyYLFZ86WjqslDybRdS_ZLuyVs9a_1574342274', NULL, NULL, NULL, NULL, NULL),
(144, 'dragon', 'B3x3KjxYziUupojyAUUAnfAMMReA2SBL', '$2y$13$/C9gGPd4PezT7NinbFI0MOjIPmXdbYmhwaGBbrMZNVSbDRVbs0VWS', NULL, 'wenhuadream@163.com', 10, 1574342581, 1574342593, 'c1tMMSBd0BtBDdHQdUw8qa0A5_ibLAmx_1574342581', NULL, NULL, NULL, NULL, NULL),
(145, 'songjiewen', 'wSobdXZq5ZLnVCHFnx5K1HeX444A46KM', '$2y$13$Aw.xbdZvwikoAsos6t1s7umjZTEskli34SZriGj1RcvnA2UmOWKzW', NULL, 'songjiewen@tjbhxqhgjz.partner.onmschina.cn', 10, 1574383789, 1574665574, 'W7x5fcSD0ZLptuhDy8c744aW-PWVez7V_1574383789', NULL, NULL, NULL, NULL, NULL),
(146, 'daijiacheng', 'NShACs8odrWu_ThJ6dxctoohgopS1KVK', '$2y$13$X7dG0LZaerdC80jWINinxOdoMHbxqywoCPrG3S4OQBFayaiUEseYG', NULL, 'daijiacheng@tjbhxqhgjz.partner.onmschina.cn', 10, 1574384120, 1574665568, '16CwlrWg4fAV9iajZRCYM6S4YRMXmCig_1574384120', NULL, NULL, NULL, NULL, NULL),
(147, 'yangruixin', 'bjIFM6cn9J9LN7eMwg5AybVs-WiR6qID', '$2y$13$HzjAfEIA7WV7QoAoq73bhugc17GT6LvaMNOa8Kwa9k0IXCDBkloMa', NULL, 'yangruixin@tjbhxqhgjz.partner.onmschina.cn', 9, 1574384258, 1574384258, 'lNQlFGV2pHeLBi1UGmRSg93zEmGyn_9q_1574384258', NULL, NULL, NULL, NULL, NULL),
(148, 'andongjie', 'I6B1qG_jk-KRy-sBCtMtHzZmHhXYN8ac', '$2y$13$l2vYRoDkTVnicjRN0QmVoexc7f96wPX1FHMv1wTyUefTxxASZtEWC', NULL, 'andongjie@tjbhxqhgjz.partner.onmschina.cn', 9, 1574384372, 1574384372, 'WQnOUd8axv35FsBx5Jc-xZK7mn3Kn1ep_1574384372', NULL, NULL, NULL, NULL, NULL),
(149, 'liuziyang', 'hT1QFWeUPo4Nvc6eT7OevdsE6yFfPQ8D', '$2y$13$.MJW6HHYkNKd6rwZFWDABuXOg3v8NQ0BZGl4dyFT1QsQi7hIfiso.', NULL, 'liuziyang@tjbhxqhgjz.partner.onmschina.cn', 10, 1574413738, 1574665910, '2T2fP4t4vZO9btVu6OELAMRjmNl2Q6f0_1574413738', NULL, NULL, NULL, NULL, NULL),
(150, 'lijunkuan', 'h3oSbgflh5ziroVuPWEmgpfl5VpAKSMg', '$2y$13$sxKax0EH1a5UM.5t8/AlEeKuHRCgMinaPr/q/OEIMUdpXc8tQBV0q', NULL, 'lijunkuan@tjbhxqhgjz.partner.onmschina.cn', 10, 1574413936, 1574665941, 'CB9dNFIBJ8in-jIi4fZ2y4LbuC3w1-wI_1574413936', NULL, NULL, NULL, NULL, NULL),
(151, 'zhangshengyang', 'ugzXGC_147eHExPECtdszs5NA5MdXuK-', '$2y$13$UYvUgELNAfYqSoUi0ennhOnaONKCG2mjz2y8yUIx3IrOd.VIllgni', NULL, 'zhangshengyang@tjbhxqhgjz.partner.onmschina.cn', 9, 1574414312, 1574414312, 'Rg2Vzj8Lgf4cuVCHxiPANqGPFkZmuWzD_1574414312', NULL, NULL, NULL, NULL, NULL),
(152, 'yinshaojun', 'ZzKeXS9qFE66ld49cAUieBBd9amZN4ad', '$2y$13$CEjE7i3LTmKeY/vG2rm6jOaxzlAjmU4ScGs/rJO4ril2O/NEHmfhi', NULL, 'yinshaojun@tjbhxqhgjz.partner.onmschina.cn', 9, 1574414412, 1574414412, 'b-GKf0QFiyNCgZ7KRXnzWuzosS73RiKa_1574414412', NULL, NULL, NULL, NULL, NULL),
(153, 'wanglixue', 'xFE_oJ6rjnKtwftLl7NqqLnTRkspy18H', '$2y$13$dKGZHV7rp9CDlFABcGbxPOk.JHid6Uq.kKibTaSH4L1cwRyVDDEHu', NULL, 'wanglixue@tjbhxqhgjz.partner.onmschina.cn', 9, 1574414700, 1574414700, 'xGBacxjMAjzu9TP9UV7aYfO46XG8Ih5i_1574414700', NULL, NULL, NULL, NULL, NULL),
(154, 'zhangzeqiang', 'QevguFgnRcb6NBxjs7S_OIBgp50TuVhl', '$2y$13$0JDNj4Aa0aFZVftZSXNcxe9oQtNVOX.ipv/h6SSVdOg3bAn9eyW3C', NULL, 'zhangzeqiang@tjbhxqhgjz.partner.onmschina.cn', 9, 1574414841, 1574414841, 'h-1YkiLhtk43pHm3Rjf94LSwuPeD2iDJ_1574414841', NULL, NULL, NULL, NULL, NULL),
(155, 'zhaodongxu', '2glmpRhNUosDY2fnAo673e2d5KwtkOsT', '$2y$13$HclFYy5dlLV3.haHBF.n3OsRDDuF0RL5EJXlKhauoHQphY2ENmEvu', NULL, 'zhaodongxu@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650417, 1574650417, 'Sp646Rj84yQCxtL75Z-wsV84Ur4_RDRu_1574650417', NULL, NULL, NULL, NULL, NULL),
(156, 'zhaochengjun', 'tfFAPdU3CijpmopfNVMO4YqGxO_EGCDO', '$2y$13$09rwflohIgoP/k06W5Q07OSyj//XstIeojujSsGqWCHg4J4wH56ni', NULL, 'zhaochengjun@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650565, 1574650565, 'tIDeQZDEmjt77linZXlbW5kpceCAKQXb_1574650565', NULL, NULL, NULL, NULL, NULL),
(157, 'chenxinle', 'RCY57qMxcqGAKrpPLRiSJBRR12Jt0gJx', '$2y$13$FhH.KwUaCiCAQXqyy8uOb.iWh7oplFcrrP5HwhajcWnxpj9eGsGJe', NULL, 'chenxinle@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650633, 1574650633, 'nw62-INTeLpZvODEsKITjhml_nYUKIYU_1574650633', NULL, NULL, NULL, NULL, NULL),
(158, 'zhanglizhao', 'caGucAb2e5jpqOZxBvMuSZxGNwZRhCnY', '$2y$13$4E0cpJUBjbyj3sxr.vKbWu0SxDMM2e1zgeQ.mZh7O08RG4TOuaor6', NULL, 'zhanglizhao@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650718, 1574650718, 'jdFXFErojuEiu1rLB_kgToUFrICHdacM_1574650718', NULL, NULL, NULL, NULL, NULL),
(159, 'wangyuezhe', 'ngt-hnieZv0YdSzMGBZnqUcIy0StNooG', '$2y$13$8wXEiOUgF0iBNjpuZiKFy.T2N6.fiiU6J80VnoOV5I1A8U5OSodaW', NULL, 'wangyuezhe@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650767, 1574650767, '27dMcNaGxEG9dsrGmEUdEaTMgeO35fLf_1574650767', NULL, NULL, NULL, NULL, NULL),
(160, 'zhangquanliang', 'llPyXglll3nfifUIoX5RHEYFpeAL6_uI', '$2y$13$2u77xJeI/b5PtxZM9teoAeyXrKCApkgcvwROCv67wYyAruE/1465S', NULL, 'zhangquanliang@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650829, 1574650829, 'k_cGrR0k-1f-1_JSfwd4apQ8zZSlrrby_1574650829', NULL, NULL, NULL, NULL, NULL),
(161, 'liuzecheng', 'npnw3Lnzro194rP4BIP8w3e5P126M8uH', '$2y$13$I4lHaIxlP4/uFwXBGfQR4elBXz.nBoCH7I1Si9S9YLEOg8vMCCfJ.', NULL, 'liuzecheng@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650887, 1574650887, 'uwBmFN9s43wAEh4q1QivKTulWrVdSUYS_1574650887', NULL, NULL, NULL, NULL, NULL),
(162, 'zhangxinxin', '6xcltylG8t0inesOxGYbjuQ2avzjOOQL', '$2y$13$sikxTS7KYKiMQ6PYDY1RmOn8HJqgPXPlpBCHLm/lrj8QDIVpkHCC6', NULL, 'zhangxinxin@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650940, 1574650940, 'I6Anf23rpmVaKpQA6nH6f_CGFbkOs8hL_1574650940', NULL, NULL, NULL, NULL, NULL),
(163, 'zhaoyuxuan', '_8UMjFjA3EJZboRhroUdtD1WTqGnmwJz', '$2y$13$ZlyLdJPF7i53uFM1x8n/quUkGCZ4hIxAxawfR.p7hv0b2zTWcAL9a', NULL, 'zhaoyuxuan@tjbhxqhgjz.partner.onmchina.cn', 9, 1574650997, 1574650997, 'KcbHSIb3zhVbgfB_fYMSE4egPu-DQTwq_1574650997', NULL, NULL, NULL, NULL, NULL),
(164, 'jiaoshuangyu', '-CCLjzzUKZ_AmdE-4PIo_kIhtgwwWo9v', '$2y$13$WZ5u8MVmCxUts/NMtRXxheZu.RmaKM4zcZNekNCZK0nRCjVXoJ1.e', NULL, 'jiaoshuangyu@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651072, 1574651072, 'AUO3xq-IL59a41M2Xp_LerEmmoIO8lla_1574651072', NULL, NULL, NULL, NULL, NULL),
(165, 'fanxiuwen', 'hUxJwzrvPDhqlW4ghJr8hbLKFNMubWLw', '$2y$13$l5ITcW6o2o/cGUor9bQ9LuJ2VcmZQ36i0gz0.W0aISw5vCtlQHSZy', NULL, 'fanxiuwen@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651124, 1574651124, 'xXQOfEaajn0Cex2cwALveQGFA71EU8ft_1574651124', NULL, NULL, NULL, NULL, NULL),
(166, 'cuijiaxuan', 'ycRnkkNr4_59f_MzykjZY5-n7t1Y9wPx', '$2y$13$N056YKNeBuXO67gaKYxnIu2JganQ9s6i7uYIeSWf0Fc6KN.a.BDAq', NULL, 'cuijiaxuan@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651177, 1574651177, 'rkNon84-v_sPUzLxidZhhY1_baidfLjV_1574651177', NULL, NULL, NULL, NULL, NULL),
(167, 'maying', 'TuA9KpB56IALh-Ql_AdLs9gPthiOf8VJ', '$2y$13$Wj85xnhpewXQnuqBoKekbevTHtR94uzrcvzE.i234RUHl3KgTTvo6', NULL, 'maying@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651351, 1574651351, 'EGbUgR-Z8n51ifOyujyh6Xs8dCQ46G1z_1574651351', NULL, NULL, NULL, NULL, NULL),
(168, 'chensijin', 'oP-8OgrtZQ333_4K2pnloLwSnOcc-Hvb', '$2y$13$M/ZfMo/uleSDpdAeI81i6efqZ84QXYeBKnTvEeoO3NKb2au2npqa.', NULL, 'chensijin@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651398, 1574651398, 'f94NjNf9NL_PhzAU7BznEICfRULJKSUQ_1574651398', NULL, NULL, NULL, NULL, NULL),
(169, 'panyingying', 'pF6w0qdYHfDMiiXuCZH68JBtBcMtMNAn', '$2y$13$KEHqhqVnHcHQyI/BSRqRkONpDizO6nBMFHqeC0s3tEoJ35QWamaP.', NULL, 'panyingying@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651468, 1574651468, 'RtRwwzj3vpZ7NW8e0hE9LulU7PwB56NF_1574651468', NULL, NULL, NULL, NULL, NULL),
(170, 'zhangsiqi', 'IAuzK5LsC6CMOew7i3Y-LhVjs2Bv3iO9', '$2y$13$4MtU24r92buoPtsqvqR4XOLvvrFltc/7msKyN.0hCr0RTBOBmsvO2', NULL, 'zhangsiqi@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651521, 1574651521, 'XpGzwz8C7QHt5uAN3KhhsbDuobbLkFbw_1574651521', NULL, NULL, NULL, NULL, NULL),
(171, 'zhangshuhan', 'B6R-VYS1M8yEz5x8D_mDXveeV4o0HTXV', '$2y$13$W.frWnJsxwdYhykfT.0YreI/DZl5hIPQV41QlbpHIPMVyzHXUDEDK', NULL, 'zhangshuhan@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651568, 1574651568, 'k1cTqpc7e-IhrtAF0ccv6vjP9Fxos6QW_1574651568', NULL, NULL, NULL, NULL, NULL),
(172, 'aozhangwenya', 'fVhh24y6-lNM5_eHRPLbNpUe6L_ilgnF', '$2y$13$ogZEU9Qj4wiy4n5igHTRq.kCqmMbqZMTNRc8LL8Q.t5zsZz4jDmKK', NULL, 'aozhangwenya@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651620, 1574651620, 'V4I73ODoENkRwL07VGsq5zxmln0ZzBf7_1574651620', NULL, NULL, NULL, NULL, NULL),
(173, 'yangxiaorui', '_f3x0UoDhJME5-1jPVv8DUGHQzJbGVgu', '$2y$13$dThXBMp.iwjYyWXm44N39enBbMRG/5KQeVWwIymiAsAQHsDfq1eiu', NULL, 'yangxiaorui@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651667, 1574651667, 'Pm448hduArUnV9VCX0C9bjSldrI7Vr6k_1574651667', NULL, NULL, NULL, NULL, NULL),
(174, 'chenwei', 'HvldBBAUa0vvVn3-RMJSAtOXsr_u97iP', '$2y$13$YWmBE3GwXcqEnJLwyqUrfePsSYrgaojQ9JwkwgNRZSZbJApvRxKFG', NULL, 'chenwei@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651720, 1574651720, 'gC_-2xZ5r-5JCEPh0a7-kyhI9IjsS0EK_1574651720', NULL, NULL, NULL, NULL, NULL),
(175, 'wangzhilin', 'om5FDxOExlbM317YRAc1IVIVyXmIaIUo', '$2y$13$TFJaGI4KimgiV/WfRzP99ONKBre5./RLANSlv6h.DBjIsgku2EogO', NULL, 'wangzhilin@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651774, 1574651774, 'i_ekUIKeXbiERe8qzj7ODGVpgwZHr1Mk_1574651774', NULL, NULL, NULL, NULL, NULL),
(176, 'shidingyue', 'pqek9lRcC_L4fClq08E7h1CFtTqqGwL4', '$2y$13$60JUQh6VkMabFfUOSr2oXeSsJCIJnqg2GYX8.t1gpJ9hsaXMEkwua', NULL, 'shidingyue@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651824, 1574651824, 'T2ZyXiHy2wNDrWe9EvtKlFnKReQtQ9rv_1574651824', NULL, NULL, NULL, NULL, NULL),
(177, 'liusitong', 'm1xGwfjwPrWlnEo18wI3L20ERiDT5NvY', '$2y$13$YcbWXHjry51iGTG9hJ7bBu8HjNK9quyHkFandCIkjut11fbN3XINu', NULL, 'liusitong@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651897, 1574651897, 'VkUlmlxV1lg_OzefChWhzZuSdYu5c-zV_1574651897', NULL, NULL, NULL, NULL, NULL),
(178, 'xuxiuzhi', 'gk7N_dOoIPtV-6VK2DUgewQfrPGHkKDR', '$2y$13$N5k21f.Q0sdw0PzeN/SXQuCGOGJ/5ZIYfNu3GzsP2tvokkAlZix2i', NULL, 'xuxiuzhi@tjbhxqhgjz.partner.onmchina.cn', 9, 1574651962, 1574651962, '4Oat2y--QKrQ9Mi9aC2dNjTeOHwph-2R_1574651962', NULL, NULL, NULL, NULL, NULL),
(179, 'wangxinyibo', 'HONjeh_RkQYpY7LZ_SB-j2gz829dgAvt', '$2y$13$kaKTqbNtAk.3kqijVl0ff.697GgLOigQowzwkqc9y6lu5/1Puamku', NULL, 'wangxinyibo@tjbhxqhgjz.partner.onmchina.cn', 9, 1574652023, 1574652023, '6eV3NAZY48NtvLOYZILba__xZX5unOpB_1574652023', NULL, NULL, NULL, NULL, NULL),
(180, 'WKY', 'eZiKGXSUL9aN0CMiJoxsBKJ6lhTyRuN5', '$2y$13$W1wZx.S28YnsvzC7jTyT1OAJGHiYub1r.tocDSCJaBFBBRaX0mnGC', NULL, '981872041@qq.com', 10, 1574652273, 1574652308, 'Qu85uX7X3mzjsKZzXz3DbrdhKnVjHFSR_1574652273', NULL, NULL, NULL, NULL, NULL),
(181, 'xunima', '1Ur5ZUxnlwP_PGy5V-uCwr7ZJGXwTAyk', '$2y$13$kbv.sGDoIMRpKajY5f6AzuHlLBiU6208n2a7mBxSxh.9ukYxiFfM.', NULL, '1515785418@qq.com', 10, 1574652427, 1574652450, '2sacwAvQ3OD-RxZfEAr2RfUeY7CFvunB_1574652427', NULL, NULL, NULL, NULL, NULL),
(182, 'chichi', 'DRWKFsdf_9ygLO-QLx8dD4eNQNdwySWY', '$2y$13$KEQkRxwgDYApkudMndEkmurvLaNq5/rbmEUotJg0Uk648YWL9fMGm', 'ZUUHxjOBfY3dh4ogB2d0JxJdGPtC_C5F_1574731005', '347371789@qq.com', 10, 1574652476, 1574731005, 'CGN1vUGa13-swr-x22WLQWNyLwA0MTln_1574652476', NULL, NULL, NULL, NULL, NULL),
(183, 'kha527', 'J6us4qTp9jMZDx8y1iIFXVzvRxE-EpGo', '$2y$13$HQnZnDPTrFqVtltdm41jfuv7bvad54WuvoSet7nLoELlnLf20oW2a', NULL, 'kha527@naver.com', 10, 1574652597, 1574730790, 'xJmomFQvoU9vye5Ks7PRj09H39HGhHac_1574652597', NULL, NULL, NULL, NULL, NULL),
(184, '王琢', 'dybi9b6zWesQMMUf_TNbkxD7kY1KN5TQ', '$2y$13$IvFP8JZVqZAB90omVSp6OuiPYFZbnyIHz.Tlw700OwXaki1Y37HYK', NULL, '2297458523@qq.com', 10, 1574652604, 1574652627, 'uU5I3je33ifbz-qYPjUc-q7LzABLKvrm_1574652604', NULL, NULL, NULL, NULL, NULL),
(185, 'awsl', 'mvHj24Lj_TmJJGG9WXGFH-GY9Etj9oHQ', '$2y$13$aH3OZK1FCpohYf7zOvZq3OOEywaQkL3Vwz3mWHsCD65K5zj9a9tmi', NULL, '1940806010@qq.com', 10, 1574652648, 1574652691, '0NiDq0e7yz_7Wzbi0YqL-STIffkXPyJ1_1574652648', NULL, NULL, NULL, NULL, NULL),
(186, 'Gshihao', '2-xvQuV0KYqO717U2HULeP9_JIXjDcst', '$2y$13$nTKNEikwPMWoFRahRVqu8uIfUok3frLcaw0T2Ro./XW6wBMx8rVi2', NULL, '494536082@qq.com', 10, 1574652707, 1574732181, 'SKAgNSttjbOZSoct165e70BUysH8Cdwt_1574652707', NULL, NULL, NULL, NULL, NULL),
(187, '郭润桔1', 'KlCwjkgp1jCwRN1wo82AZAgQlqrRsxYc', '$2y$13$2C7EYJeqZSc.BZoTXmPIgeM8DNeVYRuE5u721B0MbxC7AZr0I.DC.', NULL, '1324635270@qq.com', 9, 1574667032, 1574667032, 'd4KBV3doBa0tcGNzmetcSWldX8ddNQcX_1574667032', NULL, NULL, NULL, NULL, NULL),
(188, '杨威', '4o8lEHc2kkmqH6fSIkletFtwcz_-qZp7', '$2y$13$JpiSae430mVwvVBn3s5lIOVhQR12TQx36rts1poEjxpMW1B523dYa', NULL, 'yangweinet@126.com', 10, 1574667177, 1575185152, 'GceViBynBty7wh9v2370rBL6QV1xCcwY_1574667177', NULL, NULL, NULL, NULL, NULL),
(189, '水滴鱼盖浇饭', 'xrYyTMft1W0fUvckzpxAUyuP60xIUsAS', '$2y$13$J0Go69auEolytNLxCPGgSu9yejeOoRkKVppmqGhTbDTHZv95dzqJy', NULL, '316800046@qq.com', 10, 1574669913, 1574669962, 'ACxVwK9gUSPhPqQ89lHLKxLGGg8QW9o0_1574669913', NULL, NULL, NULL, NULL, NULL),
(190, '缴双宇', '53OF57ycVyXSCubAutYJUvTDQ3cGW8zb', '$2y$13$UGR7n4XGRwtc46bNGvh1UeL8ynQXNTRxydy0sSTcvrvw64Niq.gOW', NULL, '3310740039@qq.com', 10, 1574670335, 1574670498, 'ZpEOPPBlQBUblPNhiN10o9bUHEKhzGS2_1574670335', NULL, NULL, NULL, NULL, NULL),
(191, '陈薇', 'rb35lQhsGU_-rrtY_0uW1CaeUx1g3GnW', '$2y$13$KZQgu706n7LV.LK9rsYubeAUFU.1JYa9xq2Gm8xZHg3W02cPlxXmG', NULL, '2196978905@qq.com', 10, 1574670454, 1574670562, 'oTki5XKEd4ohYscJiOaj1JQr1v_PLvZb_1574670454', NULL, NULL, NULL, NULL, NULL),
(192, '刘子阳', '1fSqbPnZBpwGsbvtNTwPPogB2LR-FI-5', '$2y$13$jnku2Do0gfVwqz7JnKsl2.cU/1y07M8tTd.7ngORDZh1u/0raao3i', NULL, '2631432848@qq.com', 10, 1574670484, 1575335370, 'ELOpuulXipEJ3AXNfxHTMQGrbtaQ-EVn_1574670484', NULL, NULL, NULL, NULL, NULL),
(193, '高翔', 'Rzx1_8qSi2LqAkC1xM6UUHBPRxbYd8wj', '$2y$13$OKMKSMeP70Mzvs3rcl1ixu42SYed2FlUKXRihM3sx8xhDmKRb.fam', NULL, '2533473133@qq.com', 10, 1574670526, 1574675128, 'PmajY4ktw43BqF_syWzBhXrtYObd5uLx_1574670526', NULL, NULL, NULL, NULL, NULL),
(194, '徐秀枝', 'LTrRaSC3YNaG6tbQUqltHY6-zTWx8TsA', '$2y$13$D1u6LZwyIQlWCKTQDsPZ/eGk/KqZfZJ3wOtAJy6ndLGwx5OlFOds.', NULL, '3256752918@qq.com', 9, 1574670562, 1574670562, 'wkX5qsRC8M548wVSQJ4sHckMSwyUe2po_1574670562', NULL, NULL, NULL, NULL, NULL),
(195, '张思琦', 'yBpf0A5QhNjjQ6P_L0GftfWp0VR5gkvb', '$2y$13$Mm138M6MEJ4UdTCNIW.qve6eGsJhuC2o4BKscaq5bQ9D9Q6jIKlcu', NULL, '3130854034@qq.com', 9, 1574670589, 1574670589, 'NFNk4ReDr_ERmH70CznR5qXgvkA1ROTJ_1574670589', NULL, NULL, NULL, NULL, NULL),
(196, '刘子阳1', '_apRLZEf0e1Qv9DSE1XwhUXcJZjn21NZ', '$2y$13$quQMuBs5GzhI0QjWtziNQuL5Z3fG3UCmWo7SkLzbeU3iRTAfQpMiS', NULL, '2631432848@qq.com1', 9, 1574670671, 1574670671, 'YpOLQMawqK3cxolV9EtTu9FA_Tgu7sRE_1574670671', NULL, NULL, NULL, NULL, NULL),
(197, '63_1574382154', 'fYvmKukeeYmEfJtjIPI_jktkH8yZIDZk', '$2y$13$dM3IkXYNwZC3NVx2nJdyi.o0k.GMV/fkfZ/VncMDX586bcgYpYJPK', NULL, '16620070409@qq.com', 9, 1574670747, 1574670747, 'DILQ9bIYd1S_A9psovxxYSjH2OZin42j_1574670747', NULL, NULL, NULL, NULL, NULL),
(198, '宋洁汶', 'Y1fuWXnVJAIBWEhNWzyjKO9kUs-KfrOs', '$2y$13$XoyMLvU44TM0fWHGysUNE.wbEwU0mJEHta9y1SAO.WCmHuKFQF6zq', NULL, '2827634133@qq.com', 9, 1574670901, 1574670901, 'CUIjrdWuKK9gGpuu9fVt_wH5rOkEj9C-_1574670901', NULL, NULL, NULL, NULL, NULL),
(199, '刘子阳2', 'wefqtv2Av2stkfdZITdDCEBRTnAoxp7n', '$2y$13$hTqNYxO5sP6YSLl28LwaxOnyMwCPw9xL2.Q4yxaYNatsPkej6WhuS', NULL, '2631432848@qq.com2', 9, 1574670901, 1574670901, 'f_iRhnuWIOsCTvrSelNPKlhldECSov1y_1574670901', NULL, NULL, NULL, NULL, NULL),
(200, '范秀文', 'BGVCbJC4s1vC1mVZZcNSfuLkGvtMXVgx', '$2y$13$7HIe6LFRGPjA.BVINe3JrOYfLJ8ygP0QboTxV3rUZ2zWc5otPM4Ce', NULL, '2728904494@qq.com', 10, 1574670946, 1575334994, 'UFmmkCeMoYOjYNu-hoxpej3ZJAWJh1WA_1574670946', NULL, NULL, NULL, NULL, NULL),
(201, 'YS_ng', 'GEiRNle__TrUnGTWDnbjQP21iyQ8VlyU', '$2y$13$puoH27NlBwdOjKRgZ0TN0u9LxfSxJLzgvsCU0wz8U9bkbbDp.6CKu', NULL, 'rmsid_1004@naver.com', 10, 1574678242, 1574678282, 'OHGU7a0k06IHIGueroUGCbUgsfG9NWx2_1574678242', NULL, NULL, NULL, NULL, NULL),
(202, 'Song jinshuo', 'r1wZ2MQ3bDLW648TwPRLBoCuXgAIpRPj', '$2y$13$vdQwZFy5hkzz20Fh6clyhOvxu/Ss0nZBly8YpdlPB0Yz1HC3KImQ2', NULL, '1564736546@qq.com', 10, 1574683431, 1574684335, 'iTQngZlVq50jWQVrSXu_S68UbGp8RO08_1574683431', NULL, NULL, NULL, NULL, NULL),
(203, 'Hajihye', 'N2vp7QQEHZCREqGIvFMBc2PrLZbXME2h', '$2y$13$jM95i8eLdbbAulzz7rZDzOw3Awt4Zj7mcEoAS97JprNxY7NFWKD5e', NULL, 'hgh2006@naver.com', 10, 1574730387, 1574736507, 'gjMXGFrFQgXlHppKDIpIhfZeTHzCP7pK_1574730387', NULL, NULL, NULL, NULL, NULL),
(204, 'hgh2006', 'wbxGj9FVXn3eRVHmOeBD4xSY101ZxTIp', '$2y$13$odgkj4Tswvf6F.RFopmCjuu6MeXDp7HWsQfDvzYeEr4zggTPAvbSu', NULL, 'hgh200606@gmail.com', 10, 1574730611, 1574731062, 'OnovQPEK4gyKayPCfclPQUd-RxBTnxgS_1574730611', NULL, NULL, NULL, NULL, NULL),
(205, 'hezhihui', 'SzqwMrPaTI57OIyVbkpmvWGa5eL_cFxe', '$2y$13$XGjpYHCtVPkYbN5a8yJC9.AV9M9MCGmCtr1Nz/GhPrDLVZoEfPp6i', NULL, 'hgh200606@hanmail.net', 10, 1574730919, 1574731183, '5f2aCdUgtr2E85CFojsbWew5_3JFJ2JC_1574730919', NULL, NULL, NULL, NULL, NULL),
(206, 'nathanive', 'tZ4tkKgn_gP1RLW529bHuDjCWsRmjPag', '$2y$13$6MzEJEMhVbMgTecAjUt.h.kAkeXCpnA5YjKaF0bBZIx86y.u42gbO', NULL, '1962580935@qq.com', 10, 1574730947, 1575265418, 'SipnFrfp6t-OJGLi6ie3KCTk4RAQ-EZN_1574730947', NULL, NULL, NULL, NULL, NULL),
(207, 'liaozude', 'i40XeDlhfSF1qkaaqp31sijYE9PU3qNT', '$2y$13$YznB5K9lx7I7M.IcexaycOX/vEQ/Nu1Z.IYM5xZKNigBMB/w/qem2', NULL, '1224266595@qq.com', 9, 1574730998, 1574730998, 'MHPATQSVS5mknzwnR17SM8TDJKsjs_2c_1574730998', NULL, NULL, NULL, NULL, NULL),
(208, 'jingyi', 'oFAbT8wZ5uXX0S_ZoJ5dFVPSqnZrYJTA', '$2y$13$eIcvQ3n3k61BBf4Gm/1w3.Sne5N88xCbAKRPJvuZdpMfw2zDL7kU.', NULL, '838178554@qq.com', 10, 1574731006, 1574731026, 'LX4CGEG5nasNcYI4r9I51UEoP-JEa3lq_1574731006', NULL, NULL, NULL, NULL, NULL),
(209, 'zyy', 'HDAIzToGyitFohAuTAZdggYt_an_pBxg', '$2y$13$t7CpJM6np6AjnM0VB2paYOuTNGpa5Ni1nwS2v8XI8UNPGSt4Q6KgW', NULL, '1172506489@qq.com', 10, 1574731202, 1574731570, 'pJ_Lo7-PvZ0PLmCRN60AtSZs1zfRV4B0_1574731202', NULL, NULL, NULL, NULL, NULL),
(210, 'Wang', 'FIIE9uzdjnk47vNshKuqwKuNUbFQIAjU', '$2y$13$RLFztdCGPulmnPnY319FsOA.1e47m/xu8R8e2mYDQp11GWPUsWgdC', NULL, '2210868733@qq.com', 10, 1574731306, 1574731492, 'AP3_pFtnKZbiSPlTmi91Eh9p9wiTQRM6_1574731306', NULL, NULL, NULL, NULL, NULL),
(211, 'Xuan', 'K7xg2hqd5ud01o-AUSc9Gc8A9hjuOGuw', '$2y$13$pIRSDGuuZaHe1CMAP9B25.ti8Xxfu.J8KM3NRb5TD7MjeK2hXfosG', NULL, '296210331@qq.com', 10, 1574731473, 1574732053, 'HDRYiN026iO1hRi5IZBVy0YF6pVVNSSm_1574731473', NULL, NULL, NULL, NULL, NULL),
(212, 'LiHang', 'KK4KrHPCilo9TdIl_alKVmhZ3IbWAJpR', '$2y$13$piaXJy9gjJprrBJxfezIruvQ.9j3JSTPAoG2FH19VUTVWPIR8G6Ny', NULL, '736163999@qq.com', 10, 1574732202, 1574732467, 'WR9iEQbn99cJ57NIBHHzbFhWcwUiG1cp_1574732202', NULL, NULL, NULL, NULL, NULL),
(213, 'kim', 't-fThiHlZAe0g6y1X44ZokSG1qgQB8Eo', '$2y$13$psIvy7p6cO2CoVuGgv7JYudCoxCiNEjmYDjO.rYD2TnZGci63P5tq', NULL, 'kimuje1995@naver.com', 10, 1574736004, 1574736055, 'U44cpJNhaOpHZQKE3FRmAK9fzP3aGbMY_1574736004', NULL, NULL, NULL, NULL, NULL),
(214, 'hey', 'QBAoZPyCP9gK1phnOvdhI6hXBAmpjBOp', '$2y$13$IotgLVnOipzT9LbsFE.7G.lrJ5D/qAJ1xM7XX3Tj9rD/z6z7tqjNW', NULL, 'kimuje1995@hanmail.net', 10, 1574736842, 1574736885, 'MD8QK8-6J-4wIgBnwREDwLspKQthVfWw_1574736842', NULL, NULL, NULL, NULL, NULL),
(215, 'jin', 'LnFHQTK-pZH9VeinrLMrU52zpFlwLVEa', '$2y$13$80zJadfZKQUSf/JU94mC3Om3gm2rSbY1IZSOEGtvo/fMoENY7/Pi6', NULL, 'kimuje1995@naver.con', 9, 1574737690, 1574737690, 'JGRkgWoagKNlq8w9AzUmY9MkUQXcQRdO_1574737690', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `created_at`, `updated_at`, `verification_token`, `access_token`, `wx_openid`, `nickname`, `info`, `avatar_id`) VALUES
(216, 'kkk527', 'olvMfEIjJY7w9Kv7dsfjcuy1SioI58yL', '$2y$13$INFAVVYu73MY/NcnQuYgWOiwTYPR2crXrnQ..MWB0Dyw6z3kmHrxG', NULL, 'kha0527@qq.com', 10, 1574737910, 1574738004, 'FcWjnVmseBbg-ufeliiVrae99jT6YyEW_1574737910', NULL, NULL, NULL, NULL, NULL),
(217, 'kimj', 'GHgY2ESuxnHp-DWC0sTH3KHkM8f9jCnq', '$2y$13$DSFe//BA4B1Zi8LbYHI1Ye/5c5ermbCBwb41lKh8vuDq6USOZ4QwG', NULL, 'kimuje1995@gmail.com', 10, 1574738556, 1574738962, '3B1bISP7JBpmA4WtIMNtkYfCMcsePnbt_1574738556', NULL, NULL, NULL, NULL, NULL),
(218, 'dl', 'PHtc2fAd7sqaZgEfbbsjGZizDRZsF6az', '$2y$13$9H2kpLhtSsYGoNwo1Rr2V.eVqKqhEAf5ZhL3NCFsCBPUEgFhaNesG', NULL, '1426622899@qq.com', 10, 1574738584, 1574774567, 'uDFPOefMp9DR3bQTUoT925FqatiGfxD7_1574738584', NULL, NULL, NULL, NULL, NULL),
(219, 'herobaba', 'TzbT0OP66YjjBEdpXOu5Wza91lyGLwdO', '$2y$13$FNWiXMzRh4Iv5eIBwIZp/e0GU2uJnKnnkZ/00ZlmTtE7j9Gz1tcu6', NULL, 'daxiawen@qq.com', 10, 1575260282, 1575260295, 'rGTLPVUzfdtDBezOLAxC7d5pqzprN_P__1575260282', NULL, NULL, NULL, NULL, NULL),
(220, 'BiKSMhgvoerxjVQu', 'LsssCdnQVm7D62t1ogoBl8ZB4Fpl5HwY', '$2y$13$qUKKfENocCZ0O4DBqFjQu.tSa9GvQSz16hrtBzrsPdQkjg1c2xcXe', NULL, 'shanonnicholson16@gmail.com', 9, 1575296508, 1575296508, 'kzh-Zs-vptaBa5dZO0vxumNAh7b_kT67_1575296508', NULL, NULL, NULL, NULL, NULL),
(221, 'LIUXIA', 'QFbX4-SSjoVtQ1ytvkAYH1Zy7-F_NMUc', '$2y$13$ES86KeOYV3IayDEmE.mhKuPORNOjcSXJ4BPw0TYENEFo.kouQwnG2', NULL, '18297735@qq.com', 10, 1575334343, 1575334824, 'g8lplCYgwcBlXrNCySqUbvpqvfc2u2E0_1575334343', NULL, NULL, NULL, NULL, NULL),
(222, '张子玉', 'PZvclicnyZODC-BPLujXR6yl4dmlmUck', '$2y$13$RolF.bhk1w.XPoQvl/J/9OF1TJuw/Hjhf9CdUq1DMEhJJjyUzsZEW', NULL, '2737113821@qq.com', 10, 1575334424, 1575334688, 'xddqPTDWBuJD1Yv_KJ-sPP6E-f03_0f6_1575334424', NULL, NULL, NULL, NULL, NULL),
(223, 'liuziyang2', 'mNG7JPbrUaPV4dbqRCxWjl2H5nEVvlJA', '$2y$13$4IFDI0fon6grBWa3kZCd3.N9zmpDOnIFteOOxjT.coobgNQzmDEGa', NULL, '514473574@qq.com', 9, 1575334533, 1575334533, 'a4Xm27fSTrpd1lafeUKodTFfAzwbt4TC_1575334533', NULL, NULL, NULL, NULL, NULL),
(224, 'liusitong1', 'YswG8DuQ7xkCOZrBQ6IPY21TVQ2rknyV', '$2y$13$kSAHzFfC.HrwdJM..zrSSeSw5PX9Nwg2/mX4r4c0yixcZyawoKqTe', NULL, '18512202750@qq.com', 9, 1575334681, 1575334681, 'NEEd9uOnToy9X_RHoPRkIy4sZ6Un_TLz_1575334681', NULL, NULL, NULL, NULL, NULL),
(225, 'wanglixue1', '7m6WZuSYks7NAx1jklwsrCh9qBMK3TRd', '$2y$13$mdZZFtbzCil2m7Xeyb/Wp.JgkeN7FPs/peAzDe4obg8cLaYOhFDsq', NULL, '3568709725@qq.com', 10, 1575334897, 1575334976, 'FU1qui4n2YmHSJwIIJrKd3pmxbSWEePG_1575334897', NULL, NULL, NULL, NULL, NULL),
(226, 'Yangruixin.', '7VUdbdCG_AQAViR7MHZdCJd_5-tbCnGz', '$2y$13$Hw6LYJvqkURImfJLNlciP.sVlOJUiHhgUzjpo7.5tnajclZv6YU4a', NULL, 'r123456@QQ.COM', 9, 1575334931, 1575334931, 'p7JB_rL1Buo_lPod7FyWEQoa_WtzhVNA_1575334931', NULL, NULL, NULL, NULL, NULL),
(227, 'xu\'xiu\'zhi', 'bnx3ndzEcgNZjHOnLZhBfuKLV2XIRwKs', '$2y$13$9aJkeSSnsqJRvszYZXzwT.MhvroG26WyPBV4HnkrbUvw7WRbVGbeC', NULL, '210069783@qq.com', 10, 1575334973, 1575335041, 'O9o0BiH7qcWuaC_Nd5MaRh7N8SEd7y45_1575334973', NULL, NULL, NULL, NULL, NULL),
(228, 'Chenxinle.', 'YBiwkD6dxeTebVpX61ipFZ-v0P2LeV8Q', '$2y$13$kn.fZ8Yx/OKVmMNtMKEGou6971rvYfu0ABHiRwd9FBVtPEzSYQaL.', NULL, '1430374960@qq.com', 10, 1575334986, 1575335161, 'kNOqL7WxzFDNM58oX8-0FcTJxo17bSrW_1575334986', NULL, NULL, NULL, NULL, NULL),
(229, '济宁', 'xpP9SiR-oOvW1JHWIVQ-gF0MaLpQos1W', '$2y$13$JDiY1./42tlC8dQGIQoSOuVJUFQCp3uFQzf4b3cYnMkQV3EsT3LQW', NULL, 'cn.guanfei@gmail.com', 9, 1575427942, 1575427942, '8HKqNgjrV1QWCS0EiQBMbQ5OWzpS1MME_1575427942', NULL, NULL, NULL, NULL, NULL),
(230, 'ShaoXing', 'YEG-Qus09pEGRqw-jHnFOp8pyD8N-nXw', '$2y$13$V12z8n2RaIbRMa78YmYgVe5AnOtf3idphceCi399HeDDEEjepWcJ6', NULL, '15616334801@163.com', 9, 1575527365, 1575527365, 'mKrdZrUBFG2x6ewuBSWo-h5bqUSj1mPm_1575527365', NULL, NULL, NULL, NULL, NULL),
(231, '空舟自横', 'S99mU073EcpqAhVPWWXpggsbJ_IQdedK', '$2y$13$a973FxxJh3bj8SrSo57DderYNuODJsrsQMWhJj8FunxoHT9uwGdI2', NULL, 'glasscw@live.com', 10, 1575620428, 1575620531, 'rnYj0Y0TPXTHJRrCvtIJisqrI-86ilH3_1575620428', NULL, NULL, NULL, NULL, NULL),
(232, '山东济宁圣地电力', 'ZParGIvKDOcbil95qT_OrfX_knkNYnOa', '$2y$13$dr1eSvO8YO4zNFgtKYzNuumUxuIuQMtvsTmNj6Ny/C9nXPfo.D6P.', NULL, 'JNSDDL@163.com', 9, 1575872749, 1575872749, 'OmhxC6TUgVARNZByjkpU-anWBUQUhF-I_1575872749', NULL, NULL, NULL, NULL, NULL),
(233, 'sddl', 'maD813UC0Yh6OPltG8RlXmOopOVv8aP8', '$2y$13$TCYZgeUIrlXxHA2dsm1J6uxPKl5jh1myIHn7dIxsL/SHwRT40l8gu', NULL, 'jnsddl2019@163.com', 10, 1575875890, 1575876185, 'C-ddo1OkgWYQd7f4MLpfLU072pVdTsYa_1575875890', NULL, NULL, NULL, NULL, NULL),
(234, 'brandonshi', 'AxBgza2uWGAPI7pYaJYAaIi847ECkX_I', '$2y$13$KLaAY7I1eY1z8cSIAthRFeoq.g8s/FNgwVYAmSVFMScui0.FD9mhe', NULL, '18079156622@189.cn', 10, 1575948945, 1575948990, '-6K3RKwJO9eYj0G7sbxrDQg9I97jq9MQ_1575948945', NULL, NULL, NULL, NULL, NULL),
(235, 'ixianren', 'Y0gB7oucxCgjq1KKPmOzf92IPswDZ7Tk', '$2y$13$jVAaNus1tSmldF3OG6gt1e5PJz.hbdpo/M5AANmOF.cauv7KFDjne', NULL, 'bty16@mails.tsinghua.edu.cn', 10, 1576068142, 1576068209, '_WX6f_b-4Dwn8-k6lMu3hHWMSx-SSIcg_1576068142', NULL, NULL, NULL, NULL, NULL),
(236, 'blue_sky_1', 'Zh4QQFJs0tsr3iaYrPBPqkMB5xwlqsiP', '$2y$13$BHBZGouyauEaBBlRXUcduO6Fp1IsKVaW8hoEx43k7kvXcSd.W9p/6', NULL, 'blueskyxxx86@126.com', 10, 1576323227, 1576323774, 't7kAWPgTObqWW-HGC8lBfnx-1KYYaRkc_1576323227', NULL, NULL, NULL, NULL, NULL),
(237, 'khanMR', '2k4-D1J256Lrx9mR3Tzl3AqbeGeeOrnG', '$2y$13$hX/kP3DK.xmesfUlyq.Zru8kNWApmkflepPOOmqdGFuHusYj/qXku', NULL, '3292176900@qq.com', 10, 1577325506, 1577325549, 'xi0GM5HdYfq3VEKR2zXB8Bk9-6-TZpoj_1577325506', NULL, NULL, NULL, NULL, NULL),
(238, 'kingcheng', 'PNj5dyqDGC4V4NkJb7vRcaSwpiL750Jl', '$2y$13$Q1ZyY5MYHjhphy255v6zsO2z9Svb9ntA.GaCAaFZ.XpbhH1q5vRCy', NULL, '42286053@qq.com', 10, 1577863374, 1577863394, 'n89nnKbMLkKCom1DwlCz-_fYmrrUeI-F_1577863374', NULL, NULL, NULL, NULL, NULL),
(241, 'guoziermao', '5XSSaISLNPtQXj06FQIDmkWeZRzNbsc1', '$2y$13$XF9VqpJy84lY1UGfynFN9eYruBxa84AkjRsY3nPrhbTQbCd/bmLmK', NULL, '863101509@qq.com', 9, 1620733254, 1620733254, 'PwKw0REOtF54EgNzFX4Zsbfycf7rJFu4_1620733254', NULL, NULL, NULL, NULL, NULL),
(242, 'qanjide', '9Yl29_vE0yCuNDYh5ql2iHiDcSPiLG6H', '$2y$13$w0eMMv5dhleLgBTMsrKpXuy4hFiCCRo3uVmJVfEniTpisFLLMhqUe', NULL, 'qianjide@cafuc.edu.cn', 9, 1620750104, 1620750104, 'RohT4YgaYmTMlBFP9r8XYBdhWuLqMLz8_1620750104', NULL, NULL, NULL, NULL, NULL),
(243, 'qianjideCafuc', 'Hqo6lhNmPB8peJ8Euj9qC3w8Arv0kcjU', '$2y$13$7nrd33qdBXa.erI../oaoupY13ZjuwxxGR34sjeYvqDvkPQsPZggq', NULL, 'qianjide@qq.com', 9, 1620786956, 1620786956, 'RPuc_Ji4f-HLP5DDI7td9Q2wX4fUlWy6_1620786956', NULL, NULL, NULL, NULL, NULL),
(244, 'LWZ', 'FfsX_SLNyxfvOPXuS8bJ_GZRa5BK42UV', '$2y$13$H49pjqY0SGuMIEOn2pGb2.1GbV6N6zRIeDbiiB7IVksa/JTC4d2m6', NULL, 'wenzhengl0222@163.com', 9, 1620823564, 1620823564, '7slkdRDBeRc_pfK8bBNU8096ewkjwZQS_1620823564', NULL, NULL, NULL, NULL, NULL),
(245, 'Zelon', 'CazGCDcM2Yx7Kurg5Z1SwmXgfdCgm9Gr', '$2y$13$SexAJgixr56aoCMkOyMvDeubpgNXUOjmRlxoJFkbW7nwUf0kT8i.G', NULL, '445846834@qq.com', 9, 1620957879, 1620957879, 'SwvjEbS-chAgQL7aFeq4fT03PQq5wJhN_1620957879', NULL, NULL, NULL, NULL, NULL),
(246, 'zhj', 'ylzKGQPMxZbsfW4-serix4MbgqCy7CtL', '$2y$13$cTOCgY.7Y7eXO0SZadh6LOogWXtDAWJQgGn3ai4jy.Yd/tCoAefjy', NULL, '15012996192@163.com', 9, 1621492414, 1621492414, 'IRNSAUv3YGCi2BGHnbmDCYEBbKq79ep4_1621492414', NULL, NULL, NULL, NULL, NULL),
(247, 'ak47', 'ChAInCIXPsBjFADf84_iG1sWUXeinSPa', '$2y$13$BSvgifO0yulAcTY05IHUT.NWeBN5J4mhQfHiciFbNVzFBzt.8uCvi', NULL, 'narrojollu-7303@yopmail.com', 9, 1621951961, 1621951961, '3vSxFyVWwVBrx0fpw-EexZ0la6N6cKYX_1621951961', NULL, NULL, NULL, NULL, NULL),
(248, 'ak四十七', '103vRj6l3U-Ev-jGtt4XTik8TtmF21be', '$2y$13$O/7Hkpvc9vJimTfpJ/85Be7PxJWj1Pxxs28CG4NJVQItHXPiLMdQa', NULL, 'ziddenamme-7944@yopmail.com', 9, 1621952099, 1621952099, '188_gnb0KPPXSyBTtBYUCAhxyYQiuQqF_1621952099', NULL, NULL, NULL, NULL, NULL),
(249, 'ak五十劫', 'N8iNOR3EVRu8PYkLjJpjtvItF8l-VKja', '$2y$13$nGcL7AIre7wzX75Ew..zeO/bDqViP9ijj1hyto5GwD3Y0YD0b2Rua', NULL, 'turbo_t@yeah.net', 9, 1621952423, 1621952423, 'AcXVioI0ervk_l_Mo1qE8GuUFe0-kfT1_1621952423', NULL, NULL, NULL, NULL, NULL),
(250, 'maziyan', 'Q6REZrSmXaRRfS1Z_OMAkW_WtnGCjJZG', '$2y$13$A8w1G0nQXNL7IioOSeT9EO0O6kQg55glYQ/2IpOgQ2GVfJRLkqQRG', NULL, '954247275@qq.com', 9, 1622620103, 1622620103, 'QhBDRUSmSS-dGBnFYNeRZHSib5WeyAu8_1622620103', NULL, NULL, NULL, NULL, NULL),
(251, 'LAN', 'i00RTz8_6IFwAk_9sjTD7_vMAEfgfOpy', '$2y$13$eFypjILy8yrK7jTuHaId6OBWOOie.PqB4s8fxQVdBGJlLf/B32DKa', NULL, '1901862842@qq.com', 9, 1622825258, 1622825258, 'VjrlbqaEkD6U7Pp6mwKorHuQIq3y1J-S_1622825258', NULL, NULL, NULL, NULL, NULL),
(252, 'raven_marshall', 'SGkdhR_0lnVrwiRyieVhKdIaNBkcBGrx', '$2y$13$DIgR85OKFBKkYqlei079PuryUNT6CY1Kr58FvMKYJZRzzPiYIIZjK', NULL, '1531180960@qq.com', 9, 1623122306, 1623122306, '3dkoLxk7YPbYk_Zfm_c0NYqNXAnRsVsO_1623122306', NULL, NULL, NULL, NULL, NULL),
(253, 'http3w', 'eSPcBAdvU3t-ABDHf9SwXPr9QPkCRH12', '$2y$13$1/30VnGy.9I9ZjQ1A3jztukUbQ2CYETHHC34zd8X/a/X27SusfWfu', NULL, 'http3w@qq.com', 9, 1623210707, 1623210707, 'kO2-yLIVB7mkTVYtQWhM3Ze0UlBlnoWz_1623210707', NULL, NULL, NULL, NULL, NULL),
(254, '22531285', 'hEKIYRVkLBtwU5yRiOHzb0aVIOI7UZkN', '$2y$13$eYVTtehSDBwh8vO6vAT8..OjISfGbqbF4tp/FRZQVqgJKijWPpn7e', NULL, '22531285@qq.com', 9, 1623219247, 1623219247, 'AdBhkkfL2us1_2NFpiVruciZxHfVHhPd_1623219247', NULL, NULL, NULL, NULL, NULL),
(255, 'xiaolu', 'qdqMR1CQFYdC0oR4dzwXih2ur7KlcBmU', '$2y$13$wWQ0qD2r/olEiT1zxgDFrekF2sg7QTm2so73aV9.WuxF.yO0B5bbm', NULL, '258789822@qq.com', 9, 1623230796, 1623230796, 'GSXl6uy0qlRyZB8d6UjD089ts8gwoe61_1623230796', NULL, NULL, NULL, NULL, NULL),
(256, 'hartin', 'xh5qs9xwuTkDiaknKid2jEiUJv2vQOWp', '$2y$13$s0cPVuCx2TNf0mf8gzjAi.QZ4omoTYSjoC6mc7jCTH/i/0MFtQ1sO', NULL, 'lhtaz@126.com', 9, 1623829204, 1623829204, 'Dv9mBrTa5_QiChWW0Yy7pz0HEBIO6NhH_1623829204', NULL, NULL, NULL, NULL, NULL),
(257, 'yuan123yanyan', 'DdQiguQick4ALD6EC6_uKz278bow0ovQ', '$2y$13$UTs9/wTHzVPA7408PtbJuuCLQryEv29Wo77Vm6njF48Kr9u5He87q', NULL, 'yuan123yanyan@163.com', 9, 1623932114, 1623932114, '5Yi8XdFJbnKFbcgfH6rOi3G9IvQTfCwZ_1623932114', NULL, NULL, NULL, NULL, NULL),
(258, '1248083241', 'JbdtpfrzJiVjuVo0aAQ-39Zht-YnMCqG', '$2y$13$pAgqwM7jd7y5Qc1wJAumUOGt0/Fvd1vHR1A/xETIgLAj7w5dAa28W', NULL, '1248083241@qq.com', 9, 1623932372, 1623932372, '7C1Zt24CNBG-OIuwCbyyGgZF5sQssYpt_1623932372', NULL, NULL, NULL, NULL, NULL),
(259, 'Bo13~', 'DrXG5r0_ghb_mH0i7cLaYRaAEn_52Jg2', '$2y$13$fXa.tAphqmhMdTFPIzfcquZwtP0H2hbb7WrNv3B0l6khaTA3Ma9gi', NULL, '1172645958@qq.com', 9, 1623987967, 1623987967, 'YGgH6k5BOhBC2ysr9FpZc5yfTolECRYR_1623987967', NULL, NULL, NULL, NULL, NULL),
(260, 'Bo13', 'LimzKEmdtGFUCQjTfflz0F_MV0HRFcCn', '$2y$13$xLBeznUM58DkJ2uGUha0DeJsushFVkoVlxAjcA6do5rClc3k6hSpO', NULL, 'm1172645958@qq.com', 9, 1623988621, 1623988621, 'PQQyDsC6wNTOMeWwylswK68CV2g1GJH2_1623988621', NULL, NULL, NULL, NULL, NULL),
(261, 'dirui7d', 'dfP2HiKNzIsyEeuehPSWvPKlqRuyrlvs', '$2y$13$oYciaHNmley5Vx3zVetdvOyah3nA/yV5w.3VYfetyF40DknfT9uKm', NULL, 'dirui@7dgame.com', 9, 1634024888, 1634024888, 'Ecp3pE3pRac4_CBNmH47KZs16qnrwZ-T_1634024888', NULL, NULL, NULL, NULL, NULL),
(262, 'dirui7d1', 'Oi3OfoW6g8H2Sms0AuVu0CP07MtvCiZM', '$2y$13$OO5UEFr.lUoFfkGEu0MC7uM9qzLFCWeTSJ7qcpC5/ODph0gRuLNHq', NULL, 'dirui@7dgame1.com', 9, 1634024978, 1634024978, '2Gjw0KNPBenjC0hlTS_E_qov4Eu0vMi6_1634024978', NULL, NULL, NULL, NULL, NULL),
(263, 'dirui7d2', 'ihcvxVTMyKJ8o1-9OJFLaMynkf92knZ_', '$2y$13$vyKf7MA5VyVyVOZPUWB6ee3y3meR0zBbNGhlyWVdy8IbLOFtvFaui', NULL, 'dirui@7dgame2.com', 9, 1634025498, 1634025498, 'Kpj40B3Zh_6BZsQg5zRQeyee4uILW6UN_1634025498', NULL, NULL, NULL, NULL, NULL),
(264, 'mrpp1981', 'lvm8LnoTvPohUe5795gdMVRgRnnwKL9I', '$2y$13$qiyGHRI.B.dXxYKjiBJAbOv0dgznyC3G2.XQ7z/PXkhudAJYt8TqC', NULL, 'dirui@mrpp.com', 10, 1634105507, 1634181880, 'Ig0Suoix6scHk0DKvWOmjm9M2NZ6Ak8z_1634105507', NULL, NULL, NULL, NULL, NULL),
(265, 'testdelancy1', 'lBGfc_XOXh5GVzELVmr27VoBXgSyypG2', '$2y$13$v6ewhUij9jQuUFSnWIDsNOLM6TjF3e5/PeJqVYYO8RGZIj7VK.qXS', 'TyiNCXkc--dxfVifKDLjdtzZLKFAGNq3_1637118212', 'jiuyuewuxin@163.com', 10, 1634182013, 1644466579, '-hEArhFPz4YrL2gRwBY4Q0T2L9ESWvsf_1634182013', NULL, NULL, 'test1', NULL, 703),
(266, 'ttjing', 'S5zwR7jFy7im0r3fl9Haa6SrMbZ4KD6y', '$2y$13$DrZnZbV3Pc8PxMdmv6OqLec3yEXsIHExPWzLNTOo1FbDh16VR6wpW', NULL, '2324587676@qq.com', 9, 1636593130, 1636593130, 'hzY4VvdDcyTwpEdm-uHC5-4e_m1LB4W0_1636593130', NULL, NULL, NULL, NULL, NULL),
(267, 'espero', 'M6mYvWaQ7HmDO7EtQPFmvog6EOvvITV0', '$2y$13$OXnc0bIYANHq/dvAcMTgreV2Par0reSwjACv71JJAd2xHpBkMp3Ru', NULL, '2000935132@stu.pku.edu.cn', 9, 1636618931, 1636618931, 'o3T8w_Q09e1J722FXfeUa0cw04aalpWs_1636618931', NULL, NULL, NULL, NULL, NULL),
(268, '鲁炳辉', 'FmUus0vhl4GLD-VRf7BbyZwc3bRdNubu', '$2y$13$GsCkQtT.lx.bXohVDmTDkujgNyv6xpRC0hwvOEDajA5iTzVnmcYvq', NULL, '1678188361@qq.com', 9, 1636643003, 1636643003, 'QVYaHt9EvK6rMudS4eVLMkOP7hXEmpqR_1636643003', NULL, NULL, NULL, NULL, NULL),
(269, 'barryru', 'ch9PO_JvEfYU5DzmAAlsWifN_eYC17iK', '$2y$13$iD9pgGlljh3mCqbL9Zwrc.q7IDMldskvyrm3Ummjz7nEqLbQyazlO', NULL, '505053621@qq.com', 9, 1636643074, 1636643074, '8KahXAIqGpAjbxSwf1bmyS2WY0216lZB_1636643074', NULL, NULL, NULL, NULL, NULL),
(270, '沐光科技', 'r3s3eqzkmXVCqfT1tPBebyniJnIo0EHD', '$2y$13$ffA7DkPf/DGGCulNdrhnW.93RuNg11NbrvINoROSJiu6EcGSJsUlu', NULL, '3379113@qq.com', 9, 1636697583, 1636697583, 'h-DS_-6qQotSJ0H74pyRKTlWyIwO78Xg_1636697583', NULL, NULL, NULL, NULL, NULL),
(271, '黄嘉兴', 'pjzhazpmS8Okkb77xBsjIilSBf7GcZvg', '$2y$13$sudI.A.8BzA25R/PDE4ouO5ixgFuFI1DxmTpG4uJP6pkFoOqY16Iq', NULL, 'g3379113@sina.com', 9, 1636697699, 1636697699, '0eW-ZE2u1311errbs7vOpVyOQfnQ4NIq_1636697699', NULL, NULL, NULL, NULL, NULL),
(272, 'liuzichen', '_42X0AJHuFERidb95OUeI9Lo6FYCNb0M', '$2y$13$XCHT3pc8qgvqCZN7IHMZB.IcYA1IAQmCG7IlUXhfi9szwzjveL7iy', NULL, 'lzc2586@163.com', 9, 1636784236, 1636784236, 'kHwv020O--Q3uBaVBrOxcUAxoUu8bp4U_1636784236', NULL, NULL, NULL, NULL, NULL),
(273, 'emma jean', 'WLPhFXohGh9M_J9qzk8IAzxNiE_eGvkA', '$2y$13$R5BHnLwczpzUVLybgjCrP.dckhIjtL7rK4Wnc2SBabA6W8QK67Pum', NULL, 'dwelljiang@163.com', 9, 1636853397, 1636853397, 'pC_iLKijbnkUkHYuVL9Gf565Ked-GgHT_1636853397', NULL, NULL, NULL, NULL, NULL),
(274, 'dwei', 'imAPhYw2B82dj3eCDyPpdYEUPqlJMfU3', '$2y$13$TXY3vblGjlj9sQPr9ohOFeyhnkWiLVYopYL7Ie3NqSKIf02Co8x5a', NULL, 'jiangdawei91@hotmail.com', 9, 1636853896, 1636853896, 'wLviiUiwCuSdX7d3hd1TycuO-OPJtbeF_1636853896', NULL, NULL, NULL, NULL, NULL),
(275, '枫林光秀', 'XZEeeZYSY9qSPSjON1x_hUpyhcsuV1qr', '$2y$13$snkhdPoIDBhs17Oagb5Xze1XNw93YkNeM1iC5jdKGvaMbGP6vAoNu', NULL, '1191236523@qq.com', 9, 1637027173, 1637027173, 'OOsUrCreKmfJ0DxwRvHPGKLDYDAwjm5S_1637027173', NULL, NULL, NULL, NULL, NULL),
(276, '枫林情缘', 'Vz666cH4yCO5Pg5PtbiIysFSaVwB_txp', '$2y$13$fyyjVMEge7KRP1rOk4rwvO1SX4/KBzMl8JLv7AW9z7SbWoACoKJ8O', NULL, '2652066873@qq.com', 9, 1637027579, 1637027579, 'DXd9EZ6I0e7zGVNGr9baC022yyQwACSS_1637027579', NULL, NULL, NULL, NULL, NULL),
(277, 'guo qi', 'Ofa1Fg3M25lrmynlrxsSwRyt3fqCYg50', '$2y$13$Da9jKteQPaKhH3fcVU6NzOboRABmfiJk.VErHjqiFtkA148VMrUcq', NULL, 'guoqi76327@163.com', 9, 1637065425, 1637065425, 'Lezn8G1epiN0gfXtlvy5MHgMTvfP-qT8_1637065425', NULL, NULL, NULL, NULL, NULL),
(287, 'zoutaosss', 'WHrmTF-i3ULCV5tKa2qfNtw4AHB6Gh-a', '$2y$13$HfSRMMzn3Z.sTs6MbzFO5u66sjvDOEzABJEUUwyVooDBQ6hFAuc66', NULL, 'zoutaosss@163.com', 9, 1638156473, 1638156473, 'OBqZosRmdFQOGtt3rS6zq0vV6hwE6d_9_1638156471', NULL, NULL, NULL, NULL, NULL),
(288, 'myhololens', 'cW_gpbZjCfMSFOGljZJXrkmfxVd5Bv7e', '$2y$13$8SeKoeGrY6PAJ0LLjW6tzeo/xlkghKClHz7SGNdSMHjhF.Uyv6bYq', NULL, 'nethz@163.com', 10, 1638320620, 1638320637, 'PW4uH0aDDe9aguyFSuc3FCYNdyhzIghA_1638320617', NULL, NULL, NULL, NULL, NULL),
(289, 'rekcah9527', '3SUMCM65MrdiW-uQyR7ABvpQWsmMAkj6', '$2y$13$m1hG9RSYLQ10T9CG7jDpguZBkme4M6hf0Dp2yG2wWN67o.1mKcEKS', NULL, 'qqs1918@163.com', 10, 1638414767, 1638414778, 'OQRJ9G9vUobC_377EShnXOeZRBRl9V1q_1638414765', NULL, NULL, NULL, NULL, NULL),
(290, 'chengliangHOLO', 'gKPRGJKT2LrF_sN4rd2wCY2dVKMFPKSr', '$2y$13$OLMuJpJVBwVxqPc.sik7G.UGHLSg7OXCf1SRieEjuuxHCH3UgoWiW', NULL, 'chengliang_cafa@126.com', 10, 1638430968, 1638779798, 'vjO4qN6T3jqfNFcezxcGu0-7BMpJRDsu_1638430966', NULL, NULL, NULL, NULL, NULL),
(291, 'szcaupd', '5xgql8xEXu_vyJxHojx_tVF9_y6whnr-', '$2y$13$hQO3X0.B1IFHsgBHpNGNcOBbIA.HwOefQ./igPlj6sz5F0mzkQVoK', NULL, 'gdc@szcaupd.com', 10, 1638525717, 1638527047, 'oYpZ9kCZcJ4iokLqBDD69bW8WbAzANqh_1638525714', NULL, NULL, NULL, NULL, NULL),
(292, 'trentwu', 'gX5MSzF3q_mpYp2cWw6dZFhyf-LX7k90', '$2y$13$nf1K.wLeiUctDOhJ9hqA6eScU4pL3WkjYztM.NWDBgG6eN4kYCPWG', NULL, 'trentwu@163.com', 10, 1638585466, 1638585511, 'yMmkMKIUyPp0tLFZvNnvS9Nfp8_relx5_1638585463', NULL, NULL, NULL, NULL, NULL),
(293, '3 POWER STUDIO', 'A0KejbjO5PdWUXyspyXeMdrxDa3S4iPN', '$2y$13$nEehZQkFqZ7NvD19iuWHr.EK9k5SlgVtjoh1fKlS0z9aUwcgu9e9y', NULL, '805879434@qq.com', 10, 1638589272, 1638589285, 'ceQj4z6t5upl6rSmatesuXlbfozf78wS_1638589270', NULL, NULL, NULL, NULL, NULL),
(303, 'GTH_2013', 'mlfjtbfi_7KJWMhW1nqzZAPfY8riqW_0', '$2y$13$CWc.D8Ac1fPyCBLFXPqE1ONMxf3XG/.GOx4uN1b1c6HpWuZ6fwcBa', NULL, '44356339@qq.com', 10, 1639230371, 1639230411, 'B0aJSjHk8LJIVnNEgWmTSatkChl3arm-_1639230369', NULL, NULL, NULL, NULL, NULL),
(304, '余洋', 'UvyMbYluyoIQy_-TKPaucOGm5boSVg_V', '$2y$13$lWxnjlHAOpLgAEgNQFVa/uirTTJbIxBENg/2eU7ZGi1hE0FDRKdNm', NULL, '125372768@qq.com', 10, 1639237781, 1639239522, 'Az-v_m8u7OSTAGekxw3cb91aGX5Dvedy_1639237779', NULL, NULL, NULL, NULL, NULL),
(305, 'cauthy', 'j-_lx1BmarvdoZbp5yGTJvW0CfjZHp_A', '$2y$13$7x.ejrW.aweQ4vu1lckvbOD0hH9fGKNfLekBxVykWRnjFeRP663Om', NULL, 'tevance@163.com', 9, 1639238961, 1639238961, '1oKdnZI_YSoOvC49bpUVLV5a-lUvZrRn_1639238958', NULL, NULL, NULL, NULL, NULL),
(306, '叁零先生', 'TZIbBD5LpLm9ajAxqrNejEQ_AmwjVu7h', '$2y$13$9Z6kIFUVSQMpdZKyPO4Vhet/Vur4tpOJ4JWZkRY2sRLorffLxzSRe', NULL, '553822958@qq.com', 10, 1639291504, 1639291540, 'tb1ZxcC9gAEtVFCxv-3fo0wu59bFg7GQ_1639291502', NULL, NULL, NULL, NULL, NULL),
(307, 'barneyxing', 'IaMRCJRCC7BRlWR0wNEYz5EMYW97X6SY', '$2y$13$mmZrut6jzmMbgPF1MtXaB.DFkA8AtTiUP1mtYM1ABDs7oMmFqpEs6', NULL, '1015898989@qq.com', 10, 1639371936, 1639371964, '3BJNker3bsNj5-vtw3hNtMrJgrg7FDYX_1639371933', NULL, NULL, NULL, NULL, NULL),
(308, 'lach1760960120', 'SjE5npUHnQCn7UkSp1pTprls6eULqP2j', '$2y$13$.YJ2SgUB91gNWcWEJxroj.p4y.X6V2SgVs/U9II7bd7BNlXynqef.', NULL, '1760960120@qq.com', 10, 1639426925, 1639426946, 'e86liZ1zmZkjw-R6kU7iL5nJ2pdfR8eP_1639426923', NULL, NULL, NULL, NULL, NULL),
(309, 'fumingxing', 'McGlAVQ5KzKaKFjEK9tm5TYBZ4NV76Pr', '$2y$13$vA1ut3bS5zxUAkU1zfDgFeocY8lPWPEvsj8EMNcjSnB6YHO4OOqN2', NULL, '405118696@qq.com', 10, 1639451406, 1639451485, 'CS0V-YECFuSt7ITmVOtXqFkwV6FfNPtu_1639451403', NULL, NULL, NULL, NULL, NULL),
(310, 'liyinbin1996', 'tRtaTRQn8dFt1cMWVDNB-yJ9EW4ijtnF', '$2y$13$2kwbnzoFVM1acnSsp9TRd.jbq3LCFojcTQ9ln86Vzif82Gefq5jRu', NULL, '865526090@qq.com', 10, 1639472198, 1639472210, 'rBW4TBiIwTAHIN1uXBBIICHlp9YLiE-9_1639472195', NULL, NULL, NULL, NULL, NULL),
(311, 'sugarman', 'bzKsIgsMnnWSFP-LoS1r_73OZk2sP1rH', '$2y$13$64cCtLhtexeMflmJJHQHteanDsC3Rnj8Vsw6flZQLHON1jDNYODGu', NULL, '972233@qq.com', 10, 1639490773, 1639491140, 'Up3-cskufQOaaCEo1CFBZFROQfblRm-r_1639490770', NULL, NULL, NULL, NULL, NULL),
(312, 'zjw_gis', 'Ndf3Yimo-2A0-RwzI0B1blirP4Ssz7zo', '$2y$13$Tfcayut/SVxnRgBvv/fh2.HHT7NQwWsTj9.lHTrS1f5V/dl60pSiC', NULL, '41497272@qq.com', 9, 1639879295, 1639879295, 'ueKp0Jq-1_LO15ImC1bxm6hlybiibDq6_1639879292', NULL, NULL, NULL, NULL, NULL),
(313, 'zjw_gis721', '4euGBYMyACAhaeAUJkWCSzZbtDxIoGkn', '$2y$13$st4mU2jCdLHo5k9XHej80uDJ3W.A8CA48H5UnRaS80vbF4dbeRVda', NULL, 'friend5158@163.com', 10, 1639879333, 1639879760, 'rGWv75-TRKpEA1YY7lqC45oPFFifZNuo_1639879330', NULL, NULL, NULL, NULL, NULL),
(314, 'YFY60', 'HmeiEh-3IfiB_JSlZzIjdDSiY3hLQIQf', '$2y$13$4S797aOMrd1o94R397HuYe8c/TbRSB7/j.xY2tpIcBOzEDkcv8MXO', NULL, '754090097@qq.com', 10, 1639887488, 1639887497, 'XwLUdKK0gAYcBp42qlYzHo6MHr1cQlR7_1639887485', NULL, NULL, NULL, NULL, NULL),
(315, 'caizhengyang', 'WTXGiPglP2xYJHMsQhsBjwBsN6na7CHD', '$2y$13$DSByo1gyPouisn/K8V8.W.077UPzdioZ/pggS98D.LLwBHnQFd98e', NULL, '2020800135@hfut.edu.cn', 10, 1639988568, 1639988592, 'nY934FEaaNg7yE89x64-E4XMXWyYkv-s_1639988566', NULL, NULL, NULL, NULL, NULL),
(316, 'brainslicer', 'GR351jHs6GIFeslSlpbM9J4W_pEKkqvP', '$2y$13$RrwRixnjHg7EHqUyFwU11Ot1EcjRQb2Xh2Olsburg5gFpJgjE0N/W', NULL, '3344352075@qq.com', 10, 1640189328, 1644419741, 'CYvk5k-uq4rwJZN5lpXhD00e_ENmSnfQ_1640189325', NULL, 'ol2S453X17xIacgWa09GWsC9kQOI', '数字神经科', '{\"sex\":\"man\",\"industry\":\"教育医疗\",\"selectedOptions\":[],\"textarea\":\"\"}', 663),
(317, '984523610', 'VDGvPJVl4aI3-j9vmikhEP7WGOqg_MMA', '$2y$13$OAHWxX3vkIcJMVwal5qPMuUotQ/e2JVFGMFHFxwnwpY9AymlhFena', NULL, '984523610@qq.com', 10, 1640249767, 1640249777, 'W9jUT4X5hqB1JNmz0N0Ixnj9_mWnmKEQ_1640249763', NULL, NULL, NULL, NULL, NULL),
(318, '杰88', 'Bk8FyJZgNJjh_WPXfEJ8MYBMFF3qRSRp', '$2y$13$ZzTVzE5dsL.BjixNeLRNkesjb8iOhkvni121tb5UTDWZp7AikmBLy', NULL, '1049272251@qq.com', 10, 1640361209, 1640395702, 'lC-xZJyqAqZ97wnbSF-8A0PB6rnEr3lQ_1640361206', NULL, NULL, NULL, NULL, NULL),
(319, '薛孝志', '7R1eCroEC2ijKc8KcBvhCSdnuNzpE3aT', '$2y$13$mi2aZm1hbhPTpcxlYqSBWuCvA6ZG55LHse6egsXeVlSJYAZM8aN46', NULL, '744636300@qq.com', 10, 1640401963, 1640401986, 'rJcuTCqf-Gfp0HBp7_ywNYK0JDRrvQKS_1640401960', NULL, NULL, NULL, NULL, NULL),
(320, 'jhy_501', 'ctjeQ48QrciV0qnRLRGDBg9KOTHvfgnl', '$2y$13$1Mx2WSQ7SFTZAHKQ4UuFGupmUu8UY4y6Hdv9UJOB3SYI1Ya95Iywi', NULL, 'jhy_501@163.com', 10, 1640426075, 1640427403, '3dxg8JzZTHVtl0f5X6M_n5uzBtuN8mYC_1640426072', NULL, NULL, NULL, NULL, NULL),
(321, 'andy', 'OyOyuIe1WtGdcZXIqVBxNxaiJ9gr_J_K', '$2y$13$5bInGyTB5CYJ0.k3G6c9DOMy3YxwwNJF5FJttQWR2f3wML5piDWma', NULL, 'yangxw@betvsys.com', 10, 1640494450, 1640494480, 'WbPmU-h4zYU_bohmscQR3tBRMMM4nlm__1640494447', NULL, NULL, NULL, NULL, NULL),
(322, '红泥小火炉', 'poCH6DGnnbJ6wS3GJT383_LRNiSMUPXG', '$2y$13$7XHgyie4AiBMV7NX/2pGZuclIhLTxNRb4ojjpCX2eU0rCDT6wePSa', NULL, '121126302@qq.com', 10, 1640520988, 1640521026, 'lTKzINPwPFNIQlrIdhv-HOQZAudyQPdx_1640520984', NULL, NULL, NULL, NULL, NULL),
(323, 'fujingqi2021', 'FLX0TaK6Gv96hNVnmCQKXhtVnMaA9oRI', '$2y$13$qomCgl3D1Wq0T2AMf9drte9nq3ec0tN8waMWLn7AvkaQ.Rd02FfE.', NULL, '253588979@qq.com', 10, 1640525572, 1640532062, 'JHYWD9EhIP_wakcjTaujh_D_DtvQNX-A_1640525570', NULL, NULL, NULL, NULL, NULL),
(324, '张杰', 'XOWheOp_WZFFMf11DABNXbAG0MFeJ26H', '$2y$13$WsO/BUgxIDx4.9rzWECFnuD8OeEyX3haA6xw59TYa2EB.VZm6F4nm', NULL, '1192656145@qq.com', 10, 1640657687, 1640657700, '7lmEAkq5T9DlJkHTO3SeGr7EXeo4bBFN_1640657685', NULL, NULL, NULL, NULL, NULL),
(325, 'abdas', '3mnrdDWe-_JR60HY8QbxzokK8gZ337WH', '$2y$13$WMMT/e3W866zHpebysogauNNy8m.Xpyq30cVn5fSyXdc56nRiKiei', NULL, '82713691@qq.com', 10, 1640762644, 1640762654, 'RdI6gIoxYdAdMzjOrdhCP-WqCAKnTXCO_1640762641', NULL, NULL, NULL, NULL, NULL),
(326, 'Scarlett', '-bcLXhAtR61TvMbzDOHHAlaP5N0T1HiB', '$2y$13$bn1uFcJYjqemwTxVHSa9R.EwUbVqZ7KERhrChdjm3UNhKWURFmfGa', NULL, '784457207@qq.com', 10, 1640871751, 1640871777, 'mlgdPSlXXmHeUjFzRdkoM3H6u7W4THw0_1640871748', NULL, NULL, NULL, NULL, NULL),
(327, 'asd5552585', 'pAcCCWjCqzjsmWgC48QChjodpY5BLbei', '$2y$13$iCaK7j0rwhZ0r8XPd3xtN.KoNLc6COCgl.JI/RAVgj6/oVs1HCoFG', NULL, '709164461@qq.com', 9, 1640943338, 1640943338, '4lMXuI7DLvBM7FqnApA5QleXU7VG4s7B_1640943335', NULL, NULL, NULL, NULL, NULL),
(328, 'tczengjin', 'XEh6zGMmb6AtawdSL2biK291mA14h2M5', '$2y$13$XotG5pxzIrGAmq9h0OFnjuWULwT/Uobzn1iC0HPJtRtOU9Gx498fe', NULL, 'tczengjin@163.com', 10, 1641314846, 1641314899, '6BuPS4aiMHb5F8LACiByo7y5zHzqwVZN_1641314842', NULL, NULL, NULL, NULL, NULL),
(329, 'rekcah1', 'uqq2WLrF_rSobVFCr3LvzXEEaXgc2ZiZ', '$2y$13$HC/qR.VPN7ltyQSjB7ynpOjNvB/JSi9z74jkLMcA8qS4fQXw/zcYG', NULL, '11247991@qq.com', 10, 1641436778, 1641436806, 'UPfhgtBrl_cKpZwtUrZrZv6WGw4JvgZ-_1641436775', NULL, NULL, NULL, NULL, NULL),
(330, 'highway', 'PaNBTPfViw5RkdV0X2vQygMK_tFe-CkN', '$2y$13$k3zSn8d9AreTniVk7DOn0uZ0QDLOmb3pqVSQ1OxElnZWzd4P0wAr2', NULL, '491606499@qq.com', 10, 1641734104, 1641734129, '82UbTj0nK0U9gfInrQRUglWvXM85a_gU_1641734101', NULL, NULL, NULL, NULL, NULL),
(331, '风风南下', 'yUBNMpDFEVvS2mipMiAyBc0DN5El-pwL', '$2y$13$ybXki4rJGkdiaU7M7TqOx./uk16BQy7X7fiQsy2WxzMq344WyqvqO', NULL, 'wendell.h.wang@qq.com', 10, 1641740145, 1641740153, '459VzrQMUxIUOyrlWs7cWNQ_sOO0cmUc_1641740143', NULL, NULL, NULL, NULL, NULL),
(332, 'liujunjie', 'haqREP87JUHy1ksc4idgX4bJ5yEOn6VI', '$2y$13$qUFLc3g986F.sQmc7x3Tyuqa9hBVYzbtwMXmu0ijs4D4BTlpuRnRe', NULL, 'xmwhune_509@sina.com', 10, 1641780186, 1641780256, 'V9GCSp1YH_7118lbTNA4Z--x-J0_WEAW_1641780182', NULL, NULL, NULL, NULL, NULL),
(333, '2746028', 'TNiVZOwtwKBCRLMsuXDKtuM4gC36NU3Q', '$2y$13$VujLCRXOl/fbuZAWagCFVuUX6ty0BCKbefcAd04.HqzqzpgRJ6.nO', NULL, '2746028@qq.com', 10, 1641783303, 1641783331, 'ZTaN3g5dVAyiMM2MSvWoJ6u4WgNsU8kB_1641783300', NULL, NULL, NULL, NULL, NULL),
(334, '大空意', '3b2TwUABSKa98kxwdWWYEJZdecsLjhFa', '$2y$13$G.Uy/BrJc.QhKm8LvJqLDOo5d1Hvh2JBERvmrdzYB/cvlPEPP3Bmu', NULL, 'wzyfx@outlook.com', 9, 1641938705, 1641938705, 'OT_iGj7cuCpfMjE9I6SrNWVZK8NL9x9l_1641938702', NULL, NULL, NULL, NULL, NULL),
(335, 'wzyfx', 'hVllyVfq8pKGo02_SYKyukmEHFI0RVSO', '$2y$13$56xFgMmmCmsAGsC01l/GsuzihiVIWDOf.3Zk7IrA3KsbabPw8r7k6', NULL, '76012750@qq.com', 9, 1641938944, 1641938944, 'TWWczexcGWovraPCuw2giUTQRhrRh3US_1641938943', NULL, NULL, NULL, NULL, NULL),
(336, 'dirui2022', 'O3F_Het6f1hob_KK00Tgl0LwYs9yYBeM', '$2y$13$ZIeycLgG9inhQZCiobmbP.9cOTsFn.yWJdjUo1TQc8sZGE70gYcfe', NULL, NULL, 10, 1643623092, 1643623210, '27VNPCwR9sKmtriYXXrQ3ImXlfsroWpA_1643623092', NULL, 'ol2S4552TLUk8Y2gsKIirOav9Xik', 'cool！', NULL, 662),
(337, 'rekcah', 'WSSFz5_r8SKA3GGfvP1VoxTaKlZfcNTs', '$2y$13$71zHtDk3AMrlGQGR8mJfxuvWPgRtZ7Y.9tF6BlQFBo4JC8E/vW4QS', NULL, NULL, 10, 1643645909, 1645667439, 'Xrv-nt-p9jYSvjwBYxLjpC5gaLpH0xXS_1643645909', NULL, 'ol2S45_T9lSazznGRpyOvVC_feP0', 'rekcah', '{\"sex\":\"man\",\"industry\":\"能源、制造业\",\"selectedOptions\":[],\"textarea\":\"\"}', 766),
(338, '155192198@qq.com', 'Yk43MFXkWinnmNH7wu5CvSIuBwwsh1oe', '$2y$13$k3u5SfyOQEA1WavLlHjVRuXgB49.u7xZnYDqUxmpBYFRdho4MPIzW', NULL, NULL, 10, 1643767312, 1643767312, '_svF_HsT4lawZotzHD1w7bZZZfBPIux1_1643767312', NULL, 'ol2S45yJtltQmJ0_60EEJYqQyIOM', '155192198@qq.com', NULL, NULL),
(339, 'ZX1999', 'Wm3xMY_CiNuixOPImMgh59kvF2mV7zYW', '$2y$13$Xuq8dTarqKZmCx.2c7/NT.rrNJJmMch.bUHRRs/zeMmDwEfzOfgfW', NULL, NULL, 10, 1643798924, 1645596279, '9jsD9-3yBnVKshPWuGToW0K0O1Lbade-_1645596279@2821369186@qq.com', NULL, 'ol2S45_Ihgm4qAG_kA6pwIk1Ll38', 'ZX1999', '{\"sex\":\"woman\",\"industry\":\"其它行业\",\"selectedOptions\":[],\"textarea\":\"\"}', 670),
(340, 'Zhangfan', 'LIdhHjNLE6Y2nQjGP2RoiNmtYRh7vg2T', '$2y$13$myNx2KXDON7inlytjN1MweBWmCIK5P/CQBRTTpEJV5pEAbXQ99Tk6', NULL, NULL, 10, 1643809421, 1643809421, '7DAXkk1yjXEFy6wQ-eTTW8COIm-XMegL_1643809421', NULL, 'ol2S45-jsOFfM4sbQmTnzLueb5H0', 'Zhangfan', NULL, NULL),
(343, 'brianlc', 'h8RsLd5PRzxC2nIznE4DI7gxFbSsVW87', '$2y$13$eisG3FAoqci63pWU.aBucun/Fgjupkeu63CKkxDYchNMNb26PJ/oW', NULL, NULL, 10, 1643858978, 1643858978, 'jb4r-hjnolGcJNAsOG3UIp87Dumdun-p_1643858978', NULL, 'ol2S45xiD14I8VJQns9eNMPw-b7A', 'brianlc', NULL, NULL),
(344, 'tinysrc', 'Vl3SY7B7livJgZ8J0j7_9XWp-2ud8O_b', '$2y$13$z0pXYOiFdMpUkfhRbDiOCOJNlHY84VX3Ewu8JZfCKcwWdsmXfvqKC', NULL, NULL, 10, 1643940629, 1643940629, '6_a0CIATbkaDiLA2g-Ng8R-pfpsdoF5r_1643940629', NULL, 'ol2S45-SBmcSoJBFBy3apfiy9q9M', 'tinysrc', NULL, NULL),
(345, '380794969', '0wyVW6D_Q-Fptgj_QDGIukP9Yw796VpN', '$2y$13$TFBw5CPuPSx1D11.OGKE0uqqsGBhn25uZsbrwtZpPTS/uZRQNjipu', NULL, NULL, 10, 1644032673, 1644032673, 'm191XR0MSOw6ZqCe1O0jW4Y-x0BKd0TW_1644032673', NULL, 'ol2S45w1BpQI4SSxFt4-I3MBeawI', '380794969', NULL, NULL),
(346, 'wxm_i9', 'TktxSXkTnG5ekXpNNi7XCB9cqJii8oW_', '$2y$13$Kb2H42cQqjpXmovMB2J9geRfVTznK8WoX/6NgQqvx1nBN.sBQD8TS', NULL, NULL, 10, 1644058335, 1644058335, 'Of2iEvi-Qz0J0GMjG8pt-tJn56_YPbdj_1644058335', NULL, 'ol2S45zDw9dxL5Ie0aTyKMQL9Az0', 'wxm_i9', NULL, NULL),
(347, 'Pianzizoukai', 'VqeZ94avOVi5PVv12O7S8Qbgbppa1_fB', '$2y$13$HbjNisPk35TTaA/iIjGDz.UWtRCGxwoSFkf36JAK/.dMLw8c.0v.q', NULL, NULL, 10, 1644150026, 1644150026, '8l9zWy3buMuS3cU9tPg95o2XlqHX8upQ_1644150026', NULL, 'ol2S45-l8bRN88DqF-bVtRrwnnBk', 'Pianzizoukai ', NULL, NULL),
(348, '如如大王1995', '7lEflKNfJP6_ltTWoY6uZvT4yYsMzAeX', '$2y$13$fgtIb4.dXjhWXDEJYDOZhOr2ObWODzDsOPv27WuXceJFjlwskVH2K', NULL, NULL, 10, 1644242503, 1644242503, '8oqTK18FuwMMs80Ca6cRY0imUa5saxRs_1644242503', NULL, 'ol2S455rTHg4036kIrDjV2gJiN3A', '如如大王1995', NULL, NULL),
(349, 'Z971111', 'SmoxtAhI4X3176EAdrp4fNxz4fJs_NvS', '$2y$13$JO3iWae2PfaFhRkpJPGsk.5c0n./T/SLkLtrPGsaLCG.tTBEKSB7i', NULL, NULL, 10, 1644286288, 1644286288, 'xFeJVxuDWxe3pEuIjHbUK7gUZ2K_ELW2_1644286288', NULL, 'ol2S45078M4q1wovb-elW9D-s-kE', 'Z971111', NULL, NULL),
(350, 'C446041238', 'CZzzk-KaZkqPE7QLTvySH5CexZ8EkjcN', '$2y$13$Imbj02rXT55lsxEiuKT4beQvhCF08zf9q6ywfYlZpD3GXYp1oMioS', NULL, NULL, 10, 1644297660, 1644297660, 'X4iOzeTE4-2LAyn8jFs9vLE8xvSEoyyU_1644297660', NULL, 'ol2S451mSGkpqbWQoYJpyFaKg7f8', 'C446041238', NULL, NULL),
(351, '随手一打七个字', 'knhX3rZgyQkslkQaYypzQH0B597DL48v', '$2y$13$Aegu6D03ZdyRcuLaECo/re7mnqPKA0ZNVG8UdWtU74APwtPYfcmXy', NULL, NULL, 10, 1644379563, 1644379563, 'nqlzd6lnRPyIjzDfT7fW-yYr54Yr0v7R_1644379563', NULL, 'ol2S45526HnfC5YJuzeOtFX7qU-I', '随手一打七个字', NULL, NULL),
(352, 'ivan_lgs', 'JvBDzy8bvAqOPeRDJnuCs5DrgJHLmDlI', '$2y$13$HR.WS1x9sS9DqDl4srgtbeqsIiqovEDuUmfOv7krX47kEyktA3lLG', NULL, NULL, 10, 1644414745, 1644414745, 'PlB8VHy2oP5iJk3LC7GwyddJkY36jlk6_1644414745', NULL, 'ol2S450AdEAr_4BYnvrzWpy-M6uY', 'ivan_lgs', NULL, NULL),
(353, 'leoche', 'g339PL8_30P8o-a4GuH7RwS8JyNeZT4x', '$2y$13$3FHyJ6fI0yqMrDwqxCnyeeYyLyX6tMFrNzgvyTVjE5YX56DHViz96', NULL, NULL, 10, 1644551515, 1644551515, 'QX-yP4ldqWZVJ0ManqIfZTFbC4Q2GJ6s_1644551515', NULL, 'ol2S4506V7rixax0aIUQBHBmoYKU', 'leoche', NULL, NULL),
(354, 'jayjudge', 'j2enedm1CCPg6L7o3vLUfQjfi3Ehg-fw', '$2y$13$DIePUplATCix2aZeBXssiOnSBxXmfnLZpVwRxorHXzCr7/Q447FtS', NULL, NULL, 10, 1644555154, 1644555154, 'iQjkSpXavzKPPvpLRd6wYjb9cP97jdJo_1644555154', NULL, 'ol2S45-2H0gLX2-YL39omaxU2XMo', 'jayjudge', NULL, NULL),
(355, 'Tolem', '7NidYdRFnDN6q-berIpXQTmzVLvkWi4K', '$2y$13$tSJgvwBwknWLGGybCpFEA.0.oR1W6o0Eu1W0oGHyjGsYMnd9wehua', NULL, NULL, 10, 1644587656, 1644587656, 'OXRGwDN7QX9ZBrs9QPLhHtOtkbfu3oqP_1644587656', NULL, 'ol2S453VguF3NGLEh9l_pu5oDJ3A', 'Tolem', NULL, NULL),
(356, 'shieducn', 'OHiPszEsdiC49mg--qj12GjvaxltsDgE', '$2y$13$TKCO/SO/Z71uWw0hSudBu.tFxcrolRxJVwi4BlVPAdPzV1q0Y2Gt2', NULL, NULL, 10, 1644651773, 1644651773, '8WIt0VhCCOTCCT4EQboE1OHNu3yPZkrv_1644651773', NULL, 'ol2S459cERvsaXO7Xi6Y2xiUw2jU', 'shieducn', NULL, NULL),
(357, 'fanjm@xiomod.com', 'rreUI5OQpoAyjl4UBpzRVfXeP-2d3mb-', '$2y$13$5AIDx/vGEUIT0QIlhaHVj.zQItNMAtG.tviWrLeWV13BPKga3Bkiu', NULL, NULL, 10, 1644655456, 1644655456, 'w-iga_ksWYgVrpi-UBWcrRpeqlb_8Y5m_1644655456', NULL, 'ol2S456N6OgJ0BRGua8ArmPBSDr0', 'fanjm@xiomod.com', NULL, NULL),
(358, 'Lopevida', 'SXExARufSnb0L3MCioB6paXp9_TOjzkR', '$2y$13$GjCoZctLKLLgUHJQQdtXr.RNa.TT/voLl9iP3pkrnC5UzB6Ui4dtC', NULL, NULL, 10, 1644659529, 1644659529, 'n_nkBRSlX2uNOXsWmxmzeG4jniQFolws_1644659529', NULL, 'ol2S458ttEiSuiG8SMbbcKjtqHtE', 'Lopevida', NULL, NULL),
(359, 'dachenbo', 'dG7vDC34bViUIkSAYeID9zJzIUxBJZuF', '$2y$13$O6614CZ4cDcah9V4GoXEvObE8BCrNLsawiQRclzAPqhIDIqKEMY4a', NULL, NULL, 10, 1644831797, 1644831797, '1oeOJR0ste9CJQm2SIJ-GYqWip4em1z9_1644831797', NULL, 'ol2S458pJqx0513T8W2ZKGmjcLcg', 'dachenbo', NULL, NULL),
(360, 'bigcat', 'tQ4O3BPUH-tpxoASTDTc-ZTgCBGN8WW6', '$2y$13$a2IfC8TsSrbjYAmqwQeKJewghWeIHQAKAJZkExClIbxs4190bjFZ6', NULL, NULL, 10, 1644856306, 1644856306, 'C2GG2ArJFUKF8TuuQQRt1-WjH60NqEeR_1644856306', NULL, 'ol2S45whUjzG6Y6v1EIDg6CEnV5M', 'bigcat', NULL, NULL),
(361, 'jiangnanyu', '3mQh3dJr4Az4PasLIuvH_X6yYTRAH3Oo', '$2y$13$2/LrInhLABfr34yfJ977ueUeWpVxGXOdTuISMBqXdzyYK7MO0A0bO', NULL, NULL, 10, 1644912308, 1644912308, 'P9HQjJ_NgKvho1ZP2R9PtPLbSkvgL-KP_1644912308', NULL, 'ol2S451BiwZnMA2FJiDNAt9dJP-M', 'jiangnanyu', NULL, NULL),
(362, '7_VII', 'isLHy6crjFOTvZ4g_NfwyHfZpcQ3gdM3', '$2y$13$N6a.WSWfiRtV49wtmY8q7..0xGiMIEAL4A7.b9a8FjyMgsH9eVwL2', NULL, NULL, 10, 1644996977, 1644996977, 'r4Lgq3viZATN773L1ObuM5oTkAifvmpm_1644996977', NULL, 'ol2S456aYvq9Nngr7EYNd_x3czIg', '7_VII', NULL, NULL),
(363, '16lou', 'VhAcv-fL-IA6C69tYhdy8fnQud0-Wfje', '$2y$13$5xgqVQ9jErSjNVeHCPoj9.rY7xKDXm6OjtYWRjyDK2jPGdE3Wm0OC', NULL, NULL, 10, 1645084979, 1645084979, '2AoC5NqYfHqFBfxAn0MwXqxtYoMzo4rU_1645084979', NULL, 'ol2S45xTmlPAh75Mqeo6CyojyrSs', '16lou', NULL, NULL),
(364, '123456', 'HTib_5HxZ8PBmz7ogm0GWO9R0BO7iqYh', '$2y$13$oxQzs3UaerBVm8Y7oVrodObpy9Wraj5waMEzDAMbefXujNh9naNGS', NULL, NULL, 10, 1645089475, 1645089475, 'SnlMDlwXtCQyHIG0TwWMWnBmHo8QnodW_1645089475', NULL, 'ol2S4580jQx11H9afx6u1-9XRIHQ', '123456', NULL, NULL),
(365, 'zzhzzh', 'YeD0IxVUNzVZqmlvHGh2I05C9XcFsyRQ', '$2y$13$hunG4HoOblRTVfneDXR/Gusn4egjLW11hDToLw1UjFiQ/h.tVaDLG', NULL, NULL, 10, 1645108010, 1645108010, 'ILeEXjFkQYBh2uA2qsaa9N8wUhiWppS2_1645108010', NULL, 'ol2S459Sxu-TDQEci7_MqPw1i1AE', 'zzhzzh', NULL, NULL),
(367, 'Jhana', 'zwQxn2SdcO4KOW-BiLVR0Op1rMoNGH90', '$2y$13$MGv0MvShI8wcvMrKUQ3Apef9vVCnT5F4k8c2aP9AtJgoQxuGKVnZG', NULL, NULL, 10, 1645220027, 1645220027, 'dmwRAG5vCA60R8iPHMYhQSurXor2Qj64_1645220027', NULL, 'ol2S45yM2jJ-QptQbI68TSwoqEMs', 'Jhana', NULL, NULL),
(368, 'sem2022', 'mLfHaNgFLMPJoXzyMk9O3ZWoziZJ8BpI', '$2y$13$gTY7uOx0ov.cQvMxVUbhgOn/PkfRiXZno.eLU7zFPK/8/QfyrPB1K', NULL, NULL, 10, 1645515010, 1645675223, 'OrzQS_gxQ9-52NT5mrOjvkhIWe5z6o7d_1645515010', NULL, 'ol2S457n-wgmB-6lG5Vaph5Y0YbM', '开源刘韬', '{\"sex\":\"man\",\"industry\":\"科技、信息技术\",\"selectedOptions\":[],\"textarea\":\"\"}', 768);

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
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `image_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `verse`
--

INSERT INTO `verse` (`id`, `author_id`, `updater_id`, `created_at`, `updated_at`, `name`, `info`, `data`, `image_id`) VALUES
(2, 24, 24, '2022-01-29 09:48:18', '2022-01-29 09:48:29', 'shiyixia', '{\"description\":\"kankan\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"fc7e6600-4da1-41a4-be48-2a04233dff97\",\"verse\":{\"name\":\"shiyixia\",\"id\":2}},\"children\":{\"metas\":[]}}', 659),
(3, 24, 24, '2022-01-30 10:32:38', '2022-01-30 10:32:43', 'shiyixia2', '{\"description\":\"shiyixia2\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"90b2a0f6-b776-49dd-9ce1-b0939795782c\",\"verse\":{\"name\":\"shiyixia2\",\"id\":3}},\"children\":{\"metas\":[]}}', 661),
(4, 337, 337, '2022-02-02 08:11:39', '2022-02-24 12:24:44', 'rao1', '{\"description\":\"\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"b6dde877-e9b0-4bbe-abb8-25debb7fbb6b\",\"verse\":{\"name\":\"rao1\",\"id\":4}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"20796edc-7213-4f33-b506-cc4371ac5bec\",\"meta\":{\"name\":\"ok\",\"id\":33}}}]}}', 664),
(5, 24, 24, '2022-02-07 02:00:11', '2022-02-13 16:23:24', '新的宇宙', '{\"description\":\"111\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"662799c1-5380-446c-a575-dc9630908597\",\"verse\":{\"name\":\"新的宇宙\",\"id\":5}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"a571d994-3604-4a37-a870-73fea8134d7e\",\"meta\":{\"name\":\"neighborhood\",\"id\":17}}}]}}', 685),
(6, 315, 315, '2022-02-08 02:14:20', '2022-02-08 02:15:54', 'Turbine Blade', '{\"description\":\"\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"095ecf8d-9ccb-4153-b8f0-06de0b50e86f\",\"verse\":{\"name\":\"Turbine Blade\",\"id\":6}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"1e4b7a77-8593-4a1a-8705-2002c2ec1ddf\",\"meta\":{\"name\":\"model1\",\"id\":3}}}]}}', 692),
(7, 350, 350, '2022-02-08 05:22:51', '2022-02-08 05:24:00', '测试01', '{\"description\":\"\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"46e1c4f4-fbe1-461c-adf6-5eafc2c9e30c\",\"verse\":{\"name\":\"测试01\",\"id\":7}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"3e54fd08-7e15-4e9b-8606-4c8764c6814b\",\"meta\":{\"name\":\"root\",\"id\":6}}}]}}', 696),
(14, 338, 338, '2022-02-09 14:56:53', '2022-02-16 15:35:56', '我的世界', '{\"description\":\"测试\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"088c43b6-e4d2-471e-86d3-5995e28dff05\",\"verse\":{\"name\":\"我的世界\",\"id\":14}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"728661b4-c69d-47aa-8fef-897ea35c9b23\",\"meta\":{\"name\":\"圆柱\",\"id\":21}}}]}}', 700),
(16, 353, 353, '2022-02-11 07:55:26', '2022-02-11 07:55:54', 'demosay', '{\"description\":\"\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"f5587398-8429-441d-8e01-81d133a22f4f\",\"verse\":{\"name\":\"demosay\",\"id\":16}},\"children\":{\"metas\":[]}}', NULL),
(17, 25, 25, '2022-02-12 02:21:47', '2022-02-12 02:45:30', 'test1', '{\"description\":\"coo!\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"cb2d6e58-2527-49f8-bf1e-7315bbd3cfca\",\"verse\":{\"name\":\"test1\",\"id\":17}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"8b652e5b-5c20-44da-89ef-37752398b37b\",\"meta\":{\"name\":\"start\",\"id\":10}}}]}}', 710),
(18, 357, 357, '2022-02-12 08:45:44', '2022-02-13 06:46:48', '测试测试', '{\"description\":\"1\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"c813c515-c75a-4612-896b-8dab872e15a5\",\"verse\":{\"name\":\"测试测试\",\"id\":18}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"05bb4d23-ba09-45a6-adf1-4577a63e83fe\",\"meta\":{\"name\":\"food\",\"id\":11}}}]}}', 718),
(20, 344, 344, '2022-02-13 13:57:31', '2022-02-23 05:45:08', 'gfdsfs', '{\"description\":\"\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"96345557-2305-48d0-b63c-85261f34d575\",\"verse\":{\"name\":\"gfdsfs\",\"id\":20}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"238f4168-6158-42d2-9c68-eedc16546604\",\"meta\":{\"name\":\"fff\",\"id\":15}}},{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"d5362a6b-0739-4c90-b239-d2e4931d8a76\",\"meta\":{\"name\":\"ffffwwwwwwfff\",\"id\":25}}}]}}', NULL),
(22, 357, 357, '2022-02-15 05:53:10', '2022-02-15 06:06:08', 'test', '{\"description\":\"test\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"0a35ac9d-cee3-4bcc-82d9-100786a79733\",\"verse\":{\"name\":\"test\",\"id\":22}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"fc51f505-68c2-4c7e-999f-267a6e54cd75\",\"meta\":{\"name\":\"fellow\",\"id\":20}}}]}}', NULL),
(23, 3, 3, '2022-02-15 12:25:18', '2022-02-24 07:28:09', '简单模型展示', '{\"description\":\"一个简单的支持三方视角的例子\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"71d64ae4-1887-4694-a2b0-936ac07dc085\",\"verse\":{\"name\":\"简单模型展示\",\"id\":23}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"992e5bf8-51cd-48d6-82d4-f4146afc0757\",\"meta\":{\"name\":\"kuagu\",\"id\":22}}},{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"6b2bb80f-d45e-470e-a17a-fccbb26889c3\",\"meta\":{\"name\":\"tougu\",\"id\":32}}}]}}', 754),
(24, 362, 362, '2022-02-16 07:46:38', '2022-02-16 07:47:00', 'test', '{\"description\":\"\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"fb12c5fc-b6b3-49c2-8f3a-7774637be974\",\"verse\":{\"name\":\"test\",\"id\":24}},\"children\":{\"metas\":[]}}', NULL),
(26, 368, 368, '2022-02-23 10:47:28', '2022-02-23 13:29:12', '第一个测试', '{\"description\":\"\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"9eae6af4-9749-46e3-9ce8-2473586a74c7\",\"verse\":{\"name\":\"第一个测试\",\"id\":26}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"4d05ddf5-b58b-4637-ba5c-67620abf7476\",\"meta\":{\"name\":\"shaking\",\"id\":30}}}]}}', 756),
(27, 3, 3, '2022-02-25 02:07:25', '2022-02-25 02:11:53', 'test', '{\"description\":\"sadfsaf\"}', '{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"03d90944-8e16-4ed6-bc21-1c8ce569ecfb\",\"verse\":{\"name\":\"test\",\"id\":27}},\"children\":{\"metas\":[{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"42bdaa67-f741-4530-afa0-80f01cdc771d\",\"meta\":{\"name\":\"library\",\"id\":34}}},{\"type\":\"Meta\",\"parameters\":{\"uuid\":\"8db989ce-03a5-4583-b729-b0872c2480c8\",\"meta\":{\"name\":\"view\",\"id\":35}}}]}}', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `verse_cyber`
--

CREATE TABLE `verse_cyber` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  `verse_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `script` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `verse_cyber`
--

INSERT INTO `verse_cyber` (`id`, `author_id`, `updater_id`, `verse_id`, `created_at`, `updated_at`, `data`, `script`) VALUES
(2, 337, 337, 4, '2022-02-02 08:11:45', '2022-02-02 08:11:53', '{}', ''),
(3, 315, 315, 6, '2022-02-08 02:14:34', '2022-02-08 12:51:04', '{\"blocks\":{\"languageVersion\":0,\"blocks\":[{\"type\":\"action_trigger\",\"id\":\"I+6):O!6Dqj%k:gl4Z5v\",\"x\":70,\"y\":70,\"fields\":{\"Action\":\"7dabd60f-01c8-495a-b316-8a3bf5bde7e5\"},\"inputs\":{\"content\":{\"block\":{\"type\":\"visual_execute\",\"id\":\"Qwc#(,rKq^f?bMe1j:~^\",\"inputs\":{\"entity\":{\"block\":{\"type\":\"entity\",\"id\":\"=9GRk4yfkZr9FqB?`iHn\",\"fields\":{\"Entity\":\"\"}}},\"bool\":{\"block\":{\"type\":\"logic_boolean\",\"id\":\"_-rfw{L*4jJ;T-YO!eH%\",\"fields\":{\"BOOL\":\"FALSE\"}}}}}}}}]}}', 'self.handling[\'7dabd60f-01c8-495a-b316-8a3bf5bde7e5\'] = function(self, parameter)\n  print(\'7dabd60f-01c8-495a-b316-8a3bf5bde7e5\')\n  CS.MrPP.Lua.LuaExecuter.SetVisual(CS.MrPP.Lua.Handler(\"entity\", \"\"), false)\nend\n'),
(5, 338, 338, 14, '2022-02-09 14:57:36', '2022-02-15 06:07:14', '{\"blocks\":{\"languageVersion\":0,\"blocks\":[{\"type\":\"entity\",\"id\":\"(K@iT+ku?vaB-qBx@bqL\",\"x\":50,\"y\":70,\"fields\":{\"Entity\":\"\"}}]}}', 'local _ = CS.MrPP.Lua.Handler(\"entity\", \"\")\n'),
(7, 25, 25, 17, '2022-02-12 02:45:33', '2022-02-12 02:45:42', '{\"blocks\":{\"languageVersion\":0,\"blocks\":[{\"type\":\"action_trigger\",\"id\":\";6~-W6?rV88jNOzzG)-N\",\"x\":110,\"y\":10,\"fields\":{\"Action\":\"\"}}]}}', 'self.handling[\'\'] = function(self, parameter)\n  print(\'\')\nend\n'),
(8, 24, 24, 5, '2022-02-12 03:58:34', '2022-02-12 03:58:38', '{}', ''),
(9, 357, 357, 18, '2022-02-12 08:45:47', '2022-02-12 08:45:58', '{}', ''),
(11, 344, 344, 20, '2022-02-13 13:57:37', '2022-02-23 05:45:22', '{\"blocks\":{\"languageVersion\":0,\"blocks\":[{\"type\":\"destroy_trigger\",\"id\":\"fEOR)n_/csdnsn6+MUvQ\",\"x\":261,\"y\":50,\"inputs\":{\"content\":{\"block\":{\"type\":\"function_execute\",\"id\":\"R:v_p0O83o{])2o+{dx[\",\"fields\":{\"function\":\"CS.MrPP.Lua.LuaExecuter.Redo()\"},\"next\":{\"block\":{\"type\":\"controls_repeat_ext\",\"id\":\"S/h/Ll7E:E$SHVZ8d$k%\",\"inputs\":{\"TIMES\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"K{BsP8A15xPt92b)RN@U\",\"fields\":{\"NUM\":10}}},\"DO\":{\"block\":{\"type\":\"boom_execute\",\"id\":\"Z=h^|j]O$_6O4PeA3Kx1\",\"inputs\":{\"boom\":{\"shadow\":{\"type\":\"vector3_data\",\"id\":\"[rvs6{Ml:tD~KHzjWPYt\",\"inputs\":{\"X\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"W8y}C?I5{h?{l_HW$9-6\",\"fields\":{\"NUM\":0.2}}},\"Y\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"w*Y`V,^(wJ,;T[Afc7c~\",\"fields\":{\"NUM\":0}}},\"Z\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"}Pu9Br?[fxkm*%@/j7w(\",\"fields\":{\"NUM\":0}}}}}}}}}},\"next\":{\"block\":{\"type\":\"variables_set\",\"id\":\"l)qMn]hSdA,!@YWR_P@]\",\"fields\":{\"VAR\":{\"id\":\"+O//^2brW5G!UH!ay`s_\"}},\"inputs\":{\"VALUE\":{\"block\":{\"type\":\"transform_data\",\"id\":\"C}JH{lXziC2gO@=vBt#a\",\"inputs\":{\"position\":{\"shadow\":{\"type\":\"vector3_data\",\"id\":\"q{Ps3yqT,nR[Hn!AV|aA\",\"inputs\":{\"X\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"Z%f^IQXAb-(b-C-gmc[Y\",\"fields\":{\"NUM\":0}}},\"Y\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"B9b72s6*(ZQoRe)^Y3a#\",\"fields\":{\"NUM\":0}}},\"Z\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"{Nqoxh6Vmb@P6_k=F3Gn\",\"fields\":{\"NUM\":0}}}}}},\"scale\":{\"shadow\":{\"type\":\"vector3_data\",\"id\":\"~Vvip|!,6MYdjwA}Ft=%\",\"inputs\":{\"X\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"#?NaQrvP;T^Oy,Oq=I,z\",\"fields\":{\"NUM\":1}}},\"Y\":{\"shadow\":{\"type\":\"math_number\",\"id\":\";:1.+W#V@NslCkOYuoHb\",\"fields\":{\"NUM\":1}}},\"Z\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"Gq3m.+W(7}MWX#51_Me}\",\"fields\":{\"NUM\":1}}}}}},\"rotate\":{\"shadow\":{\"type\":\"vector3_data\",\"id\":\"]_Zm`zyHS[2BDoO*C2Eq\",\"inputs\":{\"X\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"Q+%nwx8-Aoa`,lyuQpPi\",\"fields\":{\"NUM\":0}}},\"Y\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"fluts|Y*LQ4?)+_E#WC-\",\"fields\":{\"NUM\":0}}},\"Z\":{\"shadow\":{\"type\":\"math_number\",\"id\":\".xaW}:dHiApB)phtGEpa\",\"fields\":{\"NUM\":0}}}}}}}}}},\"next\":{\"block\":{\"type\":\"controls_if\",\"id\":\"I$WW/.Kn)Dss}mowELL1\",\"inputs\":{\"DO0\":{\"block\":{\"type\":\"controls_repeat_ext\",\"id\":\"]ai~x~rW/js.h{8@%j;*\",\"inputs\":{\"TIMES\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"?~VwTJ5JRhZ`N]NQ+d#v\",\"fields\":{\"NUM\":10}}}},\"next\":{\"block\":{\"type\":\"controls_if\",\"id\":\"~*,yze#}fR.i4J)sx.uJ\",\"next\":{\"block\":{\"type\":\"controls_repeat_ext\",\"id\":\"d6bJ8]WmH(8-5l;akQv/\",\"inputs\":{\"TIMES\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"TsQi$s^Y|aVflo=x]xtA\",\"fields\":{\"NUM\":10}}}}}}}}}}}}}}}}}}}}}]},\"variables\":[{\"name\":\"jjjk\",\"id\":\"+O//^2brW5G!UH!ay`s_\"}]}', 'self.handling[\'@destroy\'] = function(self)\n  print(\'@destroy\')\n  CS.MrPP.Lua.LuaExecuter.Redo()\n  for count = 1, 10 do\n    CS.MrPP.Lua.LuaExecuter.Boom(parameter.sample, (CS.UnityEngine.Vector3(0.2,0,0)))\n  end\n  jjjk = CS.MrPP.Lua.Transform((CS.UnityEngine.Vector3(0,0,0)), (CS.UnityEngine.Vector3(1,1,1)), (CS.UnityEngine.Vector3(0,0,0)))\n  if false then\n    for count2 = 1, 10 do\n    end\n    if false then\n    end\n    for count3 = 1, 10 do\n    end\n  end\nend\n'),
(13, 362, 362, 24, '2022-02-16 07:47:01', '2022-02-16 07:47:01', NULL, NULL),
(14, 3, 3, 23, '2022-02-22 15:56:02', '2022-02-24 08:53:33', '{\"blocks\":{\"languageVersion\":0,\"blocks\":[{\"type\":\"action_trigger\",\"id\":\"3;e`jFcL4;;?^X-~iH2=\",\"x\":170,\"y\":150,\"fields\":{\"Action\":\"5c60d2d3-4db5-4eb3-a05c-ddaccd8dc984\"},\"inputs\":{\"content\":{\"block\":{\"type\":\"controls_repeat_ext\",\"id\":\"vY(BfBl*Qj%.eBO,@MC7\",\"inputs\":{\"TIMES\":{\"shadow\":{\"type\":\"math_number\",\"id\":\",bLCkC9j]37tbX!6n$Lk\",\"fields\":{\"NUM\":10}}}},\"next\":{\"block\":{\"type\":\"boom_execute\",\"id\":\"zyF4X3{,$w/TrRF4I%z6\",\"inputs\":{\"boom\":{\"shadow\":{\"type\":\"vector3_data\",\"id\":\"VR(cH|hmL!d%?8q?wt}z\",\"inputs\":{\"X\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"dn*OS[N@34MVTF,!T381\",\"fields\":{\"NUM\":0.2}}},\"Y\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"$|2D_BoA`STeJk%Ur,[q\",\"fields\":{\"NUM\":0}}},\"Z\":{\"shadow\":{\"type\":\"math_number\",\"id\":\"gXGB4Sh1t?vb_z!m=}P(\",\"fields\":{\"NUM\":0}}}}}}}}}}}}},{\"type\":\"text\",\"id\":\"@KAy:-A1urAIVCyyd)Bq\",\"x\":210,\"y\":90,\"fields\":{\"TEXT\":\"骨骼\"}}]}}', 'local _ = \'骨骼\'\n\nself.handling[\'5c60d2d3-4db5-4eb3-a05c-ddaccd8dc984\'] = function(self, parameter)\n  print(\'5c60d2d3-4db5-4eb3-a05c-ddaccd8dc984\')\n  for count = 1, 10 do\n  end\n  CS.MrPP.Lua.LuaExecuter.Boom(parameter.sample, (CS.UnityEngine.Vector3(0.2,0,0)))\nend\n'),
(15, 368, 368, 26, '2022-02-23 13:10:53', '2022-02-23 13:11:01', '{}', ''),
(16, 3, 3, 27, '2022-02-25 02:10:33', '2022-02-25 02:11:16', '{}', '');

-- --------------------------------------------------------

--
-- 表的结构 `verse_rete`
--

CREATE TABLE `verse_rete` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verse_id` int(11) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `version`
--

CREATE TABLE `version` (
  `id` int(11) NOT NULL,
  `version` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `version`
--

INSERT INTO `version` (`id`, `version`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `video`
--

CREATE TABLE `video` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `video`
--

INSERT INTO `video` (`id`, `name`, `file_id`, `user_id`, `created_at`, `image_id`) VALUES
(15, 'abc.mp4', 422, 3, '2021-05-22 15:45:03', 432),
(16, '20210105_154330_HoloLens.mp4', 437, 24, '2021-05-24 03:43:49', 438);

-- --------------------------------------------------------

--
-- 表的结构 `wx`
--

CREATE TABLE `wx` (
  `id` int(11) NOT NULL,
  `wx_openid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `wx`
--

INSERT INTO `wx` (`id`, `wx_openid`, `token`, `user_id`, `created_at`) VALUES
(3, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'ciJdNgjFJtOeaEivZi4C_I-TT1bN1K1x', NULL, '2021-12-04 16:23:26'),
(4, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'SzHzkovBWKZ4apZ7hwceCrV5uk2AtlbI', NULL, '2021-12-04 16:25:51'),
(5, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'P5jQsHQ3JJEw2C968y7K0NwTRo0a4iRS', NULL, '2021-12-04 16:59:42'),
(6, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'ywzS6HHDD4S--eItjA7ABKH9Eoa8xx8-', NULL, '2021-12-04 17:09:31'),
(7, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'Pu-62WTzkywAvJFCyxU1p340Tn03rLbR', NULL, '2021-12-04 17:23:34'),
(8, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'aN1EwKNBCE9UUK2hDu8YXz-gRzMLxIyq', NULL, '2021-12-04 17:25:25'),
(9, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'fxYqVPPz3Gp6E0p9xdPDXRk3cN07cL5W', NULL, '2021-12-04 17:25:55'),
(10, 'ol2S459vnAQOAHKfy_tRPnitgStc', '4mkOij66bPZJItdAa55ntVZoWkukPxBS', NULL, '2021-12-04 17:26:33'),
(11, 'ol2S459vnAQOAHKfy_tRPnitgStc', '1B4IpFKfT34XftFPbXG-PpJndXQ-3jby', NULL, '2021-12-04 17:27:07'),
(12, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'r4sgfMvGYgP6mQYqC7ueNFl_ThKoXiX1', NULL, '2021-12-04 17:27:43'),
(13, 'ol2S459vnAQOAHKfy_tRPnitgStc', '5QFyb7bxn4dZdVy-hd6mnj9YRbFRCy7Q', NULL, '2021-12-05 00:37:41'),
(14, 'ol2S459vnAQOAHKfy_tRPnitgStc', '-zkshvjDkxIw69JhTT7V3OIoa1e4vsxb', NULL, '2021-12-05 00:37:59'),
(15, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'F1nepvgWgllFW2V27ltby9XoZ4eYvyZF', NULL, '2021-12-05 00:38:20'),
(16, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'SxoJq7nQaBCTb_QRxsrdfPIw5acQpcna', NULL, '2021-12-05 00:57:58'),
(17, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'VJvWxcg1CiRfx4sKcwMvKvp3o6O6kcwt', NULL, '2021-12-05 00:59:28'),
(18, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'GdXeHVWnJ1sky5z1_ibo_j1gL8LcMcjY', NULL, '2021-12-05 01:24:39'),
(19, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'ewkL58O90G9D4e3jxKQCff9Wfq-HZhqL', NULL, '2021-12-05 02:35:16'),
(20, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'QfJ8elCygr25Cv-__5S22L8QhCupL6_2', NULL, '2021-12-05 03:43:01'),
(21, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'WLEMEDsH0cVmTmBBvl0xf5LGTQMVqe5H', NULL, '2021-12-05 05:30:32'),
(22, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'bdlfXLx18LUSV0H-ZQmJBmA1GAV7ZT_t', NULL, '2021-12-05 10:04:04'),
(23, 'ol2S459vnAQOAHKfy_tRPnitgStc', '03K_WHCHHjO5ppYV5z_arhmB97fXabL2', NULL, '2021-12-05 10:08:29'),
(25, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'S_0cFGeod_9KYSHnVgNB1DgkvH1Ca3B4', 3, '2021-12-05 22:15:47'),
(26, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'Xq3097Qk1UkFhY2EjI3pzbNRmiSVEWm_', 3, '2021-12-05 22:44:30'),
(27, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'spuskxeTdgKPdMfPVlgGPMWEDTOGbAPh', 3, '2021-12-05 22:48:10'),
(28, 'ol2S459vnAQOAHKfy_tRPnitgStc', 'JMXLgfw6mEBNQPD5_r-9jgYsFROh0_AM', 3, '2021-12-05 23:24:28'),
(29, 'ol2S4552TLUk8Y2gsKIirOav9Xik', 'rj2r7BFRSKZyHEDR8GTWZk7Q3VuH7lQ5', NULL, '2021-12-05 23:34:12'),
(30, 'ol2S450rUUuW8Pijd3_MgEW54-XE', '1ZXKzZK8WhgN7Il2KwHvwQin4bosMV1n', NULL, '2021-12-06 06:34:28'),
(39, 'ol2S4552TLUk8Y2gsKIirOav9Xik', 'DTOWEV3_bqG8ey4JJtwwza1ujHron2Xc', NULL, '2021-12-06 23:37:10'),
(41, 'ol2S450rUUuW8Pijd3_MgEW54-XE', 'nmH2vna8oNlgYYcp5pdbYnE96-igm6ba', NULL, '2021-12-07 01:50:31'),
(42, 'ol2S4552TLUk8Y2gsKIirOav9Xik', '3auW6_wVH1m4L9XgSQIBt_R4ydrikM8U', NULL, '2021-12-07 02:01:59'),
(43, 'ol2S4552TLUk8Y2gsKIirOav9Xik', '5g6K6HfZSmnnrds56x6T9q1hWAHlHDwI', NULL, '2021-12-07 05:34:31'),
(49, 'ol2S45xgZybB6LlljbYIsLGY1bCw', '', NULL, '2021-12-07 08:18:50'),
(50, 'ol2S453bc9vC_V0zzTlaOeBN3RJ8', '', NULL, '2021-12-07 15:03:36'),
(51, 'ol2S4524zm5LmZLbl7Y0YkN2wALU', '', NULL, '2021-12-08 02:14:44'),
(52, 'ol2S458mp2yNGRgGJIo4ffaAtT5s', '', NULL, '2021-12-08 04:32:40'),
(53, 'ol2S45yEW46yzE6zLSZY-otqfVPE', '', NULL, '2021-12-09 03:39:21'),
(54, 'ol2S45_cNEDCvnhXkGxnFK0Lq4PE', '', NULL, '2021-12-11 13:18:24'),
(55, 'ol2S45-67zDfUvLd7wrW8hnTosBw', '', NULL, '2021-12-11 15:47:36'),
(56, 'ol2S450uAnDDormniIvlHKe4JwwY', '', NULL, '2021-12-12 06:43:17'),
(57, 'ol2S457vVae6pTMa0UhNUBU-4nBg', '', NULL, '2021-12-12 11:44:24'),
(58, 'ol2S45zDWOmIQfig5Pd25aANLccE', '', NULL, '2021-12-13 20:19:25'),
(59, 'ol2S45wYcyMDVt8BuWbxA_YbR_GE', '', NULL, '2021-12-14 02:01:52'),
(60, 'ol2S45z_sX8eP_cPt4o9Prpu7AXU', '', NULL, '2021-12-14 03:07:23'),
(61, 'ol2S45yUn3pjG0jqgmApEEsN8glk', '', NULL, '2021-12-14 03:59:03'),
(62, 'ol2S45yhteJtyZdWdIWa68XHzqcU', '', NULL, '2021-12-14 07:43:06'),
(63, 'ol2S457frts1K0yCTxwNVgmjLZUo', '', NULL, '2021-12-14 08:51:26'),
(64, 'ol2S456Pyyd_kJCdrSYkNRvgUaNA', '', NULL, '2021-12-14 13:25:32'),
(65, 'ol2S45xX9IshV9kbVTz4wRaoH5bE', '', NULL, '2021-12-19 01:59:50'),
(66, 'ol2S45yhzhe6sGinf1HdiDqkb210', '', NULL, '2021-12-19 04:14:09'),
(67, 'ol2S458kKoHSNogr-nlWfcDJQhKM', '', NULL, '2021-12-19 13:53:24'),
(68, 'ol2S45xB8mCGPhivoE0f5o6OoND4', '', NULL, '2021-12-20 08:15:42'),
(69, 'ol2S450zKJj0OPDVWsN8TvAjBKL4', '', NULL, '2021-12-21 02:18:58'),
(70, 'ol2S45wx-NHMKNx0jHG1a61DE_Jc', '', NULL, '2021-12-21 02:24:19'),
(71, 'ol2S45-7ol-fAZ6E3gczBnXHtHRk', '', NULL, '2021-12-21 07:50:35'),
(72, 'ol2S4535cKtTXgcsGJ4yDCgdcWrQ', '', NULL, '2021-12-22 16:02:11'),
(73, 'ol2S453X17xIacgWa09GWsC9kQOI', '', NULL, '2021-12-22 16:16:52'),
(74, 'ol2S455_TLDZv6JpethqZSr23IS4', '', NULL, '2021-12-23 08:54:04'),
(75, 'ol2S45_qGSyCZmI14qCwceSsTaSY', '', NULL, '2021-12-24 12:51:24'),
(76, 'ol2S459vsntgX-QeXPG6GgwlUOH8', '', NULL, '2021-12-24 15:51:10'),
(77, 'ol2S4572EnW_PlUNgc6RpEEy-L9E', '', NULL, '2021-12-25 03:10:12'),
(78, 'ol2S45zd36diSN6bjeIK2piTl5H0', '', NULL, '2021-12-25 06:27:23'),
(79, 'ol2S453kGw5RaiY5XLDq8L7NJDAg', '', NULL, '2021-12-25 07:40:00'),
(80, 'ol2S4541K1i2dAHb1-6XnHCefneg', '', NULL, '2021-12-25 10:09:43'),
(81, 'ol2S456jhLa6dMgR4KbWxscM7LUk', '', NULL, '2021-12-26 12:13:50'),
(82, 'ol2S45_S3b954EZ0HfqfDfPmwH6g', '', NULL, '2021-12-26 13:29:54'),
(83, 'ol2S45_ouHSt-usCCH_U5Ef70xLk', '', NULL, '2021-12-28 02:55:11'),
(84, 'ol2S45x28wpg-FkzI6XRs_30Wj90', '', NULL, '2021-12-29 07:21:11'),
(85, 'ol2S453xavqewuVQcWhC2gztpxe0', '', NULL, '2021-12-30 13:40:43'),
(86, 'ol2S45xxKYwRHRw9GdQuCHDI0XL0', '', NULL, '2022-01-01 06:15:19'),
(87, 'ol2S458z4-D3GFwAOhHsblAl8-HY', '', NULL, '2022-01-01 07:43:52'),
(88, 'ol2S45wK-ZHcMrIJoA_6OcBiXzbM', '', NULL, '2022-01-02 12:43:01'),
(89, 'ol2S45y9lz61pz64ThDLFBDIloAs', '', NULL, '2022-01-03 12:26:39'),
(90, 'ol2S452f1GTjU3hGlBapGOaIJnTs', '', NULL, '2022-01-07 02:38:26'),
(91, 'ol2S454YBCY2f5oAEXi_o-18UcZQ', '', NULL, '2022-01-09 13:12:01'),
(92, 'ol2S45yK0rI0dDB6h_UCiLh4RpVw', '', NULL, '2022-01-09 13:50:39'),
(93, 'ol2S4542T8uJ0t0CE33RwDbHxfQ0', '', NULL, '2022-01-10 02:01:39'),
(94, 'ol2S457H7emtpvi_zfpKIul2LS4g', '', NULL, '2022-01-10 02:53:14'),
(95, 'ol2S459KeSSE9SOctBCWHht6xrpo', '', NULL, '2022-01-11 04:38:47'),
(96, 'ol2S454_rkajKMhYMkYWQLiEfhMQ', '', NULL, '2022-01-11 21:50:01'),
(97, 'ol2S457agDX7hiTv5unk8SYvMRCU', '', NULL, '2022-01-12 01:09:24'),
(98, 'ol2S45zd64nSh6liIfnRR9rqP9Fc', '', NULL, '2022-01-12 07:45:34'),
(99, 'ol2S456iUjDrA47SxHP9lZ9Av63k', '', NULL, '2022-01-12 08:21:39'),
(100, 'ol2S45w7_nrnOYM19CUakfaxv4WU', '', NULL, '2022-01-12 08:29:00'),
(101, 'ol2S45weis9OtAf1edsl4qmT6RXI', '', NULL, '2022-01-12 16:18:19'),
(102, 'ol2S457-x1styU6sU7-FlOrR941o', '', NULL, '2022-01-12 23:59:29'),
(103, 'ol2S45y4lrQ1sOkZO6Q9Q_1xLw_g', '', NULL, '2022-01-13 00:28:07'),
(107, 'ol2S45-PRe30uj7BbUVq5Ud3IBF4', '', NULL, '2022-01-13 10:14:17'),
(108, 'ol2S4562DnMkJoJ5Gh0gGFmXcDSY', '', NULL, '2022-01-18 09:55:50'),
(109, 'ol2S451ELUkJ-FkcKgY6PLnVQas0', '', NULL, '2022-01-18 16:12:53'),
(110, 'ol2S453obH2GgsBcGq203m-igcpc', '', NULL, '2022-01-19 02:59:32'),
(111, 'ol2S45zHlKP3L9hH8DYm3pZ-tB8s', '', NULL, '2022-01-20 01:33:33'),
(112, 'ol2S45xJpeaiMHKzLld4c1QOcVdw', '', NULL, '2022-01-20 07:30:30'),
(113, 'ol2S45y_w4DUmVznQ2dkTj8pP4wk', '', NULL, '2022-01-21 00:21:18'),
(114, 'ol2S45_Ihgm4qAG_kA6pwIk1Ll38', '', NULL, '2022-01-21 01:45:02'),
(115, 'ol2S457NwR7eqQjdB9nBzsOPoWV0', '', NULL, '2022-01-21 06:13:12'),
(116, 'ol2S45-jsOFfM4sbQmTnzLueb5H0', '', NULL, '2022-01-21 11:17:06'),
(117, 'ol2S456bOfIHA98nZtoJEfFIdlYQ', '', NULL, '2022-01-22 06:30:16'),
(118, 'ol2S453aeTA8Ivu9K0WcsXOQtQ0c', '', NULL, '2022-01-23 14:33:15'),
(119, 'ol2S4512kRTdazRSuFA-26uoS8o8', '', NULL, '2022-01-24 07:48:57'),
(120, 'ol2S453a3uK5x3pY1HtwXxrerD1U', '', NULL, '2022-01-25 07:04:51'),
(121, 'ol2S4522k8boOksZwmrs4PMLIytk', '', NULL, '2022-01-25 11:36:22'),
(122, 'ol2S453jJf9Y1JoG1aoDk6zIfrXs', '', NULL, '2022-01-26 09:27:57'),
(123, 'ol2S45-H1m36mBEwyj5st5qfpSVQ', '', NULL, '2022-01-27 01:45:31'),
(124, 'ol2S450rUUuW8Pijd3_MgEW54-XE', '2qCjKhX_0VrR4nJefDjgLj7Uc3roOZ3U', NULL, '2022-01-27 10:07:14'),
(125, 'ol2S450rUUuW8Pijd3_MgEW54-XE', 'yh6tuTATVJ1FmeaUCI6An7vTczDeVwA-', NULL, '2022-01-27 10:09:56'),
(126, 'ol2S450rUUuW8Pijd3_MgEW54-XE', '0QstJZAjQ5v6SEV9fs9t9OASyXwdfwHP', NULL, '2022-01-27 10:10:45'),
(127, 'ol2S450rUUuW8Pijd3_MgEW54-XE', 'yKFcAjrtTW_kRxv0XXz1QCBDjCqTpHf9', NULL, '2022-01-27 10:12:28'),
(128, 'ol2S450rUUuW8Pijd3_MgEW54-XE', '95VHmlkpU2iQw3o3SsMCcTA3UZTEeVaI', NULL, '2022-01-27 10:29:06'),
(129, 'ol2S455w6G35e0vwHKXFySQs4Ozc', 'mzrYG5AIxw9wBRZ1e6fEzsQL2oqNdQFt', NULL, '2022-01-28 09:41:42'),
(130, 'ol2S455w6G35e0vwHKXFySQs4Ozc', 'RhZlJVe-VssogQ6R2GSa4gPDghoVE7DY', NULL, '2022-01-28 09:48:47'),
(131, 'ol2S459286Id4Y081qnGvGGPb27Y', '', NULL, '2022-01-29 06:08:48'),
(144, 'ol2S4552TLUk8Y2gsKIirOav9Xik', 'ieRgJdYPXBt7QexG6LwXjGsetA85tXyx', NULL, '2022-01-31 09:51:26'),
(148, 'ol2S45_T9lSazznGRpyOvVC_feP0', 'aE3cMHZV3FoF21Pw2ythEUwLh5FjKpZR', NULL, '2022-01-31 16:17:55'),
(149, 'ol2S45yJtltQmJ0_60EEJYqQyIOM', 'cSxWR-_wCbhb0ExrK5vUsG1sRsdvos2j', NULL, '2022-02-02 02:01:25'),
(150, 'ol2S45_Ihgm4qAG_kA6pwIk1Ll38', 'jzJqW3ZBuQPSBwKNqdKDywWUFrZbC4po', NULL, '2022-02-02 10:47:53'),
(151, 'ol2S45-jsOFfM4sbQmTnzLueb5H0', 'bb3FpaV93m7Wg_jizu5x6NEs_4RrwRes', NULL, '2022-02-02 13:34:21'),
(152, 'ol2S45-jsOFfM4sbQmTnzLueb5H0', 'bb3FpaV93m7Wg_jizu5x6NEs_4RrwRes', NULL, '2022-02-02 13:36:46'),
(153, 'ol2S45-jsOFfM4sbQmTnzLueb5H0', 'dU81_kcf5Ea8oP7hsK0FcjSHj6BOVPUD', NULL, '2022-02-02 13:37:35'),
(154, 'ol2S45-jsOFfM4sbQmTnzLueb5H0', 'Lgbaky8JRD60bpeoT03SjsbZ6umfaEm6', NULL, '2022-02-02 13:42:42'),
(157, 'ol2S45yJtltQmJ0_60EEJYqQyIOM', 'XccOxb5mrUQOmNa18BzYay6-rNNxfshl', 338, '2022-02-02 14:32:57'),
(159, 'ol2S45xiD14I8VJQns9eNMPw-b7A', '1tFpoUVl2YCOLrjaKF9jKj5D1SCtS0lA', NULL, '2022-02-03 03:28:46'),
(160, 'ol2S45-SBmcSoJBFBy3apfiy9q9M', 'A-KTx7cngXsyY4eP0DFx4gOhawIJ5Fji', NULL, '2022-02-04 02:09:36'),
(161, 'ol2S45w1BpQI4SSxFt4-I3MBeawI', 'TQo_H1nIx73u0NGpDIf0Bs-nH-b5Ge53', NULL, '2022-02-05 03:44:11'),
(162, 'ol2S45zDw9dxL5Ie0aTyKMQL9Az0', 'gdlGVjJKk1otJfmd7kBksCZSnZ9xM7ES', NULL, '2022-02-05 10:51:55'),
(163, 'ol2S45-l8bRN88DqF-bVtRrwnnBk', 'aA2qDRqBNd0J_RwcHwVLf5sBLF0waJmk', NULL, '2022-02-06 12:19:50'),
(164, 'ol2S455rTHg4036kIrDjV2gJiN3A', '9WUbI0Q9cuk8Z0L_0-23UqBoqzbIN2BE', NULL, '2022-02-07 14:01:07'),
(165, 'ol2S45078M4q1wovb-elW9D-s-kE', 'EXQy82nK2T2NKSogExWEvDk8HQHgTPK7', NULL, '2022-02-08 02:10:58'),
(166, 'ol2S451mSGkpqbWQoYJpyFaKg7f8', '7B2k_aIlk5ANAPA5yLNiQVcw_XRMJ30Z', NULL, '2022-02-08 05:20:39'),
(167, 'ol2S45526HnfC5YJuzeOtFX7qU-I', 'R9FOkpeFo-GW-8X8tFTSwhiV1w4tUStN', NULL, '2022-02-09 04:05:15'),
(168, 'ol2S454yXErF2Ckbfm8C4qooK6iY', 'jYKlnsstZ-NXfZJRxSbGvSX_6boOCoEG', NULL, '2022-02-09 12:38:50'),
(169, 'ol2S450AdEAr_4BYnvrzWpy-M6uY', '-Domuv8l3NiWRW0S05IpIxzdJhJM9D5Q', NULL, '2022-02-09 13:51:31'),
(172, 'ol2S45zMEGN5Jj58INp0x0Q50om8', 'Fvz0Mg7qQz_dNXU2xriHYqgkTzwv6L_C', NULL, '2022-02-10 01:26:26'),
(173, 'ol2S4506V7rixax0aIUQBHBmoYKU', 'zVz7CpGSKncH2i0nLk-DO2_mOi8ICOgd', NULL, '2022-02-11 03:51:29'),
(174, 'ol2S45-2H0gLX2-YL39omaxU2XMo', 'eA-KR3JssLNt2b-tDbGoqWsmyiTlTwHc', NULL, '2022-02-11 04:51:49'),
(175, 'ol2S45-2H0gLX2-YL39omaxU2XMo', 'YL8FpLkquwK3uZF2l74LDzY5jPBBleMy', NULL, '2022-02-11 04:52:10'),
(176, 'ol2S453VguF3NGLEh9l_pu5oDJ3A', 'Rcl1j8iu8pUMyw_1n6iIDKvo5mEq6yAO', NULL, '2022-02-11 13:52:11'),
(178, 'ol2S459cERvsaXO7Xi6Y2xiUw2jU', 'Z294tFBeuabh4XGyxcUn9yQirXSC3Adp', NULL, '2022-02-12 07:42:32'),
(179, 'ol2S456N6OgJ0BRGua8ArmPBSDr0', 'X1LQoEq_Fv_y0iYQuiohihBC6PU7KHVd', NULL, '2022-02-12 08:43:24'),
(180, 'ol2S458ttEiSuiG8SMbbcKjtqHtE', '3_ZO4c7Ld6rd8W2LAonxEVM23njR-4ee', NULL, '2022-02-12 09:51:47'),
(181, 'ol2S458pJqx0513T8W2ZKGmjcLcg', 'sM6yxbIWv9LqFV8lS2q0Cf02hwBv6UFT', NULL, '2022-02-14 09:42:51'),
(182, 'ol2S45whUjzG6Y6v1EIDg6CEnV5M', 'FuzIw9QE-3_byklUbI-ClZNmY5Qda2Rn', NULL, '2022-02-14 16:30:37'),
(183, 'ol2S451BiwZnMA2FJiDNAt9dJP-M', 'MZi8NXQKcJRG4RYETaZ6mcLqcJ9cm8jp', NULL, '2022-02-15 08:04:26'),
(184, 'ol2S45-flq6NDc202ks93_K1fAQQ', 'KL1RwD_ASPEXtHADhv8WaHPfJflFeiMw', NULL, '2022-02-15 15:17:55'),
(185, 'ol2S456aYvq9Nngr7EYNd_x3czIg', 'U8-6adb49zDfMGKb6TLczIdk0HIegKjL', NULL, '2022-02-16 07:35:35'),
(186, 'ol2S45-HG9ArSuGJgbfMzjQOC2hY', '', NULL, '2022-02-16 11:26:24'),
(189, 'ol2S45xTmlPAh75Mqeo6CyojyrSs', 'v0VJecU8buPcg-dMgRndz4g2V1-Kt7-Q', NULL, '2022-02-17 08:02:07'),
(190, 'ol2S4580jQx11H9afx6u1-9XRIHQ', 'snGHlGHVl5Rriw6AkRo7t9oTS630ws9Q', NULL, '2022-02-17 09:17:12'),
(191, 'ol2S4580jQx11H9afx6u1-9XRIHQ', 'Qhkv_EA6YCCY3B8o0sPUNuEdeMt7PEkF', NULL, '2022-02-17 09:17:29'),
(192, 'ol2S459Sxu-TDQEci7_MqPw1i1AE', 'ZnDPQKblVpQlAe8WMObXslKjD8f0Pr_z', NULL, '2022-02-17 14:25:57'),
(193, 'ol2S45yM2jJ-QptQbI68TSwoqEMs', 'uTW9R6gBKVB6AI31knVKdwIMSsHfr_rA', NULL, '2022-02-18 21:33:09'),
(194, 'ol2S453jZLVz3BAeTYL40lXAGuFU', '', NULL, '2022-02-19 08:52:22'),
(196, 'ol2S457n-wgmB-6lG5Vaph5Y0YbM', 'hfI-MIzJDOKV1e8ZMeUVAiBsCBTIH8vZ', NULL, '2022-02-22 07:29:36');

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
-- 表的索引 `blockly`
--
ALTER TABLE `blockly`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-content-type` (`type`);

--
-- 表的索引 `content_type`
--
ALTER TABLE `content_type`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `debug`
--
ALTER TABLE `debug`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-debug-submitter_id` (`submitter_id`),
  ADD KEY `idx-debug-solver_id` (`solver_id`);

--
-- 表的索引 `editor`
--
ALTER TABLE `editor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project` (`project`),
  ADD KEY `template` (`template`);

--
-- 表的索引 `editor_data`
--
ALTER TABLE `editor_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-editor_data-node_id` (`node_id`),
  ADD KEY `idx-editor_data-project_id` (`project_id`),
  ADD KEY `idx-editor_data-user_id` (`user_id`);

--
-- 表的索引 `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-feedback-reporter` (`reporter`),
  ADD KEY `idx-feedback-repairer` (`repairer`),
  ADD KEY `idx-feedback-state_id` (`state_id`),
  ADD KEY `idx-feedback-describe_id` (`describe_id`);

--
-- 表的索引 `feedback_describe`
--
ALTER TABLE `feedback_describe`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `feedback_state`
--
ALTER TABLE `feedback_state`
  ADD PRIMARY KEY (`id`);

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
-- 表的索引 `file_store`
--
ALTER TABLE `file_store`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx-file_store-key` (`key`);

--
-- 表的索引 `goods`
--
ALTER TABLE `goods`
  ADD PRIMARY KEY (`id`);

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
-- 表的索引 `logic`
--
ALTER TABLE `logic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-logic-project_id` (`project_id`),
  ADD KEY `idx-logic-user_id` (`user_id`);

--
-- 表的索引 `maker`
--
ALTER TABLE `maker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-maker-user_id` (`user_id`),
  ADD KEY `idx-maker-polygen_id` (`polygen_id`),
  ADD KEY `idx-maker-programme_id` (`programme_id`);

--
-- 表的索引 `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-material-albedo` (`albedo`),
  ADD KEY `idx-material-metallic` (`metallic`),
  ADD KEY `idx-material-normal` (`normal`),
  ADD KEY `idx-material-occlusion` (`occlusion`),
  ADD KEY `idx-material-emission` (`emission`),
  ADD KEY `idx-material-user_id` (`user_id`),
  ADD KEY `idx-material-polygen_id` (`polygen_id`);

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
  ADD KEY `idx-meta-verse_id` (`verse_id`),
  ADD KEY `idx-meta-author_id` (`author_id`),
  ADD KEY `idx-meta-updater_id` (`updater_id`),
  ADD KEY `idx-meta-image_id` (`image_id`);

--
-- 表的索引 `meta_rete`
--
ALTER TABLE `meta_rete`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-meta_rete-meta_id` (`meta_id`);

--
-- 表的索引 `method`
--
ALTER TABLE `method`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-method-user_id` (`user_id`);

--
-- 表的索引 `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- 表的索引 `mrpp_project`
--
ALTER TABLE `mrpp_project`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-mrpp_project-author_id` (`author_id`),
  ADD KEY `idx-mrpp_project-updater_id` (`updater_id`),
  ADD KEY `idx-mrpp_project-image` (`image`);

--
-- 表的索引 `picture`
--
ALTER TABLE `picture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-picture-file_id` (`file_id`),
  ADD KEY `fk-picture-user_id` (`user_id`),
  ADD KEY `idx-picture-name` (`name`(191)),
  ADD KEY `idx-picture-image_id` (`image_id`);

--
-- 表的索引 `polygen`
--
ALTER TABLE `polygen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-polygen-name` (`name`),
  ADD KEY `idx-polygen-user_id` (`user_id`),
  ADD KEY `idx-polygen-file_id` (`file_id`),
  ADD KEY `idx-polygen-image_id` (`image_id`);

--
-- 表的索引 `polygen_material`
--
ALTER TABLE `polygen_material`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-polygen_material-polygen_id` (`polygen_id`),
  ADD KEY `idx-polygen_material-material_id` (`material_id`);

--
-- 表的索引 `programme`
--
ALTER TABLE `programme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-programme-author_id` (`author_id`);

--
-- 表的索引 `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-project-user_id` (`user_id`),
  ADD KEY `idx-project-programme_id` (`programme_id`),
  ADD KEY `idx-project-image_id` (`image_id`),
  ADD KEY `idx-project-name` (`name`);

--
-- 表的索引 `project_data`
--
ALTER TABLE `project_data`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `project_index`
--
ALTER TABLE `project_index`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-project_index-author_id` (`author_id`),
  ADD KEY `idx-project_index-image_id` (`image_id`),
  ADD KEY `idx-project_index-data_id` (`data_id`);

--
-- 表的索引 `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx-resource-type` (`type`),
  ADD KEY `idx-resource-author_id` (`author_id`),
  ADD KEY `idx-resource-file_id` (`file_id`),
  ADD KEY `idx-resource-image_id` (`image_id`),
  ADD KEY `idx-resource-updater_id` (`updater_id`);

--
-- 表的索引 `scripts`
--
ALTER TABLE `scripts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- 表的索引 `script_data`
--
ALTER TABLE `script_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_id` (`project_id`),
  ADD KEY `idx-script_data-project_id` (`project_id`),
  ADD KEY `idx-script_data-user_id` (`user_id`);

--
-- 表的索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `url`
--
ALTER TABLE `url`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-url-version` (`version`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `password_reset_token` (`password_reset_token`),
  ADD KEY `user-wx_openid` (`wx_openid`),
  ADD KEY `idx-user-avatar_id` (`avatar_id`);

--
-- 表的索引 `verse`
--
ALTER TABLE `verse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse-author_id` (`author_id`),
  ADD KEY `idx-verse-updater_id` (`updater_id`),
  ADD KEY `idx-verse-image_id` (`image_id`);

--
-- 表的索引 `verse_cyber`
--
ALTER TABLE `verse_cyber`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_cyber-verse_id` (`verse_id`),
  ADD KEY `idx-verse_cyber-author_id` (`author_id`),
  ADD KEY `idx-verse_cyber-updater_id` (`updater_id`);

--
-- 表的索引 `verse_rete`
--
ALTER TABLE `verse_rete`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_rete-verse_id` (`verse_id`);

--
-- 表的索引 `version`
--
ALTER TABLE `version`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `version` (`version`);

--
-- 表的索引 `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-video-file_id` (`file_id`),
  ADD KEY `fk-video-user_id` (`user_id`),
  ADD KEY `idx-video-image_id` (`image_id`);

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
-- 使用表AUTO_INCREMENT `blockly`
--
ALTER TABLE `blockly`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- 使用表AUTO_INCREMENT `content`
--
ALTER TABLE `content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `content_type`
--
ALTER TABLE `content_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `debug`
--
ALTER TABLE `debug`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `editor`
--
ALTER TABLE `editor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=480;

--
-- 使用表AUTO_INCREMENT `editor_data`
--
ALTER TABLE `editor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=988;

--
-- 使用表AUTO_INCREMENT `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `feedback_describe`
--
ALTER TABLE `feedback_describe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `feedback_state`
--
ALTER TABLE `feedback_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `file`
--
ALTER TABLE `file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=776;

--
-- 使用表AUTO_INCREMENT `file_store`
--
ALTER TABLE `file_store`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `goods`
--
ALTER TABLE `goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- 使用表AUTO_INCREMENT `logic`
--
ALTER TABLE `logic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `maker`
--
ALTER TABLE `maker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- 使用表AUTO_INCREMENT `material`
--
ALTER TABLE `material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- 使用表AUTO_INCREMENT `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- 使用表AUTO_INCREMENT `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `message_tags`
--
ALTER TABLE `message_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `meta`
--
ALTER TABLE `meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- 使用表AUTO_INCREMENT `meta_rete`
--
ALTER TABLE `meta_rete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `method`
--
ALTER TABLE `method`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mrpp_project`
--
ALTER TABLE `mrpp_project`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `picture`
--
ALTER TABLE `picture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- 使用表AUTO_INCREMENT `polygen`
--
ALTER TABLE `polygen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=956;

--
-- 使用表AUTO_INCREMENT `polygen_material`
--
ALTER TABLE `polygen_material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `programme`
--
ALTER TABLE `programme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- 使用表AUTO_INCREMENT `project`
--
ALTER TABLE `project`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=522;

--
-- 使用表AUTO_INCREMENT `project_data`
--
ALTER TABLE `project_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `project_index`
--
ALTER TABLE `project_index`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `question`
--
ALTER TABLE `question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `reply`
--
ALTER TABLE `reply`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- 使用表AUTO_INCREMENT `resource`
--
ALTER TABLE `resource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- 使用表AUTO_INCREMENT `scripts`
--
ALTER TABLE `scripts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `script_data`
--
ALTER TABLE `script_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `url`
--
ALTER TABLE `url`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=369;

--
-- 使用表AUTO_INCREMENT `verse`
--
ALTER TABLE `verse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- 使用表AUTO_INCREMENT `verse_cyber`
--
ALTER TABLE `verse_cyber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `verse_rete`
--
ALTER TABLE `verse_rete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `version`
--
ALTER TABLE `version`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `video`
--
ALTER TABLE `video`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `wx`
--
ALTER TABLE `wx`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

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
-- 限制表 `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `fk-content-type` FOREIGN KEY (`type`) REFERENCES `content_type` (`id`) ON DELETE CASCADE;

--
-- 限制表 `debug`
--
ALTER TABLE `debug`
  ADD CONSTRAINT `fk-debug-solver_id` FOREIGN KEY (`solver_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-debug-submitter_id` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `editor_data`
--
ALTER TABLE `editor_data`
  ADD CONSTRAINT `fk-editor_data-project_id` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-editor_data-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk-feedback-describe_id` FOREIGN KEY (`describe_id`) REFERENCES `feedback_describe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-feedback-repairer` FOREIGN KEY (`repairer`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-feedback-reporter` FOREIGN KEY (`reporter`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-feedback-state_id` FOREIGN KEY (`state_id`) REFERENCES `feedback_state` (`id`) ON DELETE CASCADE;

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
-- 限制表 `maker`
--
ALTER TABLE `maker`
  ADD CONSTRAINT `fk-maker-polygen_id` FOREIGN KEY (`polygen_id`) REFERENCES `polygen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-maker-programme_id` FOREIGN KEY (`programme_id`) REFERENCES `programme` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-maker-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `fk-material-albedo` FOREIGN KEY (`albedo`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-material-emission` FOREIGN KEY (`emission`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-material-metallic` FOREIGN KEY (`metallic`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-material-normal` FOREIGN KEY (`normal`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-material-occlusion` FOREIGN KEY (`occlusion`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-material-polygen_id` FOREIGN KEY (`polygen_id`) REFERENCES `polygen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-material-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

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
-- 限制表 `meta_rete`
--
ALTER TABLE `meta_rete`
  ADD CONSTRAINT `fk-meta_rete-meta_id` FOREIGN KEY (`meta_id`) REFERENCES `meta` (`id`) ON DELETE CASCADE;

--
-- 限制表 `method`
--
ALTER TABLE `method`
  ADD CONSTRAINT `fk-method-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `mrpp_project`
--
ALTER TABLE `mrpp_project`
  ADD CONSTRAINT `fk-mrpp_project-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-mrpp_project-image` FOREIGN KEY (`image`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-mrpp_project-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `picture`
--
ALTER TABLE `picture`
  ADD CONSTRAINT `fk-picture-file_id` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-picture-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-picture-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `polygen`
--
ALTER TABLE `polygen`
  ADD CONSTRAINT `fk-polygen-file_id` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-polygen-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-polygen-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `polygen_material`
--
ALTER TABLE `polygen_material`
  ADD CONSTRAINT `fk-polygen_material-material_id` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-polygen_material-polygen_id` FOREIGN KEY (`polygen_id`) REFERENCES `polygen` (`id`) ON DELETE CASCADE;

--
-- 限制表 `programme`
--
ALTER TABLE `programme`
  ADD CONSTRAINT `fk-programme-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `fk-project-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-project-programme_id` FOREIGN KEY (`programme_id`) REFERENCES `programme` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-project-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `project_index`
--
ALTER TABLE `project_index`
  ADD CONSTRAINT `fk-project_index-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-project_index-data_id` FOREIGN KEY (`data_id`) REFERENCES `project_data` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-project_index-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE;

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
-- 限制表 `script_data`
--
ALTER TABLE `script_data`
  ADD CONSTRAINT `fk-script_data-project_id` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-script_data-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `url`
--
ALTER TABLE `url`
  ADD CONSTRAINT `fk-url-version` FOREIGN KEY (`version`) REFERENCES `version` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk-user-avatar_id` FOREIGN KEY (`avatar_id`) REFERENCES `file` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse`
--
ALTER TABLE `verse`
  ADD CONSTRAINT `fk-verse-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_cyber`
--
ALTER TABLE `verse_cyber`
  ADD CONSTRAINT `fk-verse_cyber-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_cyber-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_cyber-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_rete`
--
ALTER TABLE `verse_rete`
  ADD CONSTRAINT `fk-verse_rete-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `fk-video-file_id` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-video-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-video-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `wx`
--
ALTER TABLE `wx`
  ADD CONSTRAINT `fk-wx-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
