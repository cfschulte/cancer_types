<?php
// inter_table_access.php -  Tue May 1 13:17:25 CDT 2018
// 
// I ran into trouble using static class methods in edit_table_form classes
// because they are all instantiated whenever upon inclusion. Sometimes,
// a simple function is just what one needs.
//

require_once "../essentials.php";
require_once "db_class.php";


 /*************************************************************************/ 
   // 
function vendorPassword($id) {
    if(! $id ) {
        return;
    }
    $sql = 'SELECT rapraeger_user, rapraeger_pswd FROM vendors WHERE id=?';
    $typeStr = 'i';

    $db_obj = new db_class();
    $userPass = $db_obj->simpleOneParamRequest($sql, $typeStr, $id);
    $db_obj->closeDB();

    return $userPass;
}

// from edit_vendor.php
//  They have a related list of information for each 
// lot that comes in. This is stored in bio_index_lot_info.
function vendorList() {
    $sql = 'SELECT id, vendor FROM vendors ORDER BY vendor';
    $db_obj = new db_class();
    $vendorlist = $db_obj->getTableNoParams($sql);
    $db_obj->closeDB();
    
    return $vendorlist;
}


// This is a quasi auto-increment for some of the tables that
// had ids but no real rhyme or reason to them. 
function getNextID($table, $primary) {
    $sql = "SELECT $primary FROM $table ORDER BY $primary DESC LIMIT 1";
//     showDebug($sql);
    $db_obj = new db_class();
    $last = $db_obj->getTableNoParams($sql);
    $db_obj->closeDB();
//     showArray($last);
    return $last[0][$primary] + 1;
}

///////////////////////
function vendorShippingCost($id) {
    $sql = 'SELECT shipping FROM vendors WHERE id=?';
    $typeStr = 'i';  // will be i at some point 
    $db_obj = new db_class();
    $shipping = $db_obj->simpleOneParamRequest($sql, $typeStr, $id);
    $db_obj->closeDB();
    
    return $shipping;
}



///////////////////////
//  for the products page 
function buildVendorSelection($vendor_id) {
    $vendorlist = vendorList();
    echo '<select id="vendor_id" name="vendor_id">' . "\n";
    // addd the '0' option 
    if($vendor_id == '0' || $vendor_id == '') {
        echo '<option  value="0" selected>Incorrect, unset, or unavailable vendor_id</option>' . "\n";
    } else {
         echo '<option value="0">Incorrect or unavailable</option>' . "\n";
    }
    // do the rest 
    foreach($vendorlist as $vendor) {
        if( $vendor['id'] == $vendor_id) {
            echo '<option   value="' . $vendor['id'] . '" selected>'. $vendor['vendor'] . '</option>' . "\n";
        }  else {
            echo '<option   value="' . $vendor['id'] . '" >'. $vendor['vendor'] . '</option>' . "\n";
        }
    }
    
    echo '</select>' . "\n";
}

