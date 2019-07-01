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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email class functionality
 */
class Custome_Role {

	/**
	 * Create custom user roles
	 *
	 * @since 1.5.0
	 */
	public function create_roles() {
		add_role(
			'ksd_customer',
			__( 'Customer', 'kanzu-support-desk' ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false,
			)
		);
		add_role(
			'ksd_agent',
			__( 'Agent', 'kanzu-support-desk' ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'upload_files' => true,
				'delete_posts' => false,
			)
		);
		add_role( 'ksd_supervisor', __( 'Supervisor', 'kanzu-support-desk' ), $this->default_supervisor_caps() );
	}

	/**
	 * Modify capabilities for all KSD roles
	 *
	 * @param string $change add|remove
	 * @since 2.2.9
	 */
	public function modify_all_role_caps( $change ) {
		$ksd_roles = array( 'ksd_supervisor', 'ksd_agent' );
		foreach ( $ksd_roles as $ksd_role ) {
			$this->modify_role_caps( $ksd_role, $change );
		}
	}

	/**
	 * Add or remove caps from a role
	 *
	 * @param string $change add|remove
	 * @param string $role ksd_agent|ksd_supervisor
	 * @param array  $capabilities
	 */
	public function modify_role_caps( $role, $change = 'add' ) {
		global $wp_roles;

		if ( ! in_array( $role, array( 'ksd_agent', 'ksd_supervisor' ) ) ) {
			return;
		}
		if ( 'add' == $change ) {
			$cap_function = 'add_cap';
		} else {
			$cap_function = 'remove_cap';
		}

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			// Add KSD core capabilities
			$role_obj = get_role( $role );
			$this->modify_default_agent_caps( $role_obj, $cap_function );

			if ( 'ksd_supervisor' == $role ) {
				$this->modify_default_supervisor_caps( $role_obj, $cap_function );
			}
		}
	}

	/**
	 * Make the specified user a supervisor
	 *
	 * @param Object $wp_user
	 */
	public function add_supervisor_caps_to_user( $wp_user ) {
		$this->modify_default_agent_caps( $wp_user, 'add_cap' );
		$this->modify_default_supervisor_caps( $wp_user, 'add_cap' );
	}

	private function get_delete_ticket_caps() {
		$capabilities = array();

		$capability_types = array( 'ksd_ticket', 'ksd_reply', 'ksd_private_note', 'ksd_ticket_activity' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"delete_{$capability_type}",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",

				// Terms
				"delete_{$capability_type}_terms",

			);
		}

		return $capabilities;
	}

}
