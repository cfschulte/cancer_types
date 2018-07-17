<?php
// essentials.php -  Thu Mar 1 09:25:07 CST 2018
//   -- PUT THE MENU IN A FUNCTION HERE.


$root = $_SERVER['DOCUMENT_ROOT'] ;
set_include_path($root . '/jquery/:'  . 
                 $root . '/cancer_types/:' .
                 $root . '/cancer_types/css:' .
                 $root . '/cancer_types/base_classes:' .  
                 $root . '/cancer_types/non_class_includes:'  . 
                 $root . '/cancer_types/forms:' . 
                 $root . '/cancer_types/tables'  );

require_once "db_class.php";


// $DATE_FORMAT = 'yy-mm-dd';
$DATE_FORMAT = 'mm/dd/yyyy';



//////////////
function showArray($inArray){
	if(is_array($inArray)){
		echo "<pre style=\"text-align:left\">\n";
		print_r($inArray);
		echo "</pre><br/>\n";
	} else {
		echo "not an array<br/>\n";
	}
}


//////////////
function showDebug( $string ) {
	echo 'DEBUG: ' . $string . '<br/>' . "\n";
}

function dump($value) {
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}
///////////////////////////////////////////////////
//  Make sure the data value is the correct type for
//  the database field.
// Right now, I'm just checking for dates, but this
// should be expanded to make sure numbers are cast 
// correctly. 
function ensureType($val, $val_type) {
    if($val_type == 'date') {
        $val = ensureDate($val);
    }
    return $val;
}


////////////////////////////////////
//  Creates a date format that won't screw up MySQL.
//  This defaults to 1969-12-31, so that would be an error.
function ensureDate($date) {
	date_default_timezone_set("America/Chicago");
	$time = strtotime($date);
	if( $time != '' ) {
		$format = "Y-m-d";
		return date($format, $time);
	} else {
		return NULL;
	}
}

function today() {
	date_default_timezone_set("America/Chicago");
// 	return date("Y-m-d");  
	return date("m/d/Y");  
}

/////////////////////////////
//  
function beforeToday($end_date) {
	if($end_date == '') { // end date has not been set, so..
		return false;
	}
	
	date_default_timezone_set("America/Chicago");	
	$now = time ();
	$endTime = strtotime($end_date);
	
	if($endTime < $now) { // before now 
		return true;
	}
	return false;
}

/////////////////////////////

function americanDate($date) {
	date_default_timezone_set("America/Chicago");
	$time = strtotime($date);
	if( $time != '' ) {
		$format = "m/d/Y";
		return date($format, $time);
	} else {
		return '';
	}
}


////////////////////////////////////
// from http://stackoverflow.com/questions/9219795/truncating-text-in-php
// truncates a string to the nearest whitespace to the maximum length 
function truncate($text, $chars = 25) {
	$orig_text = $text;
	$text .= ' ';
	$text = substr($text,0,$chars);
	$text = substr($text,0,strrpos($text,' '));
	if( $orig_text != $text ) {
		$text .= '...';
	}
	return $text;
}

//////////
// Quickly create a more usable associative array from the post data sent by json
// TODO: describe this mo better 
function simpleAssocArray($inArray) {
	$outarray = array() ;
	
	// the current format is array('name' => $name, 'value' => $value, $proto_object )
	foreach( $inArray as $object ) {
		$name = $object['name'];
		$value = $object['value'];
		
		$outarray[$name] = $value;
	}
	
	return $outarray;
}

////////////////////
// Set the initials and privileges from the userid 
function getUserInfo( $userid ){
    // query users in database for this information 
    
    $db_obj = new db_class();
    $sql = 'SELECT initials, privileges FROM users WHERE userid=?';
    $db_table = $db_obj->simpleOneParamRequest($sql, 's', $userid);
    $user_row = $db_table[0];
    $db_obj->closeDB();
    
    return $user_row;
}

////////////////////
// Get shibboleth information for userid 
// TODO: get this working with shibboleth
function check_login() {
    $userid = $cfschulte;
}


////////////////////
// Check to see if the user is logged in. 
// I'm moving away from ldap towards using the shibboleth
function check_login_ldap() {
    global $BASE_URL;
    
    	$userid = '';
	if( array_key_exists('lab_inventory', $_COOKIE) ){
		$userid = $_COOKIE['lab_inventory'];
	}
    
    // TODO: Save the Get info and let the user go to where she of he wants. - might 
    // need to be done somewhere else. 
    
	if($userid == '') {  
		$url = $BASE_URL . '/cancer_types/';
		echo header("Location: $url");
		die();
	}
// needed so that the back-button won't take you to that page -- don't know if this does anything
	header("Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0");
	header("Pragma: no-cache");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // A date in the past	

}

