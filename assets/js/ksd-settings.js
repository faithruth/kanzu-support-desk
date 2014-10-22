/*Load jQuery before this file.*/
KSDSettings = function(){
	this.init = function(){
		
		//Mail settings
		this.submitMailForm();
	}

	ksd_show_dialog = function(dialog_type,message){           
        message = message || "Loading...";//Set default message
        jQuery('.'+dialog_type).html(message);//Set the message
        jQuery('.'+dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
    };
    
    
	/*
	 * Submit Mail Settings form.
	 */
	this.submitMailForm = function(){
        /**AJAX: Update settings @TODO Handle errors**/
        jQuery('form#update-settings').submit( function(e){
            e.preventDefault();   
            ksd_show_dialog("loading");  
            jQuery.post(	ksd_admin.ajax_url, 
                                jQuery(this).serialize(), //The action and nonce are hidden fields in the form
		function(response) {//@TODO Check for errors 	
                    ksd_show_dialog("success",JSON.parse(response));       
            });
        });
        
	}//eof:submitMailForm
	
}//eof:KSDSettings