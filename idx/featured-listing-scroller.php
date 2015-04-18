<?php
/**
 * Return registered image sizes.
 *
 * Return a two-dimensional array of just the additionally registered image sizes, with width, height and crop sub-keys.
 *
 * @since 1.0
 *
 * @global array $_wp_additional_image_sizes Additionally registered image sizes.
 *
 * @return array Two-dimensional, with width, height and crop sub-keys.
 */
function rc_get_additional_image_sizes() {
	global $_wp_additional_image_sizes;
	if ( $_wp_additional_image_sizes )
		return $_wp_additional_image_sizes;
	return array();
}
/* Add Scripts and Styles */
function rcidxwp_enqueue_style() {
	wp_enqueue_style( 'owl-css', plugins_url( 'css/owl.carousel.css', __FILE__ ), array(), '1.0.0', true );
}
function rcidxwp_enqueue_script() {
  wp_register_script( 'owl', plugins_url( 'js/owl.carousel.min.js', __FILE__ ), array( 'jquery' ) );  
  wp_enqueue_script( 'owl' );
}
add_action( 'wp_enqueue_scripts', 'rcidxwp_enqueue_style' );
add_action( 'wp_enqueue_scripts', 'rcidxwp_enqueue_script' );
/**
 *
 * Adds a Featured Listings widget for WP Listings that enables responsive scrolling.
 *
 * @package Widgets
 * @author  Agent Evolution
 * @license GPL-2.0+
 * @link    
 */
