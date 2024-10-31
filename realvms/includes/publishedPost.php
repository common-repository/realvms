<?php 
	/*
	 * ----------------------------------------------------------------------------------------------
	 * Get the post content from the post id created here
	 * ----------------------------------------------------------------------------------------------
	 */
	$postContent = $wpdb->get_var ( "SELECT post_content FROM " . $wpdb->prefix . 
	"posts WHERE ID = '" . addslashes( $postID ) . "'" );
	
	/*
	 * ----------------------------------------------------------------------------------------------
	 * Get the video ID and image url which we previously setup in our session
	 * ----------------------------------------------------------------------------------------------
	 */
	session_start();
	
	$videoID = $_SESSION ['videoID'];
	$videoImage = $_SESSION ['videoImage'];
	
	/*
	 * ----------------------------------------------------------------------------------------------
	 * Match up the video ID with the content
	 * ----------------------------------------------------------------------------------------------
	 */
	$match = array();
	preg_match ( "/$videoID/", $postContent, $match );

	if ( $match [0] == $videoID ) {
		$lowestNumComments = $_POST ['lowestNumComments'];
		$highestNumComments = $_POST ['highestNumComments'];
		$removeSwearWords = $_POST ['removeSwearRemove'];
		
		/*
		 * ------------------------------------------------------------------------------------------
		 * Check to see if we already have already added this video information to our database
		 * ------------------------------------------------------------------------------------------
		 */
		$checkForExistingEntry = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . $RealVMS->optionsTable . 
		" WHERE seen = '" . addslashes ( $videoID ) . "'" );
		
		/*
		 * ------------------------------------------------------------------------------------------
		 * Add the video information to our database only if it has not been previously added
		 * ------------------------------------------------------------------------------------------
		 */
		if( empty( $checkForExistingEntry ) ) {
			$wpdb->query ( "INSERT INTO " . $wpdb->prefix . $RealVMS->optionsTable .
			" (seen,post_id,image) VALUES ('" . addslashes ( $videoID ) . "','" . 
			addslashes ( $postID ) . "','" . addslashes ( $videoImage ) . "')" );
		}
		
		/*
		 * ------------------------------------------------------------------------------------------
		 * If the user would like to include comments along this this post then add them now
		 * ------------------------------------------------------------------------------------------
		 */
		$comments->addComments ( $videoID, $postID, $lowestNumComments, $highestNumComments, 
		$removeSwearWords );
	}
	
	/*
	 * ----------------------------------------------------------------------------------------------
	 * Unset the session values we have setup previously since we don't need them anymore and we
	 * want to avoid any duplicates in the future
	 * ----------------------------------------------------------------------------------------------
	 */
	unset ( $_SESSION ['videoURL'] );
	unset ( $_SESSION ['videoTitle'] );
	unset ( $_SESSION ['videoDescription'] );
	unset ( $_SESSION ['videoTags'] );
	unset ( $_SESSION ['videoID'] );
	unset ( $_SESSION ['videoImage'] );
	
	/*
	 * ----------------------------------------------------------------------------------------------
	 * Call the get_social_object() function which is contained in our includes/functions.php file
	 * ----------------------------------------------------------------------------------------------
	 */
	$socialObject = get_social_object ( $postID );
	$socialObject ['content'] = $postContent;
	
	$realvms_DelUser = get_option( 'realvms_DelUser' );
	$realvms_DelPass = get_option( 'realvms_DelPass' );
	$realvms_FavesUser = get_option( 'realvms_FavesUser' );
	$realvms_FavesPass = get_option( 'realvms_FavesPass' );
	
	if( empty( $realvms_DelUser ) || empty( $realvms_DelPass ) ) {
		$realvms_DelPass = $_POST ['deliciousPassword'];
		$realvms_DelUser = $_POST ['deliciousUserName'];
	}
	
	if( empty( $realvms_FavesUser ) || empty( $realvms_FavesPass ) ) {
		$realvms_FavesUser = $_POST ['favesPassword'];
		$realvms_FavesPass = $_POST ['favesUserName'];
	}
		
	/*
	 * -------------------------------------------------------------------------------------------------
	 * Settings For Delicious
	 * -------------------------------------------------------------------------------------------------
	 */
	if ( !empty( $realvms_DelUser ) && !empty( $realvms_DelPass ) && $_POST ['post_delicious654'] == '1' ) {
		$bookmarks->delicious ( $socialObject, $realvms_DelUser, $realvms_DelPass );
	}
	
	/*
	 * -------------------------------------------------------------------------------------------------
	 * Settings For Faves
	 * -------------------------------------------------------------------------------------------------
	 */
	if ( !empty( $realvms_FavesUser ) && !empty( $realvms_FavesPass ) && $_POST ['post_faves654'] == '1' ) {
		$bookmarks->faves ( $socialObject, $realvms_FavesUser, $realvms_FavesPass );
	}
?>