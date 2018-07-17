<?php
// edit_key_value.php -  Thu Apr 20 13:45:44 CDT 2017
// 
// The ajax call is parsed in ajax_parser.php so we can call these functions
// from wherever they might be needed or useful 
//

require_once "db_class.php";

///////////////////////////////////////////////////
// Takes the data from an existing record and duplicate it.
// This assumes that the primary key is called 'id'.
function cloneRecord($indata){
    $db_obj = new db_class();
    $typeHashLong = $db_obj->columnTypeHashLong($indata['table']);
    
   // Add the word 'clone' to the value that generates the title.
    $fieldForTitle = $indata['title_input']; 
    
   // get the original data
    $sql = 'SELECT * FROM ' . $indata['table'] . ' WHERE id=?';
    $db_table = $db_obj->simpleOneParamRequest($sql, 'i', $indata['id']);
    $the_data = $db_table[0];
    $inventory_table = $indata['table'];
    
   // update the title 
    $the_data[$fieldForTitle] = 'Clone of ' . $the_data[$fieldForTitle];
        
   // use this data to create another entry 
    $result = $db_obj->buildAndExecuteInsert( $inventory_table, $the_data, 'id' );
    
   // get the id of the new clone 
    $this_id = $db_obj->lastInsertedID( $inventory_table );
    
   // some tables require identifying values that are not 'id'
    if( $inventory_table == 'current_lab_inventory' ){

        // for make solution number and id the same 
        $data_elements = array('id'=> $this_id, 'solution_number' => $this_id); 
        
        $result = $db_obj->buildAndExecuteUpdate($inventory_table, $data_elements);
    } elseif ( $inventory_table == 'cell_inventory' ) {
    
        $new_inventory_num = $the_data['inventory_num'] . '_1';
        $data_elements = array('id'=> $this_id, 'record' => $this_id, 'inventory_num' => $new_inventory_num); 
        $result = $db_obj->buildAndExecuteUpdate($inventory_table, $data_elements);
        
    }
   
    $db_obj->closeDB();
    
    $linked_result = cloneLinkedTableData($indata['table'], $this_id, $indata['id']);
        
    return array('new_id' => $this_id, 'old_id' => $indata['id'], 'insert_result' => $result, 'linked_result'=> $linked_result, $data_elements);
}

///////////////////////////////////////////////////
// Clone the data in the tables that are linked to 
// the newly cloned one. This just parses those 
// responsibilities to the actual cloners 
function cloneLinkedTableData($table, $new_id, $old_id){
    $result = 0;
    
    switch ($table) {
        case 'vendors':
            $result = cloneBlanketOrders($table, $new_id, $old_id);
            break;
        case 'biochemical_index':
             $result =  cloneLotInfo($table, $new_id, $old_id); // no break;
         case 'current_lab_inventory':
         case 'antibody_inventory':
            $result = cloneApplicationMatches($table, $new_id, $old_id);
            break;
    }
    
    
//     if($table == 'vendors'){
//         require_once "blanket_orders.php";
//         $result = cloneBlanketOrders($table, $new_id, $old_id);
//     } elseif( $table == 'biochemical_index' || $table == 'current_lab_inventory' ) {
//         $result = cloneApplicationMatches($table, $new_id, $old_id);
//         $result .= 'lot_info' . cloneLotInfo($table, $new_id, $old_id);
//     }
    
    return $result;
}

///////////////////////////////////////////////////
//  
function cloneBlanketOrders($table, $new_id, $old_id) {
    $db_obj = new db_class();
    
    $result = '';
    $sql = 'SELECT * FROM blanket_orders WHERE vendor_id=?';
    $db_table = $db_obj->simpleOneParamRequest($sql, 'i', $old_id);
    foreach ( $db_table as $blanket_order ){
        $blanket_order['vendor_id'] = $new_id;
        // create a new lot_info with the current biochemical id 
        $result .= $db_obj->buildAndExecuteInsert( 'blanket_orders', $blanket_order, 'id' );
    }
    
    $db_obj->closeDB();
    return $result;
}


