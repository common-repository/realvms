<?php
	class RealVMS_SocialClass {    
		function delicious( $socialObject, $user, $pass ) {
			$properURL = $this->getProperURL( $socialObject );
			
			$title = $socialObject ['title'];
			$desc = strip_tags( $socialObject ['content'] );
			$desc = substr( $desc, 0, 100 );
			
			if( empty( $title ) ) {
			  $title = $user . ' - ' . $socialObject ['ID'];
			}
			
			$tags = urlencode( $socialObject ['tags'] );
			$title = urlencode( $title );
			$desc = urlencode( $desc );
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Post this link to delicious
			 * ---------------------------------------------------------------------------------------------
			 */
			$this->get_file_contents( 'https://' . $user . ':' . $pass . '@api.del.icio.us/v1/posts/add?url=' . $properURL . '&description=' . $title . '&extended=' . $desc . '&tags=' . $tags . '&replace=no&shared=yes' );
		}
	    
	    function faves( $socialObject, $user, $pass ) {
			$properURL = $this->getProperURL( $socialObject );
			
			$title = $socialObject ['title'];
			$desc = strip_tags( $socialObject ['content'] );
			$desc = substr( $desc, 0, 100 );
			
			if( empty( $title ) ) {
				$title = $user . ' - ' . $socialObject ['ID'];
			}
			
			$tags = urlencode( $socialObject ['tags'] );
			$title = urlencode( $title );
			$desc = urlencode( $desc );
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Post this link to delicious
			 * ---------------------------------------------------------------------------------------------
			 */
			$this->get_file_contents( 'https://' . $user . ':' . $pass . '@secure.faves.com/v1/posts/add?url=' . $properURL . '&description=' . $title . '&extended=' . $desc . '&tags=' . $tags . '&replace=no&shared=yes' );
	    }
	    
	    function getProperURL( $socialObject ) {
			$permalink_structure = $socialObject ['link_structure'];
			
			if( empty( $permalink_structure ) ) {
				$properURL = $socialObject ['domain'] . '?id=' . $socialObject ['ID'];
				
				return $properURL;
			}
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Bust apart the post date
			 * ---------------------------------------------------------------------------------------------
			 */
			$break = explode( ' ', $socialObject ['date'] );
			$first = explode( '-', $break [0] );
			$last = explode( ':', $break [1] );
			$postYear = $first [0];
			$postMonth = $first [1];
			$postDay = $first [2];
			$postHour = $last [0];
			$postMinute = $last [1];
			$postSecond = $last [2];
			
			/*
			 * ---------------------------------------------------------------------------------------------
			 * Form this post URL according to the currently set permalink structure
			 * ---------------------------------------------------------------------------------------------
			 */
			$permalink_structure = str_replace( '%year%', $postYear, $permalink_structure );
			$permalink_structure = str_replace( '%monthnum%', $postMonth, $permalink_structure );
			$permalink_structure = str_replace( '%day%', $postDay, $permalink_structure );
			$permalink_structure = str_replace( '%hour%', $postHour, $permalink_structure );
			$permalink_structure = str_replace( '%minute%', $postMinute, $permalink_structure );
			$permalink_structure = str_replace( '%second%', $postSecond, $permalink_structure );
			$permalink_structure = str_replace( '%postname%', $socialObject ['name'], $permalink_structure );
			$permalink_structure = str_replace( '%post_id%', $socialObject ['ID'], $permalink_structure );
			$permalink_structure = str_replace( '%category%', $socialObject ['cat_slug'], $permalink_structure );
			$permalink_structure = str_replace( '%author%', $socialObject ['author_name'], $permalink_structure );
			
			$properURL = $socialObject ['domain'] . $permalink_structure;
			
			return $properURL;
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
	$bookmarks = new RealVMS_SocialClass ( );
?>
