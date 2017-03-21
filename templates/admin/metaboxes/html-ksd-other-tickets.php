<div class="ksd-misc-extras misc-pub-section">
    <button class="button button-small" id="merge-tickets-button"><?php _e( 'Merge Ticket', 'kanzu-support-desk' ); ?></button>
    <?php  
        $ksd_current_customer = get_userdata( $post->post_author );
        $ksd_admin = KSD_Admin::get_instance();
        $customer_other_tickets = $ksd_admin->get_customer_tickets( $ksd_current_customer->ID, array( 'exclude' => array( $post->ID ) ) );     
        
        if ( ! empty ( $customer_other_tickets ) ) :
            echo '<ul>';
            foreach ( $customer_other_tickets as $a_ticket ): ?>        
                <li>                      
                    <a href='<?php echo admin_url( 'post.php?post=' . absint( $a_ticket->ID ) . '&action=edit' ); ?>'>#<?php echo $a_ticket->ID ; ?></a><span class="ksd-post-status-display <?php echo $a_ticket->post_status ; ?>"> <?php echo $a_ticket->post_status ; ?></span><span> <?php echo $a_ticket->post_title;?></span>   
                </li>
                <?php
            endforeach;
            echo '</ul>';
        else:
                _e( 'No other tickets logged by this customer', 'kanzu-support-desk' );
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