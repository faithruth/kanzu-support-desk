<?php
/**
 * Admin side Kanzu Support Desk Notifications
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

namespace Kanzu\Ksd\Admin\Notification;

use Kanzu\Ksd\Hook_Registry as ksd_hooks;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Email class functionality
 */
class Email {

	private $ksd_hooks;

	Public function __constructor(){
		$this->ksd_hooks = $ksd_hooks;
	}

	/**
	 * Process the notification feedback
	 *
	 * @since 2.2.0
	 */
	public function process_notification_feedback() {
		include_once KSD_PLUGIN_DIR . 'includes/admin/notification/class-notification.php';
		$ksd_notify = new Notification();
		$response = $ksd_notify->process_notification_feedback();
		echo json_encode($response);
		die();
	}

	/**
	 * Disable display of notifications
	 * @since 2.2.0
	 */
	public function disable_notifications() {
		$ksd_settings = $this->ksd_hooks->get_settings();
		$ksd_settings['notifications_enabled'] = "no";
		$this->ksd_hooks->update_settings($ksd_settings);
		echo json_encode(__('Thanks for your time. If you ever have any feedback, please get in touch - feedback@kanzucode.com', 'kanzu-support-desk'));
		if (!defined('PHPUNIT')) {
			die();
		}

	}

	/**
	 * Disable display of notifications
	 *
	 * @since 2.3.6
	 */
	public function hide_questionnaire() {
		$ksd_settings = $this->ksd_hooks->get_settings();
		$ksd_settings['show_questionnaire_link'] = "no";
		$this->ksd_hooks->update_settings($ksd_settings);
		wp_send_json_success(
			array('message' => __('Questionnaire hidden', 'kanzu-support-desk'))
		);
	}

	/**
	 * Retrieve Kanzu Support Desk notifications. These are currently
	 * retrieved from the KSD blog feed, http://blog.kanzucode.com/feed/
	 * @since 1.3.2
	 */
	public function get_notifications() {
		ob_start();
		if (false === ($cache = get_transient('ksd_notifications_feed'))) {
			$feed = wp_remote_get('http://kanzucode.com/work/blog/kanzu-support-desk-articles/feed/', array('sslverify' => false));
			if (!is_wp_error($feed)) {
				if (isset($feed['body']) && strlen($feed['body']) > 0) {
					$cache = wp_remote_retrieve_body($feed);
					set_transient('ksd_notifications_feed', $cache, 86400); //Check everyday
				}
			} else {
				$cache["error"] = __('Sorry, an error occurred while retrieving the latest notifications. A re-attempt will be made later. Thank you.', 'kanzu-support-desk');
			}
		}
		echo json_encode($cache);
		echo ob_get_clean();
		die();
	}

	/**
	 * AJAX callback to send notification of a new ticket
	 */
	public function notify_new_ticket() {
		// if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
		//       die ( __('Busted!', 'kanzu-support-desk') );
		// } //@TODO Update this NONCE check.
		$this->do_notify_new_ticket();
		echo json_encode(__('Notification sent.', 'kanzu-support-desk'));
		die(); //IMPORTANT. Shouldn't be left out.
	}

	/**
	 * Notify the primary administrator that a new ticket has been logged
	 * The wp_mail call in send_mail takes a while (about 5s in our tests)
	 * so for tickets logged in the admin side, we call this using AJAX
	 * @param string $notify_email Email to notify
	 * @param int $tkt_id
	 * @param string $customer_email The email of the customer for whom the new ticket has been created
	 * @param string $ticket_subject The new ticket's subject
	 * @param Array $attachments Filenames to attach to the notification
	 * @since 1.5.5
	 */
	public function do_notify_new_ticket($notify_email, $tkt_id, $customer_email = null, $ticket_subject = null, $ticket_message = null, $attachments = array()) {
		$ksd_settings = $this->ksd_hooks->get_settings();
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$notify_new_tkt_message = sprintf(__('New customer support ticket on your site %s:', 'kanzu-support-desk'), $blog_name) . "\r\n\r\n";
		if (!is_null($customer_email)) {
			$notify_new_tkt_message .= sprintf(__('Customer E-mail: %s', 'kanzu-support-desk'), $customer_email) . "\r\n\r\n";
		}
		if (!is_null($ticket_subject)) {
			$notify_new_tkt_message .= sprintf(__('Ticket Subject: %s', 'kanzu-support-desk'), $ticket_subject) . "\r\n\r\n";
		}
		if (!is_null($ticket_message)) {
			$notify_new_tkt_message .= sprintf(__('Ticket Message: %s', 'kanzu-support-desk'), $ticket_message) . "\r\n\r\n";
		}
		$notify_new_tkt_message .= $this->ksd_hooks->output_ksd_signature($tkt_id);
		$notify_new_tkt_subject = sprintf(__('[%s] New Support Ticket', 'kanzu-support-desk'), $blog_name);

		//Use two filters, ksd_new_ticket_notifxn_message and ksd_new_ticket_notifxn_subject, to make changes to the
		//the notification message and subject by add-ons
		$this->send_email($notify_email, apply_filters('ksd_new_ticket_notifxn_message', $notify_new_tkt_message, $ticket_message, $ksd_settings, $tkt_id), apply_filters('ksd_new_ticket_notifxn_subject', $notify_new_tkt_subject, $ticket_subject, $ksd_settings, $tkt_id), null, $attachments);

	}

	public function send_debug_email() {
		$email = sanitize_email($_POST['email']);
		if (!is_email($email)) {
			wp_send_json_error(__('Error | Invalid email address specified', 'kanzu-support-desk'));
		}
		$message = __('This is the test message you requested for. Signed. Sealed. Delivered.', 'kanzu-support-desk');
		if ($this->send_email($email, $message)) {
			wp_send_json_success(__('Email sent successfully', 'kanzu-support-desk'));
		} else {
			wp_send_json_error(sprintf(__('Error | Email sending failed. Please <a href="%s" target="_blank">read our guide on this</a>', 'kanzu-support-desk'), 'https://kanzucode.com/knowledge_base/troubleshooting-wordpress-email-delivery/'));
		}
	}

		/**
		 * Send feedback using the form in contextual help
		 *
		 * @since 2.3.6
		 */
		public function send_support_tab_feedback() {

			$current_user = wp_get_current_user();

			$subject = sanitize_text_field($_POST['ksd_support_tab_subject']);
			$message = sanitize_text_field($_POST['ksd_support_tab_message']);

			if (strlen($subject) <= 2) {
				$response = __("Error | The subject field is too short. Please type something then send", "kanzu-support-desk");
				echo json_encode($response);
				die();
			}

			if (strlen($message) <= 2) {
				$response = __("Error | The message field is too short. Please type something then send", "kanzu-support-desk");
				echo json_encode($response);
				die();
			}

			$feedback_message = "{$message},{$current_user->display_name},{$current_user->user_email}";
			$feedback_subject = "KSD Feedback - " . $subject;

			$response = ($this->send_email("feedback@kanzucode.com", $feedback_message, $feedback_subject) ? __('Sent successfully. Thank you!', 'kanzu-support-desk') : __('Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk'));
			echo json_encode($response);
			die();
		}

}
