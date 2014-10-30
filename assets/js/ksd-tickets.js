/*Load jQuery before this file.
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * */
KSDTickets = function(){
    _this = this;
    this.init = function(){

        this.uiTabs();
        this.uiListTickets();
        this.newTicket();
        this.editTicketForm();

        this.deleteTicket();
        this.changeTicketStatus();
        this.uiSingleTicketView();
        
        //Search
        this.TicketSearch();
        
        //Pagination
        this.TicketPagination();
        
        //Page Refresh
        this.refreshPage();
    }

    this.getTickets = function( current_tab, search, limit, offset ){
        
        //Default values
        if( typeof(search)=== 'undefined' )  search = "";
        if( typeof(limit) === 'undefined' )  limit = 5;
        if( typeof(offset)=== 'undefined' )  offset = 0;
        
                    if(jQuery(current_tab).hasClass("pending"))//Check if the tab has been loaded before
                    {
                        var data = {
                                action : 'ksd_filter_tickets',
                                ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
                                view :  current_tab,
                                search: search,
                                limit:  limit,
                                offset: offset
                        };		

                        
                        jQuery.post(ksd_admin.ajax_url, data, function(response) {
                            if(jQuery.isArray(JSON.parse(response))){
                                    
                                
                                   //  jQuery(current_tab+' #ticket-list').remove();
                                     
                                   tab_id = current_tab.replace("#tickets-tab-","");
                                     rws2 = "";
                                     rws2 += '<div class="ksd-row-all-hide" id="ksd_row_all_'+tab_id+'">';
                                     rws2 +=    '<div  id="tkt_all_options"> \
                                                    <a href="#" class="trash" id="#">Trash All</a> | \
                                                    <a href="#" id="#" class="change_status">Change All Statuses</a> | \
                                                    <a href="#" id="#" class="assign_to">Assign All To</a> \
                                                </div>' ;
                                     rws2 += '</div>';
                                     
                                     jQuery(current_tab+' #ticket-list').html( rws2);
                                     
                                    jQuery.each( JSON.parse(response)[0], function( key, value ) {
                                            /*
                                            rws = 	'<div id="ticket-list-item" class="ticket_'+value.tkt_id+'">';
                                            rws += 	'<div class="ticket-info">';
                                            rws += 	'<input type="checkbox" value="'+value.tkt_id+'" name="ticket_ids[]" id="ticket_checkbox_'+value.tkt_id+'">';

                                            rws +=	'<span class="subject"><a href="'+ksd_admin.ksd_tickets_url+'&ticket='+value.tkt_id+'&action=edit">'+value.tkt_subject+'</a></span> - ';
                                            rws += 	'<span class="">'+value.tkt_description+'</span>';
                                            rws += 	'<span class="customer_name"><a href="'+ksd_admin.ksd_tickets_url+'&ticket='+value.tkt_id+'&action=edit">'+value.tkt_logged_by+'</a></span>';
                                            rws += 	'<span class="ticket-time">'+value.tkt_time_logged+'</span>';
                                            rws += 	'</div>';
                                            rws += 	'<div class="ticket-actions" id="tkt_'+value.tkt_id+'">';
                                            rws += 	'<a href="#" class="trash" id="tkt_'+value.tkt_id+'">Trash</a> | ';
                                            rws += 	'<a href="#" id="tkt_'+value.tkt_id+'" class="change_status">Change Status</a> | ';
                                            rws += 	'<a href="#" id="tkt_'+value.tkt_id+'" class="assign_to">Assign To</a>';
                                            rws += 	ksd_admin.ksd_agents_list;
                                            rws += 	'<ul class="status hidden"><li>OPEN</li><li>ASSIGNED</li><li>PENDING</li><li>RESOLVED</li></ul>';
                                            rws += 	'</div>';
                                            rws +=      '</div>';
                                            jQuery(current_tab+' #ticket-list2').append( rws);
                                            */

                                            /*@todo: for testing template 2 */
                                            rws2 = '<div class="ksd-row-data ticket-list-item" id="ksd_tkt_id_'+value.tkt_id+'">';
                                            rws2 += 	'<div class="ticket-info">';
                                            rws2 += 	'<input type="checkbox" value="'+value.tkt_id+'" name="ticket_ids[]" id="ticket_checkbox_'+value.tkt_id+'">';
                                            rws2 += 	'<span class="customer_name"><a href="'+ksd_admin.ksd_tickets_url+'&ticket='+value.tkt_id+'&action=edit">'+value.tkt_logged_by+'</a></span>';
                                            rws2 +=	'<span class="subject-and-message-excerpt"><a href="'+ksd_admin.ksd_tickets_url+'&ticket='+value.tkt_id+'&action=edit">'+value.tkt_subject;
                                            rws2 += 	' - '+value.tkt_message_excerpt+'</span></a>';                                            
                                            rws2 += 	'<span class="ticket-time">'+value.tkt_time_logged+'</span>';
                                            rws2 += 	'</div>';
                                            rws2 += 	'<div class="ticket-actions" id="tkt_'+value.tkt_id+'">';
                                            rws2 += 	'<a href="#" class="trash" id="tkt_'+value.tkt_id+'">Trash</a> | ';
                                            rws2 += 	'<a href="#" id="tkt_'+value.tkt_id+'" class="change_status">Change Status</a> | ';
                                            rws2 += 	'<a href="#" id="tkt_'+value.tkt_id+'" class="assign_to">Assign To</a>';
                                            rws2 += 	ksd_admin.ksd_agents_list;
                                            rws2 += 	'<ul class="status hidden"><li>OPEN</li><li>ASSIGNED</li><li>PENDING</li><li>RESOLVED</li></ul>';
                                            rws2 += 	'</div>';
                                            rws2 +=     '</div>';
                                            jQuery(current_tab+' #ticket-list').append( rws2);
                                            
                                            

                                    });//eof:jQUery.each

                                    /**Add class .alternate to every other row in the tickets table.*/
                                    //jQuery("#ticket-list div#ticket-list-item").filter(':even').addClass("alternate");
                                    jQuery("#ticket-list .ksd-row-data").filter(':even').addClass("alternate");
                                    
                                    RowCtrlEffects();
                            }
                            else{
                              //  jQuery(current_tab+' #ticket-list tr').remove();

                                     /*rws2 = ' \
                                              <tr class="ksd-row-nodata"> \
                                              <td colspan="5">\
                                              ' + JSON.parse(response) + ' \
                                              </td> \
                                              </tr>';*/
                                     
                                jQuery(current_tab+' #select-all-tickets').remove();  
                                jQuery(current_tab+' #ticket-list').addClass("empty").html(JSON.parse(response));
                            }//eof:if

                            jQuery(current_tab).removeClass("pending");
                            
                            
                            //Add Navigation
                            var tab_id = current_tab.replace("#tickets-tab-","");
                            var total_rows = JSON.parse(response)[1];
                            var currentpage = offset+1; 
                            _loadTicketPagination(tab_id, currentpage, total_rows, limit);
                            
                           });//eof:jQuery.post	
                    }//eof:if                
    }
	/*
	 * List all tickets
	 */
	this.uiListTickets = function(){
		//---------------------------------------------------------------------------------
		/*All check box.
		* control_id: tkt_chkbx_all
		*/
		jQuery( "#ticket-tabs .tkt_chkbx_all" ).on( "click", function() {
			//TODO:Show all options
			if ( jQuery(this).prop('checked') === true){
				jQuery("#tkt_all_options").removeClass("ticket-actions");
				jQuery('input:checkbox').not(this).prop('checked', this.checked);
                                
                                //
                                tab_id=jQuery(this).attr("id").replace("tkt_chkbx_all_","");                                
                                jQuery("#ksd_row_all_" + tab_id ).removeClass('ksd-row-all-hide').addClass("ksd-row-all-show");
                                
                                
			}else{
				jQuery("#tkt_all_options").addClass("ticket-actions");
				jQuery('input:checkbox').not(this).prop('checked', this.checked);
                                
                                tab_id=jQuery(this).attr("id").replace("tkt_chkbx_all_","");
                                jQuery("#ksd_row_all_" + tab_id ).removeClass('ksd-row-all-show').addClass("ksd-row-all-hide");
			}
			
		});
		


		//---------------------------------------------------------------------------------
        /**Hide/Show the change ticket options on click of a ticket's 'change status' item**/
	jQuery("#ticket-tabs").on('click','.ticket-actions a.change_status',function(event) {
		event.preventDefault();//Important otherwise the page skips around
		var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
		jQuery("#tkt_"+tkt_id+" ul.status").toggleClass("hidden");
                
	});
	
	//---------------------------------------------------------------------------------
	/**AJAX: Send the AJAX request when a new status is chosen**/
	jQuery("#ticket-tabs").on('click','.ticket-actions ul.status li',function() {
		KSDUtils.showDialog("loading");
                var tkt_id =jQuery(this).parent().parent().attr("id").replace("tkt_","");//Get the ticket ID
		var tkt_status = jQuery(this).text();
		jQuery.post(	ksd_admin.ajax_url, 
						{ 	action : 'ksd_change_status',
							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
							tkt_id : tkt_id,
							tkt_status : tkt_status
						}, 
				function(response) {	
                                    
							KSDUtils.showDialog("success",JSON.parse(response));
                                                        jQuery('#ticket-list div#ksd_tkt_id_'+tkt_id).remove();
				});		
	});       

        
      //---------------------------------------------------------------------------------
        /**Hide/Show the assign to options on click of a ticket's 'Assign To' item**/
    	jQuery("#ticket-tabs").on('click','.ticket-actions a.assign_to',function(event) {
    		event.preventDefault();//Important otherwise the page skips around
    		var tkt_id= jQuery(this).parent().attr('id').replace("tkt_",""); //Get the ticket ID
    		jQuery("#tkt_"+tkt_id+" ul.assign_to2").toggleClass("hidden");
                
    	});
    	
    	//---------------------------------------------------------------------------------
            /**AJAX: Send the AJAX request to change ticket owner on selecting new person to 'Assign to'**/
    	jQuery("#ticket-tabs").on('click','.ticket-actions ul.assign_to2 li',function() {
            console.log("ASSIGN_TO 2");
                KSDUtils.showDialog("loading");
    		var tkt_id =jQuery(this).parent().parent().attr("id").replace("tkt_","");//Get the ticket ID
    		var assign_assigned_to = jQuery(this).attr("id");
                console.log("DEBUG:" + " tkt_id:" + tkt_id + " assign_assigned_to:" + assign_assigned_to);
    		jQuery.post(	ksd_admin.ajax_url, 
    						{ 	action : 'ksd_assign_to',
    							ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
    							tkt_id : tkt_id,
                                                            ksd_current_user_id : ksd_admin.ksd_current_user_id,
    							tkt_assign_assigned_to : assign_assigned_to
    						}, 
    				function(response) {	
                                    KSDUtils.showDialog("success",JSON.parse(response));
    				});		
    	});
        
	}//eof:
	
        this.deleteTicket = function(){
		//---------------------------------------------------------------------------------
		/**AJAX: Delete a ticket **/
		jQuery("#ticket-tabs").on('click','.ticket-actions a.trash',function(event) {
	            event.preventDefault();
                    
	             var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
                     console.log("tkt_id:" + tkt_id);
	             jQuery( "#delete-dialog" ).dialog({
	                modal: true,
	                buttons: {
	                    Yes : function() {
	                            jQuery( this ).dialog( "close" );
	                            KSDUtils.showDialog("loading");                           
	                            jQuery.post(	ksd_admin.ajax_url, 
							{ 	action : 'ksd_delete_ticket',
								ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
								tkt_id : tkt_id
							}, 
					function(response) {
	                                    jQuery('#ticket-list div#ksd_tkt_id_'+tkt_id).remove();
	                                    KSDUtils.showDialog("success",JSON.parse(response));  				                                
					});	
	                    },                           
	                    No : function() {
	                    jQuery( this ).dialog( "close" );
	                    }               
	                }
	            });	
                    jQuery("div.ui-widget-overlay").remove();
                    
                    
		});	
        }
        this.editTicketForm = function(){
            //--------------------------------------------------------------------------------------
            /**AJAX: Send a single ticket response when it's been typed and 'Reply' is hit**/
           //@TODO Fix wp_editor bug that returns stale data with each submission      
            jQuery('form#edit-ticket').submit( function(e){
                e.preventDefault(); 
                var action = jQuery("input[name=action]").attr("value");
                KSDUtils.showDialog("loading");//Show a dialog message
                jQuery.post(	ksd_admin.ajax_url, 
                                    jQuery(this).serialize(), //The action, nonce and TicketID are hidden fields in the form
                    function(response) {//@TODO Check for errors 
                        switch(action){
                            case "ksd_update_private_note":
                               KSDUtils.showDialog("success",JSON.parse(response));
                            break;
                            default:
                                jQuery("#ticket-replies").append("<div class='ticket-reply'>"+JSON.parse(response)+"</div>");	
                                 //Clear the reply field
                                 jQuery("textarea[name=ksd_ticket_reply]").val(" ");      
                        }
                });
            });
            
        /**While working on a single ticket, switch between reply/forward and Add note modes
         * We define the action (used by AJAX) and change the submit button's text
         * @TODO Move submitButtonText to PHP so it can be localized**/
         jQuery('ul.edit-ticket-options li a').click(function(e){
             e.preventDefault();
             action = jQuery(this).attr("href").replace("#","");
          switch(action){
              case "forward_ticket":
                 submitButtonText = "Forward";
                  break;
              case "update_private_note":
                  submitButtonText = "Update Note";
              break;
          default:
                submitButtonText   = "Reply";
          }
          jQuery("input[name=action]").attr("value","ksd_"+action);
          jQuery("input[name=edit-ticket]").attr("value",submitButtonText);
         });

        /**For the Reply/Forward/Private Note tabs that appear when viewing a single ticket.*/
        //First check if the element exists
        if (jQuery("ul.edit-ticket-options").length){
            jQuery("#edit-ticket-tabs").tabs();            

        }

        
        }	
	
        
        
	this.newTicket = function(){
	       
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
        }//eof:if(jQuery("#ksd-single-ticket .description").hasClass("pending")){ 
        
        
            /*On focus, Toggle customer name, email and subject */
            _toggleFieldValues();
        
        
            /**Validate New Tickets**/
            //@TODO Add server side validation too
            jQuery("form#new-ticket").validate();

		
	}//eof:newTicket()
        
        
 
        this.uiTabs = function(){

            /**For the tickets tabs**/
            jQuery( "#ticket-tabs").tabs();

            /*Switch the active tab depending on what page has been selected*/
            activeTab=0;        
            switch(ksd_admin.admin_tab){
                    case "ksd-tickets":
                            activeTab=1;
                    break;
                    case "ksd-new-ticket":
                            activeTab=2;
                    break;        
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
            //Set the title
            jQuery('.admin-ksd-title h2').html(ksd_admin.admin_tab.replace("ksd-","").replace("-"," "));

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



            /**Change the title onclick of a side navigation tab*/
            jQuery( "#tabs .ksd-main-nav li a" ).click(function() {
                    jQuery('.admin-ksd-title h2').html(jQuery(this).attr('href').replace("#","").replace("_"," "));//Remove the hashtag, replace _ with a space
            });
            
            /**Pre-populate the first tab in the tickets view*/
            if(jQuery("#tickets-tab-1").hasClass("pending")){
                    _this.getTickets("#tickets-tab-1");
            }	
            /**Do AJAX calls for filtering tickets on click of any of the tabs**/
            jQuery( "#ticket-tabs li a" ).click(function() {
                    _this.getTickets( jQuery(this).attr('href') );
            });	

            jQuery( "input[type=checkbox]" ).on( "click", function() {
            //alert("Checked");

            });
        
        
        }
        
        /*
         * Change ticket status
         */
        this.changeTicketStatus = function(){
            /**AJAX: Send the AJAX request when a new status is chosen**/
            jQuery("#ticket-tabs").on('click','.ticket-actions ul.status li',function() {
                    KSDUtils.showDialog("loading");
                    var tkt_id =jQuery(this).parent().parent().attr("id").replace("tkt_","");//Get the ticket ID
                    var tkt_status = jQuery(this).text();
                    jQuery.post(	ksd_admin.ajax_url, 
                                                    { 	action : 'ksd_change_status',
                                                            ksd_admin_nonce : ksd_admin.ksd_admin_nonce,
                                                            tkt_id : tkt_id,
                                                            tkt_status : tkt_status
                                                    }, 
                                    function(response) {	
                                        KSDUtils.showDialog("success",JSON.parse(response));				                            
                                    });		
            });
                
            /**Hide/Show the change ticket options on click of a ticket's 'change status' item**/
            jQuery("#ticket-tabs").on('click','.ticket-actions a.change_status',function(event) {
                    event.preventDefault();//Important otherwise the page skips around
                    var tkt_id= jQuery(this).attr('id').replace("tkt_",""); //Get the ticket ID
                    jQuery(".ticket_"+tkt_id+" ul.status").toggleClass("hidden");
            });

        
        }
        
        
        this.uiSingleTicketView = function(){
        /**AJAX: In single ticket view mode, get the current ticket's description, sender and subject and any private notes*/
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
                             jQuery("#ksd-single-ticket textarea[name=ksd_ticket_private_note]").val(the_ticket.tkt_private_notes);
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
        }//eof:this.uiSingleTicketView
        
        
        
        
        
        _toggleFieldValues = function(){

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
                "customer_email" : "Customer Email"/*,
                "ksd_tkt_search_input_1" : "Search...",
                "ksd_tkt_search_input_2" : "Search...",
                "ksd_tkt_search_input_3" : "Search...",
                "ksd_tkt_search_input_4" : "Search...",
                "ksd_tkt_search_input_5" : "Search..."*/
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
        }
        
        this.TicketPagination = function(){
            
            
            //start:Limit
            //Removed mouseout. Was sending multiple AJAX calls at the same time. 
            jQuery(".ksd-pagination-limit").bind("mouseleave", function(){
                var limit = jQuery(this).val();
                
                var tab_id = jQuery(this).attr("id").replace("ksd_pagination_limit_","");
                var search_text = jQuery("input[name=ksd_tkt_search_input_"+tab_id+"]").val();
                var tab_id_name="#tickets-tab-"+tab_id;
                //alert("limit:" + limit + " search:" + search_text);
                jQuery(tab_id_name).addClass("pending");
                 _this.getTickets( "#tickets-tab-"+tab_id, search_text, limit );
                
            });
            
            
            jQuery(".ksd-pagination-limit").bind("keypress", function(e){
                if(e.keyCode==13){ //Enter key
                 var limit = jQuery(this).val();
                
                var tab_id = jQuery(this).attr("id").replace("ksd_pagination_limit_","");
                var search_text = jQuery("input[name=ksd_tkt_search_input_"+tab_id+"]").val();
                var tab_id_name="#tickets-tab-"+tab_id;
                //alert("limit:" + limit + " search:" + search_text);
                jQuery(tab_id_name).addClass("pending");
                 _this.getTickets( "#tickets-tab-"+tab_id, search_text, limit );                   
                }

                
            });
            //End:Limit
            
        }
        
        //AJAX:: When the refresh button is hit
        this.refreshPage = function() {
            jQuery('.ksd-ticket-refresh').click(function(){
                var limit = jQuery(".ksd-pagination-limit").val();                
                var tab_id = jQuery(".ksd-pagination-limit").attr("id").replace("ksd_pagination_limit_","");
                var search_text = jQuery("input[name=ksd_tkt_search_input_"+tab_id+"]").val();
                var tab_id_name="#tickets-tab-"+tab_id;
                //alert("limit:" + limit + " search:" + search_text);
                jQuery(tab_id_name).addClass("pending");
                 _this.getTickets( "#tickets-tab-"+tab_id, search_text, limit );                   
                });
        }
        
        this.TicketSearch = function(){

            jQuery(".ksd-tkt-search-btn").click(function(){
                var tab_id = jQuery(this).attr("id").replace("ksd_tkt_search_btn_","");
                var search_text = jQuery("input[name=ksd_tkt_search_input_"+tab_id+"]").val();
                var tab_id_name="#tickets-tab-"+tab_id;
                
                //get pagination
                var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();
                
                jQuery(tab_id_name).addClass("pending");
                 _this.getTickets( "#tickets-tab-"+tab_id, search_text, limit);
                 
            });
            
            jQuery(".ksd_tkt_search_input").bind("keypress",function(e){
                if(e.keyCode==13){ //Enter key
                    var tab_id = jQuery(this).attr("name").replace("ksd_tkt_search_input_","");
                    var search_text = jQuery("input[name=ksd_tkt_search_input_"+tab_id+"]").val();
                    var tab_id_name="#tickets-tab-"+tab_id;
                    //get pagination
                    var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();

                    jQuery(tab_id_name).addClass("pending");
                     _this.getTickets( "#tickets-tab-"+tab_id, search_text, limit);
                }
            });
            
        }
        
        
        _getTabId = function(tab_id){
            var tab_id_name="#tickets-tab-"+tab_id;
            return tab_id_name;
        }
        /*Add effects to ticket row
         * Add border to the ksd-row-ctrl table row
         * */
        RowCtrlEffects = function(){

            jQuery(".ksd-row-ctrl").bind("hover mouseover focus",function(){
            
                var id = jQuery(this).attr("id");
                var tkt_id = jQuery(this).attr("id").replace("ksd_tkt_ctrl_","");
                jQuery("#ksd_tkt_id_" + tkt_id).addClass("ksd-row-ctrl-hover");
                

            });
            
            jQuery(".ksd-row-ctrl").mouseout(function(){
                var id = jQuery(this).attr("id");
                var tkt_id = jQuery(this).attr("id").replace("ksd_tkt_ctrl_","");
                jQuery("#ksd_tkt_id_" + tkt_id).removeClass("ksd-row-ctrl-hover");
            });


            /*All checkbox**/
            jQuery( "#ticket-tabs .tkt_chkbx_all" ).on( "click", function() {
                    //TODO:Show all options
                    if ( jQuery(this).prop('checked') === true){
                            jQuery("#tkt_all_options").removeClass("ticket-actions");
                            jQuery('input:checkbox').not(this).prop('checked', this.checked);

                            //
                            tab_id=jQuery(this).attr("id").replace("tkt_chkbx_all_","");                                
                            jQuery("#ksd_row_all_" + tab_id ).removeClass('ksd-row-all-hide').addClass("ksd-row-all-show");


                    }else{
                            jQuery("#tkt_all_options").addClass("ticket-actions");
                            jQuery('input:checkbox').not(this).prop('checked', this.checked);

                            tab_id=jQuery(this).attr("id").replace("tkt_chkbx_all_","");
                            jQuery("#ksd_row_all_" + tab_id ).removeClass('ksd-row-all-show').addClass("ksd-row-all-hide");
                    }

            });

        }

        
        /*
         * 
         * @param {type} tab_id
         * @returns {undefined}
         */
        _getCurrentPage = function(tab_id){
            var curpage = jQuery("#ksd_pagination_"+ tab_id + " ul li .current-nav").html();
            //return (KSDUtils.isNumber(curpage)) ? curpage : 1;
            return parseInt(curpage);
        }
        
        
        _getPagLimt = function(tab_id){
            var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();
            return limit;
        }
        
        /**
         * Renders the table pagination
         * 
         * @param {type} tab_id
         * @param {type} current_page
         * @param {type} total_results
         * @param {type} limit
         * @returns {undefined}
         */
        _loadTicketPagination = function( tab_id, current_page, total_results, limit){
                    
                    //@TODO: Why is this coming as o instead of 0.
                    if( total_results == "o" || total_results == "0"  ) return; 
            
                    var pages = (total_results/limit);
                    jQuery("#ksd_pagination_"+ tab_id + " ul li").remove()
                    jQuery("#ksd_pagination_"+ tab_id + " ul li a.current-nav").removeClass("current-nav");
                    
                    jQuery("#ksd_pagination_"+ tab_id + " ul").append('\
                        <li><a rel="external" href="#"><<</a></li>  \
                        <li><a rel="external" href="#"><</a></li>');    
            
                    for (i =0; i < pages; i++){
                        currentclass=(i== current_page-1)?"current-nav" : "";
                        ii=i+1;
                        jQuery("#ksd_pagination_"+ tab_id + " ul").append(' \
                            <li><a rel="external" href="#" class="'+currentclass+'">'+ ii +'</li> \
                        ');
                    }
                    
                    jQuery("#ksd_pagination_"+ tab_id + " ul").append('\
                        <li><a rel="external" href="#">></a></li>  \
                        <li><a rel="external" href="#">>></a></li>');    
            
                    
                    //Attach click events
                    jQuery("#ksd_pagination_"+ tab_id + " ul li a").click(function(){
                        var cpage = jQuery(this).html() ;
                        var current_page = _getCurrentPage(tab_id);
                        var limit = _getPagLimt(tab_id);
                        var pages = Math.ceil(total_results/limit);
                        
                        
                            
                        //console.log( "cpage:" + cpage);
                        //Prev, Next
                        if(cpage == ">" || cpage == "&gt;"){
                            cpage = current_page + 1;
                        }
                        if(cpage == ">>" || cpage=='&gt;&gt;'){
                            cpage = Math.ceil(total_results/limit);
                        }
                        if(cpage == "<" || cpage == '&lt;'){
                            cpage = current_page - 1;
                        }
                        if(cpage == "<<" || cpage == '&lt;&lt;'){
                            cpage = 1;
                        }
                        
                        if( cpage <  1 || cpage > pages || cpage == current_page ){
                            return;
                        }

                        //get pagination
                        var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();
                        
                        //search
                        var search_text = jQuery("input[name=ksd_tkt_search_input_"+tab_id+"]").val();
                        
                         jQuery( _getTabId(tab_id) ).addClass("pending");
                          _this.getTickets( _getTabId(tab_id), search_text, limit, cpage-1);
                        
                    });
        }
}