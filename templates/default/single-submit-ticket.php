<?php
/**
 * Single submit ticket template
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

$settings = kanzu_support_desk::get_settings();
if ( isset( $_GET['ksd_tkt_submitted'] ) ) :
	$response_key = KSD()->session->get( 'ksd_notice' );
	echo wp_kses_post( "<div class='ksd-support-form-response' >{$settings[$response_key[0]]}</div>" );
endif;?>
<div class="ksd-new-ticket-form-wrap <?php echo wp_kses_post( $form_wrapper_classes ); ?>">
		<?php echo wp_kses_post( $before_form ); ?>
		<form method="POST" class="ksd-new-ticket-public <?php echo wp_kses_post( $form_classes ); ?>" enctype="multipart/form-data">
			<ul>
			<?php KSD()->templates->get_template_part( 'list', 'ticket-table' ); ?>

			  <li class="ksd-public-submit">
				<img src="<?php echo wp_kses_post( KSD_PLUGIN_URL . 'assets/images/loading_dialog.gif' ); ?>" class="ksd_loading_dialog" width="45" height="35" />
				<input type="submit" value="<?php esc_html_e( 'Send Message', 'kanzu-support-desk' ); ?>" name="ksd-submit-tab-new-ticket" class="ksd-submit"/>
			  </li>
			</ul>
			<input name="action" type="hidden" value="ksd_log_new_ticket" />
			<?php if ( isset( $_GET['woo_order_id'] ) ) : ?>
			<input name="ksd_woo_order_id" type="hidden" value="<?php echo wp_kses_post( sanitize_key( $_GET['woo_order_id'] ) ); ?>" />
			<?php endif; ?>
			<input name="ksd_tkt_channel" type="hidden" value="support-tab" />
			<?php wp_nonce_field( 'ksd-new-ticket', 'new-ticket-nonce' ); ?>
		</form>
		<div class="ksd-support-form-response"></div>
	</div>
