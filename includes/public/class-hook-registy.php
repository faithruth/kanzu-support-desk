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

class KSD_Admin_hooks_registry {

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
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-ksd-admin.php';
		$this->ksd_admin = KSD_Admin::get_instance();

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

		//Enqueue styles
		add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
		//Add form for new ticket to the footer
		add_action('wp_footer', array($this, 'generate_new_ticket_form'));
		//Handle AJAX
		add_action('wp_ajax_nopriv_ksd_log_new_ticket', array($this, 'log_new_ticket'));
		add_action('wp_ajax_nopriv_ksd_register_user', array($this, 'register_user'));
		add_action('wp_ajax_nopriv_ksd_reply_ticket', array($this, 'reply_ticket'));
		add_action('wp_ajax_ksd_admin_bar_clicked', array($this, 'admin_bar_clicked'));

		//Add a shortcode for the public form
		add_shortcode('ksd_support_form', array($this, 'form_short_code'));
		add_shortcode('ksd_my_tickets', array($this, 'display_my_tickets'));

		//Add custom post types
		add_action('init', array('KSD_Custom_Post_Types', 'create_custom_post_types'));

		//Add custom ticket statuses
		add_action('init', array('KSD_Custom_Post_Types', 'custom_ticket_statuses'));

		//Add widget for the support form
		add_action('widgets_init', array($this, 'register_support_form_widget'));

		//Style public view of tickets
		add_filter('the_content', array($this, 'apply_templates'));

		//Redirect customers on login
		//add_filter( 'login_redirect', array ( $this, 'do_login_redirect' ), 10, 3 );

		//Add ticket cc
		add_filter('the_content', array($this, 'add_ticket_cc'));

		//Allow secret URL for tickets from guests
		add_action('template_redirect', array($this, 'allow_secret_urls'));

		//Remove 'Protected' from ticket titles
		add_filter('protected_title_format', array($this, 'remove_protected_prefix'));

		//Add content to WooCommerce/EDD Pages
		add_action('woocommerce_after_my_account', array($this, 'woo_edd_append_ticket_list'));
		add_action('edd_after_purchase_history', array($this, 'woo_edd_append_ticket_list'));
		add_action('edd_after_download_history', array($this, 'woo_edd_append_ticket_list'));
		add_action('edd_customer_after_tables', array($this, 'edd_customers_admin_append_ticket_table'));

		//Add 'Create ticket' to Woo Orders page
		add_filter('woocommerce_my_account_my_orders_columns', array($this, 'woo_orders_add_table_headers'));
		add_filter('woocommerce_my_account_my_orders_actions', array($this, 'woo_orders_add_ticket_button'), 10, 2);

		//Filter tickets archive page
		add_action('pre_get_posts', array($this, 'hide_ticket_archive_content'));

		//Only show a user his own attachments
		add_filter('ajax_query_attachments_args', array($this, 'filter_media'));

		//Add attachments to ticket content
		add_filter('ksd_the_ticket_content', array($this, 'append_attachments_to_content'), 10, 2);

		//Filter ksd-public-grecaptcha script tags
		add_filter('script_loader_tag', array($this, 'add_async_defer_attributes'), 10, 2);

		//Add support tickets tab to WooCommerce single product view
		add_filter('woocommerce_product_tabs', array($this, 'woo_support_tickets_tab'), 999);

		//Add admin bar menu
		add_action('admin_bar_menu', array($this, 'display_admin_bar_menu'), 999);

		add_filter('post_row_actions', array($this, 'modify_row_actions'), 10, 2);

