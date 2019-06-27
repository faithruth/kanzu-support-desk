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
use Kanzu\Ksd\Admin\Tickets\Customer as ksd_customer;
use Kanzu\Ksd\Admin\Admin as ksd_admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ticket Reply class functionality
 */
class Reply {

	private $ksd_customer;

	private $ksd_admin;

	public function __construct()
    {
		$this->ksd_customer = $ksd_customer;
		
		$this->ksd_admin = $ksd_Admin;
    }
	/**
	 * Add a reply to a single ticket.
	 *
	 * @param Array $ticket_reply_array The ticket reply Array. This exists wnen this function is called by an add-on
	 * Note that add-ons have to provide tkt_id too. It's retrieved in the check before this function is called
	 *
	 */

	public function reply_ticket($ticket_reply_array = null) {
		//In add-on mode, this function was called by an add-on.
		$add_on_mode = (is_array($ticket_reply_array) ? true : false);

		if (!$add_on_mode) {
//Check for NONCE if not in add-on mode.
			if (isset($_POST['ksd_admin_nonce']) || isset($_POST['ksd_new_reply_nonce'])) {
				if (isset($_POST['ksd_admin_nonce']) && !wp_verify_nonce($_POST['ksd_admin_nonce'], 'ksd-admin-nonce')) {
					die(__('Busted!', 'kanzu-support-desk'));
				}
				//Front end reply nonce check.
				if (isset($_POST['ksd_new_reply_nonce']) && !wp_verify_nonce($_POST['ksd_new_reply_nonce'], 'ksd-add-new-reply')) {
					die(__('Busted!', 'kanzu-support-desk'));
				}
			} else {
				die(__('Busted!', 'kanzu-support-desk'));
			}
		}

		$this->do_admin_includes();
		try {
			$new_reply = array();
			//If this was called by an add-on, populate the $_POST array.
			if ($add_on_mode) {
				$_POST = $ticket_reply_array;
				if (isset($_POST['ksd_rep_created_by'])) {
					$new_reply['post_author'] = $_POST['ksd_rep_created_by'];
				}
				if (isset($_POST['ksd_cust_email'])) {
					$customer_email = sanitize_email($_POST['ksd_cust_email']);
					$customer_details = get_user_by('email', $customer_email);
					if ($customer_details) {
						$new_reply['post_author'] = $customer_details->ID;
					} else {
						$new_customer = new stdClass();
						$new_customer->user_email = $customer_email;
						$new_reply['post_author'] = $this->ksd_customer->create_new_customer($new_customer);
					}
				}
			} else {
				$new_reply['post_author'] = get_current_user_id();
			}

			$parent_ticket_ID = sanitize_text_field($_POST['tkt_id']);
			$new_reply['post_title'] = wp_strip_all_tags($_POST['ksd_reply_title']);
			$new_reply['post_parent'] = $parent_ticket_ID;
			//Add KSD reply defaults.
			$new_reply['post_type'] = 'ksd_reply';
			$new_reply['post_status'] = 'publish';
			$new_reply['comment_status'] = 'closed ';

			$cc = null;
			if (isset($_POST['ksd_tkt_cc']) && $_POST['ksd_tkt_cc'] != __('CC', 'kanzu-support-desk')) {
				$new_reply['rep_cc'] = sanitize_text_field($_POST['ksd_tkt_cc']);
				$cc = $_POST['ksd_tkt_cc'];
			}

			if (isset($_POST['ksd_rep_date_created'])) {
//Set by add-ons
				$new_reply['post_date'] = $this->validate_post_date(sanitize_text_field($_POST['ksd_rep_date_created']));
			}

			$new_reply['post_content'] = wp_kses_post(stripslashes($_POST['ksd_ticket_reply']));
			if (strlen($new_reply['post_content']) < 2 && !$add_on_mode) {
//If the response sent it too short
				throw new Exception(__("Error | Reply too short", 'kanzu-support-desk'), -1);
			}

			//Add the reply to the replies table.
			$new_reply_id = wp_insert_post($new_reply);

			if (!$add_on_mode) // Allow addons not in add_on_mode to do something.
			{
				do_action('ksd_new_reply_created', $parent_ticket_ID, $new_reply_id);
			}

			if (null !== $cc) {
				add_post_meta($new_reply_id, '_ksd_tkt_info_cc', $cc, true);
			}

			//Mark ticket as unread @TODO Update this.
			$this->mark_ticket_reply_unread($parent_ticket_ID);

			//Update the main ticket's tkt_time_updated field.
			$parent_ticket = get_post($parent_ticket_ID);
			$parent_ticket->post_modified = current_time('mysql');
			wp_update_post($parent_ticket);

			//Do notifications.
			if ($parent_ticket->post_author == $new_reply['post_author']) {
//This is a reply from the customer. Notify the assignee
				$notify_user = $this->get_ticket_assignee_to_notify($parent_ticket_ID);
			} else {
//This is a reply from an agent. Notify the customer.
				$notify_user = get_userdata($parent_ticket->post_author);
			}

			$parent_ticket_channel = get_post_meta($parent_ticket_id, '_ksd_tkt_info_channel', true);

			/**
			 * @filter `ksd_reply_logged_notfxn_email_message_{$parent_ticket_channel}` Right after a ticket reply is logged, this is applied to the message content of the email notification to be sent to the customer/agent. $parent_ticket_channel is the channel used to log the parent ticket
			 *
			 * @param string $new_reply_content The reply to be sent
			 * @param int $parent_ticket_ID Ticket ID of the parent ticket
			 */
			$ticket_reply_message = apply_filters('ksd_reply_logged_notfxn_email_message_' . $parent_ticket_channel, $new_reply['post_content'], $parent_ticket_ID);
			$ticket_reply_message .= Kanzu_Support_Desk::output_ksd_signature($parent_ticket_ID);

			/**
			 * @filter `ksd_reply_logged_notfxn_email_subject_{$parent_ticket_channel}` Right after a ticket reply is logged, this is applied to the message subject of the email notification to be sent to the customer/agent. $parent_ticket_channel is the channel used to log the parent ticket
			 *
			 * @param string $new_reply_subject The reply to be sent
			 * @param int $parent_ticket_ID Ticket ID of the parent ticket
			 */
			$ticket_reply_subject = apply_filters('ksd_reply_logged_notfxn_email_subject', $parent_ticket->post_title, $parent_ticket_ID);

			//Like all good replies, prepend a Re:
			$ticket_reply_subject = 'Re:' . $ticket_reply_subject;

			$addon_tkt_id = (isset($_POST['ksd_addon_tkt_id']) ? $_POST['ksd_addon_tkt_id'] : 0);

			/**
			 * @filter `ksd_reply_logged_notfxn_email_headers_{$parent_ticket_channel}` Right after a ticket reply is logged, this is applied to the message headers of the email notification to be sent to the customer/agent. $parent_ticket_channel is the channel used to log the parent ticket
			 *
			 * @param array $ticket_reply_headers Headers of the email being sent
			 * @param WP_Post Object $parent_ticket The parent ticket
			 * @param int $addon_tkt_id The ID received from the add-on that logged this reply if this reply came from an add-on. Otherwise, it'll be 0
			 */
			$ticket_reply_headers = apply_filters('ksd_reply_logged_notfxn_email_headers_' . $parent_ticket_channel, $ticket_reply_headers, $parent_ticket, $addon_tkt_id);

			$this->send_email($notify_user->user_email, $ticket_reply_message, $ticket_reply_subject, $cc, array(), 0, $ticket_reply_headers);

			if ($add_on_mode && !isset($_POST['ksd_public_reply_form'])) {
//ksd_public_reply_form is set for replies from the public reply form

				/**
				 * @filter `ksd_new_reply_logged` Run when a new reply to a ticket is created.
				 *
				 * @param int $addon_tkt_id The ID specified by the add-on that logged this ticket
				 * @param int $new_reply_id The ID of the newly-created reply
				 */
				do_action('ksd_new_reply_logged', $addon_tkt_id, $new_reply_id);

				/**
				 * @filter 'ksd_new_reply_logged_'.{$parent_ticket_channel} Run when a new reply is logged.
				 * $parent_ticket_channel is the channel used to log the parent ticket. This allows particular
				 * addons to run custom actions when a new channel-specific reply is logged and not on every reply
				 *
				 * @since 2.3.4
				 *
				 * @param int $addon_tkt_id The ID specified by the add-on that logged this ticket
				 * @param int $new_reply_id The ID of the newly-created reply
				 */
				do_action('ksd_new_reply_logged_' . $parent_ticket_channel, $addon_tkt_id, $new_reply_id);
				return; //End the party if this came from an add-on. All an add-on needs if for the reply to be logged.
			}

			if ($new_reply_id > 0) {
				//Add 'post_author' to the response.
				$new_reply['post_author'] = get_userdata($new_reply['post_author'])->display_name;
				echo json_encode($new_reply);
			} else {
				throw new Exception(__("Error", 'kanzu-support-desk'), -1);
			}
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
	 * Validate the post date before saving a post. This is usually set by add-ons
	 * Adapted from wp-includes/post.php
	 *
	 * @param Date $post_date
	 * @return Date in form 0000-00-00 00:00:00
	 * @since 2.0.0
	 */
	private function validate_post_date($post_date) {
		if (empty($post_date) || '0000-00-00 00:00:00' == $post_date) {
			return current_time('mysql');
		}
		// validate the date
		$mm = substr($post_date, 5, 2);
		$jj = substr($post_date, 8, 2);
		$aa = substr($post_date, 0, 4);
		$valid_date = wp_checkdate($mm, $jj, $aa, $post_date);
		if (!$valid_date) {
			return current_time('mysql');
		}
		return $valid_date;
	}
	/**
	 * Log new ticket reply
	 *
	 * @sine 2.2.12
	 *
	 * @param type $new_ticket The reply to the ticket
	 */
	public function do_reply_ticket($new_ticket) {
		$this->do_admin_includes();

		$customer_details = get_user_by('email', $new_ticket['ksd_cust_email']);
		$new_ticket['ksd_tkt_cust_id'] = $customer_details->ID;

		if (false === $customer_details) {
			//Customer does not exist
			$cust_email = sanitize_email($new_ticket_raw['ksd_cust_email']); //Get the provided email address
			//Check that it is a valid email address. Don't do this check in add-on mode
			if (!is_email($cust_email)) {
				throw new Exception(__('Error | Invalid email address specified', 'kanzu-support-desk'), -1);
			}
			$new_customer = new stdClass();
			$new_customer->user_email = $cust_email;
			//Check whether one or more than one customer name was provided
			if (false === strpos(trim(sanitize_text_field($new_ticket_raw['ksd_cust_fullname'])), ' ')) {
//Only one customer name was provided
				$new_customer->first_name = sanitize_text_field($new_ticket_raw['ksd_cust_fullname']);
			} else {
				preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field($new_ticket_raw['ksd_cust_fullname']), $new_customer_fullname);
				$new_customer->first_name = $new_customer_fullname[1];
				$new_customer->last_name = $new_customer_fullname[2]; //We store everything besides the first name in the last name field
			}
			//Add the customer to the user table and get the customer ID
			$new_ticket['post_author'] = $this->ksd_customer->create_new_customer($new_customer);
		}

		$new_ticket['ksd_tkt_cust_id'] = $customer_details->ID;

		$ticket_reply['tkt_id'] = $new_ticket['ksd_tkt_id'];
		$ticket_reply['ksd_reply_title'] = $new_ticket['ksd_tkt_subject'];
		$ticket_reply['ksd_ticket_reply'] = $new_ticket['ksd_tkt_message'];
		$ticket_reply['ksd_rep_created_by'] = $new_ticket['ksd_tkt_cust_id'];
		$ticket_reply['ksd_rep_date_created'] = $new_ticket['ksd_tkt_time_logged'];

		//Add addon ticket ID.
		if (isset($new_ticket['ksd_addon_tkt_id'])) {
			$ticket_reply['ksd_addon_tkt_id'] = $new_ticket['ksd_addon_tkt_id'];
		}

		$this->reply_ticket($ticket_reply);
	}

	/**
	 * Get ticket's replies and private notes
	 * @param int $tkt_id The ticket ID
	 * @param boolean $get_notes Whether to get private notes or not
	 * @since 2.0.0
	 */
	public function do_get_ticket_replies_and_notes($tkt_id, $get_notes = true) {
		$args = array('post_type' => 'ksd_reply', 'post_parent' => $tkt_id, 'order' => 'ASC', 'posts_per_page' => -1, 'offset' => 0);

		if ($get_notes || $this->current_user_can_view_private_notes()) {
			$args['post_type'] = array('ksd_reply', 'ksd_private_note');
			$args['post_status'] = array('private', 'publish');
		}

		$replies = get_posts($args); //@TODO Re-test this. Might need to change it to new WP_Query
		//Replace the reply author ID with the display name and get the reply's attachments.
		foreach ($replies as $reply) {
			$reply->post_author_display_name = get_userdata($reply->post_author)->display_name;
			//@TODO Get the reply's attachments.

			$reply->post_author_avatar = get_avatar($reply->post_author, 46);

			//Change the time to something more human-readable.
			$reply->post_date = date_i18n(__('g:i A d M Y'), strtotime($reply->post_date));

			//Format the message for viewing.
			$reply->post_content = $this->ksd_admin->format_message_content_for_viewing($reply->post_content);

			//Add reply's CC
			$reply->ksd_cc = get_post_meta($reply->ID, '_ksd_tkt_info_cc', true);

			//Add reply's Facebook Comment ID.
			$reply_comment_id = get_post_meta($reply->ID, '_ksd_rep_info_comment_id', true);
			if (!empty($reply_comment_id)) {
				$reply->comment_id = $reply_comment_id;
			}
		}
		return $replies;
	}

	private function current_user_can_view_private_notes() {
		global $current_user;
		if (isset($current_user->roles) && is_array($current_user->roles) && (in_array('ksd_agent', $current_user->roles) || in_array('ksd_supervisor', $current_user->roles) || in_array('administrator', $current_user->roles))) {

			return true;
		}
		return false;

	}

	/**
	 * Remove all 'Ticket read' meta values
	 * @param int $parent_ticket_id
	 */
	private function mark_ticket_reply_unread($parent_ticket_id) {
		$post_meta = get_post_meta($parent_ticket_id);
		foreach ($post_meta as $meta_key => $meta_value) {
			if (false !== strpos($meta_key, '_ksd_tkt_info_is_read_by_')) {
				delete_post_meta($parent_ticket_id, $meta_key);
			}
		}
	}

	/**
	 * Decide whether an incoming ticket is a reply
	 *
	 * @param array $new_ticket The incoming ticket
	 *
	 */
	public function set_is_ticket_a_reply($new_ticket) {
		if (false !== strpos($new_ticket['ksd_tkt_subject'], '~')) {
			$ticket_subject_array = explode('~', $new_ticket['ksd_tkt_subject']);
			$new_ticket['tkt_id'] = end($ticket_subject_array);
			$new_ticket['is_reply'] = true;
		}
		return $new_ticket;
	}

	/**
	 * Send agent replies to customers
	 * @param int $customer_ID The customer's ID
	 * @param string $message The message to send to the customer
	 * @param string $subject The message subject
	 * @return N/A
	 */
	private function send_agent_reply($customer_ID, $message, $subject) {
		$cust_info = get_userdata($customer_ID);
		$this->send_email($cust_info->user_email, $message, 'Re: ' . $subject);
	}

	/**
	 * Get the ticket assignee of a new reply or ticket
	 * If no assignee exists, return the primary admin
	 * @return Object User
	 * @since 2.0.0
	 */
	private function get_ticket_assignee_to_notify($tkt_id) {
		//$parent_ticket_ID, $new_reply['post_content'], 'Re: '. $parent_ticket->post_title,$cc
		$assignee_id = get_post_meta($tkt_id, '_ksd_tkt_info_assigned_to', true);
		if (empty($assignee_id) || 0 != $assignee_id) {
//No assignee
			$assignee_id = 1;
		}
		return get_userdata($assignee_id);
	}

}