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
	/**Do AJAX calls for filtering tickets**/
	jQuery( "#ticket-tabs li a" ).click(function() {
		var data = {
			action : 'ksd_admin_ajax_action',
			ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
			view : jQuery(this).attr('href')
		};		
		jQuery.post(ksd_admin.ajax_url, data, function(response) {
			//alert('Got this from the server: ' + response);
			console.log(JSON.parse(response));
			jQuery("#tickets-tab-2").html("Db response received");
		});
	});
	
});
