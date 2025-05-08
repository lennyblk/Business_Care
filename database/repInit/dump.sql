/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.6.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: businesscare2
-- ------------------------------------------------------
-- Server version	11.7.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES
(1,'admin@admin.com','123456789','Sadmin','admin');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice`
--

DROP TABLE IF EXISTS `advice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `publish_date` date NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `is_personalized` tinyint(1) DEFAULT 0,
  `min_formule` enum('Starter','Basic','Premium') DEFAULT 'Basic',
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `advice_ibfk_1` (`category_id`),
  CONSTRAINT `advice_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `advice_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice`
--

LOCK TABLES `advice` WRITE;
/*!40000 ALTER TABLE `advice` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_category`
--

DROP TABLE IF EXISTS `advice_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_category`
--

LOCK TABLES `advice_category` WRITE;
/*!40000 ALTER TABLE `advice_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_feedback`
--

DROP TABLE IF EXISTS `advice_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `advice_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL COMMENT 'Rating from 1 to 5',
  `comment` text DEFAULT NULL,
  `is_helpful` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_advice_feedback` (`employee_id`,`advice_id`),
  KEY `advice_feedback_ibfk_2` (`advice_id`),
  CONSTRAINT `advice_feedback_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`),
  CONSTRAINT `advice_feedback_ibfk_2` FOREIGN KEY (`advice_id`) REFERENCES `advice` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_feedback`
--

LOCK TABLES `advice_feedback` WRITE;
/*!40000 ALTER TABLE `advice_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_has_tag`
--

DROP TABLE IF EXISTS `advice_has_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_has_tag` (
  `advice_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`advice_id`,`tag_id`),
  KEY `advice_has_tag_ibfk_2` (`tag_id`),
  CONSTRAINT `advice_has_tag_ibfk_1` FOREIGN KEY (`advice_id`) REFERENCES `advice` (`id`) ON DELETE CASCADE,
  CONSTRAINT `advice_has_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `advice_tag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_has_tag`
--

LOCK TABLES `advice_has_tag` WRITE;
/*!40000 ALTER TABLE `advice_has_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_has_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_media`
--

DROP TABLE IF EXISTS `advice_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advice_id` int(11) NOT NULL,
  `media_type` enum('image','video','document','other') NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `advice_media_ibfk_1` (`advice_id`),
  CONSTRAINT `advice_media_ibfk_1` FOREIGN KEY (`advice_id`) REFERENCES `advice` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_media`
--

LOCK TABLES `advice_media` WRITE;
/*!40000 ALTER TABLE `advice_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_schedule`
--

DROP TABLE IF EXISTS `advice_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advice_id` int(11) NOT NULL,
  `scheduled_date` date NOT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `target_audience` enum('All','Specific') DEFAULT 'All',
  `target_criteria` text DEFAULT NULL COMMENT 'JSON encoded targeting criteria if specific',
  PRIMARY KEY (`id`),
  KEY `advice_schedule_ibfk_1` (`advice_id`),
  CONSTRAINT `advice_schedule_ibfk_1` FOREIGN KEY (`advice_id`) REFERENCES `advice` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_schedule`
--

LOCK TABLES `advice_schedule` WRITE;
/*!40000 ALTER TABLE `advice_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advice_tag`
--

DROP TABLE IF EXISTS `advice_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advice_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tag_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advice_tag`
--

LOCK TABLES `advice_tag` WRITE;
/*!40000 ALTER TABLE `advice_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `advice_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anonymous_report`
--

DROP TABLE IF EXISTS `anonymous_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anonymous_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encrypted_employee_id` varchar(255) NOT NULL,
  `report_date` datetime DEFAULT current_timestamp(),
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` enum('New','Processing','Resolved') DEFAULT 'New',
  `severity_level` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anonymous_report`
--

LOCK TABLES `anonymous_report` WRITE;
/*!40000 ALTER TABLE `anonymous_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `anonymous_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `association`
--

DROP TABLE IF EXISTS `association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `association` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `domain` varchar(100) NOT NULL,
  `contact_info` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `association`
--

LOCK TABLES `association` WRITE;
/*!40000 ALTER TABLE `association` DISABLE KEYS */;
/*!40000 ALTER TABLE `association` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_question`
--

DROP TABLE IF EXISTS `chatbot_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatbot_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `response` text DEFAULT NULL,
  `question_date` datetime DEFAULT current_timestamp(),
  `status` enum('Resolved','Unresolved') DEFAULT 'Unresolved',
  PRIMARY KEY (`id`),
  KEY `chatbot_question_ibfk_1` (`employee_id`),
  CONSTRAINT `chatbot_question_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_question`
--

LOCK TABLES `chatbot_question` WRITE;
/*!40000 ALTER TABLE `chatbot_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatbot_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community`
--

DROP TABLE IF EXISTS `community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('Internal','External') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community`
--

LOCK TABLES `community` WRITE;
/*!40000 ALTER TABLE `community` DISABLE KEYS */;
/*!40000 ALTER TABLE `community` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `pays` varchar(100) DEFAULT 'France',
  `telephone` varchar(20) DEFAULT NULL,
  `creation_date` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `formule_abonnement` enum('Starter','Basic','Premium') DEFAULT 'Starter',
  `statut_compte` enum('Actif','Inactif') DEFAULT 'Actif',
  `date_debut_contrat` date DEFAULT curdate(),
  `date_fin_contrat` date DEFAULT NULL,
  `mode_paiement_prefere` varchar(50) DEFAULT NULL,
  `employee_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

LOCK TABLES `company` WRITE;
/*!40000 ALTER TABLE `company` DISABLE KEYS */;
INSERT INTO `company` VALUES
(1,'Sarah Corp','90 rue du Caca','90000','Paris','France','01010101','2025-05-04','kiwotap814@nutrv.com','$2y$10$UUvxi3vNi7zZmjASjDBjeeiMiG8yndcLGW9NQjIn1am1cTkXGiE7K','12345678912345','Starter','Actif','2025-05-04',NULL,NULL,0),
(2,'Bruh Corp','12 rue Lakers','89000','Pantin','France','010011010','2025-05-04','xafeti4894@javbing.com','$2y$10$Ln1Uzybo2vJAvper2Zew5ODSCwxnGzdp9MXp7KnqJVfpr3HERnNRS','12345678912345','Starter','Actif','2025-05-04',NULL,NULL,0);
/*!40000 ALTER TABLE `company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contract`
--

DROP TABLE IF EXISTS `contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contract` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `services` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Direct Debit','Invoice') NOT NULL,
  `formule_abonnement` enum('Starter','Basic','Premium') DEFAULT 'Starter',
  `stripe_checkout_id` varchar(255) DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','unpaid','processing','active') DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contract`
--

LOCK TABLES `contract` WRITE;
/*!40000 ALTER TABLE `contract` DISABLE KEYS */;
INSERT INTO `contract` VALUES
(1,1,'2025-05-04','2026-05-04','Starter',1800.00,'Direct Debit','Starter','cs_test_a1bXE4UJYt4W5f0fNIAzctEKycSwHbrdn1mFGLlqWdDWPmUHkq7uhDd4xa',NULL,'active');
/*!40000 ALTER TABLE `contract` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donation`
--

DROP TABLE IF EXISTS `donation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `association_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `donation_type` enum('Financial','Material','Time') NOT NULL,
  `amount_or_description` text NOT NULL,
  `donation_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Validated') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `donation_ibfk_1` (`association_id`),
  KEY `donation_ibfk_2` (`employee_id`),
  CONSTRAINT `donation_ibfk_1` FOREIGN KEY (`association_id`) REFERENCES `association` (`id`),
  CONSTRAINT `donation_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donation`
--

LOCK TABLES `donation` WRITE;
/*!40000 ALTER TABLE `donation` DISABLE KEYS */;
/*!40000 ALTER TABLE `donation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `departement` varchar(100) DEFAULT NULL,
  `date_creation_compte` date DEFAULT curdate(),
  `password` varchar(255) NOT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `preferences_langue` varchar(10) DEFAULT 'fr',
  `advice_notification_enabled` tinyint(1) DEFAULT 1,
  `id_carte_nfc` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `employee_ibfk_1` (`company_id`),
  CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee`
--

LOCK TABLES `employee` WRITE;
/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
INSERT INTO `employee` VALUES
(1,2,'Lenny','Blackett','cocoblackett@gmail.com','01010101','Artisan','90000','2025-05-05','$2y$10$8j8auo5NQUWwtkcod/lWCOV0JZmbI4Rc7o0fPX27pdQEOR3fK5wpS','2025-05-05 02:09:18','Francais',1,'1'),
(2,2,'Allo','Maman','bruh@bruh.com','0101010101','Technicien','98900','2025-05-05','$2y$10$vRxkm9WTWCYc5FCidouuDuCFBdBFBPSGdwIlOzDId6xPBioM2lVSm',NULL,'fr',1,'2');
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_advice_preference`
--

DROP TABLE IF EXISTS `employee_advice_preference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_advice_preference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `preferred_categories` text DEFAULT NULL COMMENT 'JSON encoded list of preferred categories',
  `preferred_tags` text DEFAULT NULL COMMENT 'JSON encoded list of preferred tags',
  `interests` text DEFAULT NULL COMMENT 'JSON encoded list of interests',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_preference` (`employee_id`),
  CONSTRAINT `employee_advice_preference_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_advice_preference`
--

LOCK TABLES `employee_advice_preference` WRITE;
/*!40000 ALTER TABLE `employee_advice_preference` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_advice_preference` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_community`
--

DROP TABLE IF EXISTS `employee_community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_community` (
  `employee_id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  PRIMARY KEY (`employee_id`,`community_id`),
  KEY `community_id` (`community_id`),
  CONSTRAINT `employee_community_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`),
  CONSTRAINT `employee_community_ibfk_2` FOREIGN KEY (`community_id`) REFERENCES `community` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_community`
--

LOCK TABLES `employee_community` WRITE;
/*!40000 ALTER TABLE `employee_community` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_community` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` datetime NOT NULL,
  `event_type` enum('Webinar','Conference','Sport Event','Workshop') NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `registrations` int(11) NOT NULL DEFAULT 0,
  `company_id` int(11) DEFAULT NULL,
  `event_proposal_id` int(11) DEFAULT NULL,
  `duration` int(11) NOT NULL DEFAULT 60 COMMENT 'Durée en minutes',
  PRIMARY KEY (`id`),
  KEY `fk_event_company` (`company_id`),
  KEY `fk_event_event_proposal` (`event_proposal_id`),
  CONSTRAINT `fk_event_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`),
  CONSTRAINT `fk_event_event_proposal` FOREIGN KEY (`event_proposal_id`) REFERENCES `event_proposal` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event`
--

LOCK TABLES `event` WRITE;
/*!40000 ALTER TABLE `event` DISABLE KEYS */;
INSERT INTO `event` VALUES
(1,'Test Event','Test description','2025-05-15 00:00:00','Webinar',10,'Online',0,2,NULL,60),
(2,'Yoga','Prestation : Yoga','2026-03-06 00:00:00','Workshop',30,'Business Care Troyes',0,2,NULL,60),
(3,'Yoga','Prestation : Yoga','2026-03-08 00:00:00','Workshop',30,'Business Care Troyes',0,2,NULL,60);
/*!40000 ALTER TABLE `event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_proposal`
--

DROP TABLE IF EXISTS `event_proposal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_proposal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `event_type_id` int(11) NOT NULL,
  `proposed_date` date NOT NULL,
  `location_id` int(11) NOT NULL,
  `status` enum('Pending','Assigned','Accepted','Rejected','Completed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `duration` int(11) NOT NULL DEFAULT 60 COMMENT 'Durée en minutes',
  PRIMARY KEY (`id`),
  KEY `fk_event_proposal_company` (`company_id`),
  KEY `fk_event_proposal_event_type` (`event_type_id`),
  CONSTRAINT `fk_event_proposal_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`),
  CONSTRAINT `fk_event_proposal_event_type` FOREIGN KEY (`event_type_id`) REFERENCES `service_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_proposal`
--

LOCK TABLES `event_proposal` WRITE;
/*!40000 ALTER TABLE `event_proposal` DISABLE KEYS */;
INSERT INTO `event_proposal` VALUES
(2,2,2,'2026-03-06',2,'Accepted','J\'adore le yoga','2025-05-07 09:08:45','2025-05-08 16:23:25',60),
(3,2,2,'2026-03-08',2,'Accepted','Test durée','2025-05-08 17:08:17','2025-05-08 17:28:52',60);
/*!40000 ALTER TABLE `event_proposal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_registration`
--

DROP TABLE IF EXISTS `event_registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_registration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `status` enum('Confirmed','Canceled','Waiting') DEFAULT 'Confirmed',
  PRIMARY KEY (`id`),
  KEY `event_registration_ibfk_1` (`event_id`),
  KEY `event_registration_ibfk_2` (`employee_id`),
  CONSTRAINT `event_registration_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  CONSTRAINT `event_registration_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_registration`
--

LOCK TABLES `event_registration` WRITE;
/*!40000 ALTER TABLE `event_registration` DISABLE KEYS */;
INSERT INTO `event_registration` VALUES
(1,2,2,'2025-05-08 16:34:25','Confirmed');
/*!40000 ALTER TABLE `event_registration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
-- Table structure for table `intervention`
--

DROP TABLE IF EXISTS `intervention`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `intervention` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `service_type_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `intervention_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('Planned','Completed','Canceled') DEFAULT 'Planned',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intervention_ibfk_1` (`provider_id`),
  KEY `intervention_ibfk_2` (`service_type_id`),
  KEY `intervention_ibfk_3` (`employee_id`),
  CONSTRAINT `intervention_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`),
  CONSTRAINT `intervention_ibfk_2` FOREIGN KEY (`service_type_id`) REFERENCES `service_type` (`id`),
  CONSTRAINT `intervention_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intervention`
--

LOCK TABLES `intervention` WRITE;
/*!40000 ALTER TABLE `intervention` DISABLE KEYS */;
/*!40000 ALTER TABLE `intervention` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Overdue') DEFAULT 'Pending',
  `pdf_path` varchar(255) DEFAULT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_ibfk_1` (`company_id`),
  KEY `invoice_ibfk_2` (`contract_id`),
  CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`),
  CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice`
--

LOCK TABLES `invoice` WRITE;
/*!40000 ALTER TABLE `invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(50) DEFAULT 'France',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES
(1,'Business Care Paris (1er)','110, rue de Rivoli','75001','Paris','France',1,'2025-05-05 15:48:14'),
(2,'Business Care Troyes','13 rue Antoine Parmentier','10000','Troyes','France',1,'2025-05-05 15:48:14'),
(3,'Business Care Biarritz','47 rue Lisboa','64200','Biarritz','France',1,'2025-05-05 15:48:14'),
(4,'Business Care Nice','8 rue Beaumont','06000','Nice','France',1,'2025-05-05 15:48:14');
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_appointment`
--

DROP TABLE IF EXISTS `medical_appointment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_appointment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `reason` text NOT NULL,
  `confidential` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `provider_id` (`provider_id`),
  CONSTRAINT `medical_appointment_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`),
  CONSTRAINT `medical_appointment_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_appointment`
--

LOCK TABLES `medical_appointment` WRITE;
/*!40000 ALTER TABLE `medical_appointment` DISABLE KEYS */;
/*!40000 ALTER TABLE `medical_appointment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_id` int(11) NOT NULL,
  `recipient_type` enum('Company','Employee','Provider') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `creation_date` datetime DEFAULT current_timestamp(),
  `send_date` datetime DEFAULT NULL,
  `status` enum('Pending','Sent','Read') DEFAULT 'Pending',
  `notification_type` enum('Email','Push','Internal') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pending_registrations`
--

DROP TABLE IF EXISTS `pending_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pending_registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_type` varchar(255) NOT NULL COMMENT 'societe, employe, prestataire',
  `company_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `departement` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `domains` varchar(255) DEFAULT NULL,
  `tarif_horaire` decimal(10,2) DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, approved, rejected',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pending_registrations_email_index` (`email`),
  KEY `pending_registrations_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pending_registrations`
--

LOCK TABLES `pending_registrations` WRITE;
/*!40000 ALTER TABLE `pending_registrations` DISABLE KEYS */;
INSERT INTO `pending_registrations` VALUES
(1,'societe','Sarah Corp',NULL,NULL,'kiwotap814@nutrv.com','$2y$10$UUvxi3vNi7zZmjASjDBjeeiMiG8yndcLGW9NQjIn1am1cTkXGiE7K','01010101',NULL,NULL,'90 rue du Caca','90000','Paris','12345678912345',NULL,NULL,NULL,NULL,'approved','2025-05-04 18:29:55','2025-05-04 18:31:12'),
(2,'societe','Bruh Corp',NULL,NULL,'xafeti4894@javbing.com','$2y$10$Ln1Uzybo2vJAvper2Zew5ODSCwxnGzdp9MXp7KnqJVfpr3HERnNRS','010011010',NULL,NULL,'12 rue Lakers','89000','Pantin','12345678912345',NULL,NULL,NULL,NULL,'approved','2025-05-04 18:53:50','2025-05-04 18:54:14'),
(3,'prestataire',NULL,'Jean','Norbert','noted81482@javbing.com','$2y$10$.kDpCN0ejd3DzSzLNHBAC.vvfHXCXC8GYaiqgZMiLukFv6zVReNe.','0690101010',NULL,NULL,NULL,'93500','Pantin','12345678912345','Allo','Artisan',45.00,NULL,'approved','2025-05-05 15:03:12','2025-05-05 15:20:37'),
(4,'prestataire',NULL,'Hector','Hibo','bayadi4488@nutrv.com','$2y$10$nRtS2jg6vkRBup8plk0hmeSTurNH6g8qDXQvPu.GTzr3k4FpENHTu','0606060606',NULL,NULL,NULL,'78000','Biarritz','12345678912345','Ojd','Demai',89.00,NULL,'rejected','2025-05-05 15:09:54','2025-05-05 15:17:52'),
(5,'prestataire',NULL,'Lenny','Blackett','yiteg72041@javbing.com','$2y$10$2MrrGGkoYrLI3Io4Lt3nRemi3WVHkL8KEENoPJGg39zJsPtZb55EW','0649755621',NULL,NULL,NULL,'93500','Pantin','12345678912345','Allo','Sport',67.00,'\"{\\\"custom_activity\\\":\\\"Futsall\\\"}\"','approved','2025-05-05 15:22:33','2025-05-05 15:26:36'),
(6,'prestataire',NULL,'Kobbie','Mainoo','yiteg72041@javbing.com','$2y$10$AxFHA31EqhRdEFHa0VFh2uWaZ1GVToG0acqqtPFIpcSF9gdtFpIdq','0101010101',NULL,NULL,NULL,'75000','Paris','12345678912345','Bonsoir','Artisan',23.00,NULL,'rejected','2025-05-05 20:54:01','2025-05-05 21:02:05'),
(7,'prestataire',NULL,'Désiré','Douéa','yiteg72041@javbing.com','$2y$10$aIqMSFq5Srrjh6tULwyE8OLtoCtOpxgDVoLwBN6a/apmFoD.nHDDa','0101010101',NULL,NULL,NULL,'75006','Paris','11223435456464','Ici c\'est PARIS','Foot',54.00,NULL,'approved','2025-05-05 21:04:43','2025-05-05 21:09:08'),
(8,'prestataire',NULL,'Sandrine','Vigot','vejayas914@javbing.com','$2y$10$mR9odwXf2AQ0TXN34MKZWeocLbec.lUm/x3oF7gag269FPy3wWTtW','0101010101',NULL,NULL,NULL,'78211','Troyes','12345678912345','Bonjour accepter moi svp','Sportive',30.00,NULL,'approved','2025-05-07 09:13:53','2025-05-07 09:14:39');
/*!40000 ALTER TABLE `pending_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
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
-- Table structure for table `personalized_advice`
--

DROP TABLE IF EXISTS `personalized_advice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personalized_advice` (
  `advice_id` int(11) NOT NULL,
  `target_criteria` text NOT NULL COMMENT 'JSON encoded criteria for targeting',
  `suggested_activities` text DEFAULT NULL COMMENT 'JSON encoded list of suggested activities',
  PRIMARY KEY (`advice_id`),
  CONSTRAINT `personalized_advice_ibfk_1` FOREIGN KEY (`advice_id`) REFERENCES `advice` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personalized_advice`
--

LOCK TABLES `personalized_advice` WRITE;
/*!40000 ALTER TABLE `personalized_advice` DISABLE KEYS */;
/*!40000 ALTER TABLE `personalized_advice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider`
--

DROP TABLE IF EXISTS `provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `domains` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `statut_prestataire` enum('Candidat','Validé','Inactif') DEFAULT 'Candidat',
  `date_validation` date DEFAULT NULL,
  `validation_documents` text DEFAULT NULL,
  `tarif_horaire` decimal(10,2) DEFAULT NULL,
  `nombre_evaluations` int(11) DEFAULT 0,
  `activity_type` enum('rencontre sportive','conférence','webinar','yoga','pot','séance d''art plastiques','session jeu vidéo','autre') NOT NULL,
  `other_activity` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider`
--

LOCK TABLES `provider` WRITE;
/*!40000 ALTER TABLE `provider` DISABLE KEYS */;
INSERT INTO `provider` VALUES
(1,'Norbert','Jean','Allo',0.00,'Artisan','noted81482@javbing.com','0690101010','$2y$10$.kDpCN0ejd3DzSzLNHBAC.vvfHXCXC8GYaiqgZMiLukFv6zVReNe.',NULL,'93500','Pantin','12345678912345',NULL,'Validé','2025-05-05',NULL,45.00,0,'yoga',NULL),
(2,'Blackett','Lenny','Allo',0.00,'Sport','yiteg72041@javbing.com','0649755621','$2y$10$2MrrGGkoYrLI3Io4Lt3nRemi3WVHkL8KEENoPJGg39zJsPtZb55EW',NULL,'93500','Pantin','12345678912345',NULL,'Validé','2025-05-05',NULL,67.00,0,'yoga',NULL),
(41,'Vigot','Sandrine','Bonjour accepter moi svp',0.00,'Sportive','vejayas914@javbing.com','0101010101','$2y$10$mR9odwXf2AQ0TXN34MKZWeocLbec.lUm/x3oF7gag269FPy3wWTtW',NULL,'78211','Troyes','12345678912345',NULL,'Validé','2025-05-07',NULL,30.00,0,'yoga',NULL);
/*!40000 ALTER TABLE `provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_assignment`
--

DROP TABLE IF EXISTS `provider_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider_assignment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_proposal_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `status` enum('Proposed','Accepted','Rejected') DEFAULT 'Proposed',
  `proposed_at` datetime DEFAULT current_timestamp(),
  `response_at` datetime DEFAULT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_provider_assignment_event_proposal` (`event_proposal_id`),
  KEY `fk_provider_assignment_provider` (`provider_id`),
  CONSTRAINT `fk_provider_assignment_event_proposal` FOREIGN KEY (`event_proposal_id`) REFERENCES `event_proposal` (`id`),
  CONSTRAINT `fk_provider_assignment_provider` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_assignment`
--

LOCK TABLES `provider_assignment` WRITE;
/*!40000 ALTER TABLE `provider_assignment` DISABLE KEYS */;
INSERT INTO `provider_assignment` VALUES
(1,1,40,'Proposed','2025-05-05 21:15:16',NULL,24.00),
(2,1,40,'Proposed','2025-05-05 21:17:10',NULL,24.00),
(3,1,40,'Proposed','2025-05-05 21:17:36',NULL,24.03),
(4,2,41,'Accepted','2025-05-07 09:17:27','2025-05-08 16:23:25',60.00),
(5,2,41,'Rejected','2025-05-07 09:18:13','2025-05-08 16:23:17',60.00),
(6,2,41,'Rejected','2025-05-07 09:18:51','2025-05-08 16:23:12',60.00),
(7,3,41,'Accepted','2025-05-08 17:23:16','2025-05-08 17:28:52',20.00);
/*!40000 ALTER TABLE `provider_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_availability`
--

DROP TABLE IF EXISTS `provider_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `date_available` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('Available','Reserved','Canceled') DEFAULT 'Available',
  `provider_assignment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_availability_ibfk_1` (`provider_id`),
  KEY `fk_provider_availability_assignment` (`provider_assignment_id`),
  CONSTRAINT `fk_provider_availability_assignment` FOREIGN KEY (`provider_assignment_id`) REFERENCES `provider_assignment` (`id`),
  CONSTRAINT `provider_availability_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_availability`
--

LOCK TABLES `provider_availability` WRITE;
/*!40000 ALTER TABLE `provider_availability` DISABLE KEYS */;
/*!40000 ALTER TABLE `provider_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_invoice`
--

DROP TABLE IF EXISTS `provider_invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid') DEFAULT 'Pending',
  `issue_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_invoice_ibfk_1` (`provider_id`),
  CONSTRAINT `provider_invoice_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_invoice`
--

LOCK TABLES `provider_invoice` WRITE;
/*!40000 ALTER TABLE `provider_invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `provider_invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_recommendation_log`
--

DROP TABLE IF EXISTS `provider_recommendation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider_recommendation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_proposal_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `geographic_match` tinyint(1) DEFAULT 0,
  `skill_match` tinyint(1) DEFAULT 0,
  `rating_score` decimal(5,2) DEFAULT 0.00,
  `price_score` decimal(5,2) DEFAULT 0.00,
  `availability_score` decimal(5,2) DEFAULT 0.00,
  `total_score` decimal(5,2) DEFAULT 0.00,
  `recommended` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_provider_recommendation_event_proposal` (`event_proposal_id`),
  KEY `fk_provider_recommendation_provider` (`provider_id`),
  CONSTRAINT `fk_provider_recommendation_event_proposal` FOREIGN KEY (`event_proposal_id`) REFERENCES `event_proposal` (`id`),
  CONSTRAINT `fk_provider_recommendation_provider` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_recommendation_log`
--

LOCK TABLES `provider_recommendation_log` WRITE;
/*!40000 ALTER TABLE `provider_recommendation_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `provider_recommendation_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quote`
--

DROP TABLE IF EXISTS `quote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `creation_date` date NOT NULL,
  `expiration_date` date NOT NULL,
  `company_size` int(11) NOT NULL,
  `formule_abonnement` enum('Starter','Basic','Premium') NOT NULL DEFAULT 'Starter',
  `activities_count` int(11) NOT NULL,
  `medical_appointments` int(11) NOT NULL,
  `extra_appointment_fee` decimal(5,2) NOT NULL,
  `chatbot_questions` varchar(20) NOT NULL,
  `weekly_advice` tinyint(1) NOT NULL,
  `personalized_advice` tinyint(1) NOT NULL,
  `price_per_employee` decimal(6,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `services_details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quote_ibfk_1` (`company_id`),
  CONSTRAINT `quote_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quote`
--

LOCK TABLES `quote` WRITE;
/*!40000 ALTER TABLE `quote` DISABLE KEYS */;
/*!40000 ALTER TABLE `quote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_evaluation`
--

DROP TABLE IF EXISTS `service_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_evaluation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intervention_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `rating` decimal(3,2) NOT NULL,
  `comment` text DEFAULT NULL,
  `evaluation_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `service_evaluation_ibfk_1` (`intervention_id`),
  KEY `service_evaluation_ibfk_2` (`employee_id`),
  CONSTRAINT `service_evaluation_ibfk_1` FOREIGN KEY (`intervention_id`) REFERENCES `intervention` (`id`),
  CONSTRAINT `service_evaluation_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_evaluation`
--

LOCK TABLES `service_evaluation` WRITE;
/*!40000 ALTER TABLE `service_evaluation` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_evaluation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_type`
--

DROP TABLE IF EXISTS `service_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`),
  CONSTRAINT `service_type_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_type`
--

LOCK TABLES `service_type` WRITE;
/*!40000 ALTER TABLE `service_type` DISABLE KEYS */;
INSERT INTO `service_type` VALUES
(1,1,'Conférence','Prestation : Conférence',100.00,60),
(2,1,'Yoga','Prestation : Yoga',100.00,60);
/*!40000 ALTER TABLE `service_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation_key` varchar(255) NOT NULL,
  `language` varchar(10) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`translation_key`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-05-08 21:57:38
