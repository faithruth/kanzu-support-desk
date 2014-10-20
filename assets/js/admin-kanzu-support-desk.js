jQuery( document ).ready(function() {
	jQuery( "#tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
        
	/**For the tickets tabs**/
	jQuery( "#ticket-tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( "#ticket-tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

	/*Switch the active tab depending on what page has been selected*/
	activeTab=0;
	switch(ksd_admin.admin_tab){
		case "ksd-tickets":
			activeTab=1;
		break;
                case "ksd-new-ticket":
			activeTab=2;
		case "ksd-settings":
			activeTab=3;
		break;
		case "ksd-addons":
			activeTab=4;
		break;
		case "ksd-help":
			activeTab=5;
		break;
	}
	jQuery( "#tabs" ).tabs( "option", "active", activeTab );
        
       /**Show update/error/Loading dialog while performing AJAX calls and on completion*/
	var ksd_show_dialog = function(dialog_type,message){           
            message = message || "Loading...";//Set default message
            jQuery('.'+dialog_type).html(message);//Set the message
            jQuery('.'+dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
        };
    /**Do an AJAX call to retrieve tickets from the Db**/
	var get_tickets = function( current_tab ) {
	if(jQuery(current_tab).hasClass("pending")){//Check if the tab has been loaded before
			var data = {
				action : 'ksd_filter_tickets',
				ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
				view : current_tab
			};		
			jQuery.post(ksd_admin.ajax_url, data, function(response) {
                            if(jQuery.isArray(JSON.parse(response))){
				jQuery.each( JSON.parse(response), function( key, value ) {
					rws = 	'<div id="ticket-list-item" class="ticket_'+value.tkt_id+'">';
					rws += 	'<div class="ticket-info">';
					rws += 	'<input type="checkbox" value="'+value.tkt_id+'" name="ticket_ids[]" id="ticket_checkbox_'+value.tkt_id+'">';
					rws +=	'<span class="subject"><a href="'+ksd_admin.ksd_tickets_url+'&ticket='+value.tkt_id+'&action=edit">'+value.tkt_title+'</a></span>';
					rws += 	'<span class="customer_name">'+value.tkt_logged_by+'</span>';
					rws += 	'<span class="ticket-source">'+value.tkt_channel+'</span>';
					rws += 	'<span class="ticket-time">'+value.tkt_time_logged+'</span>';
					rws += 	'</div>';
					rws += 	'<div class="ticket-actions" id="tkt_'+value.tkt_id+'">';
                                        rws += 	'<a href="#" class="edit" id="tkt_'+value.tkt_id+'">Edit</a> | ';
					rws += 	'<a href="#" class="trash" id="tkt_'+value.tkt_id+'">Trash</a> | ';
					rws += 	'<a href="#" id="tkt_'+value.tkt_id+'" class="change_status">Change Status</a> | ';
					rws += 	'<a href="#" id="tkt_'+value.tkt_id+'" class="assign_to">Assign To</a>';
					rws += 	ksd_admin.ksd_agents_list;
					rws += 	'<ul class="status hidden"><li>OPEN</li><li>ASSIGNED</li><li>PENDING</li><li>RESOLVED</li></ul>';
					rws += 	'</div>';
					rws +=  '</div>';
					jQuery(current_tab+' #ticket-list').append( rws);                                            
				});
				/**Add class .alternate to every other row in the tickets table.*/
				jQuery("#ticket-list div#ticket-list-item").filter(':even').addClass("alternate");
                                }
                            else{
                               jQuery(current_tab+' #select-all-tickets').remove();  
                               jQuery(current_tab+' #ticket-list').addClass("empty").append(JSON.parse(response));   
                            }
                           jQuery(current_tab).removeClass("pending");
			});
		}
	};
        
        /*Get URL parameters*/
        jQuery.urlParam = function(name){
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results==null){
               return null;
            }
            else{
               return results[1] || 0;
            }
        }
	
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
	jQuery( "#ticket-tabs #tkt_chkbx_all" ).on( "click", function() {
		//TODO:Show all options
		if ( jQuery(this).prop('checked') === true){
			jQuery("#tkt_all_options").removeClass("ticket-actions");
			jQuery('input:checkbox').not(this).prop('checked', this.checked);
		}else{
			jQuery("#tkt_all_options").addClass("ticket-actions");
			jQuery('input:checkbox').not(this).prop('checked', this.checked);
		}
		
	});

	/**AJAX: Delete a ticket **/
	jQuery("#ticket-tabs").on('click','.ticket-actions a.trash',function(event) {
            event.preventDefault();
             var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
             jQuery( "#delete-dialog" ).dialog({
                modal: true,
                buttons: {
                    Yes : function() {
                            jQuery( this ).dialog( "close" );
                            ksd_show_dialog("loading");                           
                            jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_delete_ticket',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
							tkt_id : tkt_id
						}, 
				function(response) {
                                    jQuery('#ticket-list .ticket_'+tkt_id).remove();
                                    ksd_show_dialog("success",JSON.parse(response));  				                                
				});	
                    },                           
                    No : function() {
                    jQuery( this ).dialog( "close" );
                    }               
                }
            });	
	});	

        /**Hide/Show the change ticket options on click of a ticket's 'change status' item**/
	jQuery("#ticket-tabs").on('click','.ticket-actions a.change_status',function(event) {
		event.preventDefault();//Important otherwise the page skips around
		var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
		jQuery(".ticket_"+tkt_id+" ul.status").toggleClass("hidden");
	});
	/**AJAX: Send the AJAX request when a new status is chosen**/
	jQuery("#ticket-tabs").on('click','.ticket-actions ul.status li',function() {
		ksd_show_dialog("loading");
                var tkt_id =jQuery(this).parent().parent().attr("id").replace("tkt_","");//Get the ticket ID
		var tkt_status = jQuery(this).text();
		jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_change_status',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
							tkt_id : tkt_id,
							tkt_status : tkt_status
						}, 
				function(response) {	
                                    ksd_show_dialog("success",JSON.parse(response));				                            
				});		
	});
        
        /**AJAX: Send a single ticket response when it's been typed and 'Reply' is hit**/
       //@TODO Fix wp_editor bug that returns stale data with each submission      
        jQuery('form#edit-ticket').submit( function(e){
            e.preventDefault();          
            jQuery.post(	ksd_admin.ajax_url, 
                                jQuery(this).serialize(), //The action, nonce and TicketID are hidden fields in the form
		function(response) {//@TODO Check for error 	
                    jQuery("#ticket-replies").append("<div class='ticket-reply'>"+JSON.parse(response)+"</div>");	
                    //Clear the reply field
                    jQuery("textarea[name=ksd_ticket_reply]").val(" ");                    
            });
        });
        
         /**Hide/Show the assign to options on click of a ticket's 'Assign To' item**/
	jQuery("#ticket-tabs").on('click','.ticket-actions a.assign_to',function(event) {
		event.preventDefault();//Important otherwise the page skips around
		var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
		jQuery(".ticket_"+tkt_id+" ul.assign_to").toggleClass("hidden");
	});
        /**AJAX: Send the AJAX request to change ticket owner on selecting new person to 'Assign to'**/
	jQuery("#ticket-tabs").on('click','.ticket-actions ul.assign_to li',function() {
                ksd_show_dialog("loading");
		var tkt_id =jQuery(this).parent().parent().attr("id").replace("tkt_","");//Get the ticket ID
		var assign_assigned_to = jQuery(this).attr("id");
		jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_assign_to',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
							tkt_id : tkt_id,
                                                        ksd_current_user_id : ksd_admin.ksd_current_user_id,
							tkt_assign_assigned_to : assign_assigned_to
						}, 
				function(response) {	
                                ksd_show_dialog("success",JSON.parse(response));
				});		
	});
        
	/**AJAX: Retrieve summary statistics for the dashboard**/
         if(jQuery("ul.dashboard-statistics-summary").hasClass("pending")){  
             		jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_get_dashboard_summary_stats',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce					
						}, 
				function(response) {	
                                   jQuery("ul.dashboard-statistics-summary").removeClass("pending");
                                    var raw_response = JSON.parse(response);
                                    var the_summary_stats = "";
                                    the_summary_stats+= "<li>"+raw_response.open_tickets[0].open_tickets+" <span>Total Open Tickets</span></li>";
                                    the_summary_stats+= "<li>"+raw_response.unassigned_tickets[0].unassigned_tickets+" <span>Unassigned Tickets</span></li>";
                                    the_summary_stats+= "<li>"+raw_response.average_response_time+" <span>Avg. Response Time</span></li>";
                                    jQuery("ul.dashboard-statistics-summary").html(the_summary_stats);                                   
				});	
             
             
         }
        
	/**Change the title onclick of a side navigation tab*/
	jQuery( "#tabs .main-nav li a" ).click(function() {
		jQuery('h2.admin-ksd-title').html(jQuery(this).attr('href').replace("#",""));
	});
       
        /**While working on a single ticket, switch between reply/forward and Add note modes**/
        jQuery('ul.edit-ticket-options li').click(function(e){
        jQuery('ul.edit-ticket-options li').removeClass('selected');//make all tabs inactive        
        jQuery(this).addClass('selected');    //then make the clicked tab active
        });
        
        /**AJAX: In single ticket view mode, get the current ticket's description, sender and subject*/
        if(jQuery("#ksd-single-ticket .description").hasClass("pending")){             
            jQuery.post(    ksd_admin.ajax_url, 
                            { 	action : 'ksd_get_single_ticket',
				ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
				tkt_id : jQuery.urlParam('ticket')//We get the ticket ID from the URL
                            }, 
			function(response) {
                            the_ticket = JSON.parse(response);
                            jQuery("#ksd-single-ticket .author_and_subject").html(the_ticket.tkt_logged_by+"-"+the_ticket.tkt_subject);
                            jQuery("#ksd-single-ticket .description").removeClass("pending").html(the_ticket.tkt_description);
                            jQuery("#ticket-replies").html("Any minute now...") ; //@TODO Add this to Localization                         
                            //Make the 'Back' button visible
                            jQuery(".top-nav li.back").removeClass("hidden");
                            
                            //Now get the responses. For cleaner code and to remove reptition in the returned results, we use multiple
                            //queries instead of a JOIN. The impact on speed is negligible
                            jQuery.post(    ksd_admin.ajax_url, 
                            { 	action : 'ksd_get_ticket_replies',
				ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
				tkt_id : jQuery.urlParam('ticket')//We get the ticket ID from the URL
                            }, 
                                function(the_replies) {       
                                    jQuery("#ticket-replies").html("") ; //Clear the replies div
                                    jQuery.each( JSON.parse(the_replies), function( key, value ) {
                                    jQuery("#ticket-replies").append("<div class='ticket-reply'>"+value.rep_message+"</div>");                                    
                                    });
                                    //Toggle the color of the reply background
                                    jQuery("#ticket-replies div.ticket-reply").filter(':even').addClass("alternate");
                                });
                        });	
        }
        
        /**Validate New Tickets**/
        //@TODO Add server side validation too
        jQuery("form#new-ticket").validate();
        
        /**Toggle the form field values for new tickets on click**/
        function toggle_form_field_input ( event ){
                if(jQuery(this).val() === event.data.old_value){
                    jQuery(this).val(event.data.new_value);                    
            }      
        };
        //The fields
        var new_form_fields = {
            "tkt_subject" : "Subject",
            "customer_name" : "Customer Name",
            "customer_email" : "Customer Email"
        };
        //Attach events to the fields @TODO Modify this to handle localization
        jQuery.each( new_form_fields, function( field_name, form_value ) {
            jQuery('input[name='+field_name+']').on('focus',{
                                                        old_value: form_value,
                                                        new_value: ""
                                                     }, toggle_form_field_input);
            jQuery('input[name='+field_name+']').on('blur',{
                                                        old_value: "",
                                                        new_value: form_value
                                                     }, toggle_form_field_input);
        });
});

/**The dashboard charts. These have their own onLoad method so they can't be run inside jQuery( document ).ready({});**/
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawDashboardGraph);
      function drawDashboardGraph() {	
				jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_dashboard_ticket_volume',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce
						}, 
				function(response) {	              		
                            var data =  google.visualization.arrayToDataTable(JSON.parse(response));
        var options = {
          title: 'Incoming Tickets'
        };
        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
				});
                            }
