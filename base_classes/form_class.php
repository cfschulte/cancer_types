<?php
// base_classes/form_class.php -  Mon Mar 5 11:31:40 CST 2018
// This is a cleaned up version of form_class from cancer_types, etc.

require_once "../essentials.php";
require_once "db_class.php";

class form_class {
    protected $title;
    protected $table_name;
    protected $primary_key;
    protected $primary_key_value;
    protected $table_display;
    
    protected $is_new = 0;
    protected $is_find_form = 0;
//     protected $is_clone;  // get rid of this one?  -- yes!!
    protected $problem_with_page = 0;
    
    protected  $editing_userid;  // to not be confused with the userid, which is a column in users 
    protected  $editing_user_initials;
    protected  $user_privileges;
    
 /*************************************************************************/   
    function __construct( $table_name='', $primary_key = 'id', $table_display='table_class.php') {
        $this->title = 'Empty';
        $this->is_new = 0;
        
        $this->table_name = $table_name;
        $this->table_display = $table_display;
        $this->primary_key = $primary_key;
    }

 /*************************************************************************/  
 // Execute the page.  
    function execute() {
        $this->editing_user_privileges = 0;
        if( array_key_exists('uid', $_SERVER)){
            $this->editing_userid = $_SERVER['uid'];
        } else {
            $this->editing_userid = 'cfschulte';  // this userid comes from check_login 
        }

//         $this->editing_user_initials = $this->initials($this->editing_userid);  
//         $this->editing_user_privileges = $this->privileges($this->editing_userid);  
        
        if(!empty($_POST) ) {
            $this->handle_post($_POST);
        } elseif(!empty($_GET) ) {
//     showArray($_GET);
            $this->handle_get($_GET);
        } else {
            $this->create_new();
        }
?>
<!DOCTYPE html>
<html>
         <?php $this->header(); ?>
         <?php 
             if(! $this->problem_with_page ) { 
                $this->body(); 
             } else  {
                $this->warnUser();
             }
         ?>
</html>
<?php 
    } /*** end execute **/

    
 /*************************************************************************/  
 // The header  -- 
 // 
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
?>
    <script type="text/javascript" src="/jquery/jquery.js"></script>
    <script type="text/javascript" src="/jquery/jquery-ui/jquery-ui.min.js"></script>
     
    <script type="text/javascript" src="/cancer_types/js/date_handler.js"></script>
    <script type="text/javascript" src="/cancer_types/js/delete.js"></script>
    <script type="text/javascript" src="/cancer_types/js/header_action.js"></script>  
    <script type="text/javascript" src="/cancer_types/js/forms.js"></script>  

<?php 
    }
    
 /*************************************************************************/  
 // The load the javascripts -- it can be called from the child classes 
 // and then augmented.
    function css_list() {
?>
<link rel="Stylesheet" type="text/css" href="/cancer_types/css/style.css" />    <link rel="Stylesheet" type="text/css" href="/jquery/jquery-ui/jquery-ui.min.css" />

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
<div id="wrapper">
  <div id="display">
    <?php $this->makeForm(); ?>
  </div>
</div>
</body>
<?php 
   }

 /*************************************************************************/  
   // Tell the user if the request couldn't be handled 
   function warnUser() {
?>   
<body>
<?php
    $this->page_header();
?> 
<div id="wrapper">
  <div id="display">
    <h3>We're sorry, but there was a problem processing this request.</h3>
  </div>
</div>
</body>

<?php 
   }

 /*************************************************************************/  
   // page_header
   function page_header() {
?>
<div class="page_header">
<div class="in_header">
<a class="home_button" href="/cancer_types/" title="home"><svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 65 68"><title>home_icon</title><rect x="6.5" y="24.5" width="53" height="43"/><path d="M62,26V68H10V26H62m1-1H9V69H63V25Z" transform="translate(-3 -1)"/><polygon points="1.35 27.5 32.5 0.66 63.65 27.5 1.35 27.5"/><path d="M35.5,2.32,65.31,28H5.69L35.5,2.32M35.5,1,3,29H68L35.5,1Z" transform="translate(-3 -1)"/></svg></a>
 <h1><?php echo $this->title; ?></h1>

<!-- 
 <a class="head_action a_button" id="logout_button" href="#">Log Out</a>
 -->
<?php 
    $this->additionalHeaderStuff();
?>
 </div>
 <div style="clear:both;"></div>
<?php 
  include "../menu.php";   
  ?>
 </div>
<?php
//  <div style="clear:both;"></div> used to be in menu.php, but this is more flexible
//      include "../menu.php";
   }

 /*************************************************************************/  
   // additionalHeaderStuff - form specific buttons 
   function additionalHeaderStuff() {
        
        return;
   }


 /*************************************************************************/  
   // Do the basic form things.  -- most likely to be overridden 
   function makeForm() {
        if($this->table_name != '' ) {
            $db_obj = new db_class();
            $desc_table = $db_obj->tableDescription($this->table_name);
            $db_obj->closeDB();
        
//             showArray($desc_table);
            echo '<form id="generic_edit_form" method="POST" >';
            echo '<input type="hidden" name="title_input"  value="name"> '; // THIS MUST BE HERE FOR CLONABLE FORMS!!!
            echo '<input type="hidden" name="table"  value="vendors">';     // THE TABLE MUST BE LABELED
            echo '<b>This is a generic input</b> <input type="text" name="generic">' . "\n";
        
            echo '<br><br><input type="submit" >';
            echo '</form>';
        } else {
            echo '<h2>hello, form<h2>';
        }
   }

      
