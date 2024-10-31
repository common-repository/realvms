<?php
	if ( $_POST ['realvmsSaveGlobals'] ) {
		$showImage = $_POST ['realvmsDisplayImage'];
		$removeBookmarks = $_POST ['realvmsRemoveBookmarks'];
		$removeEverything = $_POST ['realvmsRemoveEverything'];
		
		if (! $showImage) {
			$showImage = 0;
		}
		
		if (! $removeBookmarks) {
			$removeBookmarks = 0;
		}
		
		if (! $removeEverything) {
			$removeEverything = 0;
		}
		
		update_option ( 'vms_showImage', $showImage );
		update_option ( 'realvms_DeleteSocialBookmarks', $removeBookmarks );
		update_option ( 'realvms_removeEverything', $removeEverything );
		
		$postMessage = '<div id="message" class="updated fade">';
		$postMessage .= '<p>Your Settings Have Been Successfully Saved.</p>';
		$postMessage .= '</div>';
	}
	
	$displayImage = get_option ( 'vms_showImage' );
	
	if ($displayImage == 1) {
		$displayImage = 'checked="true"';
	} else {
		$displayImage = '';
	}
	
	$socialDeactivation = get_option ( 'realvms_DeleteSocialBookmarks' );
	
	if ($socialDeactivation == 1) {
		$socialDeactivation = 'checked="true"';
	} else {
		$socialDeactivation = '';
	}
	
	$removeEverything = get_option ( 'realvms_removeEverything' );
	
	if ($removeEverything == 1) {
		$removeEverything = 'checked="true"';
	} else {
		$removeEverything = '';
	}
?>	

<div class="wrap">
<div id="message" class="error">
		<p><a href="http://www.realvms.com#anchor">Upgrade To RealVMS Pro</a> To Get The Highest Features And Performance.</p>
	</div>
	<h2>General Settings <a href="http://www.realvms.com/general-settings-overview/" target="_blank">
						<img src="<?php echo $RealVMS->httpLocation.'/images/help.png'; ?>" alt="Help" />
					</a></h2>
	
	<?php 
		if ($postMessage) {
			echo $postMessage;
		}
	?>
	
	<form method="post" action="<?php echo $RealVMS->actionRequest; ?>">
		<table cellpadding="0" cellspacing="10" border="0">
			<tr>
				<td>
					Display Image On Everything But Single Page? 
					<input type="checkbox" <?php echo $displayImage; ?> value="1" name="realvmsDisplayImage" />
				</td>
			</tr>
			
			<tr>
				<td>
					Remove Social Bookmark Information On Deactivation? 
					<input type="checkbox" <?php echo $socialDeactivation; ?> value="1" name="realvmsRemoveBookmarks" />
				</td>
			</tr>
			
			<tr>
				<td>
					Remove Everything On Deactivation? 
					<input type="checkbox" <?php echo $removeEverything; ?> value="1" name="realvmsRemoveEverything" />
					<br>
					<small><b>ONLY</b> select this option if you wish to start over from scratch, otherwise never choose this option.</small>
				</td>
			</tr>
		</table>
		
		<input type="submit" value="Save Settings" name="realvmsSaveGlobals" class="button-secondary action" />
	</form>
</div>
		