///////////////////////////////////////////////////
//
function cloneApplicationMatches($table, $new_id, $old_id) {
    $db_obj = new db_class();
    
    $result = '';
    $sql = 'SELECT * FROM application_matches WHERE table_name=? AND table_row_id=?';
    $typeStr = 'si';
    $paramList = array($table, $old_id);
    $db_table = $db_obj->safeSelect($sql, $typeStr, $paramList);
    
    foreach( $db_table as $app_match ){
        $app_match['table_row_id'] = $new_id;
        $result .= $db_obj->buildAndExecuteInsert( 'application_matches', $app_match, 'id' );
    }
    
    $db_obj->closeDB();
    return $result;
}


///////////////////////////////////////////////////
//
function cloneLotInfo($table, $new_id, $old_id) {
    $db_obj = new db_class();
    
    $result = '';
    $sql = 'SELECT * FROM bio_index_lot_info WHERE biochem_id=?';
    $db_table = $db_obj->simpleOneParamRequest($sql, 'i', $old_id);
    foreach ( $db_table as $lot_info ){
        $lot_info['biochem_id'] = $new_id;
        // create a new lot_info with the current biochemical id 
        $result .= $db_obj->buildAndExecuteInsert( 'bio_index_lot_info', $lot_info, 'id' );
    }
    
    $db_obj->closeDB();
    return $result;
}



///////////////////////////////////////////////////
// new 
function newRecord($indata) {
    $db_obj = new db_class();
    $typeHash = $db_obj->columnTypeHash($indata['table']);
    
    if($indata['table'] != 'vendors'){ 
        $column_name =  $indata['name'];
        $sql = "INSERT INTO " . $indata['table'] . ' (id,' . $column_name . ') VALUES (?,?)';
        $typestr = 'i' . $typeHash[$column_name];
         $paramList = array($indata['id'], $indata['value']);
   } else {
       // vendors needs to set current_id to id for new records
        $column_name =  $indata['name'];
        $sql = "INSERT INTO " . $indata['table'] . ' (id,current_id,' . $column_name . ') VALUES (?,?,?)';
        $typestr = 'ii' . $typeHash[$column_name];
        $paramList = array($indata['id'], $indata['id'], $indata['value']);
    }
    $result = $db_obj->safeInsertUpdateDelete($sql, $typestr, $paramList);
    $db_obj->closeDB();

//     return array($sql, $typestr, array($indata['id'], $indata['value']) ) ;
    return array($result, $sql, $typestr, array($indata['id'], $indata['value'])) ;
}




///////////////////////////////////////////////////
// Update fields 
function updateField($indata) {    
    // Create a backup of what existing data for an undo.
    $backup_result = backup_field($indata);
//     return $backup_result;
    
    // generate the sql and parameters
    $db_obj = new db_class();
    $typeHashLong = $db_obj->columnTypeHashLong($indata['table']);
    
    $sql = 'UPDATE ' . $indata['table'] . ' SET ' . $indata['name'] . '=? WHERE id=?';
    $value = $indata['value'];
    if( $typeHashLong[$indata['name']]['type'] =='date' ){
        $value = ensureDate( $indata['value'] );
    }
    
    $paramList = array($value, $indata['id']);
    
    
    $typeList = $typeHashLong[$indata['name']]['typeChar'] ;
    $typeList .= 'i';

    $update_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
    $db_obj->closeDB();
    
//     $return_hash['debug'] = array($sql, $typeList, $paramList, $result);
    
    return array( 'backup_result' => $backup_result, 'update_result' => $update_result );
} 

///////////////////////////////////////////////////
// DROP TABLE IF EXISTS `backup_varchar`;
// CREATE TABLE `backup_varchar` (
//    backup_id          int NOT NULL,
//    db_table           varchar(64) NOT NULL, 
//    id         int  DEFAULT NULL,  -- will always be an int, even if the current db is accessing something else.
//    table_column       varchar(64) DEFAULT NULL,
//    column_value       varchar(256) DEFAULT NULL,  <-- subject to change.
//    time_saved          BIGINT NOT NULL,
//    PRIMARY KEY (`backup_id`)
// );
// 

