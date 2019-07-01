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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ticket Reply class functionality
 */
class Bulk_ticket {

	/**
	 * In bulk edit mode, save changes to tickets
	 *
	 * @TODO Add these changes to ticket activities
	 */
	public function save_bulk_edit_ksd_ticket() {
		$post_ids       = ( ! empty( $_POST['post_ids'] ) ) ? $_POST['post_ids'] : array();
		$update_columns = array();
		$update_keys    = array( '_ksd_tkt_info_assigned_to', '_ksd_tkt_info_severity' );

		foreach ( $update_keys as $key ) {
			if ( ! empty( $_POST[ $key ] ) ) {
				$update_columns[ $key ] = wp_kses_post( $_POST[ $key ] );
			}
		}

		if ( ! empty( $post_ids ) && is_array( $post_ids ) && ! empty( $update_columns ) ) {
			foreach ( $post_ids as $post_id ) {
				foreach ( $update_columns as $ksd_key => $new_value ) {
					update_post_meta( $post_id, $ksd_key, $new_value );
				}
			}
		}
		if ( ! defined( 'PHPUNIT' ) ) {
			die();
		}

	}

	/**
	 * Modify ticket bulk update messages
	 *
	 * @since 2.0.0
	 */
	public function ticket_bulk_update_messages( $bulk_messages, $bulk_counts ) {
		$bulk_messages['ksd_ticket'] = array(
			'updated'   => sprintf( _n( '%s ticket updated.', '%s tickets updated.', $bulk_counts['updated'], 'kanzu-support-desk' ), $bulk_counts['updated'] ),
			'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 ticket not updated, somebody is editing it.', 'kanzu-support-desk' ) :
			_n( '%s ticket not updated, somebody is editing it.', '%s tickets not updated, somebody is editing them.', $bulk_counts['locked'] ),
			'deleted'   => _n( '%s ticket permanently deleted.', '%s tickets permanently deleted.', $bulk_counts['deleted'] ),
			'trashed'   => _n( '%s ticket moved to the Trash.', '%s tickets moved to the Trash.', $bulk_counts['trashed'] ),
			'untrashed' => _n( '%s ticket restored from the Trash.', '%s tickets restored from the Trash.', $bulk_counts['untrashed'] ),
		);
		return $bulk_messages;
	}

}
