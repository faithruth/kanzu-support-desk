<table class="wp-list-table widefat striped downloads">
    <thead>
        <tr>            
            <th><?php _e( 'Ticket', 'kanzu-support-desk' ); ?></th>
            <th><?php _e( 'Status', 'kanzu-support-desk' ); ?></th>
            <th><?php _e( 'Date', 'kanzu-support-desk' ); ?></th>
            <th><?php _e( 'Actions', 'kanzu-support-desk' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        global $post;
        
        $support_tickets = get_posts( array(
            'post_type'         => 'ksd_ticket',
            'post_status'       => array( 'new', 'open', 'draft', 'pending', 'resolved' ),
            'tax_query'         => array(
                array(
                    'taxonomy'  => 'product',
                    'field'     => 'slug',
                    'terms'     => strtolower( $post->post_title )
                )
            )
        ) );
        if ( ! empty( $support_tickets ) ) :
            foreach ( $support_tickets as $support_ticket ):
                ?>
                <tr>
                    <td><?php echo $support_ticket->post_title; ?></td>
                    <td><?php echo $support_ticket->post_status; ?></td>     
                    <td><?php echo date( 'd M Y, @ H:i', strtotime( $support_ticket->post_modified ) ); ?></td>
                    <td><a href='<?php echo admin_url( 'post.php?post=' . absint( $support_ticket->ID ) . '&action=edit' ); ?>'><?php _e( 'View Ticket', 'kanzu-support-desk' ); ?></a></td>
                </tr>
                <?php
            endforeach;
        else:
            echo '<tr><td colspan="4" >' . __( 'You have not logged any tickets yet', 'kanzu-support-desk' ) . '</td></tr>';
        endif;
        ?>    
    </tbody>
</table>

