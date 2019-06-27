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
use Kanzu\Ksd\Admin\Tickets\Meta as ksd_meta;
use Kanzu\Ksd\Admin\Notification\Email as ksd_notification;
use Kanzu\Ksd\Admin\Notification\Reply_Ticket as ksd_reply;


if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ticket Reply class functionality
 */
class Ticket_Log {

	private $ksd_customer;

	private $ksd_meta;

	private $ksd_notification;

	private $ksd_reply;

	public function __construct()
    {
		$this->ksd_customer = $ksd_customer;
		$this->ksd_meta = $ksd_meta;
		$this->ksd_notification = $ksd_notification;
		$this->ksd_reply = $ksd_reply;
    }
	/**
	 * Log new tickets or replies initiated by add-ons
	 * Generally, this is called whenever a new ticket is logged
	 * using the action ksd_log_new_ticket [and not the AJAX version
	 * of the same action]
	 *
	 * @param Array $new_ticket New ticket array. Can also be a reply array
	 *
	 * @since 1.0.1
	 */
	public function do_log_new_ticket($new_ticket) 
	{
		$this->do_admin_includes();
		$new_ticket['is_reply'] = false;

		/**
		 * @filter `ksd_new_ticket_or_reply` An incoming KSD ticket/reply. Add-ons should
		 * modify $new_ticket and set $new_ticket['is_reply'] = true for this to be considered
		 * a reply. If it is a reply, the add-on should also set the ticket parent ID in $new_ticket['tkt_id']
		 */
		$new_ticket = apply_filters('ksd_new_ticket_or_reply', $new_ticket);

		//Handle Facebook channel replies
		if ('Facebook Reply' == $new_ticket['ksd_tkt_subject']) {
			$new_ticket['ksd_reply_title'] = $new_ticket['ksd_tkt_subject'];
			$new_ticket['ksd_ticket_reply'] = $new_ticket['ksd_tkt_message'];
			$new_ticket['ksd_rep_date_created'] = $new_ticket['ksd_tkt_time_logged'];
			$this->ksd_reply->reply_ticket($new_ticket);
			return;
		}

		if ($new_ticket['is_reply']) {
			$this->ksd_reply->reply_ticket($new_ticket);
			return;
		}

		//This is a new ticket.
		$this->log_new_ticket($new_ticket, true);
	}


	/**
	 * Add attachment(s) to a ticket
	 * Call this after $this->do_admin_includes() is called
	 * @param int $ticket_id The ticket or reply's ID
	 * @param Array $attachments_array Array containing the attachments
	 * The array is of the form:
	Array
	(
	[0] => Array(
	[url]        => http://url/filename.txt,
	[size]       =>  724 B,
	[filename]   =>  filename.txt
	),
	[1] => Array(
	[url]        => http://url/filename.jpg,
	[size]       =>  146 kB,
	[filename]   =>  filename.jpg
	)
	 *                )
	 * @param Boolean $is_reply Whether this is a reply or a ticket.
	 */
	private function add_ticket_attachments($ticket_id, $attachments_array, $is_reply = false) 
	{
		$attachment_ids = array();
		foreach ($attachments_array as $attachment) {
			$filename = $attachment['url'];
			$filetype = wp_check_filetype(basename($filename), null);

			$attachment = array(
				'guid' => $filename,
				'post_mime_type' => $filetype,
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
				'post_content' => '',
				'post_status' => 'inherit',
			);

			//Insert the attachment.
			$attach_id = wp_insert_attachment($attachment, $filename, $ticket_id);
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
			wp_update_attachment_metadata($attach_id, $attach_data);
			$attachment_ids[] = $attach_id;
		}

		$this->save_ticket_attachments($ticket_id, $attachment_ids);
	}

	/**
	 * Append attachments to a ticket
	 * @param int $ticket_id
	 * @param array $attachment_ids
	 */
	private function save_ticket_attachments($ticket_id, $attachment_ids) {
		add_post_meta($ticket_id, '_ksd_tkt_attachments', $attachment_ids);
	}
	

