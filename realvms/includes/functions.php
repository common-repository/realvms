<?php
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * Include all of our class files here
	 * -----------------------------------------------------------------------------------------------------
	 */
	include_once( str_replace ( '/includes/functions.php', '', __FILE__ ) . '/classes/YouTube.php' );
	include_once( str_replace ( '/includes/functions.php', '', __FILE__ ) . '/classes/Bookmarks.php' );
	include_once( str_replace ( '/includes/functions.php', '', __FILE__ ) . '/classes/Comments.php' );
	include_once( str_replace ( '/includes/functions.php', '', __FILE__ ) . '/classes/Realvms.php' );

	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This function is used to search for videos using the youtube api, the videos returned are based upon 
	 * the search term the user enters to perform there search criteria, the user can additionally specify
	 * exactly which category and by which search crieria (Views, Rating, ect) there results will be 
	 * returned for.
	 * -----------------------------------------------------------------------------------------------------
	 */
	function realvmsSearchVideos ( $searchTerm, $category, $orderBy, $searchIndex = 1, $numResultsToDisplay = 18 ) {
		global $youtube;
		
		if( empty( $searchTerm ) ) {
			return 'invalid_search_term';
		}
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Perform a search via the youtube api and display back out results
		 * -------------------------------------------------------------------------------------------------
		 */
		$videoResults = $youtube->searchVideos ( $searchTerm, $category, $orderBy, $searchIndex, $numResultsToDisplay );

		/*
		 * -------------------------------------------------------------------------------------------------
		 * Count how many videos were returned, if there are none then show the message saying no videos 
		 * have been found
		 * -------------------------------------------------------------------------------------------------
		 */
		if( count( $videoResults ) <= 0 ) {
			
			return false;
			
		}
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Format the reults into an HTML structure to display on the other page
		 * -------------------------------------------------------------------------------------------------
		 */
		$formattedResults = realvmsFormatVideoResults ( $videoResults, $numResultsToDisplay, $searchIndex, $searchTerm, $category, $orderBy );
		
		return $formattedResults;
	}
 
	/* 
	 * -----------------------------------------------------------------------------------------------------
	 * This function is used to format the search results for use with this specific plugin. It 
	 * basically takes the information and creates the HTML to place on the page
	 * -----------------------------------------------------------------------------------------------------
	 */
	function realvmsFormatVideoResults ( $videoResults, $numResultsToDisplay, $searchIndex, $searchTerm, $category, $orderBy) {
		global $RealVMS;

		/*
		 * -------------------------------------------------------------------------------------------------
		 * Loop through all of our video results and create our HTML table to show the user
		 * -------------------------------------------------------------------------------------------------
		 */
		$a = 0;
		$b = 0;
		
		$html = '<table cellpadding="0" cellspacing="4" border="0">';
		
		if( is_array( $videoResults ) ) {
			foreach( $videoResults as $result ) {
				if( $b == 0 ) {
					$html .= '<tr>';
				}

				/*
				 * -----------------------------------------------------------------------------------------
				 * Get the required values we will need to use if the user adds the video to there site
				 * -----------------------------------------------------------------------------------------
				 */
				$videoURL = $result ['playURL'];
				$videoTitle = $result ['title'];
				$videoDescription = strip_tags( $result['description'] );
				$videoTags = $result ['tags'];
				$videoID = $result['videoID'];
				$videoImage = $result['image'];

				/*
				 * -----------------------------------------------------------------------------------------
				 * Create the first part of this cell and the first part of the form
				 * -----------------------------------------------------------------------------------------
				 */

				$html .= <<<EOD
				<td style="border:1px solid #ccc;">
					<table cellpadding="2" cellspacing="2" border="0">
						<tr>
							<td>
								<a href="javascript:void();" title="$videoTitle" style="cursor:default;">
									<img src="$videoImage" alt="$videoTitle" height="100px" width="125px" />
								</a>
							</td>
						</tr>
						<tr>
							<td style="padding-left:4px;padding-bottom:4px;">
								<form name="frm$a" action="$RealVMS->httpLocation/includes/addSession.php" method="post">
									<input type="hidden" name="createSession" value="1" />
									<input type="hidden" name="videoURL" value="$videoURL" />
									<input type="hidden" name="videoID" value="$videoID" />
									<input type="hidden" name="videoTags" value="$videoTags" />
									<input type="hidden" name="videoDescription" value="$videoDescription" />
									<input type="hidden" name="videoTitle" value="$videoTitle" />
									<input type="hidden" name="videoImage" value="$videoImage" />
									
									<center>
										<input type="submit" value="Add Video" onclick="javascript:frm$a.submit();" class="button-secondary action" />
									</center>
								</form>
							</td>
						</tr>
						<tr>
							<td style="padding-left:4px;">
								<center>
									<input type="submit" value="Preview Video" onclick="previewVideo('$a','$videoURL');" class="button-secondary action" />
								</center>
							</td>
						</tr>
					</table>
				</td>
EOD;
				$b++;
				
				/*
				 * -----------------------------------------------------------------------------------------
				 * If we have displayed 7 results in this row then start a new row
				 * -----------------------------------------------------------------------------------------
				 */
				if( $b == 6 ) {
					$b = 0;
					$html .= '</tr>';
				}
				
				$a++;
			}
		}
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * If we ended before we reached 7 results for this row make sure we close the table row
		 * -------------------------------------------------------------------------------------------------
		 */
		if( $b < 6 ) {
			$html .= '</tr>';
		}
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Close off our table and the paragraph tag around it
		 * -------------------------------------------------------------------------------------------------
		 */
		$html .= '</table></p>';

		/*
		 * -------------------------------------------------------------------------------------------------
		 * The below section is now used as a paging system for our video search page
		 * -------------------------------------------------------------------------------------------------
		 * If this is our first page of results and we have a complete set of results then show a next link
		 * -------------------------------------------------------------------------------------------------
		 */
		if( $searchIndex == 1 && count( $videoResults ) == $numResultsToDisplay ) {
			$searchIndex = $searchIndex + $numResultsToDisplay;
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the first part of our div tag and our form element
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<div style="clear:both;margin-top:10px;">'.
			'<span style="clear:none;float:right;margin-right:15px;">'.
			'<form action="' . $RealVMS->actionRequest . '" method="POST">';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create all of the hidden elements in our form
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<input type="hidden" name="realvmsSearchTerm" value="' . $searchTerm . '" />'.
			'<input type="hidden" name="realvmsSearchIndex" value="' . $searchIndex . '" />'.
			'<input type="hidden" name="realvmsCategory" value="' . $category . '" />'.
			'<input type="hidden" name="realvmsVideoOrder" value="' . $orderBy . '" />';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the submit button and close off our form and other tags
			 * ---------------------------------------------------------------------------------------------
			 */
			
			$html .= '<input type="submit" value="Next" name="realvmsSearchVideos" class="button-secondary action" /></form></span></div><br /><br />';
		}
		elseif( $searchIndex > 1 && count( $videoResults ) == $numResultsToDisplay ) {
			/*
			 * ---------------------------------------------------------------------------------------------
			 * If this is our second or more page of results and we still have a complete 28 of them then 
			 * show a previous and next link
			 * ---------------------------------------------------------------------------------------------
			 */
			$next = $searchIndex + $numResultsToDisplay;
			$prev = $searchIndex - $numResultsToDisplay;
			
			if( $prev < 1 ) {
				$prev = 1;
			}
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the first part of our div tag and our form element
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<div style="clear:both;margin-top:10px;">'.
			'<span style="clear:none;float:left;margin-left:15px;">'.
			'<form action="' . $RealVMS->actionRequest . '" method="POST">';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create all of the hidden elements in our form
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<input type="hidden" name="realvmsSearchTerm" value="' . $searchTerm . '" />'.
			'<input type="hidden" name="realvmsSearchIndex" value="' . $prev . '" />'.
			'<input type="hidden" name="realvmsCategory" value="' . $category . '" />'.
			'<input type="hidden" name="realvmsVideoOrder" value="' . $orderBy . '" />';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the submit button and close off our form and other tags
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<input type="submit" value="Previous" name="realvmsSearchVideos" class="button-secondary action" /></form></span>';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the first part of our div tag and our form element
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<span style="clear:none;float:right;margin-right:15px;">'.
			'<form action="' . $RealVMS->actionRequest . '" method="POST">';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create all of the hidden elements in our form
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<input type="hidden" name="realvmsSearchTerm" value="' . $searchTerm . '" />'.
			'<input type="hidden" name="realvmsSearchIndex" value="' . $next . '" />'.
			'<input type="hidden" name="realvmsCategory" value="' . $category . '" />'.
			'<input type="hidden" name="realvmsVideoOrder" value="' . $orderBy . '" />';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the submit button and close off our form and other tags
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<input type="submit" value="Next" name="realvmsSearchVideos" class="button-secondary action" /></form></span></div><br /><br />';
		}
		elseif( $searchIndex > 1 && count( $videoResults ) < $numResultsToDisplay ) {
			/*
			 * ---------------------------------------------------------------------------------------------
			 * If this is our second or more page of results and we have less then 28 results then there is 
			 * no need to show a next link instead all we need to show is our previous link
			 * ---------------------------------------------------------------------------------------------
			 */
			$prev = $searchIndex - $numResultsToDisplay;
			
			if( $prev < 1 ) {
				$prev = 1;
			}
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the first part of our div tag and our form element
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<div style="clear:both;margin-top:10px;">'.
			'<span style="clear:none;float:left;margin-left:15px;">'.
			'<form action="' . $RealVMS->actionRequest . '" method="POST">';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create all of the hidden elements in our form
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<input type="hidden" name="realvmsSearchTerm" value="' . $searchTerm . '" />'.
			'<input type="hidden" name="realvmsSearchIndex" value="' . $prev . '" />'.
			'<input type="hidden" name="realvmsCategory" value="' . $category . '" />'.
			'<input type="hidden" name="realvmsVideoOrder" value="' . $orderBy . '" />';
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Create the submit button and close off our form and other tags
			 * ---------------------------------------------------------------------------------------------
			 */
			$html .= '<input type="submit" value="Previous" name="realvmsSearchVideos" class="button-secondary action" /></form></span></div><br /><br />';
		}
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Return our formatted results
		 * -------------------------------------------------------------------------------------------------
		 */
		return $html;
	}
 
	function get_social_object($postID) {
		global $wpdb, $RealVMS;
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * First determine the URL structure for this site
		 * -------------------------------------------------------------------------------------------------
		 */
		$permalink_structure = get_option( 'permalink_structure' );
		$socialObject ['link_structure'] = $permalink_structure;
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Get the details of this post
		 * -------------------------------------------------------------------------------------------------
		 */
		$postDetails = $wpdb->get_results( "SELECT post_title,post_date,post_name,ID,post_category,post_author FROM " . $wpdb->prefix . "posts WHERE ID = '" . addslashes( $postID ) . "' LIMIT 1" );
		
		$postDetails = $postDetails [0];
		$socialObject ['title'] = $postDetails->post_title;
		$socialObject ['date'] = $postDetails->post_date;
		$socialObject ['name'] = $postDetails->post_name;
		$socialObject ['ID'] = $postDetails->ID;
		$socialObject ['domain'] = $RealVMS->domainName;
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Get the category name & the author name
		 * -------------------------------------------------------------------------------------------------
		 */
		$tax_id = $wpdb->get_var( "SELECT term_taxonomy_id FROM " . $wpdb->prefix . "term_relationships WHERE object_id = '" . addslashes( $postID ) . "'");
		
		$term_id = $wpdb->get_var( "SELECT term_id FROM " . $wpdb->prefix . "term_taxonomy WHERE term_taxonomy_id = '" . addslashes( $tax_id ) . "'");
		
		$cat_slug = $wpdb->get_var( "SELECT slug FROM " . $wpdb->prefix . "terms WHERE term_id = '" . addslashes( $term_id ) . "'");
		
		$author_name = $wpdb->get_var( "SELECT user_nicename FROM " . $wpdb->prefix . "users WHERE ID = '" . addslashes( $postDetails->post_author ) . "'");
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Form any tags this post has
		 * -------------------------------------------------------------------------------------------------
		 */
		$term_ids = $wpdb->get_results( "SELECT term_taxonomy_id FROM " . $wpdb->prefix . "term_relationships WHERE object_id = '" . addslashes( $postID ) . "'");
		
		if($term_ids) {
			foreach($term_ids as $tax_ids) {
				$term_id = $wpdb->get_var( "SELECT term_id FROM " . $wpdb->prefix . "term_taxonomy WHERE term_taxonomy_id = '" . addslashes( $tax_ids->term_taxonomy_id ) . "'");
				
				$tags .= ' '.$wpdb->get_var( "SELECT name FROM " . $wpdb->prefix . "terms WHERE term_id = '" . addslashes( $term_id ) . "'");
			}
		}
		
		$socialObject ['cat_slug'] = $cat_slug;
		$socialObject ['author_name'] = $author_name;
		$socialObject ['tags'] = $tags;
		
		return $socialObject;
	}
	
	/*
	 * -------------------------------------------------------------------------------------------------
	 * CURL alternative to file_get_contents();
	 * -------------------------------------------------------------------------------------------------
	 * This is used in case some hosts will not allow the use of the file_get_contents function
	 * -------------------------------------------------------------------------------------------------
	 */
	function get_file_contents( $url )
	{
		if ( !function_exists( 'file_get_contents' ) || ini_get( 'allow_url_fopen' ) == 0 || ini_get( 'allow_url_fopen' ) == 'off' || ini_get( 'allow_url_fopen' ) == '0' ) {
		    $c = curl_init ( );
		    curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
		    curl_setopt( $c, CURLOPT_URL, $url );
		    $contents = curl_exec( $c );
		    curl_close( $c );
		
		    return $contents;
		}
		else {
			$contents = @file_get_contents( $url );
			
			return $contents;
		}
	}
	
	function automationEvent() {
		global $RealVMS;
		
		$url = $RealVMS->httpLocation.'/automate.php';
		
		get_file_contents( $url );
	}
	
	function rvmsnls2p($str)
	{
	  return str_replace('<p></p>', '', '<p>'
	        . preg_replace('#([\r\n]\s*?[\r\n]){2,}#', '</p>$0<p>', $str)
	        . '</p>');
	}
?>
