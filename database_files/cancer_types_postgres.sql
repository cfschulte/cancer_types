
--
-- Table structure for table backup_deleted_entries
--

DROP TABLE IF EXISTS backup_deleted_entries;

CREATE TABLE backup_deleted_entries (
  id SERIAL,
  table_name varchar(64) NOT NULL,
  table_prim_key varchar(16) DEFAULT NULL,
  prim_key_val int DEFAULT NULL,
  entry_data text,
  time_saved int NOT NULL,
  PRIMARY KEY (id)
) ;

--
-- Dumping data for table backup_deleted_entries
--


--
-- Table structure for table backup_table
--

DROP TABLE IF EXISTS backup_table;

CREATE TABLE backup_table (
  backup_id SERIAL,
  db_table varchar(64) NOT NULL,
  id int DEFAULT NULL,
  form_type varchar(32) DEFAULT NULL,
  table_column varchar(64) DEFAULT NULL,
  value_varchar varchar(256) DEFAULT NULL,
  value_text text,
  value_int int DEFAULT NULL,
  value_float float DEFAULT NULL,
  value_money decimal(15,2) DEFAULT NULL,
  value_date date DEFAULT NULL,
  time_saved int NOT NULL,
  PRIMARY KEY (backup_id)
)   ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table backup_table
--

--
-- Table structure for table cancer_type
--

DROP TABLE IF EXISTS cancer_type;

CREATE TABLE cancer_type (
  id SERIAL,
  cancer_type varchar(127) DEFAULT NULL,
  synopsis varchar(255) DEFAULT NULL,
  overview text,
  primary_anatomical_site int DEFAULT '0',
  other_anatom_type_text varchar(255) DEFAULT NULL,
  primary_trt_program int DEFAULT '0',
  PRIMARY KEY (id)
)   ;

--
-- Dumping data for table cancer_type
--

INSERT INTO cancer_type VALUES (1,'Acute Lymphoblastic Leukemia (ALL)',NULL,'Leukemia is a broad term for cancers of the blood cells. The type of leukemia depends on the type of blood cell that becomes cancer and whether it grows quickly or slowly. Leukemia occurs most often in adults older than 55, but it is also the most common cancer in children younger than 15. Explore the links on this page to learn more about the types of leukemia plus treatment, statistics, research, and clinical trials.',0,NULL,0),(2,'Acute Myeloid Leukemia (AML)',NULL,NULL,0,NULL,0),(3,'Adrenocortical Carcinoma','Also called cancer of the adrenal cortex cancer. It is rare','Adrenocortical cancer (also called cancer of the adrenal cortex) is rare. Certain inherited disorders increase the risk of adrenocortical cancer.  Explore the links on this page to learn more about adrenocortical cancer treatment, research, and clinical trials.',5,NULL,0),(4,'Anal Cancer','A nasty thing to have','This is a quick overview',0,NULL,0),(5,'Appendix Cancer',NULL,NULL,0,NULL,0),(6,'Astrocytomas, Childhood (Brain Cancer)',NULL,'',0,NULL,0),(7,'Atypical Teratoid/Rhabdoid Tumor',NULL,NULL,0,NULL,0),(8,'Bladder Cancer','',NULL,7,NULL,0),(9,'Bone Cancer','includes Ewing Sarcoma and Osteosarcoma and Malignant Fibrous Histiocytoma',NULL,0,NULL,0),(10,'Burkitt Lymphoma ','see Non-Hodgkin Lymphoma',NULL,0,NULL,0);

--
-- Table structure for table primary_anatomical_site
--

DROP TABLE IF EXISTS primary_anatomical_site;

CREATE TABLE primary_anatomical_site (
  table_index int NOT NULL,
  descriptor varchar(75) NOT NULL,
  PRIMARY KEY (table_index)
)  ;


--
-- Dumping data for table primary_anatomical_site
--

INSERT INTO primary_anatomical_site VALUES (0,'n/a'),(1,'H&amp;N'),(2,'Brain'),(3,'Thorax'),(4,'Breast'),(5,'Abdomen'),(6,'GYN'),(7,'GU'),(8,'Skin'),(9,'Extremities'),(10,'Other (Please specifiy)');

--
-- Table structure for table treatment_program_list
--

DROP TABLE IF EXISTS treatment_program_list;

CREATE TABLE treatment_program_list (
  tp_list_id SERIAL,
  cancer_type_id INT NOT NULL,
  treat_prog_id int NOT NULL,
  PRIMARY KEY (tp_list_id)
)  ;


--
-- Dumping data for table treatment_program_list
--



--
-- Table structure for table treatment_programs
--

DROP TABLE IF EXISTS treatment_programs;

CREATE TABLE treatment_programs (
  table_index int NOT NULL,
  descriptor varchar(75) NOT NULL,
  PRIMARY KEY (table_index)
)  ;


--
-- Dumping data for table treatment_programs
--

INSERT INTO treatment_programs VALUES (0,'N/A'),(1,'Breast'),(2,'Brain/CNS'),(3,'Gastrointestinal'),(4,'Head and Neck'),(5,'Lung/Thoracic'),(6,'Genitourinary'),(7,'Gynocological'),(8,'Pediatric'),(9,'Prostate'),(10,'Melanomas and Sarcomas'),(11,'Other');

