<?php

	/*
	 * -----------------------------------------------------------------------------------------------------
	 * Plugin Name: RealVMS
	 * -----------------------------------------------------------------------------------------------------
	 * Description: Quickly and easily add videos to your website without hassle.
	 * -----------------------------------------------------------------------------------------------------
	 * Version: 1.4.2
	 * -----------------------------------------------------------------------------------------------------
	 * Author: RealVMS
	 * -----------------------------------------------------------------------------------------------------
	 * Author URI: http://www.realvms.com
	 * -----------------------------------------------------------------------------------------------------
	 * Include our functions file which will include all of our classes and required functions
	 * Its important to keep this include statement below the above file system code otherwise we will may
	 * run into a few unexpected issues with the globals.
	 * -----------------------------------------------------------------------------------------------------
	 */
	include_once ('includes/functions.php');
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * When this plugin gets activated run the installation() function	
	 * -----------------------------------------------------------------------------------------------------
	 */
	register_activation_hook ( __FILE__, 'installation' );
	
	/*
	 * --------------------------------------------------------------------------------------------------
	 * This function gets called when the plugin gets activated from the users Wordpress Admin Panel
	 * --------------------------------------------------------------------------------------------------
	 * The purpose of this function is to install and/or update the database information used by the
	 * plugin.
	 * --------------------------------------------------------------------------------------------------
	 */
	function installation() {
		global $wpdb, $RealVMS;
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Check to see if the realvms_options table has been created, if its not then create it
		 * ----------------------------------------------------------------------------------------------
		 */		
		$tname = $wpdb->prefix . 'realvms_options';
		if ($wpdb->get_var ( "SHOW TABLES LIKE '$tname'" ) != $tname) {
			$sql = "CREATE TABLE " . $tname . " (
	                  id bigint(20) NOT NULL AUTO_INCREMENT,
	              	  seen text NOT NULL,
	              	  image text NOT NULL,
	              	  post_id bigint(20) NOT NULL,
	              	  UNIQUE KEY id (id)
	              );";
			$wpdb->query ( $sql );
		}
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Check to see if the realvms_automate table has been created, if its not then create it
		 * ----------------------------------------------------------------------------------------------
		 */
		$tname = $wpdb->prefix . 'realvms_automate';
		if ($wpdb->get_var ( "SHOW TABLES LIKE '$tname'" ) != $tname) {
			$sql = "CREATE TABLE " . $tname . " (
	                  id bigint(20) NOT NULL AUTO_INCREMENT,
	              	  keyword text NOT NULL,
	              	  searchCategory text NOT NULL,
	              	  crone bigint(10) NOT NULL,
	              	  sortBy varchar(255) NOT NULL,
	              	  crtwo bigint(10) NOT NULL,
	              	  delBookmark int(1) NOT NULL,
	              	  favesBookmark int(1) NOT NULL,
	              	  tags text NOT NULL,
	              	  safeComments tinyint(1) NOT NULL,
	              	  cat_id bigint(20) NOT NULL,
	              	  vtags tinyint(1) NOT NULL,
	              	  vdes tinyint(1) NOT NULL,
	              	  UNIQUE KEY id (id)
	              );";
			$wpdb->query ( $sql );
		}
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * If the user was using a version 1.1 or less then update the database tables - if they have
		 * a version higher then this one, then these values won't toss an error, simply nothing will
		 * happen.
		 * ----------------------------------------------------------------------------------------------
		 */
		$tname = $wpdb->prefix . 'realvms_automate';
		$sql = "ALTER TABLE $tname DROP source";
		$wpdb->query ( $sql );
		
		$tname = $wpdb->prefix . 'realvms_automate';
		$sql = "ALTER TABLE $tname DROP cat_id";
		$wpdb->query ( $sql );
		
		$sql = "ALTER TABLE $tname ADD cat_id bigint(20) NOT NULL";
		$wpdb->query ( $sql );
		
		$sql = "ALTER TABLE $tname ADD searchCategory TEXT NOT NULL";
		$wpdb->query ( $sql );
		
		$sql = "ALTER TABLE $tname ADD sortBy varchar(255) NOT NULL";
		$wpdb->query ( $sql );
		
		$sql = "ALTER TABLE $tname ADD delBookmark int(1) NOT NULL";
		$wpdb->query ( $sql );
		
		$sql = "ALTER TABLE $tname ADD favesBookmark int(1) NOT NULL";
		$wpdb->query ( $sql );
		
		$tname = $wpdb->prefix . 'realvms_api_settings';
		$sql = "DROP TABLE $tname;";
		$wpdb->query ( $sql );
		
		$tname = $wpdb->prefix . 'realvms_social_services';
		$sql = "DROP TABLE $tname;";
		$wpdb->query ( $sql );
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This gets called when the plugin gets deactivated
	 * -----------------------------------------------------------------------------------------------------
	 */
	register_deactivation_hook(__FILE__, 'realvmsPluginDeactivation');
	
	function realvmsPluginDeactivation() {
		global $wpdb;
		
		$removeBookmarks = get_option ( 'realvms_DeleteSocialBookmarks' );
		$removeEverything = get_option ( 'realvms_removeEverything' );
		
		if( $removeBookmarks == 1 ) {
			delete_option ( 'realvms_DelUser' );
			delete_option ( 'realvms_DelPass' );
			delete_option ( 'realvms_FavesUser' );
			delete_option ( 'realvms_FavesPass' );
			delete_option ( 'realvms_DeleteSocialBookmarks' );
		}
		
		/*
		 * Remove Everything Associated With the Realvms Plugin
		 */
		if( $removeEverything == 1 ) {
			delete_option ( 'realvms_DelUser' );
			delete_option ( 'realvms_DelPass' );
			delete_option ( 'realvms_FavesUser' );
			delete_option ( 'realvms_FavesPass' );
			delete_option ( 'realvms_DeleteSocialBookmarks' );
			delete_option ( 'vms_showImage' );
			delete_option ( 'realvms_DeleteSocialBookmarks' );
			delete_option ( 'realvms_removeEverything' );
			
			$tname = $wpdb->prefix . 'realvms_automate';
			$sql = "DROP TABLE $tname;";
			$wpdb->query ( $sql );
			
			$tname = $wpdb->prefix . 'realvms_options';
			$sql = "DROP TABLE $tname;";
			$wpdb->query ( $sql );
		}

		delete_option ( 'realvmsVideoWidth' );
		delete_option ( 'realvmsVideoHeight' );
		delete_option ( 'vms_clickImage' );
		
		wp_clear_scheduled_hook('realAutomation');
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This action is used to add a items to the main tab for this plugin
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_action ( 'admin_menu', 'addMenuItems' );
	
	function addMenuItems() {
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Add the main menu for this plugin to the Wordpress Admin Panel
		 * ----------------------------------------------------------------------------------------------
		 */
		add_menu_page ( __ ( 'RealVMS', 'RealVMS Options' ), __ ( 'RealVMS', 'RealVMS Options' ), 8, 
		__FILE__, 'addVideos' );
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Add a sub-menu called Add Videos
		 * ----------------------------------------------------------------------------------------------
		 */
		add_submenu_page ( __FILE__, 'Add Videos', 'Add Videos', 8, __FILE__, 'addVideos' );
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Add a sub-menu called Automation
		 * ----------------------------------------------------------------------------------------------
		 */
		add_submenu_page ( __FILE__, 'Automation', 'Automation', 8, 'automation.php', 'automation' );
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Add a sub-menu called Social Bookmarks
		 * ----------------------------------------------------------------------------------------------
		 */
		add_submenu_page ( __FILE__, 'Social Bookmarks', 'Social Bookmarks', 8, 'socialBookmarks.php', 
		'socialBookmarks' );
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Add a sub-menu called General Settings
		 * ----------------------------------------------------------------------------------------------
		 */
		add_submenu_page ( __FILE__, 'General Settings', 'General Settings', 8, 'generalSettings.php', 
		'generalSettings' );
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This action is used to insert script tags into the admin section
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_action ( 'admin_print_scripts', 'addScriptTags' );
	
	function addScriptTags() {
		global $RealVMS;
		
		echo <<<EOD
				<script type='text/javascript'>
					var httpLocation = '$RealVMS->httpLocation';
				</script>
				<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
				<script type="text/javascript" src="$RealVMS->httpLocation/js/main.js"></script>
EOD;
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This action is used when a post gets removed
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_action ( 'delete_post', 'removedPost' );
	
	function removedPost( $postID ) {
		global $wpdb, $RealVMS;
		
		$wpdb->query ( "DELETE FROM " . $wpdb->prefix . $RealVMS->optionsTable . " WHERE post_id = '" . addslashes( $postID ) . "' LIMIT 1" );
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This gets fired when a post is published
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_action ( 'publish_post', 'publishedPost' );
	
	function publishedPost( $postID ) {
		global $wpdb, $RealVMS, $comments, $bookmarks;

		include_once( str_replace ( 'index.php', '', __FILE__ ) . '/includes/publishedPost.php' );
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This here will give us some additional post functionality inside of the wordpress post/edit
	 * admin page
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_action ( 'edit_form_advanced', 'advancedPostSettings' );
	
	function advancedPostSettings() {
		include_once( str_replace ( 'index.php', '', __FILE__ ) . '/includes/advancedPostSettings.php' );
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * The action gets called when a category gets removed, when a category is 
	 * removed we have to update our automated database settings so that when we re-run the 
	 * automation section our posts will still have a category to be entered into
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_action ( 'delete_category', 'removedCategory' );
	
	function removedCategory( $categoryID ) {
		global $wpdb, $RealVMS;
		
		/*
		 * ----------------------------------------------------------------------------------------------
		 * Find the category id of a default category from the term_taxonomy table 
		 * ----------------------------------------------------------------------------------------------
		 */
		$defaultCategoryID = $wpdb->get_var( "SELECT term_id FROM " . $wpdb->prefix . "term_taxonomy WHERE 
		taxonomy = 'category'" );
		
		if( $defaultCategoryID > 0 ) {
			/*
			 * ------------------------------------------------------------------------------------------
			 * Update the automated database so that our posts will still go into the correct category, 
			 * if we don't update at this stage the next time our automate script runs the post won't 
			 * have a category to go into and will cause issues.
			 * ------------------------------------------------------------------------------------------
			 */
			$wpdb->query( "UPDATE " . $wpdb->prefix . $RealVMS->automateTable . " SET cat_id = '" . 
			$defaultCategoryID . "' WHERE cat_id = '" . addslashes( $categoryID ) . "'" );
		}
		else {
			/*
			 * ------------------------------------------------------------------------------------------
			 * It appears that there are no more categories left which means somewhere along the lines 
			 * some code (NOT THIS PLUGIN) deleted the final default category which cannot be removed 
			 * from the default wordpress user interface menu.
			 * ------------------------------------------------------------------------------------------
			 * This should technically never happen but if it does then we have to remove the automation
			 * feature for this category so we dont run into any issues with this plugin
			 * ------------------------------------------------------------------------------------------
			 */
			$wpdb->query( "DELETE FROM " . $wpdb->prefix . $RealVMS->automateTable . " WHERE 
			cat_id = '" . $defaultCategoryID . "'" );
		}
	}
	
	/*
	 * -----------------------------------------------------------------------------------------------------
	 * Instead of creating my own seperate options table we will just use the one which is already
	 * created by Wordpress to store a range of values that would otherwise need there own unique
	 * table - This enables us to cut down on creating additional database tables
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_option ( 'vms_showImage', '0' );
	add_option ( 'realvms_DelUser', '' );
	add_option ( 'realvms_DelPass', '' );
	add_option ( 'realvms_FavesUser', '' );
	add_option ( 'realvms_FavesPass', '' );
	add_option ( 'realvms_DeleteSocialBookmarks', '0' );
	add_option ( 'realvms_removeEverything', '0' );

	/*
	 * -----------------------------------------------------------------------------------------------------
	 * Apply a filter to to the post content just before its displayed on the screen and/or after 
	 * any plugins have manipulated the data - this filter is used to manipulate data without
	 * using a database but before its displayed to the user.
	 * -----------------------------------------------------------------------------------------------------
	 */
	add_filter ( 'the_content', 'changeThePost' );
	
	function changeThePost() {
		global $wpdb, $post, $RealVMS;
		
		$showImage = get_option ( 'vms_showImage' );
		
		/*
		 * -------------------------------------------------------------------------------------------------
		 * Try to find the video ID
		 * -------------------------------------------------------------------------------------------------
		 */
		$videoID = $wpdb->get_var ( "SELECT seen FROM " . $wpdb->prefix . $RealVMS->optionsTable . 
		" WHERE post_id = '" . $post->ID . "'" );
	
		$match = array ( );
		preg_match ( "/$videoID/", $post->post_content, $match );
		
		if ( isset( $videoID ) && $showImage == 1 && $match [0] == $videoID && !is_single ()) {
			$image = $wpdb->get_var ( "SELECT image FROM " . $wpdb->prefix . $RealVMS->optionsTable . 
			" WHERE post_id = '" . $post->ID . "'" );
			
			$replace = '<p><center><a href="' . get_permalink ( $post->ID ) . '"><img style="margin:8px;" ' . 
			'src="' . stripslashes ( $image ) . '" alt="' . $post->post_title . '" /></a></center></p>';
			
			return preg_replace ( "/<object(.*?)object>/i", $replace, $post->post_content );
		}
		
		return rvmsnls2p( $post->post_content );
	}

	function generalSettings() {
		global $RealVMS;
		
		include_once( str_replace ( 'index.php', '', __FILE__ ) . '/includes/generalSettings.php' );
	}

	function socialBookmarks() {
		global $RealVMS;
				
		include_once( str_replace ( 'index.php', '', __FILE__ ) . '/includes/socialBookmarks.php' );
	}

	/*
	 * -----------------------------------------------------------------------------------------------------
	 * This function is used to search and display videos from the "Manual Control" tab
	 * -----------------------------------------------------------------------------------------------------
	 */
	function addVideos() {
		global $RealVMS;
		
		include_once( str_replace ( 'index.php', '', __FILE__ ) . '/includes/addVideos.php' );
	}
	
	add_action('realAutomation', 'automationEvent');

	function automation() {
		global $wpdb, $RealVMS;
		
		include_once( str_replace ( 'index.php', '', __FILE__ ) . '/includes/automation.php' );
	}
?>