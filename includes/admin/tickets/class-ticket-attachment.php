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
class Ticket_Attachment {

	/**
	 * Add the button used to add attachments to a ticket
	 * @param string $editor_id The editor ID
	 */
	public function add_attachments_button($editor_id) {
		if (!isset($_GET['page'])) {
			return;
		}
		if (strpos($editor_id, 'ksd_') !== false) {
//Check that we are modifying a KSD wp_editor. Don't modify wp_editor for posts, pages, etc
			echo "<a href='#' id='ksd-add-attachment-{$editor_id}' class='button {$editor_id}'>" . __('Add Attachment', 'kanzu-support-desk') . "</a>";
		}
	}

}