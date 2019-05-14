<?php
// ajax_parser.php -  Thu Mar 1 13:44:13 CST 2018
// 
// I'm separating the ajax parser and json 'return' from the handling code 
// so I can call handling functions without including any non-function-wrapped
// code. 
//

require_once 'essentials.php';

$id = $_POST['id'];
$data = $_POST['data'];

$result = "Well, what have I done?  $id  -- this is the one";

// Handling REGULAR TABLES 
if( $id =='key_value' ) {
    
   require_once 'edit_key_value.php'; 
   $result = updateField( $data ) ;

} elseif ($id == 'new_record') {

    require_once 'edit_key_value.php'; 
    $result = newRecord( $data );

} elseif( $id== 'clone' ){  
    
    require_once 'edit_key_value.php'; 
    $result = cloneRecord( $data );
    
}  elseif ($id == 'userlist_needs_undo') {

    require_once 'edit_key_value.php'; 
    $result = userlistChanges();
    
}  elseif ($id == 'form_needs_undo') {

    require_once 'edit_key_value.php'; 
    $result = enableUndo( $data );
    
} elseif( $id == 'undo_last_generic') {

    require_once 'edit_key_value.php'; 
    $result = undoLastGeneric( $data );
    
} elseif( $id == 'undo_last_user_change') {

    require_once 'edit_key_value.php'; 
    $result = undoLastUserChange( );
    
} elseif( $id == 'search') {  // FIND/SEARCH
    require_once 'search.php'; 
    // Eventually, there will be no return result.
    $result = search_and_display( $data );
//     require_once 'edit_key_value.php'; 
    
} elseif( $id == 'new_user') {  // USERS 
    
    require_once 'edit_key_value.php'; 
    $result = newUser( $data );
    
}  elseif( $id == 'delete_user') {

    require_once 'edit_key_value.php'; 
    $result = deleteUser( $data );
    
} elseif( $id == 'delete_entry') { // DELETE ENTRY 

    require_once 'delete.php'; 
    $result = delete_entry( $data );
    
} elseif( $id == 'activate_undo_entry_delete') { // DELETE ENTRY 

    require_once 'delete.php'; 
    $result = entriesToRestore( $data );
    
}  elseif( $id == 'undo_entry_delete') { // DELETE ENTRY 

    require_once 'delete.php'; 
    $result = undoEntryDelete( $data );
        
}
 
   
        

// Default json reply. The replies are generated within
// the action's scope
echo json_encode( $result );
