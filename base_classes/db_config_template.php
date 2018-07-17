<?php
// db_config.php -  Thu Apr 5 07:55:05 CDT 2018
// Since some servers require the domain to be localhost and others
// require 127.0.0.1, and since having a config file might make the 
// db_class more flexible, and since we don't want to archive passwords,
// etc. in git, I am separating the basic database access stuff into 
// this little config file. 

$DATABASE     = 'cancer_types';
$DB_USER      = 'the_curious';
$DB_PASSWORD  = 'CHOOSE_A_PASSWORD';
$DB_SERVER    = 'localhost'; 

