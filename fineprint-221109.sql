/*
SQLyog Ultimate v12.5.1 (32 bit)
MySQL - 10.4.11-MariaDB : Database - webdir_sample
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `abilities` */

DROP TABLE IF EXISTS `abilities`;

CREATE TABLE `abilities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `only_owned` tinyint(1) NOT NULL DEFAULT 0,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `scope` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `abilities_scope_index` (`scope`)
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `abilities` */

/*Table structure for table `app_categories` */

DROP TABLE IF EXISTS `app_categories`;

CREATE TABLE `app_categories` (
  `app_id` int(11) NOT NULL,
  `category_id` smallint(6) NOT NULL,
  PRIMARY KEY (`app_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `app_categories_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `ref_app_categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_categories` */

/*Table structure for table `app_changelogs` */

DROP TABLE IF EXISTS `app_changelogs`;

CREATE TABLE `app_changelogs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `based_on_id` bigint(20) DEFAULT NULL COMMENT 'what version is this version compared to?',
  `version` int(11) NOT NULL DEFAULT 1,
  `diffs` text NOT NULL COMMENT 'json payload of differences',
  `comment` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'has it been verified by a verifier?',
  `status` enum('pending','approved','committed','rejected') NOT NULL DEFAULT 'pending' COMMENT 'pending = default, to be decided; approved = to be applied; committed = applied, rejected = not applied',
  `is_switch` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_id` (`app_id`,`version`),
  KEY `based_on_id` (`based_on_id`),
  CONSTRAINT `app_changelogs_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_changelogs_ibfk_2` FOREIGN KEY (`based_on_id`) REFERENCES `app_changelogs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=219 DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_changelogs` */

/*Table structure for table `app_report_categories` */

DROP TABLE IF EXISTS `app_report_categories`;

CREATE TABLE `app_report_categories` (
  `report_id` bigint(20) NOT NULL,
  `category_id` smallint(6) NOT NULL,
  PRIMARY KEY (`report_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `app_report_categories_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `app_reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_report_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `ref_app_report_categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_report_categories` */

/*Table structure for table `app_reports` */

DROP TABLE IF EXISTS `app_reports`;

CREATE TABLE `app_reports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `version_id` bigint(20) DEFAULT NULL,
  `verdict_id` int(11) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('submitted','validated','dropped') NOT NULL DEFAULT 'submitted',
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_id` (`app_id`),
  KEY `version_id` (`version_id`),
  KEY `user_id` (`user_id`),
  KEY `verdict_id` (`verdict_id`),
  CONSTRAINT `app_reports_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_reports_ibfk_2` FOREIGN KEY (`version_id`) REFERENCES `app_changelogs` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_reports_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_reports_ibfk_4` FOREIGN KEY (`verdict_id`) REFERENCES `app_verdicts` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_reports` */

/*Table structure for table `app_tags` */

DROP TABLE IF EXISTS `app_tags`;

