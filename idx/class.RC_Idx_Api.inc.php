<?php
/**
 * Contains methods for interacting with the IDX API
 *
 * @package IDX Integration
 * @link http://middleware.idxbroker.com/docs/api/index.html
 */
class RC_Idx_Api {

	public $api_key;

	public function __construct() {
		$this->api_key = get_option('realtycandy_apikey');
	}

	public function remote_get( $level, $method, $http_method = 'GET' ) {

		global $wp_version;

		$key = $this->api_key;

		$response = wp_remote_get(
			'https://api.idxbroker.com/' . $level . '/' . $method,
			array(
				'method' => $http_method,
				'timeout' => apply_filters( 'http_request_timeout', 5),
				'redirection' => apply_filters( 'http_request_redirection_count', 5),
				'httpversion' => apply_filters( 'http_request_version', '1.0'),
				'user-agent' => apply_filters( 'http_headers_useragent', 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )  ),
				'blocking' => true,
				'headers' => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => $key,
					'outputtype'   => 'json'
				),
				'cookies' => array(),
				'body' => null,
				'compress' => false,
				'decompress' => true,
				'sslverify' => false,
				'stream' => false,
				'filename' => null
			)
		);

		return $response;
		print_r($response);
		echo '<p>https://api.idxbroker.com/</p>' . $level . '/' . $method;
	}

	/**
	 * Returns the json_decoded response body of wp_remote_get response
	 *
	 * @param string $level clients, partners, mls, or leads
	 * @param string $method component level method for the api request
	 * @param bool $assoc if true converts objects to associate array
	 * @return array json decoded response body
	 */
	public function decoded_response_body( $level, $method, $assoc = 0 ) {

		$response = $this->remote_get($level, $method);

		if(!is_wp_error($response))
			return json_decode($response['body'], $assoc);
	}

	/**
	 * Returns an array of city objects for the agents mls area
	 *
	 * @return array $default_cities
	 */
	public function default_cities() {

		if ( false === ( $default_cities = get_transient('default_cities') ) ) {
			$default_cities = $this->decoded_response_body('clients', 'cities/combinedActiveMLS');
			set_transient('default_cities', $default_cities, 60*60*12);
		}

		return $default_cities;
	}

	/**
	 * Returns an array of city list ids
	 *
	 * @return array $list_ids
	 */
	public function city_list_ids() {

		if ( false === ( $list_ids = get_transient('city_list_ids') ) ) {
			$list_ids = $this->decoded_response_body('clients', 'cities');
			set_transient('city_list_ids', $list_ids, 60*60*12);
		}

		return $list_ids;
	}

	/**
	 * Returns a list of cities
	 *
	 * @return array $city_list
	 */
	public function city_list( $list_id ) {

		$transient = 'city_list_' . $list_id;

		if ( false === ( $city_list = get_transient($transient) ) ) {
			$city_list = $this->decoded_response_body('clients', 'cities/' . $list_id);
			set_transient($transient, $city_list, 60*60*12);
		}

		return $city_list;
	}

	/**
	 * Returns the IDs and names for each of a client's city lists including MLS city lists
	 *
	 * @return array
	 */
	public function city_list_names() {

		$transient = 'city_list_names';

		if ( false === ( $city_list_names = get_transient($transient) ) ) {
			$city_list_names = $this->decoded_response_body('clients', 'citieslistname');
			set_transient($transient, $city_list_names, 60*60*12);
		}

		return $city_list_names;
	}

	/**
	 * Returns an unordered list of city links
	 *
	 * @param int|string the id of the city list to pull cities from
	 * @param bool $columns if true adds column classes to the ul tags
	 * @param int $number_of_columns optional total number of columns to split the links into
	 */
	public function city_list_links( $list_id, $idx_id, $columns = 0, $number_columns = 4 ) {

		$cities = $this->city_list($list_id);

		if ( !$cities ) {
			return false;
		}

		$column_class = '';

		if ( true == $columns ) {

			// Max of four columns
			$number_columns = ( $number_columns > 4 ) ? 4 : (int)$number_columns;

			$number_links = count($cities);

			$column_size = $number_links / $number_columns;

			// if more columns than links make one column for every link
			if ( $column_size < 1 ) {
				$number_columns = $number_links;
			}

			// round the column size up to a whole number
			$column_size = ceil($column_size);

			// column class
			switch ($number_columns) {
				case 0:
					$column_class = 'columns small-12 large-12';
					break;
				case 1:
					$column_class = 'columns small-12 large-12';
					break;
				case 2:
					$column_class = 'columns small-12 medium-6 large-6';
					break;
				case 3:
					$column_class = 'columns small-12 medium-4 large-4';
					break;
				case 4:
					$column_class = 'columns small-12 medium-3 large-3';
					break;
			}
		}

		$output =
		'<div class="city-list-links city-list-links-' . $list_id . ' row">' . "\n\t";

		$output .= ( true == $columns ) ? '<ul class="' . $column_class . '">' : '<ul>';

		$count = 0;

		foreach ($cities as $city) {

			$count++;

			$href = $this->subdomain_url() . 'city-' . $idx_id . '-' . rawurlencode($city->name) . '-' . $city->id;

			$output .= "\n\t\t" . '<li>' . "\n\t\t\t" . '<a href="' . $href . '">' . $city->name . '</a>' . "\n\t\t" . '</li>';

			if ( true == $columns && $count % $column_size == 0 && $count != 1 && $count != $number_links ) {
				$output .= "\n\t" . '</ul>' . "\n\t" . '<ul class="' . $column_class . '">';
			}

		}

		$output .= "\n\t" . '</ul>' . "\n" . '</div><!-- .city-list-links -->';

		return $output;
	}

	/**
	 * Returns an array of property types objects for the agents mls area
	 *
	 * @return array $property_types
	 */
	public function property_types() {

		if ( false === ( $property_types = get_transient('property_types') ) ) {
			$property_types = $this->decoded_response_body('mls', 'propertytypes');
			set_transient('property_types', $property_types, 60*60*12);
		}

		return $property_types;
	}

	/**
	 * Returns all the system pages (search, featured, contact, etc)
	 */
	public function system_links() {

		if ( false === ( $system_links = get_transient('system_links') ) ) {
			$system_links = $this->decoded_response_body('clients', 'systemlinks');
			set_transient('system_links', $system_links, 60*60*12);
		}

		return $system_links;
	}

	/**
	 * Returns the url for the clients system results page
	 *
	 * @return bool|string
	 */
	public function system_results_url() {

		$links = $this->system_links();

		if ( !$links ) {
			return false;
		}

		foreach ($links as $link) {
			if ( $link->systemresults ) {
				$results_url = $link->url;
			}
		}

		// What if or can they have more than one system results page?
		if ( isset($results_url) ) {
			return $results_url;
		}

		return false;
	}

	/**
	 * Returns the url of the link
	 *
	 * @param string $name name of the link to return the url of
	 * @return bool|string
	 */
	public function system_link_url($name) {

		$links = $this->system_links();

		if ( !$links ) {
			return false;
		}

		foreach ($links as $link) {
			if ( $name == $link->name ) {
				return $link->url;
			}
		}

		return false;
	}

	/**
	 * Returns the url of the first system link found with
	 * a category of "details"
	 *
	 * @return bool|string link url if found else false
	 */
	public function details_url() {

		$links = $this->system_links();

		if ( !$links ) {
			return false;
		}

		foreach ($links as $link) {
			if ( 'details' == $link->category ) {
				return $link->url;
			}
		}

		return false;
	}

	/**
	 * Returns an array of system link urls
	 *
	 * @return array
	 */
	public function all_system_link_urls() {

		$links = $this->system_links();

		if ( !$links ) {
			return array();
		}

		$system_link_urls = array();

		foreach ($links as $link) {
			$system_link_urls[] = $link->url;
		}

		return $system_link_urls;
	}

	/**
	 * Returns an array of system link names
	 *
	 * @return array
	 */
	public function all_system_link_names() {

		$links = $this->system_links();

		if ( !$links ) {
			return array();
		}

		$system_link_names = array();

		foreach ($links as $link) {
			$system_link_names[] = $link->name;
		}

		return $system_link_names;
	}

	/**
	 * Returns the IDX IDs and names for all of the paper work approved MLSs
	 * on the client's account
	 */
	public function approved_mls() {

		$transient = 'approved_mls';

		if ( false === ( $approved_mls = get_transient($transient) ) ) {
			$approved_mls = $this->decoded_response_body('mls', 'approvedmls');
			set_transient($transient, $approved_mls, 60*60*12);
		}

		return $approved_mls;
	}

	/**
	 * Returns the subdomain url WITH trailing slash
	 *
	 * @return string $url
	 */
	public function subdomain_url() {

		$url = $this->system_link_url('Sitemap');
		$url = explode('sitemap', $url);

		return $url[0];
	}

	/**
	 * Deletes all transient data stored by the idx api class
	 *
	 * @return void
	 */
	public function delete_all_transient_data() {

		delete_transient('system_links');
		delete_transient('city_list_ids');
		delete_transient('default_cities');
		delete_transient('idx_widgets');
		delete_transient('city_list_names');
		delete_transient('approved_mls');

		$property_types = array(
			'featured',
			'soldpending',
			'supplemental',
			'historical'
		);

		foreach ($property_types as $type) {
			delete_transient($type . '_properties');
		}

		$lists = $this->city_list_ids();

		if (empty($lists)) {
			return;
		}

		foreach ($lists as $key => $value) {
			delete_transient('city_list_' . $value);
		}
	}

	/**
	 * Clears the page wrapper cache
	 *
	 * @return bool true if success
	 */
	public function clear_wrapper_cache() {

		$response = $this->remote_get('clients','wrappercache','DELETE');

		if ( $response['response']['code'] == '204' ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns property data for the $type parameter
	 *
	 * @param string $type featured, soldpending, supplemental, historical
	 */
	public function client_properties($type) {


    $response = $this->decoded_response_body('clients', $type, true);
    return $response;
    
		if ( false === ( $properties = get_transient($type . '_rcproperties') ) ) {
			$properties = $this->decoded_response_body('clients', $type, true);
			set_transient($type . '_rcproperties', $properties, 60*60*12);
		}

		//return $properties;
    
	}

	/**
	 * Returns an array of urls to widget source code
	 */
	public function idx_widgets() {

		if ( false === ( $widgets = get_transient('idx_widgets') ) ) {
			$widgets = $this->decoded_response_body('clients', 'widgetsrc');
			set_transient('idx_widgets', $widgets, 60*60*12);
		}

		return $widgets;
	}
}