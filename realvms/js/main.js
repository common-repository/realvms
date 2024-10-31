	
	if(window.location == 'http://'+window.location.hostname+'/wp-admin/post-new.php?realvms=true') {  
	  window.onload = initValues;
	}

	function initValues() {
		$.ajax({
			type: "GET",
			url: httpLocation+"/includes/addSession.php",
			success: function(msg){
				var arr = msg.split('/*/');
				 
				var videoURL = arr[0];
				var videoTitle = arr[1];
				var videoDescription = arr[2];
				var videoTags = arr[3];
				
				jQuery("#title").val(videoTitle);
				
				/*
				 * --------------------------------------------------------------------------------------------------
				 * Update the tags using the built in wordpress javascript functions
				 * --------------------------------------------------------------------------------------------------
				 */
				if(videoTags != null && videoTags != '' && videoTags != 'null') {
					update_quickclicks(videoTags);
					flush_to_text(videoTags);
				}
				
				if(videoDescription != null && videoDescription != '' && videoDescription != 'null') {
					videoDescription = '<p>'+videoDescription+'</p>';
				}
				else {
					videoDescription = '';
				}
				
				jQuery("#content").val('<p><object width="425" height="344"><param name="movie" value="'+
				videoURL+'"></param><param name="allowFullScreen" '+
				'value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="'+
				videoURL+'" type="application/x-shockwave-flash" allowscriptaccess="always" '+
				'allowfullscreen="true" width="425" height="344"></embed></object></p>'+
				"\r\n\r\n"+videoDescription);
			}
		 });
		
		return;
	}
	
	function flush_to_text(tags) {
		var newtags = tags;

		// massage
		newtags = newtags.replace( /\s+,+\s*/g, ',' ).replace( /,+/g, ',' ).replace( /,+\s+,+/g, ',' ).replace( /,+\s*$/g, '' ).replace( /^\s*,+/g, '' );
		jQuery('#tags-input').val( newtags );

		return false;
	}

	function update_quickclicks(tags) {
		var current_tags = tags.split(',');
		jQuery( '#tagchecklist' ).empty();
		shown = false;
		jQuery.each( current_tags, function( key, val ) {
			val = val.replace( /^\s+/, '' ).replace( /\s+$/, '' ); // trim
			if ( !val.match(/^\s+$/) && '' != val ) {
				txt = '<span><a id="tag-check-' + key + '" class="ntdelbutton">X</a>&nbsp;' + val + '</span> ';
				jQuery( '#tagchecklist' ).append( txt );
				jQuery( '#tag-check-' + key ).click( new_tag_remove_tag );
				shown = true;
			}
		});
		if ( shown )
			jQuery( '#tagchecklist' ).prepend( '<strong>'+postL10n.tagsUsed+'</strong><br />' );
	}
	
	/*
	 * --------------------------------------------------------------------------------------------------
	 * open up a new window and display the video inside of this window
	 * --------------------------------------------------------------------------------------------------
	 */
	function previewVideo(num,URL) { 
		window.open(URL,num,'width=425,height=344,toolbar=0,resizable=0,location=0,directories=0,status=0,menubar=0,scrollbars=0');
		return false;
	}
