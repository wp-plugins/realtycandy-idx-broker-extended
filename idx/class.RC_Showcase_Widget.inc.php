<?php
/**
 * Creates a widget that outputs a showcase of IDX properties
 *
 * @subpackage Widgets
 * @see RC_Idx_Api
 */
class RC_Showcase_Widget extends WP_Widget {
	public $_idx;
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$this->_idx = new RC_Idx_Api;
		parent::__construct(
	 		'rc_showcase', // Base ID
			'RealtyCandy :: IDX Property Showcase', // Name
			array(
				'description' => __( 'Displays a showcase of properties', 'realtycandy' ),
				'classname'   => 'rc-idx-showcase-widget'
			)
		);
	}
	/**
	 * Returns the markup for the featured properties
	 *
	 * @uses RC_Showcase_Widget::calc_percent()
	 * @param array $instance Previously saved values from database.
	 * @return string $output html markup for front end display
	 */
	public function body($instance) {
		$properties = $this->_idx->client_properties($instance['properties']);
		if ( empty($properties) ) {
			return 'No properties found';
		}
		// sort low to high
		usort($properties, array($this, 'price_cmp') );
		if ( 'high-low' == $instance['order'] ) {
			$properties = array_reverse($properties);
		}
		$max = $instance['max'];
		$num_per_row = $instance['listings_per_row'];
		$total = count($properties);
		$count = 0;
		$output = '';
		$column_class = '';
		if ( true == $instance['use_rows'] ) {
			// Max of four columns
			$number_columns = ( $num_per_row > 4 ) ? 4 : (int)$num_per_row;
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
		foreach ($properties as $prop) {
			if ( !empty($max) && $count == $max ) {
				return $output;
			}
			if ( 1 == $instance['use_rows'] && $count == 0 && $max != '1' ) {
				$output .= '<div class="row">';
			}
			$count++;
			if ( 1 == $instance['show_image'] ) {
				$output .= sprintf(
					'<div class="showcase-property %15$s">
						<a href="%3$s" class="showcase-photo">
							<img src="%4$s" alt="%5$s" title="%5$s" />
							<span class="price">%1$s</span>
							<span class="status">%2$s</span>
						</a>
						<a href="%3$s">
							<p class="address">
								<span class="street">%6$s %7$s %8$s %9$s</span>
								<span class="cityname">%10$s</span>,
								<span class="state"> %11$s</span>
							</p>
						</a>
						<p class="beds-baths-sqft">
							<span class="beds">%12$s Beds</span>
							<span class="baths">%13$s Baths</span>
							<span class="sqft">%14$s Sq Ft</span>
						</p>
					</div>',
					$prop['listingPrice'],
					$prop['propStatus'],
					$this->_idx->details_url() . '/' . $prop['detailsURL'],
					$prop['image']['0']['url'],
					$prop['remarksConcat'],
					$prop['streetNumber'],
					$prop['streetName'],				
					$prop['streetDirection'],
					$prop['unitNumber'],
					$prop['cityName'],
					$prop['state'],
					$prop['bedrooms'],
					$prop['totalBaths'],
					$prop['sqFt'],
					$column_class
				);
			} else {
				$output .= sprintf(
					'<li class="showcase-property-list %12$s">
						<a href="%2$s">
							<p>
								<span class="price">%1$s</span>
								<span class="address">
									<span class="street">%3$s %4$s %5$s %6$s</span>
									<span class="cityname">%7$s</span>,
									<span class="state"> %8$s</span>
								</span>
								<span class="beds-baths-sqft">
									<span class="beds">%9$s Beds</span>
									<span class="baths">%10$s Baths</span>
									<span class="sqft">%11$s Sq Ft</span>
								</span>
							</p>
						</a>
					</li>',
					$prop['listingPrice'],
					$this->_idx->details_url() . '/' . $prop['detailsURL'],
					$prop['streetNumber'],
					$prop['streetName'],				
					$prop['streetDirection'],
					$prop['unitNumber'],
					$prop['cityName'],
					$prop['state'],
					$prop['bedrooms'],
					$prop['totalBaths'],
					$prop['sqFt'],
					$column_class
				);
			}
			if ( 1 == $instance['use_rows'] && $count != 1 ) {
				// close a row if..
				// num_per_row is a factor of count OR
				// count is equal to the max number of listings to show OR
				// count is equal to the total number of listings available
				if ( $count % $num_per_row == 0 || $count == $total || $count == $max ) {
					$output .= '</div> <!-- .row -->';
				}
				// open a new row if..
				// num per row is a factor of count AND
				// count is not equal to max AND
				// count is not equal to total
				if ( $count % $num_per_row == 0 && $count != $max && $count != $total ) {
					$output .= '<div class="row">';
				}
			}
		}
		return $output;
	}
	/**
	 * Converts the decimal to a percent
	 *
	 * @param mixed $num decimal to convert
	 */
	function calc_percent($num) {
		$num = round($num, 2);
		$num = preg_replace('/0\./', '', $num);
		if ( strlen( (string)$num ) == 1 ) {
			$num *= 10;
		}
		$num = ( $num == 100 ) ? 100 : $num -= 4;
		return $num;
	}
	/**
	 * Compares the price fields of two arrays
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	public function price_cmp($a, $b) {
		$a = $this->clean_price($a['listingPrice']);
		$b = $this->clean_price($b['listingPrice']);
		if ( $a == $b ) {
			return 0;
		}
		return ( $a < $b ) ? -1 : 1;
	}
	/**
	 * Removes the "$" and "," from the price field
	 *
	 * @param string $price
	 * @return mixed $price the cleaned price
	 */
	public function clean_price($price) {
		$patterns = array(
			'/\$/',
			'/,/'
		);
		$price = preg_replace($patterns, '', $price);
		return $price;
	}
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
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
		echo $this->body($instance);
		echo $after_widget;
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['properties']       = strip_tags( $new_instance['properties'] );
		$instance['show_image']       = (bool) $new_instance['show_image'];
		$instance['listings_per_row'] = (int) $new_instance['listings_per_row'];
		$instance['max']              = strip_tags( $new_instance['max'] );
		$instance['order']            = strip_tags( $new_instance['order'] );
		$instance['use_rows']         = (bool) $new_instance['use_rows'];
		return $instance;
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$_idx = $this->_idx;
		$defaults = array(
			'title'            => __( 'Properties', 'realtycandy' ),
			'properties'       => 'featured',
			'show_image'       => '1',
			'use_rows'         => '1',
			'listings_per_row' => 4,
			'max'              => '',
			'order'            => 'high-low'
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'properties' ); ?>"><?php _e( 'Properties to Display:', 'realtycandy' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'properties' ); ?>" name="<?php echo $this->get_field_name( 'properties' ) ?>">
				<option <?php selected($instance['properties'], 'featured'); ?> value="featured"><?php _e( 'Featured', 'realtycandy' ); ?></option>
				<option <?php selected($instance['properties'], 'soldpending'); ?> value="soldpending"><?php _e( 'Sold/Pending', 'realtycandy' ); ?></option>
				<option <?php selected($instance['properties'], 'supplemental'); ?> value="supplemental"><?php _e( 'Supplemental', 'realtycandy' ); ?></option>
				<option <?php selected($instance['properties'], 'historical'); ?> value="historical"><?php _e( 'Historical', 'realtycandy' ); ?></option>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_image'], 1); ?> id="<?php echo $this->get_field_id( 'show_image' ); ?>" name="<?php echo $this->get_field_name( 'show_image' ); ?>" value="1" />
			<label for="<?php echo $this->get_field_id( 'show_image' ); ?>"><?php _e( 'Show image?', 'realtycandy'); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['use_rows'], 1); ?> id="<?php echo $this->get_field_id( 'use_rows' ); ?>" name="<?php echo $this->get_field_name( 'use_rows' ); ?>" value="1" />
			<label for="<?php echo $this->get_field_id( 'use_rows' ); ?>"><?php _e( 'Use rows?', 'realtycandy'); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'listings_per_row' ); ?>"><?php _e( 'Listings per row:', 'realtycandy' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'listings_per_row' ); ?>" name="<?php echo $this->get_field_name( 'listings_per_row' ) ?>">
				<option <?php selected($instance['listings_per_row'], '2'); ?> value="2">2</option>
				<option <?php selected($instance['listings_per_row'], '3'); ?> value="3">3</option>
				<option <?php selected($instance['listings_per_row'], '4'); ?> value="4">4</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'max' ); ?>"><?php _e( 'Max number of listings to show:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'max' ); ?>" name="<?php echo $this->get_field_name( 'max' ); ?>" type="text" value="<?php esc_attr_e( $instance['max'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Sort order:', 'realtycandy' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ) ?>">
				<option <?php selected($instance['order'], 'high-low'); ?> value="high-low"><?php _e( 'Highest to Lowest Price', 'realtycandy' ); ?></option>
				<option <?php selected($instance['order'], 'low-high'); ?> value="low-high"><?php _e( 'Lowest to Highest Price', 'realtycandy' ); ?></option>
			</select>
		</p>
		<?php
	}
}