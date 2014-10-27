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
}

KSDUtils.showDialog = function(dialog_type,message){
    /**Show update/error/Loading dialog while performing AJAX calls and on completion*/
    message = message || "Loading...";//Set default message
    jQuery('.'+dialog_type).html(message);//Set the message
    jQuery('.'+dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
}

KSDUtils.isNumber = function(){
    return typeof n== "number" && isFinite(n) && n%1===0;
}