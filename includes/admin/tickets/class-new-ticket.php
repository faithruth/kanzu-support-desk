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
class New_Ticket {

	/**
	 * Change a new ticket object into a $_POST array. $POST arrays are
	 * used by the functions that log new tickets & replies
	 * Add-ons on the other hand supply $new_ticket objects. This function
	 * is a bridge between the two
	 * @param Object $new_ticket New ticket object
	 * @return Array $_POST An array used by the functions that log new tickets. This
	 *                      array is basically the same as the object but has ksd_ prefixing all keys
	 */
	public function convert_ticket_object_to_post($new_ticket) {
		$_POST = array();
		foreach ($new_ticket as $key => $value) {
			$_POST['ksd_' . $key] = $value;
		}
		return $_POST;
	}

}