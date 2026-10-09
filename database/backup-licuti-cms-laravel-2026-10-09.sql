-- MySQL dump 10.13  Distrib 5.7.44, for Win64 (x86_64)
--
-- Host: localhost    Database: licuti-cms-laravel
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banner_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `banner_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home_slider',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brand_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-app_settings','a:14:{s:9:\"site_name\";a:2:{s:2:\"vi\";s:10:\"Licuti CMS\";s:2:\"en\";s:10:\"Licuti CMS\";}s:6:\"slogan\";a:2:{s:2:\"vi\";s:40:\"Nền tảng thương mại điện tử\";s:2:\"en\";s:19:\"E-commerce platform\";}s:8:\"logo_url\";s:0:\"\";s:11:\"favicon_url\";s:0:\"\";s:13:\"contact_email\";s:18:\"contact@licuti.com\";s:13:\"contact_phone\";s:10:\"0123456789\";s:15:\"contact_address\";a:2:{s:2:\"vi\";s:21:\"Hà Nội, Việt Nam\";s:2:\"en\";s:14:\"Hanoi, Vietnam\";}s:14:\"seo_meta_title\";a:2:{s:2:\"vi\";s:24:\"Licuti CMS - Trang chủ\";s:2:\"en\";s:17:\"Licuti CMS - Home\";}s:20:\"seo_meta_description\";a:2:{s:2:\"vi\";s:43:\"Hệ thống quản trị nội dung Licuti\";s:2:\"en\";s:32:\"Licuti content management system\";}s:19:\"google_analytics_id\";s:0:\"\";s:15:\"social_facebook\";s:21:\"https://facebook.com/\";s:14:\"social_youtube\";s:20:\"https://youtube.com/\";s:13:\"primary_color\";s:7:\"#3b82f6\";s:16:\"footer_copyright\";a:2:{s:2:\"vi\";s:40:\"© 2026 Licuti CMS. All rights reserved.\";s:2:\"en\";s:40:\"© 2026 Licuti CMS. All rights reserved.\";}}',2106554065),('laravel-cache-brands:select','O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:16:\"App\\Models\\Brand\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:6:\"brands\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:3;s:4:\"uuid\";s:36:\"ea55dd1d-001a-4f6f-af46-2e79d2935af1\";s:4:\"logo\";N;s:7:\"website\";N;s:9:\"is_active\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"deleted_at\";N;}s:11:\"\0*\0original\";a:9:{s:2:\"id\";i:3;s:4:\"uuid\";s:36:\"ea55dd1d-001a-4f6f-af46-2e79d2935af1\";s:4:\"logo\";N;s:7:\"website\";N;s:9:\"is_active\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"deleted_at\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:12:\"translations\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"uuid\";i:1;s:4:\"logo\";i:2;s:7:\"website\";i:3;s:9:\"is_active\";i:4;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}',2106365459),('laravel-cache-categories:select','O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:19:\"App\\Models\\Category\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"categories\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:1;s:4:\"uuid\";s:36:\"8b17ade2-b312-47ef-bd32-ac6bd963cf85\";s:9:\"parent_id\";N;s:5:\"image\";N;s:4:\"icon\";N;s:9:\"is_active\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";}s:11:\"\0*\0original\";a:9:{s:2:\"id\";i:1;s:4:\"uuid\";s:36:\"8b17ade2-b312-47ef-bd32-ac6bd963cf85\";s:9:\"parent_id\";N;s:5:\"image\";N;s:4:\"icon\";N;s:9:\"is_active\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:12:\"translations\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:4:\"uuid\";i:1;s:9:\"parent_id\";i:2;s:5:\"image\";i:3;s:4:\"icon\";i:4;s:9:\"is_active\";i:5;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}',2106365459),('laravel-cache-languages_active','O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:4:\"name\";s:10:\"Vietnamese\";s:11:\"native_name\";s:14:\"Tiếng Việt\";s:4:\"flag\";N;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:4:\"name\";s:10:\"Vietnamese\";s:11:\"native_name\";s:14:\"Tiếng Việt\";s:4:\"flag\";N;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:11:\"native_name\";i:3;s:4:\"flag\";i:4;s:10:\"is_default\";i:5;s:9:\"is_active\";i:6;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:11:\"native_name\";s:7:\"English\";s:4:\"flag\";N;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:11:\"native_name\";s:7:\"English\";s:4:\"flag\";N;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:11:\"native_name\";i:3;s:4:\"flag\";i:4;s:10:\"is_default\";i:5;s:9:\"is_active\";i:6;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}',2106266182),('laravel-cache-product_attributes:for_product:2:v0','O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:3:{i:0;O:27:\"App\\Models\\ProductAttribute\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:18:\"product_attributes\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:6;s:4:\"uuid\";s:36:\"8c13579d-da03-479b-ac0e-15f84a42cc7e\";s:4:\"code\";s:17:\"check-color-99999\";s:4:\"type\";s:5:\"color\";s:13:\"is_filterable\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"product_id\";N;}s:11:\"\0*\0original\";a:9:{s:2:\"id\";i:6;s:4:\"uuid\";s:36:\"8c13579d-da03-479b-ac0e-15f84a42cc7e\";s:4:\"code\";s:17:\"check-color-99999\";s:4:\"type\";s:5:\"color\";s:13:\"is_filterable\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"product_id\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:13:\"is_filterable\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:2:{s:12:\"translations\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:6:\"values\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:32:\"App\\Models\\ProductAttributeValue\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:24:\"product_attribute_values\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:10;s:4:\"uuid\";s:36:\"07675762-34e2-407e-8c66-85eed742a85a\";s:12:\"attribute_id\";i:6;s:5:\"value\";s:3:\"Red\";s:10:\"color_code\";s:7:\"#ff0000\";s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:10;s:4:\"uuid\";s:36:\"07675762-34e2-407e-8c66-85eed742a85a\";s:12:\"attribute_id\";i:6;s:5:\"value\";s:3:\"Red\";s:10:\"color_code\";s:7:\"#ff0000\";s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-29 11:09:00\";s:10:\"updated_at\";s:19:\"2026-09-29 11:09:00\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"uuid\";i:1;s:12:\"attribute_id\";i:2;s:5:\"value\";i:3;s:10:\"color_code\";i:4;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:4:\"uuid\";i:1;s:10:\"product_id\";i:2;s:4:\"code\";i:3;s:4:\"type\";i:4;s:13:\"is_filterable\";i:5;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:27:\"App\\Models\\ProductAttribute\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:18:\"product_attributes\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:5;s:4:\"uuid\";s:36:\"6b2f1d73-8841-4282-b267-ebee24b76b08\";s:4:\"code\";s:4:\"size\";s:4:\"type\";s:6:\"select\";s:13:\"is_filterable\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"product_id\";N;}s:11:\"\0*\0original\";a:9:{s:2:\"id\";i:5;s:4:\"uuid\";s:36:\"6b2f1d73-8841-4282-b267-ebee24b76b08\";s:4:\"code\";s:4:\"size\";s:4:\"type\";s:6:\"select\";s:13:\"is_filterable\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"product_id\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:13:\"is_filterable\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:2:{s:12:\"translations\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:38:\"App\\Models\\ProductAttributeTranslation\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:30:\"product_attribute_translations\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:2:\"id\";i:5;s:12:\"attribute_id\";i:5;s:6:\"locale\";s:2:\"vi\";s:4:\"name\";s:14:\"Kích thước\";s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";}s:11:\"\0*\0original\";a:6:{s:2:\"id\";i:5;s:12:\"attribute_id\";i:5;s:6:\"locale\";s:2:\"vi\";s:4:\"name\";s:14:\"Kích thước\";s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:3:{i:0;s:12:\"attribute_id\";i:1;s:6:\"locale\";i:2;s:4:\"name\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:6:\"values\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:32:\"App\\Models\\ProductAttributeValue\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:24:\"product_attribute_values\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:8;s:4:\"uuid\";s:36:\"4eb257c8-ec94-41d2-b7bc-4ee1402e4a0f\";s:12:\"attribute_id\";i:5;s:5:\"value\";s:1:\"M\";s:10:\"color_code\";N;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:8;s:4:\"uuid\";s:36:\"4eb257c8-ec94-41d2-b7bc-4ee1402e4a0f\";s:12:\"attribute_id\";i:5;s:5:\"value\";s:1:\"M\";s:10:\"color_code\";N;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"uuid\";i:1;s:12:\"attribute_id\";i:2;s:5:\"value\";i:3;s:10:\"color_code\";i:4;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:32:\"App\\Models\\ProductAttributeValue\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:24:\"product_attribute_values\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:9;s:4:\"uuid\";s:36:\"1da6fa85-8f76-48e5-8a81-bf1e99a36c9b\";s:12:\"attribute_id\";i:5;s:5:\"value\";s:1:\"L\";s:10:\"color_code\";N;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:9;s:4:\"uuid\";s:36:\"1da6fa85-8f76-48e5-8a81-bf1e99a36c9b\";s:12:\"attribute_id\";i:5;s:5:\"value\";s:1:\"L\";s:10:\"color_code\";N;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-09-23 14:24:00\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:00\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"uuid\";i:1;s:12:\"attribute_id\";i:2;s:5:\"value\";i:3;s:10:\"color_code\";i:4;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:4:\"uuid\";i:1;s:10:\"product_id\";i:2;s:4:\"code\";i:3;s:4:\"type\";i:4;s:13:\"is_filterable\";i:5;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:2;O:27:\"App\\Models\\ProductAttribute\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:18:\"product_attributes\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:4;s:4:\"uuid\";s:36:\"bf9049cc-1b4d-43f1-87b3-a0bdc618b15e\";s:4:\"code\";s:5:\"color\";s:4:\"type\";s:5:\"color\";s:13:\"is_filterable\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";s:10:\"product_id\";N;}s:11:\"\0*\0original\";a:9:{s:2:\"id\";i:4;s:4:\"uuid\";s:36:\"bf9049cc-1b4d-43f1-87b3-a0bdc618b15e\";s:4:\"code\";s:5:\"color\";s:4:\"type\";s:5:\"color\";s:13:\"is_filterable\";i:1;s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";s:10:\"product_id\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:13:\"is_filterable\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:2:{s:12:\"translations\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:38:\"App\\Models\\ProductAttributeTranslation\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:30:\"product_attribute_translations\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:2:\"id\";i:4;s:12:\"attribute_id\";i:4;s:6:\"locale\";s:2:\"vi\";s:4:\"name\";s:10:\"Màu sắc\";s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:23:01\";}s:11:\"\0*\0original\";a:6:{s:2:\"id\";i:4;s:12:\"attribute_id\";i:4;s:6:\"locale\";s:2:\"vi\";s:4:\"name\";s:10:\"Màu sắc\";s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:23:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:3:{i:0;s:12:\"attribute_id\";i:1;s:6:\"locale\";i:2;s:4:\"name\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:38:\"App\\Models\\ProductAttributeTranslation\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:30:\"product_attribute_translations\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:2:\"id\";i:6;s:12:\"attribute_id\";i:4;s:6:\"locale\";s:2:\"en\";s:4:\"name\";s:10:\"Màu sắc\";s:10:\"created_at\";s:19:\"2026-09-23 14:24:19\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";}s:11:\"\0*\0original\";a:6:{s:2:\"id\";i:6;s:12:\"attribute_id\";i:4;s:6:\"locale\";s:2:\"en\";s:4:\"name\";s:10:\"Màu sắc\";s:10:\"created_at\";s:19:\"2026-09-23 14:24:19\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:3:{i:0;s:12:\"attribute_id\";i:1;s:6:\"locale\";i:2;s:4:\"name\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:6:\"values\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:32:\"App\\Models\\ProductAttributeValue\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:24:\"product_attribute_values\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:6;s:4:\"uuid\";s:36:\"0749d590-1bf0-420f-a843-13778c6a711a\";s:12:\"attribute_id\";i:4;s:5:\"value\";s:9:\"Màu đen\";s:10:\"color_code\";s:7:\"#1c1e21\";s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:6;s:4:\"uuid\";s:36:\"0749d590-1bf0-420f-a843-13778c6a711a\";s:12:\"attribute_id\";i:4;s:5:\"value\";s:9:\"Màu đen\";s:10:\"color_code\";s:7:\"#1c1e21\";s:13:\"display_order\";i:0;s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"uuid\";i:1;s:12:\"attribute_id\";i:2;s:5:\"value\";i:3;s:10:\"color_code\";i:4;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:32:\"App\\Models\\ProductAttributeValue\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:24:\"product_attribute_values\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";i:7;s:4:\"uuid\";s:36:\"6016fec7-bf17-4272-9ab9-234a4b785ff7\";s:12:\"attribute_id\";i:4;s:5:\"value\";s:8:\"Màu cam\";s:10:\"color_code\";s:7:\"#feaa34\";s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";i:7;s:4:\"uuid\";s:36:\"6016fec7-bf17-4272-9ab9-234a4b785ff7\";s:12:\"attribute_id\";i:4;s:5:\"value\";s:8:\"Màu cam\";s:10:\"color_code\";s:7:\"#feaa34\";s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-09-23 14:23:01\";s:10:\"updated_at\";s:19:\"2026-09-23 14:24:19\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:1:{s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:4:\"uuid\";i:1;s:12:\"attribute_id\";i:2;s:5:\"value\";i:3;s:10:\"color_code\";i:4;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:4:\"uuid\";i:1;s:10:\"product_id\";i:2;s:4:\"code\";i:3;s:4:\"type\";i:4;s:13:\"is_filterable\";i:5;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}',2106357458);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
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
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `flash_sales`
--

DROP TABLE IF EXISTS `flash_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flash_sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `folder_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_folders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/',
  `target` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (8,'0001_01_01_000000_create_users_table',1),(9,'0001_01_01_000001_create_cache_table',1),(10,'0001_01_01_000002_create_jobs_table',1),(11,'2026_05_05_171801_create_permission_tables',1),(12,'2026_05_05_172458_create_personal_access_tokens_table',1),(13,'2026_08_04_143144_create_media_folders_table',1),(14,'2026_08_04_143145_create_media_table',1),(15,'2026_08_10_161705_create_settings_table',2),(16,'2026_08_12_133142_create_languages_table',3),(19,'2026_08_15_125510_create_tags_table',4),(20,'2026_08_15_125519_create_posts_table',4),(21,'2026_08_15_125527_create_post_translations_table',4),(22,'2026_08_15_125536_create_post_tag_table',4),(23,'2026_08_15_125544_create_pages_table',4),(24,'2026_08_15_125553_create_page_translations_table',4),(25,'2026_08_15_125601_create_banners_table',4),(26,'2026_08_15_125610_create_banner_translations_table',4),(27,'2026_08_15_125618_create_menus_table',4),(28,'2026_08_15_125626_create_menu_items_table',4),(33,'2026_08_15_130950_create_brands_table',5),(34,'2026_08_15_130951_create_brand_translations_table',5),(35,'2026_08_15_130952_create_products_table',5),(36,'2026_08_15_130953_create_product_translations_table',5),(37,'2026_08_15_130954_create_product_attributes_table',5),(38,'2026_08_15_130955_create_product_attribute_translations_table',5),(39,'2026_08_15_130956_create_product_variants_table',5),(41,'2026_08_15_130958_create_carts_table',5),(42,'2026_08_15_130959_create_orders_table',5),(44,'2026_08_15_131001_create_payments_table',5),(45,'2026_08_15_131002_create_coupons_table',5),(46,'2026_08_15_131003_create_flash_sales_table',5),(47,'2026_08_15_131004_create_warehouses_table',5),(48,'2026_08_15_131005_create_inventories_table',5),(49,'2026_08_15_130948_create_categories_table',6),(50,'2026_08_15_130949_create_category_translations_table',6),(53,'2026_08_15_113902_create_post_categories_table',7),(54,'2026_08_15_113911_create_post_category_translations_table',7),(56,'2026_08_28_171216_add_columns_to_posts_and_translations_table',8),(57,'2026_08_28_175815_add_missing_columns_to_posts_table',9),(58,'2026_08_30_081215_rename_featured_image_to_image_in_posts_table',10),(59,'2026_09_05_111302_create_seo_metadata_table',10),(60,'2026_09_07_145027_add_schema_type_to_seo_metadata_table',11),(61,'2026_09_09_154700_create_post_category_post_table',12),(62,'2026_09_15_041000_add_status_to_pages_table',13),(63,'2026_09_15_063500_enhance_pages_module_schema',13),(64,'2026_09_15_070200_add_published_at_to_pages_table',13),(65,'2026_09_15_115301_add_soft_deletes_to_pages_table',13),(66,'2026_09_16_060100_drop_duplicate_meta_from_pages_table',14),(67,'2026_09_17_080000_enhance_tags_and_post_tag_tables',15),(68,'2026_09_17_081500_enhance_brands_and_translations_tables',16),(69,'2026_09_17_083000_enhance_product_attributes_and_values_tables',17),(70,'2026_09_17_084500_enhance_banners_and_translations_tables',18),(71,'2026_09_17_090000_enhance_menus_and_menu_items_tables',19),(72,'2026_09_17_091500_enhance_products_variants_and_reviews_tables',20),(73,'2026_09_17_093000_enhance_product_attributes_tables',21),(74,'2026_09_17_094500_enhance_banners_tables',22),(75,'2026_09_17_095500_enhance_menus_tables',23),(76,'2026_09_17_101000_enhance_products_tables',24),(77,'2026_09_22_000000_add_admin_access_permission',25),(78,'2026_09_23_060000_create_product_attribute_variant_pivots',26),(79,'2026_09_25_000000_add_product_translation_indexes',27),(80,'2026_09_26_060000_add_primary_image_to_products_table',28),(81,'2026_09_28_000000_add_variant_detail_columns_to_product_variants_table',29),(82,'2026_09_28_010000_enhance_products_for_shipping_taxonomy',29),(85,'2026_10_03_000000_drop_dimensions_from_products',30),(86,'2026_10_04_214557_create_product_attribute_value_translations_table',31),(87,'2026_10_05_112400_change_product_attributes_code_unique_to_scoped',32),(88,'2026_10_06_000000_fix_settings_vietnamese_encoding',32),(90,'2026_08_15_131000_create_payment_methods_table',34),(91,'2026_08_15_130957_create_product_reviews_table',35);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
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
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `page_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `page_template` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `published_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_uuid_unique` (`uuid`),
  UNIQUE KEY `payment_methods_code_unique` (`code`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'users.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(2,'users.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(3,'users.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(4,'users.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(5,'users.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(6,'users.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(7,'users.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(8,'users.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(9,'users.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(10,'users.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(11,'users.restore','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(12,'users.restore','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(13,'users.force-delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(14,'users.force-delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(15,'users.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(16,'users.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(17,'users.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(18,'users.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(19,'roles.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(20,'roles.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(21,'roles.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(22,'roles.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(23,'roles.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(24,'roles.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(25,'roles.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(26,'roles.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(27,'permissions.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(28,'permissions.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(29,'permissions.assign','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(30,'permissions.assign','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(31,'permissions.revoke','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(32,'permissions.revoke','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(33,'categories.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(34,'categories.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(35,'categories.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(36,'categories.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(37,'categories.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(38,'categories.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(39,'categories.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(40,'categories.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(41,'brands.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(42,'brands.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(43,'brands.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(44,'brands.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(45,'brands.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(46,'brands.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(47,'brands.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(48,'brands.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(49,'products.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(50,'products.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(51,'products.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(52,'products.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(53,'products.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(54,'products.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(55,'products.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(56,'products.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(57,'products.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(58,'products.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(59,'products.publish','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(60,'products.publish','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(61,'products.approve','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(62,'products.approve','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(63,'products.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(64,'products.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(65,'products.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(66,'products.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(67,'orders.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(68,'orders.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(69,'orders.view-all','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(70,'orders.view-all','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(71,'orders.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(72,'orders.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(73,'orders.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(74,'orders.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(75,'orders.cancel','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(76,'orders.cancel','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(77,'orders.refund','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(78,'orders.refund','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(79,'orders.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(80,'orders.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(81,'inventory.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(82,'inventory.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(83,'inventory.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(84,'inventory.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(85,'inventory.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(86,'inventory.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(87,'inventory.adjust','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(88,'inventory.adjust','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(89,'coupons.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(90,'coupons.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(91,'coupons.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(92,'coupons.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(93,'coupons.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(94,'coupons.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(95,'coupons.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(96,'coupons.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(97,'posts.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(98,'posts.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(99,'posts.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(100,'posts.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(101,'posts.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(102,'posts.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(103,'posts.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(104,'posts.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(105,'posts.publish','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(106,'posts.publish','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(107,'pages.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(108,'pages.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(109,'pages.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(110,'pages.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(111,'pages.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(112,'pages.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(113,'pages.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(114,'pages.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(115,'banners.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(116,'banners.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(117,'banners.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(118,'banners.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(119,'banners.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(120,'banners.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(121,'banners.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(122,'banners.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(123,'media.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(124,'media.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(125,'media.upload','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(126,'media.upload','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(127,'media.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(128,'media.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(129,'reports.sales','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(130,'reports.sales','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(131,'reports.inventory','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(132,'reports.inventory','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(133,'reports.customers','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(134,'reports.customers','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(135,'reports.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(136,'reports.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(137,'settings.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(138,'settings.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(139,'settings.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(140,'settings.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(141,'settings.general','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(142,'settings.general','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(143,'settings.payment','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(144,'settings.payment','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(145,'settings.email','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(146,'settings.email','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(147,'settings.sms','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(148,'settings.sms','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(149,'logs.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(150,'logs.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(151,'logs.clear','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(152,'logs.clear','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(153,'admin.access','web','2026-09-22 02:19:10','2026-09-22 02:19:10'),(154,'admin.access','api','2026-09-22 02:19:10','2026-09-22 02:19:10'),(155,'post-categories.view','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(156,'post-categories.view','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(157,'post-categories.create','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(158,'post-categories.create','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(159,'post-categories.update','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(160,'post-categories.update','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(161,'post-categories.delete','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(162,'post-categories.delete','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(163,'tags.view','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(164,'tags.view','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(165,'tags.create','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(166,'tags.create','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(167,'tags.update','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(168,'tags.update','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(169,'tags.delete','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(170,'tags.delete','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(171,'menus.view','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(172,'menus.view','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(173,'menus.create','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(174,'menus.create','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(175,'menus.update','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(176,'menus.update','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(177,'menus.delete','web','2026-09-22 02:32:42','2026-09-22 02:32:42'),(178,'menus.delete','api','2026-09-22 02:32:42','2026-09-22 02:32:42'),(179,'product-attributes.view','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(180,'product-attributes.view','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(181,'product-attributes.create','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(182,'product-attributes.create','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(183,'product-attributes.update','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(184,'product-attributes.update','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(185,'product-attributes.delete','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(186,'product-attributes.delete','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(187,'product-reviews.view','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(188,'product-reviews.view','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(189,'product-reviews.update','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(190,'product-reviews.update','api','2026-10-09 06:56:40','2026-10-09 06:56:40'),(191,'product-reviews.delete','web','2026-10-09 06:56:40','2026-10-09 06:56:40'),(192,'product-reviews.delete','api','2026-10-09 06:56:40','2026-10-09 06:56:40');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_category_post` (
  `post_id` bigint(20) unsigned NOT NULL,
  `post_category_id` bigint(20) unsigned NOT NULL,
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
INSERT INTO `post_category_post` VALUES (2,2),(1,3),(2,3);
/*!40000 ALTER TABLE `post_category_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_category_translations`
--

DROP TABLE IF EXISTS `post_category_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_category_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_category_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `tag_id` bigint(20) unsigned NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `post_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_translations_post_id_locale_unique` (`post_id`,`locale`),
  KEY `post_translations_slug_index` (`slug`),
  CONSTRAINT `post_translations_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_translations`
--

LOCK TABLES `post_translations` WRITE;
/*!40000 ALTER TABLE `post_translations` DISABLE KEYS */;
INSERT INTO `post_translations` VALUES (1,'2026-09-08 08:44:01','2026-09-14 08:54:32',1,'vi','Bài viết test tiếng việt','Bài viết test tiếng việt',NULL,NULL,NULL,NULL,NULL),(2,'2026-09-08 08:57:01','2026-09-08 08:57:01',2,'vi','Bài viết kiểm tra lưu ảnh đại diện và auto slug','laravel-11-chuyen-sau','KhÃ³a há»c Laravel 11 thá»±c chiáº¿n toÃ n diá»‡n.',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `post_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `view_count` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `author_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_uuid_unique` (`uuid`),
  KEY `posts_status_index` (`status`),
  KEY `posts_author_id_index` (`author_id`),
  KEY `posts_is_featured_index` (`is_featured`),
  CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'ed35fec5-ff47-4fd0-8999-21b8e47bdf4d','draft',0,0,'2026-09-08 08:44:01','2026-09-14 08:54:32',NULL,'2026-09-14 08:54:00',NULL,1),(2,'846bc4f8-f343-48df-95fe-35aa8bf04a67','published',0,0,'2026-09-08 08:57:01','2026-09-11 10:31:23','9c0bf8b9-1206-4e32-b475-30058be8b368','2026-09-09 09:18:00',NULL,1);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute`
--

DROP TABLE IF EXISTS `product_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `is_variation` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_value` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `attribute_value_id` bigint(20) unsigned NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_value_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_value_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `color_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'select',
  `is_filterable` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `helpful_count` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_reviews_uuid_unique` (`uuid`),
  KEY `product_reviews_product_id_status_index` (`product_id`,`status`),
  KEY `product_reviews_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_tag`
--

DROP TABLE IF EXISTS `product_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `tag_id` bigint(20) unsigned NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variant_attribute_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` bigint(20) unsigned NOT NULL,
  `attribute_value_id` bigint(20) unsigned NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variant_attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` bigint(20) unsigned NOT NULL,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `attribute_value_id` bigint(20) unsigned DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `compare_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int(11) NOT NULL DEFAULT '0',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `compare_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT '0',
  `track_inventory` tinyint(1) NOT NULL DEFAULT '1',
  `weight` decimal(8,2) DEFAULT NULL,
  `product_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'physical',
  `length` decimal(8,2) DEFAULT NULL,
  `width` decimal(8,2) DEFAULT NULL,
  `height` decimal(8,2) DEFAULT NULL,
  `is_free_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `shipping_fee` decimal(15,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `is_tax_inclusive` tinyint(1) NOT NULL DEFAULT '1',
  `allow_backorder` tinyint(1) NOT NULL DEFAULT '0',
  `low_stock_threshold` int(10) unsigned DEFAULT NULL,
  `min_order_quantity` int(10) unsigned DEFAULT NULL,
  `max_order_quantity` int(10) unsigned DEFAULT NULL,
  `sold_individually` tinyint(1) NOT NULL DEFAULT '0',
  `primary_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
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
INSERT INTO `role_has_permissions` VALUES (1,1),(3,1),(5,1),(7,1),(9,1),(11,1),(13,1),(15,1),(17,1),(19,1),(21,1),(23,1),(25,1),(27,1),(29,1),(31,1),(33,1),(35,1),(37,1),(39,1),(41,1),(43,1),(45,1),(47,1),(49,1),(51,1),(53,1),(55,1),(57,1),(59,1),(61,1),(63,1),(65,1),(67,1),(69,1),(71,1),(73,1),(75,1),(77,1),(79,1),(81,1),(83,1),(85,1),(87,1),(89,1),(91,1),(93,1),(95,1),(97,1),(99,1),(101,1),(103,1),(105,1),(107,1),(109,1),(111,1),(113,1),(115,1),(117,1),(119,1),(121,1),(123,1),(125,1),(127,1),(129,1),(131,1),(133,1),(135,1),(137,1),(139,1),(141,1),(143,1),(145,1),(147,1),(149,1),(151,1),(153,1),(155,1),(157,1),(159,1),(161,1),(163,1),(165,1),(167,1),(169,1),(171,1),(173,1),(175,1),(177,1),(179,1),(181,1),(183,1),(185,1),(187,1),(189,1),(191,1),(1,2),(3,2),(5,2),(7,2),(9,2),(11,2),(13,2),(15,2),(17,2),(19,2),(21,2),(23,2),(25,2),(27,2),(29,2),(31,2),(33,2),(35,2),(37,2),(39,2),(41,2),(43,2),(45,2),(47,2),(49,2),(51,2),(53,2),(55,2),(57,2),(59,2),(61,2),(63,2),(65,2),(67,2),(69,2),(71,2),(73,2),(75,2),(77,2),(79,2),(81,2),(83,2),(85,2),(87,2),(89,2),(91,2),(93,2),(95,2),(97,2),(99,2),(101,2),(103,2),(105,2),(107,2),(109,2),(111,2),(113,2),(115,2),(117,2),(119,2),(121,2),(123,2),(125,2),(127,2),(129,2),(131,2),(133,2),(135,2),(137,2),(139,2),(141,2),(143,2),(145,2),(147,2),(149,2),(151,2),(153,2),(155,2),(157,2),(159,2),(161,2),(163,2),(165,2),(167,2),(169,2),(171,2),(173,2),(175,2),(177,2),(179,2),(181,2),(183,2),(185,2),(187,2),(189,2),(191,2),(33,3),(35,3),(37,3),(41,3),(49,3),(51,3),(53,3),(55,3),(97,3),(99,3),(101,3),(105,3),(107,3),(109,3),(111,3),(115,3),(117,3),(119,3),(123,3),(125,3),(153,3),(155,3),(157,3),(159,3),(163,3),(165,3),(167,3),(171,3),(173,3),(175,3),(179,3);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seo_metadata` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seoable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seoable_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schema_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `robots_index` tinyint(1) NOT NULL DEFAULT '1',
  `robots_follow` tinyint(1) NOT NULL DEFAULT '1',
  `focus_keyword` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_score` int(11) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
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
INSERT INTO `sessions` VALUES ('0obQaWJ3brqV7nsQXZBYTeq2zmFpqXoEsCG6lkEn',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoidFNtQmRld2ZKSHZxZUJ5Z25qczV5UjI1ZmEzZ09hODlmbTYwaE1OMCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjUwOiJodHRwOi8vbGljdXRpLWNtcy1sYXJhdmVsLnRlc3QvYWRtaW4vc2V0dGluZ3MvbWFpbCI7czo1OiJyb3V0ZSI7czoxOToiYWRtaW4uc2V0dGluZ3MuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1791194456),('1duFOUgq5PBw0yLy6974qtfBo32CugIHov5vN2M9',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaUJCTGM4Y3NMVG1JSHkyRkxtRXkzUlcxc0lLV0JpcUtrZ0dYdTY4UyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1791187096),('1PJe7FlpWDyfCmWCpT8XXoQhPWy2VKMHM10yB4hq',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMDh4RlplRzhwd25qcVI1Um1JS2Y4WTBGOU00bmJmaXhIV1VIVnNVMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTQ6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9jYXRlZ29yaWVzL2NyZWF0ZSI7czo1OiJyb3V0ZSI7czoyMzoiYWRtaW4uY2F0ZWdvcmllcy5jcmVhdGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1790992611),('2Vwa2Eg0ZPucrrtHb0UiuA3yvEbKa6stV3AnYuHa',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYnc5bmNkRkcwTDZhemZHdGYzSE96a0VMbUdGNVhTR0ZzSllHOGZuayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0LWF0dHJpYnV0ZXMiO3M6NToicm91dGUiO3M6MzA6ImFkbWluLnByb2R1Y3QtYXR0cmlidXRlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1791191684),('3MlvbWGqX5bSGjWmSV4gWngQYCN5Purizm1IE7EK',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoieFdoMUlFeW1kc0ZSUTAwRVRzeXdOeDdTcFZ3SkZmM2d5UEpZUm1xYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0cyI7czo1OiJyb3V0ZSI7czoyMDoiYWRtaW4ucHJvZHVjdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1791187650),('3q6j2ih8yu1cIvfecTkcT3FtRovOIcbnLfbfwj70',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRXlZR1B2d3JuZENjVnNvdE9Rbm45Mk1jSld4RGhwT0pzZVpEUnlNWiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo1NjoiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0L2FkbWluL3NldHRpbmdzL2FwcGVhcmFuY2UiO3M6NToicm91dGUiO3M6MTk6ImFkbWluLnNldHRpbmdzLmVkaXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1791194494),('BVpAZ11XotvsoDFKHAPIY63MVh8rgRFVKV8h0D1A',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoibnBuakpIU1I3MWxRVUd6Ykw3bFhQOXB6V0JQNkVCZ3BobW5SSUF5ZSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0LWF0dHJpYnV0ZXMiO3M6NToicm91dGUiO3M6MzA6ImFkbWluLnByb2R1Y3QtYXR0cmlidXRlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1791192496),('deWZChhCPEixcP0Y8iHE752bPrD9EBkVI9E2VYcb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia0lIZEcxRUt3UVVPR0FHbU1kUk95c2JNcUtnNWtMT3lxT0ZIbmZlTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1791187093),('gxgK4QeukdcrvzenQhzv7xXtIhuz0zi5FRpZ4vyf',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWE95U2IzbzlLRnFDV1NaNXFSdXVKVTRNQUpkWDNYYml2WHZwczNyWCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoxNDE6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0LWF0dHJpYnV0ZXMvOGMxMzU3OWQtZGEwMy00NzliLWFjMGUtMTVmODRhNDJjYzdlL3ZhbHVlcy8wNzY3NTc2Mi0zNGUyLTQwN2UtOGM2Ni04NWVlZDc0MmE4NWEvZWRpdCI7czo1OiJyb3V0ZSI7czozNjoiYWRtaW4ucHJvZHVjdC1hdHRyaWJ1dGVzLnZhbHVlcy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1791167334),('HfTyl9E9pFc2uLjoJSCRjFS5Xlj2Hlq94mFa7ezD',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMHpNZE0zNE1Jb0llYVl1N3gyeTVBUWJ0d2RhSjZxSmFWeFRHWHQ2aCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YToxOntzOjg6ImludGVuZGVkIjtzOjU1OiJodHRwOi8vbGljdXRpLWNtcy1sYXJhdmVsLnRlc3QvYWRtaW4vcHJvZHVjdC1hdHRyaWJ1dGVzIjt9fQ==',1791187267),('JASJXHCmLLBzmA8xBl55CZowTwWbFaM9YS7thft6',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOGpKTDF4YWVrN1B0VWM3ejNTYWZVRzRuNHdYcDFEMW1GcHo0UU1ITyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0LWF0dHJpYnV0ZXMiO3M6NToicm91dGUiO3M6MzA6ImFkbWluLnByb2R1Y3QtYXR0cmlidXRlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1791193900),('jslAnndacsJeGuIMnuzrZ5jjGPk3CR5RLUmew0wq',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWXBNSGFPYjlNWDRzTXRVOXNURUJZSFFxdTVERlpydXJMQ205YWtPNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wb3N0cyI7czo1OiJyb3V0ZSI7czoxNzoiYWRtaW4ucG9zdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1791187427),('kqd9SOEqFhbIfRKktN0xNlUoGCk0AsVbqtcnDGFc',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRlRaRlZLMFRCUG9UcjR0ZGdMVnpHNXQ1N1hhMDV4MDRYdTVmdlNpWSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo4NzoiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0L2FkbWluL3Byb2R1Y3RzL2RmMDVlNjllLTYyNjMtNDYzZS04Y2RkLWMyY2I4M2VkOTkyNS9lZGl0IjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5wcm9kdWN0cy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1791005459),('N1fxjV3T2r5De1ehYa5CuBxmyjHlbDCHWAdzaEec',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiN2pvNmUwR1ZGVDR3RTYxNTBvWHpnZHZHUGxESEZ4dFZxdk82Y2dKRCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1NToiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0L2FkbWluL3Byb2R1Y3QtYXR0cmlidXRlcyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjU1OiJodHRwOi8vbGljdXRpLWNtcy1sYXJhdmVsLnRlc3QvYWRtaW4vcHJvZHVjdC1hdHRyaWJ1dGVzIjtzOjU6InJvdXRlIjtzOjMwOiJhZG1pbi5wcm9kdWN0LWF0dHJpYnV0ZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1791187092),('N5sEnZWbbTUiPM60H2iF5jNXUNydhj9n0nLNK66W',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV3B6djdONXNTUXRmSDJNWEx6OWdmVXFaSllqUElTYXBzcGJ5V01yYiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo1MzoiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0L2FkbWluL3NldHRpbmdzL2dlbmVyYWwiO3M6NToicm91dGUiO3M6MTk6ImFkbWluLnNldHRpbmdzLmVkaXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1791252566),('NNdGMhVIqn4ligmLHPfuDbEE50VPEOQY8hdnkAch',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoibGV4b2pKY3E2MGg0VXRCSFZ4REdJWHZjdjFGQlh0OEdEUldtV2hCWCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo5OToiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0L2FkbWluL3Byb2R1Y3QtYXR0cmlidXRlcy84YzEzNTc5ZC1kYTAzLTQ3OWItYWMwZS0xNWY4NGE0MmNjN2UvdmFsdWVzIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTk6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0LWF0dHJpYnV0ZXMvOGMxMzU3OWQtZGEwMy00NzliLWFjMGUtMTVmODRhNDJjYzdlL3ZhbHVlcyI7czo1OiJyb3V0ZSI7czozNzoiYWRtaW4ucHJvZHVjdC1hdHRyaWJ1dGVzLnZhbHVlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1791187095),('OUNvotibbFSs8UsYjHWM83ZlTcH3SXPdnf9kwmZy',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicmFrMEFHRHRpQmVJcDZPdkpPcDhLc1ptb25IV0cyODZiM0xXZlVnUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1791187097),('PsBB0hKooUmwP3c7p3Ig06KhgZWbd1BeoFkFigPQ',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicDJlOUlGR2ZGVHVuZzJzZzhGZ3hTRXc1bmpPd1V1ME1kaXJTaTVWTCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo4NzoiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0L2FkbWluL3Byb2R1Y3RzL2RmMDVlNjllLTYyNjMtNDYzZS04Y2RkLWMyY2I4M2VkOTkyNS9lZGl0IjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5wcm9kdWN0cy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790997458),('u2Tzhal4VEpAGSamwb5XTA7A7Do9GeDDtkYoJoeP',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUEpORkF1elRyRjdsSXQ1eUdJWUxpVUwwWmlxYUJNamVESEZ3SVZpTiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MjoiaHR0cDovL2xpY3V0aS1jbXMtbGFyYXZlbC50ZXN0L2FkbWluL3Bvc3RzIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wb3N0cyI7czo1OiJyb3V0ZSI7czoxNzoiYWRtaW4ucG9zdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1791187097),('UrbqhcgVWCfU7L1kc5FUgJrwleRj61V8SSul0o86',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRFNJWHdGOTJIeHBXYUpueEF0TzY4U3BKeEFGVnVFTkNEc3VXMXNFVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0LWF0dHJpYnV0ZXMiO3M6NToicm91dGUiO3M6MzA6ImFkbWluLnByb2R1Y3QtYXR0cmlidXRlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1791191728),('YzYwCc2MxCaY83voOTPhUwsMn5R1m1QGdnTxG4O8',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoieVNMeDk0UWZFNzlaUENKY0lVODdROE9FQVpscTkzdm5TQ2FvajJvOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTk6Imh0dHA6Ly9saWN1dGktY21zLWxhcmF2ZWwudGVzdC9hZG1pbi9wcm9kdWN0LWF0dHJpYnV0ZXMvOGMxMzU3OWQtZGEwMy00NzliLWFjMGUtMTVmODRhNDJjYzdlL3ZhbHVlcyI7czo1OiJyb3V0ZSI7czozNzoiYWRtaW4ucHJvZHVjdC1hdHRyaWJ1dGVzLnZhbHVlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1791188680);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
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
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',
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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_media_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `status` enum('active','inactive','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-09 17:06:02
