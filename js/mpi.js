/*-- MPI Jquery Script
-------------------------------------------------------*/
jQuery(document).ready(function(){
	jQuery('#mpiblock .handlediv').click(function(){jQuery(this).parent().find('.inside').slideToggle("slow");});
	jQuery('#mpi-collapse').click(function(){jQuery('.inside').slideUp("slow");});
	jQuery('#mpi-expand').click(function(){jQuery('.inside').slideDown("slow");});

	var thspar = jQuery('#mpiblock a').attr('target','_parent');
	jQuery(thspar).parent('p').not('.not-found').hide();
});

function valid_extension(){
	var extension = new Array();
	var fieldvalue = document.form_expImp.mpi_expfileUp.value;
	extension[0] = ".mpi";
	var thisext = fieldvalue.substr(fieldvalue.lastIndexOf('.'));
	for(var i = 0; i < extension.length; i++) {
		if(thisext == extension[i]) { 
		return true; 
		}
	}
	alert("Please upload vaild .mpi extension file.");
	return false;
}

function valid_zipfile(mpi_eleId){
	var	extension = ".zip";
	var inp = document.getElementById(mpi_eleId);
    var count = inp.files.length;

    for(var a=0; a < count; a++){
		var fieldvalue = inp.files.item(a).name;
		var thisext = fieldvalue.substr(fieldvalue.lastIndexOf('.'));
		if(thisext == extension){ 
		return true; 
		}
    }

	alert("Please upload vaild .zip extension file.");
	return false;
}

function mpi_delcfirm(){   
	var mpi_agree = confirm('Are you sure you want to delete file.');
	return mpi_agree; 
}