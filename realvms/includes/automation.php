<?php 
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * The user would like to change to setup for the first time there automation so execute the following
	 * code
	 * ------------------------------------------------------------------------------------------------------
	 */
	if( $_POST ['realvmsStartAutomation'] ) {
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Get the date the user would like to start running the automation
		 * --------------------------------------------------------------------------------------------------
		 */
		$month = $_POST ['SelectMonth'];
		$day = $_POST ['SelectDay'];
		$year = $_POST ['SelectYear'];
		$hour = $_POST ['SelectHour'];
		$minute = $_POST ['SelectMinute'];
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Make sure that the user has entered in a value greater then the current date if they have not 
		 * then change there settings to the current date
		 * --------------------------------------------------------------------------------------------------
		 */
		
		if( date('m') > $month ) {
			$month = date('m');
		}
		
		if( date('d') > $day ) {
			$day = date('d');
		}
		
		if( date('Y') > $year ) {
			$year = date('Y');
		}
		
		if( date('H') > $hour ) {
			$hour = date('H');
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Create the timestamp which is required by the wordpress cron job
		 * --------------------------------------------------------------------------------------------------
		 */
		$timestamp = mktime( $hour, $minute, date('s'), $month, $day, $year );
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Clear the current automation if there is one and setup the new one with the settings specified by
		 * the user
		 * --------------------------------------------------------------------------------------------------
		 */
		wp_clear_scheduled_hook( 'realAutomation' );
		wp_schedule_event( $timestamp, $_POST ['realvmsRunEvery'], 'realAutomation' );
	}
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * If the user would like to stop the current automation process then clear the scheduled hook from
	 * the wordpress database
	 * ------------------------------------------------------------------------------------------------------
	 */
	if( $_POST ['realvmsStopAutomation'] ) {
		wp_clear_scheduled_hook('realAutomation');
	}
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * Check to see if the user currently has an active automation process 
	 * ------------------------------------------------------------------------------------------------------
	 */
	$cronJob = get_option( 'cron' );
		
	foreach($cronJob as $key => $value) {
		if( is_array( $value['realAutomation'] ) ) {
			$currentAutomationTimestamp = $key;
			break;
		}
	}
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * If the user is currently running a process then show them a message letting them know what the 
	 * current server time is and let them know when there next automation is scheduled to fire, however if
	 * the user does not currently have an active automation process running, give them a message letting
	 * them know that there are no scheduled automations.
	 * ------------------------------------------------------------------------------------------------------
	 */
	if( isset( $currentAutomationTimestamp ) ) {
		$currentServerTime = date( 'F d, Y - g:ia' );
		$nextAutomation = date( 'F d, Y - g:ia', $currentAutomationTimestamp );
		
		$automationMessage = <<<EOD
		<div id="message" class="updated fade">
			<p>
				<b>Current Server Time: </b>$currentServerTime
			</p>
			
			<p>
				<b>Next Scheduled Automation: </b>$nextAutomation
			</p>
		</div>
EOD;
	}
	else {
		$automationMessage = <<<EOD
		<div id="message" class="updated fade">
			<p>
				There is currently no scheduled automations.
			</p>
		</div>
EOD;
	}

	/*
	 * ------------------------------------------------------------------------------------------------------
	 * If the user would like to add a new entry to the automation stage then add the required information
	 * to our database
	 * ------------------------------------------------------------------------------------------------------
	 */
	if ( $_POST ['realvmsAddAutoVideoInfo'] || $_POST ['realvmsUpdateAutoVideoInfo'] ) {
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Get all the required information setup by the user
		 * --------------------------------------------------------------------------------------------------
		 */
		$categoryID = $_POST ['realvmsSiteCategories'];
		$searchCategory = $_POST ['realvmsCategory'];
		$searchTerm = $_POST ['realvmsSearchTerm'];
		$userGeneratedTags = $_POST ['realvmsPostTags'];
		$lowestNumComments = $_POST ['commentRangeOne'];
		$highestNumComments = $_POST ['commentRangeTwo'];
		$useSafeComments = $_POST ['realvmsSafeComments'];
		$includeVideoTags = $_POST ['realvmsIncludeVideoTags'];
		$includeVideoDescription = $_POST ['realvmsIncludeVideoDescription'];
		$sortBy = $_POST ['realvmsVideoOrder'];
		$addToDelicious = $_POST ['post_delicious654'];
		$addToFaves = $_POST ['post_faves654'];
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If any of the checkboxes are not selected then its a good idea just to make sure that there 
		 * values are equal to zero
		 * --------------------------------------------------------------------------------------------------
		 */
		if( empty( $addToDelicious ) ) {
			$addToDelicious = 0;
		}
		
		if( empty( $addToFaves ) ) {
			$addToFaves = 0;
		}
		
		if ( !isset( $includeVideoTags ) ) {
			$includeVideoTags = 0;
		}
		
		if ( !isset( $includeVideoDescription ) ) {
			$includeVideoDescription = 0;
		}

		if ( !isset( $useSafeComments ) ) {
			$useSafeComments = 0;
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Check to make sure that the lowestNumberOfComments is less then the highestNumberOfComments, if
		 * its not then set the lowestNumberOfComments equal to the highestNumberOfComments minus 20
		 * --------------------------------------------------------------------------------------------------
		 */
		if ( $lowestNumComments > $highestNumComments ) {
			$lowestNumComments = $highestNumComments - 20;
			
			if ( $lowestNumComments < 0 ) {
				$lowestNumComments = 0;
			}
		}
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If the user did not set a comment range then set both values to zero so that no comments will 
		 * get added by the automation process
		 * --------------------------------------------------------------------------------------------------
		 */
		if ( empty( $lowestNumComments ) && empty( $highestNumComments ) ) {
			$lowestNumComments = 0;
			$highestNumComments = 0;
		}
	
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Now that we have done all of the required formatting add this entry to our database so that
		 * it will get executed the next time the automation is run
		 * --------------------------------------------------------------------------------------------------
		 */
		if( $_POST ['realvmsAddAutoVideoInfo'] ) {
			$wpdb->query ( "INSERT INTO " . $wpdb->prefix . $RealVMS->automateTable . " (searchCategory,keyword,crone,crtwo,tags,safeComments,cat_id,vtags,vdes,sortBy,favesBookmark,delBookmark) VALUES ('" . addslashes($searchCategory) . "','" . addslashes ( $searchTerm ) . "','" . addslashes ( $lowestNumComments ) . "','" . addslashes ( $highestNumComments ) . "','" . addslashes ( $userGeneratedTags ) . "','$useSafeComments','" . addslashes( $categoryID ) . "','$includeVideoTags','$includeVideoDescription','" . addslashes ( $sortBy ) . "','" . addslashes ( $addToFaves ) . "','" . addslashes ( $addToDelicious ) . "')" );
			
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Show a success message to the user letting them know that there entry has been added to the 
		 * database and will be run during the next automation schedule.
		 * --------------------------------------------------------------------------------------------------
		 */
			$statusMessage = <<<EOD
			<div id="message" class="updated fade">
				<p>Your Settings Have Been Successfully Saved.</p>
			</div>
EOD;
		}
		elseif( $_POST ['realvmsUpdateAutoVideoInfo'] ) {
			$wpdb->query ( "UPDATE " . $wpdb->prefix . $RealVMS->automateTable . " SET searchCategory = '".addslashes($searchCategory)."', keyword = '" . addslashes ( $searchTerm ) . "', crone = '" . addslashes ( $lowestNumComments ) . "', crtwo = '" . addslashes ( $highestNumComments ) . "', tags = '" . addslashes ( $userGeneratedTags ) . "', safeComments = '" . addslashes( $useSafeComments ) . "', cat_id = '" . addslashes( $categoryID ) . "', vtags = '" . addslashes( $includeVideoTags ) . "', vdes = '" . addslashes( $includeVideoDescription ) . "', sortBy = '" . addslashes ( $sortBy ) . "', favesBookmark = '" . addslashes ( $addToFaves ) . "', delBookmark = '" . addslashes ( $addToDelicious ) . "' WHERE id = '" . addslashes( $_POST ['realvmsEditAutoID'] ) . "'" );
			
			$statusMessage = <<<EOD
			<div id="message" class="updated fade">
				<p>
					Your Settings Have Been Successfully Updated.
				</p>
			</div>
EOD;
		}
	}
	
	/*
	 * ------------------------------------------------------------------------------------------------------
	 * If the user would like to remove an entry from the automation schedule then allow them to do so
	 * now
	 * ------------------------------------------------------------------------------------------------------
	 */
	if ( $_POST ['realvmsRemoveDatabaseID'] ) {
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Get the id of the entry the user would like to remove
		 * --------------------------------------------------------------------------------------------------
		 */
		$removeThisID = $_POST ['realvmsRemoveDatabaseID'];
		
		/*
		 * --------------------------------------------------------------------------------------------------
		 * Remove this value from our table
		 * --------------------------------------------------------------------------------------------------
		 */
		$wpdb->query ( "DELETE FROM " . $wpdb->prefix . $RealVMS->automateTable . 
		" WHERE id = '$removeThisID' LIMIT 1" );

		$statusMessage = <<<EOD
		<div id="message" class="updated fade">
			<p>
				Your Post Automation Has Been Successfully Removed.
			</p>
		</div>
EOD;
	}
?>	
	<div class="wrap" id="wrap">
	<div id="message" class="error">
		<p><a href="http://www.realvms.com#anchor">Upgrade To RealVMS Pro</a> To Get The Highest Features And Performance.</p>
	</div>
	
	<?php 	
		/*
		 * --------------------------------------------------------------------------------------------------
		 * If the user has clicked the edit button then get all of the information related to the entry 
		 * they would like to edit
		 * --------------------------------------------------------------------------------------------------
		 */
		if( $_POST ['realvmsEditDatabaseID'] ) {
			$automatedSettings = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . $RealVMS->automateTable . " WHERE id = '" . $_POST ['realvmsEditDatabaseID'] . "'" );
			$automatedSettings = $automatedSettings[0];
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Place all of the wordpress categories into a drop down list
		 * ---------------------------------------------------------------------------------------------
		 */
		$option = '<select name="realvmsSiteCategories">';
		$categories = get_categories ( 'hide_empty=0' );
		
		if ($categories) {
			foreach ( $categories as $cat ) {
				if ($cat->taxonomy == 'category') {
					if( $automatedSettings->cat_id == $cat->cat_ID ) {
						$option .= '<option value="' . $cat->cat_ID . '" selected="true">';
					}
					else {
						$option .= '<option value="' . $cat->cat_ID . '">';
					}
					
					$option .= $cat->cat_name;
					$option .= '</option>';	
				}
			}
		}
		$option .= '</select>';
	?>
	
	<h2>Automated Settings <a href="http://www.realvms.com/how-to-use-the-automation-feature/" target="_blank">
						<img src="<?php echo $RealVMS->httpLocation.'/images/help.png'; ?>" alt="Help" />
					</a></h2>
	
	<?php 
		$RealVMS->javascriptWarning();
		
		echo $automationMessage;
	?>	
		
	<table cellpadding="0" width="100%" cellspacing="4" border="0" width="98%">
		<tr>
			<td>
			<form method="POST" action="<?php echo $RealVMS->actionRequest; ?>">
				Run The Automation Every: 
				<select name="realvmsRunEvery">
					<option value="hourly">Hour</option>
					<option value="daily">Day</option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td>
				Start the automation on: 
				
				<select name="SelectMonth">
					<option <?php if( date('m') == 01 ){echo 'selected="true"';} ?> value="01">January</option>
					<option <?php if( date('m') == 02 ){echo 'selected="true"';} ?> value="02">Febuary</option>
					<option <?php if( date('m') == 03 ){echo 'selected="true"';} ?> value="03">March</option>
					<option <?php if( date('m') == 04 ){echo 'selected="true"';} ?> value="04">April</option>
					<option <?php if( date('m') == 05 ){echo 'selected="true"';} ?> value="05">May</option>
					<option <?php if( date('m') == 06 ){echo 'selected="true"';} ?> value="06">June</option>
					<option <?php if( date('m') == 07 ){echo 'selected="true"';} ?> value="07">July</option>
					<option <?php if( date('m') == 08 ){echo 'selected="true"';} ?> value="08">August</option>
					<option <?php if( date('m') == 09 ){echo 'selected="true"';} ?> value="09">September</option>
					<option <?php if( date('m') == 10 ){echo 'selected="true"';} ?> value="10">October</option>
					<option <?php if( date('m') == 11 ){echo 'selected="true"';} ?> value="11">November</option>
					<option <?php if( date('m') == 12 ){echo 'selected="true"';} ?> value="12">December</option>
				</select>
				
				<select name="SelectDay">
					<option <?php if( date('d') == 01 ){echo 'selected="true"';} ?> value="01">01</option>
					<option <?php if( date('d') == 02 ){echo 'selected="true"';} ?> value="02">02</option>
					<option <?php if( date('d') == 03 ){echo 'selected="true"';} ?> value="03">03</option>
					<option <?php if( date('d') == 04 ){echo 'selected="true"';} ?> value="04">04</option>
					<option <?php if( date('d') == 05 ){echo 'selected="true"';} ?> value="05">05</option>
					<option <?php if( date('d') == 06 ){echo 'selected="true"';} ?> value="06">06</option>
					<option <?php if( date('d') == 07 ){echo 'selected="true"';} ?> value="07">07</option>
					<option <?php if( date('d') == 08 ){echo 'selected="true"';} ?> value="08">08</option>
					<option <?php if( date('d') == 09 ){echo 'selected="true"';} ?> value="09">09</option>
					<option <?php if( date('d') == 10 ){echo 'selected="true"';} ?> value="10">10</option>
					<option <?php if( date('d') == 11 ){echo 'selected="true"';} ?> value="11">11</option>
					<option <?php if( date('d') == 12 ){echo 'selected="true"';} ?> value="12">12</option>
					<option <?php if( date('d') == 13 ){echo 'selected="true"';} ?> value="13">13</option>
					<option <?php if( date('d') == 14 ){echo 'selected="true"';} ?> value="14">14</option>
					<option <?php if( date('d') == 15 ){echo 'selected="true"';} ?> value="15">15</option>
					<option <?php if( date('d') == 16 ){echo 'selected="true"';} ?> value="16">16</option>
					<option <?php if( date('d') == 17 ){echo 'selected="true"';} ?> value="17">17</option>
					<option <?php if( date('d') == 18 ){echo 'selected="true"';} ?> value="18">18</option>
					<option <?php if( date('d') == 19 ){echo 'selected="true"';} ?> value="19">19</option>
					<option <?php if( date('d') == 20 ){echo 'selected="true"';} ?> value="20">20</option>
					<option <?php if( date('d') == 21 ){echo 'selected="true"';} ?> value="21">21</option>
					<option <?php if( date('d') == 22 ){echo 'selected="true"';} ?> value="22">22</option>
					<option <?php if( date('d') == 23 ){echo 'selected="true"';} ?> value="23">23</option>
					<option <?php if( date('d') == 24 ){echo 'selected="true"';} ?> value="24">24</option>
					<option <?php if( date('d') == 25 ){echo 'selected="true"';} ?> value="25">25</option>
					<option <?php if( date('d') == 26 ){echo 'selected="true"';} ?> value="26">26</option>
					<option <?php if( date('d') == 27 ){echo 'selected="true"';} ?> value="27">27</option>
					<option <?php if( date('d') == 28 ){echo 'selected="true"';} ?> value="28">28</option>
					<option <?php if( date('d') == 29 ){echo 'selected="true"';} ?> value="29">29</option>
					<option <?php if( date('d') == 30 ){echo 'selected="true"';} ?> value="30">30</option>
					<option <?php if( date('d') == 31 ){echo 'selected="true"';} ?> value="31">31</option>
				</select>
				
				<select name="SelectYear">
					<?php 
						$currentYear = date('Y');
						$tenYearsAhead = $currentYear + 10;
						
						for($i=$currentYear;$i<$tenYearsAhead;$i++) {
							echo '<option value="' . $i . '">' . $i . '</option>';	
						}
					?>
				</select>
				At: 
				<select name="SelectHour">
					<option <?php if( date('H') == 00 ){echo 'selected="true"';} ?> value="00">Midnight</option>
					<option <?php if( date('H') == 01 ){echo 'selected="true"';} ?> value="01">1am</option>
					<option <?php if( date('H') == 02 ){echo 'selected="true"';} ?> value="02">2am</option>
					<option <?php if( date('H') == 03 ){echo 'selected="true"';} ?> value="03">3am</option>
					<option <?php if( date('H') == 04 ){echo 'selected="true"';} ?> value="04">4am</option>
					<option <?php if( date('H') == 05 ){echo 'selected="true"';} ?> value="05">5am</option>
					<option <?php if( date('H') == 06 ){echo 'selected="true"';} ?> value="06">6am</option>
					<option <?php if( date('H') == 07 ){echo 'selected="true"';} ?> value="07">7am</option>
					<option <?php if( date('H') == 08 ){echo 'selected="true"';} ?> value="08">8am</option>
					<option <?php if( date('H') == 09 ){echo 'selected="true"';} ?> value="09">9am</option>
					<option <?php if( date('H') == 10 ){echo 'selected="true"';} ?> value="10">10am</option>
					<option <?php if( date('H') == 11 ){echo 'selected="true"';} ?> value="11">11am</option>
					<option <?php if( date('H') == 12 ){echo 'selected="true"';} ?> value="12">Noon</option>
					<option <?php if( date('H') == 13 ){echo 'selected="true"';} ?> value="13">1pm</option>
					<option <?php if( date('H') == 14 ){echo 'selected="true"';} ?> value="14">2pm</option>
					<option <?php if( date('H') == 15 ){echo 'selected="true"';} ?> value="15">3pm</option>
					<option <?php if( date('H') == 16 ){echo 'selected="true"';} ?> value="16">4pm</option>
					<option <?php if( date('H') == 17 ){echo 'selected="true"';} ?> value="17">5pm</option>
					<option <?php if( date('H') == 18 ){echo 'selected="true"';} ?> value="18">6pm</option>
					<option <?php if( date('H') == 19 ){echo 'selected="true"';} ?> value="19">7pm</option>
					<option <?php if( date('H') == 20 ){echo 'selected="true"';} ?> value="20">8pm</option>
					<option <?php if( date('H') == 21 ){echo 'selected="true"';} ?> value="21">9pm</option>
					<option <?php if( date('H') == 22 ){echo 'selected="true"';} ?> value="22">10pm</option>
					<option <?php if( date('H') == 23 ){echo 'selected="true"';} ?> value="23">11pm</option>
				</select>
				:
				<select name="SelectMinute">
					<option <?php if( date('i') == 00 ){echo 'selected="true"';} ?> value="00">00</option>
					<option <?php if( date('i') == 01 ){echo 'selected="true"';} ?> value="01">01</option>
					<option <?php if( date('i') == 02 ){echo 'selected="true"';} ?> value="02">02</option>
					<option <?php if( date('i') == 03 ){echo 'selected="true"';} ?> value="03">03</option>
					<option <?php if( date('i') == 04 ){echo 'selected="true"';} ?> value="04">04</option>
					<option <?php if( date('i') == 05 ){echo 'selected="true"';} ?> value="05">05</option>
					<option <?php if( date('i') == 06 ){echo 'selected="true"';} ?> value="06">06</option>
					<option <?php if( date('i') == 07 ){echo 'selected="true"';} ?> value="07">07</option>
					<option <?php if( date('i') == 08 ){echo 'selected="true"';} ?> value="08">08</option>
					<option <?php if( date('i') == 09 ){echo 'selected="true"';} ?> value="09">09</option>
					<option <?php if( date('i') == 10 ){echo 'selected="true"';} ?> value="10">10</option>
					<option <?php if( date('i') == 11 ){echo 'selected="true"';} ?> value="11">11</option>
					<option <?php if( date('i') == 12 ){echo 'selected="true"';} ?> value="12">12</option>
					<option <?php if( date('i') == 13 ){echo 'selected="true"';} ?> value="13">13</option>
					<option <?php if( date('i') == 14 ){echo 'selected="true"';} ?> value="14">14</option>
					<option <?php if( date('i') == 15 ){echo 'selected="true"';} ?> value="15">15</option>
					<option <?php if( date('i') == 16 ){echo 'selected="true"';} ?> value="16">16</option>
					<option <?php if( date('i') == 17 ){echo 'selected="true"';} ?> value="17">17</option>
					<option <?php if( date('i') == 18 ){echo 'selected="true"';} ?> value="18">18</option>
					<option <?php if( date('i') == 19 ){echo 'selected="true"';} ?> value="19">19</option>
					<option <?php if( date('i') == 20 ){echo 'selected="true"';} ?> value="20">20</option>
					<option <?php if( date('i') == 21 ){echo 'selected="true"';} ?> value="21">21</option>
					<option <?php if( date('i') == 22 ){echo 'selected="true"';} ?> value="22">22</option>
					<option <?php if( date('i') == 23 ){echo 'selected="true"';} ?> value="23">23</option>
					<option <?php if( date('i') == 24 ){echo 'selected="true"';} ?> value="24">24</option>
					<option <?php if( date('i') == 25 ){echo 'selected="true"';} ?> value="25">25</option>
					<option <?php if( date('i') == 26 ){echo 'selected="true"';} ?> value="26">26</option>
					<option <?php if( date('i') == 27 ){echo 'selected="true"';} ?> value="27">27</option>
					<option <?php if( date('i') == 28 ){echo 'selected="true"';} ?> value="28">28</option>
					<option <?php if( date('i') == 29 ){echo 'selected="true"';} ?> value="29">29</option>
					<option <?php if( date('i') == 30 ){echo 'selected="true"';} ?> value="30">30</option>
					<option <?php if( date('i') == 31 ){echo 'selected="true"';} ?> value="31">31</option>
					<option <?php if( date('i') == 32 ){echo 'selected="true"';} ?> value="32">32</option>
					<option <?php if( date('i') == 33 ){echo 'selected="true"';} ?> value="33">33</option>
					<option <?php if( date('i') == 34 ){echo 'selected="true"';} ?> value="34">34</option>
					<option <?php if( date('i') == 35 ){echo 'selected="true"';} ?> value="35">35</option>
					<option <?php if( date('i') == 36 ){echo 'selected="true"';} ?> value="36">36</option>
					<option <?php if( date('i') == 37 ){echo 'selected="true"';} ?> value="37">37</option>
					<option <?php if( date('i') == 38 ){echo 'selected="true"';} ?> value="38">38</option>
					<option <?php if( date('i') == 39 ){echo 'selected="true"';} ?> value="39">39</option>
					<option <?php if( date('i') == 40 ){echo 'selected="true"';} ?> value="40">40</option>
					<option <?php if( date('i') == 41 ){echo 'selected="true"';} ?> value="41">41</option>
					<option <?php if( date('i') == 42 ){echo 'selected="true"';} ?> value="42">42</option>
					<option <?php if( date('i') == 43 ){echo 'selected="true"';} ?> value="43">43</option>
					<option <?php if( date('i') == 44 ){echo 'selected="true"';} ?> value="44">44</option>
					<option <?php if( date('i') == 45 ){echo 'selected="true"';} ?> value="45">45</option>
					<option <?php if( date('i') == 46 ){echo 'selected="true"';} ?> value="46">46</option>
					<option <?php if( date('i') == 47 ){echo 'selected="true"';} ?> value="47">47</option>
					<option <?php if( date('i') == 48 ){echo 'selected="true"';} ?> value="48">48</option>
					<option <?php if( date('i') == 49 ){echo 'selected="true"';} ?> value="49">49</option>
					<option <?php if( date('i') == 50 ){echo 'selected="true"';} ?> value="52">50</option>
					<option <?php if( date('i') == 51 ){echo 'selected="true"';} ?> value="51">51</option>
					<option <?php if( date('i') == 52 ){echo 'selected="true"';} ?> value="52">52</option>
					<option <?php if( date('i') == 53 ){echo 'selected="true"';} ?> value="53">53</option>
					<option <?php if( date('i') == 54 ){echo 'selected="true"';} ?> value="54">54</option>
					<option <?php if( date('i') == 55 ){echo 'selected="true"';} ?> value="55">55</option>
					<option <?php if( date('i') == 56 ){echo 'selected="true"';} ?> value="56">56</option>
					<option <?php if( date('i') == 57 ){echo 'selected="true"';} ?> value="57">57</option>
					<option <?php if( date('i') == 58 ){echo 'selected="true"';} ?> value="58">58</option>
					<option <?php if( date('i') == 59 ){echo 'selected="true"';} ?> value="59">59</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
					<input type="submit" value="Start The Automation" name="realvmsStartAutomation" class="button-secondary action" />
				</form>
			</td>
			<td style="text-align:right;">
				<form method="POST" action="<?php echo $RealVMS->actionRequest; ?>">
					<input type="submit" value="Stop The Automation" name="realvmsStopAutomation" class="button-secondary action" />
				</form>
			</td>
		</tr>
	</table>
	
	<h2>Automation Setup</h2>
	
		<?php 
			if (isset($statusMessage)) {
				echo $statusMessage;
			}
		?>
		
		<table cellpadding="0" cellspacing="0" border="0" width="98%">				
			<tr>
				<td valign="top" width="50%">
					<form method="POST" action="<?php echo $RealVMS->actionRequest; ?>">
					<p>Keyword: <input type="text" size="20" name="realvmsSearchTerm" 
					value="<?php echo $automatedSettings->keyword; ?>" /> 
					</p>
					
					<p>
						YouTube Category: <select name="realvmsCategory">
							<option <?php if($automatedSettings->searchCategory == 'All') {echo 'selected="true"';} ?> value="All">All</option>
							<option <?php if($automatedSettings->searchCategory == 'Autos&Vehicles') {echo 'selected="true"';} ?> value="Autos&amp;Vehicles">Autos & Vehicles</option>
							<option <?php if($automatedSettings->searchCategory == 'Comedy') {echo 'selected="true"';} ?> value="Comedy">Comedy</option>
							<option <?php if($automatedSettings->searchCategory == 'Education') {echo 'selected="true"';} ?> value="Education">Education</option>
							<option <?php if($automatedSettings->searchCategory == 'Entertainment') {echo 'selected="true"';} ?> value="Entertainment">Entertainment</option>
							<option <?php if($automatedSettings->searchCategory == 'Film&Animation') {echo 'selected="true"';} ?> value="Film&amp;Animation">Film & Animation</option>
							<option <?php if($automatedSettings->searchCategory == 'Gaming') {echo 'selected="true"';} ?> value="Gaming">Gaming</option>
							<option <?php if($automatedSettings->searchCategory == 'Howto&Style') {echo 'selected="true"';} ?> value="Howto&amp;Style">Howto & Style</option>
							<option <?php if($automatedSettings->searchCategory == 'Music') {echo 'selected="true"';} ?> value="Music">Music</option>
							<option <?php if($automatedSettings->searchCategory == 'News&Politics') {echo 'selected="true"';} ?> value="News&amp;Politics">News & Politics</option>
							<option <?php if($automatedSettings->searchCategory == 'People&Blogs') {echo 'selected="true"';} ?> value="People&amp;Blogs">People & Blogs</option>
							<option <?php if($automatedSettings->searchCategory == 'Pets&Animals') {echo 'selected="true"';} ?> value="Pets&amp;Animals">Pets & Animals</option>
							<option <?php if($automatedSettings->searchCategory == 'Science&Technology') {echo 'selected="true"';} ?> value="Science&amp;Technology">Science & Technology</option>
							<option <?php if($automatedSettings->searchCategory == 'Sports') {echo 'selected="true"';} ?> value="Sports">Sports</option>
							<option <?php if($automatedSettings->searchCategory == 'Travel&Events') {echo 'selected="true"';} ?> value="Travel&amp;Events">Travel & Events</option>
						</select>
					</p>
						
						<p>Sort By <select name="realvmsVideoOrder">
							<option <?php if($automatedSettings->sortBy == 'relevance') {echo 'selected="true"';} ?> value="relevance">Relevance</option>
							<option <?php if($automatedSettings->sortBy == 'published') {echo 'selected="true"';} ?> value="published">Published</option>
							<option <?php if($automatedSettings->sortBy == 'viewCount') {echo 'selected="true"';} ?> value="viewCount">View Count</option>
							<option <?php if($automatedSettings->sortBy == 'rating') {echo 'selected="true"';} ?> value="rating">Rating</option>
							</select>
						</p>
	

						<p>Select Category: <?php echo $option; ?></p>
					
						<p>Post Tags: <input type="text" size="40" name="realvmsPostTags" value="<?php echo $automatedSettings->tags; ?>" />
						<br /><small>Seperate Tags With A Comma</small></p>
						
						<p>Comment Range: <input type="text" size="5" name="commentRangeOne" value="<?php echo $automatedSettings->crone; ?>" /> To 
						<input type="text" size="5" name="commentRangeTwo" value="<?php echo $automatedSettings->crtwo; ?>" /></p>
					
						<p>Use Safe Commenting? <input type="checkbox" <?php if($automatedSettings->safeComments == '1') {echo 'checked="true"';} ?> value="1" name="realvmsSafeComments" /></p>
						
						<p>Use Video Tags? <input type="checkbox" value="1" name="realvmsIncludeVideoTags" <?php if($automatedSettings->vtags == '1') {echo 'checked="true"';} ?> /></p>
						
						<p>Use Video Description? <input type="checkbox" value="1" name="realvmsIncludeVideoDescription" <?php if($automatedSettings->vdes == '1') {echo 'checked="true"';} ?> /></p>
						
						<p>Bookmark On Faves? <input type="checkbox" value="1" tabindex="17" name="post_faves654" <?php if($automatedSettings->favesBookmark == '1') {echo 'checked="true"';} ?> /></p>
						
						<p>Bookmark On Del.icio.us? <input type="checkbox" value="1" tabindex="14" name="post_delicious654" <?php if($automatedSettings->delBookmark == '1') {echo 'checked="true"';} ?> /></p>
						
						<p><b>Please Note: </b> In order to use the bookmarking features during the automation stage you must fill in your username/password for each service under the 
						Social Bookmarks Tab.</p>
						
						<p>
							<?php 
								if( $_POST ['realvmsEditDatabaseID'] ) :
							?>
								<input type="submit" value="Update Post Details" name="realvmsUpdateAutoVideoInfo" class="button-secondary action" />
								<input type="hidden" value="<?php echo $_POST ['realvmsEditDatabaseID']; ?>" name="realvmsEditAutoID" />
							<?php 
								else :
							?>
								<input type="submit" value="Add Post Details" name="realvmsAddAutoVideoInfo" class="button-secondary action" />
							<?php
								endif;
							?>
						</p>
						
						</form>
					</td>
<?php 

	/*
	 * -------------------------------------------------------------------------------------------------
	 * Get all of the already added automated processes and display them here
	 * -------------------------------------------------------------------------------------------------
	 */
	$realvmsAutomatedSettings = $wpdb->get_results ( "SELECT id,keyword FROM " . $wpdb->prefix . 
	$RealVMS->automateTable );
	
	$ele = '<ul>';
	
	if ( $realvmsAutomatedSettings ) {
		$a = 0;
		foreach ( $realvmsAutomatedSettings as $e ) {
			unset($keyword);
			unset($id);
			
			$keyword = stripslashes ( $e->keyword );
			$id = stripslashes ( $e->id );
			
			$ele .= <<<EOD
			<li>
				$keyword - 
				<input type="submit" value="Remove" onclick="javascript:removeForm$a.submit();" class="button-secondary action" />
				
				<input type="submit" value="Edit" onclick="javascript:editForm$a.submit();" class="button-secondary action" />
				
				<div style="display:none;">
					<form name="removeForm$a" action="$RealVMS->actionRequest" method="POST">
						<input type="hidden" value="$id" name="realvmsRemoveDatabaseID" />
					</form>
					
					<form name="editForm$a" action="$RealVMS->actionRequest" method="POST">
						<input type="hidden" value="$id" name="realvmsEditDatabaseID" />
					</form>
				</div>
			</li>
EOD;
			
			$a++;
		}
	} else {
		$ele .= '<li>No Current Elements</li>';
	}
	
	$ele .= '</ul>';
?>	
					<td valign="top" width="40%" style="padding-top:8px;text-align:right;">
						<div style="text-align:left;padding:8px;border:1px solid #eee;">
							<b>Current Operations</b>
							<div id="curop"><?php echo $ele; ?></div>
						</div>
					</td>
				</tr>
			</table>
	</div>