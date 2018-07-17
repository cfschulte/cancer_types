<?php
// edit_order.php -  Wed May 16 08:46:40 CDT 2018
// This should be very similar to edit_key_value.php with a few specific
// details. We'll see...
// We probably should have worked harder to resolve the redundancies, but
// that was bogging me down and I wasn't progressing.


require_once "essentials.php";
require_once "db_class.php";



///////////////////////////////////////////////////
// UPDATE order_form_update field 
function update_order_form ($indata) {
    $return_result = array();
    
    $return_result['backup_result'] = backup_order_form_update($indata);
    
    if( $indata['name'] == 'product_name') {
        $return_result['title'] = truncate($indata['value']);
    } 
    
    $db_obj = new db_class();
    $typeHashLong = $db_obj->columnTypeHashLong('orders');
    
    $sql = 'UPDATE orders SET ' . $indata['name'] . '=? WHERE order_number=?';
    $value = $indata['value'];
    if( $typeHashLong[$indata['name']]['type'] =='date' ){
        $value = ensureDate( $indata['value'] );
    }
    $paramList = array($value, $indata['order_number']);
    $typeList = $typeHashLong[$indata['name']]['typeChar'] ;
    $typeList .= 'i';

    $return_result['update_result'] = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
    $db_obj->closeDB();
   
   return $return_result;
}

///////////////////////////////////////////////////
// BACKUP ORDER_FORM_UPDATE field 
function backup_order_form_update($indata) {
//     return $indata['value'];
    
    $db_obj = new db_class();
    
    $typeHashLong = $db_obj->columnTypeHashLong('orders');
    $backup_value = $indata['previousIndependetVal'];
    // make sure the date is in the correct format.
    if( $typeHashLong[$indata['name']]['type'] =='date' ){
        $backup_value = ensureDate( $indata['previousIndependetVal'] );
    }
    
    // start the sql and indicate which fields need to be set 
    $sql =   'INSERT INTO backup_order_fields ' ;
    $sql .=  '(db_table,order_number,form_type,table_column,'. which_backup_field($typeHashLong[$indata['name']]['type'])  . ',time_saved) ';
    $sql .=  ' VALUES (?,?,?,?,?,?) ';

    $typeList = 'siss' . $typeHashLong[$indata['name']]['typeChar'] . 'i';
    $paramList = array('orders', $indata['order_number'], $indata['type'], $indata['name'], $backup_value , time());

    $db_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
    $db_obj->closeDB();
   
//     return array($sql, $typeList, $paramList, 'form_type' => $typeHashLong[$indata['name']]['type']);
    return $db_result;
}


///////////////////////////////////////////////////
//  Determin whether or not the update button should 
// be ENABLED OF DISABLED 
function enableOrderFieldUndo($indata) {
    
    $db_obj = new db_class() ;
    $sql = 'SELECT time_saved FROM backup_order_fields WHERE order_number=?';
    $db_table = $db_obj->simpleOneParamRequest($sql, 'i', $indata['order_number'] );
    $db_obj->closeDB();
    
    return sizeof($db_table);
}

///////////////////////////////////////////////////
// Which database to send save this in?
//    value_varchar    varchar(256) DEFAULT NULL,
//    value_text       TEXT DEFAULT NULL,
//    value_int        int DEFAULT NULL,
//    value_float      float DEFAULT NULL,
//    value_money      decimal(15,2) DEFAULT NULL,
//    value_date      DATE DEFAULT NULL,

