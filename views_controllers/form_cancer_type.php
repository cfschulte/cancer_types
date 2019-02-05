<?php
// form_cancer_type.php -  Tue May 1 12:12:02 CDT 2018
// 

require_once "../essentials.php";
require_once "form_class.php";
require_once "inter_table_access.php";

class form_cancer_type extends form_class {

    protected $largestProductID;

    protected $is_new;
    protected $is_find_form;
    protected $id;
    protected $cancer_type;
    protected $synopsis;
    protected $overview;
    protected $primary_anatomical_site;
    protected $other_anatom_type_text;
    protected $primary_trt_program;

/*************************************************************************/   
    function __construct() {
        parent::__construct('cancer_type_list', 'id','table_cancer_type.php');
        $this->largestProductID = $this->getLargestPrimaryID();   
        $this->is_new = '0';
        $this->is_clone = FALSE;
    }

// be prepared to add <script type="text/javascript" src="/cancer_types/js/reorder.js"></script>, or something like it.



 /*************************************************************************/  
   // 
   function makeForm() {
?>
    <div id="form_wrap">
    <a class="faux_button" id="done_goback_button" href="#">Go Back</a>
<?php  if(! $this->is_find_form AND ! $this->is_new): ?>
     <button class="faux_button" id="clone_this" href="#" <?php if( $this->is_new == 1 ){ echo 'disabled';}  ?> >Clone</button>
        <a class="faux_button" href="/cancer_types/views_controllers/form_cancer_type.php?new=yes">New Cancer Type</a>
     <span class="right"><a class="faux_button" id="delete" href="#">Delete</a></span>
<?php  elseif($this->is_find_form) : ?>
        <span style="float:right">
        <button class="search_button"  id="search_button" name="search_button" >Search</button>
        <b>Match:</b> <select class="semi_faux"  id="conjunction"><option value="OR">Any</option><option value="AND">All</option></select> Fields
        </span>
        <div style="clear:both"></div>
        <p id="search_inform"></p>
<?php  endif ?>

      <div class="clear"></div>
     <br> 
     <br>

     <?php  
    // We don't want to go through the whole generic_update during a search 
     if(! $this->is_find_form ):
     ?>
        <form method="post"  id="generic_edit_form"  class="generic_update">
     <?php  else: ?>
        <form method="post"  id="generic_find_form"  >
     <?php  endif ?>
     
<?php  if(! $this->is_find_form ): ?>
     <input type="hidden" name="id" value="<?php echo $this->id ?>">
     <span class="tag">ID:</span> <b><?php echo $this->id ?></b><br><br>
<?php  endif ?>
     <input type="hidden" name="is_new" value="<?php echo $this->is_new ?>">
     <input type="hidden" name="table" value="cancer_type_list">
     <input type="hidden" name="table_display" value="<?php echo $this->table_display ?>">
     <input type="hidden" name="title_input"  value="cancer_type"> 
     
     
     <span class="tag">Cancer Type:</span>   <input  class="info" type="text" name="cancer_type" size="95" value="<?php echo $this->cancer_type ?>">  <br>
     <span class="tag">Synopsis</span>   <input  class="info" type="text" name="synopsis" size="95" value="<?php echo $this->synopsis ?>">  
     
    <span class="tag_long">Primary Anatomical Site</span> <?php $this->buildGenericSelect('primary_anatomical_site', 'primary_anatomical_site', 'table_index', 'descriptor') ?>
<!-- 
    <span class="leftForm"></span><br>
    <span class="rightForm"></span>
 -->
    <br> <br>
     <span class="tag">Overview:</span><textarea class="form_text" name="overview"><?php echo $this->overview; ?></textarea>
<br><br>
     <input type="submit" id="undo_generic_update" name="undo_generic_update" value="undo" >
   </form>

   </div>
<?php
$this->deleteInfo();
   }

 /*************************************************************************/  
   // set initial variables with 
   function handle_get($indata) {
        
        $this->is_new = 0;  // necessary for an insert
        $this->is_find_form = 0;  
        
        if( array_key_exists('find', $indata) ) {
            $this->is_find_form = 1;
            $this->title = 'Search Products';
        } elseif(array_key_exists('new', $indata)) {
            $this->is_new = '1';
            $this->id = $this->largestProductID + 1;
            
            $this->cancer_type = '';
            $this->synopsis = '';
            $this->product_name = '';
            $this->overview = '';
            $this->primary_anatomical_site = '';
            $this->other_anatom_type_text = '';
            $this->primary_trt_program = '';
        } else {
            $this->id =  $indata['id'];
            $db_table = $this->fetchRecordInfo('id', $this->id);
           
            $this->cancer_type = $db_table['cancer_type'];
            $this->synopsis = $db_table['synopsis'];
            $this->product_name = $db_table['product_name'];
            $this->overview = $db_table['overview'];
            $this->primary_anatomical_site = $db_table['primary_anatomical_site'];
            $this->other_anatom_type_text = $db_table['other_anatom_type_text'];
            $this->primary_trt_program = $db_table['primary_trt_program'];
            
            $this->title = $this->cancer_type;
        }
        $this->primary_key_value = $this->id;
    }

} // end of class definition 



$form_cancer_type = new form_cancer_type();
$form_cancer_type->execute();
