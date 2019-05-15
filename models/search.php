<?php
// search.php -  Mon Jul 9 14:26:20 CDT 2018
// Our new search method is to open an empty edit form that is 
// altered to input the criteria for the search. These criteria
// are passed here and the query is generated and the results 
// are returned.

require_once "db_class.php";

///////////////////////////////////////////////////
// Perform SEARCH and send the results to the DISPLAY table 
function search_and_display( $criteria ) {
    $edit_page = $criteria['edit_page'];
    $conjunction = $criteria['conjunction'];
    
    $form_data = simpleAssocArray( $criteria['form_data'] );
    $table_display = $criteria["table_display"];
    
    $result_table = generate_and_execute_query($form_data, $conjunction);
    if( empty($result_table)){
       // tell the user that this yielded nothing 
        return array('inform' => "No matching records.");
    } else {
        $url = '/cancer_types/views_controllers/' .  $table_display . '?';
        
        $count = 0;
        foreach($result_table as $sub) {
            foreach($sub as $key=>$value){
                if($count > 0) {
                    $url .= '&';
                }
                $url .= $key . $count . '=' . $value;
                $count++;
            }
        }

        return array('redirect' => $url);
    }
}

///////////////////////////////////////////////////
// Build the SQL QUERY 
function generate_and_execute_query($form_data, $conjunction){
    $table = $form_data['table'];
    
    $db_obj = new db_class();
    // get the type_hash 
    $columnTypeHash = $db_obj->columnTypeHash($table);
    // get the primary key 
    $primary_key = $db_obj->getPrimaryKey($table);
    
    // generate the query
    $sql = "SELECT $primary_key FROM $table WHERE";
    $params = array();
    $typestr = '';
    
    foreach ($columnTypeHash as $column => $typechar){
        if(array_key_exists($column, $form_data) && !empty(trim($form_data[$column]))) {
            // add the proper conjunction for the query 
            if( sizeof($params) > 0) {
                $sql .= $conjunction;
            }
            $sql .=  ' '  . $column . ' LIKE ? ' ;
            $typestr .= $columnTypeHash[$column];
            $params[] = '%'. $form_data[$column] . '%';
        }
    }
    
    // execute the search.
    $db_table = $db_obj->safeSelect($sql, $typestr, $params);
    
    $db_obj->closeDB();
    
    return $db_table;
}

