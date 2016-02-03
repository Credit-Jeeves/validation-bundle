-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: credit_jeeves2
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.14.04.1

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_token`
--

LOCK TABLES `access_token` WRITE;
/*!40000 ALTER TABLE `access_token` DISABLE KEYS */;
INSERT INTO `access_token` VALUES (1,42,2,'test',NULL,NULL),(2,72,3,'test_partneranna_lee@example.com',NULL,NULL),(3,65,2,'test_landlord',NULL,NULL);
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
INSERT INTO `atb_simulation` VALUES (1,5,'score',122,400,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=',620,'NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3 icy26YjqrpzMRSGs73qWNpUdTPAgr6au/SPzrt2WKi/M9d8XlVJbpzqRuj0u jiweXGME8cuua8o4xSq3DgI51ZfMhwgm0kCzoYUb88S3qG2imFD8ujts1Jxb 2gfJUQMHdyDJ','2iOPFjwaLu/QhvsRihc0zWBU0FqjAtxhuYA9DtREruchwtsPaykvwkU/tA6TGluGy16XWYYvaCLbYwPMcRr/jNE83f6wzszvKB3+0aYiljIjR9tl01xqvG+iKJkb/IbxUT6i0S4ACCz4zPcKa9OAFEbtg6eNgjHIsTOIIiWdJ2VPqZCJjfRz8Th/ZW3h2F/Xx2Z1U63432k00wXgYVdVFr8EFvjvEFh/buX4LFuKt0AB3Fc4jC2yMw/3zGvQJPFMl2uHU37lbX6JNMaWt5mxGZP6ix7SRv03ahPHzny+KrEuDTi/KIZ2gj5VD6XI7r6aDUDy3VOdKfJy+Y3b5e2uhoKTOawOugiz/+4ucE332EdzTWTp1yn+g2EXWM6cw2sHasfwQqnqaprhcZgAiQkbYOwfycWFbZ2frqN+0la1URod5stiTA9JFQTFRIfn16EKphDhch/FiTH6X1cEbdtHOBb2HTMQWBJm6cDuVzS8JFmQD+mT6b2Y8eqkkFYplNkkYr0XThWt0sgkhLhaMRDJMi1/FILDN2PAKb5MHNLcKutz56TWPJEmWjoteeSPmvCjLeHFGdqi2EbY/quCXBQJSYx0QeRgiL4f6yhmFa6RIwhTnlIMirm0Cwq/15WPq4tGjfAo7GcNw+nWynXuM7vbf/vsU5S/5kEyAjR2NK4wut7LoeFTReJnHt9Or66IGRJRQr+PBpDFAGJl9fYeneQguQ==','2015-09-21 13:17:14','2015-09-21 13:17:14'),(2,5,'search',1000,103,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=',620,'NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3 icy26YjqrpzMRSGs73qWNpUdTPAgr6au/SPzrt2WKi/M9d8XlVJbpzqRuj0u jiweXGME8cuua8o4xSq3DgI51ZfMh3bPLt648qVg2L13kgnLdl+pFU3J3v9h YtLusEoQ4odZ','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','2015-09-21 13:17:14','2015-09-21 13:17:14');
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
  CONSTRAINT `FK_1096A96612867DD` FOREIGN KEY (`cj_account_group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_account_group_affiliate`
--

LOCK TABLES `cj_account_group_affiliate` WRITE;
/*!40000 ALTER TABLE `cj_account_group_affiliate` DISABLE KEYS */;
INSERT INTO `cj_account_group_affiliate` VALUES (1,1,2,'rj1.dev/dealer_test.php/test/iframe/key/DXFYBYHX4H','token','DXFYBYHX4H','en','2015-09-21 13:17:14','2015-09-21 13:17:14');
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
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C338DAAA76ED395` (`user_id`),
  CONSTRAINT `FK_C338DAAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_address`
--

