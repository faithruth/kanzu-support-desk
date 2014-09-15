<div id="select-all-tickets">
      <input type="checkbox" id="tkt_chkbx_all" checked=""><span><?php _e('All ','kanzu-support-desk'); ?></span>
	  <!-- TODO: Options -->
		<div class="ticket-actions" id="tkt_all_options">
			<a href="#" class="trash" id="tkt_'+value.tkt_id+'">Trash</a> | 
			<a href="#" id="tkt_'+value.tkt_id+'" class="change_status">Change Status</a> | 
			<a href="#" id="tkt_'+value.tkt_id+'" class="assign_to">Assign To</a>
		</div>
	  <!-- //TODO: Options -->
	  
</div>
<div id="ticket-list" >
</div>

