<?php
// index.php -  Mon Nov 16 15:41:32 CST 2015
// 
	require_once "essentials.php";
	require_once "db_class.php";

  // we will get the cookies up and going for this later. 
	// base url
// 	$homeURL = '/cancer_types';
// 	
// 	$user_privileges = 0;
// 	$userid = '';
//     if( array_key_exists('uid', $_SERVER)){
//         $userid = $_SERVER['uid'];
//     } else {
//         $userid = 'cfschulte';  // this userid comes from check_login 
//     }
//     $userInfo = getUserInfo( $userid );
//     if( !empty($userInfo) ){
//         $user_privileges = $userInfo['privileges'];
//     }
//    // redirect the people from the wrong labs 
//     if( $user_privileges < 1  ) {
//         header('Location: /cancer_types/disallowed.php');
//     }
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
 
  
 <link rel="Stylesheet" type="text/css" href="/cancer_types/css/style.css" />

 <Title>Cancer Types</Title>


</head>
<body>
 
<div id="wrapper"> 
    <div style="text-align:center">
       <h2><a class="front_page_link" href="https://www.cancer.gov/about-cancer/understanding/what-is-cancer">What is Cancer?</a></h2>
       <h3><a class="front_page_link" href="/cancer_types/views_controllers/table_cancer_type.php">Cancer Types</a></h3>
    </div>
  <br>
  <br>
  <br>
  <br>
  <br>
  <div id="footer">
    <p>Department of Human Oncology, UW Medical School, 600 Highland Ave., Madison, WI 53792</p>
    <p><a href="http://www.med.wisc.edu/">University of Wisconsin School of Medicine and Public Health</a></p>
    <p>© 2017 Board of Regents of the <a href="http://www.wisconsin.edu">University of Wisconsin System</a></p>
    
  <?php
     date_default_timezone_set("America/Chicago");
     echo "<p>This page was last modified: " .  date("d F Y", getlastmod()) . "</p>";
  ?>  
  	
  </div>
</div>
  
</body>
</html>

