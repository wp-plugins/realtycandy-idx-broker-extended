<?php
/**
 * Creates a widget that outputs the idx lead login form
 *
 * @package IDX Integration
 * @subpackage Widgets
 * @see RC_Idx_Api
 */
class RC_Lead_Login_Widget extends WP_Widget {
	public $_idx;
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$this->_idx = new RC_Idx_Api;
		parent::__construct(
	 		'rc_lead_login',   // Base ID
			__( 'RealtyCandy :: IDX Lead Login', 'realtycandy' ), // Name
			array(
				'description' => __( 'Lead login form', 'realtycandy' ),
				'classname'   => 'rc-idx-login-widget'
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
		$title = $instance['title'];
		$custom_text = $instance['custom_text'];
		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		if ( !empty($custom_text) ) {
			echo '<p>', $custom_text, '</p>';
		} 
		?>
		<form action="<?php echo $this->_idx->subdomain_url(); ?>ajax/userlogin.php" method="post" target="" name="leadLoginForm">
			<input type="hidden" name="action" value="login">
			<input type="hidden" name="loginWidget" value="true">
			<label for="bb-IDX-widgetEmail"><?php _e( 'Email Address:' , 'realtycandy' ); ?></label>
			<input id="bb-IDX-widgetEmail" type="text" name="email" placeholder="Enter your email address">
			<input id="bb-IDX-widgetPassword" type="hidden" name="password" value="">
			<input id="bb-IDX-widgetLeadLoginSubmit" type="submit" name="login" value="Log In">
		</form>
		<?php
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
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['custom_text'] = htmlentities( $new_instance['custom_text'] );
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
			'title'          => 'Account Login',
			'custom_text'    => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'realtycandy' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'custom_text') ?>"><?php _e( 'Custom Text', 'realtycandy' ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'custom_text' ); ?>" name="<?php echo $this->get_field_name( 'custom_text' ); ?>" value="<?php esc_attr_e( $instance['custom_text'] ); ?>" rows="5"><?php esc_attr_e( $instance['custom_text'] ); ?></textarea>
		</p>
		<?php
	}
}