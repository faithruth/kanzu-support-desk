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
         /**AJAX: Retrieve summary statistics for the dashboard**/
        if(jQuery("ul.dashboard-statistics-summary").hasClass("pending")){  
                    jQuery.post(	ksd_admin.ajax_url, 
                        { 	action : 'ksd_get_dashboard_summary_stats',
                                ksd_admin_nonce : ksd_admin.ksd_admin_nonce					
                        }, 
                            function(response) {
                      jQuery("ul.dashboard-statistics-summary").removeClass("pending");
                       var raw_response = JSON.parse(response);
                       var unassignedTickets = ( 'undefined' !== typeof raw_response.unassigned_tickets[0] ? raw_response.unassigned_tickets[0].unassigned_tickets : 0 );
                       var openTickets = ( 'undefined' !== typeof raw_response.open_tickets[0] ? raw_response.open_tickets[0].open_tickets : 0)
                       var averageResponseTime = ( 'undefined' !== typeof raw_response.average_response_time ? raw_response.average_response_time : '00:00' );
                       var the_summary_stats = "";
                       the_summary_stats+= "<li>"+openTickets+" <span>"+ksd_admin.ksd_labels.dashboard_open_tickets+"</span></li>";
                       the_summary_stats+= "<li>"+unassignedTickets+" <span>"+ksd_admin.ksd_labels.dashboard_unassigned_tickets+"</span></li>";
                       the_summary_stats+= "<li>"+averageResponseTime+" <span>"+ksd_admin.ksd_labels.dashboard_avg_response_time+"</span></li>";
                       jQuery("ul.dashboard-statistics-summary").html(the_summary_stats);                                   
                });	
        }
    }//eof:statistics
	
	/*Initialise charts*/
	this.charts = function(){
            try{
            /**The dashboard charts. These have their own onLoad method so they can't be run inside jQuery( document ).ready({});**/
                    function ksdDrawDashboardGraph() {	
                        jQuery.post( ksd_admin.ajax_url, 
                                {action : 'ksd_dashboard_ticket_volume',
                                    ksd_admin_nonce : ksd_admin.ksd_admin_nonce
                                }, 
                                function( response ) {	
                                    //IMPORTANT! Google Charts, without width & height explicitly specified, are drawn
                                    //to fill the parent element. This doesn't work so well if the parent element is hidden
                                    //while the drawing is happening. In such cases, the final chart will have default dimensions (400px x 200px)
                                    //To work-around this, we first unhide our parent div just before drawing the chart
                                    var ksdChartContainer = document.getElementById( 'dashboard' );                                    
                                    if ( 'undefined' !== typeof google.visualization ) //First check if we can draw a Google Chart
                                       ksdChartContainer.style.display = 'block';//Unhide the parent element
                                    var ksdData =  google.visualization.arrayToDataTable( JSON.parse(response) );                                   
                                    var ksdOptions = {
                                        title: ksd_admin.ksd_labels.dashboard_chart_title
                                                };
                                    var ksdDashboardChart = new google.visualization.LineChart(document.getElementById('ksd_dashboard_chart'));
                                    //Add a listener to know when drawing the chart is complete.                     
                                    google.visualization.events.addListener( ksdDashboardChart, 'ready', function () {
                                       if ( ! jQuery('ul.ksd-main-nav li:first').hasClass("ui-tabs-active") ) {
                                           ksdChartContainer.style.display = 'none'; //If our dashboard tab isn't the selected one, we hide it. 
                                       }                                        
                                            });                                           
                                    ksdDashboardChart.draw( ksdData, ksdOptions );  
                        });//eof: jQuery.port
                    }
                   google.setOnLoadCallback(ksdDrawDashboardGraph);
              }catch( err ){
                  jQuery('#ksd_dashboard_chart').html( err );
              }
	}//eof:charts
}