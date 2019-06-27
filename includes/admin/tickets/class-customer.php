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

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ticket Reply class functionality
 */
class Customer {

	/**
	 * Create a new customer in wp_users
	 * @param Object $customer The customer object
	 */
	public function create_new_customer($customer) {
		$username = sanitize_user(preg_replace('/@(.)+/', '', $customer->user_email)); //Derive a username from the emailID
		//Ensure username is unique. Adapted from WooCommerce.
		$append = 1;
		$new_username = $username;

		while (username_exists($username)) {
			$username = $new_username . $append;
			$append++;
		}
		$password = wp_generate_password(); //Generate a random password.
		//First name.
		$first_name = empty($customer->first_name) ? $username : $customer->first_name;

		$userdata = array(
			'user_login' => $username,
			'user_pass' => $password,
			'user_email' => $customer->user_email,
			'display_name' => empty($customer->last_name) ? $first_name : $first_name . ' ' . $customer->last_name,
			'first_name' => $first_name,
			'role' => 'ksd_customer',
		);
		if (!empty($customer->last_name)) {
//Add the username if it was provided.
			$userdata['last_name'] = $customer->last_name;
		}
		$user_id = wp_insert_user($userdata);
		if (!is_wp_error($user_id)) {
			return $user_id;
		}
		return false;
	}

	/**
	 * Add a 'My Tickets' link to the Profile page
	 * that's displayed when a ksd_customer logs in
	 */
	public function add_my_tickets_link() {
		global $current_user;
		if (isset($current_user->roles) && is_array($current_user->roles) && in_array('ksd_customer', $current_user->roles)) {
			$current_settings = Kanzu_Support_Desk::get_settings(); //Get current settings
			$link_label = __('View My Tickets', 'kanzu-support-desk');
			echo '<a href="' . get_permalink($current_settings['page_my_tickets']) . '" class="ksd-customer-ticket-link button button-primary">' . $link_label . '</a>';
		}
	}

	/**
	 * Get a customer's tickets
	 * @param int $customer_id
	 * @param array $query_params Extra criteria to use to filter the customer's ticket list
	 * @since 2.0.0
	 */
	public function get_customer_tickets($customer_id, $query_params = array()) {
		$my_ticket_args = array();
		$this->do_admin_includes();

		$my_ticket_args['post_type'] = 'ksd_ticket';
		$my_ticket_args['author'] = $customer_id;
		$my_ticket_args['post_status'] = array('new', 'open', 'pending', 'resolved');
		$my_ticket_args['posts_per_page'] = -1;
		$my_ticket_args['offset'] = 0;

		if (!empty($query_params)) {
			$my_ticket_args = array_merge($my_ticket_args, $query_params);
		}
		return apply_filters('ksd_my_tickets_array', get_posts(apply_filters('ksd_my_tickets_args', $my_ticket_args)));
	}

}