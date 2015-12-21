-- MySQL dump 10.14  Distrib 5.5.44-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: cacti
-- ------------------------------------------------------
-- Server version	5.5.44-MariaDB

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
-- Table structure for table `cdef`
--

DROP TABLE IF EXISTS `cdef`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdef` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cdef`
--

LOCK TABLES `cdef` WRITE;
/*!40000 ALTER TABLE `cdef` DISABLE KEYS */;
INSERT INTO `cdef` VALUES (3,'3d352eed9fa8f7b2791205b3273708c7','Make Stack Negative'),(4,'e961cc8ec04fda6ed4981cf5ad501aa5','Make Per 5 Minutes'),(12,'f1ac79f05f255c02f914c920f1038c54','Total All Data Sources'),(2,'73f95f8b77b5508157d64047342c421e','Turn Bytes into Bits'),(14,'634a23af5e78af0964e8d33b1a4ed26b','Multiply by 1024'),(15,'068984b5ccdfd2048869efae5166f722','Total All Data Sources, Multiply by 1024'),(16,'1535524443c638e6d0274a6c296feb58','Divide by 10'),(17,'2f7a32b6bbdb5c08103bd90a9c9adf39','div 10');
/*!40000 ALTER TABLE `cdef` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cdef_items`
--

DROP TABLE IF EXISTS `cdef_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdef_items` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `cdef_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sequence` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `value` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cdef_id` (`cdef_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cdef_items`
--

LOCK TABLES `cdef_items` WRITE;
/*!40000 ALTER TABLE `cdef_items` DISABLE KEYS */;
INSERT INTO `cdef_items` VALUES (7,'9bbf6b792507bb9bb17d2af0970f9be9',2,1,4,'CURRENT_DATA_SOURCE'),(9,'a4b8eb2c3bf4920a3ef571a7a004be53',2,2,6,'8'),(8,'caa4e023ac2d7b1c4b4c8c4adfd55dfe',2,3,2,'3'),(10,'c888c9fe6b62c26c4bfe23e18991731d',3,1,4,'CURRENT_DATA_SOURCE'),(11,'1e1d0b29a94e08b648c8f053715442a0',3,3,2,'3'),(12,'4355c197998c7f8b285be7821ddc6da4',3,2,6,'-1'),(13,'40bb7a1143b0f2e2efca14eb356236de',4,1,4,'CURRENT_DATA_SOURCE'),(14,'42686ea0925c0220924b7d333599cd67',4,3,2,'3'),(15,'faf1b148b2c0e0527362ed5b8ca1d351',4,2,6,'300'),(16,'0ef6b8a42dc83b4e43e437960fccd2ea',12,1,4,'ALL_DATA_SOURCES_NODUPS'),(18,'86370cfa0008fe8c56b28be80ee39a40',14,1,4,'CURRENT_DATA_SOURCE'),(19,'9a35cc60d47691af37f6fddf02064e20',14,2,6,'1024'),(20,'5d7a7941ec0440b257e5598a27dd1688',14,3,2,'3'),(21,'44fd595c60539ff0f5817731d9f43a85',15,1,4,'ALL_DATA_SOURCES_NODUPS'),(22,'aa38be265e5ac31783e57ce6f9314e9a',15,2,6,'1024'),(23,'204423d4b2598f1f7252eea19458345c',15,3,2,'3'),(24,'dc0d3bad3d72eee3634ef1b1526d5379',16,1,4,'CURRENT_DATA_SOURCE'),(25,'2318035ef191a521ee9b52c6d66b81cf',16,2,6,'10'),(26,'d4a5092928315fc0812a3c929b0bd556',16,3,2,'4'),(27,'1b8f65858042a90e0216119d92c21427',17,1,4,'CURRENT_DATA_SOURCE'),(28,'99094900354ad9f857c3ede74dc0d7c2',17,2,6,'10'),(29,'f8c02b6ec507ded8bece2d6c1f3b473a',17,3,2,'4');
/*!40000 ALTER TABLE `cdef_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `colors`
--

DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `colors` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hex` varchar(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `colors`
--

LOCK TABLES `colors` WRITE;
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` VALUES (1,'000000'),(2,'FFFFFF'),(4,'FAFD9E'),(5,'C0C0C0'),(6,'74C366'),(7,'6DC8FE'),(8,'EA8F00'),(9,'FF0000'),(10,'4444FF'),(11,'FF00FF'),(12,'00FF00'),(13,'8D85F3'),(14,'AD3B6E'),(15,'EACC00'),(16,'12B3B5'),(17,'157419'),(18,'C4FD3D'),(19,'817C4E'),(20,'002A97'),(21,'0000FF'),(22,'00CF00'),(24,'F9FD5F'),(25,'FFF200'),(26,'CCBB00'),(27,'837C04'),(28,'EAAF00'),(29,'FFD660'),(30,'FFC73B'),(31,'FFAB00'),(33,'FF7D00'),(34,'ED7600'),(35,'FF5700'),(36,'EE5019'),(37,'B1441E'),(38,'FFC3C0'),(39,'FF897C'),(40,'FF6044'),(41,'FF4105'),(42,'DA4725'),(43,'942D0C'),(44,'FF3932'),(45,'862F2F'),(46,'FF5576'),(47,'562B29'),(48,'F51D30'),(49,'DE0056'),(50,'ED5394'),(51,'B90054'),(52,'8F005C'),(53,'F24AC8'),(54,'E8CDEF'),(55,'D8ACE0'),(56,'A150AA'),(57,'750F7D'),(58,'8D00BA'),(59,'623465'),(60,'55009D'),(61,'3D168B'),(62,'311F4E'),(63,'D2D8F9'),(64,'9FA4EE'),(65,'6557D0'),(66,'4123A1'),(67,'4668E4'),(68,'0D006A'),(69,'00004D'),(70,'001D61'),(71,'00234B'),(72,'002A8F'),(73,'2175D9'),(74,'7CB3F1'),(75,'005199'),(76,'004359'),(77,'00A0C1'),(78,'007283'),(79,'00BED9'),(80,'AFECED'),(81,'55D6D3'),(82,'00BBB4'),(83,'009485'),(84,'005D57'),(85,'008A77'),(86,'008A6D'),(87,'00B99B'),(88,'009F67'),(89,'00694A'),(90,'00A348'),(91,'00BF47'),(92,'96E78A'),(93,'00BD27'),(94,'35962B'),(95,'7EE600'),(96,'6EA100'),(97,'CAF100'),(98,'F5F800'),(99,'CDCFC4'),(100,'BCBEB3'),(101,'AAABA1'),(102,'8F9286'),(103,'797C6E'),(104,'2E3127');
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_input`
--

DROP TABLE IF EXISTS `data_input`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_input` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `input_string` varchar(255) DEFAULT NULL,
  `type_id` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_input`
--

LOCK TABLES `data_input` WRITE;
/*!40000 ALTER TABLE `data_input` DISABLE KEYS */;
INSERT INTO `data_input` VALUES (1,'3eb92bb845b9660a7445cf9740726522','Get SNMP Data','',2),(2,'bf566c869ac6443b0c75d1c32b5a350e','Get SNMP Data (Indexed)','',3),(3,'274f4685461170b9eb1b98d22567ab5e','Unix - Get Free Disk Space','<path_cacti>/scripts/diskfree.sh <partition>',1),(4,'95ed0993eb3095f9920d431ac80f4231','Unix - Get Load Average','perl <path_cacti>/scripts/loadavg_multi.pl',1),(5,'79a284e136bb6b061c6f96ec219ac448','Unix - Get Logged In Users','perl <path_cacti>/scripts/unix_users.pl <username>',1),(6,'362e6d4768937c4f899dd21b91ef0ff8','Linux - Get Memory Usage','perl <path_cacti>/scripts/linux_memory.pl <grepstr>',1),(7,'a637359e0a4287ba43048a5fdf202066','Unix - Get System Processes','perl <path_cacti>/scripts/unix_processes.pl',1),(8,'47d6bfe8be57a45171afd678920bd399','Unix - Get TCP Connections','perl <path_cacti>/scripts/unix_tcp_connections.pl <grepstr>',1),(9,'cc948e4de13f32b6aea45abaadd287a3','Unix - Get Web Hits','perl <path_cacti>/scripts/webhits.pl <log_path>',1),(10,'8bd153aeb06e3ff89efc73f35849a7a0','Unix - Ping Host','perl <path_cacti>/scripts/ping.pl <ip>',1),(11,'80e9e4c4191a5da189ae26d0e237f015','Get Script Data (Indexed)','',4),(12,'332111d8b54ac8ce939af87a7eac0c06','Get Script Server Data (Indexed)','',6),(13,'34e2211e7568e5667baec4c86f5aac51','DOCSIS Stats','<path_cacti>/scripts/ss_docsis_stats.php ss_docsis_stats <hostname> <snmp_community> <snmp_version> <snmp_port> <snmp_timeout> <snmpv3_auth_username> <snmpv3_auth_password>',5);
/*!40000 ALTER TABLE `data_input` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_input_data`
--

DROP TABLE IF EXISTS `data_input_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_input_data` (
  `data_input_field_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_template_data_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `t_value` char(2) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`data_input_field_id`,`data_template_data_id`),
  KEY `t_value` (`t_value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_input_data`
--

LOCK TABLES `data_input_data` WRITE;
/*!40000 ALTER TABLE `data_input_data` DISABLE KEYS */;
INSERT INTO `data_input_data` VALUES (14,1,'on',''),(13,1,'on',''),(12,1,'on',''),(14,2,'on',''),(13,2,'on',''),(12,2,'on',''),(14,3,'on',''),(13,3,'on',''),(12,3,'on',''),(1,4,'',''),(1,5,'',''),(1,6,'',''),(14,7,'on',''),(13,7,'on',''),(12,7,'on',''),(14,8,'on',''),(13,8,'on',''),(12,8,'on',''),(14,9,'on',''),(13,9,'on',''),(12,9,'on',''),(14,10,'on',''),(13,10,'on',''),(12,10,'on',''),(22,12,'','Buffers:'),(22,13,'','MemFree:'),(22,14,'','^Cached:'),(22,15,'','SwapFree:'),(29,18,'',''),(1,19,'',''),(2,19,'',''),(6,21,'','.1.3.6.1.2.1.25.3.3.1.2.1'),(1,27,'',''),(6,28,'','.1.3.6.1.4.1.9.9.109.1.1.1.1.3.1'),(6,29,'','.1.3.6.1.4.1.9.9.109.1.1.1.1.4.1'),(1,30,'',''),(1,31,'',''),(1,32,'',''),(1,33,'',''),(1,34,'',''),(14,35,'on',''),(13,35,'on',''),(12,35,'on',''),(14,36,'on',''),(13,36,'on',''),(12,36,'on',''),(1,22,'',''),(1,23,'',''),(1,24,'',''),(1,25,'',''),(1,26,'',''),(33,37,'on',''),(32,37,'on',''),(31,37,'on',''),(14,38,'on',''),(13,38,'on',''),(12,38,'on',''),(14,39,'on',''),(13,39,'on',''),(12,39,'on',''),(14,40,'on',''),(13,40,'on',''),(12,40,'on',''),(14,41,'on',''),(13,41,'on',''),(12,41,'on',''),(14,55,'on',''),(13,55,'on',''),(12,55,'on',''),(37,56,'on',''),(36,56,'on',''),(35,56,'on',''),(37,57,'on',''),(36,57,'on',''),(35,57,'on',''),(1,58,'',''),(1,59,'',''),(1,20,'',''),(5,6,'',''),(22,62,NULL,'MemFree:'),(22,63,NULL,'SwapFree:'),(4,6,'',''),(3,6,'',''),(2,6,'',''),(6,69,'on',''),(1,68,'',''),(2,68,'',''),(6,6,'','.1.3.6.1.4.1.2021.11.51.0'),(2,27,'',''),(3,27,'',''),(4,27,'',''),(5,27,'',''),(6,27,'','.1.3.6.1.4.1.9.2.1.58.0'),(2,59,'',''),(3,59,'',''),(4,59,'',''),(5,59,'',''),(6,59,'','.1.3.6.1.2.1.25.1.5.0'),(2,58,'',''),(3,58,'',''),(4,58,'',''),(5,58,'',''),(6,58,'','.1.3.6.1.2.1.25.1.6.0'),(2,24,'',''),(3,24,'',''),(4,24,'',''),(5,24,'',''),(6,24,'','.1.3.6.1.4.1.23.2.28.2.5.0'),(2,25,'',''),(3,25,'',''),(4,25,'',''),(5,25,'',''),(6,25,'','.1.3.6.1.4.1.23.2.28.2.6.0'),(2,22,'',''),(3,22,'',''),(4,22,'',''),(5,22,'',''),(6,22,'','.1.3.6.1.4.1.23.2.28.2.1.0'),(2,23,'',''),(3,23,'',''),(4,23,'',''),(5,23,'',''),(6,23,'','.1.3.6.1.4.1.23.2.28.2.2.0'),(2,26,'',''),(3,26,'',''),(4,26,'',''),(5,26,'',''),(6,26,'','.1.3.6.1.4.1.23.2.28.2.7.0'),(2,20,'',''),(3,20,'',''),(4,20,'',''),(5,20,'',''),(6,20,'','.1.3.6.1.4.1.23.2.28.3.2.0'),(3,19,'',''),(4,19,'',''),(5,19,'',''),(6,19,'','.1.3.6.1.4.1.23.2.28.3.1'),(2,4,'',''),(3,4,'',''),(4,4,'',''),(5,4,'',''),(6,4,'','.1.3.6.1.4.1.2021.11.52.0'),(2,5,'',''),(3,5,'',''),(4,5,'',''),(5,5,'',''),(6,5,'','.1.3.6.1.4.1.2021.11.50.0'),(2,30,'',''),(3,30,'',''),(4,30,'',''),(5,30,'',''),(6,30,'','.1.3.6.1.4.1.2021.10.1.3.1'),(2,32,'',''),(3,32,'',''),(4,32,'',''),(5,32,'',''),(6,32,'','.1.3.6.1.4.1.2021.10.1.3.3'),(2,31,'',''),(3,31,'',''),(4,31,'',''),(5,31,'',''),(6,31,'','.1.3.6.1.4.1.2021.10.1.3.2'),(2,33,'',''),(3,33,'',''),(4,33,'',''),(5,33,'',''),(6,33,'','.1.3.6.1.4.1.2021.4.14.0'),(3,68,'',''),(4,68,'',''),(5,68,'',''),(6,68,'','.1.3.6.1.4.1.2021.4.15.0'),(2,34,'',''),(3,34,'',''),(4,34,'',''),(5,34,'',''),(6,34,'','.1.3.6.1.4.1.2021.4.6.0'),(20,17,'',''),(20,65,NULL,''),(53,70,'',''),(52,70,'',''),(51,70,'',''),(50,70,'',''),(49,70,'',''),(48,70,'',''),(47,70,'',''),(53,71,NULL,''),(52,71,NULL,''),(51,71,NULL,'500'),(50,71,NULL,'161'),(49,71,NULL,'2'),(48,71,NULL,'public'),(47,71,NULL,'10.42.2.107'),(53,72,NULL,''),(52,72,NULL,''),(51,72,NULL,'500'),(50,72,NULL,'161'),(49,72,NULL,'2'),(48,72,NULL,'public'),(47,72,NULL,'10.42.2.107'),(53,73,NULL,''),(52,73,NULL,''),(51,73,NULL,'1500'),(50,73,NULL,'161'),(49,73,NULL,'1'),(48,73,NULL,'public'),(47,73,NULL,'10.42.2.106'),(53,74,NULL,''),(52,74,NULL,''),(51,74,NULL,'1500'),(50,74,NULL,'161'),(49,74,NULL,'1'),(48,74,NULL,'public'),(47,74,NULL,'10.42.2.106'),(53,75,NULL,''),(52,75,NULL,''),(51,75,NULL,'500'),(50,75,NULL,'161'),(49,75,NULL,'1'),(48,75,NULL,'public'),(47,75,NULL,'cm-100002.test.erznet.tv'),(53,76,NULL,''),(52,76,NULL,''),(51,76,NULL,'500'),(50,76,NULL,'161'),(49,76,NULL,'1'),(48,76,NULL,'public'),(47,76,NULL,'cm-100002.test.erznet.tv'),(53,77,NULL,''),(52,77,NULL,''),(51,77,NULL,'500'),(50,77,NULL,'161'),(49,77,NULL,'1'),(48,77,NULL,'public'),(47,77,NULL,'cm-100003.test.erznet.tv'),(53,78,NULL,''),(52,78,NULL,''),(51,78,NULL,'500'),(50,78,NULL,'161'),(49,78,NULL,'1'),(48,78,NULL,'public'),(47,78,NULL,'cm-100003.test.erznet.tv'),(53,79,NULL,''),(52,79,NULL,''),(51,79,NULL,'500'),(50,79,NULL,'161'),(49,79,NULL,'1'),(48,79,NULL,'public'),(47,79,NULL,'cm-100004.test.erznet.tv'),(53,80,NULL,''),(52,80,NULL,''),(51,80,NULL,'500'),(50,80,NULL,'161'),(49,80,NULL,'1'),(48,80,NULL,'public'),(47,80,NULL,'cm-100004.test.erznet.tv'),(53,81,NULL,''),(52,81,NULL,''),(51,81,NULL,'500'),(50,81,NULL,'161'),(49,81,NULL,'1'),(48,81,NULL,'public'),(47,81,NULL,'cm-100005.test.erznet.tv'),(53,82,NULL,''),(52,82,NULL,''),(51,82,NULL,'500'),(50,82,NULL,'161'),(49,82,NULL,'1'),(48,82,NULL,'public'),(47,82,NULL,'cm-100005.test.erznet.tv'),(53,83,NULL,''),(52,83,NULL,''),(51,83,NULL,'500'),(50,83,NULL,'161'),(49,83,NULL,'1'),(48,83,NULL,'public'),(47,83,NULL,'cm-100006.test.erznet.tv'),(53,84,NULL,''),(52,84,NULL,''),(51,84,NULL,'500'),(50,84,NULL,'161'),(49,84,NULL,'1'),(48,84,NULL,'public'),(47,84,NULL,'cm-100006.test.erznet.tv'),(53,85,NULL,''),(52,85,NULL,''),(51,85,NULL,'500'),(50,85,NULL,'161'),(49,85,NULL,'1'),(48,85,NULL,'public'),(47,85,NULL,'cm-100007.test.erznet.tv'),(53,86,NULL,''),(52,86,NULL,''),(51,86,NULL,'500'),(50,86,NULL,'161'),(49,86,NULL,'1'),(48,86,NULL,'public'),(47,86,NULL,'cm-100007.test.erznet.tv'),(53,87,NULL,''),(52,87,NULL,''),(51,87,NULL,'500'),(50,87,NULL,'161'),(49,87,NULL,'1'),(48,87,NULL,'public'),(47,87,NULL,'cm-100008.test.erznet.tv'),(53,88,NULL,''),(52,88,NULL,''),(51,88,NULL,'500'),(50,88,NULL,'161'),(49,88,NULL,'1'),(48,88,NULL,'public'),(47,88,NULL,'cm-100008.test.erznet.tv'),(53,89,NULL,''),(52,89,NULL,''),(51,89,NULL,'500'),(50,89,NULL,'161'),(49,89,NULL,'1'),(48,89,NULL,'public'),(47,89,NULL,'cm-100009.test.erznet.tv'),(53,90,NULL,''),(52,90,NULL,''),(51,90,NULL,'500'),(50,90,NULL,'161'),(49,90,NULL,'1'),(48,90,NULL,'public'),(47,90,NULL,'cm-100009.test.erznet.tv'),(53,91,NULL,''),(52,91,NULL,''),(51,91,NULL,'500'),(50,91,NULL,'161'),(49,91,NULL,'2'),(48,91,NULL,'public'),(47,91,NULL,'10.42.2.107'),(53,92,NULL,''),(52,92,NULL,''),(51,92,NULL,''),(50,92,NULL,''),(49,92,NULL,''),(48,92,NULL,''),(47,92,NULL,''),(53,93,NULL,''),(52,93,NULL,''),(51,93,NULL,''),(50,93,NULL,''),(49,93,NULL,''),(48,93,NULL,''),(47,93,NULL,''),(53,94,NULL,''),(52,94,NULL,''),(51,94,NULL,'500'),(50,94,NULL,'161'),(49,94,NULL,'2'),(48,94,NULL,'public'),(47,94,NULL,'10.42.2.106'),(53,95,NULL,''),(52,95,NULL,''),(51,95,NULL,'500'),(50,95,NULL,'161'),(49,95,NULL,'2'),(48,95,NULL,'public'),(47,95,NULL,'10.42.2.106'),(53,96,NULL,''),(52,96,NULL,''),(51,96,NULL,'500'),(50,96,NULL,'161'),(49,96,NULL,'2'),(48,96,NULL,'public'),(47,96,NULL,'10.42.2.107'),(53,97,NULL,''),(52,97,NULL,''),(51,97,NULL,'500'),(50,97,NULL,'161'),(49,97,NULL,'2'),(48,97,NULL,'public'),(47,97,NULL,'10.42.2.107'),(53,98,NULL,''),(52,98,NULL,''),(51,98,NULL,'500'),(50,98,NULL,'161'),(49,98,NULL,'1'),(48,98,NULL,'public'),(47,98,NULL,'cm-100002.test.erznet.tv'),(53,99,NULL,''),(52,99,NULL,''),(51,99,NULL,'500'),(50,99,NULL,'161'),(49,99,NULL,'1'),(48,99,NULL,'public'),(47,99,NULL,'cm-100002.test.erznet.tv'),(53,100,NULL,''),(52,100,NULL,''),(51,100,NULL,'500'),(50,100,NULL,'161'),(49,100,NULL,'1'),(48,100,NULL,'public'),(47,100,NULL,'cm-100003.test.erznet.tv'),(53,101,NULL,''),(52,101,NULL,''),(51,101,NULL,'500'),(50,101,NULL,'161'),(49,101,NULL,'1'),(48,101,NULL,'public'),(47,101,NULL,'cm-100003.test.erznet.tv'),(53,102,NULL,''),(52,102,NULL,''),(51,102,NULL,'500'),(50,102,NULL,'161'),(49,102,NULL,'1'),(48,102,NULL,'public'),(47,102,NULL,'cm-100004.test.erznet.tv'),(53,103,NULL,''),(52,103,NULL,''),(51,103,NULL,'500'),(50,103,NULL,'161'),(49,103,NULL,'1'),(48,103,NULL,'public'),(47,103,NULL,'cm-100004.test.erznet.tv'),(53,104,NULL,''),(52,104,NULL,''),(51,104,NULL,'500'),(50,104,NULL,'161'),(49,104,NULL,'1'),(48,104,NULL,'public'),(47,104,NULL,'cm-100005.test.erznet.tv'),(53,105,NULL,''),(52,105,NULL,''),(51,105,NULL,'500'),(50,105,NULL,'161'),(49,105,NULL,'1'),(48,105,NULL,'public'),(47,105,NULL,'cm-100005.test.erznet.tv'),(53,106,NULL,''),(52,106,NULL,''),(51,106,NULL,'500'),(50,106,NULL,'161'),(49,106,NULL,'1'),(48,106,NULL,'public'),(47,106,NULL,'cm-100006.test.erznet.tv'),(53,107,NULL,''),(52,107,NULL,''),(51,107,NULL,'500'),(50,107,NULL,'161'),(49,107,NULL,'1'),(48,107,NULL,'public'),(47,107,NULL,'cm-100006.test.erznet.tv'),(53,108,NULL,''),(52,108,NULL,''),(51,108,NULL,'500'),(50,108,NULL,'161'),(49,108,NULL,'1'),(48,108,NULL,'public'),(47,108,NULL,'cm-100007.test.erznet.tv'),(53,109,NULL,''),(52,109,NULL,''),(51,109,NULL,'500'),(50,109,NULL,'161'),(49,109,NULL,'1'),(48,109,NULL,'public'),(47,109,NULL,'cm-100007.test.erznet.tv'),(53,110,NULL,''),(52,110,NULL,''),(51,110,NULL,'500'),(50,110,NULL,'161'),(49,110,NULL,'1'),(48,110,NULL,'public'),(47,110,NULL,'cm-100008.test.erznet.tv'),(53,111,NULL,''),(52,111,NULL,''),(51,111,NULL,'500'),(50,111,NULL,'161'),(49,111,NULL,'1'),(48,111,NULL,'public'),(47,111,NULL,'cm-100008.test.erznet.tv'),(53,112,NULL,''),(52,112,NULL,''),(51,112,NULL,'500'),(50,112,NULL,'161'),(49,112,NULL,'1'),(48,112,NULL,'public'),(47,112,NULL,'cm-100009.test.erznet.tv'),(53,113,NULL,''),(52,113,NULL,''),(51,113,NULL,'500'),(50,113,NULL,'161'),(49,113,NULL,'1'),(48,113,NULL,'public'),(47,113,NULL,'cm-100009.test.erznet.tv'),(53,114,NULL,''),(52,114,NULL,''),(51,114,NULL,'500'),(50,114,NULL,'161'),(49,114,NULL,'2'),(48,114,NULL,'public'),(47,114,NULL,'10.42.2.106'),(53,115,NULL,''),(52,115,NULL,''),(51,115,NULL,'500'),(50,115,NULL,'161'),(49,115,NULL,'2'),(48,115,NULL,'public'),(47,115,NULL,'10.42.2.106'),(53,116,NULL,''),(52,116,NULL,''),(51,116,NULL,'500'),(50,116,NULL,'161'),(49,116,NULL,'2'),(48,116,NULL,'public'),(47,116,NULL,'10.42.2.107'),(53,117,NULL,''),(52,117,NULL,''),(51,117,NULL,'500'),(50,117,NULL,'161'),(49,117,NULL,'2'),(48,117,NULL,'public'),(47,117,NULL,'10.42.2.107'),(53,118,NULL,''),(52,118,NULL,''),(51,118,NULL,'500'),(50,118,NULL,'161'),(49,118,NULL,'1'),(48,118,NULL,'public'),(47,118,NULL,'cm-100002.test.erznet.tv'),(53,119,NULL,''),(52,119,NULL,''),(51,119,NULL,'500'),(50,119,NULL,'161'),(49,119,NULL,'1'),(48,119,NULL,'public'),(47,119,NULL,'cm-100002.test.erznet.tv'),(53,120,NULL,''),(52,120,NULL,''),(51,120,NULL,'500'),(50,120,NULL,'161'),(49,120,NULL,'1'),(48,120,NULL,'public'),(47,120,NULL,'cm-100003.test.erznet.tv'),(53,121,NULL,''),(52,121,NULL,''),(51,121,NULL,'500'),(50,121,NULL,'161'),(49,121,NULL,'1'),(48,121,NULL,'public'),(47,121,NULL,'cm-100003.test.erznet.tv'),(53,122,NULL,''),(52,122,NULL,''),(51,122,NULL,'500'),(50,122,NULL,'161'),(49,122,NULL,'1'),(48,122,NULL,'public'),(47,122,NULL,'cm-100004.test.erznet.tv'),(53,123,NULL,''),(52,123,NULL,''),(51,123,NULL,'500'),(50,123,NULL,'161'),(49,123,NULL,'1'),(48,123,NULL,'public'),(47,123,NULL,'cm-100004.test.erznet.tv'),(53,124,NULL,''),(52,124,NULL,''),(51,124,NULL,'500'),(50,124,NULL,'161'),(49,124,NULL,'1'),(48,124,NULL,'public'),(47,124,NULL,'cm-100005.test.erznet.tv'),(53,125,NULL,''),(52,125,NULL,''),(51,125,NULL,'500'),(50,125,NULL,'161'),(49,125,NULL,'1'),(48,125,NULL,'public'),(47,125,NULL,'cm-100005.test.erznet.tv'),(53,126,NULL,''),(52,126,NULL,''),(51,126,NULL,'500'),(50,126,NULL,'161'),(49,126,NULL,'1'),(48,126,NULL,'public'),(47,126,NULL,'cm-100006.test.erznet.tv'),(53,127,NULL,''),(52,127,NULL,''),(51,127,NULL,'500'),(50,127,NULL,'161'),(49,127,NULL,'1'),(48,127,NULL,'public'),(47,127,NULL,'cm-100006.test.erznet.tv'),(53,128,NULL,''),(52,128,NULL,''),(51,128,NULL,'500'),(50,128,NULL,'161'),(49,128,NULL,'1'),(48,128,NULL,'public'),(47,128,NULL,'cm-100007.test.erznet.tv'),(53,129,NULL,''),(52,129,NULL,''),(51,129,NULL,'500'),(50,129,NULL,'161'),(49,129,NULL,'1'),(48,129,NULL,'public'),(47,129,NULL,'cm-100007.test.erznet.tv'),(53,130,NULL,''),(52,130,NULL,''),(51,130,NULL,'500'),(50,130,NULL,'161'),(49,130,NULL,'1'),(48,130,NULL,'public'),(47,130,NULL,'cm-100008.test.erznet.tv'),(53,131,NULL,''),(52,131,NULL,''),(51,131,NULL,'500'),(50,131,NULL,'161'),(49,131,NULL,'1'),(48,131,NULL,'public'),(47,131,NULL,'cm-100008.test.erznet.tv'),(53,132,NULL,''),(52,132,NULL,''),(51,132,NULL,'500'),(50,132,NULL,'161'),(49,132,NULL,'1'),(48,132,NULL,'public'),(47,132,NULL,'cm-100009.test.erznet.tv'),(53,133,NULL,''),(52,133,NULL,''),(51,133,NULL,'500'),(50,133,NULL,'161'),(49,133,NULL,'1'),(48,133,NULL,'public'),(47,133,NULL,'cm-100009.test.erznet.tv'),(53,134,NULL,''),(52,134,NULL,''),(51,134,NULL,'500'),(50,134,NULL,'161'),(49,134,NULL,'2'),(48,134,NULL,'public'),(47,134,NULL,'10.42.2.100'),(53,135,NULL,''),(52,135,NULL,''),(51,135,NULL,'500'),(50,135,NULL,'161'),(49,135,NULL,'2'),(48,135,NULL,'public'),(47,135,NULL,'10.42.2.100'),(53,136,NULL,''),(52,136,NULL,''),(51,136,NULL,'500'),(50,136,NULL,'161'),(49,136,NULL,'2'),(48,136,NULL,'public'),(47,136,NULL,'10.42.2.101'),(53,137,NULL,''),(52,137,NULL,''),(51,137,NULL,'500'),(50,137,NULL,'161'),(49,137,NULL,'2'),(48,137,NULL,'public'),(47,137,NULL,'10.42.2.101'),(53,138,NULL,''),(52,138,NULL,''),(51,138,NULL,'500'),(50,138,NULL,'161'),(49,138,NULL,'2'),(48,138,NULL,'public'),(47,138,NULL,'10.42.2.102'),(53,139,NULL,''),(52,139,NULL,''),(51,139,NULL,'500'),(50,139,NULL,'161'),(49,139,NULL,'2'),(48,139,NULL,'public'),(47,139,NULL,'10.42.2.102'),(53,140,NULL,''),(52,140,NULL,''),(51,140,NULL,'500'),(50,140,NULL,'161'),(49,140,NULL,'2'),(48,140,NULL,'public'),(47,140,NULL,'10.42.2.103'),(53,141,NULL,''),(52,141,NULL,''),(51,141,NULL,'500'),(50,141,NULL,'161'),(49,141,NULL,'2'),(48,141,NULL,'public'),(47,141,NULL,'10.42.2.103'),(53,142,NULL,''),(52,142,NULL,''),(51,142,NULL,'500'),(50,142,NULL,'161'),(49,142,NULL,'2'),(48,142,NULL,'public'),(47,142,NULL,'10.42.2.104'),(53,143,NULL,''),(52,143,NULL,''),(51,143,NULL,'500'),(50,143,NULL,'161'),(49,143,NULL,'2'),(48,143,NULL,'public'),(47,143,NULL,'10.42.2.104'),(53,144,NULL,''),(52,144,NULL,''),(51,144,NULL,'500'),(50,144,NULL,'161'),(49,144,NULL,'2'),(48,144,NULL,'public'),(47,144,NULL,'10.42.2.105'),(53,145,NULL,''),(52,145,NULL,''),(51,145,NULL,'500'),(50,145,NULL,'161'),(49,145,NULL,'2'),(48,145,NULL,'public'),(47,145,NULL,'10.42.2.105'),(53,146,NULL,''),(52,146,NULL,''),(51,146,NULL,'500'),(50,146,NULL,'161'),(49,146,NULL,'2'),(48,146,NULL,'public'),(47,146,NULL,'10.42.2.106'),(53,147,NULL,''),(52,147,NULL,''),(51,147,NULL,'500'),(50,147,NULL,'161'),(49,147,NULL,'2'),(48,147,NULL,'public'),(47,147,NULL,'10.42.2.106'),(53,148,NULL,''),(52,148,NULL,''),(51,148,NULL,'500'),(50,148,NULL,'161'),(49,148,NULL,'2'),(48,148,NULL,'public'),(47,148,NULL,'10.42.2.107'),(53,149,NULL,''),(52,149,NULL,''),(51,149,NULL,'500'),(50,149,NULL,'161'),(49,149,NULL,'2'),(48,149,NULL,'public'),(47,149,NULL,'10.42.2.107'),(53,150,NULL,''),(52,150,NULL,''),(51,150,NULL,'500'),(50,150,NULL,'161'),(49,150,NULL,'2'),(48,150,NULL,'public'),(47,150,NULL,'10.42.2.108'),(53,151,NULL,''),(52,151,NULL,''),(51,151,NULL,'500'),(50,151,NULL,'161'),(49,151,NULL,'2'),(48,151,NULL,'public'),(47,151,NULL,'10.42.2.108'),(53,152,NULL,''),(52,152,NULL,''),(51,152,NULL,'500'),(50,152,NULL,'161'),(49,152,NULL,'2'),(48,152,NULL,'public'),(47,152,NULL,'10.42.2.109'),(53,153,NULL,''),(52,153,NULL,''),(51,153,NULL,'500'),(50,153,NULL,'161'),(49,153,NULL,'2'),(48,153,NULL,'public'),(47,153,NULL,'10.42.2.109'),(53,154,NULL,''),(52,154,NULL,''),(51,154,NULL,'500'),(50,154,NULL,'161'),(49,154,NULL,'2'),(48,154,NULL,'public'),(47,154,NULL,'10.42.2.100'),(53,155,NULL,''),(52,155,NULL,''),(51,155,NULL,'500'),(50,155,NULL,'161'),(49,155,NULL,'2'),(48,155,NULL,'public'),(47,155,NULL,'10.42.2.100'),(53,156,NULL,''),(52,156,NULL,''),(51,156,NULL,'500'),(50,156,NULL,'161'),(49,156,NULL,'2'),(48,156,NULL,'public'),(47,156,NULL,'10.42.2.101'),(53,157,NULL,''),(52,157,NULL,''),(51,157,NULL,'500'),(50,157,NULL,'161'),(49,157,NULL,'2'),(48,157,NULL,'public'),(47,157,NULL,'10.42.2.101'),(53,158,NULL,''),(52,158,NULL,''),(51,158,NULL,'500'),(50,158,NULL,'161'),(49,158,NULL,'2'),(48,158,NULL,'public'),(47,158,NULL,'10.42.2.102'),(53,159,NULL,''),(52,159,NULL,''),(51,159,NULL,'500'),(50,159,NULL,'161'),(49,159,NULL,'2'),(48,159,NULL,'public'),(47,159,NULL,'10.42.2.102'),(53,160,NULL,''),(52,160,NULL,''),(51,160,NULL,'500'),(50,160,NULL,'161'),(49,160,NULL,'2'),(48,160,NULL,'public'),(47,160,NULL,'10.42.2.103'),(53,161,NULL,''),(52,161,NULL,''),(51,161,NULL,'500'),(50,161,NULL,'161'),(49,161,NULL,'2'),(48,161,NULL,'public'),(47,161,NULL,'10.42.2.103'),(53,162,NULL,''),(52,162,NULL,''),(51,162,NULL,'500'),(50,162,NULL,'161'),(49,162,NULL,'2'),(48,162,NULL,'public'),(47,162,NULL,'10.42.2.104'),(53,163,NULL,''),(52,163,NULL,''),(51,163,NULL,'500'),(50,163,NULL,'161'),(49,163,NULL,'2'),(48,163,NULL,'public'),(47,163,NULL,'10.42.2.104'),(53,164,NULL,''),(52,164,NULL,''),(51,164,NULL,'500'),(50,164,NULL,'161'),(49,164,NULL,'2'),(48,164,NULL,'public'),(47,164,NULL,'10.42.2.105'),(53,165,NULL,''),(52,165,NULL,''),(51,165,NULL,'500'),(50,165,NULL,'161'),(49,165,NULL,'2'),(48,165,NULL,'public'),(47,165,NULL,'10.42.2.105'),(53,166,NULL,''),(52,166,NULL,''),(51,166,NULL,'500'),(50,166,NULL,'161'),(49,166,NULL,'2'),(48,166,NULL,'public'),(47,166,NULL,'10.42.2.106'),(53,167,NULL,''),(52,167,NULL,''),(51,167,NULL,'500'),(50,167,NULL,'161'),(49,167,NULL,'2'),(48,167,NULL,'public'),(47,167,NULL,'10.42.2.106'),(53,168,NULL,''),(52,168,NULL,''),(51,168,NULL,'500'),(50,168,NULL,'161'),(49,168,NULL,'2'),(48,168,NULL,'public'),(47,168,NULL,'10.42.2.107'),(53,169,NULL,''),(52,169,NULL,''),(51,169,NULL,'500'),(50,169,NULL,'161'),(49,169,NULL,'2'),(48,169,NULL,'public'),(47,169,NULL,'10.42.2.107'),(53,170,NULL,''),(52,170,NULL,''),(51,170,NULL,'500'),(50,170,NULL,'161'),(49,170,NULL,'2'),(48,170,NULL,'public'),(47,170,NULL,'10.42.2.108'),(53,171,NULL,''),(52,171,NULL,''),(51,171,NULL,'500'),(50,171,NULL,'161'),(49,171,NULL,'2'),(48,171,NULL,'public'),(47,171,NULL,'10.42.2.108'),(53,172,NULL,''),(52,172,NULL,''),(51,172,NULL,'500'),(50,172,NULL,'161'),(49,172,NULL,'2'),(48,172,NULL,'public'),(47,172,NULL,'10.42.2.109'),(53,173,NULL,''),(52,173,NULL,''),(51,173,NULL,'500'),(50,173,NULL,'161'),(49,173,NULL,'2'),(48,173,NULL,'public'),(47,173,NULL,'10.42.2.109'),(53,174,NULL,''),(52,174,NULL,''),(51,174,NULL,'500'),(50,174,NULL,'161'),(49,174,NULL,'2'),(48,174,NULL,'public'),(47,174,NULL,'10.42.2.100'),(53,175,NULL,''),(52,175,NULL,''),(51,175,NULL,'500'),(50,175,NULL,'161'),(49,175,NULL,'2'),(48,175,NULL,'public'),(47,175,NULL,'10.42.2.100'),(53,176,NULL,''),(52,176,NULL,''),(51,176,NULL,'500'),(50,176,NULL,'161'),(49,176,NULL,'2'),(48,176,NULL,'public'),(47,176,NULL,'10.42.2.100'),(53,177,NULL,''),(52,177,NULL,''),(51,177,NULL,'500'),(50,177,NULL,'161'),(49,177,NULL,'2'),(48,177,NULL,'public'),(47,177,NULL,'10.42.2.100'),(53,178,NULL,''),(52,178,NULL,''),(51,178,NULL,'500'),(50,178,NULL,'161'),(49,178,NULL,'2'),(48,178,NULL,'public'),(47,178,NULL,'10.42.2.101'),(53,179,NULL,''),(52,179,NULL,''),(51,179,NULL,'500'),(50,179,NULL,'161'),(49,179,NULL,'2'),(48,179,NULL,'public'),(47,179,NULL,'10.42.2.101'),(53,180,NULL,''),(52,180,NULL,''),(51,180,NULL,'500'),(50,180,NULL,'161'),(49,180,NULL,'2'),(48,180,NULL,'public'),(47,180,NULL,'10.42.2.101'),(53,181,NULL,''),(52,181,NULL,''),(51,181,NULL,'500'),(50,181,NULL,'161'),(49,181,NULL,'2'),(48,181,NULL,'public'),(47,181,NULL,'10.42.2.101'),(53,230,NULL,''),(52,230,NULL,''),(51,230,NULL,'500'),(50,230,NULL,'161'),(49,230,NULL,'2'),(48,230,NULL,'public'),(47,230,NULL,'10.42.2.114'),(53,231,NULL,''),(52,231,NULL,''),(51,231,NULL,'500'),(50,231,NULL,'161'),(49,231,NULL,'2'),(48,231,NULL,'public'),(47,231,NULL,'10.42.2.114'),(53,232,NULL,''),(52,232,NULL,''),(51,232,NULL,'500'),(50,232,NULL,'161'),(49,232,NULL,'2'),(48,232,NULL,'public'),(47,232,NULL,'10.42.2.114'),(53,233,NULL,''),(52,233,NULL,''),(51,233,NULL,'500'),(50,233,NULL,'161'),(49,233,NULL,'2'),(48,233,NULL,'public'),(47,233,NULL,'10.42.2.114'),(53,234,NULL,''),(52,234,NULL,''),(51,234,NULL,'500'),(50,234,NULL,'161'),(49,234,NULL,'2'),(48,234,NULL,'public'),(47,234,NULL,'10.42.2.115'),(53,235,NULL,''),(52,235,NULL,''),(51,235,NULL,'500'),(50,235,NULL,'161'),(49,235,NULL,'2'),(48,235,NULL,'public'),(47,235,NULL,'10.42.2.115'),(53,236,NULL,''),(52,236,NULL,''),(51,236,NULL,'500'),(50,236,NULL,'161'),(49,236,NULL,'2'),(48,236,NULL,'public'),(47,236,NULL,'10.42.2.115'),(53,237,NULL,''),(52,237,NULL,''),(51,237,NULL,'500'),(50,237,NULL,'161'),(49,237,NULL,'2'),(48,237,NULL,'public'),(47,237,NULL,'10.42.2.115'),(53,238,NULL,''),(52,238,NULL,''),(51,238,NULL,'500'),(50,238,NULL,'161'),(49,238,NULL,'2'),(48,238,NULL,'public'),(47,238,NULL,'10.42.2.116'),(53,239,NULL,''),(52,239,NULL,''),(51,239,NULL,'500'),(50,239,NULL,'161'),(49,239,NULL,'2'),(48,239,NULL,'public'),(47,239,NULL,'10.42.2.116'),(53,240,NULL,''),(52,240,NULL,''),(51,240,NULL,'500'),(50,240,NULL,'161'),(49,240,NULL,'2'),(48,240,NULL,'public'),(47,240,NULL,'10.42.2.116'),(53,241,NULL,''),(52,241,NULL,''),(51,241,NULL,'500'),(50,241,NULL,'161'),(49,241,NULL,'2'),(48,241,NULL,'public'),(47,241,NULL,'10.42.2.116'),(53,246,NULL,''),(52,246,NULL,''),(51,246,NULL,'500'),(50,246,NULL,'161'),(49,246,NULL,'2'),(48,246,NULL,'public'),(47,246,NULL,'10.42.2.118'),(53,247,NULL,''),(52,247,NULL,''),(51,247,NULL,'500'),(50,247,NULL,'161'),(49,247,NULL,'2'),(48,247,NULL,'public'),(47,247,NULL,'10.42.2.118'),(53,248,NULL,''),(52,248,NULL,''),(51,248,NULL,'500'),(50,248,NULL,'161'),(49,248,NULL,'2'),(48,248,NULL,'public'),(47,248,NULL,'10.42.2.118'),(53,249,NULL,''),(52,249,NULL,''),(51,249,NULL,'500'),(50,249,NULL,'161'),(49,249,NULL,'2'),(48,249,NULL,'public'),(47,249,NULL,'10.42.2.118'),(53,250,NULL,''),(52,250,NULL,''),(51,250,NULL,'500'),(50,250,NULL,'161'),(49,250,NULL,'2'),(48,250,NULL,'public'),(47,250,NULL,'10.42.2.119'),(53,251,NULL,''),(52,251,NULL,''),(51,251,NULL,'500'),(50,251,NULL,'161'),(49,251,NULL,'2'),(48,251,NULL,'public'),(47,251,NULL,'10.42.2.119'),(53,252,NULL,''),(52,252,NULL,''),(51,252,NULL,'500'),(50,252,NULL,'161'),(49,252,NULL,'2'),(48,252,NULL,'public'),(47,252,NULL,'10.42.2.119'),(53,253,NULL,''),(52,253,NULL,''),(51,253,NULL,'500'),(50,253,NULL,'161'),(49,253,NULL,'2'),(48,253,NULL,'public'),(47,253,NULL,'10.42.2.119'),(53,254,NULL,''),(52,254,NULL,''),(51,254,NULL,'500'),(50,254,NULL,'161'),(49,254,NULL,'2'),(48,254,NULL,'public'),(47,254,NULL,'10.42.2.120'),(53,255,NULL,''),(52,255,NULL,''),(51,255,NULL,'500'),(50,255,NULL,'161'),(49,255,NULL,'2'),(48,255,NULL,'public'),(47,255,NULL,'10.42.2.120'),(53,256,NULL,''),(52,256,NULL,''),(51,256,NULL,'500'),(50,256,NULL,'161'),(49,256,NULL,'2'),(48,256,NULL,'public'),(47,256,NULL,'10.42.2.120'),(53,257,NULL,''),(52,257,NULL,''),(51,257,NULL,'500'),(50,257,NULL,'161'),(49,257,NULL,'2'),(48,257,NULL,'public'),(47,257,NULL,'10.42.2.120'),(53,258,NULL,''),(52,258,NULL,''),(51,258,NULL,'500'),(50,258,NULL,'161'),(49,258,NULL,'2'),(48,258,NULL,'public'),(47,258,NULL,'10.42.2.121'),(53,259,NULL,''),(52,259,NULL,''),(51,259,NULL,'500'),(50,259,NULL,'161'),(49,259,NULL,'2'),(48,259,NULL,'public'),(47,259,NULL,'10.42.2.121'),(53,260,NULL,''),(52,260,NULL,''),(51,260,NULL,'500'),(50,260,NULL,'161'),(49,260,NULL,'2'),(48,260,NULL,'public'),(47,260,NULL,'10.42.2.121'),(53,261,NULL,''),(52,261,NULL,''),(51,261,NULL,'500'),(50,261,NULL,'161'),(49,261,NULL,'2'),(48,261,NULL,'public'),(47,261,NULL,'10.42.2.121'),(53,262,NULL,''),(52,262,NULL,''),(51,262,NULL,'500'),(50,262,NULL,'161'),(49,262,NULL,'2'),(48,262,NULL,'public'),(47,262,NULL,'10.42.2.130'),(53,263,NULL,''),(52,263,NULL,''),(51,263,NULL,'500'),(50,263,NULL,'161'),(49,263,NULL,'2'),(48,263,NULL,'public'),(47,263,NULL,'10.42.2.130'),(53,264,NULL,''),(52,264,NULL,''),(51,264,NULL,'500'),(50,264,NULL,'161'),(49,264,NULL,'2'),(48,264,NULL,'public'),(47,264,NULL,'10.42.2.130'),(53,265,NULL,''),(52,265,NULL,''),(51,265,NULL,'500'),(50,265,NULL,'161'),(49,265,NULL,'2'),(48,265,NULL,'public'),(47,265,NULL,'10.42.2.130'),(53,266,NULL,''),(52,266,NULL,''),(51,266,NULL,'500'),(50,266,NULL,'161'),(49,266,NULL,'2'),(48,266,NULL,'public'),(47,266,NULL,'10.42.2.131'),(53,267,NULL,''),(52,267,NULL,''),(51,267,NULL,'500'),(50,267,NULL,'161'),(49,267,NULL,'2'),(48,267,NULL,'public'),(47,267,NULL,'10.42.2.131'),(53,268,NULL,''),(52,268,NULL,''),(51,268,NULL,'500'),(50,268,NULL,'161'),(49,268,NULL,'2'),(48,268,NULL,'public'),(47,268,NULL,'10.42.2.131'),(53,269,NULL,''),(52,269,NULL,''),(51,269,NULL,'500'),(50,269,NULL,'161'),(49,269,NULL,'2'),(48,269,NULL,'public'),(47,269,NULL,'10.42.2.131'),(53,270,NULL,''),(52,270,NULL,''),(51,270,NULL,'500'),(50,270,NULL,'161'),(49,270,NULL,'2'),(48,270,NULL,'public'),(47,270,NULL,'10.42.2.132'),(53,271,NULL,''),(52,271,NULL,''),(51,271,NULL,'500'),(50,271,NULL,'161'),(49,271,NULL,'2'),(48,271,NULL,'public'),(47,271,NULL,'10.42.2.132'),(53,272,NULL,''),(52,272,NULL,''),(51,272,NULL,'500'),(50,272,NULL,'161'),(49,272,NULL,'2'),(48,272,NULL,'public'),(47,272,NULL,'10.42.2.132'),(53,273,NULL,''),(52,273,NULL,''),(51,273,NULL,'500'),(50,273,NULL,'161'),(49,273,NULL,'2'),(48,273,NULL,'public'),(47,273,NULL,'10.42.2.132'),(53,274,NULL,''),(52,274,NULL,''),(51,274,NULL,'500'),(50,274,NULL,'161'),(49,274,NULL,'2'),(48,274,NULL,'public'),(47,274,NULL,'10.42.2.133'),(53,275,NULL,''),(52,275,NULL,''),(51,275,NULL,'500'),(50,275,NULL,'161'),(49,275,NULL,'2'),(48,275,NULL,'public'),(47,275,NULL,'10.42.2.133'),(53,276,NULL,''),(52,276,NULL,''),(51,276,NULL,'500'),(50,276,NULL,'161'),(49,276,NULL,'2'),(48,276,NULL,'public'),(47,276,NULL,'10.42.2.133'),(53,277,NULL,''),(52,277,NULL,''),(51,277,NULL,'500'),(50,277,NULL,'161'),(49,277,NULL,'2'),(48,277,NULL,'public'),(47,277,NULL,'10.42.2.133'),(53,278,NULL,''),(52,278,NULL,''),(51,278,NULL,'500'),(50,278,NULL,'161'),(49,278,NULL,'2'),(48,278,NULL,'public'),(47,278,NULL,'10.42.2.132'),(53,279,NULL,''),(52,279,NULL,''),(51,279,NULL,'500'),(50,279,NULL,'161'),(49,279,NULL,'2'),(48,279,NULL,'public'),(47,279,NULL,'10.42.2.132'),(53,280,NULL,''),(52,280,NULL,''),(51,280,NULL,'500'),(50,280,NULL,'161'),(49,280,NULL,'2'),(48,280,NULL,'public'),(47,280,NULL,'10.42.2.132'),(53,281,NULL,''),(52,281,NULL,''),(51,281,NULL,'500'),(50,281,NULL,'161'),(49,281,NULL,'2'),(48,281,NULL,'public'),(47,281,NULL,'10.42.2.132'),(53,286,NULL,''),(52,286,NULL,''),(51,286,NULL,'500'),(50,286,NULL,'161'),(49,286,NULL,'2'),(48,286,NULL,'public'),(47,286,NULL,'10.42.2.134'),(53,287,NULL,''),(52,287,NULL,''),(51,287,NULL,'500'),(50,287,NULL,'161'),(49,287,NULL,'2'),(48,287,NULL,'public'),(47,287,NULL,'10.42.2.134'),(53,288,NULL,''),(52,288,NULL,''),(51,288,NULL,'500'),(50,288,NULL,'161'),(49,288,NULL,'2'),(48,288,NULL,'public'),(47,288,NULL,'10.42.2.134'),(53,289,NULL,''),(52,289,NULL,''),(51,289,NULL,'500'),(50,289,NULL,'161'),(49,289,NULL,'2'),(48,289,NULL,'public'),(47,289,NULL,'10.42.2.134'),(53,290,NULL,''),(52,290,NULL,''),(51,290,NULL,'500'),(50,290,NULL,'161'),(49,290,NULL,'2'),(48,290,NULL,'public'),(47,290,NULL,'10.42.2.135'),(53,291,NULL,''),(52,291,NULL,''),(51,291,NULL,'500'),(50,291,NULL,'161'),(49,291,NULL,'2'),(48,291,NULL,'public'),(47,291,NULL,'10.42.2.135'),(53,292,NULL,''),(52,292,NULL,''),(51,292,NULL,'500'),(50,292,NULL,'161'),(49,292,NULL,'2'),(48,292,NULL,'public'),(47,292,NULL,'10.42.2.135'),(53,293,NULL,''),(52,293,NULL,''),(51,293,NULL,'500'),(50,293,NULL,'161'),(49,293,NULL,'2'),(48,293,NULL,'public'),(47,293,NULL,'10.42.2.135'),(53,294,NULL,''),(52,294,NULL,''),(51,294,NULL,'500'),(50,294,NULL,'161'),(49,294,NULL,'2'),(48,294,NULL,'public'),(47,294,NULL,'10.42.2.10'),(53,295,NULL,''),(52,295,NULL,''),(51,295,NULL,'500'),(50,295,NULL,'161'),(49,295,NULL,'2'),(48,295,NULL,'public'),(47,295,NULL,'10.42.2.10'),(53,296,NULL,''),(52,296,NULL,''),(51,296,NULL,'500'),(50,296,NULL,'161'),(49,296,NULL,'2'),(48,296,NULL,'public'),(47,296,NULL,'10.42.2.10'),(53,297,NULL,''),(52,297,NULL,''),(51,297,NULL,'500'),(50,297,NULL,'161'),(49,297,NULL,'2'),(48,297,NULL,'public'),(47,297,NULL,'10.42.2.10'),(53,298,NULL,''),(52,298,NULL,''),(51,298,NULL,'500'),(50,298,NULL,'161'),(49,298,NULL,'2'),(48,298,NULL,'public'),(47,298,NULL,'10.42.2.35'),(53,299,NULL,''),(52,299,NULL,''),(51,299,NULL,'500'),(50,299,NULL,'161'),(49,299,NULL,'2'),(48,299,NULL,'public'),(47,299,NULL,'10.42.2.35'),(53,300,NULL,''),(52,300,NULL,''),(51,300,NULL,'500'),(50,300,NULL,'161'),(49,300,NULL,'2'),(48,300,NULL,'public'),(47,300,NULL,'10.42.2.35'),(53,301,NULL,''),(52,301,NULL,''),(51,301,NULL,'500'),(50,301,NULL,'161'),(49,301,NULL,'2'),(48,301,NULL,'public'),(47,301,NULL,'10.42.2.35'),(53,306,NULL,''),(52,306,NULL,''),(51,306,NULL,'500'),(50,306,NULL,'161'),(49,306,NULL,'2'),(48,306,NULL,'public'),(47,306,NULL,'10.42.2.100'),(53,307,NULL,''),(52,307,NULL,''),(51,307,NULL,'500'),(50,307,NULL,'161'),(49,307,NULL,'2'),(48,307,NULL,'public'),(47,307,NULL,'10.42.2.100'),(53,308,NULL,''),(52,308,NULL,''),(51,308,NULL,'500'),(50,308,NULL,'161'),(49,308,NULL,'2'),(48,308,NULL,'public'),(47,308,NULL,'10.42.2.100'),(53,309,NULL,''),(52,309,NULL,''),(51,309,NULL,'500'),(50,309,NULL,'161'),(49,309,NULL,'2'),(48,309,NULL,'public'),(47,309,NULL,'10.42.2.100'),(53,310,NULL,''),(52,310,NULL,''),(51,310,NULL,'500'),(50,310,NULL,'161'),(49,310,NULL,'2'),(48,310,NULL,'public'),(47,310,NULL,'10.42.2.101'),(53,311,NULL,''),(52,311,NULL,''),(51,311,NULL,'500'),(50,311,NULL,'161'),(49,311,NULL,'2'),(48,311,NULL,'public'),(47,311,NULL,'10.42.2.101'),(53,312,NULL,''),(52,312,NULL,''),(51,312,NULL,'500'),(50,312,NULL,'161'),(49,312,NULL,'2'),(48,312,NULL,'public'),(47,312,NULL,'10.42.2.101'),(53,313,NULL,''),(52,313,NULL,''),(51,313,NULL,'500'),(50,313,NULL,'161'),(49,313,NULL,'2'),(48,313,NULL,'public'),(47,313,NULL,'10.42.2.101'),(53,314,NULL,''),(52,314,NULL,''),(51,314,NULL,'500'),(50,314,NULL,'161'),(49,314,NULL,'2'),(48,314,NULL,'public'),(47,314,NULL,'10.42.2.71'),(53,315,NULL,''),(52,315,NULL,''),(51,315,NULL,'500'),(50,315,NULL,'161'),(49,315,NULL,'2'),(48,315,NULL,'public'),(47,315,NULL,'10.42.2.71'),(53,316,NULL,''),(52,316,NULL,''),(51,316,NULL,'500'),(50,316,NULL,'161'),(49,316,NULL,'2'),(48,316,NULL,'public'),(47,316,NULL,'10.42.2.71'),(53,317,NULL,''),(52,317,NULL,''),(51,317,NULL,'500'),(50,317,NULL,'161'),(49,317,NULL,'2'),(48,317,NULL,'public'),(47,317,NULL,'10.42.2.71'),(53,318,NULL,''),(52,318,NULL,''),(51,318,NULL,'500'),(50,318,NULL,'161'),(49,318,NULL,'2'),(48,318,NULL,'public'),(47,318,NULL,'10.42.2.72'),(53,319,NULL,''),(52,319,NULL,''),(51,319,NULL,'500'),(50,319,NULL,'161'),(49,319,NULL,'2'),(48,319,NULL,'public'),(47,319,NULL,'10.42.2.72'),(53,320,NULL,''),(52,320,NULL,''),(51,320,NULL,'500'),(50,320,NULL,'161'),(49,320,NULL,'2'),(48,320,NULL,'public'),(47,320,NULL,'10.42.2.72'),(53,321,NULL,''),(52,321,NULL,''),(51,321,NULL,'500'),(50,321,NULL,'161'),(49,321,NULL,'2'),(48,321,NULL,'public'),(47,321,NULL,'10.42.2.72'),(53,322,NULL,''),(52,322,NULL,''),(51,322,NULL,'500'),(50,322,NULL,'161'),(49,322,NULL,'2'),(48,322,NULL,'public'),(47,322,NULL,'10.42.2.79'),(53,323,NULL,''),(52,323,NULL,''),(51,323,NULL,'500'),(50,323,NULL,'161'),(49,323,NULL,'2'),(48,323,NULL,'public'),(47,323,NULL,'10.42.2.79'),(53,324,NULL,''),(52,324,NULL,''),(51,324,NULL,'500'),(50,324,NULL,'161'),(49,324,NULL,'2'),(48,324,NULL,'public'),(47,324,NULL,'10.42.2.79'),(53,325,NULL,''),(52,325,NULL,''),(51,325,NULL,'500'),(50,325,NULL,'161'),(49,325,NULL,'2'),(48,325,NULL,'public'),(47,325,NULL,'10.42.2.79'),(53,326,NULL,''),(52,326,NULL,''),(51,326,NULL,'500'),(50,326,NULL,'161'),(49,326,NULL,'2'),(48,326,NULL,'public'),(47,326,NULL,'10.42.2.80'),(53,327,NULL,''),(52,327,NULL,''),(51,327,NULL,'500'),(50,327,NULL,'161'),(49,327,NULL,'2'),(48,327,NULL,'public'),(47,327,NULL,'10.42.2.80'),(53,328,NULL,''),(52,328,NULL,''),(51,328,NULL,'500'),(50,328,NULL,'161'),(49,328,NULL,'2'),(48,328,NULL,'public'),(47,328,NULL,'10.42.2.80'),(53,329,NULL,''),(52,329,NULL,''),(51,329,NULL,'500'),(50,329,NULL,'161'),(49,329,NULL,'2'),(48,329,NULL,'public'),(47,329,NULL,'10.42.2.80'),(53,330,NULL,''),(52,330,NULL,''),(51,330,NULL,'500'),(50,330,NULL,'161'),(49,330,NULL,'2'),(48,330,NULL,'public'),(47,330,NULL,'10.42.2.51'),(53,331,NULL,''),(52,331,NULL,''),(51,331,NULL,'500'),(50,331,NULL,'161'),(49,331,NULL,'2'),(48,331,NULL,'public'),(47,331,NULL,'10.42.2.51'),(53,332,NULL,''),(52,332,NULL,''),(51,332,NULL,'500'),(50,332,NULL,'161'),(49,332,NULL,'2'),(48,332,NULL,'public'),(47,332,NULL,'10.42.2.51'),(53,333,NULL,''),(52,333,NULL,''),(51,333,NULL,'500'),(50,333,NULL,'161'),(49,333,NULL,'2'),(48,333,NULL,'public'),(47,333,NULL,'10.42.2.51'),(53,334,NULL,''),(52,334,NULL,''),(51,334,NULL,'500'),(50,334,NULL,'161'),(49,334,NULL,'2'),(48,334,NULL,'public'),(47,334,NULL,'10.42.2.52'),(53,335,NULL,''),(52,335,NULL,''),(51,335,NULL,'500'),(50,335,NULL,'161'),(49,335,NULL,'2'),(48,335,NULL,'public'),(47,335,NULL,'10.42.2.52'),(53,336,NULL,''),(52,336,NULL,''),(51,336,NULL,'500'),(50,336,NULL,'161'),(49,336,NULL,'2'),(48,336,NULL,'public'),(47,336,NULL,'10.42.2.52'),(53,337,NULL,''),(52,337,NULL,''),(51,337,NULL,'500'),(50,337,NULL,'161'),(49,337,NULL,'2'),(48,337,NULL,'public'),(47,337,NULL,'10.42.2.52'),(53,338,NULL,''),(52,338,NULL,''),(51,338,NULL,'500'),(50,338,NULL,'161'),(49,338,NULL,'2'),(48,338,NULL,'public'),(47,338,NULL,'10.42.2.52'),(53,339,NULL,''),(52,339,NULL,''),(51,339,NULL,'500'),(50,339,NULL,'161'),(49,339,NULL,'2'),(48,339,NULL,'public'),(47,339,NULL,'10.42.2.52'),(53,340,NULL,''),(52,340,NULL,''),(51,340,NULL,'500'),(50,340,NULL,'161'),(49,340,NULL,'2'),(48,340,NULL,'public'),(47,340,NULL,'10.42.2.52'),(53,341,NULL,''),(52,341,NULL,''),(51,341,NULL,'500'),(50,341,NULL,'161'),(49,341,NULL,'2'),(48,341,NULL,'public'),(47,341,NULL,'10.42.2.52'),(53,342,NULL,''),(52,342,NULL,''),(51,342,NULL,'500'),(50,342,NULL,'161'),(49,342,NULL,'2'),(48,342,NULL,'public'),(47,342,NULL,'10.42.2.53'),(53,343,NULL,''),(52,343,NULL,''),(51,343,NULL,'500'),(50,343,NULL,'161'),(49,343,NULL,'2'),(48,343,NULL,'public'),(47,343,NULL,'10.42.2.53'),(53,344,NULL,''),(52,344,NULL,''),(51,344,NULL,'500'),(50,344,NULL,'161'),(49,344,NULL,'2'),(48,344,NULL,'public'),(47,344,NULL,'10.42.2.53'),(53,345,NULL,''),(52,345,NULL,''),(51,345,NULL,'500'),(50,345,NULL,'161'),(49,345,NULL,'2'),(48,345,NULL,'public'),(47,345,NULL,'10.42.2.53'),(53,346,NULL,''),(52,346,NULL,''),(51,346,NULL,'500'),(50,346,NULL,'161'),(49,346,NULL,'2'),(48,346,NULL,'public'),(47,346,NULL,'10.42.2.45'),(53,347,NULL,''),(52,347,NULL,''),(51,347,NULL,'500'),(50,347,NULL,'161'),(49,347,NULL,'2'),(48,347,NULL,'public'),(47,347,NULL,'10.42.2.45'),(53,348,NULL,''),(52,348,NULL,''),(51,348,NULL,'500'),(50,348,NULL,'161'),(49,348,NULL,'2'),(48,348,NULL,'public'),(47,348,NULL,'10.42.2.45'),(53,349,NULL,''),(52,349,NULL,''),(51,349,NULL,'500'),(50,349,NULL,'161'),(49,349,NULL,'2'),(48,349,NULL,'public'),(47,349,NULL,'10.42.2.45'),(53,350,NULL,''),(52,350,NULL,''),(51,350,NULL,'500'),(50,350,NULL,'161'),(49,350,NULL,'2'),(48,350,NULL,'public'),(47,350,NULL,'10.42.2.46'),(53,351,NULL,''),(52,351,NULL,''),(51,351,NULL,'500'),(50,351,NULL,'161'),(49,351,NULL,'2'),(48,351,NULL,'public'),(47,351,NULL,'10.42.2.46'),(53,352,NULL,''),(52,352,NULL,''),(51,352,NULL,'500'),(50,352,NULL,'161'),(49,352,NULL,'2'),(48,352,NULL,'public'),(47,352,NULL,'10.42.2.46'),(53,353,NULL,''),(52,353,NULL,''),(51,353,NULL,'500'),(50,353,NULL,'161'),(49,353,NULL,'2'),(48,353,NULL,'public'),(47,353,NULL,'10.42.2.46'),(53,354,NULL,''),(52,354,NULL,''),(51,354,NULL,'500'),(50,354,NULL,'161'),(49,354,NULL,'2'),(48,354,NULL,'public'),(47,354,NULL,'10.42.2.30'),(53,355,NULL,''),(52,355,NULL,''),(51,355,NULL,'500'),(50,355,NULL,'161'),(49,355,NULL,'2'),(48,355,NULL,'public'),(47,355,NULL,'10.42.2.30'),(53,356,NULL,''),(52,356,NULL,''),(51,356,NULL,'500'),(50,356,NULL,'161'),(49,356,NULL,'2'),(48,356,NULL,'public'),(47,356,NULL,'10.42.2.30'),(53,357,NULL,''),(52,357,NULL,''),(51,357,NULL,'500'),(50,357,NULL,'161'),(49,357,NULL,'2'),(48,357,NULL,'public'),(47,357,NULL,'10.42.2.30'),(53,358,NULL,''),(52,358,NULL,''),(51,358,NULL,'500'),(50,358,NULL,'161'),(49,358,NULL,'2'),(48,358,NULL,'public'),(47,358,NULL,'10.42.2.31'),(53,359,NULL,''),(52,359,NULL,''),(51,359,NULL,'500'),(50,359,NULL,'161'),(49,359,NULL,'2'),(48,359,NULL,'public'),(47,359,NULL,'10.42.2.31'),(53,360,NULL,''),(52,360,NULL,''),(51,360,NULL,'500'),(50,360,NULL,'161'),(49,360,NULL,'2'),(48,360,NULL,'public'),(47,360,NULL,'10.42.2.31'),(53,361,NULL,''),(52,361,NULL,''),(51,361,NULL,'500'),(50,361,NULL,'161'),(49,361,NULL,'2'),(48,361,NULL,'public'),(47,361,NULL,'10.42.2.31'),(53,362,NULL,''),(52,362,NULL,''),(51,362,NULL,'500'),(50,362,NULL,'161'),(49,362,NULL,'2'),(48,362,NULL,'public'),(47,362,NULL,'cm-100018first.domain.com'),(53,363,NULL,''),(52,363,NULL,''),(51,363,NULL,'500'),(50,363,NULL,'161'),(49,363,NULL,'2'),(48,363,NULL,'public'),(47,363,NULL,'cm-100018first.domain.com'),(53,364,NULL,''),(52,364,NULL,''),(51,364,NULL,'500'),(50,364,NULL,'161'),(49,364,NULL,'2'),(48,364,NULL,'public'),(47,364,NULL,'cm-100018first.domain.com'),(53,365,NULL,''),(52,365,NULL,''),(51,365,NULL,'500'),(50,365,NULL,'161'),(49,365,NULL,'2'),(48,365,NULL,'public'),(47,365,NULL,'cm-100018first.domain.com'),(53,366,NULL,''),(52,366,NULL,''),(51,366,NULL,'500'),(50,366,NULL,'161'),(49,366,NULL,'2'),(48,366,NULL,'public'),(47,366,NULL,'cm-100019first.domain.com'),(53,367,NULL,''),(52,367,NULL,''),(51,367,NULL,'500'),(50,367,NULL,'161'),(49,367,NULL,'2'),(48,367,NULL,'public'),(47,367,NULL,'cm-100019first.domain.com'),(53,368,NULL,''),(52,368,NULL,''),(51,368,NULL,'500'),(50,368,NULL,'161'),(49,368,NULL,'2'),(48,368,NULL,'public'),(47,368,NULL,'cm-100019first.domain.com'),(53,369,NULL,''),(52,369,NULL,''),(51,369,NULL,'500'),(50,369,NULL,'161'),(49,369,NULL,'2'),(48,369,NULL,'public'),(47,369,NULL,'cm-100019first.domain.com'),(53,370,NULL,''),(52,370,NULL,''),(51,370,NULL,'500'),(50,370,NULL,'161'),(49,370,NULL,'2'),(48,370,NULL,'public'),(47,370,NULL,'cm-100018.first.domain.com'),(53,371,NULL,''),(52,371,NULL,''),(51,371,NULL,'500'),(50,371,NULL,'161'),(49,371,NULL,'2'),(48,371,NULL,'public'),(47,371,NULL,'cm-100018.first.domain.com'),(53,372,NULL,''),(52,372,NULL,''),(51,372,NULL,'500'),(50,372,NULL,'161'),(49,372,NULL,'2'),(48,372,NULL,'public'),(47,372,NULL,'cm-100018.first.domain.com'),(53,373,NULL,''),(52,373,NULL,''),(51,373,NULL,'500'),(50,373,NULL,'161'),(49,373,NULL,'2'),(48,373,NULL,'public'),(47,373,NULL,'cm-100018.first.domain.com'),(53,374,NULL,''),(52,374,NULL,''),(51,374,NULL,'500'),(50,374,NULL,'161'),(49,374,NULL,'2'),(48,374,NULL,'public'),(47,374,NULL,'cm-100019.first.domain.com'),(53,375,NULL,''),(52,375,NULL,''),(51,375,NULL,'500'),(50,375,NULL,'161'),(49,375,NULL,'2'),(48,375,NULL,'public'),(47,375,NULL,'cm-100019.first.domain.com'),(53,376,NULL,''),(52,376,NULL,''),(51,376,NULL,'500'),(50,376,NULL,'161'),(49,376,NULL,'2'),(48,376,NULL,'public'),(47,376,NULL,'cm-100019.first.domain.com'),(53,377,NULL,''),(52,377,NULL,''),(51,377,NULL,'500'),(50,377,NULL,'161'),(49,377,NULL,'2'),(48,377,NULL,'public'),(47,377,NULL,'cm-100019.first.domain.com'),(53,382,NULL,''),(52,382,NULL,''),(51,382,NULL,'500'),(50,382,NULL,'161'),(49,382,NULL,'2'),(48,382,NULL,'public'),(47,382,NULL,'10.42.2.222'),(53,383,NULL,''),(52,383,NULL,''),(51,383,NULL,'500'),(50,383,NULL,'161'),(49,383,NULL,'2'),(48,383,NULL,'public'),(47,383,NULL,'10.42.2.222'),(53,384,NULL,''),(52,384,NULL,''),(51,384,NULL,'500'),(50,384,NULL,'161'),(49,384,NULL,'2'),(48,384,NULL,'public'),(47,384,NULL,'10.42.2.222'),(53,385,NULL,''),(52,385,NULL,''),(51,385,NULL,'500'),(50,385,NULL,'161'),(49,385,NULL,'2'),(48,385,NULL,'public'),(47,385,NULL,'10.42.2.222'),(53,386,NULL,''),(52,386,NULL,''),(51,386,NULL,'500'),(50,386,NULL,'161'),(49,386,NULL,'2'),(48,386,NULL,'public'),(47,386,NULL,'10.42.2.87'),(53,387,NULL,''),(52,387,NULL,''),(51,387,NULL,'500'),(50,387,NULL,'161'),(49,387,NULL,'2'),(48,387,NULL,'public'),(47,387,NULL,'10.42.2.87'),(53,388,NULL,''),(52,388,NULL,''),(51,388,NULL,'500'),(50,388,NULL,'161'),(49,388,NULL,'2'),(48,388,NULL,'public'),(47,388,NULL,'10.42.2.87'),(53,389,NULL,''),(52,389,NULL,''),(51,389,NULL,'500'),(50,389,NULL,'161'),(49,389,NULL,'2'),(48,389,NULL,'public'),(47,389,NULL,'10.42.2.87'),(53,390,NULL,''),(52,390,NULL,''),(51,390,NULL,'500'),(50,390,NULL,'161'),(49,390,NULL,'2'),(48,390,NULL,'public'),(47,390,NULL,'cm-100000.first.domain.com'),(53,391,NULL,''),(52,391,NULL,''),(51,391,NULL,'500'),(50,391,NULL,'161'),(49,391,NULL,'2'),(48,391,NULL,'public'),(47,391,NULL,'cm-100000.first.domain.com'),(53,392,NULL,''),(52,392,NULL,''),(51,392,NULL,'500'),(50,392,NULL,'161'),(49,392,NULL,'2'),(48,392,NULL,'public'),(47,392,NULL,'cm-100000.first.domain.com'),(53,393,NULL,''),(52,393,NULL,''),(51,393,NULL,'500'),(50,393,NULL,'161'),(49,393,NULL,'2'),(48,393,NULL,'public'),(47,393,NULL,'cm-100000.first.domain.com'),(53,394,NULL,''),(52,394,NULL,''),(51,394,NULL,'500'),(50,394,NULL,'161'),(49,394,NULL,'2'),(48,394,NULL,'public'),(47,394,NULL,'cm-100000.first.domain.com'),(53,395,NULL,''),(52,395,NULL,''),(51,395,NULL,'500'),(50,395,NULL,'161'),(49,395,NULL,'2'),(48,395,NULL,'public'),(47,395,NULL,'cm-100001.first.domain.com'),(53,396,NULL,''),(52,396,NULL,''),(51,396,NULL,'500'),(50,396,NULL,'161'),(49,396,NULL,'2'),(48,396,NULL,'public'),(47,396,NULL,'cm-100001.first.domain.com'),(53,397,NULL,''),(52,397,NULL,''),(51,397,NULL,'500'),(50,397,NULL,'161'),(49,397,NULL,'2'),(48,397,NULL,'public'),(47,397,NULL,'cm-100001.first.domain.com'),(53,398,NULL,''),(52,398,NULL,''),(51,398,NULL,'500'),(50,398,NULL,'161'),(49,398,NULL,'2'),(48,398,NULL,'public'),(47,398,NULL,'cm-100001.first.domain.com'),(53,399,NULL,''),(52,399,NULL,''),(51,399,NULL,'500'),(50,399,NULL,'161'),(49,399,NULL,'2'),(48,399,NULL,'public'),(47,399,NULL,'cm-100001.first.domain.com'),(9,420,'',''),(8,420,'',''),(7,420,'',''),(10,420,'',''),(11,420,'',''),(12,420,'',''),(13,420,'',''),(14,420,'',''),(39,420,'',''),(44,420,'',''),(45,420,'',''),(46,420,'',''),(46,421,'',''),(45,421,'',''),(44,421,'',''),(39,421,'',''),(14,421,'',''),(13,421,'',''),(12,421,'',''),(11,421,'',''),(10,421,'',''),(9,421,'',''),(8,421,'',''),(7,421,'',''),(46,422,'',''),(45,422,'',''),(44,422,'',''),(39,422,'',''),(14,422,'',''),(13,422,'',''),(12,422,'',''),(11,422,'',''),(10,422,'',''),(9,422,'',''),(8,422,'',''),(7,422,'',''),(46,423,'',''),(45,423,'',''),(44,423,'',''),(39,423,'',''),(14,423,'',''),(13,423,'',''),(12,423,'',''),(11,423,'',''),(10,423,'',''),(9,423,'',''),(8,423,'',''),(7,423,'',''),(46,424,'',''),(45,424,'',''),(44,424,'',''),(39,424,'',''),(14,424,'',''),(13,424,'',''),(12,424,'',''),(11,424,'',''),(10,424,'',''),(9,424,'',''),(8,424,'',''),(7,424,'',''),(7,425,'',''),(8,425,'',''),(9,425,'',''),(10,425,'',''),(11,425,'',''),(12,425,'',''),(13,425,'',''),(14,425,'',''),(39,425,'',''),(44,425,'',''),(45,425,'',''),(46,425,'',''),(46,426,'',''),(45,426,'',''),(44,426,'',''),(39,426,'',''),(14,426,'',''),(13,426,'',''),(7,426,'',''),(8,426,'',''),(9,426,'',''),(10,426,'',''),(11,426,'',''),(12,426,'',''),(46,427,'',''),(39,427,'',''),(44,427,'',''),(45,427,'',''),(14,427,'',''),(13,427,'',''),(12,427,'',''),(11,427,'',''),(10,427,'',''),(9,427,'',''),(8,427,'',''),(7,427,'',''),(46,428,'',''),(45,428,'',''),(44,428,'',''),(39,428,'',''),(14,428,'',''),(13,428,'',''),(11,428,'',''),(12,428,'',''),(10,428,'',''),(9,428,'',''),(7,428,'',''),(8,428,'',''),(46,429,'',''),(45,429,'',''),(44,429,'',''),(39,429,'',''),(14,429,'',''),(13,429,'',''),(12,429,'',''),(11,429,'',''),(10,429,'',''),(9,429,'',''),(8,429,'',''),(7,429,'',''),(46,430,'',''),(45,430,'',''),(44,430,'',''),(39,430,'',''),(14,430,'',''),(13,430,'',''),(12,430,'',''),(11,430,'',''),(10,430,'',''),(9,430,'',''),(8,430,'',''),(7,430,'','');
/*!40000 ALTER TABLE `data_input_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_input_fields`
--

DROP TABLE IF EXISTS `data_input_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_input_fields` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `data_input_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL DEFAULT '',
  `data_name` varchar(50) NOT NULL DEFAULT '',
  `input_output` char(3) NOT NULL DEFAULT '',
  `update_rra` char(2) DEFAULT '0',
  `sequence` smallint(5) NOT NULL DEFAULT '0',
  `type_code` varchar(40) DEFAULT NULL,
  `regexp_match` varchar(200) DEFAULT NULL,
  `allow_nulls` char(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_input_id` (`data_input_id`),
  KEY `type_code` (`type_code`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_input_fields`
--

LOCK TABLES `data_input_fields` WRITE;
/*!40000 ALTER TABLE `data_input_fields` DISABLE KEYS */;
INSERT INTO `data_input_fields` VALUES (1,'92f5906c8dc0f964b41f4253df582c38',1,'SNMP IP Address','management_ip','in','',0,'hostname','',''),(2,'32285d5bf16e56c478f5e83f32cda9ef',1,'SNMP Community','snmp_community','in','',0,'snmp_community','',''),(3,'ad14ac90641aed388139f6ba86a2e48b',1,'SNMP Username','snmp_username','in','',0,'snmp_username','','on'),(4,'9c55a74bd571b4f00a96fd4b793278c6',1,'SNMP Password','snmp_password','in','',0,'snmp_password','','on'),(5,'012ccb1d3687d3edb29c002ea66e72da',1,'SNMP Version (1, 2, or 3)','snmp_version','in','',0,'snmp_version','','on'),(6,'4276a5ec6e3fe33995129041b1909762',1,'OID','oid','in','',0,'snmp_oid','',''),(7,'617cdc8a230615e59f06f361ef6e7728',2,'SNMP IP Address','management_ip','in','',0,'hostname','',''),(8,'acb449d1451e8a2a655c2c99d31142c7',2,'SNMP Community','snmp_community','in','',0,'snmp_community','',''),(9,'f4facc5e2ca7ebee621f09bc6d9fc792',2,'SNMP Username (v3)','snmp_username','in','',0,'snmp_username','','on'),(10,'1cc1493a6781af2c478fa4de971531cf',2,'SNMP Password (v3)','snmp_password','in','',0,'snmp_password','','on'),(11,'b5c23f246559df38662c255f4aa21d6b',2,'SNMP Version (1, 2, or 3)','snmp_version','in','',0,'snmp_version','',''),(12,'6027a919c7c7731fbe095b6f53ab127b',2,'Index Type','index_type','in','',0,'index_type','',''),(13,'cbbe5c1ddfb264a6e5d509ce1c78c95f',2,'Index Value','index_value','in','',0,'index_value','',''),(14,'e6deda7be0f391399c5130e7c4a48b28',2,'Output Type ID','output_type','in','',0,'output_type','',''),(15,'edfd72783ad02df128ff82fc9324b4b9',3,'Disk Partition','partition','in','',1,'','',''),(16,'8b75fb61d288f0b5fc0bd3056af3689b',3,'Kilobytes Free','kilobytes','out','on',0,'','',''),(17,'363588d49b263d30aecb683c52774f39',4,'1 Minute Average','1min','out','on',0,'','',''),(18,'ad139a9e1d69881da36fca07889abf58',4,'5 Minute Average','5min','out','on',0,'','',''),(19,'5db9fee64824c08258c7ff6f8bc53337',4,'10 Minute Average','10min','out','on',0,'','',''),(20,'c0cfd0beae5e79927c5a360076706820',5,'Username (Optional)','username','in','',1,'','','on'),(21,'52c58ad414d9a2a83b00a7a51be75a53',5,'Logged In Users','users','out','on',0,'','',''),(22,'05eb5d710f0814871b8515845521f8d7',6,'Grep String','grepstr','in','',1,'','',''),(23,'86cb1cbfde66279dbc7f1144f43a3219',6,'Result (in Kilobytes)','kilobytes','out','on',0,'','',''),(24,'d5a8dd5fbe6a5af11667c0039af41386',7,'Number of Processes','proc','out','on',0,'','',''),(25,'8848cdcae831595951a3f6af04eec93b',8,'Grep String','grepstr','in','',1,'','','on'),(26,'3d1288d33008430ce354e8b9c162f7ff',8,'Connections','connections','out','on',0,'','',''),(27,'c6af570bb2ed9c84abf32033702e2860',9,'(Optional) Log Path','log_path','in','',1,'','','on'),(28,'f9389860f5c5340c9b27fca0b4ee5e71',9,'Web Hits','webhits','out','on',0,'','',''),(29,'5fbadb91ad66f203463c1187fe7bd9d5',10,'IP Address','ip','in','',1,'hostname','',''),(30,'6ac4330d123c69067d36a933d105e89a',10,'Milliseconds','out_ms','out','on',0,'','',''),(31,'d39556ecad6166701bfb0e28c5a11108',11,'Index Type','index_type','in','',0,'index_type','',''),(32,'3b7caa46eb809fc238de6ef18b6e10d5',11,'Index Value','index_value','in','',0,'index_value','',''),(33,'74af2e42dc12956c4817c2ef5d9983f9',11,'Output Type ID','output_type','in','',0,'output_type','',''),(34,'8ae57f09f787656bf4ac541e8bd12537',11,'Output Value','output','out','on',0,'','',''),(35,'172b4b0eacee4948c6479f587b62e512',12,'Index Type','index_type','in','',0,'index_type','',''),(36,'30fb5d5bcf3d66bb5abe88596f357c26',12,'Index Value','index_value','in','',0,'index_value','',''),(37,'31112c85ae4ff821d3b288336288818c',12,'Output Type ID','output_type','in','',0,'output_type','',''),(38,'5be8fa85472d89c621790b43510b5043',12,'Output Value','output','out','on',0,'','',''),(39,'c1f36ee60c3dc98945556d57f26e475b',2,'SNMP Port','snmp_port','in','',0,'snmp_port','',''),(40,'fc64b99742ec417cc424dbf8c7692d36',1,'SNMP Port','snmp_port','in','',0,'snmp_port','',''),(41,'20832ce12f099c8e54140793a091af90',1,'SNMP Authenticaion Protocol (v3)','snmp_auth_protocol','in','',0,'snmp_auth_protocol','',''),(42,'c60c9aac1e1b3555ea0620b8bbfd82cb',1,'SNMP Privacy Passphrase (v3)','snmp_priv_passphrase','in','',0,'snmp_priv_passphrase','',''),(43,'feda162701240101bc74148415ef415a',1,'SNMP Privacy Protocol (v3)','snmp_priv_protocol','in','',0,'snmp_priv_protocol','',''),(44,'2cf7129ad3ff819a7a7ac189bee48ce8',2,'SNMP Authenticaion Protocol (v3)','snmp_auth_protocol','in','',0,'snmp_auth_protocol','',''),(45,'6b13ac0a0194e171d241d4b06f913158',2,'SNMP Privacy Passphrase (v3)','snmp_priv_passphrase','in','',0,'snmp_priv_passphrase','',''),(46,'3a33d4fc65b8329ab2ac46a36da26b72',2,'SNMP Privacy Protocol (v3)','snmp_priv_protocol','in','',0,'snmp_priv_protocol','',''),(47,'54b0258aa72ce0e561d840d5bbcbbf5b',13,'Hostname','hostname','in','',1,'hostname','',''),(48,'0cca9da37b1689195cd19aa1ad880a40',13,'SNMP Community','snmp_community','in','',2,'snmp_community','',''),(49,'2bfe454940cb1bcc76301e5221c282bf',13,'SNMP Version','snmp_version','in','',3,'snmp_version','',''),(50,'b856e04de8385c03b19c56c6d9bc549a',13,'SNMP Port','snmp_port','in','',4,'snmp_port','',''),(51,'ba2fac84796b9c4b6cd9ffdcd851485e',13,'SNMP Timeout','snmp_timeout','in','',5,'snmp_timeout','',''),(52,'2d449f5a02e1843f3ec8fab9e8ca8d93',13,'SNMPv3 Username','snmpv3_auth_username','in','',6,'snmp_username','',''),(53,'db258decdfad7b537bfe937c5d92c0a2',13,'SNMPv3 Password','snmpv3_auth_password','in','',7,'snmp_password','',''),(54,'41e426f1b0d7a4a9ace38978e7178f1e',13,'Downstream Power','docsIfDownChannelPower','out','on',0,'','',''),(55,'67d5f8eec652d2f77a14cbf7927c4c9e',13,'Signal / Noise Ratio','docsIfSigQSignalNoise','out','on',0,'','',''),(56,'7ae7746ceddaacc3187e989ee08aad02',13,'Microreflections','docsIfSigQMicroreflections','out','on',0,'','',''),(57,'f9cc15f788d5008652b044c714b3c40b',13,'Ranging Timeout','docsIfCmRangingTimeout','out','on',0,'','',''),(58,'6e1e0a917bfe51d7f02eea528df0048f',13,'Transmit Power','docsIfCmStatusTxPower','out','on',0,'','',''),(59,'73f08799881e0ace389521747baae6af',13,'Reset or initialized the interface','docsIfCmStatusResets','out','on',0,'','',''),(60,'616c622dfe145542f26ee17409ad7eb3',13,'Lost synchronization with the downstream channel.','docsIfCmStatusLostSyncs','out','on',0,'','',''),(61,'1c9730192582ce7e7b518d7c4b5305c8',13,'T1 Timeouts (not receiving a valid upstream channel descriptor (UCD) from the CMTS within the specified time)','docsIfCmStatusT1Timeouts','out','on',0,'','',''),(62,'af77a63b608dcb8d252e4a8017053b52',13,'T2 Timeouts (Initial ranging opportunities are not being received.)','docsIfCmStatusT2Timeouts','out','on',0,'','',''),(63,'2b6d01eb186318571ca1160870e64189',13,'T3 Timeouts (CMTS failed too many times to respond to a RNG-REQ message)','docsIfCmStatusT3Timeouts','out','on',0,'','',''),(64,'b5000b12da214a6cd3b110b724a792d2',13,'T4 Timeouts (Maintenance ranging opportunities for this cable modem are not being received from the CMTS)','docsIfCmStatusT4Timeouts','out','on',0,'','','');
/*!40000 ALTER TABLE `data_input_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_local`
--

DROP TABLE IF EXISTS `data_local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_local` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `snmp_query_id` mediumint(8) NOT NULL DEFAULT '0',
  `snmp_index` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=357 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_local`
--

LOCK TABLES `data_local` WRITE;
/*!40000 ALTER TABLE `data_local` DISABLE KEYS */;
INSERT INTO `data_local` VALUES (3,13,1,0,''),(4,15,1,0,''),(5,11,1,0,''),(6,17,1,0,''),(7,16,1,0,'');
/*!40000 ALTER TABLE `data_local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_template`
--

DROP TABLE IF EXISTS `data_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_template`
--

LOCK TABLES `data_template` WRITE;
/*!40000 ALTER TABLE `data_template` DISABLE KEYS */;
INSERT INTO `data_template` VALUES (3,'c8a8f50f5f4a465368222594c5709ede','ucd/net - Hard Drive Space'),(4,'cdfed2d401723d2f41fc239d4ce249c7','ucd/net - CPU Usage - System'),(5,'a27e816377d2ac6434a87c494559c726','ucd/net - CPU Usage - User'),(6,'c06c3d20eccb9598939dc597701ff574','ucd/net - CPU Usage - Nice'),(7,'a14f2d6f233b05e64263ff03a5b0b386','Karlnet - Noise Level'),(8,'def1a9019d888ed2ad2e106aa9595ede','Karlnet - Signal Level'),(9,'513a99ae3c9c4413609c1534ffc36eab','Karlnet - Wireless Transmits'),(10,'77404ae93c9cc410f1c2c717e7117378','Karlnet - Wireless Re-Transmits'),(11,'9e72511e127de200733eb502eb818e1d','Unix - Load Average'),(13,'dc33aa9a8e71fb7c61ec0e7a6da074aa','Linux - Memory - Free'),(15,'41f55087d067142d702dd3c73c98f020','Linux - Memory - Free Swap'),(16,'9b8c92d3c32703900ff7dd653bfc9cd8','Unix - Processes'),(17,'c221c2164c585b6da378013a7a6a2c13','Unix - Logged in Users'),(18,'a30a81cb1de65b52b7da542c8df3f188','Unix - Ping Host'),(19,'0de466a1b81dfe581d44ac014b86553a','Netware - Total Users'),(20,'bbe2da0708103029fbf949817d3a4537','Netware - Total Logins'),(22,'e4ac5d5fe73e3c773671c6d0498a8d9d','Netware - File System Reads'),(23,'f29f8c998425eedd249be1e7caf90ceb','Netware - File System Writes'),(24,'7a6216a113e19881e35565312db8a371','Netware - Cache Checks'),(25,'1dbd1251c8e94b334c0e6aeae5ca4b8d','Netware - Cache Hits'),(26,'1a4c5264eb27b5e57acd3160af770a61','Netware - Open Files'),(27,'e9def3a0e409f517cb804dfeba4ccd90','Cisco Router - 5 Minute CPU'),(30,'9b82d44eb563027659683765f92c9757','ucd/net - Load Average - 1 Minute'),(31,'87847714d19f405ff3c74f3341b3f940','ucd/net - Load Average - 5 Minute'),(32,'308ac157f24e2763f8cd828a80b3e5ff','ucd/net - Load Average - 15 Minute'),(33,'797a3e92b0039841b52e441a2823a6fb','ucd/net - Memory - Buffers'),(34,'fa15932d3cab0da2ab94c69b1a9f5ca7','ucd/net - Memory - Free'),(35,'6ce4ab04378f9f3b03ee0623abb6479f','Netware - Volumes'),(36,'03060555fab086b8412bbf9951179cd9','Netware - Directory Entries'),(37,'e4ac6919d4f6f21ec5b281a1d6ac4d4e','Unix - Hard Drive Space'),(38,'36335cd98633963a575b70639cd2fdad','Interface - Errors/Discards'),(39,'2f654f7d69ac71a5d56b1db8543ccad3','Interface - Unicast Packets'),(40,'c84e511401a747409053c90ba910d0fe','Interface - Non-Unicast Packets'),(41,'6632e1e0b58a565c135d7ff90440c335','Interface - Traffic'),(42,'1d17325f416b262921a0b55fe5f7e31d','Netware - CPU Utilization'),(43,'d814fa3b79bd0f8933b6e0834d3f16d0','Host MIB - Hard Drive Space'),(44,'f6e7d21c19434666bbdac00ccef9932f','Host MIB - CPU Utilization'),(45,'f383db441d1c246cff8482f15e184e5f','Host MIB - Processes'),(46,'2ef027cc76d75720ee5f7a528f0f1fda','Host MIB - Logged in Users'),(47,'a274deec1f78654dca6c446ba75ebca4','ucd/net - Memory - Cache'),(48,'d429e4a6019c91e6e84562593c1968ca','SNMP - Generic OID Template'),(49,'e0f657392fd959d4c3e5fe99ca9f84e4','DOCSIS Stats'),(50,'1bffd23019ab495f83a47552dc3355d2','CMTS - SNR'),(51,'5ce5c31cd18b221fc1b32304c236dcda','CMTS - Active CM'),(52,'328fa0a66c7f6684c68bdf7bc63fb293','CMTS - Total CM'),(53,'2b396933d461091e7e05f7d41399ccf8','CMTS - ModProf'),(54,'4cd0f62267f761e9d9b1447806516d6a','CMTS - Power'),(55,'934e6805b601a5c8829e8730145c59dd','CMTS - Usage'),(56,'911aef94efb433725ba503b056eff9a9','CMTS - DS Total CM'),(57,'ebf601ef00038715695b28130330396c','CMTS - DS Active CM'),(58,'b0d2bf2cfbf65addcbbc981b55a0e5d7','CMTS - QUnerroreds'),(59,'0523ac6858cf6edd85f9835c87c4d7a0','CMTS - QCorrecteds'),(60,'5baca19c4068543f8ab04431c5045bb0','CMTS - QUncorrectables');
/*!40000 ALTER TABLE `data_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_template_data`
--

DROP TABLE IF EXISTS `data_template_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_template_data` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `local_data_template_data_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `local_data_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_input_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `t_name` char(2) DEFAULT NULL,
  `name` varchar(250) NOT NULL DEFAULT '',
  `name_cache` varchar(255) NOT NULL DEFAULT '',
  `data_source_path` varchar(255) DEFAULT NULL,
  `t_active` char(2) DEFAULT NULL,
  `active` char(2) DEFAULT NULL,
  `t_rrd_step` char(2) DEFAULT NULL,
  `rrd_step` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `t_rra_id` char(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `local_data_id` (`local_data_id`),
  KEY `data_template_id` (`data_template_id`),
  KEY `data_input_id` (`data_input_id`)
) ENGINE=MyISAM AUTO_INCREMENT=431 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_template_data`
--

LOCK TABLES `data_template_data` WRITE;
/*!40000 ALTER TABLE `data_template_data` DISABLE KEYS */;
INSERT INTO `data_template_data` VALUES (3,0,0,3,2,'on','|host_description| - Hard Drive Space','',NULL,'','on','',300,''),(4,0,0,4,1,'','|host_description| - CPU Usage - System','',NULL,'','on','',300,''),(5,0,0,5,1,'','|host_description| - CPU Usage - User','',NULL,'','on','',300,''),(6,0,0,6,1,'','|host_description| - CPU Usage - Nice','',NULL,'','on','',300,''),(7,0,0,7,2,'on','|host_description| - Noise Level','',NULL,'','on','',300,''),(8,0,0,8,2,'on','|host_description| - Signal Level','',NULL,'','on','',300,''),(9,0,0,9,2,'on','|host_description| - Wireless Transmits','',NULL,'','on','',300,''),(10,0,0,10,2,'on','|host_description| - Wireless Re-Transmits','',NULL,'','on','',300,''),(11,0,0,11,4,'','|host_description| - Load Average','',NULL,'','on','',300,''),(13,0,0,13,6,'','|host_description| - Memory - Free','',NULL,'','on','',300,''),(15,0,0,15,6,'','|host_description| - Memory - Free Swap','',NULL,'','on','',300,''),(16,0,0,16,7,'','|host_description| - Processes','',NULL,'','on','',300,''),(17,0,0,17,5,'','|host_description| - Logged in Users','',NULL,'','on','',300,''),(18,0,0,18,10,'','|host_description| - Ping Host','',NULL,'','on','',300,''),(19,0,0,19,1,'','|host_description| - Total Users','',NULL,'','on','',300,''),(20,0,0,20,1,'','|host_description| - Total Logins','',NULL,'','on','',300,''),(22,0,0,22,1,'','|host_description| - File System Reads','',NULL,'','on','',300,''),(23,0,0,23,1,'','|host_description| - File System Writes','',NULL,'','on','',300,''),(24,0,0,24,1,'','|host_description| - Cache Checks','',NULL,'','on','',300,''),(25,0,0,25,1,'','|host_description| - Cache Hits','',NULL,'','on','',300,''),(26,0,0,26,1,'','|host_description| - Open Files','',NULL,'','on','',300,''),(27,0,0,27,1,'','|host_description| - 5 Minute CPU','',NULL,'','on','',300,''),(30,0,0,30,1,'','|host_description| - Load Average - 1 Minute','',NULL,'','on','',300,''),(31,0,0,31,1,'','|host_description| - Load Average - 5 Minute','',NULL,'','on','',300,''),(32,0,0,32,1,'','|host_description| - Load Average - 15 Minute','',NULL,'','on','',300,''),(33,0,0,33,1,'','|host_description| - Memory - Buffers','',NULL,'','on','',300,''),(34,0,0,34,1,'','|host_description| - Memory - Free','',NULL,'','on','',300,''),(35,0,0,35,2,'on','|host_description| - Volumes','',NULL,'','on','',300,''),(36,0,0,36,2,'on','|host_description| - Directory Entries','',NULL,'','on','',300,''),(37,0,0,37,11,'on','|host_description| - Hard Drive Space','',NULL,'','on','',300,''),(38,0,0,38,2,'on','|host_description| - Errors/Discards','',NULL,'','on','',300,''),(39,0,0,39,2,'on','|host_description| - Unicast Packets','',NULL,'','on','',300,''),(40,0,0,40,2,'on','|host_description| - Non-Unicast Packets','',NULL,'','on','',300,''),(41,0,0,41,2,'on','|host_description| - Traffic','',NULL,'','on','',300,''),(55,0,0,42,2,'','|host_description| - CPU Utilization','',NULL,'','on','',300,''),(56,0,0,43,12,'','|host_description| - Hard Drive Space','',NULL,'','on','',300,''),(57,0,0,44,12,'','|host_description| - CPU Utilization','',NULL,'','on','',300,''),(58,0,0,45,1,'','|host_description| - Processes','',NULL,'','on','',300,''),(59,0,0,46,1,'','|host_description| - Logged in Users','',NULL,'','on','',300,''),(62,13,3,13,6,NULL,'|host_description| - Memory - Free','Localhost - Memory - Free','<path_rra>/localhost_mem_buffers_3.rrd',NULL,'on',NULL,300,NULL),(63,15,4,15,6,NULL,'|host_description| - Memory - Free Swap','Localhost - Memory - Free Swap','<path_rra>/localhost_mem_swap_4.rrd',NULL,'on',NULL,300,NULL),(64,11,5,11,4,NULL,'|host_description| - Load Average','Localhost - Load Average','<path_rra>/localhost_load_1min_5.rrd',NULL,'on',NULL,300,NULL),(65,17,6,17,5,NULL,'|host_description| - Logged in Users','Localhost - Logged in Users','<path_rra>/localhost_users_6.rrd',NULL,'on',NULL,300,NULL),(66,16,7,16,7,NULL,'|host_description| - Processes','Localhost - Processes','<path_rra>/localhost_proc_7.rrd',NULL,'on',NULL,300,NULL),(68,0,0,47,1,'','|host_description| - Memory - Cache','',NULL,'','on','',300,''),(69,0,0,48,1,'on','|host_description| -','',NULL,'','on','',300,''),(70,0,0,49,13,'','|host_description| - DOCSIS Stats','',NULL,'','on','',300,''),(420,0,0,50,2,'','|host_description| - SNR','',NULL,'','on','',300,''),(421,0,0,51,2,'','|host_description| - Active CM','',NULL,'','on','',300,''),(422,0,0,52,2,'','|host_description| - Total CM','',NULL,'','on','',300,''),(423,0,0,53,2,'','|host_description| - ModProf','',NULL,'','on','',300,''),(424,0,0,54,2,'','CMTS - Power','',NULL,'','on','',300,''),(425,0,0,55,2,'','CMTS - Usage','',NULL,'','on','',300,''),(426,0,0,56,2,'','|host_description| - Total CM','',NULL,'','on','',300,''),(427,0,0,57,2,'','|host_description| - Active CM','',NULL,'','on','',300,''),(428,0,0,58,2,'','|host_description| - QUnerroreds','',NULL,'','on','',300,''),(429,0,0,59,2,'','|host_description| - QCorrecteds','',NULL,'','on','',300,''),(430,0,0,60,2,'','|host_description| - QUncorrectables','',NULL,'','on','',300,'');
/*!40000 ALTER TABLE `data_template_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_template_data_rra`
--

DROP TABLE IF EXISTS `data_template_data_rra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_template_data_rra` (
  `data_template_data_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rra_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`data_template_data_id`,`rra_id`),
  KEY `data_template_data_id` (`data_template_data_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_template_data_rra`
--

LOCK TABLES `data_template_data_rra` WRITE;
/*!40000 ALTER TABLE `data_template_data_rra` DISABLE KEYS */;
INSERT INTO `data_template_data_rra` VALUES (3,1),(3,2),(3,3),(3,4),(4,1),(4,2),(4,3),(4,4),(5,1),(5,2),(5,3),(5,4),(6,1),(6,2),(6,3),(6,4),(7,1),(7,2),(7,3),(7,4),(8,1),(8,2),(8,3),(8,4),(9,1),(9,2),(9,3),(9,4),(10,1),(10,2),(10,3),(10,4),(11,1),(11,2),(11,3),(11,4),(13,1),(13,2),(13,3),(13,4),(15,1),(15,2),(15,3),(15,4),(16,1),(16,2),(16,3),(16,4),(17,1),(17,2),(17,3),(17,4),(18,1),(18,2),(18,3),(18,4),(19,1),(19,2),(19,3),(19,4),(20,1),(20,2),(20,3),(20,4),(22,1),(22,2),(22,3),(22,4),(23,1),(23,2),(23,3),(23,4),(24,1),(24,2),(24,3),(24,4),(25,1),(25,2),(25,3),(25,4),(26,1),(26,2),(26,3),(26,4),(27,1),(27,2),(27,3),(27,4),(30,1),(30,2),(30,3),(30,4),(31,1),(31,2),(31,3),(31,4),(32,1),(32,2),(32,3),(32,4),(33,1),(33,2),(33,3),(33,4),(34,1),(34,2),(34,3),(34,4),(35,1),(35,2),(35,3),(35,4),(36,1),(36,2),(36,3),(36,4),(37,1),(37,2),(37,3),(37,4),(38,1),(38,2),(38,3),(38,4),(39,1),(39,2),(39,3),(39,4),(40,1),(40,2),(40,3),(40,4),(41,1),(41,2),(41,3),(41,4),(55,1),(55,2),(55,3),(55,4),(56,1),(56,2),(56,3),(56,4),(57,1),(57,2),(57,3),(57,4),(58,1),(58,2),(58,3),(58,4),(59,1),(59,2),(59,3),(59,4),(62,1),(62,2),(62,3),(62,4),(63,1),(63,2),(63,3),(63,4),(64,1),(64,2),(64,3),(64,4),(65,1),(65,2),(65,3),(65,4),(66,1),(66,2),(66,3),(66,4),(68,1),(68,2),(68,3),(68,4),(69,1),(69,2),(69,3),(69,4),(70,1),(70,2),(70,3),(70,4),(71,1),(71,2),(71,3),(71,4),(72,1),(72,2),(72,3),(72,4),(73,1),(73,2),(73,3),(73,4),(74,1),(74,2),(74,3),(74,4),(75,1),(75,2),(75,3),(75,4),(76,1),(76,2),(76,3),(76,4),(77,1),(77,2),(77,3),(77,4),(78,1),(78,2),(78,3),(78,4),(79,1),(79,2),(79,3),(79,4),(80,1),(80,2),(80,3),(80,4),(81,1),(81,2),(81,3),(81,4),(82,1),(82,2),(82,3),(82,4),(83,1),(83,2),(83,3),(83,4),(84,1),(84,2),(84,3),(84,4),(85,1),(85,2),(85,3),(85,4),(86,1),(86,2),(86,3),(86,4),(87,1),(87,2),(87,3),(87,4),(88,1),(88,2),(88,3),(88,4),(89,1),(89,2),(89,3),(89,4),(90,1),(90,2),(90,3),(90,4),(91,1),(91,2),(91,3),(91,4),(92,1),(92,2),(92,3),(92,4),(93,1),(93,2),(93,3),(93,4),(94,1),(94,2),(94,3),(94,4),(95,1),(95,2),(95,3),(95,4),(96,1),(96,2),(96,3),(96,4),(97,1),(97,2),(97,3),(97,4),(98,1),(98,2),(98,3),(98,4),(99,1),(99,2),(99,3),(99,4),(100,1),(100,2),(100,3),(100,4),(101,1),(101,2),(101,3),(101,4),(102,1),(102,2),(102,3),(102,4),(103,1),(103,2),(103,3),(103,4),(104,1),(104,2),(104,3),(104,4),(105,1),(105,2),(105,3),(105,4),(106,1),(106,2),(106,3),(106,4),(107,1),(107,2),(107,3),(107,4),(108,1),(108,2),(108,3),(108,4),(109,1),(109,2),(109,3),(109,4),(110,1),(110,2),(110,3),(110,4),(111,1),(111,2),(111,3),(111,4),(112,1),(112,2),(112,3),(112,4),(113,1),(113,2),(113,3),(113,4),(114,1),(114,2),(114,3),(114,4),(115,1),(115,2),(115,3),(115,4),(116,1),(116,2),(116,3),(116,4),(117,1),(117,2),(117,3),(117,4),(118,1),(118,2),(118,3),(118,4),(119,1),(119,2),(119,3),(119,4),(120,1),(120,2),(120,3),(120,4),(121,1),(121,2),(121,3),(121,4),(122,1),(122,2),(122,3),(122,4),(123,1),(123,2),(123,3),(123,4),(124,1),(124,2),(124,3),(124,4),(125,1),(125,2),(125,3),(125,4),(126,1),(126,2),(126,3),(126,4),(127,1),(127,2),(127,3),(127,4),(128,1),(128,2),(128,3),(128,4),(129,1),(129,2),(129,3),(129,4),(130,1),(130,2),(130,3),(130,4),(131,1),(131,2),(131,3),(131,4),(132,1),(132,2),(132,3),(132,4),(133,1),(133,2),(133,3),(133,4),(134,1),(134,2),(134,3),(134,4),(135,1),(135,2),(135,3),(135,4),(136,1),(136,2),(136,3),(136,4),(137,1),(137,2),(137,3),(137,4),(138,1),(138,2),(138,3),(138,4),(139,1),(139,2),(139,3),(139,4),(140,1),(140,2),(140,3),(140,4),(141,1),(141,2),(141,3),(141,4),(142,1),(142,2),(142,3),(142,4),(143,1),(143,2),(143,3),(143,4),(144,1),(144,2),(144,3),(144,4),(145,1),(145,2),(145,3),(145,4),(146,1),(146,2),(146,3),(146,4),(147,1),(147,2),(147,3),(147,4),(148,1),(148,2),(148,3),(148,4),(149,1),(149,2),(149,3),(149,4),(150,1),(150,2),(150,3),(150,4),(151,1),(151,2),(151,3),(151,4),(152,1),(152,2),(152,3),(152,4),(153,1),(153,2),(153,3),(153,4),(154,1),(154,2),(154,3),(154,4),(155,1),(155,2),(155,3),(155,4),(156,1),(156,2),(156,3),(156,4),(157,1),(157,2),(157,3),(157,4),(158,1),(158,2),(158,3),(158,4),(159,1),(159,2),(159,3),(159,4),(160,1),(160,2),(160,3),(160,4),(161,1),(161,2),(161,3),(161,4),(162,1),(162,2),(162,3),(162,4),(163,1),(163,2),(163,3),(163,4),(164,1),(164,2),(164,3),(164,4),(165,1),(165,2),(165,3),(165,4),(166,1),(166,2),(166,3),(166,4),(167,1),(167,2),(167,3),(167,4),(168,1),(168,2),(168,3),(168,4),(169,1),(169,2),(169,3),(169,4),(170,1),(170,2),(170,3),(170,4),(171,1),(171,2),(171,3),(171,4),(172,1),(172,2),(172,3),(172,4),(173,1),(173,2),(173,3),(173,4),(174,1),(174,2),(174,3),(174,4),(175,1),(175,2),(175,3),(175,4),(176,1),(176,2),(176,3),(176,4),(177,1),(177,2),(177,3),(177,4),(178,1),(178,2),(178,3),(178,4),(179,1),(179,2),(179,3),(179,4),(180,1),(180,2),(180,3),(180,4),(181,1),(181,2),(181,3),(181,4),(230,1),(230,2),(230,3),(230,4),(231,1),(231,2),(231,3),(231,4),(232,1),(232,2),(232,3),(232,4),(233,1),(233,2),(233,3),(233,4),(234,1),(234,2),(234,3),(234,4),(235,1),(235,2),(235,3),(235,4),(236,1),(236,2),(236,3),(236,4),(237,1),(237,2),(237,3),(237,4),(238,1),(238,2),(238,3),(238,4),(239,1),(239,2),(239,3),(239,4),(240,1),(240,2),(240,3),(240,4),(241,1),(241,2),(241,3),(241,4),(246,1),(246,2),(246,3),(246,4),(247,1),(247,2),(247,3),(247,4),(248,1),(248,2),(248,3),(248,4),(249,1),(249,2),(249,3),(249,4),(250,1),(250,2),(250,3),(250,4),(251,1),(251,2),(251,3),(251,4),(252,1),(252,2),(252,3),(252,4),(253,1),(253,2),(253,3),(253,4),(254,1),(254,2),(254,3),(254,4),(255,1),(255,2),(255,3),(255,4),(256,1),(256,2),(256,3),(256,4),(257,1),(257,2),(257,3),(257,4),(258,1),(258,2),(258,3),(258,4),(259,1),(259,2),(259,3),(259,4),(260,1),(260,2),(260,3),(260,4),(261,1),(261,2),(261,3),(261,4),(262,1),(262,2),(262,3),(262,4),(263,1),(263,2),(263,3),(263,4),(264,1),(264,2),(264,3),(264,4),(265,1),(265,2),(265,3),(265,4),(266,1),(266,2),(266,3),(266,4),(267,1),(267,2),(267,3),(267,4),(268,1),(268,2),(268,3),(268,4),(269,1),(269,2),(269,3),(269,4),(270,1),(270,2),(270,3),(270,4),(271,1),(271,2),(271,3),(271,4),(272,1),(272,2),(272,3),(272,4),(273,1),(273,2),(273,3),(273,4),(274,1),(274,2),(274,3),(274,4),(275,1),(275,2),(275,3),(275,4),(276,1),(276,2),(276,3),(276,4),(277,1),(277,2),(277,3),(277,4),(278,1),(278,2),(278,3),(278,4),(279,1),(279,2),(279,3),(279,4),(280,1),(280,2),(280,3),(280,4),(281,1),(281,2),(281,3),(281,4),(286,1),(286,2),(286,3),(286,4),(287,1),(287,2),(287,3),(287,4),(288,1),(288,2),(288,3),(288,4),(289,1),(289,2),(289,3),(289,4),(290,1),(290,2),(290,3),(290,4),(291,1),(291,2),(291,3),(291,4),(292,1),(292,2),(292,3),(292,4),(293,1),(293,2),(293,3),(293,4),(294,1),(294,2),(294,3),(294,4),(295,1),(295,2),(295,3),(295,4),(296,1),(296,2),(296,3),(296,4),(297,1),(297,2),(297,3),(297,4),(298,1),(298,2),(298,3),(298,4),(299,1),(299,2),(299,3),(299,4),(300,1),(300,2),(300,3),(300,4),(301,1),(301,2),(301,3),(301,4),(306,1),(306,2),(306,3),(306,4),(307,1),(307,2),(307,3),(307,4),(308,1),(308,2),(308,3),(308,4),(309,1),(309,2),(309,3),(309,4),(310,1),(310,2),(310,3),(310,4),(311,1),(311,2),(311,3),(311,4),(312,1),(312,2),(312,3),(312,4),(313,1),(313,2),(313,3),(313,4),(314,1),(314,2),(314,3),(314,4),(315,1),(315,2),(315,3),(315,4),(316,1),(316,2),(316,3),(316,4),(317,1),(317,2),(317,3),(317,4),(318,1),(318,2),(318,3),(318,4),(319,1),(319,2),(319,3),(319,4),(320,1),(320,2),(320,3),(320,4),(321,1),(321,2),(321,3),(321,4),(322,1),(322,2),(322,3),(322,4),(323,1),(323,2),(323,3),(323,4),(324,1),(324,2),(324,3),(324,4),(325,1),(325,2),(325,3),(325,4),(326,1),(326,2),(326,3),(326,4),(327,1),(327,2),(327,3),(327,4),(328,1),(328,2),(328,3),(328,4),(329,1),(329,2),(329,3),(329,4),(330,1),(330,2),(330,3),(330,4),(331,1),(331,2),(331,3),(331,4),(332,1),(332,2),(332,3),(332,4),(333,1),(333,2),(333,3),(333,4),(334,1),(334,2),(334,3),(334,4),(335,1),(335,2),(335,3),(335,4),(336,1),(336,2),(336,3),(336,4),(337,1),(337,2),(337,3),(337,4),(338,1),(338,2),(338,3),(338,4),(339,1),(339,2),(339,3),(339,4),(340,1),(340,2),(340,3),(340,4),(341,1),(341,2),(341,3),(341,4),(342,1),(342,2),(342,3),(342,4),(343,1),(343,2),(343,3),(343,4),(344,1),(344,2),(344,3),(344,4),(345,1),(345,2),(345,3),(345,4),(346,1),(346,2),(346,3),(346,4),(347,1),(347,2),(347,3),(347,4),(348,1),(348,2),(348,3),(348,4),(349,1),(349,2),(349,3),(349,4),(350,1),(350,2),(350,3),(350,4),(351,1),(351,2),(351,3),(351,4),(352,1),(352,2),(352,3),(352,4),(353,1),(353,2),(353,3),(353,4),(354,1),(354,2),(354,3),(354,4),(355,1),(355,2),(355,3),(355,4),(356,1),(356,2),(356,3),(356,4),(357,1),(357,2),(357,3),(357,4),(358,1),(358,2),(358,3),(358,4),(359,1),(359,2),(359,3),(359,4),(360,1),(360,2),(360,3),(360,4),(361,1),(361,2),(361,3),(361,4),(362,1),(362,2),(362,3),(362,4),(363,1),(363,2),(363,3),(363,4),(364,1),(364,2),(364,3),(364,4),(365,1),(365,2),(365,3),(365,4),(366,1),(366,2),(366,3),(366,4),(367,1),(367,2),(367,3),(367,4),(368,1),(368,2),(368,3),(368,4),(369,1),(369,2),(369,3),(369,4),(370,1),(370,2),(370,3),(370,4),(371,1),(371,2),(371,3),(371,4),(372,1),(372,2),(372,3),(372,4),(373,1),(373,2),(373,3),(373,4),(374,1),(374,2),(374,3),(374,4),(375,1),(375,2),(375,3),(375,4),(376,1),(376,2),(376,3),(376,4),(377,1),(377,2),(377,3),(377,4),(382,1),(382,2),(382,3),(382,4),(383,1),(383,2),(383,3),(383,4),(384,1),(384,2),(384,3),(384,4),(385,1),(385,2),(385,3),(385,4),(386,1),(386,2),(386,3),(386,4),(387,1),(387,2),(387,3),(387,4),(388,1),(388,2),(388,3),(388,4),(389,1),(389,2),(389,3),(389,4),(390,1),(390,2),(390,3),(390,4),(391,1),(391,2),(391,3),(391,4),(392,1),(392,2),(392,3),(392,4),(393,1),(393,2),(393,3),(393,4),(394,1),(394,2),(394,3),(394,4),(395,1),(395,2),(395,3),(395,4),(396,1),(396,2),(396,3),(396,4),(397,1),(397,2),(397,3),(397,4),(398,1),(398,2),(398,3),(398,4),(399,1),(399,2),(399,3),(399,4),(420,1),(420,2),(420,3),(420,4),(421,1),(421,2),(421,3),(421,4),(422,1),(422,2),(422,3),(422,4),(423,1),(423,2),(423,3),(423,4),(424,1),(424,2),(424,3),(424,4),(425,1),(425,2),(425,3),(425,4),(426,1),(426,2),(426,3),(426,4),(427,1),(427,2),(427,3),(427,4),(428,1),(428,2),(428,3),(428,4),(429,1),(429,2),(429,3),(429,4),(430,1),(430,2),(430,3),(430,4);
/*!40000 ALTER TABLE `data_template_data_rra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_template_rrd`
--

DROP TABLE IF EXISTS `data_template_rrd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_template_rrd` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `local_data_template_rrd_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `local_data_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `t_rrd_maximum` char(2) DEFAULT NULL,
  `rrd_maximum` varchar(20) NOT NULL DEFAULT '0',
  `t_rrd_minimum` char(2) DEFAULT NULL,
  `rrd_minimum` varchar(20) NOT NULL DEFAULT '0',
  `t_rrd_heartbeat` char(2) DEFAULT NULL,
  `rrd_heartbeat` mediumint(6) NOT NULL DEFAULT '0',
  `t_data_source_type_id` char(2) DEFAULT NULL,
  `data_source_type_id` smallint(5) NOT NULL DEFAULT '0',
  `t_data_source_name` char(2) DEFAULT NULL,
  `data_source_name` varchar(19) NOT NULL DEFAULT '',
  `t_data_input_field_id` char(2) DEFAULT NULL,
  `data_input_field_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `duplicate_dsname_contraint` (`local_data_id`,`data_source_name`,`data_template_id`),
  KEY `local_data_id` (`local_data_id`),
  KEY `data_template_id` (`data_template_id`),
  KEY `local_data_template_rrd_id` (`local_data_template_rrd_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3958 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_template_rrd`
--

LOCK TABLES `data_template_rrd` WRITE;
/*!40000 ALTER TABLE `data_template_rrd` DISABLE KEYS */;
INSERT INTO `data_template_rrd` VALUES (3,'2d53f9c76767a2ae8909f4152fd473a4',0,0,3,'','0','','0','',600,'',1,'','hdd_free','',0),(4,'93d91aa7a3cc5473e7b195d5d6e6e675',0,0,3,'','0','','0','',600,'',1,'','hdd_used','',0),(5,'7bee7987bbf30a3bc429d2a67c6b2595',0,0,4,'','100','','0','',600,'',2,'','cpu_system','',0),(6,'ddccd7fbdece499da0235b4098b87f9e',0,0,5,'','100','','0','',600,'',2,'','cpu_user','',0),(7,'122ab2097f8c6403b7b90cde7b9e2bc2',0,0,6,'','100','','0','',600,'',2,'','cpu_nice','',0),(8,'34f50c820092ea0fecba25b4b94a7946',0,0,7,'','100','','0','',600,'',1,'','wrls_noise','',0),(9,'830b811d1834e5ba0e2af93bd92db057',0,0,8,'','100','','0','',600,'',1,'','wrls_signal','',0),(10,'2f1b016a2465eef3f7369f6313cd4a94',0,0,9,'','1000000','','0','',600,'',2,'','wrls_transmits','',0),(11,'28ffcecaf8b50e49f676f2d4a822685d',0,0,10,'','1000000','','0','',600,'',2,'','wrls_retransmits','',0),(12,'8175ca431c8fe50efff5a1d3ae51b55d',0,0,11,'','500','','0','',600,'',1,'','load_1min','',17),(13,'a2eeb8acd6ea01cd0e3ac852965c0eb6',0,0,11,'','500','','0','',600,'',1,'','load_5min','',18),(14,'9f951b7fb3b19285a411aebb5254a831',0,0,11,'','500','','0','',600,'',1,'','load_15min','',19),(16,'a4df3de5238d3beabee1a2fe140d3d80',0,0,13,'','0','','0','',600,'',1,'','mem_buffers','',23),(18,'7fea6acc9b1a19484b4cb4cef2b6c5da',0,0,15,'','0','','0','',600,'',1,'','mem_swap','',23),(19,'f1ba3a5b17b95825021241398bb0f277',0,0,16,'','1000','','0','',600,'',1,'','proc','',24),(20,'46a5afe8e6c0419172c76421dc9e304a',0,0,17,'','500','','0','',600,'',1,'','users','',21),(21,'962fd1994fe9cae87fb36436bdb8a742',0,0,18,'','5000','','0','',600,'',1,'','ping','',30),(22,'7a8dd1111a8624369906bf2cd6ea9ca9',0,0,19,'','100000','','0','',600,'',1,'','total_users','',0),(23,'ddb6e74d34d2f1969ce85f809dbac23d',0,0,20,'','100000','','0','',600,'',1,'','total_logins','',0),(25,'289311d10336941d33d9a1c48a7b11ee',0,0,22,'','10000000','','0','',600,'',2,'','fs_reads','',0),(26,'02216f036cca04655ee2f67fedb6f4f0',0,0,23,'','10000000','','0','',600,'',2,'','fs_writes','',0),(27,'9e402c0f29131ef7139c20bd500b4e8a',0,0,24,'','10000000','','0','',600,'',2,'','cache_checks','',0),(28,'46717dfe3c8c030d8b5ec0874f9dbdca',0,0,25,'','1000000','','0','',600,'',2,'','cache_hits','',0),(29,'7a88a60729af62561812c43bde61dfc1',0,0,26,'','100000','','0','',600,'',1,'','open_files','',0),(30,'3c0fd1a188b64a662dfbfa985648397b',0,0,27,'','100','','0','',600,'',1,'','5min_cpu','',0),(33,'ed44c2438ef7e46e2aeed2b6c580815c',0,0,30,'','500','','0','',600,'',1,'','load_1min','',0),(34,'9b3a00c9e3530d9e58895ac38271361e',0,0,31,'','500','','0','',600,'',1,'','load_5min','',0),(35,'6746c2ed836ecc68a71bbddf06b0e5d9',0,0,32,'','500','','0','',600,'',1,'','load_15min','',0),(36,'9835d9e1a8c78aa2475d752e8fa74812',0,0,33,'','10000000','','0','',600,'',1,'','mem_buffers','',0),(37,'9c78dc1981bcea841b8c827c6dc0d26c',0,0,34,'','10000000','','0','',600,'',1,'','mem_free','',0),(38,'62a56dc76fe4cd8566a31b5df0274cc3',0,0,35,'','0','','0','',600,'',1,'','vol_total','',0),(39,'2e366ab49d0e0238fb4e3141ea5a88c3',0,0,35,'','0','','0','',600,'',1,'','vol_free','',0),(40,'dceedc84718dd93a5affe4b190bca810',0,0,35,'','0','','0','',600,'',1,'','vol_freeable','',0),(42,'93330503f1cf67db00d8fe636035e545',0,0,36,'','100000000000','','0','',600,'',1,'','dir_total','',0),(43,'6b0fe4aa6aaf22ef9cfbbe96d87fa0d7',0,0,36,'','100000000000','','0','',600,'',1,'','dir_used','',0),(44,'4c82df790325d789d304e6ee5cd4ab7d',0,0,37,'','0','','0','',600,'',1,'','hdd_free','',0),(46,'c802e2fd77f5b0a4c4298951bf65957c',0,0,38,'','10000000','','0','',600,'',2,'','errors_in','',0),(47,'4e2a72240955380dc8ffacfcc8c09874',0,0,38,'','10000000','','0','',600,'',2,'','discards_in','',0),(48,'636672962b5bb2f31d86985e2ab4bdfe',0,0,39,'','1000000000','','0','',600,'',2,'','unicast_in','',0),(49,'18ce92c125a236a190ee9dd948f56268',0,0,39,'','1000000000','','0','',600,'',2,'','unicast_out','',0),(50,'13ebb33f9cbccfcba828db1075a8167c',0,0,38,'','10000000','','0','',600,'',2,'','discards_out','',0),(51,'31399c3725bee7e09ec04049e3d5cd17',0,0,38,'','10000000','','0','',600,'',2,'','errors_out','',0),(52,'7be68cbc4ee0b2973eb9785f8c7a35c7',0,0,40,'','1000000000','','0','',600,'',2,'','nonunicast_out','',0),(53,'93e2b6f59b10b13f2ddf2da3ae98b89a',0,0,40,'','1000000000','','0','',600,'',2,'','nonunicast_in','',0),(54,'2df25c57022b0c7e7d0be4c035ada1a0',0,0,41,'on','100000000','','0','',600,'',2,'','traffic_in','',0),(55,'721c0794526d1ac1c359f27dc56faa49',0,0,41,'on','100000000','','0','',600,'',2,'','traffic_out','',0),(56,'07175541991def89bd02d28a215f6fcc',0,0,37,'','0','','0','',600,'',1,'','hdd_used','',0),(76,'07492e5cace6d74e7db3cb1fc005a3f3',0,0,42,'','100','','0','',600,'',1,'','cpu','',0),(78,'0ee6bb54957f6795a5369a29f818d860',0,0,43,'','0','','0','',600,'',1,'','hdd_used','',0),(79,'9825aaf7c0bdf1554c5b4b86680ac2c0',0,0,44,'','100','','0','',600,'',1,'','cpu','',0),(80,'50ccbe193c6c7fc29fb9f726cd6c48ee',0,0,45,'','1000','','0','',600,'',1,'','proc','',0),(81,'9464c91bcff47f23085ae5adae6ab987',0,0,46,'','5000','','0','',600,'',1,'','users','',0),(84,'',16,3,13,NULL,'0',NULL,'0',NULL,600,NULL,1,NULL,'mem_buffers',NULL,23),(85,'',18,4,15,NULL,'0',NULL,'0',NULL,600,NULL,1,NULL,'mem_swap',NULL,23),(86,'',12,5,11,NULL,'500',NULL,'0',NULL,600,NULL,1,NULL,'load_1min',NULL,17),(87,'',13,5,11,NULL,'500',NULL,'0',NULL,600,NULL,1,NULL,'load_5min',NULL,18),(88,'',14,5,11,NULL,'500',NULL,'0',NULL,600,NULL,1,NULL,'load_15min',NULL,19),(89,'',20,6,17,NULL,'500',NULL,'0',NULL,600,NULL,1,NULL,'users',NULL,21),(90,'',19,7,16,NULL,'1000',NULL,'0',NULL,600,NULL,1,NULL,'proc',NULL,24),(92,'165a0da5f461561c85d092dfe96b9551',0,0,43,'','0','','0','',600,'',1,'','hdd_total','',0),(95,'7a6ca455bbeff99ca891371bc77d5cf9',0,0,47,'','10000000','','0','',600,'',1,'','mem_cache','',0),(96,'224b83ea73f55f8a861bcf4c9bea0472',0,0,48,'on','100','','0','',600,'on',1,'','snmp_oid','',0),(97,'36ba03f42a07890254766952db8343ac',0,0,49,'','0','','0','',600,'',1,'','DownChannelPower','',54),(98,'5d7a4a679400db3f307e98a728b45890',0,0,49,'','0','','0','',600,'',1,'','SigQSignalNoise','',55),(99,'4edce0e22c1f017366495370ef980eb5',0,0,49,'','0','','0','',600,'',1,'','SigQMicroreflection','',56),(100,'96134e1100fd6eaf5621390cd883819c',0,0,49,'','0','','0','',600,'',1,'','CmRangingTimeout','',57),(101,'0c4ecbc528a085cd7fc5ca27e7e715a5',0,0,49,'','1000','','0','',600,'',1,'','CmStatusTxPower','',58),(102,'33c44e6978d42a5c9b491c3d9e73d321',0,0,49,'','0','','0','',600,'',1,'','CmStatusResets','',59),(103,'109ba20300eeff35d0da90141ff6bf57',0,0,49,'','0','','0','',600,'',1,'','CmStatusLostSyncs','',60),(104,'7e5ded115643c28b02c0d3ae3ee0e52a',0,0,49,'','0','','0','',600,'',1,'','CmStatusT1Timeouts','',61),(105,'f7ba916293c23ed84389b96b86bc6d48',0,0,49,'','0','','0','',600,'',1,'','CmStatusT2Timeouts','',62),(106,'46edac5eb96a584ff127213a62936eec',0,0,49,'','0','','0','',600,'',1,'','CmStatusT3Timeouts','',63),(107,'e6557d80328971eef7c125c5a46d9b4c',0,0,49,'','0','','0','',600,'',1,'','CmStatusT4Timeouts','',64),(3947,'2ca71202954336336c4faf6a2ff16427',0,0,50,'','0','','0','',600,'',1,'','SNR','',0),(3948,'ec60a03f2e38aa4f6fa71472197213d6',0,0,51,'','0','','0','',600,'',1,'','ActiveCM','',0),(3949,'f3f94a63449e66f9f851b16265008ab4',0,0,52,'','0','','0','',600,'',1,'','TotalCM','',0),(3950,'449f33a7083a6fe1a9a4d3bb29e818b4',0,0,53,'','0','','0','',600,'',1,'','ModProf','',0),(3951,'8f8aff9e4b3f41a6c4aef124c5763788',0,0,54,'','0','','0','',600,'',1,'','PowerCasa','',0),(3952,'01189474e7e3374aac71b808a7d3c93b',0,0,55,'','0','','0','',600,'',1,'','Usage','',0),(3953,'6bb309d720253a2df508912889053722',0,0,56,'','0','','0','',600,'',1,'','DSTotalCM','',0),(3954,'1f17b38a696eddf9825338bfd57dafc7',0,0,57,'','0','','0','',600,'',1,'','DSActiveCM','',0),(3955,'7b3cbf913dae39c11e9b9c63ef9998b0',0,0,58,'','0','','0','',600,'',2,'','QUnerroreds','',0),(3956,'5b576129ab071e565332c35c53e2a876',0,0,59,'','0','','0','',600,'',2,'','QCorrecteds','',0),(3957,'233630fbf1f21548b8b7d57bfd9ffad7',0,0,60,'','0','','0','',600,'',2,'','QUncorrectables','',0);
/*!40000 ALTER TABLE `data_template_rrd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_local`
--

DROP TABLE IF EXISTS `graph_local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_local` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `snmp_query_id` mediumint(8) NOT NULL DEFAULT '0',
  `snmp_index` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `graph_template_id` (`graph_template_id`),
  KEY `snmp_query_id` (`snmp_query_id`),
  KEY `snmp_index` (`snmp_index`)
) ENGINE=MyISAM AUTO_INCREMENT=354 DEFAULT CHARSET=latin1 COMMENT='Creates a relationship for each item in a custom graph.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_local`
--

LOCK TABLES `graph_local` WRITE;
/*!40000 ALTER TABLE `graph_local` DISABLE KEYS */;
INSERT INTO `graph_local` VALUES (1,12,1,0,''),(2,9,1,0,''),(3,10,1,0,''),(4,8,1,0,'');
/*!40000 ALTER TABLE `graph_local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_template_input`
--

DROP TABLE IF EXISTS `graph_template_input`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_template_input` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `column_name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=latin1 COMMENT='Stores the names for graph item input groups.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_template_input`
--

LOCK TABLES `graph_template_input` WRITE;
/*!40000 ALTER TABLE `graph_template_input` DISABLE KEYS */;
INSERT INTO `graph_template_input` VALUES (3,'e9d4191277fdfd7d54171f153da57fb0',2,'Inbound Data Source','','task_item_id'),(4,'7b361722a11a03238ee8ab7ce44a1037',2,'Outbound Data Source','','task_item_id'),(5,'b33eb27833614056e06ee5952c3e0724',3,'Available Disk Space Data Source','','task_item_id'),(6,'ef8799e63ee00e8904bcc4228015784a',3,'Used Disk Space Data Source','','task_item_id'),(7,'2662ef4fbb0bf92317ffd42c7515af37',5,'Signal Level Data Source','','task_item_id'),(8,'a6edef6624c796d3a6055305e2e3d4bf',5,'Noise Level Data Source','','task_item_id'),(9,'b0e902db1875e392a9d7d69bfbb13515',5,'Signal Level Color','','color_id'),(10,'24632b1d4a561e937225d0a5fbe65e41',5,'Noise Level Color','','color_id'),(11,'6d078f1d58b70ad154a89eb80fe6ab75',6,'Transmissions Data Source','','task_item_id'),(12,'878241872dd81c68d78e6ff94871d97d',6,'Re-Transmissions Data Source','','task_item_id'),(13,'f8fcdc3a3f0e8ead33bd9751895a3462',6,'Transmissions Color','','color_id'),(14,'394ab4713a34198dddb5175aa40a2b4a',6,'Re-Transmissions Color','','color_id'),(15,'433f328369f9569446ddc59555a63eb8',7,'Ping Host Data Source','','task_item_id'),(16,'a1a91c1514c65152d8cb73522ea9d4e6',7,'Legend Color','','color_id'),(17,'2fb4deb1448379b27ddc64e30e70dc42',7,'Legend Text','','text_format'),(18,'592cedd465877bc61ab549df688b0b2a',8,'Processes Data Source','','task_item_id'),(19,'1d51dbabb200fcea5c4b157129a75410',8,'Legend Color','','color_id'),(20,'8cb8ed3378abec21a1819ea52dfee6a3',9,'1 Minute Data Source','','task_item_id'),(21,'5dfcaf9fd771deb8c5430bce1562e371',9,'5 Minute Data Source','','task_item_id'),(22,'6f3cc610315ee58bc8e0b1f272466324',9,'15 Minute Data Source','','task_item_id'),(23,'b457a982bf46c6760e6ef5f5d06d41fb',10,'Logged in Users Data Source','','task_item_id'),(24,'bd4a57adf93c884815b25a8036b67f98',10,'Legend Color','','color_id'),(25,'d7cdb63500c576e0f9f354de42c6cf3a',11,'1 Minute Data Source','','task_item_id'),(26,'a23152f5ec02e7762ca27608c0d89f6c',11,'5 Minute Data Source','','task_item_id'),(27,'2cc5d1818da577fba15115aa18f64d85',11,'15 Minute Data Source','','task_item_id'),(30,'6273c71cdb7ed4ac525cdbcf6180918c',12,'Free Data Source','','task_item_id'),(31,'5e62dbea1db699f1bda04c5863e7864d',12,'Swap Data Source','','task_item_id'),(32,'4d52e112a836d4c9d451f56602682606',4,'System CPU Data Source','','task_item_id'),(33,'f0310b066cc919d2f898b8d1ebf3b518',4,'User CPU Data Source','','task_item_id'),(34,'d9eb6b9eb3d7dd44fd14fdefb4096b54',4,'Nice CPU Data Source','','task_item_id'),(35,'f45def7cad112b450667aa67262258cb',13,'Memory Free Data Source','','task_item_id'),(36,'f8c361a8c8b7ad80e8be03ba7ea5d0d6',13,'Memory Buffers Data Source','','task_item_id'),(37,'03d11dce695963be30bd744bd6cbac69',14,'Cache Hits Data Source','','task_item_id'),(38,'9cbc515234779af4bf6cdf71a81c556a',14,'Cache Checks Data Source','','task_item_id'),(39,'2c4d561ee8132a8dda6de1104336a6ec',15,'CPU Utilization Data Source','','task_item_id'),(40,'6e1cf7addc0cc419aa903552e3eedbea',16,'File System Reads Data Source','','task_item_id'),(41,'7ea2aa0656f7064d25a36135dd0e9082',16,'File System Writes Data Source','','task_item_id'),(42,'63480bca78a38435f24a5b5d5ed050d7',17,'Current Logins Data Source','','task_item_id'),(44,'31fed1f9e139d4897d0460b10fb7be94',15,'Legend Color','','color_id'),(45,'bb9d83a02261583bc1f92d9e66ea705d',18,'CPU Usage Data Source','','task_item_id'),(46,'51196222ed37b44236d9958116028980',18,'Legend Color','','color_id'),(47,'fd26b0f437b75715d6dff983e7efa710',19,'Free Space Data Source','','task_item_id'),(48,'a463dd46862605c90ea60ccad74188db',19,'Total Space Data Source','','task_item_id'),(49,'9977dd7a41bcf0f0c02872b442c7492e',19,'Freeable Space Data Source','','task_item_id'),(51,'a7a69bbdf6890d6e6eaa7de16e815ec6',20,'Used Directory Entries Data Source','','task_item_id'),(52,'0072b613a33f1fae5ce3e5903dec8fdb',20,'Available Directory Entries Data Source','','task_item_id'),(53,'940beb0f0344e37f4c6cdfc17d2060bc',21,'Available Disk Space Data Source','','task_item_id'),(54,'7b0674dd447a9badf0d11bec688028a8',21,'Used Disk Space Data Source','','task_item_id'),(55,'fa83cd3a3b4271b644cb6459ea8c35dc',22,'Discards In Data Source','','task_item_id'),(56,'7946e8ee1e38a65462b85e31a15e35e5',22,'Errors In Data Source','','task_item_id'),(57,'00ae916640272f5aca54d73ae34c326b',23,'Unicast Packets Out Data Source','','task_item_id'),(58,'1bc1652f82488ebfb7242c65d2ffa9c7',23,'Unicast Packets In Data Source','','task_item_id'),(59,'e3177d0e56278de320db203f32fb803d',24,'Non-Unicast Packets In Data Source','','task_item_id'),(60,'4f20fba2839764707f1c3373648c5fef',24,'Non-Unicast Packets Out Data Source','','task_item_id'),(61,'e5acdd5368137c408d56ecf55b0e077c',22,'Discards Out Data Source','','task_item_id'),(62,'a028e586e5fae667127c655fe0ac67f0',22,'Errors Out Data Source','','task_item_id'),(63,'2764a4f142ba9fd95872106a1b43541e',25,'Inbound Data Source','','task_item_id'),(64,'f73f7ddc1f4349356908122093dbfca2',25,'Outbound Data Source','','task_item_id'),(65,'86bd8819d830a81d64267761e1fd8ec4',26,'Total Disk Space Data Source','','task_item_id'),(66,'6c8967850102202de166951e4411d426',26,'Used Disk Space Data Source','','task_item_id'),(67,'bdad718851a52b82eca0a310b0238450',27,'CPU Utilization Data Source','','task_item_id'),(68,'e7b578e12eb8a82627557b955fd6ebd4',27,'Legend Color','','color_id'),(69,'37d09fb7ce88ecec914728bdb20027f3',28,'Logged in Users Data Source','','task_item_id'),(70,'699bd7eff7ba0c3520db3692103a053d',28,'Legend Color','','color_id'),(71,'df905e159d13a5abed8a8a7710468831',29,'Processes Data Source','','task_item_id'),(72,'8ca9e3c65c080dbf74a59338d64b0c14',29,'Legend Color','','color_id'),(73,'69ad68fc53af03565aef501ed5f04744',30,'Open Files Data Source','','task_item_id'),(74,'562726cccdb67d5c6941e9e826ef4ef5',31,'Inbound Data Source','','task_item_id'),(75,'82426afec226f8189c8928e7f083f80f',31,'Outbound Data Source','','task_item_id'),(76,'69a23877302e7d142f254b208c58b596',32,'Inbound Data Source','','task_item_id'),(77,'f28013abf8e5813870df0f4111a5e695',32,'Outbound Data Source','','task_item_id'),(78,'8644b933b6a09dde6c32ff24655eeb9a',33,'Outbound Data Source','','task_item_id'),(79,'49c4b4800f3e638a6f6bb681919aea80',33,'Inbound Data Source','','task_item_id'),(80,'e0b395be8db4f7b938d16df7ae70065f',13,'Cache Memory Data Source','','task_item_id'),(81,'2dca37011521501b9c2b705d080db750',34,'Data Source [snmp_oid]',NULL,'task_item_id'),(82,'b8d8ade5f5f3dd7b12f8cc56bbb4083e',34,'Legend Color','','color_id'),(83,'ac2355b4895c37e14df827f969f31c12',34,'Legend Text','','text_format'),(84,'11f8767cd8278ecc4610fff77c2de6d3',35,'Data Source [SigQMicroreflection]','','task_item_id'),(85,'df88b6bc8467e6b48bdfd294dbe3abff',35,'Data Source [CmStatusTxPower]','','task_item_id'),(86,'304e791947304200f5773c5b8b125c89',35,'Data Source [DownChannelPower]','','task_item_id'),(87,'1e8bb9a56d0d67234b9eb668b58975d1',35,'Data Source [SigQSignalNoise]','','task_item_id'),(88,'b76c9ba120c34bfc7a38c01e55a028c0',36,'Data Source [CmStatusLostSyncs]','','task_item_id'),(89,'dcb4b6af3c9ee3e91cd43f610cb3e974',36,'Data Source [CmStatusResets]','','task_item_id'),(90,'83d135f6478f41517a66efa57406a157',36,'Data Source [CmStatusT1Timeouts]','','task_item_id'),(91,'22daeec6985a98abd2da584aa3386832',36,'Data Source [CmStatusT2Timeouts]','','task_item_id'),(92,'b78a927e4da956f86d62433ecddf4574',36,'Data Source [CmStatusT3Timeouts]','','task_item_id'),(93,'bd22adb80628661d00e94dd5e2646ca2',36,'Data Source [CmStatusT4Timeouts]','','task_item_id'),(94,'15cd1536d216d3c7aefe66bbc550a328',36,'Data Source [CmRangingTimeout]','','task_item_id'),(95,'10adcdd92cc447be7ba46cd172c61deb',37,'Data Source [SigQMicroreflection]','','task_item_id'),(104,'bf3b05b3d4a71fd6992ba5eac5f593b0',39,'Data Source [DownChannelPower]','','task_item_id'),(97,'dc47205a4fc89c82f2d74b5dfd2d7d9b',37,'Data Source [DownChannelPower]','','task_item_id'),(98,'94030564f554802c12a30cf631b816da',37,'Data Source [SigQSignalNoise]','','task_item_id'),(108,'dec73f56882600288822e051311edb36',41,'Data Source [ActiveCM]','','task_item_id'),(100,'c3c8f115124a5714139f493d73b9d6e1',38,'Data Source [CmStatusTxPower]','','task_item_id'),(106,'fef1813c4e36b6a4fc500942dfab508c',40,'SNR','','task_item_id'),(107,'df7f791f361b31d983d6328a679c3643',40,'Data Source [SNR]','','task_item_id'),(109,'4370c58555cbee7480133b75bd979c7e',41,'Data Source [TotalCM]','','task_item_id'),(110,'bb68c8fcc75e12961415d38dbb1841aa',42,'Data Source [ModProf]','','task_item_id'),(111,'39e1f75f441373ee36b58a06cc43851d',43,'Data Source [PowerCasa]','','task_item_id'),(112,'832a66a311072eb36d6df5060032e64f',44,'Data Source [Usage]','','task_item_id'),(113,'ff3138672c4f694b61ffba8d70bdbcbe',45,'Data Source [DSActiveCM]','','task_item_id'),(114,'deba339a1f967d41cac86ac8cf0317a7',45,'Data Source [DSTotalCM]','','task_item_id'),(115,'1b20847590292098bf7bd629be7b5fc9',46,'Data Source [Power]','','task_item_id'),(116,'c835f3a68e1c134f8674eb07935de1dc',47,'Data Source [QCorrecteds]','','task_item_id'),(117,'f976495e491adcdc751d0894f9325eea',47,'Data Source [QUnerroreds]','','task_item_id'),(118,'f7680509e7334bc44f805fa6eded6dfd',47,'Data Source [QUncorrectables]','','task_item_id');
/*!40000 ALTER TABLE `graph_template_input` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_template_input_defs`
--

DROP TABLE IF EXISTS `graph_template_input_defs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_template_input_defs` (
  `graph_template_input_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `graph_template_item_id` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`graph_template_input_id`,`graph_template_item_id`),
  KEY `graph_template_input_id` (`graph_template_input_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Stores the relationship for what graph iitems are associated';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_template_input_defs`
--

LOCK TABLES `graph_template_input_defs` WRITE;
/*!40000 ALTER TABLE `graph_template_input_defs` DISABLE KEYS */;
INSERT INTO `graph_template_input_defs` VALUES (3,9),(3,10),(3,11),(3,12),(4,13),(4,14),(4,15),(4,16),(5,21),(5,22),(5,23),(5,24),(6,17),(6,18),(6,19),(6,20),(7,45),(7,46),(7,47),(7,48),(8,49),(8,50),(8,51),(8,52),(9,45),(10,49),(11,53),(11,54),(11,55),(11,56),(12,57),(12,58),(12,59),(12,60),(13,53),(14,57),(15,61),(15,62),(15,63),(15,64),(16,61),(17,61),(18,65),(18,66),(18,67),(18,68),(19,65),(20,69),(20,70),(21,71),(21,72),(22,73),(22,74),(23,76),(23,77),(23,78),(23,79),(24,76),(25,80),(25,81),(26,82),(26,83),(27,84),(27,85),(30,95),(30,96),(30,97),(30,98),(31,99),(31,100),(31,101),(31,102),(32,29),(32,30),(32,31),(32,32),(33,33),(33,34),(33,35),(33,36),(34,37),(34,38),(34,39),(34,40),(35,103),(35,104),(35,105),(35,106),(36,107),(36,108),(36,109),(36,110),(37,111),(37,112),(37,113),(37,114),(38,115),(38,116),(38,117),(38,118),(39,119),(39,120),(39,121),(39,122),(40,123),(40,124),(40,125),(40,126),(41,127),(41,128),(41,129),(41,130),(42,131),(42,132),(42,133),(42,134),(44,119),(45,139),(45,140),(45,141),(45,142),(46,139),(47,143),(47,144),(47,145),(47,146),(48,147),(48,148),(48,149),(48,150),(49,151),(49,152),(49,153),(49,154),(51,159),(51,160),(51,161),(51,162),(52,163),(52,164),(52,165),(52,166),(53,172),(53,173),(53,174),(53,175),(54,167),(54,169),(54,170),(54,171),(55,180),(55,181),(55,182),(55,183),(56,184),(56,185),(56,186),(56,187),(57,188),(57,189),(57,190),(57,191),(58,192),(58,193),(58,194),(58,195),(59,196),(59,197),(59,198),(59,199),(60,200),(60,201),(60,202),(60,203),(61,204),(61,205),(61,206),(61,207),(62,208),(62,209),(62,210),(62,211),(63,212),(63,213),(63,214),(63,215),(64,216),(64,217),(64,218),(64,219),(65,307),(65,308),(65,309),(65,310),(66,303),(66,304),(66,305),(66,306),(67,315),(67,316),(67,317),(67,318),(68,315),(69,319),(69,320),(69,321),(69,322),(70,319),(71,323),(71,324),(71,325),(71,326),(72,323),(73,358),(73,359),(73,360),(73,361),(74,362),(74,363),(74,364),(74,365),(75,366),(75,367),(75,368),(75,369),(75,371),(75,372),(76,373),(76,374),(76,375),(76,376),(76,383),(77,377),(77,378),(77,379),(77,380),(77,384),(78,385),(78,386),(78,387),(78,388),(78,393),(79,389),(79,390),(79,391),(79,392),(79,394),(80,403),(80,404),(80,405),(80,406),(81,407),(81,408),(81,409),(81,410),(82,407),(83,407),(84,419),(84,420),(84,421),(84,422),(85,415),(85,416),(85,417),(85,418),(86,411),(86,412),(86,413),(86,414),(87,423),(87,424),(87,425),(87,426),(88,427),(88,428),(88,429),(88,430),(89,435),(89,436),(89,437),(89,438),(90,439),(90,440),(90,441),(90,442),(91,443),(91,444),(91,445),(91,446),(92,447),(92,448),(92,449),(92,450),(93,451),(93,452),(93,453),(93,454),(94,431),(94,432),(94,433),(94,434),(95,2723),(95,2724),(95,2725),(95,2726),(97,2715),(97,2716),(97,2717),(97,2718),(98,2727),(98,2728),(98,2729),(98,2730),(100,2735),(100,2736),(100,2737),(100,2738),(104,5987),(104,5988),(104,5989),(104,5990),(107,6383),(107,6384),(107,6385),(107,6386),(108,6387),(108,6388),(108,6389),(108,6390),(108,6391),(109,6392),(109,6393),(109,6394),(109,6395),(109,6396),(110,6397),(110,6398),(111,6399),(111,6400),(111,6401),(111,6402),(112,6403),(112,6404),(112,6405),(112,6406),(113,6407),(113,6408),(113,6409),(113,6410),(113,6411),(114,6412),(114,6413),(114,6414),(114,6415),(114,6416),(115,6417),(115,6418),(115,6419),(115,6420),(116,6425),(116,6426),(116,6427),(116,6428),(117,6421),(117,6422),(117,6423),(117,6424),(118,6429),(118,6430),(118,6431),(118,6432);
/*!40000 ALTER TABLE `graph_template_input_defs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_templates`
--

DROP TABLE IF EXISTS `graph_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_templates` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(32) NOT NULL DEFAULT '',
  `name` char(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=latin1 COMMENT='Contains each graph template name.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_templates`
--

LOCK TABLES `graph_templates` WRITE;
/*!40000 ALTER TABLE `graph_templates` DISABLE KEYS */;
INSERT INTO `graph_templates` VALUES (34,'010b90500e1fc6a05abfd542940584d0','SNMP - Generic OID Template'),(2,'5deb0d66c81262843dce5f3861be9966','Interface - Traffic (bits/sec)'),(3,'abb5e813c9f1e8cd6fc1e393092ef8cb','ucd/net - Available Disk Space'),(4,'e334bdcf821cd27270a4cc945e80915e','ucd/net - CPU Usage'),(5,'280e38336d77acde4672879a7db823f3','Karlnet - Wireless Levels'),(6,'3109d88e6806d2ce50c025541b542499','Karlnet - Wireless Transmissions'),(7,'cf96dfb22b58e08bf101ca825377fa4b','Unix - Ping Latency'),(8,'9fe8b4da353689d376b99b2ea526cc6b','Unix - Processes'),(9,'fe5edd777a76d48fc48c11aded5211ef','Unix - Load Average'),(10,'63610139d44d52b195cc375636653ebd','Unix - Logged in Users'),(11,'5107ec0206562e77d965ce6b852ef9d4','ucd/net - Load Average'),(12,'6992ed4df4b44f3d5595386b8298f0ec','Linux - Memory Usage'),(13,'be275639d5680e94c72c0ebb4e19056d','ucd/net - Memory Usage'),(14,'f17e4a77b8496725dc924b8c35b60036','Netware - File System Cache'),(15,'46bb77f4c0c69671980e3c60d3f22fa9','Netware - CPU Utilization'),(16,'8e77a3036312fd0fda32eaea2b5f141b','Netware - File System Activity'),(17,'5892c822b1bb2d38589b6c27934b9936','Netware - Logged In Users'),(18,'9a5e6d7781cc1bd6cf24f64dd6ffb423','Cisco - CPU Usage'),(19,'0dd0438d5e6cad6776f79ecaa96fb708','Netware - Volume Information'),(20,'b18a3742ebea48c6198412b392d757fc','Netware - Directory Information'),(21,'8e7c8a511652fe4a8e65c69f3d34779d','Unix - Available Disk Space'),(22,'06621cd4a9289417cadcb8f9b5cfba80','Interface - Errors/Discards'),(23,'e0d1625a1f4776a5294583659d5cee15','Interface - Unicast Packets'),(24,'10ca5530554da7b73dc69d291bf55d38','Interface - Non-Unicast Packets'),(25,'df244b337547b434b486662c3c5c7472','Interface - Traffic (bytes/sec)'),(26,'7489e44466abee8a7d8636cb2cb14a1a','Host MIB - Available Disk Space'),(27,'c6bb62bedec4ab97f9db9fd780bd85a6','Host MIB - CPU Utilization'),(28,'e8462bbe094e4e9e814d4e681671ea82','Host MIB - Logged in Users'),(29,'62205afbd4066e5c4700338841e3901e','Host MIB - Processes'),(30,'e3780a13b0f7a3f85a44b70cd4d2fd36','Netware - Open Files'),(31,'1742b2066384637022d178cc5072905a','Interface - Traffic (bits/sec, 95th Percentile)'),(32,'13b47e10b2d5db45707d61851f69c52b','Interface - Traffic (bits/sec, Total Bandwidth)'),(33,'8ad6790c22b693680e041f21d62537ac','Interface - Traffic (bytes/sec, Total Bandwidth)'),(35,'0c6333c9f115a0942c18073fa8728435','DOCSIS - Signal Levels'),(36,'440af5328203c2bc0918de3c52ca9c6f','DOCSIS - Stat Counters'),(37,'342074846e2685feb2cb2bb2b53017c4','DOCSIS - Signal Levels DS'),(38,'cbcc37baaf7830dae227cac3ea7619e2','DOCSIS - Signal Levels US'),(39,'1180a3f4199767ce50d346a31cf643c3','DOCSIS - Signal Levels DS level'),(40,'3dd86e1f4503a7e4ba164064db12a88c','CMTS - SNR'),(41,'222e696b4b6591414f37b12d2f914806','CMTS - Number CM'),(42,'fc72d3140778bb9e41e8dd4a117d4132','CMTS - ModProf'),(43,'6040a934a4e769f876ad8ef5daa1052b','CMTS - Power'),(44,'56e030108a3496606f343127cd5d2244','CMTS - Usage'),(45,'d987b71e8808e60d36033f6a40288dd2','CMTS - DS Number CM'),(46,'4828c5f6aaf18b9cf51376c84d883980','CMTS - Power - CASA'),(47,'682a602954295c2f7b388ed327757c2e','CMTS - Packet Analysis');
/*!40000 ALTER TABLE `graph_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_templates_gprint`
--

DROP TABLE IF EXISTS `graph_templates_gprint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_templates_gprint` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `gprint_text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_templates_gprint`
--

LOCK TABLES `graph_templates_gprint` WRITE;
/*!40000 ALTER TABLE `graph_templates_gprint` DISABLE KEYS */;
INSERT INTO `graph_templates_gprint` VALUES (2,'e9c43831e54eca8069317a2ce8c6f751','Normal','%8.2lf %s'),(3,'19414480d6897c8731c7dc6c5310653e','Exact Numbers','%8.0lf'),(4,'304a778405392f878a6db435afffc1e9','Load Average','%8.2lf');
/*!40000 ALTER TABLE `graph_templates_gprint` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_templates_graph`
--

DROP TABLE IF EXISTS `graph_templates_graph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_templates_graph` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `local_graph_template_graph_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `local_graph_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `t_image_format_id` char(2) DEFAULT '0',
  `image_format_id` tinyint(1) NOT NULL DEFAULT '0',
  `t_title` char(2) DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_cache` varchar(255) NOT NULL DEFAULT '',
  `t_height` char(2) DEFAULT '0',
  `height` mediumint(8) NOT NULL DEFAULT '0',
  `t_width` char(2) DEFAULT '0',
  `width` mediumint(8) NOT NULL DEFAULT '0',
  `t_upper_limit` char(2) DEFAULT '0',
  `upper_limit` varchar(20) NOT NULL DEFAULT '0',
  `t_lower_limit` char(2) DEFAULT '0',
  `lower_limit` varchar(20) NOT NULL DEFAULT '0',
  `t_vertical_label` char(2) DEFAULT '0',
  `vertical_label` varchar(200) DEFAULT NULL,
  `t_slope_mode` char(2) DEFAULT '0',
  `slope_mode` char(2) DEFAULT 'on',
  `t_auto_scale` char(2) DEFAULT '0',
  `auto_scale` char(2) DEFAULT NULL,
  `t_auto_scale_opts` char(2) DEFAULT '0',
  `auto_scale_opts` tinyint(1) NOT NULL DEFAULT '0',
  `t_auto_scale_log` char(2) DEFAULT '0',
  `auto_scale_log` char(2) DEFAULT NULL,
  `t_scale_log_units` char(2) DEFAULT '0',
  `scale_log_units` char(2) DEFAULT NULL,
  `t_auto_scale_rigid` char(2) DEFAULT '0',
  `auto_scale_rigid` char(2) DEFAULT NULL,
  `t_auto_padding` char(2) DEFAULT '0',
  `auto_padding` char(2) DEFAULT NULL,
  `t_base_value` char(2) DEFAULT '0',
  `base_value` mediumint(8) NOT NULL DEFAULT '0',
  `t_grouping` char(2) DEFAULT '0',
  `grouping` char(2) NOT NULL DEFAULT '',
  `t_export` char(2) DEFAULT '0',
  `export` char(2) DEFAULT NULL,
  `t_unit_value` char(2) DEFAULT '0',
  `unit_value` varchar(20) DEFAULT NULL,
  `t_unit_exponent_value` char(2) DEFAULT '0',
  `unit_exponent_value` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `local_graph_id` (`local_graph_id`),
  KEY `graph_template_id` (`graph_template_id`),
  KEY `title_cache` (`title_cache`)
) ENGINE=MyISAM AUTO_INCREMENT=410 DEFAULT CHARSET=latin1 COMMENT='Stores the actual graph data.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_templates_graph`
--

LOCK TABLES `graph_templates_graph` WRITE;
/*!40000 ALTER TABLE `graph_templates_graph` DISABLE KEYS */;
INSERT INTO `graph_templates_graph` VALUES (2,0,0,2,'',1,'on','|host_description| - Traffic','','',120,'',500,'','100','','0','','bits per second','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(3,0,0,3,'',1,'on','|host_description| - Hard Drive Space','','',120,'',500,'','100','','0','','bytes','0','on','','on','',2,'','','0','','','on','','on','',1024,'0','','','on','','','',''),(4,0,0,4,'',1,'','|host_description| - CPU Usage','','',120,'',500,'','100','','0','','percent','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(5,0,0,5,'',1,'on','|host_description| - Wireless Levels','','',120,'',500,'','100','','0','','percent','0','on','','','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(6,0,0,6,'',1,'on','|host_description| - Wireless Transmissions','','',120,'',500,'','100','','0','','transmissions','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(7,0,0,7,'',1,'','|host_description| - Ping Latency','','',120,'',500,'','100','','0','','milliseconds','0','on','','on','',2,'','','0','','','','','on','',1000,'0','','','on','','','',''),(8,0,0,8,'',1,'','|host_description| - Processes','','',120,'',500,'','100','','0','','processes','0','on','','on','',2,'','','0','','','','','on','',1000,'0','','','on','','','',''),(9,0,0,9,'',1,'','|host_description| - Load Average','','',120,'',500,'','100','','0','','processes in the run queue','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','','0'),(10,0,0,10,'',1,'','|host_description| - Logged in Users','','',120,'',500,'','100','','0','','users','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(11,0,0,11,'',1,'','|host_description| - Load Average','','',120,'',500,'','100','','0','','processes in the run queue','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','','0'),(12,0,0,12,'',1,'','|host_description| - Memory Usage','','',120,'',500,'','100','','0','','kilobytes','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(13,0,0,13,'',1,'','|host_description| - Memory Usage','','',120,'',500,'','100','','0','','bytes','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(14,0,0,14,'',1,'','|host_description| - File System Cache','','',120,'',500,'','100','','0','','cache checks/hits','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(15,0,0,15,'',1,'','|host_description| - CPU Utilization','','',120,'',500,'','100','','0','','percent','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(16,0,0,16,'',1,'','|host_description| - File System Activity','','',120,'',500,'','100','','0','','reads/writes per sec','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(17,0,0,17,'',1,'','|host_description| - Logged In Users','','',120,'',500,'','100','','0','','users','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(18,0,0,18,'',1,'','|host_description| - CPU Usage','','',120,'',500,'','100','','0','','percent','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(19,0,0,19,'',1,'on','|host_description| - Volume Information','','',120,'',500,'','100','','0','','bytes','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(20,0,0,20,'',1,'','|host_description| - Directory Information','','',120,'',500,'','100','','0','','directory entries','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(21,0,0,21,'',1,'on','|host_description| - Available Disk Space','','',120,'',500,'','100','','0','','bytes','0','on','','on','',2,'','','0','','','on','','on','',1024,'0','','','on','','','',''),(22,0,0,22,'',1,'on','|host_description| - Errors/Discards','','',120,'',500,'','100','','0','','errors/sec','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(23,0,0,23,'',1,'on','|host_description| - Unicast Packets','','',120,'',500,'','100','','0','','packets/sec','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(24,0,0,24,'',1,'on','|host_description| - Non-Unicast Packets','','',120,'',500,'','100','','0','','packets/sec','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(25,0,0,25,'',1,'on','|host_description| - Traffic','','',120,'',500,'','100','','0','','bytes per second','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(34,0,0,26,'',1,'on','|host_description| - Available Disk Space','','',120,'',500,'','100','','0','','bytes','0','on','','on','',2,'','','0','','','on','','on','',1024,'0','','','on','','','',''),(35,0,0,27,'',1,'on','|host_description| - CPU Utilization','','',120,'',500,'','100','','0','','percent','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(36,0,0,28,'',1,'','|host_description| - Logged in Users','','',120,'',500,'','100','','0','','users','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(37,0,0,29,'',1,'','|host_description| - Processes','','',120,'',500,'','100','','0','','processes','0','on','','on','',2,'','','0','','','','','on','',1000,'0','','','on','','','',''),(38,12,1,12,'0',1,'0','|host_description| - Memory Usage','Localhost - Memory Usage','0',120,'0',500,'0','100','0','0','0','kilobytes','0','on','0','on','0',2,'0','','0','','0','on','0','on','0',1000,'0','','0','on','0','','0',''),(39,9,2,9,'0',1,'0','|host_description| - Load Average','Localhost - Load Average','0',120,'0',500,'0','100','0','0','0','processes in the run queue','0','on','0','on','0',2,'0','','0','','0','on','0','on','0',1000,'0','','0','on','0','','0','0'),(40,10,3,10,'0',1,'0','|host_description| - Logged in Users','Localhost - Logged in Users','0',120,'0',500,'0','100','0','0','0','users','0','on','0','on','0',2,'0','','0','','0','on','0','on','0',1000,'0','','0','on','0','','0',''),(41,8,4,8,'0',1,'0','|host_description| - Processes','Localhost - Processes','0',120,'0',500,'0','100','0','0','0','processes','0','on','0','on','0',2,'0','','0','','0','','0','on','0',1000,'0','','0','on','0','','0',''),(42,0,0,30,'',1,'','|host_description| - Open Files','','',120,'',500,'','100','','0','','files','0','on','','on','',2,'','','0','','','','','on','',1000,'0','','','on','','','',''),(43,0,0,31,'',1,'on','|host_description| - Traffic','','',120,'',500,'','100','','0','','bits per second','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(44,0,0,32,'',1,'on','|host_description| - Traffic','','',120,'',500,'','100','','0','','bits per second','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(45,0,0,33,'',1,'on','|host_description| - Traffic','','',120,'',500,'','100','','0','','bytes per second','0','on','','on','',2,'','','0','','','on','','on','',1000,'0','','','on','','','',''),(47,0,0,34,'',1,'on','|host_description| -','','',120,'',500,'','100','','0','on','','0','on','','on','',2,'','','0','','','','','on','',1000,'0','','','on','','','',''),(48,0,0,35,'',1,'','|host_description| - DOCSIS Signal Levels','','',120,'',500,'','0','','0','','','','on','','on','',1,'','','','','','','','on','',1000,'0','','','on','','','',''),(49,0,0,36,'',1,'','|host_description| - DOCSIS Stat Counters','','',120,'',500,'','0','','0','','','','on','','on','',1,'','','','','','','','on','',1000,'0','','','on','','','',''),(153,0,0,37,'',1,'','|host_description| - DOCSIS Signal Levels DS','','',120,'',500,'','0','','0','','','','on','','on','',1,'','','','','','','','on','',1000,'0','','','on','','','',''),(154,0,0,38,'',1,'','|host_description| - DOCSIS Signal Levels US','','',120,'',500,'','0','','0','','','','on','','on','',1,'','','','','','','','on','',1000,'0','','','on','','','',''),(371,0,0,39,'',1,'','|host_description| - DOCSIS Signal Levels DS','','',120,'',500,'','0','','0','','','','on','','on','',1,'','','','','','','','on','',1000,'0','','','on','','','',''),(402,0,0,40,'',1,'','|host_description| - SNR','','',120,'',500,'','100','','0','','','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','',''),(403,0,0,41,'',1,'','|host_description| - Number CM','','',120,'',500,'','1000','','0','','','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','',''),(404,0,0,42,'',1,'','|host_description| - ModProf','','',120,'',500,'','1000','','0','','','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','',''),(405,0,0,43,'',1,'','|host_description| - Power','','',120,'',500,'','100','','-100','','','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','',''),(406,0,0,44,'',1,'','|host_description| - Usage','','',120,'',500,'','100','','0','','','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','',''),(407,0,0,45,'',1,'','|host_description| - Number CM','','',120,'',500,'','1000','','0','','','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','',''),(408,0,0,46,'',1,'','|host_description| - Power','','',120,'',500,'','100','','0','','','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','',''),(409,0,0,47,'',1,'on','|host_description| - Packet Analysis','','',120,'',500,'','100','','0','','Packets','','on','','on','',2,'','','','','','on','','on','',1000,'0','','','on','','','','');
/*!40000 ALTER TABLE `graph_templates_graph` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_templates_item`
--

DROP TABLE IF EXISTS `graph_templates_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_templates_item` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `local_graph_template_item_id` int(12) unsigned NOT NULL DEFAULT '0',
  `local_graph_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `task_item_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `color_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `alpha` char(2) DEFAULT 'FF',
  `graph_type_id` tinyint(3) NOT NULL DEFAULT '0',
  `cdef_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `consolidation_function_id` tinyint(2) NOT NULL DEFAULT '0',
  `text_format` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `hard_return` char(2) DEFAULT NULL,
  `gprint_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sequence` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `graph_template_id` (`graph_template_id`),
  KEY `local_graph_id` (`local_graph_id`),
  KEY `task_item_id` (`task_item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6433 DEFAULT CHARSET=latin1 COMMENT='Stores the actual graph item data.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_templates_item`
--

LOCK TABLES `graph_templates_item` WRITE;
/*!40000 ALTER TABLE `graph_templates_item` DISABLE KEYS */;
INSERT INTO `graph_templates_item` VALUES (9,'0470b2427dbfadb6b8346e10a71268fa',0,0,2,54,22,'FF',7,2,1,'Inbound','','',2,1),(10,'84a5fe0db518550266309823f994ce9c',0,0,2,54,0,'FF',9,2,4,'Current:','','',2,2),(11,'2f222f28084085cd06a1f46e4449c793',0,0,2,54,0,'FF',9,2,1,'Average:','','',2,3),(12,'55acbcc33f46ee6d754e8e81d1b54808',0,0,2,54,0,'FF',9,2,3,'Maximum:','','on',2,4),(13,'fdaf2321fc890e355711c2bffc07d036',0,0,2,55,20,'FF',4,2,1,'Outbound','','',2,5),(14,'768318f42819217ed81196d2179d3e1b',0,0,2,55,0,'FF',9,2,4,'Current:','','',2,6),(15,'cb3aa6256dcb3acd50d4517b77a1a5c3',0,0,2,55,0,'FF',9,2,1,'Average:','','',2,7),(16,'671e989be7cbf12c623b4e79d91c7bed',0,0,2,55,0,'FF',9,2,3,'Maximum:','','on',2,8),(17,'b561ed15b3ba66d277e6d7c1640b86f7',0,0,3,4,48,'FF',7,14,1,'Used','','',2,1),(18,'99ef051057fa6adfa6834a7632e9d8a2',0,0,3,4,0,'FF',9,14,4,'Current:','','',2,2),(19,'3986695132d3f4716872df4c6fbccb65',0,0,3,4,0,'FF',9,14,1,'Average:','','',2,3),(20,'0444300017b368e6257f010dca8bbd0d',0,0,3,4,0,'FF',9,14,3,'Maximum:','','on',2,4),(21,'4d6a0b9063124ca60e2d1702b3e15e41',0,0,3,3,20,'FF',8,14,1,'Available','','',2,5),(22,'181b08325e4d00cd50b8cdc8f8ae8e77',0,0,3,3,0,'FF',9,14,4,'Current:','','',2,6),(23,'bba0a9ff1357c990df50429d64314340',0,0,3,3,0,'FF',9,14,1,'Average:','','',2,7),(24,'d4a67883d53bc1df8aead21c97c0bc52',0,0,3,3,0,'FF',9,14,3,'Maximum:','','on',2,8),(25,'253c9ec2d66905245149c1c2dc8e536e',0,0,3,0,1,'FF',5,15,1,'Total','','',2,9),(26,'ea9ea883383f4eb462fec6aa309ba7b5',0,0,3,0,0,'FF',9,15,4,'Current:','','',2,10),(27,'83b746bcaba029eeca170a9f77ec4864',0,0,3,0,0,'FF',9,15,1,'Average:','','',2,11),(28,'82e01dd92fd37887c0696192efe7af65',0,0,3,0,0,'FF',9,15,3,'Maximum:','','on',2,12),(29,'ff0a6125acbb029b814ed1f271ad2d38',0,0,4,5,9,'FF',7,0,1,'System','','',2,1),(30,'f0776f7d6638bba76c2c27f75a424f0f',0,0,4,5,0,'FF',9,0,4,'Current:','','',2,2),(31,'39f4e021aa3fed9207b5f45a82122b21',0,0,4,5,0,'FF',9,0,1,'Average:','','',2,3),(32,'800f0b067c06f4ec9c2316711ea83c1e',0,0,4,5,0,'FF',9,0,3,'Maximum:','','on',2,4),(33,'9419dd5dbf549ba4c5dc1462da6ee321',0,0,4,6,21,'FF',8,0,1,'User','','',2,5),(34,'e461dd263ae47657ea2bf3fd82bec096',0,0,4,6,0,'FF',9,0,4,'Current:','','',2,6),(35,'f2d1fbb8078a424ffc8a6c9d44d8caa0',0,0,4,6,0,'FF',9,0,1,'Average:','','',2,7),(36,'e70a5de639df5ba1705b5883da7fccfc',0,0,4,6,0,'FF',9,0,3,'Maximum:','','on',2,8),(37,'85fefb25ce9fd0317da2706a5463fc42',0,0,4,7,12,'FF',8,0,1,'Nice','','',2,9),(38,'a1cb26878776999db16f1de7577b3c2a',0,0,4,7,0,'FF',9,0,4,'Current:','','',2,10),(39,'7d0f9bf64a0898a0095f099674754273',0,0,4,7,0,'FF',9,0,1,'Average:','','',2,11),(40,'b2879248a522d9679333e1f29e9a87c3',0,0,4,7,0,'FF',9,0,3,'Maximum:','','on',2,12),(41,'d800aa59eee45383b3d6d35a11cdc864',0,0,4,0,1,'FF',4,12,1,'Total','','',2,13),(42,'cab4ae79a546826288e273ca1411c867',0,0,4,0,0,'FF',9,12,4,'Current:','','',2,14),(43,'d44306ae85622fec971507460be63f5c',0,0,4,0,0,'FF',9,12,1,'Average:','','',2,15),(44,'aa5c2118035bb83be497d4e099afcc0d',0,0,4,0,0,'FF',9,12,3,'Maximum:','','on',2,16),(45,'4aa34ea1b7542b770ace48e8bc395a22',0,0,5,9,48,'FF',7,0,1,'Signal Level','','',2,1),(46,'22f118a9d81d0a9c8d922efbbc8a9cc1',0,0,5,9,0,'FF',9,0,4,'Current:','','',2,2),(47,'229de0c4b490de9d20d8f8d41059f933',0,0,5,9,0,'FF',9,0,1,'Average:','','',2,3),(48,'cd17feb30c02fd8f21e4d4dcde04e024',0,0,5,9,0,'FF',9,0,3,'Maximum:','','on',2,4),(49,'8723600cfd0f8a7b3f7dc1361981aabd',0,0,5,8,25,'FF',5,0,1,'Noise Level','','',2,5),(50,'cb06be2601b5abfb7a42fc07586de1c2',0,0,5,8,0,'FF',9,0,4,'Current:','','',2,6),(51,'55a2ee0fd511e5210ed85759171de58f',0,0,5,8,0,'FF',9,0,1,'Average:','','',2,7),(52,'704459564c84e42462e106eef20db169',0,0,5,8,0,'FF',9,0,3,'Maximum:','','on',2,8),(53,'aaebb19ec522497eaaf8c87a631b7919',0,0,6,10,48,'FF',7,0,1,'Transmissions','','',2,1),(54,'8b54843ac9d41bce2fcedd023560ed64',0,0,6,10,0,'FF',9,0,4,'Current:','','',2,2),(55,'05927dc83e07c7d9cffef387d68f35c9',0,0,6,10,0,'FF',9,0,1,'Average:','','',2,3),(56,'d11e62225a7e7a0cdce89242002ca547',0,0,6,10,0,'FF',9,0,3,'Maximum:','','on',2,4),(57,'6397b92032486c476b0e13a35b727041',0,0,6,11,25,'FF',5,0,1,'Re-Transmissions','','',2,5),(58,'cdfa5f8f82f4c479ff7f6f54160703f6',0,0,6,11,0,'FF',9,0,4,'Current:','','',2,6),(59,'ce2a309fb9ef64f83f471895069a6f07',0,0,6,11,0,'FF',9,0,1,'Average:','','',2,7),(60,'9cbfbf57ebde435b27887f27c7d3caea',0,0,6,11,0,'FF',9,0,3,'Maximum:','','on',2,8),(61,'80e0aa956f50c261e5143273da58b8a3',0,0,7,21,25,'FF',7,0,1,'','','',2,1),(62,'48fdcae893a7b7496e1a61efc3453599',0,0,7,21,0,'FF',9,0,4,'Current:','','',2,2),(63,'22f43e5fa20f2716666ba9ed9a7d1727',0,0,7,21,0,'FF',9,0,1,'Average:','','',2,3),(64,'3e86d497bcded7af7ab8408e4908e0d8',0,0,7,21,0,'FF',9,0,3,'Maximum:','','on',2,4),(65,'ba00ecd28b9774348322ff70a96f2826',0,0,8,19,48,'FF',7,0,1,'Running Processes','','',2,1),(66,'8d76de808efd73c51e9a9cbd70579512',0,0,8,19,0,'FF',9,0,4,'Current:','','',3,2),(67,'304244ca63d5b09e62a94c8ec6fbda8d',0,0,8,19,0,'FF',9,0,1,'Average:','','',3,3),(68,'da1ba71a93d2ed4a2a00d54592b14157',0,0,8,19,0,'FF',9,0,3,'Maximum:','','on',3,4),(69,'93ad2f2803b5edace85d86896620b9da',0,0,9,12,15,'FF',7,0,1,'1 Minute Average','','',2,1),(70,'e28736bf63d3a3bda03ea9f1e6ecb0f1',0,0,9,12,0,'FF',9,0,4,'Current:','','on',4,2),(71,'bbdfa13adc00398eed132b1ccb4337d2',0,0,9,13,8,'FF',8,0,1,'5 Minute Average','','',2,3),(72,'2c14062c7d67712f16adde06132675d6',0,0,9,13,0,'FF',9,0,4,'Current:','','on',4,4),(73,'9cf6ed48a6a54b9644a1de8c9929bd4e',0,0,9,14,9,'FF',8,0,1,'15 Minute Average','','',2,5),(74,'c9824064305b797f38feaeed2352e0e5',0,0,9,14,0,'FF',9,0,4,'Current:','','on',4,6),(75,'fa1bc4eff128c4da70f5247d55b8a444',0,0,9,0,1,'FF',4,12,1,'','','on',2,7),(76,'5c94ac24bc0d6d2712cc028fa7d4c7d2',0,0,10,20,67,'FF',7,0,1,'Users','','',2,1),(77,'8bc7f905526f62df7d5c2d8c27c143c1',0,0,10,20,0,'FF',9,0,4,'Current:','','',3,2),(78,'cd074cd2b920aab70d480c020276d45b',0,0,10,20,0,'FF',9,0,1,'Average:','','',3,3),(79,'415630f25f5384ba0c82adbdb05fe98b',0,0,10,20,0,'FF',9,0,3,'Maximum:','','on',3,4),(80,'d77d2050be357ab067666a9485426e6b',0,0,11,33,15,'FF',7,0,1,'1 Minute Average','','',2,1),(81,'13d22f5a0eac6d97bf6c97d7966f0a00',0,0,11,33,0,'FF',9,0,4,'Current:','','on',4,2),(82,'8580230d31d2851ec667c296a665cbf9',0,0,11,34,8,'FF',8,0,1,'5 Minute Average','','',2,3),(83,'b5b7d9b64e7640aa51dbf58c69b86d15',0,0,11,34,0,'FF',9,0,4,'Current:','','on',4,4),(84,'2ec10edf4bfaa866b7efd544d4c3f446',0,0,11,35,9,'FF',8,0,1,'15 Minute Average','','',2,5),(85,'b65666f0506c0c70966f493c19607b93',0,0,11,35,0,'FF',9,0,4,'Current:','','on',4,6),(86,'6c73575c74506cfc75b89c4276ef3455',0,0,11,0,1,'FF',4,12,1,'Total','','on',2,7),(95,'5fa7c2317f19440b757ab2ea1cae6abc',0,0,12,16,41,'FF',7,14,1,'Free','','',2,9),(96,'b1d18060bfd3f68e812c508ff4ac94ed',0,0,12,16,0,'FF',9,14,4,'Current:','','',2,10),(97,'780b6f0850aaf9431d1c246c55143061',0,0,12,16,0,'FF',9,14,1,'Average:','','',2,11),(98,'2d54a7e7bb45e6c52d97a09e24b7fba7',0,0,12,16,0,'FF',9,14,3,'Maximum:','','on',2,12),(99,'40206367a3c192b836539f49801a0b15',0,0,12,18,30,'FF',8,14,1,'Swap','','',2,13),(100,'7ee72e2bb3722d4f8a7f9c564e0dd0d0',0,0,12,18,0,'FF',9,14,4,'Current:','','',2,14),(101,'c8af33b949e8f47133ee25e63c91d4d0',0,0,12,18,0,'FF',9,14,1,'Average:','','',2,15),(102,'568128a16723d1195ce6a234d353ce00',0,0,12,18,0,'FF',9,14,3,'Maximum:','','on',2,16),(103,'7517a40d478e28ed88ba2b2a65e16b57',0,0,13,37,52,'FF',7,14,1,'Memory Free','','',2,1),(104,'df0c8b353d26c334cb909dc6243957c5',0,0,13,37,0,'FF',9,14,4,'Current:','','',2,2),(105,'c41a4cf6fefaf756a24f0a9510580724',0,0,13,37,0,'FF',9,14,1,'Average:','','',2,3),(106,'9efa8f01c6ed11364a21710ff170f422',0,0,13,37,0,'FF',9,14,3,'Maximum:','','on',2,4),(107,'95d6e4e5110b456f34324f7941d08318',0,0,13,36,35,'FF',8,14,1,'Memory Buffers','','',2,5),(108,'0c631bfc0785a9cca68489ea87a6c3da',0,0,13,36,0,'FF',9,14,4,'Current:','','',2,6),(109,'3468579d3b671dfb788696df7dcc1ec9',0,0,13,36,0,'FF',9,14,1,'Average:','','',2,7),(110,'c3ddfdaa65449f99b7f1a735307f9abe',0,0,13,36,0,'FF',9,14,3,'Maximum:','','on',2,8),(111,'4c64d5c1ce8b5d8b94129c23b46a5fd6',0,0,14,28,41,'FF',7,0,1,'Cache Hits','','',2,1),(112,'5c1845c9bd1af684a3c0ad843df69e3e',0,0,14,28,0,'FF',9,0,4,'Current:','','',3,2),(113,'e5169563f3f361701902a8da3ac0c77f',0,0,14,28,0,'FF',9,0,1,'Average:','','',3,3),(114,'35e87262efa521edbb1fd27f09c036f5',0,0,14,28,0,'FF',9,0,3,'Maximum:','','on',3,4),(115,'53069d7dba4c31b338f609bea4cd16f3',0,0,14,27,66,'FF',8,0,1,'Cache Checks','','',2,5),(116,'d9c102579839c5575806334d342b50de',0,0,14,27,0,'FF',9,0,4,'Current:','','',3,6),(117,'dc1897c3249dbabe269af49cee92f8c0',0,0,14,27,0,'FF',9,0,1,'Average:','','',3,7),(118,'ccd21fe0b5a8c24057f1eff4a6b66391',0,0,14,27,0,'FF',9,0,3,'Maximum:','','on',3,8),(119,'ab09d41c358f6b8a9d0cad4eccc25529',0,0,15,76,9,'FF',7,0,1,'CPU Utilization','','',2,1),(120,'5d5b8d8fbe751dc9c86ee86f85d7433b',0,0,15,76,0,'FF',9,0,4,'Current:','','',3,2),(121,'4822a98464c6da2afff10c6d12df1831',0,0,15,76,0,'FF',9,0,1,'Average:','','',3,3),(122,'fc6fbf2a964bea0b3c88ed0f18616aa7',0,0,15,76,0,'FF',9,0,3,'Maximum:','','on',3,4),(123,'e4094625d5443b4c87f9a87ba616a469',0,0,16,25,67,'FF',7,0,1,'File System Reads','','',2,1),(124,'ae68425cd10e8a6623076b2e6859a6aa',0,0,16,25,0,'FF',9,0,4,'Current:','','',3,2),(125,'40b8e14c6568b3f6be6a5d89d6a9f061',0,0,16,25,0,'FF',9,0,1,'Average:','','',3,3),(126,'4afbdc3851c03e206672930746b1a5e2',0,0,16,25,0,'FF',9,0,3,'Maximum:','','on',3,4),(127,'ea47d2b5516e334bc5f6ce1698a3ae76',0,0,16,26,93,'FF',8,0,1,'File System Writes','','',2,5),(128,'899c48a2f79ea3ad4629aff130d0f371',0,0,16,26,0,'FF',9,0,4,'Current:','','',3,6),(129,'ab474d7da77e9ec1f6a1d45c602580cd',0,0,16,26,0,'FF',9,0,1,'Average:','','',3,7),(130,'e143f8b4c6d4eeb6a28b052e6b8ce5a9',0,0,16,26,0,'FF',9,0,3,'Maximum:','','on',3,8),(131,'facfeeb6fc2255ba2985b2d2f695d78a',0,0,17,23,30,'FF',7,0,1,'Current Logins','','',2,1),(132,'2470e43034a5560260d79084432ed14f',0,0,17,23,0,'FF',9,0,4,'Current:','','',3,2),(133,'e9e645f07bde92b52d93a7a1f65efb30',0,0,17,23,0,'FF',9,0,1,'Average:','','',3,3),(134,'bdfe0d66103211cfdaa267a44a98b092',0,0,17,23,0,'FF',9,0,3,'Maximum:','','on',3,4),(139,'098b10c13a5701ddb7d4d1d2e2b0fdb7',0,0,18,30,9,'FF',7,0,1,'CPU Usage','','',2,1),(140,'1dbda412a9926b0ee5c025aa08f3b230',0,0,18,30,0,'FF',9,0,4,'Current:','','',3,2),(141,'725c45917146807b6a4257fc351f2bae',0,0,18,30,0,'FF',9,0,1,'Average:','','',3,3),(142,'4e336fdfeb84ce65f81ded0e0159a5e0',0,0,18,30,0,'FF',9,0,3,'Maximum:','','on',3,4),(143,'7dab7a3ceae2addd1cebddee6c483e7c',0,0,19,39,25,'FF',7,14,1,'Free Space','','',2,5),(144,'aea239f3ceea8c63d02e453e536190b8',0,0,19,39,0,'FF',9,14,4,'Current:','','',2,6),(145,'a0efae92968a6d4ae099b676e0f1430e',0,0,19,39,0,'FF',9,14,1,'Average:','','',2,7),(146,'4fd5ba88be16e3d513c9231b78ccf0e1',0,0,19,39,0,'FF',9,14,3,'Maximum:','','on',2,8),(147,'d2e98e51189e1d9be8888c3d5c5a4029',0,0,19,38,69,'FF',7,14,1,'Total Space','','',2,1),(148,'12829294ee3958f4a31a58a61228e027',0,0,19,38,0,'FF',9,14,4,'Current:','','',2,2),(149,'4b7e8755b0f2253723c1e9fb21fd37b1',0,0,19,38,0,'FF',9,14,1,'Average:','','',2,3),(150,'cbb19ffd7a0ead2bf61512e86d51ee8e',0,0,19,38,0,'FF',9,14,3,'Maximum:','','on',2,4),(151,'37b4cbed68f9b77e49149343069843b4',0,0,19,40,95,'FF',5,14,1,'Freeable Space','','',2,9),(152,'5eb7532200f2b5cc93e13743a7db027c',0,0,19,40,0,'FF',9,14,4,'Current:','','',2,10),(153,'b0f9f602fbeaaff090ea3f930b46c1c7',0,0,19,40,0,'FF',9,14,1,'Average:','','',2,11),(154,'06477f7ea46c63272cee7253e7cd8760',0,0,19,40,0,'FF',9,14,3,'Maximum:','','on',2,12),(171,'a751838f87068e073b95be9555c57bde',0,0,21,56,0,'FF',9,14,3,'Maximum:','','on',2,4),(170,'3b13eb2e542fe006c9bf86947a6854fa',0,0,21,56,0,'FF',9,14,1,'Average:','','',2,3),(169,'8ef3e7fb7ce962183f489725939ea40f',0,0,21,56,0,'FF',9,14,4,'Current:','','',2,2),(167,'6ca2161c37b0118786dbdb46ad767e5d',0,0,21,56,48,'FF',7,14,1,'Used','','',2,1),(159,'6877a2a5362a9390565758b08b9b37f7',0,0,20,43,77,'FF',7,0,1,'Used Directory Entries','','',2,1),(160,'a978834f3d02d833d3d2def243503bf2',0,0,20,43,0,'FF',9,0,4,'Current:','','',3,2),(161,'7422d87bc82de20a4333bd2f6460b2d4',0,0,20,43,0,'FF',9,0,1,'Average:','','',3,3),(162,'4d52762859a3fec297ebda0e7fd760d9',0,0,20,43,0,'FF',9,0,3,'Maximum:','','on',3,4),(163,'999d4ed1128ff03edf8ea47e56d361dd',0,0,20,42,1,'FF',5,0,1,'Available Directory Entries','','',2,5),(164,'3dfcd7f8c7a760ac89d34398af79b979',0,0,20,42,0,'FF',9,0,4,'Current:','','',3,6),(165,'217be75e28505c8f8148dec6b71b9b63',0,0,20,42,0,'FF',9,0,1,'Average:','','',3,7),(166,'69b89e1c5d6fc6182c93285b967f970a',0,0,20,42,0,'FF',9,0,3,'Maximum:','','on',3,8),(172,'5d6dff9c14c71dc1ebf83e87f1c25695',0,0,21,44,20,'FF',8,14,1,'Available','','',2,5),(173,'b27cb9a158187d29d17abddc6fdf0f15',0,0,21,44,0,'FF',9,14,4,'Current:','','',2,6),(174,'6c0555013bb9b964e51d22f108dae9b0',0,0,21,44,0,'FF',9,14,1,'Average:','','',2,7),(175,'42ce58ec17ef5199145fbf9c6ee39869',0,0,21,44,0,'FF',9,14,3,'Maximum:','','on',2,8),(176,'9bdff98f2394f666deea028cbca685f3',0,0,21,0,1,'FF',5,15,1,'Total','','',2,9),(177,'fb831fefcf602bc31d9d24e8e456c2e6',0,0,21,0,0,'FF',9,15,4,'Current:','','',2,10),(178,'5a958d56785a606c08200ef8dbf8deef',0,0,21,0,0,'FF',9,15,1,'Average:','','',2,11),(179,'5ce67a658cec37f526dc84ac9e08d6e7',0,0,21,0,0,'FF',9,15,3,'Maximum:','','on',2,12),(180,'7e04a041721df1f8828381a9ea2f2154',0,0,22,47,31,'FF',4,0,1,'Discards In','','',2,1),(181,'afc8bca6b1b3030a6d71818272336c6c',0,0,22,47,0,'FF',9,0,4,'Current:','','',2,2),(182,'6ac169785f5aeaf1cc5cdfd38dfcfb6c',0,0,22,47,0,'FF',9,0,1,'Average:','','',2,3),(183,'178c0a0ce001d36a663ff6f213c07505',0,0,22,47,0,'FF',9,0,3,'Maximum:','','on',2,4),(184,'8e3268c0abde7550616bff719f10ee2f',0,0,22,46,48,'FF',4,0,1,'Errors In','','',2,5),(185,'18891392b149de63b62c4258a68d75f8',0,0,22,46,0,'FF',9,0,4,'Current:','','',2,6),(186,'dfc9d23de0182c9967ae3dabdfa55a16',0,0,22,46,0,'FF',9,0,1,'Average:','','',2,7),(187,'c47ba64e2e5ea8bf84aceec644513176',0,0,22,46,0,'FF',9,0,3,'Maximum:','','on',2,8),(188,'9d052e7d632c479737fbfaced0821f79',0,0,23,49,71,'FF',4,0,1,'Unicast Packets Out','','',2,5),(189,'9b9fa6268571b6a04fa4411d8e08c730',0,0,23,49,0,'FF',9,0,4,'Current:','','',2,6),(190,'8e8f2fbeb624029cbda1d2a6ddd991ba',0,0,23,49,0,'FF',9,0,1,'Average:','','',2,7),(191,'c76495beb1ed01f0799838eb8a893124',0,0,23,49,0,'FF',9,0,3,'Maximum:','','on',2,8),(192,'d4e5f253f01c3ea77182c5a46418fc44',0,0,23,48,25,'FF',7,0,1,'Unicast Packets In','','',2,1),(193,'526a96add143da021c5f00d8764a6c12',0,0,23,48,0,'FF',9,0,4,'Current:','','',2,2),(194,'81eeb46f451212f00fd7caee42a81c0b',0,0,23,48,0,'FF',9,0,1,'Average:','','',2,3),(195,'089e4d1c3faeb00fd5dcc9622b06d656',0,0,23,48,0,'FF',9,0,3,'Maximum:','','on',2,4),(196,'fe66cb973966d22250de073405664200',0,0,24,53,25,'FF',7,0,1,'Non-Unicast Packets In','','',2,1),(197,'1ba3fc3466ad32fdd2669cac6cad6faa',0,0,24,53,0,'FF',9,0,4,'Current:','','',2,2),(198,'f810154d3a934c723c21659e66199cdf',0,0,24,53,0,'FF',9,0,1,'Average:','','',2,3),(199,'98a161df359b01304346657ff1a9d787',0,0,24,53,0,'FF',9,0,3,'Maximum:','','on',2,4),(200,'d5e55eaf617ad1f0516f6343b3f07c5e',0,0,24,52,71,'FF',4,0,1,'Non-Unicast Packets Out','','',2,5),(201,'9fde6b8c84089b9f9044e681162e7567',0,0,24,52,0,'FF',9,0,4,'Current:','','',2,6),(202,'9a3510727c3d9fa7e2e7a015783a99b3',0,0,24,52,0,'FF',9,0,1,'Average:','','',2,7),(203,'451afd23f2cb59ab9b975fd6e2735815',0,0,24,52,0,'FF',9,0,3,'Maximum:','','on',2,8),(204,'617d10dff9bbc3edd9d733d9c254da76',0,0,22,50,18,'FF',4,0,1,'Discards Out','','',2,9),(205,'9269a66502c34d00ac3c8b1fcc329ac6',0,0,22,50,0,'FF',9,0,4,'Current:','','',2,10),(206,'d45deed7e1ad8350f3b46b537ae0a933',0,0,22,50,0,'FF',9,0,1,'Average:','','',2,11),(207,'2f64cf47dc156e8c800ae03c3b893e3c',0,0,22,50,0,'FF',9,0,3,'Maximum:','','on',2,12),(208,'57434bef8cb21283c1a73f055b0ada19',0,0,22,51,89,'FF',4,0,1,'Errors Out','','',2,13),(209,'660a1b9365ccbba356fd142faaec9f04',0,0,22,51,0,'FF',9,0,4,'Current:','','',2,14),(210,'28c5297bdaedcca29acf245ef4bbed9e',0,0,22,51,0,'FF',9,0,1,'Average:','','',2,15),(211,'99098604fd0c78fd7dabac8f40f1fb29',0,0,22,51,0,'FF',9,0,3,'Maximum:','','on',2,16),(212,'de3eefd6d6c58afabdabcaf6c0168378',0,0,25,54,22,'FF',7,0,1,'Inbound','','',2,1),(213,'1a80fa108f5c46eecb03090c65bc9a12',0,0,25,54,0,'FF',9,0,4,'Current:','','',2,2),(214,'fe458892e7faa9d232e343d911e845f3',0,0,25,54,0,'FF',9,0,1,'Average:','','',2,3),(215,'175c0a68689bebc38aad2fbc271047b3',0,0,25,54,0,'FF',9,0,3,'Maximum:','','on',2,4),(216,'1bf2283106510491ddf3b9c1376c0b31',0,0,25,55,20,'FF',4,0,1,'Outbound','','',2,5),(217,'c5202f1690ffe45600c0d31a4a804f67',0,0,25,55,0,'FF',9,0,4,'Current:','','',2,6),(218,'eb9794e3fdafc2b74f0819269569ed40',0,0,25,55,0,'FF',9,0,1,'Average:','','',2,7),(219,'6bcedd61e3ccf7518ca431940c93c439',0,0,25,55,0,'FF',9,0,3,'Maximum:','','on',2,8),(303,'b7b381d47972f836785d338a3bef6661',0,0,26,78,0,'FF',9,0,3,'Maximum:','','on',2,8),(304,'36fa8063df3b07cece878d54443db727',0,0,26,78,0,'FF',9,0,1,'Average:','','',2,7),(305,'2c35b5cae64c5f146a55fcb416dd14b5',0,0,26,78,0,'FF',9,0,4,'Current:','','',2,6),(306,'16d6a9a7f608762ad65b0841e5ef4e9c',0,0,26,78,48,'FF',7,0,1,'Used','','',2,5),(307,'d80e4a4901ab86ee39c9cc613e13532f',0,0,26,92,20,'FF',7,0,1,'Total','','',2,1),(308,'567c2214ee4753aa712c3d101ea49a5d',0,0,26,92,0,'FF',9,0,4,'Current:','','',2,2),(309,'ba0b6a9e316ef9be66abba68b80f7587',0,0,26,92,0,'FF',9,0,1,'Average:','','',2,3),(310,'4b8e4a6bf2757f04c3e3a088338a2f7a',0,0,26,92,0,'FF',9,0,3,'Maximum:','','on',2,4),(317,'8536e034ab5268a61473f1ff2f6bd88f',0,0,27,79,0,'FF',9,0,1,'Average:','','',3,3),(316,'d478a76de1df9edf896c9ce51506c483',0,0,27,79,0,'FF',9,0,4,'Current:','','',3,2),(315,'42537599b5fb8ea852240b58a58633de',0,0,27,79,9,'FF',7,0,1,'CPU Utilization','','',2,1),(318,'87e10f9942b625aa323a0f39b60058e7',0,0,27,79,0,'FF',9,0,3,'Maximum:','','on',3,4),(319,'38f6891b0db92aa8950b4ce7ae902741',0,0,28,81,67,'FF',7,0,1,'Users','','',2,1),(320,'af13152956a20aa894ef4a4067b88f63',0,0,28,81,0,'FF',9,0,4,'Current:','','',3,2),(321,'1b2388bbede4459930c57dc93645284e',0,0,28,81,0,'FF',9,0,1,'Average:','','',3,3),(322,'6407dc226db1d03be9730f4d6f3eeccf',0,0,28,81,0,'FF',9,0,3,'Maximum:','','on',3,4),(323,'fca6a530c8f37476b9004a90b42ee988',0,0,29,80,48,'FF',7,0,1,'Running Processes','','',2,1),(324,'5acebbde3dc65e02f8fda03955852fbe',0,0,29,80,0,'FF',9,0,4,'Current:','','',3,2),(325,'311079ffffac75efaab2837df8123122',0,0,29,80,0,'FF',9,0,1,'Average:','','',3,3),(326,'724d27007ebf31016cfa5530fee1b867',0,0,29,80,0,'FF',9,0,3,'Maximum:','','on',3,4),(373,'1995d8c23e7d8e1efa2b2c55daf3c5a7',0,0,32,54,22,'FF',7,2,1,'Inbound','','',2,1),(335,'',95,1,12,84,41,'FF',7,0,1,'Free','','',2,9),(336,'',96,1,12,84,0,'FF',9,0,4,'Current:','','',2,10),(337,'',97,1,12,84,0,'FF',9,0,1,'Average:','','',2,11),(338,'',98,1,12,84,0,'FF',9,0,3,'Maximum:','','on',2,12),(339,'',99,1,12,85,30,'FF',8,0,1,'Swap','','',2,13),(340,'',100,1,12,85,0,'FF',9,0,4,'Current:','','',2,14),(341,'',101,1,12,85,0,'FF',9,0,1,'Average:','','',2,15),(342,'',102,1,12,85,0,'FF',9,0,3,'Maximum:','','on',2,16),(343,'',69,2,9,86,15,'FF',7,0,1,'1 Minute Average','','',2,1),(344,'',70,2,9,86,0,'FF',9,0,4,'Current:','','on',4,2),(345,'',71,2,9,87,8,'FF',8,0,1,'5 Minute Average','','',2,3),(346,'',72,2,9,87,0,'FF',9,0,4,'Current:','','on',4,4),(347,'',73,2,9,88,9,'FF',8,0,1,'15 Minute Average','','',2,5),(348,'',74,2,9,88,0,'FF',9,0,4,'Current:','','on',4,6),(349,'',75,2,9,0,1,'FF',4,12,1,'','','',2,7),(350,'',76,3,10,89,67,'FF',7,0,1,'Users','','',2,1),(351,'',77,3,10,89,0,'FF',9,0,4,'Current:','','',3,2),(352,'',78,3,10,89,0,'FF',9,0,1,'Average:','','',3,3),(353,'',79,3,10,89,0,'FF',9,0,3,'Maximum:','','on',3,4),(354,'',65,4,8,90,48,'FF',7,0,1,'Running Processes','','',2,1),(355,'',66,4,8,90,0,'FF',9,0,4,'Current:','','',3,2),(356,'',67,4,8,90,0,'FF',9,0,1,'Average:','','',3,3),(357,'',68,4,8,90,0,'FF',9,0,3,'Maximum:','','on',3,4),(358,'803b96bcaec33148901b4b562d9d2344',0,0,30,29,89,'FF',7,0,1,'Open Files','','',2,1),(359,'da26dd92666cb840f8a70e2ec5e90c07',0,0,30,29,0,'FF',9,0,4,'Current:','','',3,2),(360,'5258970186e4407ed31cca2782650c45',0,0,30,29,0,'FF',9,0,1,'Average:','','',3,3),(361,'7d08b996bde9cdc7efa650c7031137b4',0,0,30,29,0,'FF',9,0,3,'Maximum:','','on',3,4),(362,'918e6e7d41bb4bae0ea2937b461742a4',0,0,31,54,22,'FF',7,2,1,'Inbound','','',2,1),(363,'f19fbd06c989ea85acd6b4f926e4a456',0,0,31,54,0,'FF',9,2,4,'Current:','','',2,2),(364,'fc150a15e20c57e11e8d05feca557ef9',0,0,31,54,0,'FF',9,2,1,'Average:','','',2,3),(365,'ccbd86e03ccf07483b4d29e63612fb18',0,0,31,54,0,'FF',9,2,3,'Maximum:','','on',2,4),(366,'964c5c30cd05eaf5a49c0377d173de86',0,0,31,55,20,'FF',4,2,1,'Outbound','','',2,5),(367,'b1a6fb775cf62e79e1c4bc4933c7e4ce',0,0,31,55,0,'FF',9,2,4,'Current:','','',2,6),(368,'721038182a872ab266b5cf1bf7f7755c',0,0,31,55,0,'FF',9,2,1,'Average:','','',2,7),(369,'2302f80c2c70b897d12182a1fc11ecd6',0,0,31,55,0,'FF',9,2,3,'Maximum:','','on',2,8),(370,'4ffc7af8533d103748316752b70f8e3c',0,0,31,0,0,'FF',1,0,1,'','','',2,9),(371,'64527c4b6eeeaf627acc5117ff2180fd',0,0,31,55,9,'FF',2,0,1,'95th Percentile','|95:bits:0:max:2|','',2,10),(372,'d5bbcbdbf83ae858862611ac6de8fc62',0,0,31,55,0,'FF',1,0,1,'(|95:bits:6:max:2| mbit in+out)','','on',2,11),(374,'55083351cd728b82cc4dde68eb935700',0,0,32,54,0,'FF',9,2,4,'Current:','','',2,2),(375,'54782f71929e7d1734ed5ad4b8dda50d',0,0,32,54,0,'FF',9,2,1,'Average:','','',2,3),(376,'88d3094d5dc2164cbf2f974aeb92f051',0,0,32,54,0,'FF',9,2,3,'Maximum:','','on',2,4),(377,'4a381a8e87d4db1ac99cf8d9078266d3',0,0,32,55,20,'FF',4,2,1,'Outbound','','',2,6),(378,'5bff63207c7bf076d76ff3036b5dad54',0,0,32,55,0,'FF',9,2,4,'Current:','','',2,7),(379,'979fff9d691ca35e3f4b3383d9cae43f',0,0,32,55,0,'FF',9,2,1,'Average:','','',2,8),(380,'0e715933830112c23c15f7e3463f77b6',0,0,32,55,0,'FF',9,2,3,'Maximum:','','on',2,11),(383,'5b43e4102600ad75379c5afd235099c4',0,0,32,54,0,'FF',1,0,1,'Total In:  |sum:auto:current:2:auto|','','on',2,5),(384,'db7c15d253ca666601b3296f2574edc9',0,0,32,55,0,'FF',1,0,1,'Total Out: |sum:auto:current:2:auto|','','on',2,12),(385,'fdaec5b9227522c758ad55882c483a83',0,0,33,55,0,'FF',9,0,3,'Maximum:','','on',2,11),(386,'6824d29c3f13fe1e849f1dbb8377d3f1',0,0,33,55,0,'FF',9,0,1,'Average:','','',2,8),(387,'54e3971b3dd751dd2509f62721c12b41',0,0,33,55,0,'FF',9,0,4,'Current:','','',2,7),(388,'cf8c9f69878f0f595d583eac109a9be1',0,0,33,55,20,'FF',4,0,1,'Outbound','','',2,6),(389,'de265acbbfa99eb4b3e9f7e90c7feeda',0,0,33,54,0,'FF',9,0,3,'Maximum:','','on',2,4),(390,'777aa88fb0a79b60d081e0e3759f1cf7',0,0,33,54,0,'FF',9,0,1,'Average:','','',2,3),(391,'66bfdb701c8eeadffe55e926d6e77e71',0,0,33,54,0,'FF',9,0,4,'Current:','','',2,2),(392,'3ff8dba1ca6279692b3fcabed0bc2631',0,0,33,54,22,'FF',7,0,1,'Inbound','','',2,1),(393,'d6041d14f9c8fb9b7ddcf3556f763c03',0,0,33,55,0,'FF',1,0,1,'Total Out: |sum:auto:current:2:auto|','','on',2,12),(394,'76ae747365553a02313a2d8a0dd55c8a',0,0,33,54,0,'FF',1,0,1,'Total In:  |sum:auto:current:2:auto|','','on',2,5),(403,'8a1b44ab97d3b56207d0e9e77a035d25',0,0,13,95,30,'FF',8,14,1,'Cache Memory','','',2,9),(404,'6db3f439e9764941ff43fbaae348f5dc',0,0,13,95,0,'FF',9,14,4,'Current:','','',2,10),(405,'cc9b2fe7acf0820caa61c1519193f65e',0,0,13,95,0,'FF',9,14,1,'Average:','','',2,11),(406,'9eea140bdfeaa40d50c5cdcd1f23f72d',0,0,13,95,0,'FF',9,14,3,'Maximum:','','on',2,12),(407,'41316670b1a36171de2bda91a0cc2364',0,0,34,96,98,'FF',7,0,1,'','','',2,1),(408,'c9e8cbdca0215b434c902e68755903ea',0,0,34,96,0,'FF',9,0,4,'Current:','','',2,2),(409,'dab91d7093e720841393feea5bdcba85',0,0,34,96,0,'FF',9,0,1,'Average:','','',2,3),(410,'03e5bd2151fea3c90843eb1130b84458',0,0,34,96,0,'FF',9,0,3,'Maximum:','','on',2,4),(411,'dac82551818de3c74df520cd0535d644',0,0,35,97,21,'FF',7,16,1,'Downstream Power (dBmV)','Downstream Power (dBmV)','',2,1),(412,'4a0d33bd62d8dcb536353f91d4b62779',0,0,35,97,0,'FF',9,16,4,'Current:','','',2,2),(413,'ccb4d56d46f76ca8247c2f427123521f',0,0,35,97,0,'FF',9,16,2,'Min:','','',2,3),(414,'c691caa6279e35ded1c8d619eae25e47',0,0,35,97,0,'FF',9,16,3,'Max:','','on',2,4),(415,'4d1fe281c1afc0519790ddcdf7c5e169',0,0,35,101,35,'FF',8,16,1,'Upstream Power (dBmV)','','',2,5),(416,'9fac95aca7e791fb338ed8e15b66ed9f',0,0,35,101,0,'FF',9,16,4,'Current:','','',2,6),(417,'2351f4196c281481d09b84551f4500ae',0,0,35,101,0,'FF',9,16,2,'Min:','','',2,7),(418,'069e98c3bc71d91d8b69144eead7502c',0,0,35,101,0,'FF',9,16,3,'Max:','','on',2,8),(419,'342a107c5fd33ebf295bc48c25179d7b',0,0,35,99,27,'FF',5,0,1,'Microreflections (dBc)','','',2,9),(420,'16a3b83b175d057c462851cd5df170dc',0,0,35,99,0,'FF',9,0,4,'Current:','','',2,10),(421,'c6caa4492a9c58de3be4942dea692ed0',0,0,35,99,0,'FF',9,0,2,'Min:','','',2,11),(422,'ac5f2134373dcc66dbf69defc16b539e',0,0,35,99,0,'FF',9,0,3,'Max:','','on',2,12),(423,'130719166d1c3f626dcbf7f255f6da02',0,0,35,98,96,'FF',5,16,1,'Signal/Noise ratio (dB)','','',2,13),(424,'5776e740fb7a6710a306114625e46fa6',0,0,35,98,0,'FF',9,16,4,'Current:','','',2,14),(425,'84c6790e039c8ad2002ff401281987ea',0,0,35,98,0,'FF',9,16,2,'Min:','','',2,15),(426,'a97545dcb622f64b926e1e1021d08b03',0,0,35,98,0,'FF',9,16,3,'Max:','','on',2,16),(427,'356615510e58585f747c5dad82c33b80',0,0,36,103,21,'FF',5,0,1,'Lost Syncs','','',2,1),(428,'1d9e7a8bfde11b25b65ce2145b4a522a',0,0,36,103,0,'FF',9,0,4,'Current:','','',2,2),(429,'fee1763e90b75110e0da12458508e405',0,0,36,103,0,'FF',9,0,1,'Average:','','',2,3),(430,'45137dbf1e696a43d957fbea3a7382cc',0,0,36,103,0,'FF',9,0,3,'Maximum:','','on',2,4),(431,'aeadc39a6ec01089879f7b438ff3eb20',0,0,36,100,91,'FF',5,0,1,'Ranging Timeouts:','','',2,5),(432,'2705101154105445aa50645d57af24d0',0,0,36,100,0,'FF',9,0,4,'Current:','','',2,6),(433,'9645c2fa4d7bac06cff4a622f8c8f3b3',0,0,36,100,0,'FF',9,0,1,'Average:','','',2,7),(434,'932b4064776ec93a907f5ad17b7acc52',0,0,36,100,0,'FF',9,0,3,'Maximum:','','on',2,8),(435,'76cdd198350935899b7496e2c10e4e94',0,0,36,102,35,'FF',5,0,1,'Resets:','','',2,9),(436,'c3e40f3701bd614349efc5308446f2ec',0,0,36,102,0,'FF',9,0,4,'Current:','','',2,10),(437,'3f9dc6bafe881368946ca7528a44c0af',0,0,36,102,0,'FF',9,0,1,'Average:','','',2,11),(438,'f33c0005b73e287801b8862ff41cb2f9',0,0,36,102,0,'FF',9,0,3,'Maximum:','','on',2,12),(439,'ebe60960acfd1c211ff9d26bcb46d0b0',0,0,36,104,28,'FF',5,0,1,'T1 Timeouts:','','',2,13),(440,'7d18bb0494eb240738249af0da558608',0,0,36,104,0,'FF',9,0,4,'Current:','','',2,14),(441,'f1bde13b57a1f4e79967c506955a63b8',0,0,36,104,0,'FF',9,0,1,'Average:','','',2,15),(442,'8e240a90e7af38ddbccff1dc5bc0ea56',0,0,36,104,0,'FF',9,0,3,'Maximum:','','on',2,16),(443,'3643d6127d479ef4460be576722223e6',0,0,36,105,55,'FF',5,0,1,'T2 Timeouts:','','',2,17),(444,'0b9cb9a252de73df3d5ad0b4b37ce2ea',0,0,36,105,0,'FF',9,0,4,'Current:','','',2,18),(445,'09aa3db349544c0c5a80e2cc4bf50d67',0,0,36,105,0,'FF',9,0,1,'Average:','','',2,19),(446,'620aaa72b43194f170af4a6990baecb2',0,0,36,105,0,'FF',9,0,3,'Maximum:','','on',2,20),(447,'d3a9d23a81a85a8fc471c3a3b720be95',0,0,36,106,37,'FF',5,0,1,'T3 Timeouts:','','',2,21),(448,'4768edc980c0413c2e34e9b849f83fae',0,0,36,106,0,'FF',9,0,4,'Current:','','',2,22),(449,'d8cf916a815f61b4a93c8520d5222b51',0,0,36,106,0,'FF',9,0,1,'Average:','','',2,23),(450,'1bcd568d9e9b1ef3b14459b31ed51f26',0,0,36,106,0,'FF',9,0,3,'Maximum:','','on',2,24),(451,'227b190b0ae2c3a3f522868e435ffd03',0,0,36,107,58,'FF',5,0,1,'T4 Timeouts:','','',2,25),(452,'c233fb4804a07029a9647a631d0d4705',0,0,36,107,0,'FF',9,0,4,'Current:','','',2,26),(453,'e9d3717760ecfb065cab4564c904829e',0,0,36,107,0,'FF',9,0,1,'Average:','','',2,27),(454,'50c38b977368ba678feb3f8c0a5de36b',0,0,36,107,0,'FF',9,0,3,'Maximum:','','on',2,28),(2715,'536ad04de48ea66d73d59d0a6bb61a59',0,0,37,97,21,'FF',7,16,1,'Downstream Power (dBmV)','Downstream Power (dBmV)','',2,1),(2716,'517d677b13cb5e741d15b7ab35eb04e8',0,0,37,97,0,'FF',9,16,4,'Current:','','',2,2),(2717,'6fff89425d493f24dc06d3adf1b3a3b9',0,0,37,97,0,'FF',9,16,2,'Min:','','',2,3),(2718,'6ca3e308db1cf2fbdafc405b963ff0a9',0,0,37,97,0,'FF',9,16,3,'Max:','','on',2,4),(2723,'4dc804a28d6d0d77604ed21e40883402',0,0,37,99,27,'FF',5,0,1,'Microreflections (dBc)','','',2,9),(2724,'12a08c471f54684866053f84a7968a1f',0,0,37,99,0,'FF',9,0,4,'Current:','','',2,10),(2725,'113071c36f81c9fba6b16b27928904a1',0,0,37,99,0,'FF',9,0,2,'Min:','','',2,11),(2726,'017fc94fcf81163da6ad3b22cbc262d6',0,0,37,99,0,'FF',9,0,3,'Max:','','on',2,12),(2727,'e55e0d3dec96696db342b4a4c5183298',0,0,37,98,96,'FF',5,16,1,'Signal/Noise ratio (dB)','','',2,13),(2728,'0a9532c4f46c9a710d645c3af4c7ab3c',0,0,37,98,0,'FF',9,16,4,'Current:','','',2,14),(2729,'9368602833ea0684ca893ca326c4c1de',0,0,37,98,0,'FF',9,16,2,'Min:','','',2,15),(2730,'1683971d002dd3e6cdf056490966f7da',0,0,37,98,0,'FF',9,16,3,'Max:','','on',2,16),(2735,'45a010445ef9de72b4547bc24aa93879',0,0,38,101,35,'FF',7,16,1,'Upstream Power (dBmV)','','',2,5),(2736,'7f2ae0c323e9f5cc9a17468c31052a30',0,0,38,101,0,'FF',9,16,4,'Current:','','',2,6),(2737,'3c9938943e40caf19a722ebe97758f8b',0,0,38,101,0,'FF',9,16,2,'Min:','','',2,7),(2738,'efbd98fd7dc1ea797a02059185b50cf5',0,0,38,101,0,'FF',9,16,3,'Max:','','on',2,8),(5990,'17be4b31d3a23d187bccc751f428500c',0,0,39,97,0,'FF',9,16,3,'Max:','','on',2,4),(5989,'133071e455a3cf99bec4d4de73479f55',0,0,39,97,0,'FF',9,16,2,'Min:','','',2,3),(5988,'701f100f603448050bbe64e39d99ce9e',0,0,39,97,0,'FF',9,16,4,'Current:','','',2,2),(5987,'9dedf9c2a26d25b6fd241660ef5327ac',0,0,39,97,21,'FF',7,16,1,'Downstream Power (dBmV)','Downstream Power (dBmV)','',2,1),(6383,'6bfb0032e19327901ef491c2f1aad9a5',0,0,40,3947,95,'FF',7,17,1,'SNR','','',2,9),(6384,'d34f0f9fe28d2115b9cad456e0014af9',0,0,40,3947,0,'FF',9,17,4,'Last','','',2,10),(6385,'cc342ec3187d7fd57e7742f8fb2d36c6',0,0,40,3947,0,'FF',9,17,1,'Average:','','',2,11),(6386,'f8ded9c9ede12a944c0e362257e4650a',0,0,40,3947,0,'FF',9,17,3,'Maximum:','','on',2,12),(6387,'1a2edb682f321cb4bf78d6f4f644de91',0,0,41,3948,95,'FF',7,0,1,'Active CM','','',2,9),(6388,'cd7e5e74dd97f583639074cd67bef29f',0,0,41,3948,0,'FF',9,0,4,'Last','','',2,10),(6389,'4760194f4fe1bfd825762523a8ca0aa6',0,0,41,3948,0,'FF',9,0,1,'Avg','','',2,11),(6390,'7871e0b9a3e385bf5fb1ba33650c0b12',0,0,41,3948,0,'FF',9,0,3,'Max','','',2,12),(6391,'2ba5b7501bdab418d921908d8bd2b660',0,0,41,3948,0,'FF',9,0,2,'Min','','on',2,13),(6392,'1b7a27517157d5afc365dda9d9b20177',0,0,41,3949,9,'FF',4,0,1,'Total CM','','',2,14),(6393,'ac2713b8ba9d15f66ad852cbf6399c9f',0,0,41,3949,0,'FF',9,0,4,'Last','','',2,15),(6394,'f85139fe9df4a1335f8fffd6b2068570',0,0,41,3949,0,'FF',9,0,1,'Avg','','',2,16),(6395,'f68d1fd6f777b68be356e3918bacccfd',0,0,41,3949,0,'FF',9,0,3,'Max','','',2,17),(6396,'5f353b49b6ba80a35626eb11e2cc90ae',0,0,41,3949,0,'FF',9,0,2,'Min','','on',2,18),(6397,'267b5902dce905993c3055384fa90e94',0,0,42,3950,95,'FF',7,0,1,'ModProf','','',2,9),(6398,'20a194f09f349706f6f1bc18e62b920b',0,0,42,3950,0,'FF',9,0,4,'','','',2,10),(6399,'f6ce18e14b5a2d73f80fa59dddb85a0c',0,0,43,3951,95,'FF',7,0,1,'Power','','',2,9),(6400,'b6461746fb5a3f4037722868a041cee8',0,0,43,3951,0,'FF',9,0,4,'Last','','',2,10),(6401,'a1655b80f7d6b1029b51f8703f2895bb',0,0,43,3951,0,'FF',9,0,1,'Average:','','',2,11),(6402,'be704af017e4d656fbb161a00548aaa6',0,0,43,3951,0,'FF',9,0,3,'Maximum:','','on',2,12),(6403,'bced6df1212922c3644735a5c2520d75',0,0,44,3952,95,'FF',7,0,1,'US Utilization (%)','','',2,9),(6404,'4ece2d44ac6ef4552196dfabdc039152',0,0,44,3952,0,'FF',9,0,4,'Last','','on',2,10),(6405,'5f12b3ea951f72dc534652cdd98e45f8',0,0,44,3952,0,'FF',9,0,1,'Average:','','on',2,11),(6406,'158a4dced80e58ab02bd9817dfbb5fc5',0,0,44,3952,0,'FF',9,0,3,'Maximum:','','on',2,12),(6407,'0c6a118c59af7c288731657721d88707',0,0,45,3954,95,'FF',7,0,1,'Active CM','','',2,9),(6408,'167f57ba7ea19ee101cf2ac9cde57cf8',0,0,45,3954,0,'FF',9,0,4,'Last','','',2,10),(6409,'eac004191b48bad4cca3473fdefb1d4c',0,0,45,3954,0,'FF',9,0,1,'Avg','','',2,11),(6410,'25f0f3fc2c2226d0a3c88625910636e5',0,0,45,3954,0,'FF',9,0,3,'Max','','',2,12),(6411,'95eb993f05f6795166da166c255f5f9e',0,0,45,3954,0,'FF',9,0,2,'Min','','on',2,13),(6412,'550a5ab8fd6874f930e0e227d5b8f4c6',0,0,45,3953,9,'FF',4,0,1,'Total CM','','',2,14),(6413,'5f8bee98d9f4200510901e15f59f2d34',0,0,45,3953,0,'FF',9,0,4,'Last','','',2,15),(6414,'32d547a64d331124b23617d6bee2df9a',0,0,45,3953,0,'FF',9,0,1,'Avg','','',2,16),(6415,'d3e269550b05ee82be26ca3d484b43dc',0,0,45,3953,0,'FF',9,0,3,'Max','','',2,17),(6416,'6cf60195a5bd6d2d76490913db9e6cd7',0,0,45,3953,0,'FF',9,0,2,'Min','','on',2,18),(6417,'14e5491e83a98e17f5850f2b25d64f65',0,0,46,3951,95,'FF',7,0,1,'Power','','',2,9),(6418,'54325941cba7ad9bfd4c01541728ab7a',0,0,46,3951,0,'FF',9,0,4,'Last','','',2,10),(6419,'f9ff9d4530671d99e8c1736957027dc9',0,0,46,3951,0,'FF',9,0,1,'Average:','','',2,11),(6420,'b514527cf190c370b251bff4f6d3429e',0,0,46,3951,0,'FF',9,0,3,'Maximum:','','on',2,12),(6421,'657f4d9d6bd29dc1371b449b362d8252',0,0,47,3955,22,'FF',7,0,1,'Valid','','',2,1),(6422,'96fc2d6ef53e5167291cd53ec164eb0e',0,0,47,3955,0,'FF',9,0,4,'Current:','','',2,2),(6423,'ddfdc25a3ca036bf9e3dbe3365ae8eba',0,0,47,3955,0,'FF',9,0,1,'Average:','','',2,3),(6424,'71ad2183ee51156de2e25461f3be4e15',0,0,47,3955,0,'FF',9,0,3,'Maximum:','','on',2,4),(6425,'0bc9c8f92091422c8134c60ea79afbbb',0,0,47,3956,20,'FF',4,0,1,'Corrected','','',2,5),(6426,'b011ae03675e8d1615b16ecf772544c6',0,0,47,3956,0,'FF',9,0,4,'Current:','','',2,6),(6427,'f27e61d15d869d4a8e1b78cfd0c885ee',0,0,47,3956,0,'FF',9,0,1,'Average:','','',2,7),(6428,'60965c61bda04b90f6499e3bf302ce58',0,0,47,3956,0,'FF',9,0,3,'Maximum:','','on',2,8),(6429,'24ac1db9f0458526ba38959b1b2a0317',0,0,47,3957,9,'FF',7,0,1,'Uncorrected','','',2,9),(6430,'f6afdc4d87486d63f1d89680b55901a9',0,0,47,3957,0,'FF',9,0,4,'Current','CURRENT','',2,10),(6431,'927a18395d583b7758ecf8dce58c8e3b',0,0,47,3957,0,'FF',9,0,1,'Average:','AVG','',2,11),(6432,'4c070c38ae152401bb9c1685c62f3404',0,0,47,3957,0,'FF',9,0,3,'Maximum:','max','',2,12);
/*!40000 ALTER TABLE `graph_templates_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_tree`
--

DROP TABLE IF EXISTS `graph_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_tree` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `sort_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_tree`
--

LOCK TABLES `graph_tree` WRITE;
/*!40000 ALTER TABLE `graph_tree` DISABLE KEYS */;
INSERT INTO `graph_tree` VALUES (1,1,'Default Tree'),(2,1,'Cable Modem');
/*!40000 ALTER TABLE `graph_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_tree_items`
--

DROP TABLE IF EXISTS `graph_tree_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_tree_items` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `graph_tree_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `local_graph_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rra_id` smallint(8) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `order_key` varchar(100) NOT NULL DEFAULT '0',
  `host_grouping_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `sort_children_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `graph_tree_id` (`graph_tree_id`),
  KEY `host_id` (`host_id`),
  KEY `local_graph_id` (`local_graph_id`),
  KEY `order_key` (`order_key`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_tree_items`
--

LOCK TABLES `graph_tree_items` WRITE;
/*!40000 ALTER TABLE `graph_tree_items` DISABLE KEYS */;
INSERT INTO `graph_tree_items` VALUES (7,1,0,0,'',1,'001000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',1,1);
/*!40000 ALTER TABLE `graph_tree_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host`
--

DROP TABLE IF EXISTS `host`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `host_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `description` varchar(150) NOT NULL DEFAULT '',
  `hostname` varchar(250) DEFAULT NULL,
  `notes` text,
  `snmp_community` varchar(100) DEFAULT NULL,
  `snmp_version` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `snmp_username` varchar(50) DEFAULT NULL,
  `snmp_password` varchar(50) DEFAULT NULL,
  `snmp_auth_protocol` char(5) DEFAULT '',
  `snmp_priv_passphrase` varchar(200) DEFAULT '',
  `snmp_priv_protocol` char(6) DEFAULT '',
  `snmp_context` varchar(64) DEFAULT '',
  `snmp_port` mediumint(5) unsigned NOT NULL DEFAULT '161',
  `snmp_timeout` mediumint(8) unsigned NOT NULL DEFAULT '500',
  `availability_method` smallint(5) unsigned NOT NULL DEFAULT '1',
  `ping_method` smallint(5) unsigned DEFAULT '0',
  `ping_port` int(12) unsigned DEFAULT '0',
  `ping_timeout` int(12) unsigned DEFAULT '500',
  `ping_retries` int(12) unsigned DEFAULT '2',
  `max_oids` int(12) unsigned DEFAULT '10',
  `device_threads` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `disabled` char(2) DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `status_event_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status_fail_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status_rec_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status_last_error` varchar(255) DEFAULT '',
  `min_time` decimal(10,5) DEFAULT '9.99999',
  `max_time` decimal(10,5) DEFAULT '0.00000',
  `cur_time` decimal(10,5) DEFAULT '0.00000',
  `avg_time` decimal(10,5) DEFAULT '0.00000',
  `total_polls` int(12) unsigned DEFAULT '0',
  `failed_polls` int(12) unsigned DEFAULT '0',
  `availability` decimal(8,5) NOT NULL DEFAULT '100.00000',
  PRIMARY KEY (`id`),
  KEY `disabled` (`disabled`)
) ENGINE=MyISAM AUTO_INCREMENT=113 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host`
--

LOCK TABLES `host` WRITE;
/*!40000 ALTER TABLE `host` DISABLE KEYS */;
INSERT INTO `host` VALUES (1,8,'Localhost','127.0.0.1','','public',0,'','','MD5','','DES','',161,500,3,2,23,400,1,10,1,'',3,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','',0.08000,0.14000,0.11000,0.09434,821,0,100.00000);
/*!40000 ALTER TABLE `host` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host_graph`
--

DROP TABLE IF EXISTS `host_graph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_graph` (
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_id`,`graph_template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host_graph`
--

LOCK TABLES `host_graph` WRITE;
/*!40000 ALTER TABLE `host_graph` DISABLE KEYS */;
INSERT INTO `host_graph` VALUES (1,8),(1,9),(1,10),(1,12);
/*!40000 ALTER TABLE `host_graph` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host_snmp_cache`
--

DROP TABLE IF EXISTS `host_snmp_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_snmp_cache` (
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `snmp_query_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `field_name` varchar(50) NOT NULL DEFAULT '',
  `field_value` varchar(255) DEFAULT NULL,
  `snmp_index` varchar(255) NOT NULL DEFAULT '',
  `oid` text NOT NULL,
  `present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`host_id`,`snmp_query_id`,`field_name`,`snmp_index`),
  KEY `host_id` (`host_id`,`field_name`),
  KEY `snmp_index` (`snmp_index`),
  KEY `field_name` (`field_name`),
  KEY `field_value` (`field_value`),
  KEY `snmp_query_id` (`snmp_query_id`),
  KEY `present` (`present`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host_snmp_cache`
--

LOCK TABLES `host_snmp_cache` WRITE;
/*!40000 ALTER TABLE `host_snmp_cache` DISABLE KEYS */;
INSERT INTO `host_snmp_cache` VALUES (1,6,'dskDevice','/dev/mapper/centos-root','/dev/mapper/centos-root','',1),(1,6,'dskDevice','/dev/vda1','/dev/vda1','',1),(1,6,'dskMount','/','/dev/mapper/centos-root','',1),(1,6,'dskMount','/boot','/dev/vda1','',1);
/*!40000 ALTER TABLE `host_snmp_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host_snmp_query`
--

DROP TABLE IF EXISTS `host_snmp_query`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_snmp_query` (
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `snmp_query_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sort_field` varchar(50) NOT NULL DEFAULT '',
  `title_format` varchar(50) NOT NULL DEFAULT '',
  `reindex_method` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_id`,`snmp_query_id`),
  KEY `host_id` (`host_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host_snmp_query`
--

LOCK TABLES `host_snmp_query` WRITE;
/*!40000 ALTER TABLE `host_snmp_query` DISABLE KEYS */;
INSERT INTO `host_snmp_query` VALUES (1,6,'dskDevice','|query_dskDevice|',0);
/*!40000 ALTER TABLE `host_snmp_query` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host_template`
--

DROP TABLE IF EXISTS `host_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host_template`
--

LOCK TABLES `host_template` WRITE;
/*!40000 ALTER TABLE `host_template` DISABLE KEYS */;
INSERT INTO `host_template` VALUES (1,'4855b0e3e553085ed57219690285f91f','Generic SNMP-enabled Host'),(3,'07d3fe6a52915f99e642d22e27d967a4','ucd/net SNMP Host'),(4,'4e5dc8dd115264c2e9f3adb725c29413','Karlnet Wireless Bridge'),(5,'cae6a879f86edacb2471055783bec6d0','Cisco Router'),(6,'9ef418b4251751e09c3c416704b01b01','Netware 4/5 Server'),(7,'5b8300be607dce4f030b026a381b91cd','Windows 2000/XP Host'),(8,'2d3e47f416738c2d22c87c40218cc55e','Local Linux Machine'),(9,'fe1afa65e5290efb1c1d2db8cff52f4c','cablemodem');
/*!40000 ALTER TABLE `host_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host_template_graph`
--

DROP TABLE IF EXISTS `host_template_graph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_template_graph` (
  `host_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_template_id`,`graph_template_id`),
  KEY `host_template_id` (`host_template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host_template_graph`
--

LOCK TABLES `host_template_graph` WRITE;
/*!40000 ALTER TABLE `host_template_graph` DISABLE KEYS */;
INSERT INTO `host_template_graph` VALUES (3,4),(3,11),(3,13),(5,18),(6,14),(6,16),(6,17),(6,30),(7,28),(7,29),(8,8),(8,9),(8,10),(8,12),(9,35),(9,36);
/*!40000 ALTER TABLE `host_template_graph` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host_template_snmp_query`
--

DROP TABLE IF EXISTS `host_template_snmp_query`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_template_snmp_query` (
  `host_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `snmp_query_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_template_id`,`snmp_query_id`),
  KEY `host_template_id` (`host_template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host_template_snmp_query`
--

LOCK TABLES `host_template_snmp_query` WRITE;
/*!40000 ALTER TABLE `host_template_snmp_query` DISABLE KEYS */;
INSERT INTO `host_template_snmp_query` VALUES (1,1),(3,1),(3,2),(4,1),(4,3),(5,1),(6,1),(6,4),(6,7),(7,1),(7,8),(7,9),(8,6);
/*!40000 ALTER TABLE `host_template_snmp_query` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugin_config`
--

DROP TABLE IF EXISTS `plugin_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_config` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `directory` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `author` varchar(64) NOT NULL DEFAULT '',
  `webpage` varchar(255) NOT NULL DEFAULT '',
  `version` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `directory` (`directory`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugin_config`
--

LOCK TABLES `plugin_config` WRITE;
/*!40000 ALTER TABLE `plugin_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugin_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugin_db_changes`
--

DROP TABLE IF EXISTS `plugin_db_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_db_changes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(16) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `column` varchar(64) NOT NULL,
  `method` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `plugin` (`plugin`),
  KEY `method` (`method`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugin_db_changes`
--

LOCK TABLES `plugin_db_changes` WRITE;
/*!40000 ALTER TABLE `plugin_db_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugin_db_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugin_hooks`
--

DROP TABLE IF EXISTS `plugin_hooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_hooks` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `hook` varchar(64) NOT NULL DEFAULT '',
  `file` varchar(255) NOT NULL DEFAULT '',
  `function` varchar(128) NOT NULL DEFAULT '',
  `status` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hook` (`hook`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugin_hooks`
--

LOCK TABLES `plugin_hooks` WRITE;
/*!40000 ALTER TABLE `plugin_hooks` DISABLE KEYS */;
INSERT INTO `plugin_hooks` VALUES (1,'internal','config_arrays','','plugin_config_arrays',1),(2,'internal','draw_navigation_text','','plugin_draw_navigation_text',1);
/*!40000 ALTER TABLE `plugin_hooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugin_realms`
--

DROP TABLE IF EXISTS `plugin_realms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_realms` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(32) NOT NULL DEFAULT '',
  `file` text NOT NULL,
  `display` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `plugin` (`plugin`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugin_realms`
--

LOCK TABLES `plugin_realms` WRITE;
/*!40000 ALTER TABLE `plugin_realms` DISABLE KEYS */;
INSERT INTO `plugin_realms` VALUES (1,'internal','plugins.php','Plugin Management');
/*!40000 ALTER TABLE `plugin_realms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller`
--

DROP TABLE IF EXISTS `poller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `hostname` varchar(250) NOT NULL DEFAULT '',
  `ip_address` int(11) unsigned NOT NULL DEFAULT '0',
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller`
--

LOCK TABLES `poller` WRITE;
/*!40000 ALTER TABLE `poller` DISABLE KEYS */;
/*!40000 ALTER TABLE `poller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_command`
--

DROP TABLE IF EXISTS `poller_command`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_command` (
  `poller_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `action` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `command` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`poller_id`,`action`,`command`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_command`
--

LOCK TABLES `poller_command` WRITE;
/*!40000 ALTER TABLE `poller_command` DISABLE KEYS */;
/*!40000 ALTER TABLE `poller_command` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_item`
--

DROP TABLE IF EXISTS `poller_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_item` (
  `local_data_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `poller_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `action` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `present` tinyint(4) NOT NULL DEFAULT '1',
  `hostname` varchar(250) NOT NULL DEFAULT '',
  `snmp_community` varchar(100) NOT NULL DEFAULT '',
  `snmp_version` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `snmp_username` varchar(50) NOT NULL DEFAULT '',
  `snmp_password` varchar(50) NOT NULL DEFAULT '',
  `snmp_auth_protocol` varchar(5) NOT NULL DEFAULT '',
  `snmp_priv_passphrase` varchar(200) NOT NULL DEFAULT '',
  `snmp_priv_protocol` varchar(6) NOT NULL DEFAULT '',
  `snmp_context` varchar(64) DEFAULT '',
  `snmp_port` mediumint(5) unsigned NOT NULL DEFAULT '161',
  `snmp_timeout` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rrd_name` varchar(19) NOT NULL DEFAULT '',
  `rrd_path` varchar(255) NOT NULL DEFAULT '',
  `rrd_num` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `rrd_step` mediumint(8) NOT NULL DEFAULT '300',
  `rrd_next_step` mediumint(8) NOT NULL DEFAULT '0',
  `arg1` text,
  `arg2` varchar(255) DEFAULT NULL,
  `arg3` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`local_data_id`,`rrd_name`),
  KEY `local_data_id` (`local_data_id`),
  KEY `host_id` (`host_id`),
  KEY `rrd_next_step` (`rrd_next_step`),
  KEY `action` (`action`),
  KEY `present` (`present`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_item`
--

LOCK TABLES `poller_item` WRITE;
/*!40000 ALTER TABLE `poller_item` DISABLE KEYS */;
INSERT INTO `poller_item` VALUES (3,0,1,1,1,'127.0.0.1','public',0,'','','MD5','','DES','',161,500,'mem_buffers','/usr/share/cacti/rra/localhost_mem_buffers_3.rrd',1,300,0,'perl /usr/share/cacti/scripts/linux_memory.pl MemFree:','',''),(4,0,1,1,1,'127.0.0.1','public',0,'','','MD5','','DES','',161,500,'mem_swap','/usr/share/cacti/rra/localhost_mem_swap_4.rrd',1,300,0,'perl /usr/share/cacti/scripts/linux_memory.pl SwapFree:','',''),(5,0,1,1,1,'127.0.0.1','public',0,'','','MD5','','DES','',161,500,'','/usr/share/cacti/rra/localhost_load_1min_5.rrd',1,300,0,'perl /usr/share/cacti/scripts/loadavg_multi.pl','',''),(6,0,1,1,1,'127.0.0.1','public',0,'','','MD5','','DES','',161,500,'users','/usr/share/cacti/rra/localhost_users_6.rrd',1,300,0,'perl /usr/share/cacti/scripts/unix_users.pl ','',''),(7,0,1,1,1,'127.0.0.1','public',0,'','','MD5','','DES','',161,500,'proc','/usr/share/cacti/rra/localhost_proc_7.rrd',1,300,0,'perl /usr/share/cacti/scripts/unix_processes.pl','','');
/*!40000 ALTER TABLE `poller_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_output`
--

DROP TABLE IF EXISTS `poller_output`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_output` (
  `local_data_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rrd_name` varchar(19) NOT NULL DEFAULT '',
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `output` text NOT NULL,
  PRIMARY KEY (`local_data_id`,`rrd_name`,`time`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_output`
--

LOCK TABLES `poller_output` WRITE;
/*!40000 ALTER TABLE `poller_output` DISABLE KEYS */;
INSERT INTO `poller_output` VALUES (119,'','2015-12-21 14:40:01','PHP Script Server has Started - Parent is cmd');
/*!40000 ALTER TABLE `poller_output` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_reindex`
--

DROP TABLE IF EXISTS `poller_reindex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_reindex` (
  `host_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_query_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `action` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `present` tinyint(4) NOT NULL DEFAULT '1',
  `op` char(1) NOT NULL DEFAULT '',
  `assert_value` varchar(100) NOT NULL DEFAULT '',
  `arg1` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`host_id`,`data_query_id`,`arg1`),
  KEY `present` (`present`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_reindex`
--

LOCK TABLES `poller_reindex` WRITE;
/*!40000 ALTER TABLE `poller_reindex` DISABLE KEYS */;
/*!40000 ALTER TABLE `poller_reindex` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poller_time`
--

DROP TABLE IF EXISTS `poller_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_time` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `poller_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poller_time`
--

LOCK TABLES `poller_time` WRITE;
/*!40000 ALTER TABLE `poller_time` DISABLE KEYS */;
INSERT INTO `poller_time` VALUES (1,2594,0,'2015-12-21 14:40:01','2015-12-21 14:40:38');
/*!40000 ALTER TABLE `poller_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rra`
--

DROP TABLE IF EXISTS `rra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rra` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `x_files_factor` double NOT NULL DEFAULT '0.1',
  `steps` mediumint(8) DEFAULT '1',
  `rows` int(12) NOT NULL DEFAULT '600',
  `timespan` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rra`
--

LOCK TABLES `rra` WRITE;
/*!40000 ALTER TABLE `rra` DISABLE KEYS */;
INSERT INTO `rra` VALUES (1,'c21df5178e5c955013591239eb0afd46','Daily (5 Minute Average)',0.5,1,600,86400),(2,'0d9c0af8b8acdc7807943937b3208e29','Weekly (30 Minute Average)',0.5,6,700,604800),(3,'6fc2d038fb42950138b0ce3e9874cc60','Monthly (2 Hour Average)',0.5,24,775,2678400),(4,'e36f3adb9f152adfa5dc50fd2b23337e','Yearly (1 Day Average)',0.5,288,797,33053184),(5,'283ea2bf1634d92ce081ec82a634f513','Hourly (1 Minute Average)',0.5,1,500,14400);
/*!40000 ALTER TABLE `rra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rra_cf`
--

DROP TABLE IF EXISTS `rra_cf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rra_cf` (
  `rra_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `consolidation_function_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rra_id`,`consolidation_function_id`),
  KEY `rra_id` (`rra_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rra_cf`
--

LOCK TABLES `rra_cf` WRITE;
/*!40000 ALTER TABLE `rra_cf` DISABLE KEYS */;
INSERT INTO `rra_cf` VALUES (1,1),(1,3),(2,1),(2,3),(3,1),(3,3),(4,1),(4,3),(5,1),(5,3);
/*!40000 ALTER TABLE `rra_cf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('path_rrdtool','/bin/rrdtool'),('path_php_binary','/bin/php'),('path_snmpwalk','/bin/snmpwalk'),('path_snmpget','/bin/snmpget'),('path_snmpbulkwalk','/bin/snmpbulkwalk'),('path_snmpgetnext','/bin/snmpgetnext'),('path_cactilog','/usr/share/cacti/log/cacti.log'),('snmp_version','net-snmp'),('rrdtool_version','rrd-1.4.x'),('poller_lastrun','1450705201'),('path_webroot','/usr/share/cacti'),('date','2015-12-21 14:40:01'),('stats_poller','Time:36.9977 Method:cmd.php Processes:1 Threads:N/A Hosts:22 HostsPerProcess:22 DataSources:89 RRDsProcessed:56'),('stats_recache','RecacheTime:0.0 HostsRecached:0'),('log_destination','1'),('log_snmp',''),('log_graph',''),('log_export',''),('log_verbosity','4'),('log_pstats','on'),('log_pwarn','on'),('log_perror','on'),('snmp_ver','2'),('snmp_community','public'),('snmp_username',''),('snmp_password',''),('snmp_auth_protocol','MD5'),('snmp_priv_passphrase',''),('snmp_priv_protocol','DES'),('snmp_timeout','500'),('snmp_port','161'),('snmp_retries','3'),('reindex_method','1'),('deletion_verification','on'),('auth_method','1'),('guest_user','guest'),('user_template','0'),('ldap_server',''),('ldap_port','389'),('ldap_port_ssl','636'),('ldap_version','3'),('ldap_encryption','0'),('ldap_referrals','0'),('ldap_mode','0'),('ldap_dn',''),('ldap_group_require',''),('ldap_group_dn',''),('ldap_group_attrib',''),('ldap_group_member_type','1'),('ldap_search_base',''),('ldap_search_filter',''),('ldap_specific_dn',''),('ldap_specific_password',''),('poller_enabled','on'),('poller_type','1'),('poller_interval','300'),('cron_interval','300'),('concurrent_processes','1'),('process_leveling','on'),('max_threads','1'),('php_servers','1'),('script_timeout','25'),('max_get_size','10'),('availability_method','2'),('ping_method','2'),('ping_port','23'),('ping_timeout','400'),('ping_retries','1'),('ping_failure_count','2'),('ping_recovery_count','2');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_graphs`
--

DROP TABLE IF EXISTS `settings_graphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_graphs` (
  `user_id` smallint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings_graphs`
--

LOCK TABLES `settings_graphs` WRITE;
/*!40000 ALTER TABLE `settings_graphs` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings_graphs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_tree`
--

DROP TABLE IF EXISTS `settings_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_tree` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `graph_tree_item_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`graph_tree_item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings_tree`
--

LOCK TABLES `settings_tree` WRITE;
/*!40000 ALTER TABLE `settings_tree` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_query`
--

DROP TABLE IF EXISTS `snmp_query`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snmp_query` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `xml_path` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_input_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_query`
--

LOCK TABLES `snmp_query` WRITE;
/*!40000 ALTER TABLE `snmp_query` DISABLE KEYS */;
INSERT INTO `snmp_query` VALUES (1,'d75e406fdeca4fcef45b8be3a9a63cbc','<path_cacti>/resource/snmp_queries/interface.xml','SNMP - Interface Statistics','Queries a host for a list of monitorable interfaces',0,2),(2,'3c1b27d94ad208a0090f293deadde753','<path_cacti>/resource/snmp_queries/net-snmp_disk.xml','ucd/net -  Get Monitored Partitions','Retrieves a list of monitored partitions/disks from a net-snmp enabled host.',0,2),(3,'59aab7b0feddc7860002ed9303085ba5','<path_cacti>/resource/snmp_queries/kbridge.xml','Karlnet - Wireless Bridge Statistics','Gets information about the wireless connectivity of each station from a Karlnet bridge.',0,2),(4,'ad06f46e22e991cb47c95c7233cfaee8','<path_cacti>/resource/snmp_queries/netware_disk.xml','Netware - Get Available Volumes','Retrieves a list of volumes from a Netware server.',0,2),(6,'8ffa36c1864124b38bcda2ae9bd61f46','<path_cacti>/resource/script_queries/unix_disk.xml','Unix - Get Mounted Partitions','Queries a list of mounted partitions on a unix-based host with the',0,11),(7,'30ec734bc0ae81a3d995be82c73f46c1','<path_cacti>/resource/snmp_queries/netware_cpu.xml','Netware - Get Processor Information','Gets information about running processors in a Netware server',0,2),(8,'9343eab1f4d88b0e61ffc9d020f35414','<path_cacti>/resource/script_server/host_disk.xml','SNMP - Get Mounted Partitions','Gets a list of partitions using SNMP',0,12),(9,'0d1ab53fe37487a5d0b9e1d3ee8c1d0d','<path_cacti>/resource/script_server/host_cpu.xml','SNMP - Get Processor Information','Gets usage for each processor in the system using the host MIB.',0,12),(10,'a1f4d6ee32fab4eb4a83f92d69564dbe','<path_cacti>/resource/snmp_queries/cisco_cmts.xml','Cisco CMTS','',0,2);
/*!40000 ALTER TABLE `snmp_query` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_query_graph`
--

DROP TABLE IF EXISTS `snmp_query_graph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snmp_query_graph` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `snmp_query_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `graph_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_query_graph`
--

LOCK TABLES `snmp_query_graph` WRITE;
/*!40000 ALTER TABLE `snmp_query_graph` DISABLE KEYS */;
INSERT INTO `snmp_query_graph` VALUES (2,'a4b829746fb45e35e10474c36c69c0cf',1,'In/Out Errors/Discarded Packets',22),(3,'01e33224f8b15997d3d09d6b1bf83e18',1,'In/Out Non-Unicast Packets',24),(4,'1e6edee3115c42d644dbd014f0577066',1,'In/Out Unicast Packets',23),(6,'da43655bf1f641b07579256227806977',2,'Available/Used Disk Space',3),(7,'1cc468ef92a5779d37a26349e27ef3ba',3,'Wireless Levels',5),(8,'bef2dc94bc84bf91827f45424aac8d2a',3,'Wireless Transmissions',6),(9,'ab93b588c29731ab15db601ca0bc9dec',1,'In/Out Bytes (64-bit Counters)',25),(10,'5a5ce35edb4b195cbde99fd0161dfb4e',4,'Volume Information (free, freeable space)',19),(11,'c1c2cfd33eaf5064300e92e26e20bc56',4,'Directory Information (total/available entries)',20),(13,'ae34f5f385bed8c81a158bf3030f1089',1,'In/Out Bits',2),(14,'1e16a505ddefb40356221d7a50619d91',1,'In/Out Bits (64-bit Counters)',2),(15,'a0b3e7b63c2e66f9e1ea24a16ff245fc',6,'Available Disk Space',21),(16,'d1e0d9b8efd4af98d28ce2aad81a87e7',1,'In/Out Bytes',25),(17,'f6db4151aa07efa401a0af6c9b871844',7,'Get Processor Utilization',15),(18,'46c4ee688932cf6370459527eceb8ef3',8,'Available Disk Space',26),(19,'4a515b61441ea5f27ab7dee6c3cb7818',9,'Get Processor Utilization',27),(20,'ed7f68175d7bb83db8ead332fc945720',1,'In/Out Bits with 95th Percentile',31),(21,'f85386cd2fc94634ef167c7f1e5fbcd0',1,'In/Out Bits with Total Bandwidth',32),(22,'7d309bf200b6e3cdb59a33493c2e58e0',1,'In/Out Bytes with Total Bandwidth',33),(23,'d9ceb91ae0a16e364d9ba192917a06c0',10,'SNR',40),(24,'dbeef70488c7afb0e9919fd1ecc1e9ab',10,'Number CM',41),(25,'16533670f83778d971fa3e293e2b9c17',10,'ModProf',42),(26,'7d83a769e507e8135896ff07b46cab7b',10,'Power',43),(27,'0acd0b8601984feedb9c9d2607dea5ad',10,'Usage',44),(28,'09930842c7300dccad428f77e0cc3006',10,'DS Number CM',45),(29,'eef124e481799f1cdfd833408e868dd8',10,'Power - Casa',46),(30,'1df06a306a6fa46a2347e33502fa0c35',10,'Packet Analysis',47);
/*!40000 ALTER TABLE `snmp_query_graph` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_query_graph_rrd`
--

DROP TABLE IF EXISTS `snmp_query_graph_rrd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snmp_query_graph_rrd` (
  `snmp_query_graph_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_template_rrd_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `snmp_field_name` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`snmp_query_graph_id`,`data_template_id`,`data_template_rrd_id`),
  KEY `data_template_rrd_id` (`data_template_rrd_id`),
  KEY `snmp_query_graph_id` (`snmp_query_graph_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_query_graph_rrd`
--

LOCK TABLES `snmp_query_graph_rrd` WRITE;
/*!40000 ALTER TABLE `snmp_query_graph_rrd` DISABLE KEYS */;
INSERT INTO `snmp_query_graph_rrd` VALUES (2,38,47,'ifInDiscards'),(3,40,52,'ifOutNUcastPkts'),(3,40,53,'ifInNUcastPkts'),(4,39,48,'ifInUcastPkts'),(2,38,51,'ifOutErrors'),(6,3,3,'dskAvail'),(6,3,4,'dskUsed'),(7,7,8,'kbWirelessStationExclHellos'),(7,8,9,'kbWirelessStationExclHellos'),(8,10,11,'kbWirelessStationExclHellos'),(8,9,10,'kbWirelessStationExclHellos'),(9,41,55,'ifHCOutOctets'),(9,41,54,'ifHCInOctets'),(10,35,38,'nwVolSize'),(10,35,40,'nwVolFreeable'),(10,35,39,'nwVolFree'),(11,36,42,'nwVolTotalDirEntries'),(11,36,43,'nwVolUsedDirEntries'),(2,38,50,'ifOutDiscards'),(2,38,46,'ifInErrors'),(13,41,54,'ifInOctets'),(14,41,54,'ifHCInOctets'),(14,41,55,'ifHCOutOctets'),(13,41,55,'ifOutOctets'),(4,39,49,'ifOutUcastPkts'),(15,37,44,'dskAvailable'),(16,41,54,'ifInOctets'),(16,41,55,'ifOutOctets'),(15,37,56,'dskUsed'),(17,42,76,'nwhrProcessorUtilization'),(18,43,78,'hrStorageUsed'),(18,43,92,'hrStorageSize'),(19,44,79,'hrProcessorLoad'),(20,41,55,'ifOutOctets'),(20,41,54,'ifInOctets'),(21,41,55,'ifOutOctets'),(21,41,54,'ifInOctets'),(22,41,55,'ifOutOctets'),(22,41,54,'ifInOctets'),(23,50,3947,'ifUpInSNR'),(24,51,3948,'ifUpACTIVE'),(24,52,3949,'ifUpTOTAL'),(25,53,3950,'ifUpChannelModulationProfile'),(26,54,3951,'ifUpChannelPower'),(27,55,3952,'ifUpUtilization'),(28,56,3953,'ifCableTOTAL'),(28,57,3954,'ifCableACTIVE'),(29,54,3951,'ifUpChannelPowerCasa'),(30,58,3955,'ifSigQUnerroreds'),(30,59,3956,'ifSigQCorrecteds'),(30,60,3957,'ifSigQUncorrectables');
/*!40000 ALTER TABLE `snmp_query_graph_rrd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_query_graph_rrd_sv`
--

DROP TABLE IF EXISTS `snmp_query_graph_rrd_sv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snmp_query_graph_rrd_sv` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `snmp_query_graph_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `data_template_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sequence` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `field_name` varchar(100) NOT NULL DEFAULT '',
  `text` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `snmp_query_graph_id` (`snmp_query_graph_id`),
  KEY `data_template_id` (`data_template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=134 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_query_graph_rrd_sv`
--

LOCK TABLES `snmp_query_graph_rrd_sv` WRITE;
/*!40000 ALTER TABLE `snmp_query_graph_rrd_sv` DISABLE KEYS */;
INSERT INTO `snmp_query_graph_rrd_sv` VALUES (10,'5d3a8b2f4a454e5b0a1494e00fe7d424',6,3,1,'name','|host_description| - Partition - |query_dskDevice|'),(11,'d0b49af67a83c258ef1eab3780f7b3dc',7,7,1,'name','|host_description| - Wireless Noise Level - |query_kbWirelessStationName|'),(12,'bf6b966dc369f3df2ea640a90845e94c',7,8,1,'name','|host_description| - Wireless Signal Level - |query_kbWirelessStationName|'),(13,'5c3616603a7ac9d0c1cb9556b377a74f',8,10,1,'name','|host_description| - Wireless Re-Transmissions - |query_kbWirelessStationName|'),(14,'080f0022f77044a512b083e3a8304e8b',8,9,1,'name','|host_description| - Wireless Transmissions - |query_kbWirelessStationName|'),(30,'8132fa9c446e199732f0102733cb1714',11,36,1,'name','|host_description| - Directories - |query_nwVolPhysicalName|'),(29,'8fc9a94a5f6ef902a3de0fa7549e7476',10,35,1,'name','|host_description| - Volumes - |query_nwVolPhysicalName|'),(80,'27eb220995925e1a5e0e41b2582a2af6',16,41,1,'rrd_maximum','|query_ifSpeed|'),(85,'e85ddc56efa677b70448f9e931360b77',14,41,1,'rrd_maximum','|query_ifSpeed|'),(84,'37bb8c5b38bb7e89ec88ea7ccacf44d4',14,41,4,'name','|host_description| - Traffic - |query_ifDescr|'),(83,'62a47c18be10f273a5f5a13a76b76f54',14,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|'),(32,'',12,37,1,'name','|host_description| - Partition - |query_dskDevice|'),(49,'6537b3209e0697fbec278e94e7317b52',2,38,1,'name','|host_description| - Errors - |query_ifIP| - |query_ifName|'),(50,'6d3f612051016f48c951af8901720a1c',2,38,2,'name','|host_description| - Errors - |query_ifName|'),(51,'62bc981690576d0b2bd0041ec2e4aa6f',2,38,3,'name','|host_description| - Errors - |query_ifIP|/|query_ifDescr|'),(52,'adb270d55ba521d205eac6a21478804a',2,38,4,'name','|host_description| - Errors - |query_ifDescr|'),(54,'77065435f3bbb2ff99bc3b43b81de8fe',3,40,1,'name','|host_description| - Non-Unicast Packets - |query_ifIP| - |query_ifName|'),(55,'240d8893092619c97a54265e8d0b86a1',3,40,2,'name','|host_description| - Non-Unicast Packets - |query_ifName|'),(56,'4b200ecf445bdeb4c84975b74991df34',3,40,3,'name','|host_description| - Non-Unicast Packets - |query_ifIP|/|query_ifDescr|'),(57,'d6da3887646078e4d01fe60a123c2179',3,40,4,'name','|host_description| - Non-Unicast Packets - |query_ifDescr|'),(59,'ce7769b97d80ca31d21f83dc18ba93c2',4,39,1,'name','|host_description| - Unicast Packets - |query_ifIP| - |query_ifName|'),(60,'1ee1f9717f3f4771f7f823ca5a8b83dd',4,39,2,'name','|host_description| - Unicast Packets - |query_ifName|'),(61,'a7dbd54604533b592d4fae6e67587e32',4,39,3,'name','|host_description| - Unicast Packets - |query_ifIP|/|query_ifDescr|'),(62,'b148fa7199edcf06cd71c89e5c5d7b63',4,39,4,'name','|host_description| - Unicast Packets - |query_ifDescr|'),(69,'cb09784ba05e401a3f1450126ed1e395',15,37,1,'name','|host_description| - Free Space - |query_dskDevice|'),(70,'87a659326af8c75158e5142874fd74b0',13,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|'),(72,'14aa2dead86bbad0f992f1514722c95e',13,41,2,'name','|host_description| - Traffic - |query_ifName|'),(73,'70390712158c3c5052a7d830fb456489',13,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|'),(74,'084efd82bbddb69fb2ac9bd0b0f16ac6',13,41,4,'name','|host_description| - Traffic - |query_ifDescr|'),(75,'7e093c535fa3d810fa76fc3d8c80c94b',13,41,1,'rrd_maximum','|query_ifSpeed|'),(76,'c7ee2110bf81639086d2da03d9d88286',16,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|'),(77,'8ef8ae2ef548892ab95bb6c9f0b3170e',16,41,2,'name','|host_description| - Traffic - |query_ifName|'),(78,'3a0f707d1c8fd0e061b70241541c7e2e',16,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|'),(79,'2347e9f53564a54d43f3c00d4b60040d',16,41,4,'name','|host_description| - Traffic - |query_ifDescr|'),(81,'2e8b27c63d98249096ad5bc320787f43',14,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|'),(82,'8d820d091ec1a9683cfa74a462f239ee',14,41,2,'name','|host_description| - Traffic - |query_ifName|'),(86,'c582d3b37f19e4a703d9bf4908dc6548',9,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|'),(88,'e1be83d708ed3c0b8715ccb6517a0365',9,41,2,'name','|host_description| - Traffic - |query_ifName|'),(89,'57a9ae1f197498ca8dcde90194f61cbc',9,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|'),(90,'0110e120981c7ff15304e4a85cb42cbe',9,41,4,'name','|host_description| - Traffic - |query_ifDescr|'),(91,'ce0b9c92a15759d3ddbd7161d26a98b7',9,41,1,'rrd_maximum','|query_ifSpeed|'),(92,'42277993a025f1bfd85374d6b4deeb60',17,42,1,'name','|host_description| - CPU Utilization - CPU|query_nwhrProcessorNum|'),(93,'a3f280327b1592a1a948e256380b544f',18,43,1,'name','|host_description| - Used Space - |query_hrStorageDescr|'),(94,'b5a724edc36c10891fa2a5c370d55b6f',19,44,1,'name','|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|'),(95,'7e87efd0075caba9908e2e6e569b25b0',20,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|'),(96,'dd28d96a253ab86846aedb25d1cca712',20,41,2,'name','|host_description| - Traffic - |query_ifName|'),(97,'ce425fed4eb3174e4f1cde9713eeafa0',20,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|'),(98,'d0d05156ddb2c65181588db4b64d3907',20,41,4,'name','|host_description| - Traffic - |query_ifDescr|'),(99,'3b018f789ff72cc5693ef79e3a794370',20,41,1,'rrd_maximum','|query_ifSpeed|'),(100,'b225229dbbb48c1766cf90298674ceed',21,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|'),(101,'c79248ddbbd195907260887b021a055d',21,41,2,'name','|host_description| - Traffic - |query_ifName|'),(102,'12a6750d973b7f14783f205d86220082',21,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|'),(103,'25b151fcfe093812cb5c208e36dd697e',21,41,4,'name','|host_description| - Traffic - |query_ifDescr|'),(104,'e9ab404a294e406c20fdd30df766161f',21,41,1,'rrd_maximum','|query_ifSpeed|'),(105,'119578a4f01ab47e820b0e894e5e5bb3',22,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|'),(106,'940e57d24b2623849c77b59ed05931b9',22,41,2,'name','|host_description| - Traffic - |query_ifName|'),(107,'0f045eab01bbc4437b30da568ed5cb03',22,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|'),(108,'bd70bf71108d32f0bf91b24c85b87ff0',22,41,4,'name','|host_description| - Traffic - |query_ifDescr|'),(109,'fdc4cb976c4b9053bfa2af791a21c5b5',22,41,1,'rrd_maximum','|query_ifSpeed|'),(110,'0d8908d464f5272c713ce52e7d23484d',23,50,2,'name','|host_description| - SNR - |query_ifDescr| - |query_ifAlias| '),(111,'0bbeeb2eca8e40ac24bd97737f2a92b3',23,50,3,'name','|host_description| - SNR - |query_ifAlias| '),(112,'bf3db28c5333e6e83c7caa7c440d8193',23,50,4,'name','|host_description| - SNR - |query_ifDescr| '),(113,'9b5fe787187d96df091e36c201dfebb3',24,51,1,'name','|host_description| - Active CM - |query_ifDescr| - |query_ifAlias| '),(114,'0e2c549c0c991b0c3a2721dc132bc95e',24,52,1,'name','|host_description| - Total CM - |query_ifDescr| - |query_ifAlias| '),(115,'be854f03f42503028e731c2e0694bcdd',24,51,2,'name','|host_description| - Active CM - |query_ifAlias|'),(116,'df1dab2125578448c135e81b4facba08',24,52,2,'name','|host_description| - Total CM - |query_ifAlias|'),(117,'ba88e14249d796fb6738489c69487e3e',24,52,3,'name','|host_description| - Total CM - |query_ifDescr| '),(118,'677985d56c7c94e54661401044d4ea7a',24,51,3,'name','|host_description| - Active CM - |query_ifDescr| '),(119,'6ced2ddbaa9541fdbff8e65f039a991d',25,53,1,'name','|host_description| - ModProf - |query_ifDescr| - |query_ifAlias| '),(120,'5ff384835fcb4c8e18ec6f22385f4897',25,53,2,'name','|host_description| - ModProf - |query_ifAlias|'),(121,'e7ae4d15439111fb4a09875a6614fc11',25,53,3,'name','|host_description| - ModProf - |query_ifDescr| '),(122,'11227543af527ad8a9f24e0078b64028',26,54,1,'name','|host_description| - Power - |query_ifDescr| - |query_ifAlias| '),(123,'893c32f4ea093d8c4199340002b74bc9',26,54,2,'name','|host_description| - Power - |query_ifAlias|'),(124,'b09def2ed2a8a80496098a6b22e7896b',26,54,3,'name',' |host_description| - Power - |query_ifDescr| '),(125,'602c4a90bd48c0591e2ac2339e1ac5b4',27,55,1,'name','|host_description| - Usage - |query_ifDescr| - |query_ifAlias| '),(126,'c8974593ba7b69945a26df98a47767a4',27,55,2,'name','|host_description| - US Usage - |query_ifAlias|'),(127,'86db6b8892d4aed7189aca2dcf18743b',27,55,3,'name',' |host_description| - US Usage - |query_ifDescr| '),(128,'8ee534fa502a1641009a326d7bce850b',28,57,1,'name','|host_description| - Active CM - |query_ifDescr| - |query_ifAlias| '),(129,'ae5a99c1fcabedf2c5bc96776f945b0b',28,56,1,'name','|host_description| - Total CM - |query_ifDescr| - |query_ifAlias| '),(130,'c125bb16adf87cd3bf58a9acf51cb1d5',29,54,1,'name','|host_description| - Power - |query_ifDescr| - |query_ifAlias|'),(131,'820b84e837f4be4ce85131152665151c',30,60,1,'title','|host_description| - Packet Analysis - |query_ifDescr| - |query_ifAlias|'),(132,'85d9190ecd58bd285da769619530449c',30,59,1,'name','|host_description| - Packet Analysis - |query_ifDescr| - |query_ifAlias|'),(133,'a833f4e3e096133cd54c811027b54f9d',30,58,1,'title','|host_description| - Packet Analysis - |query_ifDescr| - |query_ifAlias|');
/*!40000 ALTER TABLE `snmp_query_graph_rrd_sv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_query_graph_sv`
--

DROP TABLE IF EXISTS `snmp_query_graph_sv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snmp_query_graph_sv` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `snmp_query_graph_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sequence` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `field_name` varchar(100) NOT NULL DEFAULT '',
  `text` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `snmp_query_graph_id` (`snmp_query_graph_id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_query_graph_sv`
--

LOCK TABLES `snmp_query_graph_sv` WRITE;
/*!40000 ALTER TABLE `snmp_query_graph_sv` DISABLE KEYS */;
INSERT INTO `snmp_query_graph_sv` VALUES (7,'437918b8dcd66a64625c6cee481fff61',6,1,'title','|host_description| - Disk Space - |query_dskPath|'),(5,'2ddc61ff4bd9634f33aedce9524b7690',7,1,'title','|host_description| - Wireless Levels (|query_kbWirelessStationName|)'),(6,'c72e2da7af2cdbd6b44a5eb42c5b4758',8,1,'title','|host_description| - Wireless Transmissions (|query_kbWirelessStationName|)'),(11,'a412c5dfa484b599ec0f570979fdbc9e',10,1,'title','|host_description| - Volume Information - |query_nwVolPhysicalName|'),(12,'48f4792dd49fefd7d640ec46b1d7bdb3',11,1,'title','|host_description| - Directory Information - |query_nwVolPhysicalName|'),(14,'',12,1,'title','|host_description| - Disk Space - |query_dskDevice|'),(15,'49dca5592ac26ff149a4fbd18d690644',13,1,'title','|host_description| - Traffic - |query_ifName|'),(16,'bda15298139ad22bdc8a3b0952d4e3ab',13,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)'),(17,'29e48483d0471fcd996bfb702a5960aa',13,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|'),(18,'3f42d358965cb94ce4f708b59e04f82b',14,1,'title','|host_description| - Traffic - |query_ifName|'),(19,'45f44b2f811ea8a8ace1cbed8ef906f1',14,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)'),(20,'69c14fbcc23aecb9920b3cdad7f89901',14,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|'),(21,'299d3434851fc0d5c0e105429069709d',2,1,'title','|host_description| - Errors - |query_ifName|'),(22,'8c8860b17fd67a9a500b4cb8b5e19d4b',2,2,'title','|host_description| - Errors - |query_ifIP| (|query_ifDescr|)'),(23,'d96360ae5094e5732e7e7496ceceb636',2,3,'title','|host_description| - Errors - |query_ifDescr|/|query_ifIndex|'),(24,'750a290cadc3dc60bb682a5c5f47df16',3,1,'title','|host_description| - Non-Unicast Packets - |query_ifName|'),(25,'bde195eecc256c42ca9725f1f22c1dc0',3,2,'title','|host_description| - Non-Unicast Packets - |query_ifIP| (|query_ifDescr|)'),(26,'d9e97d22689e4ffddaca23b46f2aa306',3,3,'title','|host_description| - Non-Unicast Packets - |query_ifDescr|/|query_ifIndex|'),(27,'48ceaba62e0c2671a810a7f1adc5f751',4,1,'title','|host_description| - Unicast Packets - |query_ifName|'),(28,'d6258884bed44abe46d264198adc7c5d',4,2,'title','|host_description| - Unicast Packets - |query_ifIP| (|query_ifDescr|)'),(29,'6eb58d9835b2b86222306d6ced9961d9',4,3,'title','|host_description| - Unicast Packets - |query_ifDescr|/|query_ifIndex|'),(30,'f21b23df740bc4a2d691d2d7b1b18dba',15,1,'title','|host_description| - Disk Space - |query_dskDevice|'),(31,'7fb4a267065f960df81c15f9022cd3a4',16,1,'title','|host_description| - Traffic - |query_ifName|'),(32,'e403f5a733bf5c8401a110609683deb3',16,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)'),(33,'809c2e80552d56b65ca496c1c2fff398',16,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|'),(34,'0a5eb36e98c04ad6be8e1ef66caeed3c',9,1,'title','|host_description| - Traffic - |query_ifName|'),(35,'4c4386a96e6057b7bd0b78095209ddfa',9,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)'),(36,'fd3a384768b0388fa64119fe2f0cc113',9,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|'),(38,'9852782792ede7c0805990e506ac9618',18,1,'title','|host_description| - Used Space - |query_hrStorageDescr|'),(39,'fa2f07ab54fce72eea684ba893dd9c95',19,1,'title','|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|'),(40,'d99f8db04fd07bcd2260d246916e03da',17,1,'title','|host_description| - CPU Utilization - CPU|query_nwhrProcessorNum|'),(41,'f434ec853c479d424276f367e9806a75',20,1,'title','|host_description| - Traffic - |query_ifName|'),(42,'9b085245847444c5fb90ebbf4448e265',20,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)'),(43,'5977863f28629bd8eb93a2a9cbc3e306',20,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|'),(44,'37b6711af3930c56309cf8956d8bbf14',21,1,'title','|host_description| - Traffic - |query_ifName|'),(45,'cc435c5884a75421329a9b08207c1c90',21,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)'),(46,'82edeea1ec249c9818773e3145836492',21,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|'),(47,'87522150ee8a601b4d6a1f6b9e919c47',22,1,'title','|host_description| - Traffic - |query_ifName|'),(48,'993a87c04f550f1209d689d584aa8b45',22,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)'),(49,'183bb486c92a566fddcb0585ede37865',22,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|'),(50,'61bcd38fec0341d992507b9a175f8b5c',23,2,'title','|host_description| - SNR - |query_ifDescr| - |query_ifAlias|'),(51,'38f2b508fda0e7388726e4da90e2787a',23,3,'title','|host_description| - SNR - |query_ifAlias| '),(52,'17912f018058949c72b3798b8f0cee83',23,4,'title','|host_description| - SNR - |query_ifDescr| '),(53,'bf31c2dcbb4ff6f3eeebc05035b32563',24,1,'title','|host_description| - Number CM - |query_ifDescr| - |query_ifAlias| '),(54,'395fdd9add3c849052e5c84efe21b22e',24,2,'title','|host_description| - Number CM - |query_ifAlias|'),(55,'58db845d659a810c7a460d9e0b309fef',24,3,'title','|host_description| - Number CM - |query_ifDescr| '),(56,'4a65e1ddb80dc45bfc7a0efd4380b30b',25,1,'title','|host_description| - ModProf - |query_ifDescr| - |query_ifAlias| '),(57,'76e0bb9b21025b4138ab3f0b7c05f286',25,2,'title','|host_description| - ModProf - |query_ifAlias|'),(58,'7a0ae6d13841fdccc7ee157f73ad2f5b',25,3,'title','|host_description| - ModProf - |query_ifDescr| '),(59,'b53bdef4a20f422d6c4a4a12dc22a7fb',26,1,'title','|host_description| - Power - |query_ifDescr| - |query_ifAlias| '),(60,'ea7c9b66526c4ecb99e2d046972527b4',26,2,'title','|host_description| - Power - |query_ifAlias|'),(61,'743ba21073ed8e5313933f88780d4d3f',26,3,'title',' |host_description| - Power - |query_ifDescr| '),(62,'2c07d5fb86ca19f524b6f8ea3bb62699',27,1,'title','|host_description| - Usage - |query_ifDescr| - |query_ifAlias| '),(63,'b9789086db60ef4c0994e55fab1ba87f',27,2,'title','|host_description| - US Usage - |query_ifAlias|'),(64,'be3ad1f919f291bc92f6aa98486762de',27,3,'title',' |host_description| - US Usage - |query_ifDescr| '),(65,'57ddc534ac61c3a0c40fcdfe5e1b46e2',28,1,'title','|host_description| - Number CM - |query_ifDescr| - |query_ifAlias| '),(66,'5d1f8990cbee3cfd6c3d3f2f4b2a87e5',29,1,'title','|host_description| - Power - |query_ifDescr| - |query_ifAlias|'),(67,'5427e4dccc1a13c67e1d4e5694639a37',30,1,'title','|host_description| - Packet Analysis - |query_ifDescr| - |query_ifAlias|');
/*!40000 ALTER TABLE `snmp_query_graph_sv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_auth`
--

DROP TABLE IF EXISTS `user_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_auth` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '0',
  `password` varchar(50) NOT NULL DEFAULT '0',
  `realm` mediumint(8) NOT NULL DEFAULT '0',
  `full_name` varchar(100) DEFAULT '0',
  `must_change_password` char(2) DEFAULT NULL,
  `show_tree` char(2) DEFAULT 'on',
  `show_list` char(2) DEFAULT 'on',
  `show_preview` char(2) NOT NULL DEFAULT 'on',
  `graph_settings` char(2) DEFAULT NULL,
  `login_opts` tinyint(1) NOT NULL DEFAULT '1',
  `policy_graphs` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `policy_trees` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `policy_hosts` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `policy_graph_templates` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `enabled` char(2) NOT NULL DEFAULT 'on',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `realm` (`realm`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_auth`
--

LOCK TABLES `user_auth` WRITE;
/*!40000 ALTER TABLE `user_auth` DISABLE KEYS */;
INSERT INTO `user_auth` VALUES (1,'admin','106e7ce67153756f85c43fa2e729560c',0,'Administrator','','on','on','on','on',1,1,1,1,1,'on'),(3,'guest','c97df68175a4bdfca61616d41d87a276',0,'Guest Account','','on','on','on','on',3,1,1,1,1,'on');
/*!40000 ALTER TABLE `user_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_auth_perms`
--

DROP TABLE IF EXISTS `user_auth_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_auth_perms` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `item_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`item_id`,`type`),
  KEY `user_id` (`user_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_auth_perms`
--

LOCK TABLES `user_auth_perms` WRITE;
/*!40000 ALTER TABLE `user_auth_perms` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_auth_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_auth_realm`
--

DROP TABLE IF EXISTS `user_auth_realm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_auth_realm` (
  `realm_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`realm_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_auth_realm`
--

LOCK TABLES `user_auth_realm` WRITE;
/*!40000 ALTER TABLE `user_auth_realm` DISABLE KEYS */;
INSERT INTO `user_auth_realm` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(7,1),(7,3),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(101,1);
/*!40000 ALTER TABLE `user_auth_realm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_log`
--

DROP TABLE IF EXISTS `user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_log` (
  `username` varchar(50) NOT NULL DEFAULT '0',
  `user_id` mediumint(8) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `result` tinyint(1) NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`username`,`user_id`,`time`),
  KEY `username` (`username`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_log`
--

LOCK TABLES `user_log` WRITE;
/*!40000 ALTER TABLE `user_log` DISABLE KEYS */;
INSERT INTO `user_log` VALUES ('admin',1,'2015-12-18 18:15:45',1,'192.168.0.179'),('admin',0,'0000-00-00 00:00:00',3,'192.168.0.179'),('admin',0,'2015-12-19 11:10:32',0,'192.168.0.143'),('admin',0,'2015-12-19 11:10:41',0,'192.168.0.143'),('admin',1,'2015-12-19 11:11:08',1,'192.168.0.143'),('admin',1,'2015-12-21 08:56:57',1,'192.168.0.179');
/*!40000 ALTER TABLE `user_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `version` (
  `cacti` char(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `version`
--

LOCK TABLES `version` WRITE;
/*!40000 ALTER TABLE `version` DISABLE KEYS */;
INSERT INTO `version` VALUES ('0.8.8b');
/*!40000 ALTER TABLE `version` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-12-21 14:41:28