function backup_field($indata) {
    $db_obj = new db_class();
    
    $typeHashLong = $db_obj->columnTypeHashLong($indata['table']);
    $backup_value = $indata['previousGenericVal'];
    // make sure the date is in the correct format.
    if( $typeHashLong[$indata['name']]['type'] =='date' ){
        $backup_value = ensureDate( $indata['previousGenericVal'] );
    }
    
//     $value_field = which_backup_field( $typeHashLong[$indata['name'] ]['type'] );
   
    
    $sql =   'INSERT INTO backup_table ' ;
    $sql .=  '(db_table,id,form_type,table_column,'. which_backup_field($typeHashLong[$indata['name']]['type'])  . ',time_saved) ';
    $sql .=  ' VALUES (?,?,?,?,?,?) ';
    
    $typeList = 'siss' . $typeHashLong[$indata['name']]['typeChar'] . 'i';
    $paramList = array($indata['table'], $indata['id'], $indata['type'], $indata['name'], $backup_value , time());
    
    $db_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
    $db_obj->closeDB();
//      return array($sql, $typeList, $paramList);
   
    return $db_result;
//     return array($sql, $typeList, $paramList);
}

///////////////////////////////////////////////////
// Which database to send save this in?
//    value_varchar    varchar(256) DEFAULT NULL,
//    value_text       TEXT DEFAULT NULL,
//    value_int        int DEFAULT NULL,
//    value_float      float DEFAULT NULL,
//    value_money      decimal(15,2) DEFAULT NULL,
//    value_date      DATE DEFAULT NULL,

function which_backup_field( $column_type ) {
    if( preg_match("/varchar/" ,$column_type) ) {
        return 'value_varchar';
    } elseif(  preg_match("/text/" ,$column_type) ) {
        return 'value_text';
    } elseif(  preg_match("/int/" ,$column_type) ) {
        return 'value_int';
    } elseif(  preg_match("/float/" ,$column_type) || preg_match("/real/" ,$column_type) || preg_match("/double/" ,$column_type)) {
        return 'value_float';
    } elseif(  preg_match("/decimal/" ,$column_type) ) {
        return 'value_money';
    } elseif(  preg_match("/date/" ,$column_type) ) {
        return 'value_date';
    }
    
    return 'no database';
}


///////////////////////////////////////////////////
//  Determin whether or not the update button should 
// be enabled of disabled 
function enableUndo($indata) {
    
    $db_obj = new db_class() ;
    $sql = 'SELECT time_saved FROM backup_table WHERE db_table=? AND id=?';
    $db_table = $db_obj->safeSelect($sql, 'si', array($indata['table'], $indata['id']));
    $db_obj->closeDB();
    
    return sizeof($db_table);
}

///////////////////////////////////////////////////
//  Determin whether or not the update button should 
// be enabled of disabled 
function userlistChanges() {
    
    $db_obj = new db_class() ;
    $sql = 'SELECT time_saved FROM backup_table WHERE db_table="users"';
    $db_table = $db_obj->getTableNoParams($sql);
    $db_obj->closeDB();
    
    return sizeof($db_table);
}

///////////////////////////////////////////////////
// UNDO 
function undoLastGeneric($indata) {
    
    $db_obj = new db_class() ;
    
    $sql = 'SELECT MAX(backup_id) FROM backup_table WHERE db_table=? AND id=? ';
    $db_result = $db_obj->safeSelect($sql, 'si', array($indata['table'], $indata['id']));
    $bo_id = $db_result[0]['MAX(backup_id)'];
    
    $sql = 'SELECT * FROM backup_table WHERE backup_id=?';
    $db_result = $db_obj->simpleOneParamRequest($sql, 'i', $bo_id);
    
    $typeHashLong = $db_obj->columnTypeHashLong($db_result[0]['db_table']);
   
   // arrange the parameters so that they are easier to handle.
    $column      = $db_result[0]['table_column'];
    $form_type   = $db_result[0]['form_type'];
    $value_type  = $typeHashLong[$column]['type'];
    $typeChar    = $typeHashLong[$column]['typeChar'];
    $value_field = which_backup_field($value_type);
    
    $value       = $db_result[0][$value_field];
    
    $parameters = array('column'=> $column, 'value_type' =>$value_type, 'typeChar' => $typeChar, 'value_field' => $value_field, 'value' =>$value);
    
 // set the field back to what it was
    $sql = 'UPDATE ' . $indata['table'] . ' SET ' . $column . '=? WHERE id=?';
    $typeList = $typeChar . 'i';
    $paramList = array($value, $indata['id']);
    $revert_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
   
  // pop the undo off of the backup_table "stack" 
    $sql = 'DELETE FROM backup_table WHERE backup_id=?';
    $typeList = 'i';
    $paramList = array($bo_id);
    $pop_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
   
   // should the undo button be enabled?
    $sql = 'SELECT time_saved FROM backup_table WHERE db_table=? AND id=?';
    $undo_size = $db_obj->safeSelect($sql, 'si', array($indata['table'], $indata['id']));
    
    $db_obj->closeDB();
    
    // format the date to what the users want.
    if($value_field == 'value_date') {
        $value = americanDate($value);
    }
    
    return array('column' => $column, 'value'  => $value,  'form_type'  => $form_type, 'undo_size' => sizeof($undo_size));
//     return array('db_result' => $db_result, 'revert_result' => $revert_result, 'pop_result' => $pop_result, 'column' => $column, 'value'  => $value);
}



