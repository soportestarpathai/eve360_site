CREATE DATABASE  IF NOT EXISTS `eve_app_12` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `eve_app_12`;
-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 70.35.200.34    Database: eve_app_12
-- ------------------------------------------------------
-- Server version	8.0.29

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(80) NOT NULL DEFAULT '',
  `entity_id` int DEFAULT NULL,
  `ip` varchar(64) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `payload_json` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `entity_type` (`entity_type`,`entity_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avisos`
--

DROP TABLE IF EXISTS `avisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `mes_reportado` char(6) NOT NULL,
  `referencia_aviso` varchar(255) NOT NULL,
  `prioridad` tinyint NOT NULL,
  `tipo_alerta` varchar(10) NOT NULL,
  `descripcion_alerta` text,
  `clave_entidad_colegiada` varchar(13) NOT NULL,
  `clave_sujeto_obligado` varchar(13) NOT NULL,
  `pa_nombre` varchar(200) NOT NULL,
  `pa_ap` varchar(200) NOT NULL,
  `pa_am` varchar(200) NOT NULL,
  `pa_fn` date NOT NULL,
  `pa_rfc` varchar(13) NOT NULL,
  `pa_curp` varchar(18) NOT NULL,
  `instrumento_publico` varchar(60) NOT NULL,
  `fecha_operacion` date NOT NULL,
  `identificador_fideicomiso` varchar(60) NOT NULL,
  `rfc_fiduciario` varchar(13) NOT NULL,
  `denominacion_razon` varchar(300) NOT NULL,
  `tipo_cesion` tinyint NOT NULL,
  `monto_cesion` decimal(18,2) NOT NULL,
  `es_modificatorio` tinyint(1) NOT NULL DEFAULT '0',
  `folio_modificacion` varchar(60) DEFAULT NULL,
  `descripcion_modificacion` varchar(3000) DEFAULT NULL,
  `cedente_client_id` int DEFAULT NULL,
  `cesionario_client_id` int DEFAULT NULL,
  `cedente_tipo` enum('FISICA','MORAL','FIDEICOMISO') NOT NULL,
  `cedente_nombre` varchar(300) DEFAULT NULL,
  `cedente_ap` varchar(200) DEFAULT NULL,
  `cedente_am` varchar(200) DEFAULT NULL,
  `cedente_fecha` date DEFAULT NULL,
  `cedente_rfc` varchar(13) DEFAULT NULL,
  `cedente_pais` char(2) DEFAULT NULL,
  `cedente_actividad` varchar(20) DEFAULT NULL,
  `cedente_giro` varchar(20) DEFAULT NULL,
  `cedente_identificador_fideicomiso` varchar(60) DEFAULT NULL,
  `cesionario_tipo` enum('FISICA','MORAL','FIDEICOMISO') NOT NULL,
  `cesionario_nombre` varchar(300) DEFAULT NULL,
  `cesionario_ap` varchar(200) DEFAULT NULL,
  `cesionario_am` varchar(200) DEFAULT NULL,
  `cesionario_fecha` date DEFAULT NULL,
  `cesionario_rfc` varchar(13) DEFAULT NULL,
  `cesionario_pais` char(2) DEFAULT NULL,
  `cesionario_actividad` varchar(20) DEFAULT NULL,
  `cesionario_giro` varchar(20) DEFAULT NULL,
  `cesionario_identificador_fideicomiso` varchar(60) DEFAULT NULL,
  `status` enum('DRAFT','XML_GENERATED') NOT NULL DEFAULT 'DRAFT',
  `xml_path` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `status` (`status`),
  KEY `fk_av_cedente_client` (`cedente_client_id`),
  KEY `fk_av_cesionario_client` (`cesionario_client_id`),
  CONSTRAINT `fk_av_cedente_client` FOREIGN KEY (`cedente_client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_av_cesionario_client` FOREIGN KEY (`cesionario_client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_av_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avisos`
--

LOCK TABLES `avisos` WRITE;
/*!40000 ALTER TABLE `avisos` DISABLE KEYS */;
/*!40000 ALTER TABLE `avisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_documents`
--

DROP TABLE IF EXISTS `client_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `doc_tipo` varchar(50) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `vence` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `vence` (`vence`),
  CONSTRAINT `fk_docs_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_documents`
--

LOCK TABLES `client_documents` WRITE;
/*!40000 ALTER TABLE `client_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('FISICA','MORAL','FIDEICOMISO') NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `nombres` varchar(150) DEFAULT NULL,
  `apellido_paterno` varchar(100) DEFAULT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `rfc` varchar(13) NOT NULL,
  `curp` varchar(18) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `pais_nacionalidad` char(2) DEFAULT NULL,
  `pais_nacimiento` char(2) DEFAULT NULL,
  `actividad_economica` varchar(20) DEFAULT NULL,
  `ocupacion` varchar(200) DEFAULT NULL,
  `extranjero` tinyint(1) NOT NULL DEFAULT '0',
  `giro_mercantil` varchar(20) DEFAULT NULL,
  `fecha_base` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `dom_calle` varchar(200) DEFAULT NULL,
  `dom_num_ext` varchar(50) DEFAULT NULL,
  `dom_num_int` varchar(50) DEFAULT NULL,
  `dom_colonia` varchar(150) DEFAULT NULL,
  `dom_municipio` varchar(150) DEFAULT NULL,
  `dom_ciudad` varchar(150) DEFAULT NULL,
  `dom_estado` varchar(150) DEFAULT NULL,
  `dom_cp` varchar(10) DEFAULT NULL,
  `tel1` varchar(50) DEFAULT NULL,
  `tel1_ext` varchar(10) DEFAULT NULL,
  `tel2` varchar(50) DEFAULT NULL,
  `tel2_ext` varchar(10) DEFAULT NULL,
  `firma_path` varchar(255) DEFAULT NULL,
  `objeto_social` text,
  `rep_nombre` varchar(200) DEFAULT NULL,
  `rep_rfc` varchar(13) DEFAULT NULL,
  `rep_curp` varchar(18) DEFAULT NULL,
  `rep_nacionalidad` char(2) DEFAULT NULL,
  `rep_fecha_nacimiento` date DEFAULT NULL,
  `rep_firma_path` varchar(255) DEFAULT NULL,
  `fideicomiso_identificador` varchar(100) DEFAULT NULL,
  `fiduciario_rfc` varchar(13) DEFAULT NULL,
  `fiduciario_nombre` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rfc` (`rfc`),
  KEY `tipo` (`tipo`),
  KEY `idx_clients_tipo` (`tipo`),
  KEY `idx_clients_rfc` (`rfc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token_hash` (`token_hash`),
  CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,1,'f9090cb990a91581269ecd2f9865e059df4dc80b1c7526167dc5a3bb18403476','2025-12-30 18:18:15','2025-12-30 16:24:13','2025-12-30 22:18:15');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (8,'audit.view'),(4,'avisos.edit'),(3,'avisos.view'),(2,'clientes.edit'),(1,'clientes.view'),(6,'config.edit'),(5,'config.view'),(7,'users.manage');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_rp_perm` (`permission_id`),
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(2,1),(1,2),(2,2),(1,3),(2,3),(1,4),(2,4),(1,5),(1,6),(1,7),(1,8);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin'),(2,'Capturista');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('brand_logo_path',''),('brand_primary_color','#5b1b2e'),('brand_secondary_color','#b58d4a'),('doc_expiry_days','30'),('required_avisos_json','{\n  \"require_monthly\": true,\n  \"month_offset\": 0,\n  \"status_required\": \"XML_GENERATED\"\n}'),('required_docs_json','{\n  \"FISICA\": [\"INE\",\"RFC\"],\n  \"MORAL\": [\"Acta constitutiva\",\"RFC\"],\n  \"FIDEICOMISO\": [\"Contrato fideicomiso\",\"RFC fiduciario\"]\n}');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trusted_devices`
--

DROP TABLE IF EXISTS `trusted_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trusted_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `selector` char(24) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `user_agent_hash` char(64) NOT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_selector` (`selector`),
  KEY `idx_user` (`user_id`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trusted_devices`
--

LOCK TABLES `trusted_devices` WRITE;
/*!40000 ALTER TABLE `trusted_devices` DISABLE KEYS */;
INSERT INTO `trusted_devices` VALUES (1,1,'e4c09e1f3e95741986356683','012f2890e7a1cc5d8e70689f3ab1c4265afcaded0a1522172dd1da755fc251c2','7e7a955d30b703da7b35087be460ef5ded2baa485033127e6bc0ac5832c3b6ad','97dbc26f5a7c805acc9972dafa7f9081e90a86770c51f13b852e7d8d856facee','2025-12-31 10:29:43',NULL,'2026-01-30 11:29:43',NULL);
/*!40000 ALTER TABLE `trusted_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `two_factor_codes`
--

DROP TABLE IF EXISTS `two_factor_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `two_factor_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_tfa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `two_factor_codes`
--

LOCK TABLES `two_factor_codes` WRITE;
/*!40000 ALTER TABLE `two_factor_codes` DISABLE KEYS */;
INSERT INTO `two_factor_codes` VALUES (1,1,'$2y$12$M5orNB5w7yXGbHG.q4w1jOwTS2otsQT8T0wYb9v4gTgmrO.dHIO9.','2025-12-30 17:34:17','2025-12-30 16:27:11','2025-12-30 22:24:17'),(2,1,'$2y$12$5.4bZ1BW80XZLtxYaLAbFuTWlhIYPEpKDpaMTv4F7KVd3U55eRwDm','2025-12-30 20:41:21',NULL,'2025-12-31 01:31:21'),(3,1,'$2y$12$2vGnk0Fx5yjv68baS49cEOOMh9MUjCbrwpbAIrB8GbCsc8xPmxMOG','2025-12-30 20:46:20',NULL,'2025-12-31 01:36:21'),(4,1,'$2y$12$bXvDhOu6QgKBzG5M58Vgwev3OL8JY3X8/d.X6JSGpSC2ZXBKluy1i','2025-12-31 11:39:18','2025-12-31 10:29:43','2025-12-31 16:29:19');
/*!40000 ALTER TABLE `two_factor_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_ur_role` (`role_id`),
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `failed_attempts` int NOT NULL DEFAULT '0',
  `lockout_until` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrador','marcozm1978@gmail.com','$2y$12$OsU7aQ8k4Z8VRo2iJ.1/w.xsG/G3aYGTFVhVfcHPe.pnaPiqvSE0e',0,0,NULL,'2025-12-30 13:04:05','2025-12-30 13:04:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-05 11:34:53
