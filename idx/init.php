<?php
/**
 *
 * Loads files and initializes classes for setting up IDX integration
 *
 * Will not initialize IDX integration without an IDX API key and valid RC key
 */

add_action('rc_setup', 'rc_idx_features_init', 16);
function rc_idx_features_init() {

	require_once( RC_CLASSES_DIR . '/theme-updater.php' );
	$rc_api_manager = new RC_API_MANAGER();
	$activation_status = get_option( $rc_api_manager->rc_apiman_activated_key );

	$api_key = get_option( 'realtycandy_apikey' );

	if ( $api_key == '' || $activation_status == 'Deactivated' ) {
		return;
	}

	require_once 'class.RC_Idx_Api.inc.php';

	require_once 'class.RC_Quicksearch_Widget.inc.php';

	//require_once 'class.RC_Omnibar_Widget.inc.php';

	require_once 'class.RC_City_Links_Widget.inc.php';

	require_once 'class.RC_Showcase_Widget.inc.php';

	require_once 'class.RC_Carousel_Widget.inc.php';

	require_once 'class.RC_Lead_Login_Widget.inc.php';

	require_once 'class.RC_Lead_Signup_Widget.inc.php';

	require_once 'class.RC_Idx_Widget.inc.php';

	if (current_theme_supports( 'rc_idx_scrollspy' )) {
		require_once 'search-scrollspy.inc.php';
	}

	require_once 'class.RC_Idx_Content.inc.php';

	$_rc_idx_content = new RC_Idx_Content;

	add_action( 'widgets_init', 'rc_register_idx_widgets' );

	add_action( 'wp_enqueue_scripts', 'rc_enqueue_idx_stylesheet', 10 );
	
}


/**
 * Registers IDX widgets
 *
 * @return void
 */
function rc_register_idx_widgets() {
	register_widget('RC_Quicksearch_Widget');
	//register_widget('RC_Omnibar_Widget');
	register_widget('RC_City_Links_Widget');
	register_widget('RC_Showcase_Widget');
	register_widget('RC_IDX_Carousel_Widget');
	register_widget('RC_Lead_Login_Widget');
	register_widget('RC_Lead_Signup_Widget');
	register_widget('RC_Idx_Widget');
}
