
<div class="wrap">
	<div id="message" class="error">
		<p><a href="http://www.realvms.com#anchor">Upgrade To RealVMS Pro</a> To Get The Highest Features And Performance.</p>
	</div>
				
	<h2>Add Videos <a href="http://www.realvms.com/how-to-manually-add-videos-using-realvms/" target="_blank">
						<img src="<?php echo $RealVMS->httpLocation.'/images/help.png'; ?>" alt="Help" />
					</a></h2>

<?php 		
	$RealVMS->javascriptWarning();

	/*
	 * ---------------------------------------------------------------------------------------------
	 * Catch the post for the realvms_options() function
	 * ---------------------------------------------------------------------------------------------
	 */
	if ( $_POST ['realvmsDisplayVideo'] == 'displayThisVideo' ) {
		/*
		 * -----------------------------------------------------------------------------------------
		 * Call the video search function
		 * -----------------------------------------------------------------------------------------
		 */
		$html = realvmsDisplayVideo ( $_POST ['realvmsVideoPlayURL'], $_POST ['realvmsVideoID'], 
		$_POST ['realvmsVideoTitle'], $_POST ['realvmsVideoDescription'], $_POST ['realvmsVideoTags'], 
		$_POST ['realvmsBigImage'] );
	}
	else {
		/*
		 * -----------------------------------------------------------------------------------------
		 * Go out and get the video search results for this search
		 * -----------------------------------------------------------------------------------------
		 */
		if( isset( $_POST ['realvmsSearchTerm'] ) ) {
			$html = realvmsSearchVideos ( $_POST ['realvmsSearchTerm'], $_POST ['realvmsCategory'], 
			$_POST ['realvmsVideoOrder'], $_POST ['realvmsSearchIndex'] );
		}
	}

	if( $html == 'invalid_search_term' ) :
?>
	<div id="message" style="margin-top:12px;" class="updated fade">
		<p>
			<font color="#FF1A00;"><b>Error: </b></font>Please Enter A Search Term
		</p>
	</div>
	
	<?php 
		elseif( $html === false ) :
	?>
	
	<div id="message" style="margin-top:12px;" class="updated fade">
		<p>
			There Are No Other Videos To Be Displayed At This Time. 
			<br><br><br>
			<b>Troubleshooting Tips: </b>
		<p>
		
		<p style="line-height:18px;">
			1) Try searching for the same keyword but select a different category. 
			Sometimes the results returned by YouTube will not show any results
			under the "All" category but will show plenty of results under a 
			different category like "Education" or "Entertainment".
		</p>
		
		<p style="line-height:18px;">
			2) If you have searched for this term under multiple categories and still
			have no results try searching for another related term. Sometimes very
			long keywords like "Show Me How Something Online Works" may not return
			any results but a more specific search like "How Do Cars Work" will 
			return plenty of results.
		</p>
	</div>
	
	<?php 
		endif;
	?>
		
	<form style="margin-top:0px;" action="<?php echo $RealVMS->actionRequest; ?>" method="POST">
		<input type="hidden" name="realvmsSearchIndex" value="1" />
		
		<table cellpadding="0" cellspacing="10" border="0">
			<tr>
				<td>
					<b>Search Term: </b><input type="text" size="35" name="realvmsSearchTerm" 
					value="<?php echo $_POST ['realvmsSearchTerm']; ?>" /> 
				</td>
			</tr>
			
			<tr>
				<td>
					<b>Choose Category: </b>
					<select name="realvmsCategory">
						<option value="All">All</option>
						<option value="Autos&amp;Vehicles">Autos & Vehicles</option>
						<option value="Comedy">Comedy</option>
						<option value="Education">Education</option>
						<option value="Entertainment">Entertainment</option>
						<option value="Film&amp;Animation">Film & Animation</option>
						<option value="Gaming">Gaming</option>
						<option value="Howto&amp;Style">Howto & Style</option>
						<option value="Music">Music</option>
						<option value="News&amp;Politics">News & Politics</option>
						<option value="People&amp;Blogs">People & Blogs</option>
						<option value="Pets&amp;Animals">Pets & Animals</option>
						<option value="Science&amp;Technology">Science & Technology</option>
						<option value="Sports">Sports</option>
						<option value="Travel&amp;Events">Travel & Events</option>
					</select> 
				</td>
			</tr>
			
			<tr>
				<td>
					<b>Sort Results By: </b> 
					<select name="realvmsVideoOrder">
						<option value="relevance">Relevance</option>
						<option value="published">Published</option>
						<option value="viewCount">View Count</option>
						<option value="rating">Rating</option>
					</select>
				</td>
			</tr>
			
			<tr>
				<td>
					<input type="submit" value="Search Videos" name="realvmsSearchVideos" id="realvmsSearchVideos" class="button-secondary action" />
				</td>
			</tr>
		</table>
	</form>
<?php 
	if( $html !== false && $html != 'invalid_search_term' ) {
		echo $html;
	}
?>
</div>