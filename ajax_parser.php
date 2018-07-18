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
    $result = 'ajax_parser';
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
    
} elseif( $id == 'update_blanket_order_field' ) {  // BLANKET ORDERS 
    
    require_once 'blanket_orders.php';
    $result = updateBlanketOrderField( $data );
    
} elseif( $id == 'delete_blanket_order_record' ) {
    
    require_once 'blanket_orders.php';
    $result = deleteBlanketOrderRecord($data);
    
} elseif( $id == 'blanket_order_needs_undo' ) {
    
    require_once 'blanket_orders.php';
    $result = enableBlanketOrderUndo( $data );
    
} elseif( $id == 'undo_delete_bo_record' ) {
    
    require_once 'blanket_orders.php';
    $result = undoBlanketOrderDelete( $data );
    
}  elseif( $id == 'new_app_info_li' ) {      // APPLICATION LISTS 
    
    require_once 'application_list_funcs.php';
    $result = prepAndCreateAppRow( $data ) ;

}  elseif( $id == 'remove_application_match' ) {
    
    require_once 'application_list_funcs.php';
    $result = removeApplicationMatch( $data ) ;

} elseif( $id == 'application_matches' ){
    
    require_once 'application_list_funcs.php';
    $result = prepAndExecuteAppUpdate( $data ) ;

} elseif( $id == 'update_application_match_field' ) {

    require_once 'application_list_funcs.php';
    $result = updateOrInsertSingleAppMatch( $data ) ;

} elseif( $id == 'app_match_needs_undo' ) {

    require_once 'application_list_funcs.php';
    $result = enableAppMatchUndo( $data ) ;

} elseif( $id == 'undo_app_match_delete' ) {

    require_once 'application_list_funcs.php';
    $result = undoAppMatchDelete( $data ) ;

} elseif( $id == 'bio_index_lot_info' ) {  // Being deprecated?   -- LOT INFO 

    require_once 'lot_info.php'; 
    $result = saveAllLotInfo($data);
    
} elseif( $id == 'updata_bi_lot_info' ) {

    require_once 'lot_info.php'; 
    $result = updateLotInfoField($data);
    
} elseif( $id == 'remove_lot_info' ) {

    require_once 'lot_info.php'; 
    $result = deleteLotInfo($data);
    
} elseif( $id == 'lot_info_needs_undo' ) { 

    require_once 'lot_info.php'; 
    $result = enableLotInfohUndoButton($data);
    
} elseif( $id == 'undo_lot_info_delete' ) { 

    require_once 'lot_info.php'; 
    $result = undoLotInfoDelete($data);
    
} elseif( $id == 'update_external_doc' ) {  // EXTERNAL DOC 
  // e.g. msds 
    require_once 'update_external_doc.php'; 
    $result = update_external( $data ) ;
    
} elseif ( $id == 'order_form_update_order_field' ) {  // ORDERING
 
    require_once 'edit_order.php';
    $result = update_order_form( $data );
    
} elseif ( $id == 'vendor_order_info' ) {  

    require_once 'edit_order.php';
    
} elseif ( $id == 'order_form_needs_undo' ) {  

    require_once 'edit_order.php';
    $result = enableOrderFieldUndo($data);
    
} elseif ( $id == 'undo_order_update' ) {  

    require_once 'edit_order.php';
    $result = undoLastUpdate($data);
    
} elseif ( $id == 'update_price' ) {  

    require_once 'edit_order.php';
    $result = updatePriceInfo($data);
    
} elseif( $id== 'order_form_blanket_order' ){

    require_once 'edit_order.php';
    $result = updateBlanketOrder($data);
    
} elseif( $id== 'delete_cell_data' ){  // STORAGE LOCATION 

    require_once 'storage_grid.php';

} elseif( $id== 'undo_storage_change' ){

    require_once 'storage_grid.php';
    $result = undo_storage_change($data);

}  elseif( $id== 'cell_inv_field' ){

    require_once 'storage_grid.php';
    $result = update_cell($data);

} elseif( $id== 'storage_needs_undo' ){

    require_once 'storage_grid.php';
    $result = storage_needs_undo($data);

}
 
   
        

// Default json reply. The replies are generated within
// the action's scope
echo json_encode( $result );
