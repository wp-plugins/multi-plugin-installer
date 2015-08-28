<?php
$mpiObj = new mpinstaller();
?>
<div class="wrap pc-wrap">
	<div class="mpiicon icon32"></div>
	<h2><?php _e('Multi Plugin Installer '.mpi_get_version().'','mpi') ?></h2>
	<?php
		if (!current_user_can('edit_plugins')) { 
			_e('You do not have sufficient permissions to manage plugins on this blog.<br>','mpi');
			return;
		}
	?>
	<div id="mpiblock">

			
		<div style="text-align:right;"><a href="javascript:void(0);" id="mpi-expand"><?php _e('Expand All','mpi') ?></a>&nbsp;<a href="javascript:void(0);" id="mpi-collapse"><?php _e('Collapse All','mpi') ?></a></div>
		
		<div><?php if($mpiObj->mpi_app_DirTesting()){} else{ _e('<div class="mpi_error">oops!!! Seems like the directory permission are not set right so some functionalities of plugin will not work.<br/>Please set the directory permission for the folder "uploads" inside "wp-content" directory to 777.</div>','mpi'); } ?></div>
		
		<!-- Install Plugins From Wordpress Site -->
		<div id="poststuff" class="mpi-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<h3 class="hndle"><span><?php _e('Install Plugins By Their Names OR WordPress Download URL :', 'mpi'); ?></span></h3>
				<div class="inside">
					<form name="form_apu" method="post" action="">
						<?php wp_nonce_field($mpiObj->key); ?>
						<div> 				
							<p><b><?php _e('Enter Exported File Name:','mpi'); ?></b><br/><input type="text" name="mpi_expfilenm" /><span><small style="color:#9B0707;">&nbsp;(<?php _e(' * Please enter unique name for file without spaces / special charactors.','mpi'); ?>)</small></span></p>			
							<p><b><?php _e('Enter the list of plugins to install.<br />You can specify either the Name or URL of the plugin zip installation file.','mpi'); ?></b></p>
							<textarea style="border:1px solid #D1D1D1;width:575px;" name="mpi_wplists" id="mpi_wplists" cols="40" rows="10"></textarea><br/><small style="color:#9B0707;">&nbsp;(<?php _e('Please enter one name in one line.','mpi') ?>)</small>
							<br/><br/>
							<div>
								<input style="float:left;" class="button button-primary mpi_button" type="submit" name="mpi_wpInstall" value="<?php _e('Install plugins &raquo;','mpi'); ?>" />
								<input style="float:right;"  class="button button-primary mpi_button" type="submit" name="mpi_wpActivate" value="<?php _e('Install & Activate plugins &raquo;','mpi'); ?>" />
								<div class="mpi_clear"></div>
							</div>
						</div>
					</form>
					<?php
						if(isset($_POST['mpi_wpInstall']) && trim($_POST['mpi_wplists'])){
							$mpiObj->mpi_app_wpInstall('install');
						}
						if(isset($_POST['mpi_wpActivate']) && trim($_POST['mpi_wplists'])){
							$mpiObj->mpi_app_wpInstall('activate');
						}	
					?>
				</div>
			</div>		
		</div>
		<!-- //Install Plugins From Wordpress Site -->
	
		<!-- Install Plugins Directly From PC Zip Files -->
		<div id="poststuff" class="mpi-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<h3 class="hndle"><span><?php _e('Install Plugins Directly From Your Local Machine :','mpi'); ?></span></h3>
				<div class="inside">
					<br/>
					<form onsubmit="return valid_zipfile('mpi_locFiles');" name="form_uppcs" method="post" action="" enctype="multipart/form-data">
						<?php wp_nonce_field($mpiObj->key); ?>
						<div>					
							<input type="file" class="mpi_left" name="mpi_locFiles[]" id="mpi_locFiles" multiple="multiple" size="40" />
							<input class="button button-primary mpi_button" type="submit" name="mpi_locInstall" value="<?php _e('Install & Activate plugins &raquo;','mpi'); ?>"  />
							<div class="mpi_clear"></div>
						</div>
						<small style="color:#9B0707;">&nbsp;(<?php _e(' you can select multi plugin.','mpi'); ?>)</small>
					</form>
					<?php
						if (isset($_POST['mpi_locInstall']) && $_FILES['mpi_locFiles']['name'][0] != ""){
							$mpiObj->mpi_app_locInstall();
						}
					?>
					
				</div>
			</div>		
		</div>
		<!-- //Install Plugins Directly From PC Zip Files -->
	
		<!-- Install Plugins Using Exported File -->
		<div id="poststuff" class="mpi-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<h3 class="hndle"><span><?php _e('Import MPI (.mpi) File To Install & Activate The Plugins :','mpi'); ?></span></h3>
				<div class="inside">
					<br/>
					<form onsubmit="return valid_extension();" name="form_expImp" method="post" action="" enctype="multipart/form-data">
						<?php wp_nonce_field($mpiObj->key); ?>
						<div>					
							<input class="mpi_left" type="file" name="mpi_expfileUp" size="40" />
							<input class="button button-primary mpi_button" type="submit" name="mpi_expfileImp" value="<?php _e('Install & Activate plugins &raquo;','mpi'); ?>" />
							<div class="mpi_clear"></div>
						</div>
					</form>
					<?php
						if (isset($_POST['mpi_expfileImp']) && $_FILES['mpi_expfileUp']['name'] != ""){
							$mpiObj->mpi_app_expFileUpload();
						}
					?>
				</div>
			</div>		
		</div>
		<!-- //Install Plugins Using Exported File -->
		
		<!-- Download Exported Files -->
		<div id="poststuff" class="mpi-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<h3 class="hndle"><span><?php _e('Downloads The Exported Files Here :','mpi') ?></span></h3>
				<div class="inside">
					<?php $mpiObj->mpi_app_downloadFiles(); ?>
				</div>
			</div>		
		</div>
		<!-- //Download Exported Files -->
		
		<!-- Take Whole Plugins Backup -->
		<div id="poststuff" class="mpi-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<h3 class="hndle"><span><?php _e('Take Whole Plugins Backup :','mpi') ?></span></h3>
				<div class="inside">
					<br/>
					<a class="mpi_plugbkup" href="<?php echo MPIPLUGIN_URL; ?>mpi_pluginsbackup.php"><?php _e('Click Here To Take Whole Plugins Backup.','mpi') ?></a>
					<?php $mpiObj->mpi_app_wholePluginsBkup(); ?>
				</div>
			</div>		
		</div>
		<!-- //Take Whole Plugins Backup -->
		
		<!-- Upload Downloaded Backup File To Install Plugins -->
		<div id="poststuff" class="mpi-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<h3 class="hndle"><span><?php _e('Upload Downloaded Backup File To Install Plugins :','mpi') ?></span></h3>
				<div class="inside">
					<br/>
					<span class="max-upload-size"><?php $mpiObj->mpi_getWP_maxupload_filesize(); ?></span>
					<form onsubmit="return valid_zipfile('mpi_upbackup');" name="form_bkupl" method="post" action="" enctype="multipart/form-data">
						<?php wp_nonce_field($mpiObj->key); ?>
						<div>					
							<input type="file" class="mpi_left" name="mpi_upbackup" id="mpi_upbackup" size="40" />
							<input class="button button-primary mpi_button" type="submit" name="mpi_upldpl" value="<?php _e('Install plugins &raquo;','mpi'); ?>" />
							<div class="mpi_clear"></div>
						</div>
					</form>
					<?php
						if (isset($_POST['mpi_upldpl']) && $_FILES['mpi_upbackup']['name'] != ""){
							$mpiObj->mpi_app_pluginBkupFileUpload();
						}
					?>
				</div>
			</div>
		</div>		
		<!-- //Upload Downloaded Backup File To Install Plugins -->
				
		<iframe frameborder="1" class="mpi_iframe" src="http://www.sketchthemes.com/sketch-updates/plugin-updates/mpi-lite/mpi.php" width="250px" height="430px" scrolling="no" ></iframe> 		
	</div>
</div>