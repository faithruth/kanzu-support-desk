<table class="wp-list-table widefat striped downloads">
    <thead>
        <tr>            
            <th><?php _e('Ticket', 'kanzu-support-desk'); ?></th>
            <th><?php _e('Status', 'kanzu-support-desk'); ?></th>
            <th><?php _e('Date', 'kanzu-support-desk'); ?></th>
            <th><?php _e('Actions', 'kanzu-support-desk'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        global $current_user; //Current user
        $ksd_admin = KSD_Admin::get_instance();
        $my_tickets = $ksd_admin->get_customer_tickets($current_user->ID);

        if ( $my_tickets->have_posts() ) :
            while ( $my_tickets->have_posts() ) : $my_tickets->the_post(); ?>        
                <tr>
                    <td><?php the_title(); ?></td>
                    <td><?php echo get_post_status() ; ?></td>     
                    <td><?php echo get_post_modified_time( 'd M Y, @ H:i' ); ?></td>
                    <td><a href='<?php echo admin_url( 'post.php?post=' . absint( get_the_ID() ) . '&action=edit' ); ?>'><?php _e('View Ticket', 'kanzu-support-desk'); ?></a></td>
                </tr>
                <?php
            endwhile;
            wp_reset_postdata(); //Restore original Post Data
        else:
            echo '<tr><td colspan="4" >' . __('You have not logged any tickets yet', 'kanzu-support-desk') . '</td></tr>';
        endif;
        ?>    
    </tbody>
</table>

