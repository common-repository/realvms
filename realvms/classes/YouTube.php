<?php
	class RealVMS_YouTube {
		
		var $wpdb;
		
		/*
		 * ------------------------------------------------------------------------------------------------
		 * PHP4 compatibility layer for calling the PHP5 constructor.
		 * ------------------------------------------------------------------------------------------------
		 */
		function RealVMS_YouTube( $wpdb ) {
			$this->wpdb = $wpdb;
		}
		
		/*
		 * ------------------------------------------------------------------------------------------------
		 * PHP5 style destructor and will run when object is destroyed.
		 * ------------------------------------------------------------------------------------------------
		 */
		function __destruct() {
			return true;
		}
		
	    /*
	     * This function is used to search videos using the YouTube API.
	     * ------------------------------------------------------------------------------------------------
	     * PARAMATER REFERENCE
	     * ------------------------------------------------------------------------------------------------
	     * $searchTerm == The search term (A keyword like 'Cars' will return videos about 'Cars') 
	     * The $searchTerm value is the only required paramater in this function
	     * ------------------------------------------------------------------------------------------------
	     * $searchIndex == The page you want the results from, the API will only let you have 50 videos
	     * per page and there can be a thousand videos which means you have to cycle through the page
	     * you want your videos from
	     * ------------------------------------------------------------------------------------------------
	     * $numResultsToDisplay == The number of videos you want returned, this value is default to 24 but 
	     * this value can be between 1 and 50
	     * ------------------------------------------------------------------------------------------------
	     * $category == The specific youtube category you would like to search in
	     * ------------------------------------------------------------------------------------------------
	     * $orderBy == The specific order you want the video results returned by, there are only four values
	     * for this paramater and they are relevance, published, viewCount, and rating
	     * ------------------------------------------------------------------------------------------------
	     * $wpdb == An object reference to the actual $wpdb Class used by wordpress
	     * ------------------------------------------------------------------------------------------------
	     */
	    function searchVideos ( $searchTerm, $category = 'All', $orderBy = 'relevance', $searchIndex = 1, $numResultsToDisplay = 18 ) {      
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Format the search term to conform to the youtube api - Basically we only need to convert all
			 * spaces to a plus sign
			 * ---------------------------------------------------------------------------------------------
			 */
			$searchTerm = str_replace( ' ', '+', $searchTerm );
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Wrap the rest of this function in a loop because at this point we may need to make several API
			 * calls, we don't want to return any videos which have already been added to the website and we
			 * may have to loop through several pages of results in order to find the max number of results to
			 * display.
			 * ---------------------------------------------------------------------------------------------
			 */
			$videoCount = 0;
			$a = -1;
			$timesAround = 0;
			
			while( $videoCount < $numResultsToDisplay ) {
				/*
				 * -----------------------------------------------------------------------------------------
				 * Format the url so we can fetch the video results from the YouTube API, please refer to the below
				 * link for a list of paramaters this URL can take and to help understand which paramaters are 
				 * currently used
				 * -----------------------------------------------------------------------------------------
				 * http://code.google.com/apis/youtube/2.0/reference.html#Query_parameter_definitions
				 * -----------------------------------------------------------------------------------------
				 */
				$url = 'http://gdata.youtube.com/feeds/api/videos?q=' . $searchTerm . '&start-index=' . $searchIndex . '&max-results=' . $numResultsToDisplay . '&category=' . $category . '&format=5&orderby=' . $orderBy . '&restriction=' . $_SERVER['REMOTE_ADDR'];

				/*
				 * -----------------------------------------------------------------------------------------
				 * Go out and get the XML file for this API call, as you can tell there is a much easier and better
				 * way to do the below code with PHP 5 and simplexml but since I had to make this compatible with PHP
				 * version 4.3 we have to use the following method.
				 * -----------------------------------------------------------------------------------------
				 */    
				$object = $this->get_file_contents( $url );
				$p = xml_parser_create();
				
				/*
				 * -----------------------------------------------------------------------------------------
				 * Define these two variables just to avoid a PHP warning (This warning is not usually displayed to 
				 * the user but may appear in your log files - Depending on your servers PHP configuration)
				 * -----------------------------------------------------------------------------------------
				 */
				$vals = '';
				$index = '';
				
				/*
				 * -----------------------------------------------------------------------------------------
				 * Structure the XML file into a PHP array
				 * -----------------------------------------------------------------------------------------
				 */
				xml_parse_into_struct( $p, $object, $vals, $index );
				xml_parser_free( $p );
	
				/*
				 * -----------------------------------------------------------------------------------------
				 * Loop through the search results and get the required information from the YouTube API
				 * -----------------------------------------------------------------------------------------
				 */
				$close = 0;

				if(is_array( $vals )) {
					foreach( $vals as $entry ) {
						/*
						 * ---------------------------------------------------------------------------------
						 * Get the total results returned by the YouTube API
						 * ---------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'OPENSEARCH:TOTALRESULTS' ) {
							$totalResults = $entry ['value'];
							
							/*
							 * --------------------------------------------------------------------------
							 * If there are no results returned by the API call then simple break out
							 * of both loops so that we don't get caught in an everlasting loop which
							 * does and has caused problems in the past
							 * --------------------------------------------------------------------------
							 */
							if( $totalResults <= 0 ) {
								break 2;
							}
							
							/*
							 * --------------------------------------------------------------------------
							 * If there are less results then the total amount we need to return then 
							 * simply make the max return value equal to the total results
							 * --------------------------------------------------------------------------
							 */
							if( $totalResults < $numResultsToDisplay ) {
								$numResultsToDisplay = $totalResults;
							}
						}
						
						/*
						 * ---------------------------------------------------------------------------------
						 * Get the title associated with this video
						 * ---------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'MEDIA:TITLE' ) {
							$title = $entry ['value'];
						}
						
						/*
						 * ---------------------------------------------------------------------------------
						 * Get the description associated with this video
						 * ---------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'MEDIA:DESCRIPTION' ) {
							$description = $entry ['value'];
						}
						
						/*
						 * ---------------------------------------------------------------------------------
						 * Get any tags associated with this video
						 * ---------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'MEDIA:KEYWORDS' ) {
							$tags = $entry ['value'];
						}
						
						/*
						 * ---------------------------------------------------------------------------------
						 * Get the play URL associated with this video
						 * ---------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'MEDIA:CONTENT' ) {
							$arr = $entry ['attributes'];
							
							if( $arr ['ISDEFAULT'] == 'true' ) {
								$playURL = $arr ['URL'];
								
								/*
								 * --------------------------------------------------------------------------
								 * Get the Video ID associated with this video
								 * --------------------------------------------------------------------------
								 */
								$match = array ( );
								preg_match( '/v\/(.*?)&/', $playURL, $match );
								
								if( empty( $match [1] ) ) {
									preg_match( '/v\/(.*?)/', $playURL, $match );
								}
								
								$videoID = $match [1];
								
								/*
								 * --------------------------------------------------------------------------
								 * If we still don't have a videoID for this post then just continue onto the
								 * next result
								 * --------------------------------------------------------------------------
								 */
								if( empty( $videoID ) ) {
									continue;
								}
							}
						}
	
						/*
						 * --------------------------------------------------------------------------------
						 * Get a thumbnail image associated with this video
						 * --------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'MEDIA:THUMBNAIL' ) {
							$arr = $entry ['attributes'];
							
							$image = $arr ['URL'];
						}
						
						if( $entry ['tag'] == 'ENTRY' && $entry ['type'] == 'open' ) {
							$close = 0;
						}
						
						if( $entry ['tag'] == 'ENTRY' && $entry ['type'] == 'close' ) {
						  	$close = 1;
						}

						if( isset( $image ) && isset( $videoID ) && isset( $playURL ) && isset( $title ) && $close == 1 ) {
							/*
							 * --------------------------------------------------------------------------
							 * Check to see if this videoID is in the database and if it is then continue
							 * onto the next result because we don't want to display videos to the user that
							 * we have already added to our database
							 * --------------------------------------------------------------------------
							 */
							$checkVideoID = $this->wpdb->get_var( "SELECT id FROM " . $this->wpdb->prefix . "realvms_options WHERE seen = '" . $this->wpdb->escape( $videoID ) . "'" );
	
							if( empty( $checkVideoID ) ) {
								$a++;
								
								$videoObject [$a] ['videoID'] = $videoID;
								$videoObject [$a] ['image'] = $image;
								$videoObject [$a] ['playURL'] = $playURL;
								$videoObject [$a] ['title'] = $title;
								$videoObject [$a] ['description'] = $description;
								$videoObject [$a] ['tags'] = $tags;
								
								$videoCount = count( $videoObject );

								if( $videoCount == $numResultsToDisplay ) {
									break 2;
								}
							}
							
							/*
							 * --------------------------------------------------------------------------
							 * Unset a couple values here just so we don't get mixed up with any duplicates
							 * --------------------------------------------------------------------------
							 */
							unset( $title );
							unset( $description );
							unset( $tags );
							unset( $playURL );
							unset( $videoID );
							unset( $image );
						}
						
						$timesAround++;
					}
				}
				
				if( $timesAround >= $totalResults ) {
					break;
				}
				
				$videoCount = count( $videoObject );
			}
			
			return $videoObject;
	    }
	    
	    /* 
	     * -------------------------------------------------------------------------------------------------
	     * This function is used to return an array of comments based upon the number of comments the
	     * user wants, the video id so we know which video to get the comments from, and also if we want
	     * to remove any swear words from the comments
	     * -------------------------------------------------------------------------------------------------
	     * PARAMATER REFERENCE
	     * -------------------------------------------------------------------------------------------------
	     * $video_id == The video we want the comments for
	     * -------------------------------------------------------------------------------------------------
	     * $com1 == The lowest amount of comments we want
	     * -------------------------------------------------------------------------------------------------
	     * $com2 == The highest amount of comments we want
	     * -------------------------------------------------------------------------------------------------
	     * $safeComments == 0 if we want to keep any swear words and 1 if we want to remove them
	     * -------------------------------------------------------------------------------------------------
	    */
	    function getComments ( $video_id, $lowestNumComments = 0, $highestNumComments = 0 ) {
			if( $lowestNumComments == 0 && $highestNumComments == 0 || empty( $lowestNumComments ) && empty( $highestNumComments ) ) {
				return false;
			}
			
			/*
			 * -------------------------------------------------------------------------------------------
			 * Determine the max number of comments to get
			 * -------------------------------------------------------------------------------------------
			 */
			if( $lowestNumComments > $highestNumComments ) {
				$max_comments = $highestNumComments - $lowestNumComments;
				
				if( $max_comments <= 0 ) {
					$max_comments = 40;
				}
			}
			else
			{
				$max_comments = rand( $lowestNumComments, $highestNumComments );
			}
			
			if( $lowestNumComments == 0 && $highestNumComments == 0 ) {
				return false;
			}
			
			/*
			 * -------------------------------------------------------------------------------------------
			 * Try to determine the number of pages to get, the default is 50 which is about 900 comments
			 * -------------------------------------------------------------------------------------------
			 */
			$x = 0;
			$start_index = 1;
			
			if( $max_comments && $max_comments > 50 ) {
				$pages = round( $max_comments / 50 ) + 1;
			}
			elseif( $max_comments && $max_comments < 50 )
			{
				$pages = 1;
			}
			else
			{
				$pages = 50;
			}
			
			/*
			 * -------------------------------------------------------------------------------------------
			 * Loop through and get all of our comments
			 * -------------------------------------------------------------------------------------------
			 */
			$index = 0;
			$found = 0;
			
			while( $x < $pages ) {
				$link = 'http://gdata.youtube.com/feeds/api/videos/' . $video_id . '/comments?max-results=50&start-index=' . $start_index;
				
				$object = $this->get_file_contents ( $link );
				$p = xml_parser_create ( );
				
				$vals = '';
				$indexs = '';
				
				xml_parse_into_struct ( $p, $object, $vals, $indexs );
				xml_parser_free ( $p );
			
				/*
				 * -------------------------------------------------------------------------------------
				 * If we have found some comments for this video then do this
				 * -------------------------------------------------------------------------------------
				 */
				if( is_array( $vals ) ) {
					foreach( $vals as $entry ) {
						/*
						 * -------------------------------------------------------------------------------
						 * Set some default values
						 * -------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'ENTRY' && $entry ['type'] == 'open' ) {
							$found = 0;
							
							unset( $c_text );
							unset( $a_name );
						}
						
						/*
						 * -------------------------------------------------------------------------------
						 * Get the comment text
						 * -------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'CONTENT' ) {
							$c_text = $entry ['value'];
						}
						
						/*
						 * -------------------------------------------------------------------------------
						 * Get the author name
						 * -------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'NAME' ) {
							$a_name = $entry ['value'];
						}
						
						/*
						 * -------------------------------------------------------------------------------
						 * Do we save this info or not?
						 * -------------------------------------------------------------------------------
						 */
						if( $entry ['tag'] == 'LINK' ) {
							$arr = $entry ['attributes'];
							$l_name = $arr ['REL'];
							
							if( strpos( $l_name, '#in-reply-to' ) === true ) {
								$found = 1;
							}
						}
						
						/*
						 * -------------------------------------------------------------------------------
						 * If this comment is not a reply to another comment then keep it
						 * -------------------------------------------------------------------------------
						 */
						if( $found == 0 && isset($a_name) && isset($c_text) ) {
							if( strlen( trim( $c_text ) ) > 3 ) {
								$author = (string)$a_name;
								$comment [$author] = $c_text;
								
								$index++;
							}
						}
						
						$comCount = count( $comment );
						
						/*
						 * -------------------------------------------------------------------------------
						 * If we have reached the max number of comments this user specified then break out 
						 * of the loop
						 * -------------------------------------------------------------------------------
						 */
						if( $max_comments == $comCount ) {
							break 2;
						}
					}
				}
				
				/*
				 * -------------------------------------------------------------------------------------
				 * We can get 50 comments per page so increment accordingly
				 * -------------------------------------------------------------------------------------
				 */
				$start_index = $start_index + 50;
				$x++;
			}
			
			/*
			 * -------------------------------------------------------------------------------------------
			 * return the comments we did or did not find.
			 * -------------------------------------------------------------------------------------------
			 */
			if( $comCount > 0 ) {
				$commentObject ['commentArray'] = $comment;
				
				return $commentObject;
			}
			else
			{
				return false;
			}
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
	}
  	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * Initialize the class object when we include the file into other parts of the plugin
	 * -----------------------------------------------------------------------------------------------------
	 */
	if( isset( $wpdb ) ) {
		$youtube = new RealVMS_YouTube ( $wpdb );
	}
?>
