<?php global $post; ?>
<ul id="ksd-ticket-replies" class="ticket-<?php echo $post->ID; ?>"> <?php
    $ksd_admin =  KSD_Admin::get_instance();
    $replies = $ksd_admin->do_get_ticket_replies_and_notes( $post->ID, false );
    foreach ( $replies as $reply ): ?>
        <li class="ticket-reply <?php echo $reply->post_type; ?>">
            <div class="ticket-reply-meta">      
                <?php echo $reply->post_author_avatar; ?>                               
                <span class="reply_author"><?php echo $reply->post_author_display_name; ?></span>
                <span class="reply_date"><?php echo $reply->post_date; ?></span>
                <?php if ( ! empty ( $reply->ksd_cc ) ) : ?>
                    <div class="ksd-reply-cc">
                        <?php _e( 'CC', 'kanzu-support-desk' ) ; ?>:<span class="ksd-cc-emails"><?php echo $reply->ksd_cc; ?></span>
                    </div>
                <?php endif; ?>    
            </div>                                 
            <div class="reply_content">             
                <?php echo $reply->post_content; ?>        
            </div>
            <?php do_action( 'ksd_after_reply'); ?>
        </li>
    <?php endforeach; ?>
</ul>