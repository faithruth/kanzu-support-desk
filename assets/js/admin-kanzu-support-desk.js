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
	
	/**Do an AJAX call to retrieve tickets from the Db**/
	var get_tickets = function( current_tab ) {
	if(jQuery(current_tab).hasClass("pending")){//Check if the tab has been loaded before
			var data = {
				action : 'ksd_admin_ajax_action',
				ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
				view : current_tab
			};		
			jQuery.post(ksd_admin.ajax_url, data, function(response) {	
				jQuery.each( JSON.parse(response), function( key, value ) {
					jQuery(current_tab).find('tbody').append('<tr><td id="title">'+value.tkt_title+'</td><td id="user"></td><td id="time_logged">'+value.tkt_time_logged+'</td><td id="status">'+value.tkt_status+'</td><td id="severity">'+value.tkt_severity+'</td></tr>');
				});				
				jQuery(current_tab).removeClass("pending");
				/**Add class .alternate to every other row in the tickets table.*/
				jQuery("table.ksd-admin-tickets-list tr").filter(':even').addClass("alternate");
			});
		}
	};
	
	/**Pre-populate the first tab in the tickets view*/
	if(jQuery("#tickets-tab-1").hasClass("pending")){
		get_tickets("#tickets-tab-1");
	}	
	/**Do AJAX calls for filtering tickets on click of any of the tabs**/
	jQuery( "#ticket-tabs li a" ).click(function() {
		get_tickets( jQuery(this).attr('href'));
	});
	

	
});
