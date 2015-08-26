<?php
require_once('../../../wp-config.php');

global $current_user;
get_currentuserinfo();

if ($current_user->user_level < 9 ) {
	wp_die('You do not have permission to perform this action');
}

$mpi_upload_dir = MPIUPLOADDIR_PATH.'/mpi_logs/';
$file_path      = $mpi_upload_dir.$_REQUEST['filename'];
mpi_delfile($file_path);
function mpi_delfile($file_path){
	@unlink($file_path);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}
?>