<?php
//admin menu hook
add_action('admin_menu', 'mpi_add_menu');

//add menu page 
function mpi_add_menu() {
	add_menu_page('Multi Plugin Installer Page', 'Multi Plugin Installer', 'administrator','mpinstaller', 'mpi_appearance', plugins_url( 'images/icon.png' , __FILE__ ));
}

function mpi_appearance(){
//plugins admin interface
require_once('mpi_appereance.php');
}
?>