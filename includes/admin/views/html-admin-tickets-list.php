<table class="ksd-admin-tickets-list wp-list-table widefat fixed posts">
	<thead>
		<tr>
			<th><?php _e('Subject','kanzu-support-desk'); ?></th>
			<th><?php _e('Requester','kanzu-support-desk'); ?></th>		
			<th><?php _e('Requested','kanzu-support-desk'); ?></th>
			<th><?php _e('Type','kanzu-support-desk'); ?></th>
			<th><?php _e('Priority','kanzu-support-desk'); ?></th>
		</tr>	
	</thead>
	<tbody>
	<?php	
 
		$ksd_admin = Kanzu_Support_Admin::get_instance();
		$all = $ksd_admin->filter_ticket_view();
		$Users   = new UsersController();		
		
 
			foreach ( $all as $ticket ): ?>
		<tr>
			<td id="title"><?php echo $ticket->tkt_subject; ?></td>
			<td id="user"><?php echo $Users->getUser( $ticket->tkt_logged_by)->user_nicename; ?></td>
			<td id="time_logged"><?php echo $ticket->tkt_time_logged; ?></td>
			<td id="status"><?php echo $ticket->tkt_status; ?></td>
			<td id="severity"><?php echo $ticket->tkt_severity; ?></td>
		</tr>
		<?php
			endforeach;		 
	?>
	</tbody>
	<tfoot>
		<tr>
			<th><?php _e('Subject','kanzu-support-desk'); ?></th>
			<th><?php _e('Requester','kanzu-support-desk'); ?></th>		
			<th><?php _e('Requested','kanzu-support-desk'); ?></th>
			<th><?php _e('Type','kanzu-support-desk'); ?></th>
			<th><?php _e('Priority','kanzu-support-desk'); ?></th>	
		</tr>	
	</tfoot>

</table>
