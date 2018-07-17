/************

forms.js - January 19, 2017
General jquery that most forms should have - I would like to 
replace reorder.js with this and a form-specific js file.
This is from build_lab_inventory. Needs lots of changing.

*************/

////////////////////////////////
// PRINT 
$(document).ready(function() {
    $("#print_it").click(function(event){
        window.print();
        event.preventDefault();
    });
});


////////////////////////////////
// INIT AJAX  with all the stuff that every ajax call has 
$(document).ready(function(){
    $.ajaxSetup({
        url:'/build_lab_inventory/ajax_parser.php',
        method: 'POST',
        dataType: 'json',
        error: function(xhr, status, errorThrown) {
           alert( "There has been a problem." );
            console.log( "Error: " + errorThrown );
            console.log( "Status: " + status );
            console.dir( xhr );
        }
    });
});


////////////////////////////////
// FIND  
$(document).ready(function() {
    $("#search_button").click(function(event){
        var db_data = {};
        db_data['form_data'] = $("#generic_find_form").serializeArray();
        db_data['table_display'] = $("input[name=table_display]").val();
        db_data['edit_page'] = window.location.pathname;
        db_data['conjunction'] = $("#conjunction").val();
        console.log(db_data);
        
        $.ajax({
            data: {
                id: 'search',
                data: db_data
            },
            success: function(json){
                console.log(json);
                if(json.hasOwnProperty('inform')) {
                    $("#search_inform").html(json.inform);
                } else if(json.hasOwnProperty('redirect')){
                    location.replace(json.redirect);
                }
            }
        });
    });
});


////////////////////////////////
// CLONING A RECORD. I think the best way to do this with the automatic
// input save. Get the original data, clone it in the database, then
// send the user to a new page. 
$(document).ready(function(){
    $("#clone_this").click(function(evt){
        var db_data = {};
        db_data['id'] = $('form.generic_update input[name=id]').val();
        db_data['table'] = $('form.generic_update input[name=table]').val();
        db_data['title_input'] = $('form.generic_update input[name=title_input]').val();
        
        $.ajax({
            data: {
                id: 'clone',
                data: db_data
            },
            success: function(json){
//                 console.log(json);
                
               // go to the new site
                var new_id = json.new_id;
                var old_id = json.old_id;
                var this_url = window.location.href; 
                var new_url = this_url.replace(old_id, new_id);
                
               // make sure the back button works
                evt.preventDefault();
                var targetUrl = $(this).attr('href'), targetTitle = $(this).attr('title');
                window.history.pushState({url: "" + targetUrl + ""}, targetTitle, targetUrl);
                                
                location.replace(new_url);
            },
        });
    });
});


////////////////////////////////
// Check whether there is anything on this page to undo. ENABLE/DISABLE undo button
$(document).ready(function() {
    var db_data = {};
    db_data['id'] = $('form.generic_update input[name=id]').val();
    db_data['table'] = $('form.generic_update input[name=table]').val();
    db_data['is_new'] = $('form.generic_update input[name=is_new]').val();
    
    if(db_data['is_new'] == 0){
        // do an ajax check
        $.ajax({
            data: {
                id: 'form_needs_undo',
                data: db_data
            },
            success: function(json){
                if(json > 0) {
                    $("#undo_generic_update").prop('disabled', false); 
                } else {
                    $("#undo_generic_update").prop('disabled', true); 
                }
            }
        });
     } else { 
        $("#undo_generic_update").prop('disabled', true); 
     }
});

////////////////////////////////////////
// Set the active field's value for an undo 
var previousGenericVal;
$(document).ready(function(){
//     $("form.generic_update input[type=text], form.generic_update select, form.generic_update textarea, form.generic_update input[type=radio]").on('focusin', function(){
    $("form.generic_update input[type=text], form.generic_update select, form.generic_update textarea").on('focusin', function(){
        previousGenericVal = $(this).val();
//         console.log(previousGenericVal);
    });
});



