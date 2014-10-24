/*Load jQuery before this file.
 * @requires KSDUtils
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * */
KSDSettings = function(){
<<<<<<< HEAD
	this.init = function(){
		
		//Mail settings
		this.submitMailForm();
	}

	ksd_show_dialog = function(dialog_type,message){           
        message = message || "Loading...";//Set default message
        jQuery('.'+dialog_type).html(message);//Set the message
        jQuery('.'+dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
    };
=======
    _this = this;
    this.init = function(){
            //Mail settings
            this.submitMailForm();
    }
>>>>>>> 6d234f6512385596bd6eac40236b2424b2cdf746
    
    
	/*
	 * Submit Mail Settings form.
	 */
	this.submitMailForm = function(){
        /**AJAX: Update settings @TODO Handle errors**/
        jQuery('form#update-settings').submit( function(e){
            e.preventDefault();   
            KSDUtils.showDialog("loading");  
            jQuery.post(ksd_admin.ajax_url, 
                        jQuery(this).serialize(), //The action and nonce are hidden fields in the form
                        function(response) {//@TODO Check for errors 	
                            KSDUtils.showDialog("success",JSON.parse(response));       
                        });
        });
        
        
         //Add Tooltips for the settings panel
         jQuery( ".help_tip" ).tooltip();
         
	}//eof:submitMailForm
	
}//eof:KSDSettings