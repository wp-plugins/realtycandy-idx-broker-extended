<?php
/**
 * Creates a quicksearch widget tied to the agents idx account
 *
 * @package IDX Integration
 * @see RC_Idx_Api
 * @subpackage Widgets
 */
class RC_Quicksearch_Widget extends WP_Widget {
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
	 		'rc_quicksearch', // Base ID
			'RealtyCandy :: IDX Quick Search', // Name
			array(
				'description' => __( 'IDX quick search widget', 'realtycandy' ),
				'classname'   => 'rc-idx-search-widget'
			)
		);
	}
	/**
	 * Outputs the html of the widget for front end display
	 */
	public function body( $instance ) {
		$_idx = $this->_idx;
		if ( $instance['orientation'] == 'vertical' ) {
		?>
		<form class="rc-quicksearch-form vertical" method="post" action="<?php echo get_template_directory_uri() . '/lib/idx/search.php'; ?>">
			
			<div class="rc-qs-city">
				<label for="rc-qs-city-select" class="rc-qs-city-label">City</label>
				
				<select id="bqf-city-select" class="bqf-city-select" name="city[]">
					<option value="">Select a City</option>
					<?php $this->selected_city_list_city_options($instance); ?>
				</select>
			</div><!-- .rc-qs-city -->
			<div class="rc-qs-price-min">
				<label for="rc-qs-price-min" class="rc-qs-price-min-label">Price Min</label>
				<input type="text" id="rc-qs-price-min" class="rc-qs-price-min-input input" name="lp" />
			</div><!-- .rc-qs-price-min -->
			<div class="rc-qs-price-max">
				<label for="rc-qs-price-max" class="rc-qs-price-max-label">Price Max</label>
				<input type="text" id="rc-qs-price-max" class="rc-qs-price-max-input input" name="hp" />
			</div><!-- .rc-qs-price-max -->
			<div class="rc-qs-beds">
				<label for="rc-qs-beds" class="rc-qs-beds-label">Beds</label>
				<input type="text" id="rc-qs-beds" class="rc-qs-beds-input input" name="bd" />
			</div><!-- .rc-qs-beds -->
			<div class="rc-qs-baths">
				<label for="rc-qs-baths" class="rc-qs-baths-label">Baths</label>
				<input type="text" id="rc-qs-baths" class="rc-qs-baths-input input" name="ba" />
			</div><!-- .rc-qs-baths -->
			<input type="hidden" name="results_url" value="<?php echo $_idx->system_results_url(); ?>" />
			<div class="rc-qs-submit rc-qs-form-bottom">
				<div class="rc-qs-submit-btn">
					<button class="rc-qs-submit-button button" type="submit" name="submit"><i class="fa fa-search"></i><span class="button-text"> <?php echo esc_attr( $instance['button_text'] ); ?></span></button>
				</div>
				<a class="advanced-search" href="<?php echo $_idx->subdomain_url() . 'search'; ?>"><?php _e( 'Advanced Search', 'realtycandy' ); ?></a>
				<a class="map-search" href="<?php echo $_idx->subdomain_url() . 'map/mapsearch'; ?>"><?php _e( 'Map Search', 'realtycandy' ); ?></a>
			</div><!-- .rc-qs-form-bottom -->
		</form>
		<?php
		} else {
		?>
		<form class="rc-quicksearch-form horizontal row" method="post" action="<?php echo get_template_directory_uri() . '/lib/idx/search.php'; ?>">
			
			<div class="columns small-12 large-9">
				<div class="rc-qs-city columns small-12 large-12">
					<select id="bqf-city-select" class="bqf-city-select" name="city[]">
						<option value="">Select a City</option>
						<?php $this->selected_city_list_city_options($instance); ?>
					</select>
				</div><!-- .rc-qs-city -->
				<div class="rc-qs-price columns small-12 large-6">
					<input id="search-min-price" class="rc-qs-price-min" name="lp" type="text" placeholder="Min Price" onblur="if (this.value == '') {this.value = 'Min Price';}" onfocus="if (this.value == 'Min Price') {this.value = '';}">
					<span>to</span>
					<input id="search-max-price" class="rc-qs-price-max" name="hp" type="text" placeholder="Max Price" onblur="if (this.value == '') {this.value = 'Max Price';}" onfocus="if (this.value == 'Max Price') {this.value = '';}">
				</div><!-- .rc-qs-price -->
				<div class="rc-qs-beds columns small-6 large-3">
					<input id="search-beds" class="rc-qs-beds" name="bd" type="text" placeholder="Beds" onblur="if (this.value == '') {this.value = 'Beds';}" onfocus="if (this.value == 'Beds') {this.value = '';}">
				</div><!-- .rc-qs-beds -->
				<div class="rc-qs-baths columns small-6 large-3">
					<input id="search-baths" class="rc-qs-baths" name="tb" type="text" placeholder="Baths" onblur="if (this.value == '') {this.value = 'Baths';}" onfocus="if (this.value == 'Baths') {this.value = '';}">
				</div><!-- .rc-qs-baths -->
			</div> <!-- .small-12 large-3 -->

			<div class="rc-qs-submit-btn columns small-12 large-3">
				<input type="hidden" name="results_url" value="<?php echo $_idx->system_results_url(); ?>" />
				<button class="rc-qs-submit-button button expand" type="submit" name="submit"><i class="fa fa-search"></i><span class="button-text"> <?php echo esc_attr( $instance['button_text'] ); ?></span></button>
				<div class="rc-qs-links">
					<?php if ( $instance['adv_search']) {
						echo '<a class="advanced-search" href="' . apply_filters( 'rc_qs_adv_link', $rcy_qs_adv_link = $_idx->subdomain_url() . 'search') . '">' . __( 'Advanced Search', 'realtycandy' ) .'</a>';
					} ?>
					<?php if ( $instance['map_search']) {
						echo '<a class="map-search" href="' . apply_filters( 'rc_qs_map_link', $rc_qs_map_link = $_idx->subdomain_url() . 'map/mapsearch') . '">' . __( 'Map Search', 'realtycandy' ) .'</a>';
					} ?>
				</div><!-- .rc-qs-links -->
			</div><!-- .rc-qs-submit -->
		
		</form>
	<?php }
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
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['city_list'] = strip_tags($new_instance['city_list']);
		$instance['button_text'] = strip_tags($new_instance['button_text']);
		$instance['orientation'] = ($new_instance['orientation']);
		$instance['adv_search'] = (int) ($new_instance['adv_search']);
		$instance['map_search'] = (int) ($new_instance['map_search']);
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
			'button_text' => 'Search Now',
			'orientation' => 'vertical',
			'adv_search'  => 1,
			'map_search'  => 1
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
		<p>
			<label for="<?php echo $this->get_field_id( 'orientation' ); ?>"><?php _e( 'Orientation:', 'realtycandy' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'orientation' ); ?>" name="<?php echo $this->get_field_name( 'orientation') ?>">
				<option value="vertical" <?php selected( 'vertical', $instance['orientation'] ); ?>><?php _e( 'Vertical', 'realtycandy' ); ?></option>
				<option value="horizontal" <?php selected( 'horizontal', $instance['orientation'] ); ?>><?php _e( 'Horizontal', 'realtycandy' ); ?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'adv_search' ); ?>" name="<?php echo $this->get_field_name( 'adv_search' ); ?>" value="1" <?php checked( $instance['adv_search'], true ); ?> />
			<label for="<?php echo $this->get_field_id( 'adv_search' ); ?>"><?php _e( 'Include Advanced Search link?', 'realtycandy' ); ?></label>
			
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'map_search' ); ?>" name="<?php echo $this->get_field_name( 'map_search' ); ?>" value="1" <?php checked( $instance['map_search'], true ); ?> />
			<label for="<?php echo $this->get_field_id( 'map_search' ); ?>"><?php _e( 'Include Map Search link?', 'realtycandy' ); ?></label>
			
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
	 * Echos the city names of the selected city list wrapped in option tags
	 *
	 * This is just a helper to keep the html clean
	 */
	public function selected_city_list_city_options( $instance ) {
		if ( !isset($instance['city_list'] ) ) {
			$instance['city_list'] = 'combinedActiveMLS';
		}
		$cities = $this->_idx->city_list($instance['city_list']);
		if ( !$cities ) {
			return;
		}
		foreach ($cities as $city_object) {
			echo '<option value="', $city_object->id, '">', $city_object->name, '</option>';
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
			$output .= '"' . $clean_city_name . ' (' . $city->id . ')"';
			if ( $count != count($cities) ) {
				$output .= ',';
			}
		}
		return '[' . $output . ']';
	}
}