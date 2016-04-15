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
    <span class="ksd-misc-value" id="ksd-misc-assign-to"><?php    $assigned_to_ID = get_post_meta( $post->ID, '_ksd_tkt_info_assigned_to', true ); 
        $assigned_to = ( 0 == $assigned_to_ID || empty ( $assigned_to_ID ) ? __( 'No One', 'kanzu-support-desk' ) : get_userdata ( $assigned_to_ID )->display_name  );
        echo    $assigned_to; ?></span>
    <a href="#assign-to" class="edit-assign-to"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_assigned_to ksd_tkt_info_wrapper hidden">    
        <select name="_ksd_tkt_info_assigned_to">
            <?php foreach ( get_users() as $agent ) { ?>
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
<input type="hidden" value="<?php echo $post->post_status; ?>" id="hidden_ksd_post_status" name="hidden_ksd_post_status"><!--On change, save the ticket status-->
<input type="hidden" value="admin-form" id="_ksd_tkt_info_channel" name="_ksd_tkt_info_channel"><!--On change, save the ticket status-->
<?php   $ksd_adminn = KSD_Admin::get_instance();
       echo $ksd_adminn->output_ticket_info_customer( get_userdata( $post->post_author ) );
?>
<div class="ksd-misc-extras misc-pub-section"><?php    
        $ksd_admin = KSD_Admin::get_instance();
        $customer_other_tickets = $ksd_admin->get_customer_tickets( $ksd_current_customer->ID, array( 'exclude' => array( $post->ID ) ) );     
        
        if ( ! empty ( $customer_other_tickets ) ) :
            printf( '<h4>%s</h4><ul>',__( 'Other Tickets','kanzu-support-desk' ) ); 
            foreach ( $customer_other_tickets as $a_ticket ): ?>        
                <li>                      
                    <a href='<?php echo admin_url( 'post.php?post=' . absint( $a_ticket->ID ) . '&action=edit' ); ?>'>#<?php echo $a_ticket->ID ; ?></a><span class="ksd-post-status-display <?php echo $a_ticket->post_status ; ?>"> <?php echo $a_ticket->post_status ; ?></span><span> <?php echo $a_ticket->post_title;?></span>   
                </li>
                <?php
            endforeach;
            echo '</ul>';
        endif;
    ?>
    <?php 
    if ( class_exists( 'WooCommerce' ) ) :
        $woo_customer_order_args = array(
            'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $ksd_current_customer->ID, 
            'post_type'   => wc_get_order_types(), 
            'post_status' => array_keys( wc_get_order_statuses() ),
        );
        if( isset( $woo_order_id ) ) {
            $woo_customer_order_args['post__not_in'] = array( $woo_order_id );
        }
        $customer_orders = get_posts( $woo_customer_order_args );
        if ( count( $customer_orders > 0 ) ):
            printf( '<h4>%s</h4><ul>',__( 'Other Orders','kanzu-support-desk' ) );
            foreach( $customer_orders as $order ): ?>
                <li>                      
                    <a href='<?php echo admin_url( 'post.php?post=' . absint( $order->ID ) . '&action=edit' ); ?>'>#<?php echo $order->ID; ?></a><span> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->post_date ) ) ; ?></span>   
                </li><?php                
            endforeach;
            echo '</ul>';
        endif;
    endif; 
    if ( class_exists( 'Easy_Digital_Downloads' ) ) :
        $edd_customer   = new EDD_Customer( $ksd_current_customer->ID, true );
        $payment_ids    = explode( ',', $edd_customer->payment_ids );
        $edd_payments   = edd_get_payments( array( 'post__in' => $payment_ids ) );
	$edd_payments   = array_slice( $edd_payments, 0, 10 );
        if ( ! empty( $edd_payments ) ) :
            printf( '<h4>%s</h4><ul>',__( 'Other Downloads','kanzu-support-desk' ) );
            foreach( $edd_payments as $download ): ?>
                <li>                      
                    <a href='<?php echo admin_url( 'post.php?post=' . absint( $download->ID ) . '&action=edit' ); ?>'>#<?php echo $download->ID; ?></a><span> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $download->post_date ) ); ?></span>   
                </li><?php                
            endforeach;
            echo '</ul>';
        endif;
    endif; ?>                
</div>