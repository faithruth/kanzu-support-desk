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

use Kanzu\Ksd\Admin\Admin as ksd_admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Ticket Reply class functionality
 */
class Meta {

	private $ksd_admin;

	public function __constructor() {
		$this->ksd_admin = $ksd_Admin;
	}

	/**
	 * Save ticket information
	 * This ensures that at all times, tickets only have one of
	 * our predefined statuses (new, open, pending, draft or resolved )
	 * Also, it saves our metavalues
	 * Note that this implements a filter; every return statement MUST return $data
	 *
	 * @since 2.0.0
	 */
	public function save_ticket_info( $data, $postarr ) {
		if ( 'ksd_ticket' !== $data['post_type'] ) {
			// Only handle our tickets.
			return $data;
		}
		// Stop processing if it is a new ticket.
		if ( 'auto-draft' == $data['post_status'] || ( isset( $postarr['auto_draft'] ) && $postarr['auto_draft'] ) ) {
			return $data;
		}

		if ( wp_is_post_revision( $postarr['ID'] ) || wp_is_post_autosave( $postarr['ID'] ) ) {
			return $data;
		}

		// Set post_author to customer.
		if ( isset( $postarr['_ksd_tkt_info_customer'] ) ) {
			$data['post_author'] = $postarr['_ksd_tkt_info_customer'];
		}
		// Save the ticket's meta information.
		$this->save_ticket_meta_info( $postarr['ID'], $postarr['post_title'], $postarr );

		if ( 'publish' == $data['post_status'] ) {
			// Change published tickets' statuses from 'publish' to KSD native ticket statuses
			$post_status         = ( 'auto-draft' == $postarr['hidden_ksd_post_status'] && isset( $postarr['hidden_ksd_post_status'] ) ? 'open' : $postarr['hidden_ksd_post_status'] );
			$data['post_status'] = $post_status;
		}
		return $data;
	}

	/**
	 * Update a ticket's activity
	 *
	 * @param string $changed_item The meta key to change
	 * @param string $ticket_title The title of the ticket being affected
	 * @param string $ticket_id The ticket's ID
	 * @param string $old_value The old value
	 * @param string $new_value The new value
	 *
	 * @since 2.0.0
	 */
	public function update_ticket_activity( $changed_item, $ticket_title, $ticket_id, $old_value, $new_value ) {
		$this->do_admin_includes();
		try {
			$new_ticket_activity                = array();
			$new_ticket_activity['post_title']  = $ticket_title;
			$new_ticket_activity['post_parent'] = $ticket_id;
			// Add KSD ticket activity defaults.
			$new_ticket_activity['post_type']      = 'ksd_ticket_activity';
			$new_ticket_activity['post_status']    = 'private';
			$new_ticket_activity['comment_status'] = 'closed ';
			// Note that the person who did this assignment is captured in the post_author field which is autopopulated by current user's ID.
			$this->get_ticket_activity( $changed_item, $old_value, $new_value );
			$new_ticket_activity['post_content'] = $activity_content;

			// Save the assignment.
			$new_ticket_activity_id = wp_insert_post( $new_ticket_activity );

			if ( $new_ticket_activity_id > 0 ) {
				return true;
			} else {
				return false;
			}
		} catch ( Exception $e ) {
			return false;
		}
	}

