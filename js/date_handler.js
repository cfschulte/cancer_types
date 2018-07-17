/****************

date_handler.js - 2017-01-05  This requires jquery-ui.js and jquery-ui.css

*****************/


/////////////////////////////////////////////////////////////////////////////
////////////////////////////////   
// give us a date-picker    - for biochem_ind_edit.php
$(document).ready(function() {
    $(".date").datepicker({
//         dateFormat: "yy-mm-dd",  // change the format!
        dateFormat: "mm/dd/yy",  // change the format!
        yearRange: '1990:2030',
        changeYear: true,
        changeMonth: true
    });
});

// basic time things. 
function today() {
	var d = new Date();

	var month = d.getMonth()+1;
	var day = d.getDate();
	var year = d.getFullYear();

	var output = year + '-' +
		(month<10 ? '0' : '') + month + '-' +
		(day<10 ? '0' : '') + day;
	return output;
}

///////////////////
// Takes a date format like, 2015-03-15 and makes it 
// into March 15, 2015 
function americanDate(inDate) {
	var m_names = new Array("filler", "Jan", "Feb", "Mar", 
	"Apr", "May", "Jun", "Jul", "Aug", "Sep", 
	"Oct", "Nov", "Dec");
	
	inDate = inDate.replace("-0", "-");
	var ymd = inDate.split("-");
	
	var y = ymd[0];
	var m = ymd[1];
	var mo = m_names[m];
	var d = ymd[2];
	
	return  mo + ' ' + d + ', ' + y;
}

