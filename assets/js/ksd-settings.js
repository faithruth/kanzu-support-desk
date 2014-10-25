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