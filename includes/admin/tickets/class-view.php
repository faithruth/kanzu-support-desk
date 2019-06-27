<?php
/**
 * Admin side Kanzu Support Desk Ticket Reply
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

namespace Kanzu\Ksd\Admin\Tickets;
use Kanzu\Ksd\Admin\Tickets\Meta as ksd_meta;
use Kanzu\Ksd\Admin\Admin as ksd_admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ticket Reply class functionality
 */
class View {

	private $ksd_meta;

	private $ksd_admin;

	public function __construct()
    {
		$this->ksd_meta = $ksd_meta;
		$this->ksd_admin = $ksd_Admin;
    }

	/**
	 * Define which ticket columns to add sorting to
	 * @return string
	 * @since 2.0.0
	 */
	public function ticket_table_sortable_columns($columns) {
		$columns['status'] = 'status';
		$columns['assigned_to'] = 'assigned_to';
		$columns['severity'] = 'severity';
		$columns['customer'] = 'customer';
		return $columns;
	}

	/**
	 * Remove some default columns. In particular, we remove the tags column
	 * @since 2.0.0
	 */
	public function ticket_table_remove_columns($columns) {
		unset($columns['tags']); //Remove tags
		return $columns;
	}

	/**
	 * Order the ticket table columns by a particular field
	 * @since 2.0.0
	 */
	public function ticket_table_columns_orderby($vars) {
		if (isset($vars['orderby'])) {
			switch ($vars['orderby']) {
			case 'severity':
				$vars = array_merge($vars, array(
					'meta_key' => '_ksd_tkt_info_severity',
					'orderby' => 'meta_value',
				));
				break;
			case 'assigned_to':
				$vars = array_merge($vars, array(
					'meta_key' => '_ksd_tkt_info_assigned_to',
					'orderby' => 'meta_value_num',
				));
				break;
			case 'status':
				$vars = array_merge($vars, array(
					'orderby' => 'post_status',
				));
				break;
			case 'customer':
				$vars = array_merge($vars, array(
					'orderby' => 'post_author',
				));
				break;
			}
		}
		return $vars;
	}

	/**
	 * Add filters to the WP ticket grid
	 * @since 2.0.0
	 */
	public function ticket_table_filter_headers() {
		global $wpdb, $current_screen;
		if ($current_screen->post_type == 'ksd_ticket') {
			$ksd_statuses = array(
				'new' => __('New', 'kanzu-support-desk'),
				'open' => __('Open', 'kanzu-support-desk'),
				'pending' => __('Pending', 'kanzu-support-desk'),
				'resolved' => __('Resolved', 'kanzu-support-desk'),
			);
			$filter = '';
			$filter .= '<select name="ksd_statuses_filter" id="filter-by-status">';
			$filter .= '<option value="0">' . __('All statuses', 'kanzu-support-desk') . '</option>';
			$filter_status_by = (isset($_GET['ksd_statuses_filter']) ? sanitize_key($_GET['ksd_statuses_filter']) : 0);
			foreach ($ksd_statuses as $value => $name) {
				$filter .= '<option ' . selected($filter_status_by, $value, false) . ' value="' . $value . '">' . $name . '</option>';
			}
			$filter .= '</select>';
			$ksd_severities = $this->get_severity_list();
			$filter .= '<select name="ksd_severities_filter" id="filter-by-severity">';
			$filter .= '<option value="0">' . __('All severities', 'kanzu-support-desk') . '</option>';
			$filter_severity_by = (isset($_GET['ksd_severities_filter']) ? sanitize_key($_GET['ksd_severities_filter']) : 0);
			foreach ($ksd_severities as $value => $name) {
				$filter .= '<option ' . selected($filter_severity_by, $value, false) . ' value="' . $value . '">' . $name . '</option>';
			}
			$filter .= '</select>';
			$filter .= '<select name="ksd_unread_filter" id="filter-by-read-unread">';
			$filter .= '<option value="0">' . _x('All states', 'Ticket read and unread states', 'kanzu-support-desk') . '</option>';
			$filter .= '<option value="unread">' . _x('Unread', 'Ticket state', 'kanzu-support-desk') . '</option>';
			$filter .= '<option value="read">' . _x('Read', 'Ticket state', 'kanzu-support-desk') . '</option>';
			$filter .= '</select>';
			echo $filter;
		}
	}

