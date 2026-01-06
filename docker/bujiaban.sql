-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： 10.206.0.5:3306
-- 生成日期： 2026-01-06 17:57:59
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
