<?php
/**
 * Admin side of Kanzu Support Desk
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

namespace Kanzu\Ksd\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'KSD_Admin' ) ) :
	/**
	 * KSD Admin class
	 */
	class KSD_Admin {

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin by loading admin scripts & styles and adding a
		 * settings page and menu. Also define the AJAX callbacks
		 *
		 * @since     1.0.0
		 */
		public function __construct() {

			// Add CC button to tinyMCE editor
			$this->add_tinymce_cc_button();

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
			if ( null === self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Register and enqueue admin-specific style sheet.
		 *
		 * @since     1.0.0
		 */
		public function enqueue_admin_styles() {
			wp_enqueue_style( KSD_SLUG . '-admin-css', KSD_PLUGIN_URL . 'assets/css/ksd-admin.css', array(), KSD_VERSION );
		}

		/**
		 * Register and enqueue admin-specific JavaScript.
		 *
		 * @since     1.0.0
		 */
		public function enqueue_admin_scripts() {
			wp_enqueue_script( KSD_SLUG . '-admin-bar-js', KSD_PLUGIN_URL . 'assets/js/ksd-admin-bar.js', array( 'jquery' ), '1.0.0' );
			wp_localize_script(
				KSD_SLUG . '-admin-bar-js',
				'ksd_admin_bar',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);

			// Load the script for Google charts. Load this before the next script.
			wp_enqueue_script( KSD_SLUG . '-admin-gcharts', '//www.google.com/jsapi', array(), KSD_VERSION );
			wp_enqueue_script( KSD_SLUG . '-admin-js', KSD_PLUGIN_URL . 'assets/js/ksd-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'json2', 'jquery-ui-dialog', 'jquery-ui-tooltip', 'jquery-ui-accordion', 'jquery-ui-autocomplete' ), KSD_VERSION );

			// Variables to send to the admin JS script
			$ksd_admin_tab = ( isset( $_GET['page'] ) ? $_GET['page'] : '' ); // This determines which tab to show as active

			// This array allows us to internationalize (translate) the words/phrases/labels displayed in the JS
			$admin_labels_array = $this->get_admin_labels();

			$ticket_info = array( 'status_list' => $this->get_submitdiv_status_options() );

			// Get current settings
			$settings = Kanzu_Support_Desk::get_settings();

			// Localization allows us to send variables to the JS script
			wp_localize_script(
				KSD_SLUG . '-admin-js',
				'ksd_admin',
				array(
					'admin_tab'                 => $ksd_admin_tab,
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'admin_post_url'            => admin_url( 'admin-post.php' ),
					'ksd_admin_nonce'           => wp_create_nonce( 'ksd-admin-nonce' ),
					'ksd_tickets_url'           => admin_url( 'admin.php?page=ksd-tickets' ),
					'ksd_current_user_id'       => get_current_user_id(),
					'ksd_labels'                => $admin_labels_array,
					'enable_anonymous_tracking' => $settings['enable_anonymous_tracking'],
					'ksd_ticket_info'           => $ticket_info,
					'ksd_current_screen'        => $this->get_current_ksd_screen(),
					'ksd_version'               => KSD_VERSION,
					'ksd_statuses'              => $this->get_status_list_options(),
				)
			);

		}

		/***
		 * Create options for the status select item in the
		 * submitdiv on the edit/reply ticket view
		 *
		 * @since 2.0.0
		 */
		private function get_submitdiv_status_options() {
			$status_options = '';
			foreach ( $this->get_status_list() as $status => $status_label ) {
				$status_options .= "<option value='{$status}'>{$status_label}</option>";
			}
			// Add a 'draft' status.
			$status_options .= "<option value='draft'>" . _x( 'Draft', 'status of a ticket', 'kanzu-support-desk' ) . '</option>';
			return $status_options;
		}

		/**
		 * Do all KSD actions present in the $_POST & $_GET superglobals.
		 * These functions are called on init
		 *
		 * @since 1.7.0
		 */
		public function do_post_and_get_actions() {
			if ( isset( $_POST['ksd_action'] ) ) {
				do_action( $_POST['ksd_action'], $_POST );
			}
			if ( isset( $_GET['ksd_action'] ) ) {
				do_action( $_GET['ksd_action'], $_GET );
			}
		}

		/**
		 * Get the labels internationalized for use in
		 * our JS, ksd-admin.js
		 */
		private function get_admin_labels() {
			$admin_labels_array                                 = array();
			$admin_labels_array['dashboard_chart_title']        = __( 'Incoming Tickets', 'kanzu-support-desk' );
			$admin_labels_array['dashboard_open_tickets']       = __( 'Total Open Tickets', 'kanzu-support-desk' );
			$admin_labels_array['dashboard_unassigned_tickets'] = __( 'Unassigned Tickets', 'kanzu-support-desk' );
			$admin_labels_array['dashboard_avg_response_time']  = __( 'Avg. Response Time', 'kanzu-support-desk' );
			// $admin_labels_array['tkt_trash']                    = __('Trash', 'kanzu-support-desk' );
			// $admin_labels_array['tkt_assign_to']                = __('Assign To', 'kanzu-support-desk' );
			// $admin_labels_array['tkt_change_status']            = __('Change Status', 'kanzu-support-desk' );
			// $admin_labels_array['tkt_change_severity']          = __('Change Severity', 'kanzu-support-desk' );
			// $admin_labels_array['tkt_mark_read']                = __('Mark as Read', 'kanzu-support-desk' );
			// $admin_labels_array['tkt_mark_unread']              = __('Mark as Unread', 'kanzu-support-desk' );
			$admin_labels_array['tkt_subject']                = __( 'Subject', 'kanzu-support-desk' );
			$admin_labels_array['tkt_cust_fullname']          = __( 'Customer Name', 'kanzu-support-desk' );
			$admin_labels_array['tkt_cust_email']             = __( 'Customer Email', 'kanzu-support-desk' );
			$admin_labels_array['tkt_reply']                  = __( 'Send', 'kanzu-support-desk' );
			$admin_labels_array['tkt_forward']                = __( 'Forward', 'kanzu-support-desk' );
			$admin_labels_array['tkt_update_note']            = __( 'Add Note', 'kanzu-support-desk' );
			$admin_labels_array['tkt_attach_file']            = __( 'Attach File', 'kanzu-support-desk' );
			$admin_labels_array['tkt_attach']                 = __( 'Attach', 'kanzu-support-desk' );
			$admin_labels_array['tkt_status_open']            = __( 'OPEN', 'kanzu-support-desk' );
			$admin_labels_array['tkt_status_pending']         = __( 'PENDING', 'kanzu-support-desk' );
			$admin_labels_array['tkt_status_resolved']        = __( 'RESOLVED', 'kanzu-support-desk' );
			$admin_labels_array['tkt_severity_low']           = __( 'LOW', 'kanzu-support-desk' );
			$admin_labels_array['tkt_severity_medium']        = __( 'MEDIUM', 'kanzu-support-desk' );
			$admin_labels_array['tkt_severity_high']          = __( 'HIGH', 'kanzu-support-desk' );
			$admin_labels_array['tkt_severity_urgent']        = __( 'URGENT', 'kanzu-support-desk' );
			$admin_labels_array['msg_still_loading']          = __( 'Loading Replies...', 'kanzu-support-desk' );
			$admin_labels_array['msg_loading']                = __( 'Loading...', 'kanzu-support-desk' );
			$admin_labels_array['msg_sending']                = __( 'Sending...', 'kanzu-support-desk' );
			$admin_labels_array['msg_reply_sent']             = __( 'Reply Sent!', 'kanzu-support-desk' );
			$admin_labels_array['msg_error']                  = __( 'Sorry, an unexpected error occurred. Kindly retry. Thank you.', 'kanzu-support-desk' );
			$admin_labels_array['msg_error_refresh']          = __( 'Sorry, but something went wrong. Please try again or reload the page.', 'kanzu-support-desk' );
			$admin_labels_array['pointer_next']               = __( 'Next', 'kanzu-support-desk' );
			$admin_labels_array['lbl_toggle_trimmed_content'] = __( 'Toggle Trimmed Content', 'kanzu-support-desk' );
			$admin_labels_array['lbl_tickets']                = __( 'Tickets', 'kanzu-support-desk' );
			$admin_labels_array['lbl_CC']                     = __( 'CC', 'kanzu-support-desk' );
			$admin_labels_array['lbl_reply_to_all']           = __( 'Reply', 'kanzu-support-desk' );
			$admin_labels_array['lbl_populate_cc']            = __( 'Populate CC field', 'kanzu-support-desk' );
			$admin_labels_array['lbl_save']                   = __( 'Save', 'kanzu-support-desk' );
			$admin_labels_array['lbl_update']                 = __( 'Submit', 'kanzu-support-desk' );
			$admin_labels_array['lbl_created_on']             = __( 'Created on', 'kanzu-support-desk' );
			$admin_labels_array['lbl_notification_nps_error'] = __( 'Please select one of the values above (0 to 10)', 'kanzu-support-desk' );

			// jQuery form validator internationalization
			$admin_labels_array['validator_required']  = __( 'This field is required.', 'kanzu-support-desk' );
			$admin_labels_array['validator_email']     = __( 'Please enter a valid email address.', 'kanzu-support-desk' );
			$admin_labels_array['validator_minlength'] = __( 'Please enter at least {0} characters.', 'kanzu-support-desk' );
			$admin_labels_array['validator_cc']        = __( 'Please enter a comma separated list of valid email addresses.', 'kanzu-support-desk' );

			// Messages for migration to v2.0.0
			// $admin_labels_array['msg_migrationv2_started']      = __('Migration of your tickets and replies has started. This may take some time. Please wait...', 'kanzu-support-desk' );
			// $admin_labels_array['msg_migrationv2_deleting']     = __('Deleting tickets. This may take sometime. Please wait...', 'kanzu-support-desk' );
			return $admin_labels_array;
		}


		/**
		 * Add contextual help messages
		 *
		 * @param string $contextual_help
		 * @param int    $screen_id
		 * @param Object $screen
		 * @global $wp_version
		 * @return string $contextual_help The contextual help
		 * @since 2.0.0
		 */
		public function add_contextual_help( $contextual_help, $screen_id, $screen ) {
			global $wp_version;

			$current_ksd_screen = $this->get_current_ksd_screen( $screen );
			if ( 'not_a_ksd_screen' == $current_ksd_screen ) {
				return $contextual_help;
			}

			switch ( $current_ksd_screen ) {
				case 'ksd-ticket-list':
					$contextual_help = sprintf(
						'<span><h2> %s </h2> <p> %s </p> <p> <b> %s </b> %s </p><p> <b> %s </b> %s </p></span>',
						__( 'Tickets', 'kanzu-support-desk' ),
						__( 'All your tickets are displayed here. View the details of a single ticket by clicking on it.', 'kanzu-support-desk' ),
						__( 'Filtering', 'kanzu-support-desk' ),
						__( 'Filter tickets using ticket status or severity.', 'kanzu-support-desk' ),
						__( 'Sorting', 'kanzu-support-desk' ),
						__( 'Re-order tickets by clicking on the header of the column you would like to order by', 'kanzu-support-desk' )
					);
					break;
				case 'ksd-add-new-ticket':
					$contextual_help = sprintf(
						'<span><h2> %s </h2> <p> %s </p><p> <b> %s :</b> %s </p></span>',
						__( 'New Ticket', 'kanzu-support-desk' ),
						__( 'Add details for a new ticket. Use the publish button to make the ticket publically visible', 'kanzu-support-desk' ),
						__( 'Save', 'kanzu-support-desk' ),
						__( 'When you save a ticket and do not publish it, it will NOT be visible to the customer. Use this for tickets that you are still making changes to', 'kanzu-support-desk' )
					);
				case 'ksd-single-ticket-details':
					$contextual_help = sprintf(
						'<span><h2> %s </h2> <p> <b> %s :</b> %s </p><p> <b> %s :</b> %s </p><p> <b> %s :</b> %s </p></span>',
						__( 'Reply ticket/Edit ticket information', 'kanzu-support-desk' ),
						__( 'Modify ticket information', 'kanzu-support-desk' ),
						__( 'Modify the details of a ticket in the "Ticket Information" box. Change status, severity, assignee and other ticket information. Use the Update button to save your changes', 'kanzu-support-desk' ),
						__( 'Reply your customer', 'kanzu-support-desk' ),
						__( 'Type a reply and use the Send button to send your reply to your customer', 'kanzu-support-desk' ),
						__( 'Private Notes', 'kanzu-support-desk' ),
						__( 'Save a private note that will be viewed by other agents. Customers are NOT able to view private notes', 'kanzu-support-desk' )
					);

					break;
				case 'ksd-edit-categories':
					$contextual_help = sprintf(
						'<span><h2> %s </h2> <p> %s </p></span>',
						__( 'Ticket Categories', 'kanzu-support-desk' ),
						__( 'Add/Edit/Delete ticket categories. Use categories to organize your tickets', 'kanzu-support-desk' )
					);
					break;
				case 'ksd-edit-products':
					$contextual_help = sprintf(
						'<span><h2> %s </h2> <p> %s </p></span>',
						__( 'Ticket Products', 'kanzu-support-desk' ),
						__( 'Add/Edit/Delete ticket products. Use products to identify which of your products the ticket is attached to', 'kanzu-support-desk' )
					);
					break;
				case 'ksd-dashboard':
					$contextual_help = sprintf(
						'<span><h2> %s </h2> <p> %s </p></span>',
						__( 'Ticket Dashboard', 'kanzu-support-desk' ),
						__( 'Shows an overview of your performance', 'kanzu-support-desk' )
					);
					break;
				case 'ksd-settings':
					$contextual_help = sprintf(
						'<span><h2> %s </h2> <p> %s </p></span>',
						__( 'KSD Settings', 'kanzu-support-desk' ),
						__( 'Customize your KSD experience by modifying your settings. Each setting has a help message next to it.', 'kanzu-support-desk' )
					);
					break;
			}

			if ( version_compare( $wp_version, '3.3', '>=' ) ) : // Sweet tabbed contextual help was introduced in 3.3
				$screen->add_help_tab( $this->add_support_help_tab() );
				$screen->add_help_tab(
					array(
						'id'      => $current_ksd_screen . '-help',
						'title'   => __( 'Overview' ),
						'content' => $contextual_help,
					)
				);
			else :
				return $contextual_help;
			endif;
		}

		private function add_support_help_tab() {
			ob_start();
			include_once KSD_PLUGIN_DIR . 'templates/admin/help-support-tab.php';
			$support_form = ob_get_clean();

			return array(
				'id'      => 'ksd-support-tab-help',
				'title'   => __( 'Help/Feedback' ),
				'content' => $support_form,
			);
		}

		/*
				 * Get categories options
		*/
		public static function get_categories_options() {
			$args       = array(
				'taxonomy'   => 'ticket_category',
				'hide_empty' => 0,
			);
			$categories = get_categories( $args );
			$options    = '';
			foreach ( $categories as $category ) {
				$options .= '<option value=' . $category->cat_ID . '>' . esc_html( $category->name ) . '</option>';
			}
			return $options;
		}

		/**
		 * Get the KSD severity list
		 *
		 * @since 2.0.0
		 */
		public function get_severity_list() {
			return array(
				'low'    => __( 'Low', 'kanzu-support-desk' ),
				'medium' => __( 'Medium', 'kanzu-support-desk' ),
				'high'   => __( 'High', 'kanzu-support-desk' ),
				'urgent' => __( 'Urgent', 'kanzu-support-desk' ),
			);
		}

		/**
		 * Get the KSD status list
		 *
		 * @since 2.0.0
		 */
		public function get_status_list() {
			return array(
				'open'     => __( 'Open', 'kanzu-support-desk' ),
				'pending'  => __( 'Pending', 'kanzu-support-desk' ),
				'resolved' => __( 'Resolved', 'kanzu-support-desk' ),
			);
		}

		/**
		 * Get status list as select options
		 */
		public function get_status_list_options() {
			$options = '';
			foreach ( $this->get_status_list() as $value => $status ) {
				$options .= '<option value="' . $value . '">' . $status . '</option>';
			}
			return $options;
		}

		/**
		 * Add settings action link to the plugins page.
		 *
		 * @since    1.0.0
		 */
		public function add_action_links( $links ) {

			return array_merge(
				array(
					'settings' => '<a href="' . admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-settings' ) . '">' . __( 'Settings', 'kanzu-support-desk' ) . '</a>',
				),
				$links
			);

		}


		/**
		 * Include the files we use in the admin dashboard
		 */
		public function do_admin_includes() {
			include_once KSD_PLUGIN_DIR . 'includes/libraries/class-controllers.php';
		}

		/**
		 * Convert a reply's object into a $_POST array
		 *
		 * @param int    $ticket_ID The parent ticket's ID
		 * @param Object $reply The reply's object
		 * @since 1.7.0
		 */
		private function convert_reply_object_to_post( $ticket_ID, $reply ) {
			$_POST                         = array();
			$_POST['tkt_id']               = $ticket_ID; // The ticket ID
			$_POST['ksd_ticket_reply']     = $reply->tkt_message; // Get the reply
			$_POST['ksd_rep_created_by']   = $reply->tkt_cust_id; // The customer's ID
			$_POST['ksd_rep_date_created'] = $reply->tkt_time_logged; // @since 1.6.2
			$_POST['ksd_addon_tkt_id']     = $reply->addon_tkt_id; // The add-on's ID for this ticket

			if ( isset( $reply->tkt_cc ) ) {
				$_POST['ksd_tkt_cc'] = $reply->tkt_cc;
			}
			return $_POST;
		}

		/**
		 * Get the statistics that show on the dashboard, above the graph
		 */
		public function get_dashboard_summary_stats() {
			if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
				die( __( 'Busted!', 'kanzu-support-desk' ) );
			}
			$this->do_admin_includes();
			try {
				$tickets       = new KSD_Tickets_Controller();
				$summary_stats = $tickets->get_dashboard_statistics_summary();
				// Compute the average. We do this here rather than using AVG in the DB query to take the load off the Db.
				$total_response_time = 0;
				foreach ( $summary_stats['response_times'] as $response_time ) {
					$total_response_time += $response_time->time_difference;
				}
				// Prevent division by zero.
				if ( count( $summary_stats['response_times'] ) > 0 ) {
					$summary_stats['average_response_time'] = date( 'H:i:s', $total_response_time / count( $summary_stats['response_times'] ) );
				} else {
					$summary_stats['average_response_time'] = '00:00:00';
				}
				echo json_encode( $summary_stats );
			} catch ( Exception $e ) {
				$response = array(
					'error' => array(
						'message' => $e->getMessage(),
						'code'    => $e->getCode(),
					),
				);
				echo json_encode( $response );
			}
			die(); // IMPORTANT: don't leave this out.
		}

		/**
		 * Update all settings
		 */
		public function update_settings() {
			if ( ! wp_verify_nonce( $_POST['update-settings-nonce'], 'ksd-update-settings' ) ) {
				die( __( 'Busted!', 'kanzu-support-desk' ) );
			}
			try {
				$old_settings = $updated_settings = Kanzu_Support_Desk::get_settings(); // Get current settings

				// Iterate through the new settings and save them. We skip all multiple checkboxes; those are handled later. As of 1.5.0, there's only one set of multiple checkboxes, ticket_management_roles.
				foreach ( $updated_settings as $option_name => $current_value ) {

					// Unset recapcha secret if it contains ********* i.e. password has not been set or changed.
					if ( 'recaptcha_secret_key' === $option_name ) {
						if ( false !== strpos( $_POST['recaptcha_secret_key'], '************************************' ) ) {
							continue;
						}
					}

					if ( $option_name == 'ticket_management_roles' ) {
						continue; // Don't handle multiple checkboxes in here @since 1.5.0
					}
					if ( $option_name == 'ticket_mail_message' ) {
						// Support HTML in ticket message @since 1.7.0.
						$updated_settings[ $option_name ] = ( isset( $_POST[ $option_name ] ) ? wp_kses_post( stripslashes( $_POST[ $option_name ] ) ) : $updated_settings[ $option_name ] );
						continue;
					}
					$updated_settings[ $option_name ] = ( isset( $_POST[ $option_name ] ) && ! is_array( $_POST[ $option_name ] ) ? sanitize_text_field( stripslashes( $_POST[ $option_name ] ) ) : $updated_settings[ $option_name ] );
				}
				// For a checkbox, if it is unchecked then it won't be set in $_POST.
				$checkbox_names = array(
					'show_support_tab',
					'tour_mode',
					'enable_new_tkt_notifxns',
					'enable_recaptcha',
					'enable_notify_on_new_ticket',
					'enable_anonymous_tracking',
					'enable_customer_signup',
					'supportform_show_categories',
					'supportform_show_severity',
					'supportform_show_products',
					'show_woo_support_tickets_tab',
				);
				// Iterate through the checkboxes and set the value to "no" for all that aren't set.
				foreach ( $checkbox_names as $checkbox_name ) {
					$updated_settings[ $checkbox_name ] = ( ! isset( $_POST[ $checkbox_name ] ) ? 'no' : $updated_settings[ $checkbox_name ] );
				}
				// Now handle the multiple checkboxes. As of 1.5.0, only have ticket_management_roles. If it isn't set, use administrator.
				$updated_settings['ticket_management_roles'] = ! isset( $_POST['ticket_management_roles'] ) ? 'administrator' : $this->convert_multiple_checkbox_to_setting( $_POST['ticket_management_roles'] );

				// Apply the settings filter to get settings from add-ons.
				$updated_settings = apply_filters( 'ksd_settings', $updated_settings, $_POST );

				$status = false;
				if ( $old_settings === $updated_settings ) {
					// update_option returns false when there is no change to the settings.
					$status = true;
				} else {
					$status = update_option( KSD_OPTIONS_KEY, $updated_settings );
				}

				if ( true === $status ) {
					do_action( 'ksd_settings_saved' );
					echo json_encode( __( 'Settings Updated', 'kanzu-support-desk' ) );
				} else {
					throw new Exception( __( 'Update failed. Please retry.', 'kanzu-support-desk' ), -1 );
				}
				die();
			} catch ( Exception $e ) {
				$response = array(
					'error' => array(
						'message' => $e->getMessage(),
						'code'    => $e->getCode(),
					),
				);
				echo json_encode( $response );
				die(); // IMPORTANT: don't leave this out.
			}
		}

		/**
		 * Reset settings to default
		 */
		public function reset_settings() {
			if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
				die( __( 'Busted!', 'kanzu-support-desk' ) );
			}
			try {
				$ksd_install   = Installer::get_instance();
				$base_settings = $ksd_install->get_default_options();
				// Add the settings from add-ons.
				$base_settings = apply_filters( 'ksd_settings', $base_settings );
				$status        = update_option( KSD_OPTIONS_KEY, $base_settings );
				if ( $status ) {
					echo json_encode( __( 'Settings Reset', 'kanzu-support-desk' ) );
				} else {
					throw new Exception( __( 'Reset failed. Please retry', 'kanzu-support-desk' ), -1 );
				}
				die();
			} catch ( Exception $e ) {
				$response = array(
					'error' => array(
						'message' => $e->getMessage(),
						'code'    => $e->getCode(),
					),
				);
				echo json_encode( $response );
				die(); // IMPORTANT: don't leave this out.
			}
		}

		/**
		 * Retrieve and display the list of add-ons
		 *
		 * @since 1.1.0
		 */
		public function load_ksd_addons() {
			ob_start();
			if ( false === ( $cache = get_transient( 'ksd_add_ons_feed' ) ) ) {
				$feed = wp_remote_get( 'https://kanzucode.com/?feed=ksdaddons', array( 'sslverify' => false ) );
				if ( ! is_wp_error( $feed ) ) {
					if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
						$cache = wp_remote_retrieve_body( $feed );
						set_transient( 'ksd_add_ons_feed', $cache, 3600 );
					}
				} else {
					$cache = '<div class="add-on-error add-ons"><p>' . __( 'Sorry, an error occurred while retrieving the add-ons list. A re-attempt will be made later. Thank you.', 'kanzu-support-desk' ) . '</div>';
				}
			}
			echo $cache;
			echo ob_get_clean();
		}

		/**
		 * Disable tour mode
		 *
		 * @since 1.1.0
		 */
		public function disable_tour_mode() {
			$ksd_settings              = Kanzu_Support_Desk::get_settings();
			$ksd_settings['tour_mode'] = 'no';
			Kanzu_Support_Desk::update_settings( $ksd_settings );
			echo json_encode( 1 );
			die();
		}

		/**
		 * Enable usage statistics
		 *
		 * @since 1.6.7
		 */
		public function enable_usage_stats() {
			$ksd_settings                              = Kanzu_Support_Desk::get_settings();
			$ksd_settings['enable_anonymous_tracking'] = 'yes';
			Kanzu_Support_Desk::update_settings( $ksd_settings );
			echo json_encode( __( 'Successfully enabled. Thank you!', 'kanzu-support-desk' ) );
			die();
		}

		/**
		 * Return the HTML for a feedback form
		 *
		 * @param string $position The position of the form
		 * @param string $send_button_text Submit button text
		 * @TODO Move this to templates folder
		 */
		public static function output_feeback_form( $position, $send_button_text = 'Send' ) {
			$form  = '<form action="#" class="ksd-feedback-' . $position . '" method="POST">';
			$form .= '<p><textarea name="ksd_user_feedback" rows="5" cols="100"></textarea></p>';
			$form .= '<input name="action" type="hidden" value="ksd_send_feedback" />';
			$form .= '<input name="feedback_type" type="hidden" value="' . $position . '" />';
			$form .= wp_nonce_field( 'ksd-send-feedback', 'feedback-nonce' );
			$form .= '<p><input type="submit" class="button-primary ' . $position . '" name="ksd-feedback-submit" value="' . $send_button_text . '"/></p>';
			$form .= '</form>';
			$form .= '<div class="ksd-feedback-response"></div>';
			return $form;
		}


		public function reset_role_caps() {
			include_once KSD_PLUGIN_DIR . 'includes/class-roles.php';
			KSD()->roles->modify_all_role_caps( 'add' );
			wp_send_json_success( __( 'Roles reset. All should be well now', 'kanzu-support-desk' ) );
		}


		/**
		 * Send feedback to Kanzu Analytics
		 *
		 * @param array $data
		 * @return array
		 */
		public function send_to_analytics( $data ) {
			return wp_remote_post(
				'http://analytics.kanzucode.com/wp-json/kanzu-analytics/v1/api',
				array(
					'method'      => 'POST',
					'timeout'     => 20,
					'redirection' => 5,
					'httpversion' => '1.1',
					'blocking'    => true,
					'body'        => $data,
				)
			);
		}
		/**
		 * Convert the multiple checkbox input, which is an array in $_POST, into a setting,
		 * which is a string of the values separated by |. We save them this way since we use
		 * them in an SQL REGEXP which uses them as is
		 * e.g. SELECT field1,field2 from table where REGEXP 'value1|value2|value3'
		 *
		 * @param Array $multiple_checbox_array An array of the checked checkboxes in a set of multiple checkboxes
		 * @return string A |-separated list of the checked values
		 * @since 1.5.0
		 */
		private function convert_multiple_checkbox_to_setting( $multiple_checbox_array ) {
			$setting_string = 'administrator'; // By default, the administrator has access
			foreach ( $multiple_checbox_array as $checkbox ) {
				$setting_string .= '|' . $checkbox;
			}
			return $setting_string;
		}

		/**
		 * Append plugin to active plugin list
		 *
		 * @since    1.1.1
		 */
		public static function append_to_activelist( $active_addons ) {
			$active_addons['ksd-mail'] = 'ksd-mail/ksd-mail.php';
			return $active_addons;
		}

		/**
		 * Add KSD tickets import to the WordPress tools toolbox
		 *
		 * @since   1.5.2
		 */
		public function add_importer_to_toolbox() {
			echo '
	            <div class="tool-box">
	                <h3 class="title"> ' . __( 'KSD Importer', 'kanzu-support-desk' ) . '</h3>
	                 <p>
	                 Import tickets into Kanzu Support Desk. Use the  <a href="?import=ksdimporter">KSD Importer </a>
	                 </p>
	            </div>
	        ';
		}

		/**
		 * Hand this over to the function in the Import class
		 *
		 * @param int $imported_ticket_id
		 * @param int $logged_ticket_id
		 */
		public function new_ticket_imported( $imported_ticket_id, $logged_ticket_id ) {
			$importer = new KSD_Importer();
			$importer->new_ticket_imported( $imported_ticket_id, $logged_ticket_id );
		}

		/**
		 * Initialize the KSD importer; it enables users to import
		 * tickets into KSD
		 * ksd_importer_init
		 *
		 * @since   1.5.4
		 */
		public function ksd_importer_init() {

			$id          = 'ksdimporter';
			$name        = __( 'KSD Importer', 'kanzu-support-desk' );
			$description = __( 'Import support tickets into the Kanzu Support Desk plugin.', 'kanzu-support-desk' );

			include_once KSD_PLUGIN_DIR . 'includes/libraries/class-importer.php';
			$importer = new KSD_Importer();
			$callback = array( $importer, 'dispatch' );
			register_importer( $id, $name, $description, $callback );
		}

		/**
		 * Handle an AJAX request to change the license's status. We use this to activate
		 * and deactivate licenses
		 */
		public function modify_license_status() {
			if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
				die( __( 'Busted!', 'kanzu-support-desk' ) );
			}

			$response = $this->do_license_modifications( $_POST['license_action'], $_POST['plugin_name'], $_POST['plugin_author_uri'], $_POST['plugin_options_key'], $_POST['license_key'], $_POST['license_status_key'], sanitize_text_field( $_POST['license'] ) );
			echo json_encode( $response );
			die(); // Important. Don't leave this out
		}

		/**
		 * For KSD plugins, make a remote call to Kanzu Code to activate/Deactivate/check license status
		 *
		 * @param string $action The action to perform on the license. Can be 'activate_license', 'deactivate_license' or 'check_license'
		 * @param string $plugin_name The plugin name
		 * @param string $plugin_author_uri Plugin author's URI
		 * @param string $plugin_options_key The plugin options key used to store its options in the KSD options array
		 * @param string $license_key The key used to store the license
		 * @param string $license_status_key The key used to store the license status
		 * @param string $license The license to check
		 * @return string $response Returns a response message
		 */
		public function do_license_modifications( $action, $plugin_name, $plugin_author_uri, $plugin_options_key, $license_key, $license_status_key, $license = null ) {
			$response_message = __( 'Sorry, an error occurred. Please retry or reload the page', 'kanzu-support-desk' );

			/*
			Retrieve the license from the database*/
			// First get overall settings
			$base_settings = get_option( KSD_OPTIONS_KEY );
			// Check that the key exists
			$plugin_settings = ( isset( $base_settings[ $plugin_options_key ] ) ? $base_settings[ $plugin_options_key ] : array() );

			if ( is_null( $license ) ) {
				$license = trim( $plugin_settings[ $license_key ] );
			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => $action,
				'license'    => $license,
				'item_name'  => urlencode( $plugin_name ), // the name of our product in EDD
				'url'        => home_url(),
			);
			$response   = wp_remote_post(
				$plugin_author_uri,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay.
			if ( is_wp_error( $response ) ) {
				return $response_message;
			}
			// decode the license data.
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			switch ( $action ) {
				case 'activate_license':
				case 'check_license':
					if ( $license_data && 'valid' == $license_data->license ) {
						$plugin_settings[ $license_status_key ] = 'valid';
						$addons                                 = $this->get_installed_addons();
						$response_message                       = __( 'License successfully validated. Welcome to a super-charged Kanzu Support Desk! Please reload the page.', 'kanzu-support-desk' );
						foreach ( $addons as $addon ) {
							if ( $plugin_options_key === $addon ) {
								$response_message = apply_filters( 'ksd_message_succ_addon_lic_activation_' . $addon, $response_message );
							}
						}
					} else {
						// Invalid license.
						$plugin_settings[ $license_status_key ] = 'invalid';
						$response_message                       = apply_filters( 'ksd_message_invalid_addon_license', __( 'Invalid License. Please renew your license', 'kanzu-support-desk' ) );
					}
					break;
				case 'deactivate_license':
					if ( $license_data && 'deactivated' == $license_data->license ) {
						$plugin_settings[ $license_status_key ] = 'invalid';
						$response_message                       = apply_filters( 'ksd_message_succ_addon_lic_deactivation', __( 'Your license has been deactivated successfully. Thank you.', 'kanzu-support-desk' ) );
					}
					break;
			}
			// Retrieve the license for saving.
			$plugin_settings[ $license_key ] = $license;

			// Update the Db.
			$base_settings[ $plugin_options_key ] = $plugin_settings;
			update_option( KSD_OPTIONS_KEY, $base_settings );

			return $response_message;
		}

		private function get_installed_addons() {
			$addons   = array();
			$settings = Kanzu_Support_Desk::get_settings();
			foreach ( $settings as $key => $value ) {
				if ( 'ksd_' === substr( $key, 0, 4 ) ) {
					if ( 'ksd_owner' != $key && 'ksd_activation_time' != $key ) {
						$addons[] = $key;
					}
				}
			}
			return $addons;
		}

		/**
		 * Add cc button
		 *
		 * @since 1.6.8
		 */
		private function add_tinymce_cc_button() {
			if ( 'edit' !== filter_input( INPUT_GET, 'action' ) && 'ksd_ticket' !== filter_input( INPUT_GET, 'post_type' ) ) {
				return;
			}
			add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_cc_plugin' ) );
			add_filter( 'mce_buttons', array( $this, 'register_tinymce_cc_button' ), 10, 2 );
		}

		/**
		 * Register the CC tinymce button
		 *
		 * @param array $plugin_array
		 * @return string
		 */
		public function add_tinymce_cc_plugin( $plugin_array ) {
			$plugin_array['KSDCC'] = KSD_PLUGIN_URL . 'assets/js/ksd-wp-editor-cc.js';
			return $plugin_array;
		}

		/**
		 * Register the CC button
		 *
		 * @param type $buttons
		 * @return type
		 */
		public function register_tinymce_cc_button( $buttons, $editor_id ) {
			global $current_screen;
			if ( 'ksd_ticket' === $current_screen->post_type ) {
				// Add the CC button only if it is a KSD editor (not a post, page, etc editor)
				if ( ! in_array( 'ksd_cc_button', $buttons ) ) {
					array_push( $buttons, 'ksd_cc_button' );
				}
			}
			return $buttons;
		}

		/**
		 * Send tracking data
		 *
		 * @return null
		 */
		public function send_tracking_data() {
			$settings = Kanzu_Support_Desk::get_settings();
			if ( 'yes' !== $settings['enable_anonymous_tracking'] ) {
				return;
			}
			if ( isset( $settings['ksd_tracking_last_send'] ) && $settings['ksd_tracking_last_send'] > strtotime( '-1 week' ) ) {
				return;
			}

			$data = $this->get_tracking_data();

			wp_remote_post(
				'http://analytics.kanzucode.com/wp-json/kanzu-analytics/v1/api',
				array(
					'method'      => 'POST',
					'timeout'     => 20,
					'redirection' => 5,
					'httpversion' => '1.1',
					'blocking'    => true,
					'body'        => $data,
				)
			);

			$settings['ksd_tracking_last_send'] = time();
			Kanzu_Support_Desk::update_settings( $settings );
		}

		/**
		 * Aggregate tracking data to send
		 *
		 * @return array
		 */
		private function get_tracking_data() {
			$data           = array();
			$data['action'] = 'tracking_data';

			// Retrieve current plugin info.
			$plugin_data     = get_plugin_data( KSD_PLUGIN_FILE );
			$plugin          = $plugin_data['Name'];
			$data['product'] = $plugin;

			// Retrieve current theme info.
			$theme_data    = wp_get_theme();
			$theme         = $theme_data->Name . ' ' . $theme_data->Version;
			$data['url']   = home_url();
			$data['theme'] = $theme;
			$data['email'] = get_bloginfo( 'admin_email' );

			// Retrieve current plugin information.
			if ( ! function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}

			$plugins        = array_keys( get_plugins() );
			$active_plugins = get_option( 'active_plugins', array() );

			foreach ( $plugins as $key => $plugin ) {
				if ( in_array( $plugin, $active_plugins ) ) {
					// Remove active plugins from list so we can show active and inactive separately.
					unset( $plugins[ $key ] );
				}
			}

			// Load all options.
			$data['options'] = wp_load_alloptions();

			$data['active_plugins']   = $active_plugins;
			$data['inactive_plugins'] = $plugins;

			return $data;
		}

		/**
		 * Add custom boxes to quick edit
		 *
		 * @param string $column_name
		 * @param string $post_type
		 * @return null
		 */
		public function quick_edit_custom_boxes( $column_name, $post_type ) {
			if ( 'ksd_ticket' != $post_type ) {
				return;
			}?>
							<?php
							switch ( $column_name ) :
								case 'assigned_to':
									?>
									<fieldset class="inline-edit-col-right inline-edit-book">
										<div class="inline-edit-col column-<?php echo $column_name; ?>">
											<label class="inline-edit-group">
												<span class="title"><?php esc_html_e( 'Assigned To:', 'kanzu-support-desk' ); ?></span>
												<select name="_ksd_tkt_info_assigned_to">
													<option value="-1">--<?php esc_html_e( 'No Change', 'kanzu-support-desk' ); ?>--</option>
													<?php foreach ( get_users( array( 'role__in' => array( 'ksd_agent', 'ksd_supervisor', 'administrator' ) ) ) as $agent ) { ?>
													<option value="<?php echo $agent->ID; ?>">
														<?php echo $agent->display_name; ?>
													</option>
													<?php }; ?>
													<option value="0"><?php esc_html_e( 'No One', 'kanzu-support-desk' ); ?></option>
												</select>
											</label>
											</div>
										</fieldset>
									<?php
									break;
								case 'severity':
									global $current_user;
									?>
									<fieldset class="inline-edit-col-right inline-edit-book">
										<div class="inline-edit-col column-<?php echo $column_name; ?>">
										<label class="inline-edit-group">
											<span class="title"><?php esc_html_e( 'Severity:', 'kanzu-support-desk' ); ?></span>
											<select name="_ksd_tkt_info_severity">
												<option value="-1">--<?php esc_html_e( 'No Change', 'kanzu-support-desk' ); ?>--</option>
																						   <?php
																							foreach ( $this->get_severity_list() as $severity_label => $severity ) :
																								?>
												<option value="<?php echo $severity_label; ?>">
																								<?php echo $severity; ?>
												</option>
																<?php endforeach; ?>
										</select>
									</label>
									</div>
								</fieldset>
								<fieldset class="inline-edit-col-right inline-edit-book">
									<div class="inline-edit-col column-<?php echo $column_name; ?>">
									<label class="inline-edit-group">
										<span class="title"><?php esc_html_e( 'State:', 'kanzu-support-desk' ); ?></span>
										<select name="_ksd_tkt_info_is_read_by_<?php echo $current_user->ID; ?>">
											<option value="-1">--<?php esc_html_e( 'No Change', 'kanzu-support-desk' ); ?>--</option>
											 <option value="read"><?php esc_attr_x( 'Read', 'Ticket state', 'kanzu-support-desk' ); ?></option>
											 <option value="unread"><?php esc_attr_x( 'Unread', 'Ticket state', 'kanzu-support-desk' ); ?></option>
										</select>
									</label>
									</div>
								</fieldset>
										<?php
									break;
		endswitch;

		}

		/**
		 * Change the Publish button to update
		 *
		 * @param string $translation
		 * @param string $text
		 * @return string
		 * @TODO Re-do this. Not too consistent
		 */
		public function change_publish_button( $translation, $text ) {
			if ( $text == 'Publish' && 'ksd_ticket' == get_post_type() ) {
				return __( 'Update', 'kanzu-support-desk' );
			}

			return $translation;
		}

		public function append_panel_to_wpcf7( $panels ) {
			$panels['ksd-support-form'] = array(
				'title'    => __( 'Kanzu Support Desk', 'kanzu-support-desk' ),
				'callback' => array( $this, 'render_contact_form_7_panel' ),
			);
			return $panels;
		}

		public function render_contact_form_7_panel() {
			echo 'Hello World';
		}

	}

endif;

return new KSD_Admin();
