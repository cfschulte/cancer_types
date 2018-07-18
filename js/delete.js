///////////////////////////////////////
// delete.js Mon Mar  5 11:56:46 CST 2018
//  - a rewrite from cancer_types and cancer_types 
// Do we even want to use this?  - This is the generic delete/undelete 
// interface for entire rows in tables.


//      <input type="hidden" id="primary_key"  name="primary_key" value="id">
//      <input type="hidden" id="primary_key_value" name="primary_key_value" value="<?php echo $this->id ?>">
//      <input type="hidden" id="delete_from_table" name="delete_from_table" value="biochemical_index">
//      <input type="hidden" id="return_address" name="return_address" value="/cancer_types/tables/biochem_index_table.php">


////////////////////////////////   
// May 30, 2018 - trying something new. We will save the current record as a text blob. 
// That way, we only need one delete backup table.
// DELETE
$(document).ready(function(){
    $("#delete").click(function(event){
//         if( confirm('Are you sure you want to delete this entry?') ) { // maybe get rid of this..
            var db_data = {};
            db_data['table'] = $("#delete_from_table").val();
            db_data['primary_key'] = $("#primary_key").val();
            db_data['primary_key_val'] = $("#primary_key_value").val();
//             if(db_data['table'] == 'orders') {
//                 db_data['primary_key'] = 'order_number';
//                 db_data['primary_key_val'] = $("#order_number").val();
//             } else {
//                 db_data['primary_key'] = 'id';
//                 db_data['primary_key_val'] = $("input[name=id]").val();
//             }
            var return_address = $("#return_address").val();
            
            
            console.log({'db_data': db_data, 'return_address': return_address});
            $.ajax({
//                 url: "/cancer_types/ajax_parser.php",
                data: {
                    id: 'delete_entry',
                    data: db_data
                },
                method: "POST",
                dataType: "json",
                success: function(json){
                    console.log( json );
//                     location.replace(return_address);
                },
//                 error: function( xhr, status, errorThrown ) {
//                     alert( "Sorry, there was a problem!" );
//                     console.log( "Error: " + errorThrown );
//                     console.log( "Status: " + status );
//                     console.dir( xhr );
//                 }
                
            });
//         }
    });
});


////////////////////////////////   
// UNDO DELETE 
$(document).ready(function(){
    $("#undo_delete").on('click',  function(event){
        console.log('undo delete');
        
        if( ! $("#undo_delete").hasClass("a_button") ){ return ;}
        var table_name = $("#table_name").val();
        
        $.ajax({
            url: "/cancer_types/ajax_parser.php",
            data: {
                id:  'undo_entry_delete',
                data: table_name
            }, 
            method: "POST",
            dataType: "json",
            success: function(json){
                console.log(json);
                location.reload();
               // refresh the display 
//                 var table_display = document.getElementById("display");
//                 var content = table_display.innerHTML;
//                 table_display.innerHTML = content;
                
               // check whether or not the undo button should be updated  
//                 if(json.backup_count < 1){
//                     $("#undo_delete").removeClass("a_button");
//                     $("#undo_delete").addClass("a_button_disabled");
//                 }
            },
            error: function( xhr, status, errorThrown ) {
                alert( "Sorry, there was a problem!" );
                console.log( "Error: " + errorThrown );
                console.log( "Status: " + status );
                console.dir( xhr );
            }
        });
    });
});


////////////////////////////////   
// DELTE BUTTON ACTIVE?
$(document).ready(function(){
    var table_name = $("#table_name").val();
    $.ajax({
        url: "/cancer_types/ajax_parser.php",
        data: {
            id:  'activate_undo_entry_delete',
            data: table_name
        },
        method: "POST",
        dataType: "json",
        success: function(json){
            
            if(json.num_deletes < 1){
                $("#undo_delete").removeClass("a_button");
                $("#undo_delete").addClass("a_button_disabled");
//  
//                 $("#undo_goes_here").html('<button class="head_action a_button_disabled" id="undo_delete" name="undo_delete" >Undo Delete</button>')
//                 $("#undo_goes_here").html('<a class="head_action a_button_disabled" id="undo_delete" href="">Undo Delete</a>')
            } else {
                $("#undo_delete").removeClass("a_button_disabled");
                $("#undo_delete").addClass("a_button");
//                 $("#undo_goes_here").html('<button class="head_action a_button" id="undo_delete" name="undo_delete" >Undo Delete</button>')
//                 $("#undo_goes_here").html('<a class="head_action a_button" id="undo_delete" href="">Undo Delete</a>')
            }
        },
        error: function( xhr, status, errorThrown ) {
            alert( "Sorry, there was a problem!" );
            console.log( "Error: " + errorThrown );
            console.log( "Status: " + status );
            console.dir( xhr );
        }
    });
});



////////////////////////////////   
// TODO: make the dialog a little nicer 
// $(document).ready(function() {
//     $("#delete").click(function(event) {
//         if( confirm('Are you sure you want to delete this entry?') ) {
//             var formData = $("#delete_form").serializeArray(); 
// //             console.log(formData);
//                         
//                 $.ajax({
//                     url: "/cancer_types/non_class_includes/delete.php",
//                     data: {
//                         id: 'vendors',  // do we really need the id?
//                         data: formData
//                     },
//                     type: "POST",
//                     dataType : "json",
// 
//                     success: function( json ) {
//                         var myHttp   = 'http://localhost';
// //                         var myHttp   = 'https://dho-web02.humonc.wisc.edu';
//                         var return_address = json.return_address;
//                         console.log('return_address: ' + return_address);   
//                         window.location.href = myHttp + return_address;    
//                     },
// 
//                     error: function( xhr, status, errorThrown ) {
//                         alert( "Sorry, there was a problem!" );
//                         console.log( "Error: " + errorThrown );
//                         console.log( "Status: " + status );
//                         console.dir( xhr );
//                     
//                     },
// 
//                 });
//             event.preventDefault();
//        }
//    });
// });