class RC_Featured_Listings_Scroller extends WP_Widget {
	function RC_Featured_Listings_Scroller() {
		$widget_ops  = array( 'classname' => 'rc-listing-scroller clearfix owlcarousel', 'description' => __( 'Display featured listings in a responsive scroller', 'realtycandy' ) );
		$control_ops = array( 'width' => 300, 'height' => 350 );
		$this->WP_Widget( 'rc-listing-scroller', __( 'RealtyCandy :: Featured Listings Scroller', 'realtycandy' ), $widget_ops, $control_ops );
	}
	function widget( $args, $instance ) {
		$prev_link = apply_filters( 'listing_scroller_prev_link', $listing_scroller_prev_link_text = __( '<i class=\"fa fa-chevron-circle-left\"></i><span>Prev</span>', 'realtycandy' ) );
		$next_link = apply_filters( 'listing_scroller_next_link', $listing_scroller_next_link_text = __( '<i class=\"fa fa-chevron-circle-right\"></i><span>Next</span>', 'realtycandy' ) );
		extract( $args );
		if( $instance['autoplay'] ) {
			$autoplay = 'autoPlay: true,';
		} else {
			$autoplay = '';
		}
		$posts_per_scroller = $instance['posts_per_widget'];
		if($posts_per_scroller === 1) {
			echo '
			<script>
			jQuery(function( $ ){
				$(".rc-listing-scroller-' . $posts_per_scroller . '").owlCarousel({
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
			echo '
			<script>
			jQuery(function( $ ){
				$(".rc-listing-scroller-' . $posts_per_scroller . '").owlCarousel({
					items: ' . $posts_per_scroller . ',
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

		echo $before_widget;
			if ( !empty( $instance['title'] ) ) {
				echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;
			}
			if ( !empty( $instance['posts_term'] ) ) {
	            $posts_term = explode( ',', $instance['posts_term'] );
        	}
			$query_args = array(
				'post_type'			=> 'listing',
				'posts_per_page'	=> $instance['posts_per_page'],
				'paged'				=> get_query_var('paged') ? get_query_var('paged') : 1
			);
			if ( isset($posts_term) && count($posts_term) == 2 ) {
				$query_args[$posts_term['0']] = $posts_term['1'];
			}
			$wp_listings_widget_query = new WP_Query( $query_args );
			echo '<div class="listings-scroller rc-listing-scroller-' . $instance['posts_per_widget'] . '">';
			global $post;
			if ( $wp_listings_widget_query->have_posts() ) : while ( $wp_listings_widget_query->have_posts() ) : $wp_listings_widget_query->the_post();
				$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $instance['image_size'] );
				$loop = sprintf( '<div class="listing-widget-thumb"><a href="%s" class="listing-image-link"><img class="lazyOwl" data-src="%s" /></a>', get_permalink(), $thumbnail_src[0] );
				if ( '' != wp_listings_get_status() ) {
					$loop .= sprintf( '<span class="listing-status %s">%s</span>', strtolower(wp_listings_get_status()), wp_listings_get_status() );
				}
				$loop .= sprintf( '<div class="listing-thumb-meta">' );
				if ( '' != get_post_meta( $post->ID, '_listing_text', true ) ) {
					$loop .= sprintf( '<span class="listing-text">%s</span>', get_post_meta( $post->ID, '_listing_text', true ) );
				} elseif ( '' != wp_listings_get_property_types() ) {
					$loop .= sprintf( '<span class="listing-property-type">%s</span>', wp_listings_get_property_types() );
				}
				if ( '' != get_post_meta( $post->ID, '_listing_price', true ) ) {
					$loop .= sprintf( '<span class="listing-price">%s</span>', get_post_meta( $post->ID, '_listing_price', true ) );
				}
				$loop .= sprintf( '</div><!-- .listing-thumb-meta --></div><!-- .listing-widget-thumb -->' );
				if ( '' != get_post_meta( $post->ID, '_listing_open_house', true ) ) {
					$loop .= sprintf( '<span class="listing-open-house">Open House: %s</span>', get_post_meta( $post->ID, '_listing_open_house', true ) );
				}
				$loop .= sprintf( '<div class="listing-widget-details"><h3 class="listing-title"><a href="%s">%s</a></h3>', get_permalink(), get_the_title() );
				$loop .= sprintf( '<p class="listing-address"><span class="listing-address">%s</span><br />', wp_listings_get_address() );
				$loop .= sprintf( '<span class="listing-city-state-zip">%s, %s %s</span></p>', wp_listings_get_city(), wp_listings_get_state(), get_post_meta( $post->ID, '_listing_zip', true ) );
				if ( '' != get_post_meta( $post->ID, '_listing_bedrooms', true ) || '' != get_post_meta( $post->ID, '_listing_bathrooms', true ) || '' != get_post_meta( $post->ID, '_listing_sqft', true )) {
					$loop .= sprintf( '<ul class="listing-beds-baths-sqft"><li class="beds">%s<span>Beds</span></li> <li class="baths">%s<span>Baths</span></li> <li class="sqft">%s<span>Sq ft</span></li></ul>', get_post_meta( $post->ID, '_listing_bedrooms', true ), get_post_meta( $post->ID, '_listing_bathrooms', true ), get_post_meta( $post->ID, '_listing_sqft', true ) );
				}
				$loop .= sprintf('</div><!-- .listing-widget-details -->');
				$loop .= sprintf( '<a href="%s" class="button btn-primary more-link">%s</a>', get_permalink(), __( 'View Listing', 'realtycandy' ) );
				/** wrap in div with possible column class, and output **/
				printf( '<div class="listing post-%s"><div class="listing-wrap">%s</div></div>', $post->ID, apply_filters( 'wp_listings_featured_listings_widget_loop', $loop ) );
			endwhile; endif;
			echo '</div><!-- .rc-listing-scroller -->';
			wp_reset_postdata();
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
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['posts_per_page']   = (int) $new_instance['posts_per_page'];
		$instance['posts_per_widget'] = (int) $new_instance['posts_per_widget'];
		$instance['image_size'] 	  = strip_tags($new_instance['image_size'] );	
		$instance['posts_term']       = strip_tags( $new_instance['posts_term'] );
		$instance['autoplay']         = (int) $new_instance['autoplay'];
		return $instance;
	}
	function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'title'				=> '',
			'posts_per_page'	=> 3,
			'posts_per_widget'	=> 4,
			'image_size'		=> 'listings',
			'posts_term'        => '',
			'autoplay'          => 1
		) );
		printf(
			'<p><label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" style="%s" /></p>',
			$this->get_field_id('title'),
			__( 'Title:', 'realtycandy' ),
			$this->get_field_id('title'),
			$this->get_field_name('title'),
			esc_attr( $instance['title'] ),
			'width: 95%;'
		); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size', 'realtycandy' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'image_size' ); ?>" class="wp-listings-image-size-selector" name="<?php echo $this->get_field_name( 'image_size' ); ?>">
				<option value="thumbnail">thumbnail (<?php echo absint( get_option( 'thumbnail_size_w' ) ); ?>x<?php echo absint( get_option( 'thumbnail_size_h' ) ); ?>)</option>
				<?php
				$sizes = rc_get_additional_image_sizes();
				foreach ( (array) $sizes as $name => $size )
					echo '<option value="' . esc_attr( $name ) . '" ' . selected( $name, $instance['image_size'], FALSE ) . '>' . esc_html( $name ) . ' (' . absint( $size['width'] ) . 'x' . absint( $size['height'] ) . ')</option>';
				?>
			</select>
		</p>
		<?php
		printf(
			'<p>%s <input type="text" name="%s" value="%s" size="3" /></p>',
			__( 'How many results should be returned?', 'realtycandy' ),
			$this->get_field_name('posts_per_page'),
			esc_attr( $instance['posts_per_page'] )
		);
		printf(
			'<p>%s <input type="text" name="%s" value="%s" size="3" /></p>',
			__( 'How many results should be shown in the widget at one time without scrolling?', 'realtycandy' ),
			$this->get_field_name('posts_per_widget'),
			esc_attr( $instance['posts_per_widget'] )
		);
		echo '<p><label for="'. $this->get_field_id( 'posts_term' ) .'">Display by term:</label>
		<select id="'. $this->get_field_id( 'posts_term' ) .'" name="'. $this->get_field_name( 'posts_term' ) .'">
			<option style="padding-right:10px;" value="" '. selected( '', $instance['posts_term'], false ) .'>'. __( 'All Taxonomies and Terms', 'realtycandy' ) .'</option>';
			$taxonomies = get_object_taxonomies('listing');
			foreach ( $taxonomies as $taxonomy ) {
				$the_tax_object = get_taxonomy($taxonomy);
				echo '<optgroup label="'. esc_attr( $the_tax_object->label ) .'">';
				$terms = get_terms( $taxonomy, 'orderby=name&hide_empty=1' );
				foreach ( $terms as $term ) {
					echo '<option style="margin-left: 8px; padding-right:10px;" value="'. esc_attr( $the_tax_object->query_var ) . ',' . $term->slug .'" '. selected( esc_attr( $the_tax_object->query_var ) . ',' . $term->slug, $instance['posts_term'], false ) .'>-' . esc_attr( $term->name ) .'</option>';
				}
				echo '</optgroup>';
			}
		echo '</select></p>';
		printf(
			'<p><label for="%s">%s</label> <input type="checkbox" name="%s" value="1" %s /></p>',
			$this->get_field_id('autoplay'),
			__( 'Autoplay?', 'realtycandy' ),
			$this->get_field_name('autoplay'),
			checked( $instance['autoplay'], true, false )
		);
	}
}