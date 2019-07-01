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

namespace Kanzu\Ksd\Admin;

use Kanzu\Ksd\Admin\Notification\Notification as ksd_notifications;
use Kanzu\Ksd\Admin\Settings as ksd_settings;

class Hooks_registry {

	/**
	 * The admin Object
	 *
	 * @var Object
	 */
	private $ksd_admin;

	/**
	 * The notofocations object
	 *
	 * @var Object
	 */
	private $ksd_notifications;

	/**
	 * The settings object
	 *
	 * @var Object
	 */
	private $ksd_settings;

	/**
	 * class constructor
	 */
	public function __construct() {
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-admin.php';
		$this->ksd_admin         = Admin::get_instance();
		$this->ksd_notifications = $ksd_notifications;
		$this->ksd_settings      = $ksd_settings;

		$this->add_action_hooks();
		$this->add_filter_hooks();
		$this->add_ajax_hooks();

	}

	/**
	 * Add actions
	 *
	 * @return void
	 */
	public function add_action_hooks() {
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this->ksd_admin, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this->ksd_admin, 'enqueue_admin_scripts' ) );
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this->ksd_settings, 'add_menu_pages' ) );

		// Add the attachments button
		add_action( 'media_buttons', array( $this->ksd_admin, 'add_attachments_button' ), 15 );

		// Load add-ons
		add_action( 'ksd_load_addons', array( $this->ksd_admin, 'load_ksd_addons' ) );

		// Generate a debug file
		add_action( 'ksd_generate_debug_file', array( $this, 'generate_debug_file' ) );

		// Register KSD tickets importer
		add_action( 'admin_init', array( $this->ksd_admin, 'ksd_importer_init' ) );

		// Do actions called in $_POST
		add_action( 'init', array( $this->ksd_admin, 'do_post_and_get_actions' ) );

		// Add contextual help messages
		add_action( 'contextual_help', array( $this->ksd_admin, 'add_contextual_help' ), 10, 3 );

		// In 'Edit ticket' view, customize the screen
		add_action( 'add_meta_boxes', array( $this->ksd_admin, 'edit_metaboxes' ), 10, 2 );

		// Add items to the submitdiv in 'edit ticket' view
		add_action( 'post_submitbox_misc_actions', array( $this->ksd_admin, 'edit_submitdiv' ) );

		// Add hash URLs to single ticket view
		add_action( 'edit_form_before_permalink', array( $this->ksd_admin, 'show_hash_url' ) );

		// Add KSD Importer to tool box
		add_action( 'tool_box', array( $this->ksd_admin, 'add_importer_to_toolbox' ) );
		// Add ticket filters to the table grid drop-down
		add_action( 'restrict_manage_posts', array( $this->ksd_admin, 'ticket_table_filter_headers' ) );

		// Bulk edit
		add_action( 'bulk_edit_custom_box', array( $this->ksd_admin, 'quick_edit_custom_boxes' ), 10, 2 );
		add_action( 'quick_edit_custom_box', array( $this->ksd_admin, 'quick_edit_custom_boxes' ), 10, 2 );

		// Add feedback
		add_action( 'admin_footer', array( $this, 'append_admin_feedback' ), 25 );

		// On EDD download/WooCommerce publish
		add_action( 'publish_product', array( $this->ksd_admin, 'on_new_product' ), 10, 2 );
		add_action( 'publish_download', array( $this->ksd_admin, 'on_new_product' ), 10, 2 );

		// Send tracking data
		add_action( 'admin_head', array( $this->ksd_admin, 'send_tracking_data' ) );

		// Add 'My tickets' button to 'My profile' page
		add_action( 'personal_options', array( $this->ksd_admin, 'add_my_tickets_link' ) );

	}

	/**
	 * add filters
	 *
	 * @return void
	 */
	public function add_filter_hooks() {
		// Add an action link pointing to the settings page.
		add_filter( 'plugin_action_links_' . plugin_basename( KSD_PLUGIN_FILE ), array( $this->ksd_admin, 'add_action_links' ) );

		// Set whether an incoming ticket is a reply
		add_filter( 'ksd_new_ticket_or_reply', array( $this->ksd_admin, 'set_is_ticket_a_reply' ) );

		// Save ticket and its information
		add_filter( 'wp_insert_post_data', array( $this->ksd_admin, 'save_ticket_info' ), '99', 2 );

		// Alter messages
		add_filter( 'post_updated_messages', array( $this->ksd_admin, 'ticket_updated_messages' ) );

		// Get final status for ticket logged by importation
		add_action( 'ksd_new_ticket_imported', array( $this->ksd_admin, 'new_ticket_imported', 10, 2 ) );

		// Modify ticket deletion and un-deletion messages
		add_filter( 'bulk_post_updated_messages', array( $this->ksd_admin, 'ticket_bulk_update_messages' ), 10, 2 );

		// Add custom views
		add_filter( 'views_edit-ksd_ticket', array( $this->ksd_admin, 'ticket_views' ) );

		// Add headers to the tickets grid
		add_filter( 'manage_ksd_ticket_posts_columns', array( $this->ksd_admin, 'add_tickets_headers' ) );
		// Populate the new columns
		add_action( 'manage_ksd_ticket_posts_custom_column', array( $this->ksd_admin, 'populate_ticket_columns' ), 10, 2 );
		// Add sorting to the new columns
		add_filter( 'manage_edit-ksd_ticket_sortable_columns', array( $this->ksd_admin, 'ticket_table_sortable_columns' ) );
		// Remove some default columns
		add_filter( 'manage_edit-ksd_ticket_columns', array( $this->ksd_admin, 'ticket_table_remove_columns' ) );

		add_filter( 'request', array( $this->ksd_admin, 'ticket_table_columns_orderby' ) );
		// Add ticket filters to the table grid drop-down
		add_filter( 'parse_query', array( $this->ksd_admin, 'ticket_table_apply_filters' ) );
		// Display ticket status next to the ticket title
		add_filter( 'display_post_states', array( $this->ksd_admin, 'display_ticket_statuses_next_to_title' ) );
		// Change 'Publish' button to 'Update'
		add_filter( 'gettext', array( $this->ksd_admin, 'change_publish_button' ), 10, 2 );

		// Tag 'read' tickets in the ticket grid
		add_filter( 'post_class', array( $this->ksd_admin, 'append_classes_to_ticket_grid' ), 10, 3 );

		// Contact form 7
		// add_filter( 'wpcf7_editor_panels', array( $this->ksd_admin, 'append_panel_to_wpcf7' ) );
	}

	/**
	 * ajax hooks
	 *
	 * @return void
	 */
	public function add_ajax_hooks() {
		// Handle AJAX calls
		add_action( 'wp_ajax_ksd_filter_tickets', array( $this->ksd_admin, 'filter_tickets' ) );
		add_action( 'wp_ajax_ksd_filter_totals', array( $this->ksd_admin, 'filter_totals' ) );
		add_action( 'wp_ajax_ksd_log_new_ticket', array( $this->ksd_admin, 'log_new_ticket' ) );
		add_action( 'wp_ajax_ksd_delete_ticket', array( $this->ksd_admin, 'delete_ticket' ) );
		add_action( 'wp_ajax_ksd_change_status', array( $this->ksd_admin, 'change_status' ) );
		add_action( 'wp_ajax_ksd_change_severity', array( $this->ksd_admin, 'change_severity' ) );
		add_action( 'wp_ajax_ksd_assign_to', array( $this->ksd_admin, 'assign_to' ) );
		add_action( 'wp_ajax_ksd_reply_ticket', array( $this->ksd_admin, 'reply_ticket' ) );
		add_action( 'wp_ajax_ksd_get_single_ticket', array( $this->ksd_admin, 'get_single_ticket' ) );
		add_action( 'wp_ajax_ksd_dashboard_ticket_volume', array( $this->ksd_admin, 'get_dashboard_ticket_volume' ) );
		add_action( 'wp_ajax_ksd_get_dashboard_summary_stats', array( $this->ksd_admin, 'get_dashboard_summary_stats' ) );
		add_action( 'wp_ajax_ksd_update_settings', array( $this->ksd_admin, 'update_settings' ) );
		add_action( 'wp_ajax_ksd_reset_settings', array( $this->ksd_admin, 'reset_settings' ) );
		add_action( 'wp_ajax_ksd_update_private_note', array( $this->ksd_admin, 'update_private_note' ) );
		add_action( 'wp_ajax_ksd_send_feedback', array( $this->ksd_admin, 'send_feedback' ) );
		add_action( 'wp_ajax_ksd_support_tab_send_feedback', array( $this->ksd_admin, 'send_support_tab_feedback' ) );
		add_action( 'wp_ajax_ksd_disable_tour_mode', array( $this->ksd_admin, 'disable_tour_mode' ) );
		add_action( 'wp_ajax_ksd_get_notifications', array( $this->ksd_admin, 'get_notifications' ) );
		add_action( 'wp_ajax_ksd_notify_new_ticket', array( $this->ksd_admin, 'notify_new_ticket' ) );
		add_action( 'wp_ajax_ksd_bulk_change_status', array( $this->ksd_admin, 'bulk_change_status' ) );
		add_action( 'wp_ajax_ksd_change_read_status', array( $this->ksd_admin, 'change_read_status' ) );
		add_action( 'wp_ajax_ksd_modify_license', array( $this->ksd_admin, 'modify_license_status' ) );
		add_action( 'wp_ajax_ksd_enable_usage_stats', array( $this->ksd_admin, 'enable_usage_stats' ) );
		add_action( 'wp_ajax_ksd_update_ticket_info', array( $this->ksd_admin, 'update_ticket_info' ) );
		add_action( 'wp_ajax_ksd_get_ticket_activity', array( $this->ksd_admin, 'get_ticket_activity' ) );
		add_action( 'wp_ajax_ksd_migrate_to_v2', array( $this->ksd_admin, 'migrate_to_v2' ) );
		add_action( 'wp_ajax_ksd_deletetables_v2', array( $this->ksd_admin, 'deletetables_v2' ) );
		add_action( 'wp_ajax_ksd_notifications_user_feedback', array( $this->ksd_admin, 'process_notification_feedback' ) );
		add_action( 'wp_ajax_ksd_notifications_disable', array( $this->ksd_email, 'disable_notifications' ) );
		add_action( 'wp_ajax_ksd_send_debug_email', array( $this->ksd_admin, 'send_debug_email' ) );
		add_action( 'wp_ajax_ksd_reset_role_caps', array( $this->ksd_admin, 'reset_role_caps' ) );
		add_action( 'wp_ajax_ksd_get_unread_ticket_count', array( $this->ksd_admin, 'get_unread_ticket_count' ) );
		add_action( 'wp_ajax_ksd_hide_questionnaire', array( $this->ksd_admin, 'hide_questionnaire' ) );

	}

	public function append_admin_feedback() {
		$notification = $this->ksd_notifications->get_new_notification();
		echo $notification;
	}

	/**
	 * Generates a debug file for download
	 *
	 * @since       1.7.0
	 * @return      void
	 */
	public function generate_debug_file() {
		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="ksd-debug.txt"' );
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-debuger.php';

		$ksd_debug = Debuger::get_instance();

		echo wp_strip_all_tags( $ksd_debug->retrieve_debug_info() );
		if ( ! defined( 'PHPUNIT' ) ) {
			die();
		}

	}

}
new Hook_Registry();