///////////////////////////////////////////////////
// UNDO USER CHANGE
function undoLastUserChange() {
    
    $db_obj = new db_class() ;
    
    $sql = 'SELECT MAX(backup_id) FROM backup_table WHERE db_table="users" ';
    $db_result = $db_obj->getTableNoParams($sql);
    $bo_id = $db_result[0]['MAX(backup_id)'];
    
    
    $sql = 'SELECT * FROM backup_table WHERE backup_id=?';
    $db_result = $db_obj->simpleOneParamRequest($sql, 'i', $bo_id);
    
    $typeHashLong = $db_obj->columnTypeHashLong($db_result[0]['db_table']);
    // arrange the parameters so that they are easier to handle.
    $id          = $db_result[0]['id'];  // THIS IS DIFFERENT FROM THE GENERIC FORM
    $column      = $db_result[0]['table_column'];
    $form_type   = $db_result[0]['form_type'];
    $value_type  = $typeHashLong[$column]['type'];
    $typeChar    = $typeHashLong[$column]['typeChar'];
    $value_field = which_backup_field($value_type);
        
    
    $value       = $db_result[0][$value_field];
    
    $parameters = array('id'=> $id, 'column'=> $column, 'value_type' =>$value_type, 'typeChar' => $typeChar, 'value_field' => $value_field, 'value' =>$value);
    
    
 // set the field back to what it was
    $sql = 'UPDATE users SET ' . $column . '=? WHERE id=?';
    $typeList = $typeChar . 'i';
    $paramList = array($value, $parameters['id']);
    $revert_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);

   
  // pop the undo off of the backup_table "stack" 
    $sql = 'DELETE FROM backup_table WHERE backup_id=?';
    $typeList = 'i';
    $paramList = array($bo_id);
    $pop_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);

   
   // should the undo button be enabled?
    $sql = 'SELECT time_saved FROM backup_table WHERE db_table=? AND id=?';
    $undo_size = $db_obj->safeSelect($sql, 'si', array($indata['table'], $indata['id']));
    
    $db_obj->closeDB();
    
    // format the date to what the users want.
    if($value_field == 'value_date') {
        $value = americanDate($value);
    }
    
    return array('id' => $id, 'column' => $column, 'value'  => $value,  'form_type'  => $form_type, 'undo_size' => sizeof($undo_size));
//     return array('db_result' => $db_result, 'revert_result' => $revert_result, 'pop_result' => $pop_result, 'column' => $column, 'value'  => $value);
}


///////////////////////////////////////////////////
// NEW USER
function newUser($userid){
    
//     return array('result' => 'got_here', 'id' => 666);
    
    $data_elements = array('userid' => $userid);
    $db_obj = new db_class();
    $result = $db_obj->buildAndExecuteInsert('users', $data_elements);
    
    $id = $db_obj->lastInsertedID();
    $db_obj->closeDB();
    
    return array('result' => $result, 'id' => $id);
} 


///////////////////////////////////////////////////
// DELETE USER
function deleteUser($id){
    
    $db_obj = new db_class();
    // LATER - back this up to a former users database 
    
    $sql = 'DELETE FROM users WHERE id=?';
    $result = $db_obj->simpleOneParamUpdate($sql, 'i', $id);
    
    $db_obj->closeDB();
    
    return array('result' => $result);
} 



