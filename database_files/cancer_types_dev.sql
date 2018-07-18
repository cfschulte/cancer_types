-- 
--  cancer_types_dev Tue Jul 17 09:34:48 CDT 2018
-- A basic MySQL app that has information about Cancer Types
-- 

--
-- Table structure for table `backup_deleted_entries`  -- for whole records 
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
-- Table structure for table `backup_table`  -- for individual fields 
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;



-- 
-- Define cancer type list 
-- 
-- general overview
DROP TABLE IF EXISTS `cancer_type_list`;
CREATE TABLE `cancer_type_list` (
    id                   mediumint(9) NOT NULL AUTO_INCREMENT,
    cancer_type          varchar(127) DEFAULT NULL,
    synopsis             varchar(255) DEFAULT NULL,
    overview             TEXT         DEFAULT NULL,

    primary_anatomical_site  int DEFAULT 0,
    other_anatom_type_text   varchar(255) DEFAULT NULL,

    primary_trt_program  int DEFAULT 0,
   PRIMARY KEY (`id`)
 )  ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



--
-- Table structure for table `anatomical_site`
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

LOAD DATA LOCAL INFILE 'primary_anatomical_site.txt' INTO TABLE primary_anatomical_site FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n' (table_index,descriptor);


-- 
-- Define treatment_programs 
-- 

DROP TABLE IF EXISTS `treatment_programs`;
CREATE TABLE `treatment_programs` (
   table_index              int    NOT NULL,
   descriptor      varchar(75)  NOT NULL,
   PRIMARY KEY (`table_index`)
)   ENGINE=InnoDB DEFAULT CHARSET=latin1;
LOAD DATA LOCAL INFILE 'treatment_programs.txt' INTO TABLE treatment_programs FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n' (table_index,descriptor);




-- 
-- treatment_program_list  
--   A list of all treatment programs this cancer might fall under.
-- part of the reason for this exercise is to get foreign keys to work 

DROP TABLE IF EXISTS `treatment_program_list`;
CREATE TABLE `treatment_program_list` (
   tp_list_id           mediumint(9) NOT NULL AUTO_INCREMENT,
   cancer_type_id       mediumint(9) NOT NULL,
--    CONSTRAINT `cancer_type_id`
--    FOREIGN KEY (`cancer_type_id`) 
--        REFERENCES cancer_type_list (id),
   treat_prog_id   int      NOT NULL,
--    CONSTRAINT `treat_prog_id`
--    FOREIGN KEY (`treat_prog_id`) 
--        REFERENCES treatment_program (treat_prog_id),
   PRIMARY KEY (tp_list_id)
)  ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
