<?php 
	class RealVMS_Comments {
		/*
		 * -----------------------------------------------------------------------------------------------------
		 * Set up all of the global variables that we will use in this object here
		 * -----------------------------------------------------------------------------------------------------
		 */
		var $youtube;
		var $wpdb;
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * PHP4 compatibility layer for calling the PHP5 constructor.
		 * -------------------------------------------------------------------------------------------------
		 */
		function RealVMS_Comments( $youtube, $wpdb ) {
			$this->youtube = $youtube;	
			$this->wpdb = $wpdb;	
		}
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * PHP5 style destructor and will run when object is destroyed.
		 * -------------------------------------------------------------------------------------------------
		 */
		function __destruct() {
			return true;
		}
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * This function is used to fetch a particular number of comments from the YouTube API, the comments
		 * returned will be for a specific video 
		 * -------------------------------------------------------------------------------------------------
		 * PARAMATER REFERENCE
		 * -------------------------------------------------------------------------------------------------
		 * $videoID - The ID of a specific YouTube video the comments will be associated with *REQUIRED*
		 * -------------------------------------------------------------------------------------------------
		 * $postID - The ID of the specific wordpress post these comments will be associated with *REQUIRED*
		 * -------------------------------------------------------------------------------------------------
		 * $lowestNumComments - The lowest number of comments the user wants to get from the API
		 * -------------------------------------------------------------------------------------------------
		 * $highestNumComments - The highest number of comments the user wants to get from the API
		 * -------------------------------------------------------------------------------------------------
		 * $removeSwearWords - If the value is 1 the user wants to remove any swear words these comments may
		 * contain otherwise we simply will not remove any swear words from the comment(s)
		 * -------------------------------------------------------------------------------------------------
		 */
		function addComments ( $videoID, $postID, $lowestNumComments = 0, $highestNumComments = 0, $removeSwearWords = 0 ) {
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Fetch the comments based upon a particular YouTube video id
			 * ---------------------------------------------------------------------------------------------
			 * PARAMATER REFERENCE
			 * ---------------------------------------------------------------------------------------------
			 * $videoID - The ID of a specific YouTube video the comments will be associated with *REQUIRED* 
			 * ---------------------------------------------------------------------------------------------
			 * $lowestNumComments - The lowest number of comments the user wants to get from the API
			 * ---------------------------------------------------------------------------------------------
			 * $highestNumComments - The highest number of comments the user wants to get from the API
			 * ---------------------------------------------------------------------------------------------
			 */
			$object = $this->youtube->getComments ( $videoID, $lowestNumComments, $highestNumComments );
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * If we have no comments then return false
			 * ---------------------------------------------------------------------------------------------
			 */
			if( $object === false ) {
				return false;
			}
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * We know we have some comments so loop through them and put them in the database
			 * ---------------------------------------------------------------------------------------------
			 */
			$commentArray = $object ['commentArray'];
			$bad_words = file( str_replace ( '/classes/Comments.php', '', __FILE__ ) . '/includes/BadWords.txt' );
			
			$item = 0;
			foreach( $commentArray as $author => $content ) {
				/*
				 * -----------------------------------------------------------------------------------------
				 * If the user wants to remove the bad words from the comment then do so now
				 * -----------------------------------------------------------------------------------------
				 */
				if( $removeSwearWords == 1 && $bad_words ) {
					foreach( $bad_words as $word ) {
						$content = preg_replace( "/\b".trim($word)."\b/i", str_repeat( "*", strlen( $word ) ), $content ); 
					}
				}
				
				$randomHour = mt_rand( 0, 23 );
				
				$use_date = date( "Y-m-d $randomHour:i:s" );
				
				$this->wpdb->query( "INSERT INTO " . $this->wpdb->prefix . "comments (comment_post_ID,comment_author,comment_author_email,comment_author_IP,comment_date,comment_date_gmt,comment_content,comment_approved,comment_author_url,comment_agent) VALUES ('" . $this->wpdb->escape( $postID ) . "','" . $this->wpdb->escape( $author ) . "','" . $this->wpdb->escape( 'automated@' . str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ) ) . "','" . $this->wpdb->escape( $_SERVER['REMOTE_ADDR'] ) . "','" . $this->wpdb->escape( $use_date ) . "','" . $this->wpdb->escape( $use_date ) . "','" . $this->wpdb->escape( strip_tags( $content ) ) . "','1','http://','" . $_SERVER['HTTP_USER_AGENT'] . "')" );
				
				$item++;
			}
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Update the comment count in the database
			 * ---------------------------------------------------------------------------------------------
			 */
			$this->wpdb->query( "UPDATE " . $this->wpdb->prefix . "posts SET comment_count = '" . addslashes( $item ) . "' WHERE ID = '" . addslashes( $postID ) . "'" );
	
			return true;
		}
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * Initialize the class object when we include the file into other parts of the plugin
	 * -----------------------------------------------------------------------------------------------------
	 */
	if( isset( $youtube ) && isset( $wpdb )) {
		$comments = new RealVMS_Comments ( $youtube, $wpdb );
	}
?>