		add_action('ksd_after_ticket_content', array($this, 'append_ticket_replies'));

	}

	/**
	 * add filters
	 *
	 * @return void
	 */
	public function add_filter_hooks() {
		// Add an action link pointing to the settings page.
		add_filter('plugin_action_links_' . plugin_basename(KSD_PLUGIN_FILE), array($this->ksd_admin, 'add_action_links'));

		//Set whether an incoming ticket is a reply
		add_filter('ksd_new_ticket_or_reply', array($this->ksd_admin, 'set_is_ticket_a_reply'));

		//Save ticket and its information
		add_filter('wp_insert_post_data', array($this->ksd_admin, 'save_ticket_info'), '99', 2);

		//Alter messages
		add_filter('post_updated_messages', array($this->ksd_admin, 'ticket_updated_messages'));

		//Get final status for ticket logged by importation
		add_action('ksd_new_ticket_imported', array($this->ksd_admin, 'new_ticket_imported', 10, 2));

		//Modify ticket deletion and un-deletion messages
		add_filter('bulk_post_updated_messages', array($this->ksd_admin, 'ticket_bulk_update_messages'), 10, 2);

		//Add custom views
		add_filter('views_edit-ksd_ticket', array($this->ksd_admin, 'ticket_views'));

		//Add headers to the tickets grid
		add_filter('manage_ksd_ticket_posts_columns', array($this->ksd_admin, 'add_tickets_headers'));
		//Populate the new columns
		add_action('manage_ksd_ticket_posts_custom_column', array($this->ksd_admin, 'populate_ticket_columns'), 10, 2);
		//Add sorting to the new columns
		add_filter('manage_edit-ksd_ticket_sortable_columns', array($this->ksd_admin, 'ticket_table_sortable_columns'));
		//Remove some default columns
		add_filter('manage_edit-ksd_ticket_columns', array($this->ksd_admin, 'ticket_table_remove_columns'));

		add_filter('request', array($this->ksd_admin, 'ticket_table_columns_orderby'));
		//Add ticket filters to the table grid drop-down
		add_filter('parse_query', array($this->ksd_admin, 'ticket_table_apply_filters'));
		//Display ticket status next to the ticket title
		add_filter('display_post_states', array($this->ksd_admin, 'display_ticket_statuses_next_to_title'));
		//Change 'Publish' button to 'Update'
		add_filter('gettext', array($this->ksd_admin, 'change_publish_button'), 10, 2);

		//Tag 'read' tickets in the ticket grid
		add_filter('post_class', array($this->ksd_admin, 'append_classes_to_ticket_grid'), 10, 3);

		//Contact form 7
		//add_filter( 'wpcf7_editor_panels', array( $this->ksd_admin, 'append_panel_to_wpcf7' ) );

	}

	/**
	 * ajax hooks
	 *
	 * @return void
	 */
	public function add_ajax_hooks() {
		//Handle AJAX calls
		add_action('wp_ajax_ksd_filter_tickets', array($this->ksd_admin, 'filter_tickets'));
		add_action('wp_ajax_ksd_filter_totals', array($this->ksd_admin, 'filter_totals'));
		add_action('wp_ajax_ksd_log_new_ticket', array($this->ksd_admin, 'log_new_ticket'));
		add_action('wp_ajax_ksd_delete_ticket', array($this->ksd_admin, 'delete_ticket'));
		add_action('wp_ajax_ksd_change_status', array($this->ksd_admin, 'change_status'));
		add_action('wp_ajax_ksd_change_severity', array($this->ksd_admin, 'change_severity'));
		add_action('wp_ajax_ksd_assign_to', array($this->ksd_admin, 'assign_to'));
		add_action('wp_ajax_ksd_reply_ticket', array($this->ksd_admin, 'reply_ticket'));
		add_action('wp_ajax_ksd_get_single_ticket', array($this->ksd_admin, 'get_single_ticket'));
		add_action('wp_ajax_ksd_dashboard_ticket_volume', array($this->ksd_admin, 'get_dashboard_ticket_volume'));
		add_action('wp_ajax_ksd_get_dashboard_summary_stats', array($this->ksd_admin, 'get_dashboard_summary_stats'));
		add_action('wp_ajax_ksd_update_settings', array($this->ksd_admin, 'update_settings'));
		add_action('wp_ajax_ksd_reset_settings', array($this->ksd_admin, 'reset_settings'));
		add_action('wp_ajax_ksd_update_private_note', array($this->ksd_admin, 'update_private_note'));
		add_action('wp_ajax_ksd_send_feedback', array($this->ksd_admin, 'send_feedback'));
		add_action('wp_ajax_ksd_support_tab_send_feedback', array($this->ksd_admin, 'send_support_tab_feedback'));
		add_action('wp_ajax_ksd_disable_tour_mode', array($this->ksd_admin, 'disable_tour_mode'));
		add_action('wp_ajax_ksd_get_notifications', array($this->ksd_admin, 'get_notifications'));
		add_action('wp_ajax_ksd_notify_new_ticket', array($this->ksd_admin, 'notify_new_ticket'));
		add_action('wp_ajax_ksd_bulk_change_status', array($this->ksd_admin, 'bulk_change_status'));
		add_action('wp_ajax_ksd_change_read_status', array($this->ksd_admin, 'change_read_status'));
		add_action('wp_ajax_ksd_modify_license', array($this->ksd_admin, 'modify_license_status'));
		add_action('wp_ajax_ksd_enable_usage_stats', array($this->ksd_admin, 'enable_usage_stats'));
		add_action('wp_ajax_ksd_update_ticket_info', array($this->ksd_admin, 'update_ticket_info'));
		add_action('wp_ajax_ksd_get_ticket_activity', array($this->ksd_admin, 'get_ticket_activity'));
		add_action('wp_ajax_ksd_migrate_to_v2', array($this->ksd_admin, 'migrate_to_v2'));
		add_action('wp_ajax_ksd_deletetables_v2', array($this->ksd_admin, 'deletetables_v2'));
		add_action('wp_ajax_ksd_notifications_user_feedback', array($this->ksd_admin, 'process_notification_feedback'));
		add_action('wp_ajax_ksd_notifications_disable', array($this->ksd_admin, 'disable_notifications'));
		add_action('wp_ajax_ksd_send_debug_email', array($this->ksd_admin, 'send_debug_email'));
		add_action('wp_ajax_ksd_reset_role_caps', array($this->ksd_admin, 'reset_role_caps'));
		add_action('wp_ajax_ksd_get_unread_ticket_count', array($this->ksd_admin, 'get_unread_ticket_count'));
		add_action('wp_ajax_ksd_hide_questionnaire', array($this->ksd_admin, 'hide_questionnaire'));

	}

	public function append_admin_feedback() {
		include_once KSD_PLUGIN_DIR . 'includes/admin/class-ksd-notifications.php';
		$ksd_notifications = new Notifications();
		$notification = $ksd_notifications->get_new_notification();
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

		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="ksd-debug.txt"');
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-ksd-debug.php';

		$ksd_debug = KSD_Debug::get_instance();

		echo wp_strip_all_tags($ksd_debug->retrieve_debug_info());
		if (!defined('PHPUNIT')) {
			die();
		}

	}

}
new Hook_Registry();
