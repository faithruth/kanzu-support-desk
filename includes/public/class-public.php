<?php
/**
 * Front-end of Kanzu Support Desk
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
namespace Kanzu\Ksd\KSD_Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly
if ( ! class_exists( 'KSD_Public' ) ) :

	class KSD_Public {

		public function __construct() {

			// Do public-facing includes
			$this->do_public_includes();
			// Add CC button to tinyMCE editor
			$this->add_tinymce_cc_button();

		}

		/**
		 * Decrement notifications counter when KSD Admin Bar is clicked
		 */
		public function admin_bar_clicked() {
			$node_id = ltrim( sanitize_text_field( $_POST['node_id'] ), 'wp-admin-bar' );
			// KSD Admin Bar clicked array map stored and fetched directly from DB to speed up queries
			$admin_bar_clicked = get_option( 'admin_bar_clicked', array() );
			$notifications     = get_option( 'admin_bar_notifications', 0 );
			$response          = array();
			if ( ! isset( $admin_bar_clicked[ $node_id ] ) || ! $admin_bar_clicked[ $node_id ] ) {
				$admin_bar_clicked[ $node_id ] = true;
				$notifications--;
				update_option( 'admin_bar_clicked', $admin_bar_clicked );
				update_option( 'admin_bar_notifications', $notifications );
				$response['message'] = 'Success';
			}
			echo json_encode( $response );
			die();
		}

		/**
		 * Display KSD Admin Bar
		 *
		 * @param WP_Admin_Bar $admin_bar
		 */
		public function display_admin_bar_menu( $admin_bar ) {

			$this->add_ksd_admin_bar_node(
				array(
					'id'    => 'ksd-qs-tour',
					'title' => __( 'Quick Tour', 'kanzu-support-desk' ),
					'href'  => admin_url( 'edit.php?post_type=ksd_ticket&ksd_getting_started=1' ),
				)
			);

			$this->add_ksd_admin_bar_node(
				array(
					'id'    => 'ksd-docs',
					'title' => __( 'Getting Started Guide', 'kanzu-support-desk' ),
					'href'  => 'https://kanzucode.com/knowledge_base/simple-wordpress-helpdesk-plugin-quick-start/',
				)
			);

			$this->add_ksd_admin_bar_parent( $admin_bar );

			// Merge with default node so that each node maintains the same parent
			$default_node = array(
				'parent' => 'ksd-admin-bar',
				'meta'   => array(
					'target' => '_blank', // Target is set to blank on all child nodes so that ajax call that decrements notifications counter has time to complete
				),
			);
			// KSD Admin Bar nodes stored and fetched directly from DB to speed up queries
			$admin_bar_nodes = get_option( KSD()->ksd_admin_bar_nodes, array() );

			foreach ( $admin_bar_nodes as $admin_bar_node ) {
				$admin_bar_node = wp_parse_args( $default_node, $admin_bar_node );
				$admin_bar->add_node( $admin_bar_node );
			}

		}

		/**
		 * Add children nodes to KSD Admin Bar Menu
		 *
		 * @param array $args
		 */
		public function add_ksd_admin_bar_node( $args ) {
			// KSD Admin Bar notifications stored and fetched directly from DB to speed up queries
			$notifications   = get_option( 'admin_bar_notifications', 0 );
			$admin_bar_nodes = get_option( KSD()->ksd_admin_bar_nodes, array() );
			$node_id[]       = $args['id'];
			$node_ids        = array();
			foreach ( $admin_bar_nodes as $admin_bar_node ) {
				$node_ids[] = $admin_bar_node['id'];
			}
			$node_mods = array_diff( $node_id, $node_ids );
			if ( ! empty( $node_mods ) ) {
				$admin_bar_nodes[] = $args;
				$notifications++;
				update_option( KSD()->ksd_admin_bar_nodes, $admin_bar_nodes );
				update_option( 'admin_bar_notifications', $notifications );
			}
		}

		/**
		 * Add KSD Admin Bar Menu parent node
		 *
		 * @param WP_Admin_Bar $admin_bar
		 */
		public function add_ksd_admin_bar_parent( $admin_bar ) {
			$notifications     = get_option( 'admin_bar_notifications', 0 );
			$parent_node_title = '<span class="ksd-admin-icon" style="display:inline-block; margin: 2px 0;" ><img src="' . KSD_PLUGIN_URL . 'assets/images/icons/kc_white_icon_25x25.png" /></span>';
			if ( $notifications ) {
				$parent_node_title .= '<span class="ksd-admin-bar-notice" style="display: inline-block;background: #d54e21;border-radius: 10px;padding: 0 6px;font-size: 9px;height: 20px;vertical-align: top;line-height: 20px;margin: 4px 4px;" >' . $notifications . '</span>';
			}
			$args = array(
				'id'     => 'ksd-admin-bar',
				'title'  => $parent_node_title,
				'parent' => 'root-default',
			);
			$admin_bar->add_node( $args );
		}

		public function add_async_defer_attributes( $tag, $handle ) {
			if ( KSD_SLUG . '-public-grecaptcha' != $handle ) {
				return $tag;
			}
			return str_replace( ' src', ' async defer src', $tag );
		}

		/**
		 * Include files required by the public-facing logic
		 */
		private function do_public_includes() {
			require_once KSD_PLUGIN_DIR . 'includes/public/class-widget-support-form.php';
			include_once KSD_PLUGIN_DIR . 'includes/admin/class-hash-urls.php';
			include_once KSD_PLUGIN_DIR . 'includes/class-custom-post-types.php';
			include_once KSD_PLUGIN_DIR . 'includes/class-onboarding.php';
			include_once KSD_PLUGIN_DIR . 'includes/class-session.php';
		}

		/**
		 * Register the support form widget
		 */
		public function register_support_form_widget() {
			register_widget( 'KSD_Support_Form_Widget' );
		}

		/**
		 * Display a form wherever shortcode [ksd_support_form] is used
		 */
		public function form_short_code() {
			return self::generate_support_form();
		}

		/**
		 * Register and enqueue front-specific style sheet.
		 *
		 * @since     1.0.0
		 */
		public function enqueue_public_styles() {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( KSD_SLUG . '-public-css', KSD_PLUGIN_URL . 'assets/css/ksd-public.css', array(), KSD_VERSION );
		}

		/**
		 * Enqueue scripts used solely at the front-end
		 *
		 * @since 1.0.0
		 */
		public function enqueue_public_scripts() {
			if ( ! is_admin() ) {
				wp_enqueue_media();
			}
			if ( is_user_logged_in() ) {
				wp_enqueue_script( KSD_SLUG . '-admin-bar-js', KSD_PLUGIN_URL . 'assets/js/ksd-admin-bar.js', array( 'jquery' ), '1.0.0' );
				wp_localize_script(
					KSD_SLUG . '-admin-bar-js',
					'ksd_admin_bar',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
					)
				);
			}

			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( KSD_SLUG . '-public-js', KSD_PLUGIN_URL . 'assets/js/ksd-public.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tooltip', 'media-upload', 'thickbox' ), KSD_VERSION );
			$ksd_public_labels                         = array();
			$ksd_public_labels['msg_grecaptcha_error'] = sprintf( __( 'Please check the <em>%s</em> checkbox and wait for it to complete loading', 'kanzu-support-desk' ), "I'm not a robot" );
			$ksd_public_labels['msg_error_refresh']    = __( 'Sorry, but it seems like something went wrong. Please try again or reload the page.', 'kanzu-support-desk' );
			$ksd_public_labels['msg_reply_sent']       = __( 'Your reply has been sent successfully. We will get back to you shortly. Thank you.', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_name']             = __( 'Name', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_subject']          = __( 'Subject', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_email']            = __( 'Email', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_first_name']       = __( 'First Name', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_last_name']        = __( 'Last Name', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_username']         = __( 'Username', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_CC']               = __( 'CC', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_reply_to_all']     = __( 'Reply', 'kanzu-support-desk' );
			$ksd_public_labels['lbl_populate_cc']      = __( 'Populate CC field', 'kanzu-support-desk' );

			// @TODO Don't retrieve settings again. Use same set of settings
			$settings = Kanzu_Support_Desk::get_settings();

			wp_localize_script(
				KSD_SLUG . '-public-js',
				'ksd_public',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'admin_post_url'         => admin_url( 'admin-post.php' ),
					'ksd_public_labels'      => $ksd_public_labels,
					'ksd_submit_tickets_url' => get_permalink( $settings['page_submit_ticket'] ),
				)
			);
			// Check whether enable_recaptcha is checked.
			if ( 'yes' == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ) {
				wp_enqueue_script( KSD_SLUG . '-public-grecaptcha', '//www.google.com/recaptcha/api.js?onload=ksdRecaptchaCallback&render=explicit', array(), KSD_VERSION );
				wp_localize_script(
					KSD_SLUG . '-public-grecaptcha',
					'ksd_grecaptcha',
					array(
						'site_key' => $settings['recaptcha_site_key'],
					)
				);
			}

		}

		/**
		 * Redirect user after successful login.
		 *
		 * @param string $redirect_to URL to redirect to.
		 * @param string $request URL the user is coming from.
		 * @param object $user Logged user's data.
		 * @return string
		 * @since 2.0.0
		 * @since 2.2.1 Filter commented out
		 */
		public function do_login_redirect( $redirect_to, $request, $user ) {
			global $user; // is there a user to check?
			if ( isset( $user->roles ) && is_array( $user->roles ) ) {
				// check for admins
				if ( in_array( 'ksd_customer', $user->roles ) ) {
					// @TODO Check $request and send customer to 'My Tickets' or to 'Create new ticket'
					$current_settings = Kanzu_Support_Desk::get_settings(); // Get current settings
					return get_permalink( $current_settings['page_my_tickets'] ); // redirect customers to 'my tickets'
				}
			}
			return $redirect_to;
		}

	}
endif;

return new Public();
