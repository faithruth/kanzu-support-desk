<div class="ksd-misc-customer misc-pub-section">
    <?php $customer_label = __('Customer: ','kanzu-support-desk');  ?>
    <span><?php echo apply_filters( 'ksd_ticket_info_customer_label', $customer_label ) ?></span>
    <span class="ksd-misc-value" id="ksd-misc-customer"><?php echo apply_filters( 'ksd_ticket_info_customer_display_name', $ksd_current_customer->display_name ); ?></span>
    <?php if( ! isset( $_GET['post'] ) ):?><a href="#customer" class="edit-customer"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_customer ksd_tkt_info_wrapper hidden">
        <select name="_ksd_tkt_info_customer"> 
            <?php
                global $wp_roles;
                $roles = array( 'ksd_customer' );
                $wp_role_keys = array_keys( $wp_roles->roles );
                if( in_array( 'subscriber', $wp_role_keys ) )
                    $roles[] = 'subscriber';
                if( in_array( 'customer', $wp_role_keys ) )
                    $roles[] = 'customer';
                $ksd_customer_list = array();
                foreach ( $roles as $role ):
                    $users = get_users( array( 'role' => $role ) );
                    if( ! empty( $users ) )
                        foreach ( $users as $user ):
                            $ksd_customer_list[] = $user;
                        endforeach;
                endforeach;
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
    <?php endif;?>
</div>
<div class="ksd-misc-customer-email misc-pub-section">
    <span><?php _e( 'Customer Email','kanzu-support-desk' ); ?>:</span>
    <span class="ksd-misc-value" id="ksd-misc-customer-email"><?php echo $ksd_current_customer->user_email; ?></span>
</div>
<div class="ksd-misc-customer-since misc-pub-section">
    <span><?php _e( 'Customer Since','kanzu-support-desk' ); ?>:</span>
    <span class="ksd-misc-value" id="ksd-misc-customer-since"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $ksd_current_customer->user_registered ) ); ?></span>
</div>
