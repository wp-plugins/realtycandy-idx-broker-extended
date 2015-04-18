<?php
/**
 * Creates a widget that allows embedding of an idx dashboard created widget
 *
 * @package IDX Integration
 * @subpackage Widgets
 * @see RC_Idx_Api
 */
class RC_Idx_Widget extends WP_Widget {
	public $_idx;
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$this->_idx = new RC_Idx_Api;
		parent::__construct(
	 		'rc_idx_dashboard_widget', // Base ID
			__('RealtyCandy :: IDX Dashboard Widget', 'realtycandy' ), // Name
			array(
				'description' => __( 'Embed an IDX widget from the IDX dashboard', 'realtycandy'),
				'classname'   => 'rc-idx-dashboard-widget'
			)
		);
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
		echo $before_widget;
		if ( ! empty( $instance['title'] ) ) {
			echo $before_title . $instance['title'] . $after_title;
		}
		if ( ! empty($instance['widget'] ) ) {
			echo '<script type="text/javascript" src="', $instance['widget'], '"></script>';
		}
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
		$instance['title']  = strip_tags($new_instance['title']);
		$instance['widget'] = esc_url_raw($new_instance['widget']);
		return $instance;
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @uses RC_City_Links_Widget::city_list_options()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$_idx = $this->_idx;
		$defaults = array(
			'title'   => '',
			'widget'  => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'realtycandy' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $instance['title'] ); ?>" />
		</p>
		<p>
			<?php _e( 'IDX widgets are widgets you have created in your IDX dashboard.
			Allow one hour for a widget to appear in this list after it has been created in the IDX dashboard.', 'realtycandy' ); ?>
		</p>
		<p>
			<select class="widefat" id="<?php echo $this->get_field_id( 'widget' ); ?>" name="<?php echo $this->get_field_name( 'widget' ) ?>">
				<option <?php selected($instance['widget'], ''); ?> value=""><?php _e( 'Select a widget', 'realtycandy' ); ?></option>
				<?php $this->widget_options($instance); ?>
			</select>
		</p>
		<?php
	}
	/**
	 * Echos widget options
	 *
	 * The option values are the IDX widget source urls.
	 * They will be displayed by name.
	 *
	 * This is just a helper to keep the html clean
	 *
	 * @param var $instance
	 */
	public function widget_options($instance) {
		$widgets = $this->_idx->idx_widgets();
		if ( !is_array($widgets) ) {
			return;
		}
		foreach($widgets as $widget) {
			echo '<option ', selected($instance['widget'], $widget->url, 0), ' value="', $widget->url, '">', $widget->name, '</option>';
		}
	}
}