/*After jquery before other scripts
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * */
KSDUtils = function(){
    _this = this;
    this.init = function(){
        
    }
    
    /**Show update/error/Loading dialog while performing AJAX calls and on completion*/
    this.showDialog = function(dialog_type,message){
            message = message || "Loading...";//Set default message
            jQuery('.'+dialog_type).html(message);//Set the message
            jQuery('.'+dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
     }
     
     
}