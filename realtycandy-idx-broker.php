<?php
/* Plugin Name: RealtyCandy IDX Broker Tools for Wordpress
 * Plugin URI: http://realtycandy.com/plugins/
 * Description: RealtyCandy IDX Broker Tools for Wordpress
 * Version: 1.5
 * Author: Realty Candy
 * Author URI: http://realtycandy.com
*/

/* WP Ajax */
# This script then triggers the AJAX request when the page is fully loaded:
add_action( 'admin_footer', 'rcidxwordpress_javascript' ); // Write our JS below here

function rcidxwordpress_javascript() { ?>
  	  <script>
		(function() {
			jQuery(document).ready(function($){
			if( $('#realtycandy_dynamic_wrapper_page_name').length > 0 &&  $('#realtycandy_dynamic_wrapper_page_name').val() !== '' ) {
        var linkData = $('#page_link').val().split('//');
        var protocol= linkData[0];
        var link = linkData[1];
        $('#protocol').text(protocol+'//');
        $('#page_link').val(link);
        $('#dynamic_page_url').show();
			}

    	$('#realtycandy_create_wrapper_page').click(function(event) {
		    event.preventDefault();
		    var post_title = $('#realtycandy_dynamic_wrapper_page_name').val();
		    var wrapper_page_id = $('#realtycandy_wrapper_id').val();
		    $('#realtycandy_dynamic_wrapper_page_name').removeClass('error');
		    $('#dynamic_page > p.error').hide();
		    if ( post_title === '') {
		    	$('#realtycandy_dynamic_wrapper_page_name').addClass('error');
		    	$('#dynamic_page > p.error').show();
		    	return;
		    }
	  // Since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	  // Create Page
      $.post(ajaxurl, {
        		'action': 'rcidxwordpress',
				'todo': 'realtycandy_create_dynamic_page',
				'post_title': post_title,
				'wrapper_page_id': wrapper_page_id
			}).done(function(response){
        var page = jQuery.parseJSON(response);
        if (page.wrapper_page_id != '') {
					setTimeout(window.location.reload(), 1000);
        }
			});
		});
        
        // Delete Page
	    $('#realtycandy_delete_wrapper_page').click(function () {
	    	var wrapper_page_id = $('#realtycandy_wrapper_id').val();
		    $.post(ajaxurl, {
          			'action': 'rcidxwordpress',
					'todo': 'realtycandy_delete_dynamic_page',
					'wrapper_page_id': wrapper_page_id
				}).done(function(response){
					// save form
					var status = $('.wrapper_status');
					status.fadeIn('fast').html('Deleting IDX Wrapper Page...');
					save_form_options('', function() {
						status.fadeIn('fast').html(' Refreshing Page...');
						setTimeout(window.location.reload(), 1000);
					});
				});
	    });
			}); 
    
  /* JS  Functions */
  function save_form_options (params, callback) {
		params = params || jQuery('#idxpagewrapper').serialize();
		params += '&' + jQuery('[name=action]').serialize();
		return jQuery.ajax({
	  		type: "POST",
	   		url: ajaxurl,
	   		data: params,
	   		success: function(data) {
    			jQuery('[name=action]').val('update');
				callback();
		  	}
 		});
	}
    
    })(window, undefined);
	</script>
<?php
}

# Set up a PHP function to handle the AJAX request.
add_action( 'wp_ajax_rcidxwordpress', 'rcidxwordpress_callback' );

