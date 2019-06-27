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
class Ticket_Activity {

	/**
	 * Get a single ticket's activity
	 * @since 2.0.0
	 */
	public function get_ticket_activity() {
		if (!wp_verify_nonce($_POST['ksd_admin_nonce'], 'ksd-admin-nonce')) {
			die(__('Busted!', 'kanzu-support-desk'));
		}
		$this->do_admin_includes();
		try {
			$args = array('post_type' => 'ksd_ticket_activity', 'post_parent' => sanitize_key($_POST['tkt_id']), 'post_status' => 'private');
			$ticket_activities = get_posts($args);

			if (count($ticket_activities) > 0 && !empty($_POST['tkt_id'])) {
				//Replace the post_author IDs with names.
				foreach ($ticket_activities as $activity) {
					$activity->post_author = (0 == $activity->post_author ? '' : get_userdata($activity->post_author)->display_name);
					$activity->post_date = date_i18n(__('M j, Y @ H:i'), strtotime($activity->post_date));
				}
			} else {
				$ticket_activities = __('No activity yet.', 'kanzu-support-desk');
			}
		} catch (Exception $e) {
			$ticket_activities = array(
				'error' => array('message' => $e->getMessage(), 'code' => $e->getCode()),
			);
		}
		echo json_encode($ticket_activities);
		die(); // IMPORTANT: don't leave this out.
	}

	/**
	 * Change a ticket's status
	 */
	public function change_status() {
		if (!wp_verify_nonce($_POST['ksd_admin_nonce'], 'ksd-admin-nonce')) {
			die(__('Busted!', 'kanzu-support-desk'));
		}

		try {
			$this->do_admin_includes();
			$tickets = new KSD_Tickets_Controller();

			if (!is_array($_POST['tkt_id'])) {
//Single ticket update
				$updated_ticket = new stdClass();
				$updated_ticket->tkt_id = $_POST['tkt_id'];
				$updated_ticket->new_tkt_status = $_POST['tkt_status'];

				if ($tickets->update_ticket($updated_ticket)) {
					echo json_encode(__('Updated', 'kanzu-support-desk'));
				} else {
					throw new Exception(__('Failed', 'kanzu-support-desk'), -1);
				}
			} else {
//Update tickets in bulk.
				$updateArray = array("tkt_status" => $_POST['tkt_status']);
				if (is_array($tickets->bulk_update_ticket($_POST['tkt_id'], $updateArray))) {
					echo json_encode(__('Tickets Updated', 'kanzu-support-desk'));
				} else {
					throw new Exception(__('Updates Failed', 'kanzu-support-desk'), -1);
				}
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
	 * Retrieve a single ticket and all its replies
	 */
	public function get_single_ticket() {

		if (!wp_verify_nonce($_POST['ksd_admin_nonce'], 'ksd-admin-nonce')) {
			die(__('Busted!', 'kanzu-support-desk')); //@TODO Change this to check referrer
		}
		$this->do_admin_includes();
		try {
			$response = get_post($_POST['tkt_id']);
			//@TODO Mark the ticket as read. Use custom field.
			$this->do_change_read_status($_POST['tkt_id']);
		} catch (Exception $e) {
			$response = array(
				'error' => array('message' => $e->getMessage(), 'code' => $e->getCode()),
			);
		}
		echo json_encode($response);
		die(); // IMPORTANT: don't leave this out.
	}

	/**
	 * Change ticket's read status
	 * @throws Exception
	 */
	public function change_read_status() {
		if (!wp_verify_nonce($_POST['ksd_admin_nonce'], 'ksd-admin-nonce')) {
			die(__('Busted!', 'kanzu-support-desk'));
		}

		try {
			$this->do_admin_includes();
			if ($this->do_change_read_status($_POST['tkt_id'], $_POST['tkt_is_read'])) {
				echo json_encode(__('Ticket updated', 'kanzu-support-desk'));
			} else {
				throw new Exception(__('Update Failed. Please retry', 'kanzu-support-desk'), -1);
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
	 * Mark a ticket as read or unread
	 * @param int $ticket_ID The ticket ID
	 * @param boolean $mark_as_read Whether to mark the ticket as read or not
	 */
	private function do_change_read_status($ticket_ID, $mark_as_read = true) {
		$ticket_read_status = ($mark_as_read ? 1 : 0);
		$updated_ticket = new stdClass();
		$updated_ticket->tkt_id = $ticket_ID;
		$updated_ticket->new_tkt_is_read = $ticket_read_status;
		$TC = new KSD_Tickets_Controller();
		return $TC->update_ticket($updated_ticket);
	}

}