	/**
	 * Apply filters to the ticket grid
	 * Called when a view is selected in the ticket grid and when the user filters a view
	 * @since 2.0.0
	 */
	public function ticket_table_apply_filters($query) {
		if (is_admin() && isset($query->query['post_type']) && 'ksd_ticket' == $query->query['post_type']) {

			$qv = &$query->query_vars;
			//Change the ticket order
			$qv['orderby'] = 'modified';
			$qv['meta_query'] = array();

			if (!empty($_GET['ksd_severities_filter'])) {
				$qv['meta_query'][] = array(
					'key' => '_ksd_tkt_info_severity',
					'value' => $_GET['ksd_severities_filter'],
					'compare' => '=',
					'type' => 'CHAR',
				);
			}
			if (!empty($_GET['ksd_statuses_filter'])) {
				$qv['post_status'] = sanitize_key($_GET['ksd_statuses_filter']);
			}
			if (!empty($_GET['ksd_unread_filter'])) {
				$qv['meta_query'][] = $this->ksd_meta->get_ticket_state_meta_query($_GET['ksd_unread_filter']);
			}
			if (!empty($_GET['ksd_view'])) {
				switch (sanitize_key($_GET['ksd_view'])) {
				case 'mine':
					$qv['meta_query'][] = array(
						'key' => '_ksd_tkt_info_assigned_to',
						'value' => get_current_user_id(),
						'compare' => '=',
						'type' => 'NUMERIC',
					);
					$qv['post_status'] = array('new', 'open', 'draft', 'pending'); //Don't show resolved tickets
					break;
				case 'unassigned':
					$qv['meta_query'][] = array(
						'key' => '_ksd_tkt_info_assigned_to',
						'value' => 0,
						'compare' => '=',
						'type' => 'NUMERIC',
					);
					$qv['post_status'] = array('new', 'open', 'draft', 'pending'); //Don't show resolved tickets
					break;
				case 'recently_updated':
					break;
				case 'recently_resolved':
					break;
				}
			}
		}
	}

	/**
	 * Filters tickets based on the view chosen
	 * @param string $filter The filter [Everything after the WHERE clause] using placeholders %s and %d
	 * @param Array $value_parameters The values to replace the $filter placeholders
	 */
	public function filter_ticket_view($filter = "", $value_parameters = array()) {
		$tickets = new KSD_Tickets_Controller();
		//$tickets_raw = $tickets->get_tickets( $filter,$value_parameters );
		$tickets_raw = $tickets->get_tickets_n_reply_cnt($filter, $value_parameters);
		//Process the tickets for viewing on the view. Replace the username and the time with cleaner versions.
		foreach ($tickets_raw as $ksd_ticket) {
			$this->format_ticket_for_viewing($ksd_ticket);
		}
		return $tickets_raw;
	}

	/**
	 * Replace a ticket's logged_by field with the nicename of the user who logged it
	 * Replace the tkt_time_logged with a date better-suited for viewing
	 * NB: Because we use {@link KSD_Users_Controller}, call this function after {@link do_admin_includes} has been called.
	 * @param Object $ticket The ticket to modify
	 * @param boolean $single_ticket_view Whether we are in single ticket view or not
	 */
	private function format_ticket_for_viewing($ticket, $single_ticket_view = false) {
		//If the ticket was logged by an agent from the admin end, then the username is available in wp_users. Otherwise, we retrive the name.
		//from the KSD customers table.
		// $tmp_tkt_assigned_by = ( 'admin-form' === $ticket->tkt_channel ? $users->get_user($ticket->tkt_assigned_by)->display_name : $CC->get_customer($ticket->tkt_assigned_by)->cust_firstname );
		$tkt_user_data = get_userdata($ticket->tkt_cust_id);
		$tmp_tkt_cust_id = $tkt_user_data->display_name;
		if ($single_ticket_view) {
			$tmp_tkt_cust_id .= ' <' . $tkt_user_data->user_email . '>';
			$ticket->tkt_message = $this->ksd_admin->format_message_content_for_viewing($ticket->tkt_message);
		}
		//Replace the tkt_assigned_by name with a prettier one.
		$ticket->tkt_cust_id = str_replace($ticket->tkt_cust_id, $tmp_tkt_cust_id, $ticket->tkt_cust_id);
		//Replace the date.
		$ticket->tkt_time_logged = date('M d', strtotime($ticket->tkt_time_logged));

		return $ticket;
	}

