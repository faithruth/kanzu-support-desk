<div id="ksd-new-ticket" style="display:none;">
<h3><?php _e('New Ticket','kanzu-support-desk'); ?></h3>
<form action="#" id="new-ticket">
<input type="text" value="Customer Name" size="30" name="customer_name" label="Customer Name"/>
<input type="text" value="Customer Email" size="30" name="customer_email" label="Customer Email"/>
<input type="text" value="Subject" maxlength="255" name="subject" label="Subject"/>
	<?php wp_editor( "Description", "ticket_description",array("media_buttons"=>false, "dfw"=>true) ); ?>
 
 </form>
</div>