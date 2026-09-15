
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
DROP TABLE IF EXISTS `banner_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banner_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `banner_translations` WRITE;
/*!40000 ALTER TABLE `banner_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `banner_translations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `brand_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brand_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `brand_translations` WRITE;
/*!40000 ALTER TABLE `brand_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `brand_translations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-app_settings','a:14:{s:9:\"site_name\";a:2:{s:2:\"vi\";s:10:\"Licuti CMS\";s:2:\"en\";s:10:\"Licuti CMS\";}s:6:\"slogan\";a:2:{s:2:\"vi\";s:40:\"Nền tảng thương mại điện tử\";s:2:\"en\";s:19:\"E-commerce platform\";}s:8:\"logo_url\";s:0:\"\";s:11:\"favicon_url\";s:0:\"\";s:13:\"contact_email\";s:18:\"contact@licuti.com\";s:13:\"contact_phone\";s:10:\"0123456789\";s:15:\"contact_address\";a:2:{s:2:\"vi\";s:21:\"Hà Nội, Việt Nam\";s:2:\"en\";s:14:\"Hanoi, Vietnam\";}s:14:\"seo_meta_title\";a:2:{s:2:\"vi\";s:24:\"Licuti CMS - Trang chủ\";s:2:\"en\";s:17:\"Licuti CMS - Home\";}s:20:\"seo_meta_description\";a:2:{s:2:\"vi\";s:43:\"Hệ thống quản trị nội dung Licuti\";s:2:\"en\";s:32:\"Licuti content management system\";}s:19:\"google_analytics_id\";s:0:\"\";s:15:\"social_facebook\";s:21:\"https://facebook.com/\";s:14:\"social_youtube\";s:20:\"https://youtube.com/\";s:13:\"primary_color\";s:7:\"#3b82f6\";s:16:\"footer_copyright\";a:2:{s:2:\"vi\";s:40:\"© 2026 Licuti CMS. All rights reserved.\";s:2:\"en\";s:40:\"© 2026 Licuti CMS. All rights reserved.\";}}',2104823294),('laravel-cache-languages_active','O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:4:\"name\";s:10:\"Vietnamese\";s:11:\"native_name\";s:21:\"Tiáº¿ng Viá»‡t\";s:4:\"flag\";N;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:4:\"name\";s:10:\"Vietnamese\";s:11:\"native_name\";s:21:\"Tiáº¿ng Viá»‡t\";s:4:\"flag\";N;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:13:\"display_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:11:\"native_name\";i:3;s:4:\"flag\";i:4;s:10:\"is_default\";i:5;s:9:\"is_active\";i:6;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:11:\"native_name\";s:7:\"English\";s:4:\"flag\";N;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:11:\"native_name\";s:7:\"English\";s:4:\"flag\";N;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:13:\"display_order\";i:2;s:10:\"created_at\";s:19:\"2026-08-12 13:40:01\";s:10:\"updated_at\";s:19:\"2026-08-12 13:40:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";s:13:\"display_order\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:11:\"native_name\";i:3;s:4:\"flag\";i:4;s:10:\"is_default\";i:5;s:9:\"is_active\";i:6;s:13:\"display_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}',2104823294);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `category_translations` WRITE;
/*!40000 ALTER TABLE `category_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_translations` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `flash_sales` WRITE;
/*!40000 ALTER TABLE `flash_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `flash_sales` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'vi','Vietnamese','Tiáº¿ng Viá»‡t',NULL,1,1,1,'2026-08-12 06:40:01','2026-08-12 06:40:01'),(2,'en','English','English',NULL,0,1,2,'2026-08-12 06:40:01','2026-08-12 06:40:01');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (14,'639c9f41-835b-4d59-9e45-c7ddbcde6f71',NULL,1,'public','media/original/20260804_162248_6a7211d8adc99.jpg','media/thumb/20260804_162248_6a7211d8adc99.webp','20260804_162248_6a7211d8adc99.jpg','gallery-3.jpg','image/jpeg',50079,NULL,'2026-08-04 09:22:49','2026-08-04 09:22:49'),(15,'6ce75093-2364-4e73-ac95-d58248b16ea9',NULL,1,'public','media/original/20260804_162250_6a7211da10a58.jpg','media/thumb/20260804_162250_6a7211da10a58.webp','20260804_162250_6a7211da10a58.jpg','gallery-2.jpg','image/jpeg',121004,NULL,'2026-08-04 09:22:50','2026-08-04 09:22:50'),(16,'69949088-439e-45a8-86e6-9920cf338175',NULL,1,'public','media/original/20260804_162250_6a7211da5f898.jpg','media/thumb/20260804_162250_6a7211da5f898.webp','20260804_162250_6a7211da5f898.jpg','gallery-1.jpg','image/jpeg',212323,NULL,'2026-08-04 09:22:50','2026-08-04 09:22:50'),(17,'28c3ba24-c212-4128-a908-0489a3cec1f6',NULL,1,'public','media/original/20260804_162250_6a7211da94975.jpg','media/thumb/20260804_162250_6a7211da94975.webp','20260804_162250_6a7211da94975.jpg','gallery-8.jpg','image/jpeg',42645,NULL,'2026-08-04 09:22:50','2026-08-04 09:22:50'),(18,'d83a9997-204b-4b9a-90c2-ad3aff0df756',NULL,1,'public','media/original/20260804_162250_6a7211daca0ae.png','media/thumb/20260804_162250_6a7211daca0ae.webp','20260804_162250_6a7211daca0ae.png','gallery-7.png','image/png',1369637,NULL,'2026-08-04 09:22:50','2026-08-04 09:22:50'),(19,'44a55379-a000-4116-89a1-df68b95c9281',NULL,1,'public','media/original/20260804_162251_6a7211db0c164.jpg','media/thumb/20260804_162251_6a7211db0c164.webp','20260804_162251_6a7211db0c164.jpg','gallery-6.jpg','image/jpeg',40997,NULL,'2026-08-04 09:22:51','2026-08-04 09:22:51'),(20,'71e13b6b-eb57-44af-98a4-aa213ce075a0',NULL,1,'public','media/original/20260804_162251_6a7211db4d96c.jpg','media/thumb/20260804_162251_6a7211db4d96c.webp','20260804_162251_6a7211db4d96c.jpg','gallery-5.jpg','image/jpeg',63699,NULL,'2026-08-04 09:22:51','2026-08-04 09:22:51'),(21,'ee3ec89e-500c-42a5-9449-ab3f601f608c',NULL,1,'public','media/original/20260804_162251_6a7211db86473.jpg','media/thumb/20260804_162251_6a7211db86473.webp','20260804_162251_6a7211db86473.jpg','gallery-4.jpg','image/jpeg',99096,NULL,'2026-08-04 09:22:51','2026-08-04 09:22:51'),(22,'3a009c72-e7c0-4b8b-81f2-62fd37b4ddf5',NULL,1,'public','media/original/20260804_162302_6a7211e681dae.jpg','media/thumb/20260804_162302_6a7211e681dae.webp','20260804_162302_6a7211e681dae.jpg','chup-anh-cuoi-vintage-1.jpg','image/jpeg',74640,NULL,'2026-08-04 09:23:02','2026-08-04 09:23:02'),(23,'307caac4-0922-49c7-b599-998050e26af8',NULL,1,'public','media/original/20260804_162302_6a7211e6c8d92.jpg','media/thumb/20260804_162302_6a7211e6c8d92.webp','20260804_162302_6a7211e6c8d92.jpg','464275181_122168201492254080_5598237610130465921_n.jpg','image/jpeg',397987,NULL,'2026-08-04 09:23:02','2026-08-04 09:23:02'),(24,'eb96639d-5071-4dbe-b780-edb7be7c6637',NULL,1,'public','media/original/20260804_162303_6a7211e72bce0.jpg','media/thumb/20260804_162303_6a7211e72bce0.webp','20260804_162303_6a7211e72bce0.jpg','560045750_1189266393019293_8342282536697983955_n-2-1024x683.jpg','image/jpeg',155454,NULL,'2026-08-04 09:23:03','2026-08-04 09:23:03'),(25,'c2bcb11e-d04c-46b2-98e0-74e60e663074',NULL,1,'public','media/original/20260804_162303_6a7211e763abf.jpg','media/thumb/20260804_162303_6a7211e763abf.webp','20260804_162303_6a7211e763abf.jpg','343342550_5955920044504242_5222768225392896037_n.jpg','image/jpeg',98364,NULL,'2026-08-04 09:23:03','2026-08-04 09:23:03'),(26,'943b6347-1020-453a-be3e-db0824ba9f36',1,1,'public','media/original/20260804_182856_6a722f689a8cd.webp','media/thumb/20260804_182856_6a722f689a8cd.webp','20260804_182856_6a722f689a8cd.webp','Anh-em-be-gai-de-thuong.webp','image/webp',130380,NULL,'2026-08-04 11:28:58','2026-08-04 11:28:58'),(27,'9c0bf8b9-1206-4e32-b475-30058be8b368',NULL,1,'public','media/original/20260809_134613_6a7884a5a9968.jpg','media/thumb/20260809_134613_6a7884a5a9968.webp','20260809_134613_6a7884a5a9968.jpg','vn-11134201-23030-173hgesmahovc8.jpg','image/jpeg',80555,NULL,'2026-08-09 06:46:15','2026-08-09 06:46:15');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `media_folders` WRITE;
/*!40000 ALTER TABLE `media_folders` DISABLE KEYS */;
INSERT INTO `media_folders` VALUES (1,'6de77de2-9e71-4b25-8dfb-a9da415ffd28','san-pham','san-pham',NULL,1,'2026-08-04 11:13:10','2026-08-04 11:13:10');
/*!40000 ALTER TABLE `media_folders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (8,'0001_01_01_000000_create_users_table',1),(9,'0001_01_01_000001_create_cache_table',1),(10,'0001_01_01_000002_create_jobs_table',1),(11,'2026_05_05_171801_create_permission_tables',1),(12,'2026_05_05_172458_create_personal_access_tokens_table',1),(13,'2026_08_04_143144_create_media_folders_table',1),(14,'2026_08_04_143145_create_media_table',1),(15,'2026_08_10_161705_create_settings_table',2),(16,'2026_08_12_133142_create_languages_table',3),(19,'2026_08_15_125510_create_tags_table',4),(20,'2026_08_15_125519_create_posts_table',4),(21,'2026_08_15_125527_create_post_translations_table',4),(22,'2026_08_15_125536_create_post_tag_table',4),(23,'2026_08_15_125544_create_pages_table',4),(24,'2026_08_15_125553_create_page_translations_table',4),(25,'2026_08_15_125601_create_banners_table',4),(26,'2026_08_15_125610_create_banner_translations_table',4),(27,'2026_08_15_125618_create_menus_table',4),(28,'2026_08_15_125626_create_menu_items_table',4),(33,'2026_08_15_130950_create_brands_table',5),(34,'2026_08_15_130951_create_brand_translations_table',5),(35,'2026_08_15_130952_create_products_table',5),(36,'2026_08_15_130953_create_product_translations_table',5),(37,'2026_08_15_130954_create_product_attributes_table',5),(38,'2026_08_15_130955_create_product_attribute_translations_table',5),(39,'2026_08_15_130956_create_product_variants_table',5),(40,'2026_08_15_130957_create_product_reviews_table',5),(41,'2026_08_15_130958_create_carts_table',5),(42,'2026_08_15_130959_create_orders_table',5),(43,'2026_08_15_131000_create_payment_methods_table',5),(44,'2026_08_15_131001_create_payments_table',5),(45,'2026_08_15_131002_create_coupons_table',5),(46,'2026_08_15_131003_create_flash_sales_table',5),(47,'2026_08_15_131004_create_warehouses_table',5),(48,'2026_08_15_131005_create_inventories_table',5),(49,'2026_08_15_130948_create_categories_table',6),(50,'2026_08_15_130949_create_category_translations_table',6),(53,'2026_08_15_113902_create_post_categories_table',7),(54,'2026_08_15_113911_create_post_category_translations_table',7),(56,'2026_08_28_171216_add_columns_to_posts_and_translations_table',8),(57,'2026_08_28_175815_add_missing_columns_to_posts_table',9),(58,'2026_08_30_081215_rename_featured_image_to_image_in_posts_table',10),(59,'2026_09_05_111302_create_seo_metadata_table',10),(60,'2026_09_07_145027_add_schema_type_to_seo_metadata_table',11),(61,'2026_09_09_154700_create_post_category_post_table',12),(62,'2026_09_15_041000_add_status_to_pages_table',13),(63,'2026_09_15_063500_enhance_pages_module_schema',13),(64,'2026_09_15_070200_add_published_at_to_pages_table',13),(65,'2026_09_15_115301_add_soft_deletes_to_pages_table',13);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (2,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `page_translations` WRITE;
/*!40000 ALTER TABLE `page_translations` DISABLE KEYS */;
INSERT INTO `page_translations` VALUES (1,'2026-09-15 02:10:13','2026-09-15 02:10:13',1,'vi','Gioi thieu','gioi-thieu','Trang gioi thieu','<p>Chung toi</p>'),(2,'2026-09-15 02:10:13','2026-09-15 02:47:05',2,'vi','LIÊN HỆ - Đã đổi title','lien-he-moi','Mô tả mới','<p>Updated</p>'),(3,'2026-09-15 02:10:13','2026-09-15 02:10:13',3,'vi','Su menh','su-menh','Su menh va tam nhin','<p>Su menh</p>'),(4,'2026-09-15 02:10:13','2026-09-15 02:10:13',4,'vi','Khuyen mai Tet','khuyen-mai-tet','Trang landing','<p>Khuyen mai</p>'),(5,'2026-09-15 02:22:56','2026-09-15 02:23:39',5,'vi','Y updated','y-updated','Mô tả ngắn cho trang','<p>Nội dung test</p>'),(6,'2026-09-15 02:23:39','2026-09-15 02:23:39',6,'vi','Y updated','y-updated-1',NULL,NULL);
/*!40000 ALTER TABLE `page_translations` ENABLE KEYS */;
UNLOCK TABLES;
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
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'285515fb-2e73-4e82-b4d2-e5559e6c7dcd',NULL,'full_width','2026-09-01 03:00:00',NULL,1,'published',NULL,NULL,NULL,'2026-09-15 02:10:13','2026-09-15 02:10:13',NULL),(2,'889d2a37-788a-4d48-bfff-8acfe84b88c3',4,'contact_us','2026-12-24 17:00:00',NULL,99,'draft',NULL,NULL,NULL,'2026-09-15 02:10:13','2026-09-15 02:47:05',NULL),(3,'2eed33ce-b1d3-4336-a056-92d42f79c230',1,'default',NULL,NULL,3,'draft',NULL,NULL,NULL,'2026-09-15 02:10:13','2026-09-15 02:10:13',NULL),(4,'c331d8cf-2eef-4339-9467-69fc428212d3',NULL,'landing','2026-09-03 03:00:00',NULL,10,'published',NULL,NULL,NULL,'2026-09-15 02:10:13','2026-09-15 02:10:13',NULL),(5,'2a24c7b5-0ddb-421b-b5f3-a147a7a67557',1,'default',NULL,NULL,0,'published',NULL,NULL,NULL,'2026-09-15 02:22:56','2026-09-15 02:23:39',NULL),(6,'6c16b552-cac4-4cb0-8b04-ad8b4b29b37a',NULL,'default',NULL,NULL,0,'draft',NULL,NULL,NULL,'2026-09-15 02:23:39','2026-09-15 02:23:39',NULL);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'users.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(2,'users.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(3,'users.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(4,'users.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(5,'users.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(6,'users.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(7,'users.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(8,'users.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(9,'users.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(10,'users.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(11,'users.restore','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(12,'users.restore','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(13,'users.force-delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(14,'users.force-delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(15,'users.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(16,'users.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(17,'users.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(18,'users.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(19,'roles.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(20,'roles.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(21,'roles.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(22,'roles.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(23,'roles.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(24,'roles.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(25,'roles.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(26,'roles.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(27,'permissions.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(28,'permissions.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(29,'permissions.assign','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(30,'permissions.assign','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(31,'permissions.revoke','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(32,'permissions.revoke','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(33,'categories.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(34,'categories.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(35,'categories.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(36,'categories.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(37,'categories.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(38,'categories.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(39,'categories.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(40,'categories.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(41,'brands.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(42,'brands.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(43,'brands.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(44,'brands.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(45,'brands.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(46,'brands.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(47,'brands.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(48,'brands.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(49,'products.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(50,'products.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(51,'products.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(52,'products.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(53,'products.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(54,'products.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(55,'products.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(56,'products.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(57,'products.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(58,'products.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(59,'products.publish','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(60,'products.publish','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(61,'products.approve','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(62,'products.approve','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(63,'products.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(64,'products.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(65,'products.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(66,'products.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(67,'orders.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(68,'orders.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(69,'orders.view-all','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(70,'orders.view-all','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(71,'orders.view-detail','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(72,'orders.view-detail','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(73,'orders.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(74,'orders.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(75,'orders.cancel','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(76,'orders.cancel','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(77,'orders.refund','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(78,'orders.refund','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(79,'orders.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(80,'orders.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(81,'inventory.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(82,'inventory.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(83,'inventory.import','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(84,'inventory.import','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(85,'inventory.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(86,'inventory.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(87,'inventory.adjust','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(88,'inventory.adjust','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(89,'coupons.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(90,'coupons.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(91,'coupons.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(92,'coupons.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(93,'coupons.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(94,'coupons.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(95,'coupons.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(96,'coupons.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(97,'posts.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(98,'posts.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(99,'posts.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(100,'posts.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(101,'posts.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(102,'posts.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(103,'posts.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(104,'posts.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(105,'posts.publish','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(106,'posts.publish','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(107,'pages.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(108,'pages.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(109,'pages.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(110,'pages.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(111,'pages.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(112,'pages.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(113,'pages.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(114,'pages.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(115,'banners.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(116,'banners.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(117,'banners.create','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(118,'banners.create','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(119,'banners.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(120,'banners.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(121,'banners.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(122,'banners.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(123,'media.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(124,'media.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(125,'media.upload','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(126,'media.upload','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(127,'media.delete','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(128,'media.delete','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(129,'reports.sales','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(130,'reports.sales','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(131,'reports.inventory','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(132,'reports.inventory','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(133,'reports.customers','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(134,'reports.customers','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(135,'reports.export','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(136,'reports.export','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(137,'settings.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(138,'settings.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(139,'settings.update','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(140,'settings.update','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(141,'settings.general','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(142,'settings.general','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(143,'settings.payment','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(144,'settings.payment','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(145,'settings.email','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(146,'settings.email','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(147,'settings.sms','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(148,'settings.sms','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(149,'logs.view','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(150,'logs.view','api','2026-08-04 07:34:42','2026-08-04 07:34:42'),(151,'logs.clear','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(152,'logs.clear','api','2026-08-04 07:34:42','2026-08-04 07:34:42');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `post_categories` WRITE;
/*!40000 ALTER TABLE `post_categories` DISABLE KEYS */;
INSERT INTO `post_categories` VALUES (2,'5b9a770a-795b-4c2d-a5df-9569294af72a',NULL,NULL,NULL,1,0,'2026-08-16 10:24:00','2026-08-20 08:44:12'),(3,'30f3b772-f038-425d-8f86-04c800b37771',2,NULL,NULL,1,0,'2026-08-19 08:34:45','2026-08-20 08:20:48'),(4,'457a0c74-6fc3-4a23-a362-318a54908c0b',3,NULL,NULL,1,0,'2026-08-20 10:52:22','2026-08-22 04:27:40');
/*!40000 ALTER TABLE `post_categories` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `post_category_post` WRITE;
/*!40000 ALTER TABLE `post_category_post` DISABLE KEYS */;
INSERT INTO `post_category_post` VALUES (2,2),(1,3),(2,3);
/*!40000 ALTER TABLE `post_category_post` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `post_category_translations` WRITE;
/*!40000 ALTER TABLE `post_category_translations` DISABLE KEYS */;
INSERT INTO `post_category_translations` VALUES (1,2,'vi','Danh má»¥c 111','danh-muc-1',NULL,NULL,NULL,NULL,'2026-08-16 10:24:00','2026-08-19 08:55:39'),(2,2,'en','Category 1','category-1',NULL,NULL,NULL,NULL,'2026-08-16 10:24:00','2026-08-19 08:14:15'),(3,3,'vi','Danh má»¥c 2','danh-muc-2',NULL,NULL,NULL,NULL,'2026-08-19 08:34:45','2026-08-19 08:34:45'),(4,3,'en','Category 2','category-2',NULL,NULL,NULL,NULL,'2026-08-19 08:34:45','2026-08-19 08:34:45'),(5,4,'vi','Danh má»¥c 3','danh-muc-3',NULL,'Danh má»¥c 1',NULL,NULL,'2026-08-20 10:52:22','2026-08-20 10:52:22');
/*!40000 ALTER TABLE `post_category_translations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `post_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `post_tag` WRITE;
/*!40000 ALTER TABLE `post_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_tag` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `post_translations` WRITE;
/*!40000 ALTER TABLE `post_translations` DISABLE KEYS */;
INSERT INTO `post_translations` VALUES (1,'2026-09-08 08:44:01','2026-09-14 08:54:32',1,'vi','BÃ i viáº¿t test tiáº¿ng viá»‡t','BÃ i viáº¿t test tiáº¿ng viá»‡t',NULL,NULL,NULL,NULL,NULL),(2,'2026-09-08 08:57:01','2026-09-08 08:57:01',2,'vi','BÃ i viáº¿t kiá»ƒm tra lÆ°u áº£nh Ä‘áº¡i diá»‡n vÃ  auto slug','laravel-11-chuyen-sau','KhÃ³a há»c Laravel 11 thá»±c chiáº¿n toÃ n diá»‡n.',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `post_translations` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'ed35fec5-ff47-4fd0-8999-21b8e47bdf4d','draft',0,0,'2026-09-08 08:44:01','2026-09-14 08:54:32',NULL,'2026-09-14 08:54:00',NULL,1),(2,'846bc4f8-f343-48df-95fe-35aa8bf04a67','published',0,0,'2026-09-08 08:57:01','2026-09-11 10:31:23','9c0bf8b9-1206-4e32-b475-30058be8b368','2026-09-09 09:18:00',NULL,1);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_attribute_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_attribute_translations` WRITE;
/*!40000 ALTER TABLE `product_attribute_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_attribute_translations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_attributes` WRITE;
/*!40000 ALTER TABLE `product_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_attributes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_translations` WRITE;
/*!40000 ALTER TABLE `product_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_translations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(3,1),(5,1),(7,1),(9,1),(11,1),(13,1),(15,1),(17,1),(19,1),(21,1),(23,1),(25,1),(27,1),(29,1),(31,1),(33,1),(35,1),(37,1),(39,1),(41,1),(43,1),(45,1),(47,1),(49,1),(51,1),(53,1),(55,1),(57,1),(59,1),(61,1),(63,1),(65,1),(67,1),(69,1),(71,1),(73,1),(75,1),(77,1),(79,1),(81,1),(83,1),(85,1),(87,1),(89,1),(91,1),(93,1),(95,1),(97,1),(99,1),(101,1),(103,1),(105,1),(107,1),(109,1),(111,1),(113,1),(115,1),(117,1),(119,1),(121,1),(123,1),(125,1),(127,1),(129,1),(131,1),(133,1),(135,1),(137,1),(139,1),(141,1),(143,1),(145,1),(147,1),(149,1),(151,1),(1,2),(3,2),(5,2),(7,2),(9,2),(11,2),(13,2),(15,2),(17,2),(19,2),(21,2),(23,2),(25,2),(27,2),(29,2),(31,2),(33,2),(35,2),(37,2),(39,2),(41,2),(43,2),(45,2),(47,2),(49,2),(51,2),(53,2),(55,2),(57,2),(59,2),(61,2),(63,2),(65,2),(67,2),(69,2),(71,2),(73,2),(75,2),(77,2),(79,2),(81,2),(83,2),(85,2),(87,2),(89,2),(91,2),(93,2),(95,2),(97,2),(99,2),(101,2),(103,2),(105,2),(107,2),(109,2),(111,2),(113,2),(115,2),(117,2),(119,2),(121,2),(123,2),(125,2),(127,2),(129,2),(131,2),(133,2),(135,2),(137,2),(139,2),(141,2),(143,2),(145,2),(147,2),(149,2),(151,2),(33,3),(35,3),(37,3),(41,3),(49,3),(51,3),(53,3),(55,3),(97,3),(99,3),(101,3),(105,3),(107,3),(109,3),(111,3),(115,3),(117,3),(119,3),(123,3),(125,3),(117,4),(121,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super-admin','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(2,'admin','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(3,'editor','web','2026-08-04 07:34:42','2026-08-04 07:34:42'),(4,'customer','web','2026-08-04 07:34:42','2026-08-04 07:34:42');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `seo_metadata` WRITE;
/*!40000 ALTER TABLE `seo_metadata` DISABLE KEYS */;
INSERT INTO `seo_metadata` VALUES (1,'App\\Models\\PostCategory',4,'vi','Danh má»¥c 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-08-20 10:52:22','2026-08-20 10:52:22'),(2,'App\\Models\\Post',1,'vi','BÃ i viáº¿t test tiáº¿ng viá»‡t',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-08 08:44:01','2026-09-14 08:54:46'),(3,'App\\Models\\Post',2,'vi','BÃ i viáº¿t kiá»ƒm tra lÆ°u áº£nh Ä‘áº¡i diá»‡n vÃ  auto slug','KhÃ³a há»c Laravel 11 thá»±c chiáº¿n toÃ n diá»‡n.',NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-09-08 08:57:01','2026-09-08 08:57:01');
/*!40000 ALTER TABLE `seo_metadata` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('b8NQWKlINnEUXSuRqXW3LqVyWaRWvKv5FpRtC7Zl',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTjc3enhOSWhNWHBzV3A3UWpZVFR1MEl0WndlMldlcnVxdHZqOU1BVSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjUzOiJodHRwOi8vbGljdXRpLWNtcy1sYXJhdmVsLnRlc3Q6ODEvYWRtaW4vcG9zdHM/dGFiPWFsbCI7czo1OiJyb3V0ZSI7czoxNzoiYWRtaW4ucG9zdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1789309018),('DzS9GJLR97WSVh5YWYg6KpAsAtCDgbnPcL9setjG',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSnhqN3ZBZ2pJYVlpMWpycWh1Z0lwUVNoQ2FFSFh6MWpyRGt0Z3RaTCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjg0OiJodHRwOi8vbGljdXRpLWNtcy1sYXJhdmVsLnRlc3QvYWRtaW4vcGFnZXMvNmMxNmI1NTItY2FjNC00Y2IwLThiMDQtYWQ4YjRiMjliMzdhL2VkaXQiO3M6NToicm91dGUiO3M6MTY6ImFkbWluLnBhZ2VzLmVkaXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1789465935),('HV5H7DtD5V8xLyQE12sLVX8d2mDokVwpirG7b53y',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRnlLWnRNcjVCNXNJQ0VES2JQb0VuZnN4R1hkRGROMU56YXMyenpySSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9wYWdlcy84ODlkMmEzNy03ODhhLTRkNDgtYmZmZi04YWNmZTg0Yjg4YzMvZWRpdCI7czo1OiJyb3V0ZSI7czoxNjoiYWRtaW4ucGFnZXMuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1789463439),('JdSjXDWskUESQV7zwsCLEs3xvJOPS4HrzyHQpUVK',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYWFHUlY2NjZHWHBZdEFubHRxbDZlWW9qdFA1aDA0Q0dRMURCZVpXUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9wYWdlcy84ODlkMmEzNy03ODhhLTRkNDgtYmZmZi04YWNmZTg0Yjg4YzMvZWRpdCI7czo1OiJyb3V0ZSI7czoxNjoiYWRtaW4ucGFnZXMuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1789465592),('kC7q7KxCxhnyJ5FbQeZWU6QWvceRZApPBtKJf3PH',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMXhFN3lEd3hRMGFDbVNZZUJQclJBV0lRNHhEMEdXeER1N0I4WllHTyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9wYWdlcy84ODlkMmEzNy03ODhhLTRkNDgtYmZmZi04YWNmZTg0Yjg4YzMvZWRpdCI7czo1OiJyb3V0ZSI7czoxNjoiYWRtaW4ucGFnZXMuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1789464247),('lfWB257I4bCHYwJgKpstU98XK4TvH00I8dLw1mXA',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaHlCWk9WQklyQW92dG9IZEZvcFJDU1BJYkZaV2pSMXcyMElhc2FURiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9wYWdlcy84ODlkMmEzNy03ODhhLTRkNDgtYmZmZi04YWNmZTg0Yjg4YzMvZWRpdCI7czo1OiJyb3V0ZSI7czoxNjoiYWRtaW4ucGFnZXMuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjE6e2k6MDtzOjc6InN1Y2Nlc3MiO31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo3OiJzdWNjZXNzIjtzOjM4OiJD4bqtcCBuaOG6rXQgdHJhbmcgdMSpbmggdGjDoG5oIGPDtG5nLiI7fQ==',1789465625),('rcTLVLac3l0GniTy3pmq1Lp9eqjqu9ZfqKlo5QsQ',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTE05dEtsenFzR0tFMHpFRWRBVUJlQ0VoMmd3RHNRdHJJeDVlOWpZciI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjUzOiJodHRwOi8vbGljdXRpLWNtcy1sYXJhdmVsLnRlc3Q6ODEvYWRtaW4vcG9zdHM/dGFiPWFsbCI7czo1OiJyb3V0ZSI7czoxNzoiYWRtaW4ucG9zdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1789148778),('WCWv7CAQPEXEzNsZfoE58UeKYZLG6B6f6pgStYV1',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoidUJoS2J6SGROZko3aUhyN3lMdEdnTXpialJqR01hUm1PV2RLWHN0ayI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjg3OiJodHRwOi8vbGljdXRpLWNtcy1sYXJhdmVsLnRlc3Q6ODEvYWRtaW4vcG9zdHMvZWQzNWZlYzUtZmY0Ny00ZmQwLTg5OTktMjFiOGU0N2JkZjRkL2VkaXQiO3M6NToicm91dGUiO3M6MTY6ImFkbWluLnBvc3RzLmVkaXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1789401287);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','{\"vi\":\"Licuti CMS\",\"en\":\"Licuti CMS\"}','general','text','TÃªn website','TÃªn hiá»ƒn thá»‹ trÃªn tiÃªu Ä‘á» trÃ¬nh duyá»‡t vÃ  trang chá»§',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(2,'slogan','{\"vi\":\"N\\u1ec1n t\\u1ea3ng th\\u01b0\\u01a1ng m\\u1ea1i \\u0111i\\u1ec7n t\\u1eed\",\"en\":\"E-commerce platform\"}','general','text','Kháº©u hiá»‡u (Slogan)','Kháº©u hiá»‡u hiá»ƒn thá»‹ dÆ°á»›i logo',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(3,'logo_url','','general','image','Logo website','Logo chÃ­nh cá»§a website',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(4,'favicon_url','','general','image','Favicon','Icon nhá» trÃªn tab trÃ¬nh duyá»‡t',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(5,'contact_email','contact@licuti.com','general','text','Email liÃªn há»‡','Email dÃ¹ng Ä‘á»ƒ khÃ¡ch hÃ ng liÃªn há»‡',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(6,'contact_phone','0123456789','general','text','Hotline','Sá»‘ Ä‘iá»‡n thoáº¡i hotline',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(7,'contact_address','{\"vi\":\"H\\u00e0 N\\u1ed9i, Vi\\u1ec7t Nam\",\"en\":\"Hanoi, Vietnam\"}','general','textarea','Äá»‹a chá»‰','Äá»‹a chá»‰ vÄƒn phÃ²ng / cá»­a hÃ ng chÃ­nh',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(8,'seo_meta_title','{\"vi\":\"Licuti CMS - Trang ch\\u1ee7\",\"en\":\"Licuti CMS - Home\"}','seo','text','Meta Title máº·c Ä‘á»‹nh','Tháº» title hiá»ƒn thá»‹ trÃªn Google náº¿u trang khÃ´ng cÃ³ cáº¥u hÃ¬nh riÃªng',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(9,'seo_meta_description','{\"vi\":\"H\\u1ec7 th\\u1ed1ng qu\\u1ea3n tr\\u1ecb n\\u1ed9i dung Licuti\",\"en\":\"Licuti content management system\"}','seo','textarea','Meta Description máº·c Ä‘á»‹nh','Tháº» mÃ´ táº£ hiá»ƒn thá»‹ trÃªn káº¿t quáº£ tÃ¬m kiáº¿m Google',1,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(10,'google_analytics_id','','seo','text','Google Analytics ID','MÃ£ theo dÃµi GA (VD: G-XXXXXXXXXX)',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(11,'social_facebook','https://facebook.com/','social','text','Facebook URL','Link Fanpage Facebook',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(12,'social_youtube','https://youtube.com/','social','text','YouTube URL','Link kÃªnh YouTube',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(13,'primary_color','#3b82f6','appearance','text','MÃ u chá»§ Ä‘áº¡o (Primary Color)','MÃ u sáº¯c chÃ­nh cho nÃºt báº¥m vÃ  Ä‘iá»ƒm nháº¥n frontend',0,'2026-08-10 09:26:10','2026-08-10 09:26:10'),(14,'footer_copyright','{\"vi\":\"\\u00a9 2026 Licuti CMS. All rights reserved.\",\"en\":\"\\u00a9 2026 Licuti CMS. All rights reserved.\"}','appearance','text','Footer Copyright','DÃ²ng báº£n quyá»n dÆ°á»›i cÃ¹ng trang web',1,'2026-08-10 09:26:10','2026-08-10 09:26:10');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'344ad8af-bff3-4483-8489-79f8bcbc8190','System Administrator','admin@licuti.com',NULL,NULL,NULL,'$2y$12$1ZJGvl0TmfPTp4tP91B8g.VG63pIQvSR0JyJU0yOXxmBvbFnldN2K',NULL,'9c0bf8b9-1206-4e32-b475-30058be8b368',NULL,NULL,'active',1,NULL,NULL,NULL,NULL,'IlRXZ6ZSyX2CDNrWAcre4iSELZRrhuaEaJGqotduI9LLJFx6nRnsslBAo4t5','2026-08-04 07:34:43','2026-08-09 06:46:53',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
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

