<?php
// table_class.php -  Mon Mar 5 08:13:11 CST 2018
//  This is the parent class for most of the basic tables that interface with 
//  the actual SQL tables. 
//  This will pretty much be a copy of my old table_class, but it will hopefully
//  be cleaned up and documented a bit more.
//

require_once "../essentials.php";
require_once  "db_class.php";


class table_class {
    protected $title;      
    protected $table_name; // sql table 
    protected $orderby;
    protected $edit_page;
  // A  (column => column_name) associative array; not necessarily all columns are listed
    protected $columns_to_show = array(); 
    protected $primary_key;
    
    protected $post_data = array();
    protected $post_action;

    protected $get_action = '';
    protected $get_array = array();
    
    protected  $userid = ''; 
    protected  $user_privileges = 0;
// highlighted_menu_ref?? I still need to figure out how I'm going to do menus


 /*************************************************************************/   
 // I used to have individual setters, but it turned out that all they did 
 // was take up space.
    function __construct($title="No Table", $table_name='', $edit_page, 
                       $columns_to_show, $orderby='', $primary_key='id') {
        $this->title = $title;
        $this->table_name = $table_name;
        $this->orderby = $orderby;
        $this->primary_key = $primary_key;
        $this->edit_page = $edit_page;
        $this->primary_key = $primary_key;
        $this->columns_to_show = $columns_to_show;
    }

 /*************************************************************************/  
 // run the thing.  The post or get will be used for filtering on the search criteria
    function execute() {
        // check the user credientials
        if( array_key_exists('uid', $_SERVER)){
            $this->userid = $_SERVER['uid'];
        } else {
            $this->userid = 'cfschulte';  // this userid comes from check_login 
        }
//          $userInfo = getUserInfo( $this->userid );
//         if( !empty($userInfo) ){
//             $this->user_privileges = $userInfo['privileges'];
//             $this->editing_user_initials   = $userInfo['initials'];
//         }
        $this->user_privileges =3;
        
      // redirect the people from the wrong labs 
        if( $this->user_privileges < 1  ) {
            header('Location: /cancer_types/disallowed.php');
        }
        if( !empty( $_GET ) ){
            $this->get_action = 'use_get'; 
            $this->get_array = $_GET;
        } elseif( !empty( $_POST )) {  // probably want to do this with a get.
            $this->post_data = $_POST ;
            if(array_key_exists('filter_by', $_POST)){
                $this->post_action = 'filter';
            } elseif(array_key_exists('add_row', $_POST)) {
                $this->post_action = 'add';
            }
        } else {
            $this->get_action = 'none';
            $this->post_action = 'none';
        }
        
        $this->userid = $userid;
?>
<!DOCTYPE html>
<html lang="en"> 
         <?php $this->header(); ?>
         <?php $this->body(); ?>
</html>
<?php    
    } /*** end execute **/

 /*************************************************************************/  
 // The HTML header  -- 
   function header() {
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<?php
  // load the javascripts -- this is a little confusing and could be done more better.
  $this->jscript_list();
  $this->css_list();
?>
    <Title><?php echo $this->title; ?></Title>
</head>

<?php 
   }
    
 /*************************************************************************/  
 // The load the javascripts -- it can be called from the child classes 
 // and then augmented.
    function jscript_list() {
  // we might want this later:
  //     <script type="text/javascript" src="/cancer_types/js/jquery.tablesorter.js"></script>


?>
    <script type="text/javascript" src="/jquery/jquery.js"></script>
    <script type="text/javascript" src="/jquery/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/jquery/jquery.tablesorter/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="/cancer_types/js/header_action.js"></script>
    <script type="text/javascript" src="/cancer_types/js/delete.js"></script>
<?php 
    }
    
 /*************************************************************************/  
 // The load the css files -- it can also be called from the child classes 
 // and then augmented.
    function css_list() {
?>
    <link rel="Stylesheet" type="text/css" href="/cancer_types/css/base.css" />
    <link rel="Stylesheet" type="text/css" href="/cancer_types/css/tables.css" />
    <link rel="Stylesheet" type="text/css" href="/cancer_types/css/forms.css" />
    <link rel="Stylesheet" type="text/css" href="/jquery/jquery-ui/jquery-ui.theme.css" />

<?php 
    }


    
 /*************************************************************************/  
   // The body
   function body() {
?>
<body>
<?php
    $this->page_header();
?> 
<input type="hidden" id="table_name" value="<?php echo $this->table_name ; ?>" >
<div id="wrapper">
  <div id="display">
    <?php $this->create_table(); ?>
  </div>
</div>

<?php 
   }


