-- phpMyAdmin SQL Dump
-- version 3.3.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 20, 2012 at 09:35 PM
-- Server version: 5.0.95
-- PHP Version: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `oss-flattraffic`
--

CREATE DATABASE `flattraffic` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `flattraffic`;



-- --------------------------------------------------------

--
-- Table structure for table `cache_rdns`
--

CREATE TABLE IF NOT EXISTS `cache_rdns` (
  `id` int(11) NOT NULL auto_increment,
  `ipaddress` varchar(64) NOT NULL,
  `reverse` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Cache of reverse DNS lookups' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `cache_rdns`
--


-- --------------------------------------------------------

--
-- Table structure for table `cache_traffic`
--

CREATE TABLE IF NOT EXISTS `cache_traffic` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_network` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `ipaddress` varchar(64) NOT NULL,
  `bytes_received` bigint(20) unsigned NOT NULL,
  `bytes_sent` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Cache space for traffic statistics' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `cache_traffic`
--


-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`name`, `value`) VALUES
('APP_MYSQL_DUMP', '/usr/bin/mysqldump'),
('APP_PDFLATEX', '/usr/bin/pdflatex'),
('AUTH_METHOD', 'sql'),
('BLACKLIST_ENABLE', 'enabled'),
('BLACKLIST_LIMIT', '10'),
('CACHE_TIME', '1349868327'),
('CACHE_TIMEOUT', '3600'),
('DATA_STORAGE_LOCATION', 'use_database'),
('DATA_STORAGE_METHOD', 'database'),
('DATEFORMAT', 'yyyy-mm-dd'),
('LANGUAGE_DEFAULT', 'en_us'),
('LANGUAGE_LOAD', 'preload'),
('PATH_TMPDIR', '/tmp'),
('PHONE_HOME', '1'),
('PHONE_HOME_TIMER', '1307153486'),
('SCHEMA_VERSION', '20110604'),
('SERVICE_TRAFFIC_DB_HOST', 'localhost'),
('SERVICE_TRAFFIC_DB_NAME', 'sample_netflow'),
('SERVICE_TRAFFIC_DB_PASSWORD', 'sample_password'),
('SERVICE_TRAFFIC_DB_TYPE', 'mysql_netflow_single'),
('SERVICE_TRAFFIC_DB_USERNAME', 'root'),
('STATS_INCLUDE_RDNS', '1'),
('STATS_INCLUDE_UNMATCHED', '0'),
('STATS_REPORT_OVERVIEW', '1'),
('STATS_REPORT_PERUSER', '1'),
('STATS_REPORT_RAW', '1'),
('SUBSCRIPTION_ID', ''),
('SUBSCRIPTION_SUPPORT', 'opensource'),
('TIMEZONE_DEFAULT', 'SYSTEM'),
('TRUNCATE_DB_LOCAL', '0'),
('TRUNCATE_DB_UNMATCHED', '0'),
('UPLOAD_MAXBYTES', '5242880'),
('UPSTREAM_BILLING_MODE', 'period_monthly'),
('UPSTREAM_BILLING_REPEAT_DATE', '16');

-- --------------------------------------------------------

--
-- Table structure for table `file_uploads`
--

CREATE TABLE IF NOT EXISTS `file_uploads` (
  `id` int(11) NOT NULL auto_increment,
  `customid` int(11) NOT NULL default '0',
  `type` varchar(20) NOT NULL,
  `timestamp` bigint(20) unsigned NOT NULL default '0',
  `file_name` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `file_location` char(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `file_uploads`
--


-- --------------------------------------------------------

--
-- Table structure for table `file_upload_data`
--

CREATE TABLE IF NOT EXISTS `file_upload_data` (
  `id` int(11) NOT NULL auto_increment,
  `fileid` int(11) NOT NULL default '0',
  `data` blob NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table for use as database-backed file storage system' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `file_upload_data`
--


-- --------------------------------------------------------

--
-- Table structure for table `journal`
--

CREATE TABLE IF NOT EXISTS `journal` (
  `id` int(11) NOT NULL auto_increment,
  `locked` tinyint(1) NOT NULL default '0',
  `journalname` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `userid` int(11) NOT NULL default '0',
  `customid` int(11) NOT NULL default '0',
  `timestamp` bigint(20) unsigned NOT NULL default '0',
  `content` text NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `journalname` (`journalname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `journal`
--


-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL auto_increment,
  `language` varchar(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `translation` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `language` (`language`),
  KEY `label` (`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=296 ;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES
(292, 'en_us', 'username_flattraffic', 'Username'),
(293, 'en_us', 'password_flattraffic', 'Password'),
(294, 'en_us', 'help_truncate_db_local', 'Removes local-to-local traffic flows from the raw DB. (increases performance on messy netflow DBs)'),
(295, 'en_us', 'help_truncate_db_unmatched', 'Removes unmatched traffic flows from the raw DB. (increases performance on messy netflow DBs)');

-- --------------------------------------------------------

--
-- Table structure for table `language_avaliable`
--

CREATE TABLE IF NOT EXISTS `language_avaliable` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `language_avaliable`
--

INSERT INTO `language_avaliable` (`id`, `name`) VALUES
(1, 'en_us');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL auto_increment,
  `priority` int(11) NOT NULL default '0',
  `parent` varchar(50) NOT NULL,
  `topic` varchar(50) NOT NULL,
  `link` varchar(50) NOT NULL,
  `permid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=189 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES
(170, 200, 'top', 'menu_reports', 'reports/reports.php', 3),
(171, 300, 'top', 'menu_networks', 'networks/networks.php', 2),
(172, 900, 'top', 'menu_admin', 'admin/admin.php', 2),
(173, 910, 'menu_admin', 'menu_admin_config', 'admin/config.php', 2),
(174, 920, 'menu_admin', 'menu_admin_users', 'user/users.php', 2),
(175, 921, 'menu_admin_users', '', 'user/user-view.php', 2),
(176, 921, 'menu_admin_users', '', 'user/user-permissions.php', 2),
(177, 921, 'menu_admin_users', '', 'user/user-delete.php', 2),
(178, 921, 'menu_admin_users', '', 'user/user-add.php', 2),
(179, 100, 'top', 'Overview', 'home.php', 0),
(180, 901, 'menu_admin', '', 'admin/admin.php', 2),
(181, 201, 'menu_reports', '', 'reports/reports.php', 3),
(182, 210, 'menu_reports', 'menu_reports_hosts', 'reports/reports-hosts.php', 3),
(183, 210, 'menu_reports', 'menu_reports_daily', 'reports/reports-daily.php', 3),
(184, 210, 'menu_reports', 'menu_reports_networks', 'reports/reports-networks.php', 3),
(185, 301, 'menu_networks', '', 'networks/networks.php', 2),
(186, 310, 'menu_networks', '', 'networks/view.php', 2),
(187, 310, 'menu_networks', '', 'networks/add.php', 2),
(188, 310, 'menu_networks', '', 'networks/delete.php', 2);

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE IF NOT EXISTS `networks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ipaddress` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `networks`
--

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL auto_increment,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores all the possible permissions' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `value`, `description`) VALUES
(1, 'disabled', 'Enabling the disabled permission will prevent the user from being able to login.'),
(2, 'admin', 'Provides access to user and configuration management features (note: any user with admin can provide themselves with access to any other section of this program)'),
(3, 'reports', 'Provides access to view usage & reports');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_salt` varchar(20) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `time` bigint(20) NOT NULL default '0',
  `ipaddress` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ipaddress` (`ipaddress`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='User authentication system.' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `realname`, `password`, `password_salt`, `contact_email`, `time`, `ipaddress`) VALUES
(1, 'setup', 'Setup Account', '14c2a5c3681b95582c3e01fc19f49853d9cdbb31', 'hctw8lbz3uhxl6sj8ixr', 'support@amberdms.com', 1350206317, 'fdd5:8ac:8ad2:101::1001');

-- --------------------------------------------------------

--
-- Table structure for table `users_blacklist`
--

CREATE TABLE IF NOT EXISTS `users_blacklist` (
  `id` int(11) NOT NULL auto_increment,
  `ipaddress` varchar(15) NOT NULL,
  `failedcount` int(11) NOT NULL default '0',
  `time` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Prevents automated login attacks.' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users_blacklist`
--


-- --------------------------------------------------------

--
-- Table structure for table `users_options`
--

CREATE TABLE IF NOT EXISTS `users_options` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=225 ;

--
-- Dumping data for table `users_options`
--

INSERT INTO `users_options` (`id`, `userid`, `name`, `value`) VALUES
(208, 3, 'lang', 'en_us'),
(209, 3, 'dateformat', 'yyyy-mm-dd'),
(210, 3, 'shrink_tableoptions', 'on'),
(211, 3, 'debug', 'on'),
(212, 3, 'concurrent_logins', 'on'),
(219, 1, 'lang', 'en_us'),
(220, 1, 'dateformat', 'dd-mm-yyyy'),
(221, 1, 'shrink_tableoptions', ''),
(222, 1, 'default_employeeid', ''),
(223, 1, 'debug', 'on'),
(224, 1, 'concurrent_logins', '');

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE IF NOT EXISTS `users_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `permid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user permissions.' AUTO_INCREMENT=6 ;

--
-- Dumping data for table `users_permissions`
--

INSERT INTO `users_permissions` (`id`, `userid`, `permid`) VALUES
(1, 1, 2),
(2, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users_sessions`
--

CREATE TABLE IF NOT EXISTS `users_sessions` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `authkey` varchar(40) NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `time` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users_sessions`
--

