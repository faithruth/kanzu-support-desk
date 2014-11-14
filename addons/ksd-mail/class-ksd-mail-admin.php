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
            <h3><?php _e("Mail","ksd-mail"); ?></h3>
                    <div>
                       <div class="setting">
                           <label for="ksd_mail_server">Mail Server</label>
                           <input type="text" value="<?php echo $settings['ksd_mail_server']; ?>" size="30" name="ksd_mail_server" />
                       </div>
                       <div class="setting">
                           <label for="ksd_mail_account">Support Email Address</label>
                           <input type="text" value="<?php echo $settings['ksd_mail_account']; ?>" size="30" name="ksd_mail_account" />
                       </div>
                       <div class="setting">
                           <label for="ksd_mail_password">Password</label>
                           <input type="password"  size="30" name="ksd_mail_password" />
                       </div>
                       <div class="setting">
                           <label for="ksd_mail_protocol">Protocol</label>
                           <select name="ksd_mail_protocol">
                               <option value="pop3" <?php selected( "pop3", $settings['ksd_mail_protocol'] ) ?>>POP3</option>
                               <option value="imap"  <?php selected( "imap", $settings['ksd_mail_protocol'] ) ?> >IMAP</option>
                           </select>
                       </div> 
                           <div class="setting">
                           <label for="ksd_mail_port">Port</label>
                           <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Default Ports are: 143 (IMAP), 993 (IMAP/SSL), 110 (POP3) and 995 (POP3/SSL)','kanzu-support-desk')  ;?>"/>
                           <input type="text" value="<?php echo $settings['ksd_mail_port']; ?>" size="30" name="ksd_mail_port" />
                       </div> 
                       <div class="setting">
                           <label for="ksd_mail_mailbox">Mailbox</label>
                           <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('The mailbox to query. You almost never need to change this','kanzu-support-desk')  ;?>"/>
                           <input type="text" value="<?php echo $settings['ksd_mail_mailbox']; ?>" size="30" name="ksd_mail_mailbox" />
                       </div>
                       <div class="setting">
                           <label for="ksd_mail_validate_certificate">Validate Certificate</label>
                           <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Validate your SSL certificate during connection. Use only if you have a valid SSL certificate otherwise it will fail','kanzu-support-desk')  ;?>"/>
                           <input name="ksd_mail_validate_certificate"  type="checkbox" <?php checked( $settings['ksd_mail_validate_certificate'], "yes" ) ?> value="yes"  />
                       </div> 
                       <div class="setting">
                           <label for="ksd_mail_useSSL">Always use secure connection(SSL)?</label>
                           <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e('Enable use of secure connections (SSL). Note that you will need to use the correct corresponding port. Defaults are 993 (IMAP/SSL) and 995 (POP3/SSL)','kanzu-support-desk')  ;?>"/>
                           <input name="ksd_mail_useSSL"  type="checkbox" <?php checked( $settings['ksd_mail_useSSL'], "yes" ) ?> value="yes"  />
                       </div> 
                    </div>
            <?php
        }
 
}
endif;

return new KSD_Mail_Admin();

