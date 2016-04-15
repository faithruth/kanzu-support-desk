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

       if ( ! empty ( $my_tickets ) ) :
             foreach ( $my_tickets as $a_ticket ): ?>
                <tr>
                    <td><?php echo $a_ticket->post_title; ?></td>
                    <td><?php echo $a_ticket->post_status ; ?></td>     
                    <td><?php echo date('d M Y, @ H:i', strtotime( $a_ticket->post_modified ) ); ?></td>
                    <td><a href='<?php echo admin_url( 'post.php?post=' . absint( $a_ticket->ID ) . '&action=edit' ); ?>'><?php _e('View Ticket', 'kanzu-support-desk'); ?></a></td>
                </tr>
                <?php
            endforeach;
        else:
            echo '<tr><td colspan="4" >' . __('You have not logged any tickets yet', 'kanzu-support-desk') . '</td></tr>';
        endif;
        ?>    
    </tbody>
</table>

