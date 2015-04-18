<?php
/**
 * Creates a quicksearch widget tied to the agents idx account
 *
 * @package IDX Integration
 * @see RC_Idx_Api
 * @subpackage Widgets
 */
class RC_Omnibar_Widget extends WP_Widget {
	/**
	 * Instance of the RC_Idx_Api class
	 */
	public $_idx;
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$this->_idx = new RC_Idx_Api;
		parent::__construct(
	 		'rc_omnibar', // Base ID
			'RealtyCandy :: IDX Omnibar Search', // Name
			array(
				'description' => __( 'IDX Omnibar quick search widget. Search city, listing ID, or address from one field.', 'realtycandy' ),
				'classname'   => 'rc-idx-omnbiar-widget'
			)
		);
	}
	/**
	 * Outputs the html of the widget for front end display
	 */
	public function body( $instance ) {
		$_idx = $this->_idx;
		?>
		<div class="rc-omnibar-search-form horizontal row">
			
			<div class="columns small-12 large-9">
				<div class="rc-qs-city columns small-12 large-12">
					<!-- For autocomplete
				<input type="text" id="qs-city-select" class="rc-qs-city-select" name="city" data-provide="typeahead" data-source="<?php //echo $this->city_list_data_source($instance); ?>" placeholder="Start typing a city name" value="Start typing a city name" onblur="if (this.value == '') {this.value = 'Start typing a city name';}" onfocus="if (this.value == 'Start typing a city name') {this.value = '';}" autocomplete="off">-->
					<input id="cities" class="omnibar" type="text" placeholder="Search by City, Address, or Listing ID" onblur="if (this.value == '') {this.value = 'Search CRMLS by City, Address, or Listing ID';}" onfocus="if (this.value == 'Search CRMLS by City, Address, or Listing ID') {this.value = '';}">
				</div><!-- .rc-qs-city -->
				<div class="rc-qs-price columns small-12 large-6">
					<input id="search-min-price" class="rc-qs-price-min" name="lp" type="text" placeholder="Min Price" onblur="if (this.value == '') {this.value = 'Min Price';}" onfocus="if (this.value == 'Min Price') {this.value = '';}">
					<span>to</span>
					<input id="search-max-price" class="max-price" name="hp" type="text" placeholder="Max Price" onblur="if (this.value == '') {this.value = 'Max Price';}" onfocus="if (this.value == 'Max Price') {this.value = '';}">
				</div><!-- .rc-qs-price -->
				<div class="rc-qs-beds columns small-6 large-3">
					<input id="search-beds" class="rc-qs-beds" name="bd" type="text" placeholder="Beds" onblur="if (this.value == '') {this.value = 'Beds';}" onfocus="if (this.value == 'Beds') {this.value = '';}">
				</div><!-- .rc-qs-beds -->
				<div class="rc-qs-baths columns small-6 large-3">
					<input id="search-baths" class="rc-qs-baths" name="tb" type="text" placeholder="Baths" onblur="if (this.value == '') {this.value = 'Baths';}" onfocus="if (this.value == 'Baths') {this.value = '';}">
				</div><!-- .rc-qs-baths -->
			</div>
			<div class="rc-qs-submit-btn columns small-12 large-3">
				<button id="gosearch" class="rc-qs-submit-button button expand" onClick="check()"><i class="fa fa-search"></i><span class="button-text"> <?php echo esc_attr( $instance['button_text'] ); ?></span></button>
				<div class="rc-qs-links">
					<a class="advanced-search" href="<?php echo $_idx->subdomain_url() . 'search'; ?>"><?php _e( 'Advanced Search', 'realtycandy' ); ?></a>
					<a class="map-search" href="<?php echo $_idx->subdomain_url() . 'map/mapsearch'; ?>"><?php _e( 'Map Search', 'realtycandy' ); ?></a>
				</div><!-- .rc-qs-links -->
			</div><!-- .rc-qs-submit -->
		
		</div>
	<?php
	}
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$_idx = $this->_idx;
		wp_enqueue_script( 'rc-idx-omnibar', RC_JS_URL . '/omnibar.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-autocomplete' ), true);
		wp_localize_script( 'rc-idx-omnibar', 'rc_omnibar', array(
			'citydata'    => $this->city_list_data_source($instance),
			'results_url' => $_idx->system_results_url()
			)
		);
		extract( $args );
		$title = $instance['title'];
		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		echo $this->body( $instance );
		echo $after_widget;
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['city_list']   = strip_tags( $new_instance['city_list'] );
		$instance['button_text'] = strip_tags( $new_instance['button_text'] );
		return $instance;
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$_idx = $this->_idx;
		$defaults = array(
			'title'       => __( 'Property Search', 'realtycandy' ),
			'city_list'   => 'combinedActiveMLS',
			'button_text' => 'Search Now'
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'city_list' ); ?>"><?php _e( 'Select a city list:', 'realtycandy' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'city_list' ); ?>" name="<?php echo $this->get_field_name( 'city_list') ?>">
				<option value="combinedActiveMLS">Combined active MLS</option>
				<?php $this->city_list_options($instance); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e( 'Search Button Text:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" type="text" value="<?php esc_attr_e( $instance['button_text'] ); ?>" />
		</p>
		<?php
	}
	/**
	 * Echos city list ids wrapped in option tags
	 *
	 * This is just a helper to keep the html clean
	 *
	 * @param var $instance
	 */
	public function city_list_options($instance) {
		$lists = $this->_idx->city_list_names();
		if ( !is_array($lists) ) {
			return;
		}
		foreach($lists as $list) {
			echo '<option ', selected($instance['city_list'], $list->id, 0), ' value="', $list->id, '">', $list->name, '</option>';
		}
	}
	/**
	 * Returns an array of the cities in the combined active MLS for autocomplete
	 */
	public function city_list_data_source( $instance ) {
		if ( !isset($instance['city_list'] ) ) {
			$instance['city_list'] = 'combinedActiveMLS';
		}
		$cities = $this->_idx->city_list($instance['city_list']);
		if ( ! $cities ) {
			return;
		}
		$count = '';
		$output = '';
		foreach ($cities as $city) {
			$count++;
			if ( '' == $city->name ) {
				continue;
				
			}
			// Clean city names of single quotes which break the form
			$clean_city_name = str_replace("'", "", $city->name);
			$output .= '"' . $city->id . '","' . $clean_city_name . '"';
			if ( $count != count($cities) ) {
				$output .= ',';
			}
		}
		return '[' . stripslashes($output) . ']';
	}
}