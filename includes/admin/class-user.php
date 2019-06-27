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

namespace Kanzu\Ksd\Admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ticket Reply class functionality
 */
class User {
	
		/**
		 * Check if the user's a member of staff. The only users  considered as staff
		 * are agents, supervisors and administrators
		 *
		 * @param Object $user The user to check
		 * @return boolean Whether the user's a member of staff or not
		 */
		public function is_user_staff($user) {
			if (!isset($user->roles) || !is_array($user->roles)) {
				return false;
			}
			if (in_array('ksd_agent', $user->roles) || in_array('ksd_supervisor', $user->roles) || in_array('administrator', $user->roles)) {
				return true;
			}
			return false;
		}

		/**
		 * Ajax handler for autocomplete user
		 *
		 */
		public function autocomplete_user() {
			global $current_user;
			$return = array();

			$users = get_users(array(
				'blog_id' => false,
				'search' => '*' . $_REQUEST['term'] . '*',
				'exclude' => $current_user->ID,
				'search_columns' => array('user_login', 'user_nicename', 'user_email'),
			));

			foreach ($users as $user) {
				$return[] = array(
					/* translators: 1: user_login, 2: user_email */
					'label' => sprintf(__('%1$s (%2$s)', 'kanzu-support-desk'), $user->user_login, $user->user_email),
					'value' => $user->user_login,
					'ID' => $user->ID,
				);
			}

			wp_die(wp_json_encode($return));
		}

		/**
		 * Temporarily added in 2.2.10 to fix
		 * user rights for anyone who upgrades from 2.2.8 and doesn't get the new roles
		 *
		 * Remove this > 2.2.10
		 */
		private function reset_user_rights() {
			if (!isset($_GET['post_type']) || !isset($_GET['taxonomy'])) {
				return;
			}
			if ('ksd_ticket' != sanitize_text_field($_GET['post_type'])) {
				return;
			}
			if (current_user_can('manage_options') && !current_user_can('manage_ksd_settings') && !current_user_can('edit_ksd_ticket')) {
				include_once KSD_PLUGIN_DIR . 'includes/class-ksd-roles.php';
				KSD()->roles->create_roles();
				KSD()->roles->modify_all_role_caps('add');
				//Make the current user a supervisor. They need to re-select supervisors and agents.
				global $current_user;
				KSD()->roles->add_supervisor_caps_to_user($current_user);
				$user = new WP_User($current_user->ID);
				KSD()->roles->modify_default_owner_caps($user, 'add_cap');
				// KSD_Admin_Notices::add_notice( 'update-roles' );//Inform the user of the changes they need to make.
			}
		}

}