	public function get_ticket_activity( $changed_item, $old_value, $new_value ) {
		switch ( $changed_item ) {
			case '_ksd_tkt_info_severity':
				$old_value        = ( '' == $old_value ? 'low' : $old_value );
				$activity_content = sprintf( __( 'changed severity from %1$s to %2$s', 'kanzu-support-desk' ), $old_value, $new_value );
				break;
			case '_ksd_tkt_info_assigned_to':
				$old_value_name   = ( 0 == $old_value ? __( 'No One', 'kanzu-support-desk' ) : $this->get_user_permalink( $old_value ) );
				$new_value_name   = ( 0 == $new_value ? __( 'No One', 'kanzu-support-desk' ) : $this->get_user_permalink( $new_value ) );
				$activity_content = sprintf( __( 're-assigned ticket from %1$s to %2$s', 'kanzu-support-desk' ), $old_value_name, $new_value_name );
				// Send an email to notify the new assignee.
				$this->do_notify_ticket_reassignment( $new_value, $ticket_id );
				break;
			case '_ksd_tkt_info_customer':
				$old_value_name   = ( 0 == $old_value ? __( 'No One', 'kanzu-support-desk' ) : $this->get_user_permalink( $old_value ) );
				$new_value_name   = ( 0 == $new_value ? __( 'No One', 'kanzu-support-desk' ) : $this->get_user_permalink( $new_value ) );
				$activity_content = sprintf( __( ' created ticket for %1$s', 'kanzu-support-desk' ), $new_value_name );
				break;
			default:
				return false; // Any unsupported meta key, end the party here.
		}
	}

	/**
	 * Notify an agent when a ticket has been re-assigned to them
	 *
	 * @param int $new_user_id The ID of the agent to whom the ticket has been reassigned
	 * @param int $tkt_id The ID of the ticket that's been re-assigned
	 * @since 2.2.0
	 * @since 2.2.9 Params $agent_name and $notify_email replaced by $new_user_id
	 */
	public function do_notify_ticket_reassignment( $new_user_id, $tkt_id ) {
		if ( $new_user_id == 0 ) {
			return;
		}
		$agent_name                   = get_userdata( $new_user_id )->display_name;
		$notify_email                 = get_userdata( $new_user_id )->user_email;
		$blog_name                    = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$notify_tkt_reassign_message  = sprintf( __( 'Hi %1$s, A support ticket has been reassigned to you on %2$s:', 'kanzu-support-desk' ), $agent_name, $blog_name ) . "\r\n\r\n";
		$notify_tkt_reassign_message .= $this->ksd_hooks->output_ksd_signature( $tkt_id );
		$notify_tkt_reassign_subject  = sprintf( __( '[%s] Support Ticket Reassigned to you', 'kanzu-support-desk' ), $blog_name );
		$this->send_email( $notify_email, $notify_tkt_reassign_message, $notify_tkt_reassign_subject );
	}

		/**
		 * Send the KSD team feedback
		 *
		 * @since 1.1.0
		 */
	public function send_feedback() {
		$feedback_type = isset( $_POST['feedback_type'] ) ? sanitize_text_field( $_POST['feedback_type'] ) : 'default';
		$user_feedback = sanitize_text_field( $_POST['ksd_user_feedback'] );
		$current_user  = wp_get_current_user();

		$data                  = array();
		$data['action']        = 'feedback';
		$data['feedback_item'] = 'kanzu-support-desk';
		$data['feedback_type'] = $feedback_type;
		$data['user_feedback'] = $user_feedback;
		$data['user_email']    = $current_user->user_email;

		if ( 'waiting_list' == $feedback_type ) {
			$this->send_to_analytics( $data );
			$addon_message = "{$user_feedback},{$current_user->user_email}";
			$response      = ( $this->send_email( 'feedback@kanzucode.com', $addon_message, 'KSD Add-on Waiting List' ) ? __( 'Sent successfully. Thank you!', 'kanzu-support-desk' ) : __( 'Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk' ) );
			echo json_encode( $response );
			die();
		}

		if ( strlen( $user_feedback ) <= 2 ) {
			$response = __( "Error | The feedback field's empty. Please type something then send", 'kanzu-support-desk' );
			echo json_encode( $response );
			die();
		}

		$this->send_to_analytics( $data );
		$feedback_message = "{$user_feedback},{$feedback_type}";
		$response         = ( $this->send_email( 'feedback@kanzucode.com', $feedback_message, 'KSD Feedback' ) ? __( 'Sent successfully. Thank you!', 'kanzu-support-desk' ) : __( 'Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk' ) );
		echo json_encode( $response );
		die();
	}