	/**
	 * Modify the ticket's attachments array for sending in mail.
	 * The mail attachments array only contains filenames
	 * @param Array $tickets_attachments_array The ticket attachment's array
	 * @return Array $mail_attachments_array The attachments array to add to mail
	 * @since 1.7.0
	 */
	private function convert_attachments_for_mail($tickets_attachments_array) {
		$mail_attachments_array = array();
		$upload_dir = wp_upload_dir();
		$attachments_dir = $upload_dir['basedir'] . '/ksd/attachments/';
		foreach ($tickets_attachments_array['filename'] as $single_attached_file) {
			$mail_attachments_array[] = $attachments_dir . $single_attached_file;
		}
		return $mail_attachments_array;
	}

	/**
	 * Log new tickets.  The different channels (admin side, front-end) all
	 * call this method to log the ticket. Other plugins call $this->do_new_ticket_logging  through
	 * an action
	 * @param Array $new_ticket_raw A new ticket array. This is present when ticket logging was initiated
	 *              by an add-on and from the front-end
	 * @param boolean $from_addon Whether the ticket was initiated by an addon or not
	 */
	public function log_new_ticket($new_ticket_raw = null, $from_addon = false) {
		global $current_user;
		if (null == $new_ticket_raw) {
			$new_ticket_raw = $_POST;
		}
		/* if ( ! $from_addon ) {//Check for NONCE if not in add-on mode
				if ( ! wp_verify_nonce( $new_ticket_raw['new-ticket-nonce'], 'ksd-new-ticket' ) ) {
						 die ( __('Busted!', 'kanzu-support-desk') );
				}
			}//@TODO Update this
		*/

		$this->do_admin_includes();

		try 
		{
			$supported__ticket_channels = apply_filters('ksd_channels', array("admin-form", "support-tab", "email", "sample-ticket", "facebook"));
			$tkt_channel = sanitize_text_field($new_ticket_raw['ksd_tkt_channel']);
			if (!in_array($tkt_channel, $supported__ticket_channels)) {
				throw new Exception(__('Error | Unsupported channel specified', 'kanzu-support-desk'), -1);
			}

			$ksd_excerpt_length = 30; //The excerpt length to use for the message.

			//Apply the pre-logging filter .
			$new_ticket_raw = apply_filters('ksd_insert_ticket_data', $new_ticket_raw);

			//We sanitize each input before storing it in the database.
			$new_ticket = array();
			$this->sanitize_field($new_ticket_raw, $new_ticket);

			//Server side validation for the inputs. Only holds if we aren't in add-on mode.
			$this->addon_mode($from_addon, $new_ticket);

			//Get the settings. We need them for tickets logged from the support tab.
			$settings = Kanzu_Support_Desk::get_settings();

			//Return a different message based on the channel the request came on.
			$output_messages_by_channel = array();
			$this->return_diff_message($output_messages_by_channel, $tkt_channel, $current_user, $new_ticket);

			//@TODO Separate action to log a private note needed.

			//Add KSD ticket defaults.
			$new_ticket['post_type'] = 'ksd_ticket';
			$new_ticket['comment_status'] = 'closed';

			//Add post password.
			if ("no" == $settings['enable_customer_signup']) {
				$post_password = wp_generate_password(5);
				$new_ticket['post_password'] = $post_password;
			}

			//Log the ticket.
			$new_ticket_id = wp_insert_post($new_ticket);

			//Add to ticket.
			$this->add_to_ticket($new_ticket_raw);

			//Add meta fields
			$this->add_meta_fields($tkt_channel, $new_ticket_raw, $settings);
		
			//Create a hash URL.
			$this->create_hash($settings, $new_ticket_id);

			//Save ticket meta info.
			$this->ksd_meta->save_ticket_meta_info($new_ticket_id, $new_ticket['post_title'], $meta_array);

			$new_ticket_status = ($new_ticket_id > 0 ? $output_messages_by_channel[$tkt_channel] : __("Error", 'kanzu-support-desk'));

			//Save the attachments.
			$this->save_attachments($new_ticket_raw);

			//If the ticket was logged by using the import feature, end the party here.
			$this->end_party($new_ticket_raw);

			//Notify users
			$this->notify_ksd_users($settings, $tkt_channel, $cust_email, $new_ticket, $new_ticket_raw, $new_ticket_id, $cc, $ksd_attachments);

			if ($from_addon) {
				return true; //For addon mode to ensure graceful exit from function.
			}

			echo json_encode($new_ticket_status);
			if (!defined('PHPUNIT')) {
				die();
			}
			// IMPORTANT: don't leave this out.

		} catch (Exception $e) {
			$response = array(
				'error' => array('message' => $e->getMessage(), 'code' => $e->getCode()),
			);
			echo json_encode($response);
			if (!defined('PHPUNIT')) {
				die();
			}
			// IMPORTANT: don't leave this out.
		}
	}

