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
class Default_role {

	/**
	 * Get the default roles assigned to a supervisor(KSD admin)
	 *
	 * @since 2.2.9
	 * @return array
	 */
	private function default_supervisor_caps() {
		return array(
			'read'                   => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'delete_others_pages'    => true,
			'delete_others_posts'    => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages'      => true,
			'edit_others_posts'      => true,
			'edit_pages'             => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_published_pages'   => true,
			'edit_published_posts'   => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true,
		);
	}

	/**
	 * Add the default caps to a supervisor role
	 *
	 * @param Object $cap_recipient The user or role receiving the cap
	 * @param string $cap_function add_cap|remove_cap The $wp_roles|$wp_user method to use.
	 *
	 * @since 2.2.9
	 */
	private function modify_default_supervisor_caps( $cap_recipient, $cap_function ) {
		$supervisor_del_capabilities = $this->get_delete_ticket_caps();
		foreach ( $supervisor_del_capabilities as $sup_cap_group ) {
			foreach ( $sup_cap_group as $cap ) {
				$cap_recipient->$cap_function( $cap );
			}
		}

		$supervisor_capabilities = array( 'ksd_manage_users', 'ksd_view_dashboard', 'ksd_view_addons', 'manage_ksd_settings' );
		foreach ( $supervisor_capabilities as $caps ) {
			$cap_recipient->$cap_function( $caps );
		}

	}

	/**
	 * Add/Remove the default caps to a supervisor role
	 *
	 * @param Object $wp_user WP_User object
	 * @param string $cap_function add_cap|remove_cap Which of the $wp_user functions to use.
	 *
	 * @since 2.2.9
	 */
	public function modify_default_owner_caps( $wp_user, $cap_function ) {
		$this->modify_default_agent_caps( $wp_user, $cap_function );
		$this->modify_default_supervisor_caps( $wp_user, $cap_function );
		$wp_user->$cap_function( 'ksd_manage_licenses' );
	}

	/**
	 * Add the default caps to an agent role
	 *
	 * @param Object $cap_recipient The user or role receiving the cap
	 * @param string $cap_function add_cap|remove_cap The $wp_roles|$wp_user method to use.
	 *
	 * @since 2.2.9
	 */
	private function modify_default_agent_caps( $cap_recipient, $cap_function ) {
		$agent_capabilities = $this->get_default_agent_caps();
		foreach ( $agent_capabilities as $agent_cap_group ) {
			foreach ( $agent_cap_group as $cap ) {
				$cap_recipient->$cap_function( $cap );
			}
		}
	}

	/**
	 * Gets the core KSD agent capabilities
	 *
	 * @access public
	 * @return array $capabilities Core post type capabilities
	 * @adapted from EDD
	 */
	private function get_default_agent_caps() {
		$capabilities = array();

		$capability_types = array( 'ksd_ticket', 'ksd_reply', 'ksd_private_note', 'ksd_ticket_activity' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

			);
		}

		return $capabilities;
	}

}