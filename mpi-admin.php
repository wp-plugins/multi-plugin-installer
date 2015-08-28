<?php
//admin menu hook
add_action('admin_menu', 'mpi_add_menu');
add_action('admin_init', 'mpi_download_backup');
add_action('admin_init', 'mpi_delete_backup');
require_once('mpi-Class.php');

//add menu page 
function mpi_add_menu() {
	add_menu_page('Multi Plugin Installer Page', 'Multi Plugin Installer', 'administrator','mpinstaller', 'mpi_appearance', plugins_url( 'images/icon.png' , __FILE__ ));
}

function mpi_appearance(){
//plugins admin interface
require_once('mpi_appereance.php');
}
function mpi_download_backup(){
 $download_flag = $_GET['dn'];
if(isset($download_flag) && trim($download_flag) && $download_flag ==1){
	$mpiObj = new mpinstaller();
	$mpiObj->mpi_download();
}
}
function mpi_delete_backup(){
 $delete_flag = $_GET['dl'];
if(isset($delete_flag) && trim($delete_flag) && $delete_flag ==1){
	$mpiObj = new mpinstaller();
	$mpiObj->mpi_delete();
}
}



?>