	/**
	 * Add custom views to the admin post grid
	 * @param Array $views The default admin post grid views
	 * @since 2.0.0
	 */
	public function ticket_views($views) {
		unset($views['publish']); //Remove the publish view
		$views['mine'] = "<a href='edit.php?post_type=ksd_ticket&amp;ksd_view=mine'>" . __('Mine', 'kanzu-support-desk') . "</a>";
		$views['unassigned'] = "<a href='edit.php?post_type=ksd_ticket&amp;ksd_view=unassigned'>" . __('Unassigned', 'kanzu-support-desk') . "</a>";
		return $views;
	}

	/**
	 * Filter tickets in the 'tickets' view
	 */
	public function filter_tickets() {
		if (!wp_verify_nonce($_POST['ksd_admin_nonce'], 'ksd-admin-nonce')) {
			die(__('Busted!', 'kanzu-support-desk'));
		}

		try {
			$this->do_admin_includes();
			$value_parameters = array();
			switch ($_POST['ksd_view']):
		case '#tickets-tab-2': //'All Tickets'
			$filter = " tkt_status != 'RESOLVED'";
			break;
		case '#tickets-tab-3': //'Unassigned Tickets'
			$filter = " tkt_assigned_to IS NULL ";
			break;
		case '#tickets-tab-4': //'Recently Updated' i.e. Updated in the last hour.
			$settings = Kanzu_Support_Desk::get_settings();
			$value_parameters[] = $settings['recency_definition'];
			$filter = " tkt_time_updated < DATE_SUB(NOW(), INTERVAL %d HOUR)";
			break;
		case '#tickets-tab-5': //'Recently Resolved'.i.e Resolved in the last hour.
			$settings = Kanzu_Support_Desk::get_settings();
			$value_parameters[] = $settings['recency_definition'];
			$filter = " tkt_time_updated < DATE_SUB(NOW(), INTERVAL %d HOUR) AND tkt_status = 'RESOLVED'";
			break;
		case '#tickets-tab-6': //'Resolved'
			$filter = " tkt_status = 'RESOLVED'";
			break;
		default: //'My Unresolved'
			$filter = " tkt_assigned_to = " . get_current_user_id() . " AND tkt_status != 'RESOLVED'";
			endswitch;

			$offset = sanitize_text_field($_POST['offset']);
			$limit = sanitize_text_field($_POST['limit']);
			$search = sanitize_text_field($_POST['search']);

			//search.
			if ($search != "") {
				$filter .= " AND UPPER(tkt_subject) LIKE UPPER(%s) ";
				$value_parameters[] = '%' . $search . '%';
			}

			//order.
			$filter .= " ORDER BY tkt_time_updated DESC "; //@since 1.6.2 sort by tkt_time_updated

			//limit.
			$count_filter = $filter; //Query without limit to get the total number of rows.
			$count_value_parameters = $value_parameters;
			$filter .= " LIMIT %d , %d ";
			$value_parameters[] = $offset; //The order of items in $value_parameters is very important.
			$value_parameters[] = $limit; //The order of placeholders should correspond to the order of entries in the array.

			//Results count.
			$tickets = new KSD_Tickets_Controller();
			$count = $tickets->get_pre_limit_count($count_filter, $count_value_parameters);
			$raw_tickets = $this->filter_ticket_view($filter, $value_parameters);

			if (empty($raw_tickets)) {
				$response = __('Nothing to see here. Great work!', 'kanzu-support-desk');
			} else {

				$response = array(
					0 => $raw_tickets,
					1 => $count,
				);

			}

			echo json_encode($response);
			die(); // IMPORTANT: don't leave this out.

		} catch (Exception $e) {
			$response = array(
				'error' => array('message' => $e->getMessage(), 'code' => $e->getCode()),
			);
			echo json_encode($response);
			die(); // IMPORTANT: don't leave this out.
		}
	}
	/**
	 * While viewing a single ticket that has a hash URL,
	 * display it in place of the permalink
	 *
	 * @param Object $post
	 */
	public function show_hash_url($post) {
		if ($post->post_type !== 'ksd_ticket') {
			return;
		}
		$hash_url = get_post_meta($post->ID, '_ksd_tkt_info_hash_url', true);
		if (empty($hash_url)) {
			return;
		}
		include_once KSD_PLUGIN_DIR . "templates/admin/metaboxes/hash-url.php";
	}