function rcidxwordpress_callback() {
  global $wpdb; // this is how you get access to the database
  
  /**
   * Functions to create and delete dynamic wrapper page 
   */
    if ( $_POST['todo'] == 'realtycandy_create_dynamic_page' ) {
    
		// default page content
		$post_content = '<div id="idxStart" style="display: none;"></div><div id="idxStop" style="display: none;"></div>';
	
		// get theme to check start/stop tag
		$isThemeIncludeIdxTag = false;
		$template_root = get_theme_root().'/'.get_stylesheet();
	
		$files = scandir( $template_root );
	
		foreach ($files as $file)
		{
			$path = $template_root . '/' . $file;
			if (is_file($path) && preg_match('/.*\.php/',$file))
			{
				$content = file_get_contents($template_root . '/' . $file);
				if (preg_match('/<div[^>\n]+?id=[\'"]idxstart[\'"].*?(\/>|><\/div>)/i', $content))
				{
					if(preg_match('/<div[^>\n]+?id=[\'"]idxstop[\'"].*?(\/>|><\/div>)/i',$content))
					{
						$isThemeIncludeIdxTag = true;
						break;
					}
				}
			}
		}
		if ($isThemeIncludeIdxTag)
		$post_content = '';
		$post_content .= '<style>.entry-title{display:none;}</style>';
		$ptitle = sanitize_text_field($_POST['post_title']); //Sanitize Title
		$post_title = $ptitle ? $ptitle : 'IDX Dynamic Wrapper Page';
      
    	// Update Post
    	$wrapper_page_id = intval( $_POST['wrapper_page_id'] ) ? $_POST['wrapper_page_id'] : '';
		if ( $wrapper_page_id ) {
		    $new_post = array(
		      	'ID' => $wrapper_page_id,
				'post_title' => $post_title,
				'post_name' => $post_title,
				'post_content' => $post_content,
				'post_type' => 'page',
				'post_status' => 'publish'
			);
	  		wp_update_post( $new_post );
	      	update_option('realtycandy_dynamic_wrapper_page_name', $post_title);
			update_option('wrapper_page_id', $wrapper_page_id);
			wp_die(json_encode(array("wrapper_page_id"=>$wrapper_page_id, "wrapper_page_name" => $post_title)));
		} 
		//Insert New Post
    	$new_post = array(
			'post_title' => $post_title,
			'post_name' => $post_title,
			'post_content' => $post_content,
			'post_type' => 'page',
			'post_status' => 'publish'
		);
		$wrapper_page_id = wp_insert_post($new_post);
		update_option('realtycandy_dynamic_wrapper_page_name', $post_title);
		update_option('realtycandy_wrapper_id', $wrapper_page_id);
		wp_die(json_encode(array("wrapper_page_id"=>$wrapper_page_id, "wrapper_page_name" => $post_title))) ;
  	}
  
	/**
	 * Delete page
	 */
	if ( $_POST['todo'] == 'realtycandy_delete_dynamic_page' ) {
    
		if ( $wrapper_page_id ) {
            echo '<h2>Delete action ID</h2>'.$wrapper_page_id;
			wp_delete_post($wrapper_page_id, true);
			wp_trash_post($wrapper_page_id);
      		update_option('realtycandy_dynamic_wrapper_page_name', '');
			update_option('realtycandy_wrapper_id', '');
		}
		echo 'action: delete';
	}

  wp_die(); // this is required to terminate immediately and return a proper response
}

/* Ajax for Clear Wrapper Cache */
add_action( 'admin_footer', 'rcclearwrapper_javascript' ); // Write our JS below here
function rcclearwrapper_javascript() {
  ?>
  <script>
  jQuery(document).ready(function($){
    $('#clearwrapper').click(function(event) {
      event.preventDefault();
      console.log('clear wrapper');
      
        $.post(ajaxurl, {
          'action': 'rcclearwrapper'
        }).done(function(response){
          var message = jQuery.parseJSON(response);
          //console.log('response: '+response);
          $('.response').addClass(message.class).html(message.response);
        });
     
    
    });
  });
  </script>
  <?php
}

