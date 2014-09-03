jQuery( document ).ready(function() {
	jQuery( "#tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
	
	/**For the tickets tabs**/
	jQuery( "#ticket-tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( "#ticket-tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

	/*Switch the active tab depending on what page has been selected*/
	activetab=0;
	switch(ksd_admin.admin_tab){
		case "ksd-tickets":
			activetab=1;
		break;
		case "ksd-settings":
			activetab=2;
		break;
		case "ksd-addons":
			activetab=3;
		break;
		case "ksd-help":
			activetab=4;
		break;
	}
	jQuery( "#tabs" ).tabs( "option", "active", activetab );
	/**Add class .alternate to every other row in the tickets table.*/
	jQuery("table.ksd-admin-tickets-list tr").filter(':even').addClass("alternate");
	
	/**Do AJAX calls for filtering tickets**/
	jQuery( "#ticket-tabs li a" ).click(function() {
		var current_tab = jQuery(this).attr('href');
		if(jQuery(current_tab).hasClass("pending")){//Check if the tab has been loaded before
			var data = {
				action : 'ksd_admin_ajax_action',
				ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
				view : current_tab
			};		
			jQuery.post(ksd_admin.ajax_url, data, function(response) {	
				jQuery(current_tab).html("Db response received. Ticket IDs retrieved:");
				jQuery.each( JSON.parse(response), function( key, value ) {
					//console.log(value.tkt_id);
					jQuery(current_tab).append(value.tkt_id+" ");
				});				
				jQuery(current_tab).removeClass("pending");
			});
		}
	});
	
});
