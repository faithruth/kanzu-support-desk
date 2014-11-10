/*Load jQuery before this file.
 * @requires KSDUtils
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * */
KSDSettings = function(){
 
    _this = this;
    this.init = function(){
            //Mail settings
            this.submitMailForm();
            this.generateSettingsAccordion();
    }
 
    
    
	/*
	 * Submit Mail Settings form.
	 */
	this.submitMailForm = function(){
        /**AJAX: Update settings @TODO Handle errors**/
        jQuery('form#update-settings').submit( function(e){
            e.preventDefault();         
            var data;
            if ( jQuery(this).find("input[type=submit]:focus" ).hasClass("ksd-reset") ){//The  reset button has been clicked
                data = { action: 'ksd_reset_settings' , ksd_admin_nonce : ksd_admin.ksd_admin_nonce }
            }
            else{//The update button has been clicked
                data =  jQuery(this).serialize();//The action and nonce are hidden fields in the form
            }          
            KSDUtils.showDialog("loading");  
            jQuery.post(ksd_admin.ajax_url, 
                        data, 
                        function(response) {//@TODO Check for errors 	
                            KSDUtils.showDialog("success",JSON.parse(response));       
                        });
        });
        
        
         //Add Tooltips for the settings panel
         jQuery( ".help_tip" ).tooltip();
         
	}//eof:submitMailForm
 
    
    
    /*
     * Generate an Accordion to wrap different sections of the
     * settings separately
     */
    this.generateSettingsAccordion = function(){
        jQuery( "#settings-accordion" ).accordion( {
            heightStyle: "content",
            collapsible: true
        });

    }//eof:generateSettingsAccordion
 
	
}//eof:KSDSettings