<?php
// cancer_types.php -  Tue May 1 10:56:04 CDT 2018
// 

require_once "../essentials.php";
require_once "table_class.php";

class table_cancer_type extends table_class {
    protected $is_new;
    protected $id;
    protected $synopsis;
    protected $overview;
    protected $primary_anatomical_site;
    protected $other_anatom_type_text;
    protected $primary_trt_program;

 /*************************************************************************/  
 // constructor
    function __construct() {
        parent::__construct('Cancer Types', 'cancer_type', 'form_cancer_type.php',
          array("id" => "ID",
                "cancer_type" => "Cancer Type",
                "synopsis" => "Synopsis",
                "primary_anatomical_site" => "Primary Anatomical Site"
               )
        );
    }


 /*************************************************************************/  
   // table_row  -- This is where most of the overriding should happen. 
    function table_row($row) {
        $id = $row['id'];
        $anatomical_site = $row['other_anatom_type_text'];
        $other_anatom_type_text = $row['other_anatom_type_text'];
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
   // get_sql  -- Meant to be overridden by child classes
   function get_sql() {
        $sql  = 'SELECT ct.id, ct.cancer_type, ct.synopsis, pas.descriptor as anatomical_site, ct.other_anatom_type_text ';
        $sql .= 'FROM cancer_type AS ct ';
        $sql .= 'LEFT JOIN primary_anatomical_site AS pas ';
        $sql .= 'ON ct.primary_anatomical_site=pas.table_index ';
        
       return $sql;
    }


} // end class



$table_cancer_type = new table_cancer_type();
$table_cancer_type->execute();