add_action( 'wp_ajax_rcclearwrapper', 'rcclearwrapper_callback' );
function rcclearwrapper_callback() {
	//get api key
  $apikey = get_option( 'realtycandy_apikey' );
  
  if ( !isset($apikey) ) wp_die( 'Go to settings page and set your API Key.' );
  
  
  // access URL and request method
  $url = 'https://api.idxbroker.com/clients/wrappercache';
  $method = 'DELETE';
  
  // headers (required and optional)
  $headers = array(
    'Content-Type: application/x-www-form-urlencoded', // required
    'accesskey: ' . $apikey, // required - replace with your own
    'outputtype: json' // optional - overrides the preferences in our API control page
  );
  
  // set up cURL
  $handle = curl_init();
  curl_setopt($handle, CURLOPT_URL, $url);
  curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
  
  // exec the cURL request and returned information. Store the returned HTTP code in $code for later reference
  $response = curl_exec($handle);
  $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
  
  if ($code == 204)
    $response = true;
  else
    $response = false;
  
switch ($code) {
    case 200:
  		$class = "updated";
      $response = "Successfully cleared wrapper cache";
      break;
    case 204:
  		$class = "updated";
      $response = "Successfully cleared wrapper cache";
      break;
    case 400: 
    $class = "error";
    $response = "Required parameter missing or invalid.";
      break;
    case 401:
      $class = "error";
      $response = "accesskey not valid or revoked.";
      break;
    case 403.4: 
    $class = "error";
    $response = "URL provided is not using SSL (HTTPS).";
      break;
    case 405: 
      $class = "error";
      $response = "Method requested is invalid. This usually indicates a typo or that you may be requested a method that is part of a different API component.";
      break;
    case 406: 
    $class = "error";
    $response = "accesskey not provided";
      break;
    case 409:
      $class = "error";
      $response = "Invalid API component specified.";
      break;
    case 412: 
    $class = "error";
    $response = "Account is over it's hourly access limit.";
      break;
    case 500: 
    $class = "error";
    $response = "General system error. Please try again later or contact IDX support.";
      break;
    case 503:
    $class = "error";
    $response = "Scheduled or emergency API maintenance will result in 503 errors.";
      break;
    case 521:
    $class = "error";
    $response = "Temporary error. There is a possibility that not all API methods are affected.";
      break;
}  


  $response = $response.' <span class="code" style="font-size:10px">(code '.$code.')</span>';
  echo json_encode(array("response"=>$response, "class" => $class));
  
  wp_die(); // this is required to terminate immediately and return a proper response
}


/* Plugin Name */
define('RCTOOLS_PLUGIN_NAME', 'RealtyCandy IDX Broker');


/**
 * Add Plugin Menu
 */
function realtycandy_menu() {
	add_menu_page( 'Clear your dynamic wrapper cache', 'RealtyCandy Tools IDX Broker', 'manage_options_null', 'realtycandy_idx_tools', 'clear_dynamic_wrapper_options', plugins_url( 'realtycandy-logo-detail.png' , __FILE__ ) , '58' );
	add_submenu_page('realtycandy_idx_tools', 'Clear Wrapper Cache', 'Clear Wrapper Cache', 'manage_options', 'clear_dynamic_wrapper_options', 'clear_dynamic_wrapper_options');
	add_submenu_page('realtycandy_idx_tools', 'Widgets', 'Widgets', 'manage_options', 'activate_widgets', 'activate_widgets');
  	$page_settings_hook_suffix =  add_submenu_page('realtycandy_idx_tools', 'Settings', 'Settings', 'manage_options', 'realtycandy_settings', 'realtycandy_settings');

  	        
	 /*
      * Use the retrieved $page_hook_suffix to hook the function that links our script.
      * This hook invokes the function only on our plugin administration screen,
      * see: http://codex.wordpress.org/Administration_Menus#Page_Hook_Suffix
      */
    add_action('admin_print_scripts-' . $page_settings_hook_suffix, 'rcidxwp_settings_admin_scripts');

}
add_action( 'admin_menu', 'realtycandy_menu' );

/**
 * Register JS Scripts and CSS
 */
function rcidxwp_admin_scripts() {
	wp_register_script( 'zclip-js', plugins_url( '/js/jquery.zclip.min.js' , __FILE__ )  );

}
add_action( 'admin_init', 'rcidxwp_admin_scripts' );

