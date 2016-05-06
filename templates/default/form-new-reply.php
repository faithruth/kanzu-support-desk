<form id="ksd-reply">
    <div class="ksd-reply-wp-editor">
        <?php global $post;
            if( ! is_user_logged_in() && ! empty( $post->post_password ) ): ?>
                <input type="email" value="<?php _e( 'Your Email', 'kanzu-support-desk' ); ?>" name="ksd_cust_email" label="Email" class="ksd-customer-email" minlength="2" required/>
        <?php endif; ?>
        <?php wp_editor( '',  'ksd-public-new-reply', array( "media_buttons" => true,"textarea_rows" => 5 ) ); ?>    
    </div>
    <div id="ksd-public-reply-error" class="hidden"></div>
    <div id="ksd-public-reply-success" class="hidden"></div>
    <input type="hidden" value="1" name="ksd_public_reply_form" />
    <?php wp_nonce_field( 'ksd-add-new-reply', 'ksd_new_reply_nonce' ); ?>
    <span class="spinner ksd-public-spinner"></span>
    <input type="submit" value="<?php _e('Send', 'kanzu-support-desk'); ?>" name="ksd_reply_ticket" id="ksd-public-reply-submit" class="button button-primary button-large ksd-submit"/>
</form>