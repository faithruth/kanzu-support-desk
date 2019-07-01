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

namespace Kanzu\Ksd\Admin\Notification;
use Kanzu\Ksd\Admin\Notification\Emails as ksd_email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email class functionality
 */
class Hooks_Registry {

	private $ksd_email;

	public function __construct() {

		$this->ksd_email = $ksd_email;

		$this->add_ajax_hooks();

	}

	/**
	 * ajax calls
	 *
	 * @return void
	 */
	public function add_ajax_hooks() {
		// Handle AJAX calls
		add_action( 'wp_ajax_ksd_send_feedback', array( $this->ksd_email, 'send_feedback' ) );
		add_action( 'wp_ajax_ksd_support_tab_send_feedback', array( $this->ksd_email, 'send_support_tab_feedback' ) );
		add_action( 'wp_ajax_ksd_get_notifications', array( $this->ksd_email, 'get_notifications' ) );
		add_action( 'wp_ajax_ksd_notify_new_ticket', array( $this->ksd_email, 'notify_new_ticket' ) );
		add_action( 'wp_ajax_ksd_notifications_user_feedback', array( $this->ksd_email, 'process_notification_feedback' ) );
		add_action( 'wp_ajax_ksd_notifications_disable', array( $this->ksd_email, 'disable_notifications' ) );
		add_action( 'wp_ajax_ksd_send_debug_email', array( $this->ksd_email, 'send_debug_email' ) );
		add_action( 'wp_ajax_ksd_hide_questionnaire', array( $this->ksd_email, 'hide_questionnaire' ) );

	}


}
