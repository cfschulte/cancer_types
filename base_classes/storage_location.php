<?php
// storage_location.php -  Tue May 22 13:40:02 CDT 2018
// ORIGINALLY: storage_location.php -  Wednesday Jan. 11, 2017
// This will default to showing a grid that represents a box in
// a Liquid Nitrogen cooler. More LN specific code will go into 
// LN_storage.php. We want to keep this more generic so that we 
// can use if for things like refrigerator or cabinet storage
// also.
// NOW, I WOULD also like to add some things to be able to 
// use generic freezer boxes. 
// THIS IS WHAT TRIPPED ME UP SO BAD WITH THE FIRST GO AT cancer_types 


require_once "../essentials.php";


class storage_location {
    protected $title;
    protected $box_id;   
    protected $db_table = "cell_inventory";
  
  //  description of inside the box 
  // this will be pretty universal - 
    protected $num_cells;
    protected $num_rows = 10;
    protected $num_columns;
    
    
    protected  $userid; 
    protected  $user_privileges;
    
    protected  $temp_data;

    
 /*************************************************************************/   
//  
    function __construct($box_id = 1) {
        $this->box_id = $box_id;
        $this->set_box_info(); 
    }

    
 /*************************************************************************/  
 // run the thing.  
    function execute() {
    
        // check the user credientials
        if( array_key_exists('uid', $_SERVER)){
            $this->userid = $_SERVER['uid'];
        } else {
            $this->userid = 'cfschulte';  // this userid comes from check_login 
        }
        
         $userInfo = getUserInfo( $this->userid );
         if( !empty($userInfo) ){
             $this->user_privileges = $userInfo['privileges'];
         }

?>
<!DOCTYPE html>
<html lang="en"> 
         <?php $this->header(); ?>
         <?php $this->body(); ?>
</html>
<?php    
    } /*** end execute **/

    
 /*************************************************************************/  
 // The header  -- 
 // NOTE: instead of hard-coding these, we should have a way of loading them, thus
 // letting the child classes add additional styles and javascripts. 
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

//     <script type="text/javascript" src="/cancer_types/js/trackpages.js"></script>  

?>
    <script type="text/javascript" src="/cancer_types/js/jquery.js"></script>
    <script type="text/javascript" src="/cancer_types/js/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/cancer_types/js/logout.js"></script>
    <script type="text/javascript" src="/cancer_types/js/header_action.js"></script>

<?php 
}


    
 /*************************************************************************/  
 // The load the javascripts -- it can be called from the child classes 
 // and then augmented.
    function css_list() {
?>
    <link rel="Stylesheet" type="text/css" href="/cancer_types/js/jquery-ui/jquery-ui.min.css" />
<link rel="Stylesheet" type="text/css" href="/cancer_types/css/style.css" />
<?php 
}

    
 /*************************************************************************/  
   // The body
   function body() {
?>
<body>
<?php
    // start with the page header
    $this->page_header(); 
    
    // then do the contents 
?> 
<div id="wrapper_storage">
  <div id="display">
    <?php 

 $this->create_table();
    
    ?>
  </div>
</div>

<?php 
   }

 /*************************************************************************/  
   // page_header  -- override the top margin for the storage_location
   function page_header() {
?>
<div class="page_header" style="margin-top:-7em;">
<div class="in_header">
<a class="home_button" href="/cancer_types/" title="home"><svg width="2em" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40.18 59.04"><defs><style>.cls-1{fill:#f1f2c0;}.cls-2,.cls-3{fill:none;stroke-miterlimit:10;}.cls-2{stroke:#f1f2c0;stroke-width:0.75px;}.cls-3{stroke:#000;stroke-width:3px;}</style></defs><title>Home</title><polygon class="cls-1" points="2.53 54.5 20.09 22.42 37.65 54.5 2.53 54.5"/><path d="M37.09,36.54,52.12,64H22.06l15-27.46m0-6.25L17,67H57.18L37.09,30.29Z" transform="translate(-17 -11)"/><rect class="cls-1" x="16.5" y="4.5" width="7" height="23"/><path d="M39,17V37H35V17h4m3-3H32V40H42V14Z" transform="translate(-17 -11)"/><ellipse class="cls-1" cx="20" cy="3.5" rx="6" ry="1.5"/><path d="M37,13c-3.31,0-6,.67-6,1.5S33.69,16,37,16s6-.67,6-1.5S40.31,13,37,13Z" transform="translate(-17 -11)"/><ellipse class="cls-1" cx="20" cy="2" rx="3.74" ry="0.5"/><path d="M37,11c-3.31,0-6,.9-6,2s2.69,2,6,2,6-.9,6-2-2.69-2-6-2Z" transform="translate(-17 -11)"/><path class="cls-1" d="M37,68.5c-8.74,0-14.51-.79-17.16-1.5,2.65-.71,8.42-1.5,17.16-1.5s14.51.79,17.16,1.5C51.51,67.71,45.74,68.5,37,68.5Z" transform="translate(-17 -11)"/><path d="M37,64c-11,0-20,1.34-20,3s9,3,20,3,20-1.34,20-3-9-3-20-3Z" transform="translate(-17 -11)"/><rect class="cls-1" x="14" y="2" width="12" height="1.5"/><polygon points="26 2 14 2 14 3.5 26 3.5 26 2 26 2"/><path class="cls-1" d="M38.79,41.81c.08-1.26.2-2.54.22-3.8a2.62,2.62,0,0,0-1.14-2.44c-1.58-1.11-3.09,1.49-1.51,2.59-.85-.59-.15-.51-.35-.15a4.49,4.49,0,0,0-.07,1.33l-.15,2.47c-.11,1.93,2.89,1.93,3,0Z" transform="translate(-17 -11)"/><path class="cls-1" d="M37.06,35.31a2.49,2.49,0,0,0-1.16.35A1.52,1.52,0,0,0,35.16,37c.05.83,0,1.63,0,2.45a1.79,1.79,0,0,0,.53,1.34,1.89,1.89,0,0,0,.77.43l.39,0c.26,0,.17,0-.26-.11a1.52,1.52,0,0,0,2.2-.9,3.21,3.21,0,0,0-.76-3,1.5,1.5,0,0,0-2.12,0,1.53,1.53,0,0,0,0,2.12c-.09-.11,0,0,0,0l2.2-.89a3,3,0,0,0-.59-.22l-.4-.06c-.19,0-.18,0,0,0l.66.38-.07-.08.44,1.06c-.08-.87.05-1.77,0-2.65l-.74,1.3-.36.06a1.55,1.55,0,0,0,1.5-1.5,1.5,1.5,0,0,0-1.5-1.5Z" transform="translate(-17 -11)"/><path class="cls-1" d="M38.1,36.73l0-.15a1.5,1.5,0,0,0-2.95.4v.36a1.5,1.5,0,0,0,3,0V37l-3,.4,0,.14a1.51,1.51,0,0,0,1.84,1,1.53,1.53,0,0,0,1.05-1.84Z" transform="translate(-17 -11)"/><path class="cls-1" d="M36.62,38.84a1.5,1.5,0,0,0,0-3,1.5,1.5,0,0,0,0,3Z" transform="translate(-17 -11)"/><path class="cls-1" d="M38.12,37.38v-.22a1.5,1.5,0,0,0-2.94-.39l0,.25H38l0,.1h-3a3,3,0,0,0,.53,2.17l-.38-.66c0-.27,0-.27,0,0V39l0,.8a1.5,1.5,0,0,0,3,0c0-1,.19-1.92-.51-2.76l.39.67c0,.23,0-.09,0-.18v-.45a1.5,1.5,0,0,0-3,0,1.86,1.86,0,0,0,.07.7c.34,1.38,2.61,1.48,2.89,0l0-.26-3-.4v.22a1.53,1.53,0,0,0,1.5,1.5,1.51,1.51,0,0,0,1.5-1.5Z" transform="translate(-17 -11)"/><line class="cls-2" x1="18.4" y1="25.98" x2="18.4" y2="29.95"/><line class="cls-2" x1="21.53" y1="25.81" x2="21.53" y2="29.78"/><line class="cls-2" x1="20.53" y1="25.81" x2="20.53" y2="29.78"/><line class="cls-2" x1="21.64" y1="25.81" x2="21.64" y2="29.78"/><ellipse class="cls-2" cx="20.1" cy="53.08" rx="14.68" ry="0.69"/><ellipse class="cls-1" cx="20.2" cy="53.14" rx="12.37" ry="0.13"/><path class="cls-1" d="M37.2,64c-6.83,0-12.37.06-12.37.13s5.54.13,12.37.13,12.36,0,12.36-.13S44,64,37.2,64Z" transform="translate(-17 -11)"/><ellipse class="cls-1" cx="20.2" cy="53.14" rx="12.37" ry="0.13"/><path class="cls-1" d="M37.2,64c-6.83,0-12.37.06-12.37.13s5.54.13,12.37.13,12.36,0,12.36-.13S44,64,37.2,64Z" transform="translate(-17 -11)"/><ellipse class="cls-1" cx="20.2" cy="53.05" rx="12.37" ry="0.13"/><path class="cls-1" d="M37.2,63.92c-6.83,0-12.37.06-12.37.13s5.54.13,12.37.13,12.36-.06,12.36-.13S44,63.92,37.2,63.92Z" transform="translate(-17 -11)"/><ellipse class="cls-1" cx="20.21" cy="53.32" rx="12.37" ry="0.13"/><path class="cls-1" d="M37.21,64.19c-6.83,0-12.37,0-12.37.13s5.54.13,12.37.13,12.37-.06,12.37-.13-5.54-.13-12.37-.13Z" transform="translate(-17 -11)"/><ellipse class="cls-1" cx="20.21" cy="53.08" rx="12.37" ry="0.13"/><path class="cls-1" d="M37.21,64c-6.83,0-12.37.06-12.37.13s5.54.13,12.37.13,12.37-.06,12.37-.13S44,64,37.21,64Z" transform="translate(-17 -11)"/><path class="cls-1" d="M37.09,68.33c-9,0-14.8-.85-17.37-1.58,2.57-.74,8.41-1.59,17.37-1.59s14.79.85,17.36,1.59C51.88,67.48,46,68.33,37.09,68.33Z" transform="translate(-17 -11)"/><path d="M37.09,66.66c1.66,0,3.21,0,4.66.09-1.45,0-3,.08-4.66.08s-3.22,0-4.67-.08c1.45-.06,3-.09,4.67-.09m0-3C26,63.66,17,65,17,66.75s9,3.08,20.08,3.08,20.07-1.38,20.07-3.08-9-3.09-20.07-3.09Z" transform="translate(-17 -11)"/><path class="cls-1" d="M37.09,68.54c-9,0-14.8-.85-17.37-1.58,2.57-.74,8.41-1.59,17.37-1.59s14.79.85,17.36,1.59C51.88,67.69,46,68.54,37.09,68.54Z" transform="translate(-17 -11)"/><path d="M37.09,66.87c1.66,0,3.21,0,4.66.09-1.45.05-3,.08-4.66.08s-3.22,0-4.67-.08c1.45-.06,3-.09,4.67-.09m0-3C26,63.87,17,65.25,17,67S26,70,37.09,70,57.16,68.66,57.16,67s-9-3.09-20.07-3.09Z" transform="translate(-17 -11)"/><line class="cls-3" x1="15.58" y1="55.88" x2="24.33" y2="55.96"/></svg></a>
 <h1><?php echo $this->title; ?></h1>

<?php 
    $this->additionalHeaderStuff();
?>

</div>
<?php   

   }


 /*************************************************************************/  
   // additionalHeaderStuff - form specific buttons 
   function additionalHeaderStuff() {
        return;
   }


 /*************************************************************************/  
   // create_table
   function create_table() {
       
       echo  '<table id="grid" class="grid_table">' . "\n"; // generic default can be overridden 
       $this->table_body();
       echo  "</table>\n";
   }


 /*************************************************************************/  
   // table_body -
   function table_body() {
        for($i=0; $i<$this->num_rows; $i++) {
            $this->table_row($i);
        }
    }


 /*************************************************************************/  
   // table_row  --  NOTE: $col_num = $j+1 because the for loop starts at zero 
    function table_row($row_num) {
        echo '<tr>';
        for($j=0; $j<$this->num_columns; $j++) {
            $this->table_cell($row_num, $j+1);
        }
        echo '</tr>' . "\n";
    }

 /*************************************************************************/  
   // table_cell
    function table_cell($row_num, $col_num) {
        $cell_num = $row_num * $this->num_columns + $col_num;
        
        echo "<td><div class=\"content\">$cell_num</div></td>";
    }


 /*************************************************************************/  
   // get_sql  -- instead of making a whole shit-ton of cases in basic_table,
   // this should be OVERRIDDEN by the child classes.
   function set_box_info() {
        $this->title = 'generic grid';
        $this->num_cells = 100;
        $this->num_rows = 10;
        $this->num_columns = 10;
    }


}