/**
 * Loads JS Scripts and CSS on all admin pages
 */
function rcidxwp_admin_load_scripts() {
		wp_register_script( 'my_custom_script', plugins_url( '/js/custom.js' , __FILE__ )  );
  		wp_enqueue_script( 'my_custom_script' );

        wp_register_style( 'custom_wp_admin_css', plugins_url( '/css/custom-admin-style.css' , __FILE__ ), false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );

        $custom_css = "
	      #wpbody .green-bt {
	        background: rgb(28, 184, 65);
	        color: white;
	        box-shadow: inset 0 1px 0 rgb(72, 219, 107),0 1px 0 rgba(0,0,0,.08);
	        border-color: rgb(24, 147, 53);
	      }";
        wp_add_inline_style( 'custom-settings-style', $custom_css );
}
add_action( 'admin_enqueue_scripts', 'rcidxwp_admin_load_scripts' );

/**
 * Link our already registered script to Settings Page - Admin Print Scriptss
 */
function rcidxwp_settings_admin_scripts() {
 
    wp_enqueue_script( 'zclip-js' );

    echo '<script>\n';
      echo "jQuery('#copy-page-wrapper-url').zclip({";
      echo "path:'<?php echo plugins_url('/js/ZeroClipboard.swf', __FILE__); ?>',";
      echo "copy:function(){return jQuery('input#page_link').val();},";
      echo "afterCopy:function(){";
      echo "jQuery('#copy-page-wrapper-url').val('Copied!').addClass('green-bt');";
      echo "}";
	  echo "});";
	echo '\n</script>';
}

/**
 * Clear Dynamic Wrapper Cache
 */
function clear_dynamic_wrapper_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
	<h1><?php echo RCTOOLS_PLUGIN_NAME; ?></h1>
	<h2>Clear your dynamic wrapper cache</h2>
  <div class="response"></div>
	<p>When dynamic wrappers are in use in the idx system the values that are gathered are cached for a period of 2 hours to increase page load performance and decrease load on your site.</p>
	<p>This method will force that cached to be cleared to that a fresh version of your wrapper is gathered the next time a page is loaded.</p>
	  <form name="form_clear_wrapper" method="post" action="">
	<input type="submit" name="Submit" id="clearwrapper" class="button-primary" value="<?php esc_attr_e('Clear Wrapper') ?>" />
	</form>
	</div>
	<?php
}

/**
 * All Widgets
 */
function activate_widgets() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

  	//get api key and registered email
	$apikey = get_option( 'realtycandy_apikey' );
  
  	?>
	<div class="wrap">
	<h1><?php echo RCTOOLS_PLUGIN_NAME; ?></h1>
  <h2>Agents Widget</h2>
	<p>Your Widgets are <?php echo $status = ($apikey ? '<span class="greenButton">Active</span>' : '<span class="redButton">Inactive</span>'); ?></p>
	<h2>Available Widgets</h2>
	  <ul>
		<li>RealtyCandy :: IDX City Links</li>
		<li>RealtyCandy :: IDX Dashboard Widget</li>
		<li>RealtyCandy :: IDX Property Carousel</li>
		<li>RealtyCandy :: Featured Listings Scroller (WP Listings)</li>
	  </ul>
	<?php
}


/**
 * Settings
 */
function realtycandy_settings() {
  
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	include_once('settings-content.php');
}


