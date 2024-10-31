<?php 
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * This is the main class for the RealVMS plugin. It is used to initialize the plugin and setup any 
	 * global values which get re-used throughout the plugin
	 * ------------------------------------------------------------------------------------------------------
	 */
	class RealVMS {
		/*
		 * --------------------------------------------------------------------------------------------------
		 * This public var holds a reference to the main WPDB class which is used to access the database
		 * --------------------------------------------------------------------------------------------------
		 */
		var $wpdb;
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * This public var holds the URL action paramater of all the FORM elements in the plugin - This value
		 * is dynamic and changes depending on which page we are currently on
		 * --------------------------------------------------------------------------------------------------
		 */
		var $actionRequest;
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * This public var holds the HTTP domain name for this website - it does not include a trailing slash
		 * and it does start with http:// or https://
		 * --------------------------------------------------------------------------------------------------
		 */
		var $domainName;
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * This public var holds the name of the folder which this plugin is inside, since we allow the user
		 * to rename the default RealVMS folder name.
		 * --------------------------------------------------------------------------------------------------
		 */
		var $pluginFolder;
		
		
		/* 
		 * --------------------------------------------------------------------------------------------------
	     * This var tells us exactly what our file system looks like in a http(s):// type format instead 
	     * of an absolute path format (/home/user/html/ect)
	     * --------------------------------------------------------------------------------------------------
	     */
		var $httpLocation;
		
		/* 
		 * --------------------------------------------------------------------------------------------------
	     * These vars are used so that if we want to change the name of our DB tables in the future we only 
	     * need to make one change which is right here instead of searching all files
	     * --------------------------------------------------------------------------------------------------
	     */
		var $optionsTable = 'realvms_options';
		var $automateTable = 'realvms_automate';
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * PHP4 compatibility layer used to setup the plugin and all of the default settings
		 * --------------------------------------------------------------------------------------------------
		 */
		function RealVMS( $wpdb ) {
			/*
			 * ----------------------------------------------------------------------------------------------
			 * This public var holds a reference to the main WPDB class which is used to access the database
			 * ----------------------------------------------------------------------------------------------
			 */
			$this->wpdb = $wpdb;
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * This public var holds the HTTP domain name for this website - it does not include a trailing 
			 * slash and it does start with http:// or https://
			 * ----------------------------------------------------------------------------------------------
			 */
			$this->domainName = get_bloginfo ( 'url' );
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * If we find a trailing slash on the domain name, simply remove it (Trust me its easier)
			 * ----------------------------------------------------------------------------------------------
			 */
			if (substr ( $this->domainName, 0, - 1 ) == '/') {
				$this->domainName = substr ( $this->domainName, - 1 );
			}
			
			/* 
			 * ----------------------------------------------------------------------------------------------
			 * Determine the folder name in which this file is located in (Basically it tells us which 
			 * folder or sub-folders we are currently running inside) - Doesn't include a trailing slash 
			 * ----------------------------------------------------------------------------------------------
			 */
			$this->pluginFolder = str_replace ( '/classes/Realvms.php', '', substr ( __FILE__, strpos ( __FILE__, 
			'/wp-content/plugins/' ) + 20 ) );
			
			/* 
			 * ----------------------------------------------------------------------------------------------
		     * This var tells us exactly what our file system looks like in a http(s):// type format instead 
		     * of an absolute path format (/home/user/html/ect)
		     * ----------------------------------------------------------------------------------------------
		     */
			$this->httpLocation = $this->domainName . '/wp-content/plugins/' . $this->pluginFolder;
			
			/*
			 * ----------------------------------------------------------------------------------------------
			 * This public var holds the URL action paramater of all the FORM elements in the plugin - This 
			 * value is dynamic and changes depending on which page we are currently on
			 * ----------------------------------------------------------------------------------------------
			 */
			$this->actionRequest = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * PHP5 style destructor and will run when object is destroyed.
		 * --------------------------------------------------------------------------------------------------
		 */
		function __destruct() {
			return true;
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * This function is called when we want to display to the user a message that they have no JavaScript
		 * enabled or they have some form of javascript blocker running in there browser.
		 * --------------------------------------------------------------------------------------------------
		 */
		function javascriptWarning() {
			$string = <<<EOD
			<noscript>
				<div id="message" class="updated fade">
					<p style="line-height:18px;">
						You Must Enable JavaScript In Your Browser For This Plugin To Work, 
						<a href="http://www.google.com/support/bin/answer.py?answer=23852&hl=en&ctx=rosetta" 
						target="_blank">Click Here</a> 
						Fore More Information. If you have already enabled JavaScript please check to make 
						sure you are not currently using any JavaScript or pop-up blockers.
					</p>
				</div>
			</noscript>
EOD;
			echo $string;
			
			return true;
		}
	}
	
	if( empty( $RealVMS ) ) {
		$RealVMS = new RealVMS( $wpdb, $comments, $bookmarks );
	}
?>