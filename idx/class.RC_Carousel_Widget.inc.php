<?php
/**
 * 
 * Creates a widget that outputs a carousel of IDX properties
 *
 */
class RC_IDX_Carousel_Widget extends WP_Widget {
	public $_idx;
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$this->_idx = new RC_Idx_Api;
		parent::__construct(
	 		'rc_carousel', // Base ID
			'RealtyCandy :: IDX Property Carousel', // Name
			array(
				'description' => __( 'Displays a carousel of properties', 'realtycandy' ),
				'classname'   => 'rc-idx-carousel-widget'
			)
		);
	}
  
  
	/**
	 * Returns the markup for the listings
	 *
	 * @uses RC_IDX_Carousel_Widget::calc_percent()
	 * @param array $instance Previously saved values from database.
	 * @return string $output html markup for front end display
	 */
	public function body($instance) {

  /* Add Scripts and Styles */
  function rcidxwp_carousel_enqueue_style() {
    wp_enqueue_style( 'rcidxwp-carousel-owl-css', plugins_url( 'css/owl.carousel.css', __FILE__ ), array(), '1.0.0', true );
  }
  
  function rcidxwp_carousel_enqueue_script() {
    wp_register_script( 'rcidxwp-carousel-owl', plugins_url( 'js/owl.carousel.min.js', __FILE__ ), array( 'jquery' ) );  
    wp_enqueue_script( 'rcidxwp-carousel-owl' );
  }
  
  add_action( 'wp_enqueue_scripts', 'rcidxwp_carousel_enqueue_style' );
  add_action( 'wp_enqueue_scripts', 'rcidxwp_carousel_enqueue_script' );

		$prev_link = apply_filters( 'idx_listing_carousel_prev_link', $idx_listing_carousel_prev_link_text = __( '<i class=\"fa fa-chevron-circle-left\"></i><span>Prev</span>', 'realtycandy' ) );
		$next_link = apply_filters( 'idx_listing_carousel_next_link', $idx_listing_carousel_next_link_text = __( '<i class=\"fa fa-chevron-circle-right\"></i><span>Next</span>', 'realtycandy' ) );
		$properties = $this->_idx->client_properties($instance['properties']);
    
    //$this->_idx->delete_all_transient_data();
    /*
    echo "<h4>First property item in Array:</h4>";
    foreach($properties as $p){
      print_r($p);
      echo "<hr/>";
      break;      
    }   
		*/
		
    
		if ( empty($properties) ) {
			return 'No properties found<br>';
		}	
		if( $instance['autoplay'] ) {
			$autoplay = 'autoPlay: true,';
		} else {
			$autoplay = '';
		}
		$display = $instance['display'];
		if($display === 1) {
			echo '
			<script>
			jQuery(function( $ ){
				$(".rc-listing-carousel-' . $display . '").owlCarousel({
					singleItem: true,
					' . $autoplay . '
					navigation: true,
					navigationText: ["' . $prev_link . '", "' . $next_link . '"],
					pagination: false,
					lazyLoad: true,
					addClassActive: true,
					itemsScaleUp: true
				});
			});
			</script>
			';
		} else {
			echo  '
			<script>
			jQuery(function( $ ){
				$(".rc-listing-carousel-' . $display . '").owlCarousel({
					items: ' . $display . ',
					' . $autoplay . '
					navigation: true,
					navigationText: ["' . $prev_link . '", "' . $next_link . '"],
					pagination: false,
					lazyLoad: true,
					addClassActive: true,
					itemsScaleUp: true
				});
			});
			</script>
			';
		}
		// sort low to high
		usort($properties, array($this, 'price_cmp') );
		if ( 'high-low' == $instance['order'] ) {
			$properties = array_reverse($properties);
		}
		$max = $instance['max'];
		$total = count($properties);
		$count = 0;
		$output = '';
		$output .= sprintf('<div class="rc-idx-carousel rc-listing-carousel-%s">', $instance['display']);
echo '<pre>';
//print_r($properties);
echo '</pre>';
		foreach ($properties as $prop) {
            
 			if ( !empty($max) && $count == $max ) {
				return $output;
			}
			$count++;
   
      //Validate Image URL
      if ( empty($prop['image']['0']['url']) ) { 
        //$prop['image']['0']['url'] = plugins_url('/images/house-placeholder.png', dirname(__FILE__)); 
      }
      
			$output .= sprintf(
				'<div class="carousel-property">
					<a href="%2$s" class="carousel-photo">
						<img class="lazyOwl" data-src="%3$s" alt="%4$s" title="%4$s" />
						<span class="price">%1$s</span>
					</a>
					<a href="%2$s">
						<p class="address">
							<span class="street">%5$s %6$s %7$s %8$s</span>
							<span class="cityname">%9$s</span>,
							<span class="state"> %10$s</span>
						</p>
					</a>
					<p class="beds-baths-sqft">
						<span class="beds">%11$s Beds</span>
						<span class="baths">%12$s Baths</span>
						<span class="sqft">%13$s Sq Ft</span>
					</p>
				</div>',
				$prop['listingPrice'],
				$this->_idx->details_url() . '/' . $prop['detailsURL'],
				get_reduced_img($prop['image']['0']['url'], 200, 120, true, true),
        //$propimage[$count],
        //$prop['image']['0']['url'], //original idx image
				$prop['remarksConcat'],
				$prop['streetNumber'],
				$prop['streetName'],				
				$prop['streetDirection'],
				$prop['unitNumber'],
				$prop['cityName'],
				$prop['state'],
				$prop['bedrooms'],
				$prop['totalBaths'],
				$prop['sqFt']
			);
		}
		$output .= '';
		return $output;
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
		$instance['display']          = (int) $new_instance['display'];
		$instance['max']              = (int) ( $new_instance['max'] );
		$instance['order']            = strip_tags( $new_instance['order'] );
		$instance['autoplay']         = $new_instance['autoplay'];
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
			'display'          => 3,
			'max'              => 15,
			'order'            => 'high-low',
			'autoplay'         => 1
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
			<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Listings to show without scrolling:', 'realtycandy' ); ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ) ?>" value="<?php esc_attr_e( $instance['display'] ); ?>" size="3">
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
		<p>
			<label for="<?php echo $this->get_field_id( 'autoplay' ); ?>"><?php _e( 'Autoplay?', 'realtycandy' ); ?></label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ) ?>" value="1" <?php checked( $instance['autoplay'], true ); ?>>
		</p>
		<?php
	}
}