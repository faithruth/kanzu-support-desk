<?php
/**
 * The support form widget
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly

if ( ! class_exists( 'KSD_Support_Form_Widget' ) ) : 
    
class KSD_Support_Form_Widget extends WP_Widget {
	
        /**
	 * Set up the widgets details
	 */
	public function __construct() {
		parent::__construct(
			'ksd_support_widget',  
			__( 'Kanzu Support Desk Support Form', 'kanzu-support-desk' ),  
			array( 'description' => __( 'Kanzu Support Desk Support Form Widget', 'kanzu-support-desk' ), )  
		);
	}
        

	/**
	 * Front-end display of the widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$widget_content = $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
                    $widget_content.= $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
                $widget_content.= KSD_Public::generate_support_form();
		echo $widget_content.$args['after_widget'];
	}

	/**
	 * Admin widget form
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Contact Support', 'kanzu-support-desk' );
		?>
		<p>
                    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'kanzu-support-desk' ); ?></label> 
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}
        
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}        

      
}

return new KSD_Support_Form_Widget();

endif;
