<?php
	set_time_limit( 0 );
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * This file is used for the automation process of this plugin. To use this file all you need
	 * to do is create a cron job to fire this file however often you want, however it will not do 
	 * anything if you don't login to the WP admin center click on the tab for this plugin and add in
	 * some required user settings.
	 * ------------------------------------------------------------------------------------------------------
	 * These first few lines of code are used so we can have access to the WP class by wordpress. We
	 * have to include the wp-config.php file so that we can have access to it, if we do not then the
	 * script won't be able to fetch our results, ect...
	 * ------------------------------------------------------------------------------------------------------
	*/
	include_once( str_replace ( '/wp-content/plugins/' . str_replace ( 'automate.php', '', 
	substr ( __FILE__, strpos ( __FILE__, '/wp-content/plugins/' ) + 20 ) )  . 'automate.php', 
	'', __FILE__ ) . '/wp-config.php' );
	
	include_once('includes/functions.php');
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * First get our keywords, tags, comment settings, ect... from the database, these are all of the
	 * settings which the user has specified under the "Automated Settings" tab in the plugin UI
	 * ------------------------------------------------------------------------------------------------------
	 */
	$results = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . $RealVMS->automateTable );
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * If we have entries in our database then loop through them and create the required posts otherwise
	 * this file will not do anything until the user adds there settings in the "Automated Settings" section
	 * of the plugin UI (User Interface)
	 * ------------------------------------------------------------------------------------------------------
	 */
	if( is_array( $results ) ) {
		foreach( $results as $settings ) {
			/*
			 * ----------------------------------------------------------------------------------------------
			 * The user would like to add a new video to there site so call the realvmsFetchVideo() 
			 * function which will in turn call the YouTube class which in turn will call up the YouTube 
			 * API, it will then find a unique video which has not been previously added to the website and
			 * once it has found a new video it will return the settings for that video in a 
			 * multi-dimentional array which is stored as $newVideoResults
			 * ----------------------------------------------------------------------------------------------
			 */
			$newVideoResults = realvmsFetchVideo( $settings->keyword, $settings->searchCategory,  $settings->sortBy );
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * If by chance the call to the YouTube API did not return any unique videos then simply skip
			 * this result and continue onto the next search until we have no more settings to search 
			 * through
			 * ----------------------------------------------------------------------------------------------
			 */
			if( empty( $newVideoResults ) ) {
				continue;
			}

			/*
			 * ----------------------------------------------------------------------------------------------
			 * We have now found a new video we would like to add to the website so call up the 
			 * realvmsMakeNewPost() function and add all the details of this video to our database
			 * ----------------------------------------------------------------------------------------------
			 */
			realvmsMakeNewPost( $settings->crone, $settings->crtwo, $newVideoResults, $settings->cat_id, $settings->tags, $settings->safeComments, $settings->vtags, $settings->vdes, $settings->delBookmark, $settings->favesBookmark );
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * This will put the script to sleep for a few seconds just to give it some time to fully add 
			 * the post, its just good practise so we don't clog up the database with too much data at once
			 * ----------------------------------------------------------------------------------------------
			 * In previous tests when you hit the database extremely hard without this sleep statement some
			 * of the entries don't seem to get added correctly
			 * ----------------------------------------------------------------------------------------------
			 */
			sleep( 2 );
		}
	}

	/*
	 * ------------------------------------------------------------------------------------------------------
	 * This function will take all the information from our search and it will add it all to the WP database 
	 * so it will get displayed on our website
	 * ------------------------------------------------------------------------------------------------------
	 */
	function realvmsMakeNewPost( $lowestNumComments = 0, $highestNumComments = 0, $newVideoResults, $cat_id, $tags, $removeSwearWords, $includeVideoTags, $includeVideoDescription, $addToDelicious, $addToFaves ) {
		global $wpdb, $bookmarks, $comments, $RealVMS;
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Determine if the user would like to include the video description contained for this YouTube video
		 * if they would like to then wrap it up inside of the $videoDescription variable for use later on.
		 * --------------------------------------------------------------------------------------------------
		 * If they do not want to add the description contained with this video then simply make the 
		 * $videoDescription varible empty but still set to avoid a warning in the next part
		 * --------------------------------------------------------------------------------------------------
		 */
		if( $includeVideoDescription == 1 ) {
			$videoDescription = '<p>' . $newVideoResults ['description'] . '</p>';
		}
		else
		{
			$videoDescription = '';
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Using the playURL from the YouTube API and by using the possible $videoDescription that the 
		 * user has choosen to use or not create the main content for our post
		 * --------------------------------------------------------------------------------------------------
		 */
		$videoObject = '<p style="margin:4px;"><center><object width="425" height="344"><param name="movie" value="' . $newVideoResults['playURL'] . '"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="' . $newVideoResults ['playURL'] . '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="344"></embed></object></center></p>' . $videoDescription;
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Search for a post with the same title as this one because if we find one then we will have to
		 * create a new unique title, if we don't create a new unique title then if two or more posts with 
		 * the same title gets added to the database when a user clicks on that title it will show all the
		 * posts on the same page which is not a proper way to interact with wordpress or a large amount of
		 * themes for wordpress.
		 * --------------------------------------------------------------------------------------------------
		 */
		$videoTitle = $newVideoResults ['title'];
		$findVideoTitle = $wpdb->get_var( "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_name = '" . addslashes( sanitize_title( $videoTitle ) ) . "'" );
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If we have found a title in our database then simple remove one character at a time from our 
		 * existing title until it is unique, once our title is unique we can break out of this statement
		 * --------------------------------------------------------------------------------------------------
		 */
		if( $findVideoTitle ) {
			$z = 1;
			
			while( $z < 100 ) {
				$videoTitle = substr( $videoTitle, -$z );
				
				$findVideoTitle = $wpdb->get_var( "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_name = '" . addslashes( sanitize_title( $videoTitle ) ) . "'" );
				
				if( empty( $findVideoTitle ) ) {
					break;
				}
				
				$z++;
			}
		}
		
		unset( $findVideoTitle );
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If by chance we have removed 100 characters from our title and its still not unique then use the
		 * first 20 characters from the $videoDescription variable for use in the title and then re-check 
		 * to see if the title is unique.
		 * --------------------------------------------------------------------------------------------------
		 */
		if( empty( $videoTitle ) ) {
			$videoTitle = substr( $videoDescription, 0, 20 );
			$findVideoTitleAgain = $wpdb->get_var( "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_name = '" . addslashes( sanitize_title( $videoTitle ) ) . "'" );
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If we have tried everything we could to create a new unique title and were not successful then
		 * we cannot safely add this post to the database so what we will do is add this video ID to our
		 * options table and return a false value.
		 * --------------------------------------------------------------------------------------------------
		 * You may wonder why we would enter this videoID into our table when we are not adding the post
		 * to our database and this is to ensure that this video will not be added to our database in the
		 * future or the attempt to add it cannot be made. Without adding it the next time the automate
		 * feature runs it will find the same video we couldn't add before because of the title issue, and
		 * it will just continually fail, it won't ever find any new videos, however because we added the
		 * entry to our database we are able to skip over the problem video and the automate feature will
		 * continue to work without issue.
		 * --------------------------------------------------------------------------------------------------
		 */
		if( empty( $videoTitle ) || !empty( $findVideoTitleAgain ) ) {
			$wpdb->query("INSERT INTO " . $wpdb->prefix . "realvms_options (seen,post_id,image) VALUES ('" . $wpdb->escape( $newVideoResults ['videoID'] ) . "','0','" . $wpdb->escape( $newVideoResults ['image'] ) . "')" );
			return false;
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * We are now ready to add this video to our database so add it to the post section of our database
		 * and then get the post ID which is contained in the $id variable, then we can add it to the 
		 * realvms_options table.
		 * --------------------------------------------------------------------------------------------------
		 */
		$wpdb->query( "INSERT INTO " . $wpdb->prefix . "posts (post_status,post_author,post_date,post_date_gmt,post_content,post_title,post_name,post_modified,post_modified_gmt,comment_count) VALUES ('publish','1','" . $wpdb->escape( date( "Y-m-d H:m:s" ) ) . "','" . $wpdb->escape( date( "Y-m-d H:m:s" ) ) . "','" . $wpdb->escape( $videoObject ) . "','" . $wpdb->escape( $videoTitle ) . "','" . $wpdb->escape( sanitize_title( $videoTitle ) ) . "','" . $wpdb->escape( date( "Y-m-d H:m:s" ) ) . "','" . $wpdb->escape( date( "Y-m-d H:m:s" ) ) . "','0')" );
		
		$id = mysql_insert_id ( );
		
		$wpdb->query("INSERT INTO " . $wpdb->prefix . $RealVMS->optionsTable . " (seen,post_id,image) VALUES ('" . $wpdb->escape( $newVideoResults ['videoID'] ) . "','" . $wpdb->escape( $id ) . "','" . $wpdb->escape( $newVideoResults ['image'] ) . "')" );
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Determine if the user would like to add this post to Delicious or to Faves, if they would like to
		 * automatically add it to one of these bookmarking services then get all of the information we will
		 * need and determine which services the user would like to add this post to
		 * --------------------------------------------------------------------------------------------------
		 */
		if( $addToDelicious == 1 || $addToFaves == 1 ) {
			$socialObject = get_social_object ( $id );
			$socialObject ['content'] = strip_tags( $videoDescription );
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * If the user would like to automatically bookmark this post to Delicious.com then do so now 
			 * otherwise skip this IF statement
			 * ----------------------------------------------------------------------------------------------
			 */
			if( $addToDelicious == 1 ) {
				$realvms_DelUser = get_option( 'realvms_DelUser' );
				$realvms_DelPass = get_option( 'realvms_DelPass' );

				if ( !empty( $realvms_DelUser ) && !empty( $realvms_DelPass ) ) {
					$bookmarks->delicious ( $socialObject, $realvms_DelUser, $realvms_DelPass );
				}
			}
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * If the user would like to automatically bookmark this post to Faves.com then do so now 
			 * otherwise skip this IF statement
			 * ----------------------------------------------------------------------------------------------
			 */
			if( $addToFaves == 1 ) {
				$realvms_FavesUser = get_option( 'realvms_FavesUser' );
				$realvms_FavesPass = get_option( 'realvms_FavesPass' );

				if ( !empty( $realvms_FavesUser ) && !empty( $realvms_FavesPass ) ) {
					$bookmarks->faves ( $socialObject, $realvms_FavesUser, $realvms_FavesPass );
				}
			}
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Call up the comments class and add any comments that this video may have, note that the comment
		 * class will determine if any comments should get added or not, the user has the choice not to add
		 * any comments based upon there settings
		 * --------------------------------------------------------------------------------------------------
		 */
		$comments->addComments( $newVideoResults ['videoID'], $id, $lowestNumComments, $highestNumComments, $removeSwearWords );
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Add the post taxonomy information
		 * --------------------------------------------------------------------------------------------------
		 */
		$resource = $wpdb->get_row( "SELECT term_taxonomy_id,count FROM " . $wpdb->prefix . "term_taxonomy WHERE term_id = '" . addslashes( $cat_id ) . "'" ); // Get the taxonomy id & count from the database
		
		$cat_count = $resource->count + 1;
		$tax_id = $resource->term_taxonomy_id;
		
		$wpdb->query( "UPDATE " . $wpdb->prefix . "term_taxonomy SET count = '" . addslashes( $cat_count ) . "' WHERE term_taxonomy_id = '" . addslashes( $tax_id ) . "'" );
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Now Update the term relationship table
		 * --------------------------------------------------------------------------------------------------
		 */
		$wpdb->query( "INSERT INTO " . $wpdb->prefix . "term_relationships (object_id,term_taxonomy_id) VALUES ('" . addslashes( $id ) . "','" . addslashes( $tax_id ) . "')" );
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Check to see if the user would like to add the tags which are used under the youtube video API
		 * --------------------------------------------------------------------------------------------------
		 */
		if( $includeVideoTags == 1 ) {
			$tags .= ',' . $newVideoResults ['tags'];
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If the user does want to use the tags from this video then add them to the database now, otherwise
		 * simply skip this IF statement
		 * --------------------------------------------------------------------------------------------------
		 */
		if( isset( $tags ) ) {
			$explode = explode( ',', $tags );
			foreach( $explode as $tag ) {
				/*
				 * ------------------------------------------------------------------------------------------
				 * Unset some variables here so that if we loop through again they wont have a value from
				 * the last loop which can very well have an effect on the category count and possibly other
				 * unseen elements in the plugin
				 * ------------------------------------------------------------------------------------------
				 */
				unset( $existingTerm );
				unset( $count );
				unset( $slug );
				unset( $term_id );
				unset( $taxonomy_id );
				unset( $term_taxonomy_id );
				
				/*
				 * ------------------------------------------------------------------------------------------
				 * Trim the whitespace from the tag just in case
				 * ------------------------------------------------------------------------------------------
				 */
				$tag = trim( $tag );
				
				/*
				 * ------------------------------------------------------------------------------------------
				 * If this tag has an empty value then don't add it to the database, just skip it and
				 * move onto the next entry
				 * ------------------------------------------------------------------------------------------
				 */
				if( empty( $tag ) ) {
					continue;
				}
				
				/*
				 * ------------------------------------------------------------------------------------------
				 * Check the database to see if we have an existing tag to work with
				 * ------------------------------------------------------------------------------------------
				 */
				$existingTerm = $wpdb->get_var( "SELECT term_id FROM " . $wpdb->prefix . "terms WHERE name = '" . addslashes( $tag ) . "'" );
				
				if( !empty( $existingTerm ) ) {
					/*
					 * Determine if this term is the name of our category name, if it is then we cannot add 
					 * it to our database because it will mess with our category count
					 */
					$term_taxonomy_id = $wpdb->get_var( "SELECT term_taxonomy_id FROM " . $wpdb->prefix . "term_taxonomy WHERE (term_id = '" . addslashes( $existingTerm ) . "') AND (taxonomy = 'post_tag')" );
					
					/*
					 * ------------------------------------------------------------------------------------------
					 * We already have this particular tag in the database so what we will do now is go 
					 * through the default configuration for tags, get the required term relationships
					 * add them all together and basically just increase the count by one for these ids
					 * ------------------------------------------------------------------------------------------
					 */
					
					if( $term_taxonomy_id > 0 ) {
						$wpdb->query( "INSERT INTO " . $wpdb->prefix . "term_relationships (object_id,term_taxonomy_id) VALUES ('" . addslashes( $id ) . "','" . addslashes( $term_taxonomy_id ) . "')" );
						
						$count = $wpdb->get_var( "SELECT count FROM " . $wpdb->prefix . "term_taxonomy WHERE term_taxonomy_id = '" . addslashes( $term_taxonomy_id ) . "'" );
						$count = $count + 1;
						
						$wpdb->query( "UPDATE " . $wpdb->prefix . "term_taxonomy SET count = '" . addslashes( $count ) . "' WHERE term_taxonomy_id = '" . addslashes( $term_taxonomy_id ) . "'" );
					}
				}
				else
				{
					/*
					 * ------------------------------------------------------------------------------------------
					 * This is a new tag so lets first create a URL friendly slug to use for this tag
					 * and then once we have done that we can add this tag into the database using the 
					 * default configuration for adding new tags
					 * ------------------------------------------------------------------------------------------
					 */
					$slug = sanitize_title( str_replace( ' ', '-', $tag ) );
					$wpdb->query( "INSERT INTO " . $wpdb->prefix . "terms (name,slug) VALUES ('" . addslashes( $tag ) . "','" . addslashes( $slug ) . "')" );
					
					$term_id = mysql_insert_id ( );
					
					$wpdb->query( "INSERT INTO " . $wpdb->prefix . "term_taxonomy (term_id,taxonomy,count) VALUES ('" . addslashes( $term_id ) . "','post_tag','1')" );
					
					$taxonomy_id = mysql_insert_id ( );
					
					$wpdb->query( "INSERT INTO " . $wpdb->prefix . "term_relationships (object_id,term_taxonomy_id) VALUES ('" . addslashes( $id ) . "','" . addslashes( $taxonomy_id ) . "')" );
				}
			}
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * This function doesn't actually return any values so once it gets to the end simply return
		 * a value of true.
		 * --------------------------------------------------------------------------------------------------
		 */
		return true;
	}
  
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * This function goes out and finds a new video to post on our website
	 * ------------------------------------------------------------------------------------------------------
	 */
	function realvmsFetchVideo( $searchTerm, $searchCategory, $sortBy ) {
		global $wpdb, $youtube, $RealVMS;

		/*
		 * --------------------------------------------------------------------------------------------------
		 * 	Keep going until we find a video we have not yet added or until we searched 100 pages
		 *	100 pages of results == anywhere from 3,000 to 5,000 video's depending on the source
		 * 	this should be more then enough videos for any one site however if you want more then 
		 *  simply increase the 100 value below but the higher you set it the slower it will become
		 *  over time when you get into the 5000 video range.
		 * --------------------------------------------------------------------------------------------------
		 */
		$a = 1;
		$searchIndex = 1;
		
		while( $a <= 100 ) {
			/*
			 * ----------------------------------------------------------------------------------------------
			 * Go out and get our post details
			 * ----------------------------------------------------------------------------------------------
			 */
			$realvmsVideoResults = $youtube->searchVideos( $searchTerm, $searchCategory, $sortBy, $searchIndex, 50 );
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * Loop through the video object & determine if we have added this video or not
			 * ----------------------------------------------------------------------------------------------
			 */
			$b = 0;
			
			while( $b < count( $realvmsVideoResults ) ) {
				$realvmsFindTheVideo = 0;
				
				/*
				 * ------------------------------------------------------------------------------------------
				 * Determine if we have added this video to our site
				 * ------------------------------------------------------------------------------------------
				 */
				$realvmsFindTheVideo = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . $RealVMS->optionsTable . " WHERE seen = '" . addslashes( $realvmsVideoResults [$b] ['videoID'] ) . "' LIMIT 1" );
				
				/*
				 * ------------------------------------------------------------------------------------------
				 * This video has not been added to our site, so return its object
				 * ------------------------------------------------------------------------------------------
				 */
				if( empty( $realvmsFindTheVideo ) )  {
					break 2;
				}
				
				$b++;
			}
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * Increment our $searchIndex variable by 50 because this is like saying get page 2 or page 3
			 * of the YouTube API and we will increase the $a variable so that we can eventually break out
			 * of this 100 loop.
			 * ----------------------------------------------------------------------------------------------
			 * The number of videos searched through can be determined by taking the number of results
			 * returned per page which in this case would be 50 and times it by however many times this loop
			 * will occur, in our case this loop will occur 100 times which means that we can search through
			 * 5000 videos or 100 pages of the YouTube API in order to find our next unique video.
			 * ----------------------------------------------------------------------------------------------
			 */
			$searchIndex = $searchIndex + 50;
			$a++;
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If we have found a new unique video we would like to add to our website then return the 
		 * $realvmsVideoResults array otherwise we have not found a new unique video to add to our website
		 * so simply return false
		 * --------------------------------------------------------------------------------------------------
		 */
		if( isset( $realvmsVideoResults [$b] ) ) {
			return $realvmsVideoResults [$b];
		}
		else
		{
			return false;
		}
	}
?>
