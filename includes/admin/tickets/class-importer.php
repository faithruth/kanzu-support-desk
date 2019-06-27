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

namespace Kanzu\Ksd\Admin\Ticket;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ticket Reply class functionality
 */
class Importer {

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