	/**
	 * Update a ticket's information
	 *
	 * @since 2.0.0
	 */
	public function update_ticket_info() {
		if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
			die( __( 'Busted!', 'kanzu-support-desk' ) );
		}
		$this->do_admin_includes();
		try {
			$tkt_id       = wp_strip_all_tags( $_POST['tkt_id'] );
			$ticket_title = wp_strip_all_tags( $_POST['ksd_reply_title'] );
			$tkt_info     = array();
			parse_str( wp_strip_all_tags( $_POST['ksd_tkt_info'] ), $tkt_info );

			// Update ticket status.
			$post              = get_post( $tkt_id );
			$post->post_status = $tkt_info['_ksd_tkt_info_status'];
			wp_update_post( $post );

			// Update the meta information.
			foreach ( $tkt_info as $tkt_info_meta_key => $tkt_info_new_value ) {
				if ( get_post_meta( $tkt_id, $tkt_info_meta_key, true ) == $tkt_info_new_value ) {
					continue;
				}

				$this->update_ticket_activity( $tkt_info_meta_key, $ticket_title, $tkt_id, get_post_meta( $tkt_id, $tkt_info_meta_key, true ), $tkt_info_new_value );
				update_post_meta( $tkt_id, $tkt_info_meta_key, $tkt_info_new_value );
			}

			$response = __( 'Ticket information updated', 'kanzu-support-desk' );

		} catch ( Exception $e ) {
			$response = array(
				'error' => array(
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				),
			);
		}
		echo json_encode( $response );
		die(); // IMPORTANT: don't leave this out.
	}

	/**
	 * Change a ticket's read/unread state
	 *
	 * @global WP_User $current_user
	 * @param int    $post_id The ticket's ID
	 * @param string $post_title The ticket's title
	 * @param string $new_ticket_state read|unread
	 */
	public function update_ticket_read_state( $post_id, $post_title, $new_ticket_state ) {
		global $current_user;
		$new_ticket_activity                = array();
		$new_ticket_activity['post_author'] = 0;
		$new_ticket_activity['post_title']  = $post_title;
		$new_ticket_activity['post_parent'] = $post_id;

		if ( 'read' == $new_ticket_state ) {
			update_post_meta( $post_id, '_ksd_tkt_info_is_read_by_' . $current_user->ID, 'yes' );
			$new_ticket_activity['post_content'] = sprintf( __( 'Ticket marked as read by %s', 'kanzu-support-desk' ), $this->get_user_permalink( $current_user->ID ) );
		}
		if ( 'unread' == $new_ticket_state ) {
			delete_post_meta( $post_id, '_ksd_tkt_info_is_read_by_' . $current_user->ID );
			$new_ticket_activity['post_content'] = sprintf( __( 'Ticket marked as unread by %s', 'kanzu-support-desk' ), $this->get_user_permalink( $current_user->ID ) );
		}
		do_action( 'ksd_insert_new_ticket_activity', $new_ticket_activity );
	}
	private function get_user_permalink( $user_id ) {
		if ( 0 == $user_id ) {
			return false;
		}
		$user = get_userdata( $user_id );
		return '<a href="' . admin_url( "user-edit.php?user_id={$user_id}" ) . '">' . $user->display_name . '</a>';
	}

	/**
	 * Save a ticket's meta information. This includes severity, assignee, etc
	 *
	 * @param int    $tkt_id The ticket ID
	 * @param string $tkt_title The ticket title
	 * @param Array  $meta_array The ticket meta information
	 * @since 2.0.0
	 */
	public function save_ticket_meta_info( $tkt_id, $tkt_title, $meta_array ) {
		global $current_user;
		$ksd_dynamic_meta_keys = apply_filters(
			'ksd_ticket_info_keys',
			array(
				'_ksd_tkt_info_severity'     => 'low',
				'_ksd_tkt_info_assigned_to'  => 0,
				'_ksd_tkt_info_channel'      => 'admin-form',
				'_ksd_tkt_info_cc'           => '',
				'_ksd_tkt_info_woo_order_id' => '',
				'_ksd_tkt_info_post_id'      => '',
			)
		);

		$ksd_static_meta_keys = array(
			'_ksd_tkt_info_hash_url',
			'_ksd_tkt_info_referer',
		);

		// Save ticket customer meta information in the activity list. This is all we do with the _ksd_tkt_info_customer field.
		if ( isset( $meta_array['_ksd_tkt_info_customer'] ) ) {
			$this->update_ticket_activity( '_ksd_tkt_info_customer', $tkt_title, $tkt_id, wp_get_current_user()->ID, $meta_array['_ksd_tkt_info_customer'] );
		}

		// For the read/unread indicator, save and add the activity separately.
		if ( isset( $meta_array[ '_ksd_tkt_info_is_read_by_' . $current_user->ID ] ) ) {
			$this->update_ticket_read_state( $tkt_id, $tkt_title, $meta_array[ '_ksd_tkt_info_is_read_by_' . $current_user->ID ] );
		}

		// Save the static keys
		$this->save_static_meta_keys( $tkt_id, $ksd_static_meta_keys, $meta_array );

		// Update the dynamic meta information.
		foreach ( $ksd_dynamic_meta_keys as $tkt_info_meta_key => $tkt_info_default_value ) {
			if ( ! isset( $meta_array[ $tkt_info_meta_key ] ) || -1 == $meta_array[ $tkt_info_meta_key ] ) {
				continue; // Only do this if the value exists.
			}

			$tkt_info_old_value = get_post_meta( $tkt_id, $tkt_info_meta_key, true );

			if ( '' == $tkt_info_old_value ) {
				// This is a new ticket.
				$tkt_info_meta_value = ( $tkt_info_default_value == $meta_array[ $tkt_info_meta_key ] ? $tkt_info_default_value : $meta_array[ $tkt_info_meta_key ] );
				add_post_meta( $tkt_id, $tkt_info_meta_key, $tkt_info_meta_value, true );
				continue;
			}
			if ( $tkt_info_old_value == $meta_array[ $tkt_info_meta_key ] ) {
				continue;
			}

			$this->update_ticket_activity( $tkt_info_meta_key, $tkt_title, $tkt_id, $tkt_info_old_value, $meta_array[ $tkt_info_meta_key ] );

			update_post_meta( $tkt_id, $tkt_info_meta_key, $meta_array[ $tkt_info_meta_key ] );
		}

	}

	/**
	 * Save static meta keys. These are keys that have values
	 * that don't change when a ticket is updated. They are only
	 * populated when the ticket is first created
	 *
	 * @param int   $tkt_id                   Ticket ID
	 * @param array $ksd_static_meta_keys   The meta keys
	 * @param array $meta_array             The ticket's meta array. Note that this is passed by reference
	 * @since 2.2.8
	 */
	public function save_static_meta_keys( $tkt_id, $ksd_static_meta_keys, &$meta_array ) {
		foreach ( $ksd_static_meta_keys as $tkt_info_meta_key ) {
			if ( isset( $meta_array[ $tkt_info_meta_key ] ) ) {
				add_post_meta( $tkt_id, $tkt_info_meta_key, $meta_array[ $tkt_info_meta_key ], true );
				unset( $meta_array[ $tkt_info_meta_key ] );
			}
		}
	}

	/**
	 * Get meta query used in filtering read/unread tickets
	 *
	 * @global WP_User $current_user
	 * @param string $ticket_state read|unread
	 * @return array The meta query to use
	 */
	public function get_ticket_state_meta_query( $ticket_state ) {
		$state_meta_query = array();
		global $current_user;
		if ( 'read' == $ticket_state ) {
			$state_meta_query = array(
				'key'     => '_ksd_tkt_info_is_read_by_' . $current_user->ID,
				'value'   => 'yes',
				'compare' => '=',
				'type'    => 'CHAR',
			);
		}
		if ( 'unread' == $ticket_state ) {
			$state_meta_query = array(
				'key'     => '_ksd_tkt_info_is_read_by_' . $current_user->ID,
				'compare' => 'NOT EXISTS',
			);
		}
		return $state_meta_query;
	}

	/**
	 * Output ticket meta boxes
	 *
	 * @param Object $post The WP_Object
	 * @param Array  $metabox The metabox array
	 * @since 2.0.0
	 */
	public function output_meta_boxes( $post, $metabox ) {
		// If this is the ticket messages metabox, format the content for viewing.
		if ( $metabox['id'] == 'ksd-ticket-messages' ) {
			$post->content = $this->ksd_admin->format_message_content_for_viewing( $post->content );
		}
		include_once KSD_PLUGIN_DIR . 'templates/admin/metaboxes/html-' . $metabox['id'] . '.php';
	}

	/**
	 * Output the HTML of the customer fields in the 'Customer Information'
	 * meta box. The customer fields are 'Customer','Customer Email','Customer Since', etc.
	 *
	 * @param Object $post The WP_Object
	 * @since 2.2.3
	 */
	public function output_ticket_info_customer( $post ) {
		ob_start();
		include_once KSD_PLUGIN_DIR . 'templates/admin/metaboxes/html-ticket-info-customer.php';
		$customer_html = ob_get_clean();
		echo apply_filters( 'ksd_ticket_info_customer_html', $customer_html );
	}

	/**
	 * Modify the metaboxes on the ticket edit screen
	 *
	 * @since 2.0.0
	 */
	public function edit_metaboxes( $post_type, $post ) {
		if ( $post_type !== 'ksd_ticket' ) {
			return;
		}

		// Remove unwanted metaboxes.
		$metaboxes_to_remove = array( 'submitdiv', 'authordiv', 'postcustom', 'postexcerpt', 'trackbacksdiv', 'tagsdiv-post_tag' );
		foreach ( $metaboxes_to_remove as $remove_metabox ) {
			remove_meta_box( $remove_metabox, 'ksd_ticket', 'side' );
		}
		// Remove post meta fields.
		remove_meta_box( 'postcustom', 'ksd_ticket', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'ksd_ticket', 'normal' );
		remove_meta_box( 'commentsdiv', 'ksd_ticket', 'normal' );

		// Add a custom submitdiv.
		$publish_callback_args = array(
			'revisions_count' => 0,
			'revision_id'     => null,
		);
		add_meta_box( 'submitdiv', __( 'Ticket Information', 'kanzu-support-desk' ), 'post_submit_meta_box', null, 'side', 'high', $publish_callback_args );

		// Customer information.
		add_meta_box(
			'ksd-ticket-info-customer',
			__( 'Customer Information', 'kanzu-support-desk' ),
			array( $this, 'output_ticket_info_customer' ),
			'ksd_ticket',
			'side',
			'high'
		);

		if ( $post->post_status !== 'auto-draft' ) {
			// For ticket updates
			// Add main metabox for ticket replies.
			add_meta_box(
				'ksd-ticket-messages',
				__( 'Ticket Messages', 'kanzu-support-desk' ),
				array( $this, 'output_meta_boxes' ),
				'ksd_ticket',
				'normal',
				'high'
			);
			// For ticket activity.
			add_meta_box(
				'ksd-ticket-activity',
				__( 'Ticket Activity', 'kanzu-support-desk' ),
				array( $this, 'output_meta_boxes' ),
				'ksd_ticket',
				'side',
				'high'
			);
			// For 'Other Tickets'.
			add_meta_box(
				'ksd-other-tickets',
				__( 'Other Tickets', 'kanzu-support-desk' ),
				array( $this, 'output_meta_boxes' ),
				'ksd_ticket',
				'side',
				'high'
			);
		}
		// Mark ticket as read by current user.
		global $current_user;
		update_post_meta( $post->ID, '_ksd_tkt_info_is_read_by_' . $current_user->ID, 'yes' );
		$new_ticket_activity                 = array();
		$new_ticket_activity['post_author']  = 0;
		$new_ticket_activity['post_title']   = $post->post_title;
		$new_ticket_activity['post_parent']  = $post->ID;
		$new_ticket_activity['post_content'] = sprintf( __( 'Ticket read by %s', 'kanzu-support-desk' ), '<a href="' . admin_url( "user-edit. php?user_id={$current_user->ID}" ) . '">' . $current_user->display_name . '</a>' );
		do_action( 'ksd_insert_new_ticket_activity', $new_ticket_activity );
	}

}
