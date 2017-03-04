<?php global $post; ?>
<ul id="ksd-ticket-replies" class="ticket-<?php echo $post->ID; ?>">
    <?php   $ksd_admin =  KSD_Admin::get_instance();
            $replies = $ksd_admin->do_get_ticket_replies_and_notes( $post->ID, false );
            foreach ( $replies as $reply ): ?>
            <li class="ticket-reply">     
                <div class="ticket-reply-author-avatar">
                    <?php echo $reply->post_author_avatar; ?>                            
                </div>                    
                <div class="reply_content">
                    <div class="ticket-reply-meta"> 
                        <div class="ticket-reply-info"> 
                            <span class="reply_author"><?php echo $reply->post_author_display_name; ?></span>
                            <span class="reply_date"><?php echo $reply->post_date; ?></span>
                            <?php if (! empty ( $reply->ksd_cc ) ) : ?>
                                <div class="ksd-reply-cc">
                                    <?php _e( 'CC', 'kanzu-support-desk' ) ; ?>:<span class="ksd-cc-emails"><?php echo $reply->ksd_cc; ?></span>
                                </div>
                            <?php endif; ?>    
                        </div>            
                    </div>    
                    <div class="reply_content">             
                        <p><?php echo $reply->post_content; ?></p>
                        <?php do_action( 'ksd_after_reply'); ?>
                    </div>
                </div>
            </li>
    <?php endforeach; ?>
</ul>