////////////////////////////////////////
// UPDATE the database whenever a field is updated in an existing record.
// This originally came from humonc_students 
$(document).ready(function() {
//     $("input.generic[type=text], select.generic, textarea.generic").change(function() {
    $("form.generic_update input[type=text], form.generic_update input[type=radio], form.generic_update select, form.generic_update textarea").change(function() {
       
        var db_data = {};
        db_data['previousGenericVal'] = previousGenericVal;
        
        
        if($(this).is('input[type=text]')) {
            db_data['type'] = 'text';
        } else if($(this).is('input[type=radio]')) {  // RADIO 
            db_data['type'] = 'radio';
          // WE CAN'T ACCESS THE PREVIOUS value directly so we save it in a hidden field.
           // First, get the name of the input holding the previous value.
           var name_prev_val_input = $(this).attr('name');
           name_prev_val_input += '_prev';
           // then set the data to that
            db_data['previousGenericVal'] = $("form.generic_update input[name=" + name_prev_val_input +"]").val();
           // and then set the hidden variable to this current one.
            $("form.generic_update input[name=" + name_prev_val_input +"]").val( $(this).val() );
        } else if($(this).is('select')) {
            db_data['type'] = 'select';
        } else if($(this).is('textarea')) {
            db_data['type'] = 'textarea';
        }
//         console.log('db_data type: ' + db_data['type']);
        db_data['name'] = $(this).attr('name');
        db_data['value'] = $(this).val();
        db_data['id'] = $('form.generic_update input[name=id]').val();
        db_data['table'] = $('form.generic_update input[name=table]').val();
        db_data['title_input'] = $('form.generic_update input[name=title_input]').val();
//         db_data['userid'] = $(this).siblings("input[name=userid]").val();
        db_data['is_new'] = $("form.generic_update input[name=is_new]").val();

       console.log( db_data);
        
        
        // New records have too many table dependent things going on 
        // that they must be handled with a submit/save button.
        if(db_data['is_new'] == 0){
            $.ajax({
                data: {
                    id: 'key_value',
                    data: db_data
                },
                success: function( json ) {
                    console.log(json);
                    // enable the undo button
                     $("#undo_generic_update").prop('disabled', false); 
                    // do we need to update the title?
                     if(db_data['name'] == db_data['title_input']) {
                        $('title').html(db_data['value']);
                        $('h1').html(db_data['value']);
                     }
                },
            });
        } else {  // create a new record!
            // get the address to update later.
            console.log('NO');
           
                $.ajax({
                    data: {
                        id: 'new_record',
                        data: db_data
                    },
                    success: function( json ) {
    //                     console.log(json);
                     
                        // Update the title if need be
                         if(db_data['name'] == db_data['title_input']) {
    //                         console.log('update the title to ' + db_data['value']);
                            $('title').html(db_data['value']);
                            $('h1').html(db_data['value']);
                         }
                     
                        var this_url = window.location.href; 
                        var id = db_data['id'];
                        var new_url = this_url.replace('new=yes', 'id=' + id);
                    
                        location.replace(new_url);
                    },
            });
        }
    }); 
});



////////////////////////////////////////
// UNDO GENERIC
$(document).ready(function() {
    $("#undo_generic_update").click(function(event){
        // which table and id are to be undone?
        var db_data = {};
        db_data['id'] = $('form.generic_update input[name=id]').val();
        db_data['table'] = $('form.generic_update input[name=table]').val();
        
        
        
        $.ajax({
                data: {
                    id:  'undo_last_generic',
                    data: db_data
                },

                success: function( json ) {
                    console.log( json );
                    var input_accessor ;
                  // reset the result 
                  if( json.form_type == 'text' ){
                      input_accessor = 'form.generic_update input[name=' + json.column + ']';
                      $(input_accessor).val(json.value);
                  } else if( json.form_type == 'textarea' ){
                      console.log('in the text area')
                      input_accessor = 'form.generic_update textarea[name=' + json.column + ']';
                     $(input_accessor).val(json.value);
                     
                  } else if( json.form_type == 'select' ){
                      input_accessor = 'form.generic_update select[name=' + json.column + ']';
                      $(input_accessor).val(json.value); 
                  } else if( json.form_type == 'radio' ){
                      var radio_group = $('form.generic_update input:radio[name=' + json.column + ']');
                      // set true for radio_group of the correct value to true. The others will
                      // be set to false by the group
                       radio_group.filter('[value='+ json.value +']').prop('checked', true);
                  }
                    
                  // should the undo button be enabled?
                    if(json.undo_size < 1) {
                        $("#undo_generic_update").prop('disabled', true); 
                    }   
                },
        });
        event.preventDefault();
    });
});

/******************
*
* ucFirstChar  - I took this off stack overload for something I was doing
*                for humonc_students. It might be handy some day.
*
******************/
function ucFirstChar(string) 
{
    if(!string || string.len === 0){
        return '';
    }
        
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

