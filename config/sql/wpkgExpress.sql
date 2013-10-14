#Wpkgexpress sql generated on: 2009-05-07 03:05:59 : 1241682539

DROP TABLE IF EXISTS `exit_codes`;
DROP TABLE IF EXISTS `hosts`;
DROP TABLE IF EXISTS `hosts_profiles`;
DROP TABLE IF EXISTS `package_actions`;
DROP TABLE IF EXISTS `package_checks`;
DROP TABLE IF EXISTS `packages`;
DROP TABLE IF EXISTS `packages_packages`;
DROP TABLE IF EXISTS `packages_profiles`;
DROP TABLE IF EXISTS `profiles`;
DROP TABLE IF EXISTS `profiles_profiles`;
DROP TABLE IF EXISTS `variables`;


CREATE TABLE `exit_codes` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`package_action_id` int(10) NOT NULL,
	`code` varchar(11) NOT NULL,
	`reboot` int(4) NOT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `hosts` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`enabled` tinyint(1) DEFAULT 1 NOT NULL,
	`name` varchar(100) NOT NULL,
	`notes` text DEFAULT NULL,
	`created` datetime DEFAULT NULL,
	`modified` datetime DEFAULT NULL,
	`mainprofile_id` int(10) NOT NULL,
	`position` int(5) DEFAULT 0 NOT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `hosts_profiles` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`host_id` int(10) NOT NULL,
	`profile_id` int(10) NOT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `package_actions` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`package_id` int(10) NOT NULL,
	`type` int(3) NOT NULL,
	`command` varchar(500) NOT NULL,
	`timeout` int(8) DEFAULT NULL,
	`workdir` varchar(500) DEFAULT NULL,
	`position` int(5) NOT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `package_checks` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`package_id` int(10) NOT NULL,
	`type` int(3) NOT NULL,
	`condition` int(3) NOT NULL,
	`path` varchar(200) DEFAULT NULL,
	`value` varchar(200) DEFAULT NULL,
	`parent_id` int(10) DEFAULT NULL,
	`lft` int(10) DEFAULT NULL,
	`rght` int(10) DEFAULT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `packages` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL,
	`id_text` varchar(100) NOT NULL,
	`enabled` tinyint(1) DEFAULT 1 NOT NULL,
	`revision` varchar(35) DEFAULT '0' NOT NULL,
	`priority` int(11) DEFAULT 0 NOT NULL,
	`reboot` tinyint(2) DEFAULT 0 NOT NULL,
	`execute` tinyint(2) DEFAULT 0 NOT NULL,
	`notify` tinyint(1) DEFAULT 0 NOT NULL,
	`notes` text DEFAULT NULL,
	`created` datetime DEFAULT NULL,
	`modified` datetime DEFAULT NULL,	PRIMARY KEY  (`id`),
	UNIQUE KEY id_text (`id_text`));

CREATE TABLE `packages_packages` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`package_id` int(10) NOT NULL,
	`dependency_id` int(10) NOT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `packages_profiles` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`profile_id` int(10) NOT NULL,
	`package_id` int(10) NOT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `profiles` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`enabled` tinyint(1) DEFAULT 1 NOT NULL,
	`id_text` varchar(100) NOT NULL,
	`notes` text DEFAULT NULL,
	`created` datetime DEFAULT NULL,
	`modified` datetime DEFAULT NULL,	PRIMARY KEY  (`id`),
	UNIQUE KEY id_text (`id_text`));

CREATE TABLE `profiles_profiles` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`profile_id` int(10) NOT NULL,
	`dependency_id` int(10) NOT NULL,	PRIMARY KEY  (`id`));

CREATE TABLE `variables` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`ref_id` int(11) NOT NULL,
	`ref_type` int(3) NOT NULL,
	`name` varchar(80) DEFAULT NULL,
	`value` varchar(500) DEFAULT NULL,	PRIMARY KEY  (`id`));

