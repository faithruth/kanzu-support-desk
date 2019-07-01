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

namespace Kanzu\Ksd\KSD_Public;

use Kanzu\Ksd\Admin\Hash_Urls as ksd_hash_urls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email class functionality
 */
class Ticket {

	private $hash_urls;

	public function __construct(){
		$this->hash_urls =  $ksd_hash_urls;
	
	}

	/**
	 * Change the 'edit' link displayed in the row actions of the ticket
	 * grid to 'reply' and update the 'view' item if a hash URL exists
	 *
	 * @param  array  $actions Row actions
	 * @param  object $post    WP_Post
	 * @return array         Modified row actions
	 */
	public function modify_row_actions( $actions, $post ) {
		if ( 'ksd_ticket' == $post->post_type ) {
			$title           = _draft_or_post_title();
			$actions['edit'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Reply &#8220;%s&#8221;', 'kanzu-support-desk' ), $title ) ),
				__( 'Reply', 'kanzu-support-desk' )
			);

			$hash_url = get_post_meta( $post->ID, '_ksd_tkt_info_hash_url', true );
			if ( ! empty( $hash_url ) ) {
				$actions['view'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					$hash_url,
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'kanzu-support-desk' ), $title ) ),
					__( 'View', 'kanzu-support-desk' )
				);
			}
		}

		return $actions;
	}

	public function allow_secret_urls() {
		$this->hash_urls->redirect_guest_tickets();
	}

	/**
	 * Tickets that have hash URLs have the word 'Protected' prepended to the title.
	 * We remove that word here
	 *
	 * @param string $title
	 * @return string
	 */
	public function remove_protected_prefix( $title ) {
		global $post;

		if ( 'ksd_ticket' == $post->post_type ) {
			return '%s';
		}
		return $title;
	}

	/**
	 * Prepends the CC to the ticket
	 *
	 * @global WP_Post $post
	 * @param int $post_id
	 * @return string $content
	 * @since 2.0.4
	 */
	public function add_ticket_cc( $content ) {
		global $post;
		$cc = get_post_meta( $post->ID, '_ksd_tkt_info_cc', true );
		if ( '' !== trim( $cc ) ) {
			$content = '<div class="ksd-ticket-cc"><span class="ksd-cc-emails">' . __( 'CC', 'kanzu-support-desk' ) . $cc . '</span></div>' . $content;
		}
		return $content;
	}

	/**
	 * Append ticket attachment HTML to ticket content
	 *
	 * @param string $ticket_content
	 * @param int    $ticket_id
	 * @return string Ticket content
	 */
	public function append_attachments_to_content( $ticket_content, $ticket_id ) {
		$attachment_html = '';
		$attachments     = get_post_meta( $ticket_id, '_ksd_tkt_attachments', true );
		if ( '' !== $attachments ) {
			foreach ( $attachments as $attach_id ) {
				$attachment_html .= '<li><a href="' . get_attachment_link( $attach_id ) . '">' . get_the_title( $attach_id ) . '</a></li>';
			}
			$ticket_content .= '<div class="ksd-attachments-addon"><h3>' . __( 'Attachments', 'kanzu-support-desk' ) . ':</h3> <ul class="ksd_attachments">' . $attachment_html . '</ul></div>';
		}
		return $ticket_content;
	}

	/**
	 * Generate the ticket form that's displayed in the front-end
	 * NB: We only show the form if you enabled the 'show_support_tab' option
	 */
	public function generate_new_ticket_form() {
		$settings = Kanzu_Support_Desk::get_settings();
		if ( 'yes' == $settings['show_support_tab'] ) {
			?>
		   <button id="ksd-new-ticket-public"><?php echo $settings['support_button_text']; ?></button>
														 <?php
															$close_image          = KSD_PLUGIN_URL . 'assets/images/icons/close.png';
															$before_form          = '<div class="ksd-close-form-wrapper"><span class="ksd_close_button">' . __( 'Close', 'kanzu-support-desk' ) . ' <img src="' . $close_image . '"  width="32" height="32" Alt="' . __( 'Close', 'kanzu-support-desk' ) . '" /></span></div>';
															$form_wrapper_classes = 'ksd-form-hidden-tab hidden';
															$form_classes         = 'ksd-form-hidden-tab-form';
															$show_onboarding      = false;
															echo self::generate_support_form( $before_form, $form_wrapper_classes, $form_classes, $show_onboarding );
		}
	}

	/**
	 * Generate a public-facing support form
	 *
	 * @TODO Use this to generate all support forms. Replace the need for html-public-new-ticket.php
	 */
	public static function generate_support_form( $before_form = '', $form_wrapper_classes = 'ksd-form-short-code', $form_classes = 'ksd-form-short-code-form', $show_onboarding = true ) {
		$settings = Kanzu_Support_Desk::get_settings();
		// Include the templating and admin classes
		include_once KSD_PLUGIN_DIR . 'includes/admin/class-admin.php';
		include_once KSD_PLUGIN_DIR . 'includes/public/class-templates.php';
		if ( 'yes' == $settings['enable_customer_signup'] && ! is_user_logged_in() ) {
			ob_start();
			include KSD_PLUGIN_DIR . 'templates/default/html-public-register.php';
			return apply_filters( 'ksd_registration_form', ob_get_clean() );
		} else {
			ob_start();
			$ksd_template  = new Templates();
			$template_part = $ksd_template->get_template_part( 'single', 'submit-ticket', false );
			if ( ! empty( $template_part ) ) {
				include $template_part;
			}

			return ob_get_clean();
		}
	}

	/**
	 * Log a new ticket. We use the backend logic
	 */
	public function log_new_ticket() {
		// First check the CAPTCHA to prevent spam
		$settings = Kanzu_Support_Desk::get_settings();
		if ( 'yes' == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ) {
			$recaptcha_response = $this->verify_recaptcha();
			if ( $recaptcha_response['error'] ) {
				echo json_encode( $recaptcha_response['message'] );
				die(); // This is important for WordPress AJAX
			}
		}

		// Use the admin side logic to do the ticket logging
		$ksd_admin = Admin::get_instance();
		$ksd_admin->log_new_ticket( $_POST );
	}

	/**
	 * Add a reply to a ticket
	 */
	public function reply_ticket() {
		$ksd_admin = Admin::get_instance();
		$ksd_admin->reply_ticket( $_POST );
	}
	/**
	 * Check, using Google reCAPTCHA, whether the submitted ticket was sent
	 * by a human
	 */
	private function verify_recaptcha() {
		$response          = array();
		$response['error'] = true; // Pre-assume an error is going to occur
		if ( empty( $_POST['g-recaptcha-response'] ) ) {
			$response['message'] = __( "ERROR - Sorry, the \"I'm not a robot\" field is required. Please refresh this page & check it.", 'kanzu-support-desk' );
			return $response;
		}
		$settings                  = Kanzu_Support_Desk::get_settings();
		$recaptcha_args            = array(
			'secret'   => $settings['recaptcha_secret_key'],
			'response' => $_POST['g-recaptcha-response'],
		);
		$google_recaptcha_response = wp_remote_get( esc_url_raw( add_query_arg( $recaptcha_args, 'https://www.google.com/recaptcha/api/siteverify' ) ), array( 'sslverify' => false ) );
		if ( is_wp_error( $google_recaptcha_response ) ) {
			$response['message'] = __( 'Sorry, an error occurred. Please retry', 'kanzu-support-desk' );
			return $response;
		}
		$recaptcha_text = json_decode( wp_remote_retrieve_body( $google_recaptcha_response ) );
		if ( $recaptcha_text->success ) {
			$response['error'] = false;
			return $response;
		} else {
			switch ( $recaptcha_text->{'error-codes'}[0] ) {
				case 'missing-input-secret':
					$response['message'] = __( 'Sorry, an error occurred due to a missing reCAPTCHA secret key. Please refresh the page and retry.', 'kanzu-support-desk' );
					break;
				case 'invalid-input-secret':
					$response['message'] = __( 'Sorry, an error occurred due to an invalid or malformed reCAPTCHA secret key. Please refresh the page and retry.', 'kanzu-support-desk' );
					break;
				case 'missing-input-response':
					$response['message'] = __( 'Sorry, an error occurred due to a missing reCAPTCHA input response. Please refresh the page and retry.', 'kanzu-support-desk' );
					break;
				case 'invalid-input-response':
					$response['message'] = __( 'Sorry, an error occurred due to an invalid or malformed reCAPTCHA input response. Please refresh the page and retry.', 'kanzu-support-desk' );
					break;
				default:
					$response['message'] = $settings['recaptcha_error_message'];
			}
			return $response;
		}
	}

	/**
	 * Append content to a single ticket. We use this to append replies
	 *
	 * @param int $tkt_id The ticket's ID
	 * @TODO Move this to a more appropriate class
	 */
	public function append_ticket_replies( $tkt_id ) {
		// Retrieve the replies
		require_once KSD_PLUGIN_DIR . 'includes/admin/class-admin.php';
		KSD()->templates->get_template_part( 'single', 'replies' );
		KSD()->templates->get_template_part( 'form', 'new-reply' );
	}

	public function display_tickets( $tickets ) {
		if ( ! empty( $tickets ) ) :
			foreach ( $tickets as $ticket ) :
				?>
							<tr>
								<td><?php echo $ticket->post_title; ?></td>
								<td><?php echo $ticket->post_status; ?></td>
								<td><?php echo date( 'd M Y, @ H:i', strtotime( $ticket->post_modified ) ); ?></td>
								<td><a href='<?php echo admin_url( 'post.php?post=' . absint( $ticket->ID ) . '&action=edit' ); ?>'><?php _e( 'View Ticket', 'kanzu-support-desk' ); ?></a></td>
							</tr>
							<?php
		endforeach;
			else :
				echo '<tr><td colspan="4" >' . __( 'You have not logged any tickets yet', 'kanzu-support-desk' ) . '</td></tr>';
		endif;
	}

}
