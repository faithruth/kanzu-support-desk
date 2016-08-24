<div class='update-nag'>
    <p>
       <?php printf( __( 'Kanzu Support Desk | Based on your feedback, we have changed the way roles are managed. Please update the users who manage your installation by following <a href="%s" target="_blank">this KSD User role guide</a>. Your agents will only be able to manage tickets after you do so','kanzu-support-desk' ), 'https://kanzucode.com/knowledge_base/help-desk-user-roles/' ); ?>
    </p>
    <p class="submit">
        <a href="https://kanzucode.com/knowledge_base/help-desk-user-roles/?utm_source=wpadmin&amp;utm_medium=notice&amp;utm_campaign=KSD" class="button-primary" target="_blank"><?php _e( 'Read KSD user roles guide', 'kanzu-support-desk' ); ?></a>
        <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'ksd_action' => 'ksd_hide_notices', 'ksd_notice' => 'update-roles' ) ), 'ksd_hide_notices_nonce', '_ksd_notice_nonce' ) ); ?>"><?php _e( 'Hide This Notice', 'kanzu-support-desk' ); ?></a>
    </p>    
</div>
               

