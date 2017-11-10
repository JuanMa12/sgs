CREATE DATABASE  IF NOT EXISTS `sgs` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `sgs`;
-- MySQL dump 10.13  Distrib 5.5.52, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sgs
-- ------------------------------------------------------
-- Server version	5.5.52-0ubuntu0.14.04.1-log

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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id Autonumerico de las ciudades',
  `profile_id` int(11) DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nombre de usuario',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Nombre',
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Email de usuario',
  `meta` longtext COLLATE utf8_unicode_ci COMMENT 'parametros de autenticacion',
  `type` int(11) NOT NULL COMMENT 'tipo de authenticacion',
  `role` int(11) NOT NULL COMMENT 'Rol del Usuario',
  `status` int(11) NOT NULL COMMENT 'Estado de usuario Usuario',
  `security_token` longtext COLLATE utf8_unicode_ci COMMENT 'Token de seguridad',
  PRIMARY KEY (`id`),
  KEY `IDX_1483A5E9CCFA12B8` (`profile_id`),
  CONSTRAINT `FK_1483A5E9CCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contiene el complemento de referencia a usuarios';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'admin','admin','ynrm@misena.edu.co','$2y$13$O1fIvbKTMmo7dZYcIVVyOe5XqMnSyXG58h97fHaJ7Tlk0.xHkm5d6',1,1,1,NULL);
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

-- Dump completed on 2017-11-05 17:53:12
