<?php
/**
 * Admin side hooks
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
namespace Kanzu\Ksd;

class Hooks_registry {

	/**
	 * The admin Object
	 *
	 * @var Object
	 */
	private $ksd_admin;

	/**
	 * class constructor
	 */
	public function __construct() {
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-admin.php';
		$this->ksd_admin = Admin::get_instance();


		$this->add_action_hooks();
		$this->add_filter_hooks();
		$this->add_ajax_hooks();

	}

	/**
	 * Add actionsInstaller
	 *
	 * @return void
	 */
	public function add_action_hooks() {
			// Load plugin text domain
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			add_action( 'init', array( $this, 'init' ), 0 );

			// Load scripts used in both the front and back ends
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_general_scripts' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_general_scripts' ) );

			// Share the plugin's settings with add-ons
			add_filter( 'ksd_get_settings', array( $this, 'get_settings' ) );

			// Handle logging of new tickets initiated by add-ons.
			add_action( 'ksd_log_new_ticket', array( $this, 'do_log_new_ticket' ) );

			// Handle logging of new ticket activities
			add_action( 'ksd_insert_new_ticket_activity', array( $this, 'insert_new_ticket_activity' ) );

			// Handle logging of replies initiated by add-ons.
			add_action( 'ksd_reply_ticket', array( $this, 'do_reply_ticket' ) );

			// A new add-on has been activated
			add_action( 'ksd_addon_activated', array( $this, 'addon_activated' ) );

			do_action( 'ksd_loaded' );
	}

			/**
			 * Load the plugin text domain for translation.
			 * .mo files should be placed in /languages/ and should be named {KSD_SLUG}-{locale}.mo
			 *  e.g. For Danish, whose locale is Danish is 'da_DK',
			 * the MO and PO files should be named kanzu-support-desk-da_DK.mo and kanzu-support-desk-da_DK.po
			 *
			 * @since    1.0.0
			 */
	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), KSD_SLUG );

		load_textdomain( 'kanzu-support-desk', trailingslashit( WP_LANG_DIR ) . KSD_SLUG . '/' . KSD_SLUG . '-' . $locale . '.mo' );
		load_plugin_textdomain( 'kanzu-support-desk', false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

		/**
		 * Enqueue scripts used in both the front and back end
		 *
		 * @since    1.0.0
		 */
	public function enqueue_general_scripts() {
		// For form validation
		wp_enqueue_script( KSD_SLUG . '-validate', KSD_PLUGIN_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), '1.13.0' );
		$validator_messages = $this->get_validator_localized_messages();
		wp_enqueue_script( KSD_SLUG . '-validate-messages', KSD_PLUGIN_URL . 'assets/js/jquery.validate.messages.js', array( KSD_SLUG . '-validate' ) );
		wp_localize_script( KSD_SLUG . '-validate-messages', 'ksd_validate_messages', $validator_messages );
	}

		/**
		 * Return localized messages used in the jQuery validation plugin
		 *
		 * @return array Localized validation messages
		 */
	private function get_validator_localized_messages() {
		return array(
			'required'    => __( 'This field is required.', 'kanzu-support-desk' ),
			'remote'      => __( 'Please fix this field.', 'kanzu-support-desk' ),
			'email'       => __( 'Please enter a valid email address.', 'kanzu-support-desk' ),
			'url'         => __( 'Please enter a valid URL.', 'kanzu-support-desk' ),
			'date'        => __( 'Please enter a valid date.', 'kanzu-support-desk' ),
			'dateISO'     => __( 'Please enter a valid date (ISO).', 'kanzu-support-desk' ),
			'number'      => __( 'Please enter a valid number.', 'kanzu-support-desk' ),
			'digits'      => __( 'Please enter only digits.', 'kanzu-support-desk' ),
			'equalTo'     => __( 'Please enter the same value again.', 'kanzu-support-desk' ),
			'creditcard'  => __( 'Please enter a valid credit card number.', 'kanzu-support-desk' ),
			'maxlength'   => sprintf( __( 'Please enter no more than %s characters.', 'kanzu-support-desk' ), '{0}' ),
			'minlength'   => sprintf( __( 'Please enter at least %s characters.', 'kanzu-support-desk' ), '{0}' ),
			'rangelength' => sprintf( __( 'Please enter a value between %1$s and %2$s characters long.', 'kanzu-support-desk' ), '{0}', '{1}' ),
			'range'       => sprintf( __( 'Please enter a value between %1$s and %2$s.', 'kanzu-support-desk' ), '{0}', '{1}' ),
			'max'         => sprintf( __( 'Please enter a value less than or equal to %s.', 'kanzu-support-desk' ), '{0}' ),
			'min'         => sprintf( __( 'Please enter a value greater than or equal to %s.', 'kanzu-support-desk' ), '{0}' ),
			'step'        => sprintf( __( 'Please enter a multiple of %s.', 'kanzu-support-desk' ), '{0}' ),
		);
	}

		/**
		 * Get all settings. Settings are stored as an array
		 * with key KSD_OPTIONS_KEY
		 */
	public static function get_settings() {
		return get_option( KSD_OPTIONS_KEY );
	}

		/**
		 * Update settings.
		 *
		 * @TODO Change this to use a filter
		 */
	public static function update_settings( $updated_settings ) {
		return update_option( KSD_OPTIONS_KEY, $updated_settings );
	}

		/**
		 * Init KSD
		 */
	public function init() {
		$this->session = new Session();
	}

		/**
		 * Log new tickets & replies initiated by add-ons
		 * We hand this over to the admin-end logic which has
		 * all the functions needed to do this smoothly
		 *
		 * @param Object $new_ticket The new ticket or reply object
		 */
	public function do_log_new_ticket( $new_ticket ) {
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-admin.php';
		$ksd_admin = Admin::get_instance();
		$ksd_admin->do_log_new_ticket( $new_ticket );
	}

		/**
		 * Log ticket replies initiated by add-ons
		 *
		 * @since 2.2.12
		 *
		 * @param Object $new_ticket The reply object
		 */
	public function do_reply_ticket( $ticket_reply ) {
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-admin.php';
		$ksd_admin = KSD_Admin::get_instance();
		$ksd_admin->do_reply_ticket( $ticket_reply );
	}

		/**
		 * Generate the signature added to ticket notifications
		 *
		 * @param int     $tkt_id
		 * @param boolean $append_logo Whether to append a logo or not
		 * @since 1.7.0
		 */
	public static function output_ksd_signature( $tkt_id, $append_logo = false ) {
		$suffix        = '';
		$no_logo_style = ( $append_logo ? '' : 'text-align:right;width:100%;' );
		$settings      = self::get_settings();

		if ( 'no' == $settings['enable_customer_signup'] ) {
			$url = get_post_meta( $tkt_id, '_ksd_tkt_info_hash_url', true );
			if ( empty( $url ) ) {
				include_once KSD_PLUGIN_DIR . 'includes/admin/class-hash-urls.php';
				$hash_urls = new Hash_Urls();
				$url       = $hash_urls->create_hash_url( $tkt_id );
			}
		} else {
			$url = get_permalink( $tkt_id );
		}

		$permalink = '<a href="' . $url . '">' . __( 'View this ticket', 'kanzu-support-desk' ) . '</a>';

		$suffix .= '<table style="width:100%;border-collapse:collapse;border-top:1px solid #CCC;">
	                    <tbody>
	                        <tr>
	                            <td style="padding:0;' . $no_logo_style . '">' . $permalink . '</td>';
		if ( $append_logo ) :
			$suffix .= ' <td style="text-align:right;width:100px;padding:0;">
		                                <a href="https://kanzucode.com/kanzu-support-desk" style="color:#3572b0;text-decoration:none" target="_blank">
		                                    <img width="200" height="80" src="http://kanzucode.com/logos/kanzu_support_desk.png" alt="Kanzu Support Desk">
		                                </a>
		                            </td>';
			endif;
		$suffix .= '</tr>
	                    </tbody>
	                   </table>';
		return $suffix;
	}

		/**
		 * Provides the support form to be used anywhere.
		 * By default, it echoes the HTML immediately. Pass array('echo' => false) to return the string instead.
		 *
		 * @since 2.2.12
		 */
	public function support_form( $args = array() ) {
		$this->includes();
		$defaults = array(
			'echo' => true,
		);

		$args = wp_parse_args( $args, apply_filters( 'ksd_support_form_defaults', $defaults ) );
		if ( $args['echo'] ) {
			echo KSD_Public::generate_support_form();
		} else {
			return KSD_Public::generate_support_form();
		}
	}

		/**
		 * Insert a new activity related to a ticket
		 *
		 * @param Array $new_ticket_activity The new activity to insert. Note that one of the keys must be post_parent and
		 *                                   it should be a valid ID of a ksd_ticket
		 * @return int|WP_Error The activity ID on success. The value 0 or WP_Error on failure.
		 * @since 2.2.12
		 */
	public function insert_new_ticket_activity( $new_ticket_activity ) {
		// Check whether the ticket this is being attached to is valid
		if ( 'ksd_ticket' !== get_post_type( $new_ticket_activity['post_parent'] ) ) {
			return 0;
		}
		$new_ticket_activity['post_type']      = 'ksd_ticket_activity';
		$new_ticket_activity['post_status']    = 'private';
		$new_ticket_activity['comment_status'] = 'closed ';
		return wp_insert_post( $new_ticket_activity );
	}

		/**
		 * A new addon's been activated
		 *
		 * @since 2.3.6
		 */
	public function addon_activated() {
		// WIP
	}

		/**
		 * Added to write custom debug messages to the debug log ( wp-content/debug.log). You
		 * need to turn debug on for this to work
		 */
	public function log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}
}

}
new Hook_Registry();
