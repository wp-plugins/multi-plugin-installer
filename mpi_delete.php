<?php
$file_path=$_REQUEST['filepath'].$_REQUEST['filename'];
mpi_delfile($file_path);
function mpi_delfile($file_path){
	@unlink($file_path);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}
?>