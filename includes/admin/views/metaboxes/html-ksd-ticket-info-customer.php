<div class="ksd-misc-customer misc-pub-section">
    <?php $customer_label = __( 'Customer: ','kanzu-support-desk' );  ?>
    <span><?php echo apply_filters( 'ksd_ticket_info_customer_label', $customer_label ) ?></span>
    <span class="ksd-misc-value" id="ksd-misc-customer"><?php echo apply_filters( 'ksd_ticket_info_customer_display_name', $ksd_current_customer->display_name ); ?></span>
    <a href="#customer" class="edit-customer"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_customer ksd_tkt_info_wrapper hidden">
        <select name="_ksd_tkt_info_customer"> 
            <?php
                $ksd_customer_list      = get_users( array('role' => 'ksd_customer' ) );
                $ksd_customer_list[]    = $ksd_current_customer;
                foreach ( $ksd_customer_list  as $ksd_customer ) : ?>
                    <option value="<?php echo $ksd_customer->ID; ?>" 
                    <?php selected( $ksd_customer->ID , $post->post_author ); ?>> 
                        <?php echo $ksd_customer->display_name; ?>  
                    </option>
            <?php endforeach; ?>
        </select>
        <a class="save-customer button" href="#customer"><?php _e( 'OK','kanzu-support-desk' ); ?></a>
        <a class="cancel-customer button-cancel" href="#customer"><?php _e( 'Cancel','kanzu-support-desk' ); ?></a>
    </div>
</div>
<div class="ksd-misc-customer-email misc-pub-section">
    <span><?php _e( 'Customer Email','kanzu-support-desk' ); ?>:</span>
    <span class="ksd-misc-value" id="ksd-misc-customer-email"><?php echo $ksd_current_customer->user_email; ?></span>
</div>
<div class="ksd-misc-customer-since misc-pub-section">
    <span><?php _e( 'Customer Since','kanzu-support-desk' ); ?>:</span>
    <span class="ksd-misc-value" id="ksd-misc-customer-since"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $ksd_current_customer->user_registered ) ); ?></span>
</div>