function which_backup_field( $column_type ) {   //STRICT COPY FROM EDIT_KEY_VALUE 
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
// UNDO - This will need to check whether or not the last update was
// a multi-field or single field undo 
// NOTE: we might change the db_table field into an information field about 
// whether or not other fields need to be updated. 
function undoLastUpdate($indata) {
    $total_price =  '';
    
    $db_obj = new db_class() ;
    
   // get the id of the last backup for this order_number 
    $sql = 'SELECT MAX(backup_id) FROM backup_order_fields WHERE order_number=? ';
    $db_result = $db_obj->simpleOneParamRequest($sql, 'i', $indata['order_number']);
    $bo_id = $db_result[0]['MAX(backup_id)'];
    
    $sql = 'SELECT * FROM backup_order_fields WHERE backup_id=?';
    $db_result = $db_obj->simpleOneParamRequest($sql, 'i', $bo_id);
    
    $typeHashLong = $db_obj->columnTypeHashLong('orders');
    
    // arrange the parameters so that they are easier to handle.
    $column      = $db_result[0]['table_column'];
    $form_type   = $db_result[0]['form_type'];
    $value_type  = $typeHashLong[$column]['type'];
    $typeChar    = $typeHashLong[$column]['typeChar'];
    $value_field = which_backup_field($value_type);
        
    $value       = $db_result[0][$value_field];
    
    $parameters = array('column'=> $column, 'value_type' =>$value_type, 'typeChar' => $typeChar, 'value_field' => $value_field, 'value' =>$value);
    
 // set the field back to what it was
    $sql = 'UPDATE orders SET ' . $column . '=? WHERE order_number=?';
    $typeList = $typeChar . 'i';
    $paramList = array($value, $indata['id']);
    $revert_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
    
  // pop the undo off of the backup_table "stack" 
    $sql = 'DELETE FROM backup_order_fields WHERE backup_id=?';
    $typeList = 'i';
    $paramList = array($bo_id);
    $pop_result = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList);
    
   
   // should the undo button be enabled?
    $sql = 'SELECT time_saved FROM backup_order_fields WHERE order_number=?';
    $backup_array = $db_obj->simpleOneParamRequest($sql, 'i', $indata['id']);
    $undo_size = sizeof($backup_array);
    
   // close up 
    $db_obj->closeDB();
    
   //  undo the penultimate change if the last undo was total_price    
    if($column == 'total_price' || $column == 'blanket_order_id') {  //TODO:  get this working for blanket_orders 
        $total_price = $value; 
        
        // it would be interesting to see if we could write this as a recursive function.
        $penult_result = undoLastUpdate($indata);
        $column = $penult_result['column'];
        $value  = $penult_result['value'];
        $undo_size = $penult_result['undo_size'];
        $form_type = 'hidden' ;
    }
    
    
    return array('column' => $column, 'value'  => $value,  'form_type'  => $form_type, 'undo_size' => $undo_size, 'total_price' => $total_price);
}


///////////////////////////////////////////////////
// QUANTITY, UNIT_PRICE, SHIPPING, AND TOTAL 
//  We need to think about how to back this up for the undo
//  functionallity. First, update the changed 'column', then
//  update the total price. If the undo button is clicked and 
//  the column name is total_price, then do a double undo.  
function updatePriceInfo($indata) {
    $db_obj = new db_class();
    $typeHashLong = $db_obj->columnTypeHashLong('orders');
    $return_array = array();

    // Back up changed column,
    $column = $indata['column'];
    $backup_data = array('name' => $column, 'order_number'  => $indata['order_number'], 'type'=>'text', 'previousIndependetVal' => $indata['previousIndependetVal'] );
    $return_array['backup_1'] = backup_order_form_update($backup_data);
    
    // Update the newly changed column,
    $sql = 'UPDATE orders SET ' . $column . '=? WHERE order_number=?';
    $value = $indata[ $column ];
    $paramList = array($value, $indata['order_number']);
    $typeList = $typeHashLong[$column]['typeChar'] ;
    $typeList .= 'i';
    $return_array['result_1'] = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList); 
    
    // Back up  the previous total cost 
    $backup_data['name'] = 'total_price';
    $backup_data['type'] = 'hidden';
    $backup_data['previousIndependetVal'] = $indata['previous_total'];
    $return_array['backup_total'] = backup_order_form_update($backup_data);
    
    // Update the total cost
    $sql = 'UPDATE orders SET total_price=? WHERE order_number=?';
    $value = $indata[ 'total_price' ];
    $paramList = array($value, $indata['order_number']);
    $typeList = $typeHashLong['total_price']['typeChar'] ;
    $typeList .= 'i';
    $return_array['result_2'] = $db_obj->safeInsertUpdateDelete($sql, $typeList, $paramList); 
   
    
    $db_obj->closeDB();
    
    return $return_array;
}


///////////////////////////////////////////////////
// BLANKET ORDERS 
// There is a whole lot more being sent here than will be stored (at least for now).
// We might need to rework the the whole relationship between blanket_orders, orders,
// vendors, and products. Make this FLEXIBLE but, for now, just put in the blanket order
// identifier. This might get a whole lot more complicated 
// 
// First off, the blanked order ids need to get updated when the the vendor is changed.
// 
function updateBlanketOrder($indata){
    $return_array = array();
    // update the blanket_order
    $bo_info = array('order_number' => $indata['order_number'], 'type' => 'text', 'name' => 'blanket_order', 'value' => $indata['blanket_order']);
    $return_array['bo_result'] = update_order_form($bo_info);
    
    // update blanket_order_id
    $bo_info = array('order_number' => $indata['order_number'], 'type' => 'hidden', 'name' => 'blanket_order_id', 'value' => $indata['blanket_order_id']);
    $return_array['bo_id_result'] = update_order_form($bo_info);
    
    
    return $return_array;
}

