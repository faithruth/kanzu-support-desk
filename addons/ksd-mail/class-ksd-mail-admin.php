<?php
/**
 * Admin side of KSD Mail
 *
 * @package   KSD_Mail
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Mail_Admin' ) ) :

class KSD_Mail_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;   


        /**
	 * Initialize the addon
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		//Add settings to the KSD Settings view
		add_action( 'ksd_settings', array( $this, 'show_settings' ) );

	}
	

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
         
        /**
         * HTML to added to settings KSD settings form.
         */
        public function show_settings(){
            ?>
            <div class="setting">
                <label for="ksd_mail_check_freq">Mail box check frequecy</label>
                <input type="text" value="<?php echo $settings['ksd_mail_check_freq']; ?>" size="15" name="ksd_mail_check_freq" />
            </div>
            <div class="setting">
                <label for="ksd_mail_mailbox">Mail box check frequecy</label>
                <input type="text" value="<?php echo $settings['ksd_mail_mailbox']; ?>" size="15" name="ksd_mail_mailbox" />
            </div>
            <?php
        }
 
}
endif;

return new KSD_Mail_Admin();