	private function create_hash($settings, $new_ticket_id){
		if ("no" == $settings['enable_customer_signup']) {
			include_once KSD_PLUGIN_DIR . "includes/admin/class-ksd-hash-urls.php";
			$hash_urls = new KSD_Hash_Urls();
			$meta_array['_ksd_tkt_info_hash_url'] = $hash_urls->create_hash_url($new_ticket_id);
		}
	}
	
	private function addon_mode($from_addon, $new_ticket){
		if ((!$from_addon && strlen($new_ticket['post_title']) < 2 || strlen($new_ticket['post_content']) < 2)) {
			throw new Exception(__('Error | Your subject and message should be at least 2 characters', 'kanzu-support-desk'), -1);
		}
	}

	private function end_party($new_ticket_raw){
		if (isset($new_ticket_raw['ksd_tkt_imported'])) {
			do_action('ksd_new_ticket_imported', array($new_ticket_raw['ksd_tkt_imported_id'], $new_ticket_id));
			return;
		}
	}

	public function save_attachments($new_ticket_raw){
		if (isset($new_ticket_raw['ksd_attachments'])) {
			$this->add_ticket_attachments($new_ticket_id, $new_ticket_raw['ksd_attachments']);
		}
		if (isset($new_ticket_raw['ksd_attachment_ids'])) {
			$this->save_ticket_attachments($new_ticket_id, $new_ticket_raw['ksd_attachment_ids']);
		}
	}

	public function add_to_ticket($new_ticket_raw){
		//Add product to ticket.
		if (isset($new_ticket_raw['ksd_tkt_product_id']) && intval($new_ticket_raw['ksd_tkt_product_id']) > 0) {
			wp_set_object_terms($new_ticket_id, intval($new_ticket_raw['ksd_tkt_product_id']), 'product');
		
		}
		
		//Add category to ticket.
		if (isset($new_ticket_raw['ksd_tkt_cat_id'])) {
			$cat_id = intval($new_ticket_raw['ksd_tkt_cat_id']);
			wp_set_object_terms($new_ticket_id, $cat_id, 'ticket_category');
		}
	}