 /*************************************************************************/  
   // page_header
   function page_header() {
?>
<div class="page_header">
<div class="in_header">
<a class="home_button" href="/cancer_types/" title="home"><svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 65 68"><title>home_icon</title><rect x="6.5" y="24.5" width="53" height="43"/><path d="M62,26V68H10V26H62m1-1H9V69H63V25Z" transform="translate(-3 -1)"/><polygon points="1.35 27.5 32.5 0.66 63.65 27.5 1.35 27.5"/><path d="M35.5,2.32,65.31,28H5.69L35.5,2.32M35.5,1,3,29H68L35.5,1Z" transform="translate(-3 -1)"/></svg></a>
 <h1 ><?php echo $this->title; ?></h1>
<!-- 
 <a class="head_action a_button" id="logout_button" href="#">Log Out</a>
 -->

<?php
    $this->additional_page_header_stuff();
?>

 </div>
</div>
 <div style="clear:both;"></div>
<?php 
//      include "../menu.php";
   }

 /*************************************************************************/  
   // additional_header_stuff
   function additional_page_header_stuff() {
?>   
<?php if( $this->table_name != 'users' ): ?>
 <a class="head_action a_button" href="/cancer_types/forms/<?php echo $this->edit_page; ?>?find=yes">Find</a>
<?php endif ?>
 <a class="head_action a_button" href="/cancer_types/views_controllers/<?php echo $this->edit_page; ?>?new=yes">New Item</a>
 <button class="head_action a_button" id="undo_delete" name="undo_delete" >Undo Delete</button>
<?php   
   }


 /*************************************************************************/  
   // create_table
   function create_table() {
      if($this->table_name == '') { return; }
      
      if($this->post_action == 'add') {
         $this->addDBRow();
      }
      
       $this->table_declaration();
       $this->table_head();
       $this->table_body();
       echo  "</table>\n";

   }

 /*************************************************************************/  
   // create_table
   function table_declaration() {
       echo  '<table  class="table_table tablesorter db_table_list">' . "\n"; // generic default can be overridden 
   }
    
    
 /*************************************************************************/  
   // create_filtered_table
   function create_filtered_table() {
       echo "<P>something goes here </P>";
       
       $filter_hash = $this->gen_filter_hash($filter);
   }


 /*************************************************************************/  
   // table_head
   function table_head() {
        
        echo "<thead>\n<tr>";
            foreach( $this->columns_to_show as $col => $title ) {
                echo '<th>' . $title . '</th>';
            }
        echo "</tr>\n</thead>\n";
   }


 /*************************************************************************/  
   // table_body
   function table_body() {
        $db_table =$this->getDBTable();
        
        echo "<tbody>\n";
        foreach( $db_table as $row ) {
            $this->table_row($row);
        }
        echo "</tbody>\n";
    }


 /*************************************************************************/  
   // table_row  -- This is where most of the overriding should happen. 
    function table_row($row) {

        echo '<tr>';
        while ( list($key, $datum) = each($row) ) {
           if($key == $this->primary_key) {
                $this->table_cell_primary($key, $datum);
           } else {
                $this->table_cell($key, $datum);
           }
        }
        echo '</tr>' . "\n";
    }

 /*************************************************************************/  
   // table_cell
    function table_cell($key, $datum) {
        echo "<td>$datum</td>";
    }

 /*************************************************************************/  
   // table_cell  -- adding an 'id' for the tables that use a psuedo-id. For 
   // example, cell_pedigree. Deanna wants to use pedigree_num as the major 
   // identifier, but does not want to be strict about its format.
    function table_cell_primary($key, $datum, $id='') {
//        echo '<td class="link_button order"><a title="order" href="/cancer_types/forms/' . $this->edit_page .'?id=' . $datum . '&table=' .$this->table_name . '" target="_blank">' . $datum . '</a></td>';
       echo '<td class="link_button order"><a title="order" href="/cancer_types/views_controllers/' . $this->edit_page .'?id=' . $datum . '&table=' .$this->table_name . '">' . $datum . '</a></td>';
            
    }
   
   

