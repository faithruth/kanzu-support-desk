<table class="wp-list-table widefat striped downloads">
    <thead>
        <tr>
            <th><?php _e('Ticket', 'kanzu-support-desk');?></th>
            <th><?php _e('Status', 'kanzu-support-desk');?></th>
            <th><?php _e('Date', 'kanzu-support-desk');?></th>
            <th><?php _e('Actions', 'kanzu-support-desk');?></th>
        </tr>
    </thead>
    <tbody>
        <?php
global $current_user; //Current user
$ksd_admin = KSD_Admin::get_instance();
$my_tickets = $ksd_admin->get_customer_tickets($current_user->ID);

$ksd_admin->display_tickets($my_tickets);
?>
    </tbody>
</table>