LOCK TABLES `cj_address` WRITE;
/*!40000 ALTER TABLE `cj_address` DISABLE KEYS */;
INSERT INTO `cj_address` VALUES (1,17,'KCR5mqRFPMqvKcvLNWvDdQ7yEg7lVAV+TcNzwwIyBic=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','fd1EP60ePDBM/I3LFe/iV+GIFO0E2IymJO9TTQX4XZ4=','22007',NULL,'Minsk','KY','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(2,18,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','09061',NULL,'APO','AE','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(3,19,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(4,20,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(5,21,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(6,21,'LurhFM+EUDuMzXyx2UtD+zq6iqUsgict6GtUhF9QaJw=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','ZqQL8+ZsaTBM7GJqqLkZYGYvpXf8z3HxGuB/PECPJs0=','220121',NULL,'BOSTON','MA','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(7,22,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','207041563',NULL,'BELTSVILLE','MD','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(8,23,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','5nznoC91xMVg15bhflTHNktPLnuBU/9k5dEa4f0iMoo=','762086621',NULL,'DENTON','TX','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(9,24,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','O/zsu8VPgnrp0fC2RvhrnNxWSWVNKBKMQLFDU/P0nFA=','152322008',NULL,'PITTSBURGH','PA','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(10,25,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SVXIi8fUY60qzk90eCG/Q9XEm1gZNKzPP2BN0mip4tU=','903013646',NULL,'INGLEWOOD','CA','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(11,26,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','KiisT3NW6qjjtUinG7rBQvij2rSGqyzT25h+iphhoiA=','595210121',NULL,'BOX ELDER','MT','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(12,27,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','19383',NULL,'WEST CHESTER','PA','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(13,28,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','669016102',NULL,'EMPORIA','KS','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(14,29,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','20704',NULL,'BELTSVILLE','MD','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(15,30,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r2p/YbwFVn8ecwkl61Ruv2enp77i2syVzEsMlRDnEio=','33647',NULL,'TAMPA','FL','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(16,31,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SJBba/YJoTOv9lviP+zLeq4OG94BF+RqH2Rn4zp35gs=','20704',NULL,'MILLINGTON','MD','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(17,32,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','c+wTR0igJhHWsb6+S5LFigC0dUlbghDKKozLdE5EDmk=','916056557',NULL,'NORTH HOLLYWOOD','CA','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(18,33,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r3ojofT4IeiYzx4+5z4YBvb+MCVK0Utq4OgJ2gkvdOg=','09182',NULL,'APO','AE','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(19,34,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tthBcFLx5KwVgTvkVAyoNI47IVZONrRf7ugUe6JtUTo=','11005',NULL,'FLORAL PARK','NY','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(20,35,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','jb4ADCnMQxJH/rf1qEdp7N+wDlfmmWfjf50osDW/m5E=','22306',NULL,'ALEXANDRIA','VA','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(21,36,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Zc43+kxuy7Da9stGjSuxTSuL8peKbQJyKqPvQhX3Caw=','26214',NULL,'BALTIMORE','MO','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(22,40,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','pPM5E/D3Ba2vLfhVYf0u3KeMGna+iGNJD1Qs3rnowg4=','49548',NULL,'GRAND RAPIDS','MI','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(23,39,'gLDyiKI/Xr86USmnUQmCbYACFAPpw4vVX5N2JWmnvrg=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','uApKhcWwpoMx2boabIsSItfu5gQhqZcjIHBeEcxkJ40=','916056801',NULL,'NORTH HOLLYWOOD','CA','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(24,38,'Lxh/LBScURlCK34fv3U1T8aLN/VAnGIrIDiK5gibymY=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','K/f4DDwQFGryObS/xENBc8JWxeUypECTtL1CrMj8yoE=','660491614',NULL,'LAWRENCE','KS','US',1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL),(25,42,'Lxh/LBScURlCK34fv3U1T8aLN/VAnGIrIDiK5gibymY=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','K/f4DDwQFGryObS/xENBc8JWxeUypECTtL1CrMj8yoE=','660491614',NULL,'LAWRENCE','KS','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(26,43,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','49',NULL,'APO','AE','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(27,44,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(28,45,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(29,46,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(30,47,'KCR5mqRFPMqvKcvLNWvDdQ7yEg7lVAV+TcNzwwIyBic=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','fd1EP60ePDBM/I3LFe/iV+GIFO0E2IymJO9TTQX4XZ4=','22007',NULL,'Minsk','KY','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(31,48,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','207041563',NULL,'BELTSVILLE','MD','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(32,49,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','5nznoC91xMVg15bhflTHNktPLnuBU/9k5dEa4f0iMoo=','762086621',NULL,'DENTON','TX','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(33,50,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','O/zsu8VPgnrp0fC2RvhrnNxWSWVNKBKMQLFDU/P0nFA=','152322008',NULL,'PITTSBURGH','PA','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(34,51,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SVXIi8fUY60qzk90eCG/Q9XEm1gZNKzPP2BN0mip4tU=','903013646',NULL,'INGLEWOOD','CA','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(35,52,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','KiisT3NW6qjjtUinG7rBQvij2rSGqyzT25h+iphhoiA=','595210121',NULL,'BOX ELDER','MT','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(36,53,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','19383',NULL,'WEST CHESTER','PA','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(37,54,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','669016102',NULL,'EMPORIA','KS','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(38,55,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r2p/YbwFVn8ecwkl61Ruv2enp77i2syVzEsMlRDnEio=','33647',NULL,'TAMPA','FL','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(39,56,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SJBba/YJoTOv9lviP+zLeq4OG94BF+RqH2Rn4zp35gs=','20704',NULL,'MILLINGTON','MD','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(40,57,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','c+wTR0igJhHWsb6+S5LFigC0dUlbghDKKozLdE5EDmk=','916056557',NULL,'NORTH HOLLYWOOD','CA','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(41,58,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tthBcFLx5KwVgTvkVAyoNI47IVZONrRf7ugUe6JtUTo=','11005',NULL,'FLORAL PARK','NY','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(42,59,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','jb4ADCnMQxJH/rf1qEdp7N+wDlfmmWfjf50osDW/m5E=','22306',NULL,'ALEXANDRIA','VA','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(43,60,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Zc43+kxuy7Da9stGjSuxTSuL8peKbQJyKqPvQhX3Caw=','26214',NULL,'BALTIMORE','MO','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(44,62,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','pPM5E/D3Ba2vLfhVYf0u3KeMGna+iGNJD1Qs3rnowg4=','49548',NULL,'GRAND RAPIDS','MI','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(45,63,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','KElf0M0pNsrdXEnakjIn7kWgO1nxMgTNi4b9Hlbapok=','33025',NULL,'HOLLYWOOD','FL','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(46,64,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Xb5xRdrQF6sgA7sfhnlwmDRlNCkHPJGNUFJ1fayYEKE=','44024',NULL,'CHARDON','OH','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(47,65,'Lxh/LBScURlCK34fv3U1T8aLN/VAnGIrIDiK5gibymY=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','K/f4DDwQFGryObS/xENBc8JWxeUypECTtL1CrMj8yoE=','660491614',NULL,'LAWRENCE','KS','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(48,66,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','49',NULL,'APO','AE','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(49,67,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(50,68,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(51,69,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(52,71,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','49',NULL,'APO','AE','US',1,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(53,42,'KCR5mqRFPMqvKcvLNWvDdQ7yEg7lVAV+TcNzwwIyBic=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','fd1EP60ePDBM/I3LFe/iV+GIFO0E2IymJO9TTQX4XZ4=','22007',NULL,'Minsk','KY','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(54,43,'YCyUpjVa6wLXhe4gkzyerCjMzMXNS/cx5md1lKEgvpk=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','lTtp7XZfNyy2t4r6g/8SbahO6eeR+ydKZjxrsU0YQsM=','09061',NULL,'APO','AE','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(55,44,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l/ej1KemfzQZcfFLbW79gWCfiLiCap27JH6P1ipr/LA=','05717',NULL,'MIDDLETOWN','NJ','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(56,45,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tcxN3WZ2UdFOIZjGp877z3X3WMhL+cSb4rcvM/P+lck=','61801',NULL,'URBANA','IL','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(57,46,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','xo2ZAYhoe7rj0jth6MaErpTxX10fagYKaekY1HBH+ao=','33039',NULL,'HOMESTEAD','FL','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(58,47,'W1tBwbENAGPRdG5LopPJXR5Nfdd7s6JhAF02vwCJOJ8=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','207041563',NULL,'BELTSVILLE','MD','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(59,48,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','5nznoC91xMVg15bhflTHNktPLnuBU/9k5dEa4f0iMoo=','762086621',NULL,'DENTON','TX','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(60,49,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','O/zsu8VPgnrp0fC2RvhrnNxWSWVNKBKMQLFDU/P0nFA=','152322008',NULL,'PITTSBURGH','PA','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(61,50,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SVXIi8fUY60qzk90eCG/Q9XEm1gZNKzPP2BN0mip4tU=','903013646',NULL,'INGLEWOOD','CA','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(62,51,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','KiisT3NW6qjjtUinG7rBQvij2rSGqyzT25h+iphhoiA=','595210121',NULL,'BOX ELDER','MT','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(63,52,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','19383',NULL,'WEST CHESTER','PA','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(64,53,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Y64Ik/Koe9TD2fjh+SuM6E5liEFATykQvybA4l3w2j0=','669016102',NULL,'EMPORIA','KS','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(65,54,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','yxzbt3xnZr5rPGtGFiIGfXIJL5mQcmvIJvnFTXInyvE=','20704',NULL,'BELTSVILLE','MD','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(66,55,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r2p/YbwFVn8ecwkl61Ruv2enp77i2syVzEsMlRDnEio=','33647',NULL,'TAMPA','FL','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(67,56,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','SJBba/YJoTOv9lviP+zLeq4OG94BF+RqH2Rn4zp35gs=','20704',NULL,'MILLINGTON','MD','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(68,57,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','c+wTR0igJhHWsb6+S5LFigC0dUlbghDKKozLdE5EDmk=','916056557',NULL,'NORTH HOLLYWOOD','CA','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(69,58,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','r3ojofT4IeiYzx4+5z4YBvb+MCVK0Utq4OgJ2gkvdOg=','09182',NULL,'APO','AE','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(70,59,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','tthBcFLx5KwVgTvkVAyoNI47IVZONrRf7ugUe6JtUTo=','11005',NULL,'FLORAL PARK','NY','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(71,60,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','jb4ADCnMQxJH/rf1qEdp7N+wDlfmmWfjf50osDW/m5E=','22306',NULL,'ALEXANDRIA','VA','US',0,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL),(72,61,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','Zc43+kxuy7Da9stGjSuxTSuL8peKbQJyKqPvQhX3Caw=','26214',NULL,'BALTIMORE','MO','US',1,'2015-09-21 13:17:17','2015-09-21 13:17:17',NULL);
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
INSERT INTO `cj_affiliate` VALUES (1,'Test','777-77-77','777-77-33','Garshina, 9',NULL,'Kharkov','Ukraine','61053','2015-09-21 13:17:11','2015-09-21 13:17:11'),(2,'Berry','777-77-77','777-77-33','Broadway, 560, ap. 204',NULL,'NYC','NY',NULL,'2015-09-21 13:17:11','2015-09-21 13:17:11');
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
  `status` enum('success','inprogress','failure','locked','backoff','unable') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PidkiqStatus)',
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
  `type` enum('d2c','prequal','tu_snapshot') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ReportType)',
  PRIMARY KEY (`id`),
  KEY `IDX_DA7942E81846CDE5` (`cj_applicant_id`),
  CONSTRAINT `FK_DA7942E81846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_applicant_report`
--

LOCK TABLES `cj_applicant_report` WRITE;
/*!40000 ALTER TABLE `cj_applicant_report` DISABLE KEYS */;
INSERT INTO `cj_applicant_report` VALUES (1,21,'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','2015-08-20 13:17:12','d2c'),(2,17,'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','2015-09-16 13:17:12','prequal'),(3,18,'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','2015-09-16 13:17:12','prequal'),(4,19,'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','2015-09-16 13:17:12','prequal'),(5,21,'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','2015-09-20 13:17:12','prequal'),(6,50,'2KqPieP1mvLBOGNQjcUkltTAHO8CTDEBNSeHLcmvbHqgsq4dSozaiEzKDzccJN2zaTzNVVVoE4FIMjiD3u9/f0+EDodCIGTkUrLfghw37MVLw17vhXzU4Rgw+33hvcsBLXrrTYmbrW4kt4dLccSXv8gfsNGwTLYls/CMsXfOPNSfNi1pReB4USmAJh7WvfgaLc5OVDUML7REk8c/6xvcxhzRWwZb4a5ZX6NKy5+M4tBHp+GONbTsGhqJkU5mZWt6q92Njt4OLUhe4a7KIdJUGOV3h+V7NdiVi//b2779B2LNTk34YoipMpq3iOwJNlzeWr4Refimgvz5Tv9uAIkanhOFj0sleG4mGl+RXZp+f1t4L5zq/GebiBhYxogg0zydsQnOt5Qxujb+2m5QuxrFTiGNBmjKA2ZDPHebZPzSH7icJOPxe5lyUkyOZLO0KCI1DungNtu3MoWzo/hV6P9QTItxxVXssGv4XLg/Pe0agt+0tnHz55CRo6R21kfUgWGgEIPVkjmklAg93TKK6lNpNAeYZ+85u0JAGP6QYXZddbSPzn4F/FrUyqe57ISgzQSzlNkZUc6ud1Io3bJ5xcB78QHrbEpVjDmGuC7qgGMjAw5UptpJumFwdjn4j/2AwSejEGW3hBU/vnIzb+4oV4EkvzODVFebwtbv7A6P1HKi/yDU3ewyVgibLXsij4hvdQYBzledfvoWxTlR0H4o5IEfvbYXdj/mV5kwqY1gHLHycrbTnKDxgzsfc2yZK14TKe342QPBw4dRBJGUWZh0idwMrfe1BReB1TrOxdbcfb2FBM7Ocg9ZoKFcQyGhTukWQZRoJFzvZGKw/GyeERAnRWUlnlkStfT4gDdZDT9E+Hx+ydQq5jjNJAwvbYxocsArpNYemwsEvyuq6AMLKw0DWYOsgsa49R5Fb4mN0QxjdOBUvKz6l1anpSQJAtvr47IVekDlgkAfKiv8Z7D75zuYw3wsXveZ2kQWg2Hg3dgV/yLMpNjY56ppPwFj07NDGrgzzMSMqPVSyUde33Ym/3UGPm5syELF/PaKMOozD6Maq3RkDOU3eY4IyIyOTzQXqAxKLfNBmuXclB9sTfzAjGfVeW9ouIOVDix0ZGPJtvSvva0h3FxY/BCNsCPpbYNW+cxFcurb49Xrcdo4lsQR2DMOOqjgbvuIgiVh6KTi4SWR1vBwEA64BiJ0obTDdsbF9ry65XeIfysitSTcRKkZoA1zlBODSnM1D1vZftzDeY61uhJbU/3P9hP2hdtEKRbTnBQ1Uuz6jF6fIlXXBPfYsLQ2DNdf3JUEc+7+gAF2YnYK4n1eJo0GkmPdO53JfgN9XiBql2p7pYXKArRFGwqjVGVnfJ1DogxR11MFKLRd7IxNFIO5+QFDtIy3vbGPHWT1yAULofrNGjq1qaw/DUKLYLA/SLkrsF9Vvj9LZxNBdXVqRth004dFfiMznaf5uBc8yHkgTxXPKhsjpZ+nSaX1f7Sy2oCPDryOhCyAV/fAHn+lx8Z7EKTllsQCABYM+2ALunt9fRsALyK2Lsem38IKzY5OzIYzLmO+F7y6+cddSWydqI+QFPaCuFe+TqN2eqfYWBTRIF3TGD9Ze0kwDCiSAxT9X3XuS/oyRSNoTOcxTtfUgiduz6mttQRavgj9UpkDTdkOR12a8jB15dobb5P88n3e+h3S4ft/J7rR0hbi8x+9CugGm+51k06H4YM7URCvx8lil30gWQmQN8mI04IEIkvnjSaMx0Ma5StnqqD7FNUjBYlaKPVVas8EJgEKqDD3OEpvUZk5D4KSdvAKe9kpRA7vSZhtzOspMSoBpUdkf1F+NNd2BpYq6Zu0F6mDGFQL6wA/v8ttUlbDp2SA/QiQgprxpUtyra2xXE9ePmNZSkE/44Yx92EfzubEaRwjS3C8GVf4OBjbYoCpYPB6fdEZo+x46DM/Mv3VKRncR2Uu9YasvKW+4tqZc8LnIuVnf4LQptJM9ai6FB1oCn4cE2ZNXX5kFdM/GRIX2WylLIj8BznpljjB1x6y06zL+AA1eeSdAT5RUh8souZfdd9/W6+fv8SP8Y1NdahNIPrt9vjq/4IaJ2mB7eqyErdq52Wct7B99W8qKc6+2vvs09vwGNvCvmMBjyO4FCBtE1jXahrYFGdT3cDRYXN71nxGoTT4TdTNVaQ/8nqEmA28qRpD8p8pnTPk5Q2VLF9h5y3OIQj0cKW21WQkR8Qc8CeQki/p4IkUtgBujogC2I/IvVBGHpSdMpfo7SdL3SVdoej1NoSKZpzJGCfXziOxSE6eX7geo3ehErObsIiuSh0LInHk1tuJ44hNOWOP8AOypUIzr0rdy3pqJkCj66NDSqHhHW6bWwnQFzMzbyoCNxJRUysPJw9LXjGxmLkF6Hv2edAAPaN9IZU+B9LtfqV8t3VajNPZPEHUJOpzJ96fwM2jy6imhyU4Rslj61baQUmCvNFtDaaQV9n1lY//oZVJnsqyaS2slscQvuodPyEVKWPMqK0F9M3WKFsQquPXI9dhNY2dZKc8C24Cntqc5UpN4W1aFU4u/cI7VSun152MLLL382YILChPxpNfaU1wm/R/YVFnooWw6oliaEZ+GByPZUtWE2eRM2pox/RMlj4XFot3V2R3JXgrThAIyFnSlbCtHjqrU220PoFB/5lyICBUQPvYOS8ncER3Q92ET4s3dlxqnHdt38mcnBt/4xVSlvEUriP1WgC3An2KpCRQsypGjCNnTPdnAkCx1D13sBBz2k62ogVUImuNIC7Lmr0h6Ml3iyXCsxzIRJ13PomzElFCwgmvxT8JnXxtpoQDuHgl3W2JcR4GNsppRQ8hvIx0MHRuqw3gcmdeWF4PqItqjHqKB0ZXGqfLnxJx602GHhoW5fhJF4hxQMZKUxKKu06vrdxqsNxgJiftGyo+iGjwx7fL98GqUiOgGT8BgkaHh/VNSIYa2uMJMgQa97tZMqJi6ZsMHCqhyMv88i8w+m6tKt9GOqfWHo+7xwA64QnZH+sWi2kw35nvREizoUKUAmAB519+SKqpgNBph30ntqWlb9650xpn2M/ukMhb7YMGedo398Os9/v6hAjAyYPJ39YoFxH6FUv5YOfP4+NPz7eGA5rXVbzI7pRQSJoeYdPz0C4q+/jQI4d5t/P0eyRHgDDMZ0tJ8ebL6wkqtNtxEXRq81LenxO1uQzqTxDfXgWE9vHV7iUDoach7xgIek9dY/okpp3iMVA41CBwL7Xnk5lO5Ayq4EmBrCkj4TD8vgrIDExf6gtIZNNdv7di+g7mceeE2S9yhhHOnIkPsZ2aZmxPMv8oB5bsRs0spNmieH9GVgd34tqz5rnAnHiqAeKsTgEV73JpKewApsndfthZk0z8HYaQj4tMIYBVAGtq9zPEq2BWyx2Rgg4FZMmZpJlK66br5Q01ZKB928UxtYwdkiNACUSX7bELn3DkUsgvYs1Jj/5MVddn7dVJpkCj5KGlCcTDg9x/GZ869qxPyNoVaL/BkvsAiz5mKhI+Y9NrOL8o2788unPjtSE0SQ84z2A/1gj4O4G9SpykVvGqCq55EkOMsE+GX36UKC33NJpf+RR1YMaWyLy44RcqdIEIcUb8hWwnyTZrNSlk12OZJezyLAkaLmZ17Rskkgj9W6C+A95GKMw4wiYyPlu+DRCziX6f6VRboMIvQ8rYaquYOMoLdoIA0mTTkDWmS++5MB9IgynvucqRZaCp/VJdJYtp8WZy07ICP3Xeo1GRz33e1yYeEMjlND+812pktE5T4OBjdlKU1Ly+7paJC2hcmuA1FfDW9yn3nEZ9xdkVnXLQlD1E8U8BYn2fby2kXfeYWnuwEiyUc5A2GD0s8VbYh18A64JGiReFusGiEzHxk9do1PhnkKsv1DnWWpzswn4+7ugId3sA4N6rgJMI7pD7YUPBI6uoAd+Z+inF647vStUQPh45aY4VqD5MUEQ/U2R1Fg3BWPmdcoiOIr2Hueo9WfYNc3vUznBCDW8DRcVDVsAdNMUFzguDg4wipawKMQ0SW/cj1EEP6uf0rNz/N2o4wQ2+KQbVp1h1eLyWuFPJdRhXtztFuR3nT9xjntM3KqftIZSh4AFDAV6O9OsPBwRbj4S+R6T+dAwgQRNzTvnQ7CoOLkaO2sRplWIOUL/eQJ944uoIqcjMfmfaOXh9BOlxBFsqka8mO/bx4nG7C2mKMLPCSOshrde4syUMBrXrDtxHT/+HapcO1uClTbr1ftS4C4GcFpfPmUJv+UghAhLHVMZ39mkuoJ+A8asgTUlEmysX3m6YnKac9QSwR519UIqtbTw54trIUlSSBMppwPvawvt2jCZsgktxb/UylDLNmazR7prbf5aO8c6FT0XdWzdarKN8vbc+iCegl/AJJNYheoUg6b9XV9NzUsl9IrBLY6OaV/dVrFDuTMAiMkwlDprEzn8N2yrfflfvNq5W+yS1kp9Wc14L439g3uShJ+9Smj2Pqpc+F5qqTZXh1g+MCio0T13QCY9ekryqalRn0lCxP3ePMSNJMpbrsF0JHzvgMytd5D/bbhvxPtFgq9b/IxsnPAKUfTiNphem7WJzwQMFOAgTdU44Ab9UnVvNpFZTnITuYGE8u0CtAtFiqU1UYr42hbbqL3Ctyv09Dw1diX1kPav0aa1TKlYWiw+OzBn8S6RElvr5kO984pX8zbXmiW95Rwy3orYDnv2u7Yk3qi7Hz1nq3tJuTGfBpF4YZ86hcpQ348VrHtvoREKpFsrfgxyZSHomhvPELl3YZMcQ6QOYrsqwL0PMxiunPOT3LGViDBLlNeZh5Gp5w4PlyB+k+mgV7FPzJVBB7PNUF7GozK7uCSo5dljyNTvTv7SwCaKMjHoQweS15vY3Vq883ztl3iYcTTjc4LHpe7qeyCcAO0IGEsrCucsLi+awBOLUK+VtdRxtwIJ6XXxCmigSo3ShyZ4CTkJESW/yKAzEaBp2/GN/V1RPa6k/hyGEIh06nzP2Cb/1HSA3eX9D9n1u/xKUECZrnJyef0xN75B4u4JwqsnGyZyoxuTVqArQMwyVBur1AQkouyIG6gPZzxFeYxGFxeyhXkbUOLYVCTFVgz9ohcH76KlFTaOQRE2d+wJaShRXAWOoGIhXE6mFdN0elpL2hUzQoxuIcOYF7lgWg0MPerbXy3zBPCBRMGeLaQW/zbWJ4GP9uNrKL9cM9j8EVvbPOlsHM83Yebzz6oR+z0B+KgRUWph2LSNseM4oiJzH9WebA3Wpi3uxtuJL+qymECq6WHMrpLj6En++ZBx2hLEo4zWa9kAm8O/v/hdUAZZyETxqrj1Wm8qG/j7VIooJVn4A6CbudwzQkaXjRPcffOpSRUJFFB6zyFZC4SGNNQNa/EPWaTyEWFc9/UcXuta9Whp/Y6tnZlC/fy2P5rsloqVt6EtxQju6o5IazTKkzK5IadKxqCMHmm+eYILBkr8/CD36nOqpi8bG6vvvdto8K8uVAuTc7acZJi0qklKd1sScL1M2xcftr4IGl1+1Fg4+wM9tYGTatWXcybBJv2LWvd/HI+5T7ia2Zu49T70oV72XSORRRQ+NBsOTyqrZZ1myGIAqPLV9VpSuG73zDlJ+PVLOHJTvQkQkuLVjb6gVNMgoezZ60XSi6fTYq53Q0iWLdpTnInO81UJujrrbZy6NKVLHNWEE1+Hs/4IjVDQa5yX7TJWmYBrTmeUqptJ28geq4Y6UWe1Slfn5VNTS+9rPqfj9sBsPYujVcjw1YuNRNmvFf+OMznx4yIOwOmIQI3rkC/modm9R8k/jVcSOHLoMIrptkRTDM7FeaLAKQVFSt2ErKBWn7YLitOs5b4jpn0LfcwSriI3NJFk6mJMiRTcLrInHfbATP0tZAfDn9Vq5srDkXSSYigpUPhxBWW1SHzEYl/It5NNjS91lHtWaH36Wh7sNinLlZVaFNzb9MGHnSDdth7UpKNvCEiYgir2DDFMUXqXlArCLPO7QTiwem7AC5t0jd2M9aHWEdpyTV0uyOS0rjXfH0TP8Ts0r6LZmV6Et3PkzHRv0tTAPgiwjnkI7NJruU8yoR9ihzJllOR0wbYVxQ3LiJwm2rGOr4FIH9EQh2w/KMdeCQiDGo7cVx5BJww==','2015-09-16 13:17:20','prequal'),(7,63,'Nz6mfLDYZ1DBCu8RLxODHmD261o2HwybYaKCT7xt2jlJyS4cduj6jnBj/nc68fyqwSmnLWU5kAo0dVZFbstaZqiz4F3jIk9ThzDJM11m8IX/Fq/mTxxvZ2emOP0QPkJj2CNR/I+Qkyh6g+Jm8yWS9Ri6BgSpmapXADpPBbbxZrEM4U16X5fEsRi+WWsnLJ3l0QO7hobYF1e93fa297lgvpo3ybT7cXKSNRJp6mUhs1DN57BX/WEiGfGco4RG3Eo7uvUrToDgXxojBZiJKyU5cpLqF5qITx/iYwRJWL8ftnlkYEbz1GxGCz1yvv86BhAbWXutphcADvQLR5yBD7ZnJx+P7l9sk7E/S+EWe5dsRncz94LBRMRqP3+5Xr3cyz0udS9eTkfIV79tP3YhhbRHneVwdcQBlcF7dk95uvTWZI8Gpu2tzgVQxhxCNoMilLnmpiB3TnDn51rQteK0i3GEu3neB7v3NDyeUL9dJyvh5NxLagwN0lH+wqdmHd0bZRgO4t1+GNks6FbV1tbPbA7E0pdm09cQn5VCd9ZdzB7UDxoWb09bNEk1c1avDEgKbSX3AqHasZa4mLtg4eX9UtdgC1RsK47KI2+BRjkIOOKmBLET9r1CX+Fgz6bVFsNdnbdt7Y7pei+9ErSMePARCfawj7tKJkseRWngf/N4s3nN4Acu3Pxet2Cownzh9nqkZPzOzTLsiO2Oo1DiK71LnFZ3dnFjsrwRbiw4XTVzQqPOioaFzf+elGJG0pBY9GJWZ7poe1EK3poqHNxTITSqky3VShMllLL0PonRZYv8pdAMKwTO3iR6atYRNjF91u6GdoQoepmJ4gi0JKoCem1zQOPNlnjrY+CencqSgztHhLrphiJ7EtTerTov12kw6Ye9JUwZLHsHArdWPaWwqzsKHJFcm1DV+SilGVNr9lOWx01lCXLeRNKUAUeo8ur1zdYSZ4hF2RdzEosuLg+VAhPpTadvpk+tS6V6WixjT/lENdcZobvjicBPH1Vu9oLboPcJiKP85FsgBCZxevBX1OkkydbH0/Sp9Bt9dMq0MR/dD/p+nMIP+tNawZkOdxUUwtJq4XJrRt2MWOlkXcx2Z0TWypc8A75HCZ0ZaHwtwUiWeJzCWagLvG4eT2qt1Tbmol/Br81XdDdZklqygpxh7Isat9uBnPFtiHjdQP0OYp6qesNHJwuvgeeZ0OgzK0nGdIGa2X/p9mP1Hrxx0wVt9iwIXXsusgFa+m0pOTG7yeOzxirjKe6g3EfuJLK9ImxwPhv9roeuJiJDCMzP3ubJ0BHO4XxJhDWRGpQyTJS7eYDRaAWjX2W6fZEbNOEwhasBwZoD8LaFZejFZeGraNgyTDyVHgTzyl4mtRZMnBOb22t9clH7ZST7/esxxiekjqio1cOUxFLY2w0GpVc8mioZZQQGMIEb6/F7xnM5XZvJo08NtPUWUvHj2SIMQ/DlLh5ZzZW/3wIGG6N6Y08KvBdZrWjQalUGXfMvX+d61CtZoDHbQLtUtdjRQrY+uF5gs42g4lj8SEAJehqK5HCtmks/5i+gKUrcTQMzAZOlY+A/d+u2krV13HwztqhwbAQLhcRpU1jgzh0AgmOgQuJw8/z/K3ah2TIaaJkPy5RsE5sGs9gjbEtlQIGDzbNRKEGuL6HSLMQPzVxKYvxHjlNjSr4meXa1U4NGIUGhXWRl14WQu/B07CeNMR+sVITuLWsM57MsvXn4TG6WsSQGcUfd5cIi42ITwootkyYfxtUyojIxg0yDoyUzLyBv8Eg+gJJBN/kt87SPpqYHWbYLT0H1sNkFS4RyFsEdgCImt6hRhlp4FVP1Y/+aGKo=','2015-09-16 13:17:21','tu_snapshot');
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
INSERT INTO `cj_applicant_score` VALUES (1,17,'xSdBIkJHFo8KXmCKAMfOcggrz21CgMESW7ZxPkJ1koo=','2015-09-21 13:17:12'),(2,17,'RFH1mTIW5XpaWqAfhUGPJyjHFz6/hE52BcmKfoNgIH0=','2015-09-21 13:17:12'),(3,17,'w/nDd03bp1uBqjFj9Xw4Vb86w5UY1UH/Ej1B/Mnul2U=','2015-09-21 13:17:12'),(4,17,'34oBjhTFh+4wA8g/nm5Wm6wJPH4VCLlZ/at/u5p8mjA=','2015-09-21 13:17:12'),(5,18,'XY9GhBvU22Hq7oNauAfUUHecvniQlz+PswK+UhmdIJQ=','2015-09-21 13:17:12'),(6,19,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2015-09-21 13:17:12'),(7,19,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2015-09-21 13:17:12'),(8,19,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=','2015-09-21 13:17:12'),(9,19,'XY9GhBvU22Hq7oNauAfUUHecvniQlz+PswK+UhmdIJQ=','2015-09-21 13:17:12'),(10,21,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2015-09-21 13:17:12'),(11,21,'Ca7GTJiDpe5XsvEeJFTTE5ELsdxRK4xb5aT0Ix4/7IE=','2015-09-21 13:17:12'),(12,21,'akEMMXputQ3AkJc4QZmt7/nqM6XgTZcoUHsUWwPrrj0=','2015-09-21 13:17:12'),(13,21,'afdeQUJmgFpq/XX0QznfgEpddohhfCCjIrRy7saIQ1g=','2015-09-21 13:17:12'),(14,21,'ex3LnR4a1dAhMk4V0r959Oqeze8rW5pJkts15Sub1Z4=','2015-09-21 13:17:13'),(15,39,'XUEY91PUYggzpPltC9h/Z3GQu1RKXlJQ5xWj57E+7s0=','2015-09-21 13:17:13'),(16,39,'+Lve9xXutKVnJlAhVD54bPWEzThG6XUUex5xcoVg9nA=','2015-09-21 13:17:13'),(17,39,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2015-09-21 13:17:13'),(18,39,'tLLokqIfIGSLphmUHf9RivHOzu2sh1c6UfRbXFQk2Qc=','2015-09-21 13:17:13'),(19,39,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2015-09-21 13:17:13'),(20,39,'RFH1mTIW5XpaWqAfhUGPJyjHFz6/hE52BcmKfoNgIH0=','2015-09-21 13:17:13'),(21,40,'XUEY91PUYggzpPltC9h/Z3GQu1RKXlJQ5xWj57E+7s0=','2015-09-21 13:17:13'),(22,40,'+Lve9xXutKVnJlAhVD54bPWEzThG6XUUex5xcoVg9nA=','2015-09-21 13:17:13'),(23,40,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2015-09-21 13:17:13'),(24,40,'tLLokqIfIGSLphmUHf9RivHOzu2sh1c6UfRbXFQk2Qc=','2015-09-21 13:17:13'),(25,40,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2015-09-21 13:17:13'),(26,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2015-09-21 13:17:13'),(27,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2015-09-21 13:17:13'),(28,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2015-09-21 13:17:13'),(29,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2015-09-21 13:17:13'),(30,40,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2015-09-21 13:17:13'),(31,30,'cOtHvwVfEA0BnhZiDoudEIJlm2ou1yVjsRUEyAfO5OY=','2015-09-21 13:17:13'),(32,30,'av/lCZmUqaEFFAZFnG6f/BJw8iLmJua19poEyeYHEPY=','2015-09-21 13:17:14'),(33,30,'Ca7GTJiDpe5XsvEeJFTTE5ELsdxRK4xb5aT0Ix4/7IE=','2015-09-21 13:17:14'),(34,30,'p+BA0IgolM41IPk5n3Vp0168W8rZwne2nhb1kii7w3Q=','2015-09-21 13:17:14'),(35,30,'hwFCfhgNxBFe0L5bdiS6M9xzIixYEBgTG7IoHQ4HMNs=','2015-09-21 13:17:14'),(36,63,'Ca7GTJiDpe5XsvEeJFTTE5ELsdxRK4xb5aT0Ix4/7IE=','2015-09-21 13:17:22'),(37,50,'z6HDb0wjWIIua7NClmFbOXrMdbGllQOGet9q63DFQg8=','2015-09-21 13:17:22'),(38,63,'hV6rZx8WxG4/k6+lItnXQd4lJyxkVrMOhX6S+M2JcyY=','2015-09-21 13:17:22'),(39,63,'PYozHgNoAWug6XzzXjRhsCblLrntIMhFT5qACfOeWnU=','2015-09-21 13:17:22');
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
  CONSTRAINT `FK_3561230752E95DE5` FOREIGN KEY (`cj_group_id`) REFERENCES `rj_group` (`id`),
  CONSTRAINT `FK_356123071846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_applicant_tradelines`
--

LOCK TABLES `cj_applicant_tradelines` WRITE;
/*!40000 ALTER TABLE `cj_applicant_tradelines` DISABLE KEYS */;
INSERT INTO `cj_applicant_tradelines` VALUES (1,24,2,'56','2861af59828d52986478e107e668b275',0,0,0,'2015-09-21 13:17:14','2015-09-21 13:17:14'),(2,24,2,'97','a40f772bd70becb8d3b290751eac3c84',0,0,0,'2015-09-21 13:17:14','2015-09-21 13:17:14');
/*!40000 ALTER TABLE `cj_applicant_tradelines` ENABLE KEYS */;
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
  CONSTRAINT `FK_CFE38D5FFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`),
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
  CONSTRAINT `FK_7434DF5452E95DE5` FOREIGN KEY (`cj_group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_group_incentives`
--

LOCK TABLES `cj_group_incentives` WRITE;
/*!40000 ALTER TABLE `cj_group_incentives` DISABLE KEYS */;
INSERT INTO `cj_group_incentives` VALUES (1,2,1,NULL,'Car washing','We\'ll wash your car, he-he....','2015-09-21 13:17:14'),(2,2,2,NULL,'Accessories','15% on BFGoodReach Tires','2015-09-21 13:17:14'),(3,2,0,NULL,'Accessories','50% on china details','2015-09-21 13:17:14'),(4,2,3,0,'Accessories','5% Original details','2015-09-21 13:17:14'),(5,1,0,NULL,'Car washing','We\'ll wash your car, he-he....','2015-09-21 13:17:14'),(6,1,1,NULL,'Accessories','10% on BFGoodReach Tires','2015-09-21 13:17:14'),(7,2,4,NULL,'Accessories','10% on Good Year Tires','2015-09-21 13:17:14');
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
  `api_integration_type` enum('none','yardi voyager','resman','mri','amsi') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none' COMMENT '(DC2Type:ApiIntegrationType)',
  `is_allowed_future_contract` tinyint(1) NOT NULL DEFAULT '0',
  `is_payment_processor_locked` tinyint(1) NOT NULL DEFAULT '0',
  `payments_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_holding`
--

LOCK TABLES `cj_holding` WRITE;
/*!40000 ALTER TABLE `cj_holding` DISABLE KEYS */;
INSERT INTO `cj_holding` VALUES (1,'Darryl\'s Holding','2015-09-21 13:17:10','2015-09-21 13:17:10','none',0,0,1),(2,'Moss Holding','2015-09-21 13:17:10','2015-09-21 13:17:10','none',0,0,1),(3,'Test Holding','2015-09-21 13:17:10','2015-09-21 13:17:10','none',0,0,1),(4,'700Credit','2015-07-23 13:17:10','2015-09-13 13:17:10','none',0,0,1),(5,'Rent Holding','2015-07-23 13:17:10','2015-09-13 13:17:10','yardi voyager',0,0,1),(6,'Estate Holding','2015-07-23 13:17:10','2015-09-13 13:17:10','none',0,0,1),(7,'Test RentHolding 2','2015-07-23 13:17:10','2015-09-13 13:17:10','none',0,0,1),(8,'Test Rent Holding 3','2015-07-23 13:17:10','2015-09-13 13:17:10','none',0,0,1),(9,'For Group Without Holding','2015-07-23 13:17:10','2015-09-13 13:17:10','none',0,0,1);
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
  CONSTRAINT `FK_3DCB43F752E95DE5` FOREIGN KEY (`cj_group_id`) REFERENCES `rj_group` (`id`),
  CONSTRAINT `FK_3DCB43F71846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_3DCB43F7ED8F6A55` FOREIGN KEY (`cj_account_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_lead`
--

LOCK TABLES `cj_lead` WRITE;
/*!40000 ALTER TABLE `cj_lead` DISABLE KEYS */;
INSERT INTO `cj_lead` VALUES (1,17,2,2,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(2,18,4,3,664,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(3,19,4,3,580,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(4,20,4,3,600,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(5,21,4,3,650,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'processed','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(6,22,4,3,714,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(7,23,2,2,552,'Honda CR-V','https://carimg.s3.amazonaws.com/8477_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(8,24,2,2,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(9,25,2,2,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(10,26,2,2,830,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(11,27,2,2,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(12,28,9,11,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(13,27,9,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(14,29,5,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(15,30,5,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(16,31,5,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(17,32,5,9,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(18,33,5,11,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(19,34,5,9,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(20,35,6,4,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(21,36,6,4,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(22,37,6,4,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(23,40,6,9,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2015-09-16 13:17:12','2015-09-16 13:17:12'),(24,39,6,11,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','office','2015-08-02 13:17:12','2015-08-02 13:17:12'),(25,21,2,2,510,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2015-08-02 13:17:12','2015-08-02 13:17:12'),(26,21,5,9,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','office','2015-08-02 13:17:12','2015-08-02 13:17:12'),(27,41,14,23,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','office','2015-09-11 13:17:12','2015-08-02 13:17:12'),(28,15,14,23,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','office','2015-09-11 13:17:12','2015-08-02 13:17:12');
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
INSERT INTO `cj_lead_history` VALUES (1,1,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(2,2,NULL,664,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(3,3,NULL,580,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(4,4,NULL,600,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(5,5,NULL,650,'BMW X5','https://carimg.s3.amazonaws.com/6800_st0640_037.jpg',1,0,NULL,0,'processed','2015-09-21 13:17:12'),(6,6,NULL,714,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(7,7,NULL,552,'Honda CR-V','https://carimg.s3.amazonaws.com/8477_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(8,8,NULL,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(9,9,NULL,600,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(10,10,NULL,830,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(11,11,NULL,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(12,12,NULL,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(13,13,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(14,14,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(15,15,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(16,16,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(17,17,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(18,18,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(19,19,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(20,20,NULL,700,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'new','2015-09-21 13:17:12'),(21,21,NULL,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(22,22,NULL,550,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','2015-09-21 13:17:12'),(23,23,NULL,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(24,24,NULL,710,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'finished','2015-09-21 13:17:12'),(25,25,NULL,510,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(26,26,NULL,620,'Honda Civic','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,0,'active','2015-09-21 13:17:12'),(27,27,NULL,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','2015-09-21 13:17:12'),(28,28,NULL,670,'Hunday','https://carimg.s3.amazonaws.com/8354_st0640_037.jpg',1,0,NULL,25,'active','2015-09-21 13:17:12');
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
  `attempts` bigint(20) NOT NULL DEFAULT '0',
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
  `type` enum('charge','custom','other','rent','report') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OperationType)',
  `amount` decimal(10,2) NOT NULL,
  `paid_for` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_21F5D92D2A26A0ED` (`cj_applicant_report_id`),
  KEY `IDX_21F5D92D8D9F6D38` (`order_id`),
  KEY `IDX_21F5D92D2576E0FD` (`contract_id`),
  KEY `IDX_21F5D92DFE54D947` (`group_id`),
  CONSTRAINT `FK_21F5D92DFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`),
  CONSTRAINT `FK_21F5D92D2576E0FD` FOREIGN KEY (`contract_id`) REFERENCES `rj_contract` (`id`),
  CONSTRAINT `FK_21F5D92D2A26A0ED` FOREIGN KEY (`cj_applicant_report_id`) REFERENCES `cj_applicant_report` (`id`),
  CONSTRAINT `FK_21F5D92D8D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_operation`
--

LOCK TABLES `cj_operation` WRITE;
/*!40000 ALTER TABLE `cj_operation` DISABLE KEYS */;
INSERT INTO `cj_operation` VALUES (1,1,1,NULL,NULL,'report',9.00,'2015-09-21','2015-09-21 13:17:14'),(2,2,NULL,9,NULL,'rent',1500.00,'2015-08-02','2015-09-21 13:17:18'),(3,3,NULL,9,NULL,'rent',1500.00,'2015-08-12','2015-09-21 13:17:18'),(4,3,NULL,9,NULL,'other',300.00,'2015-08-12','2015-09-21 13:17:18'),(5,4,NULL,9,NULL,'rent',1500.00,'2015-08-22','2015-09-21 13:17:18'),(6,5,NULL,9,NULL,'rent',1500.00,'2015-08-22','2015-09-21 13:17:18'),(7,6,NULL,9,NULL,'rent',1500.00,'2015-09-21','2015-09-21 13:17:18'),(8,7,NULL,9,NULL,'rent',700.00,'2015-08-22','2015-09-21 13:17:18'),(9,8,NULL,9,NULL,'rent',750.00,'2015-08-22','2015-09-21 13:17:18'),(10,9,NULL,9,NULL,'rent',800.00,'2015-08-22','2015-09-21 13:17:18'),(11,10,NULL,9,NULL,'rent',1500.00,'2015-09-01','2015-09-21 13:17:18'),(12,11,NULL,9,NULL,'rent',1500.00,'2015-09-11','2015-09-21 13:17:18'),(13,12,NULL,7,NULL,'rent',1500.00,'2015-08-21','2015-09-21 13:17:18'),(14,13,NULL,8,NULL,'rent',3700.00,'2015-09-21','2015-09-21 13:17:18'),(15,14,NULL,3,NULL,'rent',1500.00,'2014-09-16','2015-09-21 13:17:18'),(16,15,NULL,3,NULL,'rent',1500.00,'2015-09-21','2015-09-21 13:17:18'),(17,16,NULL,3,NULL,'rent',1500.00,'2014-10-16','2015-09-21 13:17:18'),(18,17,NULL,3,NULL,'rent',1500.00,'2014-11-15','2015-09-21 13:17:18'),(19,18,NULL,3,NULL,'rent',1500.00,'2014-12-15','2015-09-21 13:17:18'),(20,19,NULL,3,NULL,'rent',1500.00,'2015-01-14','2015-09-21 13:17:18'),(21,20,NULL,3,NULL,'rent',1500.00,'2015-02-13','2015-09-21 13:17:18'),(22,21,NULL,3,NULL,'rent',1500.00,'2015-03-15','2015-09-21 13:17:18'),(23,22,NULL,3,NULL,'rent',1500.00,'2015-04-14','2015-09-21 13:17:18'),(24,23,NULL,3,NULL,'rent',1500.00,'2015-05-14','2015-09-21 13:17:18'),(25,24,NULL,3,NULL,'rent',1500.00,'2015-06-13','2015-09-21 13:17:18'),(26,25,NULL,4,NULL,'rent',1250.00,'2015-01-21','2015-01-21 13:17:18'),(27,26,NULL,4,NULL,'rent',1250.00,'2015-02-21','2015-02-21 13:17:18'),(28,27,NULL,4,NULL,'rent',1250.00,'2015-03-21','2015-03-21 13:17:18'),(29,28,NULL,4,NULL,'rent',1250.00,'2015-04-21','2015-04-21 13:17:18'),(30,29,NULL,4,NULL,'rent',1250.00,'2015-05-21','2015-05-21 13:17:18'),(31,30,NULL,4,NULL,'rent',1250.00,'2015-06-21','2015-06-21 13:17:18'),(32,31,NULL,4,NULL,'rent',1250.00,'2015-08-21','2015-08-21 13:17:18'),(33,32,NULL,2,NULL,'rent',1250.00,'2014-10-21','2015-09-21 13:17:18'),(34,33,NULL,2,NULL,'rent',1250.00,'2014-11-21','2015-09-21 13:17:18'),(35,34,NULL,2,NULL,'rent',1250.00,'2014-12-21','2015-09-21 13:17:18'),(36,35,NULL,2,NULL,'rent',1250.00,'2015-01-21','2015-09-21 13:17:18'),(37,36,NULL,2,NULL,'rent',1250.00,'2015-02-21','2015-09-21 13:17:18'),(38,37,NULL,2,NULL,'rent',1250.00,'2015-03-21','2015-09-21 13:17:18'),(39,38,NULL,2,NULL,'rent',1250.00,'2015-04-21','2015-09-21 13:17:18'),(40,39,NULL,2,NULL,'rent',1250.00,'2015-05-21','2015-09-21 13:17:18'),(41,40,NULL,2,NULL,'rent',1250.00,'2015-06-21','2015-09-21 13:17:18'),(42,41,NULL,2,NULL,'rent',1250.00,'2015-07-21','2015-09-21 13:17:18'),(43,42,NULL,2,NULL,'rent',1250.00,'2015-08-21','2015-09-21 13:17:18'),(44,43,NULL,2,NULL,'rent',1250.00,'2015-09-21','2015-09-21 13:17:18'),(45,44,NULL,20,NULL,'rent',1.00,'2015-09-20','2015-09-21 13:17:18'),(46,45,NULL,20,NULL,'rent',2.00,'2015-09-20','2015-09-21 13:17:18'),(47,46,NULL,16,NULL,'rent',1500.00,'2015-08-21','2015-09-21 13:17:18'),(48,47,NULL,18,NULL,'rent',987.00,'2014-01-01','2014-01-11 00:00:00'),(49,47,NULL,18,NULL,'other',13.00,'2014-01-01','2014-01-01 00:00:00'),(50,48,NULL,18,NULL,'rent',987.00,'2014-02-01','2014-02-01 00:00:00'),(51,48,NULL,18,NULL,'other',13.00,'2014-02-01','2014-02-01 00:00:00'),(52,49,NULL,18,NULL,'rent',987.00,'2014-03-01','2014-03-01 00:00:00'),(53,49,NULL,18,NULL,'other',13.00,'2014-03-01','2014-03-01 00:00:00'),(54,50,NULL,18,NULL,'rent',820.00,'2014-04-01','2014-04-01 00:00:00'),(55,51,NULL,18,NULL,'rent',180.00,'2014-04-01','2014-05-05 00:00:00'),(56,52,NULL,23,NULL,'rent',500.00,'2015-09-21','2015-09-21 13:17:18'),(57,52,NULL,23,NULL,'rent',500.00,'2015-10-21','2015-09-21 13:17:18'),(58,52,NULL,23,NULL,'other',111.00,'2015-09-21','2015-09-21 13:17:18'),(59,5,NULL,9,NULL,'rent',1500.00,'2015-09-21','2015-09-21 13:17:18');
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
  `payment_account_id` bigint(20) DEFAULT NULL,
  `deposit_account_id` int(11) DEFAULT NULL,
  `status` enum('cancelled','complete','error','new','pending','refunded','refunding','reissued','returned','sending') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OrderStatus)',
  `payment_type` enum('bank','card','cash') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OrderPaymentType)',
  `sum` decimal(10,2) NOT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `payment_processor` enum('heartland','aci') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentProcessor)',
  `descriptor` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_type` enum('submerchant','pay_direct') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OrderAlgorithmType)',
  PRIMARY KEY (`id`),
  KEY `IDX_DA53B53D1846CDE5` (`cj_applicant_id`),
  KEY `IDX_DA53B53DAE9DDE6F` (`payment_account_id`),
  KEY `IDX_DA53B53D6E60BC73` (`deposit_account_id`),
  CONSTRAINT `FK_DA53B53D6E60BC73` FOREIGN KEY (`deposit_account_id`) REFERENCES `rj_deposit_account` (`id`),
  CONSTRAINT `FK_DA53B53D1846CDE5` FOREIGN KEY (`cj_applicant_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_DA53B53DAE9DDE6F` FOREIGN KEY (`payment_account_id`) REFERENCES `rj_payment_account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_order`
--

LOCK TABLES `cj_order` WRITE;
/*!40000 ALTER TABLE `cj_order` DISABLE KEYS */;
INSERT INTO `cj_order` VALUES (1,21,NULL,NULL,'complete','cash',9.00,NULL,'2015-09-21 13:17:14','2015-09-21 13:17:14','heartland',NULL,'submerchant'),(2,42,9,NULL,'complete','card',1500.00,NULL,'2015-08-02 13:17:17','2015-08-02 13:17:17','heartland',NULL,'pay_direct'),(3,42,NULL,NULL,'complete','card',1800.00,NULL,'2015-08-12 13:17:17','2015-08-12 13:17:17','heartland',NULL,'submerchant'),(4,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-08-22 13:17:17','2015-08-22 13:17:17','heartland',NULL,'submerchant'),(5,42,NULL,NULL,'error','card',3000.00,NULL,'2015-08-22 13:17:17','2015-08-22 13:17:17','heartland',NULL,'submerchant'),(6,42,NULL,NULL,'cancelled','card',1500.00,NULL,'2015-09-21 13:17:17','2015-09-21 13:17:17','heartland',NULL,'submerchant'),(7,42,NULL,NULL,'refunded','card',700.00,NULL,'2015-08-22 13:17:17','2015-08-22 13:17:17','heartland',NULL,'submerchant'),(8,42,NULL,NULL,'returned','card',750.00,NULL,'2015-08-22 13:17:17','2015-08-22 13:17:17','heartland',NULL,'submerchant'),(9,42,NULL,NULL,'returned','bank',800.00,NULL,'2015-08-22 13:17:17','2015-08-22 13:17:17','heartland',NULL,'submerchant'),(10,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-09-01 13:17:17','2015-09-01 13:17:17','heartland',NULL,'submerchant'),(11,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-09-11 13:17:17','2015-09-11 13:17:17','heartland',NULL,'submerchant'),(12,42,NULL,NULL,'new','card',1500.00,NULL,'2015-09-19 13:17:17','2015-09-19 13:17:17','heartland',NULL,'submerchant'),(13,47,NULL,NULL,'new','card',3700.00,NULL,'2015-09-21 13:17:17','2015-09-21 13:17:17','heartland',NULL,'submerchant'),(14,42,9,NULL,'complete','card',1500.00,NULL,'2014-09-16 13:17:17','2014-09-14 13:17:17','heartland',NULL,'submerchant'),(15,42,NULL,NULL,'complete','card',1500.00,NULL,'2014-10-16 13:17:17','2014-10-21 13:17:17','heartland',NULL,'submerchant'),(16,42,NULL,NULL,'complete','card',1500.00,NULL,'2014-10-16 13:17:17','2014-10-14 13:17:17','heartland',NULL,'submerchant'),(17,42,NULL,NULL,'complete','card',1500.00,NULL,'2014-11-15 13:17:17','2014-11-13 13:17:17','heartland',NULL,'submerchant'),(18,42,NULL,NULL,'complete','card',1500.00,NULL,'2014-12-15 13:17:17','2014-12-13 13:17:17','heartland',NULL,'submerchant'),(19,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-01-14 13:17:17','2015-01-12 13:17:17','heartland',NULL,'submerchant'),(20,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-02-13 13:17:17','2015-02-11 13:17:17','heartland',NULL,'submerchant'),(21,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-03-15 13:17:17','2015-03-13 13:17:17','heartland',NULL,'submerchant'),(22,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-04-14 13:17:17','2015-04-12 13:17:17','heartland',NULL,'submerchant'),(23,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-05-14 13:17:17','2015-05-12 13:17:17','heartland',NULL,'submerchant'),(24,42,NULL,NULL,'complete','card',1500.00,NULL,'2015-06-13 13:17:17','2015-06-11 13:17:17','heartland',NULL,'submerchant'),(25,43,NULL,NULL,'complete','card',1250.00,NULL,'2015-01-21 13:17:17','2015-01-21 13:17:17','heartland',NULL,'submerchant'),(26,43,NULL,NULL,'complete','card',1250.00,NULL,'2015-02-21 13:17:17','2015-02-21 13:17:17','heartland',NULL,'submerchant'),(27,43,NULL,NULL,'complete','card',1250.00,NULL,'2015-03-21 13:17:17','2015-03-21 13:17:17','heartland',NULL,'submerchant'),(28,43,NULL,NULL,'complete','card',1250.00,NULL,'2015-04-21 13:17:17','2015-04-21 13:17:17','heartland',NULL,'submerchant'),(29,43,NULL,NULL,'complete','card',1250.00,NULL,'2015-05-21 13:17:17','2015-05-21 13:17:17','heartland',NULL,'submerchant'),(30,43,NULL,NULL,'complete','card',1250.00,NULL,'2015-06-21 13:17:17','2015-06-21 13:17:17','heartland',NULL,'submerchant'),(31,43,NULL,NULL,'complete','card',1250.00,NULL,'2015-07-21 13:17:17','2015-07-21 13:17:17','heartland',NULL,'submerchant'),(32,42,NULL,NULL,'complete','card',1250.00,NULL,'2014-10-21 13:17:17','2014-10-21 13:17:17','heartland',NULL,'submerchant'),(33,42,NULL,NULL,'complete','card',1250.00,NULL,'2014-11-21 13:17:17','2014-11-21 13:17:17','heartland',NULL,'submerchant'),(34,42,NULL,NULL,'complete','card',1250.00,NULL,'2014-11-21 13:17:17','2014-11-21 13:17:17','heartland',NULL,'submerchant'),(35,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-01-21 13:17:17','2015-01-21 13:17:17','heartland',NULL,'submerchant'),(36,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-02-21 13:17:17','2015-02-21 13:17:17','heartland',NULL,'submerchant'),(37,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-04-21 13:17:17','2015-04-21 13:17:17','heartland',NULL,'submerchant'),(38,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-04-21 13:17:17','2015-04-21 13:17:17','heartland',NULL,'submerchant'),(39,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-05-21 13:17:17','2015-05-21 13:17:17','heartland',NULL,'submerchant'),(40,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-06-21 13:17:17','2015-06-21 13:17:17','heartland',NULL,'submerchant'),(41,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-07-21 13:17:17','2015-07-21 13:17:17','heartland',NULL,'submerchant'),(42,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-08-21 13:17:17','2015-08-21 13:17:17','heartland',NULL,'submerchant'),(43,42,NULL,NULL,'complete','card',1250.00,NULL,'2015-09-20 13:17:17','2015-09-20 13:17:17','heartland',NULL,'submerchant'),(44,53,NULL,NULL,'complete','card',1.00,NULL,'2015-09-20 13:17:17','2015-09-20 13:17:17','heartland',NULL,'submerchant'),(45,53,NULL,NULL,'complete','card',2.00,NULL,'2015-09-20 13:17:17','2015-09-20 13:17:17','heartland',NULL,'submerchant'),(46,53,NULL,NULL,'complete','card',1500.00,NULL,'2015-08-21 13:17:17','2015-08-21 13:17:17','heartland',NULL,'submerchant'),(47,42,NULL,NULL,'complete','card',1000.00,NULL,'2014-01-01 00:00:00','2014-01-01 00:00:00','heartland',NULL,'submerchant'),(48,42,NULL,NULL,'complete','card',1000.00,NULL,'2014-02-01 00:00:00','2014-02-01 00:00:00','heartland',NULL,'submerchant'),(49,42,NULL,NULL,'complete','card',1000.00,NULL,'2014-03-01 00:00:00','2014-03-01 00:00:00','heartland',NULL,'submerchant'),(50,42,NULL,NULL,'complete','card',820.00,NULL,'2014-04-01 00:00:00','2014-04-01 00:00:00','heartland',NULL,'submerchant'),(51,42,NULL,NULL,'complete','card',180.00,NULL,'2014-05-05 00:00:00','2014-05-05 00:00:00','heartland',NULL,'submerchant'),(52,57,NULL,NULL,'complete','card',1111.00,NULL,'2015-09-20 13:17:17','2015-09-20 13:17:17','heartland',NULL,'submerchant');
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
  `precise_id_user_pwd` longtext COLLATE utf8_unicode_ci NOT NULL,
  `precise_id_eai` longtext COLLATE utf8_unicode_ci NOT NULL,
  `credit_profile_user_pwd` longtext COLLATE utf8_unicode_ci NOT NULL,
  `credit_profile_eai` longtext COLLATE utf8_unicode_ci NOT NULL,
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
INSERT INTO `cj_settings` VALUES (1,'CIPy7wPejGPZGMyIZV4ZrB+mlX7/L62i+2mRljqdwjnIYE4pv0k8pPm8fH5BGH4dFMR3UTFgN0QoqdnDe4/cgA==','uvzrzV024jBbGB74YAx3DAXYlB0XyF1wXwgbRlCBtTM=','5DjdCLI+g3OLUBrWFiRNlc6czeVCfBnSS5VD4BhEODc=','uvzrzV024jBbGB74YAx3DAXYlB0XyF1wXwgbRlCBtTM=','Test Contract text','Some rules','2015-09-21 13:17:14');
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
  `last_ip` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('admin','applicant','dealer','tenant','tenant','landlord','partner') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:UserType)',
  `external_landlord_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_98C9F47592FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_98C9F475A0D96FBF` (`email_canonical`),
  UNIQUE KEY `UNIQ_98C9F4756F21F112` (`invite_code`),
  KEY `IDX_98C9F4756CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_98C9F4756CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cj_user`
--

LOCK TABLES `cj_user` WRITE;
/*!40000 ALTER TABLE `cj_user` DISABLE KEYS */;
INSERT INTO `cj_user` VALUES (1,NULL,'admin@creditjeeves.com','admin@creditjeeves.com','admin@creditjeeves.com','admin@creditjeeves.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','5ca33d221fd09f16c1ecba9c1aadc3eb','2015-09-20 13:17:11',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Administrator',NULL,'Super','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK1M7N',1,1,'en',1,'none',NULL,0,1,'2015-07-23 13:17:11','2015-09-21 13:17:11',NULL,'admin',NULL),(2,NULL,'honda-admin','honda-admin','honda-admin','honda-admin',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Bill',NULL,'Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5CKV',1,1,'en',1,'none',NULL,0,1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(3,NULL,'honda@example.com','honda@example.com','honda@example.com','honda@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Honda',NULL,'Dealer','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5D39',1,1,'en',1,'none',NULL,0,0,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(4,NULL,'alex.emelyanov.ua@gmail.com','alex.emelyanov.ua@gmail.com','alex.emelyanov.ua@gmail.com','alex.emelyanov.ua@gmail.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Alex',NULL,'Emelyanov','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5DHI',1,1,'en',1,'none',NULL,0,1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(5,1,'darryl@cars.com','darryl@cars.com','darryl@cars.com','darryl@cars.com',1,'dthzxwo5em0w8so4kcogskskko8wsoo','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Darryl',NULL,'Eaton','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5DWN',1,1,'en',1,'none',NULL,1,1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(6,1,'ton@cars.com','ton@cars.com','ton@cars.com','ton@cars.com',1,'jov7szif74ocokso4k8gsc8wcc8osgo','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ton',NULL,'Sharp','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5EBG',1,1,'en',1,'none',NULL,0,0,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(7,1,'alex@cars.com','alex@cars.com','alex@cars.com','alex@cars.com',1,'n1y1foj5rr448gscksg4gog0w84ss4c','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Alex',NULL,'Emelyanov','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5ER4',1,1,'en',1,'none',NULL,0,0,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(8,1,'zane@cars.com','zane@cars.com','zane@cars.com','zane@cars.com',1,'djlutrxztncc40okog08gwgcwc40os8','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Zane',NULL,'Stagg','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5F6F',1,1,'en',1,'none',NULL,0,0,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(9,1,'darryl@autotrader.com','darryl@autotrader.com','darryl@autotrader.com','darryl@autotrader.com',1,'4axqnxo9e7qc8ccsssk8kko0wgkgcgg','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Darryl',NULL,'Eaton','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5FM6',1,1,'en',1,'none',NULL,1,1,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(10,1,'ton@autotrader.com  ','ton@autotrader.com  ','ton@autotrader.com  ','ton@autotrader.com  ',1,'4qcd9ozo45usok84okos4gg484sw0w0','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ton',NULL,'Sharp','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5G1E',1,1,'en',1,'none',NULL,0,0,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'dealer',NULL),(11,1,'zane@autotrader.com','zane@autotrader.com','zane@autotrader.com','zane@autotrader.com',1,'4xkaen6zyb4s084cwogsso80skcwck4','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Zane',NULL,'Stagg','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5GGS',1,1,'en',1,'none',NULL,0,0,'2012-11-29 14:33:35','2015-09-21 13:17:11',NULL,'dealer',NULL),(12,NULL,'darryl@autonation.com','darryl@autonation.com','darryl@autonation.com','darryl@autonation.com',1,'te0s83zdoi8cs4ko4ggcwc0sswskkcg','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Darryl',NULL,'Eaton','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5GVR',1,1,'en',1,'none',NULL,0,1,'2012-11-29 14:36:16','2015-09-21 13:17:11',NULL,'dealer',NULL),(13,1,'audi@example.com','audi@example.com','audi@example.com','audi@example.com',1,'j5wgeqy8r1ss4sk0wc4o0c08ws8kog8','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'AUDI',NULL,'Dealer','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5HAZ',1,1,'en',1,'none',NULL,0,0,'2012-11-29 14:36:16','2015-09-21 13:17:11',NULL,'dealer',NULL),(14,4,'support@700credit.com','support@700credit.com','support@700credit.com','support@700credit.com',1,'jsckt1f1tn4cgwg40kcsksc80wccoso','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'700Credit',NULL,'700Credit','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'1a1dc91c9073',1,1,'en',1,'none',NULL,1,1,'2012-11-29 14:36:16','2015-09-21 13:17:11',NULL,'dealer',NULL),(15,4,'support2@700credit.com','support2@700credit.com','support2@700credit.com','support2@700credit.com',1,'qq0701iawys4c00swcgwooks8ss4k44','7b3e63c45d5cb6859f325ab1447321ef',NULL,0,0,NULL,NULL,NULL,'a:1:{i:0;s:10:\"CREDIT_API\";}',0,NULL,'700CreditAPI',NULL,'700CreditAPI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5I6C',1,1,'en',1,'none',NULL,1,1,'2012-11-29 14:36:16','2015-09-21 13:17:11',NULL,'dealer',NULL),(16,NULL,'api@usequity.com','api@usequity.com','api@usequity.com','api@usequity.com',1,'8phum1bknikocckgcoo8co4kg4o04g4','848c4abcaa73a1c14c273cf0d394d4a8',NULL,0,0,NULL,NULL,NULL,'a:1:{i:0;s:13:\"USE_QUITY_API\";}',0,NULL,'USEquityAPI',NULL,'USEquityAPI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QK5ILW',1,1,'en',1,'none',NULL,1,1,'2012-11-29 14:36:16','2015-09-21 13:17:11',NULL,'dealer',NULL),(17,NULL,'alexey.karpik+app1334753295955955@gmail.com','alexey.karpik+app1334753295955955@gmail.com','alexey.karpik+app1334753295955955@gmail.com','alexey.karpik+app1334753295955955@gmail.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ivan','Petrovich','Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3751181804','1980-10-22','18LW1dmYAbWnogX0Jj9fJlxbu0FJIWAf2gPvBsF8JL8=',1,'EWA8QK8LV8',1,1,'en',1,'none',1,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(18,NULL,'john@example.com','john@example.com','john@example.com','john@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EWA8QK8MHO',1,1,'en',1,'none',1,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(19,NULL,'alex@example.com','alex@example.com','alex@example.com','alex@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ALEX',NULL,'JORDAN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8560370319','1951-01-01','o/XbnU2gkSeBfX3iXG5j6Snq3cGSPl/Akn5yqDFakus=',1,'EWA8QK8MZN',1,1,'en',1,'passed',1,0,0,'2015-09-21 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(20,NULL,'empty@example.com','empty@example.com','empty@example.com','empty@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'OLA','MAE','TAYLOR','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3097458439','1955-09-14','Hzx9ANUNJMkWJpbUxQwrSi446R4oQLIYFMZsZfbrlUo=',1,'EWA8QK8NHL',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(21,NULL,'emilio@example.com','emilio@example.com','emilio@example.com','emilio@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'BRIAN','P','KURTH','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7188491319','1957-02-19','67I+L2Pl9SvLEvEhw1Ss16sD19o6mj2HYWeMAKFCVi8=',1,'EWA8QK8NZP',1,1,'en',1,'passed',1,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(22,NULL,'robert@example.com','robert@example.com','robert@example.com','robert@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBERT','SCOTT','BIRMINGHAM','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1957-02-19','hsh/v/SvWEOB7XOEj1Tzuotn0UqChKq41U3n+Ib56Oo=',1,'EWA8QK8OHI',1,1,'en',0,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(23,NULL,'mamazza@example.com','mamazza@example.com','mamazza@example.com','mamazza@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'WILLIAM','N','JOHNSON','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','CjdmjM3h49dVq81ay0Lietv7z7qMTyCY7tnCX4tx48U=',1,'EWA8QK8OZB',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(24,NULL,'marion@example.com','marion@example.com','marion@example.com','marion@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MARION','R','BRIEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','nRDcgyOn6wu2mi+qL4NKLfjSWEQ2dajaAlGtAEON2dc=',1,'EWA8QK8PH0',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(25,NULL,'hugo@example.com','hugo@example.com','hugo@example.com','hugo@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'HUGO','WOSBELLY','RODRIGUEZ','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','EXWuftAv8hCgEE4JMQx7VmW57FzQRs0tu/6mioDHVDs=',1,'EWA8QK8PYS',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(26,NULL,'miguel@example.com','miguel@example.com','miguel@example.com','miguel@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MIGUEL','M','CENTENO','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','Y7aS5ZM2AaiwP0UJcni+Vg3Hw2uDia0iT1KShhzwHRQ=',1,'EWA8QK8QGI',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(27,NULL,'CONNIE@example.com','connie@example.com','CONNIE@example.com','connie@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'CONNIE','S','WEBSTER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1941-01-01','lQeEBCdr8yXWIHdflkrmC9PNA7TtL+ANyf8wCBNCqoo=',1,'EWA8QK8QY2',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(28,NULL,'lory@example.com','lory@example.com','lory@example.com','lory@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LORY','M','STEFFANS','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1962-09-19','380NKJ9S3Ad4y2DAVJxDDZjKPMd90SHj9TV+ADyjG8c=',1,'EWA8QK8RG0',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(29,NULL,'app3@example.com','app3@example.com','app3@example.com','app3@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBERT','SCOTT','BIRMINGHAM','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3028320298','1962-06-05','hsh/v/SvWEOB7XOEj1Tzuotn0UqChKq41U3n+Ib56Oo=',1,'EWA8QK8RXN',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(30,NULL,'app4@example.com','app4@example.com','app4@example.com','app4@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MILDRED',NULL,'RIOS-HERNANDEZ','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'4068921606','2015-09-21','nIDo1db9vq25V2vyEfn6HY2vhKyJS7SdFUrtNjIyE04=',1,'EWA8QK8SF2',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(31,NULL,'app5@example.com','app5@example.com','app5@example.com','app5@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ANTHONY','D','DELLISANTI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'4105367237','1956-08-09','Yk1atQG018s86si+DpItfafiuiY+TpGYVavoF6bLkK4=',1,'EWA8QK8SWV',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(32,NULL,'app6@example.com','app6@example.com','app6@example.com','app6@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LINDA','A','LEMOINE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','2015-09-21','eUt9MpW59ta4Ea9irGF+GpAXklHBBAiGRWKjK/rszl0=',1,'EWA8QK8TEK',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(33,NULL,'app8@example.com','app8@example.com','app8@example.com','app8@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'GARY','A','LINDSAY','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3048428383','1955-11-30','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EWA8QK8TW4',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(34,NULL,'app9@example.com','app9@example.com','app9@example.com','app9@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'THOMAS','DENNIS','LOPES','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','2015-09-21','wJVJE2Boh5UkGehexbiRqmMlklpTQgoo2w6o9vyC+Z4=',1,'EWA8QK8UF0',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(35,NULL,'app10@example.com','app10@example.com','app10@example.com','app10@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBYN','L','PIPER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7034910325','1968-09-21','hGDZNda+L9PJPWEu2gMRf7hn5Xy1D8VdeP9BpyJ2V7s=',1,'EWA8QK8UYI',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(36,NULL,'app11@example.com','app11@example.com','app11@example.com','app11@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LAURIEANN','KATHLEEN','RADLEIN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','1968-02-14','WO0EKHQP12xEv4rwUOXt0pFf7KXJQ3/awl4T7fjIoLI=',1,'EWA8QK8VGQ',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(37,NULL,'linda@example.com','linda@example.com','linda@example.com','linda@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','2015-09-21','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'TESTFULL',1,1,'en',1,'none',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(38,NULL,'tenant133@example.com','tenant133@example.com','tenant133@example.com','tenant133@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'TIMOTHY','A','APPLEGATE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7858655392','1937-11-10','urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'EWA8QK8WH6',1,1,'en',1,'passed',NULL,0,0,'2015-08-02 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(39,NULL,'app14@example.com','app14@example.com','app14@example.com','app14@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'PATRICIA','A','ROTHWELL','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8187859255','1937-01-01','urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'TESTCODE',1,1,'en',1,'passed',NULL,0,0,'2015-08-02 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(40,NULL,'app12@example.com','app12@example.com','app12@example.com','app12@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROGER','D','STANLEY','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'6165311574','1949-12-09','xPsjgwUzrkxk6ShvMYn9a533cjNOGHIVl2js0SVxPGA=',1,'EWA8QK8XGL',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(41,NULL,'noname@gmail.com','noname@gmail.com','noname@gmail.com','noname@gmail.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'700Credit','Petrovich','Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'5291181804','1980-10-22','18LW1dmYAbWnogX0Jj9fJlxbu0FJIWAf2gPvBsF8JL8=',1,'EWA8QK8XZ7',1,1,'en',1,'none',1,0,0,'2015-09-16 13:17:11','2015-09-21 13:17:11',NULL,'applicant',NULL),(42,NULL,'tenant11@example.com','tenant11@example.com','tenant11@example.com','tenant11@example.com',1,'67215feov4sg488g8wo08o0gskog4gk','1a1dc91c907325c69271ddf0c944bc72','2015-09-21 12:17:15',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'TIMOTHY','A','APPLEGATE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7858655392','1937-11-10','urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'EWA8QMX91A',1,1,'en',1,'none',NULL,0,0,'2015-08-02 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(43,NULL,'john@rentrack.com','john@rentrack.com','john@rentrack.com','john@rentrack.com',1,'4xf174llvag48o4kccc8sc844ck4ccw','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:15',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EWA8QMX9OY',1,1,'en',1,'passed',1,0,0,'2015-08-07 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(44,NULL,'alex@rentrack.com','alex@rentrack.com','alex@rentrack.com','alex@rentrack.com',1,'ofsq8pds12os8wo8oc8swwosc00s884','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:15',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ALEX',NULL,'JORDAN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8603790319','1951-01-01','o/XbnU2gkSeBfX3iXG5j6Snq3cGSPl/Akn5yqDFakus=',1,'EWA8QMXA91',1,1,'en',1,'passed',1,0,0,'2015-08-09 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(45,NULL,'ola@rentrack.com','ola@rentrack.com','ola@rentrack.com','ola@rentrack.com',1,'bi9xilqepdwk8g8c4gg0ogk8ok0o8wc','1a1dc91c907325c69271ddf0c944bc72','2015-09-16 13:17:15',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'OLA','MAE','TAYLOR','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3097458439','1955-09-14','Hzx9ANUNJMkWJpbUxQwrSi446R4oQLIYFMZsZfbrlUo=',1,'EWA8QMXAT7',1,1,'en',0,'passed',NULL,0,0,'2015-08-12 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(46,NULL,'emilio1@rentrack.com','emilio1@rentrack.com','emilio1@rentrack.com','emilio1@rentrack.com',1,'5cd7r2qkc9og08ck4cogcs8skg0csc4','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:15',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'BRIAN','P','KURTH','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7188491319','1957-02-19','67I+L2Pl9SvLEvEhw1Ss16sD19o6mj2HYWeMAKFCVi8=',1,'EWA8QMXBDD',1,1,'en',1,'passed',1,0,0,'2015-08-14 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(47,NULL,'ivan@rentrack.com','ivan@rentrack.com','ivan@rentrack.com','ivan@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Ivan','Petrovich','Gates','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3751181804','1980-10-22','18LW1dmYAbWnogX0Jj9fJlxbu0FJIWAf2gPvBsF8JL8=',1,'EWA8QMXBYB',1,1,'en',1,'none',1,0,0,'2015-09-16 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(48,NULL,'robert@rentrack.com','robert@rentrack.com','robert@rentrack.com','robert@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBERT','SCOTT','BIRMINGHAM','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1957-02-19','hsh/v/SvWEOB7XOEj1Tzuotn0UqChKq41U3n+Ib56Oo=',1,'EWA8QMXCJF',1,1,'en',0,'passed',NULL,0,0,'2015-09-16 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(49,NULL,'mamazza@rentrack.com','mamazza@rentrack.com','mamazza@rentrack.com','mamazza@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'WILLIAM','N','JOHNSON','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','CjdmjM3h49dVq81ay0Lietv7z7qMTyCY7tnCX4tx48U=',1,'EWA8QMXD3A',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(50,NULL,'marion@rentrack.com','marion@rentrack.com','marion@rentrack.com','marion@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MARION','R','BRIEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','nRDcgyOn6wu2mi+qL4NKLfjSWEQ2dajaAlGtAEON2dc=',1,'EWA8QMXDMM',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(51,NULL,'hugo@rentrack.com','hugo@rentrack.com','hugo@rentrack.com','hugo@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'HUGO','WOSBELLY','RODRIGUEZ','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','EXWuftAv8hCgEE4JMQx7VmW57FzQRs0tu/6mioDHVDs=',1,'EWA8QMYFN6',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(52,NULL,'miguel@rentrack.com','miguel@rentrack.com','miguel@rentrack.com','miguel@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MIGUEL','M','CENTENO','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7818945369','1970-01-01','Y7aS5ZM2AaiwP0UJcni+Vg3Hw2uDia0iT1KShhzwHRQ=',1,'EWA8QMYG6D',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(53,NULL,'connie@rentrack.com','connie@rentrack.com','connie@rentrack.com','connie@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'CONNIE','S','WEBSTER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1941-01-01','lQeEBCdr8yXWIHdflkrmC9PNA7TtL+ANyf8wCBNCqoo=',0,'77777TEST',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:15','2015-09-21 13:17:15',NULL,'tenant',NULL),(54,NULL,'lory@rentrack.com','lory@rentrack.com','lory@rentrack.com','lory@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LORY','M','STEFFANS','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','1962-09-19','380NKJ9S3Ad4y2DAVJxDDZjKPMd90SHj9TV+ADyjG8c=',1,'EWA8QMYH6Q',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(55,NULL,'mathew@rentrack.com','mathew@rentrack.com','mathew@rentrack.com','mathew@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'MATHEW','J','DOYLE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','1933-09-02','5AvE47PM0XMz9zRixW6qpQJtMi+ZccgLQblTbSzvke0=',1,'EWA8QMYHPP',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(56,NULL,'anthony@rentrack.com','anthony@rentrack.com','anthony@rentrack.com','anthony@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ANTHONY','D','DELLISANTI','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'4105367237','1956-08-09','Yk1atQG018s86si+DpItfafiuiY+TpGYVavoF6bLkK4=',1,'EWA8QMYI8Y',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(57,NULL,'linda@rentrack.com','linda@rentrack.com','linda@rentrack.com','linda@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LINDA','A','LEMOINE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','2015-09-21','eUt9MpW59ta4Ea9irGF+GpAXklHBBAiGRWKjK/rszl0=',1,'EWA8QMYIS2',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(58,NULL,'thomas@rentrack.com','thomas@rentrack.com','thomas@rentrack.com','thomas@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'THOMAS','DENNIS','LOPES','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','2015-09-21','wJVJE2Boh5UkGehexbiRqmMlklpTQgoo2w6o9vyC+Z4=',1,'EWA8QMYJAW',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(59,NULL,'robyn@rentrack.com','robyn@rentrack.com','robyn@rentrack.com','robyn@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROBYN','L','PIPER','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7034910325','1968-09-21','hGDZNda+L9PJPWEu2gMRf7hn5Xy1D8VdeP9BpyJ2V7s=',1,'EWA8QMYJTQ',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(60,NULL,'laurieann@rentrack.com','laurieann@rentrack.com','laurieann@rentrack.com','laurieann@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'LAURIEANN','KATHLEEN','RADLEIN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','1968-02-14','WO0EKHQP12xEv4rwUOXt0pFf7KXJQ3/awl4T7fjIoLI=',1,'EWA8QMYKCG',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(61,NULL,'invite@rentrack.com','invite@rentrack.com','invite@rentrack.com','invite@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,NULL,NULL,NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'','2015-09-21','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'TESTFULL_RJ',1,1,'en',1,'failed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(62,NULL,'roger@rentrack.com','roger@rentrack.com','roger@rentrack.com','roger@rentrack.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ROGER','D','STANLEY','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'6165311574','1949-12-09','xPsjgwUzrkxk6ShvMYn9a533cjNOGHIVl2js0SVxPGA=',1,'EWA8QMYLCH',1,1,'en',1,'passed',NULL,0,0,'2015-09-16 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(63,NULL,'transU@example.com','transu@example.com','transU@example.com','transu@example.com',1,'izgu8ou4opw00c4gko0kccs0kwk8w0k','1a1dc91c907325c69271ddf0c944bc72','2015-09-21 12:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Anne',NULL,'Test','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7858655392','1980-11-10','CnMUrJbBaznS4WkkywHzfSp2o1Ec8iveiWTIwrT/6Jc=',1,'EWA8QMYLV1',1,1,'en',1,'passed',NULL,0,0,'2015-08-02 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(64,NULL,'transU+datapull@example.com','transu+datapull@example.com','transU+datapull@example.com','transu+datapull@example.com',1,'o36hs1gv5msgso84ko4o4gos4g8ss8s','1a1dc91c907325c69271ddf0c944bc72','2015-09-21 12:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'DANIEL',NULL,'Nader','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8058675309','1972-11-10','pLsFb0sQGUJnNwNSKnLXnp330BgXDsZ4kLCmfeuF03Y=',1,'EWA8QMYMDT',1,1,'en',1,'passed',NULL,0,0,'2015-08-02 13:17:16','2015-09-21 13:17:16',NULL,'tenant',NULL),(65,5,'landlord1@example.com','landlord1@example.com','landlord1@example.com','landlord1@example.com',1,'ae9rthk95k0kwgcgcsso0o4osk0skwo','1a1dc91c907325c69271ddf0c944bc72','2015-09-21 12:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'TIMOTHY','A','APPLEGATE','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7858655392',NULL,'urIQQQ4HYrMIq0SHBrLnGcGvqINAC6IvkGlqFo2cmyc=',1,'EWA8QNB6RW',1,1,'en',1,'passed',NULL,0,1,'2015-08-02 13:17:16','2015-09-21 13:17:16',NULL,'landlord',NULL),(66,6,'landlord2@example.com','landlord2@example.com','landlord2@example.com','landlord2@example.com',1,'o96xw0ocxmows8sccc00c0g880c8ok0','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',0,'EWA8QNB7HE',1,1,'en',1,'none',1,0,1,'2015-08-07 13:17:16','2015-09-21 13:17:16',NULL,'landlord',NULL),(67,6,'landlord3@example.com','landlord3@example.com','landlord3@example.com','landlord3@example.com',1,'hzyvknws6k8wggo4s0kcw48k048c40w','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'ALEX',NULL,'JORDAN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'8603790319','1951-01-01','o/XbnU2gkSeBfX3iXG5j6Snq3cGSPl/Akn5yqDFakus=',1,'EWA8QNB85O',1,1,'en',1,'passed',1,0,0,'2015-08-09 13:17:16','2015-09-21 13:17:16',NULL,'landlord',NULL),(68,7,'landlord4@example.com','landlord4@example.com','landlord4@example.com','landlord4@example.com',1,'knlrx4pjhuogskkc4okg048ogswgo8s','1a1dc91c907325c69271ddf0c944bc72','2015-09-16 13:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'OLA','MAE','TAYLOR','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'3097458439','1955-09-14','Hzx9ANUNJMkWJpbUxQwrSi446R4oQLIYFMZsZfbrlUo=',1,'EWA8QNB8VM',1,1,'en',0,'passed',NULL,0,1,'2015-08-12 13:17:16','2015-09-21 13:17:16',NULL,'landlord',NULL),(69,8,'landlord5@example.com','landlord5@example.com','landlord5@example.com','landlord5@example.com',1,'rmwc4er06f40wogk4skog8k84s8c8k0','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'BRIAN','P','KURTH','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'7188491319','1957-02-19','67I+L2Pl9SvLEvEhw1Ss16sD19o6mj2HYWeMAKFCVi8=',1,'EWA8QNB9ID',1,1,'en',1,'passed',1,0,1,'2015-08-14 13:17:16','2015-09-21 13:17:16',NULL,'landlord',NULL),(70,6,'agent1@example.com','agent1@example.com','agent1@example.com','agent1@example.com',1,'ep3ahd5epy0c4cckkw80kc8wcgw4004','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Agent',NULL,'Test','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'','2015-09-21','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,'EWA8QNBA4C',0,0,'en',0,'passed',NULL,0,0,'2015-08-02 13:17:16','2015-09-21 13:17:16',NULL,'landlord',NULL),(71,6,'landlord6@example.com','landlord6@example.com','landlord6@example.com','landlord6@example.com',1,'fkkdm0o5qagcs480gsc8ssw84w4os88','1a1dc91c907325c69271ddf0c944bc72','2015-09-20 13:17:16',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'John','WAKEFIELD','BREEN','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,2,'9137644215','1957-02-19','WaTk+IDdI29SfMA0Iar98eOUcKJVBRTacimYqTqaflg=',1,'EWA8QNBAQV',1,1,'en',1,'none',1,0,1,'2015-08-07 13:17:16','2015-09-21 13:17:16',NULL,'landlord',NULL),(72,NULL,'anna_lee@example.com','anna_lee@example.com','anna_lee@example.com','anna_lee@example.com',1,'kx6z2ul5ji80ss44w48k0ookcgock8s','1a1dc91c907325c69271ddf0c944bc72',NULL,0,0,NULL,NULL,NULL,'a:0:{}',0,NULL,'Anna',NULL,'Lee','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',NULL,NULL,NULL,NULL,NULL,'',NULL,'l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',0,'EWA8QQ3UZH',1,1,'en',1,'none',NULL,0,0,'2015-09-20 13:17:21','2015-09-21 13:17:21',NULL,'partner',NULL);
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
INSERT INTO `cj_vehicle` VALUES (1,17,'Honda','Civic',1,0,NULL,'2015-09-16 13:17:14','2015-09-16 13:17:14'),(2,18,'BMW','X5',0,0,NULL,'2015-09-16 13:17:14','2015-09-16 13:17:14'),(3,19,'BMW','X5',0,0,NULL,'2015-09-16 13:17:14','2015-09-16 13:17:14'),(4,20,'BMW','X5',0,0,NULL,'2015-09-16 13:17:14','2015-09-16 13:17:14'),(5,21,'BMW','X5',0,0,NULL,'2015-09-16 13:17:14','2015-09-16 13:17:14'),(6,23,'Honda','CR-V',0,0,NULL,'2015-09-16 13:17:14','2015-09-16 13:17:14');
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
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` VALUES (1,'qvxzb7ge734ko4ogwcskwksogoc0wskws40gg8oocokwg404s','a:0:{}','39uyn651qlk4ssws40sgs44cwsskgccoc0o04ccgsccgooowwo','a:2:{i:0;s:13:\"refresh_token\";i:1;s:8:\"password\";}',NULL),(2,'3fjeg33hn944o0o88woc8w04c0c0gsso000ggk0848skkkkw84','a:1:{i:0;s:16:\"http://localhost\";}','20h1hdqkgeaswo8kw8o800ck00wk4o8okck48wgskg0gwcco0w','a:2:{i:0;s:5:\"token\";i:1;s:18:\"authorization_code\";}','TestApp'),(3,'3iui2vbjxtwksskk88os8040408ccwwkss80o4c4c88skoc804','a:1:{i:0;s:16:\"http://localhost\";}','2qeobwbv72ecosgk4g0w8sokwkco0cwwo0sg4sg0cwskssk880','a:2:{i:0;s:5:\"token\";i:1;s:18:\"authorization_code\";}','CreditComClient');
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
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email`
--

LOCK TABLES `email` WRITE;
/*!40000 ALTER TABLE `email` DISABLE KEYS */;
INSERT INTO `email` VALUES (1,'invite.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(2,'welcome.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(3,'score.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(4,'target.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(5,'finished.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(6,'password.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(7,'example.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(8,'resetting.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(9,'check.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(10,'receipt.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(11,'rjCheck.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(12,'rjLandLordInvite.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(13,'rjTenantInvite.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(14,'rjTenantLatePayment.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(15,'rjLandlordComeFromInvite.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(16,'rjPendingContract.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(17,'exist_invite.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(18,'rjTodayPayments.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(19,'rjTodayNotPaid.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(20,'rjDailyReport.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(21,'rjTenantLateContract.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(22,'rjPaymentDue.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(23,'rjListLateContracts.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(24,'rjOrderReceipt.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(25,'rjOrderError.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(26,'rjTenantInviteReminder.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(27,'rjTenantInviteReminderPayment.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(28,'rjContractApproved.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(29,'rjContractRemovedFromDbByLandlord.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(30,'rjContractRemovedFromDbByTenant.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(32,'rj_resetting.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(33,'rjEndContract.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(34,'rjOrderCancel.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(35,'rjOrderCancelToLandlord.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(36,'rjPendingOrder.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(37,'rjContractAmountChanged.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(38,'rjBatchDepositReportLandlord.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(39,'rjBatchDepositReportHolding.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(40,'rjReceipt.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(41,'rjPushBatchReceiptsReport.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(42,'rjYardiPaymentAcceptedTurnOn.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(43,'rjYardiPaymentAcceptedTurnOff.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(44,'rjLateReportingLandlord.html','2015-09-21 13:17:14','2015-09-21 13:17:14'),(45,'rjLateReportingTenant.html','2015-09-21 13:17:14','2015-09-21 13:17:14');
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
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_translation`
--

LOCK TABLES `email_translation` WRITE;
/*!40000 ALTER TABLE `email_translation` DISABLE KEYS */;
INSERT INTO `email_translation` VALUES (1,1,'en','subject','Welcome to Credit Jeeves'),(2,1,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to Credit Jeeves{% endblock %}\n{% block email %}\n      <p>\n          {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n          The Credit Jeeves program shows you your current credit score, a summary of your credit profile, and a customized action plan to help\n          you reach your target score. We then monitor your progress over the next few months to let you know when you are likely qualified for a loan.\n      </p>\n      <p>\n          Enrollment is free, simple, and takes less than a minute. Credit Jeeves will not negatively impact your credit and does not post a\n          \'hard inquiry.\'\n      </p>\n      <p>\n          Set up your Credit Jeeves Account now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a> and take the first step towards better financing.\n      </p>\n      <p>\n          You will be able to:\n          * See and monitor your current credit score.\n          * Follow easy-to-understand actions to optimize your score for your goals.\n          * See a summary of your credit file and learn more about how this information affects your score.\n          * Receive alerts when you reach your target score.\n      </p>\n      <br />\n      <p>\n        Tip: Do not shop around for a loan right now. This will create multiple \'hard inquiries\' on your credit file which can negatively\n        impact your score. Credit Jeeves makes a \'soft inquiry\' and will allow you to view your score and action plan without hurting your\n        chances to requalify for a loan in the future.\n      </p>\n      <p>\n          Again, {{ groupName }} is providing you this service for free.\n      </p>\n      <p>\n      Sign Up Now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a>\n      </p>\n{% endblock %}'),(3,2,'en','subject','Welcome to Credit Jeeves'),(4,2,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to CreditJeeves{% endblock %}{% block email %}<p>You have taken the first step towards your new car.</p><p>To see your customized action plan, sign in at <a href=\"http://my.creditjeeves.com/\">cj</a> anytime.</p><strong>Get started today:</strong><ul>  <li>Understand<a href=\"http://www.creditjeeves.com/educate/understand-your-credit-score\">how your credit score is determined</a></li><li>Review your <a href=\"http://cj/_dev.php/?\">action plan</a> and decide what step you will take first.</li><li>Click on the \"learn more\" link next to that step to find out what to do.</li></ul><i>Trouble answering the verification questions?</i><p>It is a good idea to get a <a href=\"https://www.annualcreditreport.com/\"> free copy of your credit report </a> to see if contains something you do not recognize. You can also contact <a href=\"mailto:help@creditjeeves.com\">help@creditjeeves.com</a> if your account becomes locked. </p><i>We want to hear from you!</i><p>Please <a href=\"http://creditjeeves.uservoice.com/\">send us your feedback</a> on how we can make the product better for you.</p>{% endblock %}'),(5,3,'en','subject','Your Credit Score has Changed - Log Into Credit Jeeves'),(6,3,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}'),(7,4,'en','subject','Your New Car Awaits - Log into Credit Jeeves'),(8,4,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Congratulations!{% endblock %}\n{% block email %}\n  <div mc:edit=\"std_content00\">\n      You have reached your dealer\'s target score of <strong>{{ targetScore }}</strong>\n  </div>\n  <div mc:edit=\"latest_score_button\">\n      <br />\n      <hr />\n      Log into Credit Jeeves to find out what to do next. Your new car awaits!\n      <br />\n      <a class=\"button\" href=\"{{ loginLink }}\" id=\"viewLatestScoreButton\">View Latest Score</a>\n      <br />\n      <hr />\n  </div>\n{% endblock %}\n'),(9,5,'en','subject','One of your leads has reached the Target Score'),(10,5,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}'),(11,6,'en','subject','One of your leads has reached the Target Score'),(12,6,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}'),(13,7,'en','subject','Example email with all avaliable fields'),(14,7,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Heading 1{% endblock %}{% block h2 %}Heading 2{% endblock %}{% block h3 %}Heading 3{% endblock %}{% block h4 %}Heading 4{% endblock %}{% block email %}{% set button = {\"text\": \"Hmm, we could add more than one button in the email body!\",\"value\": \"Test\",\"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}<p>Lorem ipsum...</p>{% set button = {\"text\": \"Some text above button\", \"value\": \"Click It\", \"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}{% endblock %}'),(15,8,'en','subject','Reset Password'),(16,8,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  CreditJeeves will never e-mail you and ask you to disclose or verify your CreditJeeves.com password, credit card, or banking account number.\n\n  Thank you for using CreditJeeves!\n{% endblock %}\n'),(17,9,'en','subject','Check Email'),(18,9,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Credit Jeeves account is almost ready!{% endblock %}\n{% block email %}Hello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n'),(19,10,'en','subject','Receipt from Credit Jeeves'),(20,10,'en','body','<div mc:edit=\"std_content00\">\n<h1 class=\"h1\">Receipt from Credit Jeeves</h1>\nThank you for purchasing your credit report through Credit Jeeves.\nYour payment was processed successfully and will appear on your next statement under CREDITJEEVE.\nHere is your receipt:<br />\n&nbsp;<br />\n<hr />\nPayment Date & Time:&nbsp;{{ date }}<br />\nPayment Amount: {{ amout }}<br />\nReference Number: {{ number }}<br />\n<br />\n<hr />\nRemember, we\'re here to help,<br /><strong>The Credit Jeeves Team</strong>\n</div>\n'),(21,11,'en','subject','Get Started with RentTrack'),(22,11,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your RentTrack account is almost ready!{% endblock %}\n{% block email %}\nHello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n'),(23,12,'en','subject','Your Tenant is Ready to Pay Rent through RentTrack'),(24,12,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Get Paid Fast Using RentTrack{% endblock %}\n{% block email %}\n  {% if nameLandlord %}\n      Hi {{ nameLandlord }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br /> \n  {% endif %}\n  Your tenant, {{ fullNameTenant }}, would like to use RentTrack to pay rent on your property at\n  {{ address }} {{ unitName }}. RentTrack allows {{ nameTenant }} to build credit history by\n  reporting on-time payments to credit bureaus. <br /> <br />\n\n  As a landlord, you benefit because RentTrack facilitates easy payments through secure electronic\n  check transfers and credit cards - payments are deposited faster and directly to your account.\n  Reminders are sent automatically to your tenants before rent is due and late notices are sent\n  to you immediately. If you have multiple properties, you can see the status of your payments\n  all in one place. To top it off, your tenant has an additional incentive to pay\n  on time each month.<br /> <br />\n\n  Ready to get paid? <br /> <br />\n  <a id=\"payRentLinkLandlord\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'landlord_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n            style=\"border: none;\n            padding: 2px 7px;\n            text-align: left;\n            color: white;\n            font-size: 14px;\n            text-shadow: 1px 1px 3px #636363;\n            filter: dropshadow(color=#636363, offx=1, offy=1);\n            cursor: pointer;\n            background-color: #669900;\n            -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n            zoom: 1;\n            text-decoration: none;\n            -moz-border-radius: 4px;\n            -webkit-border-radius: 4px;\n            border-radius: 4px;\"\n>Sign up</a> Still have some questions? <a href=\"http://www.renttrack.com/property-management\">Read More</a> or call 866.841.9090\n{% endblock %}\n'),(25,13,'en','subject','Your Landlord is Requesting Rent Payment through RentTrack'),(26,13,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ rentAddress }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit history by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode, \'isImported\': isImported }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n'),(27,14,'en','subject','Your Rent Payment is Late'),(28,14,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Late. Pay Now!{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Dear {{ nameTenant }}, <br />  <br />\n  {% else %}\n      Heads Up!<br /> <br />\n  {% endif %}\n  It looks like {{ fullNameLandlord }} expected your rent payment for {{ address }} {{ unitName }} already.\n\n  <a href=\"http://my.renttrack.com/\">Log in to RentTrack today</a> and and make an immediate payment. We\'ll\n  let {{ fullNameLandlord }} that rent is on its way once the payment goes through.\n\n  Better yet, you can set up automatic payments so you never miss one again. <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">Learn More</a>\n\n  Watching out for you,\n  The RentTrack Team\n{% endblock %}\n'),(29,15,'en','subject','Your Landlord Joined RentTrack'),(30,15,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n  Congratulations! {{ fullNameLandlord }} has teamed up with RentTrack.\n  <br /><br />\n  We\'re now working with them to ready their account to accept payments. You\'ll receive another email when you\'re\n  approved to pay rent online.\n  <br /><br />\n  Thank you for your patience!\n{% endblock %}\n'),(31,16,'en','subject','Your Tenant Needs Approval'),(32,16,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  {{ nameTenant }} is ready to pay rent for {{ address }}\n  <br /></br />\n  Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a>, click on the Tenants tab, and click on the\n  review \"eye\" next to the pending tenant. You will then be able to add rent details and approve the tenant. Once\n  this is complete, your tenant will be able to set up their rent payment.\n{% endblock %}\n'),(33,17,'en','subject','Your have new dealer!'),(34,17,'en','body','{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your have new dealer!{% endblock %}\n{% block email %}\n    <p>\n        {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n    </p>\n    <p>\n        Again, {{ groupName }} is providing you this service for free.\n    </p>\n{% endblock %}\n'),(35,18,'en','subject','Rent Payments Today'),(36,18,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Collected{% endblock %}\n{% block email %}\n  Hi {{ nameLandlord }},\n  <br /><br />\n  We collected ${{ amount }} in rent today. To see your recent payments,\n  <a href=\"https://my.renttrack.com/\">log into RentTrack</a> and click on Dashboard.\n  <br /><br />\n  Payments typically settle in 1-3 days to your account. If you suspect a payment is not transferring, or have\n  any other questions, please contact us at help@creditjeeves.com or call 866-841-9090.\n{% endblock %}\n'),(37,19,'en','subject','Not Paid Today.'),(38,19,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  Today not paid is {{ amount }}\n  <br />\n  <br />\n  Enjoy, <br />\n  The RentTrack Team\n{% endblock %}\n'),(39,20,'en','subject','RentTrack Daily Report'),(40,20,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Status</th>\n      <th style=\"padding:5px;\">Amount</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for key, value in report %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ key }}</td>\n      <td style=\"padding:5px;\">\n      {% if value > 0 %}\n        ${{ value }}\n      {% else %}\n      ---\n      {% endif %}\n      </td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n{% endblock %}\n'),(41,21,'en','subject','Rent Payment is Late'),(42,21,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }},{% endblock %}\n{% block email %}\n  It looks like your rent payment for {{ address }} is <b>late by {{ diff }} day(s)</b>.\n  <br /><br />\n  <a href=\"https://my.renttrack.com/\">Log into RentTrack</a> today to make a new payment. We\'d recommend setting up\n  <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">automatic payments</a>\n  so you won\'t see an email like this next month.\n  <br /><br />\n  If you have alread paid by a different method like cash or (*gasp*) paper check, then your landlord needs\n  to log into RentTrack and update your records. They have also received an email reminder regarding this payment.\n  <br /><br />\n  If you need assistance, please email help@renttrack.com or call (866) 841-9090.\n{% endblock %}\n'),(43,22,'en','subject','Your Rent Is Due'),(44,22,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Is Due{% endblock %}\n{% block email %}\nYour rent payment to {{ nameHolding }} for {{ address }} is coming up.\n<br /><br />\n{% if paymentType == \'recurring\' %}\n  It looks like you have recurring payments set up, so we\'ll send you another email when we make your payment.\n  Please note that if you are paying by credit card, you will also pay a technology fee with your rent.\n  If you need to change your payment details or cancel your payment,\n  please <a href=\"https://my.renttrack.com/\">log in to RentTrack today</a> and make any adjustments.\n{% elseif paymentType == \'one_time\' %}\n  It looks like you already have a payment set up, so we\'ll send you another email when we make your payment.\n{% else %}\n  You do not have recurring payments set up. <a href=\"https://my.renttrack.com/\">Log in to RentTrack today</a>\n  to set up a one-time or recurring payment.\n{% endif %}\n{% endblock %}\n'),(45,23,'en','subject','Review Late Rent Payments'),(46,23,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ nameLandlord }},{% endblock %}\n{% block email %}\nThe following tenants have not submitted on-time payments:\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Tenant</th>\n      <th style=\"padding:5px;\">Email</th>\n      <th style=\"padding:5px;\">Address</th>\n      <th style=\"padding:5px;\">Days Late</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for tenant in tenants %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ tenant.name }}</td>\n      <td style=\"padding:5px;\">{{ tenant.email }}</td>\n      <td style=\"padding:5px;\">{{ tenant.address }}</td>\n      <td style=\"padding:5px;\">{{ tenant.late }}</td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n  <br />\n  Please <a href=\"https://my.renttrack.com\">log into RentTrack</a>\n  and click on \"Resolve\" next to late tenants at the top of the Payments Dashboard to either record payments\n  via alternate means or to send them an email reminder.\n{% endblock %}\n'),(47,24,'en','subject','Rent Payment Receipt'),(48,24,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Paid{% endblock %}\n{% block email %}\n{% if nameTenant %}\n  Hi {{ nameTenant }}! <br /><br />\n{% else %}\n  Hello!  <br /><br />\n{% endif %}\n\nYour rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n'),(49,25,'en','subject','Order Error'),(50,25,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n{{ \'order.error.title\'| trans }}.\n<br /><br />\n{{ \'order.error.message\' | trans }}: {{ error }}\n<br /><br />\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.paid.to\' | trans }}:</td><td style=\"padding:5px;\">{{ groupName }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.id\' | trans }}:</td><td style=\"padding:5px;\">{{ orderId }}</td>\n    </tr>\n    {% if transactionId > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionId }}</td>\n    </tr>\n    {% endif %}\n  </tbody>\n</table>\n{{ \'order.contact.us\' | trans }}\n{% endblock %}\n'),(51,26,'en','subject','Reminder. Your Landlord is Requesting Rent Payment through RentTrack'),(52,26,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n'),(53,27,'en','subject','Reminder. Your Landlord ask to install your payment'),(54,27,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n    href=\"http://{{ serverName }}/\"\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n'),(55,28,'en','subject','You\'re Approved to Pay Rent Online'),(56,28,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}You\'re Approved!{% endblock %}\n{% block email %}\nHello {{ nameTenant }},\n\nYour landlord has approved you and you can now set up your rent payment. Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a> and click on the \"Pay\" button corresponding to your rental.\n{% endblock %}\n'),(57,29,'en','subject','You Contract was Removed by Your Landlord'),(58,29,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameTenant }},{% endblock %}\n{% block email %}\n  Your landlord, {{ fullNameLandlord }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\n<br /><br />\nIf this is an error, please contact your landlord.\n{% endblock %}'),(59,30,'en','subject','Your Contract was Removed by Your Tenant'),(60,30,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your tenant, {{ fullNameTenant }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\nIf this is an error, please contact your tenant.\n{% endblock %}\n'),(61,31,'en','subject','Your RentTrack Merchant Account is Ready!'),(62,31,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your merchant account for \"{{ groupName }}\" is approved and ready!\n  <br /><br />\n\n  You can now accept rent payments online, and funds will be deposited into the account\n  you specified in your application. Begin by\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/285491-how-do-i-add-or-invite-a-tenant-\">inviting your tenants</a>, or\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/275851-how-do-i-approve-a-tenant-so-they-can-pay-rent-\">approving any pending tenants</a>\n  that invited you.\n{% endblock %}\n'),(63,32,'en','subject','Reset Password'),(64,32,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  Didn\'t request this change?\n  If you didn\'t request a new password, please contact us at <a mailto=\"help@renttrack.com\">help@renttrack.com</a>.\n\n  RentTrack will never e-mail you and ask you to disclose or verify your RentTrack.com password, credit card, or banking account number.\n\n  Thank you for using RentTrack!\n{% endblock %}'),(65,33,'en','subject','End Contract'),(66,33,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ tenantFullName }}!{% endblock %}\n{% block email %}\n   Your landlord {{landlordFullName}}, has ended contract by address: {{ address }} #{{ unitName }}.\n   {% if uncollectedBalance > 0%}\n      And you have uncollected balance on this contract {{ uncollectedBalance }}$.\n   {% else %}\n\n   {% endif %}\n{% endblock %}\n'),(67,34,'en','subject','Your Rent Payment was Reversed'),(68,34,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Dear {{ tenantFullName }},{% endblock %}\n{% block email %}\n  {% if orderStatus == \'refunded\' %}\n  Per your request, your rent of {{ rentAmount }} sent on {{ orderDate }} was refunded and should appear in your account within a few days.\n  {% elseif orderStatus == \'cancelled\' %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was cancelled.\n  {% else %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was returned. Your rent is currently not paid.\n  You will receive a follow up from RentTrack customer support with the reason for return and ways to fix it.\n  {% endif %}\n  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n'),(69,35,'en','subject','Your Rent Payment was Reversed'),(70,35,'en','body','{% extends \'RjComponentBundle:Mailer:base.html.twig\' %}\n{% block h1 %}Dear {{ landlordFirstName }},{% endblock %}\n{% block email %}\n    {% if orderStatus == \'refunded\' %}\n\n    At the request of {{ tenantName }}, their rent of {{ rentAmount }} sent on {{ orderDate }} was refunded.\n    Any monies already deposited will be deducted from your account within a couple of days.\n    Please contact your tenant if you have any questions regarding this refund.\n    {% elseif orderStatus == \'cancelled\' %}\n\n    At the request of {{ tenantName}}, their rent payment of {{ rentAmount }} sent on {{ orderDate }}\n    was cancelled. You will not see a deposit in your account since it was cancelled before\n    payment settlement. Please contact your tenant if you have any questions regarding this cancellation.\n    {% else %}\n\n    The rent payment by {{ tenantName }} for {{ rentAmount }} sent on {{ orderDate }} was returned.\n    Any monies already deposited  will be deducted from your account per the RentTrack terms of service.\n    Your tenant\'s rent is currently unpaid.\n    Your tenant may try to pay again through RentTrack, or you may arrange an alternate, immediate payment method.\n    {% endif %}\n\n    If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n'),(71,36,'en','subject','Your Rent is Processing'),(72,36,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Processing{% endblock %}\n{% block email %}\n  Hi {{ tenantName }}! <br /><br />\n\n  Your rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ orderTime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\'border: 1px solid #C8C8C8;\'>\n        <td style=\'padding:5px;\'>{{ \'email.rent_amount\' | trans }}:</td>\n        <td style=\'padding:5px;\'>{{ rentAmount }}</td>\n    </tr>\n    <tr style=\'border: 1px solid #C8C8C8;\'>\n        <td style=\'padding:5px;\'>{{ \'email.other_amount\' | trans }}:</td>\n        <td style=\'padding:5px;\'>{{ otherAmount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n\n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n'),(73,37,'en','subject','Your Rent amount was adjusted on your contract'),(74,37,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ tenantName }}! <br />\n  <br />\n  Your property manager has adjusted the rent amount on your contract to {{ rentAmount }}.\n  Since the recurring payment you had set up for {{ paymentAmount }} is no longer correct,\n  we have cancelled your recurring payment.<br />\n  </br>\n  Please <a href=\"https://my.renttrack.com/\">log in to RentTrack</a> and set up a new recurring payment.\n  Be sure to specify the correct month that your next recurring payment should count for.<br />\n  </br>\n  If you have any questions regarding this change, please contact your property manager.\n  If you have questions about setting up a new recurring payment,\n  please contact us at help@renttrack.com or call (866) 841-9090.</br>\n  </br>\n  </br>\n  </br>\n  {{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n'),(75,38,'en','subject','Daily Batch Deposit Report'),(76,38,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Batch Deposit Report{% endblock %}\n{% block email %}\nDear {{ landlordFirstName }}, <br />\nYour batch deposit report for <b>{{ date | date(\"m/d/Y\") }}</b> for group <b>{{ groupName }}</b>\n{% if accountNumber %}(Account #{{ accountNumber }}){% endif %} is below:<br />\n<br />\n{% if batches %}\n  {% for batch in batches %}\n  Batch ID: <b class=\"batch-id\">{{ batch.batchId }}</b><br />\n  {% if groupPaymentProcessor == \'heartland\' %}\n      Payment Type: <b>{{ (\'order.type.\' ~ batch.paymentType) | trans }}</b><br />\n  {% endif %}\n  <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n    <thead>\n      <tr>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_initiated\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n      </tr>\n    </thead>\n    <tfoot>\n      <tr>\n         <td colspan=\"5\" style=\"padding:3px;border: 1px solid #4E4E4E;\" align=\"right\"><b>{{ \'order.total\' | trans }}:</b></td>\n         <td style=\"padding:3px;border: 1px solid #4E4E4E;\"><b>${{ batch.paymentTotal }}</b></td>\n      </tr>\n     </tfoot>\n    <tbody>\n      {% for transaction in batch.transactions %}\n      <tr>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'transaction.status.text.\' ~ transaction.transactionStatus) | trans }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n        {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n        </td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.dateInitiated | date(\"m/d/Y\") }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">${{ transaction.amount }}</td>\n      </tr>\n      {% endfor %}\n    </tbody>\n  </table>\n  <br />\n  {% endfor %}\n{% endif %}\n{% if returns %}\n  Reversals (Each will be Debited Separately)\n  <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n    <thead>\n      <tr>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.status_message\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_reversal\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n      </tr>\n    </thead>\n    <tbody>\n      {% for transaction in returns %}\n      <tr>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'order.status.text.\' ~ transaction.orderStatus) | trans }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.messages }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n        {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n        </td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.reversalDate | date(\"m/d/Y\") }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">-${{ transaction.amount | abs }}</td>\n      </tr>\n      {% endfor %}\n    </tbody>\n  </table>\n{% endif %}\n{% if not (returns and batches)  %}\nThere are no deposits to report.\n{% endif %}\n{% endblock %}\n'),(77,39,'en','subject','Daily Batch Deposit Report'),(78,39,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Batch Deposit Report{% endblock %}\n{% block email %}\nDear {{ landlordFirstName }}, <br />\nYour batch deposit report for <b>{{ date | date(\"m/d/Y\") }}</b> is below:\n{% for group in groups %}\n<br />\n<br />For group <b class=\"group-name\">{{ group.groupName }}</b>{% if group.accountNumber %} (Account #{{ group.accountNumber }}){% endif %}:<br />\n<br />\n  {% if group.batches %}\n    {% for batch in group.batches %}\n    Batch ID: <b class=\"batch-id\">{{ batch.batchId }}</b><br />\n    {% if group.groupPaymentProcessor == \'heartland\' %}\n        Payment Type: <b>{{ (\'order.type.\' ~ batch.paymentType) | trans }}</b><br />\n    {% endif %}\n    <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n      <thead>\n        <tr>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_initiated\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n        </tr>\n      </thead>\n      <tfoot>\n        <tr>\n           <td colspan=\"5\" style=\"padding:3px;border: 1px solid #4E4E4E;\" align=\"right\"><b>{{ \'order.total\' | trans }}:</b></td>\n           <td style=\"padding:3px;border: 1px solid #4E4E4E;\"><b>${{ batch.paymentTotal }}</b></td>\n        </tr>\n       </tfoot>\n      <tbody>\n        {% for transaction in batch.transactions %}\n        <tr>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'transaction.status.text.\' ~ transaction.transactionStatus) | trans }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n            {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n          </td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.dateInitiated | date(\"m/d/Y\") }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">${{ transaction.amount }}</td>\n        </tr>\n        {% endfor %}\n      </tbody>\n    </table>\n    <br />\n    {% endfor %}\n  {% endif %}\n  {% if group.returns %}\n    Reversals (Each will be Debited Separately)\n    <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n      <thead>\n        <tr>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.status_message\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_reversal\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n        </tr>\n      </thead>\n      <tbody>\n        {% for transaction in group.returns %}\n        <tr>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'order.status.text.\' ~ transaction.orderStatus) | trans }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.messages }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n          {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n          </td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.reversalDate | date(\"m/d/Y\") }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">-${{ transaction.amount | abs }}</td>\n        </tr>\n        {% endfor %}\n      </tbody>\n    </table>\n  {% endif %}\n  {% if not (group.returns and group.batches) %}\n  There are no deposits to report.\n  {% endif %}\n{% endfor %}\n{% endblock %}\n'),(79,40,'en','subject','Receipt from Rent Track'),(80,40,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ tenantName }}! <br />\n  <br />\n  Thank you for purchasing your credit report through Credit Jeeves.\n  Your payment was processed successfully and will appear on your next statement.\n  Here is your receipt:<br />\n  &nbsp;<br />\n  <hr />\n  Payment Date & Time:&nbsp;{{ date }}<br />\n  Payment Amount: {{ amout }}<br />\n  Reference Number: {{ number }}<br />\n  <br />\n  </br>\n  </br>\n  {{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n'),(81,41,'en','subject','Push Batch Receipts Report'),(82,41,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n The following batch deposits for {{ data[\'deposit_date\']|date(\'Y-m-d\') }} were uploaded to your accounting system.\n Please review and post the batches.\n <br />\n <br />\n <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n   <thead>\n     <tr>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>\n          {{ \'common.batch_id\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>\n          {{ \'yardi.accounting_batch_id\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">\n          {{ \'common.type_ach_cc\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">\n          {{ \'yardi.email.number_of_payments\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">\n          {{ \'common.status\'| trans }}\n       </th>\n     </tr>\n   </thead>\n   <tbody>\n        {% for value in data[\'data\'] %}\n          <tr>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'payment_batch_id\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'batchId\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'type\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'total\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'status\'] }}\n            </td>\n          </tr>\n        {% endfor %}\n      </tbody>\n    </table>\n{% endblock %}\n'),(83,42,'en','subject','Online Payments Enabled'),(84,42,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ TenantName }},\n  Your property manager has re-enabled online payments. You can now\n  <a href=\"{{ href }}\">log into RentTrack</a> and set up a new payment.\n{% endblock %}\n'),(85,43,'en','subject','Online Payments Disabled'),(86,43,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ TenantName }},\n  Your property manager has disabled online payments. Any unprocessed payments you had scheduled\n  through RentTrack have been cancelled. Contact your property manager immediately for more information.\n{% endblock %}\n'),(87,44,'en','subject','Action Required for Rent Reporting'),(88,44,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ landlordName }},\n  As part of our regular audit of rent payments we plan to report to the bureaus, we noticed that:\n  {% for contract in contracts %}\n\n  {{ contract.tenant.fullname }}{% if contract.tenant.getResidentForHolding(contract.holding) %}, {{ contract.tenant.getResidentForHolding(contract.holding).getResidentId() }}{% endif %}\n  does not have a payment on record for {{ month }}, and will be reported as delinquent.\n\n  {% endfor %}\n\n  If this person has paid outside of RentTrack, please log in and resolve the late alert you will see on your dashboard by entering an external/cash payment:\n  <a href=\"http://help.renttrack.com/knowledgebase/articles/289089-how-do-i-resolve-a-late-payment\">http://help.renttrack.com/knowledgebase/articles/289089-how-do-i-resolve-a-late-payment</a>.\n\n  Alternatively, you can end her contract with an outstanding balance if she is truly delinquent:\n  <a href=\"http://help.renttrack.com/knowledgebase/articles/323277-how-do-i-update-or-end-a-tenant-contract\">http://help.renttrack.com/knowledgebase/articles/323277-how-do-i-update-or-end-a-tenant-contract</a>\n  Or let us know by replying to this email.\n\n  Thank you so much for helping us ensure that the data we report is accurate.\n{% endblock %}\n'),(89,45,'en','subject','Action Required for Rent Reporting'),(90,45,'en','body','{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ tenantName }},\n\n  As part of our regular audit of rent payments we plan to report to the bureaus, we noticed that you are missing a payment for {{ month }}, and may be reported as delinquent.\n\n  Please make a rent payment as soon as possible.\n  If you have paid outside of RentTrack, or if your lease has ended, please contact your property manager and have them update your record in RentTrack.\n  We need them to verify that you have made a payment before we can report it to the bureaus.\n  To learn more, please visit: <help URL to be filled in later>\n  Thank you so much for helping us ensure that the data we report is accurate. If you have any questions, please contact <a href=\"mailto:help@renttrack.com\">help@renttrack.com</a>.\n\n{% endblock %}\n');
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
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ext_log_entries`
--

LOCK TABLES `ext_log_entries` WRITE;
/*!40000 ALTER TABLE `ext_log_entries` DISABLE KEYS */;
INSERT INTO `ext_log_entries` VALUES (1,'create','2015-09-21 13:17:14','1','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:11:\"invite.html\";}',NULL),(2,'create','2015-09-21 13:17:14','2','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"welcome.html\";}',NULL),(3,'create','2015-09-21 13:17:14','3','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:10:\"score.html\";}',NULL),(4,'create','2015-09-21 13:17:14','4','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:11:\"target.html\";}',NULL),(5,'create','2015-09-21 13:17:14','5','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:13:\"finished.html\";}',NULL),(6,'create','2015-09-21 13:17:14','6','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:13:\"password.html\";}',NULL),(7,'create','2015-09-21 13:17:14','7','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"example.html\";}',NULL),(8,'create','2015-09-21 13:17:14','8','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:14:\"resetting.html\";}',NULL),(9,'create','2015-09-21 13:17:14','9','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:10:\"check.html\";}',NULL),(10,'create','2015-09-21 13:17:14','10','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"receipt.html\";}',NULL),(11,'create','2015-09-21 13:17:14','11','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:12:\"rjCheck.html\";}',NULL),(12,'create','2015-09-21 13:17:14','12','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:21:\"rjLandLordInvite.html\";}',NULL),(13,'create','2015-09-21 13:17:14','13','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjTenantInvite.html\";}',NULL),(14,'create','2015-09-21 13:17:14','14','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:24:\"rjTenantLatePayment.html\";}',NULL),(15,'create','2015-09-21 13:17:14','15','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:29:\"rjLandlordComeFromInvite.html\";}',NULL),(16,'create','2015-09-21 13:17:14','16','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:22:\"rjPendingContract.html\";}',NULL),(17,'create','2015-09-21 13:17:14','17','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"exist_invite.html\";}',NULL),(18,'create','2015-09-21 13:17:14','18','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:20:\"rjTodayPayments.html\";}',NULL),(19,'create','2015-09-21 13:17:14','19','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjTodayNotPaid.html\";}',NULL),(20,'create','2015-09-21 13:17:14','20','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:18:\"rjDailyReport.html\";}',NULL),(21,'create','2015-09-21 13:17:14','21','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:25:\"rjTenantLateContract.html\";}',NULL),(22,'create','2015-09-21 13:17:14','22','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"rjPaymentDue.html\";}',NULL),(23,'create','2015-09-21 13:17:14','23','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:24:\"rjListLateContracts.html\";}',NULL),(24,'create','2015-09-21 13:17:14','24','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjOrderReceipt.html\";}',NULL),(25,'create','2015-09-21 13:17:14','25','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"rjOrderError.html\";}',NULL),(26,'create','2015-09-21 13:17:14','26','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:27:\"rjTenantInviteReminder.html\";}',NULL),(27,'create','2015-09-21 13:17:14','27','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:34:\"rjTenantInviteReminderPayment.html\";}',NULL),(28,'create','2015-09-21 13:17:14','28','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:23:\"rjContractApproved.html\";}',NULL),(29,'create','2015-09-21 13:17:14','29','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:38:\"rjContractRemovedFromDbByLandlord.html\";}',NULL),(30,'create','2015-09-21 13:17:14','30','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:36:\"rjContractRemovedFromDbByTenant.html\";}',NULL),(31,'create','2015-09-21 13:17:14','31','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:26:\"rjMerchantNameSetuped.html\";}',NULL),(32,'create','2015-09-21 13:17:14','32','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:17:\"rj_resetting.html\";}',NULL),(33,'create','2015-09-21 13:17:14','33','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:18:\"rjEndContract.html\";}',NULL),(34,'create','2015-09-21 13:17:14','34','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:18:\"rjOrderCancel.html\";}',NULL),(35,'create','2015-09-21 13:17:14','35','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:28:\"rjOrderCancelToLandlord.html\";}',NULL),(36,'create','2015-09-21 13:17:14','36','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:19:\"rjPendingOrder.html\";}',NULL),(37,'create','2015-09-21 13:17:14','37','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:28:\"rjContractAmountChanged.html\";}',NULL),(38,'create','2015-09-21 13:17:14','38','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:33:\"rjBatchDepositReportLandlord.html\";}',NULL),(39,'create','2015-09-21 13:17:14','39','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:32:\"rjBatchDepositReportHolding.html\";}',NULL),(40,'create','2015-09-21 13:17:14','40','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:14:\"rjReceipt.html\";}',NULL),(41,'create','2015-09-21 13:17:14','41','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:30:\"rjPushBatchReceiptsReport.html\";}',NULL),(42,'create','2015-09-21 13:17:14','42','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:33:\"rjYardiPaymentAcceptedTurnOn.html\";}',NULL),(43,'create','2015-09-21 13:17:14','43','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:34:\"rjYardiPaymentAcceptedTurnOff.html\";}',NULL),(44,'create','2015-09-21 13:17:14','44','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:28:\"rjLateReportingLandlord.html\";}',NULL),(45,'create','2015-09-21 13:17:14','45','Rj\\EmailBundle\\Entity\\EmailTemplate',1,'a:1:{s:4:\"name\";s:26:\"rjLateReportingTenant.html\";}',NULL),(46,'create','2015-09-21 13:17:15','1','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:24:\"Welcome to Credit Jeeves\";}',NULL),(47,'create','2015-09-21 13:17:15','2','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1853:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to Credit Jeeves{% endblock %}\n{% block email %}\n      <p>\n          {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n          The Credit Jeeves program shows you your current credit score, a summary of your credit profile, and a customized action plan to help\n          you reach your target score. We then monitor your progress over the next few months to let you know when you are likely qualified for a loan.\n      </p>\n      <p>\n          Enrollment is free, simple, and takes less than a minute. Credit Jeeves will not negatively impact your credit and does not post a\n          \'hard inquiry.\'\n      </p>\n      <p>\n          Set up your Credit Jeeves Account now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a> and take the first step towards better financing.\n      </p>\n      <p>\n          You will be able to:\n          * See and monitor your current credit score.\n          * Follow easy-to-understand actions to optimize your score for your goals.\n          * See a summary of your credit file and learn more about how this information affects your score.\n          * Receive alerts when you reach your target score.\n      </p>\n      <br />\n      <p>\n        Tip: Do not shop around for a loan right now. This will create multiple \'hard inquiries\' on your credit file which can negatively\n        impact your score. Credit Jeeves makes a \'soft inquiry\' and will allow you to view your score and action plan without hurting your\n        chances to requalify for a loan in the future.\n      </p>\n      <p>\n          Again, {{ groupName }} is providing you this service for free.\n      </p>\n      <p>\n      Sign Up Now at <a href=\"{{ inviteLink }}\">{{ inviteLink }}</a>\n      </p>\n{% endblock %}\";}',NULL),(48,'create','2015-09-21 13:17:15','3','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:24:\"Welcome to Credit Jeeves\";}',NULL),(49,'create','2015-09-21 13:17:15','4','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1166:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Welcome to CreditJeeves{% endblock %}{% block email %}<p>You have taken the first step towards your new car.</p><p>To see your customized action plan, sign in at <a href=\"http://my.creditjeeves.com/\">cj</a> anytime.</p><strong>Get started today:</strong><ul>  <li>Understand<a href=\"http://www.creditjeeves.com/educate/understand-your-credit-score\">how your credit score is determined</a></li><li>Review your <a href=\"http://cj/_dev.php/?\">action plan</a> and decide what step you will take first.</li><li>Click on the \"learn more\" link next to that step to find out what to do.</li></ul><i>Trouble answering the verification questions?</i><p>It is a good idea to get a <a href=\"https://www.annualcreditreport.com/\"> free copy of your credit report </a> to see if contains something you do not recognize. You can also contact <a href=\"mailto:help@creditjeeves.com\">help@creditjeeves.com</a> if your account becomes locked. </p><i>We want to hear from you!</i><p>Please <a href=\"http://creditjeeves.uservoice.com/\">send us your feedback</a> on how we can make the product better for you.</p>{% endblock %}\";}',NULL),(50,'create','2015-09-21 13:17:15','5','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:54:\"Your Credit Score has Changed - Log Into Credit Jeeves\";}',NULL),(51,'create','2015-09-21 13:17:15','6','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:48:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\";}',NULL),(52,'create','2015-09-21 13:17:15','7','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:44:\"Your New Car Awaits - Log into Credit Jeeves\";}',NULL),(53,'create','2015-09-21 13:17:15','8','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:543:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Congratulations!{% endblock %}\n{% block email %}\n  <div mc:edit=\"std_content00\">\n      You have reached your dealer\'s target score of <strong>{{ targetScore }}</strong>\n  </div>\n  <div mc:edit=\"latest_score_button\">\n      <br />\n      <hr />\n      Log into Credit Jeeves to find out what to do next. Your new car awaits!\n      <br />\n      <a class=\"button\" href=\"{{ loginLink }}\" id=\"viewLatestScoreButton\">View Latest Score</a>\n      <br />\n      <hr />\n  </div>\n{% endblock %}\n\";}',NULL),(54,'create','2015-09-21 13:17:15','9','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:46:\"One of your leads has reached the Target Score\";}',NULL),(55,'create','2015-09-21 13:17:15','10','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:48:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\";}',NULL),(56,'create','2015-09-21 13:17:15','11','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:46:\"One of your leads has reached the Target Score\";}',NULL),(57,'create','2015-09-21 13:17:15','12','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:48:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\";}',NULL),(58,'create','2015-09-21 13:17:15','13','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:39:\"Example email with all avaliable fields\";}',NULL),(59,'create','2015-09-21 13:17:15','14','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:575:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}{% block h1 %}Heading 1{% endblock %}{% block h2 %}Heading 2{% endblock %}{% block h3 %}Heading 3{% endblock %}{% block h4 %}Heading 4{% endblock %}{% block email %}{% set button = {\"text\": \"Hmm, we could add more than one button in the email body!\",\"value\": \"Test\",\"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}<p>Lorem ipsum...</p>{% set button = {\"text\": \"Some text above button\", \"value\": \"Click It\", \"link\": \"#\"} %}{% include \"CoreBundle:Mailer:button.html.twig\" with button %}{% endblock %}\";}',NULL),(60,'create','2015-09-21 13:17:15','15','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:14:\"Reset Password\";}',NULL),(61,'create','2015-09-21 13:17:15','16','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:435:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  CreditJeeves will never e-mail you and ask you to disclose or verify your CreditJeeves.com password, credit card, or banking account number.\n\n  Thank you for using CreditJeeves!\n{% endblock %}\n\";}',NULL),(62,'create','2015-09-21 13:17:15','17','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:11:\"Check Email\";}',NULL),(63,'create','2015-09-21 13:17:15','18','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:308:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Credit Jeeves account is almost ready!{% endblock %}\n{% block email %}Hello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n\";}',NULL),(64,'create','2015-09-21 13:17:15','19','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Receipt from Credit Jeeves\";}',NULL),(65,'create','2015-09-21 13:17:15','20','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:500:\"<div mc:edit=\"std_content00\">\n<h1 class=\"h1\">Receipt from Credit Jeeves</h1>\nThank you for purchasing your credit report through Credit Jeeves.\nYour payment was processed successfully and will appear on your next statement under CREDITJEEVE.\nHere is your receipt:<br />\n&nbsp;<br />\n<hr />\nPayment Date & Time:&nbsp;{{ date }}<br />\nPayment Amount: {{ amout }}<br />\nReference Number: {{ number }}<br />\n<br />\n<hr />\nRemember, we\'re here to help,<br /><strong>The Credit Jeeves Team</strong>\n</div>\n\";}',NULL),(66,'create','2015-09-21 13:17:15','21','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Get Started with RentTrack\";}',NULL),(67,'create','2015-09-21 13:17:15','22','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:312:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your RentTrack account is almost ready!{% endblock %}\n{% block email %}\nHello {{ user.full_name }},\n<br /><br />\nPlease visit <a href=\"{{ checkUrl }}\">{{ checkUrl }}</a> to confirm your registration.\n<br /><br />\nSee you soon!\n{% endblock %}\n\";}',NULL),(68,'create','2015-09-21 13:17:15','23','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:50:\"Your Tenant is Ready to Pay Rent through RentTrack\";}',NULL),(69,'create','2015-09-21 13:17:15','24','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:2116:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Get Paid Fast Using RentTrack{% endblock %}\n{% block email %}\n  {% if nameLandlord %}\n      Hi {{ nameLandlord }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br /> \n  {% endif %}\n  Your tenant, {{ fullNameTenant }}, would like to use RentTrack to pay rent on your property at\n  {{ address }} {{ unitName }}. RentTrack allows {{ nameTenant }} to build credit history by\n  reporting on-time payments to credit bureaus. <br /> <br />\n\n  As a landlord, you benefit because RentTrack facilitates easy payments through secure electronic\n  check transfers and credit cards - payments are deposited faster and directly to your account.\n  Reminders are sent automatically to your tenants before rent is due and late notices are sent\n  to you immediately. If you have multiple properties, you can see the status of your payments\n  all in one place. To top it off, your tenant has an additional incentive to pay\n  on time each month.<br /> <br />\n\n  Ready to get paid? <br /> <br />\n  <a id=\"payRentLinkLandlord\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'landlord_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n            style=\"border: none;\n            padding: 2px 7px;\n            text-align: left;\n            color: white;\n            font-size: 14px;\n            text-shadow: 1px 1px 3px #636363;\n            filter: dropshadow(color=#636363, offx=1, offy=1);\n            cursor: pointer;\n            background-color: #669900;\n            -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n            zoom: 1;\n            text-decoration: none;\n            -moz-border-radius: 4px;\n            -webkit-border-radius: 4px;\n            border-radius: 4px;\"\n>Sign up</a> Still have some questions? <a href=\"http://www.renttrack.com/property-management\">Read More</a> or call 866.841.9090\n{% endblock %}\n\";}',NULL),(70,'create','2015-09-21 13:17:15','25','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:58:\"Your Landlord is Requesting Rent Payment through RentTrack\";}',NULL),(71,'create','2015-09-21 13:17:15','26','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1907:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ rentAddress }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit history by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode, \'isImported\': isImported }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n\";}',NULL),(72,'create','2015-09-21 13:17:15','27','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:25:\"Your Rent Payment is Late\";}',NULL),(73,'create','2015-09-21 13:17:15','28','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:815:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Late. Pay Now!{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Dear {{ nameTenant }}, <br />  <br />\n  {% else %}\n      Heads Up!<br /> <br />\n  {% endif %}\n  It looks like {{ fullNameLandlord }} expected your rent payment for {{ address }} {{ unitName }} already.\n\n  <a href=\"http://my.renttrack.com/\">Log in to RentTrack today</a> and and make an immediate payment. We\'ll\n  let {{ fullNameLandlord }} that rent is on its way once the payment goes through.\n\n  Better yet, you can set up automatic payments so you never miss one again. <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">Learn More</a>\n\n  Watching out for you,\n  The RentTrack Team\n{% endblock %}\n\";}',NULL),(74,'create','2015-09-21 13:17:15','29','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:30:\"Your Landlord Joined RentTrack\";}',NULL),(75,'create','2015-09-21 13:17:15','30','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:416:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n  Congratulations! {{ fullNameLandlord }} has teamed up with RentTrack.\n  <br /><br />\n  We\'re now working with them to ready their account to accept payments. You\'ll receive another email when you\'re\n  approved to pay rent online.\n  <br /><br />\n  Thank you for your patience!\n{% endblock %}\n\";}',NULL),(76,'create','2015-09-21 13:17:15','31','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Your Tenant Needs Approval\";}',NULL),(77,'create','2015-09-21 13:17:15','32','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:515:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  {{ nameTenant }} is ready to pay rent for {{ address }}\n  <br /></br />\n  Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a>, click on the Tenants tab, and click on the\n  review \"eye\" next to the pending tenant. You will then be able to add rent details and approve the tenant. Once\n  this is complete, your tenant will be able to set up their rent payment.\n{% endblock %}\n\";}',NULL),(78,'create','2015-09-21 13:17:15','33','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:21:\"Your have new dealer!\";}',NULL),(79,'create','2015-09-21 13:17:15','34','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:369:\"{% extends \"CoreBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your have new dealer!{% endblock %}\n{% block email %}\n    <p>\n        {{ groupName }} has teamed up with Credit Jeeves to help you understand your credit score and achieve your financing goals.\n    </p>\n    <p>\n        Again, {{ groupName }} is providing you this service for free.\n    </p>\n{% endblock %}\n\";}',NULL),(80,'create','2015-09-21 13:17:15','35','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:19:\"Rent Payments Today\";}',NULL),(81,'create','2015-09-21 13:17:15','36','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:544:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Collected{% endblock %}\n{% block email %}\n  Hi {{ nameLandlord }},\n  <br /><br />\n  We collected ${{ amount }} in rent today. To see your recent payments,\n  <a href=\"https://my.renttrack.com/\">log into RentTrack</a> and click on Dashboard.\n  <br /><br />\n  Payments typically settle in 1-3 days to your account. If you suspect a payment is not transferring, or have\n  any other questions, please contact us at help@creditjeeves.com or call 866-841-9090.\n{% endblock %}\n\";}',NULL),(82,'create','2015-09-21 13:17:15','37','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:15:\"Not Paid Today.\";}',NULL),(83,'create','2015-09-21 13:17:15','38','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:228:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n  Today not paid is {{ amount }}\n  <br />\n  <br />\n  Enjoy, <br />\n  The RentTrack Team\n{% endblock %}\n\";}',NULL),(84,'create','2015-09-21 13:17:15','39','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:22:\"RentTrack Daily Report\";}',NULL),(85,'create','2015-09-21 13:17:15','40','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:756:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameLandlord }}!{% endblock %}\n{% block email %}\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Status</th>\n      <th style=\"padding:5px;\">Amount</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for key, value in report %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ key }}</td>\n      <td style=\"padding:5px;\">\n      {% if value > 0 %}\n        ${{ value }}\n      {% else %}\n      ---\n      {% endif %}\n      </td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n{% endblock %}\n\";}',NULL),(86,'create','2015-09-21 13:17:15','41','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:20:\"Rent Payment is Late\";}',NULL),(87,'create','2015-09-21 13:17:15','42','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:876:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }},{% endblock %}\n{% block email %}\n  It looks like your rent payment for {{ address }} is <b>late by {{ diff }} day(s)</b>.\n  <br /><br />\n  <a href=\"https://my.renttrack.com/\">Log into RentTrack</a> today to make a new payment. We\'d recommend setting up\n  <a href=\"https://renttrack.uservoice.com/knowledgebase/articles/263021-how-do-i-set-up-automatic-payments-\">automatic payments</a>\n  so you won\'t see an email like this next month.\n  <br /><br />\n  If you have alread paid by a different method like cash or (*gasp*) paper check, then your landlord needs\n  to log into RentTrack and update your records. They have also received an email reminder regarding this payment.\n  <br /><br />\n  If you need assistance, please email help@renttrack.com or call (866) 841-9090.\n{% endblock %}\n\";}',NULL),(88,'create','2015-09-21 13:17:15','43','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:16:\"Your Rent Is Due\";}',NULL),(89,'create','2015-09-21 13:17:15','44','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:962:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Rent Is Due{% endblock %}\n{% block email %}\nYour rent payment to {{ nameHolding }} for {{ address }} is coming up.\n<br /><br />\n{% if paymentType == \'recurring\' %}\n  It looks like you have recurring payments set up, so we\'ll send you another email when we make your payment.\n  Please note that if you are paying by credit card, you will also pay a technology fee with your rent.\n  If you need to change your payment details or cancel your payment,\n  please <a href=\"https://my.renttrack.com/\">log in to RentTrack today</a> and make any adjustments.\n{% elseif paymentType == \'one_time\' %}\n  It looks like you already have a payment set up, so we\'ll send you another email when we make your payment.\n{% else %}\n  You do not have recurring payments set up. <a href=\"https://my.renttrack.com/\">Log in to RentTrack today</a>\n  to set up a one-time or recurring payment.\n{% endif %}\n{% endblock %}\n\";}',NULL),(90,'create','2015-09-21 13:17:15','45','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:25:\"Review Late Rent Payments\";}',NULL),(91,'create','2015-09-21 13:17:15','46','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1185:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ nameLandlord }},{% endblock %}\n{% block email %}\nThe following tenants have not submitted on-time payments:\n<table \n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <thead>\n    <tr\n      style=\"background-color: #F5F5F5; border: 1px solid #C8C8C8;\"\n    >\n      <th style=\"padding:5px;\">Tenant</th>\n      <th style=\"padding:5px;\">Email</th>\n      <th style=\"padding:5px;\">Address</th>\n      <th style=\"padding:5px;\">Days Late</th>\n    </tr>\n  </thead>\n  <tbody>\n    {% for tenant in tenants %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ tenant.name }}</td>\n      <td style=\"padding:5px;\">{{ tenant.email }}</td>\n      <td style=\"padding:5px;\">{{ tenant.address }}</td>\n      <td style=\"padding:5px;\">{{ tenant.late }}</td>\n    </tr>\n    {% endfor %}\n  </tbody>\n</table>\n  <br />\n  Please <a href=\"https://my.renttrack.com\">log into RentTrack</a>\n  and click on \"Resolve\" next to late tenants at the top of the Payments Dashboard to either record payments\n  via alternate means or to send them an email reminder.\n{% endblock %}\n\";}',NULL),(92,'create','2015-09-21 13:17:15','47','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:20:\"Rent Payment Receipt\";}',NULL),(93,'create','2015-09-21 13:17:15','48','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1601:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Paid{% endblock %}\n{% block email %}\n{% if nameTenant %}\n  Hi {{ nameTenant }}! <br /><br />\n{% else %}\n  Hello!  <br /><br />\n{% endif %}\n\nYour rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n\";}',NULL),(94,'create','2015-09-21 13:17:15','49','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:11:\"Order Error\";}',NULL),(95,'create','2015-09-21 13:17:15','50','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1831:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ nameTenant }}!{% endblock %}\n{% block email %}\n{{ \'order.error.title\'| trans }}.\n<br /><br />\n{{ \'order.error.message\' | trans }}: {{ error }}\n<br /><br />\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.paid.to\' | trans }}:</td><td style=\"padding:5px;\">{{ groupName }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ datetime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'amount\' | trans }}:</td><td style=\"padding:5px;\">{{ amount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n    \n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.id\' | trans }}:</td><td style=\"padding:5px;\">{{ orderId }}</td>\n    </tr>\n    {% if transactionId > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionId }}</td>\n    </tr>\n    {% endif %}\n  </tbody>\n</table>\n{{ \'order.contact.us\' | trans }}\n{% endblock %}\n\";}',NULL),(96,'create','2015-09-21 13:17:15','51','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:68:\"Reminder. Your Landlord is Requesting Rent Payment through RentTrack\";}',NULL),(97,'create','2015-09-21 13:17:15','52','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1884:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n  {% if inviteCode %}\n    href=\"http://{{ serverName }}{{ path(\'tenant_invite\', {\'code\': inviteCode }) }}\"\n  {% else %}\n    href=\"http://{{ serverName }}/\"\n  {% endif %}\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n\";}',NULL),(98,'create','2015-09-21 13:17:15','53','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:51:\"Reminder. Your Landlord ask to install your payment\";}',NULL),(99,'create','2015-09-21 13:17:15','54','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1750:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Pay Rent. Built Credit.{% endblock %}\n{% block email %}\n  {% if nameTenant %}\n      Hi {{ nameTenant }}! <br />  <br />\n  {% else %}\n      Hello!  <br /> <br />\n  {% endif %}\n  Your landlord, {{ fullNameLandlord }}, would like you to use RentTrack to pay your rent for\n  {{ address }} {{ unitName }}. RentTrack makes it easy to pay rent through secure electronic check transfers\n  and credit card payments - you get to choose. You also have the opportunity to build credit by signing up for\n  credit bureau payment reporting. Finally, <b>paying by electronic checks is completely free</b>.\n  <br /> <br />\n\n  Ready to get something out of your rent payments?<br /> <br />\n  <a id=\"payRentLink\"\n    href=\"http://{{ serverName }}/\"\n    style=\"\n                  border: none;\n                  padding: 2px 7px;\n                  text-align: left;\n                  color: white;\n                  font-size: 14px;\n                  text-shadow: 1px 1px 3px #636363;\n                  filter: dropshadow(color=#636363, offx=1, offy=1);\n                  cursor: pointer;\n                  background-color: #669900;\n                  -ms-filter: \'progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff,endColorstr=#00ffffff)\';\n                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#00ffffff, endColorstr=#00ffffff);\n                  zoom: 1;\n                  text-decoration: none;\n                  -moz-border-radius: 4px;\n                  -webkit-border-radius: 4px;\n                  border-radius: 4px;\n          \">Pay Rent</a> Still have some questions? <a href=\"http://www.renttrack.com/how-it-works\">Learn More</a>\n{% endblock %}\n\";}',NULL),(100,'create','2015-09-21 13:17:15','55','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:34:\"You\'re Approved to Pay Rent Online\";}',NULL),(101,'create','2015-09-21 13:17:15','56','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:358:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}You\'re Approved!{% endblock %}\n{% block email %}\nHello {{ nameTenant }},\n\nYour landlord has approved you and you can now set up your rent payment. Please <a href=\"http://my.renttrack.com/\">log in to RentTrack</a> and click on the \"Pay\" button corresponding to your rental.\n{% endblock %}\n\";}',NULL),(102,'create','2015-09-21 13:17:15','57','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:41:\"You Contract was Removed by Your Landlord\";}',NULL),(103,'create','2015-09-21 13:17:15','58','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:326:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameTenant }},{% endblock %}\n{% block email %}\n  Your landlord, {{ fullNameLandlord }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\n<br /><br />\nIf this is an error, please contact your landlord.\n{% endblock %}\";}',NULL),(104,'create','2015-09-21 13:17:15','59','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:40:\"Your Contract was Removed by Your Tenant\";}',NULL),(105,'create','2015-09-21 13:17:15','60','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:310:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your tenant, {{ fullNameTenant }}, removed the contract on RentTrack for:<br />\n  {{ address }} {{ unitName }}.\nIf this is an error, please contact your tenant.\n{% endblock %}\n\";}',NULL),(106,'create','2015-09-21 13:17:15','61','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:41:\"Your RentTrack Merchant Account is Ready!\";}',NULL),(107,'create','2015-09-21 13:17:15','62','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:677:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hello {{ fullNameLandlord }},{% endblock %}\n{% block email %}\n  Your merchant account for \"{{ groupName }}\" is approved and ready!\n  <br /><br />\n\n  You can now accept rent payments online, and funds will be deposited into the account\n  you specified in your application. Begin by\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/285491-how-do-i-add-or-invite-a-tenant-\">inviting your tenants</a>, or\n  <a href=\"http://renttrack.uservoice.com/knowledgebase/articles/275851-how-do-i-approve-a-tenant-so-they-can-pay-rent-\">approving any pending tenants</a>\n  that invited you.\n{% endblock %}\n\";}',NULL),(108,'create','2015-09-21 13:17:15','63','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:14:\"Reset Password\";}',NULL),(109,'create','2015-09-21 13:17:15','64','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:579:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ user.full_name }}!{% endblock %}\n{% block email %}\n  You recently asked to reset your password.\n  <a href=\"{{ confirmationUrl }}\">Click here to change your password.</a>\n\n  Didn\'t request this change?\n  If you didn\'t request a new password, please contact us at <a mailto=\"help@renttrack.com\">help@renttrack.com</a>.\n\n  RentTrack will never e-mail you and ask you to disclose or verify your RentTrack.com password, credit card, or banking account number.\n\n  Thank you for using RentTrack!\n{% endblock %}\";}',NULL),(110,'create','2015-09-21 13:17:15','65','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:12:\"End Contract\";}',NULL),(111,'create','2015-09-21 13:17:15','66','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:390:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Hi {{ tenantFullName }}!{% endblock %}\n{% block email %}\n   Your landlord {{landlordFullName}}, has ended contract by address: {{ address }} #{{ unitName }}.\n   {% if uncollectedBalance > 0%}\n      And you have uncollected balance on this contract {{ uncollectedBalance }}$.\n   {% else %}\n\n   {% endif %}\n{% endblock %}\n\";}',NULL),(112,'create','2015-09-21 13:17:15','67','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:30:\"Your Rent Payment was Reversed\";}',NULL),(113,'create','2015-09-21 13:17:15','68','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:774:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Dear {{ tenantFullName }},{% endblock %}\n{% block email %}\n  {% if orderStatus == \'refunded\' %}\n  Per your request, your rent of {{ rentAmount }} sent on {{ orderDate }} was refunded and should appear in your account within a few days.\n  {% elseif orderStatus == \'cancelled\' %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was cancelled.\n  {% else %}\n  Your payment of {{ rentAmount }} sent on {{ orderDate }} was returned. Your rent is currently not paid.\n  You will receive a follow up from RentTrack customer support with the reason for return and ways to fix it.\n  {% endif %}\n  If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n\";}',NULL),(114,'create','2015-09-21 13:17:15','69','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:30:\"Your Rent Payment was Reversed\";}',NULL),(115,'create','2015-09-21 13:17:15','70','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1307:\"{% extends \'RjComponentBundle:Mailer:base.html.twig\' %}\n{% block h1 %}Dear {{ landlordFirstName }},{% endblock %}\n{% block email %}\n    {% if orderStatus == \'refunded\' %}\n\n    At the request of {{ tenantName }}, their rent of {{ rentAmount }} sent on {{ orderDate }} was refunded.\n    Any monies already deposited will be deducted from your account within a couple of days.\n    Please contact your tenant if you have any questions regarding this refund.\n    {% elseif orderStatus == \'cancelled\' %}\n\n    At the request of {{ tenantName}}, their rent payment of {{ rentAmount }} sent on {{ orderDate }}\n    was cancelled. You will not see a deposit in your account since it was cancelled before\n    payment settlement. Please contact your tenant if you have any questions regarding this cancellation.\n    {% else %}\n\n    The rent payment by {{ tenantName }} for {{ rentAmount }} sent on {{ orderDate }} was returned.\n    Any monies already deposited  will be deducted from your account per the RentTrack terms of service.\n    Your tenant\'s rent is currently unpaid.\n    Your tenant may try to pay again through RentTrack, or you may arrange an alternate, immediate payment method.\n    {% endif %}\n\n    If you have any other questions, please contact help@renttrack.com or call 866-841-9090 x2.\n{% endblock %}\n\";}',NULL),(116,'create','2015-09-21 13:17:15','71','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:23:\"Your Rent is Processing\";}',NULL),(117,'create','2015-09-21 13:17:15','72','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1750:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Your Rent is Processing{% endblock %}\n{% block email %}\n  Hi {{ tenantName }}! <br /><br />\n\n  Your rent payment to {{ groupName }} was sent just now. They should see the deposit in their account in 1-3 days.\n\nThe details:\n\n<table\n  width=\"100%\"\n  style=\"\n    border: 1px solid #C8C8C8;\n    border-collapse: collapse;\n \"\n>\n  <tbody>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.date.time\' | trans }}:</td><td style=\"padding:5px;\">{{ orderTime }}</td>\n    </tr>\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.transaction.id\' | trans }}:</td><td style=\"padding:5px;\">{{ transactionID }}</td>\n    </tr>\n    <tr style=\'border: 1px solid #C8C8C8;\'>\n        <td style=\'padding:5px;\'>{{ \'email.rent_amount\' | trans }}:</td>\n        <td style=\'padding:5px;\'>{{ rentAmount }}</td>\n    </tr>\n    <tr style=\'border: 1px solid #C8C8C8;\'>\n        <td style=\'padding:5px;\'>{{ \'email.other_amount\' | trans }}:</td>\n        <td style=\'padding:5px;\'>{{ otherAmount }}</td>\n    </tr>\n    {% if fee > 0 %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ fee }}</td>\n    </tr>\n    {% else %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.fee\' | trans }}:</td><td style=\"padding:5px;\">{{ \'order.fee.free\' | trans }}</td>\n    </tr>\n    {% endif %}\n    <tr style=\"border: 1px solid #C8C8C8;\">\n      <td style=\"padding:5px;\">{{ \'order.total\' | trans }}:</td><td style=\"padding:5px;\">{{ total }}</td>\n    </tr>\n\n  </tbody>\n</table>\n</br>\n</br>\n</br>\n{{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n\";}',NULL),(118,'create','2015-09-21 13:17:15','73','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:46:\"Your Rent amount was adjusted on your contract\";}',NULL),(119,'create','2015-09-21 13:17:15','74','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:868:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ tenantName }}! <br />\n  <br />\n  Your property manager has adjusted the rent amount on your contract to {{ rentAmount }}.\n  Since the recurring payment you had set up for {{ paymentAmount }} is no longer correct,\n  we have cancelled your recurring payment.<br />\n  </br>\n  Please <a href=\"https://my.renttrack.com/\">log in to RentTrack</a> and set up a new recurring payment.\n  Be sure to specify the correct month that your next recurring payment should count for.<br />\n  </br>\n  If you have any questions regarding this change, please contact your property manager.\n  If you have questions about setting up a new recurring payment,\n  please contact us at help@renttrack.com or call (866) 841-9090.</br>\n  </br>\n  </br>\n  </br>\n  {{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n\";}',NULL),(120,'create','2015-09-21 13:17:15','75','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Daily Batch Deposit Report\";}',NULL),(121,'create','2015-09-21 13:17:15','76','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:5285:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Batch Deposit Report{% endblock %}\n{% block email %}\nDear {{ landlordFirstName }}, <br />\nYour batch deposit report for <b>{{ date | date(\"m/d/Y\") }}</b> for group <b>{{ groupName }}</b>\n{% if accountNumber %}(Account #{{ accountNumber }}){% endif %} is below:<br />\n<br />\n{% if batches %}\n  {% for batch in batches %}\n  Batch ID: <b class=\"batch-id\">{{ batch.batchId }}</b><br />\n  {% if groupPaymentProcessor == \'heartland\' %}\n      Payment Type: <b>{{ (\'order.type.\' ~ batch.paymentType) | trans }}</b><br />\n  {% endif %}\n  <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n    <thead>\n      <tr>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_initiated\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n      </tr>\n    </thead>\n    <tfoot>\n      <tr>\n         <td colspan=\"5\" style=\"padding:3px;border: 1px solid #4E4E4E;\" align=\"right\"><b>{{ \'order.total\' | trans }}:</b></td>\n         <td style=\"padding:3px;border: 1px solid #4E4E4E;\"><b>${{ batch.paymentTotal }}</b></td>\n      </tr>\n     </tfoot>\n    <tbody>\n      {% for transaction in batch.transactions %}\n      <tr>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'transaction.status.text.\' ~ transaction.transactionStatus) | trans }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n        {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n        </td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.dateInitiated | date(\"m/d/Y\") }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">${{ transaction.amount }}</td>\n      </tr>\n      {% endfor %}\n    </tbody>\n  </table>\n  <br />\n  {% endfor %}\n{% endif %}\n{% if returns %}\n  Reversals (Each will be Debited Separately)\n  <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n    <thead>\n      <tr>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.status_message\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_reversal\' | trans }}</th>\n        <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n      </tr>\n    </thead>\n    <tbody>\n      {% for transaction in returns %}\n      <tr>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'order.status.text.\' ~ transaction.orderStatus) | trans }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.messages }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n        {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n        </td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.reversalDate | date(\"m/d/Y\") }}</td>\n        <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">-${{ transaction.amount | abs }}</td>\n      </tr>\n      {% endfor %}\n    </tbody>\n  </table>\n{% endif %}\n{% if not (returns and batches)  %}\nThere are no deposits to report.\n{% endif %}\n{% endblock %}\n\";}',NULL),(122,'create','2015-09-21 13:17:15','77','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Daily Batch Deposit Report\";}',NULL),(123,'create','2015-09-21 13:17:15','78','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:5568:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block h1 %}Batch Deposit Report{% endblock %}\n{% block email %}\nDear {{ landlordFirstName }}, <br />\nYour batch deposit report for <b>{{ date | date(\"m/d/Y\") }}</b> is below:\n{% for group in groups %}\n<br />\n<br />For group <b class=\"group-name\">{{ group.groupName }}</b>{% if group.accountNumber %} (Account #{{ group.accountNumber }}){% endif %}:<br />\n<br />\n  {% if group.batches %}\n    {% for batch in group.batches %}\n    Batch ID: <b class=\"batch-id\">{{ batch.batchId }}</b><br />\n    {% if group.groupPaymentProcessor == \'heartland\' %}\n        Payment Type: <b>{{ (\'order.type.\' ~ batch.paymentType) | trans }}</b><br />\n    {% endif %}\n    <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n      <thead>\n        <tr>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_initiated\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n        </tr>\n      </thead>\n      <tfoot>\n        <tr>\n           <td colspan=\"5\" style=\"padding:3px;border: 1px solid #4E4E4E;\" align=\"right\"><b>{{ \'order.total\' | trans }}:</b></td>\n           <td style=\"padding:3px;border: 1px solid #4E4E4E;\"><b>${{ batch.paymentTotal }}</b></td>\n        </tr>\n       </tfoot>\n      <tbody>\n        {% for transaction in batch.transactions %}\n        <tr>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'transaction.status.text.\' ~ transaction.transactionStatus) | trans }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n            {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n          </td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.dateInitiated | date(\"m/d/Y\") }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">${{ transaction.amount }}</td>\n        </tr>\n        {% endfor %}\n      </tbody>\n    </table>\n    <br />\n    {% endfor %}\n  {% endif %}\n  {% if group.returns %}\n    Reversals (Each will be Debited Separately)\n    <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n      <thead>\n        <tr>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>{{ \'order.transaction.id.short\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.status\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.status_message\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'order.resident\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.property\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'payment.date_reversal\' | trans }}</th>\n          <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">{{ \'amount\' | trans }}</th>\n        </tr>\n      </thead>\n      <tbody>\n        {% for transaction in group.returns %}\n        <tr>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.transactionId }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ (\'order.status.text.\' ~ transaction.orderStatus) | trans }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.messages }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.resident }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n          {{ transaction.property }}{% if not transaction.isSingle %}{{ \' #\' ~ transaction.unitName }}{% endif %}\n          </td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">{{ transaction.reversalDate | date(\"m/d/Y\") }}</td>\n          <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">-${{ transaction.amount | abs }}</td>\n        </tr>\n        {% endfor %}\n      </tbody>\n    </table>\n  {% endif %}\n  {% if not (group.returns and group.batches) %}\n  There are no deposits to report.\n  {% endif %}\n{% endfor %}\n{% endblock %}\n\";}',NULL),(124,'create','2015-09-21 13:17:15','79','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:23:\"Receipt from Rent Track\";}',NULL),(125,'create','2015-09-21 13:17:15','80','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:519:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ tenantName }}! <br />\n  <br />\n  Thank you for purchasing your credit report through Credit Jeeves.\n  Your payment was processed successfully and will appear on your next statement.\n  Here is your receipt:<br />\n  &nbsp;<br />\n  <hr />\n  Payment Date & Time:&nbsp;{{ date }}<br />\n  Payment Amount: {{ amout }}<br />\n  Reference Number: {{ number }}<br />\n  <br />\n  </br>\n  </br>\n  {{ \'order.receipt.footer\' | trans }}\n{% endblock %}\n\";}',NULL),(126,'create','2015-09-21 13:17:15','81','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:26:\"Push Batch Receipts Report\";}',NULL),(127,'create','2015-09-21 13:17:15','82','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:2071:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n The following batch deposits for {{ data[\'deposit_date\']|date(\'Y-m-d\') }} were uploaded to your accounting system.\n Please review and post the batches.\n <br />\n <br />\n <table width=\"100%\" style=\"border: 1px solid #4E4E4E; border-collapse: collapse;font-size: 12px;\">\n   <thead>\n     <tr>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>\n          {{ \'common.batch_id\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\" nowrap>\n          {{ \'yardi.accounting_batch_id\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">\n          {{ \'common.type_ach_cc\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">\n          {{ \'yardi.email.number_of_payments\'| trans }}\n       </th>\n       <th style=\"padding:3px;border: 1px solid #4E4E4E;background: #ccc;\">\n          {{ \'common.status\'| trans }}\n       </th>\n     </tr>\n   </thead>\n   <tbody>\n        {% for value in data[\'data\'] %}\n          <tr>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'payment_batch_id\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'batchId\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'type\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'total\'] }}\n            </td>\n            <td style=\"padding:3px;border: 1px solid #4E4E4E;{{ cycle([\'background: #eee;\', \'\'], loop.index) }}\">\n              {{ value[\'status\'] }}\n            </td>\n          </tr>\n        {% endfor %}\n      </tbody>\n    </table>\n{% endblock %}\n\";}',NULL),(128,'create','2015-09-21 13:17:15','83','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:23:\"Online Payments Enabled\";}',NULL),(129,'create','2015-09-21 13:17:15','84','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:254:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ TenantName }},\n  Your property manager has re-enabled online payments. You can now\n  <a href=\"{{ href }}\">log into RentTrack</a> and set up a new payment.\n{% endblock %}\n\";}',NULL),(130,'create','2015-09-21 13:17:15','85','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:24:\"Online Payments Disabled\";}',NULL),(131,'create','2015-09-21 13:17:15','86','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:316:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ TenantName }},\n  Your property manager has disabled online payments. Any unprocessed payments you had scheduled\n  through RentTrack have been cancelled. Contact your property manager immediately for more information.\n{% endblock %}\n\";}',NULL),(132,'create','2015-09-21 13:17:15','87','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:34:\"Action Required for Rent Reporting\";}',NULL),(133,'create','2015-09-21 13:17:15','88','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:1327:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ landlordName }},\n  As part of our regular audit of rent payments we plan to report to the bureaus, we noticed that:\n  {% for contract in contracts %}\n\n  {{ contract.tenant.fullname }}{% if contract.tenant.getResidentForHolding(contract.holding) %}, {{ contract.tenant.getResidentForHolding(contract.holding).getResidentId() }}{% endif %}\n  does not have a payment on record for {{ month }}, and will be reported as delinquent.\n\n  {% endfor %}\n\n  If this person has paid outside of RentTrack, please log in and resolve the late alert you will see on your dashboard by entering an external/cash payment:\n  <a href=\"http://help.renttrack.com/knowledgebase/articles/289089-how-do-i-resolve-a-late-payment\">http://help.renttrack.com/knowledgebase/articles/289089-how-do-i-resolve-a-late-payment</a>.\n\n  Alternatively, you can end her contract with an outstanding balance if she is truly delinquent:\n  <a href=\"http://help.renttrack.com/knowledgebase/articles/323277-how-do-i-update-or-end-a-tenant-contract\">http://help.renttrack.com/knowledgebase/articles/323277-how-do-i-update-or-end-a-tenant-contract</a>\n  Or let us know by replying to this email.\n\n  Thank you so much for helping us ensure that the data we report is accurate.\n{% endblock %}\n\";}',NULL),(134,'create','2015-09-21 13:17:15','89','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:7:\"subject\";s:5:\"value\";s:34:\"Action Required for Rent Reporting\";}',NULL),(135,'create','2015-09-21 13:17:15','90','Rj\\EmailBundle\\Entity\\EmailTemplateTranslation',1,'a:3:{s:6:\"locale\";s:2:\"en\";s:8:\"property\";s:4:\"body\";s:5:\"value\";s:835:\"{% extends \"RjComponentBundle:Mailer:base.html.twig\" %}\n{% block email %}\n  Dear {{ tenantName }},\n\n  As part of our regular audit of rent payments we plan to report to the bureaus, we noticed that you are missing a payment for {{ month }}, and may be reported as delinquent.\n\n  Please make a rent payment as soon as possible.\n  If you have paid outside of RentTrack, or if your lease has ended, please contact your property manager and have them update your record in RentTrack.\n  We need them to verify that you have made a payment before we can report it to the bureaus.\n  To learn more, please visit: <help URL to be filled in later>\n  Thank you so much for helping us ensure that the data we report is accurate. If you have any questions, please contact <a href=\"mailto:help@renttrack.com\">help@renttrack.com</a>.\n\n{% endblock %}\n\";}',NULL);
/*!40000 ALTER TABLE `ext_log_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_mapping`
--

DROP TABLE IF EXISTS `import_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) NOT NULL,
  `header_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `mapping_data` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_index_constraint` (`group_id`,`header_hash`),
  KEY `IDX_5AF68566FE54D947` (`group_id`),
  CONSTRAINT `FK_5AF68566FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_mapping`
--

LOCK TABLES `import_mapping` WRITE;
/*!40000 ALTER TABLE `import_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_mapping` ENABLE KEYS */;
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
  `credit_track_payment_account_id` bigint(20) DEFAULT NULL,
  `report_id` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `related_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E956F4E2BE04EA9` (`job_id`),
  KEY `IDX_E956F4E24C3A3BB` (`payment_id`),
  KEY `IDX_E956F4E28D9F6D38` (`order_id`),
  KEY `IDX_E956F4E29305140F` (`credit_track_payment_account_id`),
  KEY `IDX_E956F4E24BD2A4C0` (`report_id`),
  CONSTRAINT `FK_E956F4E24BD2A4C0` FOREIGN KEY (`report_id`) REFERENCES `cj_applicant_report` (`id`),
  CONSTRAINT `FK_E956F4E24C3A3BB` FOREIGN KEY (`payment_id`) REFERENCES `rj_payment` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_E956F4E28D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`),
  CONSTRAINT `FK_E956F4E29305140F` FOREIGN KEY (`credit_track_payment_account_id`) REFERENCES `rj_payment_account` (`id`),
  CONSTRAINT `FK_E956F4E2BE04EA9` FOREIGN KEY (`job_id`) REFERENCES `jms_jobs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jms_job_related_entities`
--

LOCK TABLES `jms_job_related_entities` WRITE;
/*!40000 ALTER TABLE `jms_job_related_entities` DISABLE KEYS */;
INSERT INTO `jms_job_related_entities` VALUES (1,1,1,NULL,NULL,NULL,'2015-09-21 13:17:19','payment'),(2,2,3,NULL,NULL,NULL,'2015-08-21 13:17:19','payment'),(3,2,NULL,46,NULL,NULL,'2015-08-21 13:17:20','order');
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
INSERT INTO `jms_jobs` VALUES (1,'pending','2015-09-21 13:17:19',NULL,NULL,'2015-09-21 13:17:19',NULL,'payment:pay','[\"--app=rj\"]',NULL,NULL,NULL,0,0,'N;',NULL,NULL,NULL,NULL),(2,'finished','2015-08-21 13:17:19','2015-08-21 13:17:19',NULL,'2015-08-21 13:17:19','2015-08-21 13:17:19','payment:pay','[\"--app=rj\"]','Start\nOK',NULL,0,0,0,'N;',5,NULL,NULL,NULL);
/*!40000 ALTER TABLE `jms_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_external_api`
--

DROP TABLE IF EXISTS `order_external_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_external_api` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `api_type` enum('yardi') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yardi' COMMENT '(DC2Type:ExternalApi)',
  `deposit_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9EFE2AD8D9F6D38` (`order_id`),
  CONSTRAINT `FK_9EFE2AD8D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_external_api`
--

LOCK TABLES `order_external_api` WRITE;
/*!40000 ALTER TABLE `order_external_api` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_external_api` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partner`
--

DROP TABLE IF EXISTS `partner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_powered_by` tinyint(1) NOT NULL,
  `request_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_312B3E165E237E06` (`name`),
  UNIQUE KEY `UNIQ_312B3E1619EB6921` (`client_id`),
  CONSTRAINT `FK_312B3E1619EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partner`
--

LOCK TABLES `partner` WRITE;
/*!40000 ALTER TABLE `partner` DISABLE KEYS */;
INSERT INTO `partner` VALUES (1,3,'creditcom',NULL,NULL,NULL,0,'CREDITCOM');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partner_code`
--

LOCK TABLES `partner_code` WRITE;
/*!40000 ALTER TABLE `partner_code` DISABLE KEYS */;
INSERT INTO `partner_code` VALUES (1,1,48,'PARTCODE1',NULL,0);
/*!40000 ALTER TABLE `partner_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partner_user`
--

DROP TABLE IF EXISTS `partner_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partner_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DDA7E551A76ED395` (`user_id`),
  KEY `IDX_DDA7E5519393F8FE` (`partner_id`),
  CONSTRAINT `FK_DDA7E551A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_DDA7E5519393F8FE` FOREIGN KEY (`partner_id`) REFERENCES `partner` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partner_user`
--

LOCK TABLES `partner_user` WRITE;
/*!40000 ALTER TABLE `partner_user` DISABLE KEYS */;
INSERT INTO `partner_user` VALUES (1,1,72);
/*!40000 ALTER TABLE `partner_user` ENABLE KEYS */;
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
-- Table structure for table `resman_settings`
--

DROP TABLE IF EXISTS `resman_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resman_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `holding_id` bigint(20) NOT NULL,
  `account_id` longtext COLLATE utf8_unicode_ci NOT NULL,
  `sync_balance` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B34C78026CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_B34C78026CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resman_settings`
--

LOCK TABLES `resman_settings` WRITE;
/*!40000 ALTER TABLE `resman_settings` DISABLE KEYS */;
INSERT INTO `resman_settings` VALUES (1,5,'akEMMXputQ3AkJc4QZmt7/nqM6XgTZcoUHsUWwPrrj0=',0);
/*!40000 ALTER TABLE `resman_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_aci_collect_pay_contract_billing`
--

DROP TABLE IF EXISTS `rj_aci_collect_pay_contract_billing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_aci_collect_pay_contract_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) DEFAULT NULL,
  `division_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7E5A20572576E0FD` (`contract_id`),
  CONSTRAINT `FK_7E5A20572576E0FD` FOREIGN KEY (`contract_id`) REFERENCES `rj_contract` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_aci_collect_pay_contract_billing`
--

LOCK TABLES `rj_aci_collect_pay_contract_billing` WRITE;
/*!40000 ALTER TABLE `rj_aci_collect_pay_contract_billing` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_aci_collect_pay_contract_billing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_aci_collect_pay_group_profile`
--

DROP TABLE IF EXISTS `rj_aci_collect_pay_group_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_aci_collect_pay_group_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) DEFAULT NULL,
  `profile_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FD65ED2CFE54D947` (`group_id`),
  CONSTRAINT `FK_FD65ED2CFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_aci_collect_pay_group_profile`
--

LOCK TABLES `rj_aci_collect_pay_group_profile` WRITE;
/*!40000 ALTER TABLE `rj_aci_collect_pay_group_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_aci_collect_pay_group_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_aci_collect_pay_user_profile`
--

DROP TABLE IF EXISTS `rj_aci_collect_pay_user_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_aci_collect_pay_user_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `profile_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E474F81BA76ED395` (`user_id`),
  CONSTRAINT `FK_E474F81BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_aci_collect_pay_user_profile`
--

LOCK TABLES `rj_aci_collect_pay_user_profile` WRITE;
/*!40000 ALTER TABLE `rj_aci_collect_pay_user_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_aci_collect_pay_user_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_aci_import_profile_map`
--

DROP TABLE IF EXISTS `rj_aci_import_profile_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_aci_import_profile_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D2F20E31A76ED395` (`user_id`),
  UNIQUE KEY `UNIQ_D2F20E31FE54D947` (`group_id`),
  CONSTRAINT `FK_D2F20E31FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`),
  CONSTRAINT `FK_D2F20E31A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_aci_import_profile_map`
--

LOCK TABLES `rj_aci_import_profile_map` WRITE;
/*!40000 ALTER TABLE `rj_aci_import_profile_map` DISABLE KEYS */;
INSERT INTO `rj_aci_import_profile_map` VALUES (1,42,NULL),(2,NULL,29);
/*!40000 ALTER TABLE `rj_aci_import_profile_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_amsi_settings`
--

DROP TABLE IF EXISTS `rj_amsi_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_amsi_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `holding_id` bigint(20) NOT NULL,
  `url` longtext COLLATE utf8_unicode_ci NOT NULL,
  `user` longtext COLLATE utf8_unicode_ci NOT NULL,
  `password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `portfolio_name` longtext COLLATE utf8_unicode_ci NOT NULL,
  `sync_balance` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E2F51ECF6CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_E2F51ECF6CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_amsi_settings`
--

LOCK TABLES `rj_amsi_settings` WRITE;
/*!40000 ALTER TABLE `rj_amsi_settings` DISABLE KEYS */;
INSERT INTO `rj_amsi_settings` VALUES (1,5,'2SE+8sIcllVNUXy7dAnmpaqrw+5wgu7Q7UF27AyQffmAaJUWKVRtspM+rXHSU3WrenEuggyGnqVnT0S1pVFyIQ==','2p+/bvV45lOlkjc9fDhgp4pr9slNIxJSUyksCT+mjJ8=','2p+/bvV45lOlkjc9fDhgp4pr9slNIxJSUyksCT+mjJ8=','5P4pWrAphPA7/ogL7eOmwZgX/Dlfx0eRde70Bh0DSXY=',0);
/*!40000 ALTER TABLE `rj_amsi_settings` ENABLE KEYS */;
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
  `bank_account_type` enum('checking','savings','business checking') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:BankAccountType)',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `payment_processor` enum('heartland','aci') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentProcessor)',
  PRIMARY KEY (`id`),
  KEY `IDX_6D16C91BFE54D947` (`group_id`),
  CONSTRAINT `FK_6D16C91BFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
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
-- Table structure for table `rj_billing_account_migration`
--

DROP TABLE IF EXISTS `rj_billing_account_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_billing_account_migration` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `heartland_payment_account_id` int(11) DEFAULT NULL,
  `aci_payment_account_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_151FCE5A31F0BB2E` (`heartland_payment_account_id`),
  UNIQUE KEY `UNIQ_151FCE5AE60CC3C9` (`aci_payment_account_id`),
  CONSTRAINT `FK_151FCE5AE60CC3C9` FOREIGN KEY (`aci_payment_account_id`) REFERENCES `rj_billing_account` (`id`),
  CONSTRAINT `FK_151FCE5A31F0BB2E` FOREIGN KEY (`heartland_payment_account_id`) REFERENCES `rj_billing_account` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_billing_account_migration`
--

LOCK TABLES `rj_billing_account_migration` WRITE;
/*!40000 ALTER TABLE `rj_billing_account_migration` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_billing_account_migration` ENABLE KEYS */;
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
  `group_id` bigint(20) NOT NULL,
  `property_id` bigint(20) DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `search` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('pending','invite','approved','current','finished','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '(DC2Type:ContractStatus)',
  `payment_accepted` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '(DC2Type:PaymentAccepted)',
  `rent` decimal(10,2) DEFAULT NULL,
  `uncollected_balance` decimal(10,2) DEFAULT NULL,
  `integrated_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_to` date DEFAULT NULL,
  `report_to_experian` tinyint(1) DEFAULT '0',
  `report_to_trans_union` tinyint(1) DEFAULT '0',
  `experian_start_at` date DEFAULT NULL,
  `trans_union_start_at` date DEFAULT NULL,
  `due_date` int(11) DEFAULT NULL,
  `start_at` date DEFAULT NULL,
  `finish_at` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `disputeCode` enum('BLANK','XB','XC','XH','XR') COLLATE utf8_unicode_ci DEFAULT 'BLANK' COMMENT '(DC2Type:DisputeCode)',
  `external_lease_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  CONSTRAINT `FK_2A4AB7F0FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_contract`
--

LOCK TABLES `rj_contract` WRITE;
/*!40000 ALTER TABLE `rj_contract` DISABLE KEYS */;
INSERT INTO `rj_contract` VALUES (1,42,5,24,1,1,NULL,'pending','0',NULL,NULL,0.00,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(2,42,5,24,1,2,NULL,'approved','0',1400.00,NULL,0.00,'2015-09-21',1,1,'2014-01-01','2014-01-01',NULL,'2015-09-21','2015-12-21','2014-10-21 13:17:16','2015-09-21 13:17:16','BLANK',NULL),(3,42,5,25,1,4,NULL,'finished','0',1500.00,NULL,0.00,'2015-07-23',0,0,NULL,NULL,NULL,'2014-09-21','2015-07-23','2014-08-17 13:17:16','2015-07-23 13:17:16','BLANK','t0012020'),(4,43,5,24,1,4,NULL,'finished','0',1250.00,NULL,0.00,'2015-07-21',0,0,NULL,NULL,NULL,'2015-01-21','2015-07-21','2015-01-17 13:17:16','2015-08-21 13:17:16','BLANK',NULL),(5,43,5,24,1,4,NULL,'approved','0',1250.00,NULL,0.00,'2015-09-26',0,0,NULL,NULL,NULL,'2015-01-26','2015-11-21','2015-01-26 13:17:16','2015-08-26 13:17:16','BLANK',NULL),(6,44,5,24,1,1,NULL,'pending','0',NULL,NULL,0.00,NULL,0,0,NULL,NULL,NULL,NULL,'2015-11-20','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(7,45,5,24,1,6,NULL,'approved','0',1700.00,NULL,0.00,'2015-09-20',1,1,'2014-01-01','2014-01-01',NULL,'2014-12-16','2015-11-20','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(8,47,5,24,1,6,NULL,'approved','0',3700.00,NULL,0.00,'2015-10-11',1,1,'2014-01-01','2014-01-01',NULL,'2015-06-23','2015-09-01','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(9,42,5,24,1,7,NULL,'current','0',1750.00,NULL,0.00,'2015-10-11',0,0,NULL,NULL,NULL,'2014-11-21','2015-11-21','2014-10-21 13:17:16','2014-11-21 13:17:16','BLANK',NULL),(10,47,5,24,1,8,NULL,'current','0',1750.00,NULL,0.00,'2015-09-24',0,0,NULL,NULL,NULL,'2015-06-23','2015-11-20','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(11,48,5,24,1,9,NULL,'current','0',1750.00,NULL,0.00,'2015-10-06',0,0,NULL,NULL,NULL,'2015-06-23','2015-11-20','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK','test'),(12,49,5,24,1,10,NULL,'current','0',1750.00,NULL,0.00,'2015-10-06',0,0,NULL,NULL,NULL,'2015-06-23','2015-11-20','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(13,50,5,24,1,11,NULL,'current','0',2100.00,NULL,0.00,'2015-09-20',0,0,NULL,NULL,11,'2015-06-23','2015-11-20','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(14,51,5,24,1,12,NULL,'approved','0',2100.00,NULL,0.00,NULL,0,0,NULL,NULL,NULL,'2015-06-23','2015-11-20','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(15,52,5,24,1,1,NULL,'finished','0',2100.00,NULL,0.00,'2015-05-24',0,0,NULL,NULL,NULL,'2014-09-26','2015-05-24','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(16,53,5,24,1,2,NULL,'finished','0',1400.00,NULL,0.00,'2015-05-24',0,0,NULL,NULL,NULL,'2014-09-26','2015-05-24','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(17,53,6,29,1,2,NULL,'finished','0',1400.00,NULL,0.00,'2015-05-24',0,0,NULL,NULL,NULL,'2014-09-26','2015-05-24','2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(18,42,5,24,1,3,NULL,'current','0',987.00,NULL,0.00,'2015-08-25',1,1,'2014-01-01','2014-01-01',NULL,'2015-01-01','2018-01-01','2014-01-01 00:00:00','2014-01-01 00:00:00','BLANK',NULL),(19,44,5,25,1,2,NULL,'pending','0',66661.00,NULL,0.00,'2015-09-21',0,0,NULL,NULL,NULL,'2015-09-21','2015-12-21','2014-10-21 13:17:16','2015-09-21 13:17:16','BLANK',NULL),(20,53,5,24,1,10,NULL,'current','0',850.00,NULL,0.00,'2015-09-22',0,0,NULL,NULL,NULL,'2015-10-21',NULL,'2015-09-01 13:17:16','2015-09-01 13:17:16','BLANK',NULL),(21,46,5,25,1,1,NULL,'pending','0',NULL,NULL,0.00,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'2015-08-02 13:17:16','2015-08-02 13:17:16','BLANK',NULL),(22,50,5,24,1,11,NULL,'current','0',199.10,NULL,0.00,'2015-09-22',0,0,NULL,NULL,NULL,'2015-06-23','2015-09-11','2015-04-24 13:17:16','2015-04-24 13:17:16','BLANK',NULL),(23,57,5,25,1,3,NULL,'current','0',500.00,NULL,0.00,'2015-11-21',0,0,NULL,NULL,NULL,'2015-09-21','2016-09-21','2015-09-20 13:17:16','2015-09-20 13:17:16','BLANK',NULL);
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
  `integrated_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_to` date DEFAULT NULL,
  `reporting` tinyint(1) DEFAULT '0',
  `start_at` date DEFAULT NULL,
  `finish_at` date DEFAULT NULL,
  `action` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `logged_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6CF9EAFD232D562B` (`object_id`),
  CONSTRAINT `FK_6CF9EAFD232D562B` FOREIGN KEY (`object_id`) REFERENCES `rj_contract` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_contract_history`
--

LOCK TABLES `rj_contract_history` WRITE;
/*!40000 ALTER TABLE `rj_contract_history` DISABLE KEYS */;
INSERT INTO `rj_contract_history` VALUES (1,1,NULL,'pending',NULL,NULL,0.00,NULL,0,NULL,NULL,'create','2015-09-21 13:17:17'),(2,2,NULL,'approved',1400.00,NULL,0.00,'2015-09-21',0,'2015-09-21','2015-12-21','create','2015-09-21 13:17:17'),(3,3,NULL,'finished',1500.00,NULL,0.00,'2015-07-23',0,'2014-09-21','2015-07-23','create','2015-09-21 13:17:17'),(4,4,NULL,'finished',1250.00,NULL,0.00,'2015-07-21',0,'2015-01-21','2015-07-21','create','2015-09-21 13:17:17'),(5,5,NULL,'approved',1250.00,NULL,0.00,'2015-09-26',0,'2015-01-26','2015-11-21','create','2015-09-21 13:17:17'),(6,6,NULL,'pending',NULL,NULL,0.00,NULL,0,NULL,'2015-11-20','create','2015-09-21 13:17:17'),(7,7,NULL,'approved',1700.00,NULL,0.00,'2015-09-20',0,'2014-12-16','2015-11-20','create','2015-09-21 13:17:17'),(8,8,NULL,'approved',3700.00,NULL,0.00,'2015-10-11',0,'2015-06-23','2015-09-01','create','2015-09-21 13:17:17'),(9,9,NULL,'current',1750.00,NULL,0.00,'2015-10-11',0,'2014-11-21','2015-11-21','create','2015-09-21 13:17:17'),(10,10,NULL,'current',1750.00,NULL,0.00,'2015-09-24',0,'2015-06-23','2015-11-20','create','2015-09-21 13:17:17'),(11,11,NULL,'current',1750.00,NULL,0.00,'2015-10-06',0,'2015-06-23','2015-11-20','create','2015-09-21 13:17:17'),(12,12,NULL,'current',1750.00,NULL,0.00,'2015-10-06',0,'2015-06-23','2015-11-20','create','2015-09-21 13:17:17'),(13,13,NULL,'current',2100.00,NULL,0.00,'2015-09-20',0,'2015-06-23','2015-11-20','create','2015-09-21 13:17:17'),(14,14,NULL,'approved',2100.00,NULL,0.00,NULL,0,'2015-06-23','2015-11-20','create','2015-09-21 13:17:17'),(15,15,NULL,'finished',2100.00,NULL,0.00,'2015-05-24',0,'2014-09-26','2015-05-24','create','2015-09-21 13:17:17'),(16,16,NULL,'finished',1400.00,NULL,0.00,'2015-05-24',0,'2014-09-26','2015-05-24','create','2015-09-21 13:17:17'),(17,17,NULL,'finished',1400.00,NULL,0.00,'2015-05-24',0,'2014-09-26','2015-05-24','create','2015-09-21 13:17:17'),(18,18,NULL,'current',987.00,NULL,0.00,'2015-08-25',0,'2015-01-01','2018-01-01','create','2015-09-21 13:17:17'),(19,19,NULL,'pending',66661.00,NULL,0.00,'2015-09-21',0,'2015-09-21','2015-12-21','create','2015-09-21 13:17:17'),(20,20,NULL,'current',850.00,NULL,0.00,'2015-09-22',0,'2015-10-21',NULL,'create','2015-09-21 13:17:17'),(21,21,NULL,'pending',NULL,NULL,0.00,NULL,0,NULL,NULL,'create','2015-09-21 13:17:17'),(22,22,NULL,'current',199.10,NULL,0.00,'2015-09-22',0,'2015-06-23','2015-09-11','create','2015-09-21 13:17:17'),(23,23,NULL,'current',500.00,NULL,0.00,'2015-11-21',0,'2015-09-21','2016-09-21','create','2015-09-21 13:17:17');
/*!40000 ALTER TABLE `rj_contract_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_contract_waiting`
--

DROP TABLE IF EXISTS `rj_contract_waiting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_contract_waiting` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) NOT NULL,
  `property_id` bigint(20) NOT NULL,
  `group_id` bigint(20) NOT NULL,
  `rent` decimal(10,2) NOT NULL,
  `resident_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `integrated_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `start_at` date NOT NULL,
  `finish_at` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_accepted` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '(DC2Type:PaymentAccepted)',
  `external_lease_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_23991718F8BD700D` (`unit_id`),
  KEY `IDX_23991718549213EC` (`property_id`),
  KEY `IDX_23991718FE54D947` (`group_id`),
  CONSTRAINT `FK_23991718FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`),
  CONSTRAINT `FK_23991718549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`),
  CONSTRAINT `FK_23991718F8BD700D` FOREIGN KEY (`unit_id`) REFERENCES `rj_unit` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_contract_waiting`
--

LOCK TABLES `rj_contract_waiting` WRITE;
/*!40000 ALTER TABLE `rj_contract_waiting` DISABLE KEYS */;
INSERT INTO `rj_contract_waiting` VALUES (1,1,1,24,500.00,'t0013535',0.00,'2015-09-21','2016-07-21','2015-09-21 13:17:21','2015-09-21 13:17:21','Tom','Jonson','0',NULL);
/*!40000 ALTER TABLE `rj_contract_waiting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_deposit_account`
--

DROP TABLE IF EXISTS `rj_deposit_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_deposit_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) NOT NULL,
  `merchant_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('error','success','init','complete') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'init' COMMENT '(DC2Type:DepositAccountStatus)',
  `message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_processor` enum('heartland','aci') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentProcessor)',
  `type` enum('application_fee','security_deposit','rent') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'rent' COMMENT '(DC2Type:DepositAccountType)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `da_unique_constraint` (`type`,`group_id`,`payment_processor`),
  KEY `IDX_7F2B897FE54D947` (`group_id`),
  CONSTRAINT `FK_7F2B897FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_deposit_account`
--

LOCK TABLES `rj_deposit_account` WRITE;
/*!40000 ALTER TABLE `rj_deposit_account` DISABLE KEYS */;
INSERT INTO `rj_deposit_account` VALUES (1,24,'Monticeto_Percent','complete',NULL,NULL,'heartland','rent'),(2,25,'Monticeto_Percent','complete',NULL,NULL,'heartland','rent'),(3,26,'WestPac','complete',NULL,NULL,'heartland','rent'),(4,27,NULL,'init',NULL,NULL,'heartland','rent'),(5,32,'RentTrackCorp','complete',NULL,NULL,'heartland','rent');
/*!40000 ALTER TABLE `rj_deposit_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_group`
--

DROP TABLE IF EXISTS `rj_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_group` (
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
  `disable_credit_card` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime NOT NULL,
  `statement_descriptor` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mailing_address_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_algorithm` enum('submerchant','pay_direct') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OrderAlgorithmType)',
  `external_group_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F2AB53D577153098` (`code`),
  KEY `IDX_F2AB53D51047997E` (`cj_affiliate_id`),
  KEY `IDX_F2AB53D56CD5FBA3` (`holding_id`),
  KEY `IDX_F2AB53D5727ACA70` (`parent_id`),
  KEY `IDX_F2AB53D5249E6EA1` (`dealer_id`),
  CONSTRAINT `FK_F2AB53D5249E6EA1` FOREIGN KEY (`dealer_id`) REFERENCES `cj_user` (`id`),
  CONSTRAINT `FK_F2AB53D51047997E` FOREIGN KEY (`cj_affiliate_id`) REFERENCES `cj_affiliate` (`id`),
  CONSTRAINT `FK_F2AB53D56CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`),
  CONSTRAINT `FK_F2AB53D5727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group`
--

LOCK TABLES `rj_group` WRITE;
/*!40000 ALTER TABLE `rj_group` DISABLE KEYS */;
INSERT INTO `rj_group` VALUES (1,NULL,9,NULL,NULL,'Credit Jeeves\' Stuff',NULL,'DZC6K2OAG3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'vehicle','2015-09-21 13:17:11',0,'2015-09-21 13:17:11','CJ',NULL,'submerchant',NULL),(2,1,9,NULL,NULL,'LA Honda Dealer',700,'DVRWP2NFQ6','Congratulations on improving your credit score!\nWe want to help you drive away with your dream car.\nCall us within the next 24 hours and we’ll give you $250 off!','www.honda.com',NULL,'805-555-1212',NULL,'124 Hitchcock Way',NULL,'Santa Barbara','CA','93101','flat',NULL,NULL,'vehicle','2015-09-21 13:17:11',0,'2015-09-21 13:17:11','CJ',NULL,'submerchant',NULL),(3,2,9,NULL,NULL,'LA BMW Dealer',900,'DZC6K2PQC6',NULL,'http://www.bmw.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'lead',NULL,'2013-07-03','vehicle','2015-09-21 13:17:11',0,'2015-09-21 13:17:11','CJ',NULL,'submerchant',NULL),(4,NULL,1,NULL,NULL,'US Cars',750,'DZC6K2QG93',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,'2015-09-11','vehicle','2015-09-21 13:17:11',0,'2015-09-21 13:17:11','CJ',NULL,'submerchant',NULL),(5,NULL,9,NULL,NULL,'AutoTrader',700,'DZC6LV0KUZ',NULL,'www.autotrader.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat','Test Contract text','2015-09-11','vehicle','2015-09-06 13:17:11',0,'2015-09-11 13:17:11','CJ',NULL,'submerchant',NULL),(6,NULL,9,NULL,NULL,'AutoNation',725,'DZC6MG3JVJ',NULL,'http://www.autonation.com/',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat','Test Contract text','2015-09-11','vehicle','2015-09-06 13:17:11',0,'2015-09-11 13:17:11','CJ',NULL,'submerchant',NULL),(7,NULL,9,NULL,NULL,'BMW',705,'DZC6PK79DK',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat','Test Contract text','2015-09-11','vehicle','2015-09-06 13:17:11',0,'2015-09-11 13:17:11','CJ',NULL,'submerchant',NULL),(8,NULL,9,NULL,NULL,'HONDA',715,'DZC6PQYDR8','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:27:18',0,'2012-11-29 14:27:18','CJ',NULL,'submerchant',NULL),(9,NULL,1,NULL,NULL,'AUDI',725,'DZC6Q9F645','','','','','','','','',NULL,'','flat','','2015-09-11','vehicle','2012-11-29 14:27:48',0,'2012-11-29 14:27:48','CJ',NULL,'submerchant',NULL),(10,NULL,9,NULL,NULL,'MERSEDES',740,'DZC6QHG82F','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:28:01',0,'2012-11-29 14:28:01','CJ',NULL,'submerchant',NULL),(11,NULL,1,NULL,NULL,'RENAULT',500,'DZC6T6GYPY','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:30:36',0,'2012-11-29 14:30:36','CJ',NULL,'submerchant',NULL),(12,NULL,9,NULL,NULL,'FORD',350,'DZC6TNZYSU','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:31:04',0,'2012-11-29 14:31:04','CJ',NULL,'submerchant',NULL),(13,NULL,9,NULL,NULL,'SAAB',600,'DZC6TZE0QH','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:31:22',0,'2012-11-29 14:31:22','CJ',NULL,'submerchant',NULL),(14,NULL,9,NULL,NULL,'LOTUS',700,'DZC6U6X53Y','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:31:34',0,'2012-11-29 14:31:34','CJ',NULL,'submerchant',NULL),(15,NULL,9,NULL,NULL,'MINI',710,'DZC6UD0P5V','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:31:44',0,'2012-11-29 14:31:44','CJ',NULL,'submerchant',NULL),(16,NULL,9,NULL,NULL,'FERRARI',725,'DZC6XE2Z91','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:34:39',0,'2012-11-29 14:34:39','CJ',NULL,'submerchant',NULL),(17,NULL,9,NULL,NULL,'PONTIAC',700,'DZC6XPDZNT','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:34:57',0,'2012-11-29 14:34:57','CJ',NULL,'submerchant',NULL),(18,NULL,9,NULL,NULL,'MASERATI',725,'DZC6YAXLEL','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:35:31',0,'2012-11-29 14:35:31','CJ',NULL,'submerchant',NULL),(19,NULL,9,NULL,NULL,'DODGE',725,'DZC6YIKTKR','','','','','','','','',NULL,'','flat',NULL,'2015-09-21','vehicle','2012-11-29 14:35:44',0,'2012-11-29 14:35:44','CJ',NULL,'submerchant',NULL),(20,NULL,9,NULL,NULL,'Vehicle group',750,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'vehicle','2015-07-23 13:17:11',0,'2015-09-13 13:17:11','CJ',NULL,'submerchant',NULL),(21,NULL,9,NULL,NULL,'Estate group',850,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'estate','2015-07-23 13:17:11',0,'2015-09-13 13:17:11','CJ',NULL,'submerchant',NULL),(22,NULL,9,NULL,NULL,'Generic group',850,'GENERIC',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'generic','2015-07-23 13:17:11',0,'2015-09-13 13:17:11','CJ',NULL,'submerchant',NULL),(23,NULL,4,NULL,NULL,'700Credit',900,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'generic','2015-07-23 13:17:11',0,'2015-09-13 13:17:11','CJ',NULL,'submerchant',NULL),(24,NULL,5,NULL,NULL,'Test Rent Group',NULL,'DXC6KXOAGX',NULL,NULL,NULL,NULL,NULL,'Nostrand Ave','A1','New York','NY','11216','flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2015-09-21 13:17:15','Test Rent','Vasiliy Pups','submerchant',NULL),(25,NULL,5,NULL,NULL,'Sea side Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'5721 12th Ave, Brooklyn','#11-a','New York','NY','11219','flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2015-09-21 13:17:15','Sea side Rent','Vasiliy Pupsovna','submerchant',NULL),(26,NULL,5,NULL,NULL,'Campus Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2015-09-21 13:17:15','Campus Rent','test71_group','submerchant',NULL),(27,NULL,5,NULL,NULL,'Western Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2012-11-29 14:35:44','Western Rent','test73_group','submerchant',NULL),(28,NULL,5,NULL,NULL,'Kharkov Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2012-11-29 14:35:44','Kharkov Rent','test723','submerchant',NULL),(29,NULL,6,NULL,NULL,'Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2015-09-21 13:17:15','Rent Group','tes13_t_7_','submerchant',NULL),(30,NULL,7,NULL,NULL,'Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2012-11-29 14:35:44','Rent Group','tes231t7_','submerchant',NULL),(31,NULL,8,NULL,NULL,'Rent Group',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'rent','2012-11-29 14:35:44',0,'2012-11-29 14:35:44','Rent Group','tes231t71_','submerchant',NULL),(32,NULL,9,NULL,NULL,'RentTrackCorp',NULL,'RentTrackCorp',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'flat',NULL,NULL,'generic','2015-09-21 13:17:15',0,'2015-09-21 13:17:15','RentTrackCorp','tes231t71_11','submerchant',NULL);
/*!40000 ALTER TABLE `rj_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_group_account_mapping`
--

DROP TABLE IF EXISTS `rj_group_account_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_group_account_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) DEFAULT NULL,
  `holding_id` bigint(20) NOT NULL,
  `account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F7E2E9ECFE54D947` (`group_id`),
  UNIQUE KEY `acc_number_constraint` (`holding_id`,`account_number`),
  KEY `IDX_F7E2E9EC6CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_F7E2E9EC6CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`),
  CONSTRAINT `FK_F7E2E9ECFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group_account_mapping`
--

LOCK TABLES `rj_group_account_mapping` WRITE;
/*!40000 ALTER TABLE `rj_group_account_mapping` DISABLE KEYS */;
INSERT INTO `rj_group_account_mapping` VALUES (1,24,5,'15235678'),(2,26,5,'12345786');
/*!40000 ALTER TABLE `rj_group_account_mapping` ENABLE KEYS */;
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
  CONSTRAINT `FK_DF1D7A7CFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group_phone`
--

LOCK TABLES `rj_group_phone` WRITE;
/*!40000 ALTER TABLE `rj_group_phone` DISABLE KEYS */;
INSERT INTO `rj_group_phone` VALUES (1,24,'111-222-4444',1,'2015-09-21 13:17:19','2015-09-21 13:17:19'),(2,24,'555-222-4444',0,'2015-09-21 13:17:19','2015-09-21 13:17:19'),(3,24,'057-710-3555',0,'2015-09-21 13:17:19','2015-09-21 13:17:19');
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
  CONSTRAINT `FK_3DFD966BFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group_property`
--

LOCK TABLES `rj_group_property` WRITE;
/*!40000 ALTER TABLE `rj_group_property` DISABLE KEYS */;
INSERT INTO `rj_group_property` VALUES (24,1),(24,2),(24,3),(24,4),(24,5),(24,6),(24,7),(24,8),(24,9),(24,10),(24,11),(24,12),(24,13),(24,14),(24,15),(24,16),(24,17),(24,18),(24,20),(25,1),(25,2),(25,16),(25,17),(25,18),(26,19),(29,1);
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
  `payment_processor` enum('heartland','aci') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'heartland' COMMENT '(DC2Type:PaymentProcessor)',
  `pid_verification` tinyint(1) NOT NULL,
  `is_integrated` tinyint(1) NOT NULL DEFAULT '0',
  `is_reporting_off` tinyint(1) NOT NULL DEFAULT '0',
  `pay_balance_only` tinyint(1) NOT NULL DEFAULT '0',
  `due_date` int(11) NOT NULL DEFAULT '1',
  `open_date` int(11) NOT NULL DEFAULT '1',
  `close_date` int(11) NOT NULL DEFAULT '31',
  `feeCC` decimal(10,2) DEFAULT NULL,
  `feeACH` decimal(10,2) DEFAULT NULL,
  `is_passed_ach` tinyint(1) NOT NULL,
  `show_properties_tab` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `auto_approve_contracts` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_534A2A70FE54D947` (`group_id`),
  CONSTRAINT `FK_534A2A70FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_group_settings`
--

LOCK TABLES `rj_group_settings` WRITE;
/*!40000 ALTER TABLE `rj_group_settings` DISABLE KEYS */;
INSERT INTO `rj_group_settings` VALUES (1,24,'heartland',0,1,0,0,1,1,31,NULL,NULL,0,1,'2015-09-21 13:17:15','2015-09-21 13:17:15',0),(2,25,'heartland',0,0,0,0,1,1,31,NULL,NULL,0,1,'2015-09-21 13:17:15','2015-09-21 13:17:15',0),(3,26,'heartland',0,0,0,0,1,1,31,NULL,NULL,0,1,'2015-09-21 13:17:15','2015-09-21 13:17:15',0),(4,27,'heartland',0,0,0,0,1,1,31,NULL,NULL,0,1,'2015-09-21 13:17:15','2015-09-21 13:17:15',0),(5,28,'heartland',0,0,0,0,1,1,31,0.00,0.00,1,1,'2015-09-21 13:17:15','2015-09-21 13:17:15',0);
/*!40000 ALTER TABLE `rj_group_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_import_error`
--

DROP TABLE IF EXISTS `rj_import_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_import_error` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `import_summary_id` bigint(20) DEFAULT NULL,
  `exception_uid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `md5_row_content` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `row_content` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `messages` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_exception` (`import_summary_id`,`md5_row_content`),
  KEY `IDX_C2440AFCE848EA4F` (`import_summary_id`),
  CONSTRAINT `FK_C2440AFCE848EA4F` FOREIGN KEY (`import_summary_id`) REFERENCES `rj_import_summary` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_import_error`
--

LOCK TABLES `rj_import_error` WRITE;
/*!40000 ALTER TABLE `rj_import_error` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_import_error` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_import_mapping_by_property`
--

DROP TABLE IF EXISTS `rj_import_mapping_by_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_import_mapping_by_property` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) NOT NULL,
  `mapping_data` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B2B8A811549213EC` (`property_id`),
  CONSTRAINT `FK_B2B8A811549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_import_mapping_by_property`
--

LOCK TABLES `rj_import_mapping_by_property` WRITE;
/*!40000 ALTER TABLE `rj_import_mapping_by_property` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_import_mapping_by_property` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_import_summary`
--

DROP TABLE IF EXISTS `rj_import_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_import_summary` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) DEFAULT NULL,
  `public_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('single_property','multi_properties','multi_groups') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ImportType)',
  `count_total` int(11) NOT NULL,
  `count_new` int(11) NOT NULL,
  `count_matched` int(11) NOT NULL,
  `count_invited` int(11) NOT NULL,
  `count_skipped` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_62070945FE54D947` (`group_id`),
  CONSTRAINT `FK_62070945FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_import_summary`
--

LOCK TABLES `rj_import_summary` WRITE;
/*!40000 ALTER TABLE `rj_import_summary` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_import_summary` ENABLE KEYS */;
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
  `unitName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_single` tinyint(1) DEFAULT '0',
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
-- Table structure for table `rj_mri_settings`
--

DROP TABLE IF EXISTS `rj_mri_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_mri_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `holding_id` bigint(20) NOT NULL,
  `url` longtext COLLATE utf8_unicode_ci NOT NULL,
  `client_id` longtext COLLATE utf8_unicode_ci NOT NULL,
  `user` longtext COLLATE utf8_unicode_ci NOT NULL,
  `password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `database_name` longtext COLLATE utf8_unicode_ci NOT NULL,
  `partner_key` longtext COLLATE utf8_unicode_ci NOT NULL,
  `hash` longtext COLLATE utf8_unicode_ci NOT NULL,
  `site_id` longtext COLLATE utf8_unicode_ci NOT NULL,
  `charge_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_19D487496CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_19D487496CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_mri_settings`
--

LOCK TABLES `rj_mri_settings` WRITE;
/*!40000 ALTER TABLE `rj_mri_settings` DISABLE KEYS */;
INSERT INTO `rj_mri_settings` VALUES (1,5,'p43169nVTQ4TcG9I67gQpWqqZeceIE9vScJmw7bQGtdpNyYXuiSCss5W9Q25KGlRwXb47ziJqg0FPS/xuUiI2w==','ZCQxiluPbSAVv5hUfVuAKlZwm11sMeX3Alb3/XJg8Qo=','ml7WyXfPiXpCgP2Ekt86mYBLjhA+mHfQvvKBPWacAo8=','X7y1xgHyPrvpCuqrW+/gC4x7QFebdPH0zhV0tHsfbPg=','SCJk7kW+VbYrfFzpgs2P1+UocYeU9JrabSHbu+xonL4=','IRPMmZeqdWndZ1PFilU0Pvy3G6utGVgatBv4tuFEubyjB6YM0C2sPPfD5hIEcOyz0zXZxftVx7Ly+NnLgLyYWoMxx5B0jLo+Evb/uQ124W2Z1PUaxfFkNRKo3hlmr63E','IQTe9QNWp3DvadZXSTDm8HjSVPjntotvn/ZI8yFmmTYTW6XFPM2PbgzLALlGiRR677aLJ8Ej+OFZt7ZqFsAcRbG47i9fo4mo6RWs6R6zw5F3IDmx8VOWu1/yK0PctWpv','m2b2RPPOk/MbRIQR9QiaVpC4CW/kR6Q1sTNn7EkZ+VE=','PPu2tBnCDvmd/P0i0spIXSeE0OCsLprAzzDaptrvbZo=');
/*!40000 ALTER TABLE `rj_mri_settings` ENABLE KEYS */;
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
  `deposit_account_id` int(11) DEFAULT NULL,
  `type` enum('recurring','one_time','immediate') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentType)',
  `status` enum('active','close') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentStatus)',
  `amount` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `paid_for` date DEFAULT NULL,
  `due_date` int(11) NOT NULL,
  `start_month` int(11) NOT NULL,
  `start_year` int(11) NOT NULL,
  `end_month` int(11) DEFAULT NULL,
  `end_year` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `close_details` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_A4398CF02576E0FD` (`contract_id`),
  KEY `IDX_A4398CF0AE9DDE6F` (`payment_account_id`),
  KEY `IDX_A4398CF06E60BC73` (`deposit_account_id`),
  CONSTRAINT `FK_A4398CF06E60BC73` FOREIGN KEY (`deposit_account_id`) REFERENCES `rj_deposit_account` (`id`),
  CONSTRAINT `FK_A4398CF02576E0FD` FOREIGN KEY (`contract_id`) REFERENCES `rj_contract` (`id`),
  CONSTRAINT `FK_A4398CF0AE9DDE6F` FOREIGN KEY (`payment_account_id`) REFERENCES `rj_payment_account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment`
--

LOCK TABLES `rj_payment` WRITE;
/*!40000 ALTER TABLE `rj_payment` DISABLE KEYS */;
INSERT INTO `rj_payment` VALUES (1,2,1,1,'recurring','active',1400.00,1400.00,'2015-09-21',11,6,2015,6,2016,'2015-09-16 13:17:18','2015-09-16 13:17:18','N;'),(2,3,2,2,'recurring','close',1500.00,1500.00,'2015-09-21',21,9,2015,6,2016,'2015-09-16 13:17:18','2015-09-16 13:17:18','N;'),(3,5,4,1,'recurring','active',1700.00,1700.00,'2015-09-21',21,9,2015,6,2016,'2015-09-16 13:17:18','2015-09-16 13:17:18','N;'),(4,7,5,1,'recurring','active',1700.00,1700.00,'2016-07-21',21,7,2016,6,2016,'2016-07-21 13:17:18','2015-09-16 13:17:18','N;'),(5,13,6,1,'recurring','active',2100.00,2100.00,'2016-10-27',27,10,2016,6,2016,'2015-08-02 13:17:18','2015-08-02 13:17:18','N;'),(6,14,7,1,'recurring','active',2100.00,2100.00,'2015-09-20',15,8,2015,NULL,NULL,'2015-08-15 13:17:18','2015-09-15 13:17:18','N;');
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
  `address_id` bigint(20) DEFAULT NULL,
  `payment_processor` enum('heartland','aci') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'heartland' COMMENT '(DC2Type:PaymentProcessor)',
  `type` enum('bank','card') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:PaymentAccountType)',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cc_expiration` date DEFAULT NULL,
  `bank_account_type` enum('checking','savings','business checking') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:BankAccountType)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1F714C26A76ED395` (`user_id`),
  KEY `IDX_1F714C26F5B7AF75` (`address_id`),
  CONSTRAINT `FK_1F714C26F5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `cj_address` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_1F714C26A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment_account`
--

LOCK TABLES `rj_payment_account` WRITE;
/*!40000 ALTER TABLE `rj_payment_account` DISABLE KEYS */;
INSERT INTO `rj_payment_account` VALUES (1,42,25,'heartland','card','Card','2A83E7EB-0558-48EC-A342-A71D0092BD3F','2015-10-31',NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL),(2,42,NULL,'heartland','bank','Bank','D98BB91F-952B-452C-A929-9FBEF5E1F0F7',NULL,NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL),(3,43,NULL,'heartland','bank','Bank','D98BB91F-952B-452C-A929-9FBEF5E1F0F7',NULL,NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL),(4,43,26,'heartland','card','Card','2A83E7EB-0558-48EC-A342-A71D0092BD3F','2016-01-31',NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL),(5,45,28,'heartland','card','Card','2A83E7EB-0558-48EC-A342-A71D0092BD3F','2016-02-29',NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL),(6,50,NULL,'heartland','bank','Bank account','D98BB91F-952B-452C-A929-9FBEF5E1F0F7',NULL,NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL),(7,51,NULL,'heartland','bank','Bank account','D98BB91F-952B-452C-A929-9FBEF5E1F0F7',NULL,NULL,'2014-02-28 00:00:00','2014-02-28 00:00:00',NULL),(8,50,53,'heartland','card','Card','C09BBB4E-4C08-4295-BF05-EAAF75961D68','2015-10-31',NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL),(9,42,25,'heartland','card','RT Card','568C0904-9174-46DE-BEC4-9B76599B28C5','2015-12-31',NULL,'2015-09-16 13:17:17','2015-09-16 13:17:17',NULL);
/*!40000 ALTER TABLE `rj_payment_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_payment_account_deposit_account`
--

DROP TABLE IF EXISTS `rj_payment_account_deposit_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_payment_account_deposit_account` (
  `payment_account_id` bigint(20) NOT NULL,
  `deposit_account_id` int(11) NOT NULL,
  PRIMARY KEY (`payment_account_id`,`deposit_account_id`),
  KEY `IDX_2E90AACFAE9DDE6F` (`payment_account_id`),
  KEY `IDX_2E90AACF6E60BC73` (`deposit_account_id`),
  CONSTRAINT `FK_2E90AACF6E60BC73` FOREIGN KEY (`deposit_account_id`) REFERENCES `rj_deposit_account` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2E90AACFAE9DDE6F` FOREIGN KEY (`payment_account_id`) REFERENCES `rj_payment_account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment_account_deposit_account`
--

LOCK TABLES `rj_payment_account_deposit_account` WRITE;
/*!40000 ALTER TABLE `rj_payment_account_deposit_account` DISABLE KEYS */;
INSERT INTO `rj_payment_account_deposit_account` VALUES (1,1),(3,1),(4,1),(5,1),(6,1),(7,1),(2,2),(8,3),(9,5);
/*!40000 ALTER TABLE `rj_payment_account_deposit_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_payment_account_migration`
--

DROP TABLE IF EXISTS `rj_payment_account_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_payment_account_migration` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `heartland_payment_account_id` bigint(20) DEFAULT NULL,
  `aci_payment_account_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_509EA7231F0BB2E` (`heartland_payment_account_id`),
  UNIQUE KEY `UNIQ_509EA72E60CC3C9` (`aci_payment_account_id`),
  CONSTRAINT `FK_509EA72E60CC3C9` FOREIGN KEY (`aci_payment_account_id`) REFERENCES `rj_payment_account` (`id`),
  CONSTRAINT `FK_509EA7231F0BB2E` FOREIGN KEY (`heartland_payment_account_id`) REFERENCES `rj_payment_account` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment_account_migration`
--

LOCK TABLES `rj_payment_account_migration` WRITE;
/*!40000 ALTER TABLE `rj_payment_account_migration` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_payment_account_migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_payment_batch_mapping`
--

DROP TABLE IF EXISTS `rj_payment_batch_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_payment_batch_mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `payment_batch_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `accounting_batch_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('opened','closed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'opened' COMMENT '(DC2Type:PaymentBatchStatus)',
  `accounting_package_type` enum('none','yardi voyager','resman','mri','amsi') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:ApiIntegrationType)',
  `external_property_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `opened_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment_batch_mapping`
--

LOCK TABLES `rj_payment_batch_mapping` WRITE;
/*!40000 ALTER TABLE `rj_payment_batch_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_payment_batch_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_payment_token`
--

DROP TABLE IF EXISTS `rj_payment_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_payment_token` (
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `details` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `after_url` longtext COLLATE utf8_unicode_ci,
  `target_url` longtext COLLATE utf8_unicode_ci NOT NULL,
  `payment_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_payment_token`
--

LOCK TABLES `rj_payment_token` WRITE;
/*!40000 ALTER TABLE `rj_payment_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `rj_payment_token` ENABLE KEYS */;
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
  CONSTRAINT `FK_FF3CD81AFE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`),
  CONSTRAINT `FK_FF3CD81A3414710B` FOREIGN KEY (`agent_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_permission`
--

LOCK TABLES `rj_permission` WRITE;
/*!40000 ALTER TABLE `rj_permission` DISABLE KEYS */;
INSERT INTO `rj_permission` VALUES (65,24),(66,29),(70,29);
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
  `is_single` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `is_multiple_buildings` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_property`
--

LOCK TABLES `rj_property` WRITE;
/*!40000 ALTER TABLE `rj_property` DISABLE KEYS */;
INSERT INTO `rj_property` VALUES (1,'US','NY','New York','Manhattan','Broadway','770','10003',NULL,40.7308364,-73.991567,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(2,'US','CA','Santa Barbara',NULL,'Andante Rd','960','93105',NULL,34.44943,-119.709369,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(3,'US','CA','Mission Canyon',NULL,'Andante Rd','750','93105',NULL,34.44987,-119.7096921,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(4,'US','NY','New York','Manhattan','Broadway','560','10012',NULL,40.723851,-73.997487,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(5,'US','NY','Jamaica','Queens','Broadway','1','11414',NULL,40.6584069,-73.830445,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(6,'US','MA','Boston',NULL,'Washington St','10','1114',NULL,42.2574449,-71.1616868,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(7,'US','CA','Palos Verdes Estates',NULL,'Vía Fernandez',NULL,'90274',NULL,33.7880762,-118.3960347,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(8,'US','WA','Seattle',NULL,'18th Ave','50','98122',NULL,47.6016982,-122.3089461,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(9,'US','MO','Kansas City',NULL,'W 48th St','121','64112',NULL,39.038827,-94.588826,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(10,'US','TX','Houston',NULL,'Crosstimbers St','1201','77022',NULL,29.8287445,-95.3856651,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(11,'US','Mt','Billings',NULL,'Overland Ave','2026','59102',NULL,45.753246,-108.565361,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(12,'US','AZ','Scottsdale',NULL,'N Palo Cristi Rd','5532','85253',NULL,33.518351,-112.0041753,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(13,'CU','Havana','Havana',NULL,'10 de Octubre',NULL,NULL,NULL,23.1094238,-82.3658518,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(14,'US','IL','Chicago',NULL,'W Madison St','733','60661',NULL,41.8810911,-87.6468986,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(15,'US','AR','Little Rock',NULL,'S Broadway St','617','72201',NULL,34.7434652,-92.2759828,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(16,'US','NY','New York','Manhattan','Broadway','776','10003',NULL,40.7312396,-73.9918488,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(17,'US','NY','New York','Manhattan','Broadway','745','10003',NULL,40.7302448,-73.9927101,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(18,'US','NY','New York','Manhattan','Broadway','785','10003',NULL,40.7316721,-73.9917422,NULL,'2015-07-23 13:17:15','2015-09-13 13:17:15',0),(19,'US','NY','New York','Manhattan','Lexington Avenue','116','10016',NULL,40.7426129,-73.9828048,1,'2015-09-20 13:17:15','2015-09-20 13:17:15',0),(20,'US','CA','Los Angeles',NULL,'West 36th Place','1156','90007',NULL,34.021764,-118.293358,NULL,'2015-09-21 13:17:15','2015-09-21 13:17:15',0);
/*!40000 ALTER TABLE `rj_property` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_property_mapping`
--

DROP TABLE IF EXISTS `rj_property_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_property_mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) NOT NULL,
  `holding_id` bigint(20) NOT NULL,
  `external_property_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_index_constraint` (`property_id`,`holding_id`),
  KEY `IDX_5339818C549213EC` (`property_id`),
  KEY `IDX_5339818C6CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_5339818C6CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`),
  CONSTRAINT `FK_5339818C549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_property_mapping`
--

LOCK TABLES `rj_property_mapping` WRITE;
/*!40000 ALTER TABLE `rj_property_mapping` DISABLE KEYS */;
INSERT INTO `rj_property_mapping` VALUES (1,1,5,'rnttrk01'),(2,2,5,'rnttrk02');
/*!40000 ALTER TABLE `rj_property_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_resident_mapping`
--

DROP TABLE IF EXISTS `rj_resident_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_resident_mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `holding_id` bigint(20) NOT NULL,
  `resident_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_index_constraint` (`tenant_id`,`holding_id`,`resident_id`),
  KEY `IDX_A9845E989033212A` (`tenant_id`),
  KEY `IDX_A9845E986CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_A9845E986CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`),
  CONSTRAINT `FK_A9845E989033212A` FOREIGN KEY (`tenant_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_resident_mapping`
--

LOCK TABLES `rj_resident_mapping` WRITE;
/*!40000 ALTER TABLE `rj_resident_mapping` DISABLE KEYS */;
INSERT INTO `rj_resident_mapping` VALUES (1,42,5,'t0013534'),(3,43,5,'t0011981'),(2,53,5,'t0011984');
/*!40000 ALTER TABLE `rj_resident_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_transaction`
--

DROP TABLE IF EXISTS `rj_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) DEFAULT NULL,
  `batch_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `is_successful` tinyint(1) NOT NULL,
  `status` enum('complete','reversed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'complete' COMMENT '(DC2Type:TransactionStatus)',
  `messages` longtext COLLATE utf8_unicode_ci,
  `merchant_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `batch_date` date DEFAULT NULL,
  `deposit_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B949C3178D9F6D38` (`order_id`),
  CONSTRAINT `FK_B949C3178D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_transaction`
--

LOCK TABLES `rj_transaction` WRITE;
/*!40000 ALTER TABLE `rj_transaction` DISABLE KEYS */;
INSERT INTO `rj_transaction` VALUES (1,2,'125478',123123,1,'complete',NULL,NULL,1500.00,'2015-08-03','2015-08-03','2015-08-02 13:17:18'),(2,3,'325698',456456,1,'complete',NULL,NULL,1500.00,'2015-08-11','2015-08-11','2015-08-12 13:17:18'),(3,4,'111555',456123,1,'complete',NULL,NULL,1500.00,'2015-08-31','2015-08-31','2015-08-22 13:17:18'),(4,5,NULL,456555,0,'complete','Heartland Error',NULL,3000.00,NULL,NULL,'2015-08-22 13:17:18'),(5,6,'147222',147741,1,'complete',NULL,NULL,1500.00,'2015-08-31',NULL,'2015-08-22 13:17:18'),(6,6,NULL,147742,1,'reversed',NULL,NULL,-1500.00,NULL,NULL,'2015-08-22 13:17:18'),(7,10,'111555',789789,1,'complete',NULL,NULL,1500.00,'2015-08-31','2015-08-31','2015-09-01 13:17:18'),(8,11,'325698',147147,1,'complete',NULL,NULL,1500.00,'2015-09-12','2015-09-12','2015-09-11 13:17:18'),(9,13,'125477',258258,1,'complete',NULL,NULL,3700.00,NULL,NULL,'2015-09-19 13:17:18'),(10,14,'325696',369369,1,'complete',NULL,NULL,1500.00,'2014-09-17','2014-09-27','2014-09-16 13:17:18'),(11,15,'325114',159159,1,'complete',NULL,NULL,1500.00,'2014-10-17','2014-10-27','2014-10-17 13:17:18'),(12,16,'325698',777888,1,'complete',NULL,NULL,1500.00,'2014-10-17','2014-10-27','2014-10-17 13:17:18'),(13,17,'325698',222333,1,'complete',NULL,NULL,1500.00,'2014-11-16','2014-11-20','2014-11-16 13:17:18'),(14,18,'325691',555666,1,'complete',NULL,NULL,1500.00,'2014-12-16','2014-12-26','2014-12-16 13:17:18'),(15,19,'325691',777555,1,'complete',NULL,NULL,1500.00,'2015-01-15','2015-01-25','2015-01-15 13:17:18'),(16,20,'325692',555999,1,'complete',NULL,NULL,1500.00,'2015-02-14','2015-02-24','2015-02-14 13:17:18'),(17,21,'325693',112233,1,'complete',NULL,NULL,1500.00,'2015-03-16','2015-03-26','2015-03-16 13:17:18'),(18,22,'325693',334455,1,'complete',NULL,NULL,1500.00,'2015-04-15','2015-04-25','2015-04-15 13:17:18'),(19,23,'325694',556667,1,'complete',NULL,NULL,1500.00,'2015-05-15','2015-05-25','2015-05-15 13:17:18'),(20,24,'325696',778899,1,'complete',NULL,NULL,1500.00,'2015-06-14','2015-06-24','2015-06-14 13:17:18'),(21,25,'555000',446632,1,'complete',NULL,NULL,1250.00,'2015-01-21','2015-01-21','2015-01-21 13:17:18'),(22,26,'555000',125368,1,'complete',NULL,NULL,1250.00,'2015-02-21','2015-02-21','2015-02-21 13:17:18'),(23,27,'555001',1234571,1,'complete',NULL,NULL,1250.00,'2015-03-21','2015-03-21','2015-03-21 13:17:18'),(24,28,'555001',2147483647,1,'complete',NULL,NULL,1250.00,'2015-04-21','2015-04-21','2015-04-21 13:17:18'),(25,29,'555002',5654464,1,'complete',NULL,NULL,1250.00,'2015-05-21','2015-05-21','2015-05-21 13:17:18'),(26,30,'555002',5465431,1,'complete',NULL,NULL,1250.00,'2015-06-21','2015-06-21','2015-06-21 13:17:18'),(27,31,'555003',4554564,1,'complete',NULL,NULL,1250.00,'2015-07-21','2015-07-21','2015-07-21 13:17:18'),(28,7,'2344665',55123260,1,'complete',NULL,NULL,700.00,'2015-08-22','2015-08-24','2015-08-22 13:17:18'),(29,7,NULL,65123261,1,'reversed','Payment was refunded',NULL,-700.00,NULL,'2015-08-25','2015-08-23 13:17:18'),(30,8,'2344665',561232653,1,'complete',NULL,NULL,750.00,'2015-08-22','2015-08-24','2015-08-22 13:17:18'),(31,8,NULL,661232602,1,'reversed',NULL,NULL,-750.00,NULL,'2015-08-25','2015-08-23 13:17:18'),(32,9,'2344665',571232603,1,'complete',NULL,NULL,800.00,NULL,NULL,'2015-08-22 13:17:18'),(33,9,NULL,671232654,1,'reversed',NULL,NULL,-800.00,NULL,NULL,'2015-08-23 13:17:18');
/*!40000 ALTER TABLE `rj_transaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_transaction_outbound`
--

DROP TABLE IF EXISTS `rj_transaction_outbound`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_transaction_outbound` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `type` enum('deposit','reversal') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OutboundTransactionType)',
  `status` enum('success','cancelled','error') COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:OutboundTransactionStatus)',
  `message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `deposit_date` datetime DEFAULT NULL,
  `batch_close_date` datetime DEFAULT NULL,
  `reversal_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DF380F958D9F6D38` (`order_id`),
  CONSTRAINT `FK_DF380F958D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `cj_order` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_transaction_outbound`
--

LOCK TABLES `rj_transaction_outbound` WRITE;
/*!40000 ALTER TABLE `rj_transaction_outbound` DISABLE KEYS */;
INSERT INTO `rj_transaction_outbound` VALUES (1,2,1,NULL,'deposit','success',NULL,100.00,NULL,NULL,NULL,'2015-09-21 13:17:22','2015-09-21 13:17:22'),(2,2,2,NULL,'deposit','success',NULL,100.00,'2015-09-21 13:17:22',NULL,NULL,'2015-09-21 13:17:22','2015-09-21 13:17:22');
/*!40000 ALTER TABLE `rj_transaction_outbound` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_unit`
--

DROP TABLE IF EXISTS `rj_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_unit` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) NOT NULL,
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
  CONSTRAINT `FK_848B915FE54D947` FOREIGN KEY (`group_id`) REFERENCES `rj_group` (`id`),
  CONSTRAINT `FK_848B915549213EC` FOREIGN KEY (`property_id`) REFERENCES `rj_property` (`id`),
  CONSTRAINT `FK_848B9156CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_unit`
--

LOCK TABLES `rj_unit` WRITE;
/*!40000 ALTER TABLE `rj_unit` DISABLE KEYS */;
INSERT INTO `rj_unit` VALUES (1,1,5,24,'1-a',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(2,1,5,24,'1-b',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(3,1,5,24,'1-c',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(4,1,5,24,'1-d',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(5,1,5,24,'1-e',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(6,1,5,24,'1-f',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(7,1,5,24,'2-a',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(8,1,5,24,'2-b',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(9,1,5,24,'2-c',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(10,1,5,24,'108',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(11,1,5,24,'2-e',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(12,1,5,24,'2-f',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(13,2,5,24,'5-a',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(14,2,5,24,'5-b',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(15,2,5,24,'5-c',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(16,2,5,24,'5-d',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(17,2,5,24,'5-e',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(18,2,5,24,'5-f',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(19,2,5,24,'7-a',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(20,2,5,24,'25-b',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(21,2,5,24,'45-c',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(22,2,5,24,'4-d',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(23,2,5,24,'11-e',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(24,2,5,24,'27-f',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(25,18,5,24,'1',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(26,18,5,25,'HH-1',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(27,19,5,26,'SINGLE_PROPERTY',NULL,NULL,'2015-09-20 13:17:16','2015-09-20 13:17:16',NULL),(28,1,5,25,'1-a',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(29,1,5,25,'2-e',NULL,NULL,'2015-07-23 13:17:16','2015-09-13 13:17:16',NULL),(30,1,6,29,'2-U',NULL,NULL,'2015-04-14 13:17:16','2015-04-14 13:17:16',NULL),(31,20,5,24,'101',NULL,NULL,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(32,20,5,24,'201',NULL,NULL,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL),(33,20,5,24,'301',NULL,NULL,'2015-09-21 13:17:16','2015-09-21 13:17:16',NULL);
/*!40000 ALTER TABLE `rj_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rj_unit_mapping`
--

DROP TABLE IF EXISTS `rj_unit_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rj_unit_mapping` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) NOT NULL,
  `external_unit_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6F633B0BF8BD700D` (`unit_id`),
  CONSTRAINT `FK_6F633B0BF8BD700D` FOREIGN KEY (`unit_id`) REFERENCES `rj_unit` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_unit_mapping`
--

LOCK TABLES `rj_unit_mapping` WRITE;
/*!40000 ALTER TABLE `rj_unit_mapping` DISABLE KEYS */;
INSERT INTO `rj_unit_mapping` VALUES (1,7,'AAABBB-7');
/*!40000 ALTER TABLE `rj_unit_mapping` ENABLE KEYS */;
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
  `credit_track_payment_account_id` bigint(20) DEFAULT NULL,
  `is_base_order_report` tinyint(1) NOT NULL,
  `credit_track_enabled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EA6F98F6A76ED395` (`user_id`),
  UNIQUE KEY `UNIQ_EA6F98F69305140F` (`credit_track_payment_account_id`),
  CONSTRAINT `FK_EA6F98F69305140F` FOREIGN KEY (`credit_track_payment_account_id`) REFERENCES `rj_payment_account` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EA6F98F6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `cj_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rj_user_settings`
--

LOCK TABLES `rj_user_settings` WRITE;
/*!40000 ALTER TABLE `rj_user_settings` DISABLE KEYS */;
INSERT INTO `rj_user_settings` VALUES (1,65,NULL,1,NULL),(2,42,9,0,'2015-08-21 13:17:19'),(3,51,7,0,'2015-09-21 13:17:19');
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

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `session_id` varchar(255) NOT NULL,
  `session_value` text NOT NULL,
  `session_time` int(11) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yardi_settings`
--

DROP TABLE IF EXISTS `yardi_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yardi_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `holding_id` bigint(20) NOT NULL,
  `url` longtext COLLATE utf8_unicode_ci NOT NULL,
  `username` longtext COLLATE utf8_unicode_ci NOT NULL,
  `password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `database_server` longtext COLLATE utf8_unicode_ci NOT NULL,
  `database_name` longtext COLLATE utf8_unicode_ci NOT NULL,
  `platform` longtext COLLATE utf8_unicode_ci NOT NULL,
  `payment_type_ach` enum('cash','check') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'check' COMMENT '(DC2Type:PaymentTypeACH)',
  `payment_type_cc` enum('cash','other') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'other' COMMENT '(DC2Type:PaymentTypeCC)',
  `notes_ach` longtext COLLATE utf8_unicode_ci,
  `notes_cc` longtext COLLATE utf8_unicode_ci,
  `sync_balance` tinyint(1) NOT NULL DEFAULT '0',
  `post_payment` tinyint(1) NOT NULL DEFAULT '1',
  `synchronization_strategy` enum('real_time','deposited') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'deposited' COMMENT '(DC2Type:SynchronizationStrategy)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6EE512796CD5FBA3` (`holding_id`),
  CONSTRAINT `FK_6EE512796CD5FBA3` FOREIGN KEY (`holding_id`) REFERENCES `cj_holding` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yardi_settings`
--

LOCK TABLES `yardi_settings` WRITE;
/*!40000 ALTER TABLE `yardi_settings` DISABLE KEYS */;
INSERT INTO `yardi_settings` VALUES (1,5,'TN3nDh9xrHvC4VkkJOixpW/ZZy0C2dNtetzOHQcHdqJ84rnL5ZuqbTSyWos6t2YiM8bjUl7F/OEp8ExAvRYXng==','rqqhWla8G8guV5sJ509mCH8WGU1DRl2mteUvpy6TR+k=','zs8ts/J0BZipZYRZmuZTMj47MGIzPCAFTB/DFlUKgBk=','tU8K9unTiDRSWItaJUlbOXn7O900Vf84Pl9EwY2ZDhE=','nB8/pITQ0w8uslQYSp6M4om/Oha1knwsOhX33EdCWIw=','TE6hFv3RI70Aadckg/lyojMmLGU6+pCszWelpXnjUeI=','check','cash','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=','l89cmWbMqHfTbmhNx1N9VzHyqn34xDmSQ+OdbpHzNfc=',1,1,'deposited');
/*!40000 ALTER TABLE `yardi_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

-- Table structure for table `migration_version`
--

DROP TABLE IF EXISTS `migration_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_version` (
  `version` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_version`
--

LOCK TABLES `migration_version` WRITE;
/*!40000 ALTER TABLE `migration_version` DISABLE KEYS */;
INSERT INTO `migration_version` VALUES (135);
/*!40000 ALTER TABLE `migration_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_versions`

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-09-21 13:26:26