 /*************************************************************************/  
   // getDBTable - THIS is where we select the rows according to the filter.
   function getDBTable() {
        // start with a general db_obj 
        $db_obj = new db_class();
        
        if( $this->post_action == 'filter') {
            $filter_hash = $this->gen_filter_hash(); // Turn the result of the filter form into a hash
            $basic_select = $this->select_what();    // Which columns do you want to select?
            
          // turn this into something that can be safely bound with mysqli 
            $query_parameters = $this->gen_filtered_sql($db_obj,  $filter_hash, $basic_select );  // turn this into 
            
          // execute
            $db_table = $db_obj->safeSelect($query_parameters['sql'], $query_parameters['types'], $query_parameters['paramList']);
        } elseif( $this->get_action == 'use_get'){
            $db_table = $this->table_from_get_params();
        } else {
            $sql = $this->get_sql();
            $db_table = $db_obj->getTableNoParams($sql);
//             showArray($db_table);
        }
        
        $db_obj->closeDB();
        
        return $db_table;
    }
   
 /*************************************************************************/  
   // get_sql  -- Meant to be overridden by child classes
   function get_sql() {
        $sql = 'SELECT ';
        if( ! empty($this->columns_to_show) ) {
            $count = 0;  // say when to put in the commas
            foreach($this->columns_to_show as $col => $title) {
               if($count > 0) {
                    $sql .= ',';
                }
                $sql .= $col;
                $count++;
            }
        } else {
            $sql .= '* ';
        }
        $sql .= ' FROM ' . $this->table_name ;
        
        
        // does it need to be in any particular order?
         $sql .= $this->add_order_by();
        
        
       return $sql;
    }

   
 /*************************************************************************/  
   // Deconstructs the get array to generate a where clause
   // Incorporates most of get_sql but it binds all the where specifications,
   // which were passed by a get statement, so that it won't be (as) hackable.
   // We probably don't need this but it's good to be thorough 
   function table_from_get_params() {
        $typestr ='';
        $params = array();
        
        $db_obj = new db_class();
        
        // Get the primary key and its type.
        $primary_key = $db_obj->getPrimaryKey($this->table_name);
        $typechar = $db_obj->columnTypeHash($this->table_name)[$primary_key];
        
        $sql = 'SELECT ';
        if( ! empty($this->columns_to_show) ) {
            $count = 0;  // say when to put in the commas
            foreach($this->columns_to_show as $col => $title) {
               if($count > 0) {
                    $sql .= ',';
                }
                $sql .= $col;
                $count++;
            }
        } else {
            $sql .= '* ';
        }
        $sql .= ' FROM ' . $this->table_name ;
        
       // Do the where clause - safely
        $sql .= ' WHERE ';
        $count = 0;
        foreach($this->get_array as $key => $value){
            if($count > 0){ $sql .= ' OR ';}
            
            $sql .= $primary_key . '=?' ;
            $params[] = $value;
            $typestr .= $typechar;
            
            // COUNT 
            $count++;
        }

       // does it need to be in any particular order?
        $sql .= $this->add_order_by();
        
        $db_table = $db_obj->safeSelect($sql, $typestr, $params);
        $db_obj->closeDB();
        
        return $db_table;
   }

 /*************************************************************************/  
   // Different (e.g. cell_pedigree) tables need to order their records differently.
   // This allows this without having to rewrite get_sql or table_from_get_params.
   function add_order_by() {
         if( ! empty($this->orderby) ) {
            return " ORDER BY $this->orderby";
         } else {
            return '';
         }
   }


