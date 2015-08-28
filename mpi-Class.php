<?php
class mpinstaller
{
    var $plugin_url;
    var $key;
    
    function __construct(){
        $this->plugin_url = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));
        $this->key = 'mpinstaller';
    }
	  
	// download the plugin handler form the wordpress org
    function mpi_plugin_handle_download($plugin_name,$package,$mpi_action,$whform)
	{
        global $wp_version;
        
        if(version_compare($wp_version, '3.0', '<')){
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';  
            $upgrader = new Plugin_Upgrader(); 
            $upgrader->install($package);
            
        	if ($upgrader->plugin_info()){
        		echo '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $upgrader->plugin_info(), 'activate-plugin_' . $plugin_file) . '" title="' . esc_attr__('Activate this plugin') . '" target="_parent">' . __('Activate Plugin') . '</a>';
        	}
        }			
        else{
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('type', 'title', 'nonce', 'url') ) ); 
			$res=$upgrader->install($package);  


			//remove temp files
			if($whform == "upload_locFiles"){
				@unlink($package);
			}
			
			if (!$upgrader->plugin_info()){
				echo $res;
			}
			
			elseif($mpi_action =="activate"){
				$mpiplugins = get_option('active_plugins');
				if($mpiplugins){
					$puginsToActiv = array($upgrader->plugin_info());
					foreach ($puginsToActiv as $mpiplugin){
						if (!in_array($mpiplugin, $mpiplugins)) {
							 array_push($mpiplugins,$mpiplugin);
							 update_option('active_plugins',$mpiplugins);
						}
					}
				}
				_e('<b class="mpi_act">Plugin activated successfully.</b><br/>','mpi');
			}
        }
    }
	
	// get plugin information	
    function mpi_get_plugin($plugin_name){
        $name = $plugin_name;
        $plugin = $plugin_name;
        $description = '';
        $author = '';
        $version = '0.1';
        $plugin_file = "$name.php";
        
        return array(
        	'Name' => $name, 
        	'Title' => $plugin, 
        	'Description' => $description, 
        	'Author' => $author, 
        	'Version' => $version
        );
    }
	
	function mpi_create_file($plugins_arr,$mpi_cfilenm)
	{
		if($plugins_arr){
				$mpi_filetxt = "";
			foreach($plugins_arr as $mpi_plugin){
				$mpi_filetxt .= $mpi_plugin.",";
			}

			$mpi_filetxt = substr($mpi_filetxt, 0, -1);
			
			if($mpi_cfilenm){
				$mpi_flnm = $mpi_cfilenm.'_'.time().".mpi";
				$mpi_file = MPIUPLOADDIR_PATH.'/mpi_logs/files/'.$mpi_flnm;
			}
			else{
				$mpi_flnm = "mpi_".time().".mpi";
				$mpi_file = MPIUPLOADDIR_PATH.'/mpi_logs/files/'.$mpi_flnm;
			}
			
			$mpi_handle = fopen($mpi_file, 'w+') or die('Cannot open file:  '.$mpi_file);
			fwrite($mpi_handle, $mpi_filetxt);
			fclose($mpi_handle);
		}
	}
    
    function mpi_get_packages($plugins_arr,$mpi_action,$mpi_cfilenm,$whform)
	{
        global $wp_version;
        
        if (!function_exists('fsockopen')) return false;
        
        foreach ($plugins_arr as $val){
            $val = trim($val);
            
			$tmp = explode('.', $val);
			$file_extension = end($tmp);
			
            if ($file_extension == 'zip'){
               $this->mpi_plugin_handle_download("temp",$val,$mpi_action,$whform);
            }
            else {
                $plugins[plugin_basename($val . ".php")] = $this->mpi_get_plugin($val);
                $send = 1;
            }
        }
        
        //$plugins = mpi_get_plugins();
        
        if ($send) 
        {
			$to_send = new stdClass();
            $to_send->plugins = $plugins;
            
            $send = serialize($to_send);
            
            $request = 'plugins=' . urlencode($send);
            $http_request = "POST /plugins/update-check/1.0/ HTTP/1.0\r\n";
            $http_request .= "Host: api.wordpress.org\r\n";
            $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
            $http_request .= "Content-Length: " . strlen($request) . "\r\n";
            $http_request .= 'User-Agent: WordPress/' . $wp_version . '; ' . get_bloginfo('url') . "\r\n";
            $http_request .= "\r\n";
            $http_request .= $request;
            
            //echo $http_request."<br><br>";
            
            $response = '';
            if (false !== ($fs = @fsockopen('api.wordpress.org', 80, $errno, $errstr, 3)) && is_resource($fs)) 
            {
                fwrite($fs, $http_request);
                
                while (!feof($fs)){
                    // One TCP-IP packet
                    $response .= fgets($fs, 1160);
                }
                
                fclose($fs);
                //echo $response;
                $response = explode("\r\n\r\n", $response, 2);
            }
            
            $response = unserialize($response[1]);
            
            $i = 0;
            foreach ($plugins_arr as $val) 
            {
                ++$i;
                if ($plugins[plugin_basename("$val.php")]) 
                {
                    if ($response) 
                    {
                        $r = $response[plugin_basename("$val.php")];
                        if (!$r) 
                        {
                            echo '<p class="not-found">' . $i . '. <strong>' . $val . '</strong> not found. Try <a href="http://google.com/search?q=' . $val . ' +wordpress">manual</a> install.</p>';
                        } 
                        elseif ($r->package) 
                        {
                            $this->_mpiflush("<p class=\"found\">$i. Found <strong>" .stripslashes($val). "</strong> ($r->slug, version $r->new_version). Processing installation...</strong></p>");
                            $this->mpi_plugin_handle_download($r->slug,$r->package,$mpi_action,$whform);
							$mpi_fileArr[] = $r->slug;
                        } 
                        else
                        {	
                           echo '<p class="not-found">' . $i . '. Package for <strong><em>' . $val . '</em></strong> not found. Try <a href="' . $r->url . '">manual</a> install.</p>';
                        }
                    } 
                    else
                    {
                        echo '<p class="not-found">' . $i . '. <strong>' . $val . '</strong> not found. Try <a href="http://google.com/search?q=' . $val . ' +wordpress">manual</a> install.</p>';
                    }
                }
            }
			
			if($mpi_cfilenm != "nocreate" && $mpi_fileArr > 0){
				$this->mpi_create_file($mpi_fileArr,$mpi_cfilenm);
			}
        }
    }
	
	function mpi_copy_directory($source, $destination) {
		if ( is_dir( $source ) ) {
			@mkdir($destination);
			$directory = dir( $source );
			while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
				if ( $readdirectory == '.' || $readdirectory == '..' ) {
					continue;
				}
				$PathDir = $source . '/' . $readdirectory; 
				if ( is_dir( $PathDir ) ) {
					$this->mpi_copy_directory( $PathDir, $destination . '/' . $readdirectory );
					continue;
				}
				@copy( $PathDir, $destination . '/' . $readdirectory );
			}
	 
			$directory->close();
		}else {
			@copy( $source, $destination );
		}
	}
	
	function mpi_delete_directory($path){
		if (is_dir($path) === true){
			$files = array_diff(scandir($path), array('.', '..'));
			foreach ($files as $file){
				$this->mpi_delete_directory(realpath($path) . '/' . $file);
			}
			return @rmdir($path);
		}
		else if (is_file($path) === true){
			return @unlink($path);
		}
		return false;
	}
	
	
	function mpi_getWP_maxupload_filesize(){
		$upload_size_unit = $max_upload_size = wp_max_upload_size();
		$sizes = array( 'KB', 'MB', 'GB' );
		for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ ) {
			$upload_size_unit /= 1024;
		}
		if ( $u < 0 ) {
			$upload_size_unit = 0;
			$u = 0;
		} else {
			$upload_size_unit = (int) $upload_size_unit;
		}
		printf( __( 'Maximum upload file size: %d%s.' ), esc_html($upload_size_unit), esc_html($sizes[$u]) );
	}
	
	
    function _mpiflush($s){
        echo $s;
        flush();
    }
	
	function mpi_app_DirTesting(){
		if(!is_dir(MPIUPLOADDIR_PATH.'/mpi_testing')){ 
			if(@mkdir(MPIUPLOADDIR_PATH.'/mpi_testing', 0777)){
				@rmdir(MPIUPLOADDIR_PATH.'/mpi_testing');
				return true;
			}
			else
			return false;
		}
	}
	
	function mpi_app_wpInstall($mpi_role){
		check_admin_referer($this->key);
		_e('<div class="mpi_h3">Plugin installation process:</div>','mpi');
		$plugin_install = !isset($_POST['mpi_wplists']) ? '' : $_POST['mpi_wplists'];
		$mpi_expfilenm = $_POST['mpi_expfilenm'];
		if ($plugin_install != '') {
			$plugin_install = str_replace(array("\r\r\r", "\r\r", "\r\n", "\n\r", "\n\n\n", "\n\n"), "\n", $plugin_install);
			$options = explode("\n", $plugin_install);
			$this->mpi_get_packages($options,$mpi_role,$mpi_expfilenm,"");
		}
	}
	
	function mpi_app_locInstall(){
		check_admin_referer($this->key);
		_e('<div class="mpi_h3">Plugin installation process:</div>','mpi');
		
		for($i=0; $i<count($_FILES['mpi_locFiles']['name']); $i++){
			$mpi_locFilenm = $_FILES['mpi_locFiles']['name'][$i];

			if (strpos($mpi_locFilenm,'mpipluginsbackup') === false){								
				//Get the temp file path
				$tmpFilePath = $_FILES['mpi_locFiles']['tmp_name'][$i];

				//Make sure we have a filepath
				if ($tmpFilePath != ""){
					//Setup our new file path
					$newFilePath = MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp' . $_FILES['mpi_locFiles']['name'][$i];
					
					//Upload the file into the temp dir
					if(@move_uploaded_file($tmpFilePath, $newFilePath)) {
						$mpi_tempurls[] = MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp'.$_FILES['mpi_locFiles']['name'][$i];
					}
				}
			}
			else{
			_e('This is <b>'.$mpi_locFilenm.'</b> not a valid zip archive.','mpi');
			}
		}
		if($mpi_tempurls)
		$this->mpi_get_packages($mpi_tempurls,"activate","nocreate","upload_locFiles");
	}
	
	function mpi_app_expFileUpload(){
		check_admin_referer($this->key);
		_e('<div class="mpi_h3">Plugin installation process:</div>','mpi');
		$plugin_install = file_get_contents($_FILES['mpi_expfileUp']['tmp_name']); 
		if ($plugin_install != '') {
			$options = explode(",", $plugin_install);
			$this->mpi_get_packages($options,"activate","nocreate","");
		}
	}
	
	function mpi_app_downloadFiles(){
		$mpi_filesDir = MPIUPLOADDIR_PATH.'/mpi_logs/files/';
		if(glob($mpi_filesDir . "*.mpi") != false){
			$sr_count = 1;
			echo '<table class="mpi_files" border="1" cellpadding="7" cellspacing="0">
			<tr><th>S. No.</th><th>Exported File Name</th><th>Exported Date,Time</th><th>Download</th><th>Delete</th></tr>';
			foreach(glob($mpi_filesDir . "*.mpi") as $filename){	
				$mpi_filenm = str_replace($mpi_filesDir, "",$filename);
				$mpi_backupfilenm = str_replace($mpi_filesDir, "",$mpi_filenm);
				$mpi_timedate = explode("_",$mpi_backupfilenm);
				$mpi_timedate = str_replace('.mpi', "",$mpi_timedate[1]);
				$mpi_timedate = date("m-d-Y , H:i:s", $mpi_timedate);
				$mpi_full_filenm = $mpi_filenm;
				$arr_params = array( 'dn' => 1, 'filename' => $mpi_full_filenm);
				$download_path =  esc_url( add_query_arg( $arr_params ) );

				$del_arr_params = array( 'dl' => 1, 'filename' => $mpi_full_filenm);
				$delete_path =  esc_url( add_query_arg( $del_arr_params ) );

				?>
					<tr>
						<td class="sr_no"><?php echo $sr_count; ?></td>
						<td class="mpi_filenm"><?php echo $mpi_filenm; ?></td>
						<td class="mpi_filenm"><?php echo $mpi_timedate; ?></td>
						<td class="mpi_dwnload"><a class="mpi_filedwn expfile" title="Download file" href='<?php echo $download_path ;?>' ></a></td>
						<td class="mpi_del"><a class="mpi_trashdwn expfile" title="Delete file" href='<?php echo $delete_path ;?>' onClick="return mpi_delcfirm();" ></a></td>
					</tr>
				<?php
				$sr_count++;
			}
			echo '</table>';
		}
		else{
			_e('<b style="color:#9B0707;">No Exported Files Are Avialable To Download.</b>','mpi');
		}
	}
	
	function mpi_app_wholePluginsBkup(){
		$mpi_backupDir = MPIUPLOADDIR_PATH.'/mpi_logs/';
		if(glob($mpi_backupDir . "*.zip") != false){
			$sr_count = 1;
			echo '<table class="mpi_files" border="1" cellpadding="7" cellspacing="0">
			<tr><th>S. No.</th><th>Backup File Name</th><th>Backup Date,Time</th><th>File Size</th><th>Download</th><th>Delete</th></tr>';
			foreach(glob($mpi_backupDir . "*.zip") as $bfilename){	
				$mpi_backupfilenm = str_replace($mpi_backupDir, "",$bfilename);
				$mpi_timedate = explode("_",$mpi_backupfilenm);
				$mpi_timedate = str_replace('.zip', "",$mpi_timedate[1]);
				$mpi_timedate = date("m-d-Y , H:i:s", $mpi_timedate);
				$mpi_filesize = round(filesize($bfilename)/1024,2);
				$mpi_full_filenm = $mpi_backupfilenm;
				if($mpi_filesize > 1024){$mpi_filesize = round($mpi_filesize/1024,3)." MB";}
				else{$mpi_filesize = $mpi_filesize." KB";}

				$arr_params = array( 'dn' => 1, 'filename' => $mpi_full_filenm);
				$download_path =  esc_url( add_query_arg( $arr_params ) );
				$del_arr_params = array( 'dl' => 1, 'filename' => $mpi_full_filenm);
				$delete_path =  esc_url( add_query_arg( $del_arr_params ) );
				
				?>
					<tr>
						<td class="sr_no"><?php echo $sr_count; ?>.</td>
						<td class="mpi_filenm"><?php echo $mpi_backupfilenm; ?></td>
						<td class="mpi_timedt"><?php echo $mpi_timedate; ?></td>
						<td class="mpi_timedt"><?php echo $mpi_filesize; ?></td>
						<td class="mpi_dwnload"><a class="mpi_filedwn expfile" title="Download file" href='<?php echo $download_path; ?>' ></a></td>
						<td class="mpi_del"><a class="mpi_trashdwn expfile" title="Delete file" href='<?php echo $delete_path; ?>' onClick="return mpi_delcfirm();" ></a></td>
					</tr>
				<?php
				$sr_count++;
			}
			echo '</table>';
		}
		else{
			_e('<b style="color:#9B0707; margin-left: 5px;">No Plugin Backup Files Are Avialable To Download.</b>','mpi');
		}
	}
	
	function mpi_app_pluginBkupFileUpload(){
		check_admin_referer($this->key);
		
		$bk_tmpFilePath = $_FILES['mpi_upbackup']['tmp_name'];

		//Make sure we have a filepath
		if ($bk_tmpFilePath != ""){														
			//Setup our new file path
			$bk_newFilePath = MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp/' . $_FILES['mpi_upbackup']['name'];

			//Upload the file into the temp dir
			if(@move_uploaded_file($bk_tmpFilePath,$bk_newFilePath)) {
				//extract zip file here
				$zip = new ZipArchive;
				if($zip->open($bk_newFilePath) === TRUE) {
					$zip->extractTo(MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp/');
					$zip->close();
					
					if(is_dir(MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp/plugins')){
						@rename(MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp/plugins', MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp/mpitemp');
						@unlink($bk_newFilePath);
						$pluginDir_src = MPIUPLOADDIR_PATH.'/mpi_logs/files/tmp/mpitemp/';

						$this->mpi_copy_directory($pluginDir_src,MPI_WP_PLUGIN_DIR);
						$this->mpi_delete_directory($pluginDir_src);
						
						_e('<b class="mpi_act">Plugins Installed Successfully.</b><br/>','mpi');
					}
					else{
						$mpi_rmdirnm = str_replace('.zip', "",$bk_newFilePath);
						@unlink($bk_newFilePath);
						$this->mpi_delete_directory($mpi_rmdirnm);
						_e('<b style="color:#9B0707">Please upload valid plugins backup file.</b>','mpi');
					}	
				}										
			}
		}
		else{
			_e('<b style="color:#9B0707">Please Increase WordPress Media Upload Size Limit.</b>','mpi');
		}
	}


	/*Function to start download after mpi_download.php bugs*/

function mpi_download(){


	global $current_user;
		get_currentuserinfo();

		if ($current_user->user_level < 9 ) {
			wp_die('You do not have permission to perform this action');
		}

		$mpi_upload_dir = MPIUPLOADDIR_PATH.'/mpi_logs/';
		$name= $_REQUEST['filename'];
		$file      = $mpi_upload_dir.$_REQUEST['filename'];
		
		 //Check the file premission
		 if(!is_readable($file)) wp_die('File not found or inaccessible!');

		 $size = filesize($file);
		 /* Figure out the MIME type | Check in array */
		 $known_mime_types=array(
		 	"pdf" => "application/pdf",
		 	"txt" => "text/plain",
		 	"html" => "text/html",
		 	"htm" => "text/html",
			"exe" => "application/octet-stream",
			"zip" => "application/zip",
			"doc" => "application/msword",
			"xls" => "application/vnd.ms-excel",
			"ppt" => "application/vnd.ms-powerpoint",
			"gif" => "image/gif",
			"png" => "image/png",
			"jpeg"=> "image/jpg",
			"jpg" =>  "image/jpg",
			"php" => "text/plain",
			"mpi" => "text/plain"
		 );
		 
		 if($mime_type==''){
			 $file_extension = strtolower(substr(strrchr($file,"."),1));
			 if(array_key_exists($file_extension, $known_mime_types)){
				$mime_type=$known_mime_types[$file_extension];
			 } else {
				$mime_type="application/force-download";
			 };
		 };
		 
		 //turn off output buffering to decrease cpu usage
		 ob_start(); 
		 
		 // required for IE, otherwise Content-Disposition may be ignored
		 if(ini_get('zlib.output_compression'))
		  ini_set('zlib.output_compression', 'Off');
		 
		 header('Content-Type: ' . $mime_type);
		 header('Content-Disposition: attachment; filename="'.$name.'"');
		 header("Content-Transfer-Encoding: binary");
		 header('Accept-Ranges: bytes');
		 
		 /* The three lines below basically make the 
		    download non-cacheable */
		 header("Cache-control: private");
		 header('Pragma: private');
		 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		 
		 // multipart-download and download resuming support
		 if(isset($_SERVER['HTTP_RANGE']))
		 {
			list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
			list($range) = explode(",",$range,2);
			list($range, $range_end) = explode("-", $range);
			$range=intval($range);
			if(!$range_end) {
				$range_end=$size-1;
			} else {
				$range_end=intval($range_end);
			}
			/*
			------------------------------------------------------------------------------------------------------
			------------------------------------------------------------------------------------------------------
		 	*/
			$new_length = $range_end-$range+1;
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: $new_length");
			header("Content-Range: bytes $range-$range_end/$size");
		 } else {
			$new_length=$size;
			header("Content-Length: ".$size);
		 }
		 
		 /* Will output the file itself */
		 $chunksize = 1*(1024*1024); //you may want to change this
		 $bytes_send = 0;
		 if ($file = fopen($file, 'r'))
		 {
			if(isset($_SERVER['HTTP_RANGE']))
			fseek($file, $range);
		 
			while(!feof($file) && 
				(!connection_aborted()) && 
				($bytes_send<$new_length)
			     )
			{
				$buffer = fread($file, $chunksize);
				print($buffer); //echo($buffer); // can also possible
				flush();
				$bytes_send += strlen($buffer);
			}
		 fclose($file);
		 } else
		 //If no permissiion
		 die('Error - can not open file.');

}

function mpi_delete(){

global $current_user;
get_currentuserinfo();

if ($current_user->user_level < 9 ) {
	wp_die('You do not have permission to perform this action');
}
$mpi_upload_dir = MPIUPLOADDIR_PATH.'/mpi_logs/';
$file_path      = $mpi_upload_dir.$_REQUEST['filename'];
if(!is_readable($file_path)) wp_die('File not found or inaccessible!');

if(@unlink($file_path))
header('Location: ' . $_SERVER['HTTP_REFERER']);
else
wp_die('Error in deleting file');

}



}
