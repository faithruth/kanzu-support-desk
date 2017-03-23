<div id="ksd-messages-metabox">
    <div id="ksd-ticket-message">
        <?php $ksd_cc = get_post_meta ( $post->ID, '_ksd_tkt_info_cc', true ); 
        if ( ! empty ( $ksd_cc ) ) : ?><div class="ksd-ticket-cc"><?php _e ( 'CC', 'kanzu-support-desk' ) ; ?>:<span class="ksd-cc-emails"><?php echo $ksd_cc; ?></span></div><?php endif; ?>
        <div class="ksd-ticket-message-content"><?php echo apply_filters( 'ksd_the_ticket_content', $post->post_content, $post->ID ); ?></div>
    </div>

    <?php KSD()->templates->get_template_part( 'single', 'replies' ); ?>

    <div id="edit-ticket-tabs"> 
        <ul class="edit-ticket-options">
            <li><a href="#reply_ticket"><?php _e( 'Reply', 'kanzu-support-desk' ); ?></a></li>
            <li><a href="#update_private_note"><?php _e('Reply to staff only', 'kanzu-support-desk'); ?></a></li>
        </ul>
        <div class="edit-ticket-description" id="reply_ticket">
            <input type="text" value="<?php _e('CC', 'kanzu-support-desk'); ?>" maxlength="255" name="ksd_tkt_cc" label="<?php _e('CC', 'kanzu-support-desk'); ?>" class="ksd-cc" minlength="2" style="display:none;" data-rule-ccRule /> 
            <?php wp_editor('', 'ksd_ticket_reply', array("media_buttons" => true, "textarea_rows" => 5)); ?> 
        </div>
        <!-- private notes -->
        <div id="update_private_note" class="single-ticket-textarea">
            <?php $private_note_settings = array( 'textarea_rows' => 5 );
            echo wp_editor( '', 'tkt_private_note', $private_note_settings ); ?>
        </div>
        <!--/ private notes -->
    </div> 
    <?php wp_nonce_field( 'ksd-add-new-reply', 'ksd_new_reply_nonce' ); ?>
    <?php include_once( KSD_PLUGIN_DIR. 'templates/admin/dialog-merge-tickets.php' ); ?>
    <div class="ksd-reply-submit-wrapper">
        <span class="spinner ksd-reply-spinner hidden"></span>
        <input type="submit" value="<?php _e('Send', 'kanzu-support-desk'); ?>" name="ksd_reply_ticket" id="ksd-reply-ticket-submit" class="button button-primary button-large ksd-submit"/>         
    </div>
</div>  