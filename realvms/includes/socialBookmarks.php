<?php 
	if ( isset( $_POST ['realvmsSaveSocialSettings'] ) ) {
		update_option ( 'realvms_DelUser', $_POST ['user_delicious'] );
		
		if( empty( $_POST ['pass_delicious'] ) ) {
			$realvms_DelPass = $_POST ['realvmsDelPass'];
		}
		else
		{
			$realvms_DelPass = $_POST ['pass_delicious'];
		}
		
		update_option ( 'realvms_DelPass', $realvms_DelPass );
		update_option ( 'realvms_FavesUser', $_POST ['user_faves'] );
		
		if( empty( $_POST ['pass_faves'] ) ) {
			$realvms_FavesPass = $_POST['realvmsFavesPass'];
		}
		else
		{
			$realvms_FavesPass = $_POST ['pass_faves'];
		}
		
		update_option ( 'realvms_FavesPass', $realvms_FavesPass );
		
		$postMessage = '<div id="message" class="updated fade"><p>Your Settings Have Been ';
		$postMessage .= 'Successfully Saved.</p></div>';
	}
	
	$realvms_DelUser = get_option( 'realvms_DelUser' );
	$realvms_DelPass = get_option( 'realvms_DelPass' );
	$realvms_FavesUser = get_option( 'realvms_FavesUser' );
	$realvms_FavesPass = get_option( 'realvms_FavesPass' );
?>	
	
<div class="wrap">
<div id="message" class="error">
		<p><a href="http://www.realvms.com#anchor">Upgrade To RealVMS Pro</a> To Get The Highest Features And Performance.</p>
	</div>
	<h2>Social Bookmarks <a href="http://www.realvms.com/the-realvms-social-bookmarking-feature/" target="_blank">
						<img src="<?php echo $RealVMS->httpLocation.'/images/help.png'; ?>" alt="Help" />
					</a></h2>
	
	<?php 
		if ( isset( $postMessage ) ) {
			echo $postMessage;
		}
	?>
	
	<p>
		<h3>Del.icio.us Information</h3>
	</p>
	
	<p>
		<form action="<?php echo $RealVMS->actionRequest; ?>" method="POST">
			<input type="hidden" name="realvmsDelPass" value="<?php echo $realvms_DelPass; ?>" />
			<input type="hidden" name="realvmsFavesPass" value="<?php echo $realvms_FavesPass; ?>" />
	
			<span style="margin-left:20px;">
				Username: <input type="text" size="40" value="<?php echo $realvms_DelUser; ?>" 
				name="user_delicious" />
			</span>
			
			<br /><br />
		
			<span style="margin-left:20px;">
				Password: <input type="password" size="40" value="" name="pass_delicious" /> (Leave blank to 
				keep your last saved password)
			</span>
			
			<br /><br />

			<p>
				<h3>Faves Information</h3>
			</p>
		
			<span style="margin-left:20px;">
				Username: <input type="text" size="40" value="<?php echo $realvms_FavesUser; ?>" 
				name="user_faves" />
			</span>
			
			<br /><br />
		
			<span style="margin-left:20px;">
				Password: <input type="password" size="40" value="" name="pass_faves" /> (Leave blank to 
				keep your last saved password)
			</span>
			
			<br /><br />
			
			<input type="submit" value="Save Settings" name="realvmsSaveSocialSettings" class="button-secondary action" />
		</form>
	</p>
</div>