 (function($) {
    $(document).ready(function () {
     	$( function() {
    	$( "#ksd-gs-tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    	$( "#ksd-gs-tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
  	});
     	$(".ksd-gs-nav").click(function() { 	
     		var active = $( "#ksd-gs-tabs" ).tabs( "option", "active" ); 
     		if( $(this).hasClass( 'ksd-gs-nav-prev' ) ){
     			active--;
     		}else{
     			active++;
     		}
    		$( "#ksd-gs-tabs" ).tabs( "option", "active", active );
    		var tabs_offset = $("#ksd-gs-tabs").offset();
    		scrollTo(tabs_offset.left, tabs_offset.top);
		});
});
 })(jQuery);