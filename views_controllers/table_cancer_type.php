<?php
// products.php -  Tue May 1 10:56:04 CDT 2018
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
        parent::__construct('Cancer Types', 'cancer_type_list', 'form_cancer_type.php',
          array("id" => "ID",
                "cancer_type" => "Cancer Type",
                "synopsis" => "Synopsis",
                "primary_anatomical_site" => "Primary Anatomical Site"
               )
        );
    }

} // end class



$table_cancer_type = new table_cancer_type();
$table_cancer_type->execute();
