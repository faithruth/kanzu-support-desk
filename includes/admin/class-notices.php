<?php
/**
 * KSD's admin notices
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

namespace Kanzu\Ksd\Admin;


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('KSD_Admin_Notices')):
/**
 * KSD_Admin_Notices
 */
	class Notices {
		/**
		 * class constructor
		 */
		public function __construct() {
			add_action('admin_notices', array($this, 'display_admin_notices'));
			add_action('ksd_hide_notices', array($this, 'hide_admin_notices'));
		}
		/**
		 * Display admin notices
		 *
		 * @return void
		 */
		public function display_admin_notices() {
			$ksd_admin_notices = get_option(KSD()->ksd_admin_notices, array());
			if ($ksd_admin_notices) {
				$notice_body = '';
				foreach ($ksd_admin_notices as $admin_notice_name) {
					if (!$this->current_user_can_view_notice($admin_notice_name)) {
						continue;
					}
					ob_start();
					include_once KSD_PLUGIN_DIR . "templates/admin/notices/{$admin_notice_name}.php";
					$notice_body .= ob_get_clean();
				}
				echo $notice_body;
			}
		}

		/**
		 * Check if the current user can view the notice
		 * @param string $notice_name
		 * @return boolean
		 */
		private function current_user_can_view_notice($notice_name) {
			if ('update-roles' == $notice_name && current_user_can('ksd_manage_licenses')) {
				return true;
			}
			return false;
		}
		/**
		 * Hide admin notices
		 *
		 * @return void
		 */
		public function hide_admin_notices() {
			$notice_name = sanitize_key($_GET['ksd_notice']);
			self::remove_notice($notice_name);
		}

		/**
		 * add notice
		 *
		 * @param string $notice_name
		 * @return void
		 */
		public static function add_notice($notice_name) {
			$notices = array_unique(array_merge(get_option(KSD()->ksd_admin_notices, array()), array($notice_name)));
			update_option(KSD()->ksd_admin_notices, $notices);
		}
		/**
		 * remove notice
		 *
		 * @param string $notice_name
		 * @return void
		 */
		public static function remove_notice($notice_name) {
			$notices = array_diff(get_option(KSD()->ksd_admin_notices, array()), array($notice_name));
			update_option(KSD()->ksd_admin_notices, $notices);
		}
	}

endif;

return new KSD_Admin_Notices();
