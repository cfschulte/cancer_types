-- MySQL dump 10.13  Distrib 5.7.20, for macos10.12 (x86_64)
--
-- Host: 127.0.0.1    Database: cancer_types
-- ------------------------------------------------------
-- Server version	5.7.20

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
-- Table structure for table `backup_deleted_entries`
--

DROP TABLE IF EXISTS `backup_deleted_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_deleted_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) NOT NULL,
  `table_prim_key` varchar(16) DEFAULT NULL,
  `prim_key_val` int(11) DEFAULT NULL,
  `entry_data` text,
  `time_saved` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_deleted_entries`
--

LOCK TABLES `backup_deleted_entries` WRITE;
/*!40000 ALTER TABLE `backup_deleted_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_deleted_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_table`
--

DROP TABLE IF EXISTS `backup_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_table` (
  `backup_id` int(11) NOT NULL AUTO_INCREMENT,
  `db_table` varchar(64) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `form_type` varchar(32) DEFAULT NULL,
  `table_column` varchar(64) DEFAULT NULL,
  `value_varchar` varchar(256) DEFAULT NULL,
  `value_text` text,
  `value_int` int(11) DEFAULT NULL,
  `value_float` float DEFAULT NULL,
  `value_money` decimal(15,2) DEFAULT NULL,
  `value_date` date DEFAULT NULL,
  `time_saved` bigint(20) NOT NULL,
  PRIMARY KEY (`backup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_table`
--

LOCK TABLES `backup_table` WRITE;
/*!40000 ALTER TABLE `backup_table` DISABLE KEYS */;
INSERT INTO `backup_table` VALUES (1,'cancer_type_list',1,'textarea','overview',NULL,'',NULL,NULL,NULL,NULL,1532028950),(2,'cancer_type_list',3,'textarea','overview',NULL,'',NULL,NULL,NULL,NULL,1532030300),(3,'cancer_type_list',3,'text','synopsis','',NULL,NULL,NULL,NULL,NULL,1532030363);
/*!40000 ALTER TABLE `backup_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cancer_type_list`
--

DROP TABLE IF EXISTS `cancer_type_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cancer_type_list` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `cancer_type` varchar(127) DEFAULT NULL,
  `synopsis` varchar(255) DEFAULT NULL,
  `overview` text,
  `primary_anatomical_site` int(11) DEFAULT '0',
  `other_anatom_type_text` varchar(255) DEFAULT NULL,
  `primary_trt_program` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cancer_type_list`
--

LOCK TABLES `cancer_type_list` WRITE;
/*!40000 ALTER TABLE `cancer_type_list` DISABLE KEYS */;
INSERT INTO `cancer_type_list` VALUES (1,'Acute Lymphoblastic Leukemia (ALL)',NULL,'Leukemia is a broad term for cancers of the blood cells. The type of leukemia depends on the type of blood cell that becomes cancer and whether it grows quickly or slowly. Leukemia occurs most often in adults older than 55, but it is also the most common cancer in children younger than 15. Explore the links on this page to learn more about the types of leukemia plus treatment, statistics, research, and clinical trials.',0,NULL,0),(2,'Acute Myeloid Leukemia (AML)',NULL,NULL,0,NULL,0),(3,'Adrenocortical Carcinoma','Also called cancer of the adrenal cortex cancer. It is rare','Adrenocortical cancer (also called cancer of the adrenal cortex) is rare. Certain inherited disorders increase the risk of adrenocortical cancer.  Explore the links on this page to learn more about adrenocortical cancer treatment, research, and clinical trials.',0,NULL,0),(4,'Anal Cancer',NULL,NULL,0,NULL,0);
/*!40000 ALTER TABLE `cancer_type_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `primary_anatomical_site`
--

DROP TABLE IF EXISTS `primary_anatomical_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `primary_anatomical_site` (
  `table_index` int(11) NOT NULL,
  `descriptor` varchar(75) NOT NULL,
  PRIMARY KEY (`table_index`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `primary_anatomical_site`
--

LOCK TABLES `primary_anatomical_site` WRITE;
/*!40000 ALTER TABLE `primary_anatomical_site` DISABLE KEYS */;
INSERT INTO `primary_anatomical_site` VALUES (0,'n/a'),(1,'H&N'),(2,'Brain'),(3,'Thorax'),(4,'Breast'),(5,'Abdomen'),(6,'GYN'),(7,'GU'),(8,'Skin'),(9,'Extremities'),(10,'Other (Please specifiy)');
/*!40000 ALTER TABLE `primary_anatomical_site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `treatment_program_list`
--

DROP TABLE IF EXISTS `treatment_program_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `treatment_program_list` (
  `tp_list_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `cancer_type_id` mediumint(9) NOT NULL,
  `treat_prog_id` int(11) NOT NULL,
  PRIMARY KEY (`tp_list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `treatment_program_list`
--

LOCK TABLES `treatment_program_list` WRITE;
/*!40000 ALTER TABLE `treatment_program_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `treatment_program_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `treatment_programs`
--

DROP TABLE IF EXISTS `treatment_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `treatment_programs` (
  `table_index` int(11) NOT NULL,
  `descriptor` varchar(75) NOT NULL,
  PRIMARY KEY (`table_index`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `treatment_programs`
--

LOCK TABLES `treatment_programs` WRITE;
/*!40000 ALTER TABLE `treatment_programs` DISABLE KEYS */;
INSERT INTO `treatment_programs` VALUES (0,'N/A'),(1,'Breast'),(2,'Brain/CNS'),(3,'Gastrointestinal'),(4,'Head and Neck'),(5,'Lung/Thoracic'),(6,'Genitourinary'),(7,'Gynocological'),(8,'Pediatric'),(9,'Prostate'),(10,'Melanomas and Sarcomas'),(11,'Other');
/*!40000 ALTER TABLE `treatment_programs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-19 15:01:49
