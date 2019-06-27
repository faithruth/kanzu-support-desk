<table class="wp-list-table widefat striped downloads">
    <thead>
        <tr>
            <th><?php esc_attr_e('Ticket', 'kanzu-support-desk');?></th>
            <th><?php esc_attr_e('Status', 'kanzu-support-desk');?></th>
            <th><?php esc_attr_e('Date', 'kanzu-support-desk');?></th>
            <th><?php esc_attr_e('Actions', 'kanzu-support-desk');?></th>
        </tr>
    </thead>
    <tbody>
        <?php
global $post;

$support_tickets = get_posts(array(
	'post_type' => 'ksd_ticket',
	'post_status' => array('new', 'open', 'draft', 'pending', 'resolved'),
	'tax_query' => array(
		array(
			'taxonomy' => 'product',
			'field' => 'slug',
			'terms' => strtolower($post->post_title),
		),
	),
));
$ksd_admin = KSD_Public::get_instance();
$ksd_admin->display_tickets($support_tickets);

?>
    </tbody>
</table>

