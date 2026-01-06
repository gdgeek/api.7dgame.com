-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： 10.206.0.5:3306
-- 生成日期： 2026-01-06 18:19:01
-- 服务器版本： 8.0.30-cynos
-- PHP 版本： 7.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `bujiaban`
--

-- --------------------------------------------------------

--
-- 表的结构 `ai_rodin`
--

CREATE TABLE `ai_rodin` (
  `id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `generation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `check` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `download` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `resource_id` int DEFAULT NULL,
  `query` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- 表的结构 `apple_id`
--

CREATE TABLE `apple_id` (
  `id` int NOT NULL,
  `apple_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `auth_assignment`
--

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `user_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `auth_item`
--

CREATE TABLE `auth_item` (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` smallint NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `rule_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `data` blob,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `auth_item_child`
--

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `child` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `auth_rule`
--

CREATE TABLE `auth_rule` (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` blob,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `code`
--

CREATE TABLE `code` (
  `id` int NOT NULL,
  `lua` mediumtext COLLATE utf8mb3_unicode_ci,
  `js` mediumtext COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `device`
--

CREATE TABLE `device` (
  `id` int NOT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `owner_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `setup` json DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `edu_class`
--

CREATE TABLE `edu_class` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `school_id` int DEFAULT NULL,
  `image_id` int DEFAULT NULL,
  `info` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `edu_school`
--

CREATE TABLE `edu_school` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_id` int DEFAULT NULL,
  `info` json DEFAULT NULL,
  `principal` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `reporter` int NOT NULL,
  `repairer` int DEFAULT NULL,
  `state_id` int DEFAULT NULL,
  `describe_id` int NOT NULL,
  `bug` text NOT NULL,
  `debug` text,
  `infomation` text,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fixed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback_describe`
--

CREATE TABLE `feedback_describe` (
  `id` int NOT NULL,
  `describe` varchar(128) DEFAULT NULL,
  `order` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback_state`
--

CREATE TABLE `feedback_state` (
  `id` int NOT NULL,
  `state` varchar(128) DEFAULT NULL,
  `order` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `file`
--

CREATE TABLE `file` (
  `id` int NOT NULL,
  `md5` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `filename` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'nokey'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `info`
--

CREATE TABLE `info` (
  `id` int NOT NULL,
  `name` varchar(10) NOT NULL,
  `company` varchar(50) NOT NULL,
  `tel` varchar(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `invitation` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `invitation`
--

CREATE TABLE `invitation` (
  `id` int NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sender_id` int NOT NULL,
  `recipient_id` int DEFAULT NULL,
  `used` tinyint(1) DEFAULT NULL,
  `auth_item_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `local`
--

CREATE TABLE `local` (
  `id` int NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ;

-- --------------------------------------------------------

--
-- 表的结构 `manager`
--

CREATE TABLE `manager` (
  `id` int NOT NULL,
  `verse_id` int NOT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `menu`
--

CREATE TABLE `menu` (
  `id` int NOT NULL,
  `name` varchar(128) NOT NULL,
  `parent` int DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `order` int DEFAULT NULL,
  `data` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- 表的结构 `meta`
--

CREATE TABLE `meta` (
  `id` int NOT NULL,
  `author_id` int NOT NULL,
  `updater_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `image_id` int DEFAULT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `prefab` tinyint(1) NOT NULL DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- 表的结构 `meta_code`
--

CREATE TABLE `meta_code` (
  `id` int NOT NULL,
  `blockly` mediumtext COLLATE utf8mb3_unicode_ci,
  `meta_id` int NOT NULL,
  `code_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1550974410),
('m130524_201442_init', 1550974413),
('m140506_102106_rbac_init', 1551027932),
('m140602_111327_create_menu_table', 1551029855),
('m160312_050000_create_user', 1551029855),
('m170907_052038_rbac_add_index_on_auth_assignment_user_id', 1551027932),
('m180523_151638_rbac_updates_indexes_without_prefix', 1551027932),
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
('m191022_033650_create_picture', 1572580021),
('m191023_094253_drop_md5_index_from_file_table', 1571824694),
('m191023_151527_add_polygen_id_column_to_material_table', 1571854194),
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
('m230222_111021_add_editable_column_to_verse_share_table', 1677117499),
('m230512_032823_create_verse_script_table', 1688526076),
('m230513_113339_add_title_column_to_verse_script_table', 1688526076),
('m230514_123243_add_blockly_column_to_verse_script_table', 1688526076),
('m230515_141411_create_event_node_table', 1688526076),
('m230515_142632_create_event_input_table', 1688526076),
('m230515_142731_create_event_output_table', 1688526076),
('m230515_144345_create_event_link_table', 1688526077),
('m230515_152340_add_event_node_id_column_to_meta_table', 1688526077),
('m230516_070127_add_title_column_to_event_input_table', 1688526077),
('m230516_070206_add_title_column_to_event_output_table', 1688526077),
('m230516_074811_add_uuid_column_to_event_input_table', 1688526077),
('m230516_074829_add_uuid_column_to_event_output_table', 1688526077),
('m230516_153312_add_verse_id_column_to_event_link_table', 1688526077),
('m230520_031742_add_uuid_column_to_verse_script_table', 1688526077),
('m230628_063701_add_event_node_id_column_to_meta_knight_table', 1688526077),
('m230701_075935_add_type_column_to_knight_table', 1688526077),
('m230704_080223_add_workspace_column_to_verse_script_table', 1688526077),
('m230704_083610_drop_blockly_column_from_verse_script_table', 1688526077),
('m230809_061222_add_schema_column_to_knight_table', 1695289245),
('m230809_062025_add_events_column_to_knight_table', 1695289245),
('m231113_111257_add_limit_column_to_knight_table', 1700122163),
('m240419_060342_drop_limit_column_from_knight_table', 1713768937),
('m240419_060852_drop_schema_column_from_knight_table', 1713768937),
('m240419_080730_add_type_column_events_column_title_column_to_meta_table', 1713768937),
('m240419_085911_add_meta_id_column_to_meta_knight_table', 1713768937),
('m240419_092745_drop_verse_id_column_from_meta_table', 1713768938),
('m240421_013510_drop_type_column_from_meta_table', 1713768938),
('m240421_013700_add_custom_column_to_meta_table', 1713768938),
('m240423_100327_drop_event_node_id_column_from_meta_table', 1713944445),
('m240428_151401_add_uuid_column_to_verse_table', 1714323829),
('m240506_100238_create_vp_guide_table', 1714995955),
('m240521_084819_create_verse_meta_table', 1716286354),
('m240526_150028_add_prefab_column_to_meta_table', 1716774005),
('m240526_150406_drop_custom_column_from_meta_table', 1716774005),
('m240627_050132_create_multilanguage_verse_table', 1719479611),
('m240629_115854_create_vp_score_table', 1719663717),
('m240714_053542_create_vp_key_value_table', 1720945041),
('m240724_142709_create_vp_token_table', 1721961180),
('m240725_043603_create_vp_level_table', 1721961180),
('m240728_163216_create_vp_map_table', 1722225486),
('m240729_135707_add_name_column_to_vp_token_table', 1722322750),
('m240730_014752_add_map_id_column_to_vp_guide_table', 1722322750),
('m240807_024940_create_apple_id_table', 1723227191),
('m240809_150343_add_vp_token_id_column_to_apple_id_table', 1723227191),
('m240810_122437_drop_user_id_column_from_vp_token_table', 1725334939),
('m240826_025650_create_code_table', 1725965244),
('m240826_025712_create_verse_code_table', 1725965244),
('m240826_060729_create_meta_code_table', 1725965244),
('m240918_132316_drop_vp_token_id_column_from_apple_id_table', 1727241864),
('m240922_151125_create_verse_release_table', 1727579295),
('m240928_072721_create_ai_rodin_table', 1727579296),
('m240930_132147_add_generation_column_check_column_download_column_to_ai_rodin_table', 1728187704),
('m240930_132809_drop_status_column_token_column_from_ai_rodin_table', 1728187704),
('m241002_131644_drop_image_id_column_from_ai_rodin_table', 1728187704),
('m241002_131737_add_resource_id_column_to_ai_rodin_table', 1728187704),
('m241003_162635_add_query_column_to_ai_rodin_table', 1728187704),
('m241003_162716_drop_prompt_column_from_ai_rodin_table', 1728187704),
('m241004_161423_add_name_column_to_ai_rodin_table', 1728187704),
('m241022_034603_create_verse_tags_table', 1732712513),
('m241023_093206_drop_message_id_column_from_verse_open_table', 1732712513),
('m250214_112359_create_wechat_table', 1739547888),
('m250214_152626_add_token_column_to_wechat_table', 1739547889),
('m250215_165013_add_updated_at_column_to_wechat_table', 1739640011),
('m250316_092744_add_unionid_column_to_wechat_table', 1742117317),
('m250323_105342_create_verse_tags_table', 1742727609),
('m250323_123614_add_key_column_to_tags_table', 1742734737),
('m250323_133432_drop_meta_knight_table', 1742736894),
('m250323_133513_drop_verse_knight_table', 1742736925),
('m250323_133538_drop_knight_table', 1742736948),
('m250323_154154_drop_event_link_table', 1742744537),
('m250323_154235_drop_event_input_table', 1742744565),
('m250323_154303_drop_event_output_table', 1742744590),
('m250323_154436_drop_event_node_table', 1742744686),
('m250330_104436_add_type_column_to_tags_table', 1743331648),
('m250330_112524_drop_info_column_managed_column_from_tags_table', 1743333944),
('m250330_134002_create_snapshot_table', 1743400199),
('m250331_035815_add_description_column_to_verse_table', 1743400200),
('m250403_070034_drop_verse_space_table', 1743988943),
('m250403_070305_drop_verse_share_table', 1743988943),
('m250403_070330_drop_verse_release_table', 1743988943),
('m250403_070405_drop_verse_open_table', 1743988943),
('m250403_074932_drop_verse_event_table', 1743988943),
('m250403_075001_drop_verse_meta_table', 1743988943),
('m250403_075304_drop_order_table', 1743988943),
('m250403_075314_drop_trade_table', 1743988943),
('m250403_081252_drop_meta_rete_table', 1743988943),
('m250403_081306_drop_meta_resource_table', 1743988943),
('m250403_081324_drop_meta_event_table', 1743988943),
('m250403_081413_drop_message_tags_table', 1743988943),
('m250403_081523_drop_like_table', 1743988943),
('m250403_081612_drop_reply_table', 1743988943),
('m250403_081717_drop_message_table', 1743988943),
('m250403_081749_drop_file_store_table', 1743988943),
('m250403_081816_drop_cyber_script_table', 1743988943),
('m250403_081849_drop_cyber_table', 1743988943),
('m250404_084503_drop_name_column_description_column_image_column_author_id_column_type_column_from_snapshot_table', 1743988943),
('m250524_153707_create_device_table', 1759997235),
('m250611_121529_add_setup_column_active_column_to_device_table', 1759997235),
('m250730_061852_create_phototype_table', 1759997236),
('m250923_045542_add_resource_id_column_to_phototype_table', 1759997236),
('m250924_155940_add_type_column_to_phototype_table', 1759997236),
('m251001_104121_create_manager_table', 1759997236),
('m251001_141414_add_managers_column_to_snapshot_table', 1759997236),
('m251010_084227_create_user_linked_table', 1760089082),
('m251124_000001_alter_meta_code_blockly', 1763976246),
('m251124_000002_alter_code_lua', 1763977446),
('m251124_000003_alter_code_js', 1763977446),
('m251126_032535_create_edu_school_table', 1764129849),
('m251126_034617_create_edu_class_table', 1764129864);

-- --------------------------------------------------------

--
-- 表的结构 `multilanguage_verse`
--

CREATE TABLE `multilanguage_verse` (
  `id` int NOT NULL,
  `verse_id` int NOT NULL,
  `language` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `phototype`
--

CREATE TABLE `phototype` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `author_id` int NOT NULL,
  `data` json DEFAULT NULL,
  `schema` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `uuid` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `image_id` int DEFAULT NULL,
  `updater_id` int DEFAULT NULL,
  `resource_id` int DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `resource`
--

CREATE TABLE `resource` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `author_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_id` int NOT NULL,
  `image_id` int DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `updater_id` int DEFAULT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- 表的结构 `snapshot`
--

CREATE TABLE `snapshot` (
  `id` int NOT NULL,
  `verse_id` int NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `code` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `data` json DEFAULT NULL,
  `metas` json DEFAULT NULL,
  `resources` json DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `managers` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `space`
--

CREATE TABLE `space` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `author_id` int NOT NULL,
  `sample_id` int NOT NULL,
  `mesh_id` int NOT NULL,
  `dat_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `image_id` int DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- 表的结构 `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `type` enum('Domain','Classify','Status') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Classify'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `token`
--

CREATE TABLE `token` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `token` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `updated_at` datetime DEFAULT NULL,
  `overdue_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` smallint NOT NULL DEFAULT '10',
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  `verification_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `access_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `wx_openid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_info`
--

CREATE TABLE `user_info` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `avatar_id` int DEFAULT NULL,
  `gold` int NOT NULL DEFAULT '0',
  `points` int NOT NULL DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- 表的结构 `user_linked`
--

CREATE TABLE `user_linked` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `key` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse`
--

CREATE TABLE `verse` (
  `id` int NOT NULL,
  `author_id` int NOT NULL,
  `updater_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `image_id` int DEFAULT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `version` int DEFAULT '1'
) ;

-- --------------------------------------------------------

--
-- 表的结构 `verse_code`
--

CREATE TABLE `verse_code` (
  `id` int NOT NULL,
  `blockly` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `verse_id` int NOT NULL,
  `code_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse_script`
--

CREATE TABLE `verse_script` (
  `id` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `verse_id` int NOT NULL,
  `script` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `workspace` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verse_tags`
--

CREATE TABLE `verse_tags` (
  `id` int NOT NULL,
  `verse_id` int NOT NULL,
  `tags_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `vp_guide`
--

CREATE TABLE `vp_guide` (
  `id` int NOT NULL,
  `order` int DEFAULT NULL,
  `level_id` int NOT NULL,
  `map_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- 表的结构 `vp_key_value`
--

CREATE TABLE `vp_key_value` (
  `id` int NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ;

-- --------------------------------------------------------

--
-- 表的结构 `vp_level`
--

CREATE TABLE `vp_level` (
  `id` int NOT NULL,
  `player_id` int NOT NULL,
  `guide_id` int NOT NULL,
  `record` float DEFAULT NULL,
  `score` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `vp_map`
--

CREATE TABLE `vp_map` (
  `id` int NOT NULL,
  `page` int NOT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ;

-- --------------------------------------------------------

--
-- 表的结构 `vp_score`
--

CREATE TABLE `vp_score` (
  `id` int NOT NULL,
  `verse_id` int NOT NULL,
  `player_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `score` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `vp_token`
--

CREATE TABLE `vp_token` (
  `id` int NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wechat`
--

CREATE TABLE `wechat` (
  `id` int NOT NULL,
  `openid` varchar(255) NOT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `unionid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- 表的结构 `wx`
--

CREATE TABLE `wx` (
  `id` int NOT NULL,
  `wx_openid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `ai_rodin`
--
ALTER TABLE `ai_rodin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-ai_rodin-user_id` (`user_id`),
  ADD KEY `idx-ai_rodin-resource_id` (`resource_id`);

--
-- 表的索引 `apple_id`
--
ALTER TABLE `apple_id`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apple_id` (`apple_id`),
  ADD KEY `idx-apple_id-user_id` (`user_id`);

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
-- 表的索引 `code`
--
ALTER TABLE `code`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `device`
--
ALTER TABLE `device`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx-device-owner_id` (`owner_id`);

--
-- 表的索引 `edu_class`
--
ALTER TABLE `edu_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-edu_class-school_id` (`school_id`),
  ADD KEY `idx-edu_class-image_id` (`image_id`);

--
-- 表的索引 `edu_school`
--
ALTER TABLE `edu_school`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-edu_school-image_id` (`image_id`),
  ADD KEY `idx-edu_school-principal` (`principal`);

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
-- 表的索引 `local`
--
ALTER TABLE `local`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- 表的索引 `manager`
--
ALTER TABLE `manager`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-manager-verse_id` (`verse_id`);

--
-- 表的索引 `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent` (`parent`);

--
-- 表的索引 `meta`
--
ALTER TABLE `meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx-meta-author_id` (`author_id`),
  ADD KEY `idx-meta-updater_id` (`updater_id`),
  ADD KEY `idx-meta-image_id` (`image_id`);

--
-- 表的索引 `meta_code`
--
ALTER TABLE `meta_code`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meta_id` (`meta_id`),
  ADD UNIQUE KEY `code_id` (`code_id`),
  ADD KEY `idx-meta_code-meta_id` (`meta_id`),
  ADD KEY `idx-meta_code-code_id` (`code_id`);

--
-- 表的索引 `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- 表的索引 `multilanguage_verse`
--
ALTER TABLE `multilanguage_verse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-multilanguage_verse-verse_id` (`verse_id`);

--
-- 表的索引 `phototype`
--
ALTER TABLE `phototype`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `type` (`type`),
  ADD KEY `idx-phototype-author_id` (`author_id`),
  ADD KEY `idx-phototype-image_id` (`image_id`),
  ADD KEY `idx-phototype-updater_id` (`updater_id`),
  ADD KEY `idx-phototype-resource_id` (`resource_id`);

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
-- 表的索引 `snapshot`
--
ALTER TABLE `snapshot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-snapshot-verse_id` (`verse_id`),
  ADD KEY `idx-snapshot-created_by` (`created_by`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- 表的索引 `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
-- 表的索引 `user_linked`
--
ALTER TABLE `user_linked`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-user_linked-user_id` (`user_id`);

--
-- 表的索引 `verse`
--
ALTER TABLE `verse`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx-verse-author_id` (`author_id`),
  ADD KEY `idx-verse-updater_id` (`updater_id`),
  ADD KEY `idx-verse-image_id` (`image_id`);

--
-- 表的索引 `verse_code`
--
ALTER TABLE `verse_code`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verse_id` (`verse_id`),
  ADD UNIQUE KEY `code_id` (`code_id`),
  ADD KEY `idx-verse_code-verse_id` (`verse_id`),
  ADD KEY `idx-verse_code-code_id` (`code_id`);

--
-- 表的索引 `verse_script`
--
ALTER TABLE `verse_script`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx-verse_script-verse_id` (`verse_id`);

--
-- 表的索引 `verse_tags`
--
ALTER TABLE `verse_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-verse_tags-verse_id` (`verse_id`),
  ADD KEY `idx-verse_tags-tags_id` (`tags_id`);

--
-- 表的索引 `vp_guide`
--
ALTER TABLE `vp_guide`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level_id` (`level_id`),
  ADD KEY `idx-vp_guide-level_id` (`level_id`),
  ADD KEY `idx-vp_guide-map_id` (`map_id`);

--
-- 表的索引 `vp_key_value`
--
ALTER TABLE `vp_key_value`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- 表的索引 `vp_level`
--
ALTER TABLE `vp_level`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-vp_level-player_id` (`player_id`),
  ADD KEY `idx-vp_level-guide_id` (`guide_id`);

--
-- 表的索引 `vp_map`
--
ALTER TABLE `vp_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page` (`page`);

--
-- 表的索引 `vp_score`
--
ALTER TABLE `vp_score`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx-vp_score-verse_id` (`verse_id`);

--
-- 表的索引 `vp_token`
--
ALTER TABLE `vp_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD UNIQUE KEY `token` (`token`);

--
-- 表的索引 `wechat`
--
ALTER TABLE `wechat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `openid` (`openid`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `unionid` (`unionid`),
  ADD KEY `idx-wechat-user_id` (`user_id`);

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
-- 使用表AUTO_INCREMENT `ai_rodin`
--
ALTER TABLE `ai_rodin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `apple_id`
--
ALTER TABLE `apple_id`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `code`
--
ALTER TABLE `code`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `device`
--
ALTER TABLE `device`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `edu_class`
--
ALTER TABLE `edu_class`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `edu_school`
--
ALTER TABLE `edu_school`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `file`
--
ALTER TABLE `file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `info`
--
ALTER TABLE `info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `invitation`
--
ALTER TABLE `invitation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `local`
--
ALTER TABLE `local`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `manager`
--
ALTER TABLE `manager`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `meta`
--
ALTER TABLE `meta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `meta_code`
--
ALTER TABLE `meta_code`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `multilanguage_verse`
--
ALTER TABLE `multilanguage_verse`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `phototype`
--
ALTER TABLE `phototype`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `resource`
--
ALTER TABLE `resource`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `snapshot`
--
ALTER TABLE `snapshot`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `space`
--
ALTER TABLE `space`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `token`
--
ALTER TABLE `token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `user_info`
--
ALTER TABLE `user_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `user_linked`
--
ALTER TABLE `user_linked`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `verse`
--
ALTER TABLE `verse`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `verse_code`
--
ALTER TABLE `verse_code`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `verse_script`
--
ALTER TABLE `verse_script`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `verse_tags`
--
ALTER TABLE `verse_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `vp_guide`
--
ALTER TABLE `vp_guide`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `vp_key_value`
--
ALTER TABLE `vp_key_value`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `vp_level`
--
ALTER TABLE `vp_level`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `vp_map`
--
ALTER TABLE `vp_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `vp_score`
--
ALTER TABLE `vp_score`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `vp_token`
--
ALTER TABLE `vp_token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wechat`
--
ALTER TABLE `wechat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wx`
--
ALTER TABLE `wx`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `ai_rodin`
--
ALTER TABLE `ai_rodin`
  ADD CONSTRAINT `fk-ai_rodin-resource_id` FOREIGN KEY (`resource_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-ai_rodin-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `apple_id`
--
ALTER TABLE `apple_id`
  ADD CONSTRAINT `fk-apple_id-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

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
-- 限制表 `device`
--
ALTER TABLE `device`
  ADD CONSTRAINT `fk-device-owner_id` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `edu_class`
--
ALTER TABLE `edu_class`
  ADD CONSTRAINT `fk-edu_class-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-edu_class-school_id` FOREIGN KEY (`school_id`) REFERENCES `edu_school` (`id`) ON DELETE CASCADE;

--
-- 限制表 `edu_school`
--
ALTER TABLE `edu_school`
  ADD CONSTRAINT `fk-edu_school-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-edu_school-principal` FOREIGN KEY (`principal`) REFERENCES `user` (`id`) ON DELETE CASCADE;

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
-- 限制表 `manager`
--
ALTER TABLE `manager`
  ADD CONSTRAINT `fk-manager-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 限制表 `meta`
--
ALTER TABLE `meta`
  ADD CONSTRAINT `fk-meta-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `meta_code`
--
ALTER TABLE `meta_code`
  ADD CONSTRAINT `fk-meta_code-code_id` FOREIGN KEY (`code_id`) REFERENCES `code` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-meta_code-meta_id` FOREIGN KEY (`meta_id`) REFERENCES `meta` (`id`) ON DELETE CASCADE;

--
-- 限制表 `multilanguage_verse`
--
ALTER TABLE `multilanguage_verse`
  ADD CONSTRAINT `fk-multilanguage_verse-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `phototype`
--
ALTER TABLE `phototype`
  ADD CONSTRAINT `fk-phototype-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-phototype-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-phototype-resource_id` FOREIGN KEY (`resource_id`) REFERENCES `resource` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-phototype-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `resource`
--
ALTER TABLE `resource`
  ADD CONSTRAINT `fk-resource-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-resource-file_id` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-resource-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-resource-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `snapshot`
--
ALTER TABLE `snapshot`
  ADD CONSTRAINT `fk-snapshot-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-snapshot-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

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
-- 限制表 `user_linked`
--
ALTER TABLE `user_linked`
  ADD CONSTRAINT `fk-user_linked-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse`
--
ALTER TABLE `verse`
  ADD CONSTRAINT `fk-verse-author_id` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse-image_id` FOREIGN KEY (`image_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse-updater_id` FOREIGN KEY (`updater_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_code`
--
ALTER TABLE `verse_code`
  ADD CONSTRAINT `fk-verse_code-code_id` FOREIGN KEY (`code_id`) REFERENCES `code` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_code-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_script`
--
ALTER TABLE `verse_script`
  ADD CONSTRAINT `fk-verse_script-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verse_tags`
--
ALTER TABLE `verse_tags`
  ADD CONSTRAINT `fk-verse_tags-tags_id` FOREIGN KEY (`tags_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-verse_tags-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `vp_guide`
--
ALTER TABLE `vp_guide`
  ADD CONSTRAINT `fk-vp_guide-level_id` FOREIGN KEY (`level_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-vp_guide-map_id` FOREIGN KEY (`map_id`) REFERENCES `vp_map` (`id`) ON DELETE CASCADE;

--
-- 限制表 `vp_level`
--
ALTER TABLE `vp_level`
  ADD CONSTRAINT `fk-vp_level-guide_id` FOREIGN KEY (`guide_id`) REFERENCES `vp_guide` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-vp_level-player_id` FOREIGN KEY (`player_id`) REFERENCES `vp_token` (`id`) ON DELETE CASCADE;

--
-- 限制表 `vp_score`
--
ALTER TABLE `vp_score`
  ADD CONSTRAINT `fk-vp_score-verse_id` FOREIGN KEY (`verse_id`) REFERENCES `verse` (`id`) ON DELETE CASCADE;

--
-- 限制表 `wechat`
--
ALTER TABLE `wechat`
  ADD CONSTRAINT `fk-wechat-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `wx`
--
ALTER TABLE `wx`
  ADD CONSTRAINT `fk-wx-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
