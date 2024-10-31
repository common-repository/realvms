<?php
	
	session_start();
	
	include_once( str_replace ( '/wp-content/plugins/' . str_replace ( '/includes/addSession.php', '', 
	substr ( __FILE__, strpos ( __FILE__, '/wp-content/plugins/' ) + 20 ) )  . '/includes/addSession.php', 
	'', __FILE__ ) . '/wp-config.php' );
	
	include_once('functions.php');

	/*
	 * ---------------------------------------------------------------------------------------------------------
	 * Get all of the post values in order to set the sessions with
	 * ---------------------------------------------------------------------------------------------------------
	 */
	if( $_POST ['createSession'] == 1) {
		$_SESSION['videoURL'] = stripslashes( $_POST ['videoURL'] );
		$_SESSION['videoTitle'] = stripslashes( $_POST ['videoTitle'] );
		$_SESSION['videoDescription'] = stripslashes( $_POST['videoDescription'] );
		$_SESSION['videoTags'] = stripslashes( $_POST ['videoTags'] );
		$_SESSION['videoID'] = stripslashes( $_POST ['videoID'] );
		$_SESSION['videoImage'] = stripslashes( $_POST ['videoImage'] );
		
		header('Location: '.$RealVMS->domainName.'/wp-admin/post-new.php?realvms=true');
	}
	else {
		echo $_SESSION['videoURL'] . '/*/' . $_SESSION['videoTitle'] . '/*/' . $_SESSION['videoDescription'] .
		'/*/' . $_SESSION['videoTags'] . '/*/' . $_SESSION['videoID'] . '/*/' . $_SESSION['videoImage'];
	}
?>
