<?php
/**
 * Notifications displayed throughout KSD's lifetime
 * on the site
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Feedback' ) ) :

class KSD_Feedback {
    
	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;    
    
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
        
        public function get_new_notification(){
            
        }
        
        private function default_notifications(){
            $defaults = array(
                array(
                    'threshold' => 1,
                    'displayed' => false,
                    'title'     => 'Checking in',
                    'message'   => 'Wasap bro',
                    'user_response' => "It's all good bro"                    
                ),
                array(
                    'threshold' => 2,
                    'displayed' => false,
                    'title'     => 'Welcome!',
                    'message'   => 'Me again bro',
                    'user_response' => "It's all good bro"                    
                )                
            );
            
            return $defaults;
        }
}
endif;
 
