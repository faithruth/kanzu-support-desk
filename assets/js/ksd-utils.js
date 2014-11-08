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
    message = message || ksd_admin.ksd_labels.msg_loading;//Set default message
    jQuery('.'+dialog_type).html(message);//Set the message
    jQuery('.'+dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
}

KSDUtils.isNumber = function(){
    return typeof n== "number" && isFinite(n) && n%1===0;
}
/*
KSDUtils.LOGLEVEL = 1;//0:INFO|1:DEBUG|2:ERROR|3:WARNING|4:ALL|-1:NONE
KSDUtils.LOGLEVELS=['KSDINFO','KSDDEBUG','KSDERROR','KSDWARNING','KSDALL','NONE']; 
KSDUtils.log = function(msg, level){ //DEGUG, INFO
    if(typeof level === 'undefined') level = 1; //defualt
    if( KSDUtils.LOGLEVEL == 0 && level == KSDUtils.LOGLEVEL){
        console.log( KSDUtils.LOGLEVELS[level] + ": " + msg);
        return;
    }
    
    if( KSDUtils.LOGLEVEL == 1 && level == KSDUtils.LOGLEVEL){
        console.log( KSDUtils.LOGLEVELS[level] + ": " + msg);
        return;
    }
    
    if( KSDUtils.LOGLEVEL == 2 && level == KSDUtils.LOGLEVEL){
        console.log( KSDUtils.LOGLEVELS[level] + ": " + msg);
        return;
    }
    
    if( KSDUtils.LOGLEVEL == 3 ){
        lvl= (level > -1 && level < KSDUtils.LOGLEVELS.length) ? 
        KSDUtils.LOGLEVELS[level] : "KSDLOG";
        console.log( KSDUtils.LOGLEVELS[level] + ": " + msg);
        return;
    }
    
    if( KSDUtils.LOGLEVEL == -1){
        return;
    }
}*/