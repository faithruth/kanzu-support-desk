
google.load("visualization", "1", {packages:["corechart"]});

jQuery( document ).ready(function() {
    
        /**For the general navigation tabs**/
	jQuery( "#tabs").tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
        

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