CREATE TABLE `app_tags` (
  `app_id` int(11) NOT NULL,
  `tag` varchar(256) NOT NULL,
  PRIMARY KEY (`app_id`,`tag`),
  KEY `tag` (`tag`),
  CONSTRAINT `app_tags_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_tags_ibfk_2` FOREIGN KEY (`tag`) REFERENCES `ref_app_tags` (`name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_tags` */

/*Table structure for table `app_verdicts` */

DROP TABLE IF EXISTS `app_verdicts`;

CREATE TABLE `app_verdicts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `version_id` bigint(20) DEFAULT NULL,
  `verification_id` bigint(20) DEFAULT NULL,
  `status` enum('innocent','guilty') NOT NULL,
  `comments` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_id` (`app_id`),
  KEY `version_id` (`version_id`),
  KEY `verification_id` (`verification_id`),
  CONSTRAINT `app_verdicts_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `app_verdicts_ibfk_2` FOREIGN KEY (`version_id`) REFERENCES `app_changelogs` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `app_verdicts_ibfk_3` FOREIGN KEY (`verification_id`) REFERENCES `app_verifications` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_verdicts` */

/*Table structure for table `app_verification_changes` */

DROP TABLE IF EXISTS `app_verification_changes`;

CREATE TABLE `app_verification_changes` (
  `verification_id` bigint(11) NOT NULL,
  `changes_id` bigint(20) NOT NULL,
  PRIMARY KEY (`verification_id`,`changes_id`),
  KEY `changes_id` (`changes_id`),
  CONSTRAINT `app_verification_changes_ibfk_1` FOREIGN KEY (`verification_id`) REFERENCES `app_verifications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_verification_changes_ibfk_2` FOREIGN KEY (`changes_id`) REFERENCES `app_changelogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_verification_changes` */

/*Table structure for table `app_verifications` */

DROP TABLE IF EXISTS `app_verifications`;

CREATE TABLE `app_verifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `base_changes_id` bigint(20) DEFAULT NULL COMMENT 'the base version before being reviewed',
  `status_id` varchar(50) NOT NULL,
  `details` text DEFAULT NULL COMMENT 'json',
  `comment` text DEFAULT NULL,
  `concern` varchar(100) DEFAULT NULL,
  `verifier_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_verifications_ibfk_1` (`app_id`),
  KEY `app_verifications_ibfk_2` (`verifier_id`),
  KEY `status` (`status_id`),
  KEY `base_changes_id` (`base_changes_id`),
  CONSTRAINT `app_verifications_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_verifications_ibfk_2` FOREIGN KEY (`verifier_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_verifications_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `ref_verification_status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `app_verifications_ibfk_4` FOREIGN KEY (`base_changes_id`) REFERENCES `app_changelogs` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=217 DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_verifications` */

/*Table structure for table `app_visual_media` */

DROP TABLE IF EXISTS `app_visual_media`;

CREATE TABLE `app_visual_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `order` smallint(6) NOT NULL DEFAULT 99,
  `type` enum('logo','image','video') NOT NULL,
  `subtype` varchar(20) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `media_name` varchar(100) NOT NULL COMMENT 'file basename',
  `media_path` text NOT NULL,
  `media_small_name` varchar(100) DEFAULT NULL COMMENT 'file basename',
  `meta` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_thumbnails_ibfk_1` (`app_id`),
  CONSTRAINT `app_visual_media_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4;

/*Data for the table `app_visual_media` */

/*Table structure for table `apps` */

DROP TABLE IF EXISTS `apps`;

CREATE TABLE `apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) unsigned NOT NULL,
  `version_id` bigint(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `short_name` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL COMMENT 'accessible absolute url to the app home page',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_reported` tinyint(1) NOT NULL DEFAULT 0,
  `reported_at` timestamp NULL DEFAULT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `page_views` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `version_id` (`version_id`),
  CONSTRAINT `apps_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `apps_ibfk_3` FOREIGN KEY (`version_id`) REFERENCES `app_changelogs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4;

/*Data for the table `apps` */

/*Table structure for table `assigned_roles` */

DROP TABLE IF EXISTS `assigned_roles`;

CREATE TABLE `assigned_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restricted_to_id` int(10) unsigned DEFAULT NULL,
  `restricted_to_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assigned_roles_entity_index` (`entity_id`,`entity_type`,`scope`),
  KEY `assigned_roles_role_id_index` (`role_id`),
  KEY `assigned_roles_scope_index` (`scope`),
  CONSTRAINT `assigned_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `assigned_roles` */

/*Table structure for table `color_schemes` */

DROP TABLE IF EXISTS `color_schemes`;

CREATE TABLE `color_schemes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `chroma` enum('light','dark','both') NOT NULL,
  `colors` text DEFAULT NULL COMMENT 'comma-separated hex colors',
  `gradient_angle` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `faved` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4;

/*Data for the table `color_schemes` */

insert  into `color_schemes`(`id`,`name`,`chroma`,`colors`,`gradient_angle`,`description`,`notes`,`faved`) values 
(1,'begin color #ac32e4','both','#ac32e4, #7918f2, #4801ff','-135deg',NULL,NULL,0),
(2,'begin color #f77062','both','#f77062, #fe5196','-135deg',NULL,NULL,1),
(3,'begin color #7BD2AB','both','#7BD2AB, #3EADCF','-135deg',NULL,NULL,1),
(4,'begin color #F1A7F1','light','#F1A7F1, #FAD0C4','45deg',NULL,NULL,0),
(5,'begin color #F5E3E6','light','#F5E3E6, #D9E4F5','45deg',NULL,NULL,1),
(6,'begin color #D5D0E5','light','#D5D0E5, #F3E6E8','45deg',NULL,NULL,0),
(7,'begin color #A1BAFE','both','#A1BAFE, #8D5185','45deg',NULL,NULL,0),
(8,'begin color #F894A4','light','#F894A4, #F9D1B7','45deg',NULL,NULL,1),
(9,'Fragrant Clouds','light','#DDBDFC, #96C8FB','-135deg',NULL,NULL,1),
(10,'begin color #C58E7F','both','#C58E7F, #DFA375','45deg',NULL,NULL,0),
(11,'begin color #BDDCF1','light','#BDDCF1, #FDF86D','45deg',NULL,NULL,0),
(12,'begin color #FF9FF3','light','#FF9FF3, #D3D3D3','45deg',NULL,NULL,0),
(13,'begin color #F8DE7E','light','#F8DE7E, #D99058','45deg',NULL,NULL,0),
(14,'begin color #CBA36D','light','#CBA36D, #E2AC6B','45deg',NULL,NULL,0),
(15,'begin color #F79AD3','both','#F79AD3, #C86FC9','45deg',NULL,NULL,0),
(16,'begin color #FEB9A3','light','#FEB9A3, #FEA684','45deg',NULL,NULL,1),
(17,'begin color #FABC3C','light','#FABC3C, #FACC6B','45deg',NULL,NULL,0),
(18,'begin color #FFB4A2','light','#FFB4A2, #FFCDB2','45deg',NULL,NULL,1),
(19,'begin color #FCD181','light','#FCD181, #E79087','45deg',NULL,NULL,0),
(20,'begin color #ffbd77','light','#ffbd77 0%, #f0f4a4 37%, #acfcd9 100%','-25deg',NULL,NULL,0),
(21,'begin color #E0D2C7','light','#E0D2C7, #44B09E','-135deg',NULL,NULL,0),
(22,'begin color #D7816A','both','#D7816A, #BD4F6C','45deg',NULL,NULL,0),
(23,'begin color #B279A7','both','#B279A7, #D387AB','45deg',NULL,NULL,0),
(24,'Average S*x','light','#a88beb, #f8ceec','45deg',NULL,'https://www.eggradients.com/category/pastel-gradient',0),
(25,'Mirage','dark','#16222a, #3a6073','45deg',NULL,'https://uigradients.com/#Mirage',0),
(26,'Sea Weed','light','#4cb8c4, #3cd3ad','45deg',NULL,'https://uigradients.com/',1),
(27,'Aubergine','dark','#aa076b, #61045f','-135deg',NULL,'https://uigradients.com/',0),
(28,'Nimvelo','dark','#314755, #26a0da','45deg',NULL,'https://uigradients.com/',0),
(29,'Skyline','dark','#1488cc, #2b32b2','45deg',NULL,'https://uigradients.com/',0),
(30,'Royal Blue','dark','#536976, #292e49','45deg',NULL,'https://uigradients.com/',1),
(31,'Moonlit Asteroid','dark','#0f2027, #203a43, #2c5364','45deg',NULL,'https://uigradients.com/',1),
(32,'Amin','dark','#8e2de2, #4a00e0','45deg',NULL,'https://uigradients.com/',1),
(33,'Azur Lane','light','#7f7fd5, #86a8e7, #91eae4','45deg',NULL,'https://uigradients.com/',0),
(34,'Blue Raspberry','both','#00b4db, #0083b0','45deg',NULL,'https://uigradients.com/',1),
(35,'eXpresso','dark','#ad5389, #3c1053','-135deg',NULL,'https://uigradients.com/',0),
(36,'Lawrencium','dark','#0f0c29, #302b63, #24243e','45deg',NULL,'https://uigradients.com/',1),
(37,'Digital Water','light','#74ebd5, #acb6e5','45deg',NULL,'https://uigradients.com/',0),
(38,'Kimoby is the New Blue','dark','#396afc, #2948ff','-135deg',NULL,'https://uigradients.com/',0),
(39,'Subu','light','#0cebeb, #20e3b2, #29ffc6','45deg',NULL,'https://uigradients.com/',0),
(40,'Socialive','both','#06beb6, #48b1bf','45deg',NULL,'https://uigradients.com/',0),
(41,'Crimson Tide','dark','#642b73, #c6426e','45deg',NULL,'https://uigradients.com/',0),
(42,'Scooter','both','#36d1dc, #5b86e5','-135deg',NULL,'https://uigradients.com/',0),
(43,'Cinnamint','light','#4ac29a, #bdfff3','45deg',NULL,'https://uigradients.com/',0),
(44,'Venice','light','#6190e8, #a7bfe8','45deg',NULL,'https://uigradients.com/',1),
(45,'Dark - Deep Blue','dark','#000428, #004e92','45deg',NULL,'https://uigradients.com/',1),
(46,'Royal','dark','#141e30, #243b55','45deg',NULL,'https://uigradients.com/',1),
(47,'Nighthawk','dark','#2c3e50, #4ca1af','45deg',NULL,'https://uigradients.com/',0),
(48,'Decent','light','#4ca1af, #c4e0e5','45deg',NULL,'https://uigradients.com/',1),
(49,'Dark Skies','dark','#4b79a1, #283e51','-135deg',NULL,'https://uigradients.com/',0),
(50,'Joomla','dark','#1e3c72, #2a5298','45deg',NULL,'https://uigradients.com/',0),
(51,'Pale Wood','light','#eacda3, #d6ae7b','45deg',NULL,'https://uigradients.com/',0),
(52,'Inbox','both','#457fca, #5691c8','45deg',NULL,'https://uigradients.com/',1),
(53,'Blush','both','#b24592, #f15f79','45deg',NULL,'https://uigradients.com/',1),
(54,'Little Leaf','both','#76b852, #8dc26f','45deg',NULL,'https://uigradients.com/',0),
(55,'Clear Sky','dark','#005c97, #363795','45deg',NULL,'https://uigradients.com/',1),
(56,'Passion','both','#e53935, #e35d5b','45deg',NULL,'https://uigradients.com/',0),
(57,'Piggy Pink','light','#ee9ca7, #ffdde1','45deg',NULL,'https://uigradients.com/',0),
(58,'Turqoise Flow','dark','#136a8a, #267871','45deg',NULL,'https://uigradients.com/',1),
(59,'Clear Sky (REVERSE)','dark','#005c97, #363795','-135deg',NULL,'https://uigradients.com/',1);

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `fileponds` */

DROP TABLE IF EXISTS `fileponds`;

CREATE TABLE `fileponds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mimetypes` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `fileponds` */

/*Table structure for table `jobs` */

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `jobs` */

/*Table structure for table `log_actions` */

DROP TABLE IF EXISTS `log_actions`;

CREATE TABLE `log_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(256) NOT NULL,
  `entity_id` varchar(256) NOT NULL,
  `related_type` varchar(256) DEFAULT NULL,
  `related_id` varchar(256) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `data` text DEFAULT NULL COMMENT 'additional data/payload important to the transaction',
  `actor_id` bigint(20) DEFAULT NULL,
  `actor_name` varchar(255) DEFAULT NULL COMMENT 'could contain the name of an internal system component',
  `at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `log_actions` */

/*Table structure for table `log_user_plans` */

DROP TABLE IF EXISTS `log_user_plans`;

CREATE TABLE `log_user_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `plan` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `log_user_plans_ibfk_1` (`user_id`),
  CONSTRAINT `log_user_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `log_user_plans` */

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'2014_10_12_000000_create_users_table',1),
(2,'2014_10_12_100000_create_password_resets_table',1),
(3,'2019_08_19_000000_create_failed_jobs_table',1),
(4,'2020_04_07_174001_create_bouncer_tables',1),
(5,'2020_06_22_101212_create_jobs_table',2),
(6,'2020_06_22_101317_create_notifications_table',2),
(7,'2022_05_04_000000_create_fileponds_table',3);

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `notifications` */

/*Table structure for table `password_resets` */

DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `password_resets` */

/*Table structure for table `permissions` */

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ability_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forbidden` tinyint(1) NOT NULL DEFAULT 0,
  `scope` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permissions_entity_index` (`entity_id`,`entity_type`,`scope`),
  KEY `permissions_ability_id_index` (`ability_id`),
  KEY `permissions_scope_index` (`scope`),
  CONSTRAINT `permissions_ability_id_foreign` FOREIGN KEY (`ability_id`) REFERENCES `abilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=568 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `permissions` */

/*Table structure for table `ref_app_categories` */

DROP TABLE IF EXISTS `ref_app_categories`;

CREATE TABLE `ref_app_categories` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `slug` varchar(256) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ref_app_categories` */

/*Table structure for table `ref_app_report_categories` */

DROP TABLE IF EXISTS `ref_app_report_categories`;

CREATE TABLE `ref_app_report_categories` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` text DEFAULT NULL,
  `order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ref_app_report_categories` */

insert  into `ref_app_report_categories`(`id`,`name`,`description`,`order`,`created_at`,`updated_at`,`deleted_at`) values 
(1,'racist/discrimination','racist/discrimination_description',0,NULL,NULL,NULL),
(2,'spam','spam_description',0,NULL,NULL,NULL),
(3,'porn/suggestive_content','porn/suggestive_content_description',0,NULL,NULL,NULL),
(4,'plagiarism','plagiarism_description',0,NULL,NULL,NULL),
(5,'fraud/scam','fraud/scam_description',0,NULL,NULL,NULL),
(6,'others','others_description',99,NULL,NULL,NULL);

/*Table structure for table `ref_app_tags` */

DROP TABLE IF EXISTS `ref_app_tags`;

CREATE TABLE `ref_app_tags` (
  `name` varchar(256) NOT NULL,
  `slug` varchar(256) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `ref_app_tags` */

/*Table structure for table `ref_prodi` */

DROP TABLE IF EXISTS `ref_prodi`;

CREATE TABLE `ref_prodi` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ref_prodi` */

insert  into `ref_prodi`(`id`,`name`,`short_name`,`slug`,`description`,`created_at`,`updated_at`,`deleted_at`) values 
(1,'Pendidikan Teknik Informatika','PTI','pendidikan-teknik-informatika','pend tekn ik ifnormatika jenajngaya S1 uhuy\nkampusnya di lptk\nuny deket gejayan\ndi belakang sendiri kompleks fakutlsas teknik','2022-10-07 19:12:45','2022-10-07 19:12:45',NULL),
(2,'Pendidikan Teknik Elektronika',NULL,'pendidikan-teknik-elektronika','gwrng wr\ng jrwekg pwrlg\naw rgknwr gnawr g\\\nwr gikwrn g;wr g\nwr kolgnwr kngwr\n gawrnk','2022-10-18 22:57:57','2022-10-18 22:57:57',NULL),
(3,'Pend teknx qwe','PTQ','pend-teknx-qwe',NULL,'2022-10-12 18:47:25','2022-10-12 18:47:25','2022-10-12 18:47:25');

/*Table structure for table `ref_verification_status` */

DROP TABLE IF EXISTS `ref_verification_status`;

CREATE TABLE `ref_verification_status` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `by` enum('editor','verifier') NOT NULL,
  `description` text DEFAULT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT 99 COMMENT 'approximate ascending order of events',
  `icon` varchar(50) DEFAULT NULL,
  `bg_style` varchar(30) DEFAULT NULL,
  `style` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `ref_verification_status` */

insert  into `ref_verification_status`(`id`,`name`,`by`,`description`,`order`,`icon`,`bg_style`,`style`,`created_at`,`updated_at`,`deleted_at`) values 
('applied','Changes Applied','editor',NULL,85,'fas fa-check','success','white',NULL,NULL,NULL),
('approved','Submission Approved','verifier',NULL,80,'fas fa-check','success','white',NULL,NULL,NULL),
('banned','Banned','verifier',NULL,95,'fas fa-ban','danger','white',NULL,NULL,NULL),
('deleted','Deleted','editor',NULL,97,'fas fa-trash','dark','white',NULL,NULL,NULL),
('published','Published','editor',NULL,90,'fas fa-check','success','white',NULL,NULL,NULL),
('rejected','Rejected','verifier',NULL,65,'fas fa-ban','danger','white',NULL,NULL,NULL),
('restored','Restored','verifier',NULL,99,'fas fa-recycle','info','black',NULL,NULL,NULL),
('resubmitted','Resubmitted','editor',NULL,68,'fas fa-redo-alt','warning','black',NULL,NULL,NULL),
('reviewing','In Review','verifier',NULL,15,'fas fa-user-check','warning','black',NULL,NULL,NULL),
('revised','Revised','editor',NULL,30,'fas fa-spell-check','primary','white',NULL,NULL,NULL),
('revising','Revising','editor',NULL,25,'far fa-edit','primary','white',NULL,NULL,NULL),
('revision-needed','Needs Revision','verifier',NULL,20,'far fa-comments','warning','black',NULL,NULL,NULL),
('unlisted','Unlisted','verifier',NULL,93,'fas fa-ban','danger','white',NULL,NULL,NULL),
('unverified','Unverified','editor',NULL,10,'far fa-lightbulb','secondary','white',NULL,NULL,NULL);

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` int(10) unsigned DEFAULT NULL,
  `scope` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`,`scope`),
  KEY `roles_scope_index` (`scope`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `roles` */

/*Table structure for table `settings` */

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` text NOT NULL COMMENT 'please write the description of what this settings key does, or what values are allowed',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `settings` */

insert  into `settings`(`key`,`value`,`description`) values 
('app.categories.range','1-5','(int range) how many categories an app must/can have'),
('app.creation_needs_verification','true','(boolean) whether a new app needs verification before becoming public'),
('app.description_limit','10000','(int) character limit for an app\'s description'),
('app.listing.per_page','20','(int) how many items are displayed per page in the frontend app listing page'),
('app.logo_resize','[300,300]','(int array) logo upload will be resized to this size in the format of [width, height]. \r\nSet to null for no resize.'),
('app.modification_needs_verification','true','(boolean) whether any edit/modification of an app needs verification to be in place, i.e pending'),
('app.reports.reason_limit','1000','(int) character limit for an app report\'s reason'),
('app.tags.range','0-10','(int range) how many tags an app must/can have'),
('app.verification.auto_commit_upon_approval','false','(boolean) whether an app is automatically published by the system instead of needing user review upon an approval verification'),
('app.visuals.caption_limit','500','(int) character limit for each visuals\' caption'),
('app.visuals.image_small_size','[750,420]','(int array) visuals image upload will be resized to this size in the format of [width, height]. \r\nSet to null for no resize.'),
('app.visuals.max_amount','10','(int) how many visual_media per app that can be displayed'),
('app.visuals.max_size','2048','(int) visuals max size in KBs'),
('user.profile.picture_small_size','[200,200]','(int array) user profile picture upload will be resized to this size in the format of [width, height]. \r\nSet to null for no resize.');

/*Table structure for table `stat_app_likes` */

DROP TABLE IF EXISTS `stat_app_likes`;

CREATE TABLE `stat_app_likes` (
  `app_id` int(11) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `value` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 for like, <1 for dislike (e.g 0 or -1)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`app_id`,`user_id`),
  CONSTRAINT `stat_app_likes_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `stat_app_likes` */

/*Table structure for table `stat_app_showcases` */

DROP TABLE IF EXISTS `stat_app_showcases`;

CREATE TABLE `stat_app_showcases` (
  `app_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `visits` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`app_id`),
  CONSTRAINT `stat_app_showcases_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `stat_app_showcases` */

/*Table structure for table `stat_apps` */

DROP TABLE IF EXISTS `stat_apps`;

CREATE TABLE `stat_apps` (
  `app_id` int(11) NOT NULL,
  `visits` bigint(20) unsigned NOT NULL DEFAULT 0,
  `unique_visits` bigint(20) unsigned NOT NULL DEFAULT 0,
  `clicks` bigint(20) unsigned NOT NULL DEFAULT 0,
  `searches` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`app_id`),
  CONSTRAINT `stat_apps_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `stat_apps` */

/*Table structure for table `system_users` */

DROP TABLE IF EXISTS `system_users`;

CREATE TABLE `system_users` (
  `user_id` bigint(20) unsigned NOT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  `username` varchar(256) NOT NULL,
  `password` text DEFAULT NULL,
  `domain` varchar(256) DEFAULT NULL,
  `prefix` varchar(256) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `uid` (`uid`),
  CONSTRAINT `system_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `system_users` */

/*Table structure for table `user_blocks` */

DROP TABLE IF EXISTS `user_blocks`;

CREATE TABLE `user_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `reason` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_blocks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;

/*Data for the table `user_blocks` */

/*Table structure for table `user_plan_changes` */

DROP TABLE IF EXISTS `user_plan_changes`;

CREATE TABLE `user_plan_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `plan` varchar(100) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_plan_changes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `user_plan_changes` */

/*Table structure for table `user_plan_changes_verifications` */

DROP TABLE IF EXISTS `user_plan_changes_verifications`;

CREATE TABLE `user_plan_changes_verifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `change_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `reason` text DEFAULT NULL,
  `verificator_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_id` (`change_id`),
  KEY `verificator_id` (`verificator_id`),
  CONSTRAINT `user_plan_changes_verifications_ibfk_1` FOREIGN KEY (`change_id`) REFERENCES `user_plan_changes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `user_plan_changes_verifications_ibfk_2` FOREIGN KEY (`verificator_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `user_plan_changes_verifications` */

/*Table structure for table `user_plans` */

DROP TABLE IF EXISTS `user_plans`;

CREATE TABLE `user_plans` (
  `user_id` bigint(20) unsigned NOT NULL,
  `plan` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `user_plans` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prodi_id` smallint(6) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `lang` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `picture` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity` enum('user','system') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `prodi_id` (`prodi_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
