<?php $ksd_current_customer = get_userdata( $post->post_author ) ?>
<?php 
$ksd_user_avatar = get_avatar( $ksd_current_customer->user_email );
if ( $ksd_user_avatar ):  ?>
<div class="ksd-misc-customer-display-pic misc-pub-section">
    <?php echo $ksd_user_avatar; ?>
</div>
<?php endif; ?>
<div class="ksd-misc-customer misc-pub-section">
    <?php $customer_label = __( 'Customer: ','kanzu-support-desk' );  ?>
    <span><?php echo apply_filters( 'ksd_ticket_info_customer_label', $customer_label ) ?></span>
    <span class="ksd-misc-value" id="ksd-misc-customer"><?php echo apply_filters( 'ksd_ticket_info_customer_display_name', $ksd_current_customer->display_name ); ?></span>
    <a href="#customer" class="edit-customer"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_customer ksd_tkt_info_wrapper hidden">
        <select name="_ksd_tkt_info_customer"> 
            <?php
            global $wpdb;
            $blog_id = get_current_blog_id();
            $meta_query = array(
                'key'       => $wpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                 'value'    => '"(ksd_customer|subscriber|customer)"',
                'compare'   => 'REGEXP'
            );
            $ksd_customer_query = new WP_User_Query( 
                    array(
                        'meta_query' => array( $meta_query )
            ));
            if ( ! empty( $ksd_customer_query->results ) ) :
                foreach ( $ksd_customer_query->results  as $ksd_customer ) : ?>
                    <option value="<?php echo $ksd_customer->ID; ?>" 
                    <?php selected( $ksd_customer->ID , $post->post_author ); ?>> 
                        <?php echo $ksd_customer->display_name; ?>  
                    </option>
                <?php endforeach; 
            endif;    
            ?>
        </select>
        <a class="save-customer button" href="#customer"><?php _e( 'OK','kanzu-support-desk' ); ?></a>
        <a class="cancel-customer button-cancel" href="#customer"><?php _e( 'Cancel','kanzu-support-desk' ); ?></a>
    </div>
</div>
<div class="ksd-misc-customer-email misc-pub-section">
    <span><?php _e( 'Customer Email','kanzu-support-desk' ); ?>:</span>
    <span class="ksd-misc-value" id="ksd-misc-customer-email"><?php echo apply_filters( 'ksd_ticket_info_customer_email', $ksd_current_customer->user_email ); ?></span>
</div>
<div class="ksd-misc-customer-since misc-pub-section">
    <span><?php _e( 'Customer Since','kanzu-support-desk' ); ?>:</span>
    <span class="ksd-misc-value" id="ksd-misc-customer-since"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $ksd_current_customer->user_registered ) ); ?></span>
</div>

<?php
    $customer_info = array();
    $customer_info = apply_filters( 'ksd_customer_info_meta', $customer_info );
    foreach ( $customer_info as $label => $value ) {
        $custom_field_id = sanitize_title( $label );
        echo '<div class="ksd-misc-customer-' . $custom_field_id . ' misc-pub-section">';
        echo '<span>' .  $label . ':</span>';
        echo '<span clsass="ksd-misc-value" id="ksd-misc-customer-' . $custom_field_id . '">' . $value .'</span>';
        echo '</div>';
    }
