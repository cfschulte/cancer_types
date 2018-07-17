/************

header_action.js - Wed Feb 21 10:04:06 CST 2018
Button actions that tend to be shared by forms, tables, and
miscellaneous handlers. 

*************/

////////////////////////////////
// Get a menu going 
/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
$(document).ready(function(){
    $('.dropbtn').click(function(evt){
        $("#myDropdown").toggleClass("show")
    });
});

////////////////////////////////
$(document).ready(function() {
    $(window).click(function(evt){
        if (!evt.target.matches('.dropbtn')) {

          var dropdowns = document.getElementsByClassName("dropdown-content");
          var i;
          for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
              openDropdown.classList.remove('show');
            }
          }
        }
    });
});


////////////////////////////////
$(document).ready(function() {
    $("#done_goback_button").click(function(event){
        console.log('closing window');
//         window.history.go(-1);
        window.history.back();
        return true;
    });
});


////////////////////////////////
$(document).ready(function() {
    $("#done_close_button").click(function(event){
        console.log('closing window');
        window.close();
        return true;
    });
});

