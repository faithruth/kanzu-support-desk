<?php
/**
 * Single Replies template
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
?>
<?php global $post; ?>
<ul id="ksd-ticket-replies" class="ticket-<?php echo wp_kses_post( $post->ID ); ?>"> 
														<?php
														$ksd_admin = Admin::get_instance();
														$replies   = $ksd_admin->do_get_ticket_replies_and_notes( $post->ID, false );
														foreach ( $replies as $reply ) :
															?>
		<li class="ticket-reply <?php echo wp_kses_post( $reply->post_type ); ?>">
			<div class="ticket-reply-meta">
															<?php echo wp_kses_post( $reply->post_author_avatar ); ?>
				<span class="reply_author"><?php echo wp_kses_post( $reply->post_author_display_name ); ?></span>
				<span class="reply_date"><?php echo wp_kses_post( $reply->post_date ); ?></span>
															<?php if ( ! empty( $reply->ksd_cc ) ) : ?>
					<div class="ksd-reply-cc">
																<?php esc_attr_e( 'CC', 'kanzu-support-desk' ); ?>:<span class="ksd-cc-emails"><?php echo wp_kses_post( $reply->ksd_cc ); ?></span>
					</div>
				<?php endif; ?>
			</div>
			<div class="reply_content">
															<?php echo wp_kses_post( $reply->post_content ); ?>
			</div>
															<?php do_action( 'ksd_after_reply' ); ?>
		</li>
	<?php endforeach; ?>
</ul>
