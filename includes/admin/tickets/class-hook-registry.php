<?php
/**
 * Admin Tickets Hook Registry
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

namespace Kanzu\Ksd\Admin\Tickets;
use Kanzu\Ksd\Admin\Tickets\Attachments as ksd_attachments;
use Kanzu\Ksd\Admin\Tickets\Meta as ksd_meta;
use Kanzu\Ksd\Admin\Tickets\View as ksd_view;
use Kanzu\Ksd\Admin\Tickets\Ticket as ksd_ticket;
use Kanzu\Ksd\Admin\Tickets\Customer as ksd_customer;
use Kanzu\Ksd\Admin\Tickets\Reply as ksd_reply;
use Kanzu\Ksd\Admin\Tickets\Bulk_Ticket as ksd_bulk;
use Kanzu\Ksd\Admin\Tickets\Ticket_Log as ksd_ticket_log;
use Kanzu\Ksd\Admin\Tickets\Ticket_Activity as ksd_ticket_activity;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Email class functionality
 */
class Hooks_Registry {

	private $ksd_attachments;

	private $ksd_meta;

	private $ksd_view;

	private $ksd_ticket;

	private $ksd_customer;

	private $ksd_reply;

	private $ksd_bulk;

	private $ksd_ticket_log;

	private $ksd_ticket_activity;

	public function __construct() {

		$this->ksd_attachments = $attachments;
		$this->ksd_meta = $ksd_meta;
		$this->ksd_view = $ksd_view;
		$this->ksd_ticket = $ksd_ticket;
		$this->ksd_customer = $ksd_customer;
		$this->ksd_reply = $ksd_reply;
		$this->ksd_bulk = $ksd_bulk;
		$this->ksd_ticket_log = $ksd_ticket_log;
		$this->ksd_ticket_activity = $ksd_ticket_activity;
		$this->ksd_reply = $ksd_reply;

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

		//Add the attachments button
		add_action('media_buttons', array($this->attachments, 'add_attachments_button'), 15);

		//In 'Edit ticket' view, customize the screen
		add_action('add_meta_boxes', array($this->ksd_meta, 'edit_metaboxes'), 10, 2);

		//Add items to the submitdiv in 'edit ticket' view
		add_action('post_submitbox_misc_actions', array($this->ksd_view, 'edit_submitdiv'));

		//Add hash URLs to single ticket view
		add_action('edit_form_before_permalink', array($this->ksd_view, 'show_hash_url'));
		
		//Add ticket filters to the table grid drop-down
		add_action('restrict_manage_posts', array($this->ksd_view, 'ticket_table_filter_headers'));

		//On EDD download/WooCommerce publish
		add_action('publish_product', array($this->ksd_ticket, 'on_new_product'), 10, 2);
		add_action('publish_download', array($this->ksd_ticket, 'on_new_product'), 10, 2);

		//Add 'My tickets' button to 'My profile' page
		add_action('personal_options', array($this->ksd_customer, 'add_my_tickets_link'));

	}

	/**
	 * add filters
	 *
	 * @return void
	 */
	public function add_filter_hooks() {

		//Set whether an incoming ticket is a reply
		add_filter('ksd_new_ticket_or_reply', array($this->ksd_reply, 'set_is_ticket_a_reply'));

		//Save ticket and its information
		add_filter('wp_insert_post_data', array($this->ksd_meta, 'save_ticket_info'), '99', 2);

		//Alter messages
		add_filter('post_updated_messages', array($this->ksd_ticket, 'ticket_updated_messages'));

		//Modify ticket deletion and un-deletion messages
		add_filter('bulk_post_updated_messages', array($this->ksd_bulk, 'ticket_bulk_update_messages'), 10, 2);

		//Add custom views
		add_filter('views_edit-ksd_ticket', array($this->ksd_view, 'ticket_views'));

		//Add headers to the tickets grid
		add_filter('manage_ksd_ticket_posts_columns', array($this->ksd_ticket, 'add_tickets_headers'));
		//Populate the new columns
		add_action('manage_ksd_ticket_posts_custom_column', array($this->ksd_view, 'populate_ticket_columns'), 10, 2);
		//Add sorting to the new columns
		add_filter('manage_edit-ksd_ticket_sortable_columns', array($this->ksd_view, 'ticket_table_sortable_columns'));
		//Remove some default columns
		add_filter('manage_edit-ksd_ticket_columns', array($this->ksd_view, 'ticket_table_remove_columns'));

		add_filter('request', array($this->ksd_view, 'ticket_table_columns_orderby'));
		//Add ticket filters to the table grid drop-down
		add_filter('parse_query', array($this->ksd_view, 'ticket_table_apply_filters'));
		//Display ticket status next to the ticket title
		add_filter('display_post_states', array($this->ksd_view, 'display_ticket_statuses_next_to_title'));

		//Tag 'read' tickets in the ticket grid
		add_filter('post_class', array($this->ksd_ticket, 'append_classes_to_ticket_grid'), 10, 3);

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
		add_action('wp_ajax_ksd_filter_tickets', array($this->ksd_view, 'filter_tickets'));
		add_action('wp_ajax_ksd_filter_totals', array($this->ksd_ticket, 'filter_totals'));
		add_action('wp_ajax_ksd_log_new_ticket', array($this->ksd_ticket_log, 'log_new_ticket'));
		add_action('wp_ajax_ksd_delete_ticket', array($this->ksd_ticket, 'delete_ticket'));
		add_action('wp_ajax_ksd_change_status', array($this->ksd_ticket_activity, 'change_status'));
		add_action('wp_ajax_ksd_change_severity', array($this->ksd_ticket, 'change_severity'));
		add_action('wp_ajax_ksd_assign_to', array($this->ksd_ticket, 'assign_to'));
		add_action('wp_ajax_ksd_reply_ticket', array($this->ksd_reply, 'reply_ticket'));
		add_action('wp_ajax_ksd_get_single_ticket', array($this->ksd_ticket_activity, 'get_single_ticket'));
		add_action('wp_ajax_ksd_dashboard_ticket_volume', array($this->ksd_ticket, 'get_dashboard_ticket_volume'));
		add_action('wp_ajax_ksd_update_private_note', array($this->ksd_ticket, 'update_private_note'));
		add_action('wp_ajax_ksd_change_read_status', array($this->ksd_ticket_activity, 'change_read_status'));
		add_action('wp_ajax_ksd_update_ticket_info', array($this->ksd_meta, 'update_ticket_info'));
		add_action('wp_ajax_ksd_get_ticket_activity', array($this->ksd_ticket_activity, 'get_ticket_activity'));
		add_action('wp_ajax_ksd_get_unread_ticket_count', array($this->ksd_ticket, 'get_unread_ticket_count'));

	}


}
