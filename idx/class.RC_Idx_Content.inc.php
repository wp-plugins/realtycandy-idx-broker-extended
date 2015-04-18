<?php
/**
 * Customizes the admin and registers post types
 *
 * @package IDX Integration
 */
class RC_Idx_Content {

	public function __construct() {

		add_action( 'init',              array($this, 'register_idx_post_types'), 9 );

		add_action( 'admin_init',        array($this, 'create_idx_pages'), 10 );

		add_action( 'admin_init',        array($this, 'delete_idx_pages') );

		add_filter( 'post_type_link',    array($this, 'post_type_link_filter_func'), 10, 2 );

		add_action( 'save_post',         array($this, 'clear_static_wrapper_cache') );

		add_action( 'admin_menu',        array($this, 'add_clear_idx_cache_admin_page') );

		add_action( 'admin_init',        array($this, 'show_idx_pages_metabox_by_default'), 20 );

		add_action( 'equity_before_loop', array($this, 'idxbroker_start'), 5 );

		add_action( 'equity_after_loop',  array($this, 'idxbroker_stop'), 5 );
	}

	/**
	 * Registers idx post types 'rc_idx_page' and 'idx_wrapper'
	 *
	 * 'rc_idx_page is mainly for use in building custom menus out
	 * of IDX pages created in the IDX dashboard. It is only visible
	 * on the admin menus page.
	 *
	 * 'idx-wrapper' allows the client to assign custom wrappers to
	 * their idx pages in the idx dashboard. They can modify the layout
	 * and seo settings and assign an idx page to that wrapper.
	 *
	 * @return void
	 */
	public function register_idx_post_types() {

	    $args = array(
	        'label'             => 'IDX Pages',
	        'labels'            => array( 'singular_name' => 'IDX Page' ),
	        'public'            => true,
	        'show_ui'           => false,
	        'show_in_nav_menus' => true,
	        'rewrite'           => false
	    );

	    register_post_type('rc_idx_page', $args);

	    $args = array(
	        'label'               => 'Wrappers',
	        'labels'              => array( 'singular_name' => 'Wrapper' ),
	        'public'              => true,
	        'show_in_nav_menus'   => false,
	        'exclude_from_search' => true,
	        'supports'            => array( 'title', 'editor', 'rc-layouts', 'thumbnail' )
	    );

	    register_post_type('idx-wrapper', $args);
	}


	/**
	 * Creates a post of the 'rc_idx_page' post type for each of the client's system links
	 *
	 * @uses RC_Idx_Api::system_links()
	 * @uses RC_Idx_Content::sanitize_title_filter()
	 * @uses RC_Idx_Content::get_existing_idx_page_urls()
	 * @return void
	 */
	function create_idx_pages() {

		$_idx = new RC_Idx_Api;

		$system_links = $_idx->system_links();

		if ( empty($system_links) ) {
			return;
		}

		$existing_page_urls = $this->get_existing_idx_page_urls();

		foreach ($system_links as $link) {

			if ( !in_array($link->url, $existing_page_urls) ) {

				$post = array(
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_name'      => $link->url,
					'post_content'   => '',
					'post_status'    => 'publish',
					'post_title'     => $link->name,
					'post_type'      => 'rc_idx_page'
				);

				// filter sanitize_tite so it returns the raw title
				add_filter('sanitize_title', array($this, 'sanitize_title_filter'), 10, 2 );

				wp_insert_post( $post );
			}
		}
	}

	/**
	 * Removes sanitization on the post_name
	 *
	 * Without this the ":","/", and "." will be removed from post slugs
	 * The filter is only added in the rc_create_idx_pages() function
	 *
	 * @return string $raw_title title without sanitization applied
	 */
	function sanitize_title_filter( $title, $raw_title ) {
		return $raw_title;
	}

	/**
	 * Deletes IDX pages that dont have a url or title matching a systemlink url or title
	 *
	 * @uses RC_Idx_Api::all_system_link_urls()
	 * @uses RC_Idx_Api::all_system_link_names()
	 * @return void
	 */
	function delete_idx_pages() {

		$posts = get_posts(array( 'post_type' => 'rc_idx_page', 'numberposts' => -1 ));

		if ( empty($posts) ) {
			return;
		}

		$_idx = new RC_Idx_Api;

		$system_link_urls = $_idx->all_system_link_urls();

		$system_link_names = $_idx->all_system_link_names();

		if ( empty($system_link_urls) || empty($system_link_names) ) {
			return;
		}

		foreach ($posts as $post) {
			// post_name oddly refers to permalink in the db
			// if an idx hosted page url or title has been changed,
			// delete the page from the wpdb
			// the updated page will be repopulated automatically
			if ( !in_array($post->post_name, $system_link_urls) || !in_array($post->post_title, $system_link_names) ) {
				wp_delete_post($post->ID);
			}
		}
	}