 /*************************************************************************/  
    // DEPRECATE Create an associative array of column, boolean, and value to search 
    function gen_filter_hash() {
        
        
        $filter_hash = array();
        
        $current_index = "0";
        foreach($this->post_data as $key=>$value) {
            if ($key == 'table' || $key == 'filter_by') { 
              // ignore the form elements that are used for other purposes
                continue; 
            }
        
            // the relavant form names have a structure of <information type>_<information group>
            // e.g. columnName_1, value_1 - date (start and end) is handled differently
            $word_pair = preg_split('/_/', $key);
            $index = $word_pair[1];
            $sub_key = $word_pair[0];
            if($index != $current_index) {
                $filter_hash[$index] = array($sub_key => $value);
                $current_index = $index;
            } else {
                $filter_hash[$index][$sub_key] = $value;
            }
        }
    
        return $filter_hash;
    }


 /*************************************************************************/  
    // This is like gen_filtered_sql from basic_table.php -- need to update it
    // RETURNS an array of sql,typelist, and paramList.
    function gen_filtered_sql($db_obj, $filter_hash, $basic_select = 'SELECT * FROM ') {
        $num_criteria = count($filter_hash); 
        
        $sql = $basic_select . $this->table_name . ' WHERE ';
        $types = '';
        $paramList = array();
        
        
        $col_types = $db_obj->columnTypeHash($this->table_name);  //From ajax_helpers
        $num_criteria_handled = 0;
    
    //     showArray($filter_hash);
        foreach($filter_hash as $key => $col) {
            if($key == "date") {
                continue;
            }
            $num_criteria--;
            if( trim($col['val']) == '' ) {
                continue;
            }
            if($num_criteria_handled > 0) {   
                $sql .= ' ' . $col['bool'] . ' '  ;
            }
                
            // sql conditional 
            $sql .= $col['column'] . ' LIKE ?';
        
            // type 
            $types .=  $col_types[$col['column']];
        
            // parameter 
            $paramList[] = '%' . $col['val'] . '%';
        
            $num_criteria_handled++;
        }  // end foreach 
    
        // handle the date.
        if(array_key_exists('date', $filter_hash)){
            $col = $filter_hash['date'];
            $date_type = date_type($col);
            if($date_type != 0 && !empty($paramList)) {
                $sql .= ' AND (';
            }
            switch($date_type) {
                case 0:
                    // get out 
                    break;
                case 1:
                    // no start date 
                    $sql .= 'date <?' ;
                    $paramList[] = ensureDate($col['end']);
                    $types .= 's';
                    break;
                case 2:
                    // no end date 
                    $sql .= 'date > ?' ;
                    $paramList[] = ensureDate($col['start']);
                    $types .= 's';
                    break;
                case 3:
                    // has both start and end dates 
                    $sql .= 'date BETWEEN ? AND ?' ;
                    $paramList[] = ensureDate($col['start']);
                    $types .= 's';
                    $paramList[] = ensureDate($col['end']);
                    $types .= 's';
                    break;
            }
            if($date_type != 0 && $num_criteria_handled > 0) {
                $sql .= ')';
            }

        }
    
        $query_parameters = array("sql"=>$sql, "types"=>$types, "paramList"=> $paramList);
        return $query_parameters;
    } 

 /*************************************************************************/ 
   // This is a rehash of the addWhatever from all_table_helpers.
   // It is called before the table is created so that the table can
   // be created normally. 
    function addDBRow() {
        $table = $this->post_data['table'];

        $db_obj = new db_class();

        $typeHash = $db_obj->columnTypeHash($table);
    //     showArray($table_desc);
        $sql='INSERT INTO ' . $table ;
        $columnStr = ' (';
        $valueStr = ' VALUES (';
        $isFirst = true;
    
        $typeStr = '';
        $values = array();
    
        foreach($typeHash as $col =>$type) {
            if(empty($this->post_data[$col])) {
                continue;
            } else { // add it to the row field 
                $typeStr .= $type;
               
               // insert a comma befor every field, except the first.
                if( $isFirst ) { 
                    $isFirst = false;
                } else {
                    $valueStr .= ',';
                    $columnStr .= ',';
                }
                $valueStr .= '?';
                $columnStr .= $col;
                $values[] = $this->post_data[$col];
            }
        }
     
         $valueStr .= ')';
         $columnStr .= ') ';
         $sql .= $columnStr . $valueStr ;
     
        $result = $db_obj->safeInsertUpdateDelete($sql, $typeStr, $values) ;
        $db_obj->closeDB();
    }


}