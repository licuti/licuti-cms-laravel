-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: licuti-cms-laravel
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `banner_translations`
--

DROP TABLE IF EXISTS `banner_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banner_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_translations_banner_id_locale_unique` (`banner_id`,`locale`),
  CONSTRAINT `banner_translations_banner_id_foreign` FOREIGN KEY (`banner_id`) REFERENCES `banners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banner_translations`
--

LOCK TABLES `banner_translations` WRITE;
/*!40000 ALTER TABLE `banner_translations` DISABLE KEYS */;
INSERT INTO `banner_translations` VALUES (3,2,'vi','Khuyến mãi Test mùa hè','0',NULL,'2026-09-18 06:43:59','2026-09-18 06:43:59');
/*!40000 ALTER TABLE `banner_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home_slider',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `display_order` int NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banners_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'6bbea783-2804-4b5c-823b-4c8c87535af9','home_banner_1','https://example.com','_self',5,NULL,NULL,1,'2026-09-18 05:44:35','2026-09-18 05:44:42','2026-09-18 05:44:42'),(2,'7d109ae3-737c-4b34-8d01-740221b8fa9a','home_slider',NULL,'_self',0,NULL,NULL,1,'2026-09-18 06:43:59','2026-09-18 06:43:59',NULL);
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brand_translations`
--

DROP TABLE IF EXISTS `brand_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brand_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_translations_brand_id_locale_unique` (`brand_id`,`locale`),
  KEY `brand_translations_slug_index` (`slug`),
  CONSTRAINT `brand_translations_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand_translations`
--

LOCK TABLES `brand_translations` WRITE;
/*!40000 ALTER TABLE `brand_translations` DISABLE KEYS */;
INSERT INTO `brand_translations` VALUES (2,2,'vi','E2E Brand Smoke UPD','e2e-brand-smoke',NULL,'2026-09-18 05:45:07','2026-09-18 05:45:11'),(3,2,'en','E2E Brand Smoke','e2e-brand-smoke',NULL,'2026-09-18 05:45:11','2026-09-18 05:45:11');
/*!40000 ALTER TABLE `brand_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (2,'37eaf81f-ac8d-4814-bc76-a6cbc05c4787',NULL,'https://example.com',1,7,'2026-09-18 05:45:07','2026-09-18 05:45:14','2026-09-18 05:45:14'),(3,'ea55dd1d-001a-4f6f-af46-2e79d2935af1',NULL,NULL,1,0,'2026-09-29 04:09:00','2026-09-29 04:09:00',NULL);
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-app_settings','a:22:{s:9:\"site_name\";a:2:{s:2:\"vi\";s:10:\"Licuti CMS\";s:2:\"en\";s:10:\"Licuti CMS\";}s:6:\"slogan\";a:2:{s:2:\"vi\";s:40:\"Nền tảng thương mại điện tử\";s:2:\"en\";s:19:\"E-commerce platform\";}s:8:\"logo_url\";s:0:\"\";s:11:\"favicon_url\";s:0:\"\";s:13:\"contact_email\";s:18:\"contact@licuti.com\";s:13:\"contact_phone\";s:10:\"0123456789\";s:15:\"contact_address\";a:2:{s:2:\"vi\";s:21:\"Hà Nội, Việt Nam\";s:2:\"en\";s:14:\"Hanoi, Vietnam\";}s:14:\"seo_meta_title\";a:2:{s:2:\"vi\";s:24:\"Licuti CMS - Trang chủ\";s:2:\"en\";s:17:\"Licuti CMS - Home\";}s:20:\"seo_meta_description\";a:2:{s:2:\"vi\";s:43:\"Hệ thống quản trị nội dung Licuti\";s:2:\"en\";s:32:\"Licuti content management system\";}s:19:\"google_analytics_id\";s:0:\"\";s:15:\"social_facebook\";s:21:\"https://facebook.com/\";s:14:\"social_youtube\";s:20:\"https://youtube.com/\";s:13:\"primary_color\";s:7:\"#3b82f6\";s:16:\"footer_copyright\";a:2:{s:2:\"vi\";s:40:\"© 2026 Licuti CMS. All rights reserved.\";s:2:\"en\";s:40:\"© 2026 Licuti CMS. All rights reserved.\";}s:11:\"mail_mailer\";s:4:\"smtp\";s:9:\"mail_host\";s:24:\"sandbox.smtp.mailtrap.io\";s:9:\"mail_port\";s:4:\"2525\";s:13:\"mail_username\";s:0:\"\";s:13:\"mail_password\";s:0:\"\";s:15:\"mail_encryption\";s:3:\"tls\";s:17:\"mail_from_address\";s:19:\"no-reply@licuti.com\";s:14:\"mail_from_name\";s:13:\"Licuti System\";}',2109768929),('laravel-cache-languages_active','O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:4:\"name\";s:10:\"Vietnamese\";s:11:\"native_name\";s:14:\"Tiếng Việt\";s:4:\"flag\";N;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:4:\"name\";s:10:\"Vietnamese\";s:11:\"native_name\";s:14:\"Tiếng Việt\";s:4:\"flag\";N;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:11:\"native_name\";i:3;s:4:\"flag\";i:4;s:10:\"is_default\";i:5;s:9:\"is_active\";i:6;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:11:\"native_name\";s:7:\"English\";s:4:\"flag\";N;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:11:\"native_name\";s:7:\"English\";s:4:\"flag\";N;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:11:\"native_name\";i:3;s:4:\"flag\";i:4;s:10:\"is_default\";i:5;s:9:\"is_active\";i:6;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}',2109768929),('laravel-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:210:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"users.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"users.view\";s:1:\"c\";s:3:\"api\";}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:17:\"users.view-detail\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:17:\"users.view-detail\";s:1:\"c\";s:3:\"api\";}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"users.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"users.create\";s:1:\"c\";s:3:\"api\";}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"users.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:12:\"users.update\";s:1:\"c\";s:3:\"api\";}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"users.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:3:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"users.delete\";s:1:\"c\";s:3:\"api\";}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:13:\"users.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:3:{s:1:\"a\";i:12;s:1:\"b\";s:13:\"users.restore\";s:1:\"c\";s:3:\"api\";}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:18:\"users.force-delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:3:{s:1:\"a\";i:14;s:1:\"b\";s:18:\"users.force-delete\";s:1:\"c\";s:3:\"api\";}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:12:\"users.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:3:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"users.export\";s:1:\"c\";s:3:\"api\";}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:12:\"users.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:3:{s:1:\"a\";i:18;s:1:\"b\";s:12:\"users.import\";s:1:\"c\";s:3:\"api\";}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:10:\"roles.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:3:{s:1:\"a\";i:20;s:1:\"b\";s:10:\"roles.view\";s:1:\"c\";s:3:\"api\";}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:12:\"roles.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:3:{s:1:\"a\";i:22;s:1:\"b\";s:12:\"roles.create\";s:1:\"c\";s:3:\"api\";}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:12:\"roles.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:3:{s:1:\"a\";i:24;s:1:\"b\";s:12:\"roles.update\";s:1:\"c\";s:3:\"api\";}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:12:\"roles.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:3:{s:1:\"a\";i:26;s:1:\"b\";s:12:\"roles.delete\";s:1:\"c\";s:3:\"api\";}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:16:\"permissions.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:3:{s:1:\"a\";i:28;s:1:\"b\";s:16:\"permissions.view\";s:1:\"c\";s:3:\"api\";}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:18:\"permissions.assign\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:29;a:3:{s:1:\"a\";i:30;s:1:\"b\";s:18:\"permissions.assign\";s:1:\"c\";s:3:\"api\";}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:18:\"permissions.revoke\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:31;a:3:{s:1:\"a\";i:32;s:1:\"b\";s:18:\"permissions.revoke\";s:1:\"c\";s:3:\"api\";}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:15:\"categories.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:33;a:3:{s:1:\"a\";i:34;s:1:\"b\";s:15:\"categories.view\";s:1:\"c\";s:3:\"api\";}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:17:\"categories.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:35;a:3:{s:1:\"a\";i:36;s:1:\"b\";s:17:\"categories.create\";s:1:\"c\";s:3:\"api\";}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:17:\"categories.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:37;a:3:{s:1:\"a\";i:38;s:1:\"b\";s:17:\"categories.update\";s:1:\"c\";s:3:\"api\";}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:17:\"categories.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:3:{s:1:\"a\";i:40;s:1:\"b\";s:17:\"categories.delete\";s:1:\"c\";s:3:\"api\";}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:11:\"brands.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:41;a:3:{s:1:\"a\";i:42;s:1:\"b\";s:11:\"brands.view\";s:1:\"c\";s:3:\"api\";}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:13:\"brands.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:3:{s:1:\"a\";i:44;s:1:\"b\";s:13:\"brands.create\";s:1:\"c\";s:3:\"api\";}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:13:\"brands.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:45;a:3:{s:1:\"a\";i:46;s:1:\"b\";s:13:\"brands.update\";s:1:\"c\";s:3:\"api\";}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:13:\"brands.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:47;a:3:{s:1:\"a\";i:48;s:1:\"b\";s:13:\"brands.delete\";s:1:\"c\";s:3:\"api\";}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:13:\"products.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:49;a:3:{s:1:\"a\";i:50;s:1:\"b\";s:13:\"products.view\";s:1:\"c\";s:3:\"api\";}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:20:\"products.view-detail\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:51;a:3:{s:1:\"a\";i:52;s:1:\"b\";s:20:\"products.view-detail\";s:1:\"c\";s:3:\"api\";}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:15:\"products.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:53;a:3:{s:1:\"a\";i:54;s:1:\"b\";s:15:\"products.create\";s:1:\"c\";s:3:\"api\";}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:15:\"products.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:55;a:3:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"products.update\";s:1:\"c\";s:3:\"api\";}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:15:\"products.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:57;a:3:{s:1:\"a\";i:58;s:1:\"b\";s:15:\"products.delete\";s:1:\"c\";s:3:\"api\";}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:16:\"products.publish\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:59;a:3:{s:1:\"a\";i:60;s:1:\"b\";s:16:\"products.publish\";s:1:\"c\";s:3:\"api\";}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:16:\"products.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:61;a:3:{s:1:\"a\";i:62;s:1:\"b\";s:16:\"products.approve\";s:1:\"c\";s:3:\"api\";}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:15:\"products.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:63;a:3:{s:1:\"a\";i:64;s:1:\"b\";s:15:\"products.export\";s:1:\"c\";s:3:\"api\";}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:15:\"products.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:65;a:3:{s:1:\"a\";i:66;s:1:\"b\";s:15:\"products.import\";s:1:\"c\";s:3:\"api\";}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:11:\"orders.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:67;a:3:{s:1:\"a\";i:68;s:1:\"b\";s:11:\"orders.view\";s:1:\"c\";s:3:\"api\";}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:15:\"orders.view-all\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:69;a:3:{s:1:\"a\";i:70;s:1:\"b\";s:15:\"orders.view-all\";s:1:\"c\";s:3:\"api\";}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:18:\"orders.view-detail\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:71;a:3:{s:1:\"a\";i:72;s:1:\"b\";s:18:\"orders.view-detail\";s:1:\"c\";s:3:\"api\";}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:13:\"orders.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:73;a:3:{s:1:\"a\";i:74;s:1:\"b\";s:13:\"orders.update\";s:1:\"c\";s:3:\"api\";}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:13:\"orders.cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:75;a:3:{s:1:\"a\";i:76;s:1:\"b\";s:13:\"orders.cancel\";s:1:\"c\";s:3:\"api\";}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:13:\"orders.refund\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:77;a:3:{s:1:\"a\";i:78;s:1:\"b\";s:13:\"orders.refund\";s:1:\"c\";s:3:\"api\";}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:13:\"orders.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:79;a:3:{s:1:\"a\";i:80;s:1:\"b\";s:13:\"orders.export\";s:1:\"c\";s:3:\"api\";}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:14:\"inventory.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:81;a:3:{s:1:\"a\";i:82;s:1:\"b\";s:14:\"inventory.view\";s:1:\"c\";s:3:\"api\";}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:16:\"inventory.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:83;a:3:{s:1:\"a\";i:84;s:1:\"b\";s:16:\"inventory.import\";s:1:\"c\";s:3:\"api\";}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:16:\"inventory.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:85;a:3:{s:1:\"a\";i:86;s:1:\"b\";s:16:\"inventory.export\";s:1:\"c\";s:3:\"api\";}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:16:\"inventory.adjust\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:87;a:3:{s:1:\"a\";i:88;s:1:\"b\";s:16:\"inventory.adjust\";s:1:\"c\";s:3:\"api\";}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:12:\"coupons.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:89;a:3:{s:1:\"a\";i:90;s:1:\"b\";s:12:\"coupons.view\";s:1:\"c\";s:3:\"api\";}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:14:\"coupons.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:91;a:3:{s:1:\"a\";i:92;s:1:\"b\";s:14:\"coupons.create\";s:1:\"c\";s:3:\"api\";}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:14:\"coupons.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:93;a:3:{s:1:\"a\";i:94;s:1:\"b\";s:14:\"coupons.update\";s:1:\"c\";s:3:\"api\";}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:14:\"coupons.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:95;a:3:{s:1:\"a\";i:96;s:1:\"b\";s:14:\"coupons.delete\";s:1:\"c\";s:3:\"api\";}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:10:\"posts.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:97;a:3:{s:1:\"a\";i:98;s:1:\"b\";s:10:\"posts.view\";s:1:\"c\";s:3:\"api\";}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:12:\"posts.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:99;a:3:{s:1:\"a\";i:100;s:1:\"b\";s:12:\"posts.create\";s:1:\"c\";s:3:\"api\";}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:12:\"posts.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:101;a:3:{s:1:\"a\";i:102;s:1:\"b\";s:12:\"posts.update\";s:1:\"c\";s:3:\"api\";}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:12:\"posts.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:103;a:3:{s:1:\"a\";i:104;s:1:\"b\";s:12:\"posts.delete\";s:1:\"c\";s:3:\"api\";}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:13:\"posts.publish\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:105;a:3:{s:1:\"a\";i:106;s:1:\"b\";s:13:\"posts.publish\";s:1:\"c\";s:3:\"api\";}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:10:\"pages.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:107;a:3:{s:1:\"a\";i:108;s:1:\"b\";s:10:\"pages.view\";s:1:\"c\";s:3:\"api\";}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:12:\"pages.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:109;a:3:{s:1:\"a\";i:110;s:1:\"b\";s:12:\"pages.create\";s:1:\"c\";s:3:\"api\";}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:12:\"pages.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:111;a:3:{s:1:\"a\";i:112;s:1:\"b\";s:12:\"pages.update\";s:1:\"c\";s:3:\"api\";}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:12:\"pages.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:113;a:3:{s:1:\"a\";i:114;s:1:\"b\";s:12:\"pages.delete\";s:1:\"c\";s:3:\"api\";}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:12:\"banners.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:115;a:3:{s:1:\"a\";i:116;s:1:\"b\";s:12:\"banners.view\";s:1:\"c\";s:3:\"api\";}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:14:\"banners.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:117;a:3:{s:1:\"a\";i:118;s:1:\"b\";s:14:\"banners.create\";s:1:\"c\";s:3:\"api\";}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:14:\"banners.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:119;a:3:{s:1:\"a\";i:120;s:1:\"b\";s:14:\"banners.update\";s:1:\"c\";s:3:\"api\";}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:14:\"banners.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:121;a:3:{s:1:\"a\";i:122;s:1:\"b\";s:14:\"banners.delete\";s:1:\"c\";s:3:\"api\";}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:10:\"media.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:123;a:3:{s:1:\"a\";i:124;s:1:\"b\";s:10:\"media.view\";s:1:\"c\";s:3:\"api\";}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:12:\"media.upload\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:125;a:3:{s:1:\"a\";i:126;s:1:\"b\";s:12:\"media.upload\";s:1:\"c\";s:3:\"api\";}i:126;a:4:{s:1:\"a\";i:127;s:1:\"b\";s:12:\"media.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:127;a:3:{s:1:\"a\";i:128;s:1:\"b\";s:12:\"media.delete\";s:1:\"c\";s:3:\"api\";}i:128;a:4:{s:1:\"a\";i:129;s:1:\"b\";s:13:\"reports.sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:129;a:3:{s:1:\"a\";i:130;s:1:\"b\";s:13:\"reports.sales\";s:1:\"c\";s:3:\"api\";}i:130;a:4:{s:1:\"a\";i:131;s:1:\"b\";s:17:\"reports.inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:131;a:3:{s:1:\"a\";i:132;s:1:\"b\";s:17:\"reports.inventory\";s:1:\"c\";s:3:\"api\";}i:132;a:4:{s:1:\"a\";i:133;s:1:\"b\";s:17:\"reports.customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:133;a:3:{s:1:\"a\";i:134;s:1:\"b\";s:17:\"reports.customers\";s:1:\"c\";s:3:\"api\";}i:134;a:4:{s:1:\"a\";i:135;s:1:\"b\";s:14:\"reports.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:135;a:3:{s:1:\"a\";i:136;s:1:\"b\";s:14:\"reports.export\";s:1:\"c\";s:3:\"api\";}i:136;a:4:{s:1:\"a\";i:137;s:1:\"b\";s:13:\"settings.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:137;a:3:{s:1:\"a\";i:138;s:1:\"b\";s:13:\"settings.view\";s:1:\"c\";s:3:\"api\";}i:138;a:4:{s:1:\"a\";i:139;s:1:\"b\";s:15:\"settings.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:139;a:3:{s:1:\"a\";i:140;s:1:\"b\";s:15:\"settings.update\";s:1:\"c\";s:3:\"api\";}i:140;a:4:{s:1:\"a\";i:141;s:1:\"b\";s:16:\"settings.general\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:141;a:3:{s:1:\"a\";i:142;s:1:\"b\";s:16:\"settings.general\";s:1:\"c\";s:3:\"api\";}i:142;a:4:{s:1:\"a\";i:143;s:1:\"b\";s:16:\"settings.payment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:143;a:3:{s:1:\"a\";i:144;s:1:\"b\";s:16:\"settings.payment\";s:1:\"c\";s:3:\"api\";}i:144;a:4:{s:1:\"a\";i:145;s:1:\"b\";s:14:\"settings.email\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:145;a:3:{s:1:\"a\";i:146;s:1:\"b\";s:14:\"settings.email\";s:1:\"c\";s:3:\"api\";}i:146;a:4:{s:1:\"a\";i:147;s:1:\"b\";s:12:\"settings.sms\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:147;a:3:{s:1:\"a\";i:148;s:1:\"b\";s:12:\"settings.sms\";s:1:\"c\";s:3:\"api\";}i:148;a:4:{s:1:\"a\";i:149;s:1:\"b\";s:9:\"logs.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:149;a:3:{s:1:\"a\";i:150;s:1:\"b\";s:9:\"logs.view\";s:1:\"c\";s:3:\"api\";}i:150;a:4:{s:1:\"a\";i:151;s:1:\"b\";s:10:\"logs.clear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:151;a:3:{s:1:\"a\";i:152;s:1:\"b\";s:10:\"logs.clear\";s:1:\"c\";s:3:\"api\";}i:152;a:4:{s:1:\"a\";i:153;s:1:\"b\";s:12:\"admin.access\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:153;a:3:{s:1:\"a\";i:154;s:1:\"b\";s:12:\"admin.access\";s:1:\"c\";s:3:\"api\";}i:154;a:4:{s:1:\"a\";i:155;s:1:\"b\";s:20:\"post-categories.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:155;a:3:{s:1:\"a\";i:156;s:1:\"b\";s:20:\"post-categories.view\";s:1:\"c\";s:3:\"api\";}i:156;a:4:{s:1:\"a\";i:157;s:1:\"b\";s:22:\"post-categories.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:157;a:3:{s:1:\"a\";i:158;s:1:\"b\";s:22:\"post-categories.create\";s:1:\"c\";s:3:\"api\";}i:158;a:4:{s:1:\"a\";i:159;s:1:\"b\";s:22:\"post-categories.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:159;a:3:{s:1:\"a\";i:160;s:1:\"b\";s:22:\"post-categories.update\";s:1:\"c\";s:3:\"api\";}i:160;a:4:{s:1:\"a\";i:161;s:1:\"b\";s:22:\"post-categories.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:161;a:3:{s:1:\"a\";i:162;s:1:\"b\";s:22:\"post-categories.delete\";s:1:\"c\";s:3:\"api\";}i:162;a:4:{s:1:\"a\";i:163;s:1:\"b\";s:9:\"tags.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:163;a:3:{s:1:\"a\";i:164;s:1:\"b\";s:9:\"tags.view\";s:1:\"c\";s:3:\"api\";}i:164;a:4:{s:1:\"a\";i:165;s:1:\"b\";s:11:\"tags.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:165;a:3:{s:1:\"a\";i:166;s:1:\"b\";s:11:\"tags.create\";s:1:\"c\";s:3:\"api\";}i:166;a:4:{s:1:\"a\";i:167;s:1:\"b\";s:11:\"tags.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:167;a:3:{s:1:\"a\";i:168;s:1:\"b\";s:11:\"tags.update\";s:1:\"c\";s:3:\"api\";}i:168;a:4:{s:1:\"a\";i:169;s:1:\"b\";s:11:\"tags.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:169;a:3:{s:1:\"a\";i:170;s:1:\"b\";s:11:\"tags.delete\";s:1:\"c\";s:3:\"api\";}i:170;a:4:{s:1:\"a\";i:171;s:1:\"b\";s:10:\"menus.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:171;a:3:{s:1:\"a\";i:172;s:1:\"b\";s:10:\"menus.view\";s:1:\"c\";s:3:\"api\";}i:172;a:4:{s:1:\"a\";i:173;s:1:\"b\";s:12:\"menus.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:173;a:3:{s:1:\"a\";i:174;s:1:\"b\";s:12:\"menus.create\";s:1:\"c\";s:3:\"api\";}i:174;a:4:{s:1:\"a\";i:175;s:1:\"b\";s:12:\"menus.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:175;a:3:{s:1:\"a\";i:176;s:1:\"b\";s:12:\"menus.update\";s:1:\"c\";s:3:\"api\";}i:176;a:4:{s:1:\"a\";i:177;s:1:\"b\";s:12:\"menus.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:177;a:3:{s:1:\"a\";i:178;s:1:\"b\";s:12:\"menus.delete\";s:1:\"c\";s:3:\"api\";}i:178;a:4:{s:1:\"a\";i:179;s:1:\"b\";s:23:\"product-attributes.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:179;a:3:{s:1:\"a\";i:180;s:1:\"b\";s:23:\"product-attributes.view\";s:1:\"c\";s:3:\"api\";}i:180;a:4:{s:1:\"a\";i:181;s:1:\"b\";s:25:\"product-attributes.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:181;a:3:{s:1:\"a\";i:182;s:1:\"b\";s:25:\"product-attributes.create\";s:1:\"c\";s:3:\"api\";}i:182;a:4:{s:1:\"a\";i:183;s:1:\"b\";s:25:\"product-attributes.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:183;a:3:{s:1:\"a\";i:184;s:1:\"b\";s:25:\"product-attributes.update\";s:1:\"c\";s:3:\"api\";}i:184;a:4:{s:1:\"a\";i:185;s:1:\"b\";s:25:\"product-attributes.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:185;a:3:{s:1:\"a\";i:186;s:1:\"b\";s:25:\"product-attributes.delete\";s:1:\"c\";s:3:\"api\";}i:186;a:4:{s:1:\"a\";i:187;s:1:\"b\";s:20:\"product-reviews.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:187;a:3:{s:1:\"a\";i:188;s:1:\"b\";s:20:\"product-reviews.view\";s:1:\"c\";s:3:\"api\";}i:188;a:4:{s:1:\"a\";i:189;s:1:\"b\";s:22:\"product-reviews.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:189;a:3:{s:1:\"a\";i:190;s:1:\"b\";s:22:\"product-reviews.update\";s:1:\"c\";s:3:\"api\";}i:190;a:4:{s:1:\"a\";i:191;s:1:\"b\";s:22:\"product-reviews.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:191;a:3:{s:1:\"a\";i:192;s:1:\"b\";s:22:\"product-reviews.delete\";s:1:\"c\";s:3:\"api\";}i:192;a:3:{s:1:\"a\";i:193;s:1:\"b\";s:11:\"themes.view\";s:1:\"c\";s:3:\"web\";}i:193;a:3:{s:1:\"a\";i:194;s:1:\"b\";s:14:\"themes.preview\";s:1:\"c\";s:3:\"web\";}i:194;a:3:{s:1:\"a\";i:195;s:1:\"b\";s:15:\"themes.activate\";s:1:\"c\";s:3:\"web\";}i:195;a:3:{s:1:\"a\";i:196;s:1:\"b\";s:13:\"themes.update\";s:1:\"c\";s:3:\"web\";}i:196;a:3:{s:1:\"a\";i:197;s:1:\"b\";s:11:\"themes.view\";s:1:\"c\";s:3:\"api\";}i:197;a:3:{s:1:\"a\";i:198;s:1:\"b\";s:14:\"themes.preview\";s:1:\"c\";s:3:\"api\";}i:198;a:3:{s:1:\"a\";i:199;s:1:\"b\";s:15:\"themes.activate\";s:1:\"c\";s:3:\"api\";}i:199;a:3:{s:1:\"a\";i:200;s:1:\"b\";s:13:\"themes.update\";s:1:\"c\";s:3:\"api\";}i:200;a:3:{s:1:\"a\";i:201;s:1:\"b\";s:19:\"content-blocks.view\";s:1:\"c\";s:3:\"web\";}i:201;a:3:{s:1:\"a\";i:202;s:1:\"b\";s:21:\"content-blocks.create\";s:1:\"c\";s:3:\"web\";}i:202;a:3:{s:1:\"a\";i:203;s:1:\"b\";s:21:\"content-blocks.update\";s:1:\"c\";s:3:\"web\";}i:203;a:3:{s:1:\"a\";i:204;s:1:\"b\";s:21:\"content-blocks.delete\";s:1:\"c\";s:3:\"web\";}i:204;a:3:{s:1:\"a\";i:205;s:1:\"b\";s:22:\"content-blocks.publish\";s:1:\"c\";s:3:\"web\";}i:205;a:3:{s:1:\"a\";i:206;s:1:\"b\";s:19:\"content-blocks.view\";s:1:\"c\";s:3:\"api\";}i:206;a:3:{s:1:\"a\";i:207;s:1:\"b\";s:21:\"content-blocks.create\";s:1:\"c\";s:3:\"api\";}i:207;a:3:{s:1:\"a\";i:208;s:1:\"b\";s:21:\"content-blocks.update\";s:1:\"c\";s:3:\"api\";}i:208;a:3:{s:1:\"a\";i:209;s:1:\"b\";s:21:\"content-blocks.delete\";s:1:\"c\";s:3:\"api\";}i:209;a:3:{s:1:\"a\";i:210;s:1:\"b\";s:22:\"content-blocks.publish\";s:1:\"c\";s:3:\"api\";}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super-admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:6:\"editor\";s:1:\"c\";s:3:\"web\";}}}',1795705410),('laravel-cache-theme_settings.default','a:5:{s:11:\"brand_label\";s:14:\"Licuti Journal\";s:12:\"accent_color\";s:7:\"#d9f05b\";s:10:\"hero_title\";s:44:\"Những ý tưởng đáng để lưu lại.\";s:16:\"hero_description\";s:114:\"Góc nhìn, câu chuyện và những điều hữu ích được tuyển chọn cho hành trình kinh doanh số.\";s:14:\"posts_per_page\";i:9;}',2110692738);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `line_key` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price_snapshot` decimal(15,2) NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_line_key_unique` (`cart_id`,`line_key`),
  UNIQUE KEY `cart_items_uuid_unique` (`uuid`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  KEY `cart_items_product_id_index` (`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `coupon_id` bigint unsigned DEFAULT NULL,
  `guest_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `converted_order_id` bigint unsigned DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_uuid_unique` (`uuid`),
  KEY `carts_coupon_id_foreign` (`coupon_id`),
  KEY `carts_user_id_status_index` (`user_id`,`status`),
  KEY `carts_status_last_activity_at_index` (`status`,`last_activity_at`),
  CONSTRAINT `carts_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_uuid_unique` (`uuid`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'8b17ade2-b312-47ef-bd32-ac6bd963cf85',NULL,NULL,NULL,1,0,'2026-09-29 04:09:00','2026-09-29 04:09:00');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_translations`
--

DROP TABLE IF EXISTS `category_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_translations_category_id_locale_unique` (`category_id`,`locale`),
  KEY `category_translations_locale_index` (`locale`),
  CONSTRAINT `category_translations_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_translations`
--

LOCK TABLES `category_translations` WRITE;
/*!40000 ALTER TABLE `category_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_block_translations`
--

DROP TABLE IF EXISTS `content_block_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_block_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content_block_id` bigint unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_block_translations_content_block_id_locale_unique` (`content_block_id`,`locale`),
  KEY `content_block_translations_locale_slug_index` (`locale`,`slug`),
  CONSTRAINT `content_block_translations_content_block_id_foreign` FOREIGN KEY (`content_block_id`) REFERENCES `content_blocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_block_translations`
--

LOCK TABLES `content_block_translations` WRITE;
/*!40000 ALTER TABLE `content_block_translations` DISABLE KEYS */;
INSERT INTO `content_block_translations` VALUES (1,1,'vi','Hero giới thiệu Licuti','hero-gioi-thieu-licuti','Khối nội dung mẫu dùng cho hero trên trang chủ.','<section class=\"content-block content-block--hero\"><p class=\"eyebrow\">LICUTI CMS</p><h2>Xây storefront theo cách của riêng bạn.</h2><p>Nội dung này được quản lý từ module Khối nội dung và có thể tái sử dụng trong theme.</p><a href=\"/posts\" class=\"btn btn-primary\">Khám phá bài viết</a></section>','2026-11-22 15:09:47','2026-11-22 15:09:47'),(2,1,'en','Licuti introduction hero','licuti-introduction-hero','A sample content block for the homepage hero.','<section class=\"content-block content-block--hero\"><p class=\"eyebrow\">LICUTI CMS</p><h2>Build your storefront your way.</h2><p>This content is managed from the Content Blocks module and can be reused by a theme.</p><a href=\"/posts\" class=\"btn btn-primary\">Explore the journal</a></section>','2026-11-22 15:09:47','2026-11-22 15:09:47'),(3,2,'vi','Ba giá trị của Licuti','ba-gia-tri-cua-licuti','Khối mẫu dạng ba cột để giới thiệu giá trị thương hiệu.','<section class=\"content-block content-block--values\"><div class=\"row g-4\"><div class=\"col-md-4\"><h3>01. Linh hoạt</h3><p>Theme tự do trình bày dữ liệu chuẩn từ hệ thống.</p></div><div class=\"col-md-4\"><h3>02. Rõ ràng</h3><p>Nội dung được quản lý tập trung, không rải trong template.</p></div><div class=\"col-md-4\"><h3>03. Mở rộng</h3><p>Khối có thể được tái sử dụng khi xây Homepage Builder.</p></div></div></section>','2026-11-22 15:09:47','2026-11-22 15:09:47'),(4,2,'en','Three Licuti values','three-licuti-values','A sample three-column block for brand values.','<section class=\"content-block content-block--values\"><div class=\"row g-4\"><div class=\"col-md-4\"><h3>01. Flexible</h3><p>The theme is free to present standardized system data.</p></div><div class=\"col-md-4\"><h3>02. Clear</h3><p>Content is managed centrally instead of being scattered in templates.</p></div><div class=\"col-md-4\"><h3>03. Extensible</h3><p>Blocks can be reused when the Homepage Builder arrives.</p></div></div></section>','2026-11-22 15:09:47','2026-11-22 15:09:47'),(5,3,'vi','CTA đăng ký nhận tin','cta-dang-ky-nhan-tin','Khối nháp để thử nghiệm lời kêu gọi hành động.','<section class=\"content-block content-block--cta\"><h2>Nhận những cập nhật mới nhất.</h2><p>Đăng ký để không bỏ lỡ các bài viết và tính năng mới của Licuti.</p><a href=\"/contact\" class=\"btn btn-dark\">Đăng ký nhận tin</a></section>','2026-11-22 15:09:47','2026-11-22 15:09:47'),(6,3,'en','Newsletter signup CTA','newsletter-signup-cta','A draft block for testing a call to action.','<section class=\"content-block content-block--cta\"><h2>Get the latest updates.</h2><p>Subscribe to follow new Licuti articles and features.</p><a href=\"/contact\" class=\"btn btn-dark\">Subscribe</a></section>','2026-11-22 15:09:47','2026-11-22 15:09:47');
/*!40000 ALTER TABLE `content_block_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_blocks`
--

DROP TABLE IF EXISTS `content_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `image` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_blocks_uuid_unique` (`uuid`),
  KEY `content_blocks_status_index` (`status`),
  KEY `content_blocks_image_foreign` (`image`),
  CONSTRAINT `content_blocks_image_foreign` FOREIGN KEY (`image`) REFERENCES `media` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_blocks`
--

LOCK TABLES `content_blocks` WRITE;
/*!40000 ALTER TABLE `content_blocks` DISABLE KEYS */;
INSERT INTO `content_blocks` VALUES (1,'10000000-0000-4000-8000-000000000001','published','2026-11-19 15:09:59',NULL,'2026-11-22 15:09:47','2026-11-22 15:09:59',NULL),(2,'10000000-0000-4000-8000-000000000002','published','2026-11-21 15:09:59',NULL,'2026-11-22 15:09:47','2026-11-22 15:09:59',NULL),(3,'10000000-0000-4000-8000-000000000003','draft',NULL,NULL,'2026-11-22 15:09:47','2026-11-22 15:09:47',NULL);
/*!40000 ALTER TABLE `content_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_users`
--

DROP TABLE IF EXISTS `coupon_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `guest_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `discount_amount` decimal(15,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupon_users_user_id_foreign` (`user_id`),
  KEY `coupon_users_coupon_id_user_id_index` (`coupon_id`,`user_id`),
  KEY `coupon_users_coupon_id_guest_email_index` (`coupon_id`,`guest_email`),
  CONSTRAINT `coupon_users_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_users`
--

LOCK TABLES `coupon_users` WRITE;
/*!40000 ALTER TABLE `coupon_users` DISABLE KEYS */;
INSERT INTO `coupon_users` VALUES (1,1,1,NULL,NULL,35000.00,'2026-10-08 11:26:36','2026-10-10 11:26:36','2026-10-10 11:26:36');
/*!40000 ALTER TABLE `coupon_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `value` decimal(15,2) NOT NULL,
  `min_order_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `max_discount_amount` decimal(15,2) DEFAULT NULL,
  `usage_limit` int unsigned DEFAULT NULL,
  `usage_per_user` int unsigned DEFAULT NULL,
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `cannot_combine_with_sale_items` tinyint(1) NOT NULL DEFAULT '0',
  `stackable` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_uuid_unique` (`uuid`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `coupons_code_index` (`code`),
  KEY `coupons_is_active_start_date_end_date_index` (`is_active`,`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'8eeba93f-425e-41b0-b06f-001b8c83f660','WELCOME10','Giảm 10% cho khách hàng mới','Áp dụng cho đơn hàng đầu tiên từ 100.000đ, giảm tối đa 50.000đ.','percent',10.00,100000.00,50000.00,500,1,5,'2026-09-30 11:26:36','2027-01-10 11:26:36',1,1,0,'2026-10-10 11:26:36','2026-10-10 11:26:36',NULL),(2,'441e5c59-1bf9-48b7-a0be-d3868ef2c2d9','FREESHIP','Miễn phí vận chuyển toàn quốc','Áp dụng cho đơn hàng từ 250.000đ.','free_shipping',0.00,250000.00,30000.00,1000,3,12,'2026-10-05 11:26:36','2027-04-10 11:26:36',1,0,1,'2026-10-10 11:26:36','2026-10-10 11:26:36',NULL),(3,'aaa16f49-13ca-4040-ba83-189a6dcfc0e5','GIAM50K','Giảm trực tiếp 50.000đ','Giảm ngay 50.000đ cho đơn hàng từ 300.000đ.','fixed',50000.00,300000.00,50000.00,100,1,2,'2026-10-08 11:26:36','2026-10-30 11:26:36',1,1,0,'2026-10-10 11:26:36','2026-10-10 11:26:36',NULL),(4,'d6d700ef-7ff7-4bd1-bdc6-36c8ccf2ceb2','VIP20','Ưu đãi VIP 20%','Dành cho khách hàng VIP, giảm tối đa 200.000đ cho đơn từ 500.000đ.','percent',20.00,500000.00,200000.00,50,2,0,'2026-10-09 11:26:36','2026-11-10 11:26:36',1,0,1,'2026-10-10 11:26:36','2026-10-10 11:26:36',NULL),(5,'b32c8950-ea23-46ab-a78f-754cf5e1a549','EXPIRED2025','Mã giảm giá đã hết hạn','Mã khuyến mãi chương trình cuối năm ngoái.','fixed',30000.00,150000.00,30000.00,200,1,195,'2026-04-10 11:26:36','2026-09-10 11:26:36',1,0,0,'2026-10-10 11:26:36','2026-10-10 11:26:36',NULL),(6,'6174f38d-7b99-49dc-9ff1-fd6736d0a953','TATHOATDONG','Mã tạm ngưng áp dụng','Tạm ngừng chương trình khuyến mãi.','percent',15.00,200000.00,60000.00,50,1,0,'2026-10-09 11:26:36','2026-11-09 11:26:36',0,1,0,'2026-10-10 11:26:36','2026-10-10 11:26:36',NULL);
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_sale_products`
--

DROP TABLE IF EXISTS `flash_sale_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash_sale_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flash_sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `variant_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flash_price` decimal(15,2) unsigned NOT NULL,
  `quantity_limit` int unsigned NOT NULL,
  `sold_quantity` int unsigned NOT NULL DEFAULT '0',
  `limit_per_user` smallint unsigned NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flash_sale_products_flash_sale_id_variant_key_unique` (`flash_sale_id`,`variant_key`),
  KEY `flash_sale_products_product_id_foreign` (`product_id`),
  KEY `flash_sale_products_product_variant_id_foreign` (`product_variant_id`),
  KEY `flash_sale_products_flash_sale_id_product_id_index` (`flash_sale_id`,`product_id`),
  CONSTRAINT `flash_sale_products_flash_sale_id_foreign` FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `flash_sale_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `flash_sale_products_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sale_products`
--

LOCK TABLES `flash_sale_products` WRITE;
/*!40000 ALTER TABLE `flash_sale_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `flash_sale_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_sale_redemptions`
--

DROP TABLE IF EXISTS `flash_sale_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash_sale_redemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flash_sale_product_id` bigint unsigned NOT NULL,
  `order_item_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `guest_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved',
  `released_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `flash_sale_redemptions_order_item_id_foreign` (`order_item_id`),
  KEY `flash_sale_redemptions_user_id_foreign` (`user_id`),
  KEY `idx_redemptions_user` (`flash_sale_product_id`,`user_id`,`status`),
  KEY `idx_redemptions_guest` (`flash_sale_product_id`,`guest_email`,`status`),
  CONSTRAINT `flash_sale_redemptions_flash_sale_product_id_foreign` FOREIGN KEY (`flash_sale_product_id`) REFERENCES `flash_sale_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `flash_sale_redemptions_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `flash_sale_redemptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sale_redemptions`
--

LOCK TABLES `flash_sale_redemptions` WRITE;
/*!40000 ALTER TABLE `flash_sale_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `flash_sale_redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_sale_translations`
--

DROP TABLE IF EXISTS `flash_sale_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash_sale_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flash_sale_id` bigint unsigned NOT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flash_sale_translations_flash_sale_id_locale_unique` (`flash_sale_id`,`locale`),
  KEY `flash_sale_translations_locale_index` (`locale`),
  CONSTRAINT `flash_sale_translations_flash_sale_id_foreign` FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sale_translations`
--

LOCK TABLES `flash_sale_translations` WRITE;
/*!40000 ALTER TABLE `flash_sale_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `flash_sale_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_sales`
--

DROP TABLE IF EXISTS `flash_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash_sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flash_sales_uuid_unique` (`uuid`),
  KEY `flash_sales_created_by_foreign` (`created_by`),
  KEY `idx_flash_sales_window` (`is_active`,`starts_at`,`ends_at`),
  KEY `flash_sales_starts_at_index` (`starts_at`),
  KEY `flash_sales_ends_at_index` (`ends_at`),
  KEY `flash_sales_is_active_index` (`is_active`),
  CONSTRAINT `flash_sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sales`
--

LOCK TABLES `flash_sales` WRITE;
/*!40000 ALTER TABLE `flash_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `flash_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventories`
--

DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventories`
--

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'vi','Vietnamese','Tiếng Việt',NULL,1,1,1,'2026-08-12 06:40:01','2026-08-12 06:40:01'),(2,'en','English','English',NULL,0,1,2,'2026-08-12 06:40:01','2026-08-12 06:40:01');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `folder_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `disk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL DEFAULT '0',
  `alt_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_folder_id_foreign` (`folder_id`),
  KEY `media_user_id_foreign` (`user_id`),
  CONSTRAINT `media_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `media_folders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (14,'639c9f41-835b-4d59-9e45-c7ddbcde6f71',NULL,1,'public','media/original/20260804_162248_6a7211d8adc99.jpg','media/thumb/20260804_162248_6a7211d8adc99.webp','20260804_162248_6a7211d8adc99.jpg','gallery-3.jpg','image/jpeg',145862,NULL,'2026-08-04 09:22:49','2026-10-01 03:25:45'),(15,'6ce75093-2364-4e73-ac95-d58248b16ea9',NULL,1,'public','media/original/20260804_162250_6a7211da10a58.jpg','media/thumb/20260804_162250_6a7211da10a58.webp','20260804_162250_6a7211da10a58.jpg','gallery-2.jpg','image/jpeg',196596,NULL,'2026-08-04 09:22:50','2026-10-01 03:25:46'),(16,'69949088-439e-45a8-86e6-9920cf338175',NULL,1,'public','media/original/20260804_162250_6a7211da5f898.jpg','media/thumb/20260804_162250_6a7211da5f898.webp','20260804_162250_6a7211da5f898.jpg','gallery-1.jpg','image/jpeg',308709,NULL,'2026-08-04 09:22:50','2026-10-01 03:25:47'),(17,'28c3ba24-c212-4128-a908-0489a3cec1f6',NULL,1,'public','media/original/20260804_162250_6a7211da94975.jpg','media/thumb/20260804_162250_6a7211da94975.webp','20260804_162250_6a7211da94975.jpg','gallery-8.jpg','image/jpeg',198747,NULL,'2026-08-04 09:22:50','2026-10-01 03:25:48'),(18,'d83a9997-204b-4b9a-90c2-ad3aff0df756',NULL,1,'public','media/original/20260804_162250_6a7211daca0ae.png','media/thumb/20260804_162250_6a7211daca0ae.webp','20260804_162250_6a7211daca0ae.png','gallery-7.png','image/png',1462697,NULL,'2026-08-04 09:22:50','2026-10-01 03:25:49'),(19,'44a55379-a000-4116-89a1-df68b95c9281',NULL,1,'public','media/original/20260804_162251_6a7211db0c164.jpg','media/thumb/20260804_162251_6a7211db0c164.webp','20260804_162251_6a7211db0c164.jpg','gallery-6.jpg','image/jpeg',161057,NULL,'2026-08-04 09:22:51','2026-10-01 03:25:50'),(20,'71e13b6b-eb57-44af-98a4-aa213ce075a0',NULL,1,'public','media/original/20260804_162251_6a7211db4d96c.jpg','media/thumb/20260804_162251_6a7211db4d96c.webp','20260804_162251_6a7211db4d96c.jpg','gallery-5.jpg','image/jpeg',107465,NULL,'2026-08-04 09:22:51','2026-10-01 03:25:51'),(21,'ee3ec89e-500c-42a5-9449-ab3f601f608c',NULL,1,'public','media/original/20260804_162251_6a7211db86473.jpg','media/thumb/20260804_162251_6a7211db86473.webp','20260804_162251_6a7211db86473.jpg','gallery-4.jpg','image/jpeg',279146,NULL,'2026-08-04 09:22:51','2026-10-01 03:25:52'),(22,'3a009c72-e7c0-4b8b-81f2-62fd37b4ddf5',NULL,1,'public','media/original/20260804_162302_6a7211e681dae.jpg','media/thumb/20260804_162302_6a7211e681dae.webp','20260804_162302_6a7211e681dae.jpg','chup-anh-cuoi-vintage-1.jpg','image/jpeg',139587,NULL,'2026-08-04 09:23:02','2026-10-01 03:25:53'),(23,'307caac4-0922-49c7-b599-998050e26af8',NULL,1,'public','media/original/20260804_162302_6a7211e6c8d92.jpg','media/thumb/20260804_162302_6a7211e6c8d92.webp','20260804_162302_6a7211e6c8d92.jpg','464275181_122168201492254080_5598237610130465921_n.jpg','image/jpeg',117245,NULL,'2026-08-04 09:23:02','2026-10-01 03:25:54'),(24,'eb96639d-5071-4dbe-b780-edb7be7c6637',NULL,1,'public','media/original/20260804_162303_6a7211e72bce0.jpg','media/thumb/20260804_162303_6a7211e72bce0.webp','20260804_162303_6a7211e72bce0.jpg','560045750_1189266393019293_8342282536697983955_n-2-1024x683.jpg','image/jpeg',168654,NULL,'2026-08-04 09:23:03','2026-10-01 03:25:54'),(25,'c2bcb11e-d04c-46b2-98e0-74e60e663074',NULL,1,'public','media/original/20260804_162303_6a7211e763abf.jpg','media/thumb/20260804_162303_6a7211e763abf.webp','20260804_162303_6a7211e763abf.jpg','343342550_5955920044504242_5222768225392896037_n.jpg','image/jpeg',155149,NULL,'2026-08-04 09:23:03','2026-10-01 03:25:55'),(26,'943b6347-1020-453a-be3e-db0824ba9f36',1,1,'public','media/original/20260804_182856_6a722f689a8cd.webp','media/thumb/20260804_182856_6a722f689a8cd.webp','20260804_182856_6a722f689a8cd.webp','Anh-em-be-gai-de-thuong.webp','image/webp',131032,NULL,'2026-08-04 11:28:58','2026-10-01 03:25:56'),(27,'9c0bf8b9-1206-4e32-b475-30058be8b368',NULL,1,'public','media/original/20260809_134613_6a7884a5a9968.jpg','media/thumb/20260809_134613_6a7884a5a9968.webp','20260809_134613_6a7884a5a9968.jpg','vn-11134201-23030-173hgesmahovc8.jpg','image/jpeg',57855,NULL,'2026-08-09 06:46:15','2026-10-01 03:25:57'),(28,'b26adfb8-99d9-4097-b266-b534eb343aba',NULL,1,'public','media/original/20260928_153303_6aba263f2d194.png','media/thumb/20260928_153303_6aba263f2d194.webp','20260928_153303_6aba263f2d194.png','customer-support.png','image/png',15681,NULL,'2026-09-28 08:33:03','2026-09-28 08:33:03');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_folders`
--

DROP TABLE IF EXISTS `media_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_folders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_folders_uuid_unique` (`uuid`),
  UNIQUE KEY `media_folders_name_parent_id_unique` (`name`,`parent_id`),
  KEY `media_folders_parent_id_foreign` (`parent_id`),
  KEY `media_folders_user_id_foreign` (`user_id`),
  CONSTRAINT `media_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `media_folders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_folders`
--

LOCK TABLES `media_folders` WRITE;
/*!40000 ALTER TABLE `media_folders` DISABLE KEYS */;
INSERT INTO `media_folders` VALUES (1,'6de77de2-9e71-4b25-8dfb-a9da415ffd28','san-pham','san-pham',NULL,1,'2026-08-04 11:13:10','2026-08-04 11:13:10');
/*!40000 ALTER TABLE `media_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/',
  `target` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `reference_id` bigint unsigned DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_items_uuid_unique` (`uuid`),
  KEY `menu_items_menu_id_foreign` (`menu_id`),
  KEY `menu_items_parent_id_foreign` (`parent_id`),
  CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_uuid_unique` (`uuid`),
  UNIQUE KEY `menus_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (8,'0001_01_01_000000_create_users_table',1),(9,'0001_01_01_000001_create_cache_table',1),(10,'0001_01_01_000002_create_jobs_table',1),(11,'2026_05_05_171801_create_permission_tables',1),(12,'2026_05_05_172458_create_personal_access_tokens_table',1),(13,'2026_08_04_143144_create_media_folders_table',1),(14,'2026_08_04_143145_create_media_table',1),(15,'2026_08_10_161705_create_settings_table',2),(16,'2026_08_12_133142_create_languages_table',3),(19,'2026_08_15_125510_create_tags_table',4),(20,'2026_08_15_125519_create_posts_table',4),(21,'2026_08_15_125527_create_post_translations_table',4),(22,'2026_08_15_125536_create_post_tag_table',4),(23,'2026_08_15_125544_create_pages_table',4),(24,'2026_08_15_125553_create_page_translations_table',4),(25,'2026_08_15_125601_create_banners_table',4),(26,'2026_08_15_125610_create_banner_translations_table',4),(27,'2026_08_15_125618_create_menus_table',4),(28,'2026_08_15_125626_create_menu_items_table',4),(33,'2026_08_15_130950_create_brands_table',5),(34,'2026_08_15_130951_create_brand_translations_table',5),(35,'2026_08_15_130952_create_products_table',5),(36,'2026_08_15_130953_create_product_translations_table',5),(37,'2026_08_15_130954_create_product_attributes_table',5),(38,'2026_08_15_130955_create_product_attribute_translations_table',5),(39,'2026_08_15_130956_create_product_variants_table',5),(41,'2026_08_15_130958_create_carts_table',5),(42,'2026_08_15_130959_create_orders_table',5),(44,'2026_08_15_131001_create_payments_table',5),(46,'2026_08_15_131003_create_flash_sales_table',5),(47,'2026_08_15_131004_create_warehouses_table',5),(48,'2026_08_15_131005_create_inventories_table',5),(49,'2026_08_15_130948_create_categories_table',6),(50,'2026_08_15_130949_create_category_translations_table',6),(53,'2026_08_15_113902_create_post_categories_table',7),(54,'2026_08_15_113911_create_post_category_translations_table',7),(56,'2026_08_28_171216_add_columns_to_posts_and_translations_table',8),(57,'2026_08_28_175815_add_missing_columns_to_posts_table',9),(58,'2026_08_30_081215_rename_featured_image_to_image_in_posts_table',10),(59,'2026_09_05_111302_create_seo_metadata_table',10),(60,'2026_09_07_145027_add_schema_type_to_seo_metadata_table',11),(61,'2026_09_09_154700_create_post_category_post_table',12),(62,'2026_09_15_041000_add_status_to_pages_table',13),(63,'2026_09_15_063500_enhance_pages_module_schema',13),(64,'2026_09_15_070200_add_published_at_to_pages_table',13),(65,'2026_09_15_115301_add_soft_deletes_to_pages_table',13),(66,'2026_09_16_060100_drop_duplicate_meta_from_pages_table',14),(67,'2026_09_17_080000_enhance_tags_and_post_tag_tables',15),(68,'2026_09_17_081500_enhance_brands_and_translations_tables',16),(69,'2026_09_17_083000_enhance_product_attributes_and_values_tables',17),(70,'2026_09_17_084500_enhance_banners_and_translations_tables',18),(71,'2026_09_17_090000_enhance_menus_and_menu_items_tables',19),(72,'2026_09_17_091500_enhance_products_variants_and_reviews_tables',20),(73,'2026_09_17_093000_enhance_product_attributes_tables',21),(74,'2026_09_17_094500_enhance_banners_tables',22),(75,'2026_09_17_095500_enhance_menus_tables',23),(76,'2026_09_17_101000_enhance_products_tables',24),(77,'2026_09_22_000000_add_admin_access_permission',25),(78,'2026_09_23_060000_create_product_attribute_variant_pivots',26),(79,'2026_09_25_000000_add_product_translation_indexes',27),(80,'2026_09_26_060000_add_primary_image_to_products_table',28),(81,'2026_09_28_000000_add_variant_detail_columns_to_product_variants_table',29),(82,'2026_09_28_010000_enhance_products_for_shipping_taxonomy',29),(85,'2026_10_03_000000_drop_dimensions_from_products',30),(86,'2026_10_04_214557_create_product_attribute_value_translations_table',31),(87,'2026_10_05_112400_change_product_attributes_code_unique_to_scoped',32),(88,'2026_10_06_000000_fix_settings_vietnamese_encoding',32),(90,'2026_08_15_131000_create_payment_methods_table',34),(91,'2026_08_15_130957_create_product_reviews_table',35),(94,'2026_08_15_131002_create_coupons_table',36),(95,'2026_10_10_104439_create_coupon_users_table',37),(96,'2026_10_12_000001_add_scalability_fields_to_payment_methods_table',38),(97,'2026_10_12_000002_change_payment_methods_config_to_long_text',39),(98,'2026_10_19_000001_enhance_orders_table',40),(99,'2026_10_19_000002_create_shipping_addresses_table',40),(100,'2026_10_19_000003_create_order_items_table',40),(101,'2026_10_19_000004_create_order_status_histories_table',40),(102,'2026_10_19_000005_enhance_payments_table',41),(103,'2026_11_10_000001_create_notifications_table',42),(104,'2026_11_10_000002_create_notification_deliveries_table',42),(105,'2026_10_26_000001_enhance_carts_table',43),(106,'2026_10_26_000002_create_cart_items_table',43),(107,'2026_10_26_000003_add_coupon_snapshot_to_orders_table',43),(108,'2026_10_28_000001_enhance_flash_sales_table',43),(109,'2026_10_28_000002_create_flash_sale_translations_table',43),(110,'2026_10_28_000003_create_flash_sale_products_table',43),(111,'2026_10_28_000004_create_flash_sale_redemptions_table',43),(112,'2026_10_28_000005_add_flash_sale_product_id_to_order_items',43),(113,'2026_11_03_000001_make_payments_gateway_index_unique',43),(114,'2026_11_21_000001_replace_post_image_with_media_id',44),(115,'2026_11_22_000001_create_theme_settings_table',45),(116,'2026_11_22_000002_add_theme_permissions',45),(117,'2026_11_22_000003_create_content_blocks_tables',46),(118,'2026_11_22_000004_add_content_block_permissions',46),(119,'2026_11_22_000005_rename_image_media_id_to_image',47);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (2,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_deliveries`
--

DROP TABLE IF EXISTS `notification_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_deliveries` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_user_id` bigint unsigned DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` smallint unsigned NOT NULL DEFAULT '0',
  `available_at` timestamp(6) NULL DEFAULT NULL,
  `lease_token` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lease_expires_at` timestamp(6) NULL DEFAULT NULL,
  `last_enqueued_at` timestamp(6) NULL DEFAULT NULL,
  `sent_at` timestamp(6) NULL DEFAULT NULL,
  `finished_at` timestamp(6) NULL DEFAULT NULL,
  `last_error_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_deliveries_event_recipient_channel_unique` (`event_key`,`recipient_key`,`channel`),
  KEY `notification_deliveries_recipient_user_id_foreign` (`recipient_user_id`),
  KEY `notification_deliveries_pending_index` (`status`,`available_at`),
  KEY `notification_deliveries_lease_index` (`status`,`lease_expires_at`),
  KEY `notification_deliveries_retention_index` (`status`,`finished_at`),
  CONSTRAINT `notification_deliveries_recipient_user_id_foreign` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_deliveries`
--

LOCK TABLES `notification_deliveries` WRITE;
/*!40000 ALTER TABLE `notification_deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp(6) NULL DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `notifications_owner_created_index` (`notifiable_type`,`notifiable_id`,`created_at`),
  KEY `notifications_owner_unread_index` (`notifiable_type`,`notifiable_id`,`read_at`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `flash_sale_product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `quantity` int unsigned NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_items_uuid_unique` (`uuid`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  KEY `order_items_order_id_product_id_index` (`order_id`,`product_id`),
  KEY `order_items_flash_sale_product_id_foreign` (`flash_sale_product_id`),
  CONSTRAINT `order_items_flash_sale_product_id_foreign` FOREIGN KEY (`flash_sale_product_id`) REFERENCES `flash_sale_products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_histories`
--

DROP TABLE IF EXISTS `order_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `from_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `changed_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_histories_changed_by_user_id_foreign` (`changed_by_user_id`),
  KEY `order_status_histories_order_id_created_at_index` (`order_id`,`created_at`),
  CONSTRAINT `order_status_histories_changed_by_user_id_foreign` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_histories`
--

LOCK TABLES `order_status_histories` WRITE;
/*!40000 ALTER TABLE `order_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `payment_method_id` bigint unsigned DEFAULT NULL,
  `coupon_id` bigint unsigned DEFAULT NULL,
  `coupon_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping_fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `cancel_reason` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_uuid_unique` (`uuid`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_payment_method_id_foreign` (`payment_method_id`),
  KEY `orders_status_created_at_index` (`status`,`created_at`),
  KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `orders_coupon_id_foreign` (`coupon_id`),
  CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_translations`
--

DROP TABLE IF EXISTS `page_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `page_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `excerpt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_translations_page_id_locale_unique` (`page_id`,`locale`),
  KEY `page_translations_slug_index` (`slug`),
  CONSTRAINT `page_translations_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_translations`
--

LOCK TABLES `page_translations` WRITE;
/*!40000 ALTER TABLE `page_translations` DISABLE KEYS */;
INSERT INTO `page_translations` VALUES (1,'2026-09-15 02:10:13','2026-09-15 02:10:13',1,'vi','Gioi thieu','gioi-thieu','Trang gioi thieu','<p>Chung toi</p>'),(2,'2026-09-15 02:10:13','2026-09-15 02:47:05',2,'vi','LIÊN HỆ - Đã đổi title','lien-he-moi','Mô tả mới','<p>Updated</p>'),(3,'2026-09-15 02:10:13','2026-09-15 02:10:13',3,'vi','Su menh','su-menh','Su menh va tam nhin','<p>Su menh</p>'),(4,'2026-09-15 02:10:13','2026-09-15 02:10:13',4,'vi','Khuyen mai Tet','khuyen-mai-tet','Trang landing','<p>Khuyen mai</p>'),(5,'2026-09-15 02:22:56','2026-09-15 02:23:39',5,'vi','Y updated','y-updated','Mô tả ngắn cho trang','<p>Nội dung test</p>'),(6,'2026-09-15 02:23:39','2026-09-16 07:08:59',6,'vi','HTTP Verify Sau Drop','http-verify-sau-drop','Mota ngan',NULL);
/*!40000 ALTER TABLE `page_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `page_template` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `published_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_uuid_unique` (`uuid`),
  KEY `pages_status_index` (`status`),
  KEY `pages_parent_id_foreign` (`parent_id`),
  KEY `pages_page_template_index` (`page_template`),
  KEY `pages_published_at_index` (`published_at`),
  CONSTRAINT `pages_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'285515fb-2e73-4e82-b4d2-e5559e6c7dcd',NULL,'full_width','2026-09-01 03:00:00',NULL,1,'published','2026-09-15 02:10:13','2026-09-15 02:10:13',NULL),(2,'889d2a37-788a-4d48-bfff-8acfe84b88c3',4,'contact_us','2026-12-24 17:00:00',NULL,99,'draft','2026-09-15 02:10:13','2026-09-15 02:47:05',NULL),(3,'2eed33ce-b1d3-4336-a056-92d42f79c230',1,'default',NULL,NULL,3,'draft','2026-09-15 02:10:13','2026-09-15 02:10:13',NULL),(4,'c331d8cf-2eef-4339-9467-69fc428212d3',NULL,'landing','2026-09-03 03:00:00',NULL,10,'published','2026-09-15 02:10:13','2026-09-15 02:10:13',NULL),(5,'2a24c7b5-0ddb-421b-b5f3-a147a7a67557',1,'default','2026-09-19 02:21:00','eb96639d-5071-4dbe-b780-edb7be7c6637',0,'archived','2026-09-15 02:22:56','2026-09-21 02:22:23',NULL),(6,'6c16b552-cac4-4cb0-8b04-ad8b4b29b37a',NULL,'default',NULL,NULL,1,'draft','2026-09-15 02:23:39','2026-09-16 06:31:58',NULL);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `min_order_amount` decimal(15,2) DEFAULT NULL,
  `max_order_amount` decimal(15,2) DEFAULT NULL,
  `supported_currencies` json DEFAULT NULL,
  `config` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_uuid_unique` (`uuid`),
  UNIQUE KEY `payment_methods_code_unique` (`code`),
  KEY `pm_active_order_idx` (`is_active`,`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned DEFAULT NULL,
  `gateway_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `gateway_response` json DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_uuid_unique` (`uuid`),
  UNIQUE KEY `payments_gateway_code_gateway_transaction_id_unique` (`gateway_code`,`gateway_transaction_id`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  KEY `payments_order_id_status_index` (`order_id`,`status`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'users.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(2,'users.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(3,'users.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(4,'users.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(5,'users.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(6,'users.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(7,'users.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(8,'users.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(9,'users.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(10,'users.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(11,'users.restore','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(12,'users.restore','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(13,'users.force-delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(14,'users.force-delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(15,'users.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(16,'users.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(17,'users.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(18,'users.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(19,'roles.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(20,'roles.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(21,'roles.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(22,'roles.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(23,'roles.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(24,'roles.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(25,'roles.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(26,'roles.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(27,'permissions.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(28,'permissions.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(29,'permissions.assign','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(30,'permissions.assign','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(31,'permissions.revoke','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(32,'permissions.revoke','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(33,'categories.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(34,'categories.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(35,'categories.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(36,'categories.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(37,'categories.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(38,'categories.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(39,'categories.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(40,'categories.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(41,'brands.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(42,'brands.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(43,'brands.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(44,'brands.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(45,'brands.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(46,'brands.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(47,'brands.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(48,'brands.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(49,'products.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(50,'products.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(51,'products.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(52,'products.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(53,'products.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(54,'products.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(55,'products.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(56,'products.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(57,'products.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(58,'products.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(59,'products.publish','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(60,'products.publish','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(61,'products.approve','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(62,'products.approve','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(63,'products.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(64,'products.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(65,'products.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(66,'products.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(67,'orders.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(68,'orders.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(69,'orders.view-all','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(70,'orders.view-all','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(71,'orders.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(72,'orders.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(73,'orders.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(74,'orders.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(75,'orders.cancel','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(76,'orders.cancel','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(77,'orders.refund','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(78,'orders.refund','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(79,'orders.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(80,'orders.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(81,'inventory.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(82,'inventory.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(83,'inventory.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(84,'inventory.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(85,'inventory.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(86,'inventory.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(87,'inventory.adjust','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(88,'inventory.adjust','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(89,'coupons.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(90,'coupons.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(91,'coupons.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(92,'coupons.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(93,'coupons.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(94,'coupons.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(95,'coupons.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(96,'coupons.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(97,'posts.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(98,'posts.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(99,'posts.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(100,'posts.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(101,'posts.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(102,'posts.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(103,'posts.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(104,'posts.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(105,'posts.publish','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(106,'posts.publish','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(107,'pages.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(108,'pages.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(109,'pages.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(110,'pages.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(111,'pages.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(112,'pages.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(113,'pages.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(114,'pages.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(115,'banners.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(116,'banners.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(117,'banners.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(118,'banners.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(119,'banners.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(120,'banners.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(121,'banners.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(122,'banners.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(123,'media.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(124,'media.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(125,'media.upload','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(126,'media.upload','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(127,'media.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(128,'media.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(129,'reports.sales','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(130,'reports.sales','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(131,'reports.inventory','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(132,'reports.inventory','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(133,'reports.customers','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(134,'reports.customers','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(135,'reports.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(136,'reports.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(137,'settings.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(138,'settings.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(139,'settings.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(140,'settings.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(141,'settings.general','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(142,'settings.general','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(143,'settings.payment','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(144,'settings.payment','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(145,'settings.email','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(146,'settings.email','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(147,'settings.sms','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(148,'settings.sms','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(149,'logs.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(150,'logs.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(151,'logs.clear','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(152,'logs.clear','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(153,'admin.access','web','2026-09-22 02:19:10','2026-09-22 02:19:10'),(154,'admin.access','api','2026-09-22 02:19:10','2026-09-22 02:19:10'),(155,'post-categories.view','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(156,'post-categories.view','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(157,'post-categories.create','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(158,'post-categories.create','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(159,'post-categories.update','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(160,'post-categories.update','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(161,'post-categories.delete','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(162,'post-categories.delete','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(163,'tags.view','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(164,'tags.view','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(165,'tags.create','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(166,'tags.create','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(167,'tags.update','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(168,'tags.update','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(169,'tags.delete','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(170,'tags.delete','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(171,'menus.view','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(172,'menus.view','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(173,'menus.create','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(174,'menus.create','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(175,'menus.update','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(176,'menus.update','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(177,'menus.delete','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(178,'menus.delete','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(179,'product-attributes.view','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(180,'product-attributes.view','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(181,'product-attributes.create','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(182,'product-attributes.create','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(183,'product-attributes.update','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(184,'product-attributes.update','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(185,'product-attributes.delete','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(186,'product-attributes.delete','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(187,'product-reviews.view','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(188,'product-reviews.view','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(189,'product-reviews.update','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(190,'product-reviews.update','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(191,'product-reviews.delete','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(192,'product-reviews.delete','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(193,'themes.view','web','2026-11-22 07:19:20','2026-11-22 07:19:20'),(194,'themes.preview','web','2026-11-22 07:19:20','2026-11-22 07:19:20'),(195,'themes.activate','web','2026-11-22 07:19:20','2026-11-22 07:19:20'),(196,'themes.update','web','2026-11-22 07:19:20','2026-11-22 07:19:20'),(197,'themes.view','api','2026-11-22 07:19:20','2026-11-22 07:19:20'),(198,'themes.preview','api','2026-11-22 07:19:20','2026-11-22 07:19:20'),(199,'themes.activate','api','2026-11-22 07:19:20','2026-11-22 07:19:20'),(200,'themes.update','api','2026-11-22 07:19:20','2026-11-22 07:19:20'),(201,'content-blocks.view','web','2026-11-22 10:01:54','2026-11-22 10:01:54'),(202,'content-blocks.create','web','2026-11-22 10:01:54','2026-11-22 10:01:54'),(203,'content-blocks.update','web','2026-11-22 10:01:54','2026-11-22 10:01:54'),(204,'content-blocks.delete','web','2026-11-22 10:01:54','2026-11-22 10:01:54'),(205,'content-blocks.publish','web','2026-11-22 10:01:54','2026-11-22 10:01:54'),(206,'content-blocks.view','api','2026-11-22 10:01:54','2026-11-22 10:01:54'),(207,'content-blocks.create','api','2026-11-22 10:01:54','2026-11-22 10:01:54'),(208,'content-blocks.update','api','2026-11-22 10:01:54','2026-11-22 10:01:54'),(209,'content-blocks.delete','api','2026-11-22 10:01:54','2026-11-22 10:01:54'),(210,'content-blocks.publish','api','2026-11-22 10:01:54','2026-11-22 10:01:54');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_categories`
--

DROP TABLE IF EXISTS `post_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_categories_uuid_unique` (`uuid`),
  KEY `post_categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `post_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `post_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_categories`
--

LOCK TABLES `post_categories` WRITE;
/*!40000 ALTER TABLE `post_categories` DISABLE KEYS */;
INSERT INTO `post_categories` VALUES (2,'5b9a770a-795b-4c2d-a5df-9569294af72a',NULL,NULL,NULL,1,0,'2026-08-16 10:24:00','2026-08-20 08:44:12'),(3,'30f3b772-f038-425d-8f86-04c800b37771',2,NULL,NULL,1,0,'2026-08-19 08:34:45','2026-08-20 08:20:48'),(4,'457a0c74-6fc3-4a23-a362-318a54908c0b',3,NULL,NULL,1,0,'2026-08-20 10:52:22','2026-08-22 04:27:40');
/*!40000 ALTER TABLE `post_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_category_post`
--

DROP TABLE IF EXISTS `post_category_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_category_post` (
  `post_id` bigint unsigned NOT NULL,
  `post_category_id` bigint unsigned NOT NULL,
  UNIQUE KEY `post_category_post_post_id_post_category_id_unique` (`post_id`,`post_category_id`),
  KEY `post_category_post_post_category_id_foreign` (`post_category_id`),
  CONSTRAINT `post_category_post_post_category_id_foreign` FOREIGN KEY (`post_category_id`) REFERENCES `post_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_category_post_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_category_post`
--

LOCK TABLES `post_category_post` WRITE;
/*!40000 ALTER TABLE `post_category_post` DISABLE KEYS */;
INSERT INTO `post_category_post` VALUES (2,2),(8,2),(11,2),(18,2),(19,2),(21,2),(26,2),(28,2),(31,2),(32,2),(33,2),(38,2),(45,2),(49,2),(57,2),(59,2),(79,2),(84,2),(85,2),(88,2),(90,2),(91,2),(99,2),(100,2),(1,3),(2,3),(3,3),(4,3),(6,3),(7,3),(12,3),(20,3),(22,3),(29,3),(30,3),(41,3),(44,3),(48,3),(52,3),(58,3),(60,3),(61,3),(63,3),(64,3),(69,3),(74,3),(77,3),(78,3),(80,3),(81,3),(83,3),(87,3),(89,3),(92,3),(93,3),(94,3),(95,3),(97,3),(101,3),(5,4),(9,4),(10,4),(13,4),(14,4),(15,4),(16,4),(17,4),(23,4),(24,4),(25,4),(27,4),(34,4),(35,4),(36,4),(37,4),(39,4),(40,4),(42,4),(43,4),(46,4),(47,4),(50,4),(51,4),(53,4),(54,4),(55,4),(56,4),(62,4),(65,4),(66,4),(67,4),(68,4),(70,4),(71,4),(72,4),(73,4),(75,4),(76,4),(82,4),(86,4),(96,4),(98,4),(102,4);
/*!40000 ALTER TABLE `post_category_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_category_translations`
--

DROP TABLE IF EXISTS `post_category_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_category_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_category_id` bigint unsigned NOT NULL,
  `locale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_category_translations_post_category_id_locale_unique` (`post_category_id`,`locale`),
  KEY `post_category_translations_locale_index` (`locale`),
  CONSTRAINT `post_category_translations_post_category_id_foreign` FOREIGN KEY (`post_category_id`) REFERENCES `post_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_category_translations`
--

LOCK TABLES `post_category_translations` WRITE;
/*!40000 ALTER TABLE `post_category_translations` DISABLE KEYS */;
INSERT INTO `post_category_translations` VALUES (1,2,'vi','Danh mục 111','danh-muc-1',NULL,NULL,NULL,NULL,'2026-08-16 10:24:00','2026-08-19 08:55:39'),(2,2,'en','Category 1','category-1',NULL,NULL,NULL,NULL,'2026-08-16 10:24:00','2026-08-19 08:14:15'),(3,3,'vi','Danh mục 2','danh-muc-2',NULL,NULL,NULL,NULL,'2026-08-19 08:34:45','2026-08-19 08:34:45'),(4,3,'en','Category 2','category-2',NULL,NULL,NULL,NULL,'2026-08-19 08:34:45','2026-08-19 08:34:45'),(5,4,'vi','Danh mục 3','danh-muc-3',NULL,'Danh má»¥c 1',NULL,NULL,'2026-08-20 10:52:22','2026-08-20 10:52:22');
/*!40000 ALTER TABLE `post_category_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_tag`
--

DROP TABLE IF EXISTS `post_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_tag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_tag_post_id_tag_id_unique` (`post_id`,`tag_id`),
  KEY `post_tag_tag_id_foreign` (`tag_id`),
  CONSTRAINT `post_tag_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_tag`
--

LOCK TABLES `post_tag` WRITE;
/*!40000 ALTER TABLE `post_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_translations`
--

DROP TABLE IF EXISTS `post_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `post_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_translations_post_id_locale_unique` (`post_id`,`locale`),
  KEY `post_translations_slug_index` (`slug`),
  CONSTRAINT `post_translations_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_translations`
--

LOCK TABLES `post_translations` WRITE;
/*!40000 ALTER TABLE `post_translations` DISABLE KEYS */;
INSERT INTO `post_translations` VALUES (1,'2026-09-08 08:44:01','2026-09-14 08:54:32',1,'vi','Bài viết test tiếng việt','Bài viết test tiếng việt',NULL,NULL,NULL,NULL,NULL),(2,'2026-09-08 08:57:01','2026-09-08 08:57:01',2,'vi','Bài viết kiểm tra lưu ảnh đại diện và auto slug','laravel-11-chuyen-sau','Khóa học Laravel 11 thực chiến toàn diện.',NULL,NULL,NULL,NULL),(3,'2026-11-25 15:11:02','2026-11-25 15:11:02',3,'vi','Bài viết mẫu 1 - Perferendis sint nesciunt.','bai-viet-mau-1-perferendis-sint-nesciunt-3','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(4,'2026-11-25 15:11:02','2026-11-25 15:11:02',4,'vi','Bài viết mẫu 2 - Et repudiandae vel voluptatem.','bai-viet-mau-2-et-repudiandae-vel-voluptatem-4','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(5,'2026-11-25 15:11:02','2026-11-25 15:11:02',5,'vi','Bài viết mẫu 3 - Ex molestiae id eaque blanditiis.','bai-viet-mau-3-ex-molestiae-id-eaque-blanditiis-5','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(6,'2026-11-25 15:11:02','2026-11-25 15:11:02',6,'vi','Bài viết mẫu 4 - Laboriosam neque adipisci ullam tempore.','bai-viet-mau-4-laboriosam-neque-adipisci-ullam-tempore-6','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(7,'2026-11-25 15:11:02','2026-11-25 15:11:02',7,'vi','Bài viết mẫu 5 - Libero aut molestias et placeat iste.','bai-viet-mau-5-libero-aut-molestias-et-placeat-iste-7','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(8,'2026-11-25 15:11:02','2026-11-25 15:11:02',8,'vi','Bài viết mẫu 6 - Laborum suscipit consequuntur.','bai-viet-mau-6-laborum-suscipit-consequuntur-8','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(9,'2026-11-25 15:11:02','2026-11-25 15:11:02',9,'vi','Bài viết mẫu 7 - Perferendis quaerat tenetur omnis necessitatibus id.','bai-viet-mau-7-perferendis-quaerat-tenetur-omnis-necessitatibus-id-9','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(10,'2026-11-25 15:11:02','2026-11-25 15:11:02',10,'vi','Bài viết mẫu 8 - Quod aut velit minus aut.','bai-viet-mau-8-quod-aut-velit-minus-aut-10','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(11,'2026-11-25 15:11:02','2026-11-25 15:11:02',11,'vi','Bài viết mẫu 9 - Ab nemo ut ipsa sit aut.','bai-viet-mau-9-ab-nemo-ut-ipsa-sit-aut-11','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(12,'2026-11-25 15:11:02','2026-11-25 15:11:02',12,'vi','Bài viết mẫu 10 - Est ad eligendi.','bai-viet-mau-10-est-ad-eligendi-12','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(13,'2026-11-25 15:11:02','2026-11-25 15:11:02',13,'vi','Bài viết mẫu 11 - Ipsam sit vel quia et.','bai-viet-mau-11-ipsam-sit-vel-quia-et-13','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(14,'2026-11-25 15:11:02','2026-11-25 15:11:02',14,'vi','Bài viết mẫu 12 - Vel explicabo dolores sunt autem.','bai-viet-mau-12-vel-explicabo-dolores-sunt-autem-14','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(15,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,'vi','Bài viết mẫu 13 - Nihil iure dolor eos sint.','bai-viet-mau-13-nihil-iure-dolor-eos-sint-15','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(16,'2026-11-25 15:11:02','2026-11-25 15:11:02',16,'vi','Bài viết mẫu 14 - Tempora omnis quas reprehenderit.','bai-viet-mau-14-tempora-omnis-quas-reprehenderit-16','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(17,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'vi','Bài viết mẫu 15 - Reprehenderit repudiandae sed.','bai-viet-mau-15-reprehenderit-repudiandae-sed-17','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(18,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,'vi','Bài viết mẫu 16 - Voluptatibus quidem quibusdam enim.','bai-viet-mau-16-voluptatibus-quidem-quibusdam-enim-18','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(19,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,'vi','Bài viết mẫu 17 - Est vitae aliquid porro eos.','bai-viet-mau-17-est-vitae-aliquid-porro-eos-19','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(20,'2026-11-25 15:11:02','2026-11-25 15:11:02',20,'vi','Bài viết mẫu 18 - Doloremque dolore optio modi ipsa quod.','bai-viet-mau-18-doloremque-dolore-optio-modi-ipsa-quod-20','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(21,'2026-11-25 15:11:02','2026-11-25 15:11:02',21,'vi','Bài viết mẫu 19 - Optio nemo a perferendis.','bai-viet-mau-19-optio-nemo-a-perferendis-21','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(22,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,'vi','Bài viết mẫu 20 - Ea assumenda blanditiis suscipit quia.','bai-viet-mau-20-ea-assumenda-blanditiis-suscipit-quia-22','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(23,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,'vi','Bài viết mẫu 21 - Voluptate aut suscipit ea.','bai-viet-mau-21-voluptate-aut-suscipit-ea-23','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(24,'2026-11-25 15:11:02','2026-11-25 15:11:02',24,'vi','Bài viết mẫu 22 - Recusandae doloribus ipsa possimus.','bai-viet-mau-22-recusandae-doloribus-ipsa-possimus-24','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(25,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,'vi','Bài viết mẫu 23 - Quos nisi magnam autem.','bai-viet-mau-23-quos-nisi-magnam-autem-25','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(26,'2026-11-25 15:11:02','2026-11-25 15:11:02',26,'vi','Bài viết mẫu 24 - Veniam labore minima.','bai-viet-mau-24-veniam-labore-minima-26','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(27,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'vi','Bài viết mẫu 25 - Ut vero ut minima aut amet.','bai-viet-mau-25-ut-vero-ut-minima-aut-amet-27','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(28,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'vi','Bài viết mẫu 26 - Facilis neque voluptatum aut natus.','bai-viet-mau-26-facilis-neque-voluptatum-aut-natus-28','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(29,'2026-11-25 15:11:02','2026-11-25 15:11:02',29,'vi','Bài viết mẫu 27 - Placeat omnis quia quis tempore.','bai-viet-mau-27-placeat-omnis-quia-quis-tempore-29','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(30,'2026-11-25 15:11:02','2026-11-25 15:11:02',30,'vi','Bài viết mẫu 28 - Ipsam totam quia est.','bai-viet-mau-28-ipsam-totam-quia-est-30','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(31,'2026-11-25 15:11:02','2026-11-25 15:11:02',31,'vi','Bài viết mẫu 29 - Et accusantium animi quia odit rerum.','bai-viet-mau-29-et-accusantium-animi-quia-odit-rerum-31','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(32,'2026-11-25 15:11:02','2026-11-25 15:11:02',32,'vi','Bài viết mẫu 30 - Dolor veritatis qui natus.','bai-viet-mau-30-dolor-veritatis-qui-natus-32','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(33,'2026-11-25 15:11:02','2026-11-25 15:11:02',33,'vi','Bài viết mẫu 31 - Porro sequi ut ex.','bai-viet-mau-31-porro-sequi-ut-ex-33','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(34,'2026-11-25 15:11:02','2026-11-25 15:11:02',34,'vi','Bài viết mẫu 32 - Vel odio accusamus quidem rerum.','bai-viet-mau-32-vel-odio-accusamus-quidem-rerum-34','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(35,'2026-11-25 15:11:02','2026-11-25 15:11:02',35,'vi','Bài viết mẫu 33 - Et repellendus fuga quisquam aspernatur.','bai-viet-mau-33-et-repellendus-fuga-quisquam-aspernatur-35','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(36,'2026-11-25 15:11:02','2026-11-25 15:11:02',36,'vi','Bài viết mẫu 34 - Illo ut blanditiis ab eum.','bai-viet-mau-34-illo-ut-blanditiis-ab-eum-36','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(37,'2026-11-25 15:11:02','2026-11-25 15:11:02',37,'vi','Bài viết mẫu 35 - Voluptatem sit perspiciatis temporibus.','bai-viet-mau-35-voluptatem-sit-perspiciatis-temporibus-37','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(38,'2026-11-25 15:11:02','2026-11-25 15:11:02',38,'vi','Bài viết mẫu 36 - Aut qui occaecati nobis ullam ut.','bai-viet-mau-36-aut-qui-occaecati-nobis-ullam-ut-38','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(39,'2026-11-25 15:11:02','2026-11-25 15:11:02',39,'vi','Bài viết mẫu 37 - Dolorem sunt est veritatis.','bai-viet-mau-37-dolorem-sunt-est-veritatis-39','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(40,'2026-11-25 15:11:02','2026-11-25 15:11:02',40,'vi','Bài viết mẫu 38 - Velit excepturi facere rerum.','bai-viet-mau-38-velit-excepturi-facere-rerum-40','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(41,'2026-11-25 15:11:02','2026-11-25 15:11:02',41,'vi','Bài viết mẫu 39 - Asperiores ex voluptatum sed.','bai-viet-mau-39-asperiores-ex-voluptatum-sed-41','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(42,'2026-11-25 15:11:02','2026-11-25 15:11:02',42,'vi','Bài viết mẫu 40 - Qui voluptatem suscipit beatae autem.','bai-viet-mau-40-qui-voluptatem-suscipit-beatae-autem-42','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(43,'2026-11-25 15:11:02','2026-11-25 15:11:02',43,'vi','Bài viết mẫu 41 - Inventore pariatur ipsum id dolorum.','bai-viet-mau-41-inventore-pariatur-ipsum-id-dolorum-43','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(44,'2026-11-25 15:11:02','2026-11-25 15:11:02',44,'vi','Bài viết mẫu 42 - Natus voluptas quos illo consectetur.','bai-viet-mau-42-natus-voluptas-quos-illo-consectetur-44','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(45,'2026-11-25 15:11:02','2026-11-25 15:11:02',45,'vi','Bài viết mẫu 43 - Repellat voluptatem repellat molestiae.','bai-viet-mau-43-repellat-voluptatem-repellat-molestiae-45','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(46,'2026-11-25 15:11:02','2026-11-25 15:11:02',46,'vi','Bài viết mẫu 44 - Mollitia quasi eum odio enim temporibus.','bai-viet-mau-44-mollitia-quasi-eum-odio-enim-temporibus-46','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(47,'2026-11-25 15:11:02','2026-11-25 15:11:02',47,'vi','Bài viết mẫu 45 - Cum dolor impedit asperiores.','bai-viet-mau-45-cum-dolor-impedit-asperiores-47','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(48,'2026-11-25 15:11:02','2026-11-25 15:11:02',48,'vi','Bài viết mẫu 46 - Occaecati ullam fugit assumenda.','bai-viet-mau-46-occaecati-ullam-fugit-assumenda-48','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(49,'2026-11-25 15:11:02','2026-11-25 15:11:02',49,'vi','Bài viết mẫu 47 - Et dolor dolor.','bai-viet-mau-47-et-dolor-dolor-49','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(50,'2026-11-25 15:11:02','2026-11-25 15:11:02',50,'vi','Bài viết mẫu 48 - Nostrum autem ex dolorum voluptas.','bai-viet-mau-48-nostrum-autem-ex-dolorum-voluptas-50','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(51,'2026-11-25 15:11:02','2026-11-25 15:11:02',51,'vi','Bài viết mẫu 49 - Voluptates soluta tempora sunt.','bai-viet-mau-49-voluptates-soluta-tempora-sunt-51','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(52,'2026-11-25 15:11:02','2026-11-25 15:11:02',52,'vi','Bài viết mẫu 50 - Deserunt temporibus quia ipsa corrupti.','bai-viet-mau-50-deserunt-temporibus-quia-ipsa-corrupti-52','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(53,'2026-11-25 15:11:02','2026-11-25 15:11:02',53,'vi','Bài viết mẫu 51 - Animi quia quia natus dolore.','bai-viet-mau-51-animi-quia-quia-natus-dolore-53','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(54,'2026-11-25 15:11:02','2026-11-25 15:11:02',54,'vi','Bài viết mẫu 52 - Vitae non non facere.','bai-viet-mau-52-vitae-non-non-facere-54','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(55,'2026-11-25 15:11:02','2026-11-25 15:11:02',55,'vi','Bài viết mẫu 53 - Voluptate esse consectetur aliquam.','bai-viet-mau-53-voluptate-esse-consectetur-aliquam-55','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(56,'2026-11-25 15:11:02','2026-11-25 15:11:02',56,'vi','Bài viết mẫu 54 - Qui eum consequuntur dicta facere.','bai-viet-mau-54-qui-eum-consequuntur-dicta-facere-56','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(57,'2026-11-25 15:11:02','2026-11-25 15:11:02',57,'vi','Bài viết mẫu 55 - Soluta et veritatis sint nisi.','bai-viet-mau-55-soluta-et-veritatis-sint-nisi-57','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(58,'2026-11-25 15:11:02','2026-11-25 15:11:02',58,'vi','Bài viết mẫu 56 - Quia aut veritatis.','bai-viet-mau-56-quia-aut-veritatis-58','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(59,'2026-11-25 15:11:02','2026-11-25 15:11:02',59,'vi','Bài viết mẫu 57 - Beatae quae mollitia atque deserunt odio.','bai-viet-mau-57-beatae-quae-mollitia-atque-deserunt-odio-59','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(60,'2026-11-25 15:11:02','2026-11-25 15:11:02',60,'vi','Bài viết mẫu 58 - Sed harum sed.','bai-viet-mau-58-sed-harum-sed-60','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(61,'2026-11-25 15:11:02','2026-11-25 15:11:02',61,'vi','Bài viết mẫu 59 - Ratione et molestias et laudantium.','bai-viet-mau-59-ratione-et-molestias-et-laudantium-61','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(62,'2026-11-25 15:11:02','2026-11-25 15:11:02',62,'vi','Bài viết mẫu 60 - Aut cumque est voluptatum molestiae nesciunt.','bai-viet-mau-60-aut-cumque-est-voluptatum-molestiae-nesciunt-62','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(63,'2026-11-25 15:11:02','2026-11-25 15:11:02',63,'vi','Bài viết mẫu 61 - Nisi omnis nihil ex.','bai-viet-mau-61-nisi-omnis-nihil-ex-63','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(64,'2026-11-25 15:11:02','2026-11-25 15:11:02',64,'vi','Bài viết mẫu 62 - Rem eius maiores.','bai-viet-mau-62-rem-eius-maiores-64','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(65,'2026-11-25 15:11:02','2026-11-25 15:11:02',65,'vi','Bài viết mẫu 63 - Aut explicabo aut at quibusdam aut.','bai-viet-mau-63-aut-explicabo-aut-at-quibusdam-aut-65','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(66,'2026-11-25 15:11:02','2026-11-25 15:11:02',66,'vi','Bài viết mẫu 64 - Rerum qui ut aspernatur enim corrupti.','bai-viet-mau-64-rerum-qui-ut-aspernatur-enim-corrupti-66','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(67,'2026-11-25 15:11:02','2026-11-25 15:11:02',67,'vi','Bài viết mẫu 65 - Reprehenderit molestias aperiam fugiat.','bai-viet-mau-65-reprehenderit-molestias-aperiam-fugiat-67','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(68,'2026-11-25 15:11:02','2026-11-25 15:11:02',68,'vi','Bài viết mẫu 66 - Ut quisquam sunt accusamus.','bai-viet-mau-66-ut-quisquam-sunt-accusamus-68','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(69,'2026-11-25 15:11:02','2026-11-25 15:11:02',69,'vi','Bài viết mẫu 67 - Voluptas illo eius voluptatibus minus.','bai-viet-mau-67-voluptas-illo-eius-voluptatibus-minus-69','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(70,'2026-11-25 15:11:02','2026-11-25 15:11:02',70,'vi','Bài viết mẫu 68 - Deleniti quo ad est laborum.','bai-viet-mau-68-deleniti-quo-ad-est-laborum-70','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(71,'2026-11-25 15:11:02','2026-11-25 15:11:02',71,'vi','Bài viết mẫu 69 - Commodi quidem ea.','bai-viet-mau-69-commodi-quidem-ea-71','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(72,'2026-11-25 15:11:02','2026-11-25 15:11:02',72,'vi','Bài viết mẫu 70 - Rerum voluptas asperiores deleniti occaecati.','bai-viet-mau-70-rerum-voluptas-asperiores-deleniti-occaecati-72','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(73,'2026-11-25 15:11:02','2026-11-25 15:11:02',73,'vi','Bài viết mẫu 71 - Rerum enim fugit et tempore saepe.','bai-viet-mau-71-rerum-enim-fugit-et-tempore-saepe-73','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(74,'2026-11-25 15:11:02','2026-11-25 15:11:02',74,'vi','Bài viết mẫu 72 - Delectus illo ducimus perferendis.','bai-viet-mau-72-delectus-illo-ducimus-perferendis-74','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(75,'2026-11-25 15:11:02','2026-11-25 15:11:02',75,'vi','Bài viết mẫu 73 - Odit quam eius omnis deleniti.','bai-viet-mau-73-odit-quam-eius-omnis-deleniti-75','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(76,'2026-11-25 15:11:02','2026-11-25 15:11:02',76,'vi','Bài viết mẫu 74 - Deleniti repellat vitae amet maxime.','bai-viet-mau-74-deleniti-repellat-vitae-amet-maxime-76','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(77,'2026-11-25 15:11:02','2026-11-25 15:11:02',77,'vi','Bài viết mẫu 75 - Tempore corrupti minus eum ut reiciendis.','bai-viet-mau-75-tempore-corrupti-minus-eum-ut-reiciendis-77','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(78,'2026-11-25 15:11:02','2026-11-25 15:11:02',78,'vi','Bài viết mẫu 76 - Eum libero sint aperiam.','bai-viet-mau-76-eum-libero-sint-aperiam-78','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(79,'2026-11-25 15:11:02','2026-11-25 15:11:02',79,'vi','Bài viết mẫu 77 - Ut aut nesciunt nihil.','bai-viet-mau-77-ut-aut-nesciunt-nihil-79','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(80,'2026-11-25 15:11:02','2026-11-25 15:11:02',80,'vi','Bài viết mẫu 78 - Ea dolores quis sed et ea.','bai-viet-mau-78-ea-dolores-quis-sed-et-ea-80','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(81,'2026-11-25 15:11:02','2026-11-25 15:11:02',81,'vi','Bài viết mẫu 79 - Iure dolor sed nisi.','bai-viet-mau-79-iure-dolor-sed-nisi-81','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(82,'2026-11-25 15:11:02','2026-11-25 15:11:02',82,'vi','Bài viết mẫu 80 - Et labore distinctio voluptatem.','bai-viet-mau-80-et-labore-distinctio-voluptatem-82','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(83,'2026-11-25 15:11:02','2026-11-25 15:11:02',83,'vi','Bài viết mẫu 81 - Sint qui error vero saepe.','bai-viet-mau-81-sint-qui-error-vero-saepe-83','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(84,'2026-11-25 15:11:02','2026-11-25 15:11:02',84,'vi','Bài viết mẫu 82 - Omnis doloremque molestias voluptas.','bai-viet-mau-82-omnis-doloremque-molestias-voluptas-84','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(85,'2026-11-25 15:11:02','2026-11-25 15:11:02',85,'vi','Bài viết mẫu 83 - Officia iste quia error veniam quia.','bai-viet-mau-83-officia-iste-quia-error-veniam-quia-85','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(86,'2026-11-25 15:11:02','2026-11-25 15:11:02',86,'vi','Bài viết mẫu 84 - Aut ullam assumenda rem eum.','bai-viet-mau-84-aut-ullam-assumenda-rem-eum-86','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(87,'2026-11-25 15:11:02','2026-11-25 15:11:02',87,'vi','Bài viết mẫu 85 - Ut quia ullam molestias deleniti placeat.','bai-viet-mau-85-ut-quia-ullam-molestias-deleniti-placeat-87','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(88,'2026-11-25 15:11:02','2026-11-25 15:11:02',88,'vi','Bài viết mẫu 86 - Rerum ratione ipsa hic perspiciatis.','bai-viet-mau-86-rerum-ratione-ipsa-hic-perspiciatis-88','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(89,'2026-11-25 15:11:02','2026-11-25 15:11:02',89,'vi','Bài viết mẫu 87 - Consequatur non voluptatum.','bai-viet-mau-87-consequatur-non-voluptatum-89','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(90,'2026-11-25 15:11:02','2026-11-25 15:11:02',90,'vi','Bài viết mẫu 88 - Nisi consequatur assumenda.','bai-viet-mau-88-nisi-consequatur-assumenda-90','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(91,'2026-11-25 15:11:02','2026-11-25 15:11:02',91,'vi','Bài viết mẫu 89 - Aliquid sed nam voluptatem dolorem quam.','bai-viet-mau-89-aliquid-sed-nam-voluptatem-dolorem-quam-91','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(92,'2026-11-25 15:11:02','2026-11-25 15:11:02',92,'vi','Bài viết mẫu 90 - Magnam magni sunt.','bai-viet-mau-90-magnam-magni-sunt-92','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(93,'2026-11-25 15:11:02','2026-11-25 15:11:02',93,'vi','Bài viết mẫu 91 - Excepturi sit corporis et.','bai-viet-mau-91-excepturi-sit-corporis-et-93','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(94,'2026-11-25 15:11:02','2026-11-25 15:11:02',94,'vi','Bài viết mẫu 92 - Ipsam voluptatem excepturi impedit sunt.','bai-viet-mau-92-ipsam-voluptatem-excepturi-impedit-sunt-94','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(95,'2026-11-25 15:11:02','2026-11-25 15:11:02',95,'vi','Bài viết mẫu 93 - Autem provident esse amet.','bai-viet-mau-93-autem-provident-esse-amet-95','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(96,'2026-11-25 15:11:02','2026-11-25 15:11:02',96,'vi','Bài viết mẫu 94 - Ad pariatur maiores provident sit eum.','bai-viet-mau-94-ad-pariatur-maiores-provident-sit-eum-96','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(97,'2026-11-25 15:11:02','2026-11-25 15:11:02',97,'vi','Bài viết mẫu 95 - Voluptatibus harum laudantium quos ipsum.','bai-viet-mau-95-voluptatibus-harum-laudantium-quos-ipsum-97','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(98,'2026-11-25 15:11:02','2026-11-25 15:11:02',98,'vi','Bài viết mẫu 96 - Error qui voluptatem rerum id doloremque.','bai-viet-mau-96-error-qui-voluptatem-rerum-id-doloremque-98','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(99,'2026-11-25 15:11:02','2026-11-25 15:11:02',99,'vi','Bài viết mẫu 97 - Et consectetur qui natus vel ad.','bai-viet-mau-97-et-consectetur-qui-natus-vel-ad-99','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(100,'2026-11-25 15:11:02','2026-11-25 15:11:02',100,'vi','Bài viết mẫu 98 - Qui et iste aperiam nihil laboriosam.','bai-viet-mau-98-qui-et-iste-aperiam-nihil-laboriosam-100','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(101,'2026-11-25 15:11:02','2026-11-25 15:11:02',101,'vi','Bài viết mẫu 99 - A molestias illo fugiat.','bai-viet-mau-99-a-molestias-illo-fugiat-101','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL),(102,'2026-11-25 15:11:02','2026-11-25 15:11:02',102,'vi','Bài viết mẫu 100 - Reprehenderit quia eos sunt.','bai-viet-mau-100-reprehenderit-quia-eos-sunt-102','Nội dung mẫu được tạo để kiểm tra danh sách bài viết, ảnh đại diện và giao diện storefront.','Đây là nội dung mẫu của bài viết. Dữ liệu này phục vụ kiểm tra hiển thị và phân trang.',NULL,NULL,NULL);
/*!40000 ALTER TABLE `post_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `view_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` bigint unsigned DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `author_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_uuid_unique` (`uuid`),
  KEY `posts_status_index` (`status`),
  KEY `posts_author_id_index` (`author_id`),
  KEY `posts_is_featured_index` (`is_featured`),
  KEY `posts_image_foreign` (`image`),
  CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_image_foreign` FOREIGN KEY (`image`) REFERENCES `media` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'ed35fec5-ff47-4fd0-8999-21b8e47bdf4d','draft',0,0,'2026-09-08 08:44:01','2026-09-14 08:54:32',NULL,'2026-09-14 08:54:00',NULL,1),(2,'846bc4f8-f343-48df-95fe-35aa8bf04a67','published',0,6,'2026-09-08 08:57:01','2026-11-22 06:32:38',27,'2026-09-09 09:18:00',NULL,1),(3,'9e3e565c-6882-3f3d-86ff-c576e1eadc77','draft',1,3852,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,NULL,NULL,1),(4,'d8280cf8-25f3-390c-91c6-d11e2db4e80a','draft',0,2669,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,NULL,NULL,1),(5,'1171ec9f-d4de-3b22-be0b-476635f60e01','draft',0,2127,'2026-11-25 15:11:02','2026-11-25 15:11:02',20,NULL,NULL,1),(6,'a77ff989-2367-3aab-a8bc-ef89db1ef6ab','published',0,22,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,'2026-10-17 15:11:02',NULL,1),(7,'434d5b29-5df6-329d-a4cb-ca6a6c721e4c','published',0,2752,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-05-30 15:11:02',NULL,1),(8,'e5dddec4-a17e-3f6e-9875-50f54e903827','published',0,800,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,'2026-09-18 15:11:02',NULL,1),(9,'9ba06450-0dc4-318a-9315-50cc147e62f1','published',0,4064,'2026-11-25 15:11:02','2026-11-25 15:11:02',16,'2026-11-22 15:11:02',NULL,1),(10,'6f44527a-54eb-3b2f-b835-9e5d3b4f8f28','published',0,1419,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-06-03 15:11:02',NULL,1),(11,'f43bb1eb-ff51-3061-8054-f611418ffb21','published',1,2722,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,'2026-07-16 15:11:02',NULL,1),(12,'d61069ad-0af6-3c55-88c8-53ffc9b484c9','draft',0,4606,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,NULL,NULL,1),(13,'f4555d54-db3e-35e7-8f0c-98088e2c2ddc','published',0,2630,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,'2026-10-19 15:11:02',NULL,1),(14,'35c3265f-88d7-3f1c-9b2d-0f5dd620e247','published',0,3342,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,'2026-06-05 15:11:02',NULL,1),(15,'ef7a1ae6-391f-3c8f-9a2b-90ae06025661','published',0,4569,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,'2026-07-27 15:11:02',NULL,1),(16,'58f42ecb-e2c8-37e7-88b4-622fbae4bd06','draft',0,2306,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,NULL,NULL,1),(17,'d0d43d77-7821-33ab-89a1-feaeafe09fe7','draft',0,2017,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,NULL,NULL,1),(18,'f7d56c6b-8e3f-3df3-a77f-71d35a8cca97','published',0,3336,'2026-11-25 15:11:02','2026-11-25 15:11:02',20,'2026-10-09 15:11:02',NULL,1),(19,'2c4c16dd-a767-3d15-ba74-d89915b19e23','published',0,4590,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,'2026-07-11 15:11:02',NULL,1),(20,'0db3b6da-4fb1-316c-8c65-204ea18c70f4','published',0,2465,'2026-11-25 15:11:02','2026-11-25 15:11:02',16,'2026-11-20 15:11:02',NULL,1),(21,'743541e8-2eae-38b9-831e-78b10385fbc4','draft',0,2863,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,NULL,NULL,1),(22,'bff73035-cc10-3d4f-9bdd-fd88d29581f7','published',0,3067,'2026-11-25 15:11:02','2026-11-25 15:11:02',26,'2026-06-22 15:11:02',NULL,1),(23,'70bb31cf-41fd-3239-9028-f0be7f426d30','draft',0,4056,'2026-11-25 15:11:02','2026-11-25 15:11:02',20,NULL,NULL,1),(24,'2f1870ba-7f9f-3f8f-b7bf-82a6c243a6dc','published',0,2201,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,'2026-10-13 15:11:02',NULL,1),(25,'ec52b76d-c7b0-3791-817a-4c9be3ef3a5b','published',0,2534,'2026-11-25 15:11:02','2026-11-25 15:11:02',16,'2026-08-25 15:11:02',NULL,1),(26,'d55c45c3-17f6-3e7b-94bb-9d7af073241c','published',1,1193,'2026-11-25 15:11:02','2026-11-25 15:11:02',26,'2026-06-24 15:11:02',NULL,1),(27,'b6b297b4-5a87-31b7-b59f-0ee0a95b11f0','draft',0,2027,'2026-11-25 15:11:02','2026-11-25 15:11:02',16,NULL,NULL,1),(28,'cc2b4e0f-44a1-307e-8ad0-8eda6c14ee52','published',0,701,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'2026-09-30 15:11:02',NULL,1),(29,'05637b7b-4b5a-3145-ae5b-10ba86debf39','published',0,486,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,'2026-10-06 15:11:02',NULL,1),(30,'dfd2d1ea-ca9b-31bc-962e-64838ee8cec0','published',0,1344,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'2026-08-22 15:11:02',NULL,1),(31,'4d2b698b-78e2-3d45-b668-98c67da5e256','published',0,784,'2026-11-25 15:11:02','2026-11-25 15:11:02',16,'2026-08-30 15:11:02',NULL,1),(32,'4b0ba53e-0876-3ecc-b952-f1b75e011140','published',0,762,'2026-11-25 15:11:02','2026-11-25 15:11:02',21,'2026-09-19 15:11:02',NULL,1),(33,'e4e03c8d-da33-3e21-a14e-add61f9b5245','published',0,325,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,'2026-10-28 15:11:02',NULL,1),(34,'a7c8ddf6-711e-35f8-8256-a5bd31ab4cf4','published',0,3044,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-10-26 15:11:02',NULL,1),(35,'6cf58c4a-df4a-34e6-a92a-5624f4390f91','draft',0,1758,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,NULL,NULL,1),(36,'db342d5c-05b2-340e-8ebb-05e4ab5157f0','published',0,3961,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,'2026-06-28 15:11:02',NULL,1),(37,'1695c1c9-53b0-334f-81b6-7b43f99a87c1','published',0,178,'2026-11-25 15:11:02','2026-11-25 15:11:02',16,'2026-07-02 15:11:02',NULL,1),(38,'f0f6bc85-c1f4-3e1f-9c09-3af93dfc9c4b','draft',1,1724,'2026-11-25 15:11:02','2026-11-25 15:11:02',20,NULL,NULL,1),(39,'bc66bdf9-ad7e-3297-aae3-c8b5905243af','published',1,4749,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-08-20 15:11:02',NULL,1),(40,'adf19149-a88e-37ff-8f6a-993dde3d1c6f','published',0,160,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,'2026-08-11 15:11:02',NULL,1),(41,'cfc36492-6dc3-35d2-a031-311a77a56a9b','published',0,3759,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'2026-07-14 15:11:02',NULL,1),(42,'0f5b8648-6946-3fc6-9703-f4cd13677946','published',0,3167,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,'2026-11-07 15:11:02',NULL,1),(43,'94b91d81-ad0c-3775-a2ff-85d2dd4a4aa6','draft',1,3257,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,NULL,NULL,1),(44,'a2b31661-b1e9-314a-8f8b-39a484833d5e','published',0,2945,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,'2026-05-31 15:11:02',NULL,1),(45,'110ae04e-c96e-3292-a98c-dd7bda6c1be0','published',1,3034,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,'2026-10-08 15:11:02',NULL,1),(46,'f9197be3-56ed-3579-be2a-22e242155912','published',0,1010,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,'2026-08-08 15:11:02',NULL,1),(47,'f347a635-4c41-3b62-bd0e-a2ca74555cfc','published',0,2215,'2026-11-25 15:11:02','2026-11-25 15:11:02',23,'2026-08-26 15:11:02',NULL,1),(48,'c34afff8-ba3d-35c8-94b8-0c1302efae5e','published',0,3426,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,'2026-07-01 15:11:02',NULL,1),(49,'49b9888b-967f-350c-9a09-5b30c4bdf909','published',0,277,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-06-15 15:11:02',NULL,1),(50,'aba8c8b6-91da-3eaf-9599-a4656bd18c65','published',0,3653,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,'2026-11-23 15:11:02',NULL,1),(51,'24670405-f3e5-35dd-a041-5b99a7044fab','draft',0,625,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,NULL,NULL,1),(52,'646d1ac1-d7e1-3927-939d-de1cd5d6807c','draft',0,3874,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,NULL,NULL,1),(53,'415e56a3-7cd4-3a07-bb72-e6021f544b5d','published',1,2425,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,'2026-10-16 15:11:02',NULL,1),(54,'57a1f397-3544-3e60-a440-1a8f083206de','published',1,1748,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'2026-08-30 15:11:02',NULL,1),(55,'5a71c4bb-9e58-358b-843d-a8cbe1a4e558','published',0,1414,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,'2026-06-05 15:11:02',NULL,1),(56,'9d6b754c-3e18-3bb0-916c-72aa19a51a82','draft',0,4351,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,NULL,NULL,1),(57,'db53ec9c-10ce-3780-8e59-a825f226e1e1','published',0,3060,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'2026-07-22 15:11:02',NULL,1),(58,'7af77a6f-17e6-3f16-b7a9-538b131317d3','published',0,2492,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,'2026-09-10 15:11:02',NULL,1),(59,'56263435-e4b8-374e-8d70-e9e7a5b8889c','draft',0,909,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,NULL,NULL,1),(60,'206811fe-3783-3f44-9dd1-bf8e91ee9e56','published',0,557,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,'2026-11-10 15:11:02',NULL,1),(61,'1da51949-e84a-3611-adf7-853da4e4dc61','published',0,1856,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,'2026-10-02 15:11:02',NULL,1),(62,'000bed31-b234-3b52-a0af-a93a0f5d699a','published',0,1620,'2026-11-25 15:11:02','2026-11-25 15:11:02',26,'2026-06-08 15:11:02',NULL,1),(63,'a245f6c0-bff9-36e3-bf58-5d034e941fdc','published',1,755,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,'2026-09-17 15:11:02',NULL,1),(64,'efbaaf10-2a10-397c-86f4-4a95d48b9dd0','published',1,1450,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-08-31 15:11:02',NULL,1),(65,'8be423ca-3e99-3702-a6e9-7b9aeac962de','published',0,4826,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,'2026-11-24 15:11:02',NULL,1),(66,'666c7e8c-e90e-365d-bd6d-8cc38563273c','draft',0,4797,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,NULL,NULL,1),(67,'ef96094b-ac6a-3ae2-afec-935cddd32dbc','draft',0,1219,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,NULL,NULL,1),(68,'967ea2b1-4974-3002-8c46-6cf38baabb49','draft',0,2464,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,NULL,NULL,1),(69,'2f01b119-7345-3845-bb6b-daf1bdb9f1ea','draft',0,3862,'2026-11-25 15:11:02','2026-11-25 15:11:02',21,NULL,NULL,1),(70,'73d21d0c-07ea-3b7c-92b0-b554f2d7e952','draft',1,1243,'2026-11-25 15:11:02','2026-11-25 15:11:02',15,NULL,NULL,1),(71,'45e751b0-29fd-3716-932c-e97aca880c4d','published',0,2381,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'2026-09-26 15:11:02',NULL,1),(72,'021580a0-4099-3b47-b9b3-a46e9ff06d70','published',0,2628,'2026-11-25 15:11:02','2026-11-25 15:11:02',21,'2026-07-08 15:11:02',NULL,1),(73,'8d70d4b2-86f8-3c35-b145-3f27d729a883','published',0,443,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-07-29 15:11:02',NULL,1),(74,'511f5b65-602e-393e-8280-bac15f1d6654','published',0,2509,'2026-11-25 15:11:02','2026-11-25 15:11:02',14,'2026-07-01 15:11:02',NULL,1),(75,'8578b39c-0f7e-3fc1-93aa-76643c63a8c2','published',0,4728,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-11-22 15:11:02',NULL,1),(76,'eb334d92-1e3d-330b-ac2d-3a9052af71e6','published',0,654,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,'2026-10-23 15:11:02',NULL,1),(77,'936f8552-cd02-327c-b3e0-180e8ad190af','published',0,3267,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-08-17 15:11:02',NULL,1),(78,'22a96613-4e67-3941-8622-183ea286a980','published',1,2473,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,'2026-06-12 15:11:02',NULL,1),(79,'5d600c2b-a309-3970-a686-5b0c7c3b3d52','published',0,53,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,'2026-09-23 15:11:02',NULL,1),(80,'8e47d51e-a81f-360b-8f57-9697690c912a','published',0,1219,'2026-11-25 15:11:02','2026-11-25 15:11:02',20,'2026-08-26 15:11:02',NULL,1),(81,'80327e65-b879-3ba8-8035-5c8b3ce04be4','published',0,638,'2026-11-25 15:11:02','2026-11-25 15:11:02',19,'2026-07-15 15:11:02',NULL,1),(82,'3512a6ff-d025-3b15-9733-59c10dc304d6','published',0,4279,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-11-01 15:11:02',NULL,1),(83,'190e7cc9-90a7-31f6-bcfc-f1ae9a904099','published',0,847,'2026-11-25 15:11:02','2026-11-25 15:11:02',21,'2026-09-29 15:11:02',NULL,1),(84,'c82fa251-4be0-36ff-b728-a531d63c83d9','draft',0,3140,'2026-11-25 15:11:02','2026-11-25 15:11:02',22,NULL,NULL,1),(85,'94553da1-098b-3cdb-bb2b-fb3755bb44d5','draft',0,3358,'2026-11-25 15:11:02','2026-11-25 15:11:02',25,NULL,NULL,1),(86,'2b31f8b7-a480-33d0-825f-d1bb3f9e5f04','draft',0,134,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,NULL,NULL,1),(87,'9f18ba5a-d8fd-3574-980c-f1b07f0828d1','published',0,988,'2026-11-25 15:11:02','2026-11-25 15:11:02',24,'2026-08-27 15:11:02',NULL,1),(88,'e037136a-a393-3b3b-803b-4a5fc95d8c33','published',0,4543,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-11-18 15:11:02',NULL,1),(89,'dfb13fc2-daa4-39e7-b62b-fa9a953dea5d','published',0,3893,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,'2026-10-13 15:11:02',NULL,1),(90,'b47ed43e-092b-304d-b308-fa1dcb555407','published',0,1983,'2026-11-25 15:11:02','2026-11-25 15:11:02',18,'2026-11-03 15:11:02',NULL,1),(91,'261fc1d6-0f3b-3ac9-b9c8-a3533fb26211','draft',0,2812,'2026-11-25 15:11:02','2026-11-25 15:11:02',24,NULL,NULL,1),(92,'88b877b5-384e-3314-bae3-ac3138067bfd','published',0,1264,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-10-31 15:11:02',NULL,1),(93,'bb58cbce-d980-349f-af0c-0f11f539694c','published',0,3336,'2026-11-25 15:11:02','2026-11-25 15:11:02',26,'2026-08-07 15:11:02',NULL,1),(94,'1c039cd8-2742-3d97-8514-42b3b9526abd','published',0,2941,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-08-18 15:11:02',NULL,1),(95,'be33afdf-638b-3451-9011-446b4e07b874','published',1,3040,'2026-11-25 15:11:02','2026-11-25 15:11:02',27,'2026-10-05 15:11:02',NULL,1),(96,'011a0c78-99b1-33c6-8218-4864862c78a4','published',0,1416,'2026-11-25 15:11:02','2026-11-25 15:11:02',20,'2026-08-28 15:11:02',NULL,1),(97,'a4739cca-d04e-3e87-be67-9cfd0b0787c7','draft',0,3057,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,NULL,NULL,1),(98,'4e2bfefa-8aec-3283-8042-877fb9639670','draft',0,4637,'2026-11-25 15:11:02','2026-11-25 15:11:02',26,NULL,NULL,1),(99,'e89344c1-4d73-3b7f-b2ba-55882d3ce0a2','draft',0,3713,'2026-11-25 15:11:02','2026-11-25 15:11:02',17,NULL,NULL,1),(100,'a92526d6-a071-3592-b52f-4eadc4e53627','published',0,1466,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-08-30 15:11:02',NULL,1),(101,'6ac33e3d-3b2a-3a00-86ef-ac75624559a7','published',0,2175,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,'2026-10-04 15:11:02',NULL,1),(102,'17f3791b-5dd1-343e-8553-12d48f5eded5','draft',0,4386,'2026-11-25 15:11:02','2026-11-25 15:11:02',28,NULL,NULL,1);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute`
--

DROP TABLE IF EXISTS `product_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attribute` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `is_variation` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_attribute_product_id_attribute_id_unique` (`product_id`,`attribute_id`),
  KEY `product_attribute_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `product_attribute_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_attribute_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attribute`
--

LOCK TABLES `product_attribute` WRITE;
/*!40000 ALTER TABLE `product_attribute` DISABLE KEYS */;
INSERT INTO `product_attribute` VALUES (4,2,5,1,0,'2026-09-28 09:12:04','2026-09-28 09:41:45');
/*!40000 ALTER TABLE `product_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute_translations`
--

DROP TABLE IF EXISTS `product_attribute_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attribute_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_attr_trans_attr_locale_unique` (`attribute_id`,`locale`),
  UNIQUE KEY `pat_attribute_locale_unique` (`attribute_id`,`locale`),
  CONSTRAINT `product_attribute_translations_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attribute_translations`
--

LOCK TABLES `product_attribute_translations` WRITE;
/*!40000 ALTER TABLE `product_attribute_translations` DISABLE KEYS */;
INSERT INTO `product_attribute_translations` VALUES (4,4,'vi','Màu sắc','2026-09-23 07:23:01','2026-09-23 07:23:01'),(5,5,'vi','Kích thước','2026-09-23 07:24:00','2026-09-23 07:24:00'),(6,4,'en','Màu sắc','2026-09-23 07:24:19','2026-09-23 07:24:19');
/*!40000 ALTER TABLE `product_attribute_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute_value`
--

DROP TABLE IF EXISTS `product_attribute_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attribute_value` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `attribute_value_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_attribute_value_product_id_attribute_value_id_unique` (`product_id`,`attribute_value_id`),
  KEY `product_attribute_value_attribute_value_id_foreign` (`attribute_value_id`),
  CONSTRAINT `product_attribute_value_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `product_attribute_values` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_attribute_value_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attribute_value`
--

LOCK TABLES `product_attribute_value` WRITE;
/*!40000 ALTER TABLE `product_attribute_value` DISABLE KEYS */;
INSERT INTO `product_attribute_value` VALUES (7,2,8,'2026-09-28 09:12:04','2026-09-28 09:12:04'),(8,2,9,'2026-09-28 09:12:04','2026-09-28 09:12:04');
/*!40000 ALTER TABLE `product_attribute_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute_value_translations`
--

DROP TABLE IF EXISTS `product_attribute_value_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attribute_value_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attribute_value_id` bigint unsigned NOT NULL,
  `locale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pat_val_trans_unique` (`attribute_value_id`,`locale`),
  KEY `product_attribute_value_translations_locale_index` (`locale`),
  CONSTRAINT `product_attribute_value_translations_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `product_attribute_values` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attribute_value_translations`
--

LOCK TABLES `product_attribute_value_translations` WRITE;
/*!40000 ALTER TABLE `product_attribute_value_translations` DISABLE KEYS */;
INSERT INTO `product_attribute_value_translations` VALUES (1,6,'en','Màu đen','2026-10-05 01:53:38','2026-10-05 01:53:38'),(2,7,'en','Màu cam','2026-10-05 01:53:38','2026-10-05 01:53:38'),(3,8,'en','M','2026-10-05 01:53:38','2026-10-05 01:53:38'),(4,9,'en','L','2026-10-05 01:53:38','2026-10-05 01:53:38'),(5,10,'en','Red','2026-10-05 01:53:38','2026-10-05 01:53:38');
/*!40000 ALTER TABLE `product_attribute_value_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute_values`
--

DROP TABLE IF EXISTS `product_attribute_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attribute_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `color_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_attribute_values_uuid_unique` (`uuid`),
  KEY `pav_attribute_id_index` (`attribute_id`),
  CONSTRAINT `product_attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attribute_values`
--

LOCK TABLES `product_attribute_values` WRITE;
/*!40000 ALTER TABLE `product_attribute_values` DISABLE KEYS */;
INSERT INTO `product_attribute_values` VALUES (6,'0749d590-1bf0-420f-a843-13778c6a711a',4,'#1c1e21',0,'2026-09-23 07:23:01','2026-09-23 07:24:19'),(7,'6016fec7-bf17-4272-9ab9-234a4b785ff7',4,'#feaa34',2,'2026-09-23 07:23:01','2026-09-23 07:24:19'),(8,'4eb257c8-ec94-41d2-b7bc-4ee1402e4a0f',5,NULL,0,'2026-09-23 07:24:00','2026-09-23 07:24:00'),(9,'1da6fa85-8f76-48e5-8a81-bf1e99a36c9b',5,NULL,1,'2026-09-23 07:24:00','2026-09-23 07:24:00'),(10,'07675762-34e2-407e-8c66-85eed742a85a',6,'#ff0000',0,'2026-09-29 04:09:00','2026-09-29 04:09:00');
/*!40000 ALTER TABLE `product_attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attributes`
--

DROP TABLE IF EXISTS `product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'select',
  `is_filterable` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_attributes_uuid_unique` (`uuid`),
  UNIQUE KEY `product_attributes_code_product_unique` (`code`,`product_id`),
  KEY `product_attributes_product_id_index` (`product_id`),
  CONSTRAINT `product_attributes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attributes`
--

LOCK TABLES `product_attributes` WRITE;
/*!40000 ALTER TABLE `product_attributes` DISABLE KEYS */;
INSERT INTO `product_attributes` VALUES (4,'bf9049cc-1b4d-43f1-87b3-a0bdc618b15e','color','color',1,0,'2026-09-23 07:23:01','2026-09-23 07:24:19',NULL),(5,'6b2f1d73-8841-4282-b267-ebee24b76b08','size','select',1,0,'2026-09-23 07:24:00','2026-09-23 07:24:00',NULL),(6,'8c13579d-da03-479b-ac0e-15f84a42cc7e','check-color-99999','color',1,0,'2026-09-29 04:09:00','2026-09-29 04:09:00',NULL);
/*!40000 ALTER TABLE `product_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `rating` tinyint unsigned NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `helpful_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_reviews_uuid_unique` (`uuid`),
  KEY `product_reviews_product_id_status_index` (`product_id`,`status`),
  KEY `product_reviews_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
INSERT INTO `product_reviews` VALUES (1,'27bd349a-258e-4a3e-8739-1c39dd698c71',2,1,5,'Sản phẩm dùng rất tốt, đóng gói cẩn thận, giao hàng nhanh chóng!','approved',1,12,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(2,'d881987d-51a3-478c-ae9a-67e59483676c',2,2,4,'Chất lượng ổn trong tầm giá, nhân viên hỗ trợ nhiệt tình, sẽ ủng hộ tiếp.','approved',1,5,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(3,'f7d018c1-9289-4c12-bfb1-af879e52a95b',2,1,5,'Hàng chính hãng, tem mác niêm phong đầy đủ, rất ưng ý!','pending',0,0,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(4,'52b6aba6-51c4-441b-a3b3-2a43e3c23f78',2,2,3,'Giao hàng hơi chậm một chút nhưng sản phẩm dùng tạm ổn.','pending',1,1,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(5,'082c8240-56b3-4e35-8f28-560011b35121',2,1,1,'Shop lừa đảo, sản phẩm không giống hình chụp, đừng ai mua!','rejected',0,0,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(6,'7741afc5-9ca0-499f-90cc-63e60e8804db',2,2,5,'Đã mua lần thứ hai tại Licuti, chất lượng dịch vụ luôn tuyệt vời.','approved',1,8,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(7,'5f3e0f27-ac00-40ed-87fa-a552c08481b0',4,2,5,'Sản phẩm dùng rất tốt, đóng gói cẩn thận, giao hàng nhanh chóng!','approved',1,12,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(8,'2ffba286-58d7-4de9-8eb7-30bb2b380c1c',4,1,4,'Chất lượng ổn trong tầm giá, nhân viên hỗ trợ nhiệt tình, sẽ ủng hộ tiếp.','approved',1,5,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(9,'3842b90f-d5f1-49e9-beed-005cfe8cf873',4,2,5,'Hàng chính hãng, tem mác niêm phong đầy đủ, rất ưng ý!','pending',0,0,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(10,'d8f38c5f-b7cb-4f0d-a547-bbf5bb4c8037',4,1,3,'Giao hàng hơi chậm một chút nhưng sản phẩm dùng tạm ổn.','pending',1,1,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(11,'2516c176-6785-4ade-b1e4-db2773003ad6',4,2,1,'Shop lừa đảo, sản phẩm không giống hình chụp, đừng ai mua!','rejected',0,0,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL),(12,'e3b418f6-47c0-4c4c-9e6e-7b48bf6ad413',4,1,5,'Đã mua lần thứ hai tại Licuti, chất lượng dịch vụ luôn tuyệt vời.','approved',1,8,'2026-10-10 11:26:37','2026-10-10 11:26:37',NULL);
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_tag`
--

DROP TABLE IF EXISTS `product_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_tag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_tag_product_id_tag_id_unique` (`product_id`,`tag_id`),
  KEY `product_tag_tag_id_index` (`tag_id`),
  CONSTRAINT `product_tag_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_tag`
--

LOCK TABLES `product_tag` WRITE;
/*!40000 ALTER TABLE `product_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_translations`
--

DROP TABLE IF EXISTS `product_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_translations_product_id_locale_unique` (`product_id`,`locale`),
  UNIQUE KEY `product_translations_product_locale_unique` (`product_id`,`locale`),
  KEY `product_translations_locale_slug_index` (`locale`,`slug`),
  KEY `product_translations_slug_index` (`slug`),
  KEY `product_translations_locale_index` (`locale`),
  CONSTRAINT `product_translations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_translations`
--

LOCK TABLES `product_translations` WRITE;
/*!40000 ALTER TABLE `product_translations` DISABLE KEYS */;
INSERT INTO `product_translations` VALUES (1,1,'vi','E2E Product Smoke UPD','e2e-product-smoke',NULL,NULL,'2026-09-18 05:45:29','2026-09-18 05:45:33'),(2,1,'en','E2E Product Smoke','e2e-product-smoke',NULL,NULL,'2026-09-18 05:45:33','2026-09-18 05:45:33'),(3,2,'vi','Sản phẩm mới','san-pham-moi',NULL,NULL,'2026-09-28 08:47:52','2026-09-28 08:47:52'),(5,3,'vi','Fix test','fix-test-1790586282',NULL,NULL,'2026-09-28 09:04:42','2026-09-28 09:04:42'),(6,2,'en','Sản phẩm mới','san-pham-moi',NULL,NULL,'2026-09-28 09:12:04','2026-09-28 09:12:04'),(7,4,'vi','San pham check','san-pham-check',NULL,'Mo ta','2026-09-29 04:09:00','2026-09-29 04:09:00');
/*!40000 ALTER TABLE `product_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variant_attribute_values`
--

DROP TABLE IF EXISTS `product_variant_attribute_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variant_attribute_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` bigint unsigned NOT NULL,
  `attribute_value_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_variant_attribute_values_variant_id_foreign` (`variant_id`),
  KEY `product_variant_attribute_values_attribute_value_id_foreign` (`attribute_value_id`),
  CONSTRAINT `product_variant_attribute_values_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `product_attribute_values` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_variant_attribute_values_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variant_attribute_values`
--

LOCK TABLES `product_variant_attribute_values` WRITE;
/*!40000 ALTER TABLE `product_variant_attribute_values` DISABLE KEYS */;
INSERT INTO `product_variant_attribute_values` VALUES (3,3,8,'2026-09-28 09:12:04','2026-09-28 09:12:04'),(4,4,9,'2026-09-28 09:12:04','2026-09-28 09:12:04');
/*!40000 ALTER TABLE `product_variant_attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variant_attributes`
--

DROP TABLE IF EXISTS `product_variant_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variant_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `attribute_value_id` bigint unsigned DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_variant_attributes_variant_id_foreign` (`variant_id`),
  KEY `product_variant_attributes_attribute_id_foreign` (`attribute_id`),
  KEY `product_variant_attributes_attribute_value_id_foreign` (`attribute_value_id`),
  CONSTRAINT `product_variant_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_variant_attributes_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `product_attribute_values` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_variant_attributes_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variant_attributes`
--

LOCK TABLES `product_variant_attributes` WRITE;
/*!40000 ALTER TABLE `product_variant_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_variant_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `compare_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_uuid_unique` (`uuid`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (3,'69abcb88-dab8-43d1-8def-3f9cb618b6ff',2,'PRD-001',NULL,50000.00,NULL,NULL,10,'c2bcb11e-d04c-46b2-98e0-74e60e663074',1,0,'2026-09-28 09:12:04','2026-09-28 09:41:45'),(4,'6d379627-e986-4dc1-b3d8-6eba43934214',2,'PRD-002',NULL,35000.00,NULL,NULL,10,NULL,1,1,'2026-09-28 09:12:04','2026-09-28 09:41:45');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `compare_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `track_inventory` tinyint(1) NOT NULL DEFAULT '1',
  `weight` decimal(8,2) DEFAULT NULL,
  `product_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'physical',
  `length` decimal(8,2) DEFAULT NULL,
  `width` decimal(8,2) DEFAULT NULL,
  `height` decimal(8,2) DEFAULT NULL,
  `is_free_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `shipping_fee` decimal(15,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `is_tax_inclusive` tinyint(1) NOT NULL DEFAULT '1',
  `allow_backorder` tinyint(1) NOT NULL DEFAULT '0',
  `low_stock_threshold` int unsigned DEFAULT NULL,
  `min_order_quantity` int unsigned DEFAULT NULL,
  `max_order_quantity` int unsigned DEFAULT NULL,
  `sold_individually` tinyint(1) NOT NULL DEFAULT '0',
  `primary_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_uuid_unique` (`uuid`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  KEY `products_status_index` (`status`),
  KEY `products_is_featured_index` (`is_featured`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'41dd31bd-40e9-4226-95f1-c333f58f5952',NULL,NULL,'E2E-001',NULL,199000.00,NULL,NULL,10,1,NULL,'physical',NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,NULL,NULL,0,NULL,0,1,'published','2026-09-18 12:45:00','2026-09-18 05:45:29','2026-09-18 05:45:36','2026-09-18 05:45:36'),(2,'df05e69e-6263-463e-8cdd-c2cb83ed9925',NULL,NULL,'PRD-ZG5AP0GD',NULL,50000.00,75000.00,27000.00,10,1,NULL,'physical',NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,NULL,NULL,0,NULL,0,1,'draft','2026-09-28 15:46:00','2026-09-28 08:47:52','2026-09-28 08:47:52',NULL),(3,'c6e144d3-9701-40ad-bd75-049441f6b791',NULL,NULL,'FIX-TEST-1790586282',NULL,250000.00,NULL,NULL,10,1,NULL,'physical',NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,NULL,NULL,0,NULL,0,1,'draft',NULL,'2026-09-28 09:04:42','2026-09-28 09:04:42','2026-09-28 09:04:42'),(4,'5603021c-589b-4e65-a56a-37b0035a5144',1,3,'CHECK-99999',NULL,28990000.00,34990000.00,22000000.00,50,1,NULL,'physical',NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,NULL,NULL,0,NULL,0,1,'published',NULL,'2026-09-29 04:09:00','2026-09-29 04:09:00',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(3,1),(5,1),(7,1),(9,1),(11,1),(13,1),(15,1),(17,1),(19,1),(21,1),(23,1),(25,1),(27,1),(29,1),(31,1),(33,1),(35,1),(37,1),(39,1),(41,1),(43,1),(45,1),(47,1),(49,1),(51,1),(53,1),(55,1),(57,1),(59,1),(61,1),(63,1),(65,1),(67,1),(69,1),(71,1),(73,1),(75,1),(77,1),(79,1),(81,1),(83,1),(85,1),(87,1),(89,1),(91,1),(93,1),(95,1),(97,1),(99,1),(101,1),(103,1),(105,1),(107,1),(109,1),(111,1),(113,1),(115,1),(117,1),(119,1),(121,1),(123,1),(125,1),(127,1),(129,1),(131,1),(133,1),(135,1),(137,1),(139,1),(141,1),(143,1),(145,1),(147,1),(149,1),(151,1),(153,1),(155,1),(157,1),(159,1),(161,1),(163,1),(165,1),(167,1),(169,1),(171,1),(173,1),(175,1),(177,1),(179,1),(181,1),(183,1),(185,1),(187,1),(189,1),(191,1),(1,2),(3,2),(5,2),(7,2),(9,2),(11,2),(13,2),(15,2),(17,2),(19,2),(21,2),(23,2),(25,2),(27,2),(29,2),(31,2),(33,2),(35,2),(37,2),(39,2),(41,2),(43,2),(45,2),(47,2),(49,2),(51,2),(53,2),(55,2),(57,2),(59,2),(61,2),(63,2),(65,2),(67,2),(69,2),(71,2),(73,2),(75,2),(77,2),(79,2),(81,2),(83,2),(85,2),(87,2),(89,2),(91,2),(93,2),(95,2),(97,2),(99,2),(101,2),(103,2),(105,2),(107,2),(109,2),(111,2),(113,2),(115,2),(117,2),(119,2),(121,2),(123,2),(125,2),(127,2),(129,2),(131,2),(133,2),(135,2),(137,2),(139,2),(141,2),(143,2),(145,2),(147,2),(149,2),(151,2),(153,2),(155,2),(157,2),(159,2),(161,2),(163,2),(165,2),(167,2),(169,2),(171,2),(173,2),(175,2),(177,2),(179,2),(181,2),(183,2),(185,2),(187,2),(189,2),(191,2),(33,3),(35,3),(37,3),(41,3),(49,3),(51,3),(53,3),(55,3),(89,3),(97,3),(99,3),(101,3),(105,3),(107,3),(109,3),(111,3),(115,3),(117,3),(119,3),(123,3),(125,3),(153,3),(155,3),(157,3),(159,3),(163,3),(165,3),(167,3),(171,3),(173,3),(175,3),(179,3),(187,3),(189,3);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super-admin','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(2,'admin','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(3,'editor','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(4,'customer','web','2026-08-04 07:34:42','2026-08-04 07:34:42');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seo_metadata`
--

DROP TABLE IF EXISTS `seo_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seo_metadata` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seoable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seoable_id` bigint unsigned NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schema_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `robots_index` tinyint(1) NOT NULL DEFAULT '1',
  `robots_follow` tinyint(1) NOT NULL DEFAULT '1',
  `focus_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_score` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_metadata_unique_locale` (`seoable_type`,`seoable_id`,`locale`),
  KEY `seo_metadata_seoable_type_seoable_id_index` (`seoable_type`,`seoable_id`),
  KEY `seo_metadata_locale_index` (`locale`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seo_metadata`
--

LOCK TABLES `seo_metadata` WRITE;
/*!40000 ALTER TABLE `seo_metadata` DISABLE KEYS */;
INSERT INTO `seo_metadata` VALUES (1,'App\\Models\\PostCategory',4,'vi','Danh mục 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-08-20 10:52:22','2026-08-20 10:52:22'),(2,'App\\Models\\Post',1,'vi','Bài viết test tiếng việt',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-08 08:44:01','2026-09-14 08:54:46'),(3,'App\\Models\\Post',2,'vi','Bài viết kiểm tra lưu ảnh đại diện và auto slug','KhÃ³a há»c Laravel 11 thá»±c chiáº¿n toÃ n diá»‡n.',NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-08 08:57:01','2026-09-08 08:57:01'),(4,'App\\Models\\Page',6,'vi','SEO Title moi','SEO Desc moi',NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-16 07:08:59','2026-09-16 07:08:59'),(5,'App\\Models\\Category',1,'vi','E2E Category Smoke',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-18 05:44:56','2026-09-18 05:44:56'),(6,'App\\Models\\Product',1,'vi','E2E Product Smoke',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-18 05:45:29','2026-09-18 05:45:29'),(7,'App\\Models\\Product',2,'vi','Sản phẩm mới',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-28 08:47:52','2026-09-28 08:47:52');
/*!40000 ALTER TABLE `seo_metadata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('NtUtCPLDGVk0mCTbBhGTbND2onOl22VrWyvhmMNh',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWlg5dHhlYkFEckhzMzBxTTlWT3p4a0pFU0c0WDhIWHVHMGtuOVZ5QiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTg6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdDo4MS9hZG1pbi9ub3RpZmljYXRpb25zL2ZlZWQiO3M6NToicm91dGUiO3M6MjQ6ImFkbWluLm5vdGlmaWNhdGlvbnMuZmVlZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1795625625),('w4DBVPP2nm0XoOn5Q9PPh18vl14SrhczKGOGuBs4',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOW5pNk5FN3kzYlE5a0ZORjJjdlJBeUhFc01hU3J0TllYd2tiUnNtWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdDo4MSI7czo1OiJyb3V0ZSI7czoxNToic3RvcmVmcm9udC5ob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795617578),('Yy85BWMZbEVh8xKWpucdSfYymkbBNF4tuHkQpOjN',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV1N0bmF0NkJXWGJPU3lKOHUwSFZzY1o3NFJLNFk5eUtsbzRzdHJtViI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo1ODoiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0OjgxL2FkbWluL25vdGlmaWNhdGlvbnMvZmVlZCI7czo1OiJyb3V0ZSI7czoyNDoiYWRtaW4ubm90aWZpY2F0aW9ucy5mZWVkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795620308),('zaZfPryaplBx4WG0qb89om9n6PHLJyYzgCibeRxy',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRjI1Z0c4MkhvcUk2TmwzVkltcG1ZY0t3U2NpekhWeGJNNkhvbUM5MCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdDo4MSI7czo1OiJyb3V0ZSI7czoxNToic3RvcmVmcm9udC5ob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795617576);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_translatable` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_index` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','{\"vi\":\"Licuti CMS\",\"en\":\"Licuti CMS\"}','general','text','Tên website','Tên hiển thị trên tiêu đề trình duyệt và trang chủ',1,'2026-08-10 09:26:10','2026-10-05 10:00:25'),(2,'slogan','{\"vi\":\"N\\u1ec1n t\\u1ea3ng th\\u01b0\\u01a1ng m\\u1ea1i \\u0111i\\u1ec7n t\\u1eed\",\"en\":\"E-commerce platform\"}','general','text','Khẩu hiệu (Slogan)','Khẩu hiệu hiển thị dưới logo',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(3,'logo_url','','general','image','Logo website','Logo chính của website',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(4,'favicon_url','','general','image','Favicon','Icon nhỏ trên tab trình duyệt',0,'2026-08-10 09:26:10','2026-10-05 10:00:25'),(5,'contact_email','contact@licuti.com','general','text','Email liên hệ','Email dùng để khách hàng liên hệ',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(6,'contact_phone','0123456789','general','text','Hotline','Số điện thoại hotline',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(7,'contact_address','{\"vi\":\"H\\u00e0 N\\u1ed9i, Vi\\u1ec7t Nam\",\"en\":\"Hanoi, Vietnam\"}','general','textarea','Địa chỉ','Địa chỉ văn phòng / cửa hàng chính',1,'2026-08-10 09:26:10','2026-10-05 10:00:25'),(8,'seo_meta_title','{\"vi\":\"Licuti CMS - Trang ch\\u1ee7\",\"en\":\"Licuti CMS - Home\"}','seo','text','Meta Title mặc định','Thẻ title hiển thị trên Google nếu trang không có cấu hình riêng',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(9,'seo_meta_description','{\"vi\":\"H\\u1ec7 th\\u1ed1ng qu\\u1ea3n tr\\u1ecb n\\u1ed9i dung Licuti\",\"en\":\"Licuti content management system\"}','seo','textarea','Meta Description mặc định','Thẻ mô tả hiển thị trên kết quả tìm kiếm Google',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(10,'google_analytics_id','','seo','text','Google Analytics ID','Mã theo dõi GA (VD: G-XXXXXXXXXX)',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(11,'social_facebook','https://facebook.com/','social','text','Facebook URL','Link Fanpage Facebook',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(12,'social_youtube','https://youtube.com/','social','text','YouTube URL','Link kênh YouTube',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(13,'primary_color','#3b82f6','appearance','text','Màu chủ đạo (Primary Color)','Màu sắc chính cho nút bấm và điểm nhấn frontend',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(14,'footer_copyright','{\"vi\":\"\\u00a9 2026 Licuti CMS. All rights reserved.\",\"en\":\"\\u00a9 2026 Licuti CMS. All rights reserved.\"}','appearance','text','Footer Copyright','Dòng bản quyền dưới cùng trang web',1,'2026-08-10 09:26:10','2026-10-05 10:00:25'),(15,'mail_mailer','smtp','mail','text','Mail Mailer','Giao thức gửi mail (smtp, mailgun, ses...)',0,'2026-10-05 10:00:25','2026-10-05 10:00:25'),(16,'mail_host','sandbox.smtp.mailtrap.io','mail','text','Mail Host','Địa chỉ máy chủ SMTP',0,'2026-10-05 10:00:25','2026-10-05 10:00:25'),(17,'mail_port','2525','mail','number','Mail Port','Cổng kết nối SMTP (25, 465, 587, 2525)',0,'2026-10-05 10:00:25','2026-10-05 10:00:25'),(18,'mail_username','','mail','text','Mail Username','Tài khoản đăng nhập SMTP',0,'2026-10-05 10:00:25','2026-10-05 10:00:25'),(19,'mail_password','','mail','text','Mail Password','Mật khẩu ứng dụng SMTP',0,'2026-10-05 10:00:25','2026-10-05 10:00:25'),(20,'mail_encryption','tls','mail','text','Mail Encryption','Giao thức mã hóa (tls, ssl)',0,'2026-10-05 10:00:25','2026-10-05 10:00:25'),(21,'mail_from_address','no-reply@licuti.com','mail','text','Mail From Address','Email gửi mặc định',0,'2026-10-05 10:00:25','2026-10-05 10:00:25'),(22,'mail_from_name','Licuti System','mail','text','Mail From Name','Tên người gửi mặc định',0,'2026-10-05 10:00:25','2026-10-05 10:00:25');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_addresses`
--

DROP TABLE IF EXISTS `shipping_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `recipient_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ward` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VN',
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_addresses_uuid_unique` (`uuid`),
  KEY `shipping_addresses_order_id_foreign` (`order_id`),
  CONSTRAINT `shipping_addresses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_addresses`
--

LOCK TABLES `shipping_addresses` WRITE;
/*!40000 ALTER TABLE `shipping_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipping_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_uuid_unique` (`uuid`),
  KEY `tags_slug_index` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (5,'aabc2f4e-6dff-4b21-b978-8da3e38a04a9','Công Nghệ Test','cong-nghe-test',0,'2026-09-18 06:42:19','2026-09-18 06:42:19'),(6,'30351245-05ae-4015-b221-94539083bea0','Check Tag','check-tag-99999',0,'2026-09-29 04:09:00','2026-09-29 04:09:00');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `theme_settings`
--

DROP TABLE IF EXISTS `theme_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `theme_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `theme_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `theme_settings_theme_slug_key_unique` (`theme_slug`,`key`),
  KEY `theme_settings_theme_slug_index` (`theme_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `theme_settings`
--

LOCK TABLES `theme_settings` WRITE;
/*!40000 ALTER TABLE `theme_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `theme_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_media_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `status` enum('active','inactive','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `google_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_uuid_unique` (`uuid`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  UNIQUE KEY `users_facebook_id_unique` (`facebook_id`),
  KEY `users_status_index` (`status`),
  KEY `users_is_admin_index` (`is_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'344ad8af-bff3-4483-8489-79f8bcbc8190','System Administrator','admin@licuti.com',NULL,NULL,NULL,'$2y$12$ar1FrSmJ/ZaujpxKICjTAOe6adF7YwkvclXgwtHxGRXsx36.HsBKC',NULL,'9c0bf8b9-1206-4e32-b475-30058be8b368',NULL,NULL,'active',1,NULL,NULL,NULL,NULL,'IlRXZ6ZSyX2CDNrWAcre4iSELZRrhuaEaJGqotduI9LLJFx6nRnsslBAo4t5','2026-08-04 07:34:43','2026-10-01 07:25:04',NULL),(2,'6cca01f2-77bf-4045-82f5-2d62820a5adb','Layout Check','layoutcheck@example.com',NULL,NULL,NULL,'$2y$12$m.JgjZ.8iFJ/8gUcOIDh/ejvw2Rmcvgig0dDTSlJxvm5GqVoo2jk2',NULL,NULL,NULL,NULL,'active',1,NULL,NULL,NULL,NULL,NULL,'2026-09-28 10:09:17','2026-09-28 10:09:17',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouses`
--

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'licuti-cms-laravel'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-25 23:57:08
