-- MySQL dump 10.13  Distrib 5.5.33, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: credit_jeeves2_test
-- ------------------------------------------------------
-- Server version	5.5.33-0+wheezy1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_token`
--

DROP TABLE IF EXISTS `access_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B6A2DD685F37A13B` (`token`),
  KEY `IDX_B6A2DD68A76ED395` (`user_id`),
  KEY `IDX_B6A2DD6819EB6921` (`client_id`),
  CONSTRAINT `FK_B6A2DD6819EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  CONSTRAINT `FK_B6A2DD68A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_token`
--

LOCK TABLES `access_token` WRITE;
/*!40000 ALTER TABLE `access_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_update_user`
--

DROP TABLE IF EXISTS `api_update_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_update_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_33880B58A76ED395` (`user_id`),
  CONSTRAINT `FK_33880B58A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_update_user`
--

LOCK TABLES `api_update_user` WRITE;
/*!40000 ALTER TABLE `api_update_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_update_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atb_simulation`
--

DROP TABLE IF EXISTS `atb_simulation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `atb_simulation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_report_id` bigint(20) NOT NULL,
  `type` enum('score','cash','search') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'score' COMMENT '(DC2Type:AtbType)',
  `input` double NOT NULL,
  `sim_type` bigint(20) NOT NULL,
  `score_current` longtext COLLATE utf8_unicode_ci NOT NULL,
  `score_target` bigint(20) NOT NULL,
  `transaction_signature` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `result` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BD5BF4F22A26A0ED` (`cj_applicant_report_id`),
  CONSTRAINT `FK_BD5BF4F22A26A0ED` FOREIGN KEY (`cj_applicant_report_id`) REFERENCES `cj_applicant_report` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atb_simulation`
--

LOCK TABLES `atb_simulation` WRITE;
/*!40000 ALTER TABLE `atb_simulation` DISABLE KEYS */;
INSERT INTO `atb_simulation` VALUES (1,5,'score',122,400,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=',620,'NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3 icy26YjqrpzMRSGs73qWNpUdTPAgr6au/SPzrt2WKi/M9d8XlVJbpzqRuj0u jiweXGME8cuua8o4xSq3DgI51ZfMhwgm0kCzoYUb88S3qG2imFD8ujts1Jxb 2gfJUQMHdyDJ','2iOPFjwaLu/QhvsRihc0zWBU0FqjAtxhuYA9DtREruchwtsPaykvwkU/tA6TGluGy16XWYYvaCLbYwPMcRr/jNE83f6wzszvKB3+0aYiljIjR9tl01xqvG+iKJkb/IbxUT6i0S4ACCz4zPcKa9OAFEbtg6eNgjHIsTOIIiWdJ2VPqZCJjfRz8Th/ZW3h2F/Xx2Z1U63432k00wXgYVdVFr8EFvjvEFh/buX4LFuKt0AB3Fc4jC2yMw/3zGvQJPFMl2uHU37lbX6JNMaWt5mxGZP6ix7SRv03ahPHzny+KrEuDTi/KIZ2gj5VD6XI7r6aDUDy3VOdKfJy+Y3b5e2uhoKTOawOugiz/+4ucE332EdzTWTp1yn+g2EXWM6cw2sHasfwQqnqaprhcZgAiQkbYOwfycWFbZ2frqN+0la1URod5stiTA9JFQTFRIfn16EKphDhch/FiTH6X1cEbdtHOBb2HTMQWBJm6cDuVzS8JFmQD+mT6b2Y8eqkkFYplNkkYr0XThWt0sgkhLhaMRDJMi1/FILDN2PAKb5MHNLcKutz56TWPJEmWjoteeSPmvCjLeHFGdqi2EbY/quCXBQJSYx0QeRgiL4f6yhmFa6RIwhTnlIMirm0Cwq/15WPq4tGjfAo7GcNw+nWynXuM7vbf/vsU5S/5kEyAjR2NK4wut7LoeFTReJnHt9Or66IGRJRQr+PBpDFAGJl9fYeneQguQ==','2014-04-07 16:10:58','2014-04-07 16:10:58'),(2,5,'search',1000,103,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=',620,'NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3 icy26YjqrpzMRSGs73qWNpUdTPAgr6au/SPzrt2WKi/M9d8XlVJbpzqRuj0u jiweXGME8cuua8o4xSq3DgI51ZfMh3bPLt648qVg2L13kgnLdl+pFU3J3v9h YtLusEoQ4odZ','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','2014-04-07 16:10:58','2014-04-07 16:10:58');
/*!40000 ALTER TABLE `atb_simulation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_code`
--

DROP TABLE IF EXISTS `auth_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uri` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5933D02C5F37A13B` (`token`),
  KEY `IDX_5933D02CA76ED395` (`user_id`),
  KEY `IDX_5933D02C19EB6921` (`client_id`),
  CONSTRAINT `FK_5933D02C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  CONSTRAINT `FK_5933D02CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_code`
--

LOCK TABLES `auth_code` WRITE;
/*!40000 ALTER TABLE `auth_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_account_group`
--

DROP TABLE IF EXISTS `cj_account_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_account_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_affiliate_id` bigint(20) DEFAULT NULL,
  `holding_id` bigint(20) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `dealer_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target_score` bigint(20) DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `website_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street_address_1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street_address_2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fee_type` enum('flat','lead') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'flat' COMMENT '(DC2Type:GroupFeeType)',
  `contract` longtext COLLATE utf8_unicode_ci,
  `contract_date` date DEFAULT NULL,
  `type` enum('vehicle','estate','generic','rent') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'vehicle' COMMENT '(DC2Type:GroupType)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FCA7EE8877153098` (`code`),
  KEY `IDX_FCA7EE881047997E` (`cj_affiliate_id`),
  KEY `IDX_FCA7EE886CD5FBA3` (`holding_id`),
  KEY `IDX_FCA7EE88727ACA70` (`parent_id`),
  KEY `IDX_FCA7EE88249E6EA1` (`dealer_id`),
  CONSTRAINT `FK_FCA7EE88249E6EA1` FOREIGN KEY (`dealer_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_FCA7EE881047997E` FOREIGN KEY (`cj_affiliate_id`) REFERENCES `cj_affiliate` (`id`),
  CONSTRAINT `FK_FCA7EE886CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`),
  CONSTRAINT `FK_FCA7EE88727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_account_group`
--

LOCK TABLES `cj_account_group` WRITE;
/*!40000 ALTER TABLE `cj_account_group` DISABLE KEYS */;
INSERT INTO `cj_account_group` VALUES (1,NULL,NULL,NULL,NULL,'Credit Jeeves\' Stuff',NULL,'DZC6K2OAG3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'vehicle','2014-04-07 16:10:54','2014-04-07 16:10:54'),(2,1,NULL,NULL,NULL,'LA Honda Dealer',700,'DVRWP2NFQ6','Congratulations on improving your credit score!\nWe want to help you drive away with your dream car.\nCall us within the next 24 hours and we’ll give you $250 off!','www.honda.com',NULL,'805-555-1212',NULL,'124 Hitchcock Way',NULL,'Santa Barbara','CA','93101','flat',NULL,NULL,'vehicle','2014-04-07 16:10:54','2014-04-07 16:10:54'),(3,2,NULL,NULL,NULL,'LA BMW Dealer',900,'DZC6K2PQC6',NULL,'http://www.bmw.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'lead',NULL,'2013-07-03','vehicle','2014-04-07 16:10:54','2014-04-07 16:10:54'),(4,NULL,1,NULL,NULL,'US Cars',750,'DZC6K2QG93',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,'2014-03-28','vehicle','2014-04-07 16:10:54','2014-04-07 16:10:54'),(5,NULL,NULL,NULL,NULL,'AutoTrader',700,'DZC6LV0KUZ',NULL,'www.autotrader.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat','Test Contract text','2014-03-28','vehicle','2014-03-23 16:10:54','2014-03-28 16:10:54'),(6,NULL,NULL,NULL,NULL,'AutoNation',725,'DZC6MG3JVJ',NULL,'http://www.autonation.com/',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat','Test Contract text','2014-03-28','vehicle','2014-03-23 16:10:54','2014-03-28 16:10:54'),(7,NULL,NULL,NULL,NULL,'BMW',705,'DZC6PK79DK',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat','Test Contract text','2014-03-28','vehicle','2014-03-23 16:10:54','2014-03-28 16:10:54'),(8,NULL,NULL,NULL,NULL,'HONDA',715,'DZC6PQYDR8','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:27:18','2012-11-29 14:27:18'),(9,NULL,1,NULL,NULL,'AUDI',725,'DZC6Q9F645','','','','','','','','',NULL,'','flat','','2014-03-28','vehicle','2012-11-29 14:27:48','2012-11-29 14:27:48'),(10,NULL,NULL,NULL,NULL,'MERSEDES',740,'DZC6QHG82F','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:28:01','2012-11-29 14:28:01'),(11,NULL,1,NULL,NULL,'RENAULT',500,'DZC6T6GYPY','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:30:36','2012-11-29 14:30:36'),(12,NULL,NULL,NULL,NULL,'FORD',350,'DZC6TNZYSU','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:31:04','2012-11-29 14:31:04'),(13,NULL,NULL,NULL,NULL,'SAAB',600,'DZC6TZE0QH','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:31:22','2012-11-29 14:31:22'),(14,NULL,NULL,NULL,NULL,'LOTUS',700,'DZC6U6X53Y','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:31:34','2012-11-29 14:31:34'),(15,NULL,NULL,NULL,NULL,'MINI',710,'DZC6UD0P5V','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:31:44','2012-11-29 14:31:44'),(16,NULL,NULL,NULL,NULL,'FERRARI',725,'DZC6XE2Z91','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:34:39','2012-11-29 14:34:39'),(17,NULL,NULL,NULL,NULL,'PONTIAC',700,'DZC6XPDZNT','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:34:57','2012-11-29 14:34:57'),(18,NULL,NULL,NULL,NULL,'MASERATI',725,'DZC6YAXLEL','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:35:31','2012-11-29 14:35:31'),(19,NULL,NULL,NULL,NULL,'DODGE',725,'DZC6YIKTKR','','','','','','','','',NULL,'','flat',NULL,'2014-04-07','vehicle','2012-11-29 14:35:44','2012-11-29 14:35:44'),(20,NULL,NULL,NULL,NULL,'Vehicle group',750,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'vehicle','2014-02-06 16:10:54','2014-03-30 16:10:54'),(21,NULL,NULL,NULL,NULL,'Estate group',850,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'estate','2014-02-06 16:10:54','2014-03-30 16:10:54'),(22,NULL,NULL,NULL,NULL,'Generic group',850,'GENERIC',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'generic','2014-02-06 16:10:54','2014-03-30 16:10:54'),(23,NULL,4,NULL,NULL,'700Credit',900,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'generic','2014-02-06 16:10:54','2014-03-30 16:10:54'),(24,NULL,5,NULL,NULL,'Test Rent Group',NULL,'DXC6KXOAGX',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2014-04-07 16:10:59'),(25,NULL,5,NULL,NULL,'Sea side Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2014-04-07 16:10:59'),(26,NULL,5,NULL,NULL,'Campus Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2012-11-29 14:35:44'),(27,NULL,5,NULL,NULL,'Western Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2012-11-29 14:35:44'),(28,NULL,5,NULL,NULL,'Kharkov Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2012-11-29 14:35:44'),(29,NULL,6,NULL,NULL,'Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2012-11-29 14:35:44'),(30,NULL,7,NULL,NULL,'Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2012-11-29 14:35:44'),(31,NULL,8,NULL,NULL,'Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44','2012-11-29 14:35:44');
/*!40000 ALTER TABLE `cj_account_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_account_group_affiliate`
--

DROP TABLE IF EXISTS `cj_account_group_affiliate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_account_group_affiliate` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_account_group_id` bigint(20) NOT NULL,
  `cj_account_id` bigint(20) NOT NULL,
  `website_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `external_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `culture` enum('en','hi','test','es') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en' COMMENT '(DC2Type:UserCulture)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1096A96612867DD` (`cj_account_group_id`),
  KEY `IDX_1096A966ED8F6A55` (`cj_account_id`),
  CONSTRAINT `FK_1096A966ED8F6A55` FOREIGN KEY (`cj_account_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_1096A96612867DD` FOREIGN KEY (`cj_account_group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_account_group_affiliate`
--

LOCK TABLES `cj_account_group_affiliate` WRITE;
/*!40000 ALTER TABLE `cj_account_group_affiliate` DISABLE KEYS */;
INSERT INTO `cj_account_group_affiliate` VALUES (1,1,2,'renttrack.te/dealer_test.php/test/iframe/key/DXFYBYHX4H','token','DXFYBYHX4H','test','2014-04-07 16:10:58','2014-04-07 16:10:58');
/*!40000 ALTER TABLE `cj_account_group_affiliate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_address`
--

DROP TABLE IF EXISTS `cj_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_address` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `unit` longtext COLLATE utf8_unicode_ci,
  `number` longtext COLLATE utf8_unicode_ci,
  `street` longtext COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `area` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'US',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C338DAAA76ED395` (`user_id`),
  CONSTRAINT `FK_C338DAAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_address`
--

LOCK TABLES `cj_address` WRITE;
/*!40000 ALTER TABLE `cj_address` DISABLE KEYS */;
INSERT INTO `cj_address` VALUES (1,17,'KCR5mqRFPMqvKcvLNWvDdQ7yEg7lVAV+TcNzwwIyBic=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','fd1EP60ePDBM/I3LFe/iV+GIFO0E2IymJO9TTQX4XZ4=','22007',NULL,'Minsk','KY','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(2,18,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','09061',NULL,'APO','AE','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(3,19,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(4,20,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(5,21,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(6,21,'LurhFM+EUDuMzXyx2UtD+zq6iqUsgict6GtUhF9QaJw=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','ZqQL8+ZsaTBM7GJqqLkZYGYvpXf8z3HxGuB/PECPJs0=','220121',NULL,'BOSTON','MA','US',0,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(7,22,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','207041563',NULL,'BELTSVILLE','MD','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(8,23,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','5nznoC91xMVg15bhflTHNktPLnuBU/9k5dEa4f0iMoo=','762086621',NULL,'DENTON','TX','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(9,24,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','O/zsu8VPgnrp0fC2RvhrnNxWSWVNKBKMQLFDU/P0nFA=','152322008',NULL,'PITTSBURGH','PA','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(10,25,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SVXIi8fUY60qzk90eCG/Q9XEm1gZNKzPP2BN0mip4tU=','903013646',NULL,'INGLEWOOD','CA','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(11,26,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','KiisT3NW6qjjtUinG7rBQvij2rSGqyzT25h+iphhoiA=','595210121',NULL,'BOX ELDER','MT','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(12,27,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','19383',NULL,'WEST CHESTER','PA','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(13,28,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','669016102',NULL,'EMPORIA','KS','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(14,29,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','20704',NULL,'BELTSVILLE','MD','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(15,30,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r2p/YbwFVn8ecwkl61Ruv2enp77i2syVzEsMlRDnEio=','33647',NULL,'TAMPA','FL','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(16,31,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SJBba/YJoTOv9lviP+zLeq4OG94BF+RqH2Rn4zp35gs=','20704',NULL,'MILLINGTON','MD','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(17,32,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','c+wTR0igJhHWsb6+S5LFigC0dUlbghDKKozLdE5EDmk=','916056557',NULL,'NORTH HOLLYWOOD','CA','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(18,33,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r3ojofT4IeiYzx4+5z4YBvb+MCVK0Utq4OgJ2gkvdOg=','09182',NULL,'APO','AE','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(19,34,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tthBcFLx5KwVgTvkVAyoNI47IVZONrRf7ugUe6JtUTo=','11005',NULL,'FLORAL PARK','NY','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(20,35,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','jb4ADCnMQxJH/rf1qEdp7N+wDlfmmWfjf50osDW/m5E=','22306',NULL,'ALEXANDRIA','VA','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(21,36,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Zc43+kxuy7Da9stGjSuxTSuL8peKbQJyKqPvQhX3Caw=','26214',NULL,'BALTIMORE','MO','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(22,40,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','pPM5E/D3Ba2vLfhVYf0u3KeMGna+iGNJD1Qs3rnowg4=','49548',NULL,'GRAND RAPIDS','MI','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(23,39,'gLDyiKI/Xr86USmnUQmCbYACFAPpw4vVX5N2JWmnvrg=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','uApKhcWwpoMx2boabIsSItfu5gQhqZcjIHBeEcxkJ40=','916056801',NULL,'NORTH HOLLYWOOD','CA','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(24,38,'Lxh/LBScURlCK34fv3U1T8aLN/VAnGIrIDiK5gibymY=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','K/f4DDwQFGryObS/xENBc8JWxeUypECTtL1CrMj8yoE=','660491614',NULL,'LAWRENCE','KS','US',1,'2014-04-07 16:10:54','2014-04-07 16:10:54'),(25,42,'Lxh/LBScURlCK34fv3U1T8aLN/VAnGIrIDiK5gibymY=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','K/f4DDwQFGryObS/xENBc8JWxeUypECTtL1CrMj8yoE=','660491614',NULL,'LAWRENCE','KS','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(26,43,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','49',NULL,'APO','AE','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(27,44,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(28,45,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(29,46,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(30,47,'KCR5mqRFPMqvKcvLNWvDdQ7yEg7lVAV+TcNzwwIyBic=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','fd1EP60ePDBM/I3LFe/iV+GIFO0E2IymJO9TTQX4XZ4=','22007',NULL,'Minsk','KY','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(31,48,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','207041563',NULL,'BELTSVILLE','MD','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(32,49,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','5nznoC91xMVg15bhflTHNktPLnuBU/9k5dEa4f0iMoo=','762086621',NULL,'DENTON','TX','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(33,50,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','O/zsu8VPgnrp0fC2RvhrnNxWSWVNKBKMQLFDU/P0nFA=','152322008',NULL,'PITTSBURGH','PA','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(34,51,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SVXIi8fUY60qzk90eCG/Q9XEm1gZNKzPP2BN0mip4tU=','903013646',NULL,'INGLEWOOD','CA','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(35,52,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','KiisT3NW6qjjtUinG7rBQvij2rSGqyzT25h+iphhoiA=','595210121',NULL,'BOX ELDER','MT','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(36,53,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','19383',NULL,'WEST CHESTER','PA','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(37,54,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','669016102',NULL,'EMPORIA','KS','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(38,55,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r2p/YbwFVn8ecwkl61Ruv2enp77i2syVzEsMlRDnEio=','33647',NULL,'TAMPA','FL','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(39,56,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SJBba/YJoTOv9lviP+zLeq4OG94BF+RqH2Rn4zp35gs=','20704',NULL,'MILLINGTON','MD','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(40,57,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','c+wTR0igJhHWsb6+S5LFigC0dUlbghDKKozLdE5EDmk=','916056557',NULL,'NORTH HOLLYWOOD','CA','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(41,58,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tthBcFLx5KwVgTvkVAyoNI47IVZONrRf7ugUe6JtUTo=','11005',NULL,'FLORAL PARK','NY','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(42,59,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','jb4ADCnMQxJH/rf1qEdp7N+wDlfmmWfjf50osDW/m5E=','22306',NULL,'ALEXANDRIA','VA','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(43,60,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Zc43+kxuy7Da9stGjSuxTSuL8peKbQJyKqPvQhX3Caw=','26214',NULL,'BALTIMORE','MO','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(44,62,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','pPM5E/D3Ba2vLfhVYf0u3KeMGna+iGNJD1Qs3rnowg4=','49548',NULL,'GRAND RAPIDS','MI','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(45,63,'Lxh/LBScURlCK34fv3U1T8aLN/VAnGIrIDiK5gibymY=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','K/f4DDwQFGryObS/xENBc8JWxeUypECTtL1CrMj8yoE=','660491614',NULL,'LAWRENCE','KS','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(46,64,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','49',NULL,'APO','AE','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(47,65,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(48,66,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(49,67,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(50,69,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','49',NULL,'APO','AE','US',1,'2014-04-07 16:11:00','2014-04-07 16:11:00'),(51,42,'KCR5mqRFPMqvKcvLNWvDdQ7yEg7lVAV+TcNzwwIyBic=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','fd1EP60ePDBM/I3LFe/iV+GIFO0E2IymJO9TTQX4XZ4=','22007',NULL,'Minsk','KY','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(52,43,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','09061',NULL,'APO','AE','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(53,44,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(54,45,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(55,46,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(56,47,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','207041563',NULL,'BELTSVILLE','MD','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(57,48,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','5nznoC91xMVg15bhflTHNktPLnuBU/9k5dEa4f0iMoo=','762086621',NULL,'DENTON','TX','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(58,49,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','O/zsu8VPgnrp0fC2RvhrnNxWSWVNKBKMQLFDU/P0nFA=','152322008',NULL,'PITTSBURGH','PA','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(59,50,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SVXIi8fUY60qzk90eCG/Q9XEm1gZNKzPP2BN0mip4tU=','903013646',NULL,'INGLEWOOD','CA','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(60,51,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','KiisT3NW6qjjtUinG7rBQvij2rSGqyzT25h+iphhoiA=','595210121',NULL,'BOX ELDER','MT','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(61,52,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','19383',NULL,'WEST CHESTER','PA','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(62,53,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','669016102',NULL,'EMPORIA','KS','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(63,54,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','20704',NULL,'BELTSVILLE','MD','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(64,55,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r2p/YbwFVn8ecwkl61Ruv2enp77i2syVzEsMlRDnEio=','33647',NULL,'TAMPA','FL','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(65,56,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SJBba/YJoTOv9lviP+zLeq4OG94BF+RqH2Rn4zp35gs=','20704',NULL,'MILLINGTON','MD','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(66,57,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','c+wTR0igJhHWsb6+S5LFigC0dUlbghDKKozLdE5EDmk=','916056557',NULL,'NORTH HOLLYWOOD','CA','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(67,58,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r3ojofT4IeiYzx4+5z4YBvb+MCVK0Utq4OgJ2gkvdOg=','09182',NULL,'APO','AE','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(68,59,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tthBcFLx5KwVgTvkVAyoNI47IVZONrRf7ugUe6JtUTo=','11005',NULL,'FLORAL PARK','NY','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(69,60,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','jb4ADCnMQxJH/rf1qEdp7N+wDlfmmWfjf50osDW/m5E=','22306',NULL,'ALEXANDRIA','VA','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(70,61,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Zc43+kxuy7Da9stGjSuxTSuL8peKbQJyKqPvQhX3Caw=','26214',NULL,'BALTIMORE','MO','US',0,'2014-04-07 16:11:01','2014-04-07 16:11:01');
/*!40000 ALTER TABLE `cj_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_affiliate`
--

DROP TABLE IF EXISTS `cj_affiliate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_affiliate` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street_address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street_address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_affiliate`
--

LOCK TABLES `cj_affiliate` WRITE;
/*!40000 ALTER TABLE `cj_affiliate` DISABLE KEYS */;
INSERT INTO `cj_affiliate` VALUES (1,'Test','777-77-77','777-77-33','Garshina, 9',NULL,'Kharkov','Ukraine','61053','2014-04-07 16:10:54','2014-04-07 16:10:54'),(2,'Berry','777-77-77','777-77-33','Broadway, 560, ap. 204',NULL,'NYC','NY',NULL,'2014-04-07 16:10:54','2014-04-07 16:10:54');
/*!40000 ALTER TABLE `cj_affiliate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_applicant_incentives`
--

DROP TABLE IF EXISTS `cj_applicant_incentives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_applicant_incentives` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `cj_incentive_id` bigint(20) NOT NULL,
  `cj_tradeline_id` bigint(20) NOT NULL,
  `status` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_61F54ABB1846CDE5` (`cj_applicant_id`),
  KEY `IDX_61F54ABB7E2A1DEB` (`cj_incentive_id`),
  CONSTRAINT `FK_61F54ABB7E2A1DEB` FOREIGN KEY (`cj_incentive_id`) REFERENCES `cj_group_incentives` (`id`),
  CONSTRAINT `FK_61F54ABB1846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_applicant_incentives`
--

LOCK TABLES `cj_applicant_incentives` WRITE;
/*!40000 ALTER TABLE `cj_applicant_incentives` DISABLE KEYS */;
/*!40000 ALTER TABLE `cj_applicant_incentives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_applicant_pidkiq`
--

DROP TABLE IF EXISTS `cj_applicant_pidkiq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_applicant_pidkiq` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `questions` longtext COLLATE utf8_unicode_ci,
  `try_num` bigint(20) NOT NULL DEFAULT '0',
  `session_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `check_summ` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_536F59E31846CDE5` (`cj_applicant_id`),
  CONSTRAINT `FK_536F59E31846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_applicant_pidkiq`
--

LOCK TABLES `cj_applicant_pidkiq` WRITE;
/*!40000 ALTER TABLE `cj_applicant_pidkiq` DISABLE KEYS */;
/*!40000 ALTER TABLE `cj_applicant_pidkiq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_applicant_report`
--

DROP TABLE IF EXISTS `cj_applicant_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_applicant_report` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `raw_data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `type` enum('d2c','prequal') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ReportType)',
  PRIMARY KEY (`id`),
  KEY `IDX_DA7942E81846CDE5` (`cj_applicant_id`),
  CONSTRAINT `FK_DA7942E81846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_applicant_report`
--

LOCK TABLES `cj_applicant_report` WRITE;
/*!40000 ALTER TABLE `cj_applicant_report` DISABLE KEYS */;
INSERT INTO `cj_applicant_report` VALUES (1,21,'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','2014-03-06 16:10:55','d2c'),(2,17,'sqAZVWSvLaAMsj8VFSwuv9rjQ190TefjSX+vR9H7KmeHl+UdLvBEjBpLg/uvrvHclFECt1qOO196gFGociyi8ot0hbxKg4PWiQ5zkxh36iAw0xOzpdZ/GIoitOuUye2t9sqdRe0pTdOtafELUqzAsqPNWFt6oN1b4RlE7zFUQ9S2Bt1TflZRA7IeIM/oPmUyKjCICQ2H096FRIqUN+YOr9kTYBuFLPG77y41tDroumH5L7mpQQnFVgy3SuVAxTWGrDPsWZS+Jq2ZeUFGKM+XoNoFjxZqD5NBhWAkQBQ7IPdK3sii/DatQCJBx4RYJwXnnnpFX+yL8+atBZc7MZdL0YnwfpaO50UeuVuo/xyjNs/8z51ucMNTM7QycJsa6t1lzcmenAvKFKwAhvNuJMSDHMTeNrOPg6QXcc4jQ0l2kSxBuOqR5IMgHPfxRgAdEqQ21GUedkXc2BigqEqUhBn7MSV3u9Z0A1RQnQMgPlrR+pvgQKZoAXzREj8YfJHzlS/CzZm3euWbGrVba2Pw39zsOwv+OfAhJI6KLFDRXxCLHtf2kP+Vq6dERqskzGgFjivPSKOGDQMBqvDQUrbIa3sEVOAA9Q9Vwc8298w2Xz54Jl+DrJFXVPxJudJAseIajLcLB6xz3Rv5Fwy5VLjksSYhHb10KohzPeO7dBNIHoXNQkyUo3ZqA5MajBdUwYpll3/q+4pI+TEK3BNCdR55sOp8u/so/HDzjtL1i0yYEIZ7+GXgZM3yTHoS0fjDHWgIIvGAC21KKQGgCyJ9k7OGbCmaKac4gXJ+G/pCLaMEAdyPZ1KxvEujR86bP9TgLT/GoRzJeNfeN/eKqbad5ROCOQKF3/GUIps+7wY5Dsy1X1CKwgPaNls+yCW1ooZC4ZhSCeMN+VBGEaSdk+fSlMdjJKm7tcmFZ7Bxu5hNLctKYahD9QcUanBORj1uvtJNGz3geCaAAtdhBodxciuQUQJEwyZFkNxnSnv5TR7Fq2uqqGfH+k3t1SrM4b+OYh+E17o3Ubdoz40FPKNK9/LAi3Y0v6Sz9gmbtcOhXZMWr+/HSQ3HIbpaSGWZ1Ci/0R0jxrYQwSaPapcpKiWUPaqT3QX3/v3bqPVXrJqL/O6LcUOmQK7h2a+RE/kVHf+llJWiGQ94zLFWTRBhoAbEZfAZST+h7DDcOi2DRlYYKE7dW8OghNZ8mUcvXtkOYPVTRuJt2gP1CGB04XLGPlTB6j+CK54CIoNN22tsuUe+wRM9tQXvK4mjYxVkY48n4NJJO4P5tbf3WqCfpeSwgvYl2ItXNXCRnrqJIwHfQy6FeanN1Ct9f7anIZ3wfNRyNEaq572evBeWBRU+huLuYAPonalIT8zlK7WBf8IbzNKWbzrEgQGl7wz3fNQwFpsGqQBvDfDhycFKDckut6T+dDD/0/NdI4IsAvCze5agIWmsHYp2SIKzCxoxwB7dsxk93mBoP0hh92p0+F0IYu8jXk7a/BzHqXBesGqD1SoHC8sQeFdCy7neQVK6Tb1kiGr0qqz729v4cqffByQkaZUTykelUDJyUPf7uIW7P+zcemShPVO14VX/8EhajXb8EURqhRS4tGg7Jv8Swp+yX/+Jf9Lj2vSrdDDokl4g4i6FMkluKvra4lcl9iKkAjB72hErtMSjb3nQb83pOYBtvyNgGP1ba7kasA1GUeUhOBeIYfKma2YI33S8COsuH0km++3QQQaWnZvOGjp7/rBnG5/TShoDBal88DjW/YFExCX+mI7QTh6Ltv6KyQZXkvMyZVMNEisYJFchg2CZkhw8R4yUYYVWRy1wtx/SISX7bRLfjQckGRNymLHsR91rwIKcP/xycxFpwqUbsLpVfBLqJmQ+rKF44ozKaFgY4acAoLWGqIc9lk17rfU7EDUBiHwoAS+EDkb1rfaLHF+xcdT6LxTX7TWhhCb3MvFD7ej375CmiV9LK8RVgjtcw/wHlwXdOhbzvjJOdrdkJmPnp8GN/DvDS8oBBogOyRpGu5hq2H+cv7wyddFYrzAq3YSztlkp/S+BW2KEKY9vPWQAja6EabRVQa2xM2as0KhLVAENF6q7dOR0mQJBxpugtya4fEyaIvTOki+PRn1hRZJBRriWPpwwR8E9RSG6gc/otyEjBxe0GhzaBi+V7XC2O1C/C7Zhspqju7wqvgecf2BH5ml1u8oyEfLlIsykd6Dxy1CRIQ8xhPTbmPLY5H0pGH9Z8zfo6SfxDSRD6gOzjzZHBTmIhVmkf4jzpPfjXjUw2vhZgEdyKXtNmD7BnCSfF8daknUH9gitz5vdJDfQvr5bIK3mI6lnPaa6N7KQxNSR9e0A21rbDeA9yUakiosuG2Xy/uwxC9waPIamG7pHih6Rj4yf+MkcraYbfW5JmECLrolEL9LGA0y+K3rrHcw8Ceq2a4nReD0P14nomn9S/VhBETmy7fBryb5ueFneXmCmu/JuznobGAqnwIUyIq5dZBdJqi/tI834NyDZJ7de8zRpXXLQojP6ayPQLipMxqQ2T+DMSFBtf7Ei2VrJVFLBQWEc7sxTUR4TsXRvkOvEBmnKpXAw/Dti8/JbHzhkf9mXdpVObeVeU+g9QYE5UHHDoG6PHpYTyR0B+w5Kq49GxltK+9MFlF2TFpPhwrIxCklV6xsK2aU2xSDHMQQxajiaz9oxU2Zj2QpnKXWGXw6ZWFpLLaysuoI2cNSV6p1oAYRjCyVfh3X8z6yJctyulpchQ01IDHow9aPdVl7DALPtblXwZVYQvDz0wMr9vjZBLFUP5d+P3GfyxVQhFNKh14feKinyyEvaOwsj1py7Zx5I0sYuWoDRULkjdbR2W9+aSsUSeYSOGtGcYFfLbmXUhajIeS1m/JEfFQigfoQ+KD1GnRlQhRefJgce7TYa7XO+z0fJNfpjUbWRhSRSGdQ9OITwqp3p8Ssde3S8iWZVkWsuuPTRBF8c/fX7CZkEvJAvMLqhpr6tw4l8sQps+m57CRBOcemwLvFWYsy2MHtgvEkGN1jrbFzNyOlEoTLbDpqpGAW8wOkZpeAwaF5drpS7z1lZNItLMGB7WV7cke2TsE9eHiBpVoUAj127K/b7aPA9pACJQ06rVHIhLyf+vSF0G8UULmFHElQMOF+/lg7vYuuMXLIFzoAaLacxLrkbE6+DmBivQXxmN7tDMAWODHx0OwCWw7lX8Ue7QJHTohSyL8HLlPW3fHEhvi9gnM7bbPNuRn5OVuUYkVjLA4h2csCbuGoeV81UAd0Vr+vyjwjEdgCxoYBrR25nnN3hYHr2p0h4DFLDwcn++DcKtT7e9Vc40jLheH79iRYXG1yH38CtJ96nTvNyPg7/vbIzHHFWNRPj7JJg9M/hKuib3sXCu5rAXiv219o0uuBomS0Bbvb4E+GdqsbQTrmPkPX3KvHbYQsOecy0bszBTTznn1ggPQiZp5PDcez0X2FiNMetZdo7pDzBaeziMgbDwli/VVl1QlPxO+JkFONQ9tJPOETfcYWk3SvQOAcHgM0uO9in9gcXHBvZ9jW33HIpr7418DayECYcREHFmeez9vnEqxv/Nmn5CggaeQc222UrlSUatYckEeR7sRuLUsPX7JG0hUn8K4kYEWZDfEK3zTYAmEAAcbds23E9ZkosYSU88TlxLXmooBAy65cMsmXpidJYDMpOVV9VHkk51+GC0s8r4eXJCNrBLRQgnHMG4am39QIyS0T1x5Kr5JpX/DbcZfhn2Ryk24ugT2vszpIC+nlF3uiz1/wfXlN3/YUDkwyJYpzoJEPhWqn+lFsOEDNxrqPHyAIHUjOQtBRfNuj5fm0UF5f/Cy7UlCz4sbKtwEqylzNJz2tghvzySiCMiDhgrjYIwvCNhYnSz3a2fjeXg5NSTKaUHMYMHq8fLrfHC2P778QsmQRsS7TFUSW+X+bTl098vwMWmmRzpOFUVHmVca1cnBkkMDYkpJcka5+PzPH0UC6/hAVMo+YCyNfifi8GlTYzqnZmQWPleD1Fs2YJ97VSWhE6kU5Wk09wwrKzOu7NBF/EGjrfU+0dMmwpR49Wo5UEwYQIscHF8dAbB8lO/dVcQqyQkuH6LN2VNCoW6ZrizkHEFIp3+V41xil0kvdP4BoxS8aw6nWSICPh9vNfeOlAHl8YjIY1O5XTb6OH2W+T/c/3910DTBcjkse/p17aPnf9KXcJz7us4BgMraGy47KIRGGQJ/04HqXpkv5C3m0jgYJ6blhoHCIvsoUV0IIi0jRFZS4J4S1O11/QICRP/T96Gb8FAlaj/KSQL8BJsF50tUDu7F1R8v66KNnykMKat/1kEI5y0LASnSSu2rW6vnTxsshNPnK+HuToaTi2bPISOR8gplcyJ7lTAVJBMX9jxsmw4NO+hnP2xzdiAUNScTojxrsjHgHuNpGtEKpuspjehPKA0a+3iMF58DSIYZFjprV28S95/D2j1H+hfHxnsAO4t2+aGnzAdQZneFx2idRg6x+AWtD+KdyGpP8NlMD2UxTTxGiDCagjtmC/T6KW3QJrjt/P48pSNCriTn5GzoktSDhtZc/Dzx30lQA9VsioTLOyWDURrJ9vsH2LmoHE2d9dWWfuFzCuuoZUJvk8dbBdvC1Tpd8+n5GWPAJyo2dSaV/gsEKSJDCuncIZKkkk5QzjfyzMZIapUkjCvqwV5nE3M2V2SXuW31dlEYxytqxbgoCFc2o/1kAg+5eN6b3jzP8lchfqVa/6ztaGCjVg3khE3DX0qJRNxggFaVqpHAkaHD9o71x4MwQiie8vkGvjxwg2xM4UcNNP4oM0kZR7k12EMD6ibXUQvP/03lm0p3lbNoiHRtqUPI7HN1O7eyODfnNdyyzxsrEkyd3I1U13gLqLTIIKzJAFVMVkvUo2gBjVzaISzETbrA70D075m746OZgvpCR6A+4I1DoxbmACt4ujeE8BFCkFAjS5Vyj0YQiQIQ+HJzPUiyMzPEO6oLe5cFy41WbvWy0I3m2xGl0evBhiCIyrBdzdr49PayQULK+xgJoUgKgWkKxi5qnytzoWnX2CRHNamF/llqGif8vi3x2PJ5Z/i9teRRs80zCmwlbPxpuSlXURal62qj8Dlxq5rua5JLGzT6UUIoLHxnzwAdVLD4HZFehS7zL5dtjiDeftWZWA/cF4GBp43eiUA59HKBFIFEyGFNPSQoMdaUm3fCI1X7vpuLeOdKLtZD353julh6m1oMyPX0CV4o59Qzj98STBtKa578xhtbDxkBvT3puUsoFiC7mb0n96JUtIn8bFjMkAkfDDiENlO/03z/gTOBc6s15WztRi6eIYm5Dz6DLWe+bIBiHvLNPR1A/0mmH2IeVi2FCNRJDv4epbQjd66yuX37xRZlxGUNJviXrtRVhkP4MvNCNFtyefHCh8BgFEmhzo/GfBDbcru5QsIhNM0+8wbhWSQK/jSG03vWXzfMgvBKLOCjbfg0I/ok94E56Pt6d1Zlpf+fj2MA34fVIR8stjUGKSkE8KZXEViwnXZsOKqiv4Edmd0LDWtwpjhPoYeWtskqnhHRVo99vTig5bAsKgfRZ31Bv9gAJeSM2/krjYjTG+TKPtJ12t6OlKogbtkSR3Rqj9+V+9sccTQNTBxThjecN+0grj/Rq/crF1jpWmzSXQRxcRbMohz3Y5eCpaeTDCOjP5fgpYO5rxs/uU1YxFsG4NCV09Jy84zbB0aGwJCCU41V+y4cWPDy6FEIxs9A96pHtp5CdbTdEDBrQFjxbNtEYLhRexKz3zbDWCTMYOv8bjw9AlAbG65uubqWZJULQXjpKID0l/tvfnGv+GUNMNGBam4F3cWl/Eu0MtDWLOJAyuu/WZudZWkrZothjuv4lH3GI84iW1NB2QS7Mr50nKPArJ3IduwszIPjCjWHzB6PzavDBghh+7yfai/IWAcAJYmd3OrN7BUIkoAmxvUKowoEsXmZ+GNCukzgPZeNDojbzPIsIDRnNGzjTzAjwCMJa1byHbeYJMYKqwe4Rt3WwxBs4mK8Ed2ArV4KyceE1pkAkcZDuZejnK5jGGntPBJxknbVYnrVhBAUy20qGevgDDPq84l/cCxkcBROlLs124ViSfxXgJGqL0hwrSY5mMQmuC+FZoOVf7pma8zmVCoGU1OyzPIW0wWptxD112yfY3347aAtrLcg4zZRvyScKZxDPcKjwpgSVfwGjmwG4gk3v+ZOlxFcpPapImzZ+kBOGoAqt139CdbVbUNGrALLR5Dc6R/4i51ASwQE2XK2zlfYUmRiilxI5zx/p2viM3+GQREKHduqrXWrzxLIrMtSq/2bSVXVVwnK1lw8RSuciSiHrpHHJECRgkzdzuf5ubpGYYcdzWCQblnEaJFiXIGQLs09lmrTPpBOc9BL91Vr0PKtM4GHmeda+jBc9KEC6NANIeu7IqwTyXdjmyRYlaAGNDNqqEMGNCh1JDauxtA8ZuZCA3VzLlU2KBgKcIW7T7/3VDAmYKf7fKibBnmoV6ckD00zKFJ8aCRt//+xmOf2ZCJSZ0dFk8QZdHkmp12uAAA4P+Qo/yJZH7R/bnIxGueOevC8535dKuHt4gdT5j88hHepvTrFcRarGpBgQgu7oXLcTVLrSDGTxapBcfj5MqBalBHvPmKQpOh18tQSZuy9n5Hz5vaQYnBkWtXoy39fEs1gV3WKYK+V4FkJ0l5tqE4fqg+jTMzNY06ee9ByV/mDjb9iCsP93i8dVI6tn3bWJ1FteeLjeYeq7sP+kwyrxKBr8yHDSFhHM4amKn8HNr7O3xfeVqf+84gKL3lqz9Jo9nhqOYl9PXBuOWZWUEKtyTiCqJxDsCFqaD6bAPtEilJ8G6j7c1H+qR/l8G4eZKR24kB6HUBgM7RjFr12IwKkztDn9Y3YS4rtoalhMxAm48NQBkf3hFOaaU6+F0POH9O3UyDDox9QTYB5uXyMoKd1IoeELgQdybJfvvc7MKD2Xil8R+MNM9u8HIJ4XvHUrQSu03IAYIn6e5LqQhIoRd5wH2dqT0m4uXrW+j1E9PSJolYsw7fIFRAc+yPNAnsiSaLPh8q+yjEu7EtYd4TZok6k6QbsfJivyn7NT5J754TnAlEwYCYhCKEqgQTBI7DzI+ZhtSyoKYfmXRMi0VdaQ6BxEi5kUQtGOzSX0wm3ahVstodHA8CRzgkF8zHQ3AWqv/ntFlJYt7csUZtzxfCHPx9/7/GlYQppCegXDDhZNcd06mZ+WviY+WOgAQdbnji4Se5fRlmH5n+qDMuoYcL80CuwbbxfbzQeSFD4NwMDb2UzF9+jLhyLIlt/DxRCmDgLns1RG3GRbp4Iwh85VnJcTIno79Z8+lvVMBE2I0z80yOQMYJuzVdnrHgyxthN3H6Rxl95iPfeJ8h48GUwpH+XHLxd4RED+MpmCanxdXuMxT8lA14NDwMYrCfnEPne9WUOz+bfc7XCX4s3IvukWY73FaBgEnPCx0tZBMysD9eBBPl8So+sV5FlqYUMS6Y3M471VvriItDkz9tMuKPDxRMp5sZ1zrf68oIjBEn225jsonMteka3oCuydcF9tY1W16SsrYalgmAUIs4Y/6kzARp3fSaCUru2BzWxkVWixKpjrJFyRLBocqwHQ38xfhzBgv6WT8bvOKKWtSNpQ/pUzeCwQFCUgp03dzNz3MPiR74Cot6NPrPlqweICA3dxH6kvZIA7A1W5fuSrGghurCd/oYsCw8BwgRYdT4iGzoxunpx7jx03+6kJPdCBJQHAgdM8UfsQoakDoCrVI43M9xoA6sY3jTdAZUomASxhfNCybk7XWbPcFB3hfKIDpIx7TN0WPq7wGgCdIIRSGqJd7YJOvyFQnJlIDnj6sqkKOh7DtZQubnIydhQRsODO7RMfVex2UqI5Um9UVFdVZ3IlyCBBTiMZOdkggs1dEzu7tJU4keNYWV75rjP3jY0xvEpjqxD1hlszofufHlswqWaTCOlkc/ugRqutlW132o76a1yARHwWCy85BUGVJktAIJecYYJp8bgsNd/X9Vee8IH4t2+fUm3jF+jaoom8d1DfWdHo6DbBWSedDhUs81myedwcpjvff1qWb1CdgZ4LKP27cJW2qtGAw9S7ws9rqIzf7NUofSGbkdVEVA/BWoTMpLkICpif0suPNn0faEdiQwVIac/VVsj1JwkhNMCCLzsmgoSTdjuxWwY3IcH8oAQC6qBY1mXYlWwPMl9LDBbbF7cfIu3++KM3KxM57DeQBGYS2b22sCayI/l+Kvat58S+5W2hRKnxVuqLMw62SIlOiLdIPHK2MmEiCJLd6O830jBypBEnlxLyTR6goPBTeTcf3biI1BQFKXNwmEYOpRWkskJj2gAQE9iGXXzuaSGEacTJ2WZ10i56rWLSf7mniSZ2TWetgGW36ecxRsLgjAd9gOCtnEIVTGPHS1Tv1HHGvtZB4cuEghx9TeD6RqmkWRhyEl733XL7sSYRp02bfu++8bMnugZOrMOAEVpm8GqFCyAAPmGfq4EwYntzaWIPmW/DCZDNfgY/K/CA6AiLQe7bQKh/qXfCK8BrwOLkkJ+9p/GemNU5EjmTJYWR17uv0tvFrI7bC2TgdHzCXFJJDfeckt8Xb+la0qW+CwPzLNrNcIC8+sjGVEeU1k+Ub+UtPyyAKrJdIB7eJtuqLP2E5PD5wgP6E0L/RuGueruVb0Ztqscq9flRAfCqpRX2GdtogX9G4WWkXgNVZvWLuXE/ZS0tZh5kedA4IxvsIPPtukFT/ypJAGzSHHG2MC8njPveh2RfpMItmvx5o9eHBaFIwo8/4TEt7G/XfwlJO+tOSn0OJIyjVhg/lPyL5Bxhtmaddhx0prloMJisos5fVlpw1N+T9u5D4Xln8pK6f1fFBmWHZgmJfOhy4DDu1RGJOUUPKncneqaqZcJSaWBcedOvoznLb5OSwgaJX+7uhVEsxHZ1k6Q/YwyO9+u314GRoacPdje6AWJzlzMu+H3EGO+Dy1YO/FEM9xCfPiH4x5CleswB7EIOnEu5aYDVtDuWYZqh/1DWpiQFvJGNXNQ/bV1aqwCqdV4805kEvfaNL5mH92LvB2VWrFyCJvYgoxkYPNWA/Iwxta0tkr1VH+uQib/V/SqGr9Yd8g/mQA7L5nNWrYuAWY0qbQiqGDgLKD803aN8RjGYrxIinz1BJdBqVaf2lfX0Z5ZAyDI8/TWbGYVfhE3ophuczzWAszEYdxPvXGN6S/72oKlxDDncosDtk2b1av8cKUMLKTmsEsJOIBbnAuYafXaV4njCRS8qGRysVj9UI0EpyZU0wZxPIZgt82xoXiAusgHYYd+ZVvgenJC7KtkdkY/Cj1IGc15Oi+XwnKCDy1WYz5o79/ErugEE2tu4+1fNRG/wcqTOQ/E/Zcg/fmgrD9qqWYbVVVztxJ5o0DG/DzrHQ0eJQLNCa7gZ47vkk1C5YvXHqhbDYmF7lAYzvudWPx2RB0jRW+/yME4l5nTZO41yWHMLLL50FcMRffUI+YznWnVOKb8E3iXZiRVGIyEQ/9Lv8AhRy/1OL25VUINKyKZ++hS1x6eIWQC5CPsDCTIZM94nTHemaZcMz48037xtdrcdpYtH1XrbI2kXoh0hoLG7CKaeZufXLCFqXdlUUKNrNphsRuEto2+le5GJDpUwJFIgQ58Og3atNJ2Gluz+4WnRwqxljvJG4xvtkBgqmWmrQMzWbAyrmd/qHt+A/Rv68An0Aa671yeFzHmP3peDauDAQGn5AbEBGgmQHpSaelP+wHfAIZEbb56x1BQs8ya6zgjizO+j7BnVHuKoscNEXTpBWwhnQL5HaogVTJErwVzzC1OPn8/sclrRDUd2dK0qk/blUwJqSUYNlmQRA7QdvuA67b8JojeGgCj0jQAydAsrS6AAKYJsq87TgDIBN6uhWnJ4ybyFuFt292Oq2vuL8tMcmMXkmdAKEXgrhXBH28s917K+VNFHaBk/CIPw8ffPRPm27Jhv2V32jLz2GkxVI+QG4hacYfCcZWMIOPLLPQZaR/QSnNg0sQN9eP6V2/s9hebOnokyPwLNj8RiXDJmLxDP6FxVicZNH5r1CZoh2iQzcSq4SAI9M8tGOtp1w8nuwpeIAJ4GR5yuQ92z7VFicjU0EWoQ7eVReEpByQoaDR3QA9S8Ezg6OgRizediJFbvvX6bDpRP3slIawCk3X3RvoXZQUMvAX7RrFuW7n9455wtNdWMKWuNVZYFFrZQxE3BcrcdDT/xSyHRopEmfJm0uxk/FbYUs29EkXKiUEJ8TBeT22p0fsap6jP7OsNu+cqrL0fUs29S1RE8HjWDLjdFZWNbMzLs7N+k2UrW+KVinq4a/o63h69JBIRsxD/255v9Np9pVAniup9xJN9rSeK9UUnyie8RKiokOr4erGxBrAwaIQWTTYpMidrS/1sffOo6CRtoUp44WnFnyqgrRq/Gxl1rGP4IYJp6vbA5FgOH8ItvwXgSEPScWoUIE0fpyIDH6NC+C/QrKjVEQNfK8uqSNIA==','2014-04-02 16:10:55','prequal'),(3,18,'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','2014-04-02 16:10:55','prequal'),(4,19,'tWeBSwoRKoPKAcmsuInbvVdHuhXWexGqUHA4+V8i4DfPwxCwpMv5wD6oQ1WNnarRYJ+jyYbvrJg0bnAbU5ufAIXDYyE8wycHZoy1apLOg4Ec6mcsXVurUKEyy/qIQnN2fXdPEj2AEHex1ASDVphAcLxs+R6tiorZ/m53XWyAesRELhIDV3K1BOU8lHZT1HZMIA8a6sAjSUxwpeBr+0mZRjRW2N+3SbrORKiuihhh04jopMQHRqBEKp2sfo4WkU8fT7afysHTRqOmxHaN/2a23ymieE2kO7ayvHlNkKmL4pkCSPv5Kl6OyicGlWJ7M6u2Fvrky9xl9W1JitUuPvKJyfd7KSqy0kfM59K0hX+Gr2dO/bIxYcSkXVvQWObUyKAt3C61kMeW0ymTrke2p5V1QOwq3VijOnHMU5zcSM8PQPeX+wB1ebqEvwVTG0vwZqqzy4hk3u/gfgnKrzZDsJNIZGTYEXm0+WrYAIP6FCpz5JX5hIJAwaeEbkV++3teC1HmPj/SJqGm9y3z4q1maxNE5BYMLsATE2JKOx57nlFxP8bsf5jqv83s4wfvyDTp15TicZyGbS5/ZOrPyzRpkGYn96F04wrzYtmZzIP2MUh0NOyJLA6m1m+Dl4nQUeRqEEJjLjUqBFTwolewnR+TfqRKpfWUPYZI/VD3bU5jpGfjCDoePBWWiq+VDtJYKaigBy41n4YP9sFzVuBdDZ1LHMSMwBgpiEa48H6SJd+6ohja5w1VNfvxKQ2Lv57iTsN/HdjSBzH9hcKzJJuEW8W07YjwJPb586XqS7QR8JfOWw4hF42g8bW8XlZy2j4VjLm2RGez8WzfBNqRqHgjyhVLIrVsLN8HwGoctBEW6Z8uj5xLQKB7WJflYzI+EnXmmwAHAUEQbo+qaCjHlrwxkYvbT3c35F/Oiu4T3zLOts7ImI5vuC6W+4WvSEpGYnjNnys3SArL7Uf+ehSUnN9w7YwPvFibL0iwpkB8nh+PdHfuDPQnE/gRS2+f9g9MeZZhR/9ecToCx8g0LSbMhiNNp8cJ7EX3uQDERhAMelHXgdysSwv0dH3FB7swSOxdQvBqjYeb1gBd7gpO7vEcTzG7E+/NyGF+Z6uVnZl3Q2VKAaZKFrmRWQhuL8JlujtUiazyWF5EH95mJL/PuYlZY3d6PFBlYSBL1e2MFO2mObUDwqiZBmnpSXknlq6EA6vnUckmPaCXxnRyhMWecjddHAED6COVtH+4jl+WiU6W8mBcvry6iSb7+VKSzdN263StdN1nt0MGw/nyeaz5K6eFFcqz4reVkabP8O3rvyMAzGIjKpfq/E0lv5sXKAWipf95iJQeJMFLV5AHzNS0cHrbtqzicCJo+Hafe+k/0hyZoEWirjWqecHgxFIH1DARXtOfKyiOSFTzwHdsHjyzv5vCGaPdlbiEZDz6x50cXcWRyiSvhSu5PeUdGp0XXO5gn/kAzCCjS18pT5Z/wzy+jccYR6pBLujFv3NYenXUiVF0fVSKdPQNhHX9xbsZDBHQfU9ldxegviebwi3xSvmkNyQgTJt9hnQTDvuoaQOrg9ycvojd18+vJ7EKZLGrx2XkOys/b+u8h/UE+pBR+ILCOlI/Qgzp+n85q/gfNQ9cpxtpne6O++fz68o742Z5r+04tvQCQkc9Jz8KVrHNNwKPWKBvKJm1jWvsJ99SFgEjUzwXC+0DHDYbhmE2ngrhFOSNkWjL2OXLADkYTFbFhnK0LJSAYmMVOgBWwxZA1icrChkz+xAVUXGsKekEZtUOyGsI1OCFAbEnYUfphqwEg7O6obvwS0pb2+6x/fFySEfO7on4UInux424Kz4jJmdceduT7JXG1TQ1gh8jKGF7ceJryMBCONVdUiG4fU5meoQ4fKvfINPWSqei41Ixu2qb6S+QAEZ3It5dtKYobQ3JqSi3I6UEkdnxybcxcoGGmVM/dRvZMooc9Ro8YYA2255NS6UyI8t4m279xZ8qQScltB+UbX6NpoCryw0TZcnTSNH8+nw41ZJZnDpURa4QyWRZXU+UshYeWGoE3qD+WARP09xkhbLo5So3sszeFK2fMiqpFK4SK4Tc+2iQJK5U9pW/Y8rlsYlcGH7hzjx+dinckMcck1Fno6uz4p/uA0J1PYFMLGrelpA5DPiJ+VYR6NlqpM5zVWSlHOswWvvyGXXT5cH86S0SX//UHYmnBbjR4Xf68yh07EsOu3hkKpw9UZPpwBhkrX32/wO5p0x1OXhhRyde3LGoKlN2t8dQc/XDnsY9qab30i77+orZUwKJ3ee8Ye0mA4LB0W7vWBpFzryo0TELYNFZ6+uwzA2hvdxSDOqm8rBfwTuS3YYOdXEIXF2fMXzQLv5DnOXIh8vj6NWozW52VaWoEmHeBdek8lFq5iQm4Qdu7ruFlTHQ1BRILP7f3ffxAtxX0DHf5FsJtfLOjLuVYhh5i7NDmNOf06YvauBekSn9VN9+SHJZNMsFH4QCOQIp/xqDKXdlz3gtWjAH5K0hdf7E1OPBinfJFgQ/+772mUA8z58mDwzrkOGwUnBGhA8Lq4D3jwKrvX2EnHf91937rP0aiVurBd8/gfAPqkiFGFMpcPLAnRoaCjOX46xsvxNXKfESohcuURqs2LXbuXhjqYJskKY7TUOSiQEMdDCvY98wCAEoCu4bOPt8w6hDb/C9TOFajfzK1h2Yy69r7JVB/otEgkyslFn9BtjEw18sxiB9kQu4a1SGrhAjX5Jo7Y8R4j+NWmbQOv7ACtry1FkOOXA6SIST42azSb3ZwLY/LdSqEqXt4EZHKBm01/vXoYjNXsPx717+YYIZhggv2QHGsl6zJb+LmHfVkmDZGeOQ4EPEJ+Xn5zFztxb5+tT9Rdij/R92KF8Pz2/dCtaQfWpdIufv2d0g69zciCuQRbr5RL+4gWdl4rkgOyTVBO6wkUIH7GFh8HFQONtvaEiMUXFEqWsEAqCnxkaIW0V4tsj3Mav2szKGHOeKfdNBg1A3bU1grm9TsY7m4xdOPkTKaoc0FrtANlVyRll8G4ecxhlbJsW5+KkxVnUCUaLryPGkwdz2ASFaTmyw1o+imVe7rqbiKNXHA5A6sq2Nr0GXEL7PY0s5qppdyA3ps/bqeVPCHo11he96CIBneo7xw5lzeED/SxtAqj+0PN0a7VlvE42QOS4J55DWwO6/PHiCI6kXASYnH9djB0+xDlFvtZjf/Pfi8E6KvVw8Pjwlt6QPAUiCPyDweQgnQY0ecq9t+2GH4jYdG8r8OXuUv8TeddsFh4yMcSlXjbqeQn5QAwqAONT4TOcywz+UNIYEdgDEGICU2jC4DG9Md6/rTwVSHnlmjCmizTJQhPIbB27Wfp7rRrH6iUfN5XAWwaBT47E3U0J8xpLcWcUCODJzdUleTpsJQ3hFd+qeb1BGWp7RGEBcOreKHKNbES490nBoW6bnj3LA+5/H1XxVOCBhJXrZfeCH6vah7Ak0+9Q747B/wjOCFqgeAeHXlxy9n7mhYKA52AHl0kPChsiMxtCIwlIgTwiu0ixqELgeRH/Ab5acaEjaG27MrtJ0OrzbwdbS4avsrinI+l2IMgMjlIR7GKZQpzk3O0UnIY+XjWgFF2cZ4gA5rYanm9cncXcZn/UzEQzrYn1MDjsIdtQhx6D0C0ZufjXIs5kUyrIcGqYFvzb8ucwh1U7KL3WPvvhgTXtijPyLoRndFvWvSe4cKty2J9YU/6yAT9aCay/4IzEYCJo6z7TRGokIj7+bczmQqFQq7aBeasZbModDPdjN76vzaUOmfmm6NZ1VSkEUUJGkRnWLU4AmxDYUeiVLqMs+BIm8sL/rnqTGeLZnakrbbJRAjY/UjtqhTdCmWMZKdwM1Uq17vhUrpaLwEMNRoupOr+5t0oKhv/UzK5zZNLad8gyzV3WModsFFztngA6RFvHIVnC4mulLRqLraeHSBlwSJzV4dInbz8O9LFX1N7iQ4MN0Hq5qc8cqFE5B7gSo1NAj4OyNWOJCx4FHqs5E4WD4OVDgc9KNpPEdF2QmIirywLjWmDOHXrgolPtT8/gu8SfuaB+Jfp7VvDA56cO64IV1bUYffPPyxH+2zPOBwp2uzutt755aKdsbNmMJQ1jfxU5ZAuNFXeB0C5s1KT6cs2Harakl/tGKpn2Qvx7ub3vN6Ghulh1CtTha8XE+vEgRgTZNe8wkFhLpLCMmlB2huvmASfGR5fEjP7ogPzSNQP8MujXT50/SuMNZmTIZxTMsjo/rm4Z6dcloqoh6IKyusG+ONlwuJr27jiPCZSUbPBOdo98XgEE/6iBXw4gjxN9MbgnKuVpf8b/bWM4JSaFzG1NfRGhOpauqEAxe4Dw24WwF7E289h2UsOR6Nq7oU/M3i+aS9ClSPGIUTzjx95njhnzr7z3IMvZMqCaYYqeWHHnOh7/6RnL0fznT5W2u4y5ssS9egcdHtfpecy4DqGhOuxaerqh2R9xTJZsnenX9OK7DeLSEe6iCIxJBEB4Ej7G16yKUCMvDen267t0R05YT4aP7Dt2dfi1L5PXObw8v+Kr1+6K1Hw9/IT9yrY6A1XyJhgywQVH8TF0cXFABsoaXKlWboOS8AjSnSBhcjnxCQS0mbBVhFFRwIiVz/FIkQrfe6/ctxkaUL/DgbBmUNVAmhafaeRFVD9Q2R+TxOlozTFpv2D+BBz8HLX/KupoGui6ILcU1hrWhCYzZphuuNYRKldBMrrZZRTfu2sqt5ZwvY3b8KSlKvJskF7oTtvU+isBBUrZfyZHMWdSA8il+q76vTeGha6jfDRh0oEwLXjxUfiBlAcagqveWAYzu3UUuL3PV6cQe80ezdyH2EmTKj2FGrVSIe+YqPekApDhZl93jPeMCdf3EoURRDlQjwuOY91hpIFy1hBlF/NMDb2qFiIZdYVt3W2aAD0kaU4SbrxP2o2lb/0VmJ6dGVLfrgEoPQR+qBb5g58Mg5pqU/bscCgbhqJM50sbIrIymaUgtgrnomABJm2yyolMcXx0pfnTJBlv6f+Kmg9dVc5F+tHfaSv3OS2lhnSeHnot7SwBw8CZ4Yp5Uo7nMpj/tRobBbwoD6tW5GMZGoGKyx/Qwof3EUy+tDAtI4hGEKZb956y+CgVg6WQEfWyA5IZWuB6Ak7lCuJEI5peQnrSRlaqA0oZK8r9VdJx477eVL3CqCbHJz54zMVQCxZHaE/MSB9ruAjglFqNC3xnN4527wSzhBpT4CPqktA40j4C6qFvPgLb/Y+9Yq8hUgyzaJ8Akl5rRhGl+/zNe4pyues49qLH2AF4bxDeIPYxEcffW/TIusB0c87yiqKDWECJiY/OoO+j0oeRCaeKUACAYGkherHVaji5BI+1Ujbaep+68/6MKRwyVVve9TfemhcFuNkWvNJ/pP3EetN880Wj02HRdrtR4Rt0HQp86N2G11Fr4CzL2G40qGaXiOriqc8cObi83sFlvEzMPWMI2pe1XCEWR9fNka0lCyX4HROnpsTj1YcgQu6xf2L1wCxQSIUA6Mkn3kI/b/Vc3iWAL4qhDZzqUTY5V47ew9YBNDY4uJK+0g+x3WLqtjKJxsVWWGe2js2LWVb0abbEn49z1B2pD9D8n9YJ8Bu1qcOFgs6NByhWtT+xKIz0ttnfdilsFlfNeYiaesXAd2mDO7fFfJX+tCdavSMZXuQWnrt3FFs5Eg21dcmg+08fZYIsNb3eUxZMgUIrKArRIYSvgOmmzj8dguawWDIuKn9pLHTFahHDhJBpkD1RHMQeDpQq+hiHxb0bzJE/m/liHMYmz6SGMHvw+lNy4UMb25OdxbeLIXgbdQZCUVpwK3MLWDPBO5vuWRs7x7JxZZscfealDIQKke34RINIMjEFYTXQWt1YZnJyO4mJFx6J930sOefijc1RrLtQqBFPJT4NP5VIOgusl+56Ms+apWmC7F6WyK0ahIvrFKZ+7Ry3sqrs6Gtiil/hBDGUo7vOx7U6Vx+LdubKlJeMgjgSWlbfiDLEWWjZ9CTmWumIJCCu6sDsJa7CiLZ3M3LbHQR5EGLAeKlwMMwl27JEF0Srw/MwrgvI14Ha7pThenc2xtq2ADmaRKB5bLzO5kL2g4y0TohodTYZJrd0aHjI3iOsFcXZ3xAZQ2sgyAGfZPRCc0wHMQeMlVW/JXmuQX2/t3mPBzIxAZeXERtmS4Ltuav0vKYOPLmdfKvGxtbeUe8YRD8xtcYoHRYtam6vF7GVEYzuVi/uXP4HtZBlXlIUazAIjXDq1A7qW31BNRds9SEuEf09hI+clnRM4g4ja0SdoTflk3NxysokMQ8cr5eCwNyERxfSsKtgOpm1RQvik1OJOcciixCbt0pYgZYwc4un7L00symfTN1v/d0lM+LVqDyqsQ5BR9EWae7+Dx1uPMoqkjWCVK0sl7pbXBJrSrvFTwwEcY6rjDaut1fSp/mGuRN1QiJiO2W1691CNZBxGPFzorkqVr4kI8Y4xZRYgwbGNzqhoreiiq5BUBra14OdBRCWOyKy1ndMl0jAAFvi4O3Fi2wOV6fleQJohK69pt+8bCitZNtpShkCnL0GwPzKG08kZ0XSTknCicigKqewhOkVeaPWqUPNpqvCIXWMBAOWbbyMaz1T5L8nU1UuZZzVxCVnpyDUXpS2FdPf5qo4LNF2LwBIOKwqfbsx5DO4LBJY9WYbfn+n6vz+2xCIWy74UeKCCGflwgNocn0vYEA3lkRyjH2Z/2tXwUzFCV7dFUNxd+LPapiVgdUtqd0U6Mi+RG2VNhqoMPcsGl2P5YUj4q79lwfMNQnc9WVEo4vYxz13P2vy+GcApzKCz5SK0F3ZuHXXLjfHfMTWheBMXnBzKD+nZ8+Y0Nl/j7Vckj5LIrKJMmk63Hm0steZZK+ZtVYD/OX4ah58uyG7EKAlBQ47KUhIohr5syJqMZZ8d1ZWF0lMqYWauRNeBc1Jz97effwb5zUPr0qXbS4qS8LgQyvkEwWgDITJxWe6Ugg7mTL4g261rLB13B/BDGgrLRlYr4F3FT8nwzN0KG2AlOSALzS+DZJsEiZanGO+txcgDutGQmIiN+JFHtt2QaLv6PHcONHX0IgI6Y+MDZeRUBqj1OxBjiFbL0czDNqLmqi7A2iIhwg+Pu/+loXhkSSg7T5hjKxik/XKdMa17DB5LtVTkgJCYQ9TYREf38/M/+rNOEJuUiDHkPuONA5QEVwhOUhVsFs2AGfTWGF4lbB5Mbh7o/tmIRjoMeZyN55OMhJLrY6yCr5Uele8HAkUBSsx3HMSAJZUzI7kbyF0O0xJm1QNfwe5UDWqOizJdqNs0K9S0qgL2bfhLMaBeCEjG7GzhJ900GV7GWGpnrneL9XriPgkXTy48RnN+7krU5t5ctr54mptv4qu6bX/F+XwspQCyO16Xq1PUVqhLXcGqD7XbzC3onrSo3s8sS1U4bK4dz5ROAC5HmDDGEPxQHW1VJuqqv8H7YdE6pgikZd6zpKPIBazNnuRktKSCsMGb0xhccZxc3fdhWiJHi9WozWW5NVkSPB5gmqYgMq/gv9g4bzW0NZJHxohgvuv5tUB7JZDnMJg7L8jJGidR+9HyI6I4neVfGK9iSRHIlknLOnWK7fGIr4L2vl/XQqKaOIvFSnoCi+SM20oLYwqUQ1X6gsJ2YIiCBRxMBFZAtX6wcCiBOHMN70ZdocNnTogtikgt9B32+sXk8yC53bkdxTWrqveUIM61tG2Ss64WsEQfAiSx93aA8Amk/jayZthii0OfsCAkvbI+qVgN6Z4L0vnqD7IgDWZMow2d947TfruKMhgv37jOohprnRSMd0kP87JhA75NoKUk4xx86Ve/MWZ2F/ZwVIxSi8zBNl753aSvBh9+gg/9Idiicf8gJ6/Bz3npFZqDl1SBacE/njxgrcyXkteoT5F/rjlNifPRR5t05+kv8mV8PsNLytr2/zhaD9CYobA6fQGmDmxk4cWA0Oit4ugLnGhgR1fuuQ+8j3W6YXt0VIg0aDUm0Dos7cWkuIVXR1toHE0ahHdsF+ZCnHmMHJXuU6FP1Xrd9hbMiB1GaamdlDgkZ3EEzGJ/dq4bxaQsZwxDVdihIOPIwS/iYM6nQrk38Ifb/Yai1YQprSIswUM1DjvUY/FITNlMVVTaBwjJ1JjmYb/CzJLPYIG8JC9K9+QvbunsOn7NGUxuPG0REzG3gi0m4Zy+O3ckD2bm2s8Nz6qn7u9IBusqEXPS57Bqny3VKqzaZFw+vhRmDMvA6yB9NPI9B1uw/TQ0XUnbUgoOuOwn0p7FJ5qX1YzMBMBTKUspTCsYSEIPZMzQsZr1yPToZ5ev5ary/1HGHQoQ0PqmVNpqWFVnhdpDxgm4PowKMVhlpoKJrCNRJRiBqpZWrg/CRbinymDmddMLBWCqLWuOvXJC2s0JC+tcWu/R4iplJuBEG42vCsCPfi5qkE0le0VYn8qy1dBtm0GK/6egMmD0iXMuxwQvMIaozztqLjZt77CKZjPApTt35lbd2tzgB/U2LoKZ5CHtw+X4hcexHxIltm3yndLY4fel9/TaLgDB6SkJ54QkxdUNFSRVaI/F4ysd80gh907jzPPRf4bU0ewrWBD09GMhh5zOOxJBPhossugGxLBBicE3BQvuRF+H1bNU7ismLMmhLlivMF0VNvCDzo7mAhB41/Lq7r4ilMUu7RlVUo0g2rk0oF0tLjQyZkMZIYXQwXiu+xlgKst9tR679rwY8NYebNcIpYg265uiBf6prZ2OI9r1RWPrffWRL6yCSwYxJCBOft2UMqof0y+IUYwpRNCPY2tadI4Ri14Jlu5uRTfsiWHH8ixwgmnvEdm1JjRsc0UrCpvQQjJdpNq9yKAuniYVtArM1Vzyo2CvTNXsjlmsHvxrTfzv5967knmxySG2HJreOVSjKzD63a3v1vib/EwcqmjEmwCy4IXc8djRL8pNnB8ouf8QHwQnq/IDTIHLFCS2FkhJzppcOSItZjmNOyHqUaA/6v9faVYwJZ4NlDPDbSTeqBEWL2BMBJvDDiaYScKaqXc1Pnt5hBA7wCXPSt/6bYAkwGXCRx7fzHgqxdsSdRwCHl0YY6Pob7gK/sRppMzRK/KM426vMlsQue+932FTlC8kS5w4B4w+2jcPHXXyqC4aT+8cKUNo+Qw0taxidf1EMWEfvFzsBXtRoMUGCF+lOdzjhLtRwEJocVujuxYwKq+KotmwWrI0JYlaHGFFffdS2xhI6jpZymeEJz+sosfT6+1OOfgmeVNSTDrynQypfnf8dHpvcnOi0gOThUS1e1gzAtecKw4kE6vhs6jqUo53F5Eqsc/607BM5v8ZALiW3jpWeJyPQ46paXPQmd1trLMGQ/YenZGHdvaIr3ZoacBs7NTqVE3/hB6y0kYUiFxUF7+W/SaB7Evky3JU3RjbXqDFqNlVB+6gD+6ZpAaZIoyStlta8dgE05J05tmHvRUnbWAqbhdFPc7IvCHRucyXW2mbDTm76oQuZ/DbMwi3HaVoAKdewpqA3wzXKjSVOkjNJJqjOG4SI+q0rO+V3L0mGxBy6FLGeWcLyfji9Oxxbjh+pw0D33NRyMvWIFIe5+3OAAkxA27UrIucorY+r3icDnTy0boSBQlc4fbCEusPm7wAUE6/1yCXSHuld4HIqucIDbZItAhAUae9oAgQYXnpX/d2xUzsthFB4JnTNbtWUf7XxR/P9lb89gA+1AIGCxSHRglghwVm69epbfXKtxeYVJMa7/nalw3nHJ7m6NcLcDRsTJrnSPPqSK1Bdd2JzXL7mMZm0eGr6H3pC960VtZEsOd0/olWwPZTrQGE3DYbKKwrWyExkFKY1eyZ/PzF++cC8tx50zQVuCP0yuUMGkkcnxnzYtOB49iUhsPun6aEuY8b9ftU6OGKntGqx+o48cGSJO7TuyQ4H+TOq/t/eT65g5riMBxr+UmSUyVegDfbV+L+/WByd5p1RFoRlCjJplnxoVd0XTF+MTFLaScxSYedY/I20eecAMcOXOhU6PtuusCee2SmWA4hjGPSza2aQIYsiiv0le0zz4zFwqOzDj5iKUbbyRRUklIRLooDVd2r+G2zlyAs62GM4rGxSGHz8fEBlSrYee+tQxSNm/G9Ep94kpLN4kT8pKxSlNSKoE2K++tECe7+vSPwFxsIydiaTk6OP7ynr1R+uM4hyqdnQqZp/5YaFibtmdJlJlVyJVbaqQoRc6DHFVwoR7z6KovukGIzaqaXyJqgmU8DfGSvfRUVgI8uweqfNwdIFnfliUqxxJohURNnG9wGTpHSpcHz4fhcmZMcXWFOUPQJnkeNQzyg/QBv3DwmiQK/9Wm7YgoXW0tiRb1WkMLsGEfrZQEmF4IzRl/A2GSllldfp0JwnQztYnLvVROxRDR/+UgRnyvdnFzPVoBVl3fp+lCAcUq2AZDgIYCYkLapdSlvRGBQt36FGQ+LbVqUUnnle0FH4alf1ldJ++nfW3oksA9tuV4aYM1RFTGlI7Ea099oDQXdYU/HAXnUu2Vse1FRsMw2uVZ0kDXGZvkp/QRmqWcahK9qS9CfL9WGEL8Xv/IKZ9sMZz8ty+I+WTOfboatCbyMnWOdMnnYD0GpO0mj4sCQz8zsZYHjTFsl03R3kGPtGDx3JXFNduYV7bYkNL1XL2/J4/EBgsgPSkQJWAlj22huo9y4rRmPr34qQMI674Cxgfau4Gy6Ui586N6BIGcITTD0rZLwH4bOlJmQSE+Xc4GD4X6RcRpT/wcrZh7XUZtIjZybCiX8d0rvaPbg3sOK2aZc5OWaJhfAjQaierYbc6LcMdrzehF5RjUs6WZeok5JjjZM5LDF+PuaRzyk3/VYG3lHlno2K9j+rKaxa4bcFGiMjhPLDeH24ajib6UKeseVpudch+gu4W7r/DvNJF21JQ+QLp/37pshR/bskm52NayQ09zIP0YBxz9eSK6lvGKVtuddbiU+Grb6CGx7OOQa2ISm6EA/W8iCA+5br+1eUJYROknws+ZQdt5kAshgQoG6/QwIq3owdn+87rfXZ80t+frlvQarwMMn/19CRGrnxDxm2A4UiYEcnI8rdAVOOVhZ4gw/Ikz4Fs2Lq99y/6z8Sg9KHtIQRlaW88EBL5+WZVRzgdd84V1n03pMtBnz8LrPEjyepYAamuNp1qx3FeO9KGanjmTwR15sQ7OAJQN4WLVb1LrWrU+CbtqcnnX1G7ZRHbIewLyNlKeCBuZxgFYKFmCUmVwa/E9WUEkW1p6sIteTKflVtLwtr9dy5lAAePHrzV0UsM7gFlgwNZaOTRf3h+QS5D/MXHvQixyRnvnnN5kcXhWZ1ibzQaooBQF89qq1QgdE2Ar9mXBXtexh14tK9MaFmRbsm48iNh8/1mn6I5DNV+Z4vkPOsUXSQx93rdu1PpY7uodu3OacnCWWHeG057CC9yuqz7gQVWq/NiHtc4Msoajr1r5ancrTpdn13Bad2j3bqyxjHdekdclU4r6bWBfSvFHOaOyQh7yuWP0/njg2uQ6j3e+Nl2h47e7cMYbCN/ELGOjbjBNjRh7Ku4mIVNZHqDmt0jCFrv7QzU+BNrXxeB+7/Orm3yhU8cpCEE8qrmy6+hNjn7p2zpo18RVX9T6u6/OlHmj09YST8NKmTyPXHDfKP81NRbm9FfELqtjKKgFlWof1y9+jn4hdGk0ydiLN46uYBB8hPvQ598JB/1Ty+LmPJUirkpKJ2FhsjV1VFmrx7PlmokDMjRJFHqaxJ+WKYf0W93pUWsSfJ2cqnRpIKSenmudo1bZw+UB5fqDgk6TIY6YCidOvuhwJk/I1vCv80OEJuqaI5jeOZ1e+TyHKkHVWU+fQCfjL+bWqUhKSAcmUT+l84S0qhOqNIrF5nIduT4Y/Sw0iAdnis5Y6Y081yAxyokeWT8sKK2kfbLlD+u9JWyQYrhIWuA/5QKQzXJABXi0D2tSMCuT/qr2M0aZA8lPSBo8r7HzG//4K8hSMJdHDz6JI1+hPn1X+XYz+jgMELo5FuBEdWJ0hkvknQRM1t728bjehLa6/jExsVyljS4zUgq5pOy7SWdLpfep+J61fMYkKttVtgWQMC5UR6F29NFhYNRLu687QMXWcR1HYnorMPQ6V/4uoD6/9Zl+RCUjA9C6eOFDy60B0dQmu1HCwLMPWXeXeZWpkLO+xkMcYSEr/k6loK+sCqoZGQzmIcTdSk1pdrGCVEInQ6TsgrhjWVvPlO+6I6vCGXC1oz7/WSD/TjkeRyNS8uENfuHE5I6+AcGeq9nS2DCv9cXpE8M5k7z80Mg/Kcqy2ZL9YGT8zwjy7iWT+wS8WDaE/YBS3KKGHnj/oiUOf5RWbn/h4js/XRnCT5Yw/CiHZV8Fe/cbkj3ly2xpw4HcGmIyi8rVnLIbYBJ4uL/fyEp+xnzGTd1pUc90avjFv5aR6JWKstWNMOpxPaMwJxccElq49SujJD4bIN/FixWQIO3Qc5eAwHblh7+Eru2n9WsuIxFlWb/iC8g902Ik0glKWyLXuRI5EeLXXJxMYZ0gqrJy3InscppFKN9WvZaZtAYGE/M+SNAbYHBPyXspB0omrwj8C197YfIKUXb5upZuaQnr1PXlC/1aC/rbJxRPGRgSbExF5RewFr6tnIGX/p3gbro67DdoTwFgbfwe9n38C5/3jtdId/QwSVSGfvsnBtckLhGbmJQCGBtvuiXFfcAinfe0EIaRhqgRMtJczkE+YosT5pG65uuXb//ClGIIwAei85GPc+tAkhtLqzZmpofHnskyaD0DYnefhIGAvrUv81P3TP/zDOjjyktuW2ZOyP+pmdUPuE5v075iqT9Trj4ucsU4Ed6Aqcr04ClyJIKDf+algzm9GLjCQIpdw4DAvUE7vPVQ6jPOjQqABYsuGwYfH3GVXR7k+I23HjO3Kdcjhf+vjxzKE4R/kZrJ+lwSBdHTDvxv79qYdQ6Gdq4tS5blxuz31ZU8fvsqM2RFVzEwoybWi5z4MX8SFCX83elMAfc5YOsBqLSACOkARICn7xuqGHKghGBSugDdGVVrntE7DGSxyphh3gaenoNnRLorzSGFAKV3k+9zmSvpczLP0dKTc43YqsRukljKZZCgGEIckGglX9KIRp6DeExarRoMcA7Y8yO9LJB9dV/VQUMKmquIqua5Tui6KoC3Ta71YZpgWM5ChchivWk5HsrE/0VqR9LJMBu+Z8gpYVStK6GnclQA6peKjWlPOLVnZH15Xa2Xs7ou1r15aJDsfXmyFkChDoBxKEXErhwhGrJIKv3ZBYFiBH4Z3rerPW6kaK4l2jZ5Jj9+a2BoQj0IjRB7an7fB1fNrkrkRA+xjOscyNJtuOTVE1ndGlD1byv+mJIZjO47K0Woz4l0FDrpJAL5ItwMK7SBgau5LDQCeXbRPX2PYpXrCIfSppw02mojLUhMwErpY95Hxypc1LQjWgURMQMiQMYzzGIt++PDLXadCNrPdaOSSz3R3v2FGtGJ4yoKqCWirximqap8IWfT4GGpeoo/0767HogEfYhHis/0oXoA/gKUctlOut6PNqsJ3IzVGqbL8zJkniCyD8b5ggpK27g9z4oAL6kLzBwtlqvA8V9XX/6/+BfA7yJv2OuHwaYVWQ33B9FncnIuAGnulveDIopSB6MM44LeoUODl35Wx2abwEOsB42HY42nhlmnE/IBXPTQ/ItO/qQIak5pBJ9cF3/IiU5yn23oH6xYU96toUTB6jks/at9lqxxF5S8IT4MLWuj/SL2jBFGRIrCL40iyb1nZR5roZGBe5iDxFD4MjaAlsFbXLCW0NQdWOjcM2TVKoW/acIiwomDZZWXbZDt+p9EGX7FNLU1YTYUhHLrMF70/JAjRmPhpfgY+h+QQPdOx9/BIJZ8WUAgWsW0MLA2HD+rCcfoXzl0qoHpKt4GKI86MpNkgMdWkJ4o1y1JzQkyFqx4GbwXcKg7ldO3Kv+4AQAamgNK8kORcJXTkLY7vrMCiPHB8qLF8lNxaW1SlKcArQJcNKMZ33jhrhkMGKaDaKX2V6LBb5FBMKlO/Xkjk6Vf6YvUOHA+ZFjbkEKmmM2t8np7aSemw0lQKHPxCAuHDww1A6Qjz7NOVgF2OlbCvwPAPOC/2gt5jQPrrjtnAnmo3Ie5wtrigl7pvGbZ0w5aAV7saFfw5FCVRkwZ9891BLVUM3+vPvvIgsZUnYCKyPvFT1bmxRioUcKRKA0itoB/bF9yBCwqR+ErkPUq8whDsWoLxzKRKTfuKdCUfsWf5GdRBynJRv1q7eccDJhpBPaECW9qRYCHFF7xUh5DV3cHJ0Kvlgo6rmW3NYa0D/cOI7xcjGcKzpZ3O0H/omdjzBZIULZUMXgRnPwqlKzOrBwWr4jf/ky0l91VzsAcVkNRO+5S7XcoyNtp872LtwMQ1/n3/7j4Ns6gDXNjS5ixGm2a8jQVjp+LZz18hdSwGjEPnVO3wyvt4G8ixoiUb8hPuhhj5ZxDHDq+3Y9ySJhW25XLEjinRwPHHzBzeVzbZZz6xhF3sPB1oEizPObwVPTzGlAFRVSB5jgPfxA3LwPs+y9CoAKrkjU4I5uY055eUcwrga2hC8yInePy0yoT73BBh9+dc1fWzojDLDY1IDKlVFoySyWbqRW23z5xOhfvun5SgA9EqkGZo7D1MgNZd7bXxHZURA1MbcIwVDA5Ugv/LP8ccgGbFXO4UPknueU5jfPs/cwOwEBwUjXSMnAO5hw0p5dtRKlE5Jc9YmpZ6QHUwKKToOn/8UFrXYt9iWCq+jgcHyhcZkU0UohKltRoXvSVo8gcD2yUDXDFVALjmoYC/Ewo9UZbOJd0tQFvqLY8feSCKzHM+l32wE6IVSDRKk9uxX3gcLJPX8ZzM7a5gACCt86tYIN4bKQ7c6agP4ABJmERP80Z8G82Xx6Wb2w25NtYnG+JHvkhyF5iznzenuEz9cY2xWXeM+Sy4UtY1MLztTfxdqhO51wA9x/G2ypx0pJx05UDIujtj3nE2wUu79ZR7DyPpxiaLOmAfLYpIJ38JzzMM1aOSIi12lf3D3oYO8UBTKm9tQaIKQDoUFL7P2KpF27b+NaQfHUb8kb5DpXz8RS59UcC+oNuQYKOlKYu1NhCiEZjK+PHwUMGQdmVTdgUHHZ+Oc3Q1EQUYZbEEtTWWgROAVA6rY+ZfNtFG64XF+W8cU3cb7QRz+blFU839myrh5jOPlfZd+wRRJF4SLfeg6r/65ygnXz/PpdxElqyKfvF1kQSYWMYF6+LBsU1uTDBv8TLYnVl7y/ti+OF/Uoo/26rS87YfZeTkdQUgLCp8JDPG15K3DtZ8hT8nZN0cZd509xaIIuXgz2OckvoVZcTLIUyvy4fRdV7AOQxinHyeQ4yVnYlazQ51K431H5wyPsJ8H6AphhK1fpqFrdqH5t8OtAk04HxvpzdHYplSPh6ELMgN5lDnATl/OCmm8N+LavdVBpPYjuD+++eiAxQza2dJr86J9L9Gx6R+ZvEX3SVDzAJG+wVuGS/wyCTs6A7idtE7B6maNOacfT8LbHu84Zi1/clM6dGMh+d/BIuMqKMZmQK6iF4ELvfEDqfWrk6rsWeX4iLkP9EJ+k+6FPhC80icCS4VngAv/eL5L2vUAENA3T1TS3grxHPRhpt2Un82RS8JLhg75ENiwYgZSRSyiSJODhv26B7ScE3r0RPI55lJsEJZl5gao6wc0TISb3ofta1GyTFIyBh4LgUWCOwLevci+swTvfRx1r4YGhYYB9Quv8V7uB2ulX3nwmR2lydRRZF0/ChX9XJ5Cpxij1k07xlt4nxGOjC7CXZQOcZBqdSVU5D2Bt2Dg8UKWc1bo9G4xBKZoOJIYPRelCqtclflPWvpxuXBx5T9+zAFo1io7VwgOyx23e0s2oFxJlfXksmUmfN39RGWj+yDuVw6aNTAlCNV3MvcN3JMr5iJNha6ymvV+BsyQupdIRADJr4glCmToO9JMFQJ4eKj8xaiURme/G4Jk5lncWwYsHodXzbhlqqF2Pl8FSM7zkSPcdiyU4vjkU3vHRd/SSydn9O3pUdbG13QMzh+GoawZfbHxEsURxXTTTkTii4ob2ecojQAmpk+HiyLeAQx4yP7x/6aphWn/8HSvVaW3uzJT5PBCo3SLfzZrml2ZChZffkp/MYlEkU8keJ1RTjkNAPHkMPgjjRE+j5FHFvlyqDXYybUseS3AQIo5XtEQSx1G2o9Qk/9pIbcNWjvz4id/rfq0NWPxDkaRTXLC44Jy1o1RmnXE1k3X+vRGBffBBcwlG32Cs9twM/xXHSuavw1k7z8981GdtcssepKDn1+xOd636/LOHtfOyMq5kaJLZfgSHWF9n9ByGK68LwKGkD+SWhF6E56JKQTMgbGQoFOv+rqZx4XODq62RusnZX852PUatSrgoPXDYs2ZQkFa4cAJvKQpzewcak/2Ihoz97LoWpb4BGbQG96Es1+JQEnm3+jz7HHhhNdSSTFXxelyAVtYIOI5kDU0r3rvASPafRkLhE3pK4vGoUhCLCsxeIBb9oba1X0o7dJ/wAyoMCpLwJhXmWujX6SfUfeuQBzXNN5JatL8FKWeVXq1g6wolCmUrHCB4+CVNsNy9iKwyOIBx4jxgudFQSKbYpoyfKc1HCf1eBFIh8PD/7hGFw8q0H73i7ybp7HmddOa75YChSW44UqzTOqqCpeVojew2vhDo8+rgum0Ss5QWQQKfDqRj2NDa7kZAgypH2mTFQt6XIPgf+BAPY+WAKzpwMZk5CGuXP3UTwZYFS17oGOKG6I7J6zsSs+G0QODBLqdVGaHwsY9l5t+edOP7UMPLUu19FveSOtuktZ2Gtgb4asdH/+8WJEc7iLNzR6HyI7ePJhRGK0efi5I3+ZK+4yjvZLJb8t5QdqGr7rE3yrfRIRKDdVLt6rtnAID/rm1H8umjgs2Rkef+dm3FWQPCM5JBvZBEF6mdhFtY/MZRrHCYog3e+7I8wl3CK4h55BQ0EToi++JA/3eMBc1h5QE6DCKxIav0Gusfux4sbcaFfQtdpbo10jMoL9i3CL9wFjxUZ9BbB9+2oF8q46GLX+8U6CPyXvqsha6HQDn3kQX79tVKUjPEGMXx3LgYppljQNm8Gu+IlBR8uVTRwwEDBPmdpbhd61lWzZ6Ge25HuRhF5V6NWl2AKUiWsPab+Tw4W5hRXtUsn6JYWhdHRZmCe1Tt4HaFPoF+2KQEAGC87Fbi/WmF5+dAXjJimm6rVNt7m43J9rwOA35qUELeA4BWf1Q3WIYDCeMFfEV3HZGwIWH8AwoGomLM9uyfWuD0NS5tkWGRKdrKlo24rTXI3iX1gR6BQeKJExaUzQAyFD7WirVRwCa+qzXT46UjXIXjtF2wNDe2EkdKVZvdIn0cGujIMUuAc1El49lBgwBMYPtXZK0cXerx8eYOlFvWVZzejEdXvDMjzZQsxTRpEH1ozuA1E9awK7i4PjRXq+txzEVFWYe+/udfGUkzhHLvZ50+LbkTDctCmRfHW6P9NR8h67WvR47qn2jbYksGzotjHz1X0eALSfrdToe09Qk7mMW440/wh5cbqKaFl1qLZKrCNLZsU3yXMitKLdi7KVVySeAqbsY+sPYM1bgb3unkfabwfP7JqUQSiePQ3XoT2G82x2mM3hS1bMVTsEgpC4kOVHu6N2cLVN51QSxTy7xEGUfvTjqY+t7hqyjAasdG4JKhliotD60V2vVM6hMukIzGvqB7ZKnNBX2jAr/Izggsny8Jyr7y0d7dhPkGLoPLEfuQ0HSDsMUgxRpUrHzHvemeTagCUEq3Tz/PyFccp3nh7rzUMy42QNRyTT1E84COh/QUPMt9/xT0MYr19FEl7pwX+6UrNGCXFanVe553oQUbjLTzH8P5MOf1/CxEX7ItlIdNxydXljmXPwdReF0U+Sz+APg9nthns+H0BckQqpE9ii/9MANUVJHoWTrR8D6//oPVo9QnpbDabRSnKDIl9yUy+iPmm227p9SUUbj8C2LXXIF7UCL/QxabhFp+HgeWXGiQ7aI4JPr8q91ae6dgcryK4WXlGjL+1Sw9k1c6ANov39AFwMnldC1UM+Q389aeQlHOV94qQsMY1DIIj/JslcoElbf7hdwJ/Va+RnV/BTBOYnn0UE+Q61SxnvuvaEeIbf6vGME0/jS+3tZB4Jk6oPBzNfGyEYbjTNA/JKrUUfQAev0mitFg8pNt8gnmybaaRnxTGdiv2VPiuuvTN+RR1wh6VrDNs0hWoirP4++d1BBQSFPkoq/wIDbtNC4mf20EU4I/3a1EwLbAh+nHa72UdsDksxZjD0Kf8vtBy8TakgZVPL7AZLlRbCWfJmBQT6i9hJJZzDA3jW3CYvae4NbobIvX8BQZ2wpU3ASSgTV+hxOylgwtu6YwnFVE43AMCn1BjnAcCnX5eS7VL8AaEiivMir3HT1UQ8D+IqTGWjjPezCG4Ldpj5toxOUihIbR0RDoPVDQ93dxlyQXoflvDGe6pJyRu7pVLuZhb8Zymxogtkj3h1Yn3cW59nE5Rocwinft0cNhXQEEiJi4lHhHHUVJlbVMYIHGp6yuOzW9X65PTu7+pEsORXxsRh8dhBXC+kw4oTo1dh2NMPOT7A5aMMjeAvvZG0KCQ9nG2bkQElj65BNku4oMT4JUpDA4XOj1NZ9umn8y0UHaMMTSfjbmLyEL0oxM42jqO4IjI9WzCI3HebxLRWYF6kw1iE05UTAX1HjzjNQ7EO36xZ8lu+xpeVL517FuPwYAeKZmW+aYjbLfBYmk/xT8f+ZhlER9qvyrjT89ySNLxYs8+75vJFJTVXI0Gsy01GNak=','2014-04-02 16:10:55','prequal'),(5,21,'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','2014-04-06 16:10:55','prequal');
/*!40000 ALTER TABLE `cj_applicant_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_applicant_score`
--

DROP TABLE IF EXISTS `cj_applicant_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_applicant_score` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `score` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_655E33C31846CDE5` (`cj_applicant_id`),
  CONSTRAINT `FK_655E33C31846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_applicant_score`
--

LOCK TABLES `cj_applicant_score` WRITE;
/*!40000 ALTER TABLE `cj_applicant_score` DISABLE KEYS */;
INSERT INTO `cj_applicant_score` VALUES (1,17,'VGGk5NvDVx8Vjkh6DW1x0kcBXbUTuq5SWlhtorl0hME=','2014-04-07 16:10:55'),(2,18,'t2SoNpA7Nud19IVncsXXKXsYY/rIKBnfkM7Z4nib1hE=','2014-04-07 16:10:55'),(3,19,'t2SoNpA7Nud19IVncsXXKXsYY/rIKBnfkM7Z4nib1hE=','2014-04-07 16:10:55'),(4,21,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=','2014-04-07 16:10:55'),(5,17,'xSdBIkJHFo8KXmCKAMfOcggrz21CgMESW7ZxPkJ1koo=','2014-04-07 16:10:55'),(6,17,'RFH1mTIW5XpaWqAfhUGPJyjHFz6/hE52BcmKfoNgIH0=','2014-04-07 16:10:55'),(7,17,'w/nDd03bp1uBqjFj9Xw4Vb86w5UY1UH/Ej1B/Mnul2U=','2014-04-07 16:10:56'),(8,17,'34oBjhTFh+4wA8g/nm5Wm6wJPH4VCLlZ/at/u5p8mjA=','2014-04-07 16:10:56'),(9,18,'XY9GhBvU22Hq7oNauAfUUHecvniQlz+PswK+UhmdIJQ=','2014-04-07 16:10:56'),(10,19,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2014-04-07 16:10:56'),(11,19,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2014-04-07 16:10:56'),(12,19,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=','2014-04-07 16:10:56'),(13,19,'XY9GhBvU22Hq7oNauAfUUHecvniQlz+PswK+UhmdIJQ=','2014-04-07 16:10:56'),(14,21,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2014-04-07 16:10:56'),(15,21,'Ca7GTJiDpe5XsvEeJFTTE5ELsdxRK4xb5aT0Ix4/7IE=','2014-04-07 16:10:56'),(16,21,'akEMMXputQ3AkJc4QZmt7/nqM6XgTZcoUHsUWwPrrj0=','2014-04-07 16:10:56'),(17,21,'afdeQUJmgFpq/XX0QznfgEpddohhfCCjIrRy7saIQ1g=','2014-04-07 16:10:56'),(18,21,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=','2014-04-07 16:10:56'),(19,39,'XUEY91PUYggzpPltC9h/Z3GQu1RKXlJQ5xWj57E+7s0=','2014-04-07 16:10:56'),(20,39,'+Lve9xXutKVnJlAhVD54bPWEzThG6XUUex5xcoVg9nA=','2014-04-07 16:10:56'),(21,39,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2014-04-07 16:10:56'),(22,39,'tLLokqIfIGSLphmUHf9RivHOzu2sh1c6UfRbXFQk2Qc=','2014-04-07 16:10:56'),(23,39,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2014-04-07 16:10:57'),(24,39,'RFH1mTIW5XpaWqAfhUGPJyjHFz6/hE52BcmKfoNgIH0=','2014-04-07 16:10:57'),(25,40,'XUEY91PUYggzpPltC9h/Z3GQu1RKXlJQ5xWj57E+7s0=','2014-04-07 16:10:57'),(26,40,'+Lve9xXutKVnJlAhVD54bPWEzThG6XUUex5xcoVg9nA=','2014-04-07 16:10:57'),(27,40,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2014-04-07 16:10:57'),(28,40,'tLLokqIfIGSLphmUHf9RivHOzu2sh1c6UfRbXFQk2Qc=','2014-04-07 16:10:57'),(29,40,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2014-04-07 16:10:57'),(30,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2014-04-07 16:10:57'),(31,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2014-04-07 16:10:57'),(32,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2014-04-07 16:10:57'),(33,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2014-04-07 16:10:57'),(34,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2014-04-07 16:10:57'),(35,30,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2014-04-07 16:10:57'),(36,30,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2014-04-07 16:10:57'),(37,30,'Ca7GTJiDpe5XsvEeJFTTE5ELsdxRK4xb5aT0Ix4/7IE=','2014-04-07 16:10:57'),(38,30,'p+BA0IgolM41IPk5n3Vp0168W8rZwne2nhb1kii7w3Q=','2014-04-07 16:10:57'),(39,30,'hwFCfhgNxBFe0L5bdiS6M9xzIixYEBgTG7IoHQ4HMNs=','2014-04-07 16:10:57');
/*!40000 ALTER TABLE `cj_applicant_score` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_applicant_tradelines`
--

DROP TABLE IF EXISTS `cj_applicant_tradelines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_applicant_tradelines` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `cj_group_id` bigint(20) NOT NULL,
  `status` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `tradeline` longtext COLLATE utf8_unicode_ci NOT NULL,
  `is_fixed` tinyint(1) DEFAULT '0',
  `is_disputed` tinyint(1) DEFAULT '0',
  `is_completed` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_356123071846CDE5` (`cj_applicant_id`),
  KEY `IDX_3561230752E95DE5` (`cj_group_id`),
  CONSTRAINT `FK_3561230752E95DE5` FOREIGN KEY (`cj_group_id`) REFERENCES `cj_account_group` (`id`),
  CONSTRAINT `FK_356123071846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_applicant_tradelines`
--

LOCK TABLES `cj_applicant_tradelines` WRITE;
/*!40000 ALTER TABLE `cj_applicant_tradelines` DISABLE KEYS */;
INSERT INTO `cj_applicant_tradelines` VALUES (1,24,2,'56','2861af59828d52986478e107e668b275',0,0,0,'2014-04-07 16:10:58','2014-04-07 16:10:58'),(2,24,2,'97','a40f772bd70becb8d3b290751eac3c84',0,0,0,'2014-04-07 16:10:58','2014-04-07 16:10:58');
/*!40000 ALTER TABLE `cj_applicant_tradelines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_checkout_authorize_net_aim`
--

DROP TABLE IF EXISTS `cj_checkout_authorize_net_aim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_checkout_authorize_net_aim` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_order_id` bigint(20) NOT NULL,
  `code` bigint(20) NOT NULL,
  `subcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reason_code` bigint(20) NOT NULL,
  `reason_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `authorization_code` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `avs` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `transaction_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `md5_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `purchase_order_number` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `card_code` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `cardholder_authentication_value` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `split_tender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_93DCFF9B2122E99A` (`cj_order_id`),
  CONSTRAINT `FK_93DCFF9B2122E99A` FOREIGN KEY (`cj_order_id`) REFERENCES `cj_order` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_checkout_authorize_net_aim`
--

LOCK TABLES `cj_checkout_authorize_net_aim` WRITE;
/*!40000 ALTER TABLE `cj_checkout_authorize_net_aim` DISABLE KEYS */;
/*!40000 ALTER TABLE `cj_checkout_authorize_net_aim` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_dealer_group`
--

DROP TABLE IF EXISTS `cj_dealer_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_dealer_group` (
  `dealer_id` bigint(20) NOT NULL,
  `group_id` bigint(20) NOT NULL,
  PRIMARY KEY (`dealer_id`,`group_id`),
  KEY `IDX_CFE38D5F249E6EA1` (`dealer_id`),
  KEY `IDX_CFE38D5FFE54D947` (`group_id`),
  CONSTRAINT `FK_CFE38D5FFE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`),
  CONSTRAINT `FK_CFE38D5F249E6EA1` FOREIGN KEY (`dealer_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_dealer_group`
--

LOCK TABLES `cj_dealer_group` WRITE;
/*!40000 ALTER TABLE `cj_dealer_group` DISABLE KEYS */;
INSERT INTO `cj_dealer_group` VALUES (2,2),(3,2),(4,3),(5,4),(5,9),(5,11),(6,4),(6,9),(6,11),(7,4),(8,4),(9,4),(9,9),(9,11),(10,5),(11,5),(12,6),(13,4),(13,9),(13,22),(14,23),(15,23);
/*!40000 ALTER TABLE `cj_dealer_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_group_incentives`
--

DROP TABLE IF EXISTS `cj_group_incentives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_group_incentives` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_group_id` bigint(20) NOT NULL,
  `consecutive_number` bigint(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7434DF5452E95DE5` (`cj_group_id`),
  CONSTRAINT `FK_7434DF5452E95DE5` FOREIGN KEY (`cj_group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_group_incentives`
--

LOCK TABLES `cj_group_incentives` WRITE;
/*!40000 ALTER TABLE `cj_group_incentives` DISABLE KEYS */;
INSERT INTO `cj_group_incentives` VALUES (1,2,1,NULL,'Car washing','We\'ll wash your car, he-he....','2014-04-07 16:10:58'),(2,2,2,NULL,'Accessories','15% on BFGoodReach Tires','2014-04-07 16:10:58'),(3,2,0,NULL,'Accessories','50% on china details','2014-04-07 16:10:58'),(4,2,3,0,'Accessories','5% Original details','2014-04-07 16:10:58'),(5,1,0,NULL,'Car washing','We\'ll wash your car, he-he....','2014-04-07 16:10:58'),(6,1,1,NULL,'Accessories','10% on BFGoodReach Tires','2014-04-07 16:10:58'),(7,2,4,NULL,'Accessories','10% on Good Year Tires','2014-04-07 16:10:58');
/*!40000 ALTER TABLE `cj_group_incentives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_holding`
--

DROP TABLE IF EXISTS `cj_holding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_holding` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_holding`
--

LOCK TABLES `cj_holding` WRITE;
/*!40000 ALTER TABLE `cj_holding` DISABLE KEYS */;
INSERT INTO `cj_holding` VALUES (1,'Darryl\'s Holding','2014-04-07 16:10:53','2014-04-07 16:10:53'),(2,'Moss Holding','2014-04-07 16:10:54','2014-04-07 16:10:54'),(3,'Test Holding','2014-04-07 16:10:54','2014-04-07 16:10:54'),(4,'700Credit','2014-02-06 16:10:54','2014-03-30 16:10:54'),(5,'Rent Holding','2014-02-06 16:10:54','2014-03-30 16:10:54'),(6,'Estate Holding','2014-02-06 16:10:54','2014-03-30 16:10:54'),(7,'Test RentHolding 2','2014-02-06 16:10:54','2014-03-30 16:10:54'),(8,'Test Rent Holding 3','2014-02-06 16:10:54','2014-03-30 16:10:54');
/*!40000 ALTER TABLE `cj_holding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_lead`
--

DROP TABLE IF EXISTS `cj_lead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_lead` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `cj_account_id` bigint(20) DEFAULT NULL,
  `cj_group_id` bigint(20) DEFAULT NULL,
  `target_score` bigint(20) DEFAULT NULL,
  `target_name` longtext COLLATE utf8_unicode_ci,
  `target_url` longtext COLLATE utf8_unicode_ci,
  `state` bigint(20) DEFAULT NULL,
  `trade_in` tinyint(1) DEFAULT NULL,
  `down_payment` bigint(20) DEFAULT NULL,
  `fraction` smallint(6) DEFAULT '0',
  `status` enum('new','prequal','active','idle','ready','finished','expired','processed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'new' COMMENT '(DC2Type:LeadStatus)',
  `source` enum('office','webpage') COLLATE utf8_unicode_ci DEFAULT 'office' COMMENT '(DC2Type:LeadSource)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3DCB43F71846CDE5` (`cj_applicant_id`),
  KEY `IDX_3DCB43F7ED8F6A55` (`cj_account_id`),
  KEY `IDX_3DCB43F752E95DE5` (`cj_group_id`),
  CONSTRAINT `FK_3DCB43F752E95DE5` FOREIGN KEY (`cj_group_id`) REFERENCES `cj_account_group` (`id`),
  CONSTRAINT `FK_3DCB43F71846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_3DCB43F7ED8F6A55` FOREIGN KEY (`cj_account_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_lead`
--

LOCK TABLES `cj_lead` WRITE;
/*!40000 ALTER TABLE `cj_lead` DISABLE KEYS */;
INSERT INTO `cj_lead` VALUES (1,17,2,2,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(2,18,4,3,664,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(3,19,4,3,580,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(4,20,4,3,600,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(5,21,4,3,650,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'processed','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(6,22,4,3,714,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(7,23,2,2,552,'Honda CR-V','https://carimg.s3.amazonaws.com/8477_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(8,24,2,2,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(9,25,2,2,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(10,26,2,2,830,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(11,27,2,2,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(12,28,9,11,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(13,27,9,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(14,29,5,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(15,30,5,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(16,31,5,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(17,32,5,9,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(18,33,5,11,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(19,34,5,9,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(20,35,6,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(21,36,6,4,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(22,37,6,4,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(23,40,6,9,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2014-04-02 16:10:55','2014-04-02 16:10:55'),(24,39,6,11,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','office','2014-02-16 16:10:55','2014-02-16 16:10:55'),(25,21,2,2,510,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2014-02-16 16:10:55','2014-02-16 16:10:55'),(26,21,5,9,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2014-02-16 16:10:55','2014-02-16 16:10:55'),(27,41,14,23,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','office','2014-03-28 16:10:55','2014-02-16 16:10:55'),(28,15,14,23,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','office','2014-03-28 16:10:55','2014-02-16 16:10:55');
/*!40000 ALTER TABLE `cj_lead` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_lead_history`
--

DROP TABLE IF EXISTS `cj_lead_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_lead_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) DEFAULT NULL,
  `editor_id` bigint(20) DEFAULT NULL,
  `target_score` bigint(20) DEFAULT NULL,
  `target_name` longtext COLLATE utf8_unicode_ci,
  `target_url` longtext COLLATE utf8_unicode_ci,
  `state` bigint(20) DEFAULT NULL,
  `trade_in` tinyint(1) DEFAULT NULL,
  `down_payment` bigint(20) DEFAULT NULL,
  `fraction` smallint(6) DEFAULT '0',
  `status` enum('new','prequal','active','idle','ready','finished','expired','processed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'new' COMMENT '(DC2Type:LeadStatus)',
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F12171C1232D562B` (`object_id`),
  CONSTRAINT `FK_F12171C1232D562B` FOREIGN KEY (`object_id`) REFERENCES `cj_lead` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_lead_history`
--

LOCK TABLES `cj_lead_history` WRITE;
/*!40000 ALTER TABLE `cj_lead_history` DISABLE KEYS */;
INSERT INTO `cj_lead_history` VALUES (1,1,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(2,2,NULL,664,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(3,3,NULL,580,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(4,4,NULL,600,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(5,5,NULL,650,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'processed','2014-04-07 16:10:55'),(6,6,NULL,714,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(7,7,NULL,552,'Honda CR-V','https://carimg.s3.amazonaws.com/8477_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(8,8,NULL,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(9,9,NULL,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(10,10,NULL,830,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(11,11,NULL,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(12,12,NULL,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(13,13,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(14,14,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(15,15,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(16,16,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(17,17,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(18,18,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(19,19,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(20,20,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2014-04-07 16:10:55'),(21,21,NULL,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(22,22,NULL,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','2014-04-07 16:10:55'),(23,23,NULL,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(24,24,NULL,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','2014-04-07 16:10:55'),(25,25,NULL,510,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(26,26,NULL,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2014-04-07 16:10:55'),(27,27,NULL,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','2014-04-07 16:10:55'),(28,28,NULL,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','2014-04-07 16:10:55');
/*!40000 ALTER TABLE `cj_lead_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_login_defense`
--

DROP TABLE IF EXISTS `cj_login_defense`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_login_defense` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `attempts` bigint(20) NOT NULL DEFAULT '1',
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6C609834A76ED395` (`user_id`),
  CONSTRAINT `FK_6C609834A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_login_defense`
--

LOCK TABLES `cj_login_defense` WRITE;
/*!40000 ALTER TABLE `cj_login_defense` DISABLE KEYS */;
/*!40000 ALTER TABLE `cj_login_defense` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_operation`
--

DROP TABLE IF EXISTS `cj_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_operation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `cj_applicant_report_id` bigint(20) DEFAULT NULL,
  `contract_id` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  `type` enum('report','rent','other','charge') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OperationType)',
  `amount` decimal(10,2) NOT NULL,
  `paid_for` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_21F5D92D2A26A0ED` (`cj_applicant_report_id`),
  KEY `IDX_21F5D92D8D9F6D38` (`order_id`),
  KEY `IDX_21F5D92D2576E0FD` (`contract_id`),
  KEY `IDX_21F5D92DFE54D947` (`group_id`),
  CONSTRAINT `FK_21F5D92DFE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`),
  CONSTRAINT `FK_21F5D92D2576E0FD` FOREIGN KEY (`contract_id`) REFERENCES `rj_contract` (`id`),
  CONSTRAINT `FK_21F5D92D2A26A0ED` FOREIGN KEY (`cj_applicant_report_id`) REFERENCES `cj_applicant_report` (`id`),
  CONSTRAINT `FK_21F5D92D8D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_operation`
--

LOCK TABLES `cj_operation` WRITE;
/*!40000 ALTER TABLE `cj_operation` DISABLE KEYS */;
INSERT INTO `cj_operation` VALUES (1,1,1,NULL,NULL,'report',9.00,'2014-04-07','2014-04-07 16:10:58'),(2,2,NULL,8,NULL,'rent',1500.00,'2014-02-16','2014-04-07 16:11:01'),(3,3,NULL,8,NULL,'rent',1500.00,'2014-02-26','2014-04-07 16:11:01'),(4,4,NULL,8,NULL,'rent',1500.00,'2014-03-08','2014-04-07 16:11:01'),(5,5,NULL,8,NULL,'rent',1500.00,'2014-03-08','2014-04-07 16:11:01'),(6,6,NULL,8,NULL,'rent',1500.00,'2014-04-07','2014-04-07 16:11:01'),(7,7,NULL,8,NULL,'rent',700.00,'2014-03-08','2014-04-07 16:11:01'),(8,8,NULL,8,NULL,'rent',750.00,'2014-03-08','2014-04-07 16:11:01'),(9,9,NULL,8,NULL,'rent',1500.00,'2014-03-18','2014-04-07 16:11:01'),(10,10,NULL,8,NULL,'rent',1500.00,'2014-03-28','2014-04-07 16:11:01'),(11,11,NULL,6,NULL,'rent',1500.00,'2014-04-05','2014-04-07 16:11:01'),(12,12,NULL,7,NULL,'rent',3700.00,'2014-04-07','2014-04-07 16:11:01'),(13,13,NULL,3,NULL,'rent',1500.00,'2013-04-02','2014-04-07 16:11:01'),(14,14,NULL,3,NULL,'rent',1500.00,'2014-04-07','2014-04-07 16:11:01'),(15,15,NULL,3,NULL,'rent',1500.00,'2013-05-02','2014-04-07 16:11:01'),(16,16,NULL,3,NULL,'rent',1500.00,'2013-06-01','2014-04-07 16:11:01'),(17,17,NULL,3,NULL,'rent',1500.00,'2013-07-01','2014-04-07 16:11:01'),(18,18,NULL,3,NULL,'rent',1500.00,'2013-07-31','2014-04-07 16:11:01'),(19,19,NULL,3,NULL,'rent',1500.00,'2013-08-30','2014-04-07 16:11:01'),(20,20,NULL,3,NULL,'rent',1500.00,'2013-09-29','2014-04-07 16:11:01'),(21,21,NULL,3,NULL,'rent',1500.00,'2013-10-29','2014-04-07 16:11:01'),(22,22,NULL,3,NULL,'rent',1500.00,'2013-11-28','2014-04-07 16:11:01'),(23,23,NULL,3,NULL,'rent',1500.00,'2013-12-28','2014-04-07 16:11:01'),(24,24,NULL,4,NULL,'rent',1250.00,'2013-08-07','2014-04-07 16:11:01'),(25,25,NULL,4,NULL,'rent',1250.00,'2013-09-07','2014-04-07 16:11:01'),(26,26,NULL,4,NULL,'rent',1250.00,'2013-10-07','2014-04-07 16:11:01'),(27,27,NULL,4,NULL,'rent',1250.00,'2013-11-07','2014-04-07 16:11:01'),(28,28,NULL,4,NULL,'rent',1250.00,'2013-12-07','2014-04-07 16:11:01'),(29,29,NULL,NULL,NULL,'rent',1250.00,'2014-01-07','2014-04-07 16:11:01'),(30,30,NULL,NULL,NULL,'rent',1250.00,'2014-02-07','2014-04-07 16:11:01'),(31,31,NULL,NULL,NULL,'rent',1250.00,'2013-05-07','2014-04-07 16:11:01'),(32,32,NULL,2,NULL,'rent',1250.00,'2013-06-07','2014-04-07 16:11:01'),(33,33,NULL,2,NULL,'rent',1250.00,'2013-06-07','2014-04-07 16:11:01'),(34,34,NULL,2,NULL,'rent',1250.00,'2013-08-07','2014-04-07 16:11:01'),(35,35,NULL,2,NULL,'rent',1250.00,'2013-09-07','2014-04-07 16:11:01'),(36,36,NULL,2,NULL,'rent',1250.00,'2013-11-07','2014-04-07 16:11:01'),(37,37,NULL,2,NULL,'rent',1250.00,'2013-11-07','2014-04-07 16:11:01'),(38,38,NULL,2,NULL,'rent',1250.00,'2013-12-07','2014-04-07 16:11:01'),(39,39,NULL,2,NULL,'rent',1250.00,'2014-01-07','2014-04-07 16:11:01'),(40,40,NULL,2,NULL,'rent',1250.00,'2014-02-07','2014-04-07 16:11:01'),(41,41,NULL,2,NULL,'rent',1250.00,'2014-03-07','2014-04-07 16:11:01'),(42,42,NULL,2,NULL,'rent',1250.00,'2014-04-06','2014-04-07 16:11:01'),(43,43,NULL,19,NULL,'rent',1.00,'2014-04-06','2014-04-07 16:11:01'),(44,44,NULL,19,NULL,'rent',2.00,'2014-04-06','2014-04-07 16:11:01'),(45,45,NULL,15,NULL,'rent',1500.00,'2014-03-07','2014-04-07 16:11:01');
/*!40000 ALTER TABLE `cj_operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_order`
--

DROP TABLE IF EXISTS `cj_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_order` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `status` enum('new','pending','complete','error','cancelled','refunded','returned') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'new' COMMENT '(DC2Type:OrderStatus)',
  `type` enum('authorize_card','heartland_card','heartland_bank','cash') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:OrderType)',
  `sum` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DA53B53D1846CDE5` (`cj_applicant_id`),
  CONSTRAINT `FK_DA53B53D1846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_order`
--

LOCK TABLES `cj_order` WRITE;
/*!40000 ALTER TABLE `cj_order` DISABLE KEYS */;
INSERT INTO `cj_order` VALUES (1,21,'complete','authorize_card',9.00,'2014-04-07 16:10:58','2014-04-07 16:10:58'),(2,42,'complete','heartland_card',1500.00,'2014-02-16 16:11:01','2014-02-16 16:11:01'),(3,42,'complete','heartland_card',1500.00,'2014-02-26 16:11:01','2014-02-26 16:11:01'),(4,42,'complete','heartland_card',1500.00,'2014-03-08 16:11:01','2014-03-08 16:11:01'),(5,42,'error','heartland_card',1500.00,'2014-03-08 16:11:01','2014-03-08 16:11:01'),(6,42,'cancelled','heartland_card',1500.00,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(7,42,'refunded','heartland_card',700.00,'2014-03-08 16:11:01','2014-03-08 16:11:01'),(8,42,'returned','heartland_card',750.00,'2014-03-08 16:11:01','2014-03-08 16:11:01'),(9,42,'complete','heartland_card',1500.00,'2014-03-18 16:11:01','2014-03-18 16:11:01'),(10,42,'complete','heartland_card',1500.00,'2014-03-28 16:11:01','2014-03-28 16:11:01'),(11,42,'new','heartland_card',1500.00,'2014-04-05 16:11:01','2014-04-05 16:11:01'),(12,47,'pending','heartland_card',3700.00,'2014-04-07 16:11:01','2014-04-07 16:11:01'),(13,42,'complete','heartland_card',1500.00,'2013-04-02 16:11:01','2013-03-31 16:11:01'),(14,42,'complete','heartland_card',1500.00,'2013-05-02 16:11:01','2013-05-07 16:11:01'),(15,42,'complete','heartland_card',1500.00,'2013-05-02 16:11:01','2013-04-30 16:11:01'),(16,42,'complete','heartland_card',1500.00,'2013-06-01 16:11:01','2013-05-30 16:11:01'),(17,42,'complete','heartland_card',1500.00,'2013-07-01 16:11:01','2013-06-29 16:11:01'),(18,42,'complete','heartland_card',1500.00,'2013-07-31 16:11:01','2013-07-29 16:11:01'),(19,42,'complete','heartland_card',1500.00,'2013-08-30 16:11:01','2013-08-28 16:11:01'),(20,42,'complete','heartland_card',1500.00,'2013-09-29 16:11:01','2013-09-27 16:11:01'),(21,42,'complete','heartland_card',1500.00,'2013-10-29 16:11:01','2013-10-27 16:11:01'),(22,42,'complete','heartland_card',1500.00,'2013-11-28 16:11:01','2013-11-26 16:11:01'),(23,42,'complete','heartland_card',1500.00,'2013-12-28 16:11:01','2013-12-26 16:11:01'),(24,43,'complete','heartland_card',1250.00,'2013-08-07 16:11:01','2013-08-07 16:11:01'),(25,43,'complete','heartland_card',1250.00,'2013-09-07 16:11:01','2013-09-07 16:11:01'),(26,43,'complete','heartland_card',1250.00,'2013-10-07 16:11:01','2013-10-07 16:11:01'),(27,43,'complete','heartland_card',1250.00,'2013-11-07 16:11:01','2013-11-07 16:11:01'),(28,43,'complete','heartland_card',1250.00,'2013-12-07 16:11:01','2013-12-07 16:11:01'),(29,43,'complete','heartland_card',1250.00,'2014-01-07 16:11:01','2014-01-07 16:11:01'),(30,43,'complete','heartland_card',1250.00,'2014-02-07 16:11:01','2014-02-07 16:11:01'),(31,42,'complete','heartland_card',1250.00,'2013-05-07 16:11:01','2013-05-07 16:11:01'),(32,42,'complete','heartland_card',1250.00,'2013-06-07 16:11:01','2013-06-07 16:11:01'),(33,42,'complete','heartland_card',1250.00,'2013-06-07 16:11:01','2013-06-07 16:11:01'),(34,42,'complete','heartland_card',1250.00,'2013-08-07 16:11:01','2013-08-07 16:11:01'),(35,42,'complete','heartland_card',1250.00,'2013-09-07 16:11:01','2013-09-07 16:11:01'),(36,42,'complete','heartland_card',1250.00,'2013-11-07 16:11:01','2013-11-07 16:11:01'),(37,42,'complete','heartland_card',1250.00,'2013-11-07 16:11:01','2013-11-07 16:11:01'),(38,42,'complete','heartland_card',1250.00,'2013-12-07 16:11:01','2013-12-07 16:11:01'),(39,42,'complete','heartland_card',1250.00,'2014-01-07 16:11:01','2014-01-07 16:11:01'),(40,42,'complete','heartland_card',1250.00,'2014-02-07 16:11:01','2014-02-07 16:11:01'),(41,42,'complete','heartland_card',1250.00,'2014-03-07 16:11:01','2014-03-07 16:11:01'),(42,42,'complete','heartland_card',1250.00,'2014-04-06 16:11:01','2014-04-06 16:11:01'),(43,53,'complete','heartland_card',1.00,'2014-04-06 16:11:01','2014-04-06 16:11:01'),(44,53,'complete','heartland_card',2.00,'2014-04-06 16:11:01','2014-04-06 16:11:01'),(45,53,'complete','heartland_card',1500.00,'2014-03-07 16:11:01','2014-03-07 16:11:01');
/*!40000 ALTER TABLE `cj_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_pricing`
--

DROP TABLE IF EXISTS `cj_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_pricing` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_account_group_id` bigint(20) NOT NULL,
  `amount` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_pricing`
--

LOCK TABLES `cj_pricing` WRITE;
/*!40000 ALTER TABLE `cj_pricing` DISABLE KEYS */;
/*!40000 ALTER TABLE `cj_pricing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_purchase`
--

DROP TABLE IF EXISTS `cj_purchase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_purchase` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `amount` bigint(20) NOT NULL,
  `cj_lead_id` bigint(20) NOT NULL,
  `cj_account_id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_purchase`
--

LOCK TABLES `cj_purchase` WRITE;
/*!40000 ALTER TABLE `cj_purchase` DISABLE KEYS */;
/*!40000 ALTER TABLE `cj_purchase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_settings`
--

DROP TABLE IF EXISTS `cj_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pidkiq_password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `pidkiq_eai` longtext COLLATE utf8_unicode_ci NOT NULL,
  `net_connect_password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `net_connect_eai` longtext COLLATE utf8_unicode_ci NOT NULL,
  `contract` longtext COLLATE utf8_unicode_ci NOT NULL,
  `rights` longtext COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_settings`
--

LOCK TABLES `cj_settings` WRITE;
/*!40000 ALTER TABLE `cj_settings` DISABLE KEYS */;
INSERT INTO `cj_settings` VALUES (1,'LYcLeS5ieWuoX5n+vPY3GcTUFtobd86xVEexzHI/7VFfBZksM2NQxczFu8GrDu1lmDvMf3t05vSt7M5Jw3J9Cg==','uvzrzV024jBbGB74YAx3DAXYlB0XyF1wXwgbRlCBtTM=','X8m/OiTqsSC9v6Yn4PkKQC/rcsr6XbyfmQ9xq0g7900=','uvzrzV024jBbGB74YAx3DAXYlB0XyF1wXwgbRlCBtTM=','Test Contract text','Some rules','2014-04-07 16:10:58');
/*!40000 ALTER TABLE `cj_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_user`
--

DROP TABLE IF EXISTS `cj_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `holding_id` bigint(20) DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `middle_initial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street_address1` longtext COLLATE utf8_unicode_ci,
  `street_address2` longtext COLLATE utf8_unicode_ci,
  `unit_no` varchar(31) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_type` bigint(20) DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `ssn` longtext COLLATE utf8_unicode_ci,
  `is_active` tinyint(1) DEFAULT '0',
  `invite_code` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resident_id` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `score_changed_notification` tinyint(1) DEFAULT '1',
  `offer_notification` tinyint(1) DEFAULT '0',
  `culture` enum('en','hi','test','es') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en' COMMENT '(DC2Type:UserCulture)',
  `has_data` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` enum('none','failed','locked','passed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none' COMMENT '(DC2Type:UserIsVerified)',
  `has_report` tinyint(1) DEFAULT NULL,
  `is_holding_admin` tinyint(1) DEFAULT '0',
  `is_super_admin` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `type` enum('admin','applicant','dealer','tenant','tenant','landlord') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:UserType)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_98C9F47592FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_98C9F475A0D96FBF` (`email_canonical`),
  UNIQUE KEY `UNIQ_98C9F4756F21F112` (`invite_code`),
  KEY `IDX_98C9F4756CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_98C9F4756CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_user`
--

LOCK TABLES `cj_user` WRITE;
/*!40000 ALTER TABLE `cj_user` DISABLE KEYS */;
INSERT INTO `cj_user` VALUES (1,NULL,'admin@creditjeeves.com','admin@creditjeeves.com','admin@creditjeeves.com','admin@creditjeeves.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','5ca33d221fd09f16c1ecba9c1aadc3eb','2014-04-06 16:10:54',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Administrator',NULL,'Super','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CQ9UE',NULL,1,1,'test',1,'none',NULL,0,1,'2014-02-06 16:10:54','2014-04-07 16:10:54','admin'),(2,NULL,'honda-admin','honda-admin','honda-admin','honda-admin',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Bill',NULL,'Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSOG6',NULL,1,1,'test',1,'none',NULL,0,1,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(3,NULL,'honda@example.com','honda@example.com','honda@example.com','honda@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Honda',NULL,'Dealer','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSPRX',NULL,1,1,'test',1,'none',NULL,0,0,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(4,NULL,'alex.emelyanov.ua@gmail.com','alex.emelyanov.ua@gmail.com','alex.emelyanov.ua@gmail.com','alex.emelyanov.ua@gmail.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Alex',NULL,'Emelyanov','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSQIK',NULL,1,1,'test',1,'none',NULL,0,1,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(5,1,'darryl@cars.com','darryl@cars.com','darryl@cars.com','darryl@cars.com',1,'71hfh6i522o0g0gs8k4w8c44o8csoow','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Darryl',NULL,'Eaton','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSRB6',NULL,1,1,'test',1,'none',NULL,1,1,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(6,1,'ton@cars.com','ton@cars.com','ton@cars.com','ton@cars.com',1,'bp14we4v93c40okcc8coos00scc40ko','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ton',NULL,'Sharp','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSS32',NULL,1,1,'test',1,'none',NULL,0,0,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(7,1,'alex@cars.com','alex@cars.com','alex@cars.com','alex@cars.com',1,'r2cm7h1j7u8ssw00osoow8k08g404c8','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Alex',NULL,'Emelyanov','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSSVP',NULL,1,1,'test',1,'none',NULL,0,0,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(8,1,'zane@cars.com','zane@cars.com','zane@cars.com','zane@cars.com',1,'4mz02s2ug4qo4kc8kckgcwwocgokcko','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Zane',NULL,'Stagg','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSTOJ',NULL,1,1,'test',1,'none',NULL,0,0,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(9,1,'darryl@autotrader.com','darryl@autotrader.com','darryl@autotrader.com','darryl@autotrader.com',1,'bhg5xqyod008ksc8oss04wkcs8ocww8','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Darryl',NULL,'Eaton','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSUIX',NULL,1,1,'test',1,'none',NULL,1,1,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(10,1,'ton@autotrader.com  ','ton@autotrader.com  ','ton@autotrader.com  ','ton@autotrader.com  ',1,'dn3iu53skvksc44ggwg480gowo80wwc','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ton',NULL,'Sharp','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSVCI',NULL,1,1,'test',1,'none',NULL,0,0,'2014-04-07 16:10:54','2014-04-07 16:10:54','dealer'),(11,1,'zane@autotrader.com','zane@autotrader.com','zane@autotrader.com','zane@autotrader.com',1,'1pn6n61hshk0k440so4o0s84o8gcw44','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Zane',NULL,'Stagg','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSW9I',NULL,1,1,'test',1,'none',NULL,0,0,'2012-11-29 14:33:35','2014-04-07 16:10:54','dealer'),(12,NULL,'darryl@autonation.com','darryl@autonation.com','darryl@autonation.com','darryl@autonation.com',1,'46er3hnfqokk4s0w8w8sgcc4kos0sg8','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Darryl',NULL,'Eaton','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSX1W',NULL,1,1,'test',1,'none',NULL,0,1,'2012-11-29 14:36:16','2014-04-07 16:10:54','dealer'),(13,1,'audi@example.com','audi@example.com','audi@example.com','audi@example.com',1,'k41kgqq58c0sss88g0g4s4w8g8w808o','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'AUDI',NULL,'Dealer','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSXU8',NULL,1,1,'test',1,'none',NULL,0,0,'2012-11-29 14:36:16','2014-04-07 16:10:54','dealer'),(14,4,'support@700credit.com','support@700credit.com','support@700credit.com','support@700credit.com',1,'ldrenqbim00kg488sc0s40wow48c0s0','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'700Credit',NULL,'700Credit','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'1a1dc91c9073',NULL,1,1,'test',1,'none',NULL,1,1,'2012-11-29 14:36:16','2014-04-07 16:10:54','dealer'),(15,4,'support2@700credit.com','support2@700credit.com','support2@700credit.com','support2@700credit.com',1,'ovsv93p2oc0ogscs4gcggswsoc44gwo','7b3e63c45d5cb6859f325ab1447321ef',NULL,0,0,NULL,NULL,NULL,'a:1:{i:0;s:10:\"CREDIT_API\";}',0,NULL,'700CreditAPI',NULL,'700CreditAPI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSYSV',NULL,1,1,'test',1,'none',NULL,1,1,'2012-11-29 14:36:16','2014-04-07 16:10:54','dealer'),(16,NULL,'api@usequity.com','api@usequity.com','api@usequity.com','api@usequity.com',1,'50hh2y1ri1gcggcsk0c0g8goowcwkoc','848c4abcaa73a1c14c273cf0d394d4a8',NULL,0,0,NULL,NULL,NULL,'a:1:{i:0;s:13:\"USE_QUITY_API\";}',0,NULL,'USEquityAPI',NULL,'USEquityAPI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3CSZ8U',NULL,1,1,'test',1,'none',NULL,1,1,'2012-11-29 14:36:16','2014-04-07 16:10:54','dealer'),(17,NULL,'alexey.karpik+app1334753295955955@gmail.com','alexey.karpik+app1334753295955955@gmail.com','alexey.karpik+app1334753295955955@gmail.com','alexey.karpik+app1334753295955955@gmail.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ivan','Petrovich','Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'375291181804','1980-10-22','18LW1dmYAbWnogX0Jj9fJlxbu0FJIWAf2gPvBsF8JL8=',1,'EF7C3CXCBR',NULL,1,1,'test',1,'none',1,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(18,NULL,'john@example.com','john@example.com','john@example.com','john@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EF7C3CXCYB',NULL,1,1,'test',1,'none',1,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(19,NULL,'alex@example.com','alex@example.com','alex@example.com','alex@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ALEX',NULL,'JORDAN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8603790319','1951-01-01','o/XbnU2gkSeBfX3iXG5j6Snq3cGSPl/Akn5yqDFakus=',1,'EF7C3CXDLB',NULL,1,1,'test',1,'passed',1,0,0,'2014-04-07 16:10:54','2014-04-07 16:10:54','applicant'),(20,NULL,'empty@example.com','empty@example.com','empty@example.com','empty@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'OLA','MAE','TAYLOR','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3097458439','1955-09-14','Hzx9ANUNJMkWJpbUxQwrSi446R4oQLIYFMZsZfbrlUo=',1,'EF7C3CXE3U',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(21,NULL,'emilio@example.com','emilio@example.com','emilio@example.com','emilio@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'BRIAN','P','KURTH','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7188491319','1957-02-19','67I+L2Pl9SvLEvEhw1Ss16sD19o6mj2HYWeMAKFCVi8=',1,'EF7C3CXEMA',NULL,1,1,'test',1,'passed',1,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(22,NULL,'robert@example.com','robert@example.com','robert@example.com','robert@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBERT','SCOTT','BIRMINGHAM','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1957-02-19','hsh/v/SvWEOB7XOEj1Tzuotn0UqChKq41U3n+Ib56Oo=',1,'EF7C3CXF4A',NULL,1,1,'test',0,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(23,NULL,'mamazza@example.com','mamazza@example.com','mamazza@example.com','mamazza@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'WILLIAM','N','JOHNSON','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','CjdmjM3h49dVq81ay0Lietv7z7qMTyCY7tnCX4tx48U=',1,'EF7C3CXGB3',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(24,NULL,'marion@example.com','marion@example.com','marion@example.com','marion@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MARION','R','BRIEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','nRDcgyOn6wu2mi+qL4NKLfjSWEQ2dajaAlGtAEON2dc=',1,'EF7C3CXHR0',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(25,NULL,'hugo@example.com','hugo@example.com','hugo@example.com','hugo@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'HUGO','WOSBELLY','RODRIGUEZ','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','EXWuftAv8hCgEE4JMQx7VmW57FzQRs0tu/6mioDHVDs=',1,'EF7C3CXIVW',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(26,NULL,'miguel@example.com','miguel@example.com','miguel@example.com','miguel@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MIGUEL','M','CENTENO','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','Y7aS5ZM2AaiwP0UJcni+Vg3Hw2uDia0iT1KShhzwHRQ=',1,'EF7C3CXJZT',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(27,NULL,'CONNIE@example.com','connie@example.com','CONNIE@example.com','connie@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'CONNIE','S','WEBSTER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1941-01-01','lQeEBCdr8yXWIHdflkrmC9PNA7TtL+ANyf8wCBNCqoo=',1,'EF7C3CXKU9',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(28,NULL,'lory@example.com','lory@example.com','lory@example.com','lory@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LORY','M','STEFFANS','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1962-09-19','380NKJ9S3Ad4y2DAVJxDDZjKPMd90SHj9TV+ADyjG8c=',1,'EF7C3CXLYX',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(29,NULL,'app3@example.com','app3@example.com','app3@example.com','app3@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBERT','SCOTT','BIRMINGHAM','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3028320298','1962-06-05','hsh/v/SvWEOB7XOEj1Tzuotn0UqChKq41U3n+Ib56Oo=',1,'EF7C3CXN1Y',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(30,NULL,'app4@example.com','app4@example.com','app4@example.com','app4@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MILDRED',NULL,'RIOS-HERNANDEZ','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'4068921606','2014-04-07','nIDo1db9vq25V2vyEfn6HY2vhKyJS7SdFUrtNjIyE04=',1,'EF7C3CXO45',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(31,NULL,'app5@example.com','app5@example.com','app5@example.com','app5@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ANTHONY','D','DELLISANTI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'4105367237','1956-08-09','Yk1atQG018s86si+DpItfafiuiY+TpGYVavoF6bLkK4=',1,'EF7C3CXP4L',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(32,NULL,'app6@example.com','app6@example.com','app6@example.com','app6@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LINDA','A','LEMOINE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','2014-04-07','eUt9MpW59ta4Ea9irGF+GpAXklHBBAiGRWKjK/rszl0=',1,'EF7C3CXQCM',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(33,NULL,'app8@example.com','app8@example.com','app8@example.com','app8@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'GARY','A','LINDSAY','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3048428383','1955-11-30','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EF7C3CXRI6',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(34,NULL,'app9@example.com','app9@example.com','app9@example.com','app9@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'THOMAS','DENNIS','LOPES','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','2014-04-07','wJVJE2Boh5UkGehexbiRqmMlklpTQgoo2w6o9vyC+Z4=',1,'EF7C3CXSO0',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(35,NULL,'app10@example.com','app10@example.com','app10@example.com','app10@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBYN','L','PIPER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7034910325','1968-04-07','hGDZNda+L9PJPWEu2gMRf7hn5Xy1D8VdeP9BpyJ2V7s=',1,'EF7C3CXTX4',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(36,NULL,'app11@example.com','app11@example.com','app11@example.com','app11@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LAURIEANN','KATHLEEN','RADLEIN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,NULL,'1968-02-14','WO0EKHQP12xEv4rwUOXt0pFf7KXJQ3/awl4T7fjIoLI=',1,'EF7C3CXUKM',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(37,NULL,'linda@example.com','linda@example.com','linda@example.com','linda@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','2014-04-07','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'TESTFULL',NULL,1,1,'test',1,'none',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(38,NULL,'tenant133@example.com','tenant133@example.com','tenant133@example.com','tenant133@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'TIMOTHY','A','APPLEGATE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7858655392','1937-11-10','urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'EF7C3CXVZR',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-02-16 16:10:54','2014-04-07 16:10:54','applicant'),(39,NULL,'app14@example.com','app14@example.com','app14@example.com','app14@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'PATRICIA','A','ROTHWELL','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8187859255','1937-01-01','urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'TESTCODE',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-02-16 16:10:54','2014-04-07 16:10:54','applicant'),(40,NULL,'app12@example.com','app12@example.com','app12@example.com','app12@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROGER','D','STANLEY','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'6165311574','1949-12-09','xPsjgwUzrkxk6ShvMYn9a533cjNOGHIVl2js0SVxPGA=',1,'EF7C3CXXUQ',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(41,NULL,'noname@gmail.com','noname@gmail.com','noname@gmail.com','noname@gmail.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'700Credit','Petrovich','Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'375291181804','1980-10-22','18LW1dmYAbWnogX0Jj9fJlxbu0FJIWAf2gPvBsF8JL8=',1,'EF7C3CXYUH',NULL,1,1,'test',1,'none',1,0,0,'2014-04-02 16:10:54','2014-04-07 16:10:54','applicant'),(42,NULL,'tenant11@example.com','tenant11@example.com','tenant11@example.com','tenant11@example.com',1,'ky29yqscy00g0c0c0w8cocsoc8s8wkg','1a1dc91c907325c69271ddf0c944bc72','2014-04-07 15:10:59',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'TIMOTHY','A','APPLEGATE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7858655392','1937-11-10','urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'EF7C3G31N2',NULL,1,1,'test',1,'none',NULL,0,0,'2014-02-16 16:10:59','2014-04-07 16:10:59','tenant'),(43,NULL,'john@rentrack.com','john@rentrack.com','john@rentrack.com','john@rentrack.com',1,'t4swhqumswgc4k4ooosc0g40c0wwskc','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:10:59',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EF7C3G32B6',NULL,1,1,'test',1,'none',1,0,0,'2014-02-21 16:10:59','2014-04-07 16:10:59','tenant'),(44,NULL,'alex@rentrack.com','alex@rentrack.com','alex@rentrack.com','alex@rentrack.com',1,'snurmwh4ti8ko80oso8044gs8448kc4','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:10:59',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ALEX',NULL,'JORDAN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8603790319','1951-01-01','o/XbnU2gkSeBfX3iXG5j6Snq3cGSPl/Akn5yqDFakus=',1,'EF7C3G32UD',NULL,1,1,'test',1,'passed',1,0,0,'2014-02-23 16:10:59','2014-04-07 16:10:59','tenant'),(45,NULL,'ola@rentrack.com','ola@rentrack.com','ola@rentrack.com','ola@rentrack.com',1,'ouo4xcrofdw48gok0kwco040gg48kc0','1a1dc91c907325c69271ddf0c944bc72','2014-04-02 16:10:59',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'OLA','MAE','TAYLOR','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3097458439','1955-09-14','Hzx9ANUNJMkWJpbUxQwrSi446R4oQLIYFMZsZfbrlUo=',1,'EF7C3G33G2',NULL,1,1,'test',0,'passed',NULL,0,0,'2014-02-26 16:10:59','2014-04-07 16:10:59','tenant'),(46,NULL,'emilio1@rentrack.com','emilio1@rentrack.com','emilio1@rentrack.com','emilio1@rentrack.com',1,'3jtikh979x8g8skwwcs8w48w08s8scw','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:10:59',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'BRIAN','P','KURTH','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7188491319','1957-02-19','67I+L2Pl9SvLEvEhw1Ss16sD19o6mj2HYWeMAKFCVi8=',1,'EF7C3G3403',NULL,1,1,'test',1,'passed',1,0,0,'2014-02-28 16:10:59','2014-04-07 16:10:59','tenant'),(47,NULL,'ivan@rentrack.com','ivan@rentrack.com','ivan@rentrack.com','ivan@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ivan','Petrovich','Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'375291181804','1980-10-22','18LW1dmYAbWnogX0Jj9fJlxbu0FJIWAf2gPvBsF8JL8=',1,'EF7C3G34K5',NULL,1,1,'test',1,'none',1,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(48,NULL,'robert@rentrack.com','robert@rentrack.com','robert@rentrack.com','robert@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBERT','SCOTT','BIRMINGHAM','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1957-02-19','hsh/v/SvWEOB7XOEj1Tzuotn0UqChKq41U3n+Ib56Oo=',1,'EF7C3G35IA',NULL,1,1,'test',0,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(49,NULL,'mamazza@rentrack.com','mamazza@rentrack.com','mamazza@rentrack.com','mamazza@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'WILLIAM','N','JOHNSON','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','CjdmjM3h49dVq81ay0Lietv7z7qMTyCY7tnCX4tx48U=',1,'EF7C3G36G3',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(50,NULL,'marion@rentrack.com','marion@rentrack.com','marion@rentrack.com','marion@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MARION','R','BRIEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','nRDcgyOn6wu2mi+qL4NKLfjSWEQ2dajaAlGtAEON2dc=',1,'EF7C3G37DJ',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(51,NULL,'hugo@rentrack.com','hugo@rentrack.com','hugo@rentrack.com','hugo@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'HUGO','WOSBELLY','RODRIGUEZ','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','EXWuftAv8hCgEE4JMQx7VmW57FzQRs0tu/6mioDHVDs=',1,'EF7C3G38FN',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(52,NULL,'miguel@rentrack.com','miguel@rentrack.com','miguel@rentrack.com','miguel@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MIGUEL','M','CENTENO','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','Y7aS5ZM2AaiwP0UJcni+Vg3Hw2uDia0iT1KShhzwHRQ=',1,'EF7C3G39AE',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(53,NULL,'connie@rentrack.com','connie@rentrack.com','connie@rentrack.com','connie@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'CONNIE','S','WEBSTER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1941-01-01','lQeEBCdr8yXWIHdflkrmC9PNA7TtL+ANyf8wCBNCqoo=',0,'77777TEST',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(54,NULL,'lory@rentrack.com','lory@rentrack.com','lory@rentrack.com','lory@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LORY','M','STEFFANS','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1962-09-19','380NKJ9S3Ad4y2DAVJxDDZjKPMd90SHj9TV+ADyjG8c=',1,'EF7C3G3AG8',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(55,NULL,'mathew@rentrack.com','mathew@rentrack.com','mathew@rentrack.com','mathew@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MATHEW','J','DOYLE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','1933-09-02','5AvE47PM0XMz9zRixW6qpQJtMi+ZccgLQblTbSzvke0=',1,'EF7C3G3B17',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(56,NULL,'anthony@rentrack.com','anthony@rentrack.com','anthony@rentrack.com','anthony@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ANTHONY','D','DELLISANTI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'4105367237','1956-08-09','Yk1atQG018s86si+DpItfafiuiY+TpGYVavoF6bLkK4=',1,'EF7C3G3BQK',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(57,NULL,'linda@rentrack.com','linda@rentrack.com','linda@rentrack.com','linda@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LINDA','A','LEMOINE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','2014-04-07','eUt9MpW59ta4Ea9irGF+GpAXklHBBAiGRWKjK/rszl0=',1,'EF7C3G3CDP',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(58,NULL,'thomas@rentrack.com','thomas@rentrack.com','thomas@rentrack.com','thomas@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'THOMAS','DENNIS','LOPES','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','2014-04-07','wJVJE2Boh5UkGehexbiRqmMlklpTQgoo2w6o9vyC+Z4=',1,'EF7C3G3CYM',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(59,NULL,'robyn@rentrack.com','robyn@rentrack.com','robyn@rentrack.com','robyn@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBYN','L','PIPER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7034910325','1968-04-07','hGDZNda+L9PJPWEu2gMRf7hn5Xy1D8VdeP9BpyJ2V7s=',1,'EF7C3G3DJL',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(60,NULL,'laurieann@rentrack.com','laurieann@rentrack.com','laurieann@rentrack.com','laurieann@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LAURIEANN','KATHLEEN','RADLEIN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','1968-02-14','WO0EKHQP12xEv4rwUOXt0pFf7KXJQ3/awl4T7fjIoLI=',1,'EF7C3G3E44',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(61,NULL,'invite@rentrack.com','invite@rentrack.com','invite@rentrack.com','invite@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','2014-04-07','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'TESTFULL_RJ',NULL,1,1,'test',1,'failed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(62,NULL,'roger@rentrack.com','roger@rentrack.com','roger@rentrack.com','roger@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROGER','D','STANLEY','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'6165311574','1949-12-09','xPsjgwUzrkxk6ShvMYn9a533cjNOGHIVl2js0SVxPGA=',1,'EF7C3G3F7E',NULL,1,1,'test',1,'passed',NULL,0,0,'2014-04-02 16:10:59','2014-04-07 16:10:59','tenant'),(63,5,'landlord1@example.com','landlord1@example.com','landlord1@example.com','landlord1@example.com',1,'phndae4h8r48s88wkckwogg0g0c0g0c','1a1dc91c907325c69271ddf0c944bc72','2014-04-07 15:11:00',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'TIMOTHY','A','APPLEGATE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7858655392',NULL,'urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'EF7C3GHPM3',NULL,1,1,'test',1,'passed',NULL,0,1,'2014-02-16 16:11:00','2014-04-07 16:11:00','landlord'),(64,6,'landlord2@example.com','landlord2@example.com','landlord2@example.com','landlord2@example.com',1,'62189y64b4sgkg844ccc4840s88sscc','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:11:00',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',0,'EF7C3GHQCK',NULL,1,1,'test',1,'none',1,0,1,'2014-02-21 16:11:00','2014-04-07 16:11:00','landlord'),(65,6,'landlord3@example.com','landlord3@example.com','landlord3@example.com','landlord3@example.com',1,'qjwqurd7n5csck4ogosscw8sogw8w8c','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:11:00',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ALEX',NULL,'JORDAN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8603790319','1951-01-01','o/XbnU2gkSeBfX3iXG5j6Snq3cGSPl/Akn5yqDFakus=',1,'EF7C3GHQYA',NULL,1,1,'en',1,'passed',1,0,0,'2014-02-23 16:11:00','2014-04-07 16:11:00','landlord'),(66,7,'landlord4@example.com','landlord4@example.com','landlord4@example.com','landlord4@example.com',1,'g2thv5v8k08448ocg8w08ogo84sc4cc','1a1dc91c907325c69271ddf0c944bc72','2014-04-02 16:11:00',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'OLA','MAE','TAYLOR','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3097458439','1955-09-14','Hzx9ANUNJMkWJpbUxQwrSi446R4oQLIYFMZsZfbrlUo=',1,'EF7C3GHRKM',NULL,1,1,'en',0,'passed',NULL,0,1,'2014-02-26 16:11:00','2014-04-07 16:11:00','landlord'),(67,8,'landlord5@example.com','landlord5@example.com','landlord5@example.com','landlord5@example.com',1,'f90a8yq99qo80wskggc4gwos4g8skgk','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:11:00',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'BRIAN','P','KURTH','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7188491319','1957-02-19','67I+L2Pl9SvLEvEhw1Ss16sD19o6mj2HYWeMAKFCVi8=',1,'EF7C3GHS68',NULL,1,1,'en',1,'passed',1,0,1,'2014-02-28 16:11:00','2014-04-07 16:11:00','landlord'),(68,6,'agent1@example.com','agent1@example.com','agent1@example.com','agent1@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:11:00',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Agent',NULL,'Test','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','2014-04-07','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EF7C3GHSSC',NULL,0,0,'test',0,'passed',NULL,0,0,'2014-02-16 16:11:00','2014-04-07 16:11:00','landlord'),(69,6,'landlord6@example.com','landlord6@example.com','landlord6@example.com','landlord6@example.com',1,'glm89tad8nk8w8g4g4c084g4o4c8ckg','1a1dc91c907325c69271ddf0c944bc72','2014-04-06 16:11:00',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EF7C3GHTDQ',NULL,1,1,'test',1,'none',1,0,1,'2014-02-21 16:11:00','2014-04-07 16:11:00','landlord');
/*!40000 ALTER TABLE `cj_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cj_vehicle`
--

DROP TABLE IF EXISTS `cj_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cj_vehicle` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cj_applicant_id` bigint(20) NOT NULL,
  `make` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` bigint(20) DEFAULT NULL,
  `trade_in` tinyint(1) DEFAULT NULL,
  `down_payment` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1AFD06AD1846CDE5` (`cj_applicant_id`),
  CONSTRAINT `FK_1AFD06AD1846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_vehicle`
--

LOCK TABLES `cj_vehicle` WRITE;
/*!40000 ALTER TABLE `cj_vehicle` DISABLE KEYS */;
INSERT INTO `cj_vehicle` VALUES (1,17,'Honda','Civic',1,0,NULL,'2014-04-02 16:10:58','2014-04-02 16:10:58'),(2,18,'BMW','X5',0,0,NULL,'2014-04-02 16:10:58','2014-04-02 16:10:58'),(3,19,'BMW','X5',0,0,NULL,'2014-04-02 16:10:58','2014-04-02 16:10:58'),(4,20,'BMW','X5',0,0,NULL,'2014-04-02 16:10:58','2014-04-02 16:10:58'),(5,21,'BMW','X5',0,0,NULL,'2014-04-02 16:10:58','2014-04-02 16:10:58'),(6,23,'Honda','CR-V',0,0,NULL,'2014-04-02 16:10:58','2014-04-02 16:10:58');
/*!40000 ALTER TABLE `cj_vehicle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `random_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uris` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `secret` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allowed_grant_types` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` VALUES (1,'qvxzb7ge734ko4ogwcskwksogoc0wskws40gg8oocokwg404s','a:0:{}','39uyn651qlk4ssws40sgs44cwsskgccoc0o04ccgsccgooowwo','a:2:{i:0;s:13:\"refresh_token\";i:1;s:8:\"password\";}');
/*!40000 ALTER TABLE `client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email`
--

DROP TABLE IF EXISTS `email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E7927C745E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email`
--

LOCK TABLES `email` WRITE;
/*!40000 ALTER TABLE `email` DISABLE KEYS */;
INSERT INTO `email` VALUES (1,'invite.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(2,'welcome.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(3,'score.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(4,'target.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(5,'finished.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(6,'password.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(7,'example.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(8,'resetting.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(9,'check.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(10,'receipt.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(11,'rjCheck.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(12,'rjLandLordInvite.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(13,'rjTenantInvite.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(14,'rjTenantLatePayment.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(15,'rjLandlordComeFromInvite.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(16,'rjPendingContract.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(17,'exist_invite.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(18,'rjTodayPayments.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(19,'rjTodayNotPaid.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(20,'rjDailyReport.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(21,'rjTenantLateContract.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(22,'rjPaymentDue.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(23,'rjListLateContracts.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(24,'rjOrderReceipt.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(25,'rjOrderError.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(26,'rjTenantInviteReminder.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(27,'rjTenantInviteReminderPayment.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(28,'rjContractApproved.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(29,'rjContractRemovedFromDbByLandlord.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(30,'rjContractRemovedFromDbByTenant.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(31,'rjMerchantNameSetuped.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(32,'rj_resetting.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(33,'rjEndContract.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(34,'rjOrderCancel.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(35,'rjOrderCancelToLandlord.html','2014-04-07 16:10:58','2014-04-07 16:10:58'),(36,'rjPendingOrder.html','2014-04-07 16:10:58','2014-04-07 16:10:58');
/*!40000 ALTER TABLE `email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_translation`
--

DROP TABLE IF EXISTS `email_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translatable_id` int(11) DEFAULT NULL,
  `locale` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `property` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lookup_unique_idx` (`locale`,`translatable_id`,`property`),
  KEY `IDX_A2A939D82C2AC5D3` (`translatable_id`),
  KEY `lookup_idx` (`locale`,`translatable_id`),
  CONSTRAINT `FK_A2A939D82C2AC5D3` FOREIGN KEY (`translatable_id`) REFERENCES `email` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_translation`
--

LOCK TABLES `email_translation` WRITE;
/*!40000 ALTER TABLE `email_translation` DISABLE KEYS */;
INSERT INTO `email_translation` VALUES (1,1,'test','subject','Welcome to Credit Jeeves'),(2,1,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to Credit Jeeves{% endblock %}\n{% block email %}\n      <p>\n          {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n          The Credit Jeeves program shows you your current credit score, a summary of your credit profile, and a customized action plan to help\n          you reach your target score. We then monitor your progress over the next few months to let you know when you are likely qualified for a loan.\n      </p>\n      <p>\n          Enrollment is free, simple, and takes less than a minute. Credit Jeeves will not negatively impact your credit and does not post a\n          \'hard inquiry.\'\n      </p>\n      <p>\n          Set up your Credit Jeeves Account now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a> and take the first step towards better financing.\n      </p>\n      <p>\n          You will be able to:\n          * See and monitor your current credit score.\n          * Follow easy-to-understand actions to optimize your score for your goals.\n          * See a summary of your credit file and learn more about how this information affects your score.\n          * Receive alerts when you reach your target score.\n      </p>\n      <br />\n      <p>\n        Tip: Do not shop around for a loan right now. This will create multiple \'hard inquiries\' on your credit file which can negatively\n        impact your score. Credit Jeeves makes a \'soft inquiry\' and will allow you to view your score and action plan without hurting your\n        chances to requalify for a loan in the future.\n      </p>\n      <p>\n          Again, {{ groupName }} is providing you this service for free.\n      </p>\n      <p>\n      Sign Up Now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a>\n      </p>\n{% endblock %}'),(3,2,'test','subject','Welcome to Credit Jeeves'),(4,2,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to CreditJeeves{% endblock %}{% block email %}<p>You have taken the first step towards your new car.</p><p>To see your customized action plan, sign in at <a href=\"http://my.creditjeeves.com/\">cj</a> anytime.</p><strong>Get started today:</strong><ul>  <li>Understand<a href=\"http://www.creditjeeves.com/educate/understand-your-credit-score\">how your credit score is determined</a></li><li>Review your <a href=\"http://cj/_dev.php/?\">action plan</a> and decide what step you will take first.</li><li>Click on the \"learn more\" link next to that step to find out what to do.</li></ul><i>Trouble answering the verification questions?</i><p>It is a good idea to get a <a href=\"https://www.annualcreditreport.com/\"> free copy of your credit report </a> to see if contains something you do not recognize. You can also contact <a href=\"mailto:help@creditjeeves.com\">help@creditjeeves.com</a> if your account becomes locked. </p><i>We want to hear from you!</i><p>Please <a href=\"http://creditjeeves.uservoice.com/\">send us your feedback</a> on how we can make the product better for you.</p>{% endblock %}'),(5,3,'test','subject','Your Credit Score has Changed - Log Into Credit Jeeves'),(6,3,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}'),(7,4,'test','subject','Your New Car Awaits - Log into Credit Jeeves'),(8,4,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Congratulations!{% endblock %}\n{% block email %}\n  <div mc:edit=\"std_content00\">\n      You have reached your dealer\'s target score of <strong>{{ targetScore }}</strong>\n  </div>\n  <div mc:edit=\"latest_score_button\">\n      <br />\n      <hr />\n      Log into Credit Jeeves to find out what to do next. Your new car awaits!\n      <br />\n      <a class=\"button\" href=\"{{ loginLink }}\" id=\"viewLatestScoreButton\">View Latest Score</a>\n      <br />\n      <hr />\n  </div>\n{% endblock %}\n'),(9,5,'test','subject','One of your leads has reached the Target Score'),(10,5,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}'),(11,6,'test','subject','One of your leads has reached the Target Score'),(12,6,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}'),(13,7,'test','subject','Example email with all avaliable fields'),(14,7,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Heading 1{% endblock %}{% block h2 %}Heading 2{% endblock %}{% block h3 %}Heading 3{% endblock %}{% block h4 %}Heading 4{% endblock %}{% block email %}{% set button = {\"text\": \"Hmm, we could add more than one button in the email body!\",\"value\": \"Test\",\"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}<p>Lorem ipsum...</p>{% set button = {\"text\": \"Some text above button\", \"value\": \"Click It\", \"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}{% endblock %}'),(15,8,'test','subject','Reset Password'),(16,8,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  CreditJeeves will never e-mail you and ask you to disclose or verify your CreditJeeves.com password, credit card, or banking account number.\n\n  Thank you for using CreditJeeves!\n{% endblock %}\n'),(17,9,'test','subject','Check Email'),(18,9,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Credit Jeeves account is almost ready!{% endblock %}\n{% block email %}Hello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n'),(19,10,'test','subject','Receipt from Credit Jeeves'),(20,10,'test','body','<div mc:edit=\"std_content00\">\n<h1 class=\"h1\">Receipt from Credit Jeeves</h1>\nThank you for purchasing your credit report through Credit Jeeves.\nYour payment was processed successfully and will appear on your next statement under CREDITJEEVE.\nHere is your receipt:<br />\n&nbsp;<br />\n<hr />\nPayment Date & Time:&nbsp;{{ date }}<br />\nPayment Amount: {{ amout }}<br />\nReference Number: {{ number }}<br />\n<br />\n<hr />\nRemember, we\'re here to help,<br /><strong>The Credit Jeeves Team</strong>\n</div>\n'),(21,11,'test','subject','Get Started with RentTrack'),(22,11,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your RentTrack account is almost ready!{% endblock %}\n{% block email %}\nHello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n'),(23,12,'test','subject','Your Tenant is Ready to Pay Rent through RentTrack'),(24,12,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Get Paid Fast Using RentTrack{% endblock %}\n{% block email %}\n  {% if nameLandlord %}\n      Hi {{ nameLandlord }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br /> \n  {% endif %}\n  Your tenant, {{ fullNameTenant }}, would like to use RentTrack to pay rent on your property at\n  {{ address }} {{ unitName }}. RentTrack allows {{ nameTenant }} to build credit history by\n  reporting on-time payments to credit bureaus. <br /> <br />\n\n  As a landlord, you benefit because RentTrack facilitates easy payments through secure electronic\n  check transfers and credit cards - payments are deposited faster and directly to your account.\n  Reminders are sent automatically to your tenants before rent is due and late notices are sent\n  to you immediately. If you have multiple properties, you can see the status of your payments\n  all in one place. To top it off, your tenant has an additional incentive to pay\n  on time each month.<br /> <br />\n\n  Ready to get paid? <br /> <br />\n  <a id=\"payRentLinkLandlord\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'landlord_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n            style=\"border: none;\n            padding: 2px 7px;\n            text-align: left;\n            color: white;\n            font-size: 14px;\n            text-shadow: 1px 1px 3px #636363;\n            filter: dropshadow(color=#636363, offx=1, offy=1);\n            cursor: pointer;\n            background-color: #669900;\n            -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n            zoom: 1;\n            text-decoration: none;\n            -moz-border-radius: 4px;\n            -webkit-border-radius: 4px;\n            border-radius: 4px;\"\n>Sign up</a> Still have some questions? <a href=\"http://www.renttrack.com/property-management\">Read More</a> or call 866.841.9090\n{% endblock %}\n'),(25,13,'test','subject','Your Landlord is Requesting Rent Payment through RentTrack'),(26,13,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n'),(27,14,'test','subject','Your Rent Payment is Late'),(28,14,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Late. Pay Now!{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Dear {{ nameTenant }}, <br />  <br />\n  {% else %}\n      Heads Up!<br /> <br />\n  {% endif %}\n  It looks like {{ fullNameLandlord }} expected your rent payment for {{ address }} {{ unitName }} already.\n\n  <a href=\"http://my.renttrack.com/\">Log in to RentTrack today</a> and and make an immediate payment. We\'ll\n  let {{ fullNameLandlord }} that rent is on its way once the payment goes through.\n\n  Better yet, you can set up automatic payments so you never miss one again. <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">Learn More</a>\n\n  Watching out for you,\n  The RentTrack Team\n{% endblock %}\n'),(29,15,'test','subject','Your Landlord Joined RentTrack'),(30,15,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n  Congratulations! {{ fullNameLandlord }} has teamed up with RentTrack.\n  <br /><br />\n  We\'re now working with them to ready their account to accept payments. You\'ll receive another email when you\'re\n  approved to pay rent online.\n  <br /><br />\n  Thank you for your patience!\n{% endblock %}\n'),(31,16,'test','subject','Your Tenant Needs Approval'),(32,16,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  {{ nameTenant }} is ready to pay rent for {{ address }}\n  <br /></br />\n  Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a>, click on the Tenants tab, and click on the\n  review \"eye\" next to the pending tenant. You will then be able to add rent details and approve the tenant. Once\n  this is complete, your tenant will be able to set up their rent payment.\n{% endblock %}\n'),(33,17,'test','subject','Your have new dealer!'),(34,17,'test','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your have new dealer!{% endblock %}\n{% block email %}\n    <p>\n        {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n    </p>\n    <p>\n        Again, {{ groupName }} is providing you this service for free.\n    </p>\n{% endblock %}\n'),(35,18,'test','subject','Rent Payments Today'),(36,18,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Collected{% endblock %}\n{% block email %}\n  Hi {{ nameLandlord }},\n  <br /><br />\n  We collected ${{ amount }} in rent today. To see your recent payments,\n  <a href=\"https://my.renttrack.com/\">log into RentTrack</a> and click on Dashboard.\n  <br /><br />\n  Payments typically settle in 1-3 days to your account. If you suspect a payment is not transferring, or have\n  any other questions, please contact us at help@creditjeeves.com or call 866-841-9090.\n{% endblock %}\n'),(37,19,'test','subject','Not Paid Today.'),(38,19,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  Today not paid is {{ amount }}\n  <br />\n  <br />\n  Enjoy, <br />\n  The RentTrack Team\n{% endblock %}\n'),(39,20,'test','subject','RentTrack Daily Report'),(40,20,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Status</th>\n      <th style=\"padding:5px;\">Amount</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for key, value in report %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ key }}</td>\n      <td style=\"padding:5px;\">\n      {% if value > 0 %}\n        ${{ value }}\n      {% else %}\n      ---\n      {% endif %}\n      </td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n{% endblock %}\n'),(41,21,'test','subject','Rent Payment is Late'),(42,21,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }},{% endblock %}\n{% block email %}\n  It looks like your rent payment for {{ address }} is <b>late by {{ diff }} day(s)</b>.\n  <br /><br />\n  <a href=\"https://my.renttrack.com/\">Log into RentTrack</a> today to make a new payment. We\'d recommend setting up\n  <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">automatic payments</a>\n  so you won\'t see an email like this next month.\n  <br /><br />\n  If you have alread paid by a different method like cash or (*gasp*) paper check, then your landlord needs\n  to log into RentTrack and update your records. They have also received an email reminder regarding this payment.\n  <br /><br />\n  If you need assistance, please email help@renttrack.com or call (866) 841-9090.\n{% endblock %}\n'),(43,22,'test','subject','Your Rent Is Due'),(44,22,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Is Due{% endblock %}\n{% block email %}\nYour rent payment to {{ nameHolding }} for {{ address }} is coming up.\n<br /><br />\n{% if recurring == true %}\n  It looks like you have recurring payments set up, so we\'ll send you another email when we make your payment.\n  If you need to change your payment details or cancel your payment,\n  please <a href=\"https://my.renttrack.com/\">log in to RentTrack today</a> and make any adjustments.\n{% else %}\n  You do not have recurring payments set up. <a href=\"https://my.renttrack.com/\">Log in to RentTrack today</a>\n  to set up a one-time or recurring payment.\n{% endif %}\n{% endblock %}\n'),(45,23,'test','subject','Review Late Rent Payments'),(46,23,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ nameLandlord }},{% endblock %}\n{% block email %}\nThe following tenants have not submitted on-time payments:\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Tenant</th>\n      <th style=\"padding:5px;\">Email</th>\n      <th style=\"padding:5px;\">Address</th>\n      <th style=\"padding:5px;\">Days Late</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for tenant in tenants %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ tenant.name }}</td>\n      <td style=\"padding:5px;\">{{ tenant.email }}</td>\n      <td style=\"padding:5px;\">{{ tenant.address }}</td>\n      <td style=\"padding:5px;\">{{ tenant.late }}</td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n  <br />\n  Please <a href=\"https://my.renttrack.com\">log into RentTrack</a>\n  and click on \"Resolve\" next to late tenants at the top of the Payments Dashboard to either record payments\n  via alternate means or to send them an email reminder.\n{% endblock %}\n'),(47,24,'test','subject','Rent Payment Receipt'),(48,24,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Paid{% endblock %}\n{% block email %}\n{% if nameTenant %}\n  Hi {{ nameTenant }}! <br /><br />\n{% else %}\n  Hello!  <br /><br />\n{% endif %}\n\nYour rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n'),(49,25,'test','subject','Order Error'),(50,25,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n{{ \'order.error.title\'| trans }}.\n<br /><br />\n{{ \'order.error.message\' | trans }}: {{ error }}\n<br /><br />\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.paid.to\' | trans }}:</td><td style=\"padding:5px;\">{{ groupName }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.id\' | trans }}:</td><td style=\"padding:5px;\">{{ orderId }}</td>\n    </tr>\n    {% if transactionId > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionId }}</td>\n    </tr>\n    {% endif %}\n  </tbody>\n</table>\n{{ \'order.contact.us\' | trans }}\n{% endblock %}\n'),(51,26,'test','subject','Reminder. Your Landlord is Requesting Rent Payment through RentTrack'),(52,26,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n'),(53,27,'test','subject','Reminder. Your Landlord ask to install your payment'),(54,27,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n    href=\"http://{{ serverName }}/\"\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n'),(55,28,'test','subject','You\'re Approved to Pay Rent Online'),(56,28,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}You\'re Approved!{% endblock %}\n{% block email %}\nHello {{ nameTenant }},\n\nYour landlord has approved you and you can now set up your rent payment. Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a> and click on the \"Pay\" button corresponding to your rental.\n{% endblock %}\n'),(57,29,'test','subject','You Contract was Removed by Your Landlord'),(58,29,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameTenant }},{% endblock %}\n{% block email %}\n  Your landlord, {{ fullNameLandlord }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\n<br /><br />\nIf this is an error, please contact your landlord.\n{% endblock %}'),(59,30,'test','subject','Your Contract was Removed by Your Tenant'),(60,30,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your tenant, {{ fullNameTenant }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\nIf this is an error, please contact your tenant.\n{% endblock %}\n'),(61,31,'test','subject','Your RentTrack Merchant Account is Ready!'),(62,31,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your merchant account for \"{{ groupName }}\" is approved and ready!\n  <br /><br />\n\n  You can now accept rent payments online, and funds will be deposited into the account\n  you specified in your application. Begin by\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/285491-how-do-i-add-or-invite-a-tenant-\">inviting your tenants</a>, or\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/275851-how-do-i-approve-a-tenant-so-they-can-pay-rent-\">approving any pending tenants</a>\n  that invited you.\n{% endblock %}\n'),(63,32,'test','subject','Reset Password'),(64,32,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  Didn\'t request this change?\n  If you didn\'t request a new password, please contact us at <a mailto=\"help@renttrack.com\">help@renttrack.com</a>.\n\n  RentTrack will never e-mail you and ask you to disclose or verify your RentTrack.com password, credit card, or banking account number.\n\n  Thank you for using RentTrack!\n{% endblock %}'),(65,33,'test','subject','End Contract'),(66,33,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ tenantFullName }}!{% endblock %}\n{% block email %}\n   Your landlord {{landlordFullName}}, has ended contract by address: {{ address }} #{{ unitName }}.\n   {% if uncollectedBalance > 0%}\n      And you have uncollected balance on this contract {{ uncollectedBalance }}$.\n   {% else %}\n\n   {% endif %}\n{% endblock %}\n'),(67,34,'test','subject','Your Rent Payment was Reversed'),(68,34,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Dear {{ tenantFullName }},{% endblock %}\n{% block email %}\n  {% if orderStatus == \'refunded\' %}\n  Per your request, your rent of {{ rentAmount }} sent on {{ orderDate }} was refunded and should appear in your account within a few days.\n  {% elseif orderStatus == \'cancelled\' %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was cancelled.\n  {% else %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was returned. Your rent is currently not paid.\n  You will receive a follow up from RentTrack customer support with the reason for return and ways to fix it.\n  {% endif %}\n  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n'),(69,35,'test','subject','Your Rent Payment was Reversed'),(70,35,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Dear {{ landlordFirstName }},{% endblock %}\n{% block email %}\n  {% if orderStatus == \'refunded\' %}\n  Per your tenant\\\'s request, their rent of {{ rentAmount }} sent on {{ orderDate }} was refunded\n  and will be deducted from your account within a couple of days. Please contact your tenant\n  if you have any questions regarding this refund.\n  {% elseif orderStatus == \'cancelled\' %}\n  Per your your tenant\\\'s request, their rent payment of {{ rentAmount }} sent on {{ orderDate }}\n  was cancelled. You will not see a deposit in your account since it was cancelled before\n  payment settlement. Please contact your tenant if you have any questions regarding this cancellation.\n  {% else %}\n  Your tenant\\\'s payment of {{ rentAmount }} sent on {{ orderDate }} was returned. This amount\n  has been deducted from your account per the RentTrack terms of service. Your rent is currently not paid.\n  Please contact your tenant if to arrange another payment.\n\n\n  RentTrack Customer Support will also reach out to your tenant to see if their payment source information\n  can be corrected.\n  {% endif %}\n  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n'),(71,36,'test','subject','Your Rent is Processing'),(72,36,'test','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Processing{% endblock %}\n{% block email %}\n  Hi {{ tenantName }}! <br /><br />\n\n  Your rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ orderTime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n\n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n');
/*!40000 ALTER TABLE `email_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ext_log_entries`
--

DROP TABLE IF EXISTS `ext_log_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ext_log_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `logged_at` datetime NOT NULL,
  `object_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `object_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` int(11) NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_class_lookup_idx` (`object_class`),
  KEY `log_date_lookup_idx` (`logged_at`),
  KEY `log_user_lookup_idx` (`username`),
  KEY `log_version_lookup_idx` (`object_id`,`object_class`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ext_log_entries`
--

LOCK TABLES `ext_log_entries` WRITE;
/*!40000 ALTER TABLE `ext_log_entries` DISABLE KEYS */;
INSERT INTO `ext_log_entries` VALUES (1,'create','2014-04-07 16:10:58','1','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:11:\"invite.html\";}',NULL),(2,'create','2014-04-07 16:10:58','2','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"welcome.html\";}',NULL),(3,'create','2014-04-07 16:10:58','3','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:10:\"score.html\";}',NULL),(4,'create','2014-04-07 16:10:58','4','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:11:\"target.html\";}',NULL),(5,'create','2014-04-07 16:10:58','5','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:13:\"finished.html\";}',NULL),(6,'create','2014-04-07 16:10:58','6','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:13:\"password.html\";}',NULL),(7,'create','2014-04-07 16:10:58','7','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"example.html\";}',NULL),(8,'create','2014-04-07 16:10:58','8','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:14:\"resetting.html\";}',NULL),(9,'create','2014-04-07 16:10:58','9','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:10:\"check.html\";}',NULL),(10,'create','2014-04-07 16:10:58','10','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"receipt.html\";}',NULL),(11,'create','2014-04-07 16:10:58','11','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"rjCheck.html\";}',NULL),(12,'create','2014-04-07 16:10:58','12','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:21:\"rjLandLordInvite.html\";}',NULL),(13,'create','2014-04-07 16:10:58','13','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjTenantInvite.html\";}',NULL),(14,'create','2014-04-07 16:10:58','14','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:24:\"rjTenantLatePayment.html\";}',NULL),(15,'create','2014-04-07 16:10:58','15','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:29:\"rjLandlordComeFromInvite.html\";}',NULL),(16,'create','2014-04-07 16:10:58','16','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:22:\"rjPendingContract.html\";}',NULL),(17,'create','2014-04-07 16:10:58','17','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"exist_invite.html\";}',NULL),(18,'create','2014-04-07 16:10:58','18','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:20:\"rjTodayPayments.html\";}',NULL),(19,'create','2014-04-07 16:10:58','19','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjTodayNotPaid.html\";}',NULL),(20,'create','2014-04-07 16:10:58','20','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:18:\"rjDailyReport.html\";}',NULL),(21,'create','2014-04-07 16:10:58','21','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:25:\"rjTenantLateContract.html\";}',NULL),(22,'create','2014-04-07 16:10:58','22','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"rjPaymentDue.html\";}',NULL),(23,'create','2014-04-07 16:10:58','23','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:24:\"rjListLateContracts.html\";}',NULL),(24,'create','2014-04-07 16:10:58','24','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjOrderReceipt.html\";}',NULL),(25,'create','2014-04-07 16:10:58','25','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"rjOrderError.html\";}',NULL),(26,'create','2014-04-07 16:10:58','26','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:27:\"rjTenantInviteReminder.html\";}',NULL),(27,'create','2014-04-07 16:10:58','27','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:34:\"rjTenantInviteReminderPayment.html\";}',NULL),(28,'create','2014-04-07 16:10:58','28','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:23:\"rjContractApproved.html\";}',NULL),(29,'create','2014-04-07 16:10:58','29','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:38:\"rjContractRemovedFromDbByLandlord.html\";}',NULL),(30,'create','2014-04-07 16:10:58','30','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:36:\"rjContractRemovedFromDbByTenant.html\";}',NULL),(31,'create','2014-04-07 16:10:58','31','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:26:\"rjMerchantNameSetuped.html\";}',NULL),(32,'create','2014-04-07 16:10:58','32','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"rj_resetting.html\";}',NULL),(33,'create','2014-04-07 16:10:58','33','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:18:\"rjEndContract.html\";}',NULL),(34,'create','2014-04-07 16:10:58','34','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:18:\"rjOrderCancel.html\";}',NULL),(35,'create','2014-04-07 16:10:58','35','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:28:\"rjOrderCancelToLandlord.html\";}',NULL),(36,'create','2014-04-07 16:10:58','36','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjPendingOrder.html\";}',NULL),(37,'create','2014-04-07 16:10:59','1','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:24:\"Welcome to Credit Jeeves\";}',NULL),(38,'create','2014-04-07 16:10:59','2','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1853:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to Credit Jeeves{% endblock %}\n{% block email %}\n      <p>\n          {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n          The Credit Jeeves program shows you your current credit score, a summary of your credit profile, and a customized action plan to help\n          you reach your target score. We then monitor your progress over the next few months to let you know when you are likely qualified for a loan.\n      </p>\n      <p>\n          Enrollment is free, simple, and takes less than a minute. Credit Jeeves will not negatively impact your credit and does not post a\n          \'hard inquiry.\'\n      </p>\n      <p>\n          Set up your Credit Jeeves Account now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a> and take the first step towards better financing.\n      </p>\n      <p>\n          You will be able to:\n          * See and monitor your current credit score.\n          * Follow easy-to-understand actions to optimize your score for your goals.\n          * See a summary of your credit file and learn more about how this information affects your score.\n          * Receive alerts when you reach your target score.\n      </p>\n      <br />\n      <p>\n        Tip: Do not shop around for a loan right now. This will create multiple \'hard inquiries\' on your credit file which can negatively\n        impact your score. Credit Jeeves makes a \'soft inquiry\' and will allow you to view your score and action plan without hurting your\n        chances to requalify for a loan in the future.\n      </p>\n      <p>\n          Again, {{ groupName }} is providing you this service for free.\n      </p>\n      <p>\n      Sign Up Now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a>\n      </p>\n{% endblock %}\";}',NULL),(39,'create','2014-04-07 16:10:59','3','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:24:\"Welcome to Credit Jeeves\";}',NULL),(40,'create','2014-04-07 16:10:59','4','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1166:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to CreditJeeves{% endblock %}{% block email %}<p>You have taken the first step towards your new car.</p><p>To see your customized action plan, sign in at <a href=\"http://my.creditjeeves.com/\">cj</a> anytime.</p><strong>Get started today:</strong><ul>  <li>Understand<a href=\"http://www.creditjeeves.com/educate/understand-your-credit-score\">how your credit score is determined</a></li><li>Review your <a href=\"http://cj/_dev.php/?\">action plan</a> and decide what step you will take first.</li><li>Click on the \"learn more\" link next to that step to find out what to do.</li></ul><i>Trouble answering the verification questions?</i><p>It is a good idea to get a <a href=\"https://www.annualcreditreport.com/\"> free copy of your credit report </a> to see if contains something you do not recognize. You can also contact <a href=\"mailto:help@creditjeeves.com\">help@creditjeeves.com</a> if your account becomes locked. </p><i>We want to hear from you!</i><p>Please <a href=\"http://creditjeeves.uservoice.com/\">send us your feedback</a> on how we can make the product better for you.</p>{% endblock %}\";}',NULL),(41,'create','2014-04-07 16:10:59','5','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:54:\"Your Credit Score has Changed - Log Into Credit Jeeves\";}',NULL),(42,'create','2014-04-07 16:10:59','6','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:48:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\";}',NULL),(43,'create','2014-04-07 16:10:59','7','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:44:\"Your New Car Awaits - Log into Credit Jeeves\";}',NULL),(44,'create','2014-04-07 16:10:59','8','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:543:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Congratulations!{% endblock %}\n{% block email %}\n  <div mc:edit=\"std_content00\">\n      You have reached your dealer\'s target score of <strong>{{ targetScore }}</strong>\n  </div>\n  <div mc:edit=\"latest_score_button\">\n      <br />\n      <hr />\n      Log into Credit Jeeves to find out what to do next. Your new car awaits!\n      <br />\n      <a class=\"button\" href=\"{{ loginLink }}\" id=\"viewLatestScoreButton\">View Latest Score</a>\n      <br />\n      <hr />\n  </div>\n{% endblock %}\n\";}',NULL),(45,'create','2014-04-07 16:10:59','9','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:46:\"One of your leads has reached the Target Score\";}',NULL),(46,'create','2014-04-07 16:10:59','10','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:48:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\";}',NULL),(47,'create','2014-04-07 16:10:59','11','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:46:\"One of your leads has reached the Target Score\";}',NULL),(48,'create','2014-04-07 16:10:59','12','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:48:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\";}',NULL),(49,'create','2014-04-07 16:10:59','13','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:39:\"Example email with all avaliable fields\";}',NULL),(50,'create','2014-04-07 16:10:59','14','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:575:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Heading 1{% endblock %}{% block h2 %}Heading 2{% endblock %}{% block h3 %}Heading 3{% endblock %}{% block h4 %}Heading 4{% endblock %}{% block email %}{% set button = {\"text\": \"Hmm, we could add more than one button in the email body!\",\"value\": \"Test\",\"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}<p>Lorem ipsum...</p>{% set button = {\"text\": \"Some text above button\", \"value\": \"Click It\", \"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}{% endblock %}\";}',NULL),(51,'create','2014-04-07 16:10:59','15','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:14:\"Reset Password\";}',NULL),(52,'create','2014-04-07 16:10:59','16','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:435:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  CreditJeeves will never e-mail you and ask you to disclose or verify your CreditJeeves.com password, credit card, or banking account number.\n\n  Thank you for using CreditJeeves!\n{% endblock %}\n\";}',NULL),(53,'create','2014-04-07 16:10:59','17','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:11:\"Check Email\";}',NULL),(54,'create','2014-04-07 16:10:59','18','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:308:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Credit Jeeves account is almost ready!{% endblock %}\n{% block email %}Hello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n\";}',NULL),(55,'create','2014-04-07 16:10:59','19','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Receipt from Credit Jeeves\";}',NULL),(56,'create','2014-04-07 16:10:59','20','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:500:\"<div mc:edit=\"std_content00\">\n<h1 class=\"h1\">Receipt from Credit Jeeves</h1>\nThank you for purchasing your credit report through Credit Jeeves.\nYour payment was processed successfully and will appear on your next statement under CREDITJEEVE.\nHere is your receipt:<br />\n&nbsp;<br />\n<hr />\nPayment Date & Time:&nbsp;{{ date }}<br />\nPayment Amount: {{ amout }}<br />\nReference Number: {{ number }}<br />\n<br />\n<hr />\nRemember, we\'re here to help,<br /><strong>The Credit Jeeves Team</strong>\n</div>\n\";}',NULL),(57,'create','2014-04-07 16:10:59','21','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Get Started with RentTrack\";}',NULL),(58,'create','2014-04-07 16:10:59','22','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:312:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your RentTrack account is almost ready!{% endblock %}\n{% block email %}\nHello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n\";}',NULL),(59,'create','2014-04-07 16:10:59','23','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:50:\"Your Tenant is Ready to Pay Rent through RentTrack\";}',NULL),(60,'create','2014-04-07 16:10:59','24','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:2116:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Get Paid Fast Using RentTrack{% endblock %}\n{% block email %}\n  {% if nameLandlord %}\n      Hi {{ nameLandlord }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br /> \n  {% endif %}\n  Your tenant, {{ fullNameTenant }}, would like to use RentTrack to pay rent on your property at\n  {{ address }} {{ unitName }}. RentTrack allows {{ nameTenant }} to build credit history by\n  reporting on-time payments to credit bureaus. <br /> <br />\n\n  As a landlord, you benefit because RentTrack facilitates easy payments through secure electronic\n  check transfers and credit cards - payments are deposited faster and directly to your account.\n  Reminders are sent automatically to your tenants before rent is due and late notices are sent\n  to you immediately. If you have multiple properties, you can see the status of your payments\n  all in one place. To top it off, your tenant has an additional incentive to pay\n  on time each month.<br /> <br />\n\n  Ready to get paid? <br /> <br />\n  <a id=\"payRentLinkLandlord\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'landlord_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n            style=\"border: none;\n            padding: 2px 7px;\n            text-align: left;\n            color: white;\n            font-size: 14px;\n            text-shadow: 1px 1px 3px #636363;\n            filter: dropshadow(color=#636363, offx=1, offy=1);\n            cursor: pointer;\n            background-color: #669900;\n            -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n            zoom: 1;\n            text-decoration: none;\n            -moz-border-radius: 4px;\n            -webkit-border-radius: 4px;\n            border-radius: 4px;\"\n>Sign up</a> Still have some questions? <a href=\"http://www.renttrack.com/property-management\">Read More</a> or call 866.841.9090\n{% endblock %}\n\";}',NULL),(61,'create','2014-04-07 16:10:59','25','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:58:\"Your Landlord is Requesting Rent Payment through RentTrack\";}',NULL),(62,'create','2014-04-07 16:10:59','26','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1884:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n\";}',NULL),(63,'create','2014-04-07 16:10:59','27','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:25:\"Your Rent Payment is Late\";}',NULL),(64,'create','2014-04-07 16:10:59','28','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:815:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Late. Pay Now!{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Dear {{ nameTenant }}, <br />  <br />\n  {% else %}\n      Heads Up!<br /> <br />\n  {% endif %}\n  It looks like {{ fullNameLandlord }} expected your rent payment for {{ address }} {{ unitName }} already.\n\n  <a href=\"http://my.renttrack.com/\">Log in to RentTrack today</a> and and make an immediate payment. We\'ll\n  let {{ fullNameLandlord }} that rent is on its way once the payment goes through.\n\n  Better yet, you can set up automatic payments so you never miss one again. <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">Learn More</a>\n\n  Watching out for you,\n  The RentTrack Team\n{% endblock %}\n\";}',NULL),(65,'create','2014-04-07 16:10:59','29','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:30:\"Your Landlord Joined RentTrack\";}',NULL),(66,'create','2014-04-07 16:10:59','30','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:416:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n  Congratulations! {{ fullNameLandlord }} has teamed up with RentTrack.\n  <br /><br />\n  We\'re now working with them to ready their account to accept payments. You\'ll receive another email when you\'re\n  approved to pay rent online.\n  <br /><br />\n  Thank you for your patience!\n{% endblock %}\n\";}',NULL),(67,'create','2014-04-07 16:10:59','31','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Your Tenant Needs Approval\";}',NULL),(68,'create','2014-04-07 16:10:59','32','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:515:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  {{ nameTenant }} is ready to pay rent for {{ address }}\n  <br /></br />\n  Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a>, click on the Tenants tab, and click on the\n  review \"eye\" next to the pending tenant. You will then be able to add rent details and approve the tenant. Once\n  this is complete, your tenant will be able to set up their rent payment.\n{% endblock %}\n\";}',NULL),(69,'create','2014-04-07 16:10:59','33','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:21:\"Your have new dealer!\";}',NULL),(70,'create','2014-04-07 16:10:59','34','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:369:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your have new dealer!{% endblock %}\n{% block email %}\n    <p>\n        {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n    </p>\n    <p>\n        Again, {{ groupName }} is providing you this service for free.\n    </p>\n{% endblock %}\n\";}',NULL),(71,'create','2014-04-07 16:10:59','35','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:19:\"Rent Payments Today\";}',NULL),(72,'create','2014-04-07 16:10:59','36','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:544:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Collected{% endblock %}\n{% block email %}\n  Hi {{ nameLandlord }},\n  <br /><br />\n  We collected ${{ amount }} in rent today. To see your recent payments,\n  <a href=\"https://my.renttrack.com/\">log into RentTrack</a> and click on Dashboard.\n  <br /><br />\n  Payments typically settle in 1-3 days to your account. If you suspect a payment is not transferring, or have\n  any other questions, please contact us at help@creditjeeves.com or call 866-841-9090.\n{% endblock %}\n\";}',NULL),(73,'create','2014-04-07 16:10:59','37','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:15:\"Not Paid Today.\";}',NULL),(74,'create','2014-04-07 16:10:59','38','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:228:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  Today not paid is {{ amount }}\n  <br />\n  <br />\n  Enjoy, <br />\n  The RentTrack Team\n{% endblock %}\n\";}',NULL),(75,'create','2014-04-07 16:10:59','39','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:22:\"RentTrack Daily Report\";}',NULL),(76,'create','2014-04-07 16:10:59','40','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:756:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Status</th>\n      <th style=\"padding:5px;\">Amount</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for key, value in report %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ key }}</td>\n      <td style=\"padding:5px;\">\n      {% if value > 0 %}\n        ${{ value }}\n      {% else %}\n      ---\n      {% endif %}\n      </td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n{% endblock %}\n\";}',NULL),(77,'create','2014-04-07 16:10:59','41','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:20:\"Rent Payment is Late\";}',NULL),(78,'create','2014-04-07 16:10:59','42','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:876:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }},{% endblock %}\n{% block email %}\n  It looks like your rent payment for {{ address }} is <b>late by {{ diff }} day(s)</b>.\n  <br /><br />\n  <a href=\"https://my.renttrack.com/\">Log into RentTrack</a> today to make a new payment. We\'d recommend setting up\n  <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">automatic payments</a>\n  so you won\'t see an email like this next month.\n  <br /><br />\n  If you have alread paid by a different method like cash or (*gasp*) paper check, then your landlord needs\n  to log into RentTrack and update your records. They have also received an email reminder regarding this payment.\n  <br /><br />\n  If you need assistance, please email help@renttrack.com or call (866) 841-9090.\n{% endblock %}\n\";}',NULL),(79,'create','2014-04-07 16:10:59','43','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:16:\"Your Rent Is Due\";}',NULL),(80,'create','2014-04-07 16:10:59','44','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:700:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Is Due{% endblock %}\n{% block email %}\nYour rent payment to {{ nameHolding }} for {{ address }} is coming up.\n<br /><br />\n{% if recurring == true %}\n  It looks like you have recurring payments set up, so we\'ll send you another email when we make your payment.\n  If you need to change your payment details or cancel your payment,\n  please <a href=\"https://my.renttrack.com/\">log in to RentTrack today</a> and make any adjustments.\n{% else %}\n  You do not have recurring payments set up. <a href=\"https://my.renttrack.com/\">Log in to RentTrack today</a>\n  to set up a one-time or recurring payment.\n{% endif %}\n{% endblock %}\n\";}',NULL),(81,'create','2014-04-07 16:10:59','45','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:25:\"Review Late Rent Payments\";}',NULL),(82,'create','2014-04-07 16:10:59','46','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1185:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ nameLandlord }},{% endblock %}\n{% block email %}\nThe following tenants have not submitted on-time payments:\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Tenant</th>\n      <th style=\"padding:5px;\">Email</th>\n      <th style=\"padding:5px;\">Address</th>\n      <th style=\"padding:5px;\">Days Late</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for tenant in tenants %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ tenant.name }}</td>\n      <td style=\"padding:5px;\">{{ tenant.email }}</td>\n      <td style=\"padding:5px;\">{{ tenant.address }}</td>\n      <td style=\"padding:5px;\">{{ tenant.late }}</td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n  <br />\n  Please <a href=\"https://my.renttrack.com\">log into RentTrack</a>\n  and click on \"Resolve\" next to late tenants at the top of the Payments Dashboard to either record payments\n  via alternate means or to send them an email reminder.\n{% endblock %}\n\";}',NULL),(83,'create','2014-04-07 16:10:59','47','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:20:\"Rent Payment Receipt\";}',NULL),(84,'create','2014-04-07 16:10:59','48','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1601:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Paid{% endblock %}\n{% block email %}\n{% if nameTenant %}\n  Hi {{ nameTenant }}! <br /><br />\n{% else %}\n  Hello!  <br /><br />\n{% endif %}\n\nYour rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n\";}',NULL),(85,'create','2014-04-07 16:10:59','49','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:11:\"Order Error\";}',NULL),(86,'create','2014-04-07 16:10:59','50','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1831:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n{{ \'order.error.title\'| trans }}.\n<br /><br />\n{{ \'order.error.message\' | trans }}: {{ error }}\n<br /><br />\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.paid.to\' | trans }}:</td><td style=\"padding:5px;\">{{ groupName }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.id\' | trans }}:</td><td style=\"padding:5px;\">{{ orderId }}</td>\n    </tr>\n    {% if transactionId > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionId }}</td>\n    </tr>\n    {% endif %}\n  </tbody>\n</table>\n{{ \'order.contact.us\' | trans }}\n{% endblock %}\n\";}',NULL),(87,'create','2014-04-07 16:10:59','51','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:68:\"Reminder. Your Landlord is Requesting Rent Payment through RentTrack\";}',NULL),(88,'create','2014-04-07 16:10:59','52','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1884:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n\";}',NULL),(89,'create','2014-04-07 16:10:59','53','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:51:\"Reminder. Your Landlord ask to install your payment\";}',NULL),(90,'create','2014-04-07 16:10:59','54','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1750:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n    href=\"http://{{ serverName }}/\"\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n\";}',NULL),(91,'create','2014-04-07 16:10:59','55','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:34:\"You\'re Approved to Pay Rent Online\";}',NULL),(92,'create','2014-04-07 16:10:59','56','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:358:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}You\'re Approved!{% endblock %}\n{% block email %}\nHello {{ nameTenant }},\n\nYour landlord has approved you and you can now set up your rent payment. Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a> and click on the \"Pay\" button corresponding to your rental.\n{% endblock %}\n\";}',NULL),(93,'create','2014-04-07 16:10:59','57','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:41:\"You Contract was Removed by Your Landlord\";}',NULL),(94,'create','2014-04-07 16:10:59','58','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:326:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameTenant }},{% endblock %}\n{% block email %}\n  Your landlord, {{ fullNameLandlord }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\n<br /><br />\nIf this is an error, please contact your landlord.\n{% endblock %}\";}',NULL),(95,'create','2014-04-07 16:10:59','59','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:40:\"Your Contract was Removed by Your Tenant\";}',NULL),(96,'create','2014-04-07 16:10:59','60','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:310:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your tenant, {{ fullNameTenant }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\nIf this is an error, please contact your tenant.\n{% endblock %}\n\";}',NULL),(97,'create','2014-04-07 16:10:59','61','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:41:\"Your RentTrack Merchant Account is Ready!\";}',NULL),(98,'create','2014-04-07 16:10:59','62','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:677:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your merchant account for \"{{ groupName }}\" is approved and ready!\n  <br /><br />\n\n  You can now accept rent payments online, and funds will be deposited into the account\n  you specified in your application. Begin by\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/285491-how-do-i-add-or-invite-a-tenant-\">inviting your tenants</a>, or\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/275851-how-do-i-approve-a-tenant-so-they-can-pay-rent-\">approving any pending tenants</a>\n  that invited you.\n{% endblock %}\n\";}',NULL),(99,'create','2014-04-07 16:10:59','63','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:14:\"Reset Password\";}',NULL),(100,'create','2014-04-07 16:10:59','64','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:579:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  Didn\'t request this change?\n  If you didn\'t request a new password, please contact us at <a mailto=\"help@renttrack.com\">help@renttrack.com</a>.\n\n  RentTrack will never e-mail you and ask you to disclose or verify your RentTrack.com password, credit card, or banking account number.\n\n  Thank you for using RentTrack!\n{% endblock %}\";}',NULL),(101,'create','2014-04-07 16:10:59','65','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:12:\"End Contract\";}',NULL),(102,'create','2014-04-07 16:10:59','66','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:390:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ tenantFullName }}!{% endblock %}\n{% block email %}\n   Your landlord {{landlordFullName}}, has ended contract by address: {{ address }} #{{ unitName }}.\n   {% if uncollectedBalance > 0%}\n      And you have uncollected balance on this contract {{ uncollectedBalance }}$.\n   {% else %}\n\n   {% endif %}\n{% endblock %}\n\";}',NULL),(103,'create','2014-04-07 16:10:59','67','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:30:\"Your Rent Payment was Reversed\";}',NULL),(104,'create','2014-04-07 16:10:59','68','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:774:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Dear {{ tenantFullName }},{% endblock %}\n{% block email %}\n  {% if orderStatus == \'refunded\' %}\n  Per your request, your rent of {{ rentAmount }} sent on {{ orderDate }} was refunded and should appear in your account within a few days.\n  {% elseif orderStatus == \'cancelled\' %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was cancelled.\n  {% else %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was returned. Your rent is currently not paid.\n  You will receive a follow up from RentTrack customer support with the reason for return and ways to fix it.\n  {% endif %}\n  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n\";}',NULL),(105,'create','2014-04-07 16:10:59','69','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:30:\"Your Rent Payment was Reversed\";}',NULL),(106,'create','2014-04-07 16:10:59','70','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1272:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Dear {{ landlordFirstName }},{% endblock %}\n{% block email %}\n  {% if orderStatus == \'refunded\' %}\n  Per your tenant\\\'s request, their rent of {{ rentAmount }} sent on {{ orderDate }} was refunded\n  and will be deducted from your account within a couple of days. Please contact your tenant\n  if you have any questions regarding this refund.\n  {% elseif orderStatus == \'cancelled\' %}\n  Per your your tenant\\\'s request, their rent payment of {{ rentAmount }} sent on {{ orderDate }}\n  was cancelled. You will not see a deposit in your account since it was cancelled before\n  payment settlement. Please contact your tenant if you have any questions regarding this cancellation.\n  {% else %}\n  Your tenant\\\'s payment of {{ rentAmount }} sent on {{ orderDate }} was returned. This amount\n  has been deducted from your account per the RentTrack terms of service. Your rent is currently not paid.\n  Please contact your tenant if to arrange another payment.\n\n\n  RentTrack Customer Support will also reach out to your tenant to see if their payment source information\n  can be corrected.\n  {% endif %}\n  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n\";}',NULL),(107,'create','2014-04-07 16:10:59','71','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:23:\"Your Rent is Processing\";}',NULL),(108,'create','2014-04-07 16:10:59','72','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:4:\"test\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1540:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Processing{% endblock %}\n{% block email %}\n  Hi {{ tenantName }}! <br /><br />\n\n  Your rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ orderTime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n\n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n\";}',NULL);
/*!40000 ALTER TABLE `ext_log_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jms_job_dependencies`
--

DROP TABLE IF EXISTS `jms_job_dependencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jms_job_dependencies` (
  `source_job_id` bigint(20) unsigned NOT NULL,
  `dest_job_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`source_job_id`,`dest_job_id`),
  KEY `IDX_8DCFE92CBD1F6B4F` (`source_job_id`),
  KEY `IDX_8DCFE92C32CF8D4C` (`dest_job_id`),
  CONSTRAINT `FK_8DCFE92C32CF8D4C` FOREIGN KEY (`dest_job_id`) REFERENCES `jms_jobs` (`id`),
  CONSTRAINT `FK_8DCFE92CBD1F6B4F` FOREIGN KEY (`source_job_id`) REFERENCES `jms_jobs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jms_job_dependencies`
--

LOCK TABLES `jms_job_dependencies` WRITE;
/*!40000 ALTER TABLE `jms_job_dependencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `jms_job_dependencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jms_job_related_entities`
--

DROP TABLE IF EXISTS `jms_job_related_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jms_job_related_entities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint(20) unsigned DEFAULT NULL,
  `payment_id` bigint(20) DEFAULT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `related_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E956F4E2BE04EA9` (`job_id`),
  KEY `IDX_E956F4E24C3A3BB` (`payment_id`),
  KEY `IDX_E956F4E28D9F6D38` (`order_id`),
  CONSTRAINT `FK_E956F4E28D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`),
  CONSTRAINT `FK_E956F4E24C3A3BB` FOREIGN KEY (`payment_id`) REFERENCES `rj_payment` (`id`),
  CONSTRAINT `FK_E956F4E2BE04EA9` FOREIGN KEY (`job_id`) REFERENCES `jms_jobs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jms_job_related_entities`
--

LOCK TABLES `jms_job_related_entities` WRITE;
/*!40000 ALTER TABLE `jms_job_related_entities` DISABLE KEYS */;
INSERT INTO `jms_job_related_entities` VALUES (1,1,1,NULL,'2014-04-07 16:11:03','payment'),(2,2,3,NULL,'2014-03-07 16:11:03','payment'),(3,2,NULL,45,'2014-03-07 16:11:03','order');
/*!40000 ALTER TABLE `jms_job_related_entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jms_jobs`
--

DROP TABLE IF EXISTS `jms_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jms_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `startedAt` datetime DEFAULT NULL,
  `checkedAt` datetime DEFAULT NULL,
  `executeAfter` datetime DEFAULT NULL,
  `closedAt` datetime DEFAULT NULL,
  `command` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `args` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `output` longtext COLLATE utf8_unicode_ci,
  `errorOutput` longtext COLLATE utf8_unicode_ci,
  `exitCode` smallint(5) unsigned DEFAULT NULL,
  `maxRuntime` smallint(5) unsigned NOT NULL,
  `maxRetries` smallint(5) unsigned NOT NULL,
  `stackTrace` longblob COMMENT '(DC2Type:jms_job_safe_object)',
  `runtime` smallint(5) unsigned DEFAULT NULL,
  `memoryUsage` int(10) unsigned DEFAULT NULL,
  `memoryUsageReal` int(10) unsigned DEFAULT NULL,
  `originalJob_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_704ADB9349C447F1` (`originalJob_id`),
  KEY `IDX_704ADB938ECAEAD4` (`command`),
  KEY `job_runner` (`executeAfter`,`state`),
  CONSTRAINT `FK_704ADB9349C447F1` FOREIGN KEY (`originalJob_id`) REFERENCES `jms_jobs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jms_jobs`
--

LOCK TABLES `jms_jobs` WRITE;
/*!40000 ALTER TABLE `jms_jobs` DISABLE KEYS */;
INSERT INTO `jms_jobs` VALUES (1,'pending','2014-04-07 16:11:03',NULL,NULL,'2014-04-07 16:11:03',NULL,'payment:pay','[\"--app=rj\"]',NULL,NULL,NULL,0,0,'N;',NULL,NULL,NULL,NULL),(2,'finished','2014-03-07 16:11:03','2014-03-07 16:11:03',NULL,'2014-03-07 16:11:03','2014-03-07 16:11:03','payment:pay','[\"--app=rj\"]','Start\nOK',NULL,0,0,0,'N;',5,NULL,NULL,NULL);
/*!40000 ALTER TABLE `jms_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partner`
--

DROP TABLE IF EXISTS `partner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `request_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_312B3E165E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partner`
--

LOCK TABLES `partner` WRITE;
/*!40000 ALTER TABLE `partner` DISABLE KEYS */;
INSERT INTO `partner` VALUES (1,'creditcom','CREDITCOM');
/*!40000 ALTER TABLE `partner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partner_code`
--

DROP TABLE IF EXISTS `partner_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partner_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `is_charged` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_27210380A76ED395` (`user_id`),
  KEY `IDX_272103809393F8FE` (`partner_id`),
  CONSTRAINT `FK_27210380A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_272103809393F8FE` FOREIGN KEY (`partner_id`) REFERENCES `partner` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partner_code`
--

LOCK TABLES `partner_code` WRITE;
/*!40000 ALTER TABLE `partner_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `partner_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refresh_token`
--

DROP TABLE IF EXISTS `refresh_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refresh_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C74F21955F37A13B` (`token`),
  KEY `IDX_C74F2195A76ED395` (`user_id`),
  KEY `IDX_C74F219519EB6921` (`client_id`),
  CONSTRAINT `FK_C74F219519EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  CONSTRAINT `FK_C74F2195A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refresh_token`
--

LOCK TABLES `refresh_token` WRITE;
/*!40000 ALTER TABLE `refresh_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `refresh_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_billing_account`
--

DROP TABLE IF EXISTS `rj_billing_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_billing_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_6D16C91BFE54D947` (`group_id`),
  CONSTRAINT `FK_6D16C91BFE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_billing_account`
--

LOCK TABLES `rj_billing_account` WRITE;
/*!40000 ALTER TABLE `rj_billing_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_billing_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_checkout_heartland`
--

DROP TABLE IF EXISTS `rj_checkout_heartland`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_checkout_heartland` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) DEFAULT NULL,
  `messages` longtext COLLATE utf8_unicode_ci,
  `is_successful` tinyint(1) NOT NULL,
  `amount` decimal(10,0) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `merchant_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `batch_id` bigint(20) DEFAULT NULL,
  `batch_date` date DEFAULT NULL,
  `deposit_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A1CC46998D9F6D38` (`order_id`),
  CONSTRAINT `FK_A1CC46998D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_checkout_heartland`
--

LOCK TABLES `rj_checkout_heartland` WRITE;
/*!40000 ALTER TABLE `rj_checkout_heartland` DISABLE KEYS */;
INSERT INTO `rj_checkout_heartland` VALUES (1,2,NULL,1,1500,123123,NULL,125478,'2014-02-17','2014-02-17','2014-02-16 16:11:01'),(2,2,NULL,1,1500,123456,NULL,NULL,NULL,NULL,'2014-02-16 16:11:01'),(3,3,NULL,1,1500,456456,NULL,325698,'2014-02-25','2014-02-25','2014-02-26 16:11:01'),(4,3,NULL,1,1500,123789,NULL,NULL,NULL,NULL,'2014-02-26 16:11:01'),(5,4,NULL,1,1500,456123,NULL,111555,'2014-03-17','2014-03-17','2014-03-08 16:11:01'),(6,9,NULL,1,1500,789789,NULL,111555,'2014-03-17','2014-03-17','2014-03-18 16:11:01'),(7,10,NULL,1,1500,147147,NULL,325698,'2014-03-29','2014-03-29','2014-03-28 16:11:01'),(8,11,NULL,1,1500,258258,NULL,125478,'2014-04-06','2014-04-06','2014-04-05 16:11:01'),(9,13,NULL,1,1500,369369,NULL,NULL,NULL,NULL,'2013-04-02 16:11:01'),(10,13,NULL,1,1500,741258,NULL,NULL,NULL,NULL,'2013-04-03 16:11:01'),(11,14,NULL,1,1500,159159,NULL,NULL,NULL,NULL,'2013-05-03 16:11:01'),(12,14,NULL,1,1500,753753,NULL,NULL,NULL,NULL,'2013-05-03 16:11:01'),(13,15,NULL,1,1500,777888,NULL,NULL,NULL,NULL,'2013-05-03 16:11:01'),(14,14,NULL,1,1500,111222,NULL,NULL,NULL,NULL,'2013-05-07 16:11:01'),(15,16,NULL,1,1500,222333,NULL,NULL,NULL,NULL,'2013-06-02 16:11:01'),(16,16,NULL,1,1500,333444,NULL,NULL,NULL,NULL,'2013-06-02 16:11:01'),(17,17,NULL,1,1500,555666,NULL,NULL,NULL,NULL,'2013-07-02 16:11:01'),(18,17,NULL,1,1500,666777,NULL,NULL,NULL,NULL,'2013-07-02 16:11:01'),(19,18,NULL,1,1500,777555,NULL,NULL,NULL,NULL,'2013-08-01 16:11:01'),(20,18,NULL,1,1500,111444,NULL,NULL,NULL,NULL,'2013-08-01 16:11:01'),(21,19,NULL,1,1500,555999,NULL,NULL,NULL,NULL,'2013-08-31 16:11:01'),(22,19,NULL,1,1500,666333,NULL,NULL,NULL,NULL,'2013-08-31 16:11:01'),(23,20,NULL,1,1500,112233,NULL,NULL,NULL,NULL,'2013-09-30 16:11:01'),(24,20,NULL,1,1500,223344,NULL,NULL,NULL,NULL,'2013-09-30 16:11:01'),(25,21,NULL,1,1500,334455,NULL,NULL,NULL,NULL,'2013-10-30 16:11:01'),(26,21,NULL,1,1500,445566,NULL,NULL,NULL,NULL,'2013-11-02 16:11:01'),(27,22,NULL,1,1500,556667,NULL,NULL,NULL,NULL,'2013-11-29 16:11:01'),(28,22,NULL,1,1500,667788,NULL,NULL,NULL,NULL,'2013-11-29 16:11:01'),(29,23,NULL,1,1500,778899,NULL,NULL,NULL,NULL,'2013-12-29 16:11:01'),(30,23,NULL,1,1500,552266,NULL,NULL,NULL,NULL,'2013-12-29 16:11:01'),(31,24,NULL,1,1200,446632,NULL,NULL,NULL,NULL,'2013-08-07 16:11:01'),(32,24,NULL,1,1200,5542369,NULL,NULL,NULL,NULL,'2013-08-07 16:11:01'),(33,24,NULL,1,1200,7458693,NULL,NULL,NULL,NULL,'2013-08-07 16:11:01'),(34,25,NULL,1,1200,12536841,NULL,NULL,NULL,NULL,'2013-09-07 16:11:01'),(35,25,NULL,1,1200,45785216,NULL,NULL,NULL,NULL,'2013-09-07 16:11:01'),(36,26,NULL,1,1200,123457185,NULL,NULL,NULL,NULL,'2013-10-07 16:11:01'),(37,26,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2013-10-07 16:11:01'),(38,27,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2013-11-07 16:11:01'),(39,27,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2013-11-07 16:11:01'),(40,28,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2013-12-07 16:11:01'),(41,28,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2013-12-07 16:11:01'),(42,29,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2014-01-07 16:11:01'),(43,29,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2014-01-07 16:11:01'),(44,30,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2014-02-07 16:11:01'),(45,30,NULL,1,1200,2147483647,NULL,NULL,NULL,NULL,'2014-02-07 16:11:01');
/*!40000 ALTER TABLE `rj_checkout_heartland` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_contract`
--

DROP TABLE IF EXISTS `rj_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_contract` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) DEFAULT NULL,
  `holding_id` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  `property_id` bigint(20) DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `search` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('pending','invite','approved','current','finished','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '(DC2Type:ContractStatus)',
  `rent` decimal(10,2) DEFAULT NULL,
  `uncollected_balance` decimal(10,2) DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `imported_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_to` date DEFAULT NULL,
  `reporting` tinyint(1) DEFAULT '0',
  `start_at` date DEFAULT NULL,
  `finish_at` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2A4AB7F09033212A` (`tenant_id`),
  KEY `IDX_2A4AB7F06CD5FBA3` (`holding_id`),
  KEY `IDX_2A4AB7F0FE54D947` (`group_id`),
  KEY `IDX_2A4AB7F0549213EC` (`property_id`),
  KEY `IDX_2A4AB7F0F8BD700D` (`unit_id`),
  CONSTRAINT `FK_2A4AB7F0F8BD700D` FOREIGN KEY (`unit_id`) REFERENCES `rj_unit` (`id`),
  CONSTRAINT `FK_2A4AB7F0549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`),
  CONSTRAINT `FK_2A4AB7F06CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`),
  CONSTRAINT `FK_2A4AB7F09033212A` FOREIGN KEY (`tenant_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_2A4AB7F0FE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_contract`
--

LOCK TABLES `rj_contract` WRITE;
/*!40000 ALTER TABLE `rj_contract` DISABLE KEYS */;
INSERT INTO `rj_contract` VALUES (1,42,5,24,1,1,NULL,'pending',NULL,NULL,0.00,0.00,NULL,0,NULL,NULL,'2014-02-16 16:11:00','2014-02-16 16:11:00'),(2,42,5,24,1,2,NULL,'approved',1400.00,NULL,0.00,0.00,'2014-06-06',0,'2014-06-06','2014-07-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(3,42,5,25,1,4,NULL,'finished',1500.00,NULL,0.00,0.00,'2014-02-06',0,'2013-04-07','2014-02-06','2013-03-03 16:11:00','2014-02-06 16:11:00'),(4,43,5,24,1,4,NULL,'finished',1500.00,NULL,0.00,0.00,'2013-12-08',0,'2013-06-21','2013-10-29','2014-02-16 16:11:00','2014-02-16 16:11:00'),(5,44,5,24,1,NULL,NULL,'pending',NULL,NULL,0.00,0.00,NULL,0,NULL,'2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(6,45,5,24,1,6,NULL,'approved',1700.00,NULL,0.00,0.00,'2014-04-06',0,'2014-01-07','2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(7,47,5,24,1,6,NULL,'approved',3700.00,NULL,0.00,0.00,'2014-04-06',0,'2014-01-07','2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(8,42,5,24,1,7,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-27',0,'2013-06-07','2014-06-07','2013-05-07 16:11:00','2013-06-07 16:11:00'),(9,47,5,24,1,8,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-10',0,'2014-01-07','2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(10,48,5,24,1,9,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-22',0,'2014-01-07','2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(11,49,5,24,1,10,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-22',0,'2014-01-07','2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(12,50,5,24,1,11,NULL,'current',2100.00,NULL,0.00,0.00,'2014-04-06',0,'2014-01-07','2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(13,51,5,24,1,12,NULL,'approved',2100.00,NULL,0.00,0.00,NULL,0,'2014-01-07','2014-06-06','2014-02-16 16:11:00','2014-02-16 16:11:00'),(14,52,5,24,1,1,NULL,'finished',2100.00,NULL,0.00,0.00,'2013-12-08',0,'2013-04-12','2013-12-08','2014-02-16 16:11:00','2014-02-16 16:11:00'),(15,53,5,24,1,2,NULL,'finished',1400.00,NULL,0.00,0.00,'2013-12-08',0,'2013-04-12','2013-12-08','2014-02-16 16:11:00','2014-02-16 16:11:00'),(16,53,6,29,1,2,NULL,'finished',1400.00,NULL,0.00,0.00,'2013-12-08',0,'2013-04-12','2013-12-08','2014-02-16 16:11:00','2014-02-16 16:11:00'),(17,53,6,29,1,2,NULL,'invite',1400.00,NULL,0.00,0.00,'2014-04-08',0,'2014-05-07',NULL,'2014-03-18 16:11:00','2014-03-18 16:11:00'),(18,44,6,29,1,2,NULL,'invite',1400.00,NULL,0.00,0.00,'2014-04-08',0,'2014-05-07',NULL,'2014-03-18 16:11:00','2014-03-18 16:11:00'),(19,53,6,29,1,2,NULL,'current',1400.00,NULL,0.00,0.00,'2014-04-08',0,'2014-05-07',NULL,'2014-03-18 16:11:00','2014-03-18 16:11:00'),(20,46,5,25,1,1,NULL,'pending',NULL,NULL,0.00,0.00,NULL,0,NULL,NULL,'2014-02-16 16:11:00','2014-02-16 16:11:00'),(21,50,5,24,1,11,NULL,'current',199.10,NULL,0.00,0.00,'2014-04-08',0,'2014-01-07','2014-03-28','2013-11-08 16:11:00','2013-11-08 16:11:00');
/*!40000 ALTER TABLE `rj_contract` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_contract_history`
--

DROP TABLE IF EXISTS `rj_contract_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_contract_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) DEFAULT NULL,
  `editor_id` bigint(20) DEFAULT NULL,
  `status` enum('pending','invite','approved','current','finished','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '(DC2Type:ContractStatus)',
  `rent` decimal(10,2) DEFAULT NULL,
  `uncollected_balance` decimal(10,2) DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `imported_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_to` date DEFAULT NULL,
  `reporting` tinyint(1) DEFAULT '0',
  `start_at` date DEFAULT NULL,
  `finish_at` date DEFAULT NULL,
  `action` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `logged_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6CF9EAFD232D562B` (`object_id`),
  CONSTRAINT `FK_6CF9EAFD232D562B` FOREIGN KEY (`object_id`) REFERENCES `rj_contract` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_contract_history`
--

LOCK TABLES `rj_contract_history` WRITE;
/*!40000 ALTER TABLE `rj_contract_history` DISABLE KEYS */;
INSERT INTO `rj_contract_history` VALUES (1,1,NULL,'pending',NULL,NULL,0.00,0.00,NULL,0,NULL,NULL,'create','2014-04-07 16:11:00'),(2,2,NULL,'approved',1400.00,NULL,0.00,0.00,'2014-06-06',0,'2014-06-06','2014-07-06','create','2014-04-07 16:11:00'),(3,3,NULL,'finished',1500.00,NULL,0.00,0.00,'2014-02-06',0,'2013-04-07','2014-02-06','create','2014-04-07 16:11:00'),(4,4,NULL,'finished',1500.00,NULL,0.00,0.00,'2013-12-08',0,'2013-06-21','2013-10-29','create','2014-04-07 16:11:00'),(5,5,NULL,'pending',NULL,NULL,0.00,0.00,NULL,0,NULL,'2014-06-06','create','2014-04-07 16:11:00'),(6,6,NULL,'approved',1700.00,NULL,0.00,0.00,'2014-04-06',0,'2014-01-07','2014-06-06','create','2014-04-07 16:11:00'),(7,7,NULL,'approved',3700.00,NULL,0.00,0.00,'2014-04-06',0,'2014-01-07','2014-06-06','create','2014-04-07 16:11:00'),(8,8,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-27',0,'2013-06-07','2014-06-07','create','2014-04-07 16:11:00'),(9,9,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-10',0,'2014-01-07','2014-06-06','create','2014-04-07 16:11:00'),(10,10,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-22',0,'2014-01-07','2014-06-06','create','2014-04-07 16:11:00'),(11,11,NULL,'current',1750.00,NULL,0.00,0.00,'2014-04-22',0,'2014-01-07','2014-06-06','create','2014-04-07 16:11:00'),(12,12,NULL,'current',2100.00,NULL,0.00,0.00,'2014-04-06',0,'2014-01-07','2014-06-06','create','2014-04-07 16:11:00'),(13,13,NULL,'approved',2100.00,NULL,0.00,0.00,NULL,0,'2014-01-07','2014-06-06','create','2014-04-07 16:11:00'),(14,14,NULL,'finished',2100.00,NULL,0.00,0.00,'2013-12-08',0,'2013-04-12','2013-12-08','create','2014-04-07 16:11:00'),(15,15,NULL,'finished',1400.00,NULL,0.00,0.00,'2013-12-08',0,'2013-04-12','2013-12-08','create','2014-04-07 16:11:00'),(16,16,NULL,'finished',1400.00,NULL,0.00,0.00,'2013-12-08',0,'2013-04-12','2013-12-08','create','2014-04-07 16:11:00'),(17,17,NULL,'invite',1400.00,NULL,0.00,0.00,'2014-04-08',0,'2014-05-07',NULL,'create','2014-04-07 16:11:00'),(18,18,NULL,'invite',1400.00,NULL,0.00,0.00,'2014-04-08',0,'2014-05-07',NULL,'create','2014-04-07 16:11:00'),(19,19,NULL,'current',1400.00,NULL,0.00,0.00,'2014-04-08',0,'2014-05-07',NULL,'create','2014-04-07 16:11:00'),(20,20,NULL,'pending',NULL,NULL,0.00,0.00,NULL,0,NULL,NULL,'create','2014-04-07 16:11:00'),(21,21,NULL,'current',199.10,NULL,0.00,0.00,'2014-04-08',0,'2014-01-07','2014-03-28','create','2014-04-07 16:11:00');
/*!40000 ALTER TABLE `rj_contract_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_deposit_account`
--

DROP TABLE IF EXISTS `rj_deposit_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_deposit_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) DEFAULT NULL,
  `merchant_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('error','success','init','complete') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'init' COMMENT '(DC2Type:DepositAccountStatus)',
  `message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7F2B897FE54D947` (`group_id`),
  CONSTRAINT `FK_7F2B897FE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_deposit_account`
--

LOCK TABLES `rj_deposit_account` WRITE;
/*!40000 ALTER TABLE `rj_deposit_account` DISABLE KEYS */;
INSERT INTO `rj_deposit_account` VALUES (1,24,'Monticeto_Percent','complete',NULL),(2,25,'Monticeto_Percent','complete',NULL);
/*!40000 ALTER TABLE `rj_deposit_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_group_phone`
--

DROP TABLE IF EXISTS `rj_group_phone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_group_phone` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DF1D7A7CFE54D947` (`group_id`),
  CONSTRAINT `FK_DF1D7A7CFE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group_phone`
--

LOCK TABLES `rj_group_phone` WRITE;
/*!40000 ALTER TABLE `rj_group_phone` DISABLE KEYS */;
INSERT INTO `rj_group_phone` VALUES (1,24,'111-22-44',1,'2014-04-07 16:11:02','2014-04-07 16:11:02'),(2,24,'555-22-44',0,'2014-04-07 16:11:02','2014-04-07 16:11:02'),(3,24,'057-71087-35',0,'2014-04-07 16:11:02','2014-04-07 16:11:02');
/*!40000 ALTER TABLE `rj_group_phone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_group_property`
--

DROP TABLE IF EXISTS `rj_group_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_group_property` (
  `group_id` bigint(20) NOT NULL,
  `property_id` bigint(20) NOT NULL,
  PRIMARY KEY (`group_id`,`property_id`),
  KEY `IDX_3DFD966BFE54D947` (`group_id`),
  KEY `IDX_3DFD966B549213EC` (`property_id`),
  CONSTRAINT `FK_3DFD966B549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`),
  CONSTRAINT `FK_3DFD966BFE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group_property`
--

LOCK TABLES `rj_group_property` WRITE;
/*!40000 ALTER TABLE `rj_group_property` DISABLE KEYS */;
INSERT INTO `rj_group_property` VALUES (24,1),(24,2),(24,3),(24,4),(24,5),(24,6),(24,7),(24,8),(24,9),(24,10),(24,11),(24,12),(24,13),(24,14),(24,15),(24,16),(24,17),(24,18),(25,1),(25,2),(25,16),(25,17),(25,18);
/*!40000 ALTER TABLE `rj_group_property` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_group_settings`
--

DROP TABLE IF EXISTS `rj_group_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_group_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) NOT NULL,
  `pid_verification` tinyint(1) NOT NULL,
  `is_integrated` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_534A2A70FE54D947` (`group_id`),
  CONSTRAINT `FK_534A2A70FE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group_settings`
--

LOCK TABLES `rj_group_settings` WRITE;
/*!40000 ALTER TABLE `rj_group_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_group_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_invite`
--

DROP TABLE IF EXISTS `rj_invite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_invite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `property_id` bigint(20) DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `unit` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DACA6BA4A76ED395` (`user_id`),
  KEY `IDX_DACA6BA4549213EC` (`property_id`),
  CONSTRAINT `FK_DACA6BA4549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`),
  CONSTRAINT `FK_DACA6BA4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_invite`
--

LOCK TABLES `rj_invite` WRITE;
/*!40000 ALTER TABLE `rj_invite` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_invite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_payment`
--

DROP TABLE IF EXISTS `rj_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_payment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) NOT NULL,
  `payment_account_id` bigint(20) NOT NULL,
  `type` enum('recurring','one_time','immediate') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentType)',
  `status` enum('active','pause','close') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentStatus)',
  `amount` decimal(10,2) NOT NULL,
  `due_date` int(11) NOT NULL,
  `start_month` int(11) NOT NULL,
  `start_year` int(11) NOT NULL,
  `end_month` int(11) DEFAULT NULL,
  `end_year` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A4398CF02576E0FD` (`contract_id`),
  KEY `IDX_A4398CF0AE9DDE6F` (`payment_account_id`),
  CONSTRAINT `FK_A4398CF0AE9DDE6F` FOREIGN KEY (`payment_account_id`) REFERENCES `rj_payment_account` (`id`),
  CONSTRAINT `FK_A4398CF02576E0FD` FOREIGN KEY (`contract_id`) REFERENCES `rj_contract` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment`
--

LOCK TABLES `rj_payment` WRITE;
/*!40000 ALTER TABLE `rj_payment` DISABLE KEYS */;
INSERT INTO `rj_payment` VALUES (1,2,1,'recurring','active',1400.00,7,1,2014,1,2015,'2014-04-02 16:11:02','2014-04-02 16:11:02'),(2,3,2,'recurring','close',1500.00,7,4,2014,1,2015,'2014-04-02 16:11:02','2014-04-02 16:11:02'),(3,4,4,'recurring','active',1700.00,7,4,2014,1,2015,'2014-04-02 16:11:02','2014-04-02 16:11:02'),(4,6,5,'recurring','active',1700.00,7,2,2015,1,2015,'2014-04-02 16:11:02','2014-04-02 16:11:02'),(5,12,6,'recurring','active',2100.00,8,5,2015,1,2015,'2014-02-16 16:11:02','2014-02-16 16:11:02'),(6,13,7,'recurring','active',2100.00,31,2,2014,NULL,NULL,'2014-02-27 00:00:00','2014-02-27 00:00:00');
/*!40000 ALTER TABLE `rj_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_payment_account`
--

DROP TABLE IF EXISTS `rj_payment_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_payment_account` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `group_id` bigint(20) NOT NULL,
  `address_id` bigint(20) DEFAULT NULL,
  `type` enum('bank','card') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentAccountType)',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cc_expiration` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1F714C26A76ED395` (`user_id`),
  KEY `IDX_1F714C26FE54D947` (`group_id`),
  KEY `IDX_1F714C26F5B7AF75` (`address_id`),
  CONSTRAINT `FK_1F714C26F5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `cj_address` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_1F714C26A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_1F714C26FE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment_account`
--

LOCK TABLES `rj_payment_account` WRITE;
/*!40000 ALTER TABLE `rj_payment_account` DISABLE KEYS */;
INSERT INTO `rj_payment_account` VALUES (1,42,24,51,'card','Card','1A41838C-00BB-444B-9FD3-887E136F0420','2014-05-31','2014-04-02 16:11:02','2014-04-02 16:11:02',NULL),(2,42,25,NULL,'bank','Bank','13201893-2913-433C-8551-19DE6FA61655',NULL,'2014-04-02 16:11:02','2014-04-02 16:11:02',NULL),(3,43,24,NULL,'bank','Bank','13201893-2913-433C-8551-19DE6FA61655',NULL,'2014-04-02 16:11:02','2014-04-02 16:11:02',NULL),(4,43,24,26,'card','Card','1A41838C-00BB-444B-9FD3-887E136F0420','2014-08-31','2014-04-02 16:11:02','2014-04-02 16:11:02',NULL),(5,45,24,28,'card','Card','1A41838C-00BB-444B-9FD3-887E136F0420','2014-09-30','2014-04-02 16:11:02','2014-04-02 16:11:02',NULL),(6,50,24,NULL,'bank','Bank account','13201893-2913-433C-8551-19DE6FA61655',NULL,'2014-04-02 16:11:02','2014-04-02 16:11:02',NULL),(7,51,24,NULL,'bank','Bank account','13201893-2913-433C-8551-19DE6FA61655',NULL,'2014-02-28 00:00:00','2014-02-28 00:00:00',NULL);
/*!40000 ALTER TABLE `rj_payment_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_permission`
--

DROP TABLE IF EXISTS `rj_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_permission` (
  `agent_id` bigint(20) NOT NULL,
  `group_id` bigint(20) NOT NULL,
  PRIMARY KEY (`agent_id`,`group_id`),
  KEY `IDX_FF3CD81A3414710B` (`agent_id`),
  KEY `IDX_FF3CD81AFE54D947` (`group_id`),
  CONSTRAINT `FK_FF3CD81AFE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`),
  CONSTRAINT `FK_FF3CD81A3414710B` FOREIGN KEY (`agent_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_permission`
--

LOCK TABLES `rj_permission` WRITE;
/*!40000 ALTER TABLE `rj_permission` DISABLE KEYS */;
INSERT INTO `rj_permission` VALUES (64,29),(68,29);
/*!40000 ALTER TABLE `rj_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_property`
--

DROP TABLE IF EXISTS `rj_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_property` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `area` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `district` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jb` double DEFAULT NULL,
  `kb` double DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_property`
--

LOCK TABLES `rj_property` WRITE;
/*!40000 ALTER TABLE `rj_property` DISABLE KEYS */;
INSERT INTO `rj_property` VALUES (1,'US','NY','New York','Manhattan','Broadway','770','10003',NULL,40.7308443,-73.9913642,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(2,'US','CA','Santa Barbara',NULL,'Andante Rd','960','93105',NULL,34.44943,-119.709369,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(3,'US','CA','Mission Canyon',NULL,'Andante Rd','750','93105',NULL,34.44987,-119.7096921,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(4,'US','NY','New York','Manhattan','Broadway','560','10012',NULL,40.723851,-73.997487,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(5,'US','NY','Jamaica','Queens','Broadway','1','11414',NULL,40.6584069,-73.830445,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(6,'US','MA','Boston',NULL,'Washington St','10','1114',NULL,42.2574449,-71.1616868,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(7,'US','CA','Palos Verdes Estates',NULL,'Vía Fernandez',NULL,'90274',NULL,33.7880762,-118.3960347,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(8,'US','WA','Seattle',NULL,'18th Ave','50','98122',NULL,47.6016982,-122.3089461,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(9,'US','MO','Kansas City',NULL,'W 48th St','121','64112',NULL,39.038827,-94.588826,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(10,'US','TX','Houston',NULL,'Crosstimbers St','1201','77022',NULL,29.8287445,-95.3856651,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(11,'US','Mt','Billings',NULL,'Overland Ave','2026','59102',NULL,45.753246,-108.565361,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(12,'US','AZ','Scottsdale',NULL,'N Palo Cristi Rd','5532','85253',NULL,33.518351,-112.0041753,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(13,'CU','Havana','Havana',NULL,'10 de Octubre',NULL,NULL,NULL,23.1094238,-82.3658518,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(14,'US','IL','Chicago',NULL,'W Madison St','733','60661',NULL,41.8810911,-87.6468986,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(15,'US','AR','Little Rock',NULL,'S Broadway St','617','72201',NULL,34.7434652,-92.2759828,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(16,'US','NY','New York','Manhattan','Broadway','776','10003',NULL,40.7312396,-73.9918488,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(17,'US','NY','New York','Manhattan','Broadway','745','10003',NULL,40.7302448,-73.9927101,'2014-02-06 16:10:59','2014-03-30 16:10:59'),(18,'US','NY','New York','Manhattan','Broadway','785','10003',NULL,40.7316721,-73.9917422,'2014-02-06 16:10:59','2014-03-30 16:10:59');
/*!40000 ALTER TABLE `rj_property` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_unit`
--

DROP TABLE IF EXISTS `rj_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_unit` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) DEFAULT NULL,
  `holding_id` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `rent` int(11) DEFAULT NULL,
  `beds` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_848B915549213EC` (`property_id`),
  KEY `IDX_848B9156CD5FBA3` (`holding_id`),
  KEY `IDX_848B915FE54D947` (`group_id`),
  CONSTRAINT `FK_848B915FE54D947` FOREIGN KEY (`group_id`) REFERENCES `cj_account_group` (`id`),
  CONSTRAINT `FK_848B915549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`),
  CONSTRAINT `FK_848B9156CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_unit`
--

LOCK TABLES `rj_unit` WRITE;
/*!40000 ALTER TABLE `rj_unit` DISABLE KEYS */;
INSERT INTO `rj_unit` VALUES (1,1,5,24,'1-a',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(2,1,5,24,'1-b',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(3,1,5,24,'1-c',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(4,1,5,24,'1-d',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(5,1,5,24,'1-e',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(6,1,5,24,'1-f',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(7,1,5,24,'2-a',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(8,1,5,24,'2-b',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(9,1,5,24,'2-c',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(10,1,5,24,'2-d',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(11,1,5,24,'2-e',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(12,1,5,24,'2-f',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(13,2,5,24,'5-a',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(14,2,5,24,'5-b',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(15,2,5,24,'5-c',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(16,2,5,24,'5-d',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(17,2,5,24,'5-e',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(18,2,5,24,'5-f',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(19,2,5,24,'7-a',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(20,2,5,24,'25-b',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(21,2,5,24,'45-c',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(22,2,5,24,'4-d',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(23,2,5,24,'11-e',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(24,2,5,24,'27-f',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL),(25,18,5,24,'1',NULL,NULL,'2014-02-06 16:11:00','2014-03-30 16:11:00',NULL);
/*!40000 ALTER TABLE `rj_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_user_settings`
--

DROP TABLE IF EXISTS `rj_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_user_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `is_base_order_report` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EA6F98F6A76ED395` (`user_id`),
  CONSTRAINT `FK_EA6F98F6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_user_settings`
--

LOCK TABLES `rj_user_settings` WRITE;
/*!40000 ALTER TABLE `rj_user_settings` DISABLE KEYS */;
INSERT INTO `rj_user_settings` VALUES (1,63,1);
/*!40000 ALTER TABLE `rj_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sent_email`
--

DROP TABLE IF EXISTS `sent_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sent_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqueId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fromEmails` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `toEmails` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8_unicode_ci,
  `source` longtext COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `contentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E92EE5FCC8CBE8A0` (`uniqueId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sent_email`
--

LOCK TABLES `sent_email` WRITE;
/*!40000 ALTER TABLE `sent_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `sent_email` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-07 16:11:47
