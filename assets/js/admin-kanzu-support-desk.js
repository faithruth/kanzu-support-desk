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
					rws = 		'<div id="ticket-list-item" class="ticket_'+value.tkt_id+'">';
					rws = rws + 	'<div class="ticket-info">';
					rws = rws + 	'<input type="checkbox" value="'+value.tkt_id+'" name="ticket_ids[]" id="ticket_checkbox_'+value.tkt_id+'">';
					rws = rws + 	'<span class="customer_name">'+value.tkt_logged_by+'</span>';
					rws = rws + 	'<span class="subject">'+value.tkt_title+'</span>';
					rws = rws + 	'<span class="description">-'+value.tkt_description+'</span>';
					rws = rws + 	'<span class="ticket-time">'+value.tkt_time_logged+'</span>';
					rws = rws + 	'</div>';
					rws = rws + 	'<div class="ticket-actions" id="tkt_'+value.tkt_id+'">';
					rws = rws + 	'<a href="#" class="trash" id="tkt_'+value.tkt_id+'">Trash</a> | ';
					rws = rws + 	'<a href="#" id="tkt_'+value.tkt_id+'" class="change_status">Change Status</a> | ';
					rws = rws + 	'<a href="#" id="tkt_'+value.tkt_id+'" class="assign_to">Assign To</a>';
					rws = rws + 	ksd_admin.ksd_agents_list;
					rws = rws + 	'<ul class="status hidden"><li>OPEN</li><li>ASSIGNED</li><li>PENDING</li><li>RESOLVED</li></ul>';
					rws = rws + 	'</div>';
					rws = rws + '</div>';
					jQuery(current_tab+' #ticket-list').append( rws);                                            
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
	
	jQuery( "input[type=checkbox]" ).on( "click", function() {
	//alert("Checked");
		
	});
	
	/*All check box.
	* control_id: tkt_chkbx_all
	*/
	jQuery( "#tkt_chkbx_all" ).on( "click", function() {
		//TODO:Show all options
		if ( jQuery(this).prop('checked') == true){
			jQuery("#tkt_all_options").removeClass("ticket-actions");
			jQuery('input:checkbox').not(this).prop('checked', this.checked);
		}else{
			jQuery("#tkt_all_options").addClass("ticket-actions");
			jQuery('input:checkbox').not(this).prop('checked', this.checked);
		}
		
	});

	/**AJAX: Delete a ticket **/
	jQuery("#ticket-list").on('click','.ticket-actions a.trash',function() {
		var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
		jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_delete_ticket',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
							tkt_id : tkt_id
						}, 
				function(response) {	
				console.log(JSON.parse(response) );                                             
				});		
	});	

        /**Hide/Show the change ticket options on click of a ticket's 'change status' item**/
	jQuery("#ticket-list").on('click','.ticket-actions a.change_status',function(event) {
		event.preventDefault();//Important otherwise the page skips around
		var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
		jQuery(".ticket_"+tkt_id+" ul.status").toggleClass("hidden");
	});
	/**AJAX: Send the AJAX request when a new status is chosen**/
	jQuery("#ticket-list").on('click','.ticket-actions ul.status li',function() {
		var tkt_id =jQuery(this).parent().parent().attr("id").replace("tkt_","");//Get the ticket ID
		var tkt_status = jQuery(this).text();
		jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_change_status',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
							tkt_id : tkt_id,
							tkt_status : tkt_status
						}, 
				function(response) {	
				console.log(JSON.parse(response) );                                             
				});		
	});
        
         /**Hide/Show the assign to options on click of a ticket's 'Assign To' item**/
	jQuery("#ticket-list").on('click','.ticket-actions a.assign_to',function(event) {
		event.preventDefault();//Important otherwise the page skips around
		var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
		jQuery(".ticket_"+tkt_id+" ul.assign_to").toggleClass("hidden");
	});
	
	/**Change the title onclick of a side navigation tab*/
	jQuery( "#tabs .main-nav li a" ).click(function() {
		jQuery('h2.admin-ksd-title').html(jQuery(this).attr('href').replace("#",""));
	});
	
});
