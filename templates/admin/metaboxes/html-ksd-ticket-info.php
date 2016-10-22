<?php if ( '' !== get_post_meta( $post->ID, '_ksd_tkt_info_woo_order_id', true ) ): ?>
    <div class="ksd-misc-woo-order-id misc-pub-section">
        <span><?php _e( 'Order','kanzu-support-desk' ); ?>:</span>
        <?php $woo_order_id = get_post_meta( $post->ID, '_ksd_tkt_info_woo_order_id', true ); ?>
        <span class="ksd-misc-value" id="ksd-misc-woo-order-id"><a href="<?php admin_url( 'post.php?post=' . absint( $woo_order_id ) . '&action=edit' ) ; ?>"><?php echo '#'.$woo_order_id; ?></a></span>
    </div>
<?php endif; ?>
<div class="ksd-misc-severity misc-pub-section">
    <span><?php _e('Severity: ','kanzu-support-desk'); ?></span>
    <?php   $the_severity = get_post_meta( $post->ID, '_ksd_tkt_info_severity', true );
            $current_severity = ( empty( $the_severity ) ? 'low' : $the_severity ); ?>
    <span class="ksd-misc-value <?php echo $current_severity; ?>" id="ksd-misc-current-severity"><?php echo $current_severity; ?></span>
    <a href="#severity" class="edit-severity"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_severity ksd_tkt_info_wrapper hidden">
        <select name="_ksd_tkt_info_severity"> 
            <?php foreach ( $this->get_severity_list()  as $severity_label => $severity ) : ?>
                    <option value="<?php echo $severity_label; ?>" 
                    <?php selected( $severity_label, get_post_meta( $post->ID, '_ksd_tkt_info_severity', true ) ); ?>> 
                    <?php echo $severity; ?>  
                    </option>
            <?php endforeach; ?>
        </select>
        <a class="save-severity button" href="#severity"><?php _e( 'OK','kanzu-support-desk' ); ?></a>
        <a class="cancel-severity button-cancel" href="#severity"><?php _e( 'Cancel','kanzu-support-desk' ); ?></a>
    </div>
</div>
<div class="ksd-misc-assign-to misc-pub-section">
    <span><?php _e('Assigned To:','kanzu-support-desk'); ?></span>
    <span class="ksd-misc-value" id="ksd-misc-assign-to"><?php
        $assigned_to_ID     = get_post_meta( $post->ID, '_ksd_tkt_info_assigned_to', true ); 
        $assigned_to_user   = get_userdata ( $assigned_to_ID );
        $assigned_to        = ( false !==  $assigned_to_user ? $assigned_to_user->display_name :   __( 'No One', 'kanzu-support-desk' )  );
        echo    $assigned_to; ?></span>
    <a href="#assign-to" class="edit-assign-to"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_assigned_to ksd_tkt_info_wrapper hidden">    
        <select name="_ksd_tkt_info_assigned_to">
            <?php foreach ( get_users( array( 'role__in' => array('ksd_agent','ksd_supervisor','administrator' ) ) ) as $agent ) { ?>
            <option value="<?php echo $agent->ID; ?>" 
                <?php selected( $agent->ID, get_post_meta( $post->ID, '_ksd_tkt_info_assigned_to', true ) ); ?>> 
                <?php echo $agent->display_name; ?>  
            </option>
            <?php }; ?>
            <option value="0"><?php _e('No One', 'kanzu-support-desk'); ?></option>
        </select>
        <a class="save-assign-to button" href="#assign-to"><?php _e( 'OK','kanzu-support-desk' ); ?></a>
        <a class="cancel-assign-to button-cancel" href="#assign-to"><?php _e( 'Cancel','kanzu-support-desk' ); ?></a>
    </div>
</div>
<?php
    $ksd_tkt_referer = get_post_meta( $post->ID, '_ksd_tkt_info_referer', true );
    if( ! empty( $ksd_tkt_referer ) ):
?>
    <div class="ksd-misc-referer misc-pub-section">
        <span><?php _e( 'Submitted From','kanzu-support-desk' ); ?>:</span>
        <span class="ksd-misc-value" id="ksd-misc-referer"><?php echo $ksd_tkt_referer ; ?></span>
    </div>
<?php endif; ?>
<input type="hidden" value="<?php echo $post->post_status; ?>" id="hidden_ksd_post_status" name="hidden_ksd_post_status"><!--On change, save the ticket status-->
<input type="hidden" value="admin-form" id="_ksd_tkt_info_channel" name="_ksd_tkt_info_channel"><!--On change, save the ticket status-->
<?php do_action( 'ksd_after_ticket_info_metabox', $post ); ?>