	/**
	 * Disables appending of the site url to the post permalink
	 *
	 * @return string $post_link
	 */
	function post_type_link_filter_func( $post_link, $post ) {

		if ( 'rc_idx_page' == $post->post_type ) {
			return $post->post_name;
		}

		return $post_link;
	}

	/**
	 * Deletes all posts of the "rc_idx_page" post type
	 *
	 * @return void
	 */
	function delete_all_idx_pages() {

		$posts = get_posts(array('post_type' => 'rc_idx_page', 'numberposts' => -1));

		if ( empty($posts) ) {
			return;
		}

		foreach ($posts as $post) {
			wp_delete_post($post->ID);
		}
	}

	/**
	 * Returns an array of existing idx page urls
	 *
	 * These are the page urls in the wordpress database
	 * not from the IDX dashboard
	 *
	 * @return array $existing urls of existing idx pages if any
	 */
	function get_existing_idx_page_urls() {

		$posts = get_posts(array('post_type' => 'rc_idx_page', 'numberposts' => -1));

		$existing = array();

		if ( empty($posts) ) {
			return $existing;
		}

		foreach ($posts as $post) {
			$existing[] = $post->post_name;
		}

		return $existing;
	}

	/**
	 * Clears the static wrapper cache after a wrapper post is saved
	 */
	function clear_static_wrapper_cache($post_id) {

		$type = get_post_type($post_id);

		// only run on idx-wrapper posts
		if ( 'idx-wrapper' != $type ) {
			return;
		}

		$_idx = new RC_Idx_Api;
		$_idx->clear_wrapper_cache();
	}

	/**
	 * Adds an admin page under tools for clearing idx transient data
	 */
	function add_clear_idx_cache_admin_page() {

		add_management_page( 'Clear IDX Cache', 'Clear IDX Cache', 'manage_options', 'clear-idx-cache', array($this, 'clear_idx_cache_admin_page_content') );
	}

	/**
	 * Outputs the content for the Clear IDX Cache page
	 */
	function clear_idx_cache_admin_page_content() {

		?>
		<div class="wrap">
			<h2>Clear IDX Cache</h2>
			<p>
				IDX related data is cached to decrease page load time. If you create a new IDX Page in the IDX dashboard and it doesn't show up for selection when editing menus, you should clear the cache here and try again.
			</p>
			<form action="" method="post">
				<input type="submit" name="clear_cache" class="button-primary" value="Clear IDX Cache" />
			</form>
		</div>

		<?php

		if ( !isset($_POST['clear_cache']) ) {
			return;
		}

		$_idx = new RC_Idx_Api;
		$_idx->delete_all_transient_data();

		if ( FALSE === get_transient('system_links') ) {
			echo '
			<div id="message" class="updated">
				<p>IDX cache successfully cleared!</p>
			</div>
			';
		} else {
			echo '
			<div id="message" class="error">
				<p>Cache not cleared. Try again.</p>
			</div>
			';
		}
	}

	/**
	 * Updates the 'metaboxhidden_idx-wrapper' user meta to show
	 * idx_pages on the nav-menus admin screen by default
	 *
	 * Preferably this function would run on the user_register action,
	 * but idx features won't exist when the first user registers.
	 *
	 * @return void
	 */
	function show_idx_pages_metabox_by_default() {

		$user = wp_get_current_user();

		$user_first_login = get_user_meta($user->ID, 'rc_user_first_login', true);

		// Only update the user meta on the first login (after IDX features have been enabled).
		// This ensures that the user can hide the IDX Pages metabox again if they want
		if ( ! empty($user_first_login) ) {
			return;
		}

		$hidden_metaboxes_on_nav_menus_page = (array) get_user_meta($user->ID, 'metaboxhidden_nav-menus', true);

		foreach ( $hidden_metaboxes_on_nav_menus_page as $key => $value) {

			if ( $value == 'add-rc_idx_page' ) {
				unset($hidden_metaboxes_on_nav_menus_page[$key]);
			}
		}

		update_user_meta($user->ID, 'metaboxhidden_nav-menus', $hidden_metaboxes_on_nav_menus_page);

		// add a meta field to keep track of the first login
		update_user_meta($user->ID, 'rc_user_first_login', 'user_first_login_false');
	}

	/**
	 * Adds IDX Start tag if is idx-wrapper post type
	 *
	 * @since  1.1.1
	 */
	function idxbroker_start() {
		if( is_singular( 'idx-wrapper' ) ) {
	    	echo '<style>.entry-title, .entry-meta {display: none;}</style><div id="idxStart"></div>';
	    }
	}

	/**
	 * Adds IDX Stop tag if is idx-wrapper post type
	 * 
	 * @since  1.1.1
	 */
	function idxbroker_stop() {
		if( is_singular( 'idx-wrapper' ) ) {
	    	echo '<div id="idxStop"></div>';
	    }
	}
}