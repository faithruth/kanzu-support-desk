/*Load google chart first. */
google.load("visualization", "1", {packages:["corechart"]});

jQuery( document ).ready(function() {
    
        /**For the general navigation tabs**/
	jQuery( "#tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( "#tabs > ul > li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
        
        /**Add tooltips to the main navigation**/
        jQuery('ul.ksd-main-nav li img').tooltip({
            track: true
            });
 
        /*Get URL parameters*/
        jQuery.urlParam = function(name){
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results===null){
               return null;
            }
            else{
               return results[1] || 0;
            }
        };
        
 
        //Settings
        Settings = new KSDSettings();
        Settings.init();
        
        //Dashboard
        Dashboard = new KSDDashboard();
        Dashboard.init();
        
        //Tickets
        Tickets = new KSDTickets();
        Tickets.init();
 
});
