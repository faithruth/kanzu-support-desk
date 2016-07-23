<ul id="ksd-list-my-tickets">
    <?php
        global $current_user;//Current user
        $ksd_admin =  KSD_Admin::get_instance();
        $my_tickets = $ksd_admin->get_customer_tickets( $current_user->ID );
        
       if ( ! empty ( $my_tickets ) ) :
            foreach ( $my_tickets as $a_ticket ): ?>
            <li class="ksd-my-ticket">
                <span class="ksd-my-ticket-status <?php echo $a_ticket->post_status ; ?>"><?php echo $ksd_admin->get_localized_status( $a_ticket->post_status ) ; ?></span>
                <span class="ksd-my-ticket-title"><a href='<?php echo get_the_permalink( $a_ticket->ID ); ?>'><?php echo $a_ticket->post_title; ?></a></span>
                <span class="ksd-my-ticket-date"><?php echo date('d M Y, @ H:i', strtotime( $a_ticket->post_modified ) ); ?></span>                
            </li><?php
            endforeach;
        else:
           echo '<li>'. __( 'You have not logged any tickets yet','kanzu-support-desk' ).'</li>';
        endif;?>        
</ul>
<?php $current_settings = Kanzu_Support_Desk::get_settings(); ?>
<a class="button button-large button-primary ksd-button" href="<?php echo get_permalink( $current_settings['page_submit_ticket'] ); ?>"><?php _e( 'Submit Ticket', 'kanzu-support-desk' ); ?></a>