/*************************************************************************/ 
  // A place for consistent, hidden information for the delete buttons 
       function deleteInfo() {
?>
    <form method="post"  id="delete_form">
    <!-- for deletes to work, this must be here and the values must be in this order -->
     <input type="hidden" id="primary_key"  name="primary_key" value="<?php echo $this->primary_key ?>">
     <input type="hidden" id="primary_key_value" name="primary_key_value" value="<?php echo $this->primary_key_value ?>">
     <input type="hidden" id="delete_from_table" name="delete_from_table" value="<?php echo $this->table_name ?>">
     <input type="hidden" id="return_address" name="return_address" value="/cancer_types/tables/<?php echo $this->table_display ?>">
    </form>
<?php
       }


    
 /*************************************************************************/  
   // set initial variables with 
   function handle_get($indata) {
//        showDebug('form_class GET:');
//        showArray($indata);
        if(array_key_exists('new', $indata)) {
            $this->is_new = 1;
            $this->title = 'New data';
        } 
        if(array_key_exists('find', $indata)){
            $this->is_find_form = 1;
            $this->title = 'Find in ' . $this->table_name;
        }
   }
    
    
 /*************************************************************************/  
   // set initial variables with 
   function handle_post($indata) {
//        showDebug('form_class POST:');
//        showArray($indata); 
       return;
   }
 
 /*************************************************************************/  
   // set initial variables with ???????/
   function create_new() {
        if($this->table_name != '' ) {
            $this->title = 'New ' . $this->table_name .
            $this->makeForm();
        }
   }
 
 /*************************************************************************/  
   // Clone the original record in the Database. 
   // This assumes the datatypes match correctly.
   function clone_record($db_table) {
        $db_obj = new db_class();
        $typeHash = $db_obj->columnTypeHash($this->table_name);
        
        $sql = 'INSERT INTO ' . $this->table_name ;
        $typeList = '';
        $columns = ' (' ;
        $values = '(' ;
        $paramList = array();
        $count = 0;
        
        foreach($db_table as $key => $value) {
            if($count > 0){ 
                $columns .= ',' ; 
                $values .= ',';
            }
            $columns .= $key;
            $values .= '?';
            $paramList[] = $value;
            $typeList .= $typeHash[$key];
            
            $count++;
        }
        
        $columns .= ')';
        $values .= ')';
        $sql .= $columns . ' VALUES ' . $values;
        
        showArray(array($sql, $typeList, $paramList));
        
        $db_obj->closeDB();
        
   }


 /*************************************************************************/  
    /////////////
    // get the largest primary ID - lots of these are auto-increment, so this is mostly for display.
    // NOTE: We should leave this to AUTO_INCREMENT.

    function getLargestPrimaryID() {
      
        $db_obj = new db_class();
        $table_count =  $db_obj->tableCount($this->table_name);

        if($table_count > 0){
            $sql = "SELECT MAX(" . $this->primary_key . ") AS id FROM " . $this->table_name ;
            $db_table = $db_obj->getTableNoParams($sql);
            $largest_id = $db_table[0]['id'];
        } else {
            $largest_id = 0;
        }
        $db_obj->closeDB();
    
       return $largest_id;
    }
 
  
 /*************************************************************************/  
   // set initial variables with 
   function fetchRecordInfo($key, $identifier) {
//         showDebug($identifier);
        
        $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE ' . $this->primary_key . '=?';
        
        $db_obj = new db_class();
        $keyTypeHash = $db_obj->columnTypeHash($this->table_name);
        $keyType = $keyTypeHash[$this->primary_key];
        $db_table = $db_obj->simpleOneParamRequest($sql, $keyType, $identifier);
        $db_obj->closeDB();
//         showArray(array($sql, $keyType, $identifier));
//         showArray($db_table);
        
        return $db_table[0];
   }

    ///////
    //  Build a set of radio-buttons
    // NOTE: Maybe I should change the order of these....
    function buildGenericRadioGroup($tablename, $form_name, $id_name, $label_name, $current_choice=0, $disabled=0) {
        $choices = $this->getChoiceList($tablename, $id_name, $label_name);

        foreach($choices as $choice) {
            echo  $choice[$label_name] ;
            echo ': <input   type="radio" name="' . $form_name . '" value="'  . $choice[$id_name] . '"' ;
            // is this checked?
            if($choice[$id_name] === $current_choice) {
                echo ' checked ';
            }
            echo '> ';
        }
    }


   ///////
   //  Build a SELECT pulldown -- echo a select input directly to the output 
   // NOTE: I should change the order of these....
    function buildGenericSelect($tablename, $form_name, $id_name, $label_name, $current_choice=0, $disabled=0) {
        $pulldownList = $this->getPulldownList($tablename, $id_name, $label_name);

        echo '<select id="' . $form_name . '" name="' . $form_name . '"';
        if($disabled) {
           echo ' disabled '; 
        }
        echo '>' . "\n";
        echo "<option></option>\n"; // Start out with a blank option.
    
        foreach($pulldownList as $choice) {
            echo '<option value="' . $choice[$id_name] . '" ';  
            $this->setSelection($choice[$id_name], $current_choice);
            echo '>' . $choice[$label_name] . '</option>' . "\n";
        }
        echo "</select>\n";
    }


    // //////////////////////
    function getPulldownList($tablename, $id_name, $label_name) {
         $sql = "SELECT $id_name, $label_name FROM  $tablename";
        
        $db_obj = new db_class();
        $db_table = $db_obj->getTableNoParams($sql);
        $db_obj->closeDB();
        return     $db_table;
    }

    // //////////////////////
    // Used to set the correct option in a select input.
    //  Does nothing if the option is not selected 
    function setSelection($option, $status) {
        echo ($option === $status)? 'selected': '';
    }

    // //////////////////////
    // Used to set the correct option in a select input.
    //  Does nothing if the option is not selected 
    function setSelectionBuffer($option, $status) {
        return ($option == $status)? 'selected': '';
    }
    
  ////////////////
  //  This is a generic checkbox. It should be overwritten by a child class.
      function checkBox($list_table_name, $item, $idListOfChecked) {
            $inputId = $list_table_name . $item['id'];
            echo '       <input type="checkbox" class="' . $list_table_name . '" name="' . $list_table_name . '" group="' . $list_table_name . '" id="' . $inputId . '"' ;
            echo ' value="' . $item['id'] . '" ' . $this->isChecked( $idListOfChecked, $item['id'] )  . '> ';
            echo ' <label for="'. $inputId .'">' . $item['descriptor'] . "</label>\n";
      }
      
      
  ////////////////
  // check to see if a var type check box should be checked or not
    function isChecked ($checkedList, $checkBoxID) {
    
        if(empty($checkedList)){ // return nothing if there is nothing set.
            return ;
        }

       // return the word 'checked' if it should be checked 
        if(in_array($checkBoxID, $checkedList)) { 
            return ' checked ';
        }
    
       // default: do nothing
        return ;
    }



    
 /*************************************************************************  
 UNDERSTAND WHAT IS GOING ON BEYOND THIS. INITIALS SHOULD BE SET ALREADY
 AND TAKEN FROM THE USERNAME
 *************************************************************************/ 
 
 /*************************************************************************/  
   // Right now, this is only used for ordering. I'm putting it here
   // because there might be a time in the future when they will want
   // more things initialed 
    function initials($userid) {
        return $this->userFieldFromID('initials', $userid);
    }
 
 /*************************************************************************/  
   // 
    function privileges($userid) {
        return  $this->userFieldFromID('privileges', $userid);
    }

 /*************************************************************************/  
   // 
    function userFieldFromID($field, $userid) {
        $sql = "SELECT $field FROM users WHERE userid=?";

        $db_obj = new db_class();
        $typeHash = $db_obj->columnTypeHash('users');
        $typeStr = $typeHash[$field];
//         showDebug($field . ' type ' . $typeStr);
//         showArray(array($sql, $typeStr, $userid));
        $db_table = $db_obj->simpleOneParamRequest($sql, $typeStr, $userid);
        $db_obj->closeDB();
        return $db_table[0][$field];
    }
  
 /*************************************************************************/  
   // The value of 'name might be the user's initials or it might be a 
   // full name. There are quite a few possibillities, so we'll focus on 
   // the major ones.
    function initialsFromName($name) {
//         showDebug($name) ;
        $initials = 'dl';
        // figure out whether we have a full name or initials
        $name_length = strlen($name);
//         showDebug($name_length) ;
        if($name_length < 4) { // the name might be the initials 
            $initials = $this->verifyAndSet($name);
        } else {
        // get the first and last. ignore the any middle - though De Mars might be a problem...
            $nameArray = preg_split("/\s+/", $name);
            $firstName = array_shift($nameArray);
            $lastName = array_pop($nameArray);
            $sql = 'SELECT initials FROM users WHERE firstname=? AND lastname=?';
            $typeStr = 'ss';

            $db_obj = new db_class();
            $db_table = $db_obj->safeSelect($sql, $typeStr, array($firstName, $lastName));
            $db_obj->closeDB();
            $initials = $db_table[0]['initials'];
//             showArray($db_table);
        }
        return $initials;
    }

 /*************************************************************************/  
   // If the user entered their initials in the name field, this will ensure 
   // that they are consistent with the database. It will also set the 'ordered_by'
   // if it is not already. Some of this needs to be done because we are importing
   // from the old access database.
    function verifyAndSet($initials) {
        // get the current initial list.
        $sql = 'SELECT initials FROM users';
        $db_obj = new db_class();
        $db_table = $db_obj->getTableNoParams($sql);
        $db_obj->closeDB();
//         showArray($db_table);
        // check to see if this is on the list..
//         showDebug('here1');
        foreach($db_table as $obj) {
            if(strcasecmp($obj['initials'], $initials) == 0 ) { // we don't expect multibyte unicode. 
                return $obj['initials']; // with the correct case 
            }
        }
//         showDebug('here');
        // if it is not exactly on the list, try comparing the first and last letters; TOTDO: test this with cfs
        if(strlen($initials) == 3) {
            $firstInit = substr($initials,0,1);
            $lastInit = substr($initials,2,1);
            $initials = $firstInit . $lastInit;
        }
        // do the same thing for each db initials:
         foreach($db_table as $obj) {
            $temp_initial = $obj['initials'];
            if(strlen($temp_initial) == 3) {
                $firstInit = substr($temp_initial,0,1);
                $lastInit = substr($temp_initial,2,1);
                $temp_initial = $firstInit . $lastInit;
            }
            if(strcasecmp($temp_initial, $initials) == 0 ) { // we don't expect multibyte unicode. 
                return $obj['initials']; // with the correct case 
            }
         }
    }


}