/***
* Generate a thumbnail on the fly 
* Used in IDX Carousel Widget
*
* @return thumbnail url
***/
if ( !function_exists('get_reduced_img') ) {
  function get_reduced_img($src_url='', $width=null, $height=null, $crop = true, $cached = true) {
  	if(!$src_url) return false;
  if ( empty( $src_url ) ) throw new Exception('Invalid source URL '.$src_url);
  if ( empty( $width ) ) $width = get_option( 'thumbnail_size_w' );
  if ( empty( $height ) ) $height = get_option( 'thumbnail_size_h' );

  $src_info = pathinfo($src_url);

  $upload_info = wp_upload_dir();
  $upload_dir = $upload_info['basedir'].'\realtycandy-uploads';
  $upload_url = $upload_info['baseurl'].'/realtycandy-uploads';
  // Experimental
  $thumb_name = preg_replace("/http:\/\/[^\/]+\/[^\/]+\/([^\/]+)\/([^\/]+)/", "$1-$2", $src_url).'_'.$width.'X'.$height.'.jpg';
  //$thumb_name = $src_info['filename'].'_'.$width.'X'.$height.'.'.$src_info['extension'];
  //$thumb_name = md5(uniqid(rand(), true)).'_'.$width.'X'.$height.'.jpg'; //Get IDX images correctly from those that do not have a file extension

  if ( FALSE === strpos( $src_url, home_url() ) ){
  $source_path = $upload_info['path'].'/realtycandy-uploads/'.$src_info['basename'];
  //$source_path = $upload_info['path'].'/realtycandy-uploads/'.md5(uniqid(rand(), true));
  $thumb_path = $upload_info['path'].'/realtycandy-uploads/'.$thumb_name;
  $thumb_url = $upload_info['url'].'/realtycandy-uploads/'.$thumb_name;
  if (!file_exists($upload_info['path'].'/realtycandy-uploads/')) {
    mkdir($upload_info['path'].'/realtycandy-uploads/', 0775, true);
	}
  if (!file_exists($source_path) && !copy($src_url, $source_path)) {
  throw new Exception('No permission on upload directory: '.$upload_info['path']);
  }

  }else{
  // define path of image
  $rel_path = str_replace( $upload_url, '', $src_url );
  $source_path = $upload_dir . $rel_path;
  $source_path_info = pathinfo($source_path);
  $thumb_path = $source_path_info['dirname'].'/'.$thumb_name;

  $thumb_rel_path = str_replace( $upload_dir, '', $thumb_path);
  $thumb_url = $upload_url . $thumb_rel_path;
  }
  if($cached && file_exists($thumb_path)) return $thumb_url;

  $editor = wp_get_image_editor( $src_url );
  $editor->resize( $width, $height, $crop );
  $new_image_info = $editor->save( $thumb_path );

  if(empty($new_image_info)) throw new Exception('Failed to create thumb: '.$thumb_path);

  return $thumb_url;
  }
}

$apikey = get_option( 'realtycandy_apikey' );
$client_email = get_option( 'realtycandy_client_email' ); 


if ($apikey != '') { //Check if api key is set 

	/**
	 * IDX Widgets
	 *
	 ******************************************/
	require_once 'idx/class.RC_Idx_Api.inc.php';
	require_once 'idx/class.RC_City_Links_Widget.inc.php';
	require_once 'idx/class.RC_Idx_Widget.inc.php';
	require_once 'idx/class.RC_Carousel_Widget.inc.php';
	require_once 'idx/featured-listing-scroller.php';
  
	/**
	* Registers IDX widgets
	*
	* @return void
	*/
	function realtycandy_register_idx_widgets() {
	register_widget('RC_City_Links_Widget');
	register_widget('RC_Idx_Widget');
	register_widget('RC_IDX_Carousel_Widget');
		if(function_exists('wp_listings_init')) {
			register_widget( 'RC_Featured_Listings_Scroller' );
		}
	}
	add_action( 'widgets_init', 'realtycandy_register_idx_widgets' );



 
 } else {

 	/**
	 * Notice about plugin Settings 
	 *
	 ******************************************/
	function realtycandy_tools_settings_notice() {
	    ?>
	    <div class="update-nag">
	        <p><?php echo RCTOOLS_PLUGIN_NAME.' needs your IDX API Key to work correctly! Please, go to <a href="admin.php?page=realtycandy_settings">Settings</a> page and set your IDX API Key.'; ?></p>
	    </div>
	    <?php
	}
	add_action( 'admin_notices', 'realtycandy_tools_settings_notice' );
 }
  

?>