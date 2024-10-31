<?php 
	/*
	 * --------------------------------------------------------------------------------------------------
	 * This here will give us some additional post functionality inside of the wordpress post/edit
	 * admin page
	 * --------------------------------------------------------------------------------------------------
	 */
	$realvms_DelUser = get_option( 'realvms_DelUser' );
	$realvms_FavesUser = get_option( 'realvms_FavesUser' );
?>

<div class="postbox">
	<h3>RealVMS Settings</h3>

	<div class="inside">
		<a href="http://realvms.com/support/manual_control">
			Click Here For Support On These Settings
		</a>

		<table border="0" cellpadding="6" cellspacing="4" style="margin-bottom: 10px; margin-top: 10px;">
		
		<?php
			/*
			 * ----------------------------------------------------------------------------------
			 * Settings to add comments to the post or not
			 * ----------------------------------------------------------------------------------
			 */
			if ( isset( $_GET ['realvms'] ) ) :
		?>
		
		    <tr>
				<td>Insert Between: <input value="" tabindex="9" type="text" 
				name="lowestNumComments" size="10" />
				And <input value="" type="text" tabindex="10" name="highestNumComments" 
				size="10" /> Comments</td>
			</tr>
			
			<tr>
				<td>Remove Swear Words From The Comments? <input type="checkbox" tabindex="11" 
				name="removeSwearRemove" value="1" /></td>
			</tr>
			
			<tr>
				<td><br /></td>
			</tr>
				
		<?php 
			endif; 

			/*
			 * ----------------------------------------------------------------------------------
			 * Settings For Delicious
			 * ----------------------------------------------------------------------------------
			 */
			if ( empty( $realvms_DelUser ) ) :
		?>
		
	    	<tr>
				<td><b>Bookmark On Del.icio.us?</b></td>
			</tr>
			
			<tr>
				<td><span style="margin-left: 20px;">Username: <input type="text" tabindex="12" 
				name="deliciousUserName" value="" /></span></td>
			</tr>
			
			<tr>
				<td><span style="margin-left: 20px;">Password: <input type="password" tabindex="13" 
				name="deliciousPassword" value="" /></span></td>
			</tr>	
				
		<?php 
			endif;
			if ($realvms_DelUser) :
		?>
		
	    	<tr>
				<td><b>Bookmarking Options</b></td>
			</tr>
			
			<tr>
				<td><span style="margin-left: 20px;">Bookmark On Del.icio.us? <input
					type="checkbox" value="1" tabindex="14" name="post_delicious654" /></span></td>
			</tr>
				
		<?php 
			endif;

			/*
			 * ---------------------------------------------------------------------------------------------
			 * Settings For Faves
			 * ---------------------------------------------------------------------------------------------
			 */
			if ( empty( $realvms_FavesUser ) ) :
		?>
		
	    	<tr>
				<td><b>Bookmark On Faves?</b></td>
			</tr>
			
			<tr>
				<td><span style="margin-left: 20px;">Username: <input type="text"
					name="favesUserName" tabindex="15" value="" /></span></td>
			</tr>
			
			<tr>
				<td><span style="margin-left: 20px;">Password: <input type="password"
					name="favesPassword" tabindex="16" value="" /></span></td>
			</tr>	
			
		<?php 
			endif;
			if ($realvms_FavesUser) :
		?>
		
	    	<tr>
				<td><span style="margin-left: 20px;">Bookmark On Faves? <input type="checkbox" value="1" 
				tabindex="17" name="post_faves654" /></span></td>
			</tr>
				
		<?php 
			endif;
		?>
		
		</table>
	</div>
</div>