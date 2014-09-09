jQuery( document ).ready(function() {
	jQuery( "#tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
	
        //tinymce.execCommand('mceAddControl',true,'ticket_description');
        
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
				action : 'ksd_filter_tickets',
				ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
				view : current_tab
			};		
			jQuery.post(ksd_admin.ajax_url, data, function(response) {	
				jQuery.each( JSON.parse(response), function( key, value ) {
					jQuery(current_tab+' #ticket-list').append('<div id="ticket-list-item"><div class="ticket-info"><input type="checkbox" value="'+value.tkt_id+'" name="ticket_ids[]" id="ticket_checkbox_'+value.tkt_id+'"><span class="customer_name">'+value.tkt_logged_by+'</span><span class="subject">'+value.tkt_title+'</span><span class="description">-'+value.tkt_description+'</span><span class="ticket-time">'+value.tkt_time_logged+'</span></div><div class="ticket-actions"><a href="'+ksd_admin.ksd_tickets_url+'&action=edit&tkt_id='+value.tkt_id+'">Edit</a> | <a href="'+ksd_admin.ksd_tickets_url+'&action=trash&tkt_id='+value.tkt_id+'" class="trash" id="tkt_'+value.tkt_id+'">Trash</a> | <a href="'+ksd_admin.ksd_tickets_url+'&action=change_status&tkt_id='+value.tkt_id+'">Change Status</a> | <a href="'+ksd_admin.ksd_tickets_url+'&action=assign_to&tkt_id='+value.tkt_id+'">Assign To</a></div></div>');                                            
				});				
				jQuery(current_tab).removeClass("pending");
				/**Add class .alternate to every other row in the tickets table.*/
				jQuery("#ticket-list div#ticket-list-item").filter(':even').addClass("alternate");
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
	
	/**AJAX: Delete a ticket **/
	jQuery( ".ticket-actions a" ).click(function(){
		console.log("Deleted Now");
		var tkt_id= jQuery(this).attr('id').replace("tkt_id_",""); //Get the ticket ID
		jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_delete_ticket',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
							tkt_id : tkt_id
						}, 
				function(response) {	
				console.log(JSON.parse(response) );                                             
				});		
	});		 
	
	/**Change the title onclick of a side navigation tab*/
	jQuery( "#tabs .main-nav li a" ).click(function() {
		jQuery('h2.admin-ksd-title').html(jQuery(this).attr('href').replace("#",""));
	});
	
});