	/**
	 * Populate our custom ticket columns
	 * @param string $column_name
	 * @param int $post_id
	 * @since 2.0.0
	 */
	public function populate_ticket_columns($column_name, $post_id) {
		if ($column_name == 'severity') {
			$ticket_severity = get_post_meta($post_id, '_ksd_tkt_info_severity', true);
			echo '' == $ticket_severity ? $this->get_ticket_severity_label('low') : $this->get_ticket_severity_label($ticket_severity);
		}
		if ($column_name == 'assigned_to') {
			$ticket_assignee_id = get_post_meta($post_id, '_ksd_tkt_info_assigned_to', true);
			echo $this->get_ticket_assignee_display_name($ticket_assignee_id);
		}
		if ($column_name == 'status') {
			global $post;
			echo "<span class='{$post->post_status}'>" . $this->get_ticket_status_label($post->post_status) . "</span>";
		}
		if ($column_name == 'customer') {
			global $post;
			echo get_userdata($post->post_author)->display_name;
		}
		if ($column_name == 'replies') {
			global $wpdb;
			$reply_count
			= $wpdb->get_var(" SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE "
				. " post_type = 'ksd_reply' AND post_parent = '${post_id}' "
			);
			echo $reply_count;
		}
	}

	/**
	 * Edit the submitddiv box displayed in the sidebar of tickets
	 * @since 2.0.0
	 * @global type $post
	 */
	public function edit_submitdiv() {
		global $post;
		if ($post->post_type !== 'ksd_ticket') {
			return;
		}
		include_once KSD_PLUGIN_DIR . "templates/admin/metaboxes/html-ksd-ticket-info.php";
	}

	/**
	 * Get a ticket assignee display name used in the 'All Tickets' list
	 *
	 * @param int $ticket_assignee_id
	 * @return string The ticket assignee name
	 */
	private function get_ticket_assignee_display_name($ticket_assignee_id) {
		if ('' == $ticket_assignee_id || 0 == $ticket_assignee_id) {
			return __('No one', 'kanzu-support-desk');
		} else {
			$ticket_assignee = get_userdata($ticket_assignee_id);
			if (false !== $ticket_assignee) {
				return $ticket_assignee->display_name;
			}
			return __('No one', 'kanzu-support-desk');
		}
	}

	/**
	 * Get ticket sererity label
	 *
	 * @param string $ticket_severity ticket severity
	 */
	public function get_ticket_severity_label($ticket_severity) {
		$label = __('Unknown', 'kanzu-support-desk');

		switch ($ticket_severity) {
		case 'low':
			$label = __('Low', 'kanzu-support-desk');
			break;
		case 'medium':
			$label = __('Medium', 'kanzu-support-desk');
			break;
		case 'high':
			$label = __('High', 'kanzu-support-desk');
			break;
		case 'urgent':
			$label = __('Urgent', 'kanzu-support-desk');
			break;
		}

		return $label;
	}

	/**
	 * Display ticket statuses next to the title in the ticket grid
	 * We use this to remove 'draft','pending' and 'Password protected' ticket states
	 * that are automatically added to tickets by WP
	 * @global Object $post
	 * @param type $states
	 * @return type
	 * @since 2.0.0
	 */
	public function display_ticket_statuses_next_to_title($states) {
		global $post;
		if ('ksd_ticket' === $post->post_type) {
			if ($post->post_status == 'pending' || $post->post_status == 'draft' || !empty($post->post_password)) {
				return array();
			}
		}

		return $states;
	}

}