<?php
/**
 * Admin side Kanzu Support Desk Tickets
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
 * Email class functionality
 */
class Ticket {



	/**
	 * Delete a ticket
	 */
	public function delete_ticket() {
		try {
			if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
				die( __( 'Busted!', 'kanzu-support-desk' ) );
			}
			$this->do_admin_includes();
			$tickets = new Tickets_Controller();

			if ( ! is_array( $_POST['tkt_id'] ) ) {
				if ( $tickets->delete_ticket( $_POST['tkt_id'] ) ) {
					echo json_encode( __( 'Deleted', 'kanzu-support-desk' ) );
				} else {
					throw new Exception( __( 'Failed', 'kanzu-support-desk' ), -1 );
				}
			} else {
				if ( is_array( $tickets->bulk_delete_tickets( $_POST['tkt_id'] ) ) ) {
					echo json_encode( __( 'Tickets Deleted', 'kanzu-support-desk' ) );
				} else {
					throw new Exception( __( 'Ticket Deletion Failed', 'kanzu-support-desk' ), -1 );
				}
			}
			die(); // IMPORTANT: don't leave this out.
		} catch ( Exception $e ) {
			$response = array(
				'error' => array(
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				),
			);
			echo json_encode( $response );
			die(); // IMPORTANT: don't leave this out.
		}
	}

	/**
	 * Change ticket's severity
	 *
	 * @throws Exception
	 */
	public function change_severity() {
		if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
			die( __( 'Busted!', 'kanzu-support-desk' ) );
		}

		try {
			$this->do_admin_includes();
			$updated_ticket                   = new stdClass();
			$updated_ticket->tkt_id           = $_POST['tkt_id'];
			$updated_ticket->new_tkt_severity = $_POST['tkt_severity'];

			$tickets = new Tickets_Controller();

			if ( $tickets->update_ticket( $updated_ticket ) ) {
				echo json_encode( __( 'Updated', 'kanzu-support-desk' ) );
			} else {
				throw new Exception( __( 'Failed', 'kanzu-support-desk' ), -1 );
			}
			die(); // IMPORTANT: don't leave this out.
		} catch ( Exception $e ) {
			$response = array(
				'error' => array(
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				),
			);
			echo json_encode( $response );
			die(); // IMPORTANT: don't leave this out.
		}
	}

	/**
	 * Change a ticket's assignment
	 */
	public function assign_to() {
		if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
			die( __( 'Busted!', 'kanzu-support-desk' ) );
		}
		try {
			$this->do_admin_includes();
			$assign_ticket = new Tickets_Controller();
			if ( ! is_array( $_POST['tkt_id'] ) ) {
				// Single ticket re-assignment
				$updated_ticket                      = new stdClass();
				$updated_ticket->tkt_id              = $_POST['tkt_id'];
				$updated_ticket->new_tkt_assigned_to = $_POST['tkt_assign_assigned_to'];
				$updated_ticket->new_tkt_assigned_by = $_POST['ksd_current_user_id'];
				if ( $assign_ticket->update_ticket( $updated_ticket ) ) {
					// Add the event to the assignments table.
					$this->do_ticket_assignment( $updated_ticket->tkt_id, $updated_ticket->new_tkt_assigned_to, $updated_ticket->new_tkt_assigned_by );
					echo json_encode( __( 'Re-assigned', 'kanzu-support-desk' ) );
				} else {
					throw new Exception( __( 'Failed', 'kanzu-support-desk' ), -1 );
				}
			} else {
				// Bulk re-assignment.
				$update_array = array(
					'tkt_assigned_to' => $_POST['tkt_assign_assigned_to'],
					'tkt_assigned_by' => $_POST['ksd_current_user_id'],
				);
				if ( is_array( $assign_ticket->bulk_update_ticket( $_POST['tkt_id'], $update_array ) ) ) {
					// Add event to assignments table.
					foreach ( $_POST['tkt_id'] as $tktID ) {
						$this->do_ticket_assignment( $tktID, $update_array['tkt_assigned_to'], $update_array['tkt_assigned_by'] );
					}
					echo json_encode( __( 'Tickets Re-assigned', 'kanzu-support-desk' ) );
				} else {
					throw new Exception( __( 'Ticket Re-assignment Failed', 'kanzu-support-desk' ), -1 );
				}
			}
			die(); // IMPORTANT: don't leave this out.
		} catch ( Exception $e ) {
			$response = array(
				'error' => array(
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				),
			);
			echo json_encode( $response );
			die(); // IMPORTANT: don't leave this out.
		}
	}

	/**
	 * Returns total tickets in each ticket filter category ie All, Resolved, etc...
	 */
	public function filter_totals() {
		if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
			die( __( 'Busted!', 'kanzu-support-desk' ) );
		}
		try {
			$this->do_admin_includes();
			$settings = Kanzu_Support_Desk::get_settings();
			$recency  = $settings['recency_definition'];
			$tickets  = new Tickets_Controller();
			$response = $tickets->get_filter_totals( get_current_user_id(), $recency );
		} catch ( Exception $e ) {
			$response = array(
				'error' => array(
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				),
			);
		}
		echo json_encode( $response );
		if ( ! defined( 'PHPUNIT' ) ) {
			die();
		}
		// IMPORTANT: don't leave this out.
	}

	/**
	 * Get tickets used in the merge tickets list
	 */
	public function get_merge_tickets() {
		check_ajax_referer( 'ksd-merging', '_ajax_ksd_merging_nonce' );
		$results = array();
		$args    = array(
			'post_type'      => 'ksd_ticket',
			'posts_per_page' => 12,
			'offset'         => 0,
			'post_status'    => array( 'new', 'open', 'pending', 'resolved', 'draft' ),
			'post__not_in'   => array( sanitize_key( $_POST['parent_tkt_ID'] ) ),
		);

		if ( isset( $_POST['search'] ) ) {
			$args['s'] = wp_unslash( $_POST['search'] );
		}

		$merge_tickets = get_posts( $args );
		foreach ( $merge_tickets as $ticket ) {
			$results[] = array(
				'ID'    => $ticket->ID,
				'title' => trim( esc_html( $ticket->post_title ) ),
			);
		}
		wp_die( wp_json_encode( $results ) );
	}

	/**
	 * Merge two tickets
	 */
	public function merge_tickets() {
		check_ajax_referer( 'ksd-merging', '_ajax_ksd_merging_nonce' );
		$parent_ticket_ID = sanitize_text_field( $_POST['parent_tkt_ID'] );
		$merge_tkt_ID     = sanitize_text_field( $_POST['merge_tkt_ID'] );

		// Make the merge ticket a reply of the parent ticket.
		$post_id = $this->transform_ticket_to_reply( $merge_tkt_ID, $parent_ticket_ID );

		if ( 0 == $post_id || is_wp_error( $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, merging the tickets failed. Please retry', 'kanzu-support-desk' ),
				)
			);
		}

		// Change parent_id of all $merge_tkt_ID replies and notes to $parent_ticket_ID.
		$this->change_replies_parent( $merge_tkt_ID, $parent_ticket_ID );

		// Delete $merge_tkt_ID's post meta since $parent_ticket_ID's now takes precedence.
		$this->delete_ticket_meta( $merge_tkt_ID );

		// Delete ticket activity since merging it with the current will only become confusing and misleading.
		$this->delete_ticket_activities( $merge_tkt_ID );

		// Record this  activity.
		global $current_user, $post;
		$new_ticket_activity                 = array();
		$new_ticket_activity['post_author']  = $current_user->ID;
		$new_ticket_activity['post_title']   = get_the_title( $parent_ticket_ID );
		$new_ticket_activity['post_parent']  = $parent_ticket_ID;
		$new_ticket_activity['post_content'] = sprintf( __( ' merged Ticket #%d into this ticket', 'kanzu-support-desk' ), $merge_tkt_ID );
		do_action( 'ksd_insert_new_ticket_activity', $new_ticket_activity );

		wp_send_json_success(
			array( 'message' => __( 'Merging completed successfully! Reloading the page...', 'kanzu-support-desk' ) )
		);
	}

	/**
	 * Change a ticket into a reply
	 *
	 * @param int $ticket_ID
	 * @param int $new_parent_ticket_ID The ticket's new parent ID
	 * @return int | WP_Error
	 */
	private function transform_ticket_to_reply( $ticket_ID, $new_parent_ticket_ID ) {
		$transformer_ticket = array(
			'ID'          => $ticket_ID,
			'post_type'   => 'ksd_reply',
			'post_status' => 'publish',
			'post_parent' => $new_parent_ticket_ID,
		);
		return wp_update_post( $transformer_ticket );
	}

	/**
	 * Delete a ticket's meta info
	 *
	 * @global Object $wpdb
	 * @param int $ticket_ID
	 */
	private function delete_ticket_meta( $ticket_ID ) {
		global $wpdb;
		$merge_tickets_meta_keys_query = "SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_ksd_tkt_info%' AND post_id='{$ticket_ID}'";
		$merge_ticket_meta_keys        = $wpdb->get_results( $merge_tickets_meta_keys_query );
		foreach ( $merge_ticket_meta_keys as $meta_info ) {
			delete_meta( $meta_info->meta_id );
		}
	}

	/**
	 * Delete a ticket's activities
	 *
	 * @param int $ticket_ID
	 */
	private function delete_ticket_activities( $ticket_ID ) {
		$activity_args = array(
			'post_type'      => array( 'ksd_ticket_activity' ),
			'post_parent'    => $ticket_ID,
			'post_status'    => array( 'private' ),
			'posts_per_page' => -1,
			'offset'         => 0,
		);

		$ticket_activities = get_posts( $activity_args );
		foreach ( $ticket_activities as $activity ) {
			wp_delete_post( $activity->ID, true );
		}
	}

	/**
	 * Change the parent ID of ticket replies and private notes
	 *
	 * @param int $old_parent_ID The current parent ticket ID
	 * @param int $new_parent_ID The new parent ticket ID
	 */
	private function change_replies_parent( $old_parent_ID, $new_parent_ID ) {
		$reply_args = array(
			'post_type'      => array( 'ksd_reply', 'ksd_private_note' ),
			'post_parent'    => $old_parent_ID,
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => -1,
			'offset'         => 0,
		);

		$replies_and_notes = get_posts( $reply_args );
		foreach ( $replies_and_notes as $tkt_reply_or_note ) {
			$update_details = array(
				'ID'          => $tkt_reply_or_note->ID,
				'post_parent' => $new_parent_ID,
			);
			wp_update_post( $update_details );
		}
	}


	/**
	 * Check whether a new ticket already exists. If it does, return its current ID
	 * We check against the ticket subject and author
	 *
	 * @param Object  $new_ticket The new ticket to check
	 * @param boolean $disable_ticket_author_check Whether to check against the ticket author. We don't check if the check's being done
	 *                                              for a member of staff since they are not the ticket creator
	 * @returns int  $ticket_id 0 if the ticket doesn't exist. The ticket's ID if it does
	 *
	 * @since 2.2.9
	 */
	public function check_if_ticket_exists( $new_ticket, $disable_ticket_author_check = false ) {
		global $wpdb;
		$ticket_id = 0;

		$TC                 = new Tickets_Controller();
		$value_parameters   = array();
		$filter             = ' post_type = %s AND post_status != %s ';
		$value_parameters[] = 'ksd_ticket';
		$value_parameters[] = 'trash';

		if ( ! $disable_ticket_author_check ) {
			$filter            .= ' AND post_author = %d ';
			$value_parameters[] = $new_ticket['ksd_tkt_cust_id'];
		}

		$TC->set_tablename( "{$wpdb->prefix}posts" );

		$customers_previous_tickets = $TC->get_tickets( $filter, $value_parameters );

		if ( count( $customers_previous_tickets ) > 0 ) {
			foreach ( $customers_previous_tickets as $a_ticket ) {
				if ( false !== strpos( $new_ticket['ksd_tkt_subject'], $a_ticket->post_title ) ) {
					$ticket_id = $a_ticket->ID;
					break;
				}
			}
		}
		return $ticket_id;
	}
	/**
	 * Generate the ticket volumes displayed in the graph in the dashboard.
	 */
	public function get_dashboard_ticket_volume() {
		try {
			if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
				die( __( 'Busted!', 'kanzu-support-desk' ) );
			}
			$this->do_admin_includes();
			$tickets     = new Tickets_Controller();
			$tickets_raw = $tickets->get_dashboard_graph_statistics();
			// If there are no tickets, the road ends here.
			if ( count( $tickets_raw ) < 1 ) {
				$response = array(
					'error' => array(
						'message' => __( "No logged tickets. Graphing isn't possible", 'kanzu-support-desk' ),
						'code'    => -1,
					),
				);
				echo json_encode( $response );
				die(); // IMPORTANT: don't leave this out.
			}

			$y_axis_label = __( 'Day', 'kanzu-support-desk' );
			$x_axis_label = __( 'Ticket Volume', 'kanzu-support-desk' );

			$output_array   = array();
			$output_array[] = array( $y_axis_label, $x_axis_label );

			foreach ( $tickets_raw as $ticket ) {
				$output_array[] = array( date_format( date_create( $ticket->date_logged ), 'd-m-Y' ), (float) $ticket->ticket_volume ); // @since 1.1.2 Added casting since JSON_NUMERIC_CHECK was kicked out
			}
			echo json_encode( $output_array ); // @since 1.1.2 Removed JSON_NUMERIC_CHECK which is only supported PHP >=5.3
			die(); // Important.

		} catch ( Exception $e ) {
			$response = array(
				'error' => array(
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				),
			);
			echo json_encode( $response );
			die(); // IMPORTANT: don't leave this out.
		}
	}

	/**
	 * Update a ticket's private note
	 */
	public function update_private_note() {
		// if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) ) {//@TODO Update this
		// die ( __('Busted!', 'kanzu-support-desk') );
		// }
		$this->do_admin_includes();
		try {
			$new_private_note                = array();
			$new_private_note['post_title']  = wp_strip_all_tags( $_POST['ksd_reply_title'] );
			$new_private_note['post_parent'] = sanitize_text_field( $_POST['tkt_id'] );
			// Add KSD private_note defaults.
			$new_private_note['post_type']      = 'ksd_private_note';
			$new_private_note['post_status']    = 'private';
			$new_private_note['comment_status'] = 'closed ';

			$new_private_note['post_content'] = wp_kses_post( stripslashes( $_POST['tkt_private_note'] ) );
			if ( strlen( $new_private_note['post_content'] ) < 2 ) {
				// If the private note sent it too short
				throw new Exception( __( 'Error | Private Note too short', 'kanzu-support-desk' ), -1 );
			}
			// Save the private_note.
			$new_private_note_id = wp_insert_post( $new_private_note );

			if ( $new_private_note_id > 0 ) {
				// Add 'post_author' to the response.
				$new_private_note['post_author'] = get_userdata( get_current_user_id() )->display_name;
				$response                        = $new_private_note;
			} else {
				throw new Exception( __( 'Failed', 'kanzu-support-desk' ), -1 );
			}
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
	 * Update ticket messages  displayed
	 *
	 * @since 2.0.0
	 * @global Object $post
	 * @global int $post_ID
	 * @param String $messages
	 * @return Array
	 */
	public function ticket_updated_messages( $messages ) {
		global $post, $post_ID;

		$message_1 = sprintf(
			'%s <a href="%s">%s</a>',
			__( 'Ticket updated.', 'kanzu-support-desk' ),
			esc_url( get_permalink( $post_ID ) ),
			__( 'View ticket', 'kanzu-support-desk' )
		);

		$message_5 = isset( $_GET['revision'] ) ?
		sprintf(
			'%s %s',
			__( 'Ticket restored to revision from', 'kanzu-support-desk' ),
			wp_post_revision_title( (int) $_GET['revision'], false )
		) : false;

		$message_6 = sprintf(
			'%s <a href="%s">%s</a>',
			__( 'Ticket published.', 'kanzu-support-desk' ),
			__( 'View ticket.', 'kanzu-support-desk' ),
			esc_url( get_permalink( $post_ID ) )
		);

		$message_8 = sprintf(
			'$s <a target="_blank" href="%s">%s</a>',
			__( 'Ticket submitted.', 'kanzu-support-desk' ),
			esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ),
			__( 'Preview ticket', 'kanzu-support-desk' )
		);

		$message_9 = sprintf(
			'%s <strong>%1$s</strong>. <a target="_blank" href="%2$s">%s</a>',
			__( 'Ticket scheduled for:', 'kanzu-support-desk' ),
			date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ),
			esc_url( get_permalink( $post_ID ) ),
			__( 'Preview ticket', 'kanzu-support-desk' )
		);

		$message_10 = sprintf(
			'%s <a target="_blank" href="%s">%s</a>',
			__( 'Ticket draft updated.', 'kanzu-support-desk' ),
			esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ),
			__( 'Preview ticket', 'kanzu-support-desk' )
		);

		$messages['ksd_ticket'] = array(
			0  => '',
			1  => $message_1,
			2  => __( 'Custom field updated.', 'kanzu-support-desk' ),
			3  => __( 'Custom field deleted.', 'kanzu-support-desk' ),
			4  => __( 'Ticket updated.', 'kanzu-support-desk' ),
			5  => $message_5,
			6  => $message_6,
			7  => __( 'Ticket saved.', 'kanzu-support-desk' ),
			8  => $message_8,
			9  => $message_9,
			10 => $message_10,
		);
		return $messages;
	}

	/**
	 * Get the current KSD screen
	 *
	 * @since 2.0.0
	 */
	private function get_current_ksd_screen( $screen = null ) {
		$current_ksd_screen_id = 'not_a_ksd_screen';
		if ( null == $screen ) {
			$screen = get_current_screen();
		}
		if ( ! $screen || 'ksd_ticket' !== $screen->post_type ) {
			return $current_ksd_screen_id;
		}
		switch ( $screen->id ) {
			case 'edit-ksd_ticket': // Ticket Grid
				$current_ksd_screen_id = 'ksd-ticket-list';
				break;
			case 'ksd_ticket': // Single ticket view and Add new ticket
				if ( 'add' == $screen->action ) {
					// Add new ticket
					$current_ksd_screen_id = 'ksd-add-new-ticket';
				} else {
					// Single ticket view
					$current_ksd_screen_id = 'ksd-single-ticket-details';
				}
				break;
			case 'edit-ticket_category': // Categories
				$current_ksd_screen_id = 'ksd-edit-categories';
				break;
			case 'edit-product':
				$current_ksd_screen_id = 'ksd-edit-products';
				break;
			case 'ksd_ticket_page_ksd-dashboard':
				$current_ksd_screen_id = 'ksd-dashboard';
				break;
			case 'ksd_ticket_page_ksd-settings': // Settings
				$current_ksd_screen_id = 'ksd-settings';
				break;
		}
		return $current_ksd_screen_id;
	}

	public function get_unread_ticket_count() {
		global $current_user;
		$args  = array(
			'post_type'      => 'ksd_ticket',
			'posts_per_page' => -1,
			'post_status'    => array( 'new', 'open', 'pending' ),
			'meta_key'       => '_ksd_tkt_info_is_read_by_' . $current_user->ID,
			'meta_compare'   => 'NOT EXISTS',
		);
		$query = new WP_Query( $args );
		if ( $query->found_posts > 0 ) {
			wp_send_json_success( $query->found_posts );
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Modify the tickets grid and add ticket-specific headers
	 *
	 * @param array $defaults The grid headers
	 * @return array
	 * @since 2.0.0
	 */
	public function add_tickets_headers( $defaults ) {
		$defaults['status']      = __( 'Status', 'kanzu-support-desk' );
		$defaults['assigned_to'] = __( 'Assigned To', 'kanzu-support-desk' );
		$defaults['severity']    = __( 'Severity', 'kanzu-support-desk' );
		$defaults['customer']    = __( 'Customer', 'kanzu-support-desk' );
		$defaults['replies']     = __( 'Replies', 'kanzu-support-desk' );
		return $defaults;
	}

	/**
	 * Get post status label
	 *
	 * @param string ticket status
	 */
	public function get_ticket_status_label( $post_status ) {
		$label = __( 'Unknown', 'kanzu-support-desk' );
		switch ( $post_status ) {
			case 'open':
				$label = __( 'Open', 'kanzu-support-desk' );
				break;
			case 'pending':
				$label = __( 'Pending', 'kanzu-support-desk' );
				break;
			case 'resolved':
				$label = __( 'Resolved', 'kanzu-support-desk' );
				break;
			case 'new':
				$label = __( 'New', 'kanzu-support-desk' );
				break;
			case 'draft':
				$label = __( 'Draft', 'kanzu-support-desk' );
				break;
		}

		return $label;
	}

	/**
	 * When a new product/download is published by EDD or WooCommerce,
	 * add it as a ticket product
	 *
	 * @since 2.2.0
	 */
	public function on_new_product( $postID, $new_product ) {
		if ( ! term_exists( $new_product->post_title, 'product' ) ) {
			$cat_details = array(
				'cat_name' => $new_product->post_title,
				'taxonomy' => 'product',
			);
			wp_insert_category( $cat_details );
		}
	}

	public function append_classes_to_ticket_grid( $classes, $class, $post_ID ) {
		global $current_screen, $current_user;
		if ( $current_screen && ! isset( $current_screen->id ) && 'edit-ksd_ticket' == $current_screen->id ) {
			return $classes;
		}
		if ( 'yes' == get_post_meta( $post_ID, '_ksd_tkt_info_is_read_by_' . $current_user->ID, true ) ) {
			$classes[] = 'read';
		}
		return $classes;
	}

	/**
	 * Get the corresponding localized version of the ticket status
	 *
	 * @since 2.2.9
	 */
	public function get_localized_status( $status ) {
		$localized_status = '';
		switch ( $status ) {
			case 'new':
				$localized_status = __( 'New', 'kanzu-support-desk' );
				break;
			case 'open':
				$localized_status = __( 'Open', 'kanzu-support-desk' );
				break;
			case 'pending':
				$localized_status = __( 'Pending', 'kanzu-support-desk' );
				break;
			case 'resolved':
				$localized_status = __( 'Resolved', 'kanzu-support-desk' );
				break;
			case 'draft':
				$localized_status = __( 'Draft', 'kanzu-support-desk' );
				break;

		}
		return $localized_status;
	}

}
