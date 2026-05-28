/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: sistema_correccion_db
-- ------------------------------------------------------
-- Server version	8.0.44-google

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
-- Table structure for table `aprobaciones`
--

DROP TABLE IF EXISTS `aprobaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aprobaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solicitud_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `accion` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `nota_sugerida_admin` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `solicitud_id` (`solicitud_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `aprobaciones_ibfk_1` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_correccion` (`id`),
  CONSTRAINT `aprobaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aprobaciones`
--

LOCK TABLES `aprobaciones` WRITE;
/*!40000 ALTER TABLE `aprobaciones` DISABLE KEYS */;
INSERT INTO `aprobaciones` (`id`, `solicitud_id`, `usuario_id`, `accion`, `comentario`, `nota_sugerida_admin`, `fecha`) VALUES (1,1,13,'Aprobado por docente','',NULL,'2026-04-21 05:05:12'),
(2,1,13,'Aprobado por docente (editado)','',NULL,'2026-04-21 05:44:35'),
(3,1,13,'Rechazado por docente (editado)','DISCULPE fue rechazada pero fue un error mio darle aprobar la solicitud el motivo es porque vi la revision y todo esta bien y no hubo error',NULL,'2026-04-21 05:45:56'),
(4,2,14,'Aprobado por docente','sera revisada solo es de confirmar por el coordinador',NULL,'2026-04-21 06:28:31'),
(5,2,17,'Aprobado por coordinador','',NULL,'2026-04-21 23:57:11'),
(6,2,18,'Finalizado por admin','se ha realizado su cambio de nota de la evaluacion 2 ',NULL,'2026-04-21 23:58:40'),
(7,3,13,'Aprobado por docente','okey sera revisado',NULL,'2026-04-22 00:32:33'),
(8,3,17,'Aprobado por coordinador','pasara para admi para que haga su correcion de nota de la evaluacion',NULL,'2026-04-22 00:33:25'),
(9,3,18,'Finalizado por admin','nota corregida de la evaluacion 2',NULL,'2026-04-22 00:34:33'),
(10,4,9,'Aprobado por docente','',NULL,'2026-04-22 00:42:52'),
(11,5,13,'Aprobado por docente','okey',NULL,'2026-04-28 06:13:34'),
(12,6,12,'Aprobado por docente','okey sera revisado',NULL,'2026-04-28 23:49:35'),
(13,6,17,'Aprobado por coordinador','esta bien la nota de evaluacion sera cambiada ',NULL,'2026-04-29 00:02:02'),
(14,4,17,'Aprobado por coordinador','',NULL,'2026-04-29 00:02:22'),
(15,5,17,'Aprobado por coordinador','',NULL,'2026-04-29 00:02:43'),
(16,1,13,'Aprobado por docente (editado)','he visto que tuve un error de calculo ',NULL,'2026-04-29 00:36:23'),
(17,1,17,'Aprobado por coordinador','',NULL,'2026-04-29 00:37:31'),
(18,7,12,'Aprobado por docente','',NULL,'2026-04-29 00:46:11'),
(19,6,18,'Finalizado por admin','',NULL,'2026-04-29 00:54:54'),
(20,5,18,'Finalizado por admin','',NULL,'2026-04-29 00:55:09'),
(21,4,18,'Finalizado por admin','',NULL,'2026-04-29 00:55:22'),
(22,1,18,'Finalizado por admin','',NULL,'2026-04-29 00:55:26'),
(23,7,17,'Aprobado por coordinador','',NULL,'2026-05-03 06:10:35'),
(24,7,18,'Finalizado por admin','Simon',NULL,'2026-05-26 16:52:14'),
(25,10,9,'Rechazado por docente','Casaca viejo, miami me lo confirmo',NULL,'2026-05-26 17:25:47'),
(26,11,9,'Rechazado por docente','Estaba probando, disculpame papito',NULL,'2026-05-26 17:35:20'),
(27,12,9,'Rechazado por docente','No me cuadra mucho eso',NULL,'2026-05-26 18:07:39'),
(28,13,9,'Aprobado por docente','Mira yo Diego que esta buieno','vos ponele 10 a este','2026-05-26 19:39:23'),
(29,13,17,'Aprobado por coordinador',NULL,NULL,'2026-05-26 19:39:58'),
(30,13,18,'Finalizado por admin','si',NULL,'2026-05-26 19:40:22'),
(31,14,9,'Aprobado por docente',NULL,'tiene razon','2026-05-26 19:54:14'),
(32,14,17,'Aprobado por coordinador','dfsdfsdf',NULL,'2026-05-26 19:54:40'),
(33,14,18,'Finalizado por admin','oki',NULL,'2026-05-26 19:55:53'),
(34,15,10,'Aprobado por docente','ok','La nota deberia ser 8.0 porque','2026-05-26 21:45:27'),
(35,15,17,'Aprobado por coordinador','si',NULL,'2026-05-26 21:46:17'),
(36,15,18,'Finalizado por admin',NULL,NULL,'2026-05-26 21:49:44'),
(37,16,9,'Aprobado por docente (excepción)','Fijate que a este men le dolia el pelo y lo tuvieron que operar, por eso hasta ahorita pudo hacer la solicitud, ayudenle ahi al viejazo',NULL,'2026-05-27 11:29:58'),
(38,16,17,'Rechazado por coordinador','wdesadasdfdasdfsa',NULL,'2026-05-27 12:07:34'),
(39,17,10,'Aprobado por docente (excepción)','ayuda por favor',NULL,'2026-05-27 12:25:14'),
(40,17,17,'Aprobado por coordinador','ayuda llevo 3 horas en esto',NULL,'2026-05-27 12:26:53'),
(41,17,18,'Finalizado por admin','Esta vieja es prima de nayib, le vamos a decir que si porque no vaya a ser va',NULL,'2026-05-27 12:28:11'),
(42,18,10,'Aprobado por docente (excepción)','ya por favor xd',NULL,'2026-05-27 13:02:29'),
(43,18,17,'Aprobado por coordinador','no mas por favorefoekjfsdmflkdmfñsd',NULL,'2026-05-27 13:03:06'),
(44,18,17,'Aprobado por coordinador','qwqdsadads',NULL,'2026-05-27 13:59:43');
/*!40000 ALTER TABLE `aprobaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asignaciones_docente`
--

DROP TABLE IF EXISTS `asignaciones_docente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaciones_docente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `docente_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `ciclo_id` int NOT NULL,
  `seccion` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01',
  `modalidad` enum('presencial','virtual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'presencial',
  `ciclo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('activa','retirada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_docente_materia_seccion_ciclo` (`docente_id`,`materia_id`,`seccion`,`ciclo_id`),
  UNIQUE KEY `unique_materia_seccion_ciclo` (`materia_id`,`seccion`,`ciclo_id`),
  KEY `ciclo_id` (`ciclo_id`),
  CONSTRAINT `asignaciones_docente_ibfk_1` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `asignaciones_docente_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  CONSTRAINT `asignaciones_docente_ibfk_3` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones_docente`
--

LOCK TABLES `asignaciones_docente` WRITE;
/*!40000 ALTER TABLE `asignaciones_docente` DISABLE KEYS */;
INSERT INTO `asignaciones_docente` (`id`, `docente_id`, `materia_id`, `ciclo_id`, `seccion`, `modalidad`, `ciclo`, `estado`) VALUES (1,9,1,1,'01','presencial','01-2026','activa'),
(2,9,2,1,'01','virtual','01-2026','activa'),
(3,10,1,1,'02','presencial','01-2026','activa'),
(4,10,2,1,'02','virtual','01-2026','activa'),
(5,10,3,1,'01','presencial','01-2026','activa'),
(6,11,4,1,'01','presencial','01-2026','activa'),
(15,11,4,1,'02','virtual','01-2026','activa'),
(16,11,5,1,'01','virtual','01-2026','activa'),
(17,12,6,1,'01','presencial','01-2026','activa'),
(18,12,5,1,'03','presencial','01-2026','activa'),
(19,13,7,1,'01','presencial','01-2026','activa'),
(20,13,7,1,'02','presencial','01-2026','activa'),
(21,13,8,1,'01','virtual','01-2026','activa'),
(22,14,9,1,'01','presencial','01-2026','activa'),
(23,14,8,1,'03','presencial','01-2026','activa');
/*!40000 ALTER TABLE `asignaciones_docente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asignaciones_estudiante`
--

DROP TABLE IF EXISTS `asignaciones_estudiante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaciones_estudiante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estudiante_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `ciclo_id` int NOT NULL,
  `seccion` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01',
  `modalidad` enum('presencial','virtual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'presencial',
  `ciclo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('activa','retirada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_estudiante_materia_seccion_ciclo` (`estudiante_id`,`materia_id`,`seccion`,`ciclo_id`),
  KEY `materia_id` (`materia_id`),
  KEY `ciclo_id` (`ciclo_id`),
  CONSTRAINT `asignaciones_estudiante_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `asignaciones_estudiante_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  CONSTRAINT `asignaciones_estudiante_ibfk_3` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones_estudiante`
--

LOCK TABLES `asignaciones_estudiante` WRITE;
/*!40000 ALTER TABLE `asignaciones_estudiante` DISABLE KEYS */;
INSERT INTO `asignaciones_estudiante` (`id`, `estudiante_id`, `materia_id`, `ciclo_id`, `seccion`, `modalidad`, `ciclo`, `estado`) VALUES (1,1,1,1,'01','presencial','01-2026','activa'),
(2,1,2,1,'01','virtual','01-2026','activa'),
(3,2,1,1,'02','presencial','01-2026','activa'),
(4,2,2,1,'02','virtual','01-2026','activa'),
(5,2,3,1,'01','presencial','01-2026','activa'),
(6,2,4,1,'01','virtual','01-2026','activa'),
(7,4,4,1,'01','presencial','01-2026','activa'),
(8,4,5,1,'01','virtual','01-2026','activa'),
(9,4,6,1,'01','presencial','01-2026','activa'),
(10,5,4,1,'02','virtual','01-2026','activa'),
(11,5,5,1,'03','presencial','01-2026','activa'),
(12,6,7,1,'01','presencial','01-2026','activa'),
(13,6,8,1,'01','virtual','01-2026','activa'),
(14,7,7,1,'02','presencial','01-2026','activa'),
(15,7,8,1,'01','virtual','01-2026','activa'),
(16,7,9,1,'01','presencial','01-2026','activa');
/*!40000 ALTER TABLE `asignaciones_estudiante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bitacora` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalle` text COLLATE utf8mb4_unicode_ci,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` (`id`, `usuario_id`, `accion`, `detalle`, `fecha`) VALUES (1,13,'Aprobado por docente','Accion registrada sobre solicitud #1','2026-04-21 05:05:12'),
(2,13,'Aprobado por docente (editado)','Accion registrada sobre solicitud #1','2026-04-21 05:44:35'),
(3,13,'Rechazado por docente (editado)','Accion registrada sobre solicitud #1','2026-04-21 05:45:56'),
(4,14,'Aprobado por docente','Accion registrada sobre solicitud #2','2026-04-21 06:28:31'),
(5,17,'Aprobado por coordinador','Accion registrada sobre solicitud #2','2026-04-21 23:57:11'),
(6,18,'Finalizado por admin','Accion registrada sobre solicitud #2','2026-04-21 23:58:40'),
(7,13,'Aprobado por docente','Accion registrada sobre solicitud #3','2026-04-22 00:32:33'),
(8,17,'Aprobado por coordinador','Accion registrada sobre solicitud #3','2026-04-22 00:33:25'),
(9,18,'Finalizado por admin','Accion registrada sobre solicitud #3','2026-04-22 00:34:33'),
(10,9,'Aprobado por docente','Accion registrada sobre solicitud #4','2026-04-22 00:42:52'),
(11,13,'Aprobado por docente','Accion registrada sobre solicitud #5','2026-04-28 06:13:34'),
(12,12,'Aprobado por docente','Accion registrada sobre solicitud #6','2026-04-28 23:49:35'),
(13,17,'Aprobado por coordinador','Accion registrada sobre solicitud #6','2026-04-29 00:02:02'),
(14,17,'Aprobado por coordinador','Accion registrada sobre solicitud #4','2026-04-29 00:02:22'),
(15,17,'Aprobado por coordinador','Accion registrada sobre solicitud #5','2026-04-29 00:02:43'),
(16,13,'Aprobado por docente (editado)','Accion registrada sobre solicitud #1','2026-04-29 00:36:23'),
(17,17,'Aprobado por coordinador','Accion registrada sobre solicitud #1','2026-04-29 00:37:31'),
(18,12,'Aprobado por docente','Accion registrada sobre solicitud #7','2026-04-29 00:46:11'),
(19,18,'Finalizado por admin','Accion registrada sobre solicitud #6','2026-04-29 00:54:54'),
(20,18,'Finalizado por admin','Accion registrada sobre solicitud #5','2026-04-29 00:55:09'),
(21,18,'Finalizado por admin','Accion registrada sobre solicitud #4','2026-04-29 00:55:22'),
(22,18,'Finalizado por admin','Accion registrada sobre solicitud #1','2026-04-29 00:55:26'),
(23,17,'Aprobado por coordinador','Accion registrada sobre solicitud #7','2026-05-03 06:10:35'),
(32,9,'Excepción creada por docente','Solicitud de excepción #16 creada para estudiante ID 1','2026-05-27 11:29:58'),
(33,10,'Excepción creada por docente','Solicitud de excepción #17 creada para estudiante ID 2','2026-05-27 12:25:14'),
(34,10,'Excepción creada por docente','Solicitud de excepción #18 creada para estudiante ID 2','2026-05-27 13:02:29');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carreras`
--

DROP TABLE IF EXISTS `carreras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carreras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facultad_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `facultad_id` (`facultad_id`),
  CONSTRAINT `carreras_ibfk_1` FOREIGN KEY (`facultad_id`) REFERENCES `facultades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carreras`
--

LOCK TABLES `carreras` WRITE;
/*!40000 ALTER TABLE `carreras` DISABLE KEYS */;
INSERT INTO `carreras` (`id`, `nombre`, `facultad_id`) VALUES (1,'Tecnico en Ingenieria de Software',1),
(2,'Ingenieria Industrial',1),
(3,'Arquitectura',1);
/*!40000 ALTER TABLE `carreras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ciclos_academicos`
--

DROP TABLE IF EXISTS `ciclos_academicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ciclos_academicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'inactivo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciclos_academicos`
--

LOCK TABLES `ciclos_academicos` WRITE;
/*!40000 ALTER TABLE `ciclos_academicos` DISABLE KEYS */;
INSERT INTO `ciclos_academicos` (`id`, `nombre`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES (1,'01-2026','2026-01-15','2026-06-30','activo'),
(2,'02-2026','2026-07-20','2026-12-15','inactivo');
/*!40000 ALTER TABLE `ciclos_academicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evidencias`
--

DROP TABLE IF EXISTS `evidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solicitud_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `solicitud_id` (`solicitud_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `evidencias_ibfk_1` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_correccion` (`id`),
  CONSTRAINT `evidencias_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evidencias`
--

LOCK TABLES `evidencias` WRITE;
/*!40000 ALTER TABLE `evidencias` DISABLE KEYS */;
INSERT INTO `evidencias` (`id`, `solicitud_id`, `usuario_id`, `archivo`, `descripcion`, `fecha`) VALUES (1,4,9,'evidencias/evidencia_docente_4_1776796972.png','Evidencia adjuntada por docente','2026-04-22 00:42:52'),
(2,6,12,'evidencias/evidencia_docente_6_1777398575.png','Evidencia adjuntada por docente','2026-04-28 23:49:35'),
(3,16,9,'evidencias/evidencia_excepcion_16_1779902997.png','Evidencia de excepción adjuntada por docente','2026-05-27 11:29:58'),
(4,17,10,'evidencias/evidencia_excepcion_17_1779906313.png','Evidencia de excepción adjuntada por docente','2026-05-27 12:25:14'),
(5,18,10,'evidencias/evidencia_excepcion_18_1779908548.png','Evidencia de excepción adjuntada por docente','2026-05-27 13:02:29'),
(6,19,2,'evidencias/evidencia_estudiante_19_1779918252.png','Evidencia adjuntada por estudiante','2026-05-27 15:44:13');
/*!40000 ALTER TABLE `evidencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facultades`
--

DROP TABLE IF EXISTS `facultades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facultades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `coordinador_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_facultades_coordinador` (`coordinador_id`),
  CONSTRAINT `fk_facultades_coordinador` FOREIGN KEY (`coordinador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facultades`
--

LOCK TABLES `facultades` WRITE;
/*!40000 ALTER TABLE `facultades` DISABLE KEYS */;
INSERT INTO `facultades` (`id`, `nombre`, `descripcion`, `coordinador_id`) VALUES (1,'Facultad de Informatica y Ciencias Aplicadas','Facultad principal del sistema',17);
/*!40000 ALTER TABLE `facultades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_notas`
--

DROP TABLE IF EXISTS `historial_notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_notas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solicitud_id` int NOT NULL,
  `nota_anterior` decimal(4,2) NOT NULL,
  `nota_nueva` decimal(4,2) NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `solicitud_id` (`solicitud_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `historial_notas_ibfk_1` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_correccion` (`id`),
  CONSTRAINT `historial_notas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_notas`
--

LOCK TABLES `historial_notas` WRITE;
/*!40000 ALTER TABLE `historial_notas` DISABLE KEYS */;
INSERT INTO `historial_notas` (`id`, `solicitud_id`, `nota_anterior`, `nota_nueva`, `usuario_id`, `fecha`) VALUES (1,3,4.99,6.99,18,'2026-04-22 00:34:33'),
(2,6,6.99,7.99,18,'2026-04-29 00:54:54'),
(3,5,6.70,6.90,18,'2026-04-29 00:55:09'),
(4,4,6.50,7.00,18,'2026-04-29 00:55:22'),
(5,1,5.99,7.99,18,'2026-04-29 00:55:26'),
(6,7,5.99,9.00,18,'2026-05-26 16:52:14'),
(7,13,9.00,9.00,18,'2026-05-26 19:40:22'),
(8,14,1.00,1.02,18,'2026-05-26 19:55:53'),
(9,15,9.00,8.00,18,'2026-05-26 21:49:44'),
(10,17,9.00,9.00,18,'2026-05-27 12:28:11');
/*!40000 ALTER TABLE `historial_notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscripciones_ciclo`
--

DROP TABLE IF EXISTS `inscripciones_ciclo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscripciones_ciclo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estudiante_id` int NOT NULL,
  `ciclo_id` int NOT NULL,
  `estado` enum('inscrito','retirado','finalizado') COLLATE utf8mb4_unicode_ci DEFAULT 'inscrito',
  `fecha_inscripcion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_inscripcion_ciclo` (`estudiante_id`,`ciclo_id`),
  KEY `ciclo_id` (`ciclo_id`),
  CONSTRAINT `inscripciones_ciclo_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `inscripciones_ciclo_ibfk_2` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscripciones_ciclo`
--

LOCK TABLES `inscripciones_ciclo` WRITE;
/*!40000 ALTER TABLE `inscripciones_ciclo` DISABLE KEYS */;
INSERT INTO `inscripciones_ciclo` (`id`, `estudiante_id`, `ciclo_id`, `estado`, `fecha_inscripcion`) VALUES (1,1,1,'inscrito','2026-05-13 20:23:00'),
(2,2,1,'inscrito','2026-05-13 20:23:00'),
(3,3,1,'inscrito','2026-05-13 20:23:00'),
(4,4,1,'inscrito','2026-05-13 20:23:00'),
(5,5,1,'inscrito','2026-05-13 20:23:00'),
(6,6,1,'inscrito','2026-05-13 20:23:00'),
(7,7,1,'inscrito','2026-05-13 20:23:00'),
(8,8,1,'inscrito','2026-05-13 20:23:00');
/*!40000 ALTER TABLE `inscripciones_ciclo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materias`
--

DROP TABLE IF EXISTS `materias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `materias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carrera_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `carrera_id` (`carrera_id`),
  CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materias`
--

LOCK TABLES `materias` WRITE;
/*!40000 ALTER TABLE `materias` DISABLE KEYS */;
INSERT INTO `materias` (`id`, `nombre`, `codigo`, `carrera_id`) VALUES (1,'Estructura de Datos','INF301',1),
(2,'Programacion Web Avanzada','INF302',1),
(3,'Seguridad de Sistemas','INF303',1),
(4,'Calculo Vectorial','MAT301',2),
(5,'Probabilidad y Estadistica','MAT302',2),
(6,'Fisica Aplicada','MAT303',2),
(7,'Dibujo Arquitectonico II','ARQ301',3),
(8,'Urbanismo','ARQ302',3),
(9,'Diseno Estructural','ARQ303',3);
/*!40000 ALTER TABLE `materias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas`
--

DROP TABLE IF EXISTS `notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estudiante_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `evaluacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nota` decimal(4,2) NOT NULL,
  `ciclo_id` int NOT NULL,
  `ciclo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nota_estudiante_materia_evaluacion_ciclo` (`estudiante_id`,`materia_id`,`evaluacion`,`ciclo_id`),
  KEY `materia_id` (`materia_id`),
  KEY `ciclo_id` (`ciclo_id`),
  CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  CONSTRAINT `notas_ibfk_3` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas`
--

LOCK TABLES `notas` WRITE;
/*!40000 ALTER TABLE `notas` DISABLE KEYS */;
INSERT INTO `notas` (`id`, `estudiante_id`, `materia_id`, `evaluacion`, `nota`, `ciclo_id`, `ciclo`) VALUES (1,7,8,'Evaluacion 2',6.99,1,'01-2026'),
(2,4,6,'Evaluacion 3',7.99,1,'01-2026'),
(3,7,7,'Evaluacion 3',6.90,1,'01-2026'),
(4,1,1,'Evaluacion 2',7.00,1,'01-2026'),
(5,7,7,'Evaluacion 2',7.99,1,'01-2026');
/*!40000 ALTER TABLE `notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos_correccion`
--

DROP TABLE IF EXISTS `periodos_correccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos_correccion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ciclo_id` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evaluacion` enum('Evaluacion 1','Evaluacion 2','Evaluacion 3','Evaluacion 4','Evaluacion 5') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ciclo_id` (`ciclo_id`),
  CONSTRAINT `periodos_correccion_ibfk_1` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos_correccion`
--

LOCK TABLES `periodos_correccion` WRITE;
/*!40000 ALTER TABLE `periodos_correccion` DISABLE KEYS */;
INSERT INTO `periodos_correccion` (`id`, `ciclo_id`, `fecha_inicio`, `fecha_fin`, `descripcion`, `evaluacion`, `estado`) VALUES (1,1,'2026-02-23','2026-03-21','Correccion de notas Evaluacion 1','Evaluacion 1',0),
(2,1,'2026-03-23','2026-04-25','Correccion de notas Evaluacion 2','Evaluacion 2',0),
(3,1,'2026-04-24','2026-05-23','Correccion de notas Evaluacion 3','Evaluacion 3',1),
(4,1,'2026-05-24','2026-06-24','Correccion de notas Evaluacion 4','Evaluacion 4',1),
(5,1,'2026-06-10','2026-06-27','Correccion de notas Evaluacion 5','Evaluacion 5',0);
/*!40000 ALTER TABLE `periodos_correccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('8J3Yybgtyrw3Mc4U5wCj26sDw5Ypb4OjbgF779jy',NULL,'169.254.169.126','Shodan-Pull/1.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicFJkdFRpY3M5VHI5R3JKcGpWUWtBRkFBdmxuWGhHVnV4S2ZCY24xMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OlNGMFk0bUxnb1l1TmRMa1kiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779994228),
('Gd760xej27pQMcZ7Nv1sfat8zq5vvWcs567baiXl',NULL,'169.254.169.126','visionheight.com/scan Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidTNGUEw5bUNKcjV2VGRFWnZNVWF5MzNlVTIyWEZSZXFWNW9OSDBIRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OmJzc25SNkR0ME04bERURGkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779997575),
('gfWsmpbLj8XMLZYmf3Vc9lYSvg4B1Z81rNPv3izm',NULL,'169.254.169.126','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoidEc0ek1MZ0RhRzdUbHdWc2tGQjNvSm5UVThFbFRBUndpQVlrZlphcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODg6Imh0dHA6Ly84LjIzMi4xNjYuMjIwL2luZGV4LnBocD9sYW5nPS4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkZ0bXAlMkZpbmRleDEiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RGNaZjBKYkFFZFVKWk9oZyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1779984742),
('gINrlaYsh2ydqm1vzoLG5vVBUo0fNdFitIpIUzzF',NULL,'169.254.169.126','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUFBVWHRMQXJnV2hlZXd4OGZ0cm1Nemxhb1pwT3VnaEtzMWFIODR1SSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OlZjdlg3Y0VqMEtYcDVXeVUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779989350),
('gm2LyqlrlpXLZnSSJ43iS67KN01TWgseaXtwS7Ke',NULL,'169.254.169.126','Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSVVPRGJwUXF1WjkySjBoMWV2M2FZYmtlNjFrT2dZMFdweGR6VTBZdSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6Ok5LUHNVazMwV3puVXFlSDQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779992143),
('H5Ncwj949f5MrTlkhEL0RlW5b6fYkz8Es77cAvLY',NULL,'169.254.169.126','Mozilla/5.0 (X11; U; Linux ppc; en-US; rv:1.8.1.13) Gecko/20080313 Iceape/1.1.9 (Debian-1.1.9-5)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV20zSU40VGtxZ3pHOWIzQmVDOUp1RWtHbldHdXJuUUM3S2hIM2ZJMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OmJzc25SNkR0ME04bERURGkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779996738),
('ibfa7VS5Wo0bijK0Zn4LfXxqdZ9TjvNVCODSxq4o',NULL,'169.254.169.126','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ3o1VlBORHhBb3hwbWZKb2FFc3ZuQzZySHV3Mm5XTUpTRjVPNUhkNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OjBmUEptaGdjdm9wUkJtVDUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779979914),
('Id8HFWioiYqoyvz85WbOe50pKOqan5S5M2y467lH',NULL,'169.254.169.126','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWndNN01PbzJYZGl0NG9ZZlI2V0NJeXI2c3ltVGRJNG9LUlJwaE9mMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OlNGMFk0bUxnb1l1TmRMa1kiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779993846),
('KjSirPSPbTWh8CiCUX2NVKFtuGVffINjP7WduS5Z',NULL,'169.254.169.126','Shodan-Pull/1.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWJ3VjB4dUhhT3dFSDdUMWtoTEo2aU5kc2tsVXRSZEFsYk1OSTEwYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OmxNQ0tXNXA0Rms0TGp0NlUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779978383),
('MEJCIF3heZSuy4khUXO9rXDYD3uSOvXSnYOp4wYO',NULL,'169.254.169.126','curl/7.64.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQkVHN2d0THI5WllKRnVNOW5FblVBWE5RUnRaNTQyNlFJeXJRd0JnNiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OmJzc25SNkR0ME04bERURGkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779997494),
('nWdUzgOkDBlmHmDIbVgccsCns6G7OkoAMxnRezSE',NULL,'169.254.169.126','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko/20100101 Firefox/65.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS3BQVzBmVWFMWE8zaVRnMnlSckw3dXB4TkRDc2o0dTZ1b0tkMU9pNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OlVCYzNtWjk4TEhVZXVmRWciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779991057),
('oGco14fcBEb1Wz9boIHG1j59qDKAcTUMond6xIb9',NULL,'169.254.169.126','visionheight.com/scan Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidDczZzZjN2hSbmFTbUh5eXhVcWhjNkp1WVdMOTNWSU93NG1JMGZ5eSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OmJzc25SNkR0ME04bERURGkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779997517),
('pR9rHZmYhtWC1aKpphnZoM0ASZKJAZoIKCjVFr0Q',NULL,'169.254.169.126','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU3lENUFGbUFIM1Bwb3VHWVFsdE1CZG5veHRucFlFd20yaExVM2s3UCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OlVCYzNtWjk4TEhVZXVmRWciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779990953),
('QbmkDmlgIQGdy3xj95ypv3CSZSxFWPbd2CcYRp5r',NULL,'169.254.169.126','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYXQ4UjlQWG9jRkIzU3cyVUNJbDdIakVUU1FrZ3pkdVBodGFWSFlxVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk5OiJodHRwOi8vOC4yMzIuMTY2LjIyMC9pbmRleC5waHA/JTJGJTNDJTNGZWNobyUyOG1kNSUyOCUyMmhpJTIyJTI5JTI5JTNCJTNGJTNFJTIwJTJGdG1wJTJGaW5kZXgxLnBocD0mY29uZmlnLWNyZWF0ZSUyMCUyRj0mbGFuZz0uLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGdXNyJTJGbG9jYWwlMkZsaWIlMkZwaHAlMkZwZWFyY21kIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkRjWmYwSmJBRWRVSlpPaGciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779984742),
('RrnsPmPNUzZK7CYY3a6yqMFRvNyhiLiTIM0oyVAl',NULL,'169.254.169.126','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY3lEbzBjT0hVdnBFY3JXYVlIUjZUclNIYVB0R3FxbnFQczg1Q2t0SiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly84LjIzMi4xNjYuMjIwLz9YREVCVUdfU0VTU0lPTl9TVEFSVD1waHBzdG9ybSI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpTRjBZNG1MZ29ZdU5kTGtZIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1779994246),
('rwg0ltRlTvtGS7MAqwMuklSE3uyyAkIK1FqcIwQ8',NULL,'169.254.169.126','masscan/1.0 (https://github.com/robertdavidgraham/masscan)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWVY3VU1EQVB5TzhzYld3cmdQc1llR3lITFJnQkY3YXpDQWllNFlaUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OmxNQ0tXNXA0Rms0TGp0NlUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779978515),
('SIHPslPguaNzBnYJmvSFGYnvLQqwW74l3gTIxDI3',NULL,'169.254.169.126','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZGdlQ05pUXVTeVpQS3dtUzVNR3djRmgyM0hrcWo3Wk0wY3prd1FmViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OkhZWWR1WGVhS3B6c2J3amkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779981927),
('TqfKwmSOYyBs3jtndP8kwwIvbyldtrPZOgyJyHAA',NULL,'169.254.169.126','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia09xY1VLa0lnTUZUYmZWNGU1dExaellRaDhlQmVoSGpvSUQ0dDRQTyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OlZBY054czBVcHd4ZFJBZUEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779988256),
('u9ITByrtxVGx5v9OjXGJtUj3s2PuXx8OPElG3Cc2',NULL,'169.254.169.126','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoidXdzU2k1SUpma2lxcTBYYUFMU2Z4ZjFOdDQxUjFwZjN2NGp0UGRENSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTQ2OiJodHRwOi8vOC4yMzIuMTY2LjIyMC9pbmRleC5waHA/ZnVuY3Rpb249Y2FsbF91c2VyX2Z1bmNfYXJyYXkmcz0lMkZpbmRleCUyRiU1Q3RoaW5rJTVDYXBwJTJGaW52b2tlZnVuY3Rpb24mdmFycyU1QjAlNUQ9bWQ1JnZhcnMlNUIxJTVEJTVCMCU1RD1IZWxsbyI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpEY1pmMEpiQUVkVUpaT2hnIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1779984742),
('ZQFhUedplotou31QQa1P7lWXhi4Vro5UCblKgZRF',NULL,'169.254.169.126','curl/7.74.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOEdsTzZGVlpXM0c0aTR2RXlpWkloVDVTWFN4Zk1mbVBPVGJZWHZYYiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OmJzc25SNkR0ME04bERURGkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779997497),
('ZXziozU1Jy6loJ5ZOHO95EWapPn9MTYrc75G16gy',NULL,'169.254.169.126','Mozilla/5.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaGpLTUJjbU4xN0V1T2NWSUZ6R1FBTW1VY0RVTmtSYjVrcUhoMDNNRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly84LjIzMi4xNjYuMjIwIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OjB5UWN1RHg5dm9pMnk5aUMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1779986209);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_correccion`
--

DROP TABLE IF EXISTS `solicitudes_correccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_correccion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estudiante_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `seccion` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciclo_id` int NOT NULL,
  `ciclo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `docente_id` int NOT NULL,
  `evaluacion` enum('Evaluacion 1','Evaluacion 2','Evaluacion 3','Evaluacion 4','Evaluacion 5') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nota_actual` decimal(4,2) NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente_docente','rechazado_docente','pendiente_coordinador','rechazado_coordinador','pendiente_admin','finalizado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente_docente',
  `fecha_solicitud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `es_excepcion` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `estudiante_id` (`estudiante_id`),
  KEY `materia_id` (`materia_id`),
  KEY `ciclo_id` (`ciclo_id`),
  KEY `docente_id` (`docente_id`),
  CONSTRAINT `solicitudes_correccion_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `solicitudes_correccion_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  CONSTRAINT `solicitudes_correccion_ibfk_3` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_academicos` (`id`),
  CONSTRAINT `solicitudes_correccion_ibfk_4` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_correccion`
--

LOCK TABLES `solicitudes_correccion` WRITE;
/*!40000 ALTER TABLE `solicitudes_correccion` DISABLE KEYS */;
INSERT INTO `solicitudes_correccion` (`id`, `estudiante_id`, `materia_id`, `seccion`, `ciclo_id`, `ciclo`, `docente_id`, `evaluacion`, `nota_actual`, `motivo`, `estado`, `fecha_solicitud`, `es_excepcion`) VALUES (1,7,7,'02',1,'01-2026',13,'Evaluacion 2',5.99,'me han corregido mal el parcial','finalizado','2026-04-21 04:58:27',0),
(2,7,9,'01',1,'01-2026',14,'Evaluacion 2',6.99,'no tiene sentido mi nota despuesde hacer los calculos','finalizado','2026-04-21 06:26:21',0),
(3,7,8,'01',1,'01-2026',13,'Evaluacion 2',4.99,'falta una nota que no me han revisado','finalizado','2026-04-22 00:29:48',0),
(4,1,1,'01',1,'01-2026',9,'Evaluacion 2',6.50,'falta nota de parcial y pague a tiempo la mensualidad','finalizado','2026-04-22 00:38:41',0),
(5,7,7,'02',1,'01-2026',13,'Evaluacion 3',6.70,'la nota me sale mal hice conteo y el parcial me debria subir mas','finalizado','2026-04-28 06:13:05',0),
(6,4,6,'01',1,'01-2026',12,'Evaluacion 3',6.99,'falta nota de laboratorio','finalizado','2026-04-28 23:43:50',0),
(7,5,5,'03',1,'01-2026',12,'Evaluacion 3',5.99,'falta la nota del laboratorio','finalizado','2026-04-29 00:45:38',0),
(8,7,8,'01',1,'01-2026',13,'Evaluacion 3',5.99,'Falta nota','pendiente_docente','2026-05-04 01:56:56',0),
(9,7,8,'01',1,'01-2026',13,'Evaluacion 3',7.80,'falta nota de laboratorio','pendiente_docente','2026-05-12 04:34:39',0),
(10,1,1,'01',1,'01-2026',9,'Evaluacion 4',7.50,'No creo viejazo','rechazado_docente','2026-05-26 17:01:27',0),
(11,1,2,'01',1,'01-2026',9,'Evaluacion 4',4.00,'bno me guasta','rechazado_docente','2026-05-26 17:34:19',0),
(12,1,2,'01',1,'01-2026',9,'Evaluacion 4',3.00,'Probando el msj','rechazado_docente','2026-05-26 17:42:49',0),
(13,1,2,'01',1,'01-2026',9,'Evaluacion 4',9.00,'megustalacebholla','finalizado','2026-05-26 19:38:46',0),
(14,1,1,'01',1,'01-2026',9,'Evaluacion 4',1.00,'parangaricutirimicuaro','finalizado','2026-05-26 19:41:43',0),
(15,2,1,'02',1,'01-2026',10,'Evaluacion 4',9.00,'Por probar','finalizado','2026-05-26 21:07:44',0),
(16,1,1,'01',1,'01-2026',9,'Evaluacion 3',8.00,'Asassdawsdsd','rechazado_coordinador','2026-05-27 11:29:57',1),
(17,2,1,'02',1,'01-2026',10,'Evaluacion 3',9.00,'sdasdasdas','finalizado','2026-05-27 12:25:13',1),
(18,2,2,'02',1,'01-2026',10,'Evaluacion 3',9.90,'sadfcascxcz','pendiente_admin','2026-05-27 13:02:28',1),
(19,2,4,'01',1,'01-2026',11,'Evaluacion 4',9.90,'fedwsdsfsdf','pendiente_docente','2026-05-27 15:44:12',0);
/*!40000 ALTER TABLE `solicitudes_correccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carnet` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('estudiante','docente','coordinador','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `carrera_id` int DEFAULT NULL,
  `estado` tinyint DEFAULT '1',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `carnet` (`carnet`),
  KEY `carrera_id` (`carrera_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` (`id`, `nombre`, `carnet`, `correo`, `password`, `rol`, `carrera_id`, `estado`, `fecha_creacion`) VALUES (1,'Luis Alberto Menendez Castro','27-5001-2026','luis.m@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',1,1,'2026-04-15 04:33:15'),
(2,'Gabriela Esmeralda Sorto Pacheco','27-5002-2026','gabriela.s@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',1,1,'2026-04-15 04:33:15'),
(3,'Rene Alexander Caceres Molina','27-5003-2026','rene.c@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',1,1,'2026-04-15 04:33:15'),
(4,'Hector Armando Salinas Rivera','27-5004-2026','hector.s@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',2,1,'2026-04-15 04:33:15'),
(5,'Daniela Patricia Orellana Fuentes','27-5005-2026','daniela.o@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',2,1,'2026-04-15 04:33:15'),
(6,'Melissa Carolina Quintanilla Flores','27-5006-2026','melissa.q@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',3,1,'2026-04-15 04:33:15'),
(7,'Jorge Eduardo Aparicio Ramos','27-5007-2026','jorge.a@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',3,1,'2026-04-15 04:33:15'),
(8,'Paola Fernanda Villalta Romero','27-5008-2026','paola.v@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','estudiante',3,1,'2026-04-15 04:33:15'),
(9,'Claudia Beatriz Menjivar Castro',NULL,'claudia.doc@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','docente',1,1,'2026-04-15 04:33:15'),
(10,'Jose Manuel Alvarado Rivas',NULL,'jose.doc@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','docente',1,1,'2026-04-15 04:33:15'),
(11,'Veronica Patricia Escobar Molina',NULL,'veronica.doc@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','docente',2,1,'2026-04-15 04:33:15'),
(12,'Mauricio Ernesto Cardenas Fuentes',NULL,'mauricio.doc@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','docente',2,1,'2026-04-15 04:33:15'),
(13,'Andrea Carolina Salguero Pineda',NULL,'andrea.doc@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','docente',3,1,'2026-04-15 04:33:15'),
(14,'Ruben Dario Figueroa Hernandez',NULL,'ruben.doc@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','docente',3,1,'2026-04-15 04:33:15'),
(17,'Silvia Marisol Fuentes de Paz',NULL,'silvia.coord@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','coordinador',NULL,1,'2026-04-15 04:33:15'),
(18,'Carlos Alberto Nolasco Herrera',NULL,'carlos.admin@utec.com','$2y$12$uIfsJbJTo2teM8dd/tnSXusTLUTifNtKn90HmG8CdjuXNcBdxSJ2S','admin',1,1,'2026-04-15 04:33:15');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-28 19:54:47
