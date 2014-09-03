<table class="ksd-admin-tickets-list">
	<thead>
		<tr>
			<th><?php _e('Subject','kanzu-support-desk'); ?></th>
			<th><?php _e('Requester','kanzu-support-desk'); ?></th>		
			<th><?php _e('Requested','kanzu-support-desk'); ?></th>
			<th><?php _e('Type','kanzu-support-desk'); ?></th>
			<th><?php _e('Priority','kanzu-support-desk'); ?></th>
		</tr>	
	</thead>
	<tfoot>
		<tr>
			<th><?php _e('Subject','kanzu-support-desk'); ?></th>
			<th><?php _e('Requester','kanzu-support-desk'); ?></th>		
			<th><?php _e('Requested','kanzu-support-desk'); ?></th>
			<th><?php _e('Type','kanzu-support-desk'); ?></th>
			<th><?php _e('Priority','kanzu-support-desk'); ?></th>	
		</tr>	
	</tfoot>
	<tbody>
	<?php	
	    echo "Here";
		$tickets = new TicketsController();		
		$all = $tickets->getTickets("");
		if (is_object($all) ):
			foreach ( $all as $ticket ):?>
		<tr>
			<td><?php echo $ticket->tkt_title; ?></td>
			<td><?php echo $ticket->tkt_logged_by; ?></td>
			<td><?php echo $ticket->tkt_time_logged; ?></td>
			<td><?php echo $ticket->tkt_status; ?></td>
			<td><?php echo $ticket->tkt_severity; ?></td>
		</tr>
		<?php
			endforeach;		 
		 endif;
	?>
	</tbody>
</table>