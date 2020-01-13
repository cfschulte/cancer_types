<?php
// test_pgsql.php -  Wed Jan 8 10:22:10 CST 2020
// 

require_once "essentials.php";
// require_once "db_config.php";
require_once "db_class.php";
?>
<!DOCTYPE html>
<html lang="en">
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/ >
<title>Test Postgresql</title>
<link rel="stylesheet" href="css/style.css">
</HEAD>
<body>
<H1>Test Postgresql</H1>

<?php
        global $DATABASE;
        global $DB_USER;
        global $DB_PASSWORD;
	
	$db_obj = new db_class();
// 	$sql = "SELECT * FROM cancer_type WHERE id=?";
// 	$sql = "DELETE FROM cancer_type WHERE id=?";
// 	$sql = "INSERT INTO cancer_type VALUES (10,'Burkitt Lymphoma ','see Non-Hodgkin Lymphoma',NULL,0,NULL,0)";
	$sql = "INSERT INTO cancer_type VALUES (?,?,?,?,?,?,?)";
	$db_table = $db_obj->safeInsertUpdateDelete($sql, 'issiiii', array(9,'Bone Cancer','includes Ewing Sarcoma and Osteosarcoma and Malignant Fibrous Histiocytoma',NULL,0,NULL,0));
	
	
	$db_obj->closeDB();
	
// 	showArray($db_table);
	
	// no connection_error
//     $dbconn = pg_connect("host=localhost dbname=handle user=$DB_USER password=$DB_PASSWORD");
// 	showDebug($dbconn);
// 	$error = pg_last_error($dbconn);
// 	showDebug($error);

//     $dbconn = pg_connect("host=localhost dbname=$DATABASE user=$DB_USER password=$DB_PASSWORD");
    
//     $sql = "SELECT cancer_type FROM cancer_type WHERE id=$1 OR id=$2";
//     $prep_result = pg_prepare($dbconn, "type_query", $sql);
//     showDebug($prep_result);
//     $exe_result = pg_execute($dbconn, "type_query", array(2,4));
//     showDebug($exe_result);
    
    
//     $the_array = pg_fetch_all($exe_result);
//     $the_array = pg_fetch_array($exe_result);
//     showArray($the_array);
//     pg_close($dbconn);
?>


</body>
</html>
