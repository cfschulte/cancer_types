<?php
// delete.php -  Thu Oct 20 15:48:52 CDT 2016
//   As of May 30, 2018, we are going to revamp this.

// require_once "../essentials.php";
require_once "db_class.php";

////////////////////////////////////////////////////////
//   MAIN DELETE 
function delete_entry($indata) {
    $db_obj = new db_class();
    
    $table = $indata['table'];
    $primary_key = $indata['primary_key'];
    $primary_key_val = $indata['primary_key_val'];
    
    $sql = 'SELECT * FROM ' . $table . ' WHERE ' . $primary_key . '=?';
    $db_table = $db_obj->simpleOneParamRequest( $sql, 'i', $primary_key_val );
//     return array( $sql, 'i', $primary_key_val ); 
    
    
    $data_elements = array();
    $data_elements['table_name'] = $table;
    $data_elements['table_prim_key'] = $primary_key;
    $data_elements['prim_key_val'] = $primary_key_val;
    $data_elements['entry_data']  = sqlRowToXML($db_table[0]);
    $data_elements['time_saved']  = time();
    
    $result = array();
   // back it up 
    $result['backup'] = $db_obj->buildAndExecuteInsert('backup_deleted_entries', $data_elements, 'id');
    
   // AND delete it from this one. 
    $sql = 'DELETE FROM ' . $table . ' WHERE ' . $primary_key . '=?';
    $result['delete'] = $db_obj->simpleOneParamUpdate( $sql, 'i', $primary_key_val );
   
    $db_obj->closeDB();
    
    return $result; 
};



////////////////////////////////////////////////////////
//  XML
function sqlRowToXML($sqlRow){
    $blob = '';
    
    foreach($sqlRow as $key => $value) {
        if(is_null($value)){ continue; }
        
        $blob .= '<datum>';
        $blob .= '<key>' . $key . '</key>';
        $blob .= '<value>' . $value . '</value>';
        $blob .= '</datum>';
    }
    
    return $blob; 
}

////////////////////////////////////////////////////////
// ENABLE/DISABLE
// does the undo_delete button need to be enabled?
// aka how many deleted entries are there 
function entriesToRestore($table_name){
    $db_obj = new db_class();
    $sql = 'SELECT id FROM backup_deleted_entries WHERE table_name=?';
    $db_table = $db_obj->simpleOneParamRequest( $sql, 's', $table_name );
    $db_obj->closeDB();
    
    return array( 'num_deletes' => sizeof($db_table) ); 
}


////////////////////////////////////////////////////////
// UNDO 
function undoEntryDelete($table_name) {
    $result = array();
    
    // open the database
    $db_obj = new db_class();
    
    // Figure out which backup to undo 
    $sql = 'SELECT MAX(id) FROM backup_deleted_entries WHERE table_name=? ';
    $db_result = $db_obj->simpleOneParamRequest( $sql, 's', $table_name );
    $result['id'] = $db_result[0]['MAX(id)'];
    
   // Get the backup data 
    $sql = 'SELECT table_prim_key, prim_key_val, entry_data FROM backup_deleted_entries WHERE id=?';
    $db_result = $db_obj->simpleOneParamRequest( $sql, 'i', $result['id'] );
    
    $primary_key = $db_result[0]['table_prim_key'];
    $key_val     = $db_result[0]['prim_key_val'];
    $xml_blob    = $db_result[0]['entry_data'];
    
   // Re-add to the database 
    $data_elements = unpackXML($xml_blob);
    $result['data_elements'] = $data_elements;
    
    $result['readd_result'] = $db_obj->buildAndExecuteInsert($table_name, $data_elements);
    
   // Remove that entry from the backup 
    $sql = 'DELETE FROM backup_deleted_entries WHERE id=?';
    $result['pop_result']  = $db_obj->simpleOneParamUpdate( $sql, 'i', $result['id'] );
    
   // Get the latest count 
    $sql = 'SELECT id FROM backup_deleted_entries WHERE table_name=?';
    $db_table = $db_obj->simpleOneParamRequest( $sql, 's', $table_name );
    $result['backup_count'] = sizeof($db_table);
    
   // close db 
    $db_obj->closeDB();
    
    return $result; 
//     return array($db_result, $result); 
}


////////////////////////////////////////////////////////
// UNPACK XML 
// Take the XML blob and  
function unpackXML($xml_blob) {
    $data = array();
    $result = preg_match_all('|<datum>(.*)</datum>|U', $xml_blob, $data);
    
    $data_elements = array();
    foreach($data[1] as $datum) {
        $key  = array();
        $result = preg_match('|<key>(.*)</key>|U', $datum, $key);
       
        $value = array();
        $result = preg_match('|<value>(.*)</value>|U', $datum, $value);

        $data_elements[$key[1]] = $value[1] ;
    }
    
    return $data_elements;
}


