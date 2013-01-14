<?php
class mpi_pluginsbackup
{
	private function mpi_recurse_zip($src,&$zip,$path) {
		$dir = opendir($src);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->mpi_recurse_zip($src . '/' . $file,$zip,$path);
				}
				else {
					$zip->addFile($src . '/' . $file,substr($src . '/' . $file,$path));
				}
			}
		}
		closedir($dir);
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}

	public function mpi_compress($src,$dst=''){
		
		if(substr($src,-1)==='/'){$src=substr($src,0,-1);}
		if(substr($dst,-1)==='/'){$dst=substr($dst,0,-1);}
		$path=strlen(dirname($src).'/');
		$filename='mpipluginsbackup_'.time().'.zip';
		$dst=empty($dst)? $filename : $dst.'/'.$filename;
		@unlink($dst);
		$zip = new ZipArchive;
		$res = $zip->open($dst, ZipArchive::CREATE);
		if($res !== TRUE){
				echo 'Error: Unable to create zip file';
				exit;}
		if(is_file($src)){$zip->addFile(substr($src,$path));}
		else{
				if(!is_dir($src)){
					 $zip->close();
					 @unlink($dst);
					 echo 'Error: File not found';
					 exit;}
		$this->mpi_recurse_zip($src,$zip,$path);}
		$zip->close();
		return $dst;
	}
	
}
	$mpi_obj = new mpi_pluginsbackup();
	$mpi_src = '../../plugins/';
	$mpi_dst = '../../uploads/mpi_logs';
	$mpi_obj->mpi_compress($mpi_src,$mpi_dst);
?>