-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: walini_pj
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.1

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
-- Table structure for table `attendance_logs`
--

DROP TABLE IF EXISTS `attendance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL COMMENT 'FK to employees.id',
  `device_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Device identifier (e.g., KIOSK-01)',
  `event_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Actual attendance time',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'masuk/pulang/lembur/pulang_lembur',
  `raw_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Original name from device',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_event_time` (`event_time`),
  KEY `idx_status` (`status`),
  KEY `idx_employee_date` (`employee_id`,`event_time`),
  CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_logs`
--

LOCK TABLES `attendance_logs` WRITE;
/*!40000 ALTER TABLE `attendance_logs` DISABLE KEYS */;
INSERT INTO `attendance_logs` VALUES (1,4,'KIOSK-TEST','2025-11-21 06:17:26','masuk','Adeli',NULL,NULL),(2,5,'KIOSK-TEST','2025-11-21 06:42:23','masuk','Rasid',NULL,NULL),(3,6,'KIOSK-TEST','2025-11-21 06:42:40','masuk','Isan',NULL,NULL),(4,7,'KIOSK-TEST','2025-11-21 06:42:46','masuk','Alip',NULL,NULL),(5,4,'KIOSK-TEST','2025-11-21 06:49:23','pulang','Adeli',NULL,NULL),(6,5,'KIOSK-TEST','2025-11-21 06:49:51','pulang','Rasid',NULL,NULL),(7,6,'KIOSK-TEST','2025-11-21 06:49:56','pulang','Isan',NULL,NULL),(8,7,'KIOSK-TEST','2025-11-21 07:00:11','pulang','Alip',NULL,NULL),(9,1,'KIOSK-TEST','2025-11-21 07:00:45','masuk','Test Employee',NULL,NULL),(10,2,'KIOSK-TEST','2025-11-21 07:01:05','masuk','John Doe',NULL,NULL),(11,3,'KIOSK-TEST','2025-11-21 07:01:12','masuk','Jane Smith',NULL,NULL);
/*!40000 ALTER TABLE `attendance_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `before_json` json DEFAULT NULL,
  `after_json` json DEFAULT NULL,
  `ip_addr` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_resource_index` (`resource`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_payments`
--

DROP TABLE IF EXISTS `booking_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','transfer','qris','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `payment_reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime NOT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_payments_paid_at_index` (`paid_at`),
  KEY `booking_payments_cashier_id_index` (`cashier_id`),
  KEY `booking_payments_booking_id_foreign` (`booking_id`),
  CONSTRAINT `booking_payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_payments_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_payments`
--

LOCK TABLES `booking_payments` WRITE;
/*!40000 ALTER TABLE `booking_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_units`
--

DROP TABLE IF EXISTS `booking_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_units_booking_id_foreign` (`booking_id`),
  KEY `booking_units_product_id_foreign` (`product_id`),
  CONSTRAINT `booking_units_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_units_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_units`
--

LOCK TABLES `booking_units` WRITE;
/*!40000 ALTER TABLE `booking_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkin` datetime NOT NULL,
  `checkout` datetime NOT NULL,
  `night_count` tinyint unsigned NOT NULL,
  `room_count` tinyint unsigned NOT NULL DEFAULT '1',
  `status` enum('draft','pending','confirmed','checked_in','checked_out','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `dp_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_booking_code_unique` (`booking_code`),
  KEY `idx_bookings_status` (`status`,`checkin`),
  KEY `idx_bookings_created_by` (`created_by`),
  KEY `bookings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `bookings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_booking_dates` CHECK ((`checkout` > `checkin`))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,'BKG202511200001','John Doe','081234567890','2025-11-22 00:00:00','2025-11-24 00:00:00',2,1,'confirmed',0.00,2500000.00,0.00,NULL,1,NULL,'2025-11-19 23:12:15','2025-11-19 23:12:15'),(3,'BKG-20251120-0002','Alif','083165655638','2025-11-20 00:00:00','2025-11-28 00:00:00',8,1,'pending',0.00,1500000.00,0.00,'123',1,NULL,'2025-11-20 00:38:33','2025-11-20 00:38:33');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `door_events`
--

DROP TABLE IF EXISTS `door_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `door_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `door_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `door_events_door_id_event_time_index` (`door_id`,`event_time`),
  KEY `door_events_user_id_index` (`user_id`),
  CONSTRAINT `door_events_door_id_foreign` FOREIGN KEY (`door_id`) REFERENCES `doors` (`id`),
  CONSTRAINT `door_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `door_events`
--

LOCK TABLES `door_events` WRITE;
/*!40000 ALTER TABLE `door_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `door_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doors`
--

DROP TABLE IF EXISTS `doors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `door_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `door_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `door_type` enum('entrance','exit','both') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'both',
  `status` enum('active','inactive','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doors_door_code_unique` (`door_code`),
  KEY `doors_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doors`
--

LOCK TABLES `doors` WRITE;
/*!40000 ALTER TABLE `doors` DISABLE KEYS */;
/*!40000 ALTER TABLE `doors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Employee unique code',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Employee full name',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Active status',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_employee_code` (`code`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'TEST123','Test Employee',1,'2025-11-21 05:46:37','2025-11-21 05:46:37'),(2,'EMP001','John Doe',1,'2025-11-21 05:46:37','2025-11-21 05:46:37'),(3,'EMP002','Jane Smith',1,'2025-11-21 05:46:37','2025-11-21 05:46:37'),(4,'0319766798','Adeli',1,'2025-11-21 06:17:07','2025-11-21 06:17:07'),(5,'0084807438','Rasid',1,'2025-11-21 06:17:07','2025-11-21 06:17:07'),(6,'0151920398','Isan',1,'2025-11-21 06:17:07','2025-11-21 06:17:07'),(7,'0909479960','Alip',1,'2025-11-21 06:17:07','2025-11-21 06:17:07');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
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
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_11_19_044723_create_roles_table',1),(5,'2025_11_19_044724_create_product_categories_table',1),(6,'2025_11_19_044724_create_products_table',1),(7,'2025_11_19_044724_create_users_table',1),(8,'2025_11_19_044725_create_bookings_table',1),(9,'2025_11_19_044725_create_product_prices_table',1),(10,'2025_11_19_044726_create_booking_payments_table',1),(12,'2025_11_19_044726_create_ticket_sales_table',1),(13,'2025_11_19_044727_create_attendance_logs_table',1),(14,'2025_11_19_044727_create_doors_table',1),(15,'2025_11_19_044727_create_employees_table',1),(24,'2025_11_19_044726_create_booking_units_table',2),(25,'2025_11_19_044727_create_ticket_sale_items_table',3),(26,'2025_11_19_044728_create_door_events_table',4),(27,'2025_11_19_044728_create_rfid_logs_table',4),(28,'2025_11_19_044729_create_vw_ticket_sales_daily_view',4),(29,'2025_11_20_033050_create_role_permissions_table',4),(30,'2025_11_20_045811_create_audit_logs_table',4),(31,'2025_11_20_051852_create_personal_access_tokens_table',4),(32,'2025_11_30_000001_add_ticket_sales_fields',5),(33,'2025_11_30_000002_add_ticket_sale_items_fields',6),(34,'2025_11_30_000003_add_remember_token_to_users',6),(35,'2025_11_30_000003_add_visits_fields',7);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
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
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_code_unique` (`code`),
  UNIQUE KEY `product_categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'VILLA','Villa & Penginapan'),(2,'COTTAGE','Cottage'),(3,'TICKET','Tickets'),(7,'PERMAINAN','Wahana Permainan'),(8,'KOLAM','Kolam & Pemandian'),(9,'PARKIR','Parkir Kendaraan'),(10,'TIKET','Tiket Masuk');
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_prices`
--

DROP TABLE IF EXISTS `product_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_prices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `label` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `day_type` enum('weekday','weekend','all') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_price` (`product_id`,`label`),
  KEY `idx_product_prices_period` (`start_date`,`end_date`),
  CONSTRAINT `product_prices_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_prices`
--

LOCK TABLES `product_prices` WRITE;
/*!40000 ALTER TABLE `product_prices` DISABLE KEYS */;
INSERT INTO `product_prices` VALUES (1,24,'weekday',700000.00,NULL,NULL,'weekday','2025-11-21 03:53:46'),(2,24,'weekend',400000.00,NULL,NULL,'weekend','2025-11-21 03:53:46'),(3,25,'weekday',400000.00,NULL,NULL,'weekday','2025-11-21 03:53:46'),(4,25,'weekend',400000.00,NULL,NULL,'weekend','2025-11-21 03:53:46'),(5,26,'weekday',700000.00,NULL,NULL,'weekday','2025-11-21 03:53:46'),(6,26,'weekend',400000.00,NULL,NULL,'weekend','2025-11-21 03:53:46'),(7,27,'weekday',700000.00,NULL,NULL,'weekday','2025-11-21 03:53:46'),(8,27,'weekend',400000.00,NULL,NULL,'weekend','2025-11-21 03:53:46');
/*!40000 ALTER TABLE `product_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category_id` smallint unsigned NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'VILLA-A','Villa Premium A',1500000.00,1,'2025-11-19 23:12:15','2025-11-19 23:12:15'),(2,1,'VILLA-B','Villa Standard B',1000000.00,1,'2025-11-19 23:12:15','2025-11-19 23:12:15'),(3,2,'COT-DLX','Cottage Deluxe',750000.00,1,'2025-11-19 23:12:15','2025-11-19 23:12:15'),(4,3,'TIX-ADT','Adult Ticket',50000.00,1,'2025-11-19 23:12:15','2025-11-19 23:12:15'),(5,3,'TIX-CHD','Child Ticket',25000.00,1,'2025-11-19 23:12:15','2025-11-19 23:12:15'),(6,7,'GOKAR-50','GOKAR 50 CC',25000.00,1,NULL,NULL),(7,7,'ATV-90','ATV 90 CC',50000.00,1,NULL,NULL),(8,7,'ATV-TOUR','ATV TEA TOURS',100000.00,1,NULL,NULL),(9,7,'FLY-MINI','FLYING FOX MINI',15000.00,1,NULL,NULL),(10,7,'FLY-EXT','FLYING FOX EXTREME 300M',50000.00,1,NULL,NULL),(11,7,'BAJAY','BAJAY TOUR',50000.00,1,NULL,NULL),(12,7,'SEPEDA','SEPEDA TOUR',25000.00,1,NULL,NULL),(13,7,'BOOGIE','BOOGIE',100000.00,1,NULL,NULL),(14,7,'TIKET-MAINAN','TIKET MAINAN',3000.00,1,NULL,NULL),(15,7,'KERETA-MINI','KERETA API MINI',15000.00,1,NULL,NULL),(16,8,'KOLAM-REG','Kolam Renang',40000.00,1,NULL,NULL),(17,8,'KOLAM-KEL','Kolam Renang Keluarga',60000.00,1,NULL,NULL),(18,8,'RENDAM','Kamar Rendam',50000.00,1,NULL,NULL),(19,8,'IKAN','Terapi Ikan',30000.00,1,NULL,NULL),(20,10,'TIKET-WALINI','Tiket Walini',40000.00,1,NULL,NULL),(21,9,'PK-R2','Parkir Roda 2',2000.00,1,NULL,NULL),(22,9,'PK-R4','Parkir Roda 4',5000.00,1,NULL,NULL),(23,9,'PK-R6','Parkir Roda 6',5000.00,1,NULL,NULL),(24,1,'V-BUNGALOW','Villa Bungalow',0.00,1,NULL,NULL),(25,1,'V-KERUCUT','Villa Kerucut',0.00,1,NULL,NULL),(26,1,'V-LUMBUNG','Villa Lumbung',0.00,1,NULL,NULL),(27,1,'V-PANGGUNG','Villa Panggung',0.00,1,NULL,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rfid_logs`
--

DROP TABLE IF EXISTS `rfid_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rfid_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rfid_logs`
--

LOCK TABLES `rfid_logs` WRITE;
/*!40000 ALTER TABLE `rfid_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `rfid_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` smallint unsigned NOT NULL,
  `permission` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission`),
  KEY `role_permissions_permission_index` (`permission`),
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'superadmin','Super Administrator',1,NULL,NULL),(2,'admin','Administrator',1,NULL,NULL),(3,'cashier','Kasir',1,NULL,NULL),(4,'frontdesk','Front Desk',1,NULL,NULL),(5,'auditor','Auditor',1,NULL,NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `sessions` VALUES ('38EYHZz2ZW5yesMAz7AZEhZrtC4q2GQjrZkKycP7',NULL,'184.105.247.252','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaWRiVzlzNGZpUlc1bEl2eDlMWFBTbWhMNmtmaVQ0NkFhRkxEMmlFbiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764471343),('4cjI00YWmN9GIQDS1lsy91JNKAQy0C3GB4GFx0sr',NULL,'51.254.49.110','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidlByeHRXNk9wQW15S3JkSjI4UEhhVGh2OG4xUllSbng5Tk9jRTFVQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764477792),('8teuOdNEFCB6Rl2fWATwZlRqaQzi8m9prvgkyo8h',NULL,'44.212.38.157','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic0RVMEJQeVhyeENEbzFHMmlhRGF2MGVIcUh1V2duN1dJUmJzbHdSbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8yMDMuODMuMzguMzQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764472092),('9tF5RuX6u7z6ajEHTcuF3TpSHiTlOe8XllKKe3Zp',NULL,'58.97.230.43','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSlIzZVMydnNFenBuQU1vU2tBZm1iWlZ1a1BXWkU1NFFDRGowR2pNViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8yMDMuODMuMzguMzQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764472697),('a6EKfgx7hj8QKsQzyGiYECibe3vsh9i2IjhnV3Yu',NULL,'34.227.193.105','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQXNaamV0bFRZNGR5Y2czMlRCS0pZeUxyalhKeEFNNkdlTnFtaDdXRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764472196),('BH4W5IHShIdt88X3VNu264ZBSAiR2zqign8lzUh8',NULL,'47.237.107.108','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia0pFSlFPTDVhRFhXaWdnTVliTUgwNUNjdk80RTVGcG5lbE9KR2l0diI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764478403),('EOCMQlpRsjN8ZeJmHSsXwHaEPWr9ETaX35chxS4k',NULL,'204.76.203.212','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 Edg/90.0.818.46','YToyOntzOjY6Il90b2tlbiI7czo0MDoiMkhBWEM4SUNLdFFDTDBweWw4OUpVZE9uOVh0TTIzRDJlRUVaS0NTNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1764476449),('F0ypI7b9GoZhB2l6IfE51TDsUNkJXM4CanjT2Pvo',NULL,'79.124.40.86','','YToyOntzOjY6Il90b2tlbiI7czo0MDoid0cyaWFxNW05WTljM3JtVnRUazlGVFppOTdPdmpHd1FZMGhKckM0TiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1764475475),('fFKynCZ2RmBKv4K9xTBZoUQOXu7fSLk7CeoqVKW5',NULL,'45.135.193.3','Mozilla/5.0 (Linux; Android 7.0; SM-G950U Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.137 Mobile Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWg3d1Vmam1vWU9rcjVFYUdrR3M1bFB5RWF3RTJ0SGxqeFNYRTRLTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8yMDMuODMuMzguMzQvP3BocGluZm89LTEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764477550),('H4SCh2E4xcU7gUi3kW5fs4c0V9YQBupnBKQ3IcKB',NULL,'103.203.59.1','HTTP Banner Detection (https://security.ipip.net)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYThIWlZ6YlBDWFlpbDBkekpNNVlaSFRQa3NwVU5WUmlRZFo1aVdXeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764474328),('ifGqiF6W1JAPGX0oEZzFLVXJR5U7hSTyUNRxKdup',NULL,'91.196.152.166','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTFNZNFVaQUtaQ3BWZG9BcHNDemt0M2JEanA1MFREYlNUdGg4M0ZtMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764471794),('IiEcUg5OhMS9H2vCvIbBsdEvPFotA4GGczRMu8Yg',NULL,'118.193.56.246','Mozilla/5.0 (Windows NT 9_0_2; Win64; x64) AppleWebKit/576.44 (KHTML, like Gecko) Chrome/90.0.2212 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVG9ONTZGdkRlNWtQZ0JuSkFWVFhOWkFNT2JhUmVxbWxmVWw5ZzVuciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8yMDMuODMuMzguMzQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764479536),('jcjNk5xDh5FJjEZ3ZmdMv1XTR6sfh06vr5gW2sgv',NULL,'103.55.225.46','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNFIwZGFPOE8xVnBjcjdLNUJ6MGhabm82a3F1WG9DUWFpZkRFVklGbCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764471523),('LlOhtnLKylgtMPZKKnJogYFohq1zFNOYrGNxPWrU',NULL,'184.105.247.252','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYXk1MjdmbDd0cFpRbUdVeHE2bDJXdkpGMlZMakhFUlJWTUV5OWhVSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764471959),('nbonMk0heArNdyVg98KJIUxybZUaHl3wxhYxvKRt',1,'160.19.226.57','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYndBVHkxMlIzUjRxREFwNm1mbk1nT3hKR0pDZWxCcFpxS1hTM3BpVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1764478836),('o1Xvc1hvybV9qR5z7MOEGy2GLJLEWbX9Yt3CpkcU',NULL,'35.203.210.81','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUJCeGJZdFpwVG1MQ1lRS2JVeTg2S01sY3JjWG5waVZaVURYVXBYNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764475486),('QLHz8iDFUcdga4I2xrivkBmbu0pYBhKLj8sk6WGq',NULL,'127.0.0.1','curl/8.5.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZTVlRGFMcDlWUXNJU09LcXlwdHNzSHNVRTNCQ0c1cENiV2FBc21GeSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764472372),('qWnCysEFG0o7ceB5p1Qz3MdmMRbI1SxaHUvzI7yj',1,'103.55.224.210','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS3FCSGxoY1I2SVdlYTZ4dEllRTBockxIRElJY2VrcXpnY1c5OTNjUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1764473715),('RqL1Jd2aNi7vwj6WspaeFX0SVxZF2sOu89zphdFf',NULL,'206.168.34.41','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRGZqeUdoYXRBbkV1akVaWkE4Y00wbmxadVVpSVJUeEFERVAyMk0ybSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vMjAzLjgzLjM4LjM0L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1764478749),('rwm7OJV59LqAY0Xgto8DQvHNE67DCVB8oVlWFqt2',NULL,'204.76.203.212','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 Edg/90.0.818.46','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVWRBTDFhN0NiQThQdHIzNklCUUpSMndud1FnUGRZN3hoQlRlVUNIMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1764471370),('sEAul6C5jYq5ApqR8YAlHcpKU403pnix6dIdgvHt',1,'160.19.226.57','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSjVjcmdOTTRsZUJBVWJFWjB4bVdDeEt6UnV4WXdsbGh6M0VqWVlIayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1764478463),('TtXhh0dM4T0jqAX4Ci77MkiwadjebmsHBba6Eyy0',NULL,'35.203.210.81','curl/7.68.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUWZYZFI3Z2pYZUFJZ1E4OFlkVU5NVGNBSWxlU2FNTkI4SkxLVGN6NyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764475088),('UOyDPVolF9RX4HOaZg0gB3dPsVRmBcq3M4jYanEx',NULL,'47.237.107.108','','YTozOntzOjY6Il90b2tlbiI7czo0MDoicUpUR0w3amJoVFNyeHc1N0xpZlVFcTI0Vm1JdXpvYWhmbm94alRKTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHBzOi8vMjAzLjgzLjM4LjM0IjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkFFckV1WWdkQUVPMWxYa2siO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764478403),('XswOvbC3bnZ05nkQP27Hq6JdGsP2MCDRcoAAL9r2',NULL,'127.0.0.1','curl/8.5.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnAyWjRRUVJNQ2R4Vno4OEJ2ZjNDTnl1elA2Rlk2Vk9pdTAwRnhxcyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHBzOi8vcHJvamVjdGFraGlyMS5zZXJ2ZXJkYXRhLmFzaWEvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1764472116),('Yx0Rva91fp9V9K9PSYBH4Ji9UXzR8ieeP9mu25W5',NULL,'152.32.211.69','curl/7.29.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUNJM1dmMlRlaWtPdTlUWURkSVlFRnEwNzZpcWFDcXZFcTl6ZGpNciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8yMDMuODMuMzguMzQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6QUVyRXVZZ2RBRU8xbFhrayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1764478288);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_sale_items`
--

DROP TABLE IF EXISTS `ticket_sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_sale_id` bigint unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `qty` int unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `line_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_sale_items_product_id_index` (`product_id`),
  KEY `ticket_sale_items_ticket_sale_id_index` (`ticket_sale_id`),
  CONSTRAINT `ticket_sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `ticket_sale_items_ticket_sale_id_foreign` FOREIGN KEY (`ticket_sale_id`) REFERENCES `ticket_sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_sale_items`
--

LOCK TABLES `ticket_sale_items` WRITE;
/*!40000 ALTER TABLE `ticket_sale_items` DISABLE KEYS */;
INSERT INTO `ticket_sale_items` VALUES (1,10,1,1,1500000.00,0.00,0.00,1500000.00,'2025-11-25 04:44:45'),(2,11,1,1,1500000.00,0.00,0.00,1500000.00,'2025-11-25 04:59:01');
/*!40000 ALTER TABLE `ticket_sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_sales`
--

DROP TABLE IF EXISTS `ticket_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cashier_id` bigint unsigned NOT NULL,
  `total_qty` int unsigned NOT NULL DEFAULT '0',
  `gross_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('open','paid','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_sales_invoice_no_unique` (`invoice_no`),
  KEY `ticket_sales_sale_date_index` (`sale_date`),
  KEY `ticket_sales_cashier_id_index` (`cashier_id`),
  KEY `ticket_sales_created_by_status_created_at_index` (`created_by`,`status`,`created_at`),
  CONSTRAINT `ticket_sales_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_sales`
--

LOCK TABLES `ticket_sales` WRITE;
/*!40000 ALTER TABLE `ticket_sales` DISABLE KEYS */;
INSERT INTO `ticket_sales` VALUES (2,'INV-20251120-0001','2025-11-17 08:07:18',1,0,0.00,230.00,-230.00,'paid',0.00,NULL,'2025-11-20 01:07:18','2025-11-20 01:07:18'),(4,'INV-20251120-0002','2025-11-19 08:08:57',1,0,0.00,1000.00,-1000.00,'paid',0.00,NULL,'2025-11-20 01:08:57','2025-11-20 01:08:57'),(5,'INV-20251120-0003','2025-11-16 08:09:23',1,17,6650000.00,3000.00,6647000.00,'paid',0.00,NULL,'2025-11-20 01:09:23','2025-11-20 01:09:24'),(6,'INV-20251120-0004','2025-11-19 08:09:24',1,11,7650000.00,0.00,7650000.00,'paid',0.00,NULL,'2025-11-20 01:09:24','2025-11-20 01:09:24'),(7,'INV-20251120-0005','2025-11-20 08:09:24',1,15,19500000.00,5000.00,19495000.00,'paid',0.00,NULL,'2025-11-20 01:09:24','2025-11-20 01:09:25'),(8,'INV-20251120-0006','2025-11-15 08:09:25',1,9,13500000.00,4000.00,13496000.00,'paid',0.00,NULL,'2025-11-20 01:09:25','2025-11-20 01:09:25'),(9,'INV-20251120-0007','2025-11-17 08:09:25',1,10,2200000.00,1000.00,2199000.00,'paid',0.00,NULL,'2025-11-20 01:09:25','2025-11-20 01:09:26'),(10,'INV-20251125-0001','2025-11-25 04:44:45',1,1,1500000.00,123.00,1499877.00,'paid',0.00,NULL,'2025-11-24 21:44:45','2025-11-24 21:44:45'),(11,'INV-20251125-0002','2025-11-25 04:59:01',1,1,1500000.00,0.00,1500000.00,'paid',0.00,NULL,'2025-11-24 21:59:01','2025-11-24 21:59:01');
/*!40000 ALTER TABLE `ticket_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` smallint unsigned NOT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_block` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'superadmin','$2y$12$2jbVdYO0mQlZzd.fL9FnKeIbeGEXA/oLMtCNyhym7nY0qm7UZ7KyC','Super Administrator','admin@airpanas.local',1,NULL,0,'2025-11-19 21:45:17','2025-11-29 19:19:00',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vw_ticket_sales_daily`
--

DROP TABLE IF EXISTS `vw_ticket_sales_daily`;
/*!50001 DROP VIEW IF EXISTS `vw_ticket_sales_daily`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_ticket_sales_daily` AS SELECT 
 1 AS `sale_date`,
 1 AS `cashier_id`,
 1 AS `cashier_name`,
 1 AS `total_transactions`,
 1 AS `total_qty`,
 1 AS `gross_amount`,
 1 AS `discount_amount`,
 1 AS `net_amount`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_ticket_sales_daily`
--

/*!50001 DROP VIEW IF EXISTS `vw_ticket_sales_daily`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`walini_user`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_ticket_sales_daily` AS select cast(`ts`.`sale_date` as date) AS `sale_date`,`ts`.`cashier_id` AS `cashier_id`,`u`.`full_name` AS `cashier_name`,count(`ts`.`id`) AS `total_transactions`,sum(`ts`.`total_qty`) AS `total_qty`,sum(`ts`.`gross_amount`) AS `gross_amount`,sum(`ts`.`discount_amount`) AS `discount_amount`,sum(`ts`.`net_amount`) AS `net_amount` from (`ticket_sales` `ts` join `users` `u` on((`u`.`id` = `ts`.`cashier_id`))) where (`ts`.`status` <> 'void') group by cast(`ts`.`sale_date` as date),`ts`.`cashier_id`,`u`.`full_name` order by `sale_date` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-30 12:17:11
