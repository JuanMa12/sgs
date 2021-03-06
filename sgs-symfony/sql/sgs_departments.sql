CREATE DATABASE  IF NOT EXISTS `sgs` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `sgs`;
-- MySQL dump 10.13  Distrib 5.5.47, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sgs
-- ------------------------------------------------------
-- Server version	5.5.47-0ubuntu0.14.04.1-log

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
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nombre del Departamento',
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Codigo del Departamento',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_16AEB8D477153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contiene los departamentos de Colombia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'ANTIOQUIA','05'),(2,'ATLÁNTICO','08'),(3,'BOGOTÁ, D. C.','11'),(4,'BOLÍVAR','13'),(5,'BOYACÁ','15'),(6,'CALDAS','17'),(7,'CAQUETÁ','18'),(8,'CAUCA','19'),(9,'CESAR','20'),(10,'CÓRDOBA','23'),(11,'CUNDINAMARCA','25'),(12,'CHOCÓ','27'),(13,'HUILA','41'),(14,'LA GUAJIRA','44'),(15,'MAGDALENA','47'),(16,'META','50'),(17,'NARIÑO','52'),(18,'NORTE DE SANTANDER','54'),(19,'QUINDÍO','63'),(20,'RISARALDA','66'),(21,'SANTANDER','68'),(22,'SUCRE','70'),(23,'TOLIMA','73'),(24,'VALLE DEL CAUCA','76'),(25,'ARAUCA','81'),(26,'CASANARE','85'),(27,'PUTUMAYO','86'),(28,'ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y ','88'),(29,'AMAZONAS','91'),(30,'GUAINÍA','94'),(31,'GUAVIARE','95'),(32,'VAUPÉS','97'),(33,'VICHADA','99');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-02-22 15:25:19
