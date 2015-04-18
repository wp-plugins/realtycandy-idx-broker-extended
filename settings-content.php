 <?php
  	$updated = false;
	  // Read in existing option value from database
    $client_email_val = get_option( 'realtycandy_client_email' );
  	$client_email_input = 'realtycandy_client_email';
    $opt_val = get_option( 'realtycandy_apikey' );
    $opt_name = 'realtycandy_apikey';
  	$realtycandy_wrapper = sanitize_text_field( get_option( 'realtycandy_dynamic_wrapper_page_name' ) );  //sanitize

	// See if the user has posted us some information
    if( isset($_POST[$opt_name]) ) {
        // Read their posted value
        $opt_val = $_POST[ $opt_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );

        // Put an settings updated message on the screen
	  	$updated = true;
	}
  	//Check if an email was set
    if ( isset($_POST[$client_email_input]) ) {  
        $client_email_val = $_POST[ $client_email_input ];
        update_option( $client_email_input, $client_email_val );

	}
  	//Check if an page wrapper was set
  	if ( isset($_POST['realtycandy_dynamic_wrapper_page_name']) ) {  
        $realtycandy_wrapper = sanitize_text_field( $_POST['realtycandy_dynamic_wrapper_page_name'] ); //sanitize
        update_option( 'realtycandy_dynamic_wrapper_page_name', $realtycandy_wrapper );

	}
  	if ($updated == true) { 
	  echo '<div class="updated"><p><strong>settings saved.</strong></p></div>';
	}
  
  	/**
	 * check wrapper page exists or not
	 */
  	$wrapper_page_id = intval( get_option('realtycandy_wrapper_id') ) ? get_option('realtycandy_wrapper_id') : ''; //sanitize
	$post_title = '';
	$wrapper_page_url = '';
	if ($wrapper_page_id) {
		if (!get_page_uri($wrapper_page_id)) {
			update_option('realtycandy_wrapper_id', '');
			$wrapper_page_id = '';
		} else {
			$post_title = get_post($wrapper_page_id)->post_title;
			$wrapper_page_url = get_page_link($wrapper_page_id);
		}
	}

	?>
	<div class="wrap">
	<h1><?php echo RCTOOLS_PLUGIN_NAME; ?></h1>
  <h2>Settings</h2>
	<h3>Account Information </h3>
	<form name="form_settings" method="post" action="">

	<h3>IDXBroker API Key </h3>
	  <p>Every account includes the ability to generate one unique API key, which is required to access data from the API.<br/> You can manage your API key in your IDX control panel by clicking Home in the main menu, and <a href="https://middleware.idxbroker.com/mgmt/apikey.php">API Key Control</a> in the submenu.</p>
	  <p><strong>API Key: </strong></p><input type="text" name="realtycandy_apikey" value="<?php echo $opt_val; ?>" /><br/><br/>
	  <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</form>
	  
	<h3>IDXBroker Create a Dynamic Wrapper Page</h3>
	  <p>Customize content that wraps the IDX Broker search</p>
	  <form id="idxpagewrapper"  method="post" action="">
	  <p><strong>Dynamic URL: </strong></p><input name="realtycandy_dynamic_wrapper_page_name" type="text" id="realtycandy_dynamic_wrapper_page_name" value="<?php echo $realtycandy_wrapper; ?>" />
	  <input name="realtycandy_wrapper_id" type="hidden" id="realtycandy_wrapper_id" value="<?php echo get_option('realtycandy_wrapper_id'); ?>" />
		<input type="submit" class="button-primary" id="realtycandy_create_wrapper_page" value="<?php echo $realtycandy_wrapper ? 'Update' : 'Create' ?>" />
		<?php
		if ( get_option('realtycandy_wrapper_id') != '' )		{
		?>
		<input type="button" class="button-secondary" id="realtycandy_delete_wrapper_page" name="realtycandy_delete_wrapper_page" value="Delete" />
		<?php
		}
		?>
	  <span class="wrapper_status"></span>
	  <p class="error hidden">Please enter a page title</p>
	  <div id="dynamic_page_url" style="display: none;">
		<span class="label">Dynamic Page Link:</span>
		<div class="input-prepend">
		  <span id="protocol" class="label"></span>
		  <input id="page_link" type="text" class="regular-text" value="<?php echo $wrapper_page_url; ?>" readonly>
		  
		  <input type="button" class="button-secondary" id="copy-page-wrapper-url" value="Copy" />
		</form>
		<h3>Instructions</h3>
		<h4>Copy/Paste Your Dynamic Wrapper URL</h4>
		<p>Copy this URL and paste in your IDX account under Designs in the main menu, and Wrappers in the submenu. Speed access: <a href="http://middleware.idxbroker.com/mgmt/wrappers.php?
keepThis=true&TB_iframe=true&height=800&width=1200" class="thickbox">http://middleware.idxbroker.com/mgmt/wrappers.php</a></p>
		<img src="<?php echo plugins_url( 'images/idx-wrapper-menu.png' , __FILE__ ); ?>" />
		<h4>Save in the Control Panel</h4>
		<p>In the IDX Control Panel go to the Designs button and the Wrappers section. Choose which wrapper with which you would like to work, Global, Categories, Pages, or Saved Links, and choose Dynamic as the wrapper type. </p> 
		<img src="<?php echo plugins_url( 'images/idx-dynamic-wrapper.png' , __FILE__ ); ?>" />
		<p>Paste your URL into the Dynamic URL field. Make sure not to duplicate the http:// </p> 
		<img src="<?php echo plugins_url( 'images/idx-dynamic-url.png' , __FILE__ ); ?>" />
		<p>Click the 'Save Changes' button and you're done! View one of your IDX Page Links to see your new wrapper in action.</p>
		
	  </div>
	  
	</div><!-- end .wrap --> 