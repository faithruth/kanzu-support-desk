/*Load jQuery before this file.
 * @requires KSDUtils
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * */
KSDDashboard = function(){
    _this = this;
    
    this.init = function(){
            this.statistics();
            this.charts();
    }
	
    /*
     * Show statistics summary.
     */
    this.statistics = function(){
        try{
            /**AJAX: Retrieve summary statistics for the dashboard**/
           if(jQuery("ul.dashboard-statistics-summary").hasClass("pending")){  
                       jQuery.post(	ksd_admin.ajax_url, 
                           { 	action : 'ksd_get_dashboard_summary_stats',
                                   ksd_admin_nonce : ksd_admin.ksd_admin_nonce					
                           }, 
                               function(response) {
                         jQuery("ul.dashboard-statistics-summary").removeClass("pending");
                          var raw_response = JSON.parse(response);
                          var the_summary_stats = "";
                          the_summary_stats+= "<li>"+raw_response.open_tickets[0].open_tickets+" <span>Total Open Tickets</span></li>";
                          the_summary_stats+= "<li>"+raw_response.unassigned_tickets[0].unassigned_tickets+" <span>Unassigned Tickets</span></li>";
                          the_summary_stats+= "<li>"+raw_response.average_response_time+" <span>Avg. Response Time</span></li>";
                          jQuery("ul.dashboard-statistics-summary").html(the_summary_stats);                                   
                   });	
           }
       }catch(err){
           console.log(err);
       }
    }//eof:statistics
	
	/*Initialise charts*/
	this.charts = function(){
            try{
            /**The dashboard charts. These have their own onLoad method so they can't be run inside jQuery( document ).ready({});**/
                  function drawDashboardGraph() {	
                        jQuery.post( ksd_admin.ajax_url, 
                                {action : 'ksd_dashboard_ticket_volume',
                                    ksd_admin_nonce : ksd_admin.ksd_admin_nonce
                                }, 
                                function(response) {	              		
                                    var data =  google.visualization.arrayToDataTable(JSON.parse(response));
                                    var options = {
                                        title: 'Incoming Tickets'
                                                };
                                    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                                    chart.draw(data, options);
                        });//eof: jQuery.port
                  }
                  
                   google.setOnLoadCallback(drawDashboardGraph);
                  
                  
              }catch(err){
                  //console.log(err);
              }
	}//eof:charts
}