	public function return_diff_message($output_messages_by_channel, $tkt_channel, $current_user, $new_ticket){
			$output_messages_by_channel['admin-form'] = __('Ticket Logged. Sending notification...', 'kanzu-support-desk');
			$output_messages_by_channel['support-tab'] = $output_messages_by_channel['email'] = $output_messages_by_channel['facebook'] = $settings['ticket_mail_message'];
			$output_messages_by_channel['sample-ticket'] = __('Sample tickets logged.', 'kanzu-support-desk');

			
			if ('facebook' != $tkt_channel && 'sample-ticket' != $tkt_channel && $current_user->ID > 0) {
				//If it is a valid user
				$new_ticket['post_author'] = $current_user->ID;
				$cust_email = $current_user->user_email;
			} elseif (isset($new_ticket_raw['ksd_tkt_cust_id'])) {
				//From addons.
				//@TODO Agents should not log tickets via add-ons otherwise the customer bug arises.
				$new_ticket['post_author'] = $new_ticket_raw['ksd_tkt_cust_id'];
				$cust_email = $new_ticket_raw['ksd_cust_email'];
			} elseif (get_user_by('email', $new_ticket['ksd_cust_email'])) {
				//Customer's already in the Db, get their customer ID.
				$customer_details = get_user_by('email', $new_ticket['ksd_cust_email']);
				$new_ticket['post_author'] = $customer_details->ID;
				$cust_email = $customer_details->user_email;
			} else {
				//The customer isn't in the Db. Let's add them. This is from an add-on.
				$cust_email = sanitize_email($new_ticket_raw['ksd_cust_email']); //Get the provided email address.
				//Check that it is a valid email address. Don't do this check in add-on mode
				if (!is_email($cust_email)) {
					throw new Exception(__('Error | Invalid email address specified', 'kanzu-support-desk'), -1);
				}
				$new_customer = new stdClass();
				$new_customer->user_email = $cust_email;
				//Check whether one or more than one customer name was provided.
				if (false === strpos(trim(sanitize_text_field($new_ticket_raw['ksd_cust_fullname'])), ' ')) {
				//Only one customer name was provided.
					$new_customer->first_name = sanitize_text_field($new_ticket_raw['ksd_cust_fullname']);
				} else {
					preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field($new_ticket_raw['ksd_cust_fullname']), $new_customer_fullname);
					$new_customer->first_name = $new_customer_fullname[1];
					$new_customer->last_name = $new_customer_fullname[2]; //We store everything besides the first name in the last name field.
				}
				//Add the customer to the user table and get the customer ID.
				$new_ticket['post_author'] = $this->ksd_customer->create_new_customer($new_customer);
			}
		}
	public function notify_ksd_users($settings, $tkt_channel, $cust_email, $new_ticket, $new_ticket_raw, $new_ticket_id, $ksd_attachments){
		$cc = isset($new_ticket_raw['ksd_tkt_cc']) && __('CC', 'kanzu-support-desk') !== $new_ticket_raw['ksd_tkt_cc'] ? $new_ticket_raw['ksd_tkt_cc'] : null;

		//Whom to we notify. Defaults to admin if ticket doesn't have an assignee.
		$notify_user_id = (isset($meta_array['_ksd_tkt_info_assigned_to']) ? $meta_array['_ksd_tkt_info_assigned_to'] : 1);
		$notify_user = get_userdata($notify_user_id);
	
		//Notify the customer that their ticket has been logged. CC is only used for tickets logged by admin-form.
			if ("yes" == $settings['enable_new_tkt_notifxns'] && $tkt_channel == "support-tab") {
				$this->send_email($cust_email, "new_ticket", $new_ticket['post_title'], $cc, array(), $new_ticket['post_author']);
			}

			//For add-ons to do something after new ticket is added. We share the ID and the final status.
			if (isset($new_ticket_raw['ksd_addon_tkt_id'])) {
				do_action('ksd_new_ticket_logged', $new_ticket_raw['ksd_addon_tkt_id'], $new_ticket_id);
			}

			//@TODO If $tkt_channel  ==  "admin-form", notify the customer.
			//@TODO If agent logs new ticket by addon, notify the customer.
			if ($tkt_channel !== "admin-form" && $tkt_channel !== "sample-ticket") {
			//Notify the agent.
				$ksd_attachments = (isset($new_ticket_raw['ksd_attachments']) ? $this->convert_attachments_for_mail($new_ticket_raw['ksd_attachments']) : array());
				$this->ksd_notification->do_notify_new_ticket($notify_user->user_email, $new_ticket_id, $cust_email, $new_ticket['post_title'], $new_ticket['post_content'], $ksd_attachments);
			}

			//If this was initiated by the email add-on, end the party here.
			if ("yes" == $settings['enable_new_tkt_notifxns'] && $tkt_channel == "email") {
				$email_subject = $new_ticket['post_title'] . " ~ {$new_ticket_id}";
				$this->send_email($cust_email, "new_ticket", $email_subject, $cc, array(), $new_ticket['post_author']); //Send an auto-reply to the customer

				return;
			}

	}

	private function sanitize_field($new_ticket_raw, $new_ticket){

		$new_ticket['post_title'] = sanitize_text_field(stripslashes($new_ticket_raw['ksd_tkt_subject']));
		$sanitized_message = wp_kses_post(stripslashes($new_ticket_raw['ksd_tkt_message']));
		$new_ticket['post_excerpt'] = wp_trim_words($sanitized_message, $ksd_excerpt_length);
		$new_ticket['post_content'] = $sanitized_message;
		$new_ticket['post_status'] = (isset($new_ticket_raw['ksd_tkt_status']) && in_array($new_ticket_raw['ksd_tkt_status'], array('new', 'open', 'pending', 'draft', 'resolved')) ? sanitize_text_field($new_ticket_raw['ksd_tkt_status']) : 'open');


		if (isset($new_ticket_raw['ksd_cust_email'])) {
			$new_ticket['ksd_cust_email'] = sanitize_email($new_ticket_raw['ksd_cust_email']);
		}

		if (isset($new_ticket_raw['ksd_tkt_time_logged'])) {
			//Set by add-ons.
			$new_ticket['post_date'] = $new_ticket_raw['ksd_tkt_time_logged'];
		} //No need for an else; if this isn't specified, the current time is automatically used.

		if (isset($_POST['ksd_tkt_attachment_ids'])) {
			$new_ticket_raw['ksd_attachment_ids'] = $_POST['ksd_tkt_attachment_ids'];
		}
	}

	private function add_meta_fields($tkt_channel, $new_ticket_raw, $settings){
		//Add meta fields.
		$meta_array = array();
		$meta_array['_ksd_tkt_info_channel'] = $tkt_channel;
		if ($tkt_channel == 'facebook') {
			$meta_array['_ksd_tkt_info_post_id'] = $new_ticket_raw['ksd_addon_tkt_id'];
		}
		
		if (wp_get_referer()) {
			$meta_array['_ksd_tkt_info_referer'] = wp_get_referer();
		}
		if (isset($new_ticket_raw['ksd_tkt_cc']) && $new_ticket_raw['ksd_tkt_cc'] != __('CC', 'kanzu-support-desk')) {
			$meta_array['_ksd_tkt_info_cc'] = sanitize_text_field($new_ticket_raw['ksd_tkt_cc']);
		}
		
		//These other fields are only available if a ticket is logged from the admin side so we need to.
			//first check if they are set.
		if (isset($new_ticket_raw['ksd_tkt_severity'])) {
			$meta_array['_ksd_tkt_info_severity'] = $new_ticket_raw['ksd_tkt_severity'];
		}
		if (isset($new_ticket_raw['ksd_tkt_assigned_to']) && !empty($new_ticket_raw['ksd_tkt_assigned_to'])) {
			$meta_array['_ksd_tkt_info_assigned_to'] = $new_ticket_raw['ksd_tkt_assigned_to'];
		}
		//If the ticket wasn't assigned by the user, check whether auto-assignment is set so we auto-assign it.
		if (empty($new_ticket_raw['ksd_tkt_assigned_to']) && !empty($settings['auto_assign_user'])) {
			$meta_array['_ksd_tkt_info_assigned_to'] = $settings['auto_assign_user'];
		}
		if (isset($new_ticket_raw['ksd_woo_order_id'])) {
			$meta_array['_ksd_tkt_info_woo_order_id'] = $new_ticket_raw['ksd_woo_order_id'